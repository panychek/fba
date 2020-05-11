<?php
declare(strict_types=1);

namespace Panychek\Fba\Domain;

use Exception;

interface IOutbondShipping
{
    /**
     * Send a command to Amazon FBA to ship a specific customer's order
     *
     * @param Order $oOrder
     * @param Buyer $oBuyer
     * @return string Tracking number
     * @throws Exception
     */
    public function ship(Order $oOrder, Buyer $oBuyer): string;
}
