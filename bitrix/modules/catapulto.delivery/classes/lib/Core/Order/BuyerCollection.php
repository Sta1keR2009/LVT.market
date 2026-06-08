<?php


namespace Ipol\Catapulto\Core\Order;


use Ipol\Catapulto\Core\Entity\Collection;

/**
 * Class BuyerCollection
 * @package Ipol\Catapulto\Core
 * @subpackage Order
 * @method false|Buyer getFirst
 * @method false|Buyer getNext
 * @method false|Buyer getLast
 */
class BuyerCollection extends Collection
{
    /**
     * @var array
     */
    protected $receivers;

    /**
     * BuyerCollection constructor.
     */
    public function __construct()
    {
        parent::__construct('buyers');
    }

}