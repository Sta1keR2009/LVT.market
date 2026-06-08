<?php
namespace Ipol\Catapulto\Bitrix\Controller;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;

/**
 * Trait SyncUtilities - common helpers for sync routines
 * @package Ipol\Catapulto\Bitrix\Controller
 */
trait SyncUtilities
{
    /**
     * Collect possible errors from DB operation result
     *
     * @param \Bitrix\Main\ORM\Data\Result $result
     * @param string $prefix text with additional error info
     */
    public function collectPossibleErrors($result, $prefix)
    {
        if ($result instanceof \Bitrix\Main\ORM\Data\Result) {
            if (!$result->isSuccess())
                $this->errors->add(array(new Error($prefix . ': ' . implode(', ', $result->getErrorMessages()))));
        } else {
            $this->errors->add(array(new Error('Result must be an instance of \Bitrix\Main\ORM\Data\Result')));
        }
    }

    /**
     * Log refresh operations
     *
     * @param \Bitrix\Main\ORM\Data\Result $result
     * @param bool $logSuccessToo
     * @param array $data additional info to log
     */
    public function logRefreshResult($result, $logSuccessToo = false, $data = [])
    {
        if ($result instanceof \Bitrix\Main\ORM\Data\Result)
        {
            if ($result->isSuccess())
            {
                if ($logSuccessToo)
                    $this->toLog(array_merge($data, ['IS_SUCCESS' => 'Y']));
            }
            else
            {
                $this->toLog(array_merge($data, ['ERRORS' => $result->getErrorMessages()]));
            }
        }
    }

    /**
     * Temporary logger wrapper
     *
     * @param array $data what to log
     */
    public function toLog($data)
    {
        /* TODO: Revive sync logger
         *
        if ($this->getLogger())
            $this->getLogger()->toLog(print_r($data, true), "", ltrim(__CLASS__, __NAMESPACE__), array('APPEND' => true));
        */

        // \Bitrix\Main\Diag\Debug::WriteToFile($data, ltrim(__CLASS__, __NAMESPACE__), '__catapulto_Sync.log');
    }
}