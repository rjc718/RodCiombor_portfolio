<?php
namespace Pedstores\Ped\Models\Customers\Accounts\Components;

use Pedstores\Ped\Models\Request;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Models\Sites\Configuration;
use Pedstores\Ped\Models\Cart\PedCash;
use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Views\Collection as ViewsCollection;

class Navigation
{
    public const KEY_ACCOUNT         = 'account';
    public const KEY_ADDRESS         = 'address';
    public const KEY_HISTORY         = 'history';
    public const KEY_DETAILS         = 'details';
    public const KEY_PREFERENCES     = 'preferences';
    public const KEY_REWARDS         = 'rewards';
    public const KEY_MRO_PRICING     = 'mroPricing';
    public const KEY_INSTALLER_INFO  = 'installerInfo';
    public const KEY_INSTALLER_LEADS = 'installerLeads';
    public const KEY_SUBSCRIPTIONS   = 'subscriptions';
    public const KEY_TAX_EXEMPTION   = 'taxExemption';
    private const BASE_DIR           = '/customers/account';
    private Request $request;
    private Store $store;
    private Configuration $config;
    private PedCash $pedCash;

    public function __construct(
        Request $request,
        Store $store,
        Configuration $config,
        PedCash $pedCash
    ) {
        $this->request = $request;
        $this->store   = $store;
        $this->config  = $config;
        $this->pedCash = $pedCash;
    }

    public function getPageData(): object
    {
        return (object) [
            self::KEY_ACCOUNT => (object) [
                'title' => 'My Account',
                'href'  => '/account.php'
            ],
            self::KEY_ADDRESS => (object) [
                'title' => 'Addresses',
                'href'  => '/address_book.php'
            ],
            self::KEY_HISTORY => (object) [
                'title' => 'Orders',
                'href'  => self::BASE_DIR . '/history'
            ],
            self::KEY_DETAILS => (object) [
                'title' => 'Order Details',
                'href'  => self::BASE_DIR . '/details'
            ],
            self::KEY_PREFERENCES => (object) [
                'title' => 'Email Preferences',
                'href'  => '/email-preferences.php'
            ],
            self::KEY_REWARDS => (object) [
                'title' => $this->pedCash->getType(),
                'href'  => '/rewards.php'
            ],
            self::KEY_MRO_PRICING => (object) [
                'title' => 'MRO Pricing',
                'href'  => '/account_mro_pricing.php'
            ],
            self::KEY_INSTALLER_INFO => (object) [
                'title' => 'Installer Information',
                'href'  => '/installer_edit.php'
            ],
            self::KEY_INSTALLER_LEADS => (object) [
                'title' => 'Installer Leads',
                'href'  => '/installer-leads.php'
            ],
            self::KEY_SUBSCRIPTIONS => (object) [
                'title' => 'Subscriptions',
                'href'  => '/account-subscriptions.php'
            ],
            self::KEY_TAX_EXEMPTION => (object) [
                'title' => 'Tax Exemption',
                'href'  => '/account-tax-exemption.php'
            ]
        ];
    }

    public function getPageDataByKey(string $key): ?object
    {
        $data = $this->getPageData();
        return $data->$key ?? null;
    }

    /**
     * For subpages, we need to know the parent pages
     * This will be used to populate the breadcrumb
     * It will also determine the URL of the Back Button
     */
    public function getSubPageStructure(string $key): array
    {
        $parents = [];
        $data    = [
            self::KEY_DETAILS => [
                'parents' => [self::KEY_HISTORY]
            ]
        ];
        if (array_key_exists($key, $data)) {
            $parents = $data[$key]['parents'];
        }
        return $parents;
    }

    public function createBackButton(string $key): ?Data
    {
        $tmpl = null;

        if ($key != self::KEY_ACCOUNT) {
            $parents = $this->getSubPageStructure($key);
            if (count($parents) > 0) {
                $data = $this->getPageDataByKey($parents[0]);
            } else {
                $data = $this->getPageDataByKey(self::KEY_ACCOUNT);
            }
            $tmpl = new Data('tags/division.php', [
                'class'   => 'mx-3 mx-lg-0 mb-3 mb-md-6',
                'content' => new Data('tags/anchor.php', [
                    'class'   => 'bi bi-chevron-left text-ferg-blue-link',
                    'content' => 'Back',
                    'href'    => $data->href ?? ''
                ])
            ]);
        }
        return $tmpl;
    }

    public function createLeftRailNav(
        bool $installer,
        bool $mro
    ): Data {
        $links = new ViewsCollection();
        foreach ($this->getLeftRailData() as $key => $data) {
            $styles = (object) [
                'background' => '',
                'text'       => '',
                'subtext'    => 'class="link-secondary fs-8"',
                'icon'       => $data->iconClass
            ];
            if ($data->isActive) {
                $styles = (object) [
                    'background' => ' bg-light"',
                    'text'       => 'class="fw-bold"',
                    'subtext'    => 'class="text-reset fs-8"',
                    'icon'       => $data->iconClass
                ];
            }

            $icon = null;
            if (!empty($data->icon)) {
                $icon = new Data('tags/image.php', [
                    'height' => 25,
                    'width'  => 25,
                    'alt'    => $data->text,
                    'src'    => $data->icon
                ]);
            }

            if (
                ($key === self::KEY_SUBSCRIPTIONS && !$this->config->get('SUBSCRIPTION_STATUS'))
                || ($key === self::KEY_MRO_PRICING && !$mro)
                || (in_array($key, [
                    self::KEY_INSTALLER_INFO,
                    self::KEY_INSTALLER_LEADS
                ]) && !$installer)
            ) {
                continue;
            }

            $links->append(
                new Data('account/nav-item.php', [
                    'href'    => $data->href,
                    'text'    => $data->text,
                    'subText' => $data->subText,
                    'icon'    => $icon,
                    'styles'  => $styles
                ])
            );
        }
        return new Data('account/left-nav.php', ['links' => $links]);
    }

    private function getLeftRailData(): object
    {
        $path = $this->store->getUrl(Store::DEFAULT) . '/images/icons/';
        $data = $this->getPageData();

        $menu = (object) [ //Dashboard
            self::KEY_ACCOUNT => (object) [
                'href'      => $data->account->href,
                'isActive'  => false,
                'text'      => 'Dashboard',
                'subText'   => 'View all account services',
                'icon'      => $path . 'speedometer-icon.svg',
                'iconClass' => ''
            ],
            self::KEY_ADDRESS => (object) [ //Address book
                'href'      => $data->address->href,
                'isActive'  => false,
                'text'      => $data->address->title,
                'subText'   => 'Manage shipping & billing addresses',
                'icon'      => $path . 'address-book-icon.svg',
                'iconClass' => ''
            ],
            self::KEY_HISTORY => (object) [ //Order History
                'href'      => $data->history->href,
                'isActive'  => false,
                'text'      => 'Order History',
                'subText'   => 'View & manage orders',
                'icon'      => $path . 'hand-truck-icon.svg',
                'iconClass' => ''
            ],
            self::KEY_PREFERENCES => (object) [ //Email Preferences
                'href'      => $data->preferences->href,
                'isActive'  => false,
                'text'      => $data->preferences->title,
                'subText'   => 'View & manage email preferences',
                'icon'      => $path . 'envelope-open.svg',
                'iconClass' => ''
            ],
            self::KEY_REWARDS => (object) [ //Rewards
                'href'      => $data->rewards->href,
                'isActive'  => false,
                'text'      => $data->rewards->title,
                'subText'   => 'View ' . $this->pedCash->getType() . ' rewards',
                'icon'      => '',
                'iconClass' => ' ' . $this->pedCash->getIconClass()
            ],
            self::KEY_MRO_PRICING => (object) [ //MRO Pricing
                'href'      => $data->mroPricing->href,
                'isActive'  => false,
                'text'      => $data->mroPricing->title,
                'subText'   => 'View branch pricing',
                'icon'      => $path . 'tag.svg',
                'iconClass' => ''
            ],
            self::KEY_INSTALLER_INFO => (object) [ //Installer Information
                'href'      => $data->installerInfo->href,
                'isActive'  => false,
                'text'      => $data->installerInfo->title,
                'subText'   => 'View & manage installer information',
                'icon'      => $path . 'tools.svg',
                'iconClass' => ''
            ],
            self::KEY_INSTALLER_LEADS => (object) [ //Installer Leads
                'href'      => $data->installerLeads->href,
                'isActive'  => false,
                'text'      => $data->installerLeads->title,
                'subText'   => 'View & manage installer leads',
                'icon'      => $path . 'noun-people.svg',
                'iconClass' => ''
            ],
            self::KEY_SUBSCRIPTIONS => (object) [ //Subscriptions
                'href'      => $data->subscriptions->href,
                'isActive'  => false,
                'text'      => $data->subscriptions->title,
                'subText'   => 'View & manage recurring subscriptions',
                'icon'      => $path . 'subscription-icon.svg',
                'iconClass' => ''
            ],
            self::KEY_TAX_EXEMPTION => (object) [ //Tax Exemption
                'href'      => $data->taxExemption->href,
                'isActive'  => false,
                'text'      => $data->taxExemption->title,
                'subText'   => 'View & manage taxes',
                'icon'      => $path . 'receipt.svg',
                'iconClass' => ''
            ]
        ];
        foreach ($menu as $key => $item) {
            $item->isActive = $this->isActive($item->href);

            /**
             * Extra logic for pages with subpages
             * Order History link should also be highlighted if on Order Details page
             */
            if (
                $key == self::KEY_HISTORY && (
                    $this->isActive($data->history->href) || 
                    $this->isActive($data->details->href)
                )
            ) {
                $item->isActive = true;
            }
        }
        return $menu;
    }

    private function isActive(string $page): bool
    {
        /**
         * Highlight left nav rail link is page active
         * Active if $data->key->href is in the current URL
         * Accounts for extra params such as order id
         */
        return strpos($this->request->getCurrentPageUrl(), $page) !== false;
    }
}