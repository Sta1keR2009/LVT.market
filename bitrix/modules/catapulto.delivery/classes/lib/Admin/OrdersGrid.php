<?php
namespace Ipol\Catapulto\Admin;

use \Bitrix\Main\Type\DateTime;

use \Ipol\Catapulto\Bitrix\Adapter;
use \Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\OperatorsTable;
use \Ipol\Catapulto\OrdersTable;
use \Ipol\Catapulto\Admin\Grid\DatabaseGrid;

/**
 * Class OrdersGrid
 * @package Ipol\Catapulto\Admin
 */
class OrdersGrid extends DatabaseGrid
{
    /**
     * @var string
     */
    protected $fetchMode = self::FETCH_AS_ARRAY;

    /**
     * @var array
     */
    protected $defaultSorting = ['ID' => 'DESC'];

    /**
     * @var array
     */
    protected $defaultButtons = [
        [
            'CAPTION' => 'TABLE_ORDERS_BTN_GET_STATUSES',
            'TYPE'    => 'button',
            'ONCLICK' => CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.suncStatuses(this)',
        ],
    ];

    /**
     * @var array
     */
    protected $defaultColumns = [
        [
            'id'          => 'ID',
            'name'        => 'TABLE_ORDERS_ID',
            'sort'        => 'ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
            'type'        => 'number',
        ],
        [
            'id'          => 'BITRIX_ID',
            'name'        => 'TABLE_ORDERS_BITRIX_ID',
            'sort'        => 'BITRIX_ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'NUMBER',
            'name'        => 'TABLE_ORDERS_NUMBER',
            'sort'        => 'NUMBER',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'TRACKING_NUMBER',
            'name'        => 'TABLE_ORDERS_TRACKING_NUMBER',
            'sort'        => 'TRACKING_NUMBER',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'TRACKING_LINK',
            'name'        => 'TABLE_ORDERS_TRACKING_LINK',
            'sort'        => 'TRACKING_LINK',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'OPERATOR',
            'name'        => 'TABLE_ORDERS_OPERATOR',
            'sort'        => 'OPERATOR',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'MAIN_STATUS_DISPLAY',
            'name'        => 'TABLE_ORDERS_MAIN_STATUS_DISPLAY',
            'sort'        => 'MAIN_STATUS_DISPLAY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'PICKUP_DAY',
            'name'        => 'TABLE_ORDERS_PICKUP_DAY',
            'sort'        => 'PICKUP_DAY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'DELIVERY_DAY',
            'name'        => 'TABLE_ORDERS_DELIVERY_DAY',
            'sort'        => 'DELIVERY_DAY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'PRICE',
            'name'        => 'TABLE_ORDERS_PRICE',
            'sort'        => 'PRICE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'WEIGHT',
            'name'        => 'TABLE_ORDERS_WEIGHT',
            'sort'        => 'WEIGHT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'SENDER_COMPANY',
            'name'        => 'TABLE_ORDERS_SENDER_COMPANY',
            'sort'        => 'SENDER_COMPANY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'RECEIVER_LOCALITY',
            'name'        => 'TABLE_ORDERS_RECEIVER_LOCALITY',
            'sort'        => 'RECEIVER_LOCALITY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'RECEIVER_NAME',
            'name'        => 'TABLE_ORDERS_RECEIVER_NAME',
            'sort'        => 'RECEIVER_NAME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'RECEIVER_COMPANY',
            'name'        => 'TABLE_ORDERS_RECEIVER_COMPANY',
            'sort'        => 'RECEIVER_COMPANY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'RECEIVER_PHONE',
            'name'        => 'TABLE_ORDERS_RECEIVER_PHONE',
            'sort'        => 'RECEIVER_PHONE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'RECEIVER_ADDRESS',
            'name'        => 'TABLE_ORDERS_RECEIVER_ADDRESS',
            'sort'        => 'RECEIVER_ADDRESS',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'DESCRIPTION',
            'name'        => 'TABLE_ORDERS_DESCRIPTION',
            'sort'        => 'DESCRIPTION',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'WITH_INSURANCE',
            'name'        => 'TABLE_ORDERS_WITH_INSURANCE',
            'sort'        => 'WITH_INSURANCE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'IS_POD',
            'name'        => 'TABLE_ORDERS_IS_POD',
            'sort'        => 'IS_POD',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'POD',
            'name'        => 'TABLE_ORDERS_POD',
            'sort'        => 'POD',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'PROBLEM_TEXT',
            'name'        => 'TABLE_ORDERS_PROBLEM_TEXT',
            'sort'        => 'PROBLEM_TEXT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'OK',
            'name'        => 'TABLE_ORDERS_OK',
            'sort'        => 'OK',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'UPTIME',
            'name'        => 'TABLE_ORDERS_UPTIME',
            'sort'        => 'UPTIME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
    ];

    /**
     * @var array
     */
    protected $defaultRowActions = [
        // Acceptable system icon classes are in \bitrix\js\main\popup\dist\main.popup.bundle.css
        // menu-popup-item-copy for documents

        'VIEW_BITRIX_ORDER' => [
            'ICONCLASS' => 'menu-popup-item-delegate',
            'TEXT'      => 'TABLE_ORDERS_ROW_VIEW_BITRIX_ORDER',
            'ONCLICK'   => 'document.location.href="sale_order_view.php?ID=#BITRIX_ID#"',
        ],
        'GET_ORDER_STATUS' => [
            'ICONCLASS' => 'menu-popup-item-view',
            'TEXT'      => 'TABLE_ORDERS_ROW_GET_ORDER_STATUS',
            'ONCLICK'   => CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.suncOrderStatus("#BITRIX_ID#")',
        ],
        'GET_ORDER_INVOICE' => [
            'ICONCLASS' => 'menu-popup-item-copy',
            'TEXT'      => 'TABLE_ORDERS_ROW_PRINT_INVOICE',
            'ONCLICK'   => CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.print("#BITRIX_ID#")',
        ],
        'GET_ORDER_STICKER' => [
            'ICONCLASS' => 'menu-popup-item-copy',
            'TEXT'      => 'TABLE_ORDERS_ROW_PRINT_STICKER',
            'ONCLICK'   => CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.printStickers("#BITRIX_ID#")',
        ],
        'GET_ORDER_POA' => [
            'ICONCLASS' => 'menu-popup-item-copy',
            'TEXT'      => 'TABLE_ORDER_PRINT_POA',
            'ONCLICK'   => CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.printOrderPOA("#BITRIX_ID#")',
        ],
        'GET_ORDER_DOCS' => [
            'ICONCLASS' => 'menu-popup-item-copy',
            'TEXT'      => 'TABLE_ORDERS_ROW_PRINT_DOCS',
            'ONCLICK'   => CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.printOrderDocs("#BITRIX_ID#")',
        ],
        [
            'delimiter'=>true,
        ],
        'CANCEL_ORDER'=>[
            'ICONCLASS' => 'menu-popup-item-delete',
            'TEXT'      => 'TABLE_ORDERS_ROW_CANCELORDER',
            'ONCLICK'   => CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.cancelOrder("#ID#")',
        ],
        /*,
        'DELETE_ORDER' => [
            'ICONCLASS' => 'menu-popup-item-delete',
            'TEXT'      => 'TABLE_ORDERS_ROW_DELETE_ORDER',
            'ONCLICK'   => 'alert("DELETE_ORDER action on CATAPULTO_id #CATAPULTO_ID#")',
        ],*/
    ];

    /**
     * Get grid action panel controls
     *
     * @see component bitrix:main.ui.grid
     * @return array
     */
    public function getControls()
    {
        return [
            [
                'ID'   => 'create-registry',
                'TYPE' => 'BUTTON',
                'NAME' => 'create-registry',
                'TEXT' => Tools::getMessage('TABLE_ORDER_SUNC'),
                'ONCHANGE' => [
                    [
                        'ACTION' => 'CALLBACK',
                        'DATA'   => [
                            [
                                'JS' => '
                                    var grid = BX.Main.gridManager.getInstanceById("'.$this->getId().'");
                                    var ids  = grid.getRows().getSelectedIds();
                                    
                                    if (ids.length > 0){
                                        '.CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.suncSelectedStatuses(ids);
                                    }
                                ',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'ID'   => 'create-registry',
                'TYPE' => 'BUTTON',
                'NAME' => 'create-registry',
                'TEXT' => Tools::getMessage('TABLE_ORDER_PRINT'),
                'ONCHANGE' => [
                    [
                        'ACTION' => 'CALLBACK',
                        'DATA'   => [
                            [
                                'JS' => '
                                    var grid = BX.Main.gridManager.getInstanceById("'.$this->getId().'");
                                    var ids  = grid.getRows().getSelectedIds();
                                    
                                    if (ids.length > 0){
                                        '.CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.prints(ids);
                                    }
                                ',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'ID'   => 'create-registry',
                'TYPE' => 'BUTTON',
                'NAME' => 'create-registry',
                'TEXT' => Tools::getMessage('TABLE_ORDER_STICKERS'),
                'ONCHANGE' => [
                    [
                        'ACTION' => 'CALLBACK',
                        'DATA'   => [
                            [
                                'JS' => '
                                    var grid = BX.Main.gridManager.getInstanceById("'.$this->getId().'");
                                    var ids  = grid.getRows().getSelectedIds();
                                    
                                    if (ids.length > 0){
                                        '.CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.printOrdersStickers(ids);
                                    }
                                ',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'ID'   => 'create-registry',
                'TYPE' => 'BUTTON',
                'NAME' => 'create-registry',
                'TEXT' => Tools::getMessage('TABLE_ORDER_PRINT_POA'),
                'ONCHANGE' => [
                    [
                        'ACTION' => 'CALLBACK',
                        'DATA'   => [
                            [
                                'JS' => '
                                    var grid = BX.Main.gridManager.getInstanceById("'.$this->getId().'");
                                    var ids  = grid.getRows().getSelectedIds();
                                    
                                    if (ids.length > 0){
                                        '.CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.printOrderPOAAll(ids);
                                    }
                                ',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'ID'   => 'create-registry',
                'TYPE' => 'BUTTON',
                'NAME' => 'create-registry',
                'TEXT' => Tools::getMessage('TABLE_ORDER_ACTS'),
                'ONCHANGE' => [
                    [
                        'ACTION' => 'CALLBACK',
                        'DATA'   => [
                            [
                                'JS' => '
                                    var grid = BX.Main.gridManager.getInstanceById("'.$this->getId().'");
                                    var ids  = grid.getRows().getSelectedIds();
                                    
                                    if (ids.length > 0){
                                        '.CATAPULTO_DELIVERY_LBL.'controller.getPage("main").actions.printOrdersDocs(ids);
                                    }
                                ',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'ID' => 'ctplt_cancel_orders',
                'TYPE' => 'BUTTON',
                'NAME' => 'ctplt_cancel_orders',
                'TEXT' => Tools::getMessage('TABLE_ORDERS_ROW_CANCELORDER'),
                'ONCHANGE' => [
                    [
                        'ACTION' => 'CALLBACK',
                        'DATA' => [
                            [
                                'JS' => '
                                    const ids = BX.Main.gridManager.getInstanceById("' . $this->getId() . '").getRows().getSelectedIds(),
                                    t = document.getElementById("ctplt_cancel_orders");                      
                                    if (!t.classList.contains("cata_btn_disabled") && (ids.length > 0)) {
                                        if (confirm("' . Tools::getMessage('ADMIN_CANCELORDER_CONFIRM') . '")) {
                                            ' . CATAPULTO_DELIVERY_LBL . 'controller.getPage("main").actions.cancelOrders(ids);
                                        }
                                    }
                                ',
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Return ORM data mapper for data selection
     *
     * @return Bitrix\Main\ORM\Data\DataManager
     */
    public function getDataMapper()
    {
        return OrdersTable::class;
    }

    /**
     * Get single data item in grid row format
     *
     * @param array $item
     * @return array
     */
    protected function getRow($item)
    {
        $ret = parent::getRow($item);

        // Add some human-readable texts instead of specific identifiers there
        // $ret['data'][__COLUMN_NAME__] = ... ;

        if (!empty($ret['data']['UPTIME']))
            $ret['data']['UPTIME'] = DateTime::createFromTimestamp($ret['data']['UPTIME'])->format("H:i:s d.m.Y");

        // convert sender address
        if (!empty($ret['data']['SENDER_ADDRESS'])) {
            $address = unserialize($ret['data']['SENDER_ADDRESS']);
            $ret['data']['SENDER_ADDRESS'] = Adapter::getAddressString($address);
        }

        // convert receiver address
        if (!empty($ret['data']['RECEIVER_ADDRESS'])) {
            $address = unserialize($ret['data']['RECEIVER_ADDRESS']);
            $ret['data']['RECEIVER_ADDRESS'] = Adapter::getAddressString($address);
        }

        // convert boolean values
        array_walk($ret['data'],'Ipol\Catapulto\Bitrix\Adapter::convertBooleanValues');

        // insert operator logo and name
        if (!empty($ret['data']['OPERATOR'])) {
            $ret['data']['OPERATOR'] = Adapter::getOperatorForGrid($ret['data']['OPERATOR']);
        }

        // Rows coloring by current order status
        // Beware:
        // - undocumented param 'attrs' used, version compatibility unknown
        // - drop .main-grid-cell background color required, check catapulto_delivery_orders.php
        $statusToColor = array(
            'OK'          => '#E2FCE2',
            'REGISTRED'   => '#E2FCE2',
            'SENDED'      => '#E2FCE2',
            'DATEWAITS'   => '#E2FCE2',

            'REFUSE'     => '#FFEDED',
            'REJECTED'   => '#FFEDED',
            'ANNULED'    => '#CACACA',

            'SENDEDTOCITY'    => '#FCFCBF',
            'ARRIVEDTOCITY'   => '#FCFCBF',

            'PARTRETURN'    => '#FCFCBF',
            'RETURNPRECEED' => '#FCFCBF',

            'READYFORGIVE'  => '#D9FFCE',
            'COURIER'       => '#D9FFCE',

            'GIVEN'        => '#ABFFAB',
            'GIVENPART'    => '#ABFFAB',
            'RETURNDONE'   => '#ABFFAB',

        );
//        $color = array_key_exists($ret['data']['STATUS'], $statusToColor) ? $statusToColor[$ret['data']['STATUS']] : '#fff';
//        $ret['attrs'] = ['style' => "background: {$color};"];

        $ret['attrs'] = ['cancelable' =>  ($ret['data']['MAIN_STATUS'] == 'in_proccess') ? 1 : 0 ];

        return $ret;
    }

    /**
     * Get row actions available for single row
     *
     * @param array $item
     * @return array
     */
    protected function getRowActions($item)
    {
        $ret = parent::getRowActions($item);
        foreach ($ret as $index => $action)
        {
            $ret[$index]['LINK']    = str_replace(['#BITRIX_ID#', '#CATAPULTO_ID#'], [$item['BITRIX_ID'], $item['CATAPULTO_ID']], $action['LINK']);
            $ret[$index]['ONCLICK'] = str_replace(['#BITRIX_ID#', '#CATAPULTO_ID#'], [$item['BITRIX_ID'], $item['CATAPULTO_ID']], $action['ONCLICK']);
        }

        return array_values($ret);
    }
}
