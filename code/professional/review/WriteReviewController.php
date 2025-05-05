<?php
namespace Pedstores\Ped\Controllers\WriteAReview;

use Pedstores\Ped\Models\WriteAReview\WriteAReview;
use Pedstores\Ped\Views\PhpTemplateView;
use Pedstores\Ped\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class WriteAReviewController
{
    private $model;
    private $view;
    private $manager;

    public function __construct(
        WriteAReview $model,
        PhpTemplateView $view,
        CsrfTokenManager $manager
    ) {
        $this->model   = $model;
        $this->view    = $view;
        $this->manager = $manager;
    }

    public function execute(): void
    {
        $data = $this->model->getPageData();

        switch ($data['action']) {
            case 'process':
                $this->actionProcess($data);
                break;
            case 'loadmodalview':
                $this->loadModalView($data);
                break;
            case 'getimage':
                echo $this->actionGetImage($data);
                break;
            case 'uploadImage':
                $this->model->uploadImage();
                break;
            default:
                break;
        }
    }

    private function actionProcess(array $data): void
    {
        //This is the primary function that controls business logic such as updating the DB and sending emails
        //Also loads the Thank You modal view

        require_once '/var/www/vhosts/ped.com/includes/functions/email_template.php';

        //If passed Schema validation, check CSRF token next
        if (count($this->model->getExceptionList()) == 0) {
            //If CSRF Token valid continue processing page
            if (!$this->manager->validate($this->model::CSRF_TOKEN, $data['review_token'])) {
                $this->model->setErrorMsg('Your security token has expired.');
            } else {

                //Validate, Sanitize and Format Data
                $data = $this->model->prepareActionProcessData($data);

                if (empty($this->model->getErrorMsg())) {
                    //Insert data into reviews
                    $data['reviewsInsertId'] = $this->model->insertDataIntoReviews(
                        $this->model->getSqlParams(
                            'reviewsInsert',
                            $data
                        )
                    );

                    //Insert data into reviews description
                    $this->model->insertDataIntoReviewsDescription(
                        $this->model->getSqlParams(
                            'reviewsDescriptionInsert',
                            $data
                        )
                    );

                    //Insert data into installer reviews and send an installer email
                    if ($data['installerReview'] == 1 && !empty($data['installerOrderId'])) {
                        $this->performCategoryActions('installer', $data);
                    }

                    //Upload files that are allowed, if any, and set image names
                    $data = $this->model->uploadAllowedFiles($data);

                    if (empty($this->model->getFileErrorMsg())) {
                        if ($data['triggerStorageBucketUpload']) {
                            uploadImageToStorageBucket();
                            $this->performCategoryActions('imageUpload', $data);
                            $this->model->updateReviewDescriptionImagesData(
                                $this->model->getSqlParams(
                                    'reviewsImageUpdate',
                                    $data
                                )
                            );
                        }
                    }

                    //Subscribe the user to the email list
                    if ($data['subscribe']) {
                        $fullStateName = $this->model->getFullStateName(
                            $data['reviewerState']
                        );


                        if (!empty($data['reviewerFirstName'])
                            && !empty($data['reviewerEmail'])
                            && !empty($fullStateName)
                        ) {
                            //Insert data into newsletter list
                            $this->model->insertDataIntoNewsletterList(
                                $this->model->getSqlParams(
                                    'newsletterListInsert',
                                    $data,
                                    ['fullStateName' => $fullStateName]
                                )
                            );
                        }
                    }

                    //Send report emails to subscribed administrators
                    //Media Submissions
                    if ($data['imageCheck'] || $data['videoCheck']) {
                        $this->performCategoryActions('mediaSubmissions', $data);
                    }

                    if ($data['productRating'] <= 2) {
                        $this->performCategoryActions('lowProductRatings', $data);
                    }

                    //General Review
                    $this->performCategoryActions('generalReview', $data);
                }
            }
        }

        //Load the page
        $this->loadModalView($data);
    }

    private function loadModalView(array $data): void
    {
        //This function builds and displays the different modal views
        if (count($data) == 0) {
            $modalData = $this->model->handleEmptyData();
        } else {
            $data      = $this->model->handleErrors($data);
            $modalData = $this->model->getViewParams($data);
        }
        echo $this->view->render(
            '/product/review/modal/' . $modalData['templateView'] . '.php',
            $modalData
        );
    }

    private function actionGetImage(array $data): string
    {
        $pattern = "/[^A-Za-z0-9\-\/\ \(\)\'\"]/";
        $id      = !empty($data['id']) ? (int) $data['id'] : 0;

        if (empty($id)) {
            $this->executeBadDataResponse();
        }

        $info = [];
        $row  = $this->model->getProductData($data['action'], [$id]);

        if (!empty($row)) {
            $info[] = [
                'id'   => $row['products_id'],
                'name' => htmlspecialchars(
                    preg_replace(
                        $pattern,
                        '',
                        $row['products_name']
                    )
                ),
                'model' => htmlspecialchars(
                    preg_replace(
                        $pattern,
                        '',
                        $row['products_model']
                    )
                ),
                'image'    => $row['products_bimage'],
                'manimage' => $row['manufacturers_image'],
                'plink'    => '/product_info.php?products_id=' . $row['products_id']];
        }
        return json_encode($info);
    }


    private function performCategoryActions(string $category, array $data): void
    {
        $emailInfo = [
            'toEmail'   => '',
            'subject'   => '',
            'text'      => '',
            'fromName'  => '',
            'fromEmail' => ''
        ];

        switch ($category) {
            case 'installer':
                $star = function ($x) {
                    return $x > 1 ? ' Stars' : ' Star';
                };

                //Insert data into installer reviews
                $this->model->insertDataIntoInstallerReviews(
                    $this->model->getSqlParams('installerReviewsInsert', $data)
                );

                if ($data['installerSelected'] > 1) {
                    $installerName = $this->model->getInstallerName(
                        $data['installerSelected']
                    );
                } elseif ($data['installerSelected'] == 1) {
                    $installerName = $data['installerOtherName'];
                } else {
                    $installerName = 'Unknown';
                }

                $reportEmails = getReportEmails(25, $data['storeId']);
                if (!empty($reportEmails)) {
                    //Email the installer team
                    $email = $data['reviewerFirstName'] . ' just reviewed an installer.<br><br>';
                    $email .= 'Here are the details of their review.<br><br>';
                    $email .= 'Order Id: ';
                    $email .= '<a target="_blank" href="https://www.powerequipmentdirect.com/PEDadmin/orders.php?oID=' . $data['installerOrderId'] . '&action=edit">';
                    $email .= $data['installerOrderId'] . '</a><br>';
                    if (!empty($data['installerCustomersId'])) {
                        $email .= 'Customers Id: <a target="_blank" href="https://www.powerequipmentdirect.com/PEDadmin/customers.php?cID=';
                        $email .= $data['installerCustomersId'] . '&action=edit">' . $data['installerCustomersId'] . '</a><br>';
                    }
                    $email .= 'Review Id: ';
                    $email .= '<a target="_blank" href="https://www.powerequipmentdirect.com/PEDadmin/reviews.php?action=edit&rID=' . $data['reviewsInsertId'] . '">';
                    $email .= $data['reviewsInsertId'] . '</a><br><br>';
                    $email .= 'Installers Name: ';
                    if ($data['installerSelected'] > 1) {
                        $email .= '<a class="bluelink" target="installerInfo" href="https://www.powerequipmentdirect.com/PEDadmin/installers.php?cID=';
                        $email .= $data['installerSelected'] . '&action=edit">';
                        $email .= tep_db_input($installerName) . '</a><br>';
                    } else {
                        $email .= tep_db_input($installerName) . '<br>';
                    }
                    $email .= 'Overall Experience: ';
                    $email .= ($data['installerExperience'] >= 1 ? $data['installerExperience'] . $star($data['installerExperience']) : 'Not Rated') . '<br>';
                    $email .= 'Quality of Work: ';
                    $email .= ($data['installerWork'] >= 1 ? $data['installerWork'] . $star($data['installerWork']) : 'Not Rated') . '<br>';
                    $email .= 'Price: ';
                    $email .= ($data['installerPrice'] >= 1 ? $data['installerPrice'] . $star($data['installerPrice']) : 'Not Rated')  . '<br>';
                    $email .= 'Additional Comments: <i>' . tep_db_input($data['installerComment']) . '</i><br><br><br>';


                    $emailInfo['subject']   = 'Installer Review';
                    $emailInfo['text']      = tep_db_input($email);
                    $emailInfo['fromName']  = 'New Installer Review';
                    $emailInfo['fromEmail'] = 'reports-noreply@powerequipmentdirect.com';
                }
                break;
            case 'generalReview':
                $reportEmails = getReportEmails(26, $data['storeId']);
                if (!empty($reportEmails)) {
                    $email = "\n\nProducts Reviewed\n";
                    if (!empty($data['pId'])) {
                        $email .= tep_get_products_name($data['pId']) . ' - ' . $data['productRating'] . " of 5\n";
                    }
                    $email .= "\n\nAutomated Message From " . $data['storeName'] . " Reviews.\n\n" . $data['reviewsAddress'] . "\n\n";
                    $email .= tep_db_input($data['reviewerFirstName']) . ' ' . tep_db_input($data['reviewerLastName']);
                    $email .= ", just added a product review.\n\nHere is what they had to say:\n\n";
                    $email .= str_replace('"', '\"', str_replace("'", "\'", $data['reviewerStoryData'])) . "\n\n\nDon't Forget to Approve this Review!";
                    if ($data['videoCheck']) {
                        $email .= "\n\nYouTube videos were submitted with this review.\n\nYouTube Video - " . $data['reviewerVideoLink'] . "\n\n";
                    } else {
                        $email .= "\n\nNo YouTube videos were submitted with this review.";
                    }
                    $email .= "\n\nReviewers IP Address is - " . ($_SERVER['HTTP_TRUE_CLIENT_IP'] ? $_SERVER['HTTP_TRUE_CLIENT_IP'] : $_SERVER['REMOTE_ADDR']);
                    if (!empty($_SERVER['HTTP_TRUE_CLIENT_IP'])) {
                        $email .= "\n\nReviewers IP Address is (Akamai) - " . $_SERVER['HTTP_TRUE_CLIENT_IP'];
                    } elseif ($_SERVER['HTTP_X_FORWARDED_FOR'] != "") {
                        $email .= "\n\nReviewers IP Address is (HTTP Forwarded - Proxy) - " . $_SERVER['HTTP_X_FORWARDED_FOR'];
                    }
                    $email .= "\n\nUser Agent - " . filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_ADD_SLASHES);
                    $email .= "\n\nVerified Review - " . ($data['isVerifiedCustomer'] ? 'Yes - ' . $data['purchaseDate'] : 'No');
                    if (!$data['isVerifiedCustomer']) {
                        $email .= '<hr>Agent Info: ' . $_SERVER['HTTP_USER_AGENT'];
                    }
                    $email .= '<hr>';
                    foreach ($data as $key => $value) {
                        if (is_array($value)) {
                            foreach ($value as $key2 => $value2) {
                                $email .= $key . '[' . $key2 . '] -' . htmlspecialchars(stripslashes($value2)) . "\n";
                            }
                        } else {
                            $email .= $key . ' - ' . htmlspecialchars(stripslashes($value)) . "\n";
                        }
                    }

                    $emailInfo['subject']   = $data['storeName'] . ' Submitted';
                    $emailInfo['text']      = nl2br($email);
                    $emailInfo['fromName']  = $data['storeName'];
                    $emailInfo['fromEmail'] = $data['storeEmailAddress'];
                }
                break;
            case 'imageUpload':
                $email = "\n\nProducts Reviewed Image Data\n";
                if (!empty($data['pId'])) {
                    $email .= tep_get_products_name($data['pId']) . ' - ' . $data['productRating'] . " of 5\n";
                }
                $email .= "\n\nAutomated Message From " . $data['storeName'] . " Reviews.\n\n" . $data['reviewsAddress'] . "\n\n";
                $email .= tep_db_input($data['reviewerFirstName']) . ' ' . tep_db_input($data['reviewerLastName']);
                if ($data['imageCheck']) {
                    $email .= "\n\nImages Were Uploaded with this review.\n\n";
                    foreach ($data['image_temp'] as $key => $value) {
                        $updatedImageName = 'review_id_' . $data['reviewsInsertId'] . '_image' . $key . '.' . strtolower(pathinfo($data['image_name'][$key], PATHINFO_EXTENSION));
                        $email .= "Image ".($key + 1)." - " . $updatedImageName . "\n";
                    }
                } else {
                    $email .= "\n\nNo Images were uploaded with this review.";
                }
                break;
            case 'mediaSubmissions':
                $reportEmails = getReportEmails(24, $data['storeId']);

                if (!empty($reportEmails)) {
                    $videoLink = '';

                    if ($data['imageCheck'] && $data['videoCheck']) {
                        $contentSubmitted = 'a Video and Image';
                    //Dont display video link for video and image
                    } elseif ($data['imageCheck']) {
                        $contentSubmitted = 'an Image';
                    } elseif ($data['videoCheck']) {
                        $contentSubmitted = 'a Video';
                        $videoLink        = $data['reviewerVideoLink'];
                    }

                    $email = $data['reviewerFirstName'] . ' just submitted a review with ' . strtolower($contentSubmitted) . '.<br><br>';
                    $email .= '<br><br><a href="https://www.powerequipmentdirect.com/PEDadmin/reviews.php?rID=' . $data['reviewsInsertId'] . '&action=edit">';
                    $email .= 'Check out the complete review.</a> ' . $videoLink;

                    $emailInfo['subject']   = $data['storeName'] . ' Review With ' . $contentSubmitted . ' Submitted!';
                    $emailInfo['text']      = nl2br($email);
                    $emailInfo['fromName']  = $data['storeName'];
                    $emailInfo['fromEmail'] = $data['storeEmailAddress'];
                }
                break;
            case 'lowProductRatings':
                $reportEmails = getReportEmails(20, $data['storeId']);
                if (!empty($reportEmails)) {
                    $email = $data['reviewerFirstName'] .' just submitted a review with a ' . $data['productRating'] . ' star rating.<br><br>';
                    $email .= '<br><br><a href="https://www.powerequipmentdirect.com/PEDadmin/reviews.php?rID=' . $data['reviewsInsertId'] . '&action=edit">';
                    $email .= 'Check out the complete review.</a>';

                    $emailInfo['subject']   = $data['storeName'] . ' Low Review Alert!';
                    $emailInfo['text']      = nl2br($email);
                    $emailInfo['fromName']  = $data['storeName'];
                    $emailInfo['fromEmail'] = $data['storeEmailAddress'];
                }
                break;
            default:
                break;
        }

        $allDataIsPresent = true;

        if (!empty($reportEmails)) {
            $emailInfo['toEmail'] = $reportEmails;
        }
        foreach ($emailInfo as $value) {
            if (empty($value)) {
                $allDataIsPresent = false;
            }
        }
        if ($allDataIsPresent) {
            tep_mail(
                '',
                $emailInfo['toEmail'],
                $emailInfo['subject'],
                $emailInfo['text'],
                $emailInfo['fromName'],
                $emailInfo['fromEmail']
            );
        }
    }

    private function executeBadDataResponse(string $customErrMsg = ''): void
    {
        if (empty($customErrMsg)) {
            $customErrMsg = 'An unexpected error occurred. Please check the input fields again';
        }
        $arr = [
            'error'    => true,
            'errorMsg' => $customErrMsg
        ];
        (new JsonResponse($arr, 400))->send();
        return;
    }
}