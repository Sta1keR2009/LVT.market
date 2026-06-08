<?php
namespace Ipol\Catapulto\Bitrix\Controller;

use \Ipol\Catapulto\OperatorsTable;
use \Ipol\Catapulto\Bitrix\Adapter;

use \Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Result;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;

/**
 * Class SyncOperators
 * @package Ipol\Catapulto\Bitrix\Controller
 */
class SyncOperators extends AbstractController
{
    // Common sync helpers
    use SyncUtilities;

    // \Bitrix\Main\ErrorCollection
    public $errors;

    // \Bitrix\Main\Result
    public $result;

    public function __construct()
    {
        parent::__construct(CATAPULTO_DELIVERY, CATAPULTO_DELIVERY_LBL);
        $this->errors = new \Bitrix\Main\ErrorCollection();
        $this->result = new \Bitrix\Main\Result();
    }

    /**
     * Get operators data from Catapulto API
     *
     * @return \Bitrix\Main\Result
     */
    public function getOperators()
    {
        $result = new Result();

        $this->application->setTimeout(30)->setCache(null);
        $answer = $this->application->companyIcon();

        if ($answer->isSuccess())
        {
            $result->setData([
                'OPERATORS' => $answer->getResponse()->getCompanies(),
            ]);
        }
        else
        {
            if ($this->application->getErrorCollection())
            {
                $this->application->getErrorCollection()->reset();
                while ($error = $this->application->getErrorCollection()->getNext())
                {
                    $result->addError(new Error('Error while requests operators data from API: '.$error->getMessage()));
                }
            }
            else
                $result->addError(new Error('Error while requests operators data from API, but no error messages get from application.'));
        }

        return $result;
    }

    /**
     * Convert operators data from Application to DB format
     *
     * @param \Ipol\Catapulto\Api\Entity\Response\Part\CompanyIcon\Company $operator
     * @return array
     */
    public static function prepareOperatorData($operator)
    {
        $data = [
            "OPERATOR_ID"      => trim($operator->getOperatorId()),
            "OPERATOR_DISPLAY" => trim($operator->getOperatorDisplay()),
            "ICON"             => trim($operator->getIcon()),
            "SMALL_ICON"       => trim($operator->getSmallIcon()),
            "PNG_ICON"         => trim($operator->getPngIcon()),
            "OP_ENABLED"       => 'Y',
        ];

        $data['SYNC_HASH']         = Adapter::makeSyncHash($data);
        $data['SYNC_IS_ACTIVE']    = 'Y';
        $data['SYNC_IS_UPDATABLE'] = 'Y';
        $data['SYNC_LAST_DATE']    = new DateTime();

        return $data;
    }

    /**
     * Refresh operators data in DB. Add or update existing operators.
     *
     * @return \Bitrix\Main\Result
     */
    public function refreshOperators()
    {
        $operatorsResult = $this->getOperators();
        if ($operatorsResult->isSuccess())
        {
            $data = $operatorsResult->getData();

            if ($data['OPERATORS']->getQuantity())
            {
                OperatorsTable::disableAllOperators();

                $operators = [];

                $data['OPERATORS']->reset();
                while ($operator = $data['OPERATORS']->getNext())
                {
                    $preparedOperator = self::prepareOperatorData($operator);
                    if (empty($preparedOperator['OPERATOR_ID']))
                        continue;

                    $operators[$preparedOperator['OPERATOR_ID']] = $preparedOperator;
                }

                // Get existing operators data from DB
                $existedOperatorsDB = OperatorsTable::getList(['select' => ['ID', 'OPERATOR_ID', 'SYNC_IS_UPDATABLE', 'SYNC_HASH'], 'filter' => ['=OPERATOR_ID' => array_keys($operators)]]);
                $existedOperators = [];
                while ($tmp = $existedOperatorsDB->fetch())
                {
                    $existedOperators[$tmp['OPERATOR_ID']] = array('ID' => $tmp['ID'], 'SYNC_IS_UPDATABLE' => $tmp['SYNC_IS_UPDATABLE'], 'SYNC_HASH' => $tmp['SYNC_HASH']);
                }

                /* Log */ $this->toLog(['STATE' => 'Operators loading start', 'DATE' => (new DateTime())->toString()]);

                foreach ($operators as $guid => $operator)
                {
                    // Existing operator
                    if (array_key_exists($guid, $existedOperators))
                    {
                        $operatorPrimaryId = $existedOperators[$guid]['ID'];

                        // Skip operators data refresh if it's marked as not updatable
                        if ($existedOperators[$guid]['SYNC_IS_UPDATABLE'] !== 'Y')
                        {
                            /* Log */ $this->toLog(['STATE' => 'Not updatable operators detected', 'EXISTED' => $existedOperators[$guid], 'PRETENDER' => $operator]);
                            continue;
                        }

                        $toUpdate = ($existedOperators[$guid]['SYNC_HASH'] == $operator['SYNC_HASH']) ? ['SYNC_IS_ACTIVE' => 'Y', 'SYNC_LAST_DATE' => $operator['SYNC_LAST_DATE'], 'OP_ENABLED' => 'Y'] : $operator;
                        $result = OperatorsTable::update($operatorPrimaryId, $toUpdate);
                        $this->collectPossibleErrors($result, 'Operator '.$guid);

                        /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Operator update', 'OPERATOR_ID' => $guid, 'OPERATOR_DATA' => $toUpdate]);
                    }
                    else
                    {
                        // New operator to add
                        $result = OperatorsTable::add($operator);
                        $this->collectPossibleErrors($result, 'Operator '.$operator['OPERATOR_ID']);

                        /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Operator add', 'OPERATOR_ID' => $operator['OPERATOR_ID'], 'OPERATOR_DATA' => $operator]);
                    }
                }

                $this->result->setData(['TOTAL_OPERATORS_FOUND' => count($operators)]);

                /* Log */ $this->toLog(['STATE' => 'Operators loading end', 'DATE' => (new DateTime())->toString()]);
            }
            else
                $this->result->addError(new \Bitrix\Main\Error('No data while getting operators from API'));
        }
        else
            $this->errors->add($operatorsResult->getErrorCollection()->toArray());

        if (!$this->errors->isEmpty())
        {
            foreach ($this->errors as $err)
                $this->result->addError($err);
        }

        return $this->result;
    }

    /**
     * Toggle unused operators inactive
     *
     * @param int $lastDate timestamp
     * @return \Bitrix\Main\Result
     */
    public function toggleInactiveOperators($lastDate)
    {
        $syncLastDate = DateTime::createFromTimestamp($lastDate);

        $operators = [];
        $existedOperatorsDB = OperatorsTable::getList(['select' => ['ID', 'SYNC_IS_UPDATABLE', 'SYNC_LAST_DATE'], 'filter' => ['<SYNC_LAST_DATE' => $syncLastDate]]);
        while ($tmp = $existedOperatorsDB->fetch())
        {
            $operators[$tmp['ID']] = $tmp;
        }

        foreach ($operators as $operatorPrimaryId => $operator)
        {
            if ($operator['SYNC_IS_UPDATABLE'] !== 'Y')
            {
                /* Log */ $this->toLog(['STATE' => 'Not updatable operator detected', 'EXISTED' => $operator]);
                continue;
            }

            $result = OperatorsTable::update($operatorPrimaryId, ['SYNC_IS_ACTIVE' => 'N', 'SYNC_LAST_DATE' => $syncLastDate]);
            $this->collectPossibleErrors($result, 'Operator '.$operatorPrimaryId);

            /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Operator toggle inactive', 'OPERATOR_ID' => $operatorPrimaryId, 'OPERATOR_DATA' => $operator]);
        }

        if (!$this->errors->isEmpty())
        {
            foreach ($this->errors as $err)
                $this->result->addError($err);
        }

        $this->result->setData(['TOTAL_OPERATORS_FOUND' => count($operators)]);

        return $this->result;
    }

    /**
     * Make statistic info about loaded operators
     *
     * @return \Bitrix\Main\Result
     */
    public static function makeStatistic()
    {
        $result = new Result();
        $result->setData(['OPERATORS_LOADED' => OperatorsTable::getDataCount(true)]);

        return $result;
    }
}
