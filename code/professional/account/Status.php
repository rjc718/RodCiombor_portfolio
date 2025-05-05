<?php
namespace Pedstores\Ped\Models\Customers\Accounts\Components;

use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Models\Orders\Factory as OrdersFactory;
use Pedstores\Ped\Models\Order;
use Pedstores\Ped\Models\Orders\Data as OrdersData;
use Pedstores\Ped\Models\Orders\Item as OrdersItem;

class Status
{
    public const STATUS_PROCESSING       = 'Processing';
    public const STATUS_SHIPPED          = 'Shipped';
    public const STATUS_BACKORDERED      = 'Backordered';
    public const STATUS_CANCELED         = 'Canceled';
    public const STATUS_PARTIAL_SHIPMENT = 'Partially Completed';
    public const STATUS_READY_FOR_PICKUP = 'Ready for Pick Up';
    public const STATUS_PICKED_UP        = 'Picked Up';
    public const STATUS_DISPLAY          = [
        self::STATUS_PROCESSING => [
            'keys' => [
                OrdersData::STATUS_PROCESSING,
                OrdersData::STATUS_RECEIVED,
                OrdersData::STATUS_BUILT_TO_ORDER,
                OrdersData::STATUS_CUSTOMER_HOLD,
                OrdersData::STATUS_GOOGLE_NEW,
                OrdersData::STATUS_INSTALLATION,
                OrdersData::STATUS_PARTS_ORDERED,
                OrdersData::STATUS_GOOGLE_PROCESSING,
                OrdersData::STATUS_GOOGLE_SHIPPED,
                OrdersData::STATUS_AWAITING_FORM,
                OrdersData::STATUS_FORM_RECEIVED,
                OrdersData::STATUS_AWAITING_TAX_DOCUMENTS,
                OrdersData::STATUS_RECEIVED_TAX_DOCUMENTS,
                OrdersData::STATUS_AWAITING_REPLACEMENT,
                OrdersData::STATUS_PARTS,
                OrdersData::STATUS_CHECK_RECEIVED,
                OrdersData::STATUS_PENDING_CANCELLATION,
                OrdersData::STATUS_REAUTHORIZED,
                OrdersData::STATUS_AWAITING_CHECK,
                OrdersData::STATUS_PURCHASE_ORDER,
                OrdersData::STATUS_ACTION_REQUIRED,
                OrdersData::STATUS_AWAITING_VENDOR_CREDIT,
                OrdersData::STATUS_NEED_TO_COLLECT,
                OrdersData::STATUS_AWAITING_RETURN,
                OrdersData::STATUS_REAUTHORIZED,
                OrdersData::STATUS_FREIGHT_CLAIMS,
                OrdersData::STATUS_LOGISTICS,
                OrdersData::STATUS_COLLECTION_AGENCY,
                OrdersData::STATUS_FRAUD,
                OrdersData::STATUS_CHARGEBACK,
                OrdersData::STATUS_GROUND_CLAIMS,
                OrdersData::STATUS_AWAITING_REFUND
            ],
            'percent'   => 25,
            'alignment' => 'justify-content-start',
        ],
        self::STATUS_SHIPPED => [
            'keys' => [
                OrdersData::STATUS_SHIPPED,
                OrdersData::STATUS_PARTS_SHIPPED,
                OrdersData::STATUS_GOOGLE_SHIPPED_REFUNDED,
                OrdersData::STATUS_RESOLVED,
            ],
            'percent'   => 100,
            'alignment' => 'justify-content-end'
        ],
        self::STATUS_BACKORDERED => [
            'keys' => [
                OrdersData::STATUS_BACKORDERED
            ],
            'percent'   => 25,
            'alignment' => 'justify-content-start'
        ],
        self::STATUS_CANCELED => [
            'keys' => [
                OrdersData::STATUS_CANCELED,
                OrdersData::STATUS_GOOGLE_REFUNDED,
                OrdersData::STATUS_GOOGLE_CANCELED
            ],
            'percent'   => 0,
            'alignment' => 'justify-content-start'
        ],
        self::STATUS_PARTIAL_SHIPMENT => [
           'keys' => [
                OrdersData::STATUS_PARTIAL_SHIPMENT
           ],
           'percent'    => 75,
            'alignment' => 'justify-content-center'
        ],
        self::STATUS_READY_FOR_PICKUP => [
            'keys'      => [],
            'percent'   => 75,
            'alignment' => 'justify-content-center'
        ],
        self::STATUS_PICKED_UP => [
            'keys'      => [],
            'percent'   => 100,
            'alignment' => 'justify-content-end'
        ]
    ];
    private OrdersFactory $ordersFactory;

    public function __construct(
        OrdersFactory $ordersFactory
    ) {
        $this->ordersFactory = $ordersFactory;
    }

    public function getStatusIdForOrder(Order $order): int
    {
        return $order->getHistory()->getLast()->getStatusId();
    }

    public function getStatusIdForOrderProduct(OrdersItem $item): int
    {
        $order = $this->ordersFactory->getOrder(
            $item->getOrderId()
        );
        return $this->getStatusIdForOrder($order);
    }

    public function isProcessing(string $label): bool
    {
        return $label == self::STATUS_PROCESSING;
    }

    public function isBackOrdered(string $label): bool
    {
        return $label == self::STATUS_BACKORDERED;
    }

    public function isShipped(string $label): bool
    {
        return $label == self::STATUS_SHIPPED;
    }

    public function isCanceled(string $label): bool
    {
        return $label == self::STATUS_CANCELED;
    }

    public function isPickedUp(string $label): bool
    {
        return $label == self::STATUS_PICKED_UP;
    }

    public function isReadyForPickup(string $label): bool
    {
        return $label == self::STATUS_READY_FOR_PICKUP;
    }

    public function getProductStatusSection(Order $order, OrdersItem $item): Data
    {
        $status      = $this->getStatusLabelForOrderProduct($item);
        $displayData = self::STATUS_DISPLAY[$status];
        $statusDate  = $order->getHistory()->getLast()->getDateAdded()->format('M d');

        //Dont show Estimated Delivery Date on LPU Products
        $estDate  = null;
        $addDates = [
            self::STATUS_PROCESSING,
            self::STATUS_BACKORDERED,
            self::STATUS_PARTIAL_SHIPMENT,
        ];
        if (in_array($status, $addDates)) {
            $estDate = $item->getEstimatedDate()->format('M d, Y');
        }
        if ($item->hasLocalPickup()) {
            $estDate = null;
        }
        return new Data(
            'account/orders/product-status.php',
            [
                'label'       => $status,
                'alignment'   => $displayData['alignment'],
                'estDate'     => $estDate !== null ? 'Est. Delivery: ' . $estDate : '',
                'statusDate'  => $statusDate !== null ? $statusDate : '',
                'progressBar' => new Data('account/widgets/progress-bar.php', [
                    'percent' => $displayData['percent'],
                    'bgColor' => $this->isCanceled($status) ? 'bg-ferg-gray' : 'bg-white'
                ])
            ]
        );
    }

    public function getStatusLabelForOrderProduct(OrdersItem $item): string
    {
        $result = $this->getStatusLabelForLpuProduct($item);
        if (empty($result)) {
            $statusId = $this->getStatusIdForOrderProduct($item);
            $result   = $this->getStatusMessageById($statusId);
        }
        return $result;
    }

    public function getStatusLabelForLpuProduct(OrdersItem $item): string
    {
        $result     = '';
        $prodStatus = $item->getProductStatus();
        if ($item->hasLocalPickup() && (
            $prodStatus == OrdersItem::STATUS_PICKUP_READY 
            || $prodStatus == OrdersItem::STATUS_PICKED_UP
        )
        ) {
            if ($prodStatus == OrdersItem::STATUS_PICKED_UP) {
                $result = self::STATUS_PICKED_UP;
            } else {
                $result = self::STATUS_READY_FOR_PICKUP;
            }
        }
        return $result;
    }

    public function getStatusLabelForOrder(Order $order): string
    {
        $result = '';
        foreach ($order->getItems() as $item) {
            if ($item->hasLocalPickup()) {
                $result = $this->getStatusLabelForLpuProduct($item);
                if (!empty($result)) {
                    break;
                }
            }
        }
        if (empty($result)) {
            $statusId = $this->getStatusIdForOrder($order);
            $result   = $this->getStatusMessageById($statusId);
        }
        return $result;
    }

    public function getStatusMessageById(int $statusId): string
    {
        $result = '';
        foreach (self::STATUS_DISPLAY as $message => $data) {
            if (in_array($statusId, $data['keys'])) {
                $result = $message;
                break;
            }
        }
        return $result;
    }
}