<?php


namespace Ipol\Catapulto\Core\Tools;


use Ipol\Catapulto\Core\Entity\Money;
use Ipol\Catapulto\Core\Order\ItemCollection;
use Ipol\Catapulto\Core\Tools\DiscountParser\MirrorLine;

class DiscountParser
{
    /**
     * @var string - 'RUB' | 'USD' etc
     */
    private $currency;
    /**
     * @var Money
     */
    private $remainingOrderDiscount;
    /**
     * @var MirrorLine[]
     */
    private $arMirror;
    /**
     * @var Money
     */
    private $orderPrice;

    /**
     * DiscountParser constructor.
     * @param Money $orderDiscount
     * @param ItemCollection $coreItemCollection
     */
    public function __construct(Money $orderDiscount, ItemCollection $coreItemCollection)
    {
        if ($orderDiscount->getAmount() && $coreItemCollection->getQuantity()) {
            $this->remainingOrderDiscount = $orderDiscount;
            $this->currency = $coreItemCollection->getFirst()->getPrice()->getCurrency();
            $this->orderPrice = new Money(0, $this->currency);
            $this->arMirror = [];
            $this->prepareMirror($coreItemCollection);

            if ($this->orderPrice->getAmount() < $this->remainingOrderDiscount->getAmount()) {
                $this->remainingOrderDiscount = $this->orderPrice;
            }
        }
    }

    /**
     * @param ItemCollection $coreItemCollection
     */
    protected function prepareMirror(ItemCollection $coreItemCollection): void
    {
        $coreItemCollection->reset();
        while ($elem = $coreItemCollection->getNext()) {
            $orderLinePrice = Money::multiply($elem->getPrice(), $elem->getQuantity());
            $this->arMirror[] = new MirrorLine($elem, $orderLinePrice->getAmount(), $this->currency);
            $this->orderPrice->add($orderLinePrice);
        }
        $coreItemCollection->reset();

        usort(
            $this->arMirror,
            function (MirrorLine $a, MirrorLine $b) {
                return $a->orderLinePrice - $b->orderLinePrice;
            }
        );
    }

    /**
     * Method will divide order discount and decrease prices of items in order
     */
    public function parseDiscountToProducts(): void
    {
        if (!$this->remainingOrderDiscount) {
            return;
        }
        $discountPercent = $this->remainingOrderDiscount->getAmount() / $this->orderPrice->getAmount();
        foreach ($this->arMirror as $line) {
            $line->computeDiscountForLine($discountPercent, $this->remainingOrderDiscount);
            if ($this->remainingOrderDiscount->getAmount() <= 0) {
                break;
            }
        }

        $this->lastRoundSequence();
        $this->lastRoundSequence(false);

        $this->applyLineDiscountsToItems();
    }

    protected function lastRoundSequence($skipMultipleItemLine = true): void
    {
        foreach ($this->arMirror as $line) {
            if ($this->remainingOrderDiscount->getAmount() <= 0) {
                return;
            }
            $line->parseForLastSequence($this->remainingOrderDiscount, $skipMultipleItemLine);
        }
    }

    protected function applyLineDiscountsToItems(): void
    {
        foreach ($this->arMirror as $line) {
            $line->itemLink->getPrice()->sub($line->discount);
        }
    }
}
