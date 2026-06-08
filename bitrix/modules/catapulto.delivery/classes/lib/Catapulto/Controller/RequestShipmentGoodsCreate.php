<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\Part\ShipmentNpData\Item;
use Ipol\Catapulto\Api\Entity\Request\Part\ShipmentNpData\ItemCollection;
use Ipol\Catapulto\Api\Entity\Request\ShipmentGoodsData as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\ShipmentGoodsCreateResult as ResultObj;
use Ipol\Catapulto\Core\Order\Order;


class RequestShipmentGoodsCreate extends AutomatedCommonRequest
{

    /** @var Order  */
    protected $coreOrder;

    /**
     * @param ResultObj $resultObj
     * @param Order $cOrder
     */
    public function __construct(ResultObj $resultObj, Order $cOrder)
    {
        parent::__construct($resultObj);
        $this->coreOrder = $cOrder;
    }

    /**
     * @return string
     */
    public function getSelfHash(): string
    {
        $orderString = serialize($this->coreOrder); //TODO change it to something better-better ACTUALLY IT SHOULD NOT BE CACHED
        return md5($orderString);
    }

    /**
     * @return $this
     */
    public function convert()
    {
        $data = new RequestObj();

        $data
            ->setCalcResult($this->coreOrder->getField('rateResultId'))
            ->setSumToPay(0);

        $itemCollection = new ItemCollection();
        $items = $this->coreOrder->getItems();
        $items->reset();

        $cargoes = $this->coreOrder->getField('cargoes_ids');
        $customCargoData = $this->coreOrder->getField('customCargoData');
        if ( is_array($cargoes) && count($cargoes) > 0 ) {
            if (count($cargoes) == 1) {
                $defaultCargoId = $cargoes[0];
                if (is_array($customCargoData) && isset($customCargoData[$defaultCargoId])) {
                    while ($product = $items->getNext()) {
                        $productId = intval($product->getId());
                        foreach ($customCargoData[$defaultCargoId]['items'] as $itm) {
                            if (intval($itm['id']) == $productId) {
                                $item = new Item();
                                $item->setName($product->getName())
                                    ->setQuantity(floatval($itm['qty']))
                                    ->setUnitValue($product->getPrice()->getAmount())
                                    ->setWeight(floatval($itm['weight']))
                                    ->setLength(floatval($itm['length']))
                                    ->setHeight(floatval($itm['height']))
                                    ->setWidth(floatval($itm['width']))
                                    ->setArticul($itm['articul'])
                                    ->setCargoId($defaultCargoId)
                                ;

                                $itemCollection->add($item);
                            }

                        }
                    }
                } else {
                    while ($product = $items->getNext()) {
                        $item = new Item();
                        $item->setName($product->getName())
                            ->setQuantity($product->getQuantity())
                            ->setUnitValue($product->getPrice()->getAmount())
                            ->setWeight($product->getWeight())
                            ->setLength($product->getLength())
                            ->setHeight($product->getHeight())
                            ->setWidth($product->getWidth())
                            ->setArticul($product->getArticul())
                            ->setCargoId($defaultCargoId)
                        ;

                        $itemCollection->add($item);
                    }
                }

            } else {
                //check count cargoes....
                $customCargoData = $this->coreOrder->getField('customCargoData');
                if (!$customCargoData) {
                    throw new \Exception('Wrong cargo data, reselect order tarif!');
                }
                $customCargoIds = array_keys($customCargoData);

                $df = array_diff($cargoes, $customCargoIds);
                if (count($df) > 0) {
                    throw new \Exception('Wrong cargo data (count), reselect order tarif!');
                }

                while ($product = $items->getNext()) {
                    $productId = intval($product->getId());
                    foreach ($customCargoData as $crg) {
                        foreach ($crg['items'] as $itm) {
                            if (intval($itm['id']) == $productId) {
                                $item = new Item();
                                $item->setName($product->getName())
                                    ->setQuantity(floatval($itm['qty']))
                                    ->setUnitValue($product->getPrice()->getAmount())
                                    ->setWeight(floatval($itm['weight']))
                                    ->setLength(floatval($itm['length']))
                                    ->setHeight(floatval($itm['height']))
                                    ->setWidth(floatval($itm['width']))
                                    ->setArticul($itm['articul'])
                                    ->setCargoId($crg['id'])
                                ;

                                $itemCollection->add($item);
                            }

                        }
                    }
                }

            }
        }


        $data->setItems($itemCollection);

        $this->setRequestObj($data);

        return $this;

    }


}
