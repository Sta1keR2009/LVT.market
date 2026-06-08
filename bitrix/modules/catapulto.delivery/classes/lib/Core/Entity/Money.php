<?php


namespace Ipol\Catapulto\Core\Entity;


/**
 * Class Money
 * @package Ipol\Catapulto\Core
 * @subpackage Entity
 */
class Money
{
    /**
     * @var int inner container for precise storing money amount. Uses minimal unit (cent, kopeyka etc)
     */
    protected $amount;
    /**
     * @var string - ISO-4217 currency code
     */
    protected $currency;
    /**
     * @var int number of decimal places for this currency (2 for RUB, for instance: 145.50 RUB - 50 - 2 decimal places)
     */
    protected static $decimal; //basically its 2, but for rare currencies with other rules you can extend this class and change it

    /**
     * Money constructor.
     * @param float $amount
     * @param string $currency
     */
    public function __construct(float $amount, string $currency = 'RUB')
    {
        self::$decimal = 2;
        $this->setAmount($amount);
        $this->currency = $currency;
    }

    /**
     * Do not use this function!
     * It's public only for inner reasons.
     * For calculations use static functions of this class
     * To get money amount use getAmount function
     * @return int
     */
    private function getPreciseInnerAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $addSum
     * @return $this
     */
    private function increasePreciseInnerAmount(int $addSum): Money
    {
        $this->amount += $addSum;
        return $this;
    }

    /**
     * @param int $multiplier
     * @return $this
     */
    public function multiplyAmount(int $multiplier): Money
    {
        $this->amount *= $multiplier;
        return $this;
    }

    /**
     * @param float $amount
     * @return Money
     */
    public function setAmount(float $amount): Money
    {
        $preRoundFloatPrecision = (ini_get('precision') > 0) ? floor(ini_get('precision') / 2) : 7;
        $strAmount = number_format($amount, $preRoundFloatPrecision, '.', '');

        //this two steps replace flour and multiply by 10^decimal
        $strAmount = substr($strAmount, 0, strpos($strAmount, '.') + self::$decimal + 1);
        $strAmount = str_replace('.', '', $strAmount);

        $this->amount = intval($strAmount);
        return $this;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return (float)number_format($this->amount / pow(10, self::$decimal), self::$decimal, '.', '');
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * returns Money representation for sum of all given Money args
     * @param Money ...$moneys
     * @return Money
     */
    public static function sum(Money ...$moneys): Money
    {
        $ret = new Money(0, $moneys[0]->getCurrency());
        foreach ($moneys as $arg) { //TODO replace with add()
            $ret->increasePreciseInnerAmount($arg->getPreciseInnerAmount());
        }
        return $ret;
    }

    /**
     * @param Money ...$moneys
     * @return $this
     */
    public function add(Money ...$moneys): Money
    {
        foreach ($moneys as $arg) {
            if($arg->getCurrency() === $this->getCurrency()) { //TODO add else option
                $this->increasePreciseInnerAmount($arg->getPreciseInnerAmount());
            }
        }
        return $this;
    }

    /**
     * returns result of subtraction all of arguments from first one
     * @param Money ...$moneys
     * @return Money
     */
    public static function subtract(Money ...$moneys): Money
    {
        $ret = new Money(0, $moneys[0]->getCurrency());
        $ret->increasePreciseInnerAmount($moneys[0]->getPreciseInnerAmount());

        foreach (array_slice($moneys, 1) as $arg) {
            $ret->increasePreciseInnerAmount((-1) * $arg->getPreciseInnerAmount());
        }
        return $ret;
    }

    /**
     * Subtract all of arguments from current object
     * @param Money ...$moneys
     * @return Money
     */
    public function sub(Money ...$moneys): Money
    {
        foreach ($moneys as $arg) {
            $this->increasePreciseInnerAmount((-1) * $arg->getPreciseInnerAmount());
        }
        return $this;
    }

    /**
     * @param Money $money
     * @param int $multiplier
     * @return static
     */
    public static function multiply(Money $money, $multiplier): Money
    {
        if (gettype($multiplier) != 'integer') {
            trigger_error('$multiplier must be type int, ' .gettype($multiplier) . 'given', E_USER_WARNING);
            $multiplier = round($multiplier);
        }
        $ret = new Money(0, $money->getCurrency());
        $ret->increasePreciseInnerAmount($money->getPreciseInnerAmount());

        return $ret->multiplyAmount($multiplier);
    }

}