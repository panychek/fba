<?php
declare(strict_types=1);

namespace Panychek\Fba\Test;

use FBAOutboundServiceMWS_Exception;
use FBAOutboundServiceMWS_Mock;
use FBAOutboundServiceMWS_Model_CreateFulfillmentOrderResponse;
use FBAOutboundServiceMWS_Model_GetFulfillmentOrderResponse;
use Panychek\Fba\Application\Exception\DomainException;
use Panychek\Fba\Application\Exception\MwsException;
use Panychek\Fba\Application\OutboundShippingServiceFactory;
use Panychek\Fba\Domain\Buyer;
use Panychek\Fba\Domain\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OutboundShippingServiceTest extends TestCase
{
    /**
     * @var Buyer|MockObject
     */
    private $buyerStub;

    /**
     * @var Order
     */
    private $orderStub;

    protected function setUp(): void
    {
        $this->buyerStub = $this->createBuyerStub();
        $this->orderStub = $this->createOrderStub();
    }

    private function createBuyerStub(): Buyer
    {
        $mockDataPath = __DIR__ . '/data/buyer.json';
        $buyerData = json_decode(file_get_contents($mockDataPath), true);

        /** @var Buyer|MockObject $stub */
        $stub = $this->getMockBuilder(Buyer::class)
            ->setConstructorArgs([$buyerData])
            ->getMock();

        $stub->method('get_country_code')
            ->willReturn('US');

        $stub->method('get_country_code3')
            ->willReturn('USA');

        $stub->method('count')
            ->willReturn(count($buyerData));

        return $stub;
    }

    private function createOrderStub(): Order
    {
        $orderId = 16400;
        $stub = new Order($orderId);

        $mockDataPath = __DIR__ . '/data/order.json';
        $stub->data = json_decode(file_get_contents($mockDataPath), true);

        return $stub;
    }

    /**
     * @group Unit
     */
    public function testShipment()
    {
        $service = (new OutboundShippingServiceFactory())();

        $mwsServiceMock = $this->createMock(FBAOutboundServiceMWS_Mock::class);

        $data = file_get_contents(__DIR__ . '/data/response/CreateFulfillmentOrder.xml');
        $mwsServiceMock->method('createFulfillmentOrder')
            ->willReturn(FBAOutboundServiceMWS_Model_CreateFulfillmentOrderResponse::fromXML($data));

        $data = file_get_contents(__DIR__ . '/data/response/GetFulfillmentOrder.xml');
        $mwsServiceMock->method('getFulfillmentOrder')
            ->willReturn(FBAOutboundServiceMWS_Model_GetFulfillmentOrderResponse::fromXML($data));

        $service->setMwsService($mwsServiceMock);

        $trackingNumber = $service->ship($this->orderStub, $this->buyerStub);

        $this->assertEquals('93ZZ00', $trackingNumber);
    }

    /**
     * @group Unit
     */
    public function testCreatingOrderBadResponseThrowsException()
    {
        $service = (new OutboundShippingServiceFactory())();

        $mwsServiceMock = $this->createMock(FBAOutboundServiceMWS_Mock::class);
        $mwsServiceMock->method('createFulfillmentOrder')
            ->will($this->throwException(new FBAOutboundServiceMWS_Exception()));

        $service->setMwsService($mwsServiceMock);

        $this->expectException(MwsException::class);
        $this->expectExceptionCode(MwsException::BAD_RESPONSE);

        $service->ship($this->orderStub, $this->buyerStub);
    }

    /**
     * @group Unit
     */
    public function testFetchingOrderBadResponseThrowsException()
    {
        $service = (new OutboundShippingServiceFactory())();

        $mwsServiceMock = $this->createMock(FBAOutboundServiceMWS_Mock::class);

        $data = file_get_contents(__DIR__ . '/data/response/CreateFulfillmentOrder.xml');
        $mwsServiceMock->method('createFulfillmentOrder')
            ->willReturn(FBAOutboundServiceMWS_Model_CreateFulfillmentOrderResponse::fromXML($data));

        $mwsServiceMock->method('getFulfillmentOrder')
            ->will($this->throwException(new FBAOutboundServiceMWS_Exception()));

        $service->setMwsService($mwsServiceMock);

        $this->expectException(MwsException::class);
        $this->expectExceptionCode(MwsException::BAD_RESPONSE);

        $service->ship($this->orderStub, $this->buyerStub);
    }

    /**
     * @group Unit
     */
    public function testEmptyOrderThrowsException()
    {
        $service = (new OutboundShippingServiceFactory())();

        $orderId = 16400;
        $order = new Order($orderId);

        $this->expectException(DomainException::class);
        $service->ship($order, $this->buyerStub);
    }

    /**
     * @group Unit
     */
    public function testEmptyBuyerThrowsException()
    {
        $service = (new OutboundShippingServiceFactory())();

        $buyer = new Buyer();

        $this->expectException(DomainException::class);
        $service->ship($this->orderStub, $buyer);
    }
}
