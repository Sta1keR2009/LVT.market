<?php


namespace Ipol\Catapulto\Core\Delivery;


use Exception;
use Ipol\Catapulto\Core\Entity\Collection;

/**
 * Class ShipmentCollection
 * @package Ipol\Catapulto\Core
 * @subpackage Delivery
 * Set of Shipments for delivery. Main point: order can be split into parts (for complete delivery calculation we use merge).
 * If order is solid and shipped in single shipment - it will be Collection with one Shipment - it's fine
 * @method false|Shipment getFirst
 * @method false|Shipment getNext
 * @method false|Shipment getLast
 */
class ShipmentCollection extends Collection
{
    /**
     * @var Shipment[]
     */
    protected $shipments;
    /**
     * @var int|string|int[]|string[]
     * Tariff priority: calculation will be done by first successful match (or by direct set in case it's not array) TODO maybe work only with array
     * Used in merge
     */
    protected $tariffPriority;
    /**
     * @var false|mixed
     * If tariff has more than one variants - will be accepted one from here
     */
    protected $variantPriority = false;

    public function __construct()
    {
        parent::__construct('shipments');
    }

    /**
     * @return int|int[]|string|string[]
     */
    public function getTariffPriority()
    {
        return $this->tariffPriority;
    }

    /**
     * @param int|int[]|string|string[] $tariffPriority
     * @return $this
     */
    public function setTariffPriority($tariffPriority)
    {
        $this->tariffPriority = $tariffPriority;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVariantPriority()
    {
        return $this->variantPriority;
    }

    /**
     * @param mixed $variantPriority
     * @return $this
     */
    public function setVariantPriority($variantPriority)
    {
        $this->variantPriority = $variantPriority;

        return $this;
    }

    /**
     * @param Shipment $shipment
     * @return $this
     */
    public function addShipment($shipment)
    {
        $this->add($shipment);

        return $this;
    }

    /**
     * @return array of tariffs in shipments
     * Used when we don't exactly know all existing tariffs
     */
    public function getShipmentsTariffs()
    {
        $arTariffs = array();

        $this->reset();
        while($shipment = $this->getNext())
        {
            if($shipment->getError()){
                continue;
            } else {
                $shipment->getSummary()->reset();
                while ($obTariff = $shipment->getSummary()->getNext())
                {
                    if(
                        (!$this->getVariantPriority() || $obTariff->getVariant() == $this->getVariantPriority()) &&
                        !in_array($obTariff->getId(), $arTariffs)
                    )
                    {
                        $arTariffs [] = $obTariff->getId();
                    }
                }
            }
        }

        return $arTariffs;
    }

    /**
     * @return false|array ('price','termMin','termMax')
     * @throws Exception
     */
    public function merge()
    {
        $this->reset();
        $merger = new ShipmentMerger();

        while ($shipment = $this->getNext())
        {
            if($shipment->getError()) {
                throw new Exception($shipment->getErrorText());
            } else {
                $essence = false;
                if (isset($this->tariffPriority))
                {
                    if(is_array($this->tariffPriority))
                    {
                        foreach($this->tariffPriority as $tariffId)
                        {
                            $essence = $this->magic_parceTarif($shipment, $tariffId, $this->getVariantPriority(), true);
                            if($essence)
                                break;
                        }
                    }
                    else
                        $essence = $this->magic_parceTarif($shipment, $this->tariffPriority, $this->getVariantPriority(),true);
                }
                else
                    $essence = $this->magic_parceTarif($shipment,false, $this->getVariantPriority());
            }
            if($essence){
                $termMax = false;
                $termMin = $essence['term'];
                if(strpos($essence['term'],'-') != false)
                {
                    $essence['term'] = explode('-', $essence['term']);
                    $termMin = trim($essence['term'][0]);
                    $termMax = trim($essence['term'][1]);
                }

                $merger->addShipment($essence['price'], $termMin, $termMax, $essence['detail']);
            }
            else
                throw new Exception(
                    ($this->getError())?
                        (is_array($this->getError())? implode(',', $this->getError()) : $this->getError()) :
                        'No tariffs in shipment found');
        }

        return $merger->getMergedArray();
    }

    /**
     * @param Shipment $shipment
     * @param bool $defId
     * @param bool $variant
     * @param bool $breakOnError
     * @return array|false
     * Parses final shipment calculation result (Tariffs),
     * keeps default tariff (defId - that we want to get),
     * with variant preference (variant), or first in list, if default is not present
     */
    protected function magic_parceTarif($shipment, $defId = false, $variant = false, $breakOnError = false)
    {
        $essence = false;

        $shipment->getSummary()->reset();
        while($obTariff = $shipment->getSummary()->getNext())
        {
            if(
                !$defId ||
                $obTariff->getId() == $defId
            )
            {
                if ($obTariff->getError())
                {
                    $this->setError($obTariff->getErrorText());
                    if($breakOnError)
                        break;
                    else
                        continue;
                }
                else {
                    if(!$variant || $obTariff->getVariant() == $variant) {
                        $essence = array(
                            'price' => $obTariff->getPrice()->getAmount(),
                            'term' => $obTariff->getTerm(),
                            'detail' => $obTariff->getDetails()
                        );
                        break;
                    }
                }
            }
        }

        return $essence;
    }

    /**
     * @return string
     * Returns Hash for Cache key
     */
    public function getHash()
    {
        $hash = '';
        $this->reset();
        while ($obShipment = $this->getNext()){
            $hash .= $obShipment->getHash(true);
        }
        return $hash;
    }


}