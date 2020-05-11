<?php
declare(strict_types=1);

namespace Panychek\Fba\Domain;

use ArrayObject;

/**
 * @property int $country_id
 * @property string $name
 * @property string $shop_username
 * @property string $email
 * @property string $phone
 * @property string $address
 * @property array $data
 * @author antons
 */
class Buyer extends ArrayObject
{
    public function __construct($array = [])
    {
        parent::__construct($array, self::ARRAY_AS_PROPS);
    }

    public function get_country_code()
    {

    }

    public function get_country_code3()
    {

    }
}
