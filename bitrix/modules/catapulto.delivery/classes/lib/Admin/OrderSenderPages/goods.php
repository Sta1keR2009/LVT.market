<?php
namespace Ipol\Catapulto\Admin;

use Ipol\Catapulto\Bitrix\Adapter;
use Ipol\Catapulto\Bitrix\Tools;
?>

<?/*<script type="text/javascript" src="<?=Tools::getJSPath()?>chosen/chosen.jquery.min.js"></script>
<link rel="stylesheet" href="<?=Tools::getJSPath()?>chosen/chosen.min.css" />*/?>
<script type="text/javascript">
    <?=self::$MODULE_LBL?>export.addPage('goods', {
        info: <?=($order->getField('addList')) ? $order->getField('addList') : '{}'?>,
        wnd: false,
        firstLoaded: false,
        blocked : <?=(Adapter::statusIsSending(self::$status))?'false':'true'?>,

        open: function () {
            if (this.wnd)
                this.wnd.open();
            else {
                this.load();
            }

            this.html();

            <?/*if(Adapter::statusIsSending(self::$status)){?>
            $('#<?=self::$MODULE_LBL?>goodsEdit').closest('.adm-workarea').siblings('.bx-core-adm-dialog-head').find('.bx-core-adm-icon-close').css('display', 'none');
            <?}*/?>
        },

        load: function () {
            this.wnd = new catapulto_delivery_wndController({
                title: '<?=Tools::getMessage('HDR_GOODSEDIT')?>',
                content: '<table id="<?=self::$MODULE_LBL?>goodsEdit"></table>',
                resizable: true,
                draggable: true,
                height: '500',
                width: '1024',
                buttons: [
                    <?if(Adapter::statusIsSending(self::$status)){?>
                    "<input id='<?=self::$MODULE_LBL?>addOK'    type='button' onclick='<?=self::$MODULE_LBL?>export.getPage(\"goods\").submit()' value='OK'>",
                    <?/*
                    "<input id='<?=self::$MODULE_LBL?>addERASE' type='button' onclick='<?=self::$MODULE_LBL?>export.getPage(\"goods\").erase()'  value='<?=Tools::getMessage('BTN_ERASE')?>'>",
                    */?>
                    <?}?>
                ]
            });

            this.wnd.open();

            var self = this;
/*
            this.self.items.forEach(function(item){
                if(
                    typeof(item.properties)    !== 'undefined' &&
                    typeof(item.properties.QR) !== 'undefined'
                ){
                    self.qrs[item.id] = item.properties.QR;
                }
            });

            this.currentQRS = this.self.copyObj(this.qrs);*/
        },

        parseLoaded : function (cargos) {
            var goodsInfo = <?=self::$MODULE_LBL?>export.items;
/*
            var saved = this.info;
            if(typeof(saved.oc) === 'undefined'){
                saved.oc = {};
            }

            for(var number in cargos){
                if(typeof(number) !== 'function'){
                    saved.oc[number] = {};
                    cargos[number].items.forEach(function(obItem){
                        goodsInfo.forEach(function(item){
                            if(obItem.id === item.id){
                                if(typeof(saved.oc[number][item.id]) === 'undefined'){
                                    saved.oc[number][item.id] = [];
                                }
                                for(var i=0;i<obItem.quantity;i++) {
                                    saved.oc[number][item.id].push(item.properties.OriginCountry);
                                }
                            }
                        });
                    });
                }
            }*/
        },

        html: function () {
            var items = this.self.items;
            var optSelect = '<option value="0">0%</option><option value="10">10%</option><option value="20">20%</option>';

            // putting saved in options
            if(!this.blocked && this.firstLoaded) {
                this.parseLoaded(items);
                this.firstLoaded = true;
            }

            var container = $('#<?=self::$MODULE_LBL?>goodsEdit');
            container.html('');
            container.append('<tr class="<?=self::$MODULE_LBL?>addHeader"><th><?=Tools::getMessage('LBL_GOOD')?></th><th><?=Tools::getMessage('LBL_VENDORCODE')?></th><th><?=Tools::getMessage('LBL_PRICE')?></th><th><?=Tools::getMessage('LBL_QUAN')?></th><th><?=Tools::getMessage('LBL_NDS')?></th><th><?=Tools::getMessage('LBL_ISDANGEROUS')?></th></tr>');

            var saved   = this.info;
            var haveCis = (typeof(saved.cis) !== 'undefined');
            var gtdLink = this.gtd;

            var rplcr = this.self.replaceAll;
            var readonly = (this.blocked) ? 'readonly' : '';
            items.forEach(function (item) {
                var _self = <?=self::$MODULE_LBL?>export.getPage('goods');
                var dataStr = '_data_iid="'+item.id+'"';
                <?/*var cntry = gtdLink.getSavedCntry(item.id);
                cntry.name = (cntry && cntry.name && cntry.code) ? cntry.name : '<?=Tools::getMessage('LBL_ADD_NOTGIVEN')?>';
                var ccd   = gtdLink.getSavedCCD(item.id);
                var tnved = gtdLink.getSavedTNVED(item.id);*/?>
                container.append('<tr><td _data_name="'+item.id+'">' + item.name + ' [' + item.id + ']</td><td><input '+dataStr+' class="articul" type="text" value="'+_self.getHtmlData(item.id,'articul')+'"/></td><td>'+_self.getHtmlData(item.id,'price')+'<input type="hidden" '+dataStr+' class="price" value="'+_self.getHtmlData(item.id,'price')+'"/><input type="hidden" '+dataStr+' class="cost" value="'+_self.getHtmlData(item.id,'cost')+'"/></td><td>'+item.quantity+'</td><td><select '+dataStr+' class="vatRate">'+optSelect+'</select></td><td><input '+dataStr+' class="IsDangerous" type="checkbox" value="Y"'+((_self.getHtmlData(item.id,'IsDangerous') && _self.getHtmlData(item.id,'IsDangerous') !== 'false')?'checked="checked"':'')+'/></td></tr>');
                <?/*container.append('<tr><td _data_name="'+item.id+'">' + item.name + ' [' + item.id + ']</td><td><input type="text" '+dataStr+' class="barcode" value="'+_self.getHtmlData(item.id,'barcode')+'"/></td><td><input '+dataStr+' class="articul" type="text" value="'+_self.getHtmlData(item.id,'articul')+'"/></td><td>'+_self.getHtmlData(item.id,'price')+'<input type="hidden" '+dataStr+' class="price" value="'+_self.getHtmlData(item.id,'price')+'"/></td><td>'+item.quantity+'</td><td><select '+dataStr+' class="vatRate">'+optSelect+'</select></td><td>'+
                    '<input type="hidden" '+dataStr+' class="oc" value="'+((cntry && cntry.code) ? cntry.code : '')+'">'  +
                    '<input type="hidden" '+dataStr+' class="ccd" value="'+((ccd) ? ccd : '')+'">' +
                    '<input type="hidden" '+dataStr+' class="tnved" value="'+((tnved) ? tnved : '')+'">' +
                    '<span '+dataStr+' class="<?=self::$MODULE_LBL?>countryHint"><?=Tools::getMessage('LBL_originCountry')?>: '+cntry.name+'</span><br>' +
                    '<span '+dataStr+' class="<?=self::$MODULE_LBL?>GTDHint"><?=Tools::getMessage('LBL_GTD')?>: '+((ccd) ? ccd : '<?=Tools::getMessage('LBL_NOTGIVEN')?>')+'</span><br>' +
                    '<span '+dataStr+' class="<?=self::$MODULE_LBL?>TNVEDHint"><?=Tools::getMessage('LBL_TNVED')?>: '+((tnved) ? tnved : '<?=Tools::getMessage('LBL_NOTGIVEN')?>')+'</span><br>' +
                    '<a href="javascript:void(0)" onclick="<?=self::$MODULE_LBL?>export.getPage(\'goods\').gtd.edit('+item.id+')"><?=Tools::getMessage('LBL_PLACEGOODDATA')?></a>'+
                    '</td></tr>'
                );*/?>

                var vatRate  = _self.getHtmlData(item.id,'vatRate');
                if(vatRate) {
                    $('[_data_iid="' + item.id + '"].vatRate').val(vatRate);
                }
            });
        },

        getHtmlData : function(id,type){
            if(typeof(this.info[id]) !== 'undefined' && typeof(this.info[id][type]) !== 'undefined'){
                return this.info[id][type];
            } else {
                var svd = false;
                this.self.items.forEach(function(item){
                    if(item.id === id){
                        svd = item[type];
                    }
                });
                return svd;
            }
        },

        // not used
        gtd : {
            blocked  : <?=(Adapter::statusIsSending(self::$status))?'false':'true'?>,
            guide    : <?=\CUtil::PhpToJSObject(self::getCountryArray())?>,
            curCargo : false,
            curItem  : false,
            curQnt   : false,

            getSavedCCD : function (itemId) {
                var ret = false;

                search = this.getSavedObj(itemId);

                if(search && typeof(search.ccd) !== 'undefined'){
                    ret = search.ccd;
                }

                return ret;
            },
            getSavedTNVED : function (itemId) {
                var ret = false;

                search = this.getSavedObj(itemId);
                if(search && typeof(search.tnved) !== 'undefined'){
                    ret = search.tnved;
                }

                return ret;
            },
            getSavedCntry : function(itemId){
                var ret = {code:false,name:false};

                search = this.getSavedObj(itemId);

                if(search && typeof(search.oc) !== 'undefined'){
                    ret.code = search.oc;
                    var cntryName = this.getCountry(search.oc);
                    if(cntryName){
                        ret.name = this.guide[cntryName];
                    }
                }

                return ret;
            },
            getSavedObj : function (itemId) {
                var quickSaved = <?=self::$MODULE_LBL?>export.getPage('goods').info;
                var search = false;

                if(!<?=self::$MODULE_LBL?>export.isEmpty(quickSaved)){
                    search = this.findItemInSaved(itemId,quickSaved);
                } else {
                    search = this.findItemInSaved(itemId,<?=self::$MODULE_LBL?>export.items);
                    if(search){
                        search = search.properties;
                    }
                }

                return search;
            },
            findItemInSaved : function (item,arSaved) {
                var found = false;
                if(typeof(arSaved) === 'object'){
                    arSaved.forEach(function(obItem){
                        if(obItem.id === item){
                            found = obItem;
                        }
                    });
                }

                return found;
            },

            edit : function (item) {
                this.curItem  = item;

                var obExt = this.makeWnd(item);
                this.wnd.open();

                $('#<?=self::$MODULE_LBL?>editCntrName').chosen({
                    no_results_text: '<?=Tools::getMessage('LBL_ADD_NFND')?>',
                    max_shown_results: 7
                });

                // loading existed
                var ccd = this.getSavedCCD(item);

                if(ccd){
                    $('#<?=self::$MODULE_LBL?>editCCD').val(ccd);
                } else {
                    if(!obExt.ccd) {
                        $('#<?=self::$MODULE_LBL?>editCCD').val('');
                    }
                }
                var tnved = this.getSavedTNVED(item);
                if(tnved){
                    $('#<?=self::$MODULE_LBL?>editTNVED').val(tnved);
                } else {
                    if(!obExt.tnved) {
                        $('#<?=self::$MODULE_LBL?>editTNVED').val('');
                    }
                }

                var cntry = this.getSavedCntry(item);
                if(cntry.code){
                    $('#<?=self::$MODULE_LBL?>editCntr').val(cntry.code);
                    $('#<?=self::$MODULE_LBL?>editCntrName').val(cntry.code);
                    $('#<?=self::$MODULE_LBL?>editCntrName').trigger('chosen:updated');
                } else {
                    if(!obExt.country) {
                        $('#<?=self::$MODULE_LBL?>editCntrName').val('');
                        $('#<?=self::$MODULE_LBL?>editCntr').val('');
                    }
                }
            },
            getCountry : function(code){
                var ret = false;
                for(var i in this.guide){
                    if(i == code){
                        return i;
                    }
                }
                return ret;
            },

            makeWnd : function (goodId) {
                if(!this.wnd){
                    var countrySelect = '<option value=""><?=Tools::getMessage('LBL_ADD_PNAME')?></option>';
                    for(var code in this.guide){
                        countrySelect +='<option value="'+code+'">'+this.guide[code]+'</option>';
                    }

                    var readonly = (this.blocked) ? 'readonly' : '';
                    var disabled = (this.blocked) ? 'disabled' : '';

                    this.wnd = new catapulto_delivery_wndController({
                        title: '<?=Tools::getMessage('HDR_ADDGTD')?>',
                        content: '<table>' +
                        '<tr><th colspan="2" id="<?=self::$MODULE_LBL?>namePlace"></th></tr>' +
                        '<tr><td><?=Tools::getMessage('LBL_originCountry')?></td><td><input type="text" '+readonly+' placeholder="<?=Tools::getMessage('LBL_ADD_PCODE')?>" id="<?=self::$MODULE_LBL?>editCntr"><br><br><?=Tools::getMessage('LBL_ADD_OR_CZ')?><br><br><select '+disabled+' id="<?=self::$MODULE_LBL?>editCntrName">'+countrySelect+'</select></td></tr>' +
                        '<tr><td><?=Tools::getMessage('LBL_GTD')?></td><td><textarea '+readonly+' id="<?=self::$MODULE_LBL?>editCCD"></textarea></td></tr>' +
                        '<tr><td><?=Tools::getMessage('LBL_TNVED')?></td><td><textarea '+readonly+' id="<?=self::$MODULE_LBL?>editTNVED"></textarea></td></tr>' +
                        '</table>',
                        //'</table><input type="checkbox" '+disabled+' id="<?=self::$MODULE_LBL?>addApply">&nbsp;<label for="<?=self::$MODULE_LBL?>addApply"><?=Tools::getMessage('LBL_ADD_APPLYSIMILAR')?></label>',
                        resizable: false,
                        draggable: true,
                        height: '350',
                        width: '400',
                        buttons: [
                            <?if(Adapter::statusIsSending(self::$status)){?>
                            "<input id='<?=self::$MODULE_LBL?>GTDOK' type='button' onclick='<?=self::$MODULE_LBL?>export.getPage(\"goods\").gtd.submit()' value='OK'>",
                            <?}?>
                        ]
                    });

                    $('#<?=self::$MODULE_LBL?>editCntrName').on('change',function(evt, params) {
                        $('#<?=self::$MODULE_LBL?>editCntr').val(params.selected);
                    });
                    $('#<?=self::$MODULE_LBL?>editCntr').on('keyup',function() {
                        $(this).val($(this).val().replace(/[^\d]/g,''));

                        var chosen = $(this).val();
                        var select = $('#<?=self::$MODULE_LBL?>editCntrName');
                        if(<?=self::$MODULE_LBL?>export.getPage('goods').gtd.getCountry(chosen)){
                            select.val(chosen);
                        } else {
                            select.val(false);
                        }

                        $('#<?=self::$MODULE_LBL?>editCntrName').trigger('chosen:updated');
                    });
                }

                var obRet = {};

                var existCntr   = $('.oc[_data_iid="'+this.curItem+'"]');
                var existCCD    = $('.ccd[_data_iid="'+this.curItem+'"]');
                var existTNVED  = $('.tnved[_data_iid="'+this.curItem+'"]');

                $('#<?=self::$MODULE_LBL?>editCntr').val(existCntr.val());
                $('#<?=self::$MODULE_LBL?>editCCD').val(existCCD.val());
                $('#<?=self::$MODULE_LBL?>editTNVED').val(existTNVED.val());
                $('#<?=self::$MODULE_LBL?>addApply').prop('checked',false);

                obRet.country = (existCntr.val());
                obRet.ccd     = (existCCD.val());
                obRet.tnved   = (existTNVED.val());

                var goodName = $('[_data_name="'+goodId+'"]');
                if(goodName.length){
                    $('#<?=self::$MODULE_LBL?>namePlace').html(goodName.html());
                } else {
                    $('#<?=self::$MODULE_LBL?>namePlace').html('');
                }

                return obRet;
            },
            wnd : false,
            submit : function(){
                var cntry = $('#<?=self::$MODULE_LBL?>editCntr').val();
                var ccd   = $('#<?=self::$MODULE_LBL?>editCCD').val();
                var tnved = $('#<?=self::$MODULE_LBL?>editTNVED').val();
                var ifAll = true;//($('#<?=self::$MODULE_LBL?>addApply').prop('checked'));

                var selector = '';
                if(ifAll){
                    selector = '[_data_iid="'+this.curItem+'"]';
                } else {
                    //selector = '[_data_cargo="'+this.curCargo+'"][_data_iid="'+this.curItem+'"][_data_iiq="'+this.curQnt+'"]';
                }
                $('.ccd'+selector).val(ccd);
                $('.tnved'+selector).val(tnved);
                if(cntry && typeof(this.guide[cntry]) !== 'undefined'){
                    $('.oc'+selector).val(cntry);
                    $('.<?=self::$MODULE_LBL?>countryHint'+selector).html('<?=Tools::getMessage('LBL_originCountry')?>: '+this.guide[cntry]);
                } else {
                    $('.oc'+selector).val('');
                    $('.<?=self::$MODULE_LBL?>countryHint'+selector).html('<?=Tools::getMessage('LBL_originCountry')?>: <?=Tools::getMessage('LBL_NOTGIVEN')?>');
                }

                $('.<?=self::$MODULE_LBL?>GTDHint'+selector).html('<?=Tools::getMessage('LBL_GTD')?>: '+((ccd)?ccd:'<?=Tools::getMessage('LBL_NOTGIVEN')?>'));
                $('.<?=self::$MODULE_LBL?>TNVEDHint'+selector).html('<?=Tools::getMessage('LBL_TNVED')?>: '+((tnved)?tnved:'<?=Tools::getMessage('LBL_NOTGIVEN')?>'));

                this.wnd.close();
            }
        },

        autoFill : function () {
            var allItems = [];
            for (var i in this.self.items) {
                var obProperties = (typeof(this.self.items[i].properties) === 'object') ? this.self.items[i].properties : false;
                allItems.push({
                    name    : this.self.items[i].name,
                    articul : this.self.items[i].articul,
//                    barcode : this.self.items[i].barcode,
                    price   : this.self.items[i].price,
                    cost    : this.self.items[i].cost,
                    quantity: this.self.items[i].quantity,
                    IsDangerous: this.self.items[i].IsDangerous,
//                    cis     : '',
//                    oc      : (obProperties && typeof(obProperties['oc']) !== 'undefined') ? obProperties['oc'] : '',
//                    ccd     : (obProperties && typeof(obProperties['ccd']) !== 'undefined') ? obProperties['ccd'] : '',
//                    tnved   : (obProperties && typeof(obProperties['tnved']) !== 'undefined') ? obProperties['tnved'] : '',
                    id: this.self.items[i].id,
                    vatRate : this.self.items[i].vatRate
                });
            }
            return allItems;
        },

        preSave: function () {
            var obSaves = ['name','articul',/*'barcode',*/'weight','price','cost','quantity','IsDangerous',/*'oc','ccd','tnved',*/'id','vatRate'];
            var save    = {};
            this.self.items.forEach(function (item) {
                var obItem = {};
                obSaves.forEach(function (field) {
                    var input = $('[_data_iid="'+item.id+'"].'+field);

                    if(input.length) {
                        if(input.attr('type') === 'checkbox'){
                            obItem[field] = $('[_data_iid="' + item.id + '"].' + field).prop('checked') ? true : false;
                        } else {
                            obItem[field] = $('[_data_iid="' + item.id + '"].' + field).val();
                        }
                    } else {
                        obItem[field] = (typeof(item[field]) === 'undefined') ? false : item[field];
                    }
                });
                save[item.id] = obItem;
            });

            this.info = save;
        },

        checkSave : function(){
            var obReturn = {success: true, reason: false};

            return obReturn;
        },

// buttons
        submit: function () {
            this.preSave();

            var check = this.checkSave();
            if(check.success) {
                this.wnd.close();
            } else {
                alert(check.reason);
            }
        },

        erase: function (forse) {
            this.info = [];
            this.html('');
            this.currentQRS = this.self.copyObj(this.qrs);
            if(typeof(forse) === 'undefined' || !forse){
                this.wnd.close();
            }
        }

    });
</script>