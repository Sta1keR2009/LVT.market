<?php


namespace Ipol\Catapulto\Core\Order;


use Ipol\Catapulto\Core\Entity\Collection;

/**
 * Class OrderCollection
 * @package Ipol\Catapulto\Core
 * @subpackage Order
 * @method false|Order getFirst
 * @method false|Order getNext
 * @method false|Order getLast
 */
class OrderCollection extends Collection
{
    /**
     * @var array
     */
    protected $orders;

    /**
     * OrderCollection constructor.
     */
    public function __construct()
    {
        parent::__construct('orders');
    }

}