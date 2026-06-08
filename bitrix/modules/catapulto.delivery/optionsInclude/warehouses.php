<?php

use Ipol\Catapulto\Bitrix\Tools;

global $APPLICATION, $USER, $arAllOptions;

$LABEL = Ipol\Catapulto\AbstractGeneral::getMODULELBL();

$APPLICATION->AddHeadString('<link href="' . Tools::getJSPath() . 'jq_chosen/chosen.min.css"  type="text/css" rel="stylesheet">', true);
$APPLICATION->AddHeadString('<script src="' . Tools::getJSPath() . 'jq_chosen/chosen.jquery.min.js"></script>', true);

//Для компонента sale.location.selector.search, чтобы использовать рендеринг input без подключения самого компонента
$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/sale.location.selector.search/templates/.default/style.css');
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_location/bundle.config.js'); // Если нужно ядро
// Но лучше оставить Extension, он в админке обычно отрабатывает:
\CJSCore::Init(['core', 'ajax', 'ui_widget', 'ui_autocomplete']);
\Bitrix\Main\UI\Extension::load("sale.location.selector.search");
\Bitrix\Main\UI\Extension::load('ui.design-tokens');

$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_widget.js');
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_etc.js');
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_autocomplete.js');


$warehousesData = \Ipol\Catapulto\WarehousesTable::getWarehouses();
$pointsInIds    = [];
$pointsInData   = [];

//Склад по-умолчанию из настроек
$defaultWarehouseId = \Ipol\Catapulto\Option::get('DEFAULT_WAREHOUSE_ID');

$htmlFreeDeliveryBlock = '<table class="wh-free-delivery-tbl" data-id="${id}">
                            <tbody><tr>
                            <td class="adm-detail-content-cell-r" style="width: 100%;">
                                <div id="sls-${fullId}" class="bx-sls bx-admin-mode">
                                    <div class="dropdown-block bx-ui-sls-input-block">
                                        <span class="dropdown-icon"></span>
                                        <input type="text" placeholder="' . Tools::getMessage('WH_INPUT_NAME') . '" autocomplete="off" value="" name="WH_FREE_DELIVERY_BX_LOC[${warehouseId}][${id}]">
                                        <div class="dropdown-fade2white"></div>
                                        <div class="bx-ui-sls-loader"></div>
                                        <div class="bx-ui-sls-clear" title="' . Tools::getMessage('WH_CLEAN_OUT') . '"></div>
                                        <div class="bx-ui-sls-pane">
                                        <script type="text/html" data-template-id="bx-ui-sls-error">
                                            <div class="bx-ui-sls-error">
                                                <div></div>
                                                {{message}}
                                            </div>
                                        <\/script>
                                        <script type="text/html" data-template-id="bx-ui-sls-dropdown-item">
                                            <div class="dropdown-item bx-ui-sls-variant">
                                                <span class="dropdown-item-text">{{display_wrapped}}</span>
                                            </div>
                                        <\/script>
                                    </div>
                                </div>
                                <input type="hidden" maxlength="50" size="20" name="WH_FREE_DELIVERY_CITY_FIAS_ID[${warehouseId}][${id}]">
                                <input type="hidden" maxlength="50" size="20" name="WH_FREE_DELIVERY_REGION_FIAS_ID[${warehouseId}][${id}]">
                            </td>
                            <td class="adm-detail-content-cell"><a href="javascript:void(0)" onclick="removeFreeDelivery(this, \'${id}\');"><img src="/bitrix/themes/.default/images/actions/delete_button.gif" border="0" width="20" height="20"></a></td>
                        </tr>
                        <tr>
                            <td class="adm-detail-content-cell-l">' . Tools::getMessage('OPT_deliveryFreeCourierFrom') . ':</td>
                            <td class="adm-detail-content-cell-r"><input type="text" maxlength="7" size="5" name="WH_FREE_DELIVERY_FREE_COURIER_FROM[${warehouseId}][${id}]"></td>
                        </tr>
                        <tr>
                            <td class="adm-detail-content-cell-l">' . Tools::getMessage('OPT_deliveryFreePVZFrom') . ':</td>
                            <td class="adm-detail-content-cell-r"><input type="text" maxlength="7" size="5" name="WH_FREE_DELIVERY_FREE_PICKUP_FROM[${warehouseId}][${id}]"></td>
                        </tr>
                    </tbody></table>';
?>

<style>
    #WH_ADDRESSES tr.wh_name td {
        padding: 10px 0;
        text-align: center;
        border-bottom: 2px solid #e0e8ea;
        background: #e0e8ea;
        font-weight: bold;
    }

    #WH_ADDRESSES tr.wh_settings td {
        /*padding: 10px 0;*/
        padding-top: 2px;
        padding-bottom: 2px;
    }

    #WH_ADDRESSES tr.wh_settings p {
        margin: 5px 0;
    }

    #WH_ADDRESSES tr.wh_settings_end td {
        border-bottom: 2px solid #e0e8ea;
        padding-bottom: 15px;
    }

    #WH_ADDRESSES tr.wh_settings_end:last-child td {
        border-bottom: none;
        padding-bottom: 5px;
    }

    #WH_ADDRESSES .switching {
        display: inline-block;
        margin-right: 40px;
        margin-left: 40px;
    }

    .stores-main, .stores-operators {
        vertical-align: top;
    }

    .stores-main {
        width: 35%;
    }

    .stores-operators {
        width: 65%;
    }

    .stores-main table, .stores-operators table {
        width: 100%;
        position: relative;
    }

    .wh-free-delivery-tbl {
        border-spacing: 0;
        padding: 0;
    }
</style>

<tr>
    <td colspan='2'>
        <table id="WH_ADDRESSES" cellpadding="0" cellspacing="2" border="0" width="100%" align="center">
            <tr class="heading">
                <td align="center" colspan="2"><?= Tools::getMessage('TAB_WH_TITLE') ?></td>
            </tr>
            <tr>
                <td colspan="2"><?
                    Tools::placeFAQ('defaultSenderTerminal')
                    ?></td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="ipol_adminButtonPanel">
                        <button onclick="addWarehouse(this);" class="ui-btn ui-btn-success">Добавить склад</button>
                    </div>
                </td>
            </tr>

            <tr class="wh_name">
                <td colspan="2">

                </td>
            </tr>
            <tr class="wh_settings def-store">
                <td class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_DEFAULT_STORE') ?></td>
                <td class="adm-detail-content-cell-r">
                    <select name="DEFAULT_WAREHOUSE_ID">
                        <?php
                        foreach ($warehousesData as $arStore) { ?>
                            <option value="<?= $arStore['ID'] ?>"<?= ($arStore['ID'] == $defaultWarehouseId ? ' selected' : '') ?>><?= ('[' . $arStore['ID'] . '] ' . $arStore['BX_LOC_NAME']) ?>
                            </option>
                        <?php
                        } ?>
                    </select>
                </td>
            </tr>
            <?php
            $arScripts = [];
            foreach ($warehousesData as $data) { ?>
                <tr class="wh_name">
                    <td colspan="4">
                        <?= Tools::getMessage('WH_NAME') ?><?= $data['ID'] ?> <?= $data['BX_LOC_NAME'] ?>
                        <div class="switching"><?= Tools::getMessage('WH_SWITCH_ON') ?>: <input type="checkbox" value="Y" name="WH_ACTIVE[<?= $data['ID'] ?>]" <?= ($data['ACTIVE'] == 'Y' ? 'checked' : '') ?>></div>
                        <?php
                        if ($USER->isAdmin()) { ?>
                            <span onclick="removeWarehouse(this, <?= $data['ID'] ?>);" class="ui-btn ui-btn-xs ui-btn-danger"><?= Tools::getMessage('WH_REMOVE') ?></span>
                        <?php
                        } ?>
                    </td>
                </tr>
                <tr class="wh_settings">
                    <td class="stores-main">
                        <table cellpadding="0" cellspacing="2" border="0" width="100%" align="center">
                            <tr class="wh_name">
                                <td colspan="2"><?= Tools::getMessage('WH_SENDER') ?></td>
                            </tr>
                            <tr>
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_COORDS') ?></td>
                                <td width="60%" class="adm-detail-content-cell-r td-coords">
                                    <p><?= Tools::getMessage('WH_COORDS_HINT') ?></p>
                                    <input type="text" name="WH_LAT[<?= $data['ID'] ?>]" value="<?= $data['LAT'] ?>">
                                    <input type="text" name="WH_LON[<?= $data['ID'] ?>]" value="<?= $data['LON'] ?>">
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l">
                                    <?= Tools::getMessage('WH_BXLOC') ?>
                                    <?php
                                    if ($hint = Tools::getMessage('WH_BXLOC_HINT')) {
                                        $id = 'WH_BXLOC_' . $data['ID'];
                                        ?>
                                        <a href='#' class='<?= $LABEL ?>PropHint' onclick='return <?= $LABEL ?>setups.popup("pop-<?= $id ?>", this, $(this).closest("table"));'></a>
                                        <?php
                                        Tools::placeHintMultiple($id, 'WH_BXLOC_HINT');
                                    } ?>
                                </td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <?
                                    $APPLICATION->IncludeComponent(
                                            "bitrix:sale.location.selector.search",
                                            "",
                                            [
                                                    "ID"                     => "",
                                                    "CODE"                   => $data['BX_LOC'],
                                                    "INPUT_NAME"             => "WH_BXLOC[" . $data['ID'] . "]",
                                                    "PROVIDE_LINK_BY"        => "code",
                                                    "SHOW_ADMIN_CONTROLS"    => 'N',
                                                    "SELECT_WHEN_SINGLE"     => 'N',
                                                    "FILTER_BY_SITE"         => 'N',
                                                    "SHOW_DEFAULT_LOCATIONS" => 'N',
                                                    "SEARCH_BY_PRIMARY"      => 'Y'
                                            ],
                                            false
                                    ); ?>
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_CATAPULTO_CITY') ?>
                                    <?php
                                    if ($hint = Tools::getMessage('WH_CATAPULTO_CITY_HINT')) {
                                        $id = 'WH_CATAPULTO_CITY_ID_' . $data['ID'];
                                        ?>
                                        <a href='#' class='<?= $LABEL ?>PropHint' onclick='return <?= $LABEL ?>setups.popup("pop-<?= $id ?>", this, $(this).closest("table"));'></a>
                                        <?php
                                        Tools::placeHintMultiple($id, 'WH_CATAPULTO_CITY_HINT');
                                    } ?>
                                </td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="text" name="WH_CATAPULTO_CITY_ID[<?= $data['ID'] ?>]" value="<?= $data['CATAPULTO_CITY_ID'] ?>">
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_CATAPULTO_CITY_INDEX') ?>
                                    <?php
                                    if ($hint = Tools::getMessage('WH_CATAPULTO_CITY_INDEX_HINT')) {
                                        $id = 'WH_CATAPULTO_CITY_INDEX_' . $data['ID'];
                                        ?>
                                        <a href='#' class='<?= $LABEL ?>PropHint' onclick='return <?= $LABEL ?>setups.popup("pop-<?= $id ?>", this, $(this).closest("table"));'></a>
                                        <?php
                                        Tools::placeHintMultiple($id, 'WH_CATAPULTO_CITY_INDEX_HINT');
                                    } ?>
                                </td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="text" name="WH_CATAPULTO_CITY_INDEX[<?= $data['ID'] ?>]" value="<?= $data['CATAPULTO_CITY_INDEX'] ?>">
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_CATAPULTO_CONTACT') ?>
                                    <?php
                                    if ($hint = Tools::getMessage('WH_CATAPULTO_CONTACT_HINT')) {
                                        $id = 'WH_CATAPULTO_CONTACT_' . $data['ID'];
                                        ?>
                                        <a href='#' class='<?= $LABEL ?>PropHint' onclick='return <?= $LABEL ?>setups.popup("pop-<?= $id ?>", this, $(this).closest("table"));'></a>
                                        <?php
                                        Tools::placeHintMultiple($id, 'WH_CATAPULTO_CONTACT_HINT');
                                    } ?>
                                </td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="text" name="WH_CATAPULTO_CONTACT_ID[<?= $data['ID'] ?>]" value="<?= $data['CATAPULTO_CONTACT_ID'] ?>">
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('OPT_deliveryFreeCourierFrom') ?>:</td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="text" name="WH_FREE_COURIER_FROM[<?= $data['ID'] ?>]" value="<?= $data['FREE_COURIER_FROM'] ?>">
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('OPT_deliveryFreePVZFrom') ?>:</td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="text" name="WH_FREE_PICKUP_FROM[<?= $data['ID'] ?>]" value="<?= $data['FREE_PICKUP_FROM'] ?>">
                                </td>
                            </tr>
                            <tr class="wh_settings" id="wh-free-delivery-wrap">
                                <td width="40%" class="adm-detail-content-cell-l">
                                    <?= Tools::getMessage('OPT_deliveryFreeByCity') ?>:
                                    <?php
                                    if ($hint = Tools::getMessage('WH_FREE_DELIVERY_BXLOC_HINT')) {
                                        $id = 'WH_FREE_DELIVERY_BXLOC_' . $data['ID'];
                                        ?>
                                        <a href='#' class='<?= $LABEL ?>PropHint' onclick='return <?= $LABEL ?>setups.popup("pop-<?= $id ?>", this, $(this).closest("table"));'></a>
                                        <?php
                                        Tools::placeHintMultiple($id, 'WH_FREE_DELIVERY_BXLOC_HINT');
                                    } ?>
                                </td>
                                <td width="60%">
                                    <?php
                                    //Вывод из БД
                                    if(!empty($data['FREE_DELIVERY_SETUP'])) {
                                        foreach ($data['FREE_DELIVERY_SETUP'] as $freeId => $arValues) {
                                            $freeFullId = "[" . $data['ID'] . "][" . $arValues['ID'] . "]";?>
                                            <table class="wh-free-delivery-tbl" data-id="<?=$freeId?>">
                                                <tbody><tr>
                                                    <td class="adm-detail-content-cell-r" style="width: 100%;">
                                                    <?$APPLICATION->IncludeComponent(
                                                        "bitrix:sale.location.selector.search",
                                                        "",
                                                        [
                                                                "ID"                     => "",
                                                                "CODE"                   => $arValues['BX_LOC'],
                                                                "INPUT_NAME"             => "WH_FREE_DELIVERY_BX_LOC" . $freeFullId,
                                                                "PROVIDE_LINK_BY"        => "code",
                                                                "SHOW_ADMIN_CONTROLS"    => 'N',
                                                                "SELECT_WHEN_SINGLE"     => 'N',
                                                                "FILTER_BY_SITE"         => 'N',
                                                                "SHOW_DEFAULT_LOCATIONS" => 'N',
                                                                "SEARCH_BY_PRIMARY"      => 'Y'
                                                        ],
                                                        false);?>
                                                    <input type="hidden" value="<?=$arValues['CITY_FIAS_ID']?>" maxlength="50" size="20" name="WH_FREE_DELIVERY_CITY_FIAS_ID<?=$freeFullId?>">
                                                    <input type="hidden" value="<?=$arValues['REGION_FIAS_ID']?>" maxlength="50" size="20" name="WH_FREE_DELIVERY_REGION_FIAS_ID<?=$freeFullId?>">
                                                    </td>
                                                    <td class="adm-detail-content-cell"><a href="javascript:void(0)" onclick="removeFreeDelivery(this, '<?=$arValues['ID']?>');"><img src="/bitrix/themes/.default/images/actions/delete_button.gif" border="0" width="20" height="20"></a></td>
                                                </tr>
                                                <tr>
                                                    <td class="adm-detail-content-cell-l"><?=Tools::getMessage('OPT_deliveryFreeCourierFrom')?>:</td>
                                                    <td class="adm-detail-content-cell-r"><input type="text" value="<?=$arValues['FREE_COURIER_FROM']?>" maxlength="7" size="5" name="WH_FREE_DELIVERY_FREE_COURIER_FROM<?=$freeFullId?>"></td>
                                                </tr>
                                                <tr>
                                                    <td class="adm-detail-content-cell-l"><?=Tools::getMessage('OPT_deliveryFreePVZFrom')?>:</td>
                                                    <td class="adm-detail-content-cell-r"><input type="text" value="<?=$arValues['FREE_PICKUP_FROM']?>" maxlength="7" size="5" name="WH_FREE_DELIVERY_FREE_PICKUP_FROM<?=$freeFullId?>"></td>
                                                </tr>
                                            </tbody></table><?php
                                        }
                                    }

                                    //Добавление пустых, если забыли заполнить
                                    if(array_key_exists('WH_FREE_DELIVERY_BX_LOC', $_REQUEST)) {
                                        foreach ($_REQUEST['WH_FREE_DELIVERY_BX_LOC'][$data['ID']] as $k => $v) {
                                            $notEmpty = (!$_REQUEST['WH_FREE_DELIVERY_FREE_COURIER_FROM'][$data['ID']][$k] && !$_REQUEST['WH_FREE_DELIVERY_FREE_PICKUP_FROM'][$data['ID']][$k]);
                                            if(empty($v) && $notEmpty) {
                                                $fullId = 'wh-'.$data['ID'].'-free-delivery-bx-loc-' . $k;
                                                $arScripts[] = "applySaleSearchExtention('" . $fullId . "');";

                                                 print str_replace(
                                                        ['${fullId}', '${id}', '${warehouseId}', '<\/script>'],
                                                        [$fullId, $k, $data['ID'], '</script>'],
                                                        $htmlFreeDeliveryBlock
                                                );
                                            }
                                        }
                                    }
                                    ?>
                                    <span onclick="addWarehouseFreeDelivery(this, <?= $data['ID'] ?>);" class="ui-btn ui-btn-xs ui-btn-success"><?= Tools::getMessage('WH_ADD_BTN') ?></span>
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_POA_ENABLED') ?></td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="checkbox" name="WH_POA_ENABLED[<?= $data['ID'] ?>]" value="Y" <?= ($data['POA_ENABLED'] == 'Y' ? 'checked' : '') ?>>
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_POA_FROM_DATA_FIO') ?></td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="text" name="WH_POA_FROM_DATA_FIO[<?= $data['ID'] ?>]" value="<?= ($data['POA_FROM_DATA']['FIO'] ?? '') ?>">
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_POA_FROM_DATA_PASSPORT_SERIA') ?></td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="text" name="WH_POA_FROM_DATA_PASSPORT_SERIA[<?= $data['ID'] ?>]" value="<?= ($data['POA_FROM_DATA']['PASSPORT_SERIA'] ?? '') ?>">
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_POA_FROM_DATA_PASSPORT_NUMBER') ?></td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="text" name="WH_POA_FROM_DATA_PASSPORT_NUMBER[<?= $data['ID'] ?>]" value="<?= ($data['POA_FROM_DATA']['PASSPORT_NUMBER'] ?? '') ?>">
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_POA_FROM_DATA_PASSPORT_DATE') ?></td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="text" name="WH_POA_FROM_DATA_PASSPORT_DATE[<?= $data['ID'] ?>]" value="<?= ($data['POA_FROM_DATA']['PASSPORT_DATE'] ?? '') ?>" onclick="BX.calendar({node: this, field: this, bTime: false});">
                                </td>
                            </tr>
                            <tr class="wh_settings">
                                <td width="40%" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_POA_FROM_DATA_EMAIL') ?></td>
                                <td width="60%" class="adm-detail-content-cell-r">
                                    <input type="text" name="WH_POA_FROM_DATA_EMAIL[<?= $data['ID'] ?>]" value="<?= ($data['POA_FROM_DATA']['EMAIL'] ?? '') ?>">
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="stores-operators">
                        <table>
                            <tr class="wh_name">
                                <td colspan="5"><?= Tools::getMessage('WH_OPERATORS') ?></td>
                            </tr>
                            <tr class="wh_settings">
                                <td colspan="2" class="adm-detail-content-cell-l"><?= Tools::getMessage('WH_DELIVERY_FROM_DEFAULT') ?></td>
                                <td colspan="3" class="adm-detail-content-cell-r">
                                    <select name="WH_DELIVERY_FROM[<?= $data['ID'] ?>]">
                                        <option value="door"<?= ($data['DELIVERY_FROM'] == 'door') ? ' selected' : '' ?>><?= Tools::getMessage('WH_FROM_DOOR') ?></option>
                                        <option value="warehouse"<?= ($data['DELIVERY_FROM'] == 'warehouse') ? ' selected' : '' ?>><?= Tools::getMessage('WH_FROM_WH') ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="<?= $LABEL ?>operatorstable">
                                <td class="adm-detail-content-cell-l" style="vertical-align: top"><b><?= Tools::getMessage('OPERATORS') ?></b></td>
                                <td class="adm-detail-content-cell-r">
                                    <div class="<?= $LABEL ?>freedlv"><b><?= Tools::getMessage('FREE_DELIVERY') ?></b></div>
                                </td>
                                <td class="adm-detail-content-cell-r">
                                    <b><?= Tools::getMessage('SEND_TYPE') ?></b>
                                </td>
                                <td class="adm-detail-content-cell-r">
                                    <b><?= Tools::getMessage('TERMINAL_CODE') ?></b>
                                </td>
                                <td class="adm-detail-content-cell-r">
                                    <b><?= Tools::getMessage('DELIVERY_TYPE') ?></b>
                                    <?php
                                    if ($hint = Tools::getMessage('DELIVERY_TYPE_HINT')) {
                                        $id = 'DELIVERY_TYPE_' . $data['ID'];
                                        ?>
                                        <a href='#' class='<?= $LABEL ?>PropHint' onclick='return <?= $LABEL ?>setups.popup("pop-<?= $id ?>", this, $(this).closest("table"));'></a>
                                        <?php
                                        Tools::placeHintMultiple($id, 'DELIVERY_TYPE_HINT');
                                    } ?>
                                </td>
                            </tr>
                            <?php
                            foreach ($arAllOptions['defaultSenderTerminal'] as $terminal) {
                                $currentOperator                  = $data['OPERATORS_SETUP'][$terminal[6]['id']] ?? [];
                                $currentOperator['DELIVERY_TYPE'] = $currentOperator['DELIVERY_TYPE'] ? : [];
                                ?>
                                <tr class="<?= $LABEL ?>operatorstable">
                                    <td class="adm-detail-content-cell-l"><?= $terminal[1] ?></td>
                                    <td class="adm-detail-content-cell-r">
                                        <div class="<?= $LABEL ?>freedlv">
                                            <input type="checkbox" value="Y" name="WH_OPERATORS_FREE[<?= $data['ID'] ?>][<?= $terminal[6]['id'] ?>]" <?= ($currentOperator['FREE'] == 'Y' ? 'checked' : '') ?> />
                                        </div>
                                    </td>
                                    <td class="adm-detail-content-cell-r">
                                        <select class="ctpt_op_stype" name="WH_OPERATORS_DELIVERY_FROM[<?= $data['ID'] ?>][<?= $terminal[6]['id'] ?>]">
                                            <option value="door"<?= ($currentOperator['DELIVERY_FROM'] == 'door' ? ' selected' : '') ?>><?= Tools::getMessage("LBL_DOOR") ?></option>
                                            <option value="warehouse"<?= ($currentOperator['DELIVERY_FROM'] == 'warehouse' ? ' selected' : '') ?>><?= Tools::getMessage("LBL_WAREHOUSE") ?></option>
                                        </select>
                                    </td>
                                    <td class="adm-detail-content-cell-r">
                                        <input
                                                class="ctpt_opterm_code"
                                                type="text"
                                                name="WH_OPERATORS_DEFAULT_TERMINAL[<?= $data['ID'] ?>][<?= $terminal[6]['id'] ?>]"
                                                value="<?= $currentOperator['DEFAULT_TERMINAL'] ?? '' ?>"
                                                <?= (!$currentOperator['DEFAULT_TERMINAL'] ? ' style="display: none"' : '') ?>
                                        />
                                    </td>
                                    <td class="adm-detail-content-cell-r">
                                        <?php
                                        $pvz      = in_array('pvz', $currentOperator['DELIVERY_TYPE']);
                                        $postamat = in_array('postamat', $currentOperator['DELIVERY_TYPE']);
                                        $courier  = in_array('courier', $currentOperator['DELIVERY_TYPE']);
                                        $all      = ($pvz && $postamat && $courier) || (!$pvz && !$postamat && !$courier);
                                        ?>
                                        <select multiple id="WH_OPERATORS_DELIVERY_TYPE_<?= $data['ID'] ?>_<?= $terminal[6]['id'] ?>" class="ctpl_select_delivery_type chosen-select" name="WH_OPERATORS_DELIVERY_TYPE[<?= $data['ID'] ?>][<?= $terminal[6]['id'] ?>][]">
                                            <option value="all"<?= ($all ? ' selected' : '') ?>><?= Tools::getMessage('LBL_ALL') ?></option>
                                            <option value="pvz"<?= (!$all && $pvz ? ' selected' : '') ?>><?= Tools::getMessage('LBL_PVZ_SHORT') ?></option>
                                            <option value="postamat"<?= (!$all && $postamat ? ' selected' : '') ?>><?= Tools::getMessage('LBL_POSTAMAT') ?></option>
                                            <option value="courier"<?= (!$all && $courier ? ' selected' : '') ?>><?= Tools::getMessage('LBL_COURIER_SHORT') ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
    </td>
</tr>

<script>
    function addWarehouse(btn) {
        $(btn).closest('form').prepend('<input type="hidden" name="WH_ADD_NEW_STORE" value="Y">');
        return true;
    }

    function removeWarehouse(btn, id) {
        if (confirm(`<?= Tools::getMessage('WH_CONFIRM_REMOVE') ?>`)) {
            let form = $(btn).closest('form');
            form.prepend(`<input type="hidden" name="WH_REMOVE_STORE" value="${id}">`);
            form.submit();
        } else {
            return false;
        }
    }

    function addWarehouseFreeDelivery(btn, warehouseId) {
        let fdWrap = $(btn).closest('#wh-free-delivery-wrap'),
            id = 'new',
            fullId = `wh-${warehouseId}-free-delivery-bx-loc-`,
            inputs = fdWrap.find('input[name^="WH_FREE_DELIVERY_BX_LOC"]');

        if (inputs.length > 0) {
            if (inputs[inputs.length - 1]) {
                id = $(inputs[inputs.length - 1]).closest('.wh-free-delivery-tbl').data('id');
                if (!isNaN(id) && isFinite(id)) {
                    id++;
                } else {
                    id += inputs.length;
                }
            }
        }

        fullId += id;

        let html = `<?=$htmlFreeDeliveryBlock?>`;

        fdWrap.find('span.ui-btn-success').before(html);

        let selector = applySaleSearchExtention(fullId);
    }

    function applySaleSearchExtention(fullId)
    {
        let selector = new BX.Sale.component.location.selector.search({
            'scope': `sls-${fullId}`,
            'source': '/bitrix/components/bitrix/sale.location.selector.search/get.php',
            'query': {
                'FILTER': {
                    'EXCLUDE_ID': '',
                    'SITE_ID': ''
                },
                'BEHAVIOUR': {
                    'SEARCH_BY_PRIMARY': '1',
                    'LANGUAGE_ID': 'ru'
                }
            },
            'selectedItem': false,
            'knownItems': {},
            'provideLinkBy': 'code',
            'messages': {
                'nothingFound': '<?=Tools::getMessage('WH_NOT_FOUND')?>',
                'error': '<?=Tools::getMessage('WH_ERROR')?>'
            },
            // "js logic"-related part
            'callback': {},
            'useSpawn': false,
            'usePopup': false,
            'initializeByGlobalEvent': false,
            'globalEventScope': {},
            // specific
            'types': {}
        });

        return selector;
    }

    function removeFreeDelivery(btn, warehouseId) {
        let fdWrap = $(btn).closest('.wh-free-delivery-tbl');
        fdWrap.hide(0, function () {
            fdWrap.remove();
        });
    }

    $('#WH_ADDRESSES input[type=text]').focus(function () {
        var t = $(this);
        if (t.val() == '0') t.val('');
    });
    BX.ready(function () {
        const form = $('.ipol_adminButtonPanel').nextAll('form').first();
        if (!form.length) return;

        form[0].addEventListener('submit', function (e) {
            // небольшая задержка, чтобы не мигало при мгновенной ошибке
            setTimeout(function () {
                BX.showWait();
            }, 100);
        });
    });
    $(function () {
        <?php
        if(!empty($arScripts)) {
            echo implode("\n", $arScripts);
        }
        ?>

        document.querySelectorAll('.<?=$LABEL?>operatorstable').forEach(el => {
            const sel = el.querySelector('select.ctpt_op_stype'), inp = el.querySelector('input.ctpt_opterm_code');
            if (!sel) return;
            sel.onchange = () => {
                if (sel.value === 'door') {
                    inp.style.display = 'none';
                } else {
                    inp.style.display = '';
                }
            }
        });


        $('body').on('change', 'input[name^=WH_POA_FROM_DATA_PASSPORT_DATE]', function () {
            let t = this, r = /^([0-9]{2})(\.)([0-9]{2})(\.)([0-9]{4})$/;
            if (!r.test(t.value)) t.value = '';
            if (t.value.length > 10) t.value = '';
        });

        $('body').on('change', 'input[name^=WH_POA_FROM_DATA_PASSPORT_SERIA]', function () {
            let t = this, r = /^[0-9]{4}$/;
            if (!r.test(t.value)) t.value = '';
        });

        $('body').on('change', 'input[name^=WH_POA_FROM_DATA_PASSPORT_NUMBER]', function () {
            let t = this, r = /^[0-9]{6}$/;
            if (!r.test(t.value)) t.value = '';
        });

        $('body').on('change', 'input[name^=WH_POA_FROM_DATA_EMAIL]', function () {
            let t = this, r = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
            if (!r.test(t.value)) t.value = '';
        });

        //chosen:ready before .chosen()
        $('.chosen-select').on('chosen:ready', function (event) {
            let all = $(event.target).find('option[value="all"]').prop('selected');
            updateChosenSelect($(event.target), {'selected': all ? 'all' : ''});
        });

        $(".chosen-select").chosen({
            placeholder_text_multiple: '<?=Tools::getMessage('DELIVERY_TYPE_SELECT')?>',
            width: "234px"
        });

        function updateChosenSelect(select, params) {
            let update = false;
            if (params['selected'] === 'all') {
                select.find('option').removeAttr('selected').prop('selected', false);
                select.find('option[value="all"').prop('selected', true);
                //select.find('option[value!="all"]').prop('disabled', true);
                update = true;
            } else if (params['deselected'] === 'all') {
                //select.find('option[value!="all"]').prop('disabled', false);
                update = true;
            } else {
                let all = true;
                select.find('option').each(function (i, el) {
                    if ($(el).val() !== 'all' && !$(el).prop('selected')) {
                        all = false;
                    }
                });

                if (all) {
                    select.find('option').removeAttr('selected').prop('selected', false);
                    select.find('option[value="all"').prop('selected', true);
                    //select.find('option[value!="all"]').prop('disabled', true);
                    update = true;
                } else {
                    select.find('option[value="all"').removeAttr('selected').prop('selected', false);
                    update = true;
                }
            }

            if (update) {
                select.trigger('chosen:updated');
            }
        }

        $('.chosen-select').on('change', function (event, params) {
            updateChosenSelect($(event.target), params);
        });
    })
</script>
<style>
    .WH_PI_LBL {
        width: 500px;
    }

    #IPOL_wndPointIn {
        width: 100%;
    }

    #IPOL_wndPointIn td:first-of-type {
        width: 45%;
    }

    #IPOL_wndPointIn select {
        width: 320px;
    }

    #IPOL_wndPointIn input[type="text"] {
        width: 200px;
    }

    #WH_ADDRESSES tr.def-store td {
        padding-top: 20px;
        padding-bottom: 30px;
    }

    .td-coords input[type="text"] {
        width: 90px;
        display: inline-block;
    }

    .chosen-container-multi .chosen-choices {
        padding-right: 0;
    }
</style>
<script>
    var IPOL_pointInForm = {
        mainWnd: false,
        whId: false,
        providerKey: false,

        init: function () {
            let html = '<table id="IPOL_wndPointIn"><tbody><tr>' +
                '<td><?=Tools::getMessage('WH_F_PI')?></td><td><select id="IPOL_WH_F_PI"></select></td>' +
                '</tr></tbody></table>';

            IPOL_pointInForm.mainWnd = new BX.CDialog({
                title: "<?=Tools::getMessage('WH_F_PI')?>",
                content: html,
                icon: 'head-block',
                resizable: true,
                draggable: true,
                height: '100',
                width: '505',
                buttons: [
                    '<input type=\"button\" id=\"IPOL_pi_saveBtn\" value=\"<?=Tools::getMessage('WH_F_PI_SAVE')?>\" onclick=\"IPOL_pointInForm.save()\"/>',
                    '<input type=\"button\" id=\"IPOL_pi_closeBtn\" value=\"<?=Tools::getMessage('WH_F_PI_CLOSE')?>\" onclick=\"IPOL_pointInForm.close()\"/>',
                ]
            });
        },
        open: function () {
            if (IPOL_pointInForm.mainWnd)
                IPOL_pointInForm.mainWnd.Show();
        },
        close: function () {
            IPOL_pointInForm.mainWnd.Close();
        },
        edit: function (whId, providerKey) {
            IPOL_pointInForm.whId = whId;
            IPOL_pointInForm.providerKey = providerKey;

            let locCode = $('[name="WH_BXLOC[' + whId + ']"]').val();
            let currentPi = $('[name="WH_PI_CDEK[' + whId + ']"]').val();

            BX.showWait();

            $.ajax({
                url: "/bitrix/js/<?=$module_id?>/ajax.php",
                type: 'POST',
                data: {
                    action: 'getPointsInRequest',
                    locCode: locCode,
                    providerKey: providerKey
                },
                dataType: 'json',
                success: function (data) {
                    BX.closeWait();
                    if (data.success) {
                        //console.log(data.data);

                        let html = '';
                        for (let i in data.data) {
                            html += "<option value = '" + i + "'";
                            if (i === currentPi)
                                html += " selected";
                            html += ">" + data.data[i] + "</option>";
                        }
                        $('#IPOL_WH_F_PI').html(html);

                        IPOL_pointInForm.open();
                    } else {
                        var str = '<?=Tools::getMessage('WH_F_PI_ERROR_NOPVZ')?>';
                        if (data.errors.length) {
                            str += "\n" + data.errors;
                        }
                        alert(str);
                    }
                }
            });
        },
        save: function () {
            let pi = $('#IPOL_WH_F_PI').val();

            if (!pi || pi == '0') {
                alert('<?=Tools::getMessage('WH_F_PI_ERROR_SELPVZ')?>');
                return;
            }

            $('[name="WH_PI_CDEK[' + IPOL_pointInForm.whId + ']"]').val(pi);
            $('[name="WH_PI_CDEK_LBL[' + IPOL_pointInForm.whId + ']"]').val($("#IPOL_WH_F_PI option:selected").text());

            IPOL_pointInForm.close();
        },
    };

    $(document).ready(IPOL_pointInForm.init);
</script>
