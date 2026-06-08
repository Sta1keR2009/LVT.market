<?php

namespace Ipol\Catapulto;


use Ipol\Catapulto\Bitrix\Tools;

class PostingHandler extends AbstractGeneral
{
    /**
     * @param $arRequest
     * for ajax-calls
     *
     * @throws \Exception
     */
    public static function getDocsAjax($arRequest)
    {
        $bxId    = intval($arRequest['bitrixId']);
        $arOrder = OrdersTable::getByBitrixId($bxId, ['KEY']);
        StatusHandler::checkStatus($arOrder['KEY']);

        $arResult['file']    = self::getOrderDocument($bxId, $arRequest['type']);
        $arResult['success'] = !empty($arResult['file']);

        echo Tools::jsonEncode($arResult);
    }

    /**
     * @param int    $bxId
     * @param string $sType
     *
     * @return string
     */
    protected static function getOrderDocument($bxId, $sType)
    {
        $sUrl    = '';
        $arOrder = OrdersTable::getByBitrixId($bxId, ['DOCUMENTS']);
        if (!empty($arOrder['DOCUMENTS'])) {
            $arDocs = unserialize($arOrder['DOCUMENTS']);
            foreach ($arDocs as $arDoc) {
                if ($arDoc['title'] === Tools::getMessage('DOCUMENT_TYPE_POA')) $arDoc['key'] = 'POA'; //Костыль т.к. от API нет параметра key
                if ($arDoc['key'] == $sType) {
                    $sUrl = $arDoc['url'];
                    break;
                }
            }
        }

        return $sUrl;
    }

    /**
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getDocsAjaxByBDId($arRequest)
    {
        $arResult    = [];
        $arBitrixIds = [];
        $obDBOrders  = OrdersTable::getList((array_filter(['filter' => ['=ID' => $arRequest['ids']]])));

        while ($arDBOrder = $obDBOrders->Fetch()) {
            if ($arDBOrder['OK']) {
                $arBitrixIds[$arDBOrder['BITRIX_ID']] = $arDBOrder['KEY'];
            }
        }

        if (!empty($arBitrixIds)) {
            foreach ($arBitrixIds as $id => $key) {
                StatusHandler::checkStatus($key);

                $arResult[$id]['file']    = self::getOrderDocument($id, $arRequest['type']);
                $arResult[$id]['success'] = !empty($arResult[$id]['file']);
            }
        }

        echo Tools::jsonEncode($arResult);
    }


    public static function saveCustomCargoData($cargoData) {
        $bxOrderId = intval($cargoData['bitrixId']);
        $cargoes = $cargoData['cargo'];
        $isChanged = $cargoData['changed'] === 'true';

        $orderData = OrderPropsTable::getByBitrixId($bxOrderId);
        $propsData = json_decode($orderData['OTHER'], true);
        if (!$propsData) $propsData = [];

        $propsData['customCargo'] = json_encode($cargoes);
        $propsData['need_reselect'] = $propsData['need_reselect'] ?? false;
        if ($isChanged) $propsData['need_reselect'] = true;
        $orderData['OTHER'] = json_encode($propsData);

        OrderPropsTable::saveProps($bxOrderId, $orderData);
        
        if (Tools::isModuleAjaxRequest()) {
            echo Tools::jsonEncode([
                'success' => true,
            ]);
        }
    }

    public static function clearCustomCargoData($cargoData) {
        $bxOrderId = intval($cargoData['bitrixId']);
        $orderData = OrderPropsTable::getByBitrixId($bxOrderId);
        $propsData = json_decode($orderData['OTHER'], true);
        if (!$propsData) $propsData = [];

        $propsData['customCargo'] = json_encode([]);
        $propsData['need_reselect'] = true;
        $orderData['OTHER'] = json_encode($propsData);

        OrderPropsTable::saveProps($bxOrderId, $orderData);

        echo Tools::jsonEncode([
            'success' => true,
        ]);
    }

}
