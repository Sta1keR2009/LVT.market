<?php

namespace Ipol\Catapulto\Bitrix\Controller;

use Ipol\Catapulto\Bitrix\Entity\BasicResponse;
use Ipol\Catapulto\Bitrix\Entity\ErrorCollector;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Core\Order\Order;

class Contact extends AbstractController
{
    protected $iso = 'ru';

    public function __construct()
    {
        parent::__construct(CATAPULTO_DELIVERY, CATAPULTO_DELIVERY_LBL);
    }
    
    public function getContactInfoByOrder(Order &$cOrder): BasicResponse
    {
        $obReturn = new BasicResponse();
        
        $zip = 0;
        $city = '';
        $fiasLevel = null;
        $cityFiasId = null;
        $settlementType = null;
        $settlementFiasId = null;
        
        $dadata = $cOrder->getField('dadata');
        if (!empty($dadata)) {
            $zip = $dadata['zip'] ?? 0;
            $city = $dadata['city'] ?? '';
            
            $fiasLevel = $dadata['fias_level'];
            $cityFiasId = $dadata['city_fias_id'];
            $settlementType = $dadata['settlement_type'];
            $settlementFiasId = $dadata['settlement_fias_id'];
        }
        if (!empty($cOrder->getAddressTo()->getZip())) $zip = intval($cOrder->getAddressTo()->getZip());
        if (!empty($cOrder->getAddressTo()->getCity())) $city = $cOrder->getAddressTo()->getCity();
        
        $receiverLocId = $cOrder->getField('receiver_loc_id');
        
        if ( ($receiverLocId == 0) || ($zip == 0) ) {
            // update zip and code of location from api
            $obResponse = $this->getGeo($zip, $city, $cityFiasId, $settlementFiasId, $fiasLevel, $settlementType);
            if ($obResponse->isSuccess() && $obResponse->getResponse()->getGeo()->getQuantity() > 0) {
                // update zip && locality_id
                $cOrder->getAddressTo()->setCode($obResponse->getResponse()->getGeo()->getFirst()->getId());
                $cOrder->getAddressTo()->setZip($obResponse->getResponse()->getGeo()->getFirst()->getZip());
            } else {
                $obReturn->setSuccess(false)
                    ->setErrorText(Tools::getMessage('ERROR_GEO_NOT_FOUND') . $cOrder->getAddressTo()->getCity());
                return $obReturn;
            }
        } else {
            $cOrder->getAddressTo()->setCode($receiverLocId);
            $cOrder->getAddressTo()->setZip($zip);
        }
        
        return $obReturn;
    }

    /**
     * Update contact data in catapulto
     * @param int $id
     * @param Order $cOrder
     *
     * @return BasicResponse
     */
    public function contactCreate(Order &$cOrder)
    {
        $obReturn = $this->getContactInfoByOrder($cOrder);
        
        if($obReturn->isSuccess()) {
            $obUpdateResponse = $this->application->contactCreate($cOrder);
            if ($obUpdateResponse->isSuccess()) {
                $obReturn->setData($obUpdateResponse->getResponse());
                // save contact id
                $cOrder->getBuyers()->getFirst()->setField('receiverId', $obUpdateResponse->getResponse()->getId());
            }
            else {
                return new ErrorCollector($obUpdateResponse);
            }
        }

        return $obReturn;
    }

    /**
     * Update contact data in catapulto
     * @param int $id
     * @param Order $cOrder
     *
     * @return BasicResponse
     */
    public function contactUpdate(int $id, Order &$cOrder)
    {
        $obReturn = $this->getContactInfoByOrder($cOrder);
        
        if($obReturn->isSuccess()) {
            $obUpdateResponse = $this->application->contactUpdate($id, $cOrder);
            if ($obUpdateResponse->isSuccess()) {
                $obReturn->setData($obUpdateResponse->getResponse());
            }
            else {
                return new ErrorCollector($obUpdateResponse);
            }
        }

        return $obReturn;
    }

    /**
     * Get geo for contact update request
     *
     * @param $term
     *
     * @return \Ipol\Catapulto\Catapulto\Entity\GeoResult
     */
    private function getGeo($term, $cityname, $cityFiasId = null, $settlementFiasId = null, $fiasLevel = null, $settlementType = null) {
        $obResponse = $this->application->geo($term, $cityname, $this->iso, 1, $cityFiasId, $settlementFiasId, $fiasLevel, $settlementType);
        return $obResponse;
    }

    public static function prepareForDB(Order $cOrder) {
        $convert = array (
            'id' => $cOrder->getBuyers()->getFirst()->getField('receiverId') ?? '',
            'country' => '',
            'locality' => $cOrder->getAddressTo()->getCity() ?? '',
            'locality_type' => '',
            'state_province' => '',
            'region1' => '',
            'region1_type' => '',
            'region2' => '',
            'region2_type' => '',
            'region3' => '',
            'region3_type' => '',
            'street' => $cOrder->getAddressTo()->getStreet() ?? '',
            'street_type' => '',
            'building' => $cOrder->getAddressTo()->getBuilding() ?? '',
            'door_number' => $cOrder->getAddressTo()->getFlat() ?? '',
            'zip' => $cOrder->getAddressTo()->getZip() ?? '',
            'comment' => $cOrder->getAddressTo()->getComment() ?? '',
            'address_line_1' => $cOrder->getAddressTo()->getLine() ?? '',
            'address_line_2' => '',
        );
        return $convert;
    }
}
