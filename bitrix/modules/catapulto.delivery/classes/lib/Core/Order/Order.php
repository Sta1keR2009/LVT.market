<?php


namespace Ipol\Catapulto\Core\Order;


use Ipol\Catapulto\Core\Entity\FieldsContainer;

/**
 * Class Order
 * @package Ipol\Catapulto\Core
 * @subpackage Order
 */
class Order
{
    use FieldsContainer;

    /**
     * @var string - order number in bitrix ('ACCOUNT_NUMBER')
     */
    protected $number;
    /**
     * @var string - name for status (supposedly inner name defined by module and API)
     * example: 'NEW'
     */
    protected $status;
    /**
     * @var string - order id in API
     */
    protected $link;
    /**
     * @var Sender
     */
    protected $sender;
    /**
     * @var BuyerCollection
     */
    protected $buyers;
    /**
     * @var ReceiverCollection
     */
    protected $receivers;
    /**
     * @var Address
     */
    protected $addressFrom;
    /**
     * @var Address
     */
    protected $addressTo;
    /**
     * @var Payment
     */
    protected $payment;
    /**
     * @var Goods
     */
    protected $goods;
    /**
     * @var ItemCollection
     */
    protected $items;

    /**
     * Order constructor.
     */
    function __construct()
    {
        $this->receivers = new ReceiverCollection();
        return $this;
    }

    /**
     * @return ItemCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ItemCollection $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param mixed $payment
     * @return $this
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @return Sender
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param Sender $sender
     * @return $this
     */
    public function setSender(Sender $sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return BuyerCollection
     */
    public function getBuyers()
    {
        return $this->buyers;
    }

    /**
     * @param BuyerCollection $buyers
     * @return Order
     */
    public function setBuyers(BuyerCollection $buyers)
    {
        $this->buyers = $buyers;
        return $this;
    }

    /**
     * @param Receiver $receiver
     * @return $this
     */
    public function addReciever(Receiver $receiver)
    {
        $this->getReceivers()->add($receiver);

        return $this;
    }

    /**
     * @return ReceiverCollection
     */
    public function getReceivers()
    {
        return $this->receivers;
    }

    /**
     * @param ReceiverCollection $receivers
     * @return $this
     */
    public function setReceivers(ReceiverCollection $receivers)
    {
        $this->receivers = $receivers;

        return $this;
    }

    /**
     * @return Address
     */
    public function getAddressFrom()
    {
        return $this->addressFrom;
    }

    /**
     * @param Address $addressFrom
     * @return $this
     */
    public function setAddressFrom(Address $addressFrom)
    {
        $this->addressFrom = $addressFrom;

        return $this;
    }

    /**
     * @return Address
     */
    public function getAddressTo()
    {
        return $this->addressTo;
    }

    /**
     * @param Address $addressTo
     * @return $this
     */
    public function setAddressTo(Address $addressTo)
    {
        $this->addressTo = $addressTo;

        return $this;
    }

    /**
     * @return Goods
     */
    public function getGoods()
    {
        return $this->goods;
    }

    /**
     * @param Goods $goods
     * @return $this
     */
    public function setGoods($goods)
    {
        $this->goods = $goods;

        return $this;
    }
}