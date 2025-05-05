<?php
namespace Pedstores\Ped\Models\Customers\Accounts\Pages\Orders;

use Pedstores\Ped\Databases\Database;
use Pedstores\Ped\Models\Request;
use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Models\Cart;
use Pedstores\Ped\Models\Orders\Factory as OrdersFactory;
use Pedstores\Ped\Models\Products\Factory as ProductsFactory;
use Pedstores\Ped\Models\Orders\Collection as OrdersCollection;
use Pedstores\Ped\Views\Collection as ViewsCollection;
use Pedstores\Ped\Models\Collections\Integer;
use Pedstores\Ped\Models\Customers\Authorization;
use Pedstores\Ped\Models\Order;
use Pedstores\Ped\Models\WriteAReview\WriteAReview;
use Pedstores\Ped\Models\Customers\Accounts\Components\Status;
use Pedstores\Ped\Models\Customers\Accounts\Components\Helpers;
use Pedstores\Ped\Models\Customers\Accounts\Components\Pagination;
use Pedstores\Ped\Models\Customers\Accounts\Components\Navigation;
use PDO;
use DateTime;
use DateInterval;

class History extends Helpers
{
    private const TMPL_DIR                = 'account/orders/history/';
    private const PAGE_DIR                = '/customers/account/history';
    private const ORDER_DETAILS_LINK_BASE = 'details/';
    private Request $request;
    private Database $db;
    private OrdersFactory $ordersFactory;
    private ProductsFactory $productsFactory;
    private Status $status;
    private WriteAReview $review;
    private Pagination $pagination;
    private Navigation $nav;

    public function __construct(
        Request $request,
        Store $store,
        Database $db,
        OrdersFactory $ordersFactory,
        ProductsFactory $productsFactory,
        Status $status,
        WriteAReview $review,
        Pagination $pagination,
        Navigation $nav,
        Cart $cart
    ) {
        $this->request         = $request;
        $this->db              = $db;
        $this->ordersFactory   = $ordersFactory;
        $this->productsFactory = $productsFactory;
        $this->status          = $status;
        $this->review          = $review;
        $this->pagination      = $pagination;
        $this->nav             = $nav;
        parent::__construct($store, $cart);
    }

    public function getPage(): Data
    {
        $customerId = (int) $this->request->getSession(
            Authorization::SESSION_KEY
        );

        $statusFilters = $this->getStatusFiltersFromURL();
        $dateFilter    = $this->request->getInteger('date')   ?? 0;
        $offset        = $this->request->getInteger('offset') ?? 0;
        $limit         = $this->request->getInteger('limit');
        if (empty($limit)) {
            $limit = self::RECORDS_PER_PAGE;
        };

        //Get all orders, modified by filters and pagination
        $orderList = $this->getOrdersForCustomer(
            $customerId,
            $dateFilter,
            $statusFilters,
            $offset,
            $limit
        );

        if ($orderList->count() > 0) {
            $content = new Data(self::TMPL_DIR . 'order-list.php', [
                'orderList' => $this->getOrderList($orderList)
            ]);
            $sort      = $this->buildOrderFilter();
            $classList = 'col-md-7 col-xxl-8';
        } else {
            $content   = new Data(self::TMPL_DIR . 'no-orders.php');
            $sort      = '';
            $classList = 'col-12';
        }

        $pagination  = '';
        $totalOrders = $this->getTotalOrdersForCustomer(
            $customerId,
            $dateFilter,
            $statusFilters
        );
        if ($totalOrders > self::RECORDS_PER_PAGE) {
            $pagination = $this->pagination->buildPagination(
                self::PAGE_DIR,
                $totalOrders,
                self::RECORDS_PER_PAGE
            );
        }

        return new Data(self::TMPL_DIR . 'page.php', [
            'top' => new Data(self::TMPL_DIR . 'top.php', [
                'titleBar' => new Data('account/orders/title-bar.php', [
                    'title' => 'Orders',
                    'icon'  => implode('', [
                        $this->store->getUrl(Store::DEFAULT),
                        '/images/icons/hand-truck-icon.svg',
                    ])
                ]),
                'backBtn'   => $this->nav->createBackButton(Navigation::KEY_HISTORY),
                'sort'      => $sort,
                'classList' => $classList
            ]),
            'content' => $content,
            'bottom'  => new Data(self::TMPL_DIR . 'bottom.php', [
                'pagination'  => $pagination,
                'writeReview' => $this->review->createTargetDiv()
            ])
        ]);
    }

    private function getOrderList(OrdersCollection $orderList): ViewsCollection
    {
        $count = 1;
        $list  = new ViewsCollection();

        foreach ($orderList as $order) {
            $itemCount = $order->getItems()->count();
            $list->append(new Data(self::TMPL_DIR . 'order.php', [
                'header' => new Data(self::TMPL_DIR . 'order-header.php', [
                    'orderStatus'  => $this->status->getStatusLabelForOrder($order),
                    'orderId'      => $order->getOrderId(),
                    'purchaseDate' => $order->getData()->getPurchaseDate()->format('m/d/y'),
                    'itemCount'    => $itemCount . ' Item' . ($itemCount > 1 ? 's' : ''),
                    'ariaExpanded' => ($count == 1 ? 'true' : 'false'),
                    'collapsed'    => ($count > 1 ? ' collapsed' : '')
                ]),
                'topLinks'    => $this->getTopLinks($order),
                'productList' => $this->getProductList($order),
                'orderId'     => $order->getOrderId(),
                'show'        => ($count == 1 ? ' show' : '')
            ]));
            $count++;
        }

        return $list;
    }

    private function getOrdersForCustomer(
        int $customersId,
        $dateFilter = 0,
        $statusFilters = [],
        $offset = 0,
        $limit = self::RECORDS_PER_PAGE
    ): OrdersCollection {
        $sql = Order::SQL_ORDER_ID_BY_CUSTOMER;
        $sql .= $this->getStatusFilterSqlParams($statusFilters);
        $sql .= $this->getDateFilterSqlParams($dateFilter);
        $sql .= " ORDER BY `o`.`date_purchased` DESC";
        $sql .= " LIMIT " . $limit . " OFFSET " . $offset;

        $orderIds = new Integer($this->db->getQueryColumn(
            $sql,
            [
                ':customersId' => (object)[
                    'value' => $customersId,
                    'type'  => PDO::PARAM_INT,
                ]
            ]
        ));

        $collection = new OrdersCollection();
        foreach ($orderIds as $id) {
            $order = $this->ordersFactory->getOrder($id);
            $collection->append($order);
        }
        return $collection;
    }

    private function getTotalOrdersForCustomer(
        int $customersId,
        $dateFilter = 0,
        $statusFilters = []
    ): int {
        $sql = Order::SQL_ORDER_COUNT_FOR_CUSTOMER;
        $sql .= $this->getStatusFilterSqlParams($statusFilters);
        $sql .= $this->getDateFilterSqlParams($dateFilter);

        $count = $this->db->getQueryResult(
            $sql,
            [
                ':customersId' => (object)[
                    'value' => $customersId,
                    'type'  => PDO::PARAM_INT,
                ]
            ]
        );
        return $count ?? 0;
    }

    private function buildOrderFilter(): Data
    {
        $selectedStatuses  = $this->getStatusFiltersFromURL();
        $selectedDateValue = $this->request->getInteger('date') ?? 0;

        $dateOptions   = new ViewsCollection();
        $statusOptions = new ViewsCollection();

        $dateLabels = [
            ['value' => 5, 'text' => 'Last 30 Days'],
            ['value' => 4, 'text' => 'Last 3 Months'],
            ['value' => 3, 'text' => 'Last 6 Months'],
            ['value' => 2, 'text' => 'Last 1 Year'],
            ['value' => 1, 'text' => 'Last 5 Years'],
            ['value' => 0, 'text' => 'All Years']
        ];
        foreach ($dateLabels as $label) {
            $checked = ($selectedDateValue === $label['value'] ? true : false);
            $dateOptions->append(
                new Data('bootstrap/formCheck.php', [
                    'classList'   => ' pt-2 m-0',
                    'checked'     => $checked,
                    'extraParams' => ' data-action="filter:date"',
                    'value'       => $label['value'],
                    'id'          => 'dateFilter' . $label['value'],
                    'for'         => 'dateFilter' . $label['value'],
                    'label'       => $label['text']
                ])
            );
        }

        $statusLabels = [
            ['value' => 1, 'text' => 'Not Yet Shipped'],
            ['value' => 2, 'text' => 'Local Pickup'],
            ['value' => 3, 'text' => 'Shipped'],
            ['value' => 4, 'text' => 'Canceled']
        ];
        foreach ($statusLabels as $label) {
            $checked = (in_array(
                $label['value'],
                $selectedStatuses
            ) ? true : false);
            $statusOptions->append(
                new Data('bootstrap/formCheck.php', [
                    'classList'   => ' pt-2 m-0',
                    'checked'     => $checked,
                    'extraParams' => ' data-action="filter:status"',
                    'value'       => $label['value'],
                    'id'          => 'statusFilter' . $label['value'],
                    'for'         => 'statusFilter' . $label['value'],
                    'label'       => $label['text']
                ])
            );
        }
        return new Data('account/orders/history/filter-dropdown.php', [
            'dateOptions'   => $dateOptions,
            'statusOptions' => $statusOptions
        ]);
    }

    private function getDateFilterSqlParams(int $filter): string
    {
        $now = new DateTime();
        switch ($filter) {
            case 1:
                $range = '5 years';
                break;
            case 2:
                $range = '1 year';
                break;
            case 3:
                $range = '6 months';
                break;
            case 4:
                $range = '3 months';
                break;
            case 5:
                $range = '30 days';
                break;
            default:
                $range = '';
        }
        if (!empty($range)) {
            $startDate = $now->sub(
                DateInterval::createFromDateString($range)
            );
            $sql = " AND `o`.`date_purchased` > '" .
            $startDate->format('Y-m-d h:i:s') . "'";
        }
        return $sql ?? "";
    }

    private function getStatusFilterSqlParams(array $statusFilters): string
    {
        $paramList = [];
        $lpuSql    = "SELECT COUNT(`op`.`products_id`)
            FROM `orders_products` `op`
            WHERE `op`.`orders_id` = `o`.`orders_id`
            AND (`op`.`scac_code` = 'LPU')";

        if (count($statusFilters) > 0) {
            foreach ($statusFilters as $filter) {
                switch ($filter) {
                    case 1: //Not Yet Shipped
                        /**
                         * Order has no Local Pickup Items
                         * Order is Backordered or Processing
                         */

                        $keys = Status::STATUS_DISPLAY[
                            Status::STATUS_BACKORDERED
                        ]['keys'];
                        $keyStr   = implode(',', $keys);
                        $statuses = $keyStr . ',';

                        $keys = Status::STATUS_DISPLAY[
                            Status::STATUS_PROCESSING
                        ]['keys'];
                        $keyStr = implode(',', $keys);
                        $statuses .= $keyStr;

                        $param = "(`o`.`orders_status`
                        IN (" . $statuses . ")
                        AND (" . $lpuSql . ") = 0)";

                        break;
                    case 2: //Local Pickup
                        $param = "(" . $lpuSql . " > 0)";
                        break;
                    case 3: //Shipped
                        $keys = Status::STATUS_DISPLAY[
                            Status::STATUS_SHIPPED
                        ]['keys'];

                        $statuses = implode(',', $keys);
                        $param    = "(`o`.`orders_status` IN (" . $statuses . "))";
                        break;
                    case 4: //Canceled
                        $keys = Status::STATUS_DISPLAY[
                            Status::STATUS_CANCELED
                        ]['keys'];

                        $statuses = implode(',', $keys);
                        $param    = "(`o`.`orders_status` IN (" . $statuses . "))";
                        break;
                    default: //No filter selected
                        $param = "";
                }
                if (!empty($param)) {
                    $paramList[] = $param;
                }
            }
            if (count($paramList) > 0) {
                $sql = " AND (" . implode(' OR ', $paramList) . ")";
            }
        }
        return $sql ?? "";
    }

    private function getStatusFiltersFromURL(): array
    {
        $params = $this->request->getSafeString('status') ?? '';
        if (!empty($params)) {
            $filters = explode('|', $params);
            $filters = array_map('intval', $filters);
        }
        return $filters ?? [];
    }

    private function getProductList(Order $order): ViewsCollection
    {
        $list          = new ViewsCollection();
        $orderStoresId = $order->getData()->getStoreId();
        $orderItems    = $order->getItems();
        $classList     = implode(' ', [
            'text-ferg-blue-link',
            'list-group-item',
            'border-0',
            'px-2',
            'px-xl-3'
        ]);

        foreach ($orderItems as $item) {
            $prodId  = $item->getProductId();
            $product = $this->productsFactory->getProduct(
                $prodId
            );
            $prodLink = $this->getProductLink(
                $product->getUrl(),
                $orderStoresId
            );

            $linkList   = new ViewsCollection();
            $buyItAgain = $this->getBuyItAgainButton(
                $prodId,
                $product->getStatusId()
            );
            if (!empty($buyItAgain)) {
                $linkList->append(
                    new Data('tags/lists/item.php', [
                        'class' => $classList . ' ps-0 ps-xl-3',
                        'text'  => $buyItAgain
                    ])
                );
            }

            $linkList->append(
                $this->review->createButtonTemplate(
                    $prodId,
                    $classList,
                    '',
                    'Write a Review',
                    1,
                    'a'
                )
            );
            //Only Show for Picked Up and Shipped Items
            $statusLabel = $this->status->getStatusLabelForOrderProduct($item);
            if (
                $this->status->isShipped($statusLabel)
                || $this->status->isPickedUp($statusLabel)
            ) {
                $linkList->append(
                    new Data('tags/lists/item.php', [
                        'class' => $classList,
                        'text'  => new Data('tags/anchor.php', [
                            'content' => 'Start a Return',
                            'class'   => 'text-ferg-blue-link',
                            'href'    => $this->store->getUrl() . self::RETURNS_LINK
                        ])
                    ])
                );
            }
            $sideLinks = new Data('tags/lists/unordered.php', [
                'class' => implode(' ', [
                    'list-group',
                    'list-group-flush',
                    'mb-xl-3',
                    'd-flex',
                    'flex-row',
                    'flex-xl-column'
                ]),
                'items' => $linkList
            ]);
            $list->append(new Data(self::TMPL_DIR . 'order-product.php', [
                'qty'       => $item->getProductQty(),
                'name'      => $item->getProductName(),
                'price'     => $item->getProductCost()->getString(),
                'prodLink'  => $prodLink,
                'sideLinks' => $sideLinks,
                'prodImg'   => new Data(self::TMPL_DIR . 'product-img.php', [
                    'img' => $this->getProductImageSrc(
                        $product->getImageData()['bimage'],
                        $orderStoresId
                    ),
                    'name' => $item->getProductName(),
                    'link' => $prodLink
                ]),
                'productStatusSection' => $this->status->getProductStatusSection(
                    $order,
                    $item
                )
            ]));
        }
        return $list;
    }

    private function getTopLinks(
        Order $order
    ): ViewsCollection {
        $linkList = new ViewsCollection();
        $linkList->append(
            new Data('tags/anchor.php', [
                'content' => 'Order Details',
                'class'   => 'text-ferg-blue-link',
                'href'    => self::ORDER_DETAILS_LINK_BASE . $order->getId()
            ])
        );
        $statusLabel = $this->status->getStatusLabelForOrder($order);
        //Show if not Cancelled or Shipped or Picked Up
        if (
            !$this->status->isCanceled($statusLabel)
            && !$this->status->isShipped($statusLabel)
            && !$this->status->isPickedUp($statusLabel)
        ) {
            $linkList->append(
                new Data('tags/anchor.php', [
                    'content' => 'Cancel Order',
                    'class'   => 'text-ferg-blue-link',
                    'href'    => $this->store->getUrl() . self::CANCEL_ORDER_LINK
                ])
            );
        }
        $linkList->append(
            new Data('tags/anchor.php', [
                'content' => 'Print Receipt',
                'class'   => 'text-ferg-blue-link',
                'target'  => '_blank',
                'href'    => $this->getOrderReceiptLink($order)
            ])
        );
        $linkList->append(
            new Data('tags/anchor.php', [
                'content' => 'Help',
                'class'   => 'text-ferg-blue-link',
                'href'    => $this->store->getUrl() . self::HELP_LINK
            ])
        );

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
                        $classList .= ' ps-lg-3';
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
                        'mb-3',
                    ]),
                    'items' => $items
                ])
            );
        }
        return $ulList;
    }
}