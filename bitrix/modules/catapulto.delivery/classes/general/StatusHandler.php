<?php

namespace Ipol\Catapulto;


use Ipol\Catapulto\Admin\Logger;
use Ipol\Catapulto\Bitrix\Controller\Status;
use Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Bitrix\Handler\Order;
use Ipol\Catapulto\Bitrix\MailSender;
use Ipol\Catapulto\Bitrix\Tools;

//use Ipol\Catapulto\Catapulto\Entity\TrackingListResult;
//use Ipol\Catapulto\Catapulto\Entity\TrackingResult;


IncludeModuleLangFile(__FILE__);

class StatusHandler extends AbstractGeneral
{
    /**
     * Финальные статусы заказов
     *
     * @var string[]
     */
    protected static $arFinalStatuses
        = [
            'reject',
            'return_to_sender',
            'completed',
            'return_doc'
        ];

    /**
     * Статус "Доставлен покупателю"
     *
     * @var string
     */
    protected static $sStatusDelivered = 'completed';

    public static function refreshStatusesAjax()
    {
        self::refreshOrderStates();
        echo 'Y';
    }

    /**
     * For agent
     *
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public static function refreshOrderStates()
    {
        $arOrders = self::getOrdersForCheckStatusesByFilter();

        if (!empty($arOrders)) {
            foreach ($arOrders as $sKey) {
                self::checkStatus($sKey);
            }
        }
    }

    /**
     * @param $arFilter
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected static function getOrdersForCheckStatusesByFilter($arFilter = [])
    {
        $arDefFilter = [
            '=OK' => 'Y',
            '!KEY' => false,
            [
                'LOGIC' => 'OR',
                '!TRACKING_STATUS' => self::getFinalStatuses(),
                '=TRACKING_STATUS' => false
            ]
        ];
        if (!empty($arFilter)) {
            $arFilter = array_merge($arFilter, $arDefFilter);
        }
        else {
            $arFilter = $arDefFilter;
        }

        /** @var \Bitrix\Main\DB\Result $rsOrders */
        $rsOrders = \Ipol\Catapulto\OrdersTable::getList(
            [
                'select' => ['ID', 'BITRIX_ID', 'KEY'],
                'filter' => $arFilter,
                'order'  => ['ID' => 'ASC']
            ]
        );

        $arOrders = [];

        while ($arOrder = $rsOrders->Fetch()) {
            $arOrders[] = $arOrder['KEY'];
        }

        return $arOrders;
    }

    /**
     * @return string[]
     */
    public static function getFinalStatuses()
    {
        return self::$arFinalStatuses;
    }

    /**
     * @param string $sKey
     *
     * @return array|bool
     * @throws \Exception
     */
    public static function checkStatus($sKey)
    {
        $handler = new Status();
        $result  = $handler->checkStatus($sKey);

        if ($result && $result->isSuccess()) {
            return self::settleStatuses($result, $sKey);
        }
        else {
            Logger::toLog(Tools::getMessage('ERR_NO_STATUS_INFO'));
        }

        return false;
    }

    /**
     * @param Catapulto\Entity\ShipmentReadResult $obShipmentReadResult
     * @param                                                     $sKey
     *
     * @return bool
     * @throws \Exception
     */
    public static function settleStatuses(Catapulto\Entity\ShipmentReadResult $obShipmentReadResult, $sKey): bool
    {
        if ($obResponse = $obShipmentReadResult->getResponse()) {

            $arErrors = [];

            $sMainStatus = $obResponse->getMainStatus();
            $sTrackingStatus = $obResponse->getTrackingStatus();
            $savedOrder  = OrdersTable::getByKey($sKey);
            // Check - if track number is changed
            $trackingLink = $obResponse->getTrackingLink() ?? '';
            $userEmail = '';
            $isNotify = false;
            if (!empty($trackingLink)) {
                $currentTrackNumber = '';
                if (!empty($savedOrder)) {
                    $currentTrackNumber = $savedOrder['TRACKING_LINK'];
                    if ($trackingLink != $currentTrackNumber) {
                        $userEmail = Order::getOrderClientMail($savedOrder['BITRIX_ID']);
                        if (!empty($userEmail)) $isNotify = true;
                    }
                }
            }
            if (!(Option::get('client_notify') !== 'Y')) $isNotify = false;

            // Check case: unknown bullshit invade from server instead of orders sent via module
            if (!empty($savedOrder) && $savedOrder['ID']) {
                $updateResult = OrdersTable::update($savedOrder['ID'], [
                    'CATAPULTO_ID'                    => $obResponse->getId(),
                    'NUMBER'                          => $obResponse->getNumber(),
                    'TRACKING_NUMBER'                 => $obResponse->getTrackingNumber() ?? '',
                    'TRACKING_LINK'                   => $trackingLink,
                    'MAIN_STATUS'                     => $sMainStatus,
                    'MAIN_STATUS_DISPLAY'             => $obResponse->getMainStatusDisplay(),
                    'PICKUP_DAY'                      => $obResponse->getPickupDay(),
                    'DELIVERY_DAY'                    => $obResponse->getDeliveryDay(),
                    'PRICE'                           => $obResponse->getPrice(),
                    'WEIGHT'                          => $obResponse->getWeight(),
                    'OPERATOR'                        => $obResponse->getOperator(),
                    'DESCRIPTION'                     => $obResponse->getDescription(),
                    'WITH_INSURANCE'                  => $obResponse->getWithInsurance(),
                    'DOCUMENTS'                       => serialize($obResponse->getDocuments()->getFields()),
                    'LAST_TRACKING_TEXT'              => $obResponse->getLastTrackingText(),
                    'PROBLEM_TEXT'                    => $obResponse->getProblemText(),
                    'RECEIVER_ADDRESS'                => serialize($obResponse->getReceiverAddress()->getFields()),
                    'IS_POD'                          => $obResponse->getIsPod(),
                    'TRACKING_STATUS'                 => $sTrackingStatus,
                    'POD'                             => serialize($obResponse->getPod()),
                    'UPTIME'                          => time()
                ]);

                if (!$updateResult->isSuccess()) {
                    $arErrors[$savedOrder['BITRIX_ID']] = Tools::getMessage('ERR_UNADLEUPDATE') . "\n" . implode("\n", $updateResult->getErrorMessages());
                } else {
                    //actions after success update
                    if ($isNotify) MailSender::notifyTracking($trackingLink, $userEmail);
                }

                // insert track number to order
                if (Options::fetchOption('addTracking') == 'Y' && !empty($obResponse->getTrackingNumber())) {
                    \Ipol\Catapulto\Bitrix\Handler\Order::addTracking($savedOrder['ID'], $obResponse->getTrackingNumber());
                }
            }

            self::checkUpdateOrderStatus($sTrackingStatus, $savedOrder, $arErrors);

            if (!empty($arErrors)) {
                $loggerStr = '';
                foreach ($arErrors as $orderId => $errMess) {
                    $loggerStr .= Tools::getMessage('LBL_ORDER') . $savedOrder[$orderId]['NUMBER'] . ' (' . $orderId . '): ' . $errMess;
                }
                Logger::toLog($loggerStr);
            }
            else {
                return true;
            }

        }
        else {
            Logger::toLog(Tools::getMessage('ERR_NO_STATUS_INFO'));
        }

        return false;
    }

    /**
     * Обновляет статус заказа Битрикс и флаг оплаты, если заказ подходит под параметры
     *
     * @param $sMainStatus
     * @param $savedOrder
     * @param $arErrors
     *
     * @return void
     */
    protected static function checkUpdateOrderStatus($sTrackingStatus, $savedOrder, &$arErrors)
    {
        $options = new Options();

        if (!empty($sTrackingStatus)) {
            // Blocking status
            $blockingStatus = $options::fetchOption('blockingStatus');
            $bNeedUpdate    = true;
            if ($blockingStatus) {
                $bNeedUpdate = \CSaleOrder::GetList(
                    ['ID' => 'ASC'],
                    ['=ID' => $savedOrder['BITRIX_ID'], '=STATUS_ID' => $blockingStatus],
                    [],
                    false,
                    ['ID']
                );
                $bNeedUpdate = ($bNeedUpdate == 0);
            }

            // updating Bitrix status
            $statusBitrix = $options::fetchOption('status_' . $sTrackingStatus);
            if ($bNeedUpdate && $statusBitrix && $sTrackingStatus !== $savedOrder['TRACKING_STATUS']) {
                if (!\CSaleOrder::StatusOrder($savedOrder['BITRIX_ID'], $statusBitrix)) {
                    $errMess = Tools::getMessage('ERR_NOUPDATE') . " " . Tools::getMessage('LBL_bitrixStatus') . ": " . $statusBitrix;
                    if (array_key_exists($savedOrder['BITRIX_ID'], $arErrors)) {
                        $arErrors[$savedOrder['BITRIX_ID']] .= $errMess;
                    }
                    else {
                        $arErrors[$savedOrder['BITRIX_ID']] = $errMess;
                    }
                }
            }

            if (
                $options::fetchOption('markPayed') === 'Y' && $sTrackingStatus === self::$sStatusDelivered && $sTrackingStatus !== $savedOrder['TRACKING_STATUS']
            ) {
                order::markPayed($savedOrder['BITRIX_ID']);
            }
        }
    }

    public static function checkStatusByBitrixIAjax()
    {
        self::checkStatusByBI($_REQUEST['bitrixId']);
        echo 'Y';
    }

    public static function checkStatusByBI($arBxIds)
    {
        $arOrders = self::getOrdersForCheckStatusesByFilter(['=BITRIX_ID' => $arBxIds]);

        if (!empty($arOrders)) {
            foreach ($arOrders as $sKey) {
                self::checkStatus($sKey);
            }
        }
    }

    public static function checkStatusByBDIAjax()
    {
        self::checkStatusByBDI($_REQUEST['ids']);
        echo 'Y';
    }

    public static function checkStatusByBDI($arIds)
    {
        $arOrders = self::getOrdersForCheckStatusesByFilter(['=ID' => $arIds]);

        if (!empty($arOrders)) {
            foreach ($arOrders as $sKey) {
                self::checkStatus($sKey);
            }
        }
    }

    /**
     * @param $sStatus
     *
     * @return bool
     */
    public static function isFinalStatus($sStatus)
    {
        return in_array($sStatus, self::$arFinalStatuses);
    }
}
