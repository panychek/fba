<?php
declare(strict_types=1);

namespace Panychek\Fba\Application\Request;

use Panychek\Fba\Application\Exception\DomainException;
use Panychek\Fba\Domain\Buyer;
use Panychek\Fba\Domain\Order;

trait ValidationTrait
{
    /**
     * @param Order $oOrder
     */
    private function assertValidOrder(Order $oOrder): void
    {
        if (empty($oOrder->data)) {
            $format = '%s expects the $oOrder argument to be a valid order; received empty';
            $message = sprintf($format, __METHOD__);

            throw new DomainException($message);
        }
    }

    /**
     * @param Buyer $oBuyer
     */
    private function assertValidBuyer(Buyer $oBuyer): void
    {
        if ($oBuyer->count() == 0) {
            $format = '%s expects the $oBuyer argument to be a valid buyer; received empty';
            $message = sprintf($format, __METHOD__);

            throw new DomainException($message);
        }
    }
}
