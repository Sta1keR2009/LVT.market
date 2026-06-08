<?php
namespace Ipol\Catapulto\Admin\Grid;

use \Ipol\Catapulto\Admin\Grid\AbstractGrid;

/**
 * Class DatabaseGrid
 * @package Ipol\Catapulto\Admin\Grid
 */
abstract class DatabaseGrid extends AbstractGrid
{
    /**
     * Data fetch mode variants
     */
    const FETCH_AS_OBJECT = 1;
    const FETCH_AS_ARRAY  = 2;

    /**
     * @var string
     */
    protected $fetchMode;

    /**
     * @var array
     */
    protected $select = ['*'];

    /**
     * Return ORM data mapper for data selection
     *
     * @return Bitrix\Main\ORM\Data\DataManager
     */
    abstract public function getDataMapper();

    /**
     * Get data fetch mode
     *
     * @return string
     */
    public function getFetchMode()
    {
        return $this->fetchMode;
    }

    /**
     * Set data fetch mode
     *
     * @param string $fetchMode use DatabaseGrid::FETCH_AS_ARRAY or DatabaseGrid::FETCH_AS_OBJECT
     * @return self
     */
    public function setFetchMode($fetchMode)
    {
        if (!in_array($fetchMode, [self::FETCH_AS_ARRAY, self::FETCH_AS_OBJECT]))
            $fetchMode = self::FETCH_AS_OBJECT;

        $this->fetchMode = $fetchMode;

        return $this;
    }

    /**
     * Get selected columns
     *
     * @return array
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * Set selected columns
     *
     * @param array $select
     * @return self
     */
    public function setSelect(array $select)
    {
        return $this->select = $select;
    }

    /**
     * Get raw data used for creating grid rows
     *
     * @return array
     */
    protected function getRawData()
    {
        $query  = $this->getQuery();
        //\Bitrix\Main\Diag\Debug::WriteToFile($query->getQuery(), 'SQL Query', '__catapulto_GridQuery.log');
        $result = $query->exec();

        if ($this->getFetchMode() === self::FETCH_AS_OBJECT)
        {
            $ret = [];
            while($item = $result->fetchObject())
            {
                $ret[] = $item;
            }
        }
        else
        {
            $ret = $result->fetchAll();
        }

        $pagination = $this->getPagination();
        $pagination->setRecordCount($result->getCount());

        return $ret;
    }

    /**
     * Query constructor
     *
     * @return Bitrix\Main\ORM\Query\Query
     */
    protected function getQuery()
    {
        $dataMapper = $this->getDataMapper();

        $query = $dataMapper::query()->setSelect($this->getSelect())->setFilter($this->getFilterValues())->setOrder($this->getSorting())->countTotal(true);
        $pagination = $this->getPagination();

        if (!$pagination->allRecordsShown())
        {
            $query->setLimit($pagination->getLimit());
            $query->setOffset($pagination->getOffset());
        }

        return $query;
    }
}