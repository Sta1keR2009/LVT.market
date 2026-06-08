<?php
use \Ipol\Catapulto\Admin\OrdersGrid;
use \Ipol\Catapulto\Bitrix\Tools;

use \Bitrix\Main\Localization\Loc;

define("ADMIN_MODULE_NAME", "catapulto.delivery");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");
global $APPLICATION, $USER;

Loc::loadMessages(__FILE__);

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

//if ($GLOBALS['APPLICATION']->GetGroupRight(CATAPULTO_DELIVERY) > 'D')

$APPLICATION->SetTitle(Tools::getMessage('ADMIN_ORDERS_TITLE'));
$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');

if (!CheckVersion(SM_VERSION, '17.0.0'))
{
    $gridVersionLock = new CAdminMessage([
        'MESSAGE' => GetMessage("ADMIN_GRID_MIN_VERSION"),
        'TYPE' => 'ERROR',
        'DETAILS' => GetMessage("ADMIN_GRID_MIN_VERSION_TEXT"),
        'HTML' => true
    ]);
    echo $gridVersionLock->Show();
}
else
{
    // Orders interface buttons, filter and grid
    $OrdersGrid = new OrdersGrid();

    $buttons = $OrdersGrid->getButtons();
    if (!empty($buttons))
    {
        $APPLICATION->IncludeComponent('bitrix:ui.button.panel', '.default', [
            'ALIGN'   => 'left',
            'BUTTONS' => $buttons,
        ]);
    }

    $columns = $OrdersGrid->getFilterColumns();
    if (!empty($columns))
    {
        $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '.default', [
            'GRID_ID'             => $OrdersGrid->getId(),
            'FILTER_ID'           => $OrdersGrid->getFilterId(),
            'FILTER'              => $columns,
            'ENABLE_LIVE_SEARCH'  => false,
            'ENABLE_LABEL'        => true,
            'DISABLE_SEARCH'      => false, // Quick search in FIND field
            // Undocumented ?
            'VALUE_REQUIRED_MODE' => false,
            'VALUE_REQUIRED'      => false,
        ]);
    }

    $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '.default', [
        'GRID_ID'                   => $OrdersGrid->getId(),
        'COLUMNS'                   => $OrdersGrid->getColumns(),
        'ROWS'                      => $OrdersGrid->getRows(),
        'NAV_OBJECT'                => $OrdersGrid->getPagination(),
        'AJAX_ID'                   => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'AJAX_MODE'                 => 'Y',
        'AJAX_OPTION_HISTORY'       => false,
        'AJAX_OPTION_JUMP'          => 'N',
        'PAGE_SIZES'                => [
            ['VALUE' => '10',   'NAME' => '10'],
            ['VALUE' => '20',   'NAME' => '20'],
            ['VALUE' => '50',   'NAME' => '50'],
            ['VALUE' => '100',  'NAME' => '100'],
            ['VALUE' => '200',  'NAME' => '200'],
            ['VALUE' => '500',  'NAME' => '500'],
        ],
        'SHOW_ROW_CHECKBOXES'       => true,
        'SHOW_CHECK_ALL_CHECKBOXES' => true,
        'SHOW_ROW_ACTIONS_MENU'     => true,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => true,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => true,
        'ALLOW_SORT'                => true,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_PIN_HEADER'          => true,
        'TOTAL_ROWS_COUNT'          => $OrdersGrid->getPagination()->getRecordCount(),

        // Undocumented params
        'EDITABLE'                  => true,

        // Group actions
        'ACTION_PANEL'              => [
            'GROUPS' => [
                'TYPE' => [
                    'ITEMS' => $OrdersGrid->getControls(),
                ]
            ]
        ],
    ]);

    Tools::jqInclude();
    // CSS hack for grid coloring
    ?>
    <style>.main-grid-cell {background: none !important;} .cata_btn_disabled {opacity:.5;cursor:default;pointer-events:none}</style>
    <script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
    <script type="text/javascript">
        var <?=CATAPULTO_DELIVERY_LBL?>controller = new catapulto_delivery_adminInterface({
            'ajaxPath' : '<?=Tools::getJSPath()?>ajax.php',
            'label'    : '<?=CATAPULTO_DELIVERY?>',
            'logging'  : true
        });

        <?=CATAPULTO_DELIVERY_LBL?>controller.expander({});
        <?=CATAPULTO_DELIVERY_LBL?>controller.addPage('main', {
            init : function () {
                this.actions(this);
                this.grids(this);
                //Row select events
                BX.addCustomEvent(
                    'Grid::selectRow',
                    BX.delegate(this.actions.onGridSelectChange, this)
                );
                BX.addCustomEvent(
                    'Grid::unselectRow',
                    BX.delegate(this.actions.onGridSelectChange, this)
                );

                //Отдельно - для селекта и деселекта всех записей
                BX.addCustomEvent(
                    'Grid::allRowsSelected',
                    BX.delegate(this.actions.onGridSelectChange, this)
                );
                BX.addCustomEvent(
                    'Grid::allRowsUnselected',
                    BX.delegate(this.actions.onGridSelectChange, this)
                );
            },
            actions : (function (self) {
                self.actions = {
                        // suncs
                    suncBtn : false,
                    suncStatuses: function (btnLink) {
                        if(!self.actions.suncBtn) {
                            self.actions.suncBtn = $(btnLink);
                        }
                        self.actions.suncBtn.attr('disabled','disabled');
                        self.actions.suncBtn.css('opacity',0.7);
                        self.self.ajax({
                            data : {<?=CATAPULTO_DELIVERY_LBL?>action:'refreshStatusesAjax'},
                            success : self.actions.onSunc
                        });
                    },
                    onSunc : function (answer) {
                        self.actions.suncBtn.removeAttr('disabled');
                        self.actions.suncBtn.css('opacity',"");
                        self.grids.reload();
                    },

                    suncOrderStatus : function (bitrixId) {
                        self.self.ajax({
                            data : {<?=CATAPULTO_DELIVERY_LBL?>action:'checkStatusByBitrixIAjax',bitrixId:bitrixId},
                            success : self.actions.onOrderSunc
                        });
                    },

                    cancelOrder: function(recId) {
                        if (confirm("<?=Tools::getMessage('ADMIN_CANCELORDER_CONFIRM')?>")) {
                            self.self.ajax({
                                data : {<?=CATAPULTO_DELIVERY_LBL?>action:'cancelOrderByRecIdAjax',recId:recId},
                                dataType: 'json',
                                success : function(res) {
                                    if (typeof(res.r) === 'undefined') {
                                        alert('Error');
                                        return false;
                                    }
                                    if (res.r) self.grids.reload();
                                    else alert(res.mes);
                                }
                            });
                        }
                    },

                    suncSelectedStatuses: function(ids){
                        self.self.ajax({
                            data : {<?=CATAPULTO_DELIVERY_LBL?>action:'checkStatusByBDIAjax',ids:ids},
                            success : self.actions.onOrderSunc
                        });
                    },
                    onOrderSunc : function (answer) {
                        self.grids.reload();
                    },
                        // print barcodes
                    print  : function (bitrixId) {
                        self.self.ajax({
                            data     : {<?=CATAPULTO_DELIVERY_LBL?>action:'getDocsAjax',bitrixId:bitrixId,type:'invoice'},
                            dataType : 'json',
                            success  : self.actions.onOrderPrint
                        });
                    },
                    onOrderPrint : function (data) {
                        if(data.success){
                            window.open(data.file);
                        } else {
                            alert('<?=Tools::getMessage('TABLE_ORDER_PRINT_ERR')?> ');
                        }
                    },
                    onDocPrint : function (data) {
                        if(data.success){
                            window.open(data.file);
                        } else {
                            alert('<?=Tools::getMessage('TABLE_ORDER_DOC_ERR')?> ');
                        }
                    },
                    onDocsPrint : function(data){
                        var errOrder = '';
                        for(var orderId in data){
                            if(data[orderId].success){
                                window.open(data[orderId].file);
                            } else {
                                errOrder += orderId+', ';
                            }
                        }

                        if(errOrder){
                            alert('<?=Tools::getMessage('TABLE_ORDER_DOCS_ERR')?> '+errOrder);
                        }
                    },
                    prints : function (ids) {
                        self.self.ajax({
                            data     : {<?=CATAPULTO_DELIVERY_LBL?>action:'getDocsAjaxByBDId',ids:ids,type:'invoice'},
                            dataType : 'json',
                            success  : self.actions.onOrdersPrint
                        });
                    },
                    onOrderSticker : function (data) {
                        if(data.success){
                            console.log(data);
                            window.open(data.file);
                        } else {
                            alert('<?=Tools::getMessage('TABLE_ORDER_STICKER_ERR')?> ');
                        }
                    },
                    onOrdersStickers : function(data){
                        var errOrder = '';
                        for(var orderId in data){
                            if(data[orderId].success){
                                window.open(data[orderId].file);
                            } else {
                                errOrder += orderId+', ';
                            }
                        }

                        if(errOrder){
                            alert('<?=Tools::getMessage('TABLE_ORDER_STICKERS_ERR')?> '+errOrder);
                        }
                    },
                    printStickers  : function (bitrixId) {
                        self.self.ajax({
                            data     : {<?=CATAPULTO_DELIVERY_LBL?>action:'getDocsAjax',bitrixId:bitrixId,type:'sticker'},
                            dataType : 'json',
                            success  : self.actions.onOrderSticker
                        });
                    },
                    printOrdersStickers : function (ids) {
                        self.self.ajax({
                            data     : {<?=CATAPULTO_DELIVERY_LBL?>action:'getDocsAjaxByBDId',ids:ids,type:'sticker'},
                            dataType : 'json',
                            success  : self.actions.onOrdersStickers
                        });
                    },
                    onOrdersPrint : function(data){
                        var errOrder = '';
                        for(var orderId in data){
                            if(data[orderId].success){
                                window.open(data[orderId].file);
                            } else {
                                errOrder += orderId+', ';
                            }
                        }

                        if(errOrder){
                            alert('<?=Tools::getMessage('TABLE_ORDER_PRINTS_ERR')?> '+errOrder);
                        }
                    },
                    printOrderDocs  : function (bitrixId) {
                        self.self.ajax({
                            data     : {<?=CATAPULTO_DELIVERY_LBL?>action:'getDocsAjax',bitrixId:bitrixId,type:'all'},
                            dataType : 'json',
                            success  : self.actions.onDocPrint
                        });
                    },
                    printOrdersDocs : function (ids) {
                        self.self.ajax({
                            data     : {<?=CATAPULTO_DELIVERY_LBL?>action:'getDocsAjaxByBDId',ids:ids,type:'all'},
                            dataType : 'json',
                            success  : self.actions.onDocsPrint
                        });
                    },
                    printOrderPOA : function (bitrixId) {
                        self.self.ajax({
                            data     : {<?=CATAPULTO_DELIVERY_LBL?>action:'getDocsAjax',bitrixId:bitrixId,type:'POA'},
                            dataType : 'json',
                            success  : self.actions.onDocPrint
                        });
                    },
                    printOrderPOAAll : function (ids) {
                        self.self.ajax({
                            data     : {<?=CATAPULTO_DELIVERY_LBL?>action:'getDocsAjaxByBDId',ids:ids,type:'POA'},
                            dataType : 'json',
                            success  : self.actions.onDocsPrint
                        });
                    },
                    cancelOrders: function (ids) {
                        self.self.ajax({
                            data: {<?=CATAPULTO_DELIVERY_LBL?>action: 'cancelOrdersByRecIdsAjax', ids: ids},
                            dataType: 'json',
                            success: function (res) {
                                if (typeof (res.r) === 'undefined') {
                                    alert('Error');
                                    return false;
                                }
                                if (res.r) self.grids.reload();
                                else alert(res.mes);
                            }
                        });
                    },
                    onGridSelectChange: function (row) {
                        const gId = '<?=$OrdersGrid->getId()?>',
                            grid = BX.Main.gridManager.getInstanceById(gId),
                            sel = grid.getRows().getSelectedIds(),
                            t = document.getElementById("ctplt_cancel_orders");
                        let isDisabled = false;
                        for (let i in sel) {
                            const isCanceable = grid.getRows().getById(sel[i]).getNode().attributes['cancelable'].value === '1';
                            if (!isCanceable) isDisabled = true;
                        }
                        if (isDisabled) t.classList.add("cata_btn_disabled")
                        else t.classList.remove("cata_btn_disabled");
                    },
                }
            }),
            grids : (function(self){
                self.grids = {
                    reload: function () {
                        self.grids.reloading('<?=$OrdersGrid->getId()?>');
                    },
                    reloading: function (gridId) {
                        var reloadParams = {apply_filter: 'Y',
                                            //clear_nav: 'Y'
                                            };
                        var gridObject = BX.Main.gridManager.getById(gridId);

                        if (gridObject.hasOwnProperty('instance')) {
                            gridObject.instance.reloadTable('POST', reloadParams);
                        }
                    }
                }
            })
        });
        $(document).ready(<?=CATAPULTO_DELIVERY_LBL?>controller.init);
    </script>
    <?php
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
