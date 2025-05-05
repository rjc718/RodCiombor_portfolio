<?php
use PHPUnit\Framework\TestCase;
use Pedstores\Ped\Models\Customers\Accounts\Components\Payment;
use Pedstores\Ped\Databases\Database;
use Pedstores\Ped\Models\Customers\Accounts\Components\Status;
use Pedstores\Ped\Models\Orders\Data as OrdersData;
use Pedstores\Ped\Models\Sites\Store;
use Pedstores\Ped\Payments\Providers\CyberSource;
use Pedstores\Ped\Views\Data;
use Pedstores\Ped\Models\Orders\Factory as OrdersFactory;
use Faker\Factory;
use Pedstores\Ped\Models\Order;

class AccountPaymentTest extends TestCase
{
    private const TMPL_DIR   = 'account/orders/details/info/payment/';
    private const CC_DEFAULT = 'Credit Card';
    private $db;
    private $store;
    private $status;
    private $faker;
    private $ordersFactory;

    protected function setUp(): void
    {
        $this->db            = $this->createMock(Database::class);
        $this->store         = $this->createMock(Store::class);
        $this->status        = $this->createMock(Status::class);
        $this->ordersFactory = $this->createMock(OrdersFactory::class);
        $this->faker         = Factory::create();
        parent::setUp();
    }

    public function testCanDisplayKnownCreditCardInfo(): void
    {
        $unit = new Payment(
            $this->db,
            $this->store,
            $this->status
        );
        $result = $unit->getPaymentInfo(
            $this->getOrderWithCreditCard()
        );
        $this->assertInstanceOf(Data::class, $result);
        $data = $result->getData();

        $this->checkMainTitleAndTemplate($result);

        //Should see the card name
        $data['payMethod'];
        $this->assertIsString(
            $data['payMethod']
        );
        $this->assertContains(
            $data['payMethod'],
            ['Visa', 'MC', 'Amex', 'Discover']
        );

        //Should see a logo
        $this->assertInstanceOf(Data::class, $data['logo']);
        $this->assertEquals(
            $data['logo']->getTemplate(),
            'tags/image.php'
        );

        //ccNumber should be 16 digits long
        //Check if the ccNumber contains at least 12 instances of '*'
        //Should normally display the last 4 digits, but not sure that we can set the JWT token in test
        $this->assertEquals(16, strlen($data['ccNumber']));
        $this->assertMatchesRegularExpression(
            '/\*{12,}/',
            $data['ccNumber']
        );
    }

    public function testCanDisplayUnknownCreditCardInfo(): void
    {
        $unit = new Payment(
            $this->db,
            $this->store,
            $this->status
        );
        $result = $unit->getPaymentInfo(
            $this->getOrderWithCreditCard(self::CC_DEFAULT)
        );
        $this->assertInstanceOf(Data::class, $result);
        $data = $result->getData();

        $this->checkMainTitleAndTemplate($result);

        //Should display 'Credit Card'
        $data['payMethod'];
        $this->assertIsString(
            $data['payMethod']
        );
        $this->assertEquals(
            $data['payMethod'],
            self::CC_DEFAULT
        );
        //Should not see a logo
        $this->assertEquals($data['logo'], '');

        //Should see the masked CC number, no digits
        $this->assertMatchesRegularExpression(
            '/\*{16}/',
            $data['ccNumber']
        );
    }

    public function testCanDisplayPaymentMethodWithoutLogo(): void
    {
        $options = [
            'Check',
            'shopatron',
            'HeatPumpStore',
            'Paypal Express',
            'Paypal Direct',
            'btPaypal'
        ];
        $unit = new Payment(
            $this->db,
            $this->store,
            $this->status
        );
        $result = $unit->getPaymentInfo(
            $this->getOrderWithoutCreditCard(
                $options[array_rand($options)]
            )
        );
        $this->assertInstanceOf(Data::class, $result);
        $data = $result->getData();

        $this->checkMainTitleAndTemplate($result);

        //Should see Payment Method
        $this->assertIsString($data['payMethod']);

        //Should not see Logo
        $this->assertEquals($data['logo'], '');

        //ccNumber should be empty
        $this->assertEquals($data['ccNumber'], '');
    }

    public function testCanDisplayPaymentMethodWithLogo(): void
    {
        $options = ['Affirm', 'Synchrony Financial', 'Synchrony'];
        $unit    = new Payment(
            $this->db,
            $this->store,
            $this->status
        );
        $result = $unit->getPaymentInfo(
            $this->getOrderWithoutCreditCard(
                $options[array_rand($options)]
            )
        );
        $this->assertInstanceOf(Data::class, $result);
        $data = $result->getData();

        $this->checkMainTitleAndTemplate($result);

        //Should see Logo
        $this->assertInstanceOf(Data::class, $data['logo']);
        $this->assertEquals(
            $data['logo']->getTemplate(),
            'tags/image.php'
        );
        //Label should be empty
        $this->assertEquals($data['payMethod'], '');
        //ccNumber should be empty
        $this->assertEquals($data['ccNumber'], '');
    }

    public function testUpdatePaymentLinkDisplays(): void
    {
        $unit = new Payment(
            $this->db,
            $this->store,
            $this->status
        );
        $result = $unit->getPaymentInfo(
            $this->getOrderThatNeedsReAuth()
        );
        $this->assertInstanceOf(Data::class, $result);
        $resultData = $result->getData();

        //Should Show Invalid Icon
        $invalidIcon = $resultData['invalidIcon'];
        $this->assertInstanceOf(Data::class, $invalidIcon);
        $this->assertEquals(
            $invalidIcon->getTemplate(),
            'bootstrap/icons/alerts/danger.php'
        );

        //Should Display Update Payment Link
        $reAuthLink = $resultData['reAuthLink'];
        $this->assertInstanceOf(Data::class, $reAuthLink);
        $this->assertEquals(
            $reAuthLink->getTemplate(),
            self::TMPL_DIR . 'reauth-link.php'
        );

        $updatePayment = $reAuthLink->getData()['updatePaymentLink'];
        $this->assertInstanceOf(Data::class, $updatePayment);
        $this->assertEquals(
            $updatePayment->getTemplate(),
            'tags/anchor.php'
        );
        $this->assertEquals(
            $updatePayment->getData()['content'],
            'Update payment information'
        );
        $this->assertIsString($updatePayment->getData()['href']);
    }

    public function testCheckUpdatePaymentLinkHref(): void
    {
        $unit = new Payment(
            $this->db,
            $this->store,
            $this->status
        );
        $result = $unit->getPaymentInfo(
            $this->getOrderThatNeedsReAuth()
        );
        $this->assertInstanceOf(Data::class, $result);

        $link = $result->getData()['reAuthLink']->getData()['updatePaymentLink'];
        $href = $link->getData()['href'];

        //Check that href is string
        $this->assertIsString($href);
        //Check parts individually
        $parts = explode('/', $href);
        //Check route name
        $this->assertEquals($parts[1], 'update-payment-information');
        //Check UUID
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $parts[2]
        );
        //Check that order Id is a number
        $this->assertIsNumeric($parts[3]);
        $this->assertMatchesRegularExpression(
            '/^\d+$/',
            $parts[3]
        );
    }

    private function getOrderWithCreditCard(string $method = ''): Order
    {
        $orderId = $this->faker->numberBetween(100000, 999999);

        $order = $this->ordersFactory->createOrder($orderId);
        $order->method('getId')->willReturn($orderId);

        $ordersData = $this->createMock(OrdersData::class);
        $order->method('getData')->willReturn($ordersData);

        if ($method === '') {
            $paymentOptions = [
                'Visa',
                'Master Card',
                'American Express',
                'Discover'
            ];
            $ccType = $paymentOptions[array_rand($paymentOptions)];
            $method = CyberSource::PAYMENT_METHOD;
            $ordersData->method('getPaymentData')->willReturn((object)[
                'transactions' => [
                    (object)[
                        'type'          => 'payment token',
                        'transactionId' => 'jwt.token.here'
                    ]
                ]
            ]);
        } else {
            $ccType = $method;
        }

        $ordersData->method('getPaymentMethod')->willReturn(
            $method
        );
        $ordersData->method('getCreditCardType')->willReturn(
            $ccType
        );
        $ordersData->method('getNeedsReauth')->willReturn(false);
        return $order;
    }

    private function getOrderWithoutCreditCard(string $method): Order
    {
        $orderId = $this->faker->numberBetween(100000, 999999);

        $order = $this->ordersFactory->createOrder($orderId);
        $order->method('getId')->willReturn($orderId);

        $ordersData = $this->createMock(OrdersData::class);
        $order->method('getData')->willReturn($ordersData);

        $ordersData->method('getPaymentMethod')->willReturn(
            $method
        );
        $ordersData->method('getNeedsReauth')->willReturn(false);
        return $order;
    }

    private function getOrderThatNeedsReAuth(): Order
    {
        $orderId = $this->faker->numberBetween(100000, 999999);
        $order = $this->ordersFactory->createOrder($orderId);
        $order->method('getId')->willReturn($orderId);

        $ordersData = $this->createMock(OrdersData::class);
        $order->method('getData')->willReturn($ordersData);
        $ordersData->method('getPaymentMethod')->willReturn(
            CyberSource::PAYMENT_METHOD
        );
        $ordersData->method('getPaymentData')->willReturn((object)[
            'transactions' => [
                (object)[
                    'type'          => 'payment token',
                    'transactionId' => 'jwt.token.here'
                ]
            ]
        ]);

        $ordersData->method('getNeedsReauth')->willReturn(true);
        $this->status->method('getStatusLabelForOrder')->willReturn(
            Status::STATUS_PROCESSING
        );
        $this->db->method('getQueryResult')->willReturn($this->faker->uuid());
        return $order;
    }

    private function checkMainTitleAndTemplate(object $result): void
    {
        $this->assertEquals(
            $result->getTemplate(),
            self::TMPL_DIR . 'method.php'
        );
        $this->assertEquals(
            $result->getData()['title'],
            'Payment Method'
        );
    }
}