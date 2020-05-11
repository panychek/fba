<?php
declare(strict_types=1);

namespace Panychek\Fba\Domain;

class Order
{
    protected $id;
    public $data;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function load()
    {
        $this->data = [];
    }
}
