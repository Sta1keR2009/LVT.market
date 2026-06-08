<?php

namespace Ipol\Catapulto\Core\Tools\DiscountParser;

use Ipol\Catapulto\Core\Entity\Money;
use Ipol\Catapulto\Core\Order\Item;

class MirrorLine
{
    /**
     * @var Item
     */
    public $itemLink;
    /**
     * @var int
     */
    public $quantity;
    /**
     * @var float
     */
    public $orderLinePrice;
    /**
     * @var Money
     */
    public $discount;
    /**
     * @var string
     */
    public $currency;

    public function __construct(Item $itemLink, float $orderLinePrice, string $currency)
    {
        $this->itemLink = $itemLink;
        $this->quantity = $itemLink->getQuantity();
        $this->orderLinePrice = $orderLinePrice;
        $this->currency = $currency;
        $this->discount = new Money(0, $currency);
    }

    public function computeDiscountForLine(float $discountPercent, Money $remainingOrderDiscount)
    {
        $curLineDiscount = new Money($this->orderLinePrice * $discountPercent, $this->currency);
        if ($curLineDiscount->getAmount() > $remainingOrderDiscount->getAmount()) {
            $curLineDiscount = $remainingOrderDiscount;
        }

        if ($this->quantity !== 1) {
            $this->parseDiscountOnLineWithMultipleItems($curLineDiscount, $remainingOrderDiscount);
        }
        $this->discount->add($curLineDiscount);
        $remainingOrderDiscount->sub(Money::multiply($this->discount, $this->quantity));
    }

    protected function parseDiscountOnLineWithMultipleItems(Money $curLineDiscount, Money $remainingOrderDiscount)
    {
        $leftDiscount = clone $rightDiscount = clone $curLineDiscount;
        $cent = new Money(0.01, $this->currency);
        while (
            fmod($leftDiscount->getAmount() * 100, $this->quantity) != 0 &&
            fmod($rightDiscount->getAmount() * 100, $this->quantity) != 0 &&
            (
                $leftDiscount->getAmount() > 0 ||
                (
                    $rightDiscount->getAmount() > $this->orderLinePrice &&
                    Money::multiply($rightDiscount, $this->quantity) > $remainingOrderDiscount->getAmount()
                )
            )
        ) {
            $leftDiscount = $leftDiscount->sub($cent);
            $rightDiscount = $rightDiscount->add($cent);
        }
        if (fmod($leftDiscount->getAmount() * 100, $this->quantity) == 0 && $leftDiscount->getAmount() > 0) {
            $curLineDiscount->setAmount($leftDiscount->getAmount() / $this->quantity);
        } elseif (
            fmod($rightDiscount->getAmount() * 100, $this->quantity) == 0 &&
            $rightDiscount->getAmount() <= $this->orderLinePrice &&
            Money::multiply($rightDiscount, $this->quantity)->getAmount() <= $remainingOrderDiscount->getAmount()
        ) {
            $curLineDiscount->setAmount($rightDiscount->getAmount() / $this->quantity);
        } else {
            $this->uniteProductLine();
        }
    }

    protected function uniteProductLine(): void
    {
        $this->itemLink->setPrice(new Money($this->orderLinePrice, $this->currency));
        $this->itemLink->setName($this->itemLink->getName() . ' x ' . $this->quantity);
        $this->itemLink->setWeight($this->itemLink->getWeight() * $this->quantity);
        $this->itemLink->setQuantity(1);
        $this->discount->multiplyAmount($this->quantity);
        $this->quantity = 1;
    }

    public function parseForLastSequence(Money $remainingOrderDiscount, $skipMultipleItemLine = true)
    {
        if (($skipMultipleItemLine && $this->quantity !== 1) || $this->orderLinePrice <= 0) {
            return;
        }
        $this->computeDiscountForLine(1, $remainingOrderDiscount);
    }

}