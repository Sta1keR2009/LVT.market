<?php


namespace Ipol\Catapulto\Core\Delivery;


use Ipol\Catapulto\Core\Entity\Collection;
use Ipol\Catapulto\Core\Entity\Money;
use Ipol\Catapulto\Core\Entity\Packing\MebiysDimMerger;

/**
 * Class CargoCollection
 * @package Ipol\Catapulto\Core
 * @subpackage Delivery
 * @method false|Cargo getFirst
 * @method false|Cargo getNext
 * @method false|Cargo getLast
 * Collection of Cargoes - used for keeping record of dividing for places
 */
class CargoCollection extends Collection
{
    /**
     * @var array
     */
    protected $Cargoes;
    /**
     * @var MebiysDimMerger|mixed
     */
    protected $packer;

    /**
     * CargoCollection constructor.
     * @param string $packer - full class name
     */
    public function __construct($packer = false)
    {
        parent::__construct('Cargoes');
        $this->packer = $packer ? new $packer : new MebiysDimMerger();
    }

    /**
     * @return Money total price to be payed for items in all cargoes
     */
    public function getTotalPrice()
    {
        $this->reset();
        $ttlPrice = new Money(0);
        while ($obCargo = $this->getNext()) {
            $ttlPrice = Money::sum($ttlPrice, $obCargo->getTotalPrice());
        }

        return $ttlPrice;
    }

    /**
     * @return Money total estimated cost for insurance of all cargoes
     */
    public function getTotalCost()
    {
        $this->reset();
        $ttlCost = new Money(0);
        while ($obCargo = $this->getNext()) {
            $ttlCost = Money::sum($ttlCost, $obCargo->getTotalCost());
        }

        return $ttlCost;
    }

    /**
     * @return int
     */
    public function getTotalWeight()
    {
        $this->reset();
        $weight = 0;

        while ($obCargo = $this->getNext()) {
            $weight += $obCargo->getWeight();
        }

        return $weight;
    }

    /**
     * @return float
     */
    public function getTotalVolume()
    {
        $this->reset();
        $volume = 0;

        while ($obCargo = $this->getNext()) {
            $volume += $obCargo->getVolume();
        }

        return $volume;
    }

    /**
     * @return array|int[]|string[]
     */
    public function getTotalDimensions()
    {
        $arGabs = array();
        $this->reset();

        while ($obCargo = $this->getNext()) {
            $cargoGabarites = $obCargo->getDimensions();
            $arGabs[] = array($cargoGabarites['L'], $cargoGabarites['W'], $cargoGabarites['H'], 1);
        }

        return $this->packer::getSumDimensions($arGabs);
    }

    /**
     * Makes CargoCollection from associative array
     * @param array $data
     * @return $this
     */
    public function fromArray($data)
    {
        if (!empty($data['cargoes']) && is_array($data['cargoes'])) {
            foreach ($data['cargoes'] as $cargo) {
                $coreCargo = new Cargo();
                $this->add($coreCargo->fromArray($cargo));
            }
        }

        /*if (!empty($data['fields']) && is_array($data['fields'])) {
            $this->setFields($data['fields']);
        }*/

        return $this;
    }

    /**
     * Returns CargoCollection data as associative array
     * @return array
     */
    public function toArray()
    {
        $data = [
            'cargoes' => [],
            'fields'  => null,
        ];

        $this->reset();
        while ($cargo = $this->getNext()) {
            $data['cargoes'][] = $cargo->toArray();
        }

        /*if (!empty($this->getContainer())) {
            foreach($this->getContainer() as $key => $val) {
                $data['fields'][$key] = $val;
            }
        }*/

        return $data;
    }

}
