<?php

namespace Ipol\Catapulto\Bitrix\Handler;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Error;
use \Bitrix\Sale\Result;
use \Bitrix\Sale\Delivery\Tracking\StatusResult;
use \Bitrix\Sale\Delivery\Tracking\Statuses;

use \Ipol\Catapulto\StatusHandler;
use \Ipol\Catapulto\OrdersTable;
use \Ipol\Catapulto\Bitrix\Tools;
use \Ipol\Catapulto\Bitrix\Controller\Status;
use \Ipol\Catapulto\Bitrix\Entity\Options;

Loc::loadMessages(__FILE__);

/**
 * Class Tracking
 *
 * @package namespace Ipol\Catapulto\Bitrix\Handler
 */
class Tracking extends \Bitrix\Sale\Delivery\Tracking\Base
{
    /**
     * @return string
     */
    public function getClassTitle()
    {
        return Tools::getMessage('TRACKING_TITLE');
    }
    
    /**
     * @return string
     */
    public function getClassDescription()
    {
        return Tools::getMessage('TRACKING_DESCRIPTION');
    }
    
    /**
     * Get Bitrix Tracking statuses by tracking numbers array
     *
     * @param array $trackingNumbers
     *
     * @return \Bitrix\Sale\Delivery\Tracking\StatusResult[]
     */
    public function getStatuses(array $trackingNumbers)
    {
        $data = [];
        
        foreach ($trackingNumbers as $number) {
            $data[$number] = $this->getStatus($number);
        }
        
        return $data;
    }
    
    /**
     * Get Bitrix Tracking status by tracking number
     *
     * @param $trackingNumber
     *
     * @return \Bitrix\Sale\Delivery\Tracking\StatusResult
     */
    public function getStatus($trackingNumber)
    {
        $statusResult = new StatusResult();
        
        $trackingNumber = trim($trackingNumber);
        /*if (preg_match('/^[A-Z]{2}?\d{9}?[A-Z]{2}$/i', $trackingNumber) !== 1)
            $statusResult->addError(new Error(Tools::getMessage('TRACKING_ERROR_WRONG_FORMAT')));*/
        
        if (!\Ipol\Catapulto\AuthHandler::isAuthorized()) {
            $statusResult->addError(new Error(Tools::getMessage('TRACKING_ERROR_NO_AUTH')));
        }
        
        $orders = OrdersTable::getList(
            [
                'select' => ['ID', 'BITRIX_ID', 'TRACKING_NUMBER', 'KEY'],
                'filter' => ['=TRACKING_NUMBER' => $trackingNumber],
                'order'  => ['ID' => 'ASC'],
            ]
        )->fetchAll();
        
        $primaryId = 0;
        $sKey      = '';
        
        if (count($orders) > 1) {
            // What if some goat edited orders table manually and TRACKING_NUMBER not unique?
            $statusResult->addError(new Error(Tools::getMessage('TRACKING_ERROR_DUPLICATE_ORDERS') . $trackingNumber));
        }
        elseif (empty($orders)) {
            $statusResult->addError(new Error(Tools::getMessage('TRACKING_ERROR_NO_ORDERS') . $trackingNumber));
        }
        else {
            $primaryId = $orders[0]['ID'];
            $sKey      = $orders[0]['KEY'];
            if (empty($sKey)) {
                $statusResult->addError(new Error(Tools::getMessage('TRACKING_ERROR_NO_KEY') . $trackingNumber));
            }
        }
        
        if ($statusResult->isSuccess()) {
            $options = new Options();
            if ($options->fetchUseTrackingStatuses() == 'Y') {
                $handler = new Status();
                $answer  = $handler->checkStatus($sKey);
                
                if ($answer && $answer->isSuccess()) {
                    StatusHandler::settleStatuses($answer, $sKey);
                    
                    // Get last inner module status from local table
                    $order = OrdersTable::getByOrderId($primaryId, ['MAIN_STATUS']);
                    
                    $statusResult->trackingNumber      = $trackingNumber;
                    $statusResult->status              = self::getMappedStatus($order['MAIN_STATUS']);
                    $link                              = $this->getTrackingUrl($trackingNumber);
                    $statusResult->description         = Tools::getMessage('TRACKING_STATUS_DESCR') . '<a href="' . $link . '">' . $link . '</a>';
                    $statusResult->lastChangeTimestamp = $orders[0]['UPTIME'] ? : time();
                }
                else {
                    $statusResult->addError(new Error(Tools::getMessage('TRACKING_ERROR_REQUEST_FAIL') . $trackingNumber));
                }
            }
            else {
                // Always return status NO_INFORMATION
                $statusResult->trackingNumber = $trackingNumber;
                $statusResult->status         = Statuses::NO_INFORMATION;
                
                $link                      = $this->getTrackingUrl($trackingNumber);
                $statusResult->description = Tools::getMessage('TRACKING_STATUS_DESCR') . '<a href="' . $link . '">' . $link . '</a>';
                
                $statusResult->lastChangeTimestamp = time();
            }
        }
        
        return $statusResult;
    }
    
    /**
     * Map corresponded Bitrix Tracking status with module inner status
     *
     * @param string $moduleStatus
     *
     * @return string
     */
    protected static function getMappedStatus($moduleStatus)
    {
        switch ($moduleStatus) {
            case 'created':
                $bitrixStatus = Statuses::NO_INFORMATION;
                break;
            case 'courier_take':
                $bitrixStatus = Statuses::WAITING_SHIPMENT;
                break;
            case 'on_road':
            case 'are_cleared':
            case 'forwarding':
                $bitrixStatus = Statuses::ON_THE_WAY;
                break;
            case 'delivery':
                $bitrixStatus = Statuses::ARRIVED;
                break;
            case 'completed':
                $bitrixStatus = Statuses::HANDED;
                break;
            case 'reject':
            case 'return_to_sender':
                $bitrixStatus = Statuses::RETURNED;
                break;
            case 'delivery_problem':
                $bitrixStatus = Statuses::PROBLEM;
                break;
            default:
                $bitrixStatus = Statuses::UNKNOWN;
                break;
        }
        
        return $bitrixStatus;
    }
    
    /**
     *
     * @param string $trackingNumber
     *
     * @return string Url were we can see tracking information
     */
    public function getTrackingUrl($trackingNumber = '')
    {
        static $orders = [];
        
        $key = $trackingNumber;
        if (empty($orders[$key])) {
            $orders[$key] = OrdersTable::getList(
                [
                    'select' => ['ID', 'BITRIX_ID', 'TRACKING_NUMBER', 'KEY'],
                    'filter' => ['=TRACKING_NUMBER' => $trackingNumber],
                    'order'  => ['ID' => 'ASC'],
                ]
            )->fetchAll();
        }
        
        return $orders[$key][0]['KEY'] ? 'https://catapulto.ru/track/' . $orders[$key][0]['KEY'] . '/' : '';
    }
    
    /**
     * Returns params structure
     *
     * @return array
     */
    public function getParamsStructure()
    {
        return [];
    }
}