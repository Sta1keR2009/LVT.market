<?php
use \Ipol\Catapulto\Admin\OrdersMassGrid;
use \Ipol\Catapulto\Bitrix\Tools;

use \Bitrix\Main\Localization\Loc;

define("ADMIN_MODULE_NAME", "catapulto.delivery");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");
global $APPLICATION, $USER;

Loc::loadMessages(__FILE__);
Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/'.ADMIN_MODULE_NAME.'/classes/general/SubscribeHandler.php');

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

//if ($GLOBALS['APPLICATION']->GetGroupRight(CATAPULTO_DELIVERY) > 'D')

$APPLICATION->SetTitle(Tools::getMessage('ORDERS_MASS_TITLE'));
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
    ?>
    <div class="adm-info-message-wrap adm-info-message-gray">
        <div class="adm-info-message">
            <?php /*<div class="adm-info-message-title"><?=Tools::getMessage('ORDERS_MASS_TITLE')?></div>*/?>
            <div class="alert alert-primary"><?=Tools::getMessage('ORDERS_MASS_DESCRIPTION')?></div>
        </div>
    </div>
    <?php
    // Orders interface buttons, filter and grid
    $OrdersMassGrid = new OrdersMassGrid();

    $buttons = $OrdersMassGrid->getButtons();
    if (!empty($buttons))
    {
        $APPLICATION->IncludeComponent('bitrix:ui.button.panel', '.default', [
            'ALIGN'   => 'left',
            'BUTTONS' => $buttons,
        ]);
    }

    $columns = $OrdersMassGrid->getFilterColumns();
    if (!empty($columns))
    {
        $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '.default', [
            'GRID_ID'             => $OrdersMassGrid->getId(),
            'FILTER_ID'           => $OrdersMassGrid->getFilterId(),
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
        'GRID_ID'                   => $OrdersMassGrid->getId(),
        'COLUMNS'                   => $OrdersMassGrid->getColumns(),
        'ROWS'                      => $OrdersMassGrid->getRows(),
        'NAV_OBJECT'                => $OrdersMassGrid->getPagination(),
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
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
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
        'TOTAL_ROWS_COUNT'          => $OrdersMassGrid->getPagination()->getRecordCount(),

        // Undocumented params
        'EDITABLE'                  => true,

        // Group actions
        'ACTION_PANEL'              => [
            'GROUPS' => [
                'TYPE' => [
                    'ITEMS' => $OrdersMassGrid->getControls(),
                ]
            ]
        ],
    ]);

    Tools::jqInclude();
    ?>
    <style>
        .main-grid-row[data-hide-checkbox="true"] .main-grid-cell-checkbox input[type="checkbox"]{
            display: none !important;
        }
        .main-grid-cell {background: none !important;} .cata_btn_disabled {opacity:.5;cursor:default;pointer-events:none}
        #popup-window-content-result_send h3 {
            margin: 0 20px 18px 0;
        }
        #popup-window-content-result_send .alert {
            padding: 6px 12px;
            border: 1px solid transparent;
            border-radius: 6px;
            margin: 0 0 10px;
        }
        #popup-window-content-result_send .alert.alert-warning {
            background: #ffe7e2;
            border: 1px solid #ffc0c0;
        }
        #popup-window-content-result_send .alert.alert-success {
            background: #e9ffe2;
            border: 1px solid #d7ffc0;
        }
        #popup-window-content-result_send .text-success {
            color: #399800;
        }
        #popup-window-content-result_send .text-error {
            color: #e50000;
        }
        #popup-window-content-result_send .result-message {
            display: flex;
            flex-direction: column;
        }
        #popup-window-content-result_send .result-message >div {
            margin: 0 0 10px;
        }
    </style>
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
                if(typeof(this.actions) === 'function') {
                    this.actions(this);
                    this.grids(this);
                }
                //Row select events
                BX.addCustomEvent(
                    'Grid::selectRow',
                    BX.delegate(this.actions.onGridSelectChange, this)
                );
                BX.addCustomEvent(
                    'Grid::unselectRow',
                    BX.delegate(this.actions.onGridSelectChange, this)
                );
                BX.addCustomEvent(
                    'Grid::updated',
                    BX.delegate(this.actions.initCheckboxes, this)
                );
                
                this.actions.initCheckboxes();
            },
            actions : (function (self) {
                self.actions = {
                    initCheckboxes: function () {
                        const getParentNode = el => {
                            return el.parentNode.parentNode.parentNode.parentNode;
                        }
                        const gId = '<?=$OrdersMassGrid->getId()?>',
                            grid = BX.Main.gridManager.getInstanceById(gId),
                            rows = grid.getRows().getRows(),
                            t = document.getElementById("mass-send_control");
                        
                        for (let i in rows) {
                            let row = rows[i].getNode();
                            let td = row.querySelector('.main-grid-cell.main-grid-cell-checkbox input.main-grid-checkbox');
                            if(td) {
                                let attr = row.getAttribute('data-hide-checkbox');
                                if (attr != null) {
                                    if(attr === 'true') {
                                        td.setAttribute('disabled', true);
                                    }
                                    else {
                                        td.removeAttribute('disabled');
                                    }
                                }
                            }
                        }

                        if (document.querySelectorAll('input.ctpt_add_insurance') != null) {
                            document.querySelectorAll('input.ctpt_add_insurance').forEach(e => {
                                e.checked = false;
                                const checkOrdEl = getParentNode(e);
                                e.onchange = () => {
                                    if (checkOrdEl == null) return;
                                    if (e.checked) {
                                        checkOrdEl.setAttribute('data-hide-checkbox', false);
                                    } else {
                                        checkOrdEl.checked = false;
                                        checkOrdEl.setAttribute('data-hide-checkbox', true);
                                    }
                                }
                            });
                        }
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
                    sendOrdersMass : function (orders) {
                        let wait = BX.showWait(BX('mass-send_control'));
                        BX('mass-send_control').setAttribute('disabled', true);
                        self.self.ajax({
                            data     : {<?=CATAPULTO_DELIVERY_LBL?>action:'sendOrdersMass',orders:orders},
                            dataType : 'json',
                            success  : function(result) {
                                BX.closeWait(wait);
                                BX('mass-send_control').removeAttribute('disabled');
                                self.actions.onSendOrdersMass(result);
                            }
                        });
                    },
                    onSendOrdersMass : function(result){
                        let mess = '<h3><?=Tools::getMessage('TABLE_ORDERS_MASS_RESULT')?></h3>';
                        if (result.success) {
                            mess += '<div class="alert alert-success"><?=Tools::getMessage('TABLE_ORDERS_MASS_SUCCESS')?></div>';
                        } else {
                            mess += '<div class="alert alert-warning"><?=Tools::getMessage('TABLE_ORDERS_MASS_ERROR')?></div>';
                        }
                        mess += '<div class="result-message">';
                        for(let id in result.orders_result) {
                            if(result.orders_result[id].success) {
                                mess += `<div class="text-success">${id}: <?=Tools::getMessage('TABLE_ORDERS_MASS_SENDED')?></div>`;
                            }
                            else {
                                mess += `<div class="text-warning">${id}: <?=Tools::getMessage('TABLE_ORDERS_MASS_NOT_SENDED')?><br>
                                <span class="text-error">${result.orders_result[id].error}</span></div>`;
                            }
                        }
                        mess += '</div>';
                        
                        let popup = BX.PopupWindowManager.create("result_send", null, {
                            content: mess,
                            autoHide : false,
                            offsetTop : 1,
                            offsetLeft : 0,
                            lightShadow : true,
                            closeIcon : true,
                            closeByEsc : true,
                            overlay: {
                                backgroundColor: 'gray', opacity: '80'
                            },
                            events: {
                                onPopupClose: function(PopupWindow) {
                                    self.grids.reload();
                                    popup.destroy();
                                }
                            },
                        });
                        popup.show();
                    },
                    onGridSelectChange: function (row) {
                        const gId = '<?=$OrdersMassGrid->getId()?>',
                            grid = BX.Main.gridManager.getInstanceById(gId),
                            sel = grid.getRows().getSelectedIds(),
                            t = document.getElementById("mass-send_control");
                        let isDisabled = false;
                        for (let i in sel) {
                            let node = grid.getRows().getById(sel[i]).getNode();
                            const isCanceable = node.attributes['data-hide-checkbox'].value === 'true';
                            if (isCanceable) {
                                isDisabled = true;
                                if(node.classList.contains("main-grid-row-checked")) {
                                    $(node).trigger('click');//unselect trigger for hide action panel
                                }
                            }
                        }
                        if (isDisabled) {
                            t.classList.add("cata_btn_disabled");
                            t.setAttribute('disabled', true);
                        }
                        else {
                            t.classList.remove("cata_btn_disabled");
                            t.removeAttribute('disabled');
                        }
                    },
                }
            }),
            grids : (function(self){
                self.grids = {
                    reload: function () {
                        self.grids.reloading('<?=$OrdersMassGrid->getId()?>');
                    },
                    reloading: function (gridId) {
                        var reloadParams = {apply_filter: 'Y',
                                            //clear_nav: 'Y'
                                            };
                        var gridObject = BX.Main.gridManager.getById(gridId);

                        if (gridObject.hasOwnProperty('instance')) {
                            gridObject.instance.reloadTable('POST', reloadParams, <?=CATAPULTO_DELIVERY_LBL?>controller.reinit);
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