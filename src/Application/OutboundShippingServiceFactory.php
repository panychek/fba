<?php
declare(strict_types=1);

namespace Panychek\Fba\Application;

use FBAOutboundServiceMWS_Client;

class OutboundShippingServiceFactory
{
    /**
     * @return OutboundShippingService
     */
    public function __invoke()
    {
        $config = [
            'ServiceURL' => getenv('SERVICE_URL'),
            'ProxyHost' => null,
            'ProxyPort' => -1,
            'ProxyUsername' => null,
            'ProxyPassword' => null,
            'MaxErrorRetry' => 3,
        ];

        $service = new FBAOutboundServiceMWS_Client(
            getenv('AWS_ACCESS_KEY_ID'),
            getenv('AWS_SECRET_ACCESS_KEY'),
            $config,
            getenv('APPLICATION_NAME'),
            getenv('APPLICATION_VERSION')
        );

        return new OutboundShippingService($service);
    }
}
