<?php

namespace Ipol\Catapulto\Bitrix\Adapter;


use Ipol\Catapulto\Bitrix\Entity\DefaultGabarites;
use Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Bitrix\Handler\GoodsPicker;
use Ipol\Catapulto\Core\Order\Item;
use Ipol\Catapulto\Core\Order\ItemCollection;
use \Ipol\Catapulto\Core\Entity\Money;
use Ipol\Catapulto\OrderPropsHandler;
use Ipol\Catapulto\OrderPropsTable;

class OrderItems
{
    protected $coreItems;
    protected $options;

    public function __construct(Options $options)
    {
        $this->options   = $options;
        $this->coreItems = new ItemCollection();
        return $this;
    }

    public function fromOrder($bitrixId)
    {
        $arGoods = GoodsPicker::fromOrder($bitrixId);

        //standard Articul Prop
        $articul = 'ARTNUMBER';//$this->options->fetchArticul(); - from options if it exist
        //$barcode = $this->options->fetchBarcode();
        //$nameFromProp = trim($this->options->fetchNameFromProp()); //possible not working

        GoodsPicker::addBasketGoodProperties($arGoods,array($articul/*, $nameFromProp,$barcode*/));
        GoodsPicker::addGoodsQRs($arGoods,$bitrixId);

        $defGabarites = new DefaultGabarites();

        $customGabs = [];
        $orderProps = OrderPropsTable::getByBitrixId(intval($bitrixId));
        if ($orderProps && !empty($orderProps['CUSTOM_GABS'])) {
            $customGabs = @json_decode($orderProps['CUSTOM_GABS'],true);
        }

        foreach($arGoods as $arGood)
        {
            if ($arGood['DIMENSIONS'] && is_array($arGood['DIMENSIONS'])) {
                $arGood['HEIGHT'] = $arGood['DIMENSIONS']['HEIGHT'];
                $arGood['WIDTH'] = $arGood['DIMENSIONS']['WIDTH'];
                $arGood['LENGTH'] = $arGood['DIMENSIONS']['LENGTH'];
            }
            $arDimensions = array(
                'WEIGHT' => $arGood['WEIGHT'] ?? 0,
                'HEIGHT' => $arGood['HEIGHT'] ?? 0,
                'WIDTH'  => $arGood['WIDTH'] ?? 0,
                'LENGTH' => $arGood['LENGTH'] ?? 0
            );
            $isCustomDims = false;
            if ($defGabarites->getMode() == 'G') {
                if ($arDimensions['WEIGHT'] == 0) {
                    $arDimensions['WEIGHT'] = $defGabarites->getWeight();
                    $isCustomDims = true;
                }
                if ($arDimensions['HEIGHT'] == 0) {
                    $arDimensions['HEIGHT'] = $defGabarites->getHeight();
                    $isCustomDims = true;
                }
                if ($arDimensions['WIDTH'] == 0) {
                    $arDimensions['WIDTH'] = $defGabarites->getWidth();
                    $isCustomDims = true;
                }
                if ($arDimensions['LENGTH'] == 0) {
                    $arDimensions['LENGTH'] = $defGabarites->getLength();
                    $isCustomDims = true;
                }
            }

            if (isset($customGabs[$arGood['PRODUCT_ID']])) {
                $arDimensions = [
                    'WEIGHT' => floatval($customGabs[$arGood['PRODUCT_ID']]['we']),
                    'HEIGHT' => floatval($customGabs[$arGood['PRODUCT_ID']]['h']),
                    'WIDTH' => floatval($customGabs[$arGood['PRODUCT_ID']]['w']),
                    'LENGTH' => floatval($customGabs[$arGood['PRODUCT_ID']]['l']),
                ];
            }
            $obItem = new Item();
            $obItem->setName($arGood['NAME'])
                ->setQuantity($arGood['QUANTITY'])
                ->setId($arGood['PRODUCT_ID'])
                ->setWeight($arDimensions['WEIGHT'])
                ->setHeight($arDimensions['HEIGHT'])
                ->setWidth($arDimensions['WIDTH'])
                ->setLength($arDimensions['LENGTH'])
                ->setField('IsDangerous',false);
            if ($isCustomDims) $obItem->setField('gbsdefault', true);
            if (
                is_array($arGood['PROPERTIES'])
                && isset($arGoods['PROPERTIES']['ARTNUMBER'])
                && !empty($arGoods['PROPERTIES']['ARTNUMBER'])
            ) $obItem->setArticul($arGood['PROPERTIES']['ARTNUMBER']);

            // Some VAT magic
            $vatRate = intval($arGood['VAT_RATE'] * 100);
            if ($vatRate > 0 && $arGood['VAT_INCLUDED'] !== 'Y')
            {
                // VAT not included in good's price, add it, cause OZON API does not know this BX differences
                $realPrice   = new Money($arGood['PRICE']);
                $realVat     = Money::multiply($realPrice, floatval($arGood['VAT_RATE']));
                $resultPrice = Money::sum($realPrice, $realVat);

                $obItem->setPrice($resultPrice->getAmount())->setCost($resultPrice->getAmount());
            }
            else
            {
                $obItem->setPrice($arGood['PRICE'])->setCost($arGood['PRICE']);
            }
            $obItem->setVatRate($vatRate);

            if($articul){
                $obItem->setArticul($arGood['PROPERTIES'][$articul]);
            }

            if ($this->options->fetchUseIdAsArticul() == 'Y' && empty($obItem->getArticul()))
            {
                $obItem->setArticul($obItem->getId());
            }

            if (empty($obItem->getArticul()))
                $obItem->setArticul('');

            // Use prop value instead of item's name if exists
            /*if ($nameFromProp) {
                if (!empty($arGood['PROPERTIES'][$nameFromProp]))
                    $obItem->setName($arGood['PROPERTIES'][$nameFromProp]);
            }*/

            /*
            if($barcode){
                $obItem->setBarcode($arGood['PROPERTIES'][$barcode]);
            }

            if($arGood['QR']){
                $obItem->setProperty('QR',$arGood['QR']);
            }*/

            $this->getCoreItems()->add($obItem);
        }

        return $this;
    }

    public function fromArray($arItems)
    {
        foreach ($arItems as $item) {
            $obItem = new Item();
            $obItem->setName($item['name'])
                ->setPrice($item['price'])
                ->setCost($item['cost'])
                ->setQuantity($item['quantity'])
                ->setId($item['id'])
                ->setWeight($item['weight'])
                ->setHeight($item['height'])
                ->setWidth($item['width'])
                ->setLength($item['length'])
                ->setVatRate($item['vatRate'])
                ->setArticul($item['articul'])
                ->setBarcode($item['barcode']);

            foreach(array('IsDangerous'/*'oc','ccd','tnved'*/) as $property){
                switch ($property){
                    case 'IsDangerous' : $item[$property] = ($item[$property] === true || $item[$property] === 'true') ? true : false; break; // fk types
                }
                $obItem->setField($property,$item[$property]);
            }

            $this->getCoreItems()->add($obItem);
        }

        return $this;
    }

    /**
     * @return ItemCollection
     */
    public function getCoreItems()
    {
        return $this->coreItems;
    }

    /**
     * @param mixed $coreItems
     * @return $this
     */
    public function setCoreItems($coreItems)
    {
        $this->coreItems = $coreItems;

        return $this;
    }
}
