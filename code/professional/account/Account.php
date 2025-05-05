<?php
namespace Pedstores\Ped\Models\Customers;

use Pedstores\Ped\Models\Customers\Customer;
use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Models\Sites\Breadcrumb;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Filesystem\Files\Reader;
use Pedstores\Ped\Views\Collection as ViewsCollection;
use Pedstores\Ped\Models\Customers\Accounts\Components\Navigation;

class Account
{
    private Breadcrumb $breadcrumb;
    private Store $store;
    private Reader $files;
    private Navigation $nav;
    private Customer $customer;

    public function __construct(
        Breadcrumb $breadcrumb,
        Store $store,
        Reader $files,
        Navigation $nav,
        Customer $customer
    ) {
        $this->breadcrumb = $breadcrumb;
        $this->store      = $store;
        $this->files      = $files;
        $this->nav        = $nav;
        $this->customer   = $customer;
    }

    public function getPageContent(
        Data $content,
        int $customerId
    ): Data {
        $default = $this->store->getUrl(Store::DEFAULT);
        $scripts = new ViewsCollection();
        $scripts->append(new Data('/tags/script.php', [
            'src' => $default . $this->files->autoVersion('/js/review-modal.js'),
        ]));
        $scripts->append(new Data('/tags/script.php', [
            'src' => $default . $this->files->autoVersion('/js/review-modal-init.js'),
        ]));

        return new Data('account/basic.php', [
            'pageContent' => $content,
            'leftRail'    => $this->nav->createLeftRailNav(
                $this->customer->isInstaller($customerId),
                $this->customer->hasMroPricing($customerId)
            ),
            'scripts' => $scripts
        ]);
    }

    public function populateBreadcrumb(string $key): void
    {
        //Add link to Home Page
        $this->breadcrumb->add('Home', $this->store->getUrl());

        //Add link to My Account (unless that is current page)
        if ($key != Navigation::KEY_ACCOUNT) {
            $data = $this->nav->getPageDataByKey(Navigation::KEY_ACCOUNT);
            $this->breadcrumb->add($data->title, $data->href);
        }

        //Check for parents
        $parents = $this->nav->getSubPageStructure($key);
        if (count($parents) > 0) {
            foreach ($parents as $parent) {
                $data = $this->nav->getPageDataByKey($parent);
                $this->breadcrumb->add($data->title, $data->href);
            }
        }

        //Add current page
        $this->breadcrumb->add($this->getPageTitleByKey($key), '');
    }

    public function getPageTitleByKey(string $key): string
    {
        $result = '';
        if (!empty($key)) {
            $data   = $this->nav->getPageDataByKey($key);
            $result = $data->title ?? '';
        }
        return $result;
    }

    public function getPageUrlByKey(string $key): string
    {
        $result = '';
        if (!empty($key)) {
            $data   = $this->nav->getPageDataByKey($key);
            $result = $data->href ?? '';
        }
        return $result;
    }
}