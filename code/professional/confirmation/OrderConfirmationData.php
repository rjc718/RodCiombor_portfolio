<?php
namespace Pedstores\Ped\Models\Legacy;

use Pedstores\Ped\Models\Legacy\PageModel;
use Pedstores\Ped\Utilities\CustomerName;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Models\Sites\Configuration;
use Pedstores\Ped\Models\Sites\Breadcrumb;
use Pedstores\Ped\Models\Request;
use Pedstores\Ped\Databases\Database;
use Symfony\Component\Routing\Router;
use Pedstores\Ped\Models\Legacy\Order;
use Pedstores\Ped\Models\Product;
use Ramsey\Uuid\Uuid;
use PDO;
use Pedstores\Ped\Models\Cart;
use Pedstores\Ped\Models\Orders\Total;
use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Views\Collection;

class OrderConfirmationData extends PageModel
{
    protected $store;
    protected $config;
    private $bonusItemList;
    private $products;
    private $pickups;
    private $kitData;
    private $lpuKitData;
    private $productsWithKits;
    private $lpuProductsWithKits;
    private $kitProductList;
    private $kitCount;
    private $totalKitPrice;
    private $totalComponents;
    private $warranties;
    private $installerProducts;
    private $PEDCashTotal;
    private $hasSubs;
    public const GET_GUEST_CHECKOUT_INFO = <<<SQL
        SELECT customers_id, guest_checkout
        FROM customers
        WHERE customers_id = ?
    SQL;
    public const DELETE_GUEST_FROM_CUSTOMER_TABLE = <<<SQL
        DELETE FROM customers WHERE customers_id = ?
    SQL;
    public const DELETE_GUEST_FROM_CUSTOMER_INFO_TABLE = <<<SQL
        DELETE FROM customers_info WHERE customers_info_id = ?
    SQL;
    public const DELETE_GUEST_FROM_ADDRESS_BOOK_TABLE = <<<SQL
        DELETE FROM address_book WHERE customers_id = ?
    SQL;
    public const UPDATE_ORDERS_CUSTOMER_ID = <<<SQL
        UPDATE orders SET customers_id = ? WHERE orders_id = ?
    SQL;
    public const UPDATE_SUBSCRIPTIONS_CUSTOMER_ID = <<<SQL
        UPDATE subscriptions SET customers_id = ? WHERE cybs_subscription_id = ?
    SQL;
    public const GET_LPU_BRANCH_INFO = <<<SQL
        SELECT `branch_name`,
               `street` AS `branch_address`,
               `locality` AS `branch_city`,
               `region_code` AS `branch_state`,
               `postal_code` AS `branch_zip`
        FROM `branches`
        JOIN `locations`
            USING(`location_id`)
        WHERE branch_number = ?
    SQL;
    public const GET_REBATE_INFO = <<<SQL
        SELECT *
        FROM promo_items
        WHERE products_id = ?
        AND status = 1
        AND ? >= start_date
        AND ? <= end_date
        AND rebate_type <> 0
        AND (stores_id != 16 OR stores_id IS NULL)
    SQL;
    public const GET_REBATE_INFO_ACW = <<<SQL
        SELECT *
        FROM promo_items
        WHERE products_id = ?
        AND status = 1
        AND ? >= start_date
        AND ? <= end_date
        AND rebate_type <> 0
        AND stores_id = 16
    SQL;
    public const GET_BONUS_ITEMS = <<<SQL
        SELECT *
        FROM promo_items
        WHERE products_id = ?
        AND status = 1
        AND ? >= start_date
        AND ? <= end_date
        AND (bonus_item_1 <> 0 OR bonus_item_2 <> 0)
        AND stores_id = ?
    SQL;
    public const GET_BONUS_ITEM_DATA = <<<SQL
        SELECT p.products_id as id, p.products_model as model, p.products_bimage, p.products_model_alt, pd.products_name as name, pd.products_url, pda.products_alt_name
        FROM products p
        LEFT JOIN products_description pd ON p.products_id = pd.products_id
        LEFT JOIN products_description_alt pda ON p.products_id = pda.products_id
        WHERE p.products_id = ?
    SQL;
    public const GET_KIT_ITEMS = <<<SQL
        SELECT pd.products_name, pd.products_url, k.component_pid, k.component_model
        FROM kits k
        LEFT JOIN products_description pd
        ON pd.products_id = k.component_pid
        WHERE master_pid = ?
    SQL;
    public const GET_XSELL_ITEMS = <<<SQL
        SELECT DISTINCT p.products_id as id, p.products_model as model, p.products_bimage, pd.products_name as name, pd.products_url, p.products_tax_class_id, p.products_price, p.products_retail_price, p.products_extra_discount
        FROM products_xsell xp, products p
        JOIN products_description pd USING(products_id)
        WHERE xp.products_id = ?
        AND xp.xsell_id = p.products_id
        AND p.products_id = pd.products_id AND p.products_status = 1
        ORDER BY sort_order
        ASC LIMIT 3
    SQL;
    public const GET_KIT_MASTER_PRODUCT_INFO = <<<SQL
        SELECT p.products_id as id, p.products_model as model, p.products_model_alt, p.products_bimage, pd.products_name as name, pd.products_url, pda.products_alt_name
        FROM products p
        LEFT JOIN products_description pd ON p.products_id = pd.products_id
        LEFT JOIN products_description_alt pda ON p.products_id = pda.products_id
        WHERE p.products_model = :model
        OR p.products_model_alt = :model
    SQL;
    public const GET_KIT_MASTER_PRODUCT_ID = <<<SQL
        SELECT p.products_id as id
        FROM products p
        LEFT JOIN products_description_alt pda ON p.products_id = pda.products_id
        WHERE p.products_model = :model
        OR p.products_model_alt = :model
    SQL;
    public const GET_INSTALLER_PACKAGE_SKU_LIST = <<<SQL
        SELECT sku FROM installer_packages WHERE prod_id = ?
    SQL;
    public const GET_ORDERS_TOTAL_VALUE = <<<SQL
        SELECT value FROM orders_total WHERE orders_id = ? AND class = ? LIMIT 1
    SQL;
    public const GET_EXISTING_ACW_CUSTOMER_ID_BY_EMAIL = <<<SQL
        SELECT customers_id FROM customers WHERE stores_id = 16 AND customers_email_address = ?
    SQL;
    public const GET_EXISTING_CUSTOMER_ID_BY_EMAIL = <<<SQL
        SELECT customers_id FROM customers WHERE stores_id IS NULL AND customers_email_address = ?
    SQL;
    public const GET_ORDERS_STORE = <<<SQL
        SELECT orders_stores_id FROM orders WHERE orders_id = ?
    SQL;

    public function __construct(
        Store $store,
        Configuration $config,
        Breadcrumb $breadcrumb,
        Database $db,
        Request $request,
        Router $router
    ) {
        parent::__construct($store, $config, $breadcrumb, $db, $request, $router);
    }

    public function handleGuestCheckout(Order $order): void
    {
        $existingCustInfo  = [];
        $guestCheckoutInfo = [];

        $cybsSubsId     = $order->getCyberSourceSubsId();
        $orderCustId    = $order->getCustomerId();
        $orderCustEmail = $order->getCustomerEmail();
        $orderId        = $order->getId();

        //Get Guest checkout Info
        $guestCheckoutInfo = $this->db->getQueryData(
            self::GET_GUEST_CHECKOUT_INFO, 
            [$orderCustId], 
            PDO::FETCH_ASSOC
        );
        if (count($guestCheckoutInfo) > 0) {
            $customerGuestCheckout = $guestCheckoutInfo[0]['guest_checkout'];
        }

        if (($order->getGuestCheckoutStatus() == 1) && (($customerGuestCheckout ?? null) == 1)) {
            //Remove Guest Checkout
            $this->db->execute(self::DELETE_GUEST_FROM_CUSTOMER_TABLE, [$orderCustId]);
            $this->db->execute(self::DELETE_GUEST_FROM_CUSTOMER_INFO_TABLE, [$orderCustId]);
            $this->db->execute(self::DELETE_GUEST_FROM_ADDRESS_BOOK_TABLE, [$orderCustId]);

            if ($this->store->getId() == 16) {
                $existingCustInfo = $this->db->getQueryData(
                    self::GET_EXISTING_ACW_CUSTOMER_ID_BY_EMAIL, 
                    [$orderCustEmail], 
                    PDO::FETCH_ASSOC
                );
            } else {
                $existingCustInfo = $this->db->getQueryData(
                    self::GET_EXISTING_CUSTOMER_ID_BY_EMAIL, 
                    [$orderCustEmail], 
                    PDO::FETCH_ASSOC
                );
            }

            if (($existingCustInfo['customers_id'] ?? 0) > 0) {
                $this->db->execute(
                    self::UPDATE_ORDERS_CUSTOMER_ID, 
                    [$orderCustId, $orderId]
                );
                if (!empty($cybsSubsId)) {
                    $this->db->execute(
                        self::UPDATE_SUBSCRIPTIONS_CUSTOMER_ID, 
                        [$existingCustInfo['customer_id'], $cybsSubsId]
                    );
                }
            } else {
                $this->db->execute(self::UPDATE_ORDERS_CUSTOMER_ID, [0, $orderId]);
            }

            //Reset Session variables
            $sessionValueKeys = [
                'customer_id',
                'customer_default_address_id',
                'customer_first_name',
                'customer_country_id',
                'customer_zone_id',
                'comments',
                'apply_ferg_discount',
            ];
            foreach ($sessionValueKeys as $key) {
                $this->request->unsetSession($key);
            }

            $sessionValueKeys = [
                'flanders_code', 
                'flanders_code_amount', 
                'runtal_code', 
                'runtal_code_amount', 
                'removal_discount', 
                'free_shipping_checkout', 
                'auth_code', 
                'auth_file'
            ];
            foreach ($sessionValueKeys as $key) {
                $sessionValue = $this->getSessionValue($key);
                if (!empty($sessionValue)) {
                    $this->request->unsetSession($key);
                }
            }
        }

        //Reset additional session variables
        $sessionValueKeys = [
            'customer_id',
            'subscription',
            'emp_discount_amount',
            'products_id_discount',
            'pickup_location',
            Cart::SESSION_KEY,
        ];
        foreach ($sessionValueKeys as $key) {
            $this->request->unsetSession($key);
        }
    }

    public function getLpuBranchInfo(int $branch_number): array
    {
        $data = [];
        $data = $this->db->getQueryRow(
            self::GET_LPU_BRANCH_INFO, 
            [$branch_number], 
            PDO::FETCH_ASSOC
        );
        return $data;
    }

    public function getProductName(array $item): string
    {
        return (!empty($item['products_alt_name']) ? $item['products_alt_name'] : $item['name']);
    }

    public function getProductModel(array $item): string
    {
        return (!empty($item['products_model_alt']) ? $item['products_model_alt'] : $item['model']);
    }

    public function getProductImgInfo(array $item): array
    {
        $imgInfo                     = [];
        $storesUrl                   = $this->store->getUrl();
        $product_name                = $this->getProductName($item);
        $product_model               = $this->getProductModel($item);
        $useProInstallConsultDisplay = $product_model == 'MHVC-PHN-CNSLT';

        //Check for Bad Images
        if ($this->store->getId() == 16) {
            if ($useProInstallConsultDisplay) {
                $product_image = 'https://www.acwholesalers.com/images/icons/icon-acw-is-wrench.svg' . $item['products_bimage'];
                $img_alt_text  = $product_name;
            } elseif (empty($item['products_bimage'])) {
                $product_image = $storesUrl . '/products-image/110/product_0_125.gif';
                $img_alt_text  = $product_name . ' image not available';
            } else {
                $product_image = 'https://www.acwholesalers.com/products-image/208/' . $item['products_bimage'];
                $img_alt_text  = $product_name;
            }
        } else {
            if (empty($item['products_bimage'])) {
                $product_image = $storesUrl . '/products-image/110/product_0_125.gif';
                $img_alt_text  = $product_name . ' image not available';
            } else {
                $product_image = $storesUrl . '/products-image/110/' . $item['products_bimage'];
                $img_alt_text  = $product_name;
            }
        }
        $imgInfo['product_image'] = $product_image;
        $imgInfo['img_alt_text']  = $img_alt_text;

        return $imgInfo;
    }

    public function getProductLink(array $item): string
    {
        $storesUrl    = $this->store->getUrl();
        $product_link = (!empty($item['products_url']) ? $item['products_url'] : $storesUrl . '/product_info.php?products_id=' . (int) $item['id']);
        return $product_link;
    }

    public function getProductPriceInfo(array $item, $calcUnitPrice = false): array
    {
        $priceInfo = [];
        if ($calcUnitPrice) {
            if (($item['quantity'] ?? 0) > 1) {
                $final_price = number_format(($item['final_price'] * $item['quantity']), 2, '.', ',');
                $unit_price  = number_format($item['final_price'], 2, '.', ',');
            } else {
                $final_price = number_format($item['final_price'], 2, '.', ',');
                $unit_price  = '';
            }
            $priceInfo['price']      = $final_price;
            $priceInfo['unit_price'] = $unit_price;
        } else {
            $priceInfo['price'] = number_format($item['products_price'], 2, '.', ',');
        }
        return $priceInfo;
    }

    public function getKitMasterProductId(string $model): int
    {
        $data = [];
        $data = $this->db->getQueryRow(
            self::GET_KIT_MASTER_PRODUCT_ID, 
            [':model' => $model, 
            PDO::FETCH_ASSOC]
        );

        $result = 0;
        if (count($data) > 0) {
            $result = $data[0];
        }
        return $result;
    }

    public function buildKitData(array $prodsWithKits, array $kitComponents): array
    {
        $kitData = [];

        for ($i = 0; $i < count($prodsWithKits); $i++) {
            $kitData[$prodsWithKits[$i]]['info']       = $this->db->getQueryRow(
                self::GET_KIT_MASTER_PRODUCT_INFO, 
                [':model' => $prodsWithKits[$i]], 
                PDO::FETCH_ASSOC
            );
            $kitData[$prodsWithKits[$i]]['components'] = [];
        }

        for ($i = 0; $i < count($kitComponents); $i++) {
            $targModel = $kitComponents[$i]['is_kit'];
            array_push($kitData[$targModel]['components'], $kitComponents[$i]);
        }
        return $kitData;
    }

    public function getPedCashInfo(): array
    {
        $pedCashInfo = [];
        if ($this->config->get('DIRECT_DOLLARS_ACTIVE') == 'true') {
            $amount = '$' . number_format(ceil($this->getPEDCashTotal()), 2, '.', ',');

            if ($this->store->getId() == 16) {
                $iconClass = 'icon-acw-comfort-cash';
            } elseif ($this->store->getId() == 15) {
                $iconClass = 'icon-nav-usp-comfort-cash';
            } else {
                $iconClass = 'icon-nav-usp-direct-dollars';
            }

            $pedCashInfo = [
                'name'      => $this->config->get('DIRECT_DOLLARS_NAME'),
                'amount'    => $amount,
                'iconClass' => $iconClass
            ];
        }
        return $pedCashInfo;
    }

    public function getPaymentTypeDisplayNames(string $payment_method): string
    {
        if (
            $payment_method == 'btPaypal' 
            || $payment_method == 'Paypal Direct' 
            || $payment_method == 'Paypal Express'
        ) 
        {
            $payment_method = 'Paypal';
        }
        if ($payment_method == 'CyberSource') {
            $payment_method = 'Credit Card';
        }
        if ($payment_method == 'HeatPumpStore') {
            $payment_method = 'Heat Pump Store';
        }
        if ($payment_method == 'shopatron') {
            $payment_method = 'Shopatron';
        }
        if ($payment_method == 'Synchrony Financial') {
            $payment_method = 'Synchrony';
        }
        if ($payment_method == 'Check') {
            $payment_method = 'Paper Check';
        }
        return $payment_method;
    }

    public function getRebateInfo(int $products_id): array
    {
        $rebateInfo = [];
        $params     = [
            $products_id,
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s")
        ];

        if ($this->store->getId() == 16) {
            $bonusData = $this->db->getQueryData(
                self::GET_REBATE_INFO_ACW, 
                $params, 
                PDO::FETCH_ASSOC
            );
        } else {
            $bonusData = $this->db->getQueryData(
                self::GET_REBATE_INFO, 
                $params, 
                PDO::FETCH_ASSOC
            );
        }

        foreach ($bonusData as $bonus) {
            if ($bonus['bonus_text'] != '') {
                $rebateInfo['bonus_text']  = $bonus['bonus_text'];
                $rebateInfo['end_date']    = date("m/d", strtotime($bonus['end_date']));
                $rebateInfo['bonus_url']   = $bonus['bonus_url'];
                $rebateInfo['rebate_form'] = $bonus['rebate_form'];
            }
        }
        return $rebateInfo;
    }

    public function getBonusItemInfoById(int $products_id): array
    {
        // Get Bonus Items associated with specific product_id
        $bonusItemData = [];
        $bonusItems    = [];

        $params = [
            $products_id,
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s"),
            $this->store->getId()
        ];
        $bonusItems = $this->db->getQueryRow(self::GET_BONUS_ITEMS, $params, PDO::FETCH_ASSOC);

        if (!empty($bonusItems['bonus_item_1'])) {
            $bonusItemData[0]                   = $this->db->getQueryRow(self::GET_BONUS_ITEM_DATA, [$bonusItems['bonus_item_1']], PDO::FETCH_ASSOC);
            $bonusItemData[0]['bonus_item_qty'] = (!empty($bonusItems['qty_1']) ? $bonusItems['qty_1'] : 0);
            $bonusItemData[0]['bonus_text']     = $bonusItems['bonus_text'];
        }
        if (!empty($bonusItems['bonus_item_2'])) {
            $bonusItemData[1]                   = $this->db->getQueryRow(self::GET_BONUS_ITEM_DATA, [$bonusItems['bonus_item_2']], PDO::FETCH_ASSOC);
            $bonusItemData[1]['bonus_item_qty'] = (!empty($bonusItems['qty_2']) ? $bonusItems['qty_2'] : 0);
            $bonusItemData[1]['bonus_text']     = $bonusItems['bonus_text'];
        }
        return $bonusItemData;
    }

    public function setBonusItemIdList(array $orderProducts): void
    {
        foreach ($orderProducts as $r) {
            //Get Top Level Kit Item
            if (!empty($r['is_kit'])) {
                $kit_id = $this->getKitMasterProductId($r['is_kit']);
                if ($kit_id != 0) {
                    $r['id'] = $kit_id;
                }
            }

            if (!is_null($r['id'])) {
                //Get Bonus Items associated with order product
                //Add data to array containing all bonus item info
                $orderProductBonusItems = $this->getBonusItemInfoById($r['id']);
            } else {
                error_log('CONFIRMATION ERROR');
                error_log(print_r($orderProducts, true));
            }

            foreach ($orderProductBonusItems as $row) {
                $this->bonusItemList[$row['id']] = $row;
            }
        }
    }

    public function getBonusItemList(): array
    {
        return $this->bonusItemList ?? [];
    }

    public function purgeBonusItemOrderProducts(array $orderProducts): array
    {
        $bonusItemList = $this->getBonusItemList();
        $count         = 0;
        //Loop through all orderProducts
        foreach ($orderProducts as $r) {
            $unset = false;
            if ($r['final_price'] == '0.0000' && empty($r['coupons_code'])) {  //Purge if it is free and no coupon code
                $unset = true;
            } else {  //Loop through bonusItemList..if id is in bonusItemList, purge
                foreach ($bonusItemList as $item) {
                    if ($item['id'] == $r['id']) {
                        $unset = true;
                    }
                }
            }

            if ($unset) {
                unset($orderProducts[$count]);
            }
            $count++;
        }
        return array_values($orderProducts);
    }

    public function getXsellItems(array $products): array
    {
        $items = [];
        array_multisort(array_column($products, 'price'), SORT_DESC, $products);
        $xsell_id = $products[0]['id'];
        $items    = $this->db->getQueryData(self::GET_XSELL_ITEMS, [$xsell_id], PDO::FETCH_ASSOC);
        return $items;
    }

    public function getLpuShippingMessage(array $params): string
    {
        $output = '';
        if (!empty($params['branch_name'])) {
            $output .= '<div>' . $params['branch_name'] . '</div>';
        }
        $output .= '<div>' . $params['branch_address'] . ',</div>';
        $output .= '<div>' . $params['branch_city'] . ', ' . $params['branch_state'] . ' ' . $params['branch_zip'] . '</div>';

        return $output;
    }

    public function getShippingStatusMessage(array $params): Data
    {
        $statusMsg         = "";
        $exclueStockStatus = ['Product Discontinued', 'Sold Out'];
        $product           = Product::getInstance($params['prodId'], $this->store->getId(), $this->db);

        $stockData = $product->getLegacyStockData();

        $contents = new Collection();

        if (!empty($stockData['stock']->factoryMessage)) {
            $contents->append(new Data('/tags/division.php', [
                'id'      => 'factoryMessage',
                'content' => $stockData['stock']->factoryMessage
            ]));
        } else {
            if (!empty($params['estShipDate']) && !in_array($stockData['stock']->text, $exclueStockStatus)) {
                $contents->append(new Data('/tags/division.php', [
                    'id'      => 'shipDate',
                    'content' => 'Est. Ship Date ' . $params['estShipDate']
                ]));
            }

            if (!in_array($stockData['stock']->text, $exclueStockStatus)) {
                $contents->append(new Data('/tags/division.php', [
                    'id'      => $stockData['stock']->id,
                    'content' => $stockData['stock']->text
                ]));
            }

            $statusMsg = new Data('/tags/division.php', [
                'class'   => (!empty($stockData['stock']->class) ? implode(' ', $stockData['stock']->class) : ''),
                'content' => new Data('/tags/division.php', [
                    'content' => $contents
                ])
            ]);
        }
        return $statusMsg;
    }

    public function getWarrantyInfo(string $products_model): array
    {
        $warrantyInfo    = [];
        $warranties      = $this->getWarranties();
        $model_no_dashes = str_replace('-', '', $products_model);

        foreach ($warranties as $w) {
            $w_name = (!empty($w['products_alt_name']) ? $w['products_alt_name'] : $w['name']);

            $match = strpos($w_name, $products_model);
            if (!$match) {
                $match = strpos($w_name, $model_no_dashes);
            }

            if ($match !== false) {
                $name_arr             = explode('--', $w_name);
                $warrantyInfo['text'] = $name_arr[0];

                $w_price               = (!empty($w['final_price']) ? $w['final_price'] : $w['price']);
                $warrantyInfo['price'] = number_format($w_price, 2, '.', ',');

                $warrantyInfo['qty'] = $w['qty'];
            }
        }
        return $warrantyInfo;
    }

    public function getInstallerInfo(int $products_id): array
    {
        $installInfo       = [];
        $installerProducts = $this->getInstallerProducts();

        $skuList = $this->db->getQueryData(self::GET_INSTALLER_PACKAGE_SKU_LIST, [$products_id], PDO::FETCH_ASSOC);
        foreach ($skuList as $sku) {
            foreach ($installerProducts as $install) {
                if ($sku['sku'] == $install['id']) {
                    $price                = (!empty($install['final_price']) ? $install['final_price'] : $install['price']);
                    $installInfo['price'] = number_format($price, 2, '.', ',');
                }
            }
        }
        return $installInfo;
    }

    public function getRowViewParams(string $viewType, array $data = []): array
    {
        $viewParams = [];
        switch ($viewType) {
            case 'warranty':
                $viewParams = [
                    'mainClass'   => 'warranty',
                    'iconClass'   => 'icon-warranty-shield',
                    'col1Content' => '',
                    'col2Header'  => 'Protection Plan',
                    'col2Msg'     => 'Guardsman ' . $data['text'],
                    'col3Content' => '<span class="qty-label">Qty&nbsp;</span>' . $data['qty'],
                    'col4Content' => '$' . $data['price']
                ];
                break;
            case 'rebate':
                $formLink = '';
                if ($data['rebate_form']) {
                    $formLink = ' <a href="https://www.powerequipmentdirect.com/rebates/' . $data['rebate_form'] . '" title="Download Rebate Form" target="_blank">View Form</a>';
                }
                $viewParams = [
                    'mainClass'   => 'rebate',
                    'iconClass'   => 'icon-tag',
                    'col1Content' => '',
                    'col2Header'  => 'Rebate Available',
                    'col2Msg'     => $data['bonus_text'] . $formLink,
                    'col3Content' => '',
                    'col4Content' => ''
                ];
                break;
            case 'subscription':
                if ($this->config->get('SUBSCRIPTION_STATUS') == 'true' && (($data['subscriptionProduct'] ?? null) == 'yes')) {
                    $viewParams = [
                        'mainClass'   => 'subscriptions',
                        'iconClass'   => 'filter-subs-icon',
                        'col1Content' => '<img height="35" width="35" src="https://www.powerequipmentdirect.com/images/icons/icon-sub-green.svg" alt="Subscriptions Icon">',
                        'col2Header'  => 'Filter Subscription',
                        'col2Msg'     => 'You will receive ' . $data['subQuantity'] . ' filter' . ($data['subQuantity'] != 1 ? 's' : '') . ' every ' . ($data['subFrequency'] > 1 ? $data['subFrequency'] : '') . ' month' . ($data['subFrequency'] != 1 ? 's' : ''),
                        'col3Content' => '',
                        'col4Content' => ''
                    ];
                }
                break;
            case 'installer':
                $installationProgramName = $this->config->get('INSTALLATION_PROGRAM_NAME');
                if (empty($installationProgramName)) {
                    $installationProgramName = 'Installation';
                }
                $viewParams = [
                    'mainClass'   => 'installation',
                    'iconClass'   => 'icon-wrench',
                    'col1Content' => '',
                    'col2Header'  => $installationProgramName,
                    'col2Msg'     => 'Includes Labor, Materials, and Removal',
                    'col3Content' => '',
                    'col4Content' => '$' . $data['price']
                ];
                break;
            case 'bonusItem':
                $imgInfo    = $this->getProductImgInfo($data);
                $viewParams = [
                    'itemLink'       => $this->getProductLink($data),
                    'itemImg'        => $imgInfo['product_image'],
                    'itemImgAltText' => $imgInfo['img_alt_text'],
                    'itemName'       => html_entity_decode($this->getProductName($data)),
                    'itemModel'      => $this->getProductModel($data)
                ];
                break;
                break;
        }
        return $viewParams;
    }

    public function getProductRowViewParams(array $item): array
    {
        $imgInfo   = $this->getProductImgInfo($item);
        $priceInfo = $this->getProductPriceInfo($item, true);

        $viewParams = [
            'itemImg'        => $imgInfo['product_image'],
            'itemImgAltText' => $imgInfo['img_alt_text'],
            'itemName'       => html_entity_decode($this->getProductName($item)),
            'itemModel'      => $this->getProductModel($item),
            'itemLink'       => $this->getProductLink($item),
            'qty'            => ($item['quantity'] ?? 0),
            'itemPrice'      => '$' . $priceInfo['price'],
            'unitPrice'      => $priceInfo['unit_price'],
        ];
        return $viewParams;
    }

    public function getOrderSummaryViewParams(Order $order, string $uuid): array
    {
        $showShippingContactInfo = false;
        $showPickupContactInfo   = false;

        $products            = $this->getShippedProductList();
        $pickups             = $this->getPickupsList();
        $productsWithKits    = $this->getProductsWithKits();
        $lpuProductsWithKits = $this->getLpuProductsWithKits();

        if (count($products) > 0 || count($productsWithKits) > 0) {
            $showShippingContactInfo = true;
        }
        if (count($pickups) > 0 || count($lpuProductsWithKits) > 0) {
            $showPickupContactInfo = true;
            if ($this->getHasSubsValue()) {
                $showShippingContactInfo = true;
            }
        }

        $orderInfo['orderNumber']    = $order->getId();
        $orderInfo['shippingMethod'] = $order->getShipMethod();
        $orderInfo['paymentType']    = $this->getPaymentTypeDisplayNames($order->getPaymentMethod() ?? '');

        $viewParams = [
            'customerEmail'           => $order->getCustomerEmail(),
            'storePhone'              => $this->config->get('STORE_PHONE'),
            'orderInfo'               => $orderInfo,
            'lpuInfo'                 => $this->getLpuContactParams($order->delivery),
            'shippingInfo'            => $this->getShippingParams($order->delivery),
            'showLpuContactInfo'      => $showPickupContactInfo,
            'showShippingContactInfo' => $showShippingContactInfo,
            'viewReceiptLink'         => $this->getViewReceiptLink($order, $uuid)
        ];
        return $viewParams;
    }

    public function getSectionViewParams(string $viewType, array $data = []): array
    {
        $viewParams = [];
        switch ($viewType) {
            case 'kitProducts':
                $viewParams = [
                    'products'        => $this->getKitProductList(),
                    'totalComponents' => $this->getTotalKitComponents(),
                    'kitCount'        => $this->getKitCount()
                ];
                break;
            case 'xsell':
                $imgInfo    = $this->getProductImgInfo($data);
                $priceInfo  = $this->getProductPriceInfo($data);
                $viewParams = [
                    'itemImg'        => $imgInfo['product_image'],
                    'itemImgAltText' => $imgInfo['img_alt_text'],
                    'itemName'       => html_entity_decode($this->getProductName($data)),
                    'itemModel'      => $this->getProductModel($data),
                    'itemLink'       => $this->getProductLink($data),
                    'itemPrice'      => '$' . $priceInfo['price']
                ];
                break;
            case 'itemListHeader':
                $viewParams = [
                    'iconClass'   => 'icon-standard-ground',
                    'colContent1' => '',
                    'colContent2' => 'Shipping Items',
                    'colContent3' => 'Est. Ship Date',
                    'colContent4' => 'Qty',
                    'colContent5' => 'Price'
                ];
                break;
            case 'lpuItemListHeader':
                $viewParams = [
                    'iconClass'   => 'icon-local-pickup',
                    'colContent1' => '',
                    'colContent2' => 'Local Pickup Items',
                    'colContent3' => 'Pickup Location',
                    'colContent4' => 'Qty',
                    'colContent5' => 'Price'
                ];
                break;
            case 'cashEarned':
                $viewParams = [
                    'iconClass'   => $data['iconClass'],
                    'col1Content' => '',
                    'cashTotal'   => $data['amount'],
                    'cashType'    => $data['name']
                ];
                break;
            case 'orderTotals':
                $viewParams = [
                    'subTotal'   => $data['sub_total'],
                    'discounts'  => $data['discounts'] ?? [],
                    'charges'    => $data['charges'],
                    'finalTotal' => $data['final_total']
                ];
                break;
            default:
                break;
        }
        return $viewParams;
    }

    public function groupOrderProductTypes(array $orderProducts): void
    {
        $installCheck        = false;
        $products            = [];
        $pickups             = [];
        $productsWithKits    = [];
        $lpuProductsWithKits = [];
        $kitComponents       = [];
        $lpuKitComponents    = [];
        $installerProducts   = [];
        $warranties          = [];
        $PEDCashTotal        = 0;

        //Throw the products in arrays for Shipped Products, LPU Products, Warranties and Installers
        foreach ($orderProducts as $r) {
            $currentModel = (!empty($r['products_model_alt']) ? $r['products_model_alt'] : $r['model']);
            $installCheck = strpos($currentModel, 'MHVC-');
            if ($installCheck === false) {
                $hasInstall = false;
            } else {
                $hasInstall = true;
            }

            if ($hasInstall) {
                $installerProducts[] = $r;
            } elseif ($r['id'] == 10342) {
                $warranties[] = $r;
            } else {
                if ($r['scac_code'] == 'LPU' && (!empty($r['dc_number']) || $r['dc_number'] == 0)) {
                    $pickups[] = $r;

                    if (!empty($r['is_kit'])) {
                        $lpuProductsWithKits[] = $r['is_kit'];
                        $lpuKitComponents[]    = $r;
                    }
                } else {
                    $products[] = $r;

                    if (!empty($r['is_kit'])) {
                        $productsWithKits[] = $r['is_kit'];
                        $kitComponents[]    = $r;
                    }
                }
            }

            if (!empty($r['ped_cash_accumulated'])) {
                $PEDCashTotal += $r['ped_cash_accumulated'];
            }
        }

        //Create Separate array for kit products so we can display the master product of each kit
        $productsWithKits    = array_values(array_unique($productsWithKits));
        $lpuProductsWithKits = array_values(array_unique($lpuProductsWithKits));

        $kitData    = $this->buildKitData($productsWithKits, $kitComponents);
        $lpuKitData = $this->buildKitData($lpuProductsWithKits, $lpuKitComponents);

        $this->setShippedProductList($products);
        $this->setPickupsList($pickups);
        $this->setProductsWithKits($productsWithKits);
        $this->setLpuProductsWithKits($lpuProductsWithKits);
        $this->setKitData($kitData);
        $this->setLpuKitData($lpuKitData);
        $this->setWarranties($warranties);
        $this->setInstallerProducts($installerProducts);
        $this->setPEDCashTotal($PEDCashTotal);
    }

    public function getShippedProductList(): array
    {
        return $this->products ?? [];
    }

    public function setShippedProductList(array $data): void
    {
        $this->products = $data;
    }

    public function getPickupsList(): array
    {
        return $this->pickups ?? [];
    }

    public function setPickupsList(array $data): void
    {
        $this->pickups = $data;
    }

    public function getProductsWithKits(): array
    {
        return $this->productsWithKits ?? [];
    }

    public function setProductsWithKits(array $data): void
    {
        $this->productsWithKits = $data;
    }

    public function getLpuProductsWithKits(): array
    {
        return $this->lpuProductsWithKits ?? [];
    }

    public function setLpuProductsWithKits(array $data): void
    {
        $this->lpuProductsWithKits = $data;
    }

    public function getLpuKitData(): array
    {
        return $this->lpuKitData ?? [];
    }

    public function setLpuKitData(array $data): void
    {
        $this->lpuKitData = $data;
    }

    public function getKitData(): array
    {
        return $this->kitData ?? [];
    }

    public function setKitData(array $data): void
    {
        $this->kitData = $data;
    }

    public function getWarranties(): array
    {
        return $this->warranties ?? [];
    }

    public function setWarranties(array $data): void
    {
        $this->warranties = $data;
    }

    public function getInstallerProducts(): array
    {
        return $this->installerProducts ?? [];
    }

    public function setInstallerProducts(array $data): void
    {
        $this->installerProducts = $data;
    }

    public function getPEDCashTotal()
    {
        return $this->PEDCashTotal ?? 0;
    }

    public function setPEDCashTotal($value)
    {
        $this->PEDCashTotal = $value;
    }

    public function getShippingParams(array $delivery): array
    {
        $shippingParams = [];
        if (!empty($delivery['name'])) {
            $shippingParams['customerName'] = $delivery['name'];
        }
        if (!empty($delivery['street_address'])) {
            $shippingParams['address'] = $delivery['street_address'] . (!empty($delivery['suburb']) ? ' ' . $delivery['suburb'] : '');
        }
        if (!empty($delivery['city'])) {
            $shippingParams['city'] = $delivery['city'];
        }
        if (!empty($delivery['state'])) {
            $shippingParams['state'] = $delivery['state'];
        }
        if (!empty($delivery['postcode'])) {
            $shippingParams['zipcode'] = $delivery['postcode'];
        }
        return $shippingParams;
    }

    public function getLpuContactParams(array $delivery): array
    {
        $lpuContactParams = [];
        if (!empty($delivery['lpu_name'])) {
            $lpuContactParams['pickupName'] = $delivery['lpu_name'];
        }
        if (!empty($delivery['lpu_email_address'])) {
            $lpuContactParams['pickupEmail'] = $delivery['lpu_email_address'];
        }
        if (!empty($delivery['lpu_telephone'])) {
            $lpuContactParams['pickupPhone'] = CustomerName::format_phone($delivery['lpu_telephone'], true);
        }
        return $lpuContactParams;
    }

    public function getOrderTotalsInfo(array $orderTotals): array
    {
        $totalsInfo = [];
        foreach ($orderTotals as $total) {
            $totalClass = $total['class'];

            switch ($totalClass) {
                case Total::CLASS_SUBTOTAL:
                    $totalsInfo['sub_total']['title'] = $total['title'];
                    $totalsInfo['sub_total']['text']  = $total['text'];
                    break;
                case Total::CLASS_DISCOUNT:
                    $totalsInfo['discounts'][] = $total;
                    break;
                case Total::CLASS_SHIPPING:
                    $totalsInfo['charges'][] = $total;
                    break;
                case Total::CLASS_TAX:
                    $totalsInfo['charges'][] = $total;
                    break;
                case Total::CLASS_TOTAL:
                    $totalsInfo['final_total']['title'] = $total['title'];
                    $totalsInfo['final_total']['text']  = strip_tags($total['text']);
                    break;
                case 'ot_remoteareafee':
                    $totalsInfo['charges'][] = $total;
                    break;
                case Total::CLASS_REGIONAL:
                    $totalsInfo['charges'][] = $total;
                    break;
                default:
                    break;
            }
        }
        return $totalsInfo;
    }

    public function buildKitComponentData(array $components): void
    {
        $kitProductListData  = [];
        $totalComponents     = 0;
        $totalKitPrice       = 0;
        $totalComponentPrice = 0;

        $this->resetKitProductList();

        foreach ($components as $comp) {
            $imgInfo = $this->getProductImgInfo($comp);

            $kitProductListData = [
                'prodImg'   => $imgInfo['product_image'],
                'prodName'  => html_entity_decode($this->getProductName($comp)),
                'prodModel' => $this->getProductModel($comp),
                'prodLink'  => $this->getProductLink($comp),
                'qty'       => $comp['quantity']
            ];

            $this->updateKitProductList($kitProductListData);

            $totalComponents += $comp['quantity'];
            $totalComponentPrice = $comp['price'] * $comp['quantity'];
            $totalKitPrice += $totalComponentPrice;
        }

        $this->setTotalKitComponents($totalComponents);
        $this->updateTotalKitPrice($totalKitPrice);
        $this->incrementKitCount();
    }

    public function getOrderTotalValue(array $params): array
    {
        $value = $this->db->getQueryRow(
            self::GET_ORDERS_TOTAL_VALUE, 
            $params, 
            PDO::FETCH_ASSOC
        );
        return $value;
    }

    public function populateBreadcrumb(): void
    {
        $this->breadcrumb->add(
            'Order Confirmation', 
            'shopping/cart/checkout/success'
        );
    }

    public function getOrder(int $orderId): ?Order
    {
        return Order::getInstance($orderId) ?? null;
    }

    public function getOrderId(string $uuid): ?string
    {
        return $this->request->getSession($uuid) ?? '';
    }

    public function isRequestValid(?string $uuid): bool
    {
        if (Uuid::isValid($uuid ?? '')) {
            $orderId = $this->getOrderId($uuid);

            if (intval($this->db->getQueryResult(
                self::GET_ORDERS_STORE, [$orderId])) === $this->store->getId()
            ) {
                $valid = true;
            } else {
                $valid = false;
            }
        } else {
            $valid = false;
        }
        return $valid;
    }

    public function getBonusItemMsg(int $qty): string
    {
        if ($qty > 1) {
            $bonusItemMsg = 'Bonus Items';
        } elseif ($qty == 1) {
            $bonusItemMsg = 'Bonus Item';
        } else {
            $bonusItemMsg = 'Bonus Item(s)';
        }
        return $bonusItemMsg;
    }

    public function getHasSubsValue(): bool
    {
        return $this->hasSubs ?? false;
    }

    public function setHasSubsValue(bool $value): void
    {
        $this->hasSubs = $value;
    }

    public function getUserScriptData(Order $order): array
    {
        $deliveryDates     = [];
        $orderDeliveryDate = '';

        foreach ($order->products as $r) {
            if (!empty($r['estimated_delivery_date'])) {
                $deliveryDates[] = $r['estimated_delivery_date'];
            }
        }
        if (count($deliveryDates) > 0) {
            $orderDeliveryDate = $deliveryDates[0];
        }

        $userScriptDataInputs   = [];
        $userScriptDataInputs[] = [
            'id' => 'data-order-id', 
            'value' => $order->getId()
        ];
        $userScriptDataInputs[] = [
            'id' => 'data-customer-email', 
            'value' => $order->getCustomerEmail()
        ];
        $userScriptDataInputs[] = [
            'id' => 'data-stores-id', 
            'value' => $this->store->getId()
        ];
        $userScriptDataInputs[] = [
            'id' => 'data-google-merchant-id', 
            'value' => $this->config->get('GOOGLE_MERCHANT_ID')
        ];
        $userScriptDataInputs[] = [
            'id' => 'data-order-delivery-date', 
            'value' => $orderDeliveryDate
        ];

        if ($this->store->getId() == 15) {
            $google_shipping_total  = $this->getOrderTotalValue(
                [$order->getId(), 'ot_shipping']
            );
            $userScriptDataInputs[] = [
                'id' => 'data-google-shipping-total', 
                'value' => number_format($google_shipping_total['value'], 2, '.', '')
            ];

            $google_tax_total       = $this->getOrderTotalValue(
                [$order->getId(), 'ot_tax']
            );
            $userScriptDataInputs[] = [
                'id' => 'data-google-tax-total', 
                'value' => number_format($google_tax_total['value'], 2, '.', '')
            ];

            $google_discount_total  = $this->getOrderTotalValue(
                [$order->getId(), 'ot_checkdiscount']
            );
            $userScriptDataInputs[] = [
                'id' => 'data-google-discount-total', 
                'value' => number_format($google_discount_total['value'], 2, '.', '')
            ];

            $google_order_total     = $this->getOrderTotalValue(
                [$order->getId(), 'ot_total']
            );
            $userScriptDataInputs[] = [
                'id' => 'data-google-order-total', 
                'value' => number_format($google_order_total['value'], 2, '.', '')
            ];

            $userScriptDataInputs[] = [
                'id' => 'data-google-payment-type', 
                'value' => $order->getPaymentMethod()
            ];
        }
        return $userScriptDataInputs;
    }

    public function getViewReceiptLink(Order $order, string $uuid): string
    {
        $viewReceiptLink = '/printorder.php?' . http_build_query([
            'order_id' => $order->getId(),
            'zip'      => $order->getCustomerZip(),
            'email'    => hash('sha256', $order->getCustomerEmail()),
            'uuid'     => $uuid,
        ]);
        return $viewReceiptLink;
    }

    public function getOrderStoreId(): int
    {
        return $this->store->getId();
    }

    public function getOrderStoreName(): string
    {
        return $this->store->getName();
    }

    public function getTotalKitPrice()
    {
        return $this->totalKitPrice ?? 0;
    }

    public function updateTotalKitPrice($newPrice)
    {
        $this->totalKitPrice += $newPrice;
    }

    public function resetTotalKitPrice(): void
    {
        $this->totalKitPrice = 0;
    }

    public function getKitCount(): int
    {
        return $this->kitCount ?? 0;
    }

    public function incrementKitCount(): void
    {
        $this->kitCount++;
    }

    public function getTotalKitComponents(): int
    {
        return $this->totalComponents ?? 0;
    }

    public function setTotalKitComponents(int $new): void
    {
        $this->totalComponents = $new;
    }

    public function getKitProductList(): array
    {
        return $this->kitProductList ?? [];
    }

    public function updateKitProductList(array $data): void
    {
        array_push($this->kitProductList, $data);
    }

    public function resetKitProductList(): void
    {
        $this->kitProductList = [];
    }

    public function isNewCart(): bool
    {
        return $this->config->get('NEW_CART_CHECKOUT_TOGGLE') === 'true';
    }
}