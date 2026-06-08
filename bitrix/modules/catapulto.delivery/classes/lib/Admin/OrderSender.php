<?php
namespace Ipol\Catapulto\Admin;


use Ipol\Catapulto\Bitrix\Adapter;
use Ipol\Catapulto\Bitrix\Entity\Encoder;
use Ipol\Catapulto\Bitrix\Handler\Deliveries;
use Ipol\Catapulto\Bitrix\Handler\Order;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Option;

class OrderSender
{
    private static $MODULE_ID  = CATAPULTO_DELIVERY;
    private static $MODULE_LBL = CATAPULTO_DELIVERY_LBL;

    public static $workMode;
    public static $workType;
    public static $orderId;
    public static $shipmentId;
    public static $status;


    protected static function getMode()
    {
        switch(self::$workMode){
            case 'order'    : return 1; break;
            case 'shipment' : return 2; break;
        }
        return false;
    }

    protected static function getId(){
        switch(self::getMode()){
            case 1  : return self::$orderId; break;
            case 2  : return self::$shipmentId; break;
        }
        return false;
    }

    public static function init(){

        if(!Tools::isAdminSection())
            return false;

        global $APPLICATION;
        $dir = $APPLICATION->GetCurDir();

        $b24path = Tools::getB24URLs();

        // TODO: проверка на поля
        // Standard BX support
        $check = ($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['REQUEST_URI'];
        if(
            strpos($check, "/bitrix/admin/sale_order_detail.php") !== false ||
            strpos($check, "/bitrix/admin/sale_order_view.php")   !== false
        ) {
            self::$workMode = 'order';
            self::$orderId  = $_REQUEST['ID'];
            self::$workType = 'standart';
        }// B24 support
        elseif (strpos($dir, $b24path['ORDER']) !== false)
        {
            self::$workMode = 'order';
            $arrayOrderId   = explode('/', ltrim($dir, $b24path['ORDER']));
            self::$orderId  = array_shift($arrayOrderId);
            self::$workType = 'b24';
        }

        if(!self::$workType || !self::$workMode || !\cmodule::includeModule('sale') || !Tools::isAdmin('R'))
            return false;

        // Prevent form loading for order history table AJAX calls
        if (isset($_REQUEST['table_id']) && $_REQUEST['table_id'] == 'table_order_history')
            return false;

        // Disable form for B24 new order window
        if(self::$orderId == 0 && self::$workType == 'b24'){
            return false;
        }


        if(
            Option::get('showInOrders') === 'Y' ||
            Deliveries::isCatapultoDelivery(self::getId())
        ) {

            // B24 button container adding
            if (self::$workType == 'b24')
            {
                \Bitrix\Main\UI\Extension::load('ui.buttons');
                \Bitrix\Main\UI\Extension::load('ui.buttons.icons');

                $containerHTML = '<div class="pagetitle-container" id="'.self::$MODULE_LBL.'btn_container"></div>';
                $APPLICATION->AddViewContent('inside_pagetitle', $containerHTML, 20000);

                \CJSCore::Init(array("window"));
                $APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/pubstyles.min.css");
                $APPLICATION->SetAdditionalCSS("/bitrix/panel/main/admin-public.min.css");
            }

            self::loadExportWindow();
            return true;
        }
        return false;
    }

    public static function loadExportWindow(){
        global $APPLICATION;

        $APPLICATION->AddHeadScript(Tools::getJSPath().'wndController.js');
        Tools::jqInclude();

        // check for existance
        $data = Adapter::getOrderData(self::getId(),self::getMode());
        self::$status = $data->getStatus(); //in_proccess

        self::generateExportOrderHtml($data);
        self::loadExportCSS();
        self::getOrderExportJs($data);
    }

    protected static function getOrderExportJs(\Ipol\Catapulto\Core\Order\Order $order)
    {
        $pathToWidjet = 'https://widgetcdn.catapulto.ru/assets/js/catapulto-widget/v3/catapulto-widget.js';
        if ((Option::get('use_widget_local') === 'Y') && file_exists($_SERVER['DOCUMENT_ROOT'] . Tools::getJSPath() . 'widjet/catapultowidget.min.js')) $pathToWidjet = Tools::getJSPath() . 'widjet/catapultowidget.min.js';
        $arItems = array();
        $order->getItems()->reset();
        while($obItem = $order->getItems()->getNext()) {
            $arItems []= $obItem->getFields();
        }

        $GLOBALS['APPLICATION']->AddHeadString('<script type="module" src="'.$pathToWidjet.'"></script>');
        $deliveryServices = [];
        if (!empty($order->getField('rateResult')['services']) && is_array($order->getField('rateResult')['services']))
            $deliveryServices = $order->getField('rateResult')['services'];
        $was_cod = false;
        if (!empty($order->getField('rateResult')['was_cod']))
            $was_cod = $order->getField('rateResult')['was_cod'] === true;
        ?>
        <script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
        <script type="text/javascript">
            var <?=self::$MODULE_LBL?>export = new catapulto_delivery_adminInterface({
                'ajaxPath' : '<?=Tools::getJSPath()?>ajax.php',
                'label'    : '<?=self::$MODULE_ID?>',
                'logging'  : true
            });

            <?=self::$MODULE_LBL?>export.expander({
                orderId       : '<?=self::$orderId?>',
                orderNum      : '<?=Order::getOrderNumber(self::$orderId)?>',
                shipmentId    : '<?=self::$shipmentId?>',
                catapultoId   : '<?=$order->getLink()?>',
                //wayBill    : '<?=$order->getField('waybill')?>',
                workMode      : '<?=self::$workMode?>',
                status        : '<?=self::$status?>',
                isSending     : <?=Adapter::statusIsSending(self::$status)?'true':'false'?>,
                price         : '<?=$order->getPayment()->getGoods()->getAmount()?>',
                deliveryPr    : '<?=$order->getPayment()->getDelivery()->getAmount()?>',
                payed         : '<?=$order->getPayment()->getPayed()->getAmount()?>',
                items         : <?=\CUtil::PhpToJSObject($arItems)?>,
                label         : '<?=self::$MODULE_LBL?>',
                sendCost      : <?=(Option::get('noCost') === 'Y') ? 'false' : 'true'?>,
                error         : false,
                ensurance     : '<?=$order->getField('saved_en_ensurance')?>',
                ensurance_cost: <?=$order->getField('insurance_cost') ?? 0?>,
                was_cod       : <?=($was_cod)?'true':'false'?>,
                dlvservices   : <?=\CUtil::PhpToJSObject($deliveryServices) ?>,
                cargoes       : <?=\CUtil::PhpToJSObject($order->getField('cargoData')->toArray()) ?>,
                cargochanged  : false,
                basePrice     : <?=(float)($order->getField('baseDeliveryPrice') ?? 0)?>,
                basePriceWithServices : <?=(float)($order->getField('baseDeliveryPriceWithServices') ?? 0)?>,
                rateWithFitting : <?=$order->getField('isFittingAvailable') ? 'true' : 'false'?>,
                rateWithPartialRed: <?=$order->getField('isPartialRedInRate') ? 'true' : 'false'?>,
                isSingleProductInOrder: <?=$order->getField('isSingleProduct')?'true':'false'?>,
            });
        </script>
        <?php
        $customGabs = $order->getField('custom_gabs');
        ?>
        <?include_once('OrderSenderPages/main.php')?>
        <?include_once('OrderSenderPages/gabs.php')?>
        <?include_once('OrderSenderPages/goodsgabs.php')?>
        <?include_once('OrderSenderPages/cargoes.php')?>
        <?//include_once('OrderSenderPages/goods.php')?>
        <?//include_once('OrderSenderPages/additional.php')?>

        <script type="text/javascript">
            $(document).ready(<?=self::$MODULE_LBL?>export.init);
        </script>
        <?
    }


    public static function generateExportOrderHtml(\Ipol\Catapulto\Core\Order\Order $order)
    {
        $rateResultId = trim($order->getField('rateResultId'));
        $receiverId   = trim($order->getBuyers()->getFirst()->getField('receiverId'));
        $arWarehouses = \Ipol\Catapulto\WarehousesTable::getWarehouses(['=ACTIVE' => 1]);
        $emptyRateResult = false;
        $encoder = new Encoder();

        if(empty($rateResultId)) {
            $emptyRateResult = true;
        }
        $warnMessage = '';
        ?>
        <div id="<?=self::$MODULE_LBL?>PLACEFORFORM">

            <table id="<?=self::$MODULE_LBL?>wndOrder">
                <tbody><tr><td><?=Tools::getMessage('LBL_STATUS')?></td><td><?=Tools::getMessage('STATUS_'.$order->getStatus())?></td></tr>
                <tr><td colspan="2"><small><?=Tools::getMessage('STATUS_'.$order->getStatus().'_DESCR')?><?=($order->getField('ozonStatus'))?Tools::getMessage('STATUS_'.$order->getField('ozonStatus').'_DESCR'):''?></small></td></tr>
                <?if($order->getField('message')){?><tr><td colspan="2" class="<?=self::$MODULE_LBL?>warning"><?=$order->getField('message')?></td></tr><?}?>
                <?if($order->getLink()){?>
                    <tr><td><?=Tools::getMessage('LBL_CATAPULTO_NUMBER')?></td><td><?=$order->getField('catapultoNumber')?></td></tr>
                <?}?>
                <?Tools::placeSOHeaderRow('COMMONDATA')?>
                <?Tools::placeSORow('number','hidden',$order->getNumber());?>
                <?$sd = $order->getField('senderCreateDate');
                Tools::placeSORow('senderCreateDate','sign',$sd['sign']);?>
                <tr><td>
                    <input type="hidden" value="<?=$sd['timestamp']?>" id="<?=self::$MODULE_LBL?>senderCreateDate">
                </td></tr>
                <?php if (!empty($order->getField('tracking_link'))) { ?>
                    <tr>
                        <td><?=Tools::getMessage('LBL_trackingLink')?></td>
                        <td>
                            <a id="<?=self::$MODULE_LBL?>trackingLink" href="<?php echo $order->getField('tracking_link');?>" target="_blank"><?php echo $order->getField('tracking_link');?></a>
                        </td>
                    </tr>
                <?php }?>
                <?if($emptyRateResult) {?>
                <tr>
                    <td><?=Tools::getMessage('LBL_receiverLocation')?></td>
                    <td>
                        <span class="<?=self::$MODULE_LBL?>warning" id="<?=self::$MODULE_LBL?>pvzPickerPickerError"><?=Tools::getMessage('ERROR_NOPVZ')?></span>
                    </td>
                </tr>
                <?} else {?>
                <tr>
                    <td colspan="2">
                        <span><?=Tools::getMessage('LBL_choise_delivery_variant')?></span>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan="2" style="text-align:center">
                        <a id="<?=self::$MODULE_LBL?>pvzPickerPicker" href="javascript:void(0)" onclick="<?=self::$MODULE_LBL?>export.getPage('main').act.selectNewDeliveryType()"><?=Tools::getMessage('SIGN_CHOOSE_DELIVERY_TYPE')?></a>
                    </td>
                </tr>


                <?php if (!$order->getField('cargoDataValid')) { ?>
                    <tr>
                        <td colspan="2" style="padding-top: 20px;">
                            <span class='CATAPULTO_DELIVERY_warning'><?=Tools::getMessage('LBL_cargo_invalid')?></span>
                            <hr/>
                            <input id="CATAPULTO_DELIVERY_delcargoes" type="button" onclick="<?=self::$MODULE_LBL?>export.getPage('main').act.clearCustomCargoData()" value="<?=Tools::getMessage('BTN_CARGODEL')?>">
                        </td>
                    </tr>
                <?php } ?>

                <?//GABARITES?>
                <?Tools::placeSOHeaderRow('GABARITES',self::$MODULE_LBL."export.getPage('main').ui.toggleBlock('gabariles')");?>
                <tr class="<?=self::$MODULE_LBL?>block_gabariles">
                    <td><?=Tools::getMessage('LBL_dimensions')?></td>
                    <td>
                        <div id="<?=self::$MODULE_LBL?>gabsPlace">
                            <span id="<?=self::$MODULE_LBL?>gabsLabel"> <?=$order->getGoods()->getLength()?> X <?=$order->getGoods()->getWidth()?> X <?=$order->getGoods()->getHeight()?></span>
                            <!--<a href='javascript:void(0)' onclick="<?=self::$MODULE_LBL?>export.getPage('gabs').edit('gabs')"><?=Tools::getMessage('BTN_EDIT')?></a>-->
                        </div>
                        <div id="<?=self::$MODULE_LBL?>gabsEditor">
                            <input type="text" name="<?=self::$MODULE_LBL?>length_edit" id="<?=self::$MODULE_LBL?>length_edit" class="<?=self::$MODULE_LBL?>gabsEdit" value=""> X
                            <input type="text" name="<?=self::$MODULE_LBL?>width_edit"  id="<?=self::$MODULE_LBL?>width_edit"  class="<?=self::$MODULE_LBL?>gabsEdit" value=""> X
                            <input type="text" name="<?=self::$MODULE_LBL?>height_edit" id="<?=self::$MODULE_LBL?>height_edit" class="<?=self::$MODULE_LBL?>gabsEdit" value="">
                            <!--<a href="javascript:void(0)" onclick="<?=self::$MODULE_LBL?>export.getPage('gabs').apply()">OK</a>-->
                        </div>

                        <input type="hidden" name="<?=self::$MODULE_LBL?>length" id="<?=self::$MODULE_LBL?>length" value="<?=$order->getGoods()->getLength()?>">
                        <input type="hidden" name="<?=self::$MODULE_LBL?>width"  id="<?=self::$MODULE_LBL?>width" value="<?=$order->getGoods()->getWidth()?>">
                        <input type="hidden" name="<?=self::$MODULE_LBL?>height" id="<?=self::$MODULE_LBL?>height" value="<?=$order->getGoods()->getHeight()?>">
                    </td>
                </tr>
                <tr class="<?=self::$MODULE_LBL?>block_gabariles">
                    <td><?=Tools::getMessage('LBL_weight')?></td>
                    <td>
                        <div id="<?=self::$MODULE_LBL?>weightPlace">
                            <span id="<?=self::$MODULE_LBL?>weightLabel"><?=$order->getGoods()->getWeight()?></span>
                            <!--<a href='javascript:void(0)' onclick="<?=self::$MODULE_LBL?>export.getPage('gabs').edit('weight')"><?=Tools::getMessage('BTN_EDIT')?></a>-->
                        </div>
                        <div id="<?=self::$MODULE_LBL?>weightEditor">
                            <input type="text" name="<?=self::$MODULE_LBL?>weight_edit" id="<?=self::$MODULE_LBL?>weight_edit" class="<?=self::$MODULE_LBL?>gabsEdit" value="">
                            <!--<a href="javascript:void(0)" onclick="<?=self::$MODULE_LBL?>export.getPage('gabs').apply()">OK</a>-->
                        </div>
                        <input type="hidden" name="<?=self::$MODULE_LBL?>weight" id="<?=self::$MODULE_LBL?>weight" value="<?=$order->getGoods()->getWeight()?>">
                    </td>
                </tr>
                <!--<tr class="<?=self::$MODULE_LBL?>block_gabariles">
                    <td></td>
                    <td><a href="javascript:void(0)" onclick="<?=self::$MODULE_LBL?>export.getPage('goodsgabs').open()"><?=Tools::getMessage('LBL_EDIT_GABS')?></a></td>
                </tr>-->

                <?// SENDER ?>
                <?
                $sender           = $order->getSender();
                $currentWarehouse = \Ipol\Catapulto\WarehousesTable::getWarehouses(['=ID' => $sender->getField('warehouseId')]);
                $warehouseCustom  = $order->getField('rateResult')['warehouse']['CUSTOM'] === true;
                if($currentWarehouse) {
                    $currentWarehouse = array_shift($currentWarehouse);
                }
                ?>
                <?$addressFrom = $order->getAddressFrom();?>
                <?Tools::placeSOHeaderRow('SENDER');?>

                <? // TODO добавить валидацию  senderId, ReceiverId, rateResultId ?>
                <?Tools::placeSORow('senderId','hidden',$sender->getField('senderId'));?>
                <?Tools::placeSORow('senderCity','sign',$sender->getField('senderCity'));?>
                <?Tools::placeSORow('warehouseCustom','checkbox',($order->getField('warehouseCustom')),false,
                    'onchange="'.self::$MODULE_LBL.'export.getPage(\'main\').events.onWarehouseCustom();"'. ($warehouseCustom ? " checked" : ""));?>
                <?Tools::placeSORow('warehouseId','select', $currentWarehouse['ID'], array_column($arWarehouses, 'TITLE', 'ID'), 'disabled' . ' onchange="'.self::$MODULE_LBL.'export.getPage(\'main\').events.onWarehouseChange();"');?>
                
                <?// RECEIVER?>
                <?$buyer = $order->getBuyers()->getFirst();?>
                <?$addressTo = $order->getAddressTo();?>
                <?Tools::placeSOHeaderRow('RECEIVER');?>

                <?if ($buyer->getField('receiverId')):?>
                    <?Tools::placeSORow('receiverId','hidden',$buyer->getField('receiverId'));?>
                <?endif;?>
                <?Tools::placeSORow('buyerName','text',$buyer->getFullName());?>
                <?Tools::placeSORow('buyerPhone','text',$buyer->getPhone());?>
                <?Tools::placeSORow('buyerCompany','text',$buyer->getField('company'));?>
                <?Tools::placeSORow('buyerZip','text',$addressTo->getZip());?>
                <?Tools::placeSORow('buyerCity','text',$addressTo->getCity());?>
                <?Tools::placeSORow('buyerStreet','text',$addressTo->getStreet());?>
                <?Tools::placeSORow('buyerBuilding','text',$addressTo->getBuilding());?>
                <?Tools::placeSORow('buyerDoorNumber','text',$addressTo->getFlat());?>
                <?if ($addressTo->getField('isDadata') === true):?>
                    <tr><td colspan="2" style="color: gray;"><small><?=Tools::getMessage('LBL_isDadata')?></small></td></tr>
                <?endif;?>
                <?if ($addressTo->getField('isDadataRewritten') === true):?>
                    <tr><td colspan="2" style="color: gray;"><small><?=Tools::getMessage('LBL_isDadataRewritten')?></small></td></tr>
                <?endif;?>
                <?Tools::placeSORow('addressLine','hidden', $addressTo->getLine());?>
                <?if ($addressTo->getField('dadata_unrestricted_value')):?>
                <tr>
                    <td>
                        <label><?=Tools::getMessage('LBL_isDadataFullAddr')?></label>
                    </td>
                    <td>
                        <span><?php echo $addressTo->getField('dadata_unrestricted_value');?></span>
                    </td>
                </tr>
                <?endif;?>

                <?// PICKUPS?>
                <tr class="<?=self::$MODULE_LBL?>delivery_pickup">
                    <td><?=Tools::getMessage('LBL_PickupPoint')?></td>
                    <td>
                        <span id="<?=self::$MODULE_LBL?>PickupPointError"></span>
                        <div id="<?=self::$MODULE_LBL?>PickupPointContainer"></div>
                    </td>
                </tr>

                <?// DELIVERY?>
                <?Tools::placeSOHeaderRow('DELIVERY');?>
                <?Tools::placeSORow('rateResultId','hidden',$order->getField('rateResultId'));?>
                <?php
                    $operator = $order->getField('rateResult')['operator'] ?? false;
                    if ($operator == 'cse')
                        $warnMessage = Tools::getMessage('LBL_CSEWarn');
                    Tools::placeSORow('operator','hidden',$operator);?>
                <? if ($currentWarehouse['OPERATORS_SETUP'][$operator]['DELIVERY_FROM'] == 'warehouse' && empty($order->getField('receiver_terminal_code'))):?>
                    <?Tools::placeSORow('sender_terminal_code', 'hidden', $order->getField('sender_terminal_code') ?? false);?>
                <?endif;?>
                <? if (!empty($order->getField('receiver_terminal_code'))):?>
                    <?Tools::placeSORow('sender_terminal_code', 'hidden', $order->getField('sender_terminal_code') ?? false);?>
                    <?Tools::placeSORow('receiver_terminal_code', 'hidden', $order->getField('receiver_terminal_code') ?? false);?>
                <?endif;?>
                <?
                    $pickupDay = $order->getField('rateResult')['pickup_date'] ?? false;
                    if (!$pickupDay) $pickupDay = $order->getField('rateResult')['pickup_day'] ?? false;
                    Tools::placeSORow('pickupDay','hidden',$pickupDay);

                    $deliveryDay = $order->getField('rateResult')['delivery_date'] ?? false;
                    if (!$deliveryDay) $deliveryDay = $order->getField('rateResult')['delivery_day'] ?? false;
                    Tools::placeSORow('deliveryDay','hidden',$deliveryDay);

                    $deliveryTime = $order->getField('rateResult')['delivery_time'] ?? '';
                    if ($deliveryTime) {
                        if(is_array($deliveryTime)) {
                            $deliveryTime = implode(',',$deliveryTime);
                        }
                        if ((LANG_CHARSET=='windows-1251') && $deliveryTime) $deliveryTime = $encoder->encodeFromAPI($deliveryTime);
                    }
                    Tools::placeSORow('deliveryTime','sign', $deliveryTime);
                ?>
                <?Tools::placeSORow('operatorRate','sign',$order->getField('rateResult')['rate'] ?? false);?>
                <?Tools::placeSORow('deliveryType','sign',$order->getField('rateResult')['delivery_type'] ?? false);?>
                <?Tools::placeSORow('comment','textbox', $order->getAddressTo()->getComment());?>
                <?Tools::placeSORow('needInsurance','checkbox',($order->getField('needInsurance')),false,
                    "checked readonly onclick='return false;' onkeydown='return false;' onchange=\"".self::$MODULE_LBL."export.getPage('main').events.onInsuranceChange()\"");?>
                <?
                //$enabled = $order->getField('isFittingInRate') || (Option::get('isFitting') === 'Y');
                $enabled = true;
                Tools::placeSORow('fitting','checkbox',$order->getField('isFittingInRate'),false,
                    "onchange=\"".self::$MODULE_LBL."export.getPage('main').events.onFittingChange()\"" . ($enabled?'':' disabled="disabled"'));

                $isChecked = false;
                $isDisabled = false;
                if (!$order->getField('isSingleProduct') && ($order->getField('isPartialRedInRate') || $order->getField('isFittingInRate'))) $isChecked = true;
                if (($isChecked && $order->getField('isFittingInRate')) || $order->getField('isSingleProduct')) $isDisabled = true;
                Tools::placeSORow('partialRedemption','checkbox',$isChecked,false,
                    "onchange=\"".self::$MODULE_LBL."export.getPage('main').events.onPartialRedemptionChange()\"" . ( $isDisabled ? ' disabled="disabled"' : '' ));?>
                <?Tools::placeSORow('insuranceValue','text',$order->getPayment()->getField('insuranceValue')->getAmount());?>
                <?
                $isSmsFilter = in_array('sms_amount', explode(',', $order->getField('rateResult')['services_filter'] ?? ''), false);
                $isServiceSms = $order->getField('isSmsAmount');
                Tools::placeSORow('smsAmount','checkbox',($isSmsFilter && Adapter::statusIsSending(self::$status)),false,
                    "onchange=\"".self::$MODULE_LBL."export.getPage('main').events.onSmsAmountChange(".($isSmsFilter ? 'true' : 'false').",".($isServiceSms ? 'true' : 'false').")\" data-is-sms-filter=\"".($isSmsFilter ? 'true' : 'false')."\" data-is-service-sms=\"".($isServiceSms ? 'true' : 'false') ."\"");?>
                <?// PAYMENT?>

                <?Tools::placeSOHeaderRow('PAYMENT')?>
                <?Tools::placeSORow('payment_isBeznal','checkbox',($order->getPayment()->getIsBeznal()),false,
                    "onchange=\"".self::$MODULE_LBL."export.getPage('main').events.onDeliveryCostChange()\"");?>
                <?Tools::placeSORow('payment_sum','hidden',$order->getPayment()->getGoods()->getAmount());?>
                <?
                $deliveryPrice = $order->getPayment()->getDelivery()->getAmount();
                $deliveryBasePrice = (float)$order->getField('rateResult')['base_price_with_services'];
                $deliveryBasePrice = $deliveryBasePrice ?: $deliveryPrice;
                $deliveryType = $order->getField('rateResult')['shipping_type'];
                $deliveryMarkup = $order->getField('rateResult')['extraPrice'] ?? [];
                if(in_array($deliveryType, ['d2d', 'w2d'], false)) {
                    $deliveryMarkup = $deliveryMarkup['courier'] ?? [];
                }
                else {
                    $deliveryMarkup = $deliveryMarkup['pvz'] ?? [];
                }
                if(!empty($deliveryMarkup)) {
                    if($deliveryMarkup['type'] == 'percent') {
                        $deliveryMarkup['cost'] = round($deliveryBasePrice * $deliveryMarkup['value'] / 100);
                    }
                    else {
                        $deliveryMarkup['cost'] = $deliveryMarkup['value'];
                    }
                }
                else {
                    $deliveryMarkup['cost'] = 0;
                }
                
                if($order->getPayment()->getField('sumToPay')->getAmount() && !Adapter::statusIsSending(self::$status)) {
                    $deliveryMarkup['cost'] = 0;
                }
                
                Tools::placeSORow('delivery_sum','hidden', ($deliveryPrice));
                Tools::placeSORow('extraPrice','hidden', $deliveryMarkup['cost']);?>
                <?php
                    if (isset($order->getField('rateResult')['price_orig'])) {
                        if ( floatval($order->getField('rateResult')['price']) != floatval($order->getField('rateResult')['price_orig']) )
                            Tools::placeSORow('real_delivery_sum','hidden',floatval($order->getField('rateResult')['price_orig']));
                    }

                ?>
                <?//Tools::placeSORow('payment_prepayment','text',$order->getPayment()->getPayed(),false,"onkeyup=\"".self::$MODULE_LBL."export.getPage('main').events.onPrepaymentChange()\"");?>
                <?Tools::placeSORow('sumToPay','text',($order->getPayment()->getField('sumToPay')->getAmount()));?>
                <?/*<tr><td colspan="2"><hr></td></tr>
                <?Tools::placeSORow('price','hidden',$order->getPayment()->getCost()->getAmount());?>*/?>

                <?
                $isNp             = $order->getPayment()->getIsNp();
                $isNpFilter       = in_array('NP', explode(',', $order->getField('rateResult')['services_filter'] ?? ''), false);
                $deliveryServices = $order->getField('rateResult')['services'] ?? [];
                $isNpInServices   = false;
                $isCodInServices  = false;
                
                foreach ($deliveryServices as $service) {
                    if ($service['name'] == 'cod_amount') {
                        $isCodInServices = true;
                    }
                    elseif($service['name'] == 'pod_amount') {
                        $isNpInServices = true;
                    }
                }
                
                if (Adapter::statusIsSending(self::$status)) {
                    Tools::placeSORow('payment_np','checkbox', ($isNp && $isNpFilter),false,
                    "onchange=\"".self::$MODULE_LBL."export.getPage('main').events.onPaymentNpChange()\" data-is-np-in-services=\"".$isNpInServices."\"");
                    $attributes = 'onchange="'.self::$MODULE_LBL.'export.getPage(\'main\').events.onDeliveryPaySideChange()"'.($order->getPayment()->getIsBeznal()?' readonly="readonly"':'').' data-is-cod-in-services="'.$isCodInServices.'"';
                    if ($order->getPayment()->getDelivery()->getAmount() == 0) {
                        Tools::placeSORow('deliveryPaySideFake','checkbox',false,false,' onchange="'.self::$MODULE_LBL.'export.getPage(\'main\').events.onDeliveryPaySideChangeFake()" readonly="readonly"');
                    } else {
                        Tools::placeSORow('deliveryPaySide','checkbox', $order->getPayment()->getIsCod(),false, $attributes);
                    }
                    if (!empty($warnMessage)) {
                        ?>
                        <tr class="<?=self::$MODULE_LBL?>cseWarn<?=($order->getPayment()->getIsBeznal())?' hide':''?>"><td colspan="2"><p style="color:#f00;"><?=$warnMessage?></p></td></tr>
                        <?
                    }
                } else {?>
                    <input type="checkbox" id="<?php echo self::$MODULE_LBL;?>payment_np" name="<?php echo self::$MODULE_LBL;?>payment_np" style="display:none" value="N" />
                    <input type="checkbox" id="<?php echo self::$MODULE_LBL;?>deliveryPaySide" name="<?php echo self::$MODULE_LBL;?>deliveryPaySide" style="display:none" value="N" />
                <?}
                ?>
                </tbody></table>
        </div>
        <?
    }

    protected static function loadExportCSS(){
        Tools::getCommonCss();
        ?>
        <style>
            #<?=self::$MODULE_LBL?>wndOrder{
                width:100%;
            }
            [class ^= "<?=self::$MODULE_LBL?>block_"] {
                display:none;
            }

            #<?=self::$MODULE_LBL?>documentType{
                max-width: 200px;
            }
            .<?=self::$MODULE_LBL?>unseen{
                display: none !important;
            }

            #<?=self::$MODULE_LBL?>DeliveryModeContainer{
                border-collapse: collapse;
            }

            #<?=self::$MODULE_LBL?>DeliveryModeContainer td{
                padding: 3px;
            }

            /*gabs*/
            .<?=self::$MODULE_LBL?>gabsEdit{
                width: 40px;
            }
            #<?=self::$MODULE_LBL?>gabsEditor,#<?=self::$MODULE_LBL?>weightEditor,#<?=self::$MODULE_LBL?>volumeEditor{
                display: none;
            }

            /* GABS & LOTS*/
            .<?=self::$MODULE_LBL?>cargoHeader,.<?=self::$MODULE_LBL?>lotHeader,.<?=self::$MODULE_LBL?>addHeader{
                background-color: #E0E8EA;
                color: #4B6267;
                font-size: 14px;
                text-align: center !important;
                text-shadow: 0px 1px #FFF;
                padding: 8px 4px 10px !important;
                height: 30px;
            }

            .<?=self::$MODULE_LBL?>cargoHeader td,.<?=self::$MODULE_LBL?>lotHeader td{
                text-align  : center !important;
                font-weight : bold;
            }

            .<?=self::$MODULE_LBL?>cargoHeader td:last-child,.<?=self::$MODULE_LBL?>lotHeader td:last-child{
                width: 40px;
            }

            #<?=self::$MODULE_LBL?>cargoEdit,#<?=self::$MODULE_LBL?>lotEdit{
                width: 100%;
            }

            .<?=self::$MODULE_LBL?>cargoExpand,.<?=self::$MODULE_LBL?>cargoDelete,.<?=self::$MODULE_LBL?>lotExpand,.<?=self::$MODULE_LBL?>lotDelete{
                width  : 15px;
                height : 15px;
                float  : left;
                cursor : pointer;
            }

            .<?=self::$MODULE_LBL?>cargoExpand,.<?=self::$MODULE_LBL?>lotExpand{
                margin: 0px 3px;
                background: url("<?=Tools::getImagePath()?>arrows.png");
            }

            .<?=self::$MODULE_LBL?>cargoExpand.<?=self::$MODULE_LBL?>Expanded,.<?=self::$MODULE_LBL?>lotExpand.<?=self::$MODULE_LBL?>Expanded{
                background-position-y: 15px !important;
                color: red !important;
            }

            .<?=self::$MODULE_LBL?>cargoExpand:hover,.<?=self::$MODULE_LBL?>lotExpand:hover{
                background-position-x: 15px;
            }

            .<?=self::$MODULE_LBL?>cargoDelete,.<?=self::$MODULE_LBL?>lotDelete{
                background: url("<?=Tools::getImagePath()?>closer.png");
                background-position-y: 15px;
            }
            .<?=self::$MODULE_LBL?>cargoDelete:hover,.<?=self::$MODULE_LBL?>lotDelete:hover{
                background-position-y: 0px !important;
            }

            .<?=self::$MODULE_LBL?>cargoItems input[type='text'], .<?=self::$MODULE_LBL?>lotItems input[type='text']{
                width: 80px;
            }

            .<?=self::$MODULE_LBL?>cargoItems, .<?=self::$MODULE_LBL?>lotItems{
                width  : 100%;
                border : 1px solid #E0E8EA;
                text-align: center !important;
            }

            .<?=self::$MODULE_LBL?>cargoParams input[type='text']{
                width: 144px;
            }

            .<?=self::$MODULE_LBL?>cargoParams{
                width  : 100%;
                border : 1px solid #E0E8EA;
                background-color: #EDF2F3;
            }

            .<?=self::$MODULE_LBL?>cargoDimensions{
                width: 30px !important;
            }


            .<?=self::$MODULE_LBL?>cargoItems th{
                background-color: #EDF2F3;
                padding: 2px;
            }

            .<?=self::$MODULE_LBL?>cargoItems td{
                text-align: center;
            }

            .<?=self::$MODULE_LBL?>newGood td{
                border-top: 1px solid #E0E8EA;
            }

            /*cargoMover lotMover*/
            #<?=self::$MODULE_LBL?>cargoMover, #<?=self::$MODULE_LBL?>cargoMover p,#<?=self::$MODULE_LBL?>lotMover, #<?=self::$MODULE_LBL?>lotMover p{
                width: 100px;
                text-align: center !important;
            }
            #<?=self::$MODULE_LBL?>cargoMover input[type='text']{
                width: 30px;
            }

            .<?=self::$MODULE_LBL?>lotItems{
                padding: 0px 5px;
            }

            .<?=self::$MODULE_LBL?>lotItems td{
                text-align: center !important;
            }

            .<?=self::$MODULE_LBL?>lotItemName{
                width: 200px;
            }

            /* ADDITIONAL */
            #<?=self::$MODULE_LBL?>goodsEdit{
                width : 100%;
            }
            #<?=self::$MODULE_LBL?>goodsEdit td{
                text-align : center;
            }
            #<?=self::$MODULE_LBL?>editCntrName{
                max-width: 180px;
            }
            .<?=self::$MODULE_LBL?>CisMarker{
                background-image: url(<?=Tools::getImagePath()?>details.png);
                width  : 13px;
                height : 15px;
                cursor : pointer;
                position: relative;
                top: 20px;
                left: 175px;
            }
            .<?=self::$MODULE_LBL?>QRSelector{
                margin-bottom: 5px;
                background-color: #f5f9f9;
                cursor : pointer;
                padding : 5px;
                word-wrap: break-word;
            }
            .<?=self::$MODULE_LBL?>QRSelector:hover{
                background-color: #E0E8EA;
            }

            #<?=self::$MODULE_LBL?>gabsgoodsEdit {
                width:100%;
            }
            #<?=self::$MODULE_LBL?>gabsgoodsEdit input[type=text] {
                width:100%;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                -o-box-sizing: border-box;
                -ms-box-sizing: border-box;
                box-sizing: border-box;
            }

            .<?=self::$MODULE_LBL?>cseWarn.hide {
                display: none;
            }

            #pop-insuranceValue {
                left: 30px !important;
            }
        </style>
        <?
    }

    // service
    protected static $arButtons;

    protected static function addButton($html)
    {
        if(!isset(self::$arButtons))
        {
            self::$arButtons = array();
        }
        if(count(self::$arButtons) && count(self::$arButtons) % 3 === 0)
        {
            self::$arButtons []= '<br><br>';
        }
        self::$arButtons []= $html;
    }

    protected function getCountryArray(){
        /*$list = Enumerations::getCountryCodes();
        $arReturn = array();
        foreach ($list as $numCode => $letCode){
            $arReturn[$numCode] = Tools::getMessage('CNTRY_'.$letCode);
        }
        return $arReturn;*/
    }
}
