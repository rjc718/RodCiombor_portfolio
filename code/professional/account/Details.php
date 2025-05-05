<?php
namespace Pedstores\Ped\Models\Customers\Accounts\Pages\Orders;

use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Models\Cart;
use Pedstores\Ped\Models\Orders\Factory as OrdersFactory;
use Pedstores\Ped\Models\Products\Factory as ProductsFactory;
use Pedstores\Ped\Models\Logistics\Branches\Factory as BranchesFactory;
use Pedstores\Ped\Models\Products\Manuals\Factory as ManualsFactory;
use Pedstores\Ped\Views\Collection as ViewsCollection;
use Pedstores\Ped\Models\Order;
use Pedstores\Ped\Models\Orders\Data as OrdersData;
use Pedstores\Ped\Models\Orders\Item as OrdersItem;
use Pedstores\Ped\Models\TrackingCarrier;
use Pedstores\Ped\Models\WriteAReview\WriteAReview;
use Pedstores\Ped\Models\Customers\Accounts\Components\Status;
use Pedstores\Ped\Models\Customers\Accounts\Components\Helpers;
use Pedstores\Ped\Models\Customers\Accounts\Components\Payment;
use Pedstores\Ped\Models\Customers\Accounts\Components\Navigation;

class Details extends Helpers
{
    private const PAGE_DIR = 'account/orders/details/';
    private OrdersFactory $ordersFactory;
    private ProductsFactory $productsFactory;
    private BranchesFactory $branchesFactory;
    private ManualsFactory $manuals;
    private Status $status;
    private TrackingCarrier $carrier;
    private WriteAReview $review;
    private Payment $payment;
    private Navigation $nav;

    public function __construct(
        Store $store,
        OrdersFactory $ordersFactory,
        ProductsFactory $productsFactory,
        BranchesFactory $branchesFactory,
        ManualsFactory $manuals,
        Status $status,
        Payment $payment,
        TrackingCarrier $carrier,
        WriteAReview $review,
        Navigation $nav,
        Cart $cart
    ) {
        $this->ordersFactory   = $ordersFactory;
        $this->productsFactory = $productsFactory;
        $this->branchesFactory = $branchesFactory;
        $this->manuals         = $manuals;
        $this->status          = $status;
        $this->payment         = $payment;
        $this->carrier         = $carrier;
        $this->review          = $review;
        $this->nav             = $nav;
        parent::__construct($store, $cart);
    }

    public function getPage(int $ordersId): Data
    {
        $notShippedList     = new ViewsCollection();
        $shippedList        = [];
        $sortingArray       = [];
        $order              = $this->ordersFactory->getOrder($ordersId);
        $statusLabel        = $this->status->getStatusLabelForOrder($order);
        $orderItems         = $order->getItems();
        $lpuCount           = 0;
        $estDeliveryDate    = '';
        $estReadyPickupDate = '';
        $shipCountMsg       = '';
        $lpuStatusCountMsg  = '';

        foreach ($orderItems as $item) {
            $trackingNumber = $item->getTrackingNumber();
            $hasLpu         = $item->hasLocalPickup();

            if ($hasLpu) {
                $lpuCount += $item->getProductQty();
            }
            /**
             * Get the last Estimated Delivery Date for Shipped/LPU
             * Display it at the top of the Status section
             */
            if ($item->getEstimatedDate() !== null) {
                $estDate = $item->getEstimatedDate()->format('F d, Y');
                if ($hasLpu) {
                    if ($this->status->isReadyForPickup($statusLabel)) {
                        $estReadyPickupDate = 'Estimated Pickup Date: ' . $estDate;
                    }
                } elseif (
                    !$this->status->isShipped($statusLabel)
                    && !$this->status->isCanceled($statusLabel)
                ) {
                    $estDeliveryDate = 'Estimated Delivery Date: '. $estDate;
                }
            }

            if (empty($trackingNumber) || $hasLpu) {
                /**
                 * Optionally separate into the following groups at a later time:
                 * Freight, processingShipping, and lpu
                 */
                $notShippedList->append(
                    $this->getItemBox($order, $item)
                );
            } else {
                if (!array_key_exists($trackingNumber, $sortingArray)) {
                    $sortingArray[$trackingNumber] = [];
                }
                array_push($sortingArray[$trackingNumber], $item);
            }
        }

        foreach ($sortingArray as $arr) {
            $shipmentItemList = new ViewsCollection();
            foreach ($arr as $item) {
                $shipmentItemList->append(
                    $this->getItemBox($order, $item)
                );
            }
            $shippedList[] = $shipmentItemList;
        }
        $shipCount = count($shippedList);

        if ($lpuCount > 0) {
            $lpuStatusCountMsg = $lpuCount . ' Item' . ($lpuCount > 1 ? 's' : '');
        } elseif ($shipCount > 0) {
            $shipCountMsg = $shipCount . ' Shipment' . ($shipCount > 1 ? 's' : '');
        }

        $output = new Data(self::PAGE_DIR . 'page.php', [
            'top' => new Data(self::PAGE_DIR . 'top.php', [
                'titleBar' => new Data('account/orders/title-bar.php', [
                    'title' => 'Order Details',
                    'icon'  => implode('', [
                        $this->store->getUrl(Store::DEFAULT),
                        '/images/icons/hand-truck-icon.svg',
                    ])
                ]),
                'backBtn'     => $this->nav->createBackButton(Navigation::KEY_DETAILS),
                'orderId'     => $order->getOrderId(),
                'orderDate'   => $order->getData()->getPurchaseDate()->format('m/d/y'),
                'totalCost'   => $order->getTotals()->getTotal()->getString(),
                'receiptLink' => $this->getOrderReceiptLink($order),
                'statusMsg'   => new Data(self::PAGE_DIR . 'info/status.php', [
                    'status'             => 'Order Status: ' . $statusLabel,
                    'shipCountMsg'       => $shipCountMsg,
                    'estDeliveryDate'    => $estDeliveryDate,
                    'lpuCountMsg'        => $lpuStatusCountMsg,
                    'estReadyPickupDate' => $estReadyPickupDate
                ]),
                'infoRows' => $this->getInfoSection(
                    $order,
                    $shipCount,
                    $lpuCount > 0
                )
            ]),
            'content' => new Data(self::PAGE_DIR . 'items/item-list.php', [
                'notShippedItems' => $notShippedList,
                'shipments'       => $this->getShipmentList($shippedList)
            ]),
            'bottom' => new Data(self::PAGE_DIR . 'bottom.php', [
                'totals'      => $this->getTotalsSection($order),
                'writeReview' => $this->review->createTargetDiv()
            ]),
            'sideBarModal' => new Data('bootstrap/offcanvas.php'),
            'orderId'      => $ordersId,
            'orderStoreId' => $order->getData()->getStoreId()
        ]);
        return $output;
    }

    private function getInfoSection(
        Order $order,
        int $numShipments,
        bool $hasLpu
    ): ViewsCollection {
        $count        = 1;
        $infoList     = new ViewsCollection();
        $columnList   = new ViewsCollection();
        $rowList      = new ViewsCollection();
        $shippingInfo = $order->getShippingContact();
        $shipLocation = $shippingInfo->getLocation();
        $name         = $shippingInfo->getFirstAndLastName();
        $city         = $shipLocation->getCity();
        $state        = $shipLocation->getRegionCode();
        $zipcode      = $shipLocation->getPostalCode();
        $address      = trim(
            $shipLocation->getStreet() . ' ' . $shipLocation->getUnit()
        );

        //Always Show Shipping Address even if there are no Shipments yet, except for LPU
        if ($numShipments == 0 && !$hasLpu) {
            $infoList->append(
                new Data(self::PAGE_DIR . 'info/address.php', [
                    'title'    => 'Shipping Address',
                    'subtitle' => '',
                    'name'     => $name,
                    'address'  => $address,
                    'city'     => $city,
                    'state'    => $state,
                    'zipcode'  => $zipcode
                ])
            );
        }

        /**
         * Get all shipments if there are more than one
         * All shipments will currently have the same address per Alonzo Turner
         */
        for ($i = 0; $i < $numShipments; $i++) {
            if ($numShipments > 1) {
                $title = 'Shipment ' . $count . ':';
            }
            $infoList->append(
                new Data(self::PAGE_DIR . 'info/address.php', [
                    'title'    => $title ?? '',
                    'subtitle' => 'Shipping Address',
                    'name'     => $name,
                    'address'  => $address,
                    'city'     => $city,
                    'state'    => $state,
                    'zipcode'  => $zipcode
                ])
            );
            $count++;
        }

        //LPU Contact Info
        if ($hasLpu) {
            $lpuInfo = $order->getLocalPickupContact();
            $infoList->append(
                new Data(self::PAGE_DIR . 'info/pickup-contact.php', [
                    'title' => 'Pickup Contact',
                    'name'  => $lpuInfo->getFirstAndLastName(),
                    'phone' => $lpuInfo->getPhoneNumberFormatted(),
                    'email' => $lpuInfo->getEmail()
                ])
            );
        }

        //Billing Info
        $billingInfo     = $order->getBillingContact();
        $billingLocation = $billingInfo->getLocation();
        $address         = trim(
            $billingLocation->getStreet() . ' ' . $billingLocation->getUnit()
        );
        $infoList->append(
            new Data(self::PAGE_DIR . 'info/address.php', [
                'title'    => 'Billing Address',
                'subtitle' => '',
                'name'     => $billingInfo->getFirstAndLastName(),
                'address'  => $address,
                'city'     => $billingLocation->getCity(),
                'state'    => $billingLocation->getRegionCode(),
                'zipcode'  => $billingLocation->getPostalCode()
            ])
        );

        //Payment Method Info
        $infoList->append(
            $this->payment->getPaymentInfo(
                $order
            )
        );

        //Add each section to rows and columns
        foreach ($infoList->chunk(2) as $info) {
            $columnList->append(
                new Data(self::PAGE_DIR . 'info/column.php', [
                    'content'      => $info,
                    'extraClasses' => ' mb-4'
                ])
            );
        }
        foreach ($columnList->chunk(2) as $col) {
            $rowList->append(
                new Data(self::PAGE_DIR . 'info/row.php', [
                    'content' => $col
                ])
            );
        }
        return $rowList;
    }

    private function getShipmentList(array $shipments): ViewsCollection
    {
        $shipmentViews  = new ViewsCollection();
        $totalShipments = count($shipments);
        $shipmentCount  = 1;

        for ($i = 0; $i < $totalShipments; $i++) {
            $shipmentViews->append(
                new Data(self::PAGE_DIR . 'items/shipment.php', [
                    'shipmentCount'  => $shipmentCount,
                    'totalShipments' => $totalShipments,
                    'shipmentStatus' => 'Shipping Address',
                    'itemList'       => $shipments[$i]
                ])
            );
            $shipmentCount++;
        }
        return $shipmentViews;
    }

    private function getItemBox(
        Order $order,
        OrdersItem $item
    ): Data {
        $ordersStoresId = $order->getData()->getStoreId();
        $product        = $this->productsFactory->getProduct(
            $item->getProductId()
        );
        $model    = $item->getProductModel();
        $modelAlt = $item->getProductModelAlt();
        if ($modelAlt) {
            $model = $modelAlt;
        }
        if ($item->hasLocalPickup()) {
            $lpuBranchLocation = $this->getLpuBranchLocationForItem($item);
        }
        return new Data(self::PAGE_DIR . 'items/item.php', [
            'name'   => $item->getProductName(),
            'imgSrc' => $this->getProductImageSrc(
                $product->getImageData()['bimage'],
                $ordersStoresId
            ),
            'link' => $this->getProductLink(
                $product->getUrl(),
                $ordersStoresId
            ),
            'qty'                  => $item->getProductQty(),
            'price'                => $item->getProductCost()->getString(),
            'model'                => $model,
            'storeName'            => $this->store->getName($ordersStoresId),
            'lpuBranchLocation'    => $lpuBranchLocation ?? '',
            'productStatusSection' => $this->status->getProductStatusSection(
                $order,
                $item
            ),
            'trackingInfo' => $this->getItemTrackingSection(
                $item,
                $ordersStoresId
            ),
            'linkButtons' => $this->getItemLinkButtons(
                $item,
                $product->getStatusId()
            )
        ]);
    }

    private function getLpuBranchLocationForItem(OrdersItem $item): Data
    {
        $branch = $this->branchesFactory->getBranchByNumber(
            $item->getWarehouseNumber()
        );
        return new Data(self::PAGE_DIR . 'items/lpu-branch.php', [
            'name'    => $branch->getName() . ';<br>',
            'address' => $branch->getLocation()->getOneLineAddress(),
        ]);
    }

    private function getItemLinkButtons(
        OrdersItem $item,
        int $statusId
    ): ViewsCollection {
        $linkList = new ViewsCollection();
        $status   = $this->status->getStatusLabelForOrderProduct($item);
        $prodId   = $item->getProductId();

        $buyItAgain = $this->getBuyItAgainButton($prodId, $statusId);
        if (!empty($buyItAgain)) {
            $linkList->append(
                $buyItAgain
            );
        }

        $linkList->append(
            $this->review->createButtonTemplate(
                $prodId,
                'text-ferg-blue-link',
                '',
                'Write a Review',
                1,
                'a'
            )
        );

        //Only Show for Picked Up and Shipped Items
        if (in_array($status, [
                Status::STATUS_SHIPPED,
                Status::STATUS_PICKED_UP
            ])) {
            $linkList->append(
                new Data('tags/anchor.php', [
                    'content' => 'Start a Return',
                    'class'   => 'text-ferg-blue-link',
                    'href'    => $this->store->getUrl() . self::RETURNS_LINK
                ])
            );
        }

        //Show if not Cancelled or Shipped or Picked Up
        if (!in_array($status, [
                Status::STATUS_CANCELED,
                Status::STATUS_SHIPPED,
                Status::STATUS_PICKED_UP
            ])) {
            $linkList->append(
                new Data('tags/anchor.php', [
                    'content' => 'Cancel Order',
                    'class'   => 'text-ferg-blue-link',
                    'href'    => $this->store->getUrl() . self::CANCEL_ORDER_LINK
                ])
            );
        }

        //Show only if Product has manuals - Might need to update when set up JS
        $manualsData = $this->manuals->getManuals($prodId)->getLinkList();

        if (count($manualsData) > 0) {
            $linkList->append(
                new Data('tags/anchor.php', [
                    'content' => 'Manuals',
                    'class'   => 'text-ferg-blue-link',
                    'href'    => '',
                    'attrs'   => implode(' ', [
                        'data-bs-toggle="offcanvas"',
                        'role="button"',
                        'data-action="account:manuals"',
                        sprintf('data-prod-id="%s"', $prodId)
                    ])
                ])
            );
        }

        $itemList   = new ViewsCollection();
        $groupCount = 1;
        $groupList  = $linkList->chunk(3);

        foreach ($groupList as $group) {
            $linkCount = 1;
            foreach ($group as $link) {
                $classList = 'list-group-item border-0 py-0';
                if ($linkCount == 1) {
                    $classList .= ' ps-0';
                    if ($groupCount > 1) {
                        $classList .= ' ps-xxl-3';
                    }
                }
                if ($groupCount == count($groupList)) {
                    if ($linkCount < count($group)) {
                        $classList .= ' border-end';
                    }
                } else {
                    $classList .= ' border-end';
                }
                $itemList->append(
                    new Data('tags/lists/item.php', [
                        'class' => $classList,
                        'text'  => $link
                    ])
                );
                $linkCount++;
            }
            $groupCount++;
        }

        $itemList = $itemList->chunk(3);

        $ulList = new ViewsCollection();
        foreach ($itemList as $items) {
            $ulList->append(
                new Data('tags/lists/unordered.php', [
                    'class' => implode(' ', [
                        'list-group',
                        'list-group-flush',
                        'd-flex',
                        'flex-row',
                        'mb-4',
                    ]),
                    'items' => $items
                ])
            );
        }
        return $ulList;
    }

    private function getTotalsSection(Order $order): Data
    {
        $lines = new ViewsCollection();

        foreach ($order->getTotals() as $detail) {
            $title = strip_tags($detail->getTitle());
            $text  = strip_tags($detail->getText());
            $tmp   = new Data(self::PAGE_DIR . 'total.php', [
                'title' => $title,
                'text'  => $text
            ]);
            if ($detail->getClass() === 'ot_total') {
                $totalLine = $tmp;
            } else {
                $lines->append($tmp);
            }
        }
        return new Data(self::PAGE_DIR . 'order-totals.php', [
            'lines' => $lines,
            'total' => $totalLine
        ]);
    }

    private function getItemTrackingSection(
        OrdersItem $item,
        int $ordersStoresId
    ): Data {
        $trackingNum = $item->getTrackingNumber();

        if (
            $this->status->getStatusIdForOrderProduct($item) == OrdersData::STATUS_CANCELED
            || empty($trackingNum)
            || $item->hasLocalPickup()
        ) {
            /**
             * Dont show Tracking Info if tracking number is empty
             * Or if it is an LPU product
             * Or if the order is cancelled
             */
            $tmp = new Data(self::PAGE_DIR . 'items/tracking/info.php', [
                'trackingNumber' => '',
                'carrierInfo'    => '',
            ]);
        } else {
            $carrierInfo = $this->carrier->getInfoById(
                $item->getTrackingCarrier()
            );

            $trackingCarrier = $carrierInfo['tracking_carrier'] ?? '';
            $carrierPhone    = $carrierInfo['carrier_phone']    ?? '';
            $scac            = $item->getScacCode()             ?? '';
            $convey          = $carrierInfo['convey']           ?? 0;
            $trackingUrl     = $carrierInfo['tracking_url']     ?? '';

            if ($scac != '' && $convey == 1) {
                $storeName = strtolower(
                    str_replace(" ", "", $this->store->getName($ordersStoresId))
                );
                $trackingTmp = new Data(
                    self::PAGE_DIR . 'items/tracking/number-link.php',
                    [
                    'trackingNum' => $trackingNum,
                    'href'        => sprintf(
                        'https://tracking.getconvey.com/%s/%s?tn=%s',
                        $storeName,
                        $scac,
                        $trackingNum
                    )
                ]
                );
            } elseif (!empty($trackingUrl)) {
                $trackingTmp = new Data(
                    self::PAGE_DIR . 'items/tracking/number-link.php',
                    [
                    'trackingNum' => $trackingNum,
                    'href'        => $trackingUrl . $trackingNum
                ]
                );
            } else {
                $trackingTmp = new Data(
                    self::PAGE_DIR . 'items/tracking/number.php',
                    [
                    'trackingNum' => $trackingNum
                ]
                );
            }

            $tmp = new Data(
                self::PAGE_DIR . 'items/tracking/info.php',
                [
                'trackingNumber' => $trackingTmp,
                'carrierInfo'    => new Data(
                    self::PAGE_DIR . 'items/tracking/carrier-info.php',
                    [
                        'name'     => $trackingCarrier,
                        'href'     => 'tel:' . $carrierPhone,
                        'linkText' => $carrierPhone
                    ]
                )
            ]
            );
        }
        return $tmp;
    }
}