<?php


namespace Ipol\Catapulto\Core\Delivery;


use Ipol\Catapulto\Core\Delivery\Cargo as CoreCargo;
use Ipol\Catapulto\Core\Entity\FieldsContainer;

/**
 * Class Shipment
 * @package Ipol\Catapulto\Core
 * @subpackage Delivery
 * Basic Shipment: from-where-wares-tariff-result
 */
class Shipment
{
    use FieldsContainer;

    /**
     * @var Location
     */
    protected $from;
    /**
     * @var Location
     */
    protected $to;
    /**
     * @var CargoCollection;
     */
    protected $cargoes;
    /**
     * @var
     * By which tariff this shipment should be calculated
     */
    protected $tariff;
    /**
     * @var string
     */
    protected $pvzIdTo;
    /**
     * @var
     */
    protected $error;
    /**
     * @var string
     */
    protected $errorText;

    /**
     * @var mixed of something useful
     */
    protected $details = false;

    /**
     * @var TariffCollection
     * Answer from delivery service, divided by variants for selected tariff
     */
    protected $summary;

    /**
     * Shipment constructor.
     */
    public function __construct()
    {
        $this->cargoes  = new CargoCollection();
        $this->summary = new TariffCollection();
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     * @return $this
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorText()
    {
        return $this->errorText;
    }

    /**
     * @param string $errorText
     * @return $this
     */
    public function setErrorText($errorText)
    {
        $this->errorText = $errorText;

        return $this;
    }

    /**
     * @param Cargo $obCargo
     * @return $this
     */
    public function addCargo(CoreCargo $obCargo)
    {
        $this->cargoes->add($obCargo);

        return $this;
    }

    /**
     * @return Location
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param Location $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return Location
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param Location $to
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return CargoCollection
     */
    public function getCargoes()
    {
        return $this->cargoes;
    }

    /**
     * @param CargoCollection $cargoes
     * @return $this
     */
    public function setCargoes($cargoes)
    {
        $this->cargoes = $cargoes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * @param mixed $tariff
     * @return $this
     */
    public function setTariff($tariff)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return string
     */
    public function getPvzIdTo()
    {
        return $this->pvzIdTo;
    }

    /**
     * @param string $pvzIdTo
     * @return Shipment
     */
    public function setPvzIdTo($pvzIdTo)
    {
        $this->pvzIdTo = $pvzIdTo;
        return $this;
    }

    /**
     * @return TariffCollection
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param TariffCollection $summary
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @param bool $full
     * @return string
     * Returns Hash of shipment for Cache key
     */
    public function getHash($full = false)
    {
        $hash = serialize(array($this->getTariff(), $this->getTo(), $this->getFrom(), $this->getCargoes(), $this->getDetails()));
        return ($full) ? $hash : md5($hash);
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Resets summary for better calculation
     * @return $this
     */
    public function resetSummary()
    {
        $this->summary = new TariffCollection();

        return $this;
    }

}