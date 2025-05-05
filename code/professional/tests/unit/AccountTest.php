<?php
namespace Pedstores\Ped\Tests\Unit\Models\Customers;

use Pedstores\Ped\Filesystem\Files\Reader;
use Pedstores\Ped\Models\Customers\Account;
use Pedstores\Ped\Models\Customers\Accounts\Components\Navigation;
use Pedstores\Ped\Models\Request;
use Pedstores\Ped\Models\Sites\Breadcrumb;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Views\Collection;
use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Models\Cart\PedCash;
use Pedstores\Ped\Models\Customers\Customer;
use Pedstores\Ped\Models\Sites\Configuration;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    private $breadcrumb;
    private $store;
    private $files;
    private $request;
    private $config;
    private $pedCash;
    private $customer;

    public function setUp(): void
    {
        $this->breadcrumb = $this->createMock(Breadcrumb::class);
        $this->store      = $this->createMock(Store::class);
        $this->files      = $this->createMock(Reader::class);
        $this->request    = $this->createMock(Request::class);
        $this->config     = $this->createMock(Configuration::class);
        $this->pedCash    = $this->createMock(PedCash::class);
        $this->customer   = $this->createMock(Customer::class);
        parent::setUp();
    }

    public function testCanGetPageContent(): void
    {
        $nav = new Navigation(
            $this->request,
            $this->store,
            $this->config,
            $this->pedCash
        );

        /**
         * $content is a placeholder for the actual content
         * In future can check if it is an instance of History, Details, etc.
         */
        $content = new Data('tags/division.php', [
            'content' => 'This is the content'
        ]);
        $unit = new Account(
            $this->breadcrumb,
            $this->store,
            $this->files,
            $nav,
            $this->customer
        );

        $customer_id = rand(1, 1000);

        $result = $unit->getPageContent(
            $content,
            $customer_id
        );
        $this->assertInstanceOf(Data::class, $result);

        //In future can check if it is an instance of History, Details, etc.
        $this->assertInstanceOf(Data::class, $result->getData()['pageContent']);

        $leftRail = $result->getData()['leftRail'];
        $this->assertInstanceOf(Data::class, $leftRail);

        $links = $leftRail->getData()['links'];
        $this->assertInstanceOf(Collection::class, $links);
        $this->assertGreaterThan(0, count($links));

        foreach ($links as $link) {
            $this->assertInstanceOf(Data::class, $link);
            $data = $link->getData();

            $this->assertIsString($data['href']);
            $this->assertIsString($data['text']);
            $this->assertIsString($data['subText']);
            $this->assertThat(
                $data['icon'],
                $this->logicalOr(
                    $this->isInstanceOf(Data::class),
                    $this->isNull()
                )
            );

            $styles = $data['styles'];
            $this->assertIsObject($styles);
            $this->assertIsString($styles->background);
            $this->assertIsString($styles->text);
            $this->assertIsString($styles->subtext);
            $this->assertIsString($styles->icon);
        }

        $scripts = $result->getData()['scripts'];
        $this->assertInstanceOf(Collection::class, $scripts);
        $this->assertGreaterThan(0, count($scripts));
        foreach ($scripts as $script) {
            $this->assertInstanceOf(Data::class, $script);
            $this->assertIsString($script->getData()['src']);
        }
    }

    public function testCanPopulateBreadCrumbs(): void
    {
        $keys = ['history', 'details', 'account'];

        $randomValue = $keys[array_rand($keys)];

        if ($randomValue == 'details') {
            $count = 4;
        } elseif ($randomValue == 'history') {
            $count = 3;
        } else {
            $count = 2;
        }

        $this->breadcrumb->expects($this->exactly($count))->method('add');

        $nav = new Navigation(
            $this->request,
            $this->store,
            $this->config,
            $this->pedCash
        );

        $unit = new Account(
            $this->breadcrumb,
            $this->store,
            $this->files,
            $nav,
            $this->customer
        );
        $unit->populateBreadcrumb($randomValue);
    }

    public function testCanGetPageTitle(): void
    {
        $nav = new Navigation(
            $this->request,
            $this->store,
            $this->config,
            $this->pedCash
        );

        $unit = new Account(
            $this->breadcrumb,
            $this->store,
            $this->files,
            $nav,
            $this->customer
        );

        //All titles should be strings
        foreach ($nav->getPageData() as $data) {
            $test = $unit->getPageTitleByKey($data->title);
            $this->assertIsString($test);
        }

        //Passing in empty key should return empty string
        $test = $unit->getPageTitleByKey('');
        $this->assertEquals('', $test);

        //Passing in random key should return empty string
        $test = $unit->getPageTitleByKey('order-unknown');
        $this->assertEquals('', $test);
    }

    public function testCanGetPageUrl(): void
    {
        $nav = new Navigation(
            $this->request,
            $this->store,
            $this->config,
            $this->pedCash
        );

        $unit = new Account(
            $this->breadcrumb,
            $this->store,
            $this->files,
            $nav,
            $this->customer
        );

        //All titles should be strings
        foreach ($nav->getPageData() as $data) {
            $test = $unit->getPageUrlByKey($data->href);
            $this->assertIsString($test);
        }

        //Passing in empty key should return empty string
        $test = $unit->getPageUrlByKey('');
        $this->assertEquals('', $test);

        //Passing in random key should return empty string
        $test = $unit->getPageUrlByKey('order-unknown');
        $this->assertEquals('', $test);
    }
}