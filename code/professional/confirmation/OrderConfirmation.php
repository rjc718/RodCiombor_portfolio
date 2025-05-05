<?php
namespace Pedstores\Ped\Controllers\Legacy;

use DateTime;
use Pedstores\Ped\Controllers\Legacy\PageController;
use Pedstores\Ped\Models\Legacy\Order;
use Pedstores\Ped\Models\Legacy\OrderConfirmationData;
use Pedstores\Ped\Views\PhpTemplateView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Pedstores\Ped\Views\Html\Footer;

class OrderConfirmation extends PageController
{
    protected $model;
    private $view;
    private $footer;

    public function __construct(
        OrderConfirmationData $model,
        PhpTemplateView $view,
        Footer $footer
    ) {
        $this->model  = $model;
        $this->view   = $view;
        $this->footer = $footer;
    }

    public function execute($params = []): void
    {
        if (!$this->model->isRequestValid($params['uuid'])) {
            $errMsg = 'There was a problem with displaying your order information.  Please try again.';
            if ($this->model->isNewCart()) {
                $cartUrl = '/shopping/cart/error/message/';
            } else {
                $cartUrl = '/shopping_cart.php?error_message=';
            }
            $response = new RedirectResponse($cartUrl . $errMsg);
        } else {
            $response = $this->displayPage($params['uuid']);
        }
        $response->send();
    }

    protected function displayPage($uuid): Response
    {
        $headers              = [];
        $scripts              = [];
        $userScriptDataInputs = [];

        $rightRailView     = '';
        $lpuItemsView      = '';
        $shippingItemsView = '';
        $bottomSectionView = '';
        $topSectionView    = '';

        $orderId = $this->model->getSessionValue($uuid);
        $order   = Order::getInstance($orderId);

        $this->model->handleGuestCheckout($order);

        $this->model->populateBreadcrumb();

        //Check for installation requests and send emails
        if (!$this->model->isNewCart()) {
            include '/var/www/vhosts/ped.com/includes/installer-checkout-success.php';
        }

        //Get All Bonus Items associated with each Order Product
        //Add the product_id to a master list
        $this->model->setBonusItemIdList($order->products);

        //Purge Bonus Items that were saved as Order Products in checkout_process.php
        //We will use the promo_items table to determine what bonus items are associated with each order product
        $order->products = $this->model->purgeBonusItemOrderProducts($order->products);

        //Throw the products in arrays for Shipped Products, LPU Products, Warranties and Installers
        $this->model->groupOrderProductTypes($order->products);

        //Top Section - Order Summary
        $topSectionView = $this->renderTopSection($order, $uuid);

        //Right Rail - X-Sell Items
        $rightRailView = $this->renderRightRail($order->products);

        //Shipped Products
        $shippingItemsView = $this->renderShippedItemsSection();

        //LPU Products
        $lpuItemsView = $this->renderLpuItemsSection();

        //Bottom Section - PED Cash and Totals
        $bottomSectionView = $this->renderBottomSection($order->totals);

        //User Script Data Points
        $userScriptDataInputs = $this->model->getUserScriptData($order);

        //Add all Views to Main Template
        $viewList = [
            'topSectionView'       => $topSectionView,
            'rightRailView'        => $rightRailView,
            'lpuItemsView'         => $lpuItemsView,
            'shippingItemsView'    => $shippingItemsView,
            'bottomSectionView'    => $bottomSectionView,
            'userScriptDataInputs' => $userScriptDataInputs
        ];

        $body = $this->view->render('orders/confirmation/page.php', $viewList);

        if ($this->model->getOrderStoreId() != 16) {
            $headers[] = $this->view->render(
                'tags/link.php',
                [
                    'rel'  => 'stylesheet',
                    'href' => $this->autoVersion('/css/dist/common.css', true)
                ]
            );
        }
        $headers[] = $this->view->render(
            'tags/link.php',
            [
                'rel'  => 'stylesheet',
                'href' => $this->autoVersion(
                    '/css/dist/order-confirmation-page.css',
                    true
                )
            ]
        );

        $scripts = [$this->view->render(
            'tags/script.php',
            [
                    'src' => $this->autoVersion(
                        '/js/order-confirmation.js',
                        true
                    )
            ]
        )
        ];

        return new Response(
            $this->view->render(
                'pages/legacy.php',
                [
                    'title'   => 'Order Confirmation @'. $this->model->getOrderStoreName(),
                    'headers' => implode("\n", $headers),
                    'head'    => $this->getHeader(),
                    'body'    => $body,
                    'foot'    => $this->footer->render($orderId),
                    'scripts' => implode("\n", $scripts)
                ]
            )
        );
    }

    private function renderRebates(array $item): string
    {
        $output     = '';
        $rebateInfo = $this->model->getRebateInfo($item['id'] ?? 0);

        if (count($rebateInfo) > 0) {
            $params = $this->model->getRowViewParams('rebate', $rebateInfo);
            $output = $this->view->render('orders/confirmation/items/extra_rows.php', $params);
        }
        return $output;
    }

    private function renderWarranty(array $item): string
    {
        $output = '';

        $currentModel = (!empty($item['products_model_alt']) ? $item['products_model_alt'] : ($item['model'] ?? ''));
        $warrantyInfo = $this->model->getWarrantyInfo($currentModel ?? '');

        if (count($warrantyInfo) > 0) {
            $params = $this->model->getRowViewParams('warranty', $warrantyInfo);
            $output = $this->view->render('orders/confirmation/items/extra_rows.php', $params);
        }
        return $output;
    }

    private function renderSubscriptions(array $item): string
    {
        $output = '';

        $params = $this->model->getRowViewParams('subscription', $item);
        if (count($params) > 0) {
            $this->model->setHasSubsValue(true);
            $output = $this->view->render('orders/confirmation/items/extra_rows.php', $params);
        }

        return $output;
    }

    private function renderInstallation(array $item): string
    {
        $installInfo = [];
        $output      = '';

        if (count($this->model->getInstallerProducts()) > 0) {
            $installInfo = $this->model->getInstallerInfo($item['id'] ?? 0);
        }
        if (count($installInfo) > 0) {
            $params = $this->model->getRowViewParams('installer', $installInfo);
            $output = $this->view->render('orders/confirmation/items/extra_rows.php', $params);
        }
        return $output;
    }

    private function renderBottomSection(array $totals): string
    {
        //Bottom Left - PED Cash (hide if empty)
        $cashInfo = [];

        if ($this->model->getPEDCashTotal() > 0) {
            $cashInfo = $this->model->getPedCashInfo($this->model->getPEDCashTotal());
        }
        if (count($cashInfo) > 0) {
            $params            = $this->model->getSectionViewParams('cashEarned', $cashInfo);
            $bottomLeftContent = $this->view->render('orders/confirmation/cash_earned.php', $params);
        } else {
            $bottomLeftContent = '';
        }

        //Bottom Right - Order Totals
        $bottomRightContent = '';
        $totalsInfo         = $this->model->getOrderTotalsInfo($totals);
        $params             = $this->model->getSectionViewParams('orderTotals', $totalsInfo);
        $bottomRightContent = $this->view->render('orders/confirmation/totals.php', $params);

        //Enter content views into main view
        $params = [
            'bottomLeftContent'  => $bottomLeftContent,
            'bottomRightContent' => $bottomRightContent
        ];
        $bottomSectionView = $this->view->render('orders/confirmation/sections/bottom.php', $params);

        return $bottomSectionView;
    }

    private function buildItemListParams(array $input, $isKit = false, $isLpu = false): array
    {
        $params               = [];
        $listParams           = [];
        $productExtraRowsList = [];

        if ($isKit) {
            $item       = $input['info'];
            $components = $input['components'];
            $dc_number  = $components[0]['dc_number'];
        } else {
            $item      = $input;
            $dc_number = $item['dc_number'];
        }

        //Bonus Items
        $bonusItems = $this->model->getBonusItemInfoById(intval($item['id'] ?? null));

        if (count($bonusItems) > 0) {
            for ($i = 0; $i < count($bonusItems); $i++) {
                $viewParams = $this->model->getRowViewParams('bonusItem', $bonusItems[$i]);

                $qty = $bonusItems[$i]['bonus_item_qty'] * (!empty($item['quantity']) ? $item['quantity'] : 0);

                $viewParams['qty']          = $qty;
                $viewParams['bonusItemMsg'] = $this->model->getBonusItemMsg($qty);
                $viewParams['itemMsg']      = $bonusItems[$i]['bonus_text'];
                $viewParams['isWarranty']   = ($bonusItems[$i]['id'] == 10342 ? true : false);

                $bonusItem = $this->view->render('orders/confirmation/items/bonus.php', $viewParams);
                array_push($productExtraRowsList, $bonusItem);
            }
        }

        //Rebate
        $rebate = $this->renderRebates($item);
        array_push($productExtraRowsList, $rebate);

        //Warranty
        $warranty = $this->renderWarranty($item);
        array_push($productExtraRowsList, $warranty);

        //Installers
        $installer = $this->renderInstallation($item);
        array_push($productExtraRowsList, $installer);

        //Subscriptions - Double check if shoudl be $kit or $kit['info']
        $subs = $this->renderSubscriptions($item);
        array_push($productExtraRowsList, $subs);

        //Kits Components
        if ($isKit) {
            $this->model->buildKitComponentData($components);
            $viewParams  = $this->model->getSectionViewParams('kitProducts');
            $kitProducts = $this->view->render('orders/confirmation/items/kits.php', $viewParams);

            $item['qty']              = 0;
            $item['final_price']      = $this->model->getTotalKitPrice();
            $item['manufacturers_id'] = 0;
        } else {
            $kitProducts = '';
        }

        if ($isLpu) {
            //Shipping Info - Branch Name, Address, City, State, Zip

            $lpuBranchInfo = $this->model->getLpuBranchInfo($dc_number);

            $params = [
                'branch_name'    => $lpuBranchInfo['branch_name'],
                'branch_address' => $lpuBranchInfo['branch_address'],
                'branch_city'    => $lpuBranchInfo['branch_city'],
                'branch_state'   => $lpuBranchInfo['branch_state'],
                'branch_zip'     => $lpuBranchInfo['branch_zip']
            ];
            $shipInfo = $this->model->getLpuShippingMessage($params);
        } else {
            //For each kit, use the ship date of the first component since they should all be the same
            if ($isKit) {
                $estShipDate = new DateTime($components[0]['orig_estimated_ship']);
            } else {
                $estShipDate = new DateTime($item['orig_estimated_ship']);
            }

            $params = [
                'prodId'      => ($item['id'] ?? 0),
                'estShipDate' => $estShipDate->format('M j')
            ];
            $shipInfo = $this->view->renderTemplate(
                $this->model->getShippingStatusMessage($params)
            );
        }

        $listParams                     = $this->model->getProductRowViewParams($item);
        $listParams['shipInfo']         = $shipInfo;
        $listParams['kitProducts']      = $kitProducts;
        $listParams['productExtraRows'] = $productExtraRowsList;
        $listParams['isLpuProduct']     = $isLpu;

        return $listParams;
    }

    private function renderShippedItemsSection(): string
    {
        $output   = '';
        $itemList = [];
        $products = $this->model->getShippedProductList();

        if (count($products) > 0) {
            foreach ($products as $item) {
                if (empty($item['is_kit'])) {
                    $itemListParams = $this->buildItemListParams($item);
                    array_push($itemList, $itemListParams);
                }
            }

            //Add Kits to itemList
            $kitData = $this->model->getKitData();

            foreach ($kitData as $kit) {
                $itemListParams = $this->buildItemListParams($kit, true);
                array_push($itemList, $itemListParams);
            }

            //Build Shipped Products Rows
            if (count($itemList) > 0) {
                $viewParams     = $this->model->getSectionViewParams('itemListHeader');
                $itemListHeader = $this->view->render('orders/confirmation/items/list_header.php', $viewParams);
                $params         = [
                    'itemListHeader' => $itemListHeader,
                    'itemList'       => $itemList,
                    'hasLpuProducts' => false
                ];
                $output = $this->view->render('orders/confirmation/items/list.php', $params);
            }
        }
        return $output;
    }

    private function renderLpuItemsSection(): string
    {
        $output  = '';
        $pickups = $this->model->getPickupsList();

        if (count($pickups) > 0) {
            $itemList = [];

            foreach ($pickups as $item) {
                if (empty($item['is_kit'])) {
                    $itemListParams = $this->buildItemListParams($item, false, true);
                    array_push($itemList, $itemListParams);
                }
            }

            //Add kits to itemList
            $lpuKitData = $this->model->getLpuKitData();

            foreach ($lpuKitData as $kit) {
                $itemListParams = $this->buildItemListParams($kit, true, true);
                array_push($itemList, $itemListParams);
            }

            //Create LPU item rows
            if (count($itemList) > 0) {
                $viewParams     = $this->model->getSectionViewParams('lpuItemListHeader');
                $itemListHeader = $this->view->render('orders/confirmation/items/list_header.php', $viewParams);

                $params = [
                    'itemListHeader' => $itemListHeader,
                    'itemList'       => $itemList,
                    'hasLpuProducts' => true
                ];
                $output = $this->view->render('orders/confirmation/items/list.php', $params);
            }
        }
        return $output;
    }

    private function renderRightRail(array $products): string
    {
        $output         = '';
        $rightRailItems = [];
        $xSellItems     = [];
        $xSellItems     = $this->model->getXsellItems($products);

        if (count($xSellItems) > 0) {
            foreach ($xSellItems as $xsell) {
                $viewParams = $this->model->getSectionViewParams('xsell', $xsell);
                array_push($rightRailItems, $viewParams);
            }
            $params = ['rightRailItems' => $rightRailItems];
            $output = $this->view->render('orders/confirmation/sections/right.php', $params);
        }
        return $output;
    }

    private function renderTopSection(Order $order, string $uuid): string
    {
        $output     = '';
        $viewParams = $this->model->getOrderSummaryViewParams($order, $uuid);
        $output     = $this->view->render('orders/confirmation/sections/top.php', $viewParams);
        return $output;
    }
}
