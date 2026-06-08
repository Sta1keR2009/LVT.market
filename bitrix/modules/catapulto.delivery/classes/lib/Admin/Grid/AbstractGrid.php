<?php
namespace Ipol\Catapulto\Admin\Grid;

use \Ipol\Catapulto\Bitrix\Tools;

use \Bitrix\Main\Grid;
use \Bitrix\Main\UI\PageNavigation;

/**
 * Class AbstractGrid
 * @package Ipol\Catapulto\Admin\Grid
 */
abstract class AbstractGrid
{
    /**
     * @var array
     */
    protected $defaultButtons = [];

    /**
     * @var array
     */
    protected $buttons;

    /**
     * @var array
     */
    protected $defaultColumns = [];

    /**
     * @var array;
     */
    protected $columns;

    /**
     * @var array
     */
    protected $filterColumns;

    /**
     * @var array
     */
    protected $defaultFilterValues = [];

    /**
     * @var array
     */
    protected $filterValues;

    /**
     * @var array
     */
    protected $defaultSorting = [];

    /**
     * @var PageNavigation
     */
    protected $pagination;

    /**
     * @var integer
     */
    protected $defaultPageSize = 20;

    /**
     * @var boolean
     */
    protected $allowAllRecords = false;

    /**
     * Get grid ID
     *
     * @see component bitrix:main.ui.grid
     * @see component bitrix:main.ui.filter
     * @return string
     */
    public function getId()
    {
        return 'tbl_'. md5(static::class);
    }

    /**
     * Get filter ID
     *
     * @see component bitrix:main.ui.filter
     * @return string
     */
    public function getFilterId()
    {
        return $this->getId().'_filter';
    }

    /**
     * Get default buttons list
     *
     * @see component bitrix:ui.button.panel
     * @return array
     */
    public function getDefaultButtons()
    {
        return $this->defaultButtons;
    }

    /**
     * Get buttons list
     *
     * @see component bitrix:ui.button.panel
     * @return array
     */
    public function getButtons()
    {
        if (is_null($this->buttons))
        {
            $this->buttons = [];

            foreach ($this->getDefaultButtons() as $index => $button)
            {
                $this->buttons[$index] = array_merge($button, ['CAPTION' => Tools::getMessage($button['CAPTION'])]);
            }
        }

        return $this->buttons;
    }

    /**
     * Set buttons list
     *
     * @param array $buttons
     * @return self
     */
    public function setButtons($buttons)
    {
        $this->buttons = $buttons;
        return $this;
    }

    /**
     * Add button
     *
     * @return self
     */
    public function addButton(array $button)
    {
        $this->getButtons();

        $this->buttons[] = $button;

        return $this;
    }

    /**
     * Get grid action panel controls
     *
     * @see component bitrix:main.ui.grid
     * @return array
     */
    public function getControls()
    {
        return [];
    }

    /**
     * Get default grid columns
     *
     * @return array
     */
    public function getDefaultColumns()
    {
        return $this->defaultColumns;
    }

    /**
     * Get grid columns
     *
     * @return array
     */
    public function getColumns()
    {
        if (is_null($this->columns))
        {
            foreach ($this->getDefaultColumns() as $index => $column)
            {
                $this->columns[$index] = array_merge($column, ['name' => Tools::getMessage($column['name'])]);
            }
        }

        return $this->columns;
    }

    /**
     * Get filter columns
     *
     * @return array
     */
    public function getFilterColumns()
    {
        if (is_null($this->filterColumns))
        {
            $this->filterColumns = [];

            $items = $this->getColumns();

            foreach ($items as $item)
            {
                if (!isset($item['filterable']) || $item['filterable'] === false)
                {
                    continue;
                }

                $this->filterColumns[] = array_merge(
                    $item,
                    is_array($item['editable'])   ? $item['editable']   : [],
                    is_array($item['filterable']) ? $item['filterable'] : []
                );
            }
        }

        return $this->filterColumns;
    }

    /**
     * Get default filter values
     *
     * @return array
     */
    public function getDefaultFilterValues()
    {
        return $this->defaultFilterValues;
    }

    /**
     * Set default filter values
     *
     * @param array $filterValues
     * @return self
     */
    public function setDefaultFilterValues(array $filterValues = [])
    {
        $this->defaultFilterValues = $filterValues;

        return $this;
    }

    /**
     * Get and prepare filter values from grid filter
     *
     * @return array
     */
    public function getFilterValues()
    {
        if (is_null($this->filterValues))
        {
            $this->filterValues = [];

            $filter         = $this->getFilterColumns();
            $option         = new \Bitrix\Main\UI\Filter\Options($this->getFilterId());
            $data           = $option->getFilter([]);
            $quickSearchKey = "";

            foreach ($filter as $column)
            {
                $format = false;

                if (isset($column["quickSearch"]))
                    $quickSearchKey = $column["quickSearch"].$column["id"];

                /*if ($column['type'] == 'date')
                {
                    $format = 'YYYY-MM-DD';
                }
                elseif ($column['type'] == 'datetime')
                {
                    $format = 'YYYY-MM-DD HH:MI:SS';

                    if ($column['id'] == 'DATE_CREATE')
                    {
                        $format = 'DD.MM.YYYY HH:MI:SS';
                    }
                }
                */

                $columnName = substr($column['id'], 0, '9') == 'PROPERTY_' ? preg_replace('{(_VALUE|_ENUM_ID)$}', '', $column['id']) : $column['id'];
                $columnName = (in_array($column['filterable'], ['%', '?'], true)) ? $column['filterable'].$columnName : $columnName;

                if (isset($data[$column['id'] .'_from']))
                {
                    $this->filterValues['>='. $columnName] = $format ? ConvertDateTime($data[$column['id'] .'_from'], $format) : $data[$column['id'] .'_from'];

                    /*if ($column['type'] == 'date')
                        $this->filterValues['>='. $columnName] .= ' 00:00:00';*/
                }
                if (isset($data[$column['id'] .'_to']))
                {
                    $this->filterValues['<='. $columnName] = $format ? ConvertDateTime($data[$column['id'] .'_to'], $format) : $data[$column['id'] .'_to'];

                    /*if ($column['type'] == 'date')
                        $this->filterValues['<='. $columnName] .= ' 23:59:59';*/
                }
                if (isset($data[$column['id']]))
                {
                    $this->filterValues[$columnName] = $format ? ConvertDateTime($data[$column['id']], $format) : $data[$column['id']];
                }
            }

            if (isset($data['FIND']) && trim($data['FIND']) && $quickSearchKey)
                $this->filterValues[$quickSearchKey] = $data['FIND'];

            $this->filterValues = array_merge($this->filterValues, $this->getDefaultFilterValues());
        }

        return $this->filterValues;
    }

    /**
     * Get default sorting
     *
     * @return array
     */
    public function getDefaultSorting()
    {
        return $this->defaultSorting;
    }

    /**
     * Get sorting
     *
     * @return array
     */
    public function getSorting()
    {
        $options = new Grid\Options($this->getId());

        $data = $options->GetSorting([
            'sort' => $this->getDefaultSorting(),
            'vars' => ['by' => 'by', 'order' => 'order'],
        ]);

        return $data['sort'];
    }

    /**
     * Get pagination manager
     *
     * @see component bitrix:main.ui.grid
     * @return Bitrix\Main\UI\PageNavigation
     */
    public function getPagination()
    {
        if (is_null($this->pagination))
        {
            $options = new Grid\Options($this->getId());
            $params  = $options->GetNavParams(['nPageSize' => $this->defaultPageSize]);

            $this->pagination = new PageNavigation($this->getId());
            $this->pagination->allowAllRecords($this->allowAllRecords)->setPageSize($params['nPageSize'])->initFromUri();
        }

        return $this->pagination;
    }

    /**
     * Get formatted rows for grid
     *
     * @see component bitrix:main.ui.grid
     * @return array
     */
    public function getRows()
    {
        $ret   = [];
        $items = $this->getRawData();

        foreach ($items as $key => $item) {
            if (!empty($item['TRACKING_LINK'])) $item['TRACKING_LINK'] = '<a target="_blank" href="' . $item['TRACKING_LINK'] . '">' . $item['TRACKING_LINK'] . '<a/>';
            $ret[$key] = $this->getRow($item);
        }

        return $ret;
    }

    /**
     * Get single data item in grid row format
     *
     * @param array $item
     * @return array
     */
    protected function getRow($item)
    {
        return [
            'data'            => $item,
            'columns'         => [],
            'actions'         => $this->getRowActions($item),
            'editable'        => true,
            'editableColumns' => [],
            'attrs'           => [],
            'columnClasses'   => [],
            'custom'          => [],
        ];
    }

    /**
     * Get default row actions available for single row
     *
     * @see getControls method for action panel actions
     * @see component bitrix:main.ui.grid
     * @return array
     */
    protected function getDefaultRowActions()
    {
        return $this->defaultRowActions;
    }

    /**
     * Get row actions available for single row
     *
     * @param array $item
     * @return array
     */
    protected function getRowActions($item)
    {
        $ret = [];

        $actions = $this->getDefaultRowActions();

        foreach ($actions as $index => $action) {
            $ret[$index] = array_merge($action, [
                'TEXT'    => Tools::getMessage($action['TEXT']),
                'LINK'    => str_replace('#ID#', $item['ID'], $action['LINK']),
                'ONCLICK' => str_replace('#ID#', $item['ID'], $action['ONCLICK']),
            ]);
        }

        return $ret;
    }

    /**
     * Get raw data used for creating grid rows
     *
     * @return array
     */
    abstract protected function getRawData();
}