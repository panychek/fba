<?php
declare(strict_types=1);

namespace Panychek\Fba\Application;

use FBAOutboundServiceMWS_Exception;
use FBAOutboundServiceMWS_Interface;
use FBAOutboundServiceMWS_Model_CreateFulfillmentOrderResponse;
use FBAOutboundServiceMWS_Model_GetFulfillmentOrderResponse;
use Panychek\Fba\Application\Exception\MwsException;
use Panychek\Fba\Application\Request\CreateOrderFactory;
use Panychek\Fba\Application\Request\GetOrderFactory;
use Panychek\Fba\Domain\Buyer;
use Panychek\Fba\Domain\IOutbondShipping;
use Panychek\Fba\Domain\Order;

class OutboundShippingService implements IOutbondShipping
{
    /**
     * @var FBAOutboundServiceMWS_Interface
     */
    private $mwsService;

    /**
     * @param FBAOutboundServiceMWS_Interface $mwsService
     */
    public function __construct(FBAOutboundServiceMWS_Interface $mwsService)
    {
        $this->setMwsService($mwsService);
    }

    /**
     * @param FBAOutboundServiceMWS_Interface $mwsService
     */
    public function setMwsService(FBAOutboundServiceMWS_Interface $mwsService): void
    {
        $this->mwsService = $mwsService;
    }

    /**
     * {@inheritDoc}
     */
    public function ship(Order $oOrder, Buyer $oBuyer): string
    {
        $this->createOrder($oOrder, $oBuyer);
        $order = $this->getOrder($oOrder);

        return $this->getTrackingNumberFromOrder($order);
    }

    /**
     * Create a fulfillment order
     *
     * @param Order $oOrder
     * @param Buyer $oBuyer
     * @throws MwsException for any API-related issues
     */
    private function createOrder(
        Order $oOrder,
        Buyer $oBuyer
    ): FBAOutboundServiceMWS_Model_CreateFulfillmentOrderResponse {
        $request = (new CreateOrderFactory())($oOrder, $oBuyer);

        try {
            return $this->mwsService->createFulfillmentOrder($request);

        } catch (FBAOutboundServiceMWS_Exception $e) {
            $message = sprintf(
                "Unable to create an order due to an API error: %s, code: %d",
                $e->getMessage(),
                $e->getStatusCode()
            );

            throw new MwsException($message, MwsException::BAD_RESPONSE);
        }
    }

    /**
     * Fetch an order information
     *
     * @param Order $oOrder
     * @return FBAOutboundServiceMWS_Model_GetFulfillmentOrderResponse
     * @throws MwsException
     */
    private function getOrder(Order $oOrder): FBAOutboundServiceMWS_Model_GetFulfillmentOrderResponse
    {
        $request = (new GetOrderFactory())($oOrder);

        try {
            return $this->mwsService->getFulfillmentOrder($request);

        } catch (FBAOutboundServiceMWS_Exception $e) {
            $message = sprintf(
                "Unable to fetch an order due to an API error: %s, code: %d",
                $e->getMessage(),
                $e->getStatusCode()
            );

            throw new MwsException($message, MwsException::BAD_RESPONSE);
        }
    }

    /**
     * Find a tracking number in an order response
     *
     * @param FBAOutboundServiceMWS_Model_GetFulfillmentOrderResponse $order
     * @return string Tracking number of this fulfillment order
     * @throws MwsException
     */
    private function getTrackingNumberFromOrder(FBAOutboundServiceMWS_Model_GetFulfillmentOrderResponse $order): string
    {
        $numbers = [];

        $result = $order->getGetFulfillmentOrderResult();
        $shipmentList = $result->getFulfillmentShipment()->getmember();

        foreach ($shipmentList as $shipment) {
            if ($shipment->isSetFulfillmentShipmentPackage()) {
                $packageList = $shipment->getFulfillmentShipmentPackage()->getmember();

                foreach ($packageList as $package) {
                    $numbers[] = $packageList[0]->getTrackingNumber();
                }
            }
        }

        $errorMessage = 'Unable to fetch a tracking number';
        if (empty($numbers)) {
            throw new MwsException($errorMessage, MwsException::MISSING_TRACKING_NUMBER);
        }

        if (count($numbers) > 1) {
            throw new MwsException($errorMessage, MwsException::AMBIGIOUS_TRACKING_NUMBER);
        }

        return $numbers[0];
    }
}
