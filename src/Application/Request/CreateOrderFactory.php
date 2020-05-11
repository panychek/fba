<?php
declare(strict_types=1);

namespace Panychek\Fba\Application\Request;

use FBAOutboundServiceMWS_Model_Address;
use FBAOutboundServiceMWS_Model_CreateFulfillmentOrderItem;
use FBAOutboundServiceMWS_Model_CreateFulfillmentOrderItemList;
use FBAOutboundServiceMWS_Model_CreateFulfillmentOrderRequest;
use Panychek\Fba\Domain\Buyer;
use Panychek\Fba\Domain\Order;

class CreateOrderFactory
{
    use ValidationTrait;

    const SHIPPING_SPEED_CATEGORY = 'Standard';

    /**
     * Prepare a request for the "CreateFulfillmentOrder" API operation
     *
     * @see https://docs.developer.amazonservices.com/en_UK/fba_outbound/FBAOutbound_CreateFulfillmentOrder.html
     *
     * @param Order $oOrder
     * @param Buyer $oBuyer
     * @return FBAOutboundServiceMWS_Model_CreateFulfillmentOrderRequest
     */
    public function __invoke(Order $oOrder, Buyer $oBuyer): FBAOutboundServiceMWS_Model_CreateFulfillmentOrderRequest
    {
        $this->assertValidOrder($oOrder);
        $this->assertValidBuyer($oBuyer);

        $request = new FBAOutboundServiceMWS_Model_CreateFulfillmentOrderRequest();
        $request->setSellerId(getenv('SELLER_ID'));

        $request->setSellerFulfillmentOrderId($oOrder->data['order_unique'])
            ->setDisplayableOrderId($oOrder->data['order_id'])
            ->setDisplayableOrderDateTime($oOrder->data['order_date'])
            ->setShippingSpeedCategory(self::SHIPPING_SPEED_CATEGORY);

        $destinationAddress = $this->buildDestinationAddress($oOrder, $oBuyer);
        $request->setDestinationAddress($destinationAddress);

        $itemList = $this->buildItemList($oOrder->data['products']);
        $request->setItems($itemList);

        return $request;
    }

    /**
     * @see https://docs.developer.amazonservices.com/en_UK/fba_outbound/FBAOutbound_Datatypes.html#Address
     *
     * @param Order $oOrder
     * @param Buyer $oBuyer
     * @return FBAOutboundServiceMWS_Model_Address
     */
    private function buildDestinationAddress(Order $oOrder, Buyer $oBuyer): FBAOutboundServiceMWS_Model_Address
    {
        $address = new FBAOutboundServiceMWS_Model_Address();
        $address->setName($oOrder->data['buyer_name'])
            ->setLine1($oOrder->data['shipping_street'])
            ->setCity($oOrder->data['shipping_city'])
            ->setStateOrProvinceCode($oOrder->data['shipping_state'])
            ->setCountryCode($oBuyer->get_country_code())
            ->setPostalCode($oOrder->data['shipping_zip']);

        return $address;
    }

    /**
     * @see https://docs.developer.amazonservices.com/en_UK/fba_outbound/FBAOutbound_Datatypes.html#CreateFulfillmentOrderItem
     *
     * @param array $products
     * @return FBAOutboundServiceMWS_Model_CreateFulfillmentOrderItemList
     */
    private function buildItemList(array $products): FBAOutboundServiceMWS_Model_CreateFulfillmentOrderItemList
    {
        $list = new FBAOutboundServiceMWS_Model_CreateFulfillmentOrderItemList;
        foreach ($products as $product) {
            $item = new FBAOutboundServiceMWS_Model_CreateFulfillmentOrderItem();
            $item->setSellerSKU($product['sku'])
                ->setSellerFulfillmentOrderItemId($product['order_product_id'])
                ->setQuantity($product['ammount'])
                ->setDisplayableComment($product['comment']);

            $list->withmember($item);
        }

        return $list;
    }
}
