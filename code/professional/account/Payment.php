<?php
namespace Pedstores\Ped\Models\Customers\Accounts\Components;

use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Models\Order;
use Pedstores\Ped\Models\Orders\Data as OrdersData;
use Pedstores\Ped\Databases\Database;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Models\Customers\Accounts\Components\Status;
use Pedstores\Ped\Payments\Providers\CyberSource;
use Pedstores\Ped\Utilities\Text;
use Firebase\JWT\JWT;
use Pedstores\Ped\Models\Cybersource\Reauthorization;

class Payment
{
    private const PAGE_DIR = 'account/orders/details/';
    private Database $db;
    private Store $store;
    private Status $status;

    public function __construct(
        Database $db,
        Store $store,
        Status $status
    ) {
        $this->db     = $db;
        $this->store  = $store;
        $this->status = $status;
    }

    public function getPaymentInfo(Order $order): Data
    {
        $ordersData = $order->getData();
        $method     = $ordersData->getPaymentMethod();

        // New Cart uses "CyberSource", legacy (maybe) uses "Credit Card"
        if ($method === CyberSource::PAYMENT_METHOD || $method === 'Credit Card') {
            //Extract the JWT Token from the payment data
            //See getContext() in Payments/Providers/CyberSource
            $paymentData = $ordersData->getPaymentData();
            $jwt         = null;
            $jwtData     = null;
            if (!is_null($paymentData)) {
                foreach ($paymentData->transactions as $transaction) {
                    if ($transaction->type === 'payment token') {
                        $jwt = $transaction->transactionId;
                        break;
                    }
                }
            }
            if (!is_null($jwt)) {
                $jwtData = Text::decodeJson(JWT::urlsafeB64Decode(
                    explode('.', $jwt)[1]
                ));
            }
            $ccNumber = $this->getCCNumber($jwtData);
        }

        $orderId = $order->getId();
        $uuid    = $this->getUuidForOrder($orderId);
        if (
            $ordersData->getNeedsReauth() 
            && !empty($uuid) 
            && $this->status->getStatusLabelForOrder($order) === Status::STATUS_PROCESSING
        ) {
            $reAuthLink  = $this->displayReauthorizationLink($uuid, $orderId);
            $invalidIcon = new Data('bootstrap/icons/alerts/danger.php', [
                'classes' => ' text-danger'
            ]);
        }

        $displayInfo = $this->getDisplayInfo($ordersData, $method);
        if (!empty($displayInfo['imgSrc'])) {
            if ($method == 'Affirm') {
                $height = 38;
                $width  = 91;
            } elseif (in_array($method, ['Synchrony', 'Synchrony Financial'])) {
                $height = 38;
                $width  = 150;
            } else {
                $height = 45;
                $width  = 45;
            }
            $logo = new Data('tags/image.php', [
                'src'    => $displayInfo['imgSrc'],
                'width'  => $width,
                'height' => $height,
                'alt'    => $displayInfo['label'],
                'class'  => 'me-3'
            ]);
        }

        return new Data(self::PAGE_DIR . 'info/payment/method.php', [
            'title'       => 'Payment Method',
            'invalidIcon' => $invalidIcon ?? '',
            'logo'        => $logo ?? '',
            'payMethod'   => $displayInfo['label'],
            'ccNumber'    => $ccNumber ?? '',
            'reAuthLink'  => $reAuthLink ?? ''
        ]);
    }

    private function displayReauthorizationLink(string $uuid, int $orderId): Data
    {
        $link = sprintf(Reauthorization::REAUTH_LINK, $uuid, $orderId);
        return new Data(self::PAGE_DIR . 'info/payment/reauth-link.php', [
            'updatePaymentLink' => new Data('tags/anchor.php', [
                'content' => 'Update payment information',
                'href'    => $link
            ])
        ]);
    }

    private function getUuidForOrder(int $orderId): ?string
    {
        return $this->db->getQueryResult(Reauthorization::SQL_UUID, [$orderId]);
    }

    private function getDisplayInfo(OrdersData $ordersData, string $method): array
    {
        $path   = $this->store->getUrl(Store::DEFAULT) . '/images/icons/';
        $label  = $method;
        $imgSrc = '';

        $methodList = [
            'btPaypal' => [
                'label'  => 'Paypal',
                'imgSrc' => ''
            ],
            'Paypal Direct' => [
                'label'  => 'Paypal',
                'imgSrc' => ''
            ],
            'Paypal Express' => [
                'label'  => 'Paypal',
                'imgSrc' => ''
            ],
            'Synchrony' => [
                'label'  => '',
                'imgSrc' => $path . 'logo-synchrony.svg'
            ],
            'Synchrony Financial' => [
                'label'  => '',
                'imgSrc' => $path . 'logo-synchrony.svg'
            ],
            'Affirm' => [
                'label'  => '',
                'imgSrc' => implode('/', [
                                    'https://cdn-assets.affirm.com',
                                    'images',
                                    'black_logo-white_bg.svg'])
            ],
            'HeatPumpStore' => [
                'label'  => 'Heat Pump Store',
                'imgSrc' => '',
            ],
            'shopatron' => [
                'label'  => 'Shopatron',
                'imgSrc' => '',
            ],
            'Check' => [
                'label'  => 'Paper Check',
                'imgSrc' => ''
            ],
            'CyberSource' => [
                'label'  => 'Credit Card',
                'imgSrc' => ''
            ],
            'Credit Card' => [
                'label'  => 'Credit Card',
                'imgSrc' => ''
            ],
        ];

        if (array_key_exists($method, $methodList)) {
            $label  = $methodList[$method]['label'];
            $imgSrc = $methodList[$method]['imgSrc'];

            if ($label === 'Credit Card') {
                //Account for instances of inconsistent capitalization in orders table
                $ccType = strtoupper($ordersData->getCreditCardType());
                switch ($ccType) {
                    case 'VISA':
                        $label  = 'Visa';
                        $imgSrc = $path . 'icon-visa.svg';
                        break;
                    case 'MASTER CARD':
                        $label  = 'MC';
                        $imgSrc = $path . 'icon-mastercard.svg';
                        break;
                    case 'AMERICAN EXPRESS':
                        $label  = 'Amex';
                        $imgSrc = $path . 'icon-amex.svg';
                        break;
                    case 'DISCOVER':
                        $label  = 'Discover';
                        $imgSrc = $path . 'icon-discover.svg';
                        break;
                    default:
                        $label  = 'Credit Card';
                        $imgSrc = '';
                }
            }
        }
        return [
            'label'  => $label,
            'imgSrc' => $imgSrc
        ];
    }

    private function getCCNumber(?object $jwtData): string
    {
        $ccNumber = '****************';
        if ($jwtData !== null) {
            if (isset($jwtData->content->paymentInformation->card->number->maskedValue)) {
                $ccNumber = $jwtData->content->paymentInformation->card->number->maskedValue;
                $ccNumber = str_replace('X', '*', $ccNumber);
            }
        }
        return $ccNumber;
    }
}
