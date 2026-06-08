<?php
namespace Ipol\Catapulto\Bitrix\Adapter;

use \Ipol\Catapulto\Bitrix\Tools;
use \Ipol\Catapulto\Bitrix\Entity\DefaultGabarites;
use \Ipol\Catapulto\Core\Delivery\CargoItem;
use \Ipol\Catapulto\Core\Delivery\Cargo as BaseCargo;
use \Ipol\Catapulto\Core\Entity\Money;
use \Ipol\Catapulto\Core\Entity\Packing\MebiysDimMerger;

/**
 * Class Cargo
 * Generates Core Cargo from Bitrix items
 * @package namespace Ipol\Catapulto\Bitrix\Adapter
 */
class Cargo
{
    /**
     * @var
     * array of items formed in Bitrix
     */
    protected $items;

    /**
     * @var \Ipol\Catapulto\Core\Delivery\Cargo
     * The base cargo object
     */
    protected $cargo;

    protected $defaultGabarites;

    public function __construct(DefaultGabarites $defaultGabarites = null)
    {
        if (!is_null($defaultGabarites))
            $this->defaultGabarites = $defaultGabarites;
    }

    public function set($arItems)
    {
        $this->setItems($arItems);
        $this->formCargo();
        return $this;
    }

    /**
     * Generates Base Cargo from given Bitrix items
     * @throws \Exception
     */
    protected function formCargo()
    {
        if (empty($this->items))
        {
            throw new \Exception('No items to convert in '.get_class());
        }

        $this->cargo = new BaseCargo();

        $arItems = $this->reformItems();

        foreach ($arItems as $item)
        {
            // If we add check for !CAY_BUY - DON'T forget to place it in reformItems
            $obCargoItem = new CargoItem();
            $this->cargo->add(
                $obCargoItem
                    ->setGabs(
                        $item['DIMENSIONS']['LENGTH'],
                        $item['DIMENSIONS']['WIDTH'],
                        $item['DIMENSIONS']['HEIGHT']
                    )
                    ->setWeight($item['WEIGHT'])
                    ->setQuantity($item['QUANTITY'])
                    ->setCost(new Money($item['PRICE']))
                    ->setPrice(new Money($item['BASE_PRICE']))
            );
        }
    }

    protected function reformItems()
    {
        if (!empty($this->defaultGabarites))
        {
            $_arItems = $this->getItems();
            if ($this->defaultGabarites->getMode() === 'O') // Default gabarites for all order
            {
                // Array for \Ipol\Catapulto\Core\Entity\Packing::getSumDimensions()
                $arDimensions = array();
                $weight       = 0;
                $hasEmpty     = false;
                $ttlPrice     = 0;

                foreach ($_arItems as $key => $item)
                {
                    if(floatval($item['WEIGHT']) > 0)
                        $weight += $item['WEIGHT'] * $item['QUANTITY'];
                    else
                        $hasEmpty = true;

                    if(
                        $item['DIMENSIONS']['LENGTH'] && floatval($item['DIMENSIONS']['LENGTH']) > 0 &&
                        $item['DIMENSIONS']['WIDTH'] && floatval($item['DIMENSIONS']['WIDTH']) > 0 &&
                        $item['DIMENSIONS']['HEIGHT'] && floatval($item['DIMENSIONS']['HEIGHT']) > 0
                    )
                        $arDimensions []= array($item['DIMENSIONS']['LENGTH'],$item['DIMENSIONS']['WIDTH'],$item['DIMENSIONS']['HEIGHT'],$item['QUANTITY']);
                    else
                        $hasEmpty = true;

                    $ttlPrice += $item['PRICE'] * $item['QUANTITY'];
                }

                if ($hasEmpty)
                {
                    $packer = new MebiysDimMerger();
                    $arDimensions = $packer::getSumDimensions($arDimensions);

                    $_arItems = array(
                        Tools::makeSimpleGood(array(
                            'WEIGHT'  => max($weight,$this->defaultGabarites->getWeight()),
                            'LENGTH'  => max($this->defaultGabarites->getLength(),$arDimensions['L']),
                            'WIDTH'   => max($this->defaultGabarites->getWidth(), $arDimensions['W']),
                            'HEIGHT'  => max($this->defaultGabarites->getHeight(),$arDimensions['H']),
                            'PRICE'   => $ttlPrice,
                        ))
                    );
                }
            }
            else // Default gabarites for each good
            {
                foreach($_arItems as $key => $item)
                {
                    if(!floatval($item['WEIGHT']))
                        $_arItems[$key]['WEIGHT'] = $this->defaultGabarites->getWeight();
                    if(!floatval($item['DIMENSIONS']['LENGTH']))
                        $_arItems[$key]['DIMENSIONS']['LENGTH'] = $this->defaultGabarites->getLength();
                    if(!floatval($item['DIMENSIONS']['WIDTH']))
                        $_arItems[$key]['DIMENSIONS']['WIDTH'] = $this->defaultGabarites->getWidth();
                    if(!floatval($item['DIMENSIONS']['HEIGHT']))
                        $_arItems[$key]['DIMENSIONS']['HEIGHT'] = $this->defaultGabarites->getHeight();
                }
            }
            $arItems = $_arItems;
        }
        else
            $arItems = $this->getItems();

        return $arItems;
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return \Ipol\Catapulto\Core\Delivery\Cargo
     */
    public function getCargo()
    {
        if (empty($this->cargo))
            $this->formCargo();

        return $this->cargo;
    }

    /**
     * @param mixed $cargo
     */
    protected function setCargo($cargo)
    {
        $this->cargo = $cargo;
    }
}