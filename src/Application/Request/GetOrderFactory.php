<?php
declare(strict_types=1);

namespace Panychek\Fba\Application\Request;

use FBAOutboundServiceMWS_Model_GetFulfillmentOrderRequest;
use Panychek\Fba\Domain\Order;

class GetOrderFactory
{
    use ValidationTrait;

    /**
     * Prepare a request for the "GetFulfillmentOrder" API operation
     *
     * @see https://docs.developer.amazonservices.com/en_UK/fba_outbound/FBAOutbound_GetFulfillmentOrder.html
     *
     * @param Order $oOrder
     * @return FBAOutboundServiceMWS_Model_GetFulfillmentOrderRequest
     */
    public function __invoke(Order $oOrder)
    {
        $this->assertValidOrder($oOrder);

        $request = new FBAOutboundServiceMWS_Model_GetFulfillmentOrderRequest();

        $request->setSellerId(getenv('SELLER_ID'));

        $request->setSellerFulfillmentOrderId($oOrder->data['order_unique']);

        return $request;
    }
}
