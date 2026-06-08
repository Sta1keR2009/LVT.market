<?php


namespace Ipol\Catapulto\Core\Order;


use Ipol\Catapulto\Core\Entity\Money;
use Ipol\Catapulto\Core\Entity\FieldsContainer;

/**
 * Class Payment
 * @package Ipol\Catapulto\Core
 * @subpackage Order
 */
class Payment
{
    use FieldsContainer;

    /**
     * @var Money
     * Payment for delivery
     */
    protected $delivery;
    /**
     * @var Money
     * Payment for order goods
     */
    protected $goods;
    /**
     * @var Money
     * Estimated order cost for insurance
     */
    protected $estimated;
    /**
     * @var Money
     * how many was already payed online, etc
     */
    protected $payed;

    /**
     * @var int|null
     * default goods VAT percentage
     */
    protected $ndsDefault;
    /**
     * @var int|null
     * delivery VAT percentage
     */
    protected $ndsDelivery;

    /**
     * @var string - 'Cash','Card','Bill','other', etc
     * Payment type
     */
    protected $type;

    /**
     * @var bool
     * 1 for cashless, 0 for cash
     */
    protected $isBeznal;
    
    /**
     * @var bool
     * 1 for NP, 0 without NP
     */
    protected $isNp;
    
    /**
     * @var bool
     * 1 for COD, 0 without COD
     */
    protected $isCod;
    
    /**
     * @var bool
     * 1 for SMS, 0 without SMS service
     */
    protected $isSmsAmount;

    /**
     * @return Money
     * Complete price check: if payment is Cashless - returns Money(0), else - (delivery cost + goods price - already payed)
     */
    public function getPrice(): Money
    {
        return ($this->getIsBeznal())?
            new Money(0) :
            $this->getNominalPrice();
    }

    /**
     * @return Money
     */
    public function getNominalPrice(): Money
    {
        $tmpSum = Money::sum($this->getDelivery(), $this->getGoods());
        return Money::subtract($tmpSum, $this->getPayed());
    }

    /**
     * @return Money
     */
    public function getCost(): Money
    {
        return ($this->getEstimated())? : $this->getGoods();
    }

    /**
     * @return Money|null - null=not set, Money can be set 0 currency_units (RUB|USD) for reason
     */
    public function getEstimated(): ?Money
    {
        return $this->estimated;
    }

    /**
     * @param Money|null $estimated
     * @return $this
     */
    public function setEstimated(?Money $estimated): Payment
    {
        $this->estimated = $estimated;

        return $this;
    }
    
    /**
     * @return bool
     */
    public function getIsNp(): bool
    {
        return $this->isNp;
    }
    
    /**
     * @return bool
     */
    public function getIsCod(): bool
    {
        return $this->isCod;
    }
    
    /**
     * @return bool
     */
    public function getIsSmsAmount(): bool
    {
        return $this->isSmsAmount;
    }

    /**
     * @return bool
     */
    public function getIsBeznal(): bool
    {
        return $this->isBeznal;
    }

    /**
     * @param bool $isBeznal
     * @return $this
     */
    public function setIsBeznal(bool $isBeznal): Payment
    {
        $this->isBeznal = $isBeznal;

        return $this;
    }
    
    /**
     * @param bool $isNp
     * @return $this
     */
    public function setIsNp(bool $isNp): Payment
    {
        $this->isNp = $isNp;
        
        return $this;
    }
    
    /**
     * @param bool $isCod
     * @return $this
     */
    public function setIsCod(bool $isCod): Payment
    {
        $this->isCod = $isCod;
        
        return $this;
    }
    
    /**
     * @param bool $isSmsAmount
     * @return $this
     */
    public function setIsSmsAmount(bool $isSmsAmount): Payment
    {
        $this->isSmsAmount = $isSmsAmount;
        
        return $this;
    }

    /**
     * @return Money
     */
    public function getDelivery(): Money
    {
        return $this->delivery ?? new Money(0);
    }

    /**
     * @param Money $delivery
     * @return $this
     */
    public function setDelivery(Money $delivery): Payment
    {
        $this->delivery = $delivery;

        return $this;
    }

    /**
     * @return Money
     */
    public function getGoods(): Money
    {
        return $this->goods ?? new Money(0);
    }

    /**
     * @param Money $goods
     * @return $this
     */
    public function setGoods(Money $goods): Payment
    {
        $this->goods = $goods;

        return $this;
    }

    /**
     * @return Money
     */
    public function getPayed(): Money
    {
        return $this->payed ?? new Money(0); //TODO bad practice to return money with static currency
    }

    /**
     * @param Money $payed
     * @return $this
     */
    public function setPayed(Money $payed): Payment
    {
        $this->payed = $payed;

        return $this;
    }


    /**
     * @return string
     */
    public function getType(): string
    {
        return ($this->type) ? : 'other';
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): Payment
    {
        $arTypes = ['Cash','Card','Bill','other'];

        $this->type = (in_array($type, $arTypes)) ? $type : 'other';

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNdsDefault(): ?int
    {
        return $this->ndsDefault;
    }

    /**
     * @param int|null $ndsDefault
     * @return $this
     */
    public function setNdsDefault(?int $ndsDefault): Payment
    {
        $this->ndsDefault = $ndsDefault;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNdsDelivery(): ?int
    {
        return $this->ndsDelivery;
    }

    /**
     * @param int|null $ndsDelivery
     * @return $this
     */
    public function setNdsDelivery(?int $ndsDelivery): Payment
    {
        $this->ndsDelivery = $ndsDelivery;

        return $this;
    }
}