<?php

namespace Ipol\Catapulto\Bitrix\Adapter;


use Bitrix\Sale\Location\Admin\LocationHelper;
use Ipol\Catapulto\Bitrix\Controller\Dadata;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\OrderPropsHandler;

class AddressTo extends Address
{

    public function fromOrder($bId,$deliveryType='courier')
    {
        $order    = \Ipol\Catapulto\Bitrix\Handler\Order::getOrderById($bId);
        $location = null;
        
        if ($deliveryType === 'courier') {
            $deliveryLocation = $order->getPropertyCollection()->getDeliveryLocation();
            if ($deliveryLocation) {
                $locationTo = $deliveryLocation->getValue();
                if ($locationTo) {
                    $location = new \Ipol\Catapulto\Bitrix\Adapter\Location($locationTo);
                    if ($location && $location->getCoreLocation()) {
                        $this->getCoreAddress()->setCountry($location->getCoreLocation()->getCountry())
                            ->setRegion($location->getCoreLocation()->getRegion())
                            ->setCity($location->getCoreLocation()->getName());
                    }
                }
            }
        }

        if(!$order)
        {
            throw new \Exception('Order '.$bId.' not found');
        }

        $arConnector = array();
        foreach(array('zip','line','street','house','flat') as $code)
        {
            $arConnector[$this->options->fetchOption($code)] = $code;
        }

        // $arProps = $order->loadPropertyCollection()->getArray();
        $arProps = $order->getPropertyCollection ()->getArray();
        $prepPVZ = false;

        foreach($arProps['properties'] as $property)
        {
            if(array_key_exists($property['CODE'],$arConnector))
            {
                $method = 'set'.ucfirst($arConnector[$property['CODE']]);
                if($value = array_pop($property['VALUE']))
                {
                    $this->getCoreAddress()->$method($value);
                }
            }

        }

        // Address parsing via dadata
        if (!empty($this->getCoreAddress()->getLine())) {

            $dadata = new Dadata();

            $addressLine = explode('#',$this->getCoreAddress()->getLine()); // Cut out the terminal code
            $queryAddress = [
                $this->getCoreAddress()->getCountry(),
                $this->getCoreAddress()->getRegion(),
                $this->getCoreAddress()->getCity(),
                trim($addressLine[0]),
            ];

            $suggest = $dadata->suggest('address', implode(', ',$queryAddress), 1);

            if (!empty($suggest['suggestions'])) {
                $result = $suggest['suggestions'][0]['data'];
                $this->getCoreAddress()
                    ->setCity($result['city_with_type'])
                    ->setStreet($result['street_with_type']);

                // building with block
                $building = trim(implode(' ', [
                    $result['house_type'],
                    $result['house'],
                    $result['block_type'],
                    $result['block']
                ]));

                $flat = trim(implode(' ', [
                    $result['flat_type'],
                    $result['flat']
                ]));

                $this->getCoreAddress()
                    ->setBuilding($building)
                    ->setFlat($flat);

                $this->getCoreAddress()->setField('isDadata', true);

            }
        }

        $this->getCoreAddress()->setComment($order->GetField('USER_DESCRIPTION'));
        
        // add zip code
        if ($location) {
            $zipCodeRes = LocationHelper::getZipByLocation($location->getBxCode(), ['filter' => ['SERVICE_CODE' => 'ZIP']]);
            if (($zip = $zipCodeRes->fetch()) && !empty($zip['XML_ID'])) {
                $this->getCoreAddress()->setZip($zip['XML_ID']);
            }
        }

        return $this;
    }

    public function fromOrderWithAddressLine($bId,$addressLine) {
        $order = \Ipol\Catapulto\Bitrix\Handler\Order::getOrderById($bId);

        if(!$order) {
            throw new \Exception('Order '.$bId.' not found');
        }

        if ($addressLine) {
            $dadata = new Dadata();
            $addressLine = explode('#',$addressLine); // Cut out the terminal code
            $suggest = $dadata->suggest('address', trim($addressLine[0]), 1);
            $currentAddressLine = '';

            if (!empty($suggest['suggestions'])) {
                $result = $suggest['suggestions'][0]['data'];
                $this->getCoreAddress()
                    ->setCity($result['settlement_with_type'] ?? $result['city_with_type'])
                    ->setStreet($result['street_with_type']);

                // building with block
                $building = trim(implode(' ', [
                    $result['house_type'],
                    $result['house'],
                    $result['block_type'],
                    $result['block']
                ]));

                $flat = trim(implode(' ', [
                    $result['flat_type'],
                    $result['flat']
                ]));

                $this->getCoreAddress()
                    ->setBuilding($building)
                    ->setFlat($flat);

                if ($result['postal_code']) {
                    $currentAddressLine = $result['postal_code'];
                    $this->getCoreAddress()->setZip($result['postal_code']);
                }
                if ($result['country']) {
                    if (!empty($currentAddressLine)) $currentAddressLine .= ', ' . $result['country'];
                    $this->getCoreAddress()->setCountry($result['country']);
                }
                if ($result['region_with_type']) {
                    if (!empty($currentAddressLine)) $currentAddressLine .= ', ' . $result['region_with_type'];
                    $this->getCoreAddress()->setRegion($result['region_with_type']);
                }
                if (!empty($currentAddressLine)) $currentAddressLine .= ', ';
                $currentAddressLine .= $addressLine[0].'#'.$addressLine[1];
                $this->getCoreAddress()->setField('dadata_unrestricted_value',$currentAddressLine);
                $this->getCoreAddress()->setField('pvz_address_filled', true);
            }
        }
        $this->getCoreAddress()->setComment($order->GetField('USER_DESCRIPTION'));
        return $this;
    }

}
