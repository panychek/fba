<?php
declare(strict_types=1);

namespace Panychek\Fba\Application\Exception;

use Exception;

class MwsException extends Exception implements ExceptionInterface
{
    const BAD_RESPONSE = 1;

    const MISSING_TRACKING_NUMBER = 2;

    const AMBIGIOUS_TRACKING_NUMBER = 3;
}
