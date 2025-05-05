<?php
namespace Pedstores\Ped\Tests\Acceptance;

use AcceptanceTester;
use Faker\Factory;
use Pedstores\Ped\Utilities\Money;

/**
 * @group desktop
 */
class AlaskaShippingCest
{
    public const ATC_VIEW_CART         = 'a.atc_view_cart_btn';
    public const SHIPPING_OPTIONS      = 'form[data-action="shipping:options"] input[data-option-cost]';
    public const PAYMENT_SUBMIT_BUTTON = 'button[data-action="payment:submit"]';
    public const CONFIRMATION_PAGE     = 'div#orderConfirmationPage';
    public const GET_TEST_PRODUCT      = <<<SQL
        SELECT products_url
        FROM products
        WHERE products_status = 1
        AND products_quantity1 > ?
        AND expedited_shipping = 0
        AND ship_method = 0
        AND individual_sale = 1
        AND products_status1 = 0
        AND products_weight < 150
        AND canada_shipping NOT LIKE "%AK%"
        ORDER BY RAND() LIMIT 1;
    SQL;

    public function placeOrderWithAlaskaShipping(AcceptanceTester $I): void
    {
        $itemQty = Factory::create()->numberBetween(1, 5);

        $prodInfo = $I->getRowFromDatabase(self::GET_TEST_PRODUCT, [($itemQty - 1)]);
        $I->amOnPage($prodInfo->products_url);
        $I->dismissPrivacyNotice($I);

        $I->addProductToCartFromPdpPage($I, $itemQty);

        $I->waitForButtonToLoseDisabledClass(self::ATC_VIEW_CART);

        $I->proceedToCartPageFromMiniCart($I, $itemQty);

        $I->proceedToCheckoutFromCartPage($I);
        $I->fillCheckoutShippingForm('AK');
        $I->validateShippingAddress($I);

        $pattern = '/^\$([0-9]{1,3}(\,[0-9]{3})*(\.[0-9]{2}))$/';

        $shippingOptions = $I->getElementsAsArray(self::SHIPPING_OPTIONS);

        $twoDayCost = null;
        foreach ($shippingOptions as $option) {
            $id = $option->getAttribute('id');
            $I->dontSee('$0.00', 'label[for="' . $id . '"]');

            $cost = $option->getAttribute('data-option-cost');

            $I->assertRegExp($pattern, $cost);

            $floatval = floatval(str_replace('$', '', $cost));
            $I->assertGreaterThan(0, $floatval);

            if ($option->getAttribute('value') === '2DA') {
                $twoDayCost = $cost;
                $I->logError('Two Day Shipping Cost: ' . $twoDayCost);
            }
        }

        if (!is_null($twoDayCost)) {
            $I->selectShipping2DA($I);
            $I->assertEquals($twoDayCost, $I->grabTextFrom('table tbody tr:nth-child(2) td'));
            $I->logError('Successfully Applied 2DA Shipping');
        } else {
            $I->selectShippingAndContinueToPayment();
        }

        $I->payWithCreditCardOrElectronicCheck($I);
        $I->scrollIntoView(self::PAYMENT_SUBMIT_BUTTON);
        $I->click(self::PAYMENT_SUBMIT_BUTTON);

        //Confirmation
        $I->waitForElement(self::CONFIRMATION_PAGE, 60);
        $I->see('Your Order Has Been Placed.  Thank You!');

        $orderId       = $I->getOrderNumberFromConfirmationPage();
        $shippingTotal = $I->getOrderShippingTotalFromDatabase($orderId);
        $I->assertGreaterThan(0.0000, $shippingTotal);
        $I->see(Money::formatFloat($shippingTotal), '#orderTotalsSection');
    }
}