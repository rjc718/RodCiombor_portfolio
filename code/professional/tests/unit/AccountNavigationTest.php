<?php
namespace Pedstores\Ped\Tests\Unit\Models\Customers;

use Pedstores\Ped\Models\Customers\Accounts\Components\Navigation;
use Pedstores\Ped\Models\Request;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Views\Collection;
use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Models\Cart\PedCash;
use Pedstores\Ped\Models\Sites\Configuration;
use PHPUnit\Framework\TestCase;
use stdClass;

class AccountNavigationTest extends TestCase
{
    private $request;
    private $store;
    private $config;
    private $pedCash;

    public function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->store   = $this->createMock(Store::class);
        $this->config  = $this->createMock(Configuration::class);
        $this->pedCash = $this->createMock(PedCash::class);
        parent::setUp();
    }

    public function testCanGetPageData(): void
    {
        $unit = new Navigation(
            $this->request,
            $this->store,
            $this->config,
            $this->pedCash
        );
        $data = $unit->getPageData();
        $this->assertIsObject($data);
        foreach ($data as $key => $value) {
            $this->assertIsString($key);
            $this->assertIsString($value->title);
            $this->assertIsString($value->href);
        }
    }

    public function testCanGetPageDataByKey(): void
    {
        $unit = new Navigation(
            $this->request,
            $this->store,
            $this->config,
            $this->pedCash
        );
        //Pick a random key form pageData
        $data = $unit->getPageDataByKey(
            array_rand(
                (array) $unit->getPageData()
            )
        );
        $this->logicalOr(
            $this->isInstanceOf(stdClass::class),
            $this->isNull()
        );
        $this->assertIsString($data->title);
        $this->assertIsString($data->href);
    }

    public function testCanGetSubPageStructure(): void
    {
        $unit = new Navigation(
            $this->request,
            $this->store,
            $this->config,
            $this->pedCash
        );
        //Test a page with no parent
        $data = $unit->getSubPageStructure($unit::KEY_HISTORY);
        $this->assertIsArray($data);
        $this->assertEmpty($data);

        //Test a page with a parent
        $data = $unit->getSubPageStructure($unit::KEY_DETAILS);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        foreach ($data as $key) {
            $this->assertIsString($key);
        }
    }

    public function testCanCreateBackButton(): void
    {
        $unit = new Navigation(
            $this->request,
            $this->store,
            $this->config,
            $this->pedCash
        );
        //Test Account Page
        //Back Button should not display, should be Null
        $this->assertNull(
            $unit->createBackButton($unit::KEY_ACCOUNT)
        );

        //Test a page with no parent,
        //Should link to Account page
        $data = $unit->getPageDataByKey($unit::KEY_ACCOUNT);
        $this->testBackButtonData(
            $unit->createBackButton($unit::KEY_HISTORY),
            $data->href
        );

        //Test a page with a parent,
        //Should link to its parent page
        //In this case Order History
        $parent = $unit->getSubPageStructure($unit::KEY_DETAILS);
        $data   = $unit->getPageDataByKey($parent[0]);
        $this->testBackButtonData(
            $unit->createBackButton($unit::KEY_DETAILS),
            $data->href
        );
    }

    public function testCanCreateLeftRailNav(): void
    {
        $unit = new Navigation(
            $this->request,
            $this->store,
            $this->config,
            $this->pedCash
        );
        //Standard, just check all the datapoints
        $nav = $unit->createLeftRailNav(false, false);
        $this->assertInstanceOf(Data::class, $nav);
        $links = $nav->getData()['links'];
        $this->assertInstanceOf(Collection::class, $links);

        foreach ($links as $link) {
            $linkData = $link->getData();
            $this->assertIsArray($linkData);
            $this->assertIsString($linkData['href']);
            $this->assertIsString($linkData['text']);
            $this->assertIsString($linkData['subText']);
            $this->assertThat(
                $linkData['icon'],
                $this->logicalOr(
                    $this->isInstanceOf(Data::class),
                    $this->isNull()
                )
            );
            if (!is_null($linkData['icon'])) {
                $img = $linkData['icon']->getData();
                $this->assertIsArray($linkData);
                $this->assertIsString($img['src']);
                $this->assertIsString($img['alt']);
                $this->assertIsInt($img['width']);
                $this->assertIsInt($img['height']);
            }

            $styles = $linkData['styles'];
            $this->assertIsObject($styles);
            $this->assertIsString($styles->background);
            $this->assertIsString($styles->text);
            $this->assertIsString($styles->subtext);
            $this->assertIsString($styles->icon);
        }

        //Installer links should display if customer is installer
        $nav          = $unit->createLeftRailNav(true, false);
        $installInfo  = $unit->getPageDataByKey($unit::KEY_INSTALLER_INFO);
        $installLeads = $unit->getPageDataByKey($unit::KEY_INSTALLER_LEADS);
        $installLinks = [$installInfo->href, $installLeads->href];
        $matches      = 0;
        foreach ($nav->getData()['links'] as $link) {
            $linkData = $link->getData();
            if (in_array($linkData['href'], $installLinks)) {
                $matches++;
            }
        }
        $this->assertEquals(2, $matches);

        //MRO link should display if customer qualifies for MRO Pricing
        $nav     = $unit->createLeftRailNav(false, true);
        $mro     = $unit->getPageDataByKey($unit::KEY_MRO_PRICING);
        $matches = 0;
        foreach ($nav->getData()['links'] as $link) {
            $linkData = $link->getData();
            if ($linkData['href'] == $mro->href) {
                $matches++;
            }
        }
        $this->assertEquals(1, $matches);
    }

    private function testBackButtonData(Data $btn, string $expectedHref): void
    {
        $this->assertInstanceOf(Data::class, $btn);
        $btnData = $btn->getData();
        $this->assertIsString($btnData['class']);
        $this->assertInstanceOf(Data::class, $btnData['content']);
        $btnContent = $btnData['content']->getData();
        $this->assertIsArray($btnContent);
        $this->assertIsString($btnContent['class']);
        $this->assertEquals('Back', $btnContent['content']);
        $this->assertEquals($expectedHref, $btnContent['href']);
    }
}