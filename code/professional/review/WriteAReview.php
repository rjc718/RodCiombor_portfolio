<?php
namespace Pedstores\Ped\Models\WriteAReview;

use InvalidArgumentException;
use Pedstores\Ped\Models\Request;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Models\Sites\Configuration;
use Pedstores\Ped\Databases\Database;
use Pedstores\Ped\Models\Stores\Email\NewsletterList;
use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Utilities\CustomerName;
use Pedstores\Ped\Views\PhpTemplateView;
use finfo;
use RuntimeException;
use PDO;

class WriteAReview
{
    public const CSRF_TOKEN                    = 'write_review';
    public const GET_GETIMAGE_PRODUCT_DATA_SQL = <<<SQL
        SELECT `p`.`products_model`, `p`.`products_model_alt`, `p`.`products_id`, `p`.`products_bimage`, `pd`.`products_name`, `m`.`manufacturers_image`
        FROM `products` AS `p`
        LEFT JOIN `products_description` AS `pd` ON `p`.`products_id` = `pd`.`products_id`
        LEFT JOIN `manufacturers` AS `m` ON `p`.`manufacturers_id` = `m`.`manufacturers_id`
        WHERE `p`.`products_id` = ?
        LIMIT 1
    SQL;
    public const GET_PRODUCTS_MODEL_SQL = <<<SQL
        SELECT `products_model`, `products_model_alt`
        FROM `products`
        WHERE `products_id` = ?
        LIMIT 1
    SQL;
    public const GET_ORDER_INFO = <<<SQL
        SELECT `o`.`orders_id`, `o`.`date_purchased`
        FROM `orders` AS `o`
        LEFT JOIN `orders_products` AS `op` ON `o`.`orders_id` = `op`.`orders_id`
        WHERE `o`.`customers_email_address` = ?
        AND (`op`.`products_id` = ? OR `op`.`is_kit` = ?)
        ORDER BY `o`.`orders_id` DESC
        LIMIT 1
    SQL;
    public const INSERT_INTO_REVIEWS = <<<SQL
        INSERT INTO `reviews`
            (`products_id`, `customers_id`, `customers_name`, `reviews_rating`, `date_added`, `last_modified`, `email_address`, `state`, `stores_id`, `verified`, `reviews_incentive`)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    SQL;
    public const INSERT_INTO_REVIEWS_DESCRIPTION = <<<SQL
        INSERT INTO `reviews_description`
            (`reviews_id`, `languages_id`, `reviews_text`, `story_grade`, `story_application`,
            `story_title`, `video_code`, `video_caption`, `r_features`, `r_quality`,
            `r_perform`, `r_value`, `r_pros`, `r_cons`, `r_friend`, `p_date`, `installer_review`)
        VALUES
            (?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?)
    SQL;
    public const INSERT_INTO_INSTALLER_REVIEWS = <<<SQL
        INSERT INTO `installer_reviews`
            (customer_id, installer_id, q, p, communication, message, order_id, date, other_installer, contacted, reviews_id)
        VALUES
            (?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?)
    SQL;
    public const GET_INSTALLER_NAME = <<<SQL
        SELECT `company_name`
        FROM `installers` WHERE `customers_id` = ?
        LIMIT 1
    SQL;
    public const GET_STATE_NAME = <<<SQL
        SELECT `zone_name`
        FROM `zones`
        WHERE `zone_id` = ?
        LIMIT 1
    SQL;
    public const GET_ALL_STATES = <<<SQL
        SELECT `zone_id`, `zone_code`, `zone_name` 
        FROM `zones` 
        WHERE `zone_country_id` = 223 
        ORDER BY `zone_name` asc
    SQL;
    public const GET_ADDITIONAL_ORDERED_PRODUCTS = <<<SQL
        SELECT `pd`.`products_name`, `p`.`products_id`, `p`.`products_model`, `p`.`products_bimage`, `o`.`orders_id`, `s`.`stores_url`
        FROM `orders` AS `o`
        LEFT JOIN `orders_products` AS `op` ON `op`.`orders_id` = `o`.`orders_id`
        LEFT JOIN `products` AS `p` ON `op`.`products_id` = `p`.`products_id`
        LEFT JOIN `products_description` AS `pd` ON `p`.`products_id` = `pd`.`products_id`
        LEFT JOIN `stores` AS `s` ON `o`.`orders_stores_id` = `s`.`stores_id`
        WHERE `o`.`customers_email_address` = ?
        AND `p`.`products_status` = '1'
        AND `p`.`products_id` != ?
        LIMIT 10
    SQL;
    public const GET_CUSTOMERS_REVIEW_ID = <<<SQL
        SELECT r.reviews_id
        FROM reviews AS r
        LEFT JOIN reviews_description AS rd ON r.reviews_id = rd.reviews_id
        WHERE r.email_address = :reviewerEmail
        AND (r.products_id = :pId OR rd.accessory1 = :pId OR rd.accessory2 = :pId)
        LIMIT 1
    SQL;
    public const GET_CUSTOMER_ID = <<<SQL
        SELECT `customers_id`
        FROM `customers`
        WHERE `customers_id` = ?
        LIMIT 1
    SQL;
    public const GET_CUSTOMER_ID_BY_EMAIL = <<<SQL
        SELECT `customers_id`
        FROM `customers`
        WHERE `customers_email_address` = ?
        LIMIT 1
    SQL;
    public const GET_ORDER_ID = <<<SQL
        SELECT `orders_id`
        FROM `orders`
        WHERE `orders_id` = ?
        LIMIT 1
    SQL;
    protected const UPDATE_REVIEW_DESCRIPTION_IMAGES_DATA = <<<SQL
        UPDATE `reviews_description`
        SET `image1` = ?, `image2` = ?, `image3` = ?, `image4` = ?,
        `image_caption1` = ?, `image_caption2` = ?, `image_caption3` = ?, `image_caption4` = ?
        WHERE `reviews_id` = ?
    SQL;
    public const GET_CUSTOMER_ORDER_INFO_BY_EMAIL = <<<SQL
        SELECT `orders_id`, `customers_name`, `customers_email_address`, `customers_state` 
        FROM `orders`
        WHERE `orders_id` = ?
        AND `customers_email_address` = ?
    SQL;
    public const GET_CUSTOMER_ORDER_INFO_BY_EMAIL_HASH = <<<SQL
        SELECT `orders_id`, `customers_name`, `customers_email_address`, `customers_state` 
        FROM `orders`
        WHERE `orders_id` = ?
        AND `email_hash` = ?
    SQL;
    public const GET_ZONE_ID_BY_CODE = <<<SQL
        SELECT `zone_id`
        FROM `zones`
        WHERE `zone_code` = ?
    SQL;
    public const GET_ZONE_ID_BY_NAME = <<<SQL
        SELECT `zone_id`
        FROM `zones`
        WHERE `zone_name` = ?
    SQL;
    private const SCHEMA_DIR = '/var/www/vhosts/schemas/products/reviews';
    private const TMP_DIR    = 'product/review/modal/';
    private const ERROR_DIR  = '/product/review/modal/errors/';
    private $db;
    private $store;
    private $config;
    protected $nl;
    private $request;
    private $view;
    private $errorMsg;
    private $exceptionList;
    private $fileErrorMsg;

    public function __construct(
        Database $db,
        NewsletterList $nl,
        Configuration $config,
        Store $store,
        Request $request,
        PhpTemplateView $view
    ) {
        $this->db            = $db;
        $this->config        = $config;
        $this->store         = $store;
        $this->nl            = $nl;
        $this->request       = $request;
        $this->view          = $view;
        $this->errorMsg      = '';
        $this->fileErrorMsg  = '';
        $this->exceptionList = [];
    }

    public static function getInstance(
        ?Database $db = null,
        ?NewsletterList $nl = null,
        ?Configuration $config = null,
        ?Store $store = null,
        ?Request $request = null,
        ?PhpTemplateView $view = null
    ) {
        if (empty($db)) {
            $db = Database::getInstance();
        }
        if (empty($nl)) {
            $nl = NewsletterList::getInstance();
        }
        if (empty($config)) {
            $config = Configuration::getInstance();
        }
        if (empty($store)) {
            $store = Store::getInstance();
        }
        if (empty($request)) {
            $request = Request::getInstance();
        }
        if (empty($view)) {
            $view = PhpTemplateView::getInstance();
        }
        return new self($db, $nl, $config, $store, $request, $view);
    }

    public function getProductData(string $action, array $params)
    {
        $data = [];

        switch ($action) {
            case 'getmodel':
                list($storesId, $id) = $params;

                $sql = '
                    SELECT `p`.`products_id`, `p`.`products_model`, `p`.`products_model_alt`, `p`.`products_bimage`, `pd`.`products_name`
                    FROM `products` AS `p`
                    LEFT JOIN `products_description` AS `pd` ON `p`.`products_id` = `pd`.`products_id`, `products_to_stores` AS `p2s`
                    WHERE `p`.`products_id` = `p2s`.`products_id`' .
                    ($storesId != 13 ? ' AND `p2s`.`stores_id` = ' . $storesId : '') . '
                    AND `p`.`manufacturers_id` = ?
                    ORDER BY `p`.`products_model`';
                $data = $this->db->getQueryData($sql, [$id], PDO::FETCH_ASSOC);
                break;
            case 'getimage':
                $id   = $params[0];
                $data = $this->db->getQueryRow(self::GET_GETIMAGE_PRODUCT_DATA_SQL, [$id], PDO::FETCH_ASSOC);
                break;
            default:
                break;
        }

        return $data;
    }

    public function getPageData(): array
    {
        $data = [];
        if ($this->request->getMethod() == 'POST') {
            if ($this->request->getWord('action', INPUT_POST) === 'uploadImage') {
                //This is only to make the image uploader populate on the modal
                //Does not need to be checked against schemas here and form data not in JSON format.
                //Photo data will be validated when views change/forms submit
                $data['action'] = 'uploadImage';
            } else {
                $data = $this->request->getJsonInput('php://input', true);

                //Convert select datapoints from strings to ints or sanitize
                $data['viewId']     = (int) $data['viewId'];
                $data['r_product']  = (int) $data['r_product'];
                $data['storesId']   = (int) $data['storesId'];
                $data['incentives'] = (int) $data['incentives'] ?? 0;
                $data['oId']        = (int) $data['oId']        ?? 0;

                //eId is hashed email address, used with oId to look up prefill info
                //uses sha256 hash, should always be 64 characters long
                //If it is not, or is null, set to empty string
                //If empty wont load prefill info, but wont throw error

                $data['eId'] = $data['eId'] ?? '';
                $data['eId'] = $this->request->sanitizeStringInput($data['eId']);

                if (strlen($data['eId']) != 64) {
                    $data['eId'] = '';
                }

                //Check fields required to load modal
                //Kill the function and return empty array if any are empty
                $required = [
                    $data['action'],
                    $data['viewId'],
                    $data['r_product'],
                    $data['storesId']
                ];
                foreach ($required as $value) {
                    if (empty($value)) {
                        return [];
                    }
                }
                //Kill function if viewsId exceeds maximum allowed value
                //Kill function if storiesId value not an active store
                $activeStores = $this->store->getAllStores(true);
                $storeFound   = false;
                foreach ($activeStores as $store) {
                    if ($store->stores_id == $data['storesId']) {
                        $storeFound = true;
                        break;
                    }
                }
                if (!$storeFound || $data['viewId'] > 3) {
                    return [];
                }

                //Save/Reload filled out form data if view changes
                if ($data['viewId'] == 2) {
                    $this->request->setSession('reviewModalData', $data['savedData']);
                } elseif ($data['viewId'] == 1) {
                    $saved = $this->request->getSession('reviewModalData');

                    if (!empty($saved)) {
                        $savedData = json_decode($saved, true);
                        //Make sure product id in session matches product id on Write Review button
                        //Need to make sure session data doesn't make incorrect data display if they close modal and open modal for different product
                        if ($savedData['r_product'] == $data['r_product']) {
                            foreach ($savedData as $key => $value) {
                                $data[$key] = $value;
                            }
                        }
                        $this->request->unsetSession('reviewModalData');
                    }
                }
                $groups = $this->groupSchemaValidationData($data ?? []);
                $this->validateSchemaData($groups);
            }
        }
        return $data ?? [];
    }

    public function groupSchemaValidationData(array $data): array
    {
        $groups['modalSchemaData']      = [];
        $groups['userSchemaData']       = [];
        $groups['photoSchemaData']      = [];
        $groups['formSubmitSchemaData'] = [];
        $groups['installerSchemaData']  = [];

        $groups['modalSchemaData'] = [
            'action'    => $data['action'],
            'r_product' => $data['r_product'],
            'storesId'  => $data['storesId'],
            'viewId'    => $data['viewId'],
            'savedData' => $data['savedData']
        ];

        if (!empty($data['eId'])) {
            $groups['userSchemaData'] = [
                'eId'        => $data['eId'],
                'oId'        => $data['oId'],
                'incentives' => $data['incentives']
            ];
        }

        $groups['photoSchemaData'] = [
            'image_temp'   => $data['image_temp'],
            'image_name'   => $data['image_name'],
            'image_r_name' => $data['image_r_name'],
            'image_t_name' => $data['image_t_name'],
            'r_cap'        => $data['r_cap']
        ];

        //Form submit only
        if ($data['viewId'] == 3) {
            $groups['formSubmitSchemaData'] = [
                'r_feat'        => $data['r_feat'],
                'r_qual'        => $data['r_qual'],
                'r_per'         => $data['r_per'],
                'r_value'       => $data['r_value'],
                'r_overall'     => $data['r_overall'],
                'r_rec'         => $data['r_rec'],
                'title'         => $data['title'],
                'story_data'    => $data['story_data'],
                'r_pros'        => $data['r_pros'],
                'r_cons'        => $data['r_cons'],
                'r_video'       => $data['r_video'],
                'video_caption' => $data['video_caption'],
                'display_name'  => $data['display_name'],
                'email'         => $data['email'],
                'state'         => $data['state'],
                'subscribe'     => $data['subscribe'],
                'review_token'  => $data['review_token']
            ];

            if ($this->allInstallationKeysExist($data, true)) {
                $groups['installerSchemaData'] = [
                    'installer_selected'      => $data['installer_selected'],
                    'installer_communication' => $data['installer_communication'],
                    'installer_work'          => $data['installer_work'],
                    'installer_price'         => $data['installer_price'],
                    'installer_comments'      => $data['installer_comments'],
                    'installer_other'         => $data['installer_other'] ?? '',
                    'installer_contact'       => $data['installer_contact'] ?? 0
                ];
            }
        }
        return $groups;
    }

    public function validateSchemaData(array $groups): void
    {
        $this->validateSchemaGroup(
            self::SCHEMA_DIR . '/modal',
            $groups['modalSchemaData']
        );

        if (count($groups['userSchemaData']) > 0) {
            $this->validateSchemaGroup(
                self::SCHEMA_DIR . '/user',
                $groups['userSchemaData']
            );
        }
        if (count($groups['installerSchemaData']) > 0) {
            $this->validateSchemaGroup(
                self::SCHEMA_DIR . '/installer',
                $groups['installerSchemaData']
            );
        }
        if (count($groups['photoSchemaData']) > 0) {
            $this->validateSchemaGroup(
                self::SCHEMA_DIR . '/photo',
                $groups['photoSchemaData']
            );
        }
        if (count($groups['formSubmitSchemaData']) > 0) {
            $this->validateSchemaGroup(
                self::SCHEMA_DIR . '/submit',
                $groups['formSubmitSchemaData']
            );
        }
    }

    public function validateSchemaGroup(string $path, array $data): void
    {
        if (!$this->request->validateJsonSchema($path, $data)) {
            if (empty($this->getErrorMsg())) {
                $this->setErrorMsg('Invalid data detected.<br>');
            }
            $exceptions = $this->getValidationErrorMessage();
            for ($i = 0; $i < count($exceptions); $i++) {
                $this->setExceptionList($exceptions[$i]);
            }
        }
    }

    public function getValidationErrorMessage(): array
    {
        $msgList = [];
        $errors  = $this->request->getSchemaErrors();

        foreach ($errors as $error) {
            $message = '';
            $prop    = $error['pointer'];
            switch ($error['constraint']) {
                case 'exclusiveMinimum':
                    $message = $error['property'] . ' ' . strtolower(
                        str_replace(
                            'value of',
                            'value greater than',
                            $error['message']
                        )
                    );
                    break;
                case 'minItems':
                    $message = str_replace(
                        'array',
                        'products array',
                        $error['message']
                    );
                    break;
                default:
                    $message = "{$error['message']}. pointer: {$prop}. ";
            }
            array_push($msgList, $message);
        }
        return $msgList;
    }

    public function getSqlParams(
        string $queryToBuildFor,
        array $data,
        array $requiredData = []
    ): array {
        $params = [];
        switch ($queryToBuildFor) {
            case 'reviewsInsert':
                $params = [
                    $data['pId'],
                    $data['cId'],
                    tep_db_input($data['reviewerDisplayName']),
                    $data['productRating'],
                    date("Y-m-d H:i:s"),
                    date("Y-m-d H:i:s"),
                    tep_db_input($data['reviewerEmail']),
                    tep_db_input($data['reviewerState']),
                    $data['storeId'],
                    $data['isVerifiedCustomer'],
                    $data['reviewsIncentive']
                ];
                break;
            case 'reviewsDescriptionInsert':
                $params = [
                    $data['reviewsInsertId'],
                    1,
                    mb_convert_encoding(
                        $data['reviewerStoryData'],
                        "HTML-ENTITIES",
                        'UTF-8'
                    ),
                    $data['reviewerStoryGrade'],
                    $data['reviewerStoryApplication'],
                    mb_convert_encoding(
                        $data['reviewerStoryTitle'],
                        "HTML-ENTITIES",
                        'UTF-8'
                    ),
                    $data['reviewerVideoLink'],
                    mb_convert_encoding(
                        $data['reviewerVideoCaption'],
                        "HTML-ENTITIES",
                        'UTF-8'
                    ),
                    $data['reviewFeatures'],
                    $data['reviewQuality'],
                    $data['reviewPerformance'],
                    $data['reviewValue'],
                    $data['pros'],
                    $data['cons'],
                    $data['reviewRecommendToFriend'],
                    $data['purchaseDate'],
                    $data['installerReview'],
                ];
                break;
            case 'installerReviewsInsert':
                $params = [
                    $data['installerCustomersId'],
                    $data['installerSelected'],
                    $data['installerWork'],
                    $data['installerPrice'],
                    $data['installerCommunication'],
                    mb_convert_encoding(
                        $data['installerComment'],
                        "HTML-ENTITIES",
                        'UTF-8'
                    ),
                    $data['installerOrderId'],
                    date("Y-m-d H:i:s"),
                    tep_db_input($data['installerOtherName']),
                    $data['installerContacted'],
                    $data['reviewsInsertId']
                ];
                break;
            case 'newsletterListInsert':
                $params = [
                    ':email'      => tep_db_input($data['reviewerEmail']),
                    ':first_name' => tep_db_input(
                        ucwords($data['reviewerFirstName'])
                    ),
                    ':last_name' => tep_db_input(
                        ucwords($data['reviewerLastName'])
                    ),
                    ':state'      => $requiredData['fullStateName'],
                    ':added_from' => 'review'
                ];
                break;
            case 'reviewsImageUpdate':
                $params = [
                    isset($data['uploaded_image'][0]) ? $data['uploaded_image'][0] : '',
                    isset($data['uploaded_image'][1]) ? $data['uploaded_image'][1] : '',
                    isset($data['uploaded_image'][2]) ? $data['uploaded_image'][2] : '',
                    isset($data['uploaded_image'][3]) ? $data['uploaded_image'][3] : '',
                    isset($data['r_cap'][0]) ? $data['r_cap'][0] : '',
                    isset($data['r_cap'][1]) ? $data['r_cap'][1] : '',
                    isset($data['r_cap'][2]) ? $data['r_cap'][2] : '',
                    isset($data['r_cap'][3]) ? $data['r_cap'][3] : '',
                    $data['reviewsInsertId']
                ];
                break;
            default:
                break;
        }

        return $params;
    }

    private function setProsOrConsValue(string $input, bool $isPro = true): string
    {
        $strToCheck = $isPro ? 'Add Pro' : 'Add Con';
        $finalVal   = '';
        $arr        = [];
        $invalid    = [
            '<', '>', ';', '{', '}',
            '[', ']', '`', '~', '|',
            '%22', '%3C', '%3E', '%25', '%7B',
            '%7D', '%7C', '%5C', '%5E', '%7E',
            '%5B', '%5D', '%60', '2F'
        ];

        $data = explode('|', $input);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $value) {
                $tempValue = filter_var($value, FILTER_SANITIZE_STRING);
                if (!in_array(
                    str_replace($invalid, '', $tempValue),
                    ['on', '']
                )
                ) {
                    $arr[] = preg_replace(
                        '/[^A-Za-z0-9\s!.,\']/',
                        '',
                        $tempValue
                    );
                }
            }

            $implodedArr = implode('|', $arr);
            $finalVal    = $implodedArr != $strToCheck ? $implodedArr : '';
        }
        return $finalVal;
    }

    private function sanitizeString(string $str, string $specialCase = ''): string
    {
        $finalVal = '';
        $tempVal  = '';
        $invalid  = [
            '<', '>', ';', '{', '}',
            '[', ']', '`', '~', '|',
            '%22', '%3C', '%3E', '%25', '%7B',
            '%7D', '%7C', '%5C', '%5E', '%7E',
            '%5B', '%5D', '%60', '2F'
        ];

        if ($specialCase == 'email') {
            $tempVal = filter_var($str, FILTER_SANITIZE_EMAIL);
        } else {
            $tempVal = filter_var($str, FILTER_SANITIZE_STRING);
        }

        $finalVal = str_replace($invalid, '', $tempVal);

        return $finalVal;
    }

    private function sanitizeVideoLink(string $str): string
    {
        $tempVal = filter_var($str, FILTER_SANITIZE_URL);
        $invalid = [
            '"', '<', '>', ';', '{', '}',
            '[', ']', '`', '~', '|',
            '(', ')', '%22', '%3C', '%7B',
            '%7D', '%7C', '%5C', '%5E', '%7E',
            '%5B', '%5D', '%60', '%28', '%29'
        ];
        $finalVal = str_replace($invalid, '', $tempVal);

        return $finalVal != 'Paste video URL from Youtube' ? $finalVal : '';
    }

    public function sanitizeData(array $data): array
    {
        //Ensure all data is sanitized and encoded before DB is updated

        //General data
        $tempData = [
            'pId'                      => (int) $data['r_product'],
            'cId'                      => (int) $data['cId'],
            'viewId'                   => (int) $data['viewId'],
            'reviewerDisplayName'      => $this->sanitizeString($data['display_name']),
            'reviewerFirstName'        => !empty($data['fname']) ? $this->sanitizeString($data['fname']) : '',
            'reviewerLastName'         => !empty($data['lname']) ? $this->sanitizeString($data['lname']) : '',
            'reviewerEmail'            => $this->sanitizeString($data['email'], 'email'),
            'reviewerState'            => (int) $data['state'],
            'reviewFeatures'           => (int) $data['r_feat'],
            'reviewQuality'            => (int) $data['r_qual'],
            'reviewValue'              => (int) $data['r_value'],
            'reviewPerformance'        => (int) $data['r_per'],
            'reviewRecommendToFriend'  => (int) $data['r_rec'],
            'pros'                     => !empty($data['r_pros']) ? $this->setProsOrConsValue($data['r_pros'], true) : '',
            'cons'                     => !empty($data['r_cons']) ? $this->setProsOrConsValue($data['r_cons']) : '',
            'reviewerVideoLink'        => !empty($data['r_video']) ? $this->sanitizeVideoLink($data['r_video']) : '',
            'reviewerVideoCaption'     => !empty($data['r_video']) ? $this->sanitizeString($data['video_caption']) : '',
            'reviewerStoryGrade'       => '0',
            'reviewerStoryApplication' => '1',
            'reviewerStoryData'        => $this->sanitizeString($data['story_data']),
            'reviewerStoryTitle'       => $this->sanitizeString($data['title']),
            'image_temp'               => filter_var_array($data['image_temp'], FILTER_SANITIZE_STRING),
            'image_name'               => filter_var_array($data['image_name'], FILTER_SANITIZE_STRING),
            'image_r_name'             => filter_var_array($data['image_r_name'], FILTER_SANITIZE_STRING),
            'image_t_name'             => filter_var_array($data['image_t_name'], FILTER_SANITIZE_STRING),
            'r_cap'                    => filter_var_array($data['r_cap'], FILTER_SANITIZE_STRING),
            'subscribe'                => !empty($data['subscribe']) ? (int) $data['subscribe'] == 1 : false,
            'reviewsIncentive'         => (int) $data['incentives']
        ];

        //Installation data
        $tempData['installerSelected'] = !empty($data['installer_selected']) ? (int) $data['installer_selected'] : 0;

        if ($tempData['installerSelected'] == 1 && !empty($data['installer_other'])) {
            $tempData['installerOtherName'] = $this->sanitizeString($data['installer_other']);
        } else {
            $tempData['installerOtherName'] = '';
        }

        $tempData['installerContacted']     = !empty($data['installer_contact']) ? (int) $data['installer_contact'] : 0;
        $tempData['installerExperience']    = !empty($data['installer_experience']) ? (int) $data['installer_experience'] : 0;
        $tempData['installerWork']          = !empty($data['installer_work']) ? (int) $data['installer_work'] : 0;
        $tempData['installerPrice']         = !empty($data['installer_price']) ? (int) $data['installer_price'] : 0;
        $tempData['installerCommunication'] = !empty($data['installer_communication']) ? (int) $data['installer_communication'] : 0;
        $tempData['installerComment']       = !empty($data['installer_comments']) ? $this->sanitizeString($data['installer_comments']) : '';
        $tempData['installerCustomersId']   = !empty($data['cId']) ? (int) $data['cId'] : 0;
        $tempData['installerOrderId']       = !empty($data['oId']) ? (int) $data['oId'] : 0;
        $tempData['installerReview']        = ($tempData['installerSelected'] != 0 || !empty($tempData['installerComment'])) ? 1 : 0;

        return $tempData;
    }

    public function performErrorChecking(array $data): array
    {
        //Ensure all input data is valid, set errorMsg if not.
        //errorMsg will be what the customer sees.  If it is set the page will not update.
        //This will run in addition to the schema validation
        //Do not sanitize data here.  Do that in sanitizeData() function.

        $errorMsg     = '';
        $youtubeRegex = '/^(?:https?:\/\/)?(?:www\.)?(((youtube-nocookie\.com\/|youtube\.com\/)(?:embed\/|\?v=|\?vi=|v\/|vi\/|watch\?v=|watch\?vi=|watch\?.+&v=|user\/))|youtu\.be\/)(?:\S+)?$/';
        $spamPatterns = [
            'bit\.ly', 'truenewworld', 'vibrationinstitute', '\þ', 'paydayloans',
            'accion\.org', 'handbag', 'louisvuitton', 'roadtrue\.com', 'louboutin',
            'replica\-handbags', 'fakehandbags', 'xrumer\.biz', 'nonprofitworlds\.info', 'url\=',
            'a href', 'viagra', 'v1agra', 'cialis', 'plavix',
            'xanax', 'rolex', 'herbal', 'natural(?! gas)', 'naturally',
            'drugs', 'enlargement', 'fda', 'pharmacy', 'HGH',
            'hgh', 'wealthy', 'pill', 'metabolism', 'wrinkles',
            'cellulite', 'hormone', 'celebrities', 'libido', 'sexual',
            'healthier', 'organs', 'younger', 'young', 'stamina',
            'virility', 'Presenting', ' anal ', ' sex ', 'prescription',
            'bb\.txt', 'Ceawdweque', 'coachbags', 'dactempata', 'BakThoutTuh',
            'kpqimbvdbcb', 'fbmhuatofdz', 'gronoMok', 'zupxtpdpfzk', 'japan\.info',
            '\/member\/', 'online banking', 'dermatology', 'haircut', 'nginx',
            'www\.tube', 'tub3r\.com', 'tuber\.com', '\.info', 'trueway',
            'url\=http\:\/\/', 'Road to Truth', 'longchamp', 'windows 7', '\¤',
            'loan', 'mulberry', 'promising career', 'hair removal', 'fakebag',
            'samoasky', 'coolCheck', 'webdirection.ru', 'blacklinks', 'pradaoutlet',
            'http:\/\/money\-surveys\.net', 'hair\-removal', 'designer\-bags', 'Bakugan', '\;\&\#',
            'nitric oxide'];
        $spamRegex = '/(' . implode('|', $spamPatterns) . ')/i';

        //Product
        if (empty((int) $data['r_product'])) {
            $errorMsg .= 'Please select your product.<br>';
        }
        //Display Name
        if (empty($data['display_name'])) {
            $errorMsg .= 'Please enter your display name.<br>';
        }
        //First name
        if (empty($data['fname']) || $data['fname'] == 'First Name') {
            $errorMsg .= 'Please enter your first name.<br>';
        }
        //First + last name
        if (!empty($data['fname']) && !empty($data['lname']) && $data['fname'] == $data['lname']) {
            $errorMsg .= 'The first name cannot match the last name.<br>';
        }
        //Email
        if (empty($data['email'])) {
            $errorMsg .= 'Please enter your email address.<br>';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errorMsg .= 'The entered email address is not valid.<br>';
        }
        //Features
        if ($this->isEmptyOrOutOfRange((int) $data['r_feat'], 0, 5)) {
            $errorMsg .= 'Please rate the product features.<br>';
        }
        //Quality
        if ($this->isEmptyOrOutOfRange((int) $data['r_qual'], 0, 5)) {
            $errorMsg .= 'Please rate the product quality.<br>';
        }
        //Value
        if ($this->isEmptyOrOutOfRange((int) $data['r_value'], 0, 5)) {
            $errorMsg .= 'Please rate the product value.<br>';
        }
        //Performance
        if ($this->isEmptyOrOutOfRange((int) $data['r_per'], 0, 5)) {
            $errorMsg .= 'Please rate the product performance.<br>';
        }
        //Recommend
        if (!empty((int) $data['r_rec']) && (int) $data['r_rec'] != 1 && (int) $data['r_rec'] != 2) {
            $errorMsg .= 'Please choose whether you would recommend this product.<br>';
        }
        //Video
        if (!empty($data['r_video']) && preg_match($youtubeRegex, $data['r_video'], $match)) {
            if (count($match) == 0) {
                $errorMsg .= 'Please Review the YouTube video URL.<br>';
            }
        }
        //Story data
        if (empty($data['story_data'])) {
            $errorMsg .= 'Please enter your detailed review.<br>';
        } elseif (preg_match($spamRegex, $data['story_data'])) {
            $errorMsg .= 'You have entered one or more spam like terms. Please rephrase your detailed review.<br>';
            $data['story_data'] = preg_replace($spamRegex, '[Spam]', $data['story_data']);
            $errorMsg .= preg_replace($spamRegex, '[Spam]', $data['story_data']);
        }
        //Title
        if (empty($data['title'])) {
            $errorMsg .= 'Please enter your reviews title.<br>';
        }
        //Subscribe
        if ((int) $data['subscribe'] != 0 && (int) $data['subscribe'] != 1) {
            $errorMsg .= 'Please choose whether you would like to subscribe to our newsletter.<br>';
        }
        //Installation is present
        if ($this->allInstallationKeysExist($data)) {
            if ((int) $data['installer_contact'] != 0 && (int) $data['installer_contact'] != 1 && (int) $data['installer_contact'] != 2) {
                $errorMsg .= 'Please select whether the installer contacted you.<br>';
            }
            if (!$this->orderIdIsValid((int) $data['oId'])) {
                $errorMsg .= 'The order ID is not valid.<br>';
            }
            //If one of the Installer Rating Items is selected, they must all be selected.  So must installer selected
            if ($data['installer_work'] > 0 || $data['installer_communication'] > 0 || $data['installer_price'] > 0) {
                if ($data['installer_selected'] == 0) {
                    $errorMsg .= 'Please select an Installer.<br>';
                }
                if ($this->isEmptyOrOutOfRange((int) $data['installer_work'], 0, 5)) {
                    $errorMsg .= 'Please rate the installation quality of work.<br>';
                }
                if ($this->isEmptyOrOutOfRange((int) $data['installer_communication'], 0, 5)) {
                    $errorMsg .= 'Please rate the installation quality of communication.<br>';
                }
                if ($this->isEmptyOrOutOfRange((int) $data['installer_price'], 0, 5)) {
                    $errorMsg .= 'Please rate the installation price.<br>';
                }
            }
        }

        if (!empty($errorMsg)) {
            $this->setErrorMsg($errorMsg);
        }

        return $data;
    }

    private function orderIdIsValid($oId): bool
    {
        return !empty($this->getOrderId([$oId]));
    }

    public function verifyCustomer(array $data): array
    {
        $pModel = $this->getProductModel([$data['pId']]);
        $oInfo  = $this->getOrderInfo([$data['reviewerEmail'], $data['pId'], $pModel]);

        if (!empty($oInfo)) {
            $data['isVerifiedCustomer'] = true;
            $data['purchaseDate']       = substr($oInfo['date_purchased'], 0, 10);
        }
        return $data;
    }

    private function isEmptyOrOutOfRange($val, $min, $max): bool
    {
        return empty($val) || $val < $min || $val > $max;
    }

    private function allInstallationKeysExist(array $data, $excludeDynamicInputs = false): bool
    {
        $result = true;

        $allInstallationKeys = [
            'installer_selected',
            'installer_work',
            'installer_communication',
            'installer_price',
            'installer_comments',
            'oId'
        ];

        if (!$excludeDynamicInputs) {
            $allInstallationKeys[] = 'installer_other';
            $allInstallationKeys[] = 'installer_contact';
        }

        foreach ($allInstallationKeys as $key) {
            if (!array_key_exists($key, $data)) {
                $result = false;
            }
        }
        return $result;
    }

    public function getProductModel(array $params): string
    {
        $result = '';

        $row = $this->db->getQueryRow(
            self::GET_PRODUCTS_MODEL_SQL,
            $params,
            PDO::FETCH_ASSOC
        );

        $result = $row['products_model'];
        if (!empty($row['products_model_alt'])) {
            $result = $row['products_model_alt'];
        }
        return $result;
    }

    public function getOrderInfo(array $params): array
    {
        $row = $this->db->getQueryRow(
            self::GET_ORDER_INFO,
            $params,
            PDO::FETCH_ASSOC
        );
        return !empty($row) ? $row : [];
    }

    public function getProdStoreId(array $params, int $storeId): int
    {
        $sql = 'SELECT `stores_id` FROM `products_to_stores` WHERE `products_id` = ?';
        if ($storeId == 16) {
            $sql .= ' AND stores_id = 16';
        }
        $sql .= ' LIMIT 1';
        $val = $this->db->getQueryResult($sql, $params);

        return !empty($val) ? $val : 0;
    }

    public function getStoreCatalog(): string
    {
        return $this->config->get('DIR_FS_CATALOG') ?? '';
    }

    public function getSocialMediaLinks(): array
    {
        $configKeys = [
            'FACEBOOK_STORE_LINK',
            'YOUTUBE_STORE_LINK',
            'LINKEDIN_STORE_LINK',
            'INSTAGRAM_STORE_LINK'
        ];
        $info  = [];
        $links = [];

        foreach ($configKeys as $key) {
            $info[$key] = $this->config->get($key, $this->store->getId());
        }
        if (count($info) > 0) {
            $links = [
                [
                    'name' => 'Facebook',
                    'link' => $info['FACEBOOK_STORE_LINK']
                ],
                [
                    'name' => 'YouTube',
                    'link' => $info['YOUTUBE_STORE_LINK']
                ],
                [
                    'name' => 'Instagram',
                    'link' => $info['INSTAGRAM_STORE_LINK']
                ],
                [
                    'name' => 'LinkedIn',
                    'link' => $info['LINKEDIN_STORE_LINK']
                ]
            ];
        }
        return $links;
    }

    public function getProsList(): array
    {
        return [
            "Easy To Use",
            "Price",
            "I'd Buy It Again",
            "Easy To Store",
            "High Quality",
            "Features",
            "Reliable",
            "Durable",
            "Exceeded Expectations",
            "Quiet",
            "Light Weight"
        ];
    }

    public function getConsList(): array
    {
        return [
            "None",
            "Heavy",
            "Expensive",
            "Bad Instructions",
            "Packaging",
            "Low Quality",
            "Difficult To Use",
            "Unreliable"
        ];
    }

    public function insertDataIntoReviews(array $params): int
    {
        return $this->db->getIdForInsert(
            self::INSERT_INTO_REVIEWS,
            $params,
            PDO::FETCH_ASSOC
        );
    }

    public function insertDataIntoReviewsDescription(array $params): void
    {
        $this->db->execute(
            self::INSERT_INTO_REVIEWS_DESCRIPTION,
            $params
        );
    }

    public function updateReviewDescriptionImagesData(array $params): void
    {
        $this->db->execute(
            self::UPDATE_REVIEW_DESCRIPTION_IMAGES_DATA,
            $params
        );
    }

    public function insertDataIntoInstallerReviews(array $params): void
    {
        $this->db->execute(
            self::INSERT_INTO_INSTALLER_REVIEWS,
            $params
        );
    }

    public function getInstallerName(int $installerSelected): string
    {
        $row = $this->db->getQueryRow(
            self::GET_INSTALLER_NAME,
            [$installerSelected],
            PDO::FETCH_ASSOC
        );
        return !empty($row['company_name']) ? $row['company_name'] : '';
    }

    public function getFullStateName(int $reviewerState): string
    {
        $row = $this->db->getQueryRow(
            self::GET_STATE_NAME,
            [$reviewerState],
            PDO::FETCH_ASSOC
        );
        return !empty($row['zone_name']) ? $row['zone_name'] : '';
    }

    public function getStatesList(): array
    {
        $statesData = [];
        $statesList = [];

        $statesData = $this->db->getQueryData(self::GET_ALL_STATES);
        if (count($statesData) > 0) {
            foreach ($statesData as $state):
                $statesList[] = [
                    'zone_id'   => $state->zone_id,
                    'zone_name' => $state->zone_name
                ];
            endforeach;
        }
        return $statesList;
    }

    public function insertDataIntoNewsletterList(array $params): void
    {
        $this->nl->createNewSubscribedUser($params);
    }

    public function getAdditionalOrderedProducts(array $params): array
    {
        return $this->db->getQueryData(
            self::GET_ADDITIONAL_ORDERED_PRODUCTS,
            $params,
            PDO::FETCH_ASSOC
        );
    }

    public function getCustomersReviewId(array $params): array
    {
        return $this->db->getQueryRow(
            self::GET_CUSTOMERS_REVIEW_ID,
            $params,
            PDO::FETCH_ASSOC
        );
    }

    public function getCustomerId(array $params): string
    {
        return $this->db->getQueryResult(
            self::GET_CUSTOMER_ID,
            $params
        );
    }

    public function getCustomerIdByEmail(array $params): string
    {
        return $this->db->getQueryResult(
            self::GET_CUSTOMER_ID_BY_EMAIL,
            $params
        );
    }

    public function getOrderId(array $params): string
    {
        return $this->db->getQueryResult(
            self::GET_ORDER_ID,
            $params
        );
    }

    public function getCustomerSupportLink(): string
    {
        if ($this->store->getId() == 16) {
            $link = tep_href_link(
                'stories_info.php',
                'stories_id=1547',
                'NONSSL'
            );
        } else {
            $link = 'contact_us.php';
        }
        return $link;
    }

    public function getTermsOfUseLink(): string
    {
        if ($this->store->getId() == 16) {
            $link = tep_href_link(
                'stories_info.php',
                'stories_id=1548',
                'NONSSL'
            );
        } else {
            $link = 'terms-of-use.php';
        }
        return $link;
    }

    public function calculateProductRating(array $data): float
    {
        $productRating = 0;
        for ($i = 0; $i < count($data); $i++) {
            $productRating += $data[$i];
        }
        $productRating = ($productRating / count($data));

        return round($productRating);
    }

    public function getCustomerOrderInfoByEmail(int $oId, string $eId): array
    {
        $orderInfo = [];

        $params = [$oId, $eId];

        if (strstr($eId, '@')) {
            $orderInfo = $this->db->getQueryRow(
                self::GET_CUSTOMER_ORDER_INFO_BY_EMAIL,
                $params,
                PDO::FETCH_ASSOC
            );
        } else {
            $orderInfo = $this->db->getQueryRow(
                self::GET_CUSTOMER_ORDER_INFO_BY_EMAIL_HASH,
                $params,
                PDO::FETCH_ASSOC
            );
        }

        return $orderInfo;
    }

    public function getZoneIdByState(string $state): int
    {
        $zoneId = 0;

        if (strlen($state) == 2) {
            $zoneInfo = $this->db->getQueryRow(
                self::GET_ZONE_ID_BY_CODE,
                [$state],
                PDO::FETCH_ASSOC
            );
        } else {
            $zoneInfo = $this->db->getQueryRow(
                self::GET_ZONE_ID_BY_NAME,
                [$state],
                PDO::FETCH_ASSOC
            );
        }
        if (count($zoneInfo) > 0) {
            $zoneId = $zoneInfo['zone_id'];
        }
        return $zoneId;
    }

    public function getRatingItems(array $data): array
    {
        return [
            [
                'item_name'      => 'Features',
                'item_num'       => 1,
                'input_val_name' => 'r_feat',
                'input_val'      => (int) $data['r_feat'] ?? 0
            ],
            [
                'item_name'      => 'Quality',
                'item_num'       => 2,
                'input_val_name' => 'r_qual',
                'input_val'      => (int) $data['r_qual'] ?? 0
            ],
            [
                'item_name'      => 'Performance',
                'item_num'       => 3,
                'input_val_name' => 'r_per',
                'input_val'      => (int) $data['r_per'] ?? 0
            ],
            [
                'item_name'      => 'Value',
                'item_num'       => 4,
                'input_val_name' => 'r_value',
                'input_val'      => (int) $data['r_value'] ?? 0
            ]
        ];
    }

    public function getReviewItems(array $data): array
    {
        return [
            'r_rec' => [
                'id'    => 'r_rec_val',
                'name'  => 'r_rec',
                'value' => $data['r_rec']
            ],
            'title' => [
                'id'    => 'review_title',
                'name'  => 'title',
                'value' => $data['title']
            ],
            'story_data' => [
                'id'    => 'review_text',
                'name'  => 'story_data',
                'value' => $data['story_data']
            ],
            'pros_chk' => [
                'id'    => 'mpros_values',
                'name'  => 'pros_chk',
                'value' => $data['r_pros']
            ],
            'add_pro' => [
                'id'    => 'add_pro_val',
                'name'  => 'add_pro',
                'value' => $data['add_pro']
            ],
            'cons_chk' => [
                'id'    => 'mcons_values',
                'name'  => 'cons_chk',
                'value' => $data['r_cons']
            ],
            'add_con' => [
                'id'    => 'add_con_val',
                'name'  => 'add_con',
                'value' => $data['add_con']
            ],
            'r_video' => [
                'id'    => 'review_video_link',
                'name'  => 'r_video',
                'value' => $data['r_video']
            ],
            'video_caption' => [
                'id'    => 'review_video_caption',
                'name'  => 'video_caption',
                'value' => $data['video_caption']
            ],
            'display_name' => [
                'id'    => 'display_name',
                'name'  => 'display_name',
                'value' => $data['display_name']
            ],
            'email' => [
                'id'    => 'customer_email',
                'name'  => 'email',
                'value' => $data['email']
            ],
            'state' => [
                'id'    => 'customer_state',
                'name'  => 'state',
                'value' => $data['state']
            ],
            'email_subscribe' => [
                'id'    => 'email_subscribe_val',
                'name'  => 'email_subscribe',
                'value' => $data['subscribe']
            ],
            'image_name' => [
                'id'    => 'image_name_val',
                'name'  => 'image_name',
                'value' => $data['image_name'] ?? ''
            ],
            'image_r_name' => [
                'id'    => 'image_r_name_val',
                'name'  => 'image_r_name',
                'value' => $data['image_r_name'] ?? ''
            ],
            'image_t_name' => [
                'id'    => 'image_t_name_val',
                'name'  => 'image_t_name',
                'value' => $data['image_t_name'] ?? ''
            ],
            'prev_image_name' => [
                'id'    => 'prev_image_name',
                'name'  => 'prev_image_name',
                'value' => $data['prev_image_name'] ?? ''
            ],
            'prev_image_r_name' => [
                'id'    => 'prev_image_r_name',
                'name'  => 'prev_image_r_name',
                'value' => $data['prev_image_r_name'] ?? ''
            ],
            'prev_image_t_name' => [
                'id'    => 'prev_image_t_name',
                'name'  => 'prev_image_t_name',
                'value' => $data['prev_image_t_name'] ?? ''
            ],
            'r_cap' => [
                'id'    => 'r_cap_val',
                'name'  => 'r_cap',
                'value' => $data['r_cap'] ?? ''
            ],
            'prev_r_cap' => [
                'id'    => 'prev_r_cap',
                'name'  => 'prev_r_cap',
                'value' => $data['prev_r_cap'] ?? ''
            ]
        ];
    }

    public function getInstallItems(array $data): array
    {
        return [
            'installer_selected' => [
                'id'    => 'installer_selected',
                'name'  => 'installer_selected',
                'value' => (int) $data['installer_selected'] ?? 0
            ],
            'installer_other' => [
                'id'    => 'installer_other',
                'name'  => 'installer_other',
                'value' => $data['installer_other'] ?? ''
            ],
            'installer_contact' => [
                'id'    => 'install_contact_val',
                'name'  => 'installer_contact',
                'value' => (int) $data['installer_contact'] ?? 0
            ],
            'installer_comments' => [
                'id'    => 'installer_comments',
                'name'  => 'installer_comments',
                'value' => $data['installer_comments'] ?? ''
            ]
        ];
    }

    public function getInstallRatingItems(array $data): array
    {
        return [
            [
                'item_name'      => 'Communication',
                'item_num'       => 5,
                'input_val_name' => 'installer_communication',
                'input_val'      => (int) $data['installer_communication'] ?? 0
            ],
            [
                'item_name'      => 'Work Quality',
                'item_num'       => 6,
                'input_val_name' => 'installer_work',
                'input_val'      => (int) $data['installer_work'] ?? 0
            ],
            [
                'item_name'      => 'Price',
                'item_num'       => 7,
                'input_val_name' => 'installer_price',
                'input_val'      => (int) $data['installer_price'] ?? 0
            ]
        ];
    }

    public function getListedInstallers(int $orderId)
    {
        $listedInstallers = [];

        // Lets look for this order, to see if we sent them any leads //
        if ($orderId != 0) {
            $installerReviewSql   = "SELECT sql_no_cache id FROM installer_reviews WHERE order_id = ?";
            $installerReviewCheck = $this->db->getQueryData(
                $installerReviewSql,
                [$orderId],
                PDO::FETCH_ASSOC
            );

            if ((int) $installerReviewCheck['id'] == 0) {
                $listedInstallers[] = [
                    'id'   => '0',
                    'text' => 'Select Your Installer'
                ];

                $installerLeadsSql = "SELECT sql_no_cache i.company_name, i.customers_id 
                                    FROM installer_impressions im 
                                    LEFT JOIN installers i 
                                    ON (im.installers_id = i.customers_id) 
                                    WHERE im.orders_id = ? 
                                    GROUP BY i.company_name 
                                    ORDER BY i.company_name";

                $installerLeadsData = $this->db->getQueryData(
                    $installerLeadsSql,
                    [$orderId],
                    PDO::FETCH_ASSOC
                );

                if (count($installerLeadsData) > 0) {
                    foreach ($installerLeadsData as $installerLeads) {
                        if (!empty(
                                $installerLeads['customers_id']
                            ) && !empty(
                                $installerLeads['company_name']
                            )
                        ) {
                            $listedInstallers[] = [
                                'id'   => $installerLeads['customers_id'],
                                'text' => $installerLeads['company_name']
                            ];
                        }
                    }
                }

                $listedInstallers[] = [
                    'id'   => '1',
                    'text' => 'Other Installer'
                ];
            }
        }
        return $listedInstallers;
    }

    public function getButtonViewParams(
        int $prodId,
        string $classes = '',
        string $extraHtml = '',
        string $text = '',
        int $viewId = 1,
        string $elemType = ''
    ): array {
        $orderId    = 0;
        $emailId    = '';
        $incentives = 0;

        if (empty($text)) {
            $text = 'Write A Review';
        }
        if (empty($elemType)) {
            $elemType = 'button';
        }

        if ($this->request->getMethod() == 'GET') {
            $orderId = $this->request->getInteger(
                'oID',
                INPUT_GET
            );

            $emailId = $this->request->getSafeString(
                'eID',
                INPUT_GET
            );

            $incentives = $this->request->getInteger(
                'incentives',
                INPUT_GET
            );
        }

        $dataAttr = [
            [
                'attr'  => 'data-view-id',
                'value' => $viewId ?? 1
            ],
            [
                'attr'  => 'data-prod-id',
                'value' => $prodId
            ],
            [
                'attr'  => 'data-stores-id',
                'value' => $this->store->getId()
            ],
            [
                'attr'  => 'data-order-id',
                'value' => $orderId ?? 0
            ],
            [
                'attr'  => 'data-email-id',
                'value' => $emailId
            ],
            [
                'attr'  => 'data-incentives',
                'value' => $incentives ?? 0
            ]
        ];

        $dataStr = '';
        foreach ($dataAttr as $d) {
            $dataStr .= ' ' . $d['attr'] . '="' . $d['value'] . '"';
        }

        //openReviewModal determines if the modal slideout functionality will fire onclick.
        //It should be true for all buttons except for buttons within the modal itself that change the view
        if ($viewId == 1) {
            $classes .= ' openReviewModal';
        }

        $viewData = [
            'text'      => $text ?? 'Write A Review',
            'data'      => $dataStr,
            'classes'   => trim($classes),
            'extraHtml' => ' ' . trim($extraHtml) ?? ''
        ];

        if ($elemType != 'button') {
            $viewData['elem'] = $elemType;
        }
        return $viewData ?? [];
    }

    public function createButton(
        int $prodId,
        string $classes = '',
        string $extraHtml = '',
        string $text = '',
        int $viewId = 1,
        string $elemType = ''
    ): string {
        return $this->view->renderTemplate(
            $this->createButtonTemplate(
                $prodId,
                $classes,
                $extraHtml,
                $text,
                $viewId,
                $elemType
            )
        );
    }

    public function createButtonTemplate(
        int $prodId,
        string $classes = '',
        string $extraHtml = '',
        string $text = '',
        int $viewId = 1,
        string $elemType = ''
    ): Data {
        if (empty($elemType)) {
            $tmp = 'button';
        } elseif ($elemType != 'button') {
            $tmp = 'elembutton';
        }
        return new Data(
            self::TMP_DIR . $tmp . '.php',
            $this->getButtonViewParams(
                $prodId,
                $classes,
                $extraHtml,
                $text,
                $viewId,
                $elemType
            )
        );
    }

    public function getTargetDivViewParams(): array
    {
        if ($this->request->getMethod() == 'GET') {
            $openReviewModal = $this->request->getInteger('openReviewModal');
        }
        return [
            'cssLink' => auto_version('/css/write-review-modal.css'),
            'jsSrc'   => sprintf(
                '%s%s%s',
                $this->store->getUrl(
                    Store::DEFAULT
                ),
                '/js',
                auto_version(
                    '/review-modal.js',
                    '/var/www/vhosts/ped.com/js'
                )
            ),
            'openReviewModal' => $openReviewModal ?? 0,
            'storeUrl'        => $this->store->getUrl()
        ];
    }

    public function createTargetDiv(): string
    {
        return $this->view->renderTemplate(
            $this->createTargetDivTemplate()
        );
    }

    public function createTargetDivTemplate(): Data
    {
        return new Data(
            self::TMP_DIR . 'targdiv.php',
            $this->getTargetDivViewParams()
        );
    }

    public function getErrorMsg(): string
    {
        return $this->errorMsg ?? '';
    }

    public function setErrorMsg(string $newMsg): void
    {
        $this->errorMsg .= $newMsg;
    }

    public function getFileErrorMsg(): string
    {
        return $this->fileErrorMsg ?? '';
    }

    public function setFileErrorMsg(string $newMsg): void
    {
        $this->fileErrorMsg .= $newMsg;
    }

    public function getExceptionList(): array
    {
        return $this->exceptionList ?? [];
    }

    public function setExceptionList(string $newException): void
    {
        array_push($this->exceptionList, $newException);
    }

    public function getViewParams(array $data): array
    {
        //Define modalData array keys before assigning them data
        $modalData = [];
        $modalData = $this->getEmptyModalDataArray((int) $data['viewId']);

        switch ((int) $data['viewId']) {
            case 3:
                $modalData = [
                    'templateView'    => 'thankyou',
                    'socialLinks'     => $this->getSocialMediaLinks(),
                    'errorMsgStr'     => $data['errorMsgStr'],
                    'fileErrorMsgStr' => $data['fileErrorMsgStr']
                ];
                break;
            case 2:
                $modalData = [
                    'templateView'        => 'guidelines',
                    'termsOfUseLink'      => $this->getTermsOfUseLink(),
                    'customerSupportLink' => $this->getCustomerSupportLink()
                ];
                break;
            default:
                $preFillInfo = [];
                $custInfo    = [];

                if ((int) $data['oId'] != 0 && !empty($data['eId'])) {
                    $custInfo = $this->getCustomerOrderInfoByEmail(
                        $data['oId'],
                        $data['eId']
                    );
                    $preFillInfo = $this->getCustomerPrefillData($custInfo);
                }

                //Get data for product image
                $prodImgInfo = $this->getProductData(
                    'getimage',
                    [$data['r_product']]
                );

                $prodModel = $prodImgInfo['products_model'];
                if (!empty($prodImgInfo['products_model_alt'])) {
                    $prodModel = $prodImgInfo['products_model_alt'];
                }
                $modalData = [
                    'templateView' => 'default',
                    'prodName'     => $prodImgInfo['products_name'],
                    'prodModel'    => $prodModel,
                    'prodImg'      => sprintf(
                        '%s/products-image/100/%s',
                        $this->store->getUrl(),
                        $prodImgInfo['products_bimage']
                    ),
                    'termsOfUseLink'     => $this->getTermsOfUseLink(),
                    'statesList'         => $this->getStatesList(),
                    'storesName'         => $this->store->getName(),
                    'prosList'           => $this->getProsList(),
                    'consList'           => $this->getConsList(),
                    'reviewItems'        => $this->getReviewItems($data),
                    'ratingItems'        => $this->getRatingItems($data),
                    'installItems'       => $this->getInstallItems($data),
                    'installRatingItems' => $this->getInstallRatingItems($data),
                    'listedInstallers'   => $this->getListedInstallers(
                        (int) $custInfo['orders_id']
                    ),
                    'preFillInfo' => $preFillInfo,
                    'submitBtn'   => $this->createButton(
                        $data['r_product'],
                        'PED_button button',
                        implode(' ', [
                            'id="submit-btn"',
                            'data-valid="0"',
                            'data-active="0"'
                        ]),
                        'Submit Review',
                        3
                    ),
                    'viewGuidelinesBtn' => $this->createButton(
                        $data['r_product'],
                        '',
                        'id="guidelines-btn"',
                        'View Guidelines',
                        2,
                        'span'
                    ),
                    'errorMsgStr'     => $data['errorMsgStr'],
                    'fileErrorMsgStr' => $data['fileErrorMsgStr']
                ];
        }

        if ($data['viewId'] != 3) {
            $modalData['prodId']     = $data['r_product'];
            $modalData['storesId']   = $data['storesId'];
            $modalData['oId']        = $data['oId'];
            $modalData['eId']        = (!empty($data['eId']) ? $data['eId'] : '0');
            $modalData['incentives'] = $data['incentives'];
        }

        return $modalData;
    }

    public function getEmptyModalDataArray(int $viewId)
    {

        //Return array with empty view params.  Ensures no bad data is passed into further function calls.
        //This allows error messages from schema to display and be logged instead of just having page break

        $modalData = [];
        switch ($viewId) {
            case 3:
                $modalData = [
                    'templateView'    => '',
                    'socialLinks'     => '',
                    'errorMsgStr'     => '',
                    'fileErrorMsgStr' => '',
                ];
                break;
            case 2:
                $modalData = [
                    'templateView'        => '',
                    'termsOfUseLink'      => '',
                    'customerSupportLink' => ''
                ];
                break;
            default:
                $modalData = [
                    'templateView'       => '',
                    'prodName'           => '',
                    'prodModel'          => '',
                    'prodImg'            => '',
                    'termsOfUseLink'     => '',
                    'statesList'         => [],
                    'storesName'         => '',
                    'prosList'           => [],
                    'consList'           => [],
                    'reviewItems'        => [],
                    'ratingItems'        => [],
                    'installItems'       => [],
                    'installRatingItems' => [],
                    'listedInstallers'   => [],
                    'reviewToken'        => '',
                    'preFillInfo'        => [],
                    'errorMsgStr'        => '',
                    'fileErrorMsgStr'    => '',
                    'submitBtn'          => '',
                    'viewGuidelinesBtn'  => ''
                ];
                break;
        }
        if ($viewId != 3) {
            $modalData['prodId']     = 0;
            $modalData['storesId']   = 0;
            $modalData['oId']        = 0;
            $modalData['eId']        = '';
            $modalData['incentives'] = 0;
        }
        return $modalData;
    }

    public function getCustomerPrefillData(array $custInfo): array
    {
        $preEmail    = '';
        $preInitials = '';
        $preFname    = '';
        $preLname    = '';
        $preState    = 0;
        $preFillInfo = [];

        if ((int) $custInfo['orders_id'] != 0) {
            $cleanName = CustomerName::split_full_name(
                $custInfo['customers_name']
            );
            if (filter_var($custInfo['customers_email_address'], FILTER_VALIDATE_EMAIL)) {
                $preEmail = $custInfo['customers_email_address'];
            }

            $preInitials = $cleanName['initials'];
            $preFname    = $cleanName['fname'];
            $preLname    = $cleanName['lname'];

            // Have to Cross Reference, as the data is stored in the wrong format //
            if (!empty($custInfo['customers_state'])) {
                $preState = $this->getZoneIdByState(
                    $custInfo['customers_state']
                );
            }
        }
        $displayNameWords = [];
        if (!empty($preInitials) && empty($preFname)) {
            $displayNameWords[] = $preInitials;
        }
        if (!empty($preFname)) {
            $displayNameWords[] = $preFname;
        }
        if (!empty($preLname)) {
            $preLname           = substr($preLname, 0, 1);
            $displayNameWords[] = $preLname;
        }

        $preFillInfo['display_name'] = implode(' ', $displayNameWords);
        $preFillInfo['email']        = $preEmail;
        $preFillInfo['state']        = $preState;

        return $preFillInfo;
    }

    public function prepareActionProcessData(array $data): array
    {
        //Split Display Name
        $displayNameArr = explode(" ", $data['display_name']);
        if (count($displayNameArr) > 0) {
            $data['fname'] = $displayNameArr[0];
        }
        if (count($displayNameArr) > 1) {
            $data['lname'] = $displayNameArr[1];
        }

        //Prepare Photo Data
        if (empty($data['image_temp'])) {
            $data['image_temp'] = [];
        } else {
            $data['image_temp'] = explode('|', $data['image_temp']);
        }
        if (empty($data['image_name'])) {
            $data['image_name'] = [];
        } else {
            $data['image_name'] = explode('|', $data['image_name']);
        }
        if (empty($data['image_r_name'])) {
            $data['image_r_name'] = [];
        } else {
            $data['image_r_name'] = explode('|', $data['image_r_name']);
        }
        if (empty($data['image_t_name'])) {
            $data['image_t_name'] = [];
        } else {
            $data['image_t_name'] = explode('|', $data['image_t_name']);
        }
        if (empty($data['r_cap'])) {
            $data['r_cap'] = [];
        } else {
            $data['r_cap'] = explode('|', $data['r_cap']);
        }

        //perform error checking
        $data = $this->performErrorChecking($data);

        if (empty($this->getErrorMsg())) {
            //Get customer ID if Exists
            $data['cId'] = $this->getCustomerIdByEmail([$data['email']]);
            if (empty($data['cId'])) {
                $data['cId'] = 0;
            }
            //sanitize
            $data = $this->sanitizeData($data);

            //Add extra non form data
            $data['storeId'] = $this->store->getId();
            $prodStoreId     = $this->getProdStoreId(
                [$data['pId']],
                $data['storeId']
            );
            if (!empty($prodStoreId)) {
                $data['storeId'] = $prodStoreId;
            }

            $data['storeCatalog']      = $this->getStoreCatalog();
            $data['storeEmailAddress'] = $this->config->get(
                'STORE_OWNER_EMAIL_ADDRESS'
            );
            $data['storeName']                  = $this->store->getName();
            $data['fileCounter']                = 1;
            $data['triggerStorageBucketUpload'] = false;
            $data['imageCheck']                 = false;
            $data['videoCheck']                 = !empty($data['reviewerVideoLink']);
            $data['reviewsInsertId']            = 0;

            //calculate product rating
            $data['productRating'] = $this->calculateProductRating(
                [
                                            $data['reviewFeatures'],
                                            $data['reviewQuality'],
                                            $data['reviewValue'],
                                            $data['reviewPerformance']
                                        ]
            );

            //Make sure customer is verified
            $data['isVerifiedCustomer'] = false;
            $data['purchaseDate']       = '';
            $data                       = $this->verifyCustomer($data);
        }
        return $data;
    }

    public function buildErrorMessage(
        string $template,
        string $code = '',
        string $msg = ''
    ): string {
        //Builds front end message that will display on modal
        return $this->view->render(
            self::ERROR_DIR . $template . '.php',
            [
            'code' => $code,
            'msg'  => $msg
        ]
        );
    }

    public function handleErrors(array $data): array
    {
        $base         = "WRITE REVIEW ERROR ";
        $errorMsg     = $this->getErrorMsg();
        $errorMsgStr  = '';
        $exceptions   = $this->getExceptionList();
        $exceptionMsg = '';

        //Make sure a template displays no matter what
        if (empty($data['viewId'])) {
            $data['viewId'] = 1;
        }

        //Error Message will appear from the following scenarios:
        //1.) Bad schema data
        //2.) Expired CSRF token on form submit
        //3.) Failure in actionProcess performErrorChecking()

        if (!empty($errorMsg)) {
            if ((int) $data['viewId'] == 3) {
                $errorMsgStr = $this->buildErrorMessage(
                    'submit',
                    '',
                    $errorMsg
                );
                $exceptionMsg = sprintf(
                    '%s203: Invalid Form Data submitted: %s',
                    $base,
                    $errorMsg
                );
            } else {
                $errorMsgStr  = $this->buildErrorMessage('load', '303');
                $exceptionMsg = $base . "303: Invalid Form Data on modal load";
            }
        }

        //Check JSON Exception List from Schema.
        //Override generic exception message from above with specific one from schema
        if (count($exceptions) > 0) {
            $exceptionMsg = $base . "403: " . $exceptions[0];
        }

        try {
            if (!empty($exceptionMsg)) {
                throw new InvalidArgumentException(
                    $exceptionMsg
                );
            }
        } catch (InvalidArgumentException $e) {
            error_log($e->getMessage());
        }

        $data['errorMsgStr']     = $errorMsgStr;
        $data['fileErrorMsgStr'] = $this->getFileErrorMsg();

        return $data;
    }

    public function handleEmptyData(): array
    {
        error_log("WRITE REVIEW ERROR 103: Empty Modal Data submitted");
        return [
            'errorMsgStr'  => $this->buildErrorMessage('load', '103'),
            'templateView' => 'default'
        ];
    }

    public function uploadImage(): void
    {
        $inputData = [
            'updatedImageNames' => [
                'r_image' => [
                    'name'        => 'image',
                    'allowUpload' => false
                ]
            ]
        ];
        $catalog = $this->getStoreCatalog();

        foreach ($inputData['updatedImageNames'] as $key => &$value) {
            $fileInfo = $_FILES[$value['name']];

            if (empty($fileInfo)) {
                continue;
            }

            $data            = [];
            $data['success'] = false;

            try {
                $fileName = $this->request->sanitizeStringInput(
                    $fileInfo['name']
                );

                // Undefined | Multiple Files | $_FILES Corruption Attack
                // If this request falls under any of them, treat it invalid.
                if (
                    !isset($fileInfo['error']) || is_array($fileInfo['error'])
                ) {
                    throw new RuntimeException('Invalid parameters.');
                }

                // Check $_FILES['upfile']['error'] value.
                switch ($fileInfo['error']) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        throw new RuntimeException('No file sent.');
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        throw new RuntimeException('Exceeded filesize limit.');
                    default:
                        throw new RuntimeException('Unknown errors.');
                }

                // You should also check filesize here.
                if ($fileInfo['size'] > 10485760) {
                    throw new RuntimeException('Exceeded filesize limit.');
                }

                // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
                // Check MIME Type by yourself.
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if (false === $ext = array_search(
                    $finfo->file($fileInfo['tmp_name']),
                    [
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp',
                    ],
                    true
                )) {
                    throw new RuntimeException('Invalid file format.');
                }

                $tmpName = $this->request->sanitizeStringInput(
                    $fileInfo['tmp_name']
                );
                $tmpName = sha1_file($tmpName);
                $dest    = $catalog . 'images/stories/submitted/tmp';

                $value['allowUpload'] = true;
                $data['tmp_name']     = sprintf(
                    '%s/%s.%s',
                    $dest,
                    $tmpName,
                    strtolower($ext)
                );
                $data['name']    = $fileName;
                $data['success'] = true;

                if ($this->request->getUploadedFile(
                    $value['name'],
                    $dest,
                    'image/' . strtolower($ext),
                    $tmpName . '.' . strtolower($ext)
                )) {
                    $this->getErrorMsg('');
                } else {
                    throw new RuntimeException('Failed to move uploaded file.');
                }
            } catch (RuntimeException $e) {
                $this->setErrorMsg($e->getMessage());
            }
            $data['message'] = $this->getErrorMsg();
            echo json_encode($data);
        }
    }

    public function uploadAllowedFiles(array $data): array
    {
        $hasError = false;
        foreach ($data['image_temp'] as $key => $value) {
            $updatedImageName = 'review_id_' . $data['reviewsInsertId'];
            $updatedImageName .= '_image' . $key . '.';
            $updatedImageName .= strtolower(
                pathinfo(
                    $data['image_name'][$key],
                    PATHINFO_EXTENSION
                )
            );

            $data['uploaded_image'][]           = $updatedImageName;
            $data['imageCheck']                 = true;
            $data['triggerStorageBucketUpload'] = true;
            $data['fileCounter']++;

            $path = $data['storeCatalog'] . 'images/stories/submitted/';
            if (copy($value, $path . $updatedImageName)) {
                $this->setFileErrorMsg('');
            } else {
                if (!$hasError) {
                    $this->setFileErrorMsg(
                        'There was an error uploading the file(s), please try again!'
                    );
                    $hasError = true;
                }
            }
            @unlink($value);
        }
        return $data;
    }
}