<?php
namespace Ipol\Catapulto\Admin;

use Ipol\Catapulto\Bitrix\Adapter;
use Ipol\Catapulto\Bitrix\Tools;
?>

<script type="text/javascript">
    <?=self::$MODULE_LBL?>export.addPage('goodsgabs', {
        info: <?=($order->getField('addList')) ? $order->getField('addList'): '{}'?>,
        goodsgabs: <?=($customGabs) ? \CUtil::PhpToJSObject($customGabs)  : '{}'?>,
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
        },

        load: function () {
            this.wnd = new catapulto_delivery_wndController({
                title: '<?=Tools::getMessage('HDR_GOODSGABSEDIT')?>',
                content: '<table id="<?=self::$MODULE_LBL?>gabsgoodsEdit"></table>',
                resizable: true,
                draggable: true,
                height: '500',
                width: '1024',
                buttons: [
                    <?if(Adapter::statusIsSending(self::$status)){?>
                    "<input id='<?=self::$MODULE_LBL?>addOK' type='button' onclick='<?=self::$MODULE_LBL?>export.getPage(\"goodsgabs\").submit()' value='<?=Tools::getMessage('BTN_SAVE')?>'>",
                    <?}?>
                ]
            });
            this.wnd.open();
        },

        parseLoaded : function (cargos) {
            var goodsInfo = <?=self::$MODULE_LBL?>export.items;
        },

        html: function () {
            var items = this.self.items;

            // putting saved in options
            if(!this.blocked && this.firstLoaded) {
                this.parseLoaded(items);
                this.firstLoaded = true;
            }

            var container = $('#<?=self::$MODULE_LBL?>gabsgoodsEdit');
            container.html('');
            container.append('<tr class="<?=self::$MODULE_LBL?>addHeader"><th><?=Tools::getMessage('LBL_GOOD')?></th><th><?=Tools::getMessage('LBL_VENDORCODE')?></th><th><?=Tools::getMessage('LBL_PRICE')?></th><th><?=Tools::getMessage('LBL_QUAN')?></th> <th><?=Tools::getMessage('LBL_width')?></th><th><?=Tools::getMessage('LBL_length')?></th><th><?=Tools::getMessage('LBL_height')?></th><th><?=Tools::getMessage('LBL_weight')?></th> </tr>');

            var saved   = this.info;
            items.forEach(function (item) {
                var _self = <?=self::$MODULE_LBL?>export.getPage('goodsgabs');
                var dataStr = '_data_iid="'+item.id+'"';

                let we = Number(_self.getHtmlData(item.id,'weight')),
                w = Number(_self.getHtmlData(item.id,'width')),
                l = Number(_self.getHtmlData(item.id,'length')),
                h = Number(_self.getHtmlData(item.id,'height'));
                if (typeof _self.goodsgabs[item.id] != 'undefined') {
                    we = Number(_self.goodsgabs[item.id]['we']);
                    w = Number(_self.goodsgabs[item.id]['w']);
                    l = Number(_self.goodsgabs[item.id]['l']);
                    h = Number(_self.goodsgabs[item.id]['h']);
                }
                if (isNaN(we)) we = 0;
                if (isNaN(w)) w = 0;
                if (isNaN(l)) l = 0;
                if (isNaN(h)) h = 0;
                container.append('<tr><td _data_name="'+item.id+'">[' + item.id + '] ' + item.name + '</td><td>'+_self.getHtmlData(item.id,'articul')+'</td><td>'+_self.getHtmlData(item.id,'price')+'</td><td>'+item.quantity+'</td> <td><input '+dataStr+' class="l" type="text" value="'+l+'"/></td><td><input '+dataStr+' class="w" type="text" value="'+w+'"/></td><td><input '+dataStr+' class="h" type="text" value="'+h+'"/></td><td><input '+dataStr+' class="we" type="text" value="'+we+'"/></td> </tr>');

            });
            container.find('input[type=text]').keyup(function (){
                var t=this,rep = /[-;":'a-zA-Zà-ÿÀ-ß\\=`¸¨/\*++!@#$%\^&_¹?><\s|~(),\[\]{}\.]/g;
                if (rep.test(t.value)) t.value = t.value.replace(rep, '');
            }).blur(function(){
                let t=$(this);
                if (isNaN(t.val()) || (t.val() == '')) t.val('0');
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

        preSave: function () {
            var obSaves = ['l','w','h','we'];
            var save    = {};
            this.self.items.forEach(function (item) {
                var obItem = {i:item.id};
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

            this.goodsgabs = save;
        },

        checkSave : function(){
            let obSaves = ['l','w','h','we'];
            var obReturn = {success: true, reason: false};

            for (const indx in this.goodsgabs) {
                let item = this.goodsgabs[indx];
                obSaves.forEach(function(param){
                    item[param] = Number(item[param]);
                    if (isNaN(item[param])) item[param] = 0;
                    if (item[param] == 0) {
                        obReturn = {success: false, reason: "<?=Tools::getMessage('ERROR')?>\n<?=Tools::getMessage('ERROR_NOGABS')?>"};
                        return obReturn;
                    }
                });
            }

            return obReturn;
        },

// buttons
        submit: function () {
            this.preSave();
            var check = this.checkSave();
            if(check.success) {
                this.self.getPage('main').widget.updateGoodsGabs(this.goodsgabs);
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