<?php
namespace Pedstores\Ped\Models\Customers\Accounts\Components;

use Pedstores\Ped\Models\Order;
use Pedstores\Ped\Models\Products\Status;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Models\Cart;

class Helpers
{
    public const RECORDS_PER_PAGE  = 5;
    public const CANCEL_ORDER_LINK = '/cancellation.php';
    public const HELP_LINK         = '/contact_us.php';
    public const RETURNS_LINK      = '/returns.php';
    protected $store;
    protected $cart;

    public function __construct(Store $store, Cart $cart)
    {
        $this->store = $store;
        $this->cart  = $cart;
    }

    public function getOrderReceiptLink(Order $order): string
    {
        $data = $order->getData();
        $orderId = $data->getId() ?? '';

        $customerContact = $order->getCustomerContact();
        $location        = $customerContact ? $customerContact->getLocation() : null;
        $postalCode      = $location ? $location->getPostalCode() : '';

        $hash       = $data->getEmailHash()  ?? '';
        $customerId = $data->getCustomerId() ?? '';

        return sprintf(
            '%s/printorder.php?order_id=%s&zip=%s&email=%s&customer_id=%s',
            $this->store->getUrl($data->getStoreId()),
            $orderId,
            $postalCode,
            $hash,
            $customerId
        );
    }

    public function getStoreUrlForLegacyOrderProducts(int $orderStoresId): string
    {
        $storeId   = Store::DEFAULT;
        $allStores = $this->store->getAllStoreIds(true);
        foreach ($allStores as $store) {
            if ($orderStoresId == $store) {
                $storeId = $orderStoresId;
                break;
            }
        }
        return $this->store->getUrl($storeId);
    }

    public function getProductImageSrc(string $bimage, int $storeId): string
    {
        return sprintf(
            '%s/products-image/110/%s',
            $this->store->getUrl($storeId, true),
            $bimage ?: 'product_0_125.gif'
        );
    }

    public function getProductLink(string $prodUrl, int $storeId): string
    {
        return $this->store->getUrl($storeId, true) . $prodUrl;
    }

    public function getSidebarHeader(string $title): Data
    {
        return new Data(
            'account/widgets/offcanvas/orders/header.php',
            ['title' => $title]
        );
    }

    public function getBuyItAgainButton(int $prodId, int $statusId): ?Data
    {
        $output = null;
        if ($statusId === Status::STATUS_AVAILABLE) {
            if ($this->cart->hasItem($prodId)) {
                $output = new Data('tags/anchor.php', [
                    'content' => 'View In Cart',
                    'class'   => 'text-ferg-blue-link',
                    'href'    => '/shopping/cart'
                ]);
            } else {
                $output = new Data(
                    'tags/button.php',
                    [
                        'class' => 'btn btn-link p-0 text-ferg-blue-link',
                        'type'  => 'button',
                        'attrs' => [
                            'data-product-id' => $prodId,
                            'data-quantity'   => '1',
                            'data-action'     => 'account:buyitagain',
                        ],
                        'text' => 'Buy It Again'
                    ]
                );
            }
        }
        return $output;
    }
}