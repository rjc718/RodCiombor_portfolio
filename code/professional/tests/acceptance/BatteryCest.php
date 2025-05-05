<?php
namespace Pedstores\Ped\Tests\Acceptance;

use AcceptanceTester;
use Faker\Factory as FakerFactory;
use Pedstores\Ped\Utilities\Money;

/**
 * @group desktop
 */
class BatteryCest
{
    public const BATTERY_OPTION_VALUE_ID        = 11;
    public const KOHLER_BATTERY_OPTION_VALUE_ID = 13;
    public const ATC_VIEW_CART                  = 'a.atc_view_cart_btn';
    public const BATTERY_CHECKBOX               = '#batteryCheckbox';
    public const BATTERY_COST_SELECTOR          = 'b[data-cost="battery"]';
    public const ADD_BATTERY_SELECTOR           = 'button[data-action="item:addbattery"]';
    public const PAYMENT_SUBMIT_BUTTON          = 'button[data-action="payment:submit"]';
    public const SHIPPING_EDIT_BUTTON           = 'button[data-action="shipping:edit"]';
    public const DELIVERY_EDIT_BUTTON           = 'button[data-action="delivery:edit"]';
    public const CONFIRMATION_PAGE              = 'div#orderConfirmationPage';
    public const GET_TEST_PRODUCT               = <<<SQL
        SELECT p.products_id, p.products_url
        FROM products_attributes pa
        LEFT JOIN products p ON pa.products_id = p.products_id
        WHERE pa.options_values_id = :optionValue
        AND p.products_status = 1
        AND p.products_status1 = 0
        AND p.individual_sale = 1
        AND (p.products_quantity > :itemQty OR p.products_quantity1 > :itemQty)
        ORDER BY RAND()
        LIMIT 1
    SQL;
    public const GET_BATTERY_PRICE = <<<SQL
    SELECT `options_values_price` FROM `products_attributes`
    WHERE `products_id` = ? AND `options_values_id` = ?
    SQL;

    public function placeOrderWithBattery(AcceptanceTester $I): void
    {
        $this->execute($I);
        $this->execute($I, true);
    }

    private function execute(AcceptanceTester $I, bool $kohler = false): void
    {
        $itemQty = FakerFactory::create()->numberBetween(1, 5);

        $optionValue = self::BATTERY_OPTION_VALUE_ID;
        if ($kohler) {
            $optionValue = self::KOHLER_BATTERY_OPTION_VALUE_ID;
        }

        $prodInfo = $I->getRowFromDatabase(
            self::GET_TEST_PRODUCT,
            [
                'optionValue' => $optionValue,
                'itemQty'     => $itemQty
            ]
        );

        $testProduct = $prodInfo->products_id;
        $url         = $prodInfo->products_url;

        $I->wait(1);
        $I->amOnPage($url);

        if (!$kohler) {
            //Don't run this the second time
            //The Dismiss Privacy Notice is not present.
            $I->dismissPrivacyNotice($I);
        }

        $I->addProductToCartFromPdpPage($I, $itemQty);

        $this->selectBatteryOnMiniCart($I);

        $I->waitForButtonToLoseDisabledClass(self::ATC_VIEW_CART);

        $I->proceedToCartPageFromMiniCart($I, $itemQty);

        $this->checkShoppingCartForBattery($I, $testProduct, $kohler, $itemQty);
        $I->proceedToCheckoutFromCartPage($I);

        //If Shipping Edit button is present, the form has already been filled out
        //May happen when tests run second time for Kohler
        if (!$I->tryToSeeElement(self::SHIPPING_EDIT_BUTTON)) {
            $I->fillCheckoutShippingForm();
            $I->validateShippingAddress($I);
        }
        //If Delivery Edit button is present, the form has already been filled out
        //May happen when tests run second time for Kohler
        if (!$I->tryToSeeElement(self::DELIVERY_EDIT_BUTTON)) {
            $I->selectShippingAndContinueToPayment();
        }

        $I->payWithCreditCardOrCheck($I);
        $I->scrollIntoView(self::PAYMENT_SUBMIT_BUTTON);
        $I->click(self::PAYMENT_SUBMIT_BUTTON);
        $I->waitForElement(self::CONFIRMATION_PAGE, 60);
        $I->see('Your Order Has Been Placed.  Thank You!');
        $I->waitForPageReady(30);
    }

    private function selectBatteryOnMiniCart(AcceptanceTester $I): void
    {
        $I->see(" Add 12V Start Battery ");
        $I->seeElement(self::BATTERY_CHECKBOX);
        $I->waitForElementClickable(self::BATTERY_CHECKBOX);
        $I->click(self::BATTERY_CHECKBOX);
    }

    private function checkShoppingCartForBattery(
        AcceptanceTester $I,
        int $prodId,
        bool $kohler,
        int $itemQty
    ): void {
        //Check if the button to add the battery exists as well as the price of a battery
        //Test will pass whether or not a battery has been added
        $I->seeElementInDOM(self::ADD_BATTERY_SELECTOR);
        $I->seeElementInDOM(self::BATTERY_COST_SELECTOR);

        $option = self::BATTERY_OPTION_VALUE_ID;
        if ($kohler) {
            $option = self::KOHLER_BATTERY_OPTION_VALUE_ID;
        }

        $cost = new Money($I->getResultFromDatabase(
            self::GET_BATTERY_PRICE,
            [$prodId, $option]
        ));

        $I->see($cost->multiply($itemQty)->getString());
    }
}