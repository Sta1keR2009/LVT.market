<?php
namespace Ipol\Catapulto\Admin;

use Ipol\Catapulto\AbstractGeneral;
use Ipol\Catapulto\Bitrix\Adapter;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Core\Order\Order;
use Ipol\Catapulto\WidgetHandler;

/** @var Order $order  */
?>

<script type="text/javascript">
    <?=self::$MODULE_LBL?>export.addPage('main', {
        city : '<?=($order->getAddressTo()) ? $order->getAddressTo()->getCity() : false?>',
        address : '<?=($order->getAddressTo()) ? trim(implode(',', [$order->getAddressTo()->getCity(), $order->getAddressTo()->getAddress()])) : false?>',
        pvz  : '<?=$order->getField('pickupPoint')?>',
        moduleLbl: '<?=self::$MODULE_LBL?>',

        init: function () {
            if ($('#<?=self::$MODULE_LBL?>btn').length) return;

            // B24 support
            if ($('#<?=self::$MODULE_LBL?>btn_container').length)
            {
                $('#<?=self::$MODULE_LBL?>btn_container').prepend("<a href='javascript:void(0)' onclick='<?=self::$MODULE_LBL?>export.getPage(\"main\").open()' class='ui-btn ui-btn-light-border ui-btn-icon-edit' style='margin-left:12px;' id='<?=self::$MODULE_LBL?>btn'><?=Tools::getMessage('BTN_EXPORT')?></a>");
            }

            // Standard
            if ($('.adm-detail-toolbar').find('.adm-detail-toolbar-right').length)
            {
                $('.adm-detail-toolbar').find('.adm-detail-toolbar-right').prepend("<a href='javascript:void(0)' onclick='<?=self::$MODULE_LBL?>export.getPage(\"main\").open()' class='adm-btn' id='<?=self::$MODULE_LBL?>btn'><?=Tools::getMessage('BTN_EXPORT')?></a>");
            }


            var btn = $('#<?=self::$MODULE_LBL?>btn');

            switch (this.self.status) {
                case 'NEW'       :
                    break;
                case 'rejected'  :
                    btn.css('color', '#F13939');
                    break;
                default       :
                    btn.css('color', '#3A9640');
                    break;
            }
            var html = $('#<?=self::$MODULE_LBL?>PLACEFORFORM').html();
            $('#<?=self::$MODULE_LBL?>PLACEFORFORM').html(' ');

            if (!html) {
                this.self.log('unable to load data of the order');
            }
            else {
                <?
                $error = false;
                if (!$error && Adapter::statusIsSending(self::$status)) {
                    self::addButton("<input id='" . self::$MODULE_LBL . "sender' type='button' onclick='" . self::$MODULE_LBL . "export.getPage(\"main\").send()' value='" . Tools::getMessage('BTN_SEND') . "'>");
                    self::addButton("<input id='" . self::$MODULE_LBL . "edtcargoes' type='button' onclick='" . self::$MODULE_LBL . "export.getPage(\"cargoes\").open()' value='" . Tools::getMessage('BTN_CARGO') . "'>");
                } else {
                    if (!Adapter::statusIsFinal(self::$status)) {
                        self::addButton("<input id='" . self::$MODULE_LBL . "checkStatus' type='button' onclick='" . self::$MODULE_LBL . "export.getPage(\"main\").act.checkStatus()' value='" . Tools::getMessage('BTN_CHECKSTATE') . "'>");
                    }
                    if (Adapter::statusIsReady(self::$status)) {
                        self::addButton("<input id='" . self::$MODULE_LBL . "getSticker' type='button' onclick='" . self::$MODULE_LBL . "export.getPage(\"main\").act.getSticker()' value='" . Tools::getMessage('BTN_GETSTICKER') . "'>");
                        self::addButton("<input id='" . self::$MODULE_LBL . "getAct' type='button' onclick='" . self::$MODULE_LBL . "export.getPage(\"main\").act.getAct()' value='" . Tools::getMessage('BTN_GETACT') . "'>");
                    }
                    if(Adapter::statusIsCancelable(self::$status)){
                        self::addButton("<input id='" . self::$MODULE_LBL . "cancelOrder' type='button' onclick='" . self::$MODULE_LBL . "export.getPage(\"main\").act.cancelOrder()' value='" . Tools::getMessage('BTN_CANCELORDER') . "'>");
                    }
                }
                //self::addButton("<input id='" . self::$MODULE_LBL . "editGoods' type='button' onclick='" . self::$MODULE_LBL . "export.getPage(\"goods\").open()' value='" . Tools::getMessage('BTN_GOODSEDIT') . "'>");
                //self::addButton("<input id='" . self::$MODULE_LBL . "editLots'   type='button' onclick='" . self::$MODULE_LBL . "export.getPage(\"lots\").open()' value='" . Tools::getMessage('BTN_LOTSEDIT') . "'>");

                ?>
                this.mainWnd = new catapulto_delivery_wndController({
                    title: '<?=Tools::getMessage('HDR_EXPORT')?>',
                    content: html,
                    resizable: true,
                    draggable: true,
                    height: '600',
                    width: '565',
                    buttons: <?=\CUtil::PhpToJSObject(self::$arButtons)?>
                });
                <?if($error){?>this.self.error='<?=$error?>';<?}?>
            }

            this.act(this);
            this.events(this);
            this.functions(this);
            this.onSend(this);
            this.onCalculate(this);
            this.widget(this);
            this.events.onInsuranceChange();
            this.events.onBuyerTypeChange();
            this.events.onWarehouseCustom();
            this.calculate();

            // CATAPULTO_DELIVERY_fitting
            if (this.self.isSingleProductInOrder)
                $('#'+this.moduleLbl+'partialRedemption').attr('disabled', 'disabled').attr('checked', false);

        },

        // wnd
        mainWnd : false,
        loaded  : false,

        open: function () {
            if (document.getElementById('pop-insuranceValue') !== null)
                document.getElementById('pop-insuranceValue').style.display = '';
            if (this.mainWnd)
                this.mainWnd.open();

            if(this.self.error){
                alert(this.self.error);
            }
        },

        // calculating cost delivery for additional services
        calculate: function () {
            const t = this
                ,s = t.self
                ,productsPrice = parseFloat(s.price)
                ,baseDLVPrice = parseFloat(s.basePrice)
                ,deliveryPr = parseFloat(s.deliveryPr)
                ,baseDLVPriceWithServices = parseFloat(s.basePriceWithServices)
                ,isBeznal = $('#'+t.moduleLbl+'payment_isBeznal')
                ,isNp = $('#'+t.moduleLbl+'payment_np')
                ,dlvPaySide = $('#'+t.moduleLbl+'deliveryPaySide')
                ,cseWarn = $('.'+t.moduleLbl+'cseWarn')
                ,sumToPay = $('#'+t.moduleLbl+'sumToPay')
                ,insurance = $('#'+t.moduleLbl+'needInsurance')
                ,priceLabel = $('#'+t.moduleLbl+'hidLabel_delivery_sum')
                ,extraPriceLabel = $('#'+t.moduleLbl+'hidLabel_extraPrice')
                ,extraPriceLabelText = $(`label[for=${t.moduleLbl}extraPrice]`)
                ,insuranceVal = t.self.ensurance_cost / 100
            ;
            
            let extraPrice = $('#'+t.moduleLbl+'extraPrice')
                ,extraPriceVal = parseFloat(extraPrice.val())
            
            let dlvSum = baseDLVPrice;

            sumToPay.attr('readonly', 'readonly');
            if(!extraPrice.data('default')) {
                extraPrice.data('default', extraPriceVal);
            }
            else {
                extraPriceVal = extraPrice.data('default');
            }
            
            let disableExtraPrice = (deliveryPr <= 0);
            
            extraPriceLabel.css({'color': 'inherit'}).text(extraPrice.data('default'));
            extraPriceLabelText.css({'color': 'inherit'});
            extraPrice.val(extraPrice.data('default'));
            
            if(extraPrice.data('default') === 0) disableExtraPrice = true;

            //calc delivery cost..
            if (s.isSending) priceLabel.html(dlvSum);
            if (baseDLVPrice > 0) {
                if (insurance.prop('checked')) dlvSum += Math.ceil(productsPrice * insuranceVal);
                //Additinal services
                dlvSum += t.calcAdditionalServiceCost();
                if (s.isSending) priceLabel.html(dlvSum);
            }

            if (isBeznal.prop('checked')) {
                sumToPay.val(0);
                priceLabel.html(baseDLVPrice);
                //dlvPaySide.prop('checked',false).attr('readonly','readonly');
                cseWarn.addClass('hide');
            } else {
                let fullSum = productsPrice;
                if(!isNp.prop('checked')) {
                    fullSum = 0;
                }
                else {
                    disableExtraPrice = true;
                }
                
                sumToPay.removeAttr('readonly');
                if (s.isSending) sumToPay.val(fullSum);

                if (baseDLVPrice > 0) {
                    if (dlvPaySide.prop('checked')) {
                        fullSum += baseDLVPrice;
                        if (insurance.prop('checked')) fullSum += Math.ceil(productsPrice * insuranceVal);
                        fullSum += t.calcAdditionalServiceCost();
                        disableExtraPrice = true;
                    }

                    if (s.isSending) sumToPay.val(fullSum);

                    dlvPaySide.removeAttr('readonly');
                    cseWarn.removeClass('hide');
                }
            }

            if(disableExtraPrice) {
                extraPriceLabel.css({'color': 'red'}).text('<?=Tools::getMessage('LBL_extraPriceOff')?>');
                extraPriceLabelText.css({'color': 'red'});
                extraPrice.val(0);
            }
        },

        calcAdditionalServiceCost: function() {
            const t = this
                ,s = t.self
                ,dlvPaySide = $('#'+t.moduleLbl+'deliveryPaySide')
                ,fitting = $('#'+t.moduleLbl+'fitting')
                ,pr = $('#'+t.moduleLbl+'partialRedemption')
                ,sms = $('#'+t.moduleLbl+'smsAmount')
            ;
            let i, adtCost, cost = 0;

            for (i in s.dlvservices) {
                switch (s.dlvservices[i]['name']) {
                    case '<?=AbstractGeneral::CTPT_SERVICE_COD?>':
                        if (dlvPaySide.prop('checked')) {
                            adtCost = Number(s.dlvservices[i]['cost']);
                            if (isNaN(adtCost)) adtCost = 0;
                            cost += adtCost;
                        }
                        break;
                    case '<?=AbstractGeneral::CTPT_SERVICE_FITTING?>':
                        if (fitting.prop('checked')) {
                            adtCost = Number(s.dlvservices[i]['cost']);
                            if (isNaN(adtCost)) adtCost = 0;
                            cost += adtCost;
                        }
                        break;
                    case '<?=AbstractGeneral::CTPT_SERVICE_PR?>':
                        if (pr.prop('checked')) {
                            adtCost = Number(s.dlvservices[i]['cost']);
                            if (isNaN(adtCost)) adtCost = 0;
                            cost += adtCost;
                        }
                        break;
                    case '<?=AbstractGeneral::CTPT_SERVICE_SMS?>':
                        if (sms.prop('checked')) {
                            adtCost = Number(s.dlvservices[i]['cost']);
                            if (isNaN(adtCost)) adtCost = 0;
                            cost += adtCost;
                        }
                        break;
                }
            }
            return cost;
        },

        onCalculate: (function (self) {
        }),


// sending
        send: function () {
            //Проверка на изменение состава или характеристик грузомест
            if (this.self.cargochanged) {
                alert('<?=Tools::getMessage('HDR_GDS_NEEDRESELECTRATE')?>');
                return;
            }
            //Возможность тарифа отправить с услугой примерки
            if (!this.self.rateWithFitting && $('#<?=self::$MODULE_LBL?>fitting').prop('checked')) {
                alert('<?=Tools::getMessage('HDR_FITTING_NEEDRESELECTRATE')?>');
                return;
            }
            
            //Нужно выбрать хотя бы 1 параметр из раздела Оплата
            let isBeznal = $('#<?=self::$MODULE_LBL?>payment_isBeznal').prop('checked'),
                isNp = $('#<?=self::$MODULE_LBL?>payment_np').prop('checked'),
                isCod = $('#<?=self::$MODULE_LBL?>deliveryPaySide').prop('checked');
            
            if(!isBeznal && !isNp && !isCod) {
                alert('<?=Tools::getMessage('HDR_PAYMENT_SETTINGS')?>');
                return;
            }
                

            $('#<?=self::$MODULE_LBL?>sender').css('display', 'none');
            $('.<?=self::$MODULE_LBL?>errInput').removeClass('<?=self::$MODULE_LBL?>errInput');
            var data = this.getInputs();

            if (data.success) {
                this.self.ajax({
                    data: this.self.concatObj(data.inputs, {
                        <?=self::$MODULE_LBL?>action: 'sendOrder',
                        orderId    : this.self.orderId,
                        shipmentId : this.self.shipmentId,
                        workMode   : this.self.workMode
                    }),
                    dataType: 'json',
                    success: this.onSend
                });
            } else {
                //Для некорректного номера телефона для СМС-оповещения отдельный alert
                //А также для услуги СМС-оповещения
                let firstAlert = '';
                let alertStr = "<?=Tools::getMessage('MESS_NOTSENDED')?>\n";
                let moreErrors = false;
                for (var i in data.errors) {
                    if (data.errors[i] === 'wrongBuyerPhoneForSmsAmount' || data.errors[i] === 'isServiceSms') {
                        if(data.errors[i] === 'wrongBuyerPhoneForSmsAmount') {
                            firstAlert += '<?=Tools::getMessage('WRONG_PHONE_FOR_SMS')?>' + "\n";
                        }
                        else if(data.errors[i] === 'isServiceSms') {
                            firstAlert += '<?=Tools::getMessage('SMS_SERVICE_NOT_AVAILABLE')?>' + "\n";
                        }
                    }
                    else if(data.errors[i] === 'deliveryPaySide' || data.errors[i] === 'payment_np') {
                        firstAlert = '<?=Tools::getMessage('NP_SERVICE_NOT_AVAILABLE')?>' + "\n";
                    }
                    else {
                        moreErrors = true;
                    }
                }
                
                if(firstAlert) {
                    alertStr += firstAlert + "\n";
                }
                
                var headerDiff = {};
                
                if(moreErrors) {
                    alertStr += "<?=Tools::getMessage('MESS_FILL')?>";
                }

                for (var i in data.errors) {
                    
                    var handler = $('#<?=self::$MODULE_LBL?>' + i);
                    handler.addClass('<?=self::$MODULE_LBL?>errInput');

                    if(
                        data.errors[i] === 'wrongBuyerPhoneForSmsAmount' ||
                        data.errors[i] === 'isServiceSms' ||
                        data.errors[i] === 'deliveryPaySide' ||
                        data.errors[i] === 'payment_np') {
                        continue;
                    }
                    
                    handler = handler.parent().parent();

                    var label = (handler.children(':first-child').find('label').length) ? handler.children(':first-child').find('label').text().trim() : handler.children(':first-child').text().trim();
                    var header = false;
                    var iter = 0;

                    while (!header && iter < 30) {
                        if (handler.prev('.heading').length)
                            header = handler.prev('.heading').text().trim();
                        else
                            handler = handler.prev();
                        iter++;
                    }
                    if (typeof(headerDiff[header]) === 'undefined')
                        headerDiff[header] = {};
                    headerDiff[header][label] = label;
                }
                for (var i in headerDiff) {
                    alertStr += "\n" + i + ": ";
                    for (var j in headerDiff[i]) {
                        alertStr += j + ", ";
                    }
                    alertStr = alertStr.substring(0, alertStr.length - 2);
                }
                alert(alertStr);
                $('#<?=self::$MODULE_LBL?>sender').css('display', '');
            }
        },

        getInputs: function (giveAnyway) {
            var depths = this.dependences();

            var data = {
                inputs: {},
                errors: {}
            };

            for (var i in depths) {
                if (typeof(depths[i].need) !== 'undefined') {
                    var preVal = $('#<?=self::$MODULE_LBL?>' + i).val();
                    if ($('#<?=self::$MODULE_LBL?>' + i).attr('type') === 'checkbox')
                        preVal = ($('#<?=self::$MODULE_LBL?>' + i).prop('checked')) ? true : false;
                    if (typeof(depths[i].link) !== 'undefined') {
                        var checkVal = $('#<?=self::$MODULE_LBL?>' + depths[i].link).val();
                        if ($('#<?=self::$MODULE_LBL?>' + depths[i].link).attr('type') === 'checkbox')
                            checkVal = ($('#<?=self::$MODULE_LBL?>' + i).prop('checked')) ? true : false;
                    }
                    switch (depths[i].need) {
                        case 'dep' :
                            if (preVal)
                                data.inputs[i] = preVal;
                            else if (!checkVal)
                                data.errors[i] = i;
                            break;
                        case 'sub' :
                            var need = (typeof(depths[i].checkVal) !== 'undefined') ?  (checkVal===depths[i].checkVal) : checkVal;
                            if (need) {
                                if (preVal)
                                    data.inputs[i] = preVal;
                                else
                                    data.errors[i] = i;
                            }
                            break;
                        case true :
                            if (preVal)
                                data.inputs[i] = preVal;
                            else
                                data.errors[i] = i;
                            break;
                        case false :
                            if (preVal)
                                data.inputs[i] = preVal;
                            break;
                    }
                }
            }

            // if(this.self.isEmpty(this.self.getPage('goods').info)){
            //     data.inputs.items = this.self.getPage('goods').autoFill();
            // } else {
            //     data.inputs.items = this.self.getPage('goods').info;
            // }

            var pregPhone = /^(\+7|7){1}[0-9]{10}$/;
            
            if(data.inputs.smsAmount) {
                pregPhone = /^(\+7|7){1}[9]{1}[0-9]{9}$/;
                if(!pregPhone.test(data.inputs.buyerPhone)) {
                    data.errors['buyerPhone'] = 'wrongBuyerPhoneForSmsAmount';
                }
                
                //Проверка наличия в тарифе услуги СМС-информирования
                let smsAmount = $('#<?=self::$MODULE_LBL?>smsAmount'),
                    isServiceSms = smsAmount.data('isServiceSms'),
                    isSmsFilter = smsAmount.data('isSmsFilter');
                
                if(!isServiceSms) {
                    data.errors['isServiceSms'] = 'isServiceSms';
                }
                
            }
            else if(data.inputs.buyerPhone && !pregPhone.test(data.inputs.buyerPhone)){
                data.errors['buyerPhone'] = 'buyerPhone';
            }
            
            if(data.inputs.deliveryPaySide) {
                let d = $('#<?=self::$MODULE_LBL?>deliveryPaySide').data();
                if(!d['isCodInServices']) {
                    data.errors['deliveryPaySide'] = 'deliveryPaySide';
                }
            }

            if(data.inputs.payment_np) {
                let d = $('#<?=self::$MODULE_LBL?>payment_np').data();
                if(!d['isNpInServices']) {
                    data.errors['payment_np'] = 'payment_np';
                }
            }
            
            if (this.self.isEmpty(data.errors) || (typeof(giveAnyway) !== 'undefined' && giveAnyway))
                return {success: true, inputs: data.inputs};
            else
                return {success: false, errors: data.errors};
        },

        onSend: (function (self) {
            self.onSend = function (data) {
                if (data.success) {
                    alert("<?=Tools::getMessage('MESS_SENDED')?>");
                    self.mainWnd.close();
                    window.location.reload();
                }
                else {
                    var str = '<?=Tools::getMessage('MESS_NOTSENDED')?>';
                    if (typeof(data.error) !== 'undefined') {
                        try {
                            data.error = JSON.parse(data.error);
                            console.log(data.error);
                        } catch (e) {
                            console.log(e);
                        }

                        if (!$.isEmptyObject(data.error) && $.isPlainObject(data.error)) {
                            for(let i in data.error) {
                                str += "\n" + data.error[i];
                            }
                        }
                        else {
                            str += "\n" + data.error;
                        }
                    }

                    $('#<?=self::$MODULE_LBL?>sender').css('display', '');

                    alert(str);
                }
            };
        }),

        dependences: function () {
            var reqs = {
                number   : {need: true},

                height: {need: true},
                length: {need: true},
                width: {need: true},
                weight: {need: true},

                senderId: {need: true},
                warehouseId: {need: false},
                warehouseCustom: {need: false},

                receiverId: {need: false},
                buyerName: {need: true},
                buyerPhone: {need: true},
                buyerCompany: {need: false},
                buyerZip: {need: true},
                buyerCity: {need: true},
                buyerStreet: {need: true},
                buyerBuilding: {need: true},
                buyerDoorNumber: {need: false},
                addressLine: {need: false},

                pickupDay: {need: false},
                deliveryDay: {need: false},

                rateResultId: {need: true},
                sender_terminal_code: {need: false},
                receiver_terminal_code: {need: false},
                operator: {need: true},
                comment: {need: false},
                needInsurance: {need: false},
                insuranceValue: {need: false},
                smsAmount: {need: false},

                fitting: {need: false},
                partialRedemption: {need: false},

                //price: {need: true}, // ОС
                deliveryCost: {need: false},
                payment_sum: {need: false},
                delivery_sum: {need: false},
                extraPrice: {need: false},
//                payment_prepayment: {need: false},
                payment_isBeznal: {need: false},
                sumToPay: {need: false},
                deliveryPaySide: {need: false},
                payment_np: {need: false},
            };

            return reqs;
        },

// actions
        act: (function (self) {
            self.act = {
                selectNewDeliveryType : function () {
                    if(self.widget.ready){
                        self.widget.open();
                    } else {
                        self.widget.load();
                    }
                },

                checkStatus: function () {
                    $('#<?=self::$MODULE_LBL?>checkStatus').css('display', 'none');
                    self.self.ajax({
                        data: {
                            <?=self::$MODULE_LBL?>action: 'checkStatusByBitrixIAjax',
                            bitrixId: self.self.orderId
                        },
                        success: function (data) {
                            window.location.reload();
                        }
                    });
                },
                cancelOrder : function(){
                    if(confirm('<?=Tools::getMessage('MESS_DOCANCEL')?>')){
                        $('#<?=self::$MODULE_LBL?>cancelOrder').css('display', 'none');
                        self.self.ajax({
                            data: {
                                <?=self::$MODULE_LBL?>action: 'cancelOrderByBXIdAjax',
                                bitrixId: self.self.orderId
                            },
                            dataType: 'json',
                            success: function (data) {
                                if (typeof(data.r) === 'undefined') {
                                    alert('Unknown error');
                                    return false;
                                }
                                if (data.r) {
                                    alert('<?=Tools::getMessage('MESS_CANCELED')?>');
                                    window.location.reload();
                                } else {
                                    alert("<?=Tools::getMessage('MESS_NOTCANCELED')?>\n"+data.mes);
                                    $('#<?=self::$MODULE_LBL?>cancelOrder').css('display', '');
                                }
                            }
                        });
                    }

                },
                getSticker: function () {
                    $('#<?=self::$MODULE_LBL?>getSticker').css('display', 'none');
                    self.self.ajax({
                        data: {
                            <?=self::$MODULE_LBL?>action: 'getStickerAjax',
                            bitrixId: self.self.orderId
                        },
                        dataType: 'json',
                        success: function (data) {
                            if (data.success) {
                                window.open(data.file);
                            } else {
                                alert('<?=Tools::getMessage("MESS_STICKER_ERROR")?> '+data.error);
                            }
                            $('#<?=self::$MODULE_LBL?>getSticker').css('display', '');
                        }
                    });
                },
                getAct: function () {
                    $('#<?=self::$MODULE_LBL?>getAct').css('display', 'none');
                    self.self.ajax({
                        data: {
                            <?=self::$MODULE_LBL?>action: 'getActsAjaxBid',
                            bitrixId: self.self.orderId
                        },
                        dataType: 'json',
                        success: function (data) {
                            if (data.success) {
                                window.open(data.file);
                            } else {
                                alert('<?=Tools::getMessage("MESS_STICKER_ERROR")?> '+data.error);
                            }
                            $('#<?=self::$MODULE_LBL?>getSticker').css('display', '');
                        }
                    });
                },
                saveCustomCargoData: function(cargoData, ischanged = false) {
                    self.self.ajax({
                        data: {
                            <?=self::$MODULE_LBL?>action: 'saveCustomCargoData',
                            bitrixId: self.self.orderId,
                            cargo: cargoData,
                            changed: ischanged,
                        },
                        dataType: 'json',
                        success: function (data) {
                            if (data.success) {
                                //window.open(data.file);
                            } else {
                                alert('<?=Tools::getMessage("MESS_STICKER_ERROR")?> '+data.error);
                            }
                            $('#<?=self::$MODULE_LBL?>getSticker').css('display', '');
                        }
                    });
                },
                clearCustomCargoData: function() {
                    self.self.ajax({
                        data: {
                            <?=self::$MODULE_LBL?>action: 'clearCustomCargoData',
                            bitrixId: self.self.orderId,
                        },
                        dataType: 'json',
                        success: function (data) {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert('<?=Tools::getMessage("MESS_STICKER_ERROR")?> '+data.error);
                            }
                        }
                    });
                },
            }
        }),

// events, lol
        events: (function (self) {
            self.events = {
                onBuyerTypeChange : function(stuff){
                    var val = $('#<?=self::$MODULE_LBL?>buyerType').val();
                    if(val === 'LegalPerson'){
                        $('.<?=self::$MODULE_LBL?>LegalPerson').css('display','');
                    } else {
                        $('.<?=self::$MODULE_LBL?>LegalPerson').css('display','none');
                    }
                },
                onDeliveryCostChange : function () {
                    let payNP = $('#<?php echo self::$MODULE_LBL;?>payment_np'),
                        payBeznal = $('#<?php echo self::$MODULE_LBL;?>payment_isBeznal'),
                        paySide = $('#<?php echo self::$MODULE_LBL;?>deliveryPaySide');
                    
                    if (!payBeznal.prop('checked')) {
                        paySide.removeAttr('disabled').prop('checked', true);
                        payNP.removeAttr('disabled').prop('checked', true);
                        payBeznal.prop('disabled', true);
                    }
                    else {
                        paySide.prop('disabled', true).prop('checked', false);
                        payNP.prop('disabled', true).prop('checked', false);
                    }

                    self.calculate();
                },
                onPaymentNpChange : function () {
                    self.functions.changePaymentIsBeznal();
                    self.calculate();
                },
                onInsuranceChange : function () {
                    var insuranceValue = $('#<?php echo self::$MODULE_LBL;?>insuranceValue');

                    if ($('#<?php echo self::$MODULE_LBL;?>needInsurance').prop('checked')) {
                        insuranceValue.removeAttr('readonly');
                    } else {
                        insuranceValue.attr('readonly', 'readonly');
                    }
                    self.calculate();
                },
                onSmsAmountChange : function (isSmsFilter, isServiceSms) {
                    self.calculate();
                },
                onWarehouseCustom : function () {
                    let warehouseSelect = $('#<?php echo self::$MODULE_LBL;?>warehouseId');

                    if ($('#<?php echo self::$MODULE_LBL;?>warehouseCustom').prop('checked')) {
                        warehouseSelect.removeAttr('disabled');
                    } else {
                        warehouseSelect.attr('disabled', true);
                    }
                },
                onWarehouseChange : function () {
                    if(confirm(`<?=Tools::getMessage('BTN_CONFIRM_CHANGE_WH')?>`)) {
                        self.act.selectNewDeliveryType();
                    }
                },
                onFittingChange: function() {
                    const fitting = $('#<?php echo self::$MODULE_LBL;?>fitting')
                        ,rd = $('#<?php echo self::$MODULE_LBL;?>partialRedemption')
                    ;

                    if (fitting.prop('checked')) {
                        rd.prop('checked', true).prop('disabled','disabled');
                    } else {
                        rd.prop('disabled', false);
                    }
                    if (self.self.isSingleProductInOrder) {
                        rd.prop('checked', false).prop('disabled','disabled');
                    }

                    self.calculate();
                },
                onPartialRedemptionChange: function() {
                    self.calculate();
                },
                onDeliveryPaySideChange: function () {
                    let t=$('#<?php echo self::$MODULE_LBL;?>deliveryPaySide');
                    if (t.prop('checked') && t.prop('disabled')) {
                        t.prop('checked',false);
                    }
                    self.functions.changePaymentIsBeznal();
                    self.calculate();
                },
                onDeliveryPaySideChangeFake: function () {
                    const t=$('#<?php echo self::$MODULE_LBL;?>deliveryPaySideFake');
                    if (t.prop('checked')) t.prop('checked', false);
                },
            }
        }),

        functions: (function(self){
            self.functions = {
                recalsSumToPay: function() {

                },
                changePaymentIsBeznal: function () {
                    let beznal = $('#<?php echo self::$MODULE_LBL;?>payment_isBeznal'),
                        payNP = $('#<?php echo self::$MODULE_LBL;?>payment_np'),
                        paySide = $('#<?php echo self::$MODULE_LBL;?>deliveryPaySide');
                    
                    if(!payNP.prop('checked') && !paySide.prop('checked')) {
                        beznal.prop('checked', true).removeAttr('disabled').trigger('change');
                    }
                    
                }
            }
        }),

        widget: (function(self) {
            self.widget = {
                ready: false,
                orderId: '<?=self::getId()?>',
                controller: false,
                widgetParams: <?=\CUtil::PhpToJSObject(WidgetHandler::getWidgetAdminParams(self::getId(), self::getMode()))?>,

                load: function () {
                    if (!this.controller) {
                        if (!this.prepareWidgetParams()) return;
                        this.controller = new CatapultoWidget2(this.widgetParams);
                        <?=self::$MODULE_LBL?>export.getPage('main').widget.ready = true;
                    } else {

                    }
                    this.controller.show();
                },

                open: function () {
                    if (!this.prepareWidgetParams()) return;
                    this.controller.reinitialize(this.widgetParams);
                    this.controller.show();
                },
                prepareWidgetParams: function() {
                    let sFilters = [],
                        cargoData = this.createCargoData(),
                        warehouseCustom = $('#<?=self::$MODULE_LBL?>warehouseCustom').prop('checked');
                    if (!$('#<?=self::$MODULE_LBL?>payment_isBeznal').prop('checked')) {
                        if($('#<?=self::$MODULE_LBL?>payment_np').prop('checked')) {
                            sFilters.push('NP');
                        }
                        if ($('#<?=self::$MODULE_LBL?>deliveryPaySide').prop('checked')) {
                            sFilters.push('COD');
                        }
                    }
                    if ($('#<?=self::$MODULE_LBL?>smsAmount').prop('checked')) {
                        sFilters.push('sms_amount');
                    }
                    this.widgetParams = Object.assign(this.widgetParams, {
                        need_insurance: $('#<?=self::$MODULE_LBL?>needInsurance').prop('checked'),
                        insured_value: Number($('#<?=self::$MODULE_LBL?>insuranceValue').val()),
                        services_filter: sFilters.join(','),
                        is_fitting: $('#<?=self::$MODULE_LBL?>fitting').prop('checked'),
                        fitting_default: $('#<?=self::$MODULE_LBL?>fitting').prop('checked'),
                        is_partial_redemption: $('#<?=self::$MODULE_LBL?>partialRedemption').prop('checked'),
                        warehouseCustom: warehouseCustom,
                        warehouseId: warehouseCustom ? $('#<?=self::$MODULE_LBL?>warehouseId').val() : null,
                        isAdminRecalculation: true,
                        onPopupClose: (Widget) => { // обработчик после закрытия кнопкой в режиме попапа
                        },
                        onSelectPvzItem: (Item, widget) => { // Событие при выборе ПВЗ варианта
                            <?=self::$MODULE_LBL?>export.getPage('main').widget.selectDeliveryType(Item);
                        },
                        onSelectCourierItem: (Item, widget) => {// Событие при выборе курьерского варианта
                            <?=self::$MODULE_LBL?>export.getPage('main').widget.selectDeliveryType(Item);
                        }
                    });
                    if (typeof(self.city) == 'string' && self.city.length > 2) this.widgetParams.location.city = self.city;
                    if (typeof(self.address) == 'string' && self.address.length > 2) this.widgetParams.location.address = self.address;
                    if (cargoData !== false) this.widgetParams.cargo = cargoData;
                    //checkCargoData
                    const crg = this.widgetParams.cargo;
                    for (let i in crg) {
                        if (
                            typeof(crg[i].height) == 'undefined'
                            || typeof(crg[i].length) == 'undefined'
                            || typeof(crg[i].weight) == 'undefined'
                            || typeof(crg[i].width) == 'undefined'
                        ) {
                            return false; //Нет нужных данных грузомест... Проверка не пройдена.
                        }
                        let h = Number(crg[i].height ?? 0);
                        let l = Number(crg[i].length ?? 0);
                        let w = Number(crg[i].width ?? 0);
                        let wei = Number(crg[i].weight ?? 0);
                        if (isNaN(h)) h=0;
                        if (isNaN(l)) l=0;
                        if (isNaN(w)) w=0;
                        if (isNaN(wei)) wei=0;
                        if (
                            h==0
                            || l==0
                            || w==0
                            || wei == 0
                        ) {
                            alert('<?=Tools::getMessage('RERATE_GBS_CHECK_ERROR')?>');
                            return false;
                        }
                    }
                    return true;
                },

                createCargoData: function() {
                    if (
                        (typeof(self.self.cargoes.cargoes) != 'undefined')
                        && (self.self.cargoes.cargoes.length > 0)
                    ) {
                        let cargoData = [];
                        for (let i in self.self.cargoes.cargoes) {
                            let crg = {
                                crg_id: self.self.cargoes.cargoes[i].id,
                                ord: self.self.cargoes.cargoes[i].ord,
                                cargo_comment: '',
                                height: self.self.cargoes.cargoes[i].height,
                                length: self.self.cargoes.cargoes[i].length,
                                quantity: 1,
                                width: self.self.cargoes.cargoes[i].width,
                                weight: self.self.cargoes.cargoes[i].weight,
                            }
                            for (let j in self.self.cargoes.cargoes[i].items) {
                                crg.cargo_comment += self.self.cargoes.cargoes[i].items[j].name + '('+ self.self.cargoes.cargoes[i].items[j].quantity +');';
                            }
                            cargoData.push(crg);
                        }
                        return cargoData;
                    }
                    return false;
                },

                formatDate: function (d) {
                    if (d.indexOf('-') === 4) {
                        let arD = d.split('-');
                        return arD[2] + '.' + arD[1] + '.' + arD[0];
                    } else {
                        return d;
                    }
                },

                selectDeliveryType: function (Item) {
                    let data = Item, newAddress = this.controller.getData().getVariant();
                    if (data && (typeof (data.Variant) !== 'undefined' || typeof (data.variant) !== 'undefined')) {
                        if (typeof (data.Variant) !== 'undefined') {
                            data.variant = data.Variant;
                        }
                    }
                    if (data && typeof (data.variant.id) !== 'undefined') {
                        data['rate_param'] = this.controller.getData().RateParams;
                    } else {
                        return false;
                    }

                    let obRateResult = {
                        id: data.variant.id,
                        receiver_loc_id: data.rate_param.receiver_locality_id,
                        receiver_zip: data.locations?.contact?.zip ?? '',
                        sender_loc_id: data.rate_param.sender_locality_id,
                        sender_zip: data.locations?.sender?.zip ?? '',
                        cargoes: data.rate_param.cargoes,
                        delivery_day: data.variant.delivery_day,
                        delivery_time: data.variant.delivery_time,
                        customer_date: data.date || null,
                        operator: data.variant.operator,
                        pickup_day: data.variant.pickup_day,
                        pickup_time: data.variant.pickup_time || null,
                        rate: data.variant.rate,
                        shipping_type: data.variant.shipping_type,
                        price: data.variant.price,
                        price_orig: data.variant.price,
                        services_filter: this.widgetParams.services_filter,
                        extraPrice: this.widgetParams.extraPrice || {},
                        services: data.variant.additional_services,
                        was_cod: this.widgetParams.services_filter.search('COD') >= 0,
                        base_price: data.variant._priceWithoutAdditionalServices,
                        base_price_with_services: data.variant._priceWithAdditionalServices,
                        is_fitting: data.variant._isFitting,
                        with_partial_red: data.variant._isFitting ? true : $('#<?=self::$MODULE_LBL?>partialRedemption').prop('checked'),
                        insurance_config: data.variant.insurance_config ?? 0,
                    }
                    if (typeof (data.selected) !== 'undefined') {
                        if ( typeof (data.selected.delivery_date) !== 'undefined' && (data.selected.delivery_date != null) )
                            obRateResult.delivery_day = data.selected.delivery_date;

                        if ( typeof (data.selected.delivery_time) !== 'undefined' && (data.selected.delivery_time != null) )
                            obRateResult.delivery_time = data.selected.delivery_time;

                        if ( typeof (data.selected.pickup_date) !== 'undefined' && (data.selected.pickup_date != null) )
                            obRateResult.pickup_day = data.selected.pickup_date;

                        if ( typeof (data.selected.pickup_time) !== 'undefined' && (data.selected.pickup_time != null) )
                            obRateResult.pickup_time = data.selected.pickup_time;
                    }

                    if ( typeof(data.Terminal) != 'undefined' ) {
                        obRateResult.terminal_cash = data.Terminal.cash;
                        obRateResult.terminal_card = data.Terminal.card;
                        obRateResult.terminal_code = data.Terminal.code;
                    }

                    let props = {
                        rate_result_id: obRateResult.id,
                        receiver_contact_id: data.rate_param.receiver_contact_id,
                        sender_contact_id: data.rate_param.sender_contact_id,
                        rate_result: JSON.stringify(obRateResult),
                        rate_cost: data.variant.price,
                        rate_term: data.variant.delivery_day ? self.widget.formatDate(data.variant.delivery_day) : '',
                        new_insurance: this.widgetParams.need_insurance,
                        isPVZ: false,
                        PVZAddress: '',
                        dadata: JSON.stringify({
                            zip: newAddress.data.postal_code,
                            country: newAddress.data.country,
                            region: newAddress.data.region_with_type,
                            city: newAddress.data.city_with_type,
                            settlement: newAddress.data.settlement,
                            street: newAddress.data.street_with_type,
                            houset: newAddress.data.house_type,
                            house: newAddress.data.house,
                            blockt: newAddress.data.block_type,
                            block: newAddress.data.block,
                            flatt: newAddress.data.flat_type,
                            flat: newAddress.data.flat,
                            value: newAddress.value,
                            unrestricted_value: newAddress.unrestricted_value,
                            fias_level: newAddress.data.fias_level,
                            city_fias_id: newAddress.data.city_fias_id,
                            settlement_type: newAddress.data.settlement_type,
                            settlement_fias_id: newAddress.data.settlement_fias_id,
                            country_iso_code: newAddress.data.country_iso_code,
                        })
                    };
                    if ( typeof(data.Terminal) != 'undefined' ) {
                        props.isPVZ = true;
                        props.PVZAddress = ((newAddress.data.settlement) ? newAddress.data.settlement : newAddress.data.city_with_type) + ', ' + data.Terminal.address + '#' + data.Terminal.code;
                    }
                    /*if (self.self.cargochanged) {
                        props.reselected = true;//JSON.stringify(self.self.cargoes);
                    }*/
                    props.reselected = true;

                    let controller = this.controller;
                    self.self.ajax({
                        data: {
                            <?=self::$MODULE_LBL?>action: 'updateOrderProps',
                            orderId: self.widget.orderId,
                            props: props
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                controller.hide();
                                window.location.reload();
                            }
                        }//self.widget.onChangeProp(props)
                    });
                },

                onChangeProp: function (props) {
                    /*let rate = JSON.parse(props.rate_result);
                    $('#<?=self::$MODULE_LBL?>hidLabel_rateResultId').html(props.rate_result_id);
                    $('#<?=self::$MODULE_LBL?>rateResultId').val(props.rate_result_id);

                    $('#<?=self::$MODULE_LBL?>hidLabel_receiverId').html(props.receiver_contact_id);
                    $('#<?=self::$MODULE_LBL?>receiverId').val(props.receiver_contact_id);

                    $('#<?=self::$MODULE_LBL?>senderId').val(props.sender_contact_id);
                    $('#<?=self::$MODULE_LBL?>hidLabel_senderId').html(rate.sender_contact_id);

                    $('#<?=self::$MODULE_LBL?>hidLabel_pickupDay').html(rate.pickup_date);
                    $('#<?=self::$MODULE_LBL?>pickupDay').val(rate.pickup_date);

                    if(rate) {
                        if (rate.operator) {
                            $('label[for="<?=self::$MODULE_LBL?>operator"]').closest('tr').find('td:last').html(rate.operator);
                        }
                        if (rate.delivery_day) {
                            $('label[for="<?=self::$MODULE_LBL?>deliveryDay"]').closest('tr').find('td:last').html(rate.delivery_day);
                        }
                        if (rate.delivery_time) {
                            $('label[for="<?=self::$MODULE_LBL?>deliveryTime"]').closest('tr').find('td:last').html(rate.delivery_time);
                        }
                        if (rate.rate) {
                            $('label[for="<?=self::$MODULE_LBL?>operatorRate"]').closest('tr').find('td:last').html(rate.rate);
                        }
                    }*/

                    this.controller.hide();
                    window.location.reload();
                    //$('#<?=self::$MODULE_LBL?>pvzPickerPickerError').closest('tr').hide(200);
                    //$('#<?=self::$MODULE_LBL?>pvzPickerPicker').closest('tr').hide(200);
                },

                updateGoodsGabs: function(currentGoodsGabs) {
                    /*self.self.ajax({
                        data: {
                            <?=self::$MODULE_LBL?>action: 'updateOrderProps',
                            orderId: self.widget.orderId,
                            props: {
                                custom_gabs: currentGoodsGabs
                            }
                        },
                        dataType: 'json',
                        success: function(a,b,c){ console.log(a,b,c) }//self.widget.onChangeProp(props)
                    });*/
                }
            }
        }),

// ui
        ui: {
            toggleBlock: function (code) {
                $('.<?=self::$MODULE_LBL?>block_' + code).toggle();
            },
            makeUnseen: function (wat, mode) {
                if (mode) {
                    wat.addClass('<?=self::$MODULE_LBL?>unseen');
                }
                else {
                    wat.removeClass('<?=self::$MODULE_LBL?>unseen');
                }
            }
        }
    });
</script>
