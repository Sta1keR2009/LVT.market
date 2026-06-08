<?php
namespace Ipol\Catapulto\Admin;
use Ipol\Catapulto\Bitrix\Tools;

?>
<script type="text/javascript">
    <?=self::$MODULE_LBL?>export.addPage('cargoes', {
        cargoes: {},
        wnd:        false,
        vatUnknown: -2,
        orderId: '<?=self::getId()?>',
        changed: false,

        open: function(){
            this.cargoes = this.self.copyObj(this.self.cargoes);
            if (this.wnd) {
                this.html();
                this.wnd.open();
            } else {
                this.load();
            }
            //$('#<?=self::$MODULE_LBL?>cargoesEdit').closest('.adm-workarea').siblings('.bx-core-adm-dialog-head').find('.bx-core-adm-icon-close').css('display', 'none');
        },

        load: function(){
            this.wnd = new catapulto_delivery_wndController({
                title:     '<?=Tools::getMessage('GDS_WINTITLE')?>',
                content:   '<table id="<?=self::$MODULE_LBL?>cargoesEdit"></table>',
                resizable: true,
                draggable: true,
                height:    '700',
                width:     '1000',
                buttons: [
                    "<input id='<?=self::$MODULE_LBL?>cargoesSAVE'  type='button' onclick='<?=self::$MODULE_LBL?>export.getPage(\"cargoes\").submit()'   value='<?=Tools::getMessage('GDS_SAVE')?>'>",
                    "<input id='<?=self::$MODULE_LBL?>cargoesERASE' type='button' onclick='<?=self::$MODULE_LBL?>export.getPage(\"cargoes\").erase()'    value='<?=Tools::getMessage('GDS_ERASE')?>'>",
                    "<input style='float:right' id='<?=self::$MODULE_LBL?>cargoesCLOSE' type='button' onclick='<?=self::$MODULE_LBL?>export.getPage(\"cargoes\").winclose()' value='<?=Tools::getMessage('GDS_CLOSE')?>'>",
                ]
            });

            this.html();
            this.wnd.open();
        },

        html: function(){
            var html = '';
            var optVatRate = '<option value="' + this.vatUnknown + '"><?=Tools::getMessage('LBL_UNKNOWN_VAT')?></option><option value="-1"><?=Tools::getMessage('LBL_NOVAT')?></option><option value="0">0%</option><option value="10">10%</option><option value="20">20%</option>';

            html += '<tr><td colspan="2">';
            html += '<a class="ipol_header" onclick="$(this).next().toggle(); return false;"><?=Tools::getMessage('GDS_ABOUT_TITLE')?></a>';
            html += '<div class="ipol_inst"><?=Tools::getMessage('GDS_ABOUT_CONTENT')?></div>';
            html += '</td></tr>';

            for (let i in this.cargoes.cargoes) {
                html += '<tr id="<?=self::$MODULE_LBL?>cargo_' + i + '" cid="'+ this.cargoes.cargoes[i].ccargo_id +'" class="<?=self::$MODULE_LBL?>cargoHeader"><td><?=Tools::getMessage('GDS_CARGO')?> ' + (parseInt(i) + 1) + '</td>';
                html += '<td><div class="<?=self::$MODULE_LBL?>cargoExpand" onclick="<?=self::$MODULE_LBL?>export.getPage(\'cargoes\').toggleParams(' + i + ')"></div>';
                if (i && i != 0)
                    html += '&nbsp;<div class="<?=self::$MODULE_LBL?>cargoDelete" onclick="<?=self::$MODULE_LBL?>export.getPage(\'cargoes\').delete(' + i + ')"></div>';
                html += '</td></tr>';

                html += '<tr><td colspan="2">';
                /* Cargo fields */
                if (typeof(this.cargoes.cargoes[i].fields) === 'object') {
                    for (let f in this.cargoes.cargoes[i].fields) {
                        html += '<input type="hidden" value="' + this.cargoes.cargoes[i].fields[f] + '" data-key="' + f + '" id="<?=self::$MODULE_LBL?>cargoFields_' + i + '_Field_' + f + '">';
                    }
                }
                html += '<table class="<?=self::$MODULE_LBL?>cargoParams" id="<?=self::$MODULE_LBL?>cargoParams_' + i + '">';
                //html += '<tr><td><?=Tools::getMessage('GDS_CARGOES_NAME')?></td><td colspan="2"><input type="text" id="<?=self::$MODULE_LBL?>cargoName_' + i + '" value="' + this.cargoes.cargoes[i].name + '"></td></tr>';
                html += '<tr><td><?=Tools::getMessage('GDS_CARGOES_GABS')?></td><td><input type="text" id="<?=self::$MODULE_LBL?>cargoLength_' + i + '" value="' + this.cargoes.cargoes[i].length + '" class="<?=self::$MODULE_LBL?>cargoDimensions"> X <input type="text" id="<?=self::$MODULE_LBL?>cargoWidth_' + i + '" class="<?=self::$MODULE_LBL?>cargoDimensions" value="' + this.cargoes.cargoes[i].width + '"> X <input type="text" id="<?=self::$MODULE_LBL?>cargoHeight_' + i + '" class="<?=self::$MODULE_LBL?>cargoDimensions" value="' + this.cargoes.cargoes[i].height + '"></td><td><a class="<?=self::$MODULE_LBL?>autoCargoGabs" onclick="<?=self::$MODULE_LBL?>export.getPage(\'cargoes\').autoGabs(' + i + ')" href="javascript:void(0)"><?=Tools::getMessage('GDS_CALC_GABS')?></a></td></tr>';
                html += '<tr><td><?=Tools::getMessage('GDS_CARGOES_WEI')?></td><td><input type="text" id="<?=self::$MODULE_LBL?>cargoWeight_' + i + '" value="' + this.cargoes.cargoes[i].weight + '"></td><td><a href="javascript:void(0)" onclick="<?=self::$MODULE_LBL?>export.getPage(\'cargoes\').autoWeight(' + i + ')"><?=Tools::getMessage('GDS_CALC_WEI')?></a></td></tr>';
                html += '</table>';
                html += '</td></tr>';

                html += '<tr><td colspan="2">';
                html += '<table class="<?=self::$MODULE_LBL?>cargoItems" id="<?=self::$MODULE_LBL?>cargoItems_' + i + '">';
                html += '<tr><th><?=Tools::getMessage('GDS_CARGOES_GABS')?></th><th><?=Tools::getMessage('GDS_CARGOES_WEI')?></th><th><?=Tools::getMessage('GDS_QUANTITY')?></th><th><?=Tools::getMessage('GDS_ARTICUL')?></th><!--<th><?=Tools::getMessage('GDS_COST')?></th>--><th><?=Tools::getMessage('GDS_PRICE')?></th><!--<th><?=Tools::getMessage('LBL_VAT')?></th>--></tr>';
                for (let j in this.cargoes.cargoes[i].items) {
                    let idPrefix = '<?=self::$MODULE_LBL?>cargoItem_' + i + '_' + this.cargoes.cargoes[i].items[j].id + '_';
                    html += '<tr class="<?=self::$MODULE_LBL?>newGood"><td colspan="5">' + '[' + this.cargoes.cargoes[i].items[j].id + '] ' + this.cargoes.cargoes[i].items[j].name + '<input type="hidden" value="' + this.cargoes.cargoes[i].items[j].name + '" id="' + idPrefix + 'Name">';
                    /* Item fields */
                    if (typeof(this.cargoes.cargoes[i].items[j].fields) === 'object') {
                        for (let f in this.cargoes.cargoes[i].items[j].fields) {
                            html += '<input type="hidden" value="' + this.cargoes.cargoes[i].items[j].fields[f] + '" data-key="' + f + '" id="' + idPrefix + 'Field_' + f + '">';
                        }
                    }
                    html += '</td></tr>';
                    html += '<tr id="<?=self::$MODULE_LBL?>cargoItemContainer_' + i + '_' + this.cargoes.cargoes[i].items[j].id + '">';
                    html += '<td><input id="' + idPrefix + 'Length" type="text" value="' + this.cargoes.cargoes[i].items[j].length + '" class="<?=self::$MODULE_LBL?>itemDimensions"> X <input id="' + idPrefix + 'Width" type="text" value="' + this.cargoes.cargoes[i].items[j].width + '" class="<?=self::$MODULE_LBL?>itemDimensions"> X <input id="' + idPrefix + 'Height" type="text" value="' + this.cargoes.cargoes[i].items[j].height + '" class="<?=self::$MODULE_LBL?>itemDimensions"></td>';
                    html += '<td><input id="' + idPrefix + 'Weight" type="text" value="' + this.cargoes.cargoes[i].items[j].weight + '" class="<?=self::$MODULE_LBL?>itemWeight"></td>';
                    html += '<td><input id="' + idPrefix + 'Quantity" type="hidden" value="' + this.cargoes.cargoes[i].items[j].quantity + '"><p class="<?=self::$MODULE_LBL?>gdscnt">' + this.cargoes.cargoes[i].items[j].quantity + '</p><a class="<?=self::$MODULE_LBL?>cargoMover" href="javascript:void(0)" id="<?=self::$MODULE_LBL?>cargoMover_' + i + '_' + this.cargoes.cargoes[i].items[j].id + '" onclick="<?=self::$MODULE_LBL?>export.getPage(\'cargoes\').moveInit(' + this.cargoes.cargoes[i].items[j].id + ',' + i + ')"></a></td>';
                    html += '<td><input id="' + idPrefix + 'Articul" placeholder="<?=Tools::getMessage('GDS_ARTICUL')?>" type="text" value="' + this.cargoes.cargoes[i].items[j].articul + '"></td>';
                    /*html += '<td><input id="' + idPrefix + 'CostAmount" type="hidden" value="' + this.cargoes.cargoes[i].items[j].cost.amount + '">' + Number.parseFloat(this.cargoes.cargoes[i].items[j].cost.amount).toFixed(2) + '<input id="' + idPrefix + 'CostCurrency" type="hidden" value="' + this.cargoes.cargoes[i].items[j].cost.currency + '"></td>';*/
                    html += '<td><input id="' + idPrefix + 'PriceAmount" type="hidden" value="' + this.cargoes.cargoes[i].items[j].price.amount + '">' + Number.parseFloat(this.cargoes.cargoes[i].items[j].price.amount).toFixed(2) + '<input id="' + idPrefix + 'PriceCurrency" type="hidden" value="' + this.cargoes.cargoes[i].items[j].price.currency + '"></td>';
                    //html += '<td><select id="' + idPrefix + 'VatRate" class="<?=self::$MODULE_LBL?>vatRate">' + optVatRate + '</select></td>';
                    html += '</tr>';
                    if (
                        typeof(this.cargoes.cargoes[i].items[j].fields.warn) != 'undefined'
                        && this.cargoes.cargoes[i].items[j].fields.warn == '1'
                    ) {
                        html += '<tr class="<?=self::$MODULE_LBL?>warn"><td><?=Tools::getMessage('GDS_GABSWARN')?></td><td colspan="4"></td></tr>';
                    }
                }
                html += '</table>';
                html += '</td></tr>';

                html += '<tr><td><div id="<?=self::$MODULE_LBL?>cargoMover" class="b-popup" style="display: none;"><div class="pop-text">';
                html += '<p><?=Tools::getMessage('GDS_MOVER')?></p>';
                html += '<p><select id="<?=self::$MODULE_LBL?>cargoMove_to"></select></p>';
                html += '<p><span id="<?=self::$MODULE_LBL?>cargoMove_left"></span>&nbsp;/&nbsp;<input value="1" id="<?=self::$MODULE_LBL?>cargoMove_operate" type="text"></p>';
                html += '<p><a href="javascript:void(0)" onclick="<?=self::$MODULE_LBL?>export.getPage(\'cargoes\').moveConfirm()"><?=Tools::getMessage('GDS_MOVE')?></a></p>';
                html += '</div><div class="close" onclick="$(this).closest(\'.b-popup\').hide();"></div></div></td></tr>';

                if (typeof(this.cargoes.cargoes[i].ui) === 'undefined') {
                    this.cargoes.cargoes[i].ui = {showParams: true};
                    this.toggleParams(i);
                }
            }

            html += '<tr><td colspan="2"><input type="button" onclick="<?=self::$MODULE_LBL?>export.getPage(\'cargoes\').add()" value="<?=Tools::getMessage('GDS_ADD')?>"></td></tr>';

            $('#<?=self::$MODULE_LBL?>cargoesEdit').html(html);

            // VAT crutch
            /*for (let i in this.cargoes.cargoes) {
                for (let j in this.cargoes.cargoes[i].items) {
                    let idPrefix = '<?=self::$MODULE_LBL?>cargoItem_' + i + '_' + this.cargoes.cargoes[i].items[j].id + '_';
                    $('#' +idPrefix + 'VatRate').val(this.cargoes.cargoes[i].items[j].vatRate);
                }
            }*/

            //Auto calc all gabs and wei
            /*for (let i in this.cargoes.cargoes) {
                //<?=self::$MODULE_LBL?>export.getPage('cargoes').autoGabs(i);
                <?=self::$MODULE_LBL?>export.getPage('cargoes').autoWeight(i);
            }*/
        },

        toggleParams: function(i, blockSwitch){
            if (typeof(this.cargoes.cargoes[i]) !== 'undefined') {
                if (this.cargoes.cargoes[i].ui.showParams) {
                    this.cargoes.cargoes[i].ui.showParams = false;
                    $('#<?=self::$MODULE_LBL?>cargoParams_' + i).hide();
                    $('#<?=self::$MODULE_LBL?>cargo_' + i).find('.<?=self::$MODULE_LBL?>cargoExpand').addClass('<?=self::$MODULE_LBL?>Expanded');
                } else {
                    this.cargoes.cargoes[i].ui.showParams = true;
                    $('#<?=self::$MODULE_LBL?>cargoParams_' + i).show();
                    $('#<?=self::$MODULE_LBL?>cargo_' + i).find('.<?=self::$MODULE_LBL?>cargoExpand').removeClass('<?=self::$MODULE_LBL?>Expanded');
                }
            }
        },

        autoWeight: function(i){
            this.preSave();
            var weight = 0;
            for (var j in this.cargoes.cargoes[i].items) {
                weight += this.cargoes.cargoes[i].items[j].weight * this.cargoes.cargoes[i].items[j].quantity;
            }
            $('#<?=self::$MODULE_LBL?>cargoWeight_' + i).val(weight);
        },

        autoGabs: function(i){
            this.preSave();
            var request = {};
            var cargoClone = this.self.copyObj(this.cargoes.cargoes[i]);

            $('.<?=self::$MODULE_LBL?>autoCargoGabs').css('visibility', 'hidden');
            this.self.ajax({
                data: {
                    <?=self::$MODULE_LBL?>action: 'countCargoDimensions',
                    cargo: cargoClone
                },
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        $('#<?=self::$MODULE_LBL?>cargoLength_' + i).val(data.length);
                        $('#<?=self::$MODULE_LBL?>cargoWidth_'  + i).val(data.width);
                        $('#<?=self::$MODULE_LBL?>cargoHeight_' + i).val(data.height);
                    }
                    $('.<?=self::$MODULE_LBL?>autoCargoGabs').css('visibility', 'visible');
                }
            });
        },

        add: function(){
            this.preSave();
            var newCargo = {
                id: this.orderId + '_' + Date.now() + '_' + Math.random(),
                ord: this.orderId,
                ccargo_id: 0,
                name:   '',
                length: 0,
                width:  0,
                height: 0,
                weight: 0,
                items:  [],
                fields: '',
                ui:     {showParams: true}
            };
            this.cargoes.cargoes.push(newCargo);
            this.html();
            this.preSave();
            this.changed = true;
        },

        delete: function(i){
            this.preSave();

            this.mover.from = i;
            this.mover.to = 0;

            for (var j in this.cargoes.cargoes[i].items) {
                this.mover.link = 0;
                this.mover.cnt = this.cargoes.cargoes[i].items[0].quantity;
                this.mover.curId = this.cargoes.cargoes[i].items[0].id;

                this.moveItem(true);
            }

            this.cargoes.cargoes.splice(i, 1);

            this.html();
            this.changed = true;
        },

        preSave: function(){
            var cargoObj = [];
            var link = this;
            $('[id^="<?=self::$MODULE_LBL?>cargo_"]').each(function () {
                var curId = $(this).attr('id').substr($(this).attr('id').indexOf('_cargo_') + 7),
                cargoId = $(this).attr('cid');
                if (typeof(cargoId) === 'undefined') cargoId = 0;

                var newCargo = {
                    id: link.orderId + '_' + Date.now() + '_' + Math.random(),
                    ord: link.orderId,
                    ccargo_id: cargoId,
                    name:   $('#<?=self::$MODULE_LBL?>cargoName_' + curId).val(),
                    length: $('#<?=self::$MODULE_LBL?>cargoLength_' + curId).val(),
                    width:  $('#<?=self::$MODULE_LBL?>cargoWidth_' + curId).val(),
                    height: $('#<?=self::$MODULE_LBL?>cargoHeight_' + curId).val(),
                    weight: $('#<?=self::$MODULE_LBL?>cargoWeight_' + curId).val(),
                };

                newCargo.items = [];
                $('[id^="<?=self::$MODULE_LBL?>cargoItemContainer_' + curId + '_"]').each(function(){
                    var itemId = $(this).attr('id').substr($(this).attr('id').indexOf('_' + curId + '_') + ('_' + curId + '_').length);

                    let itemFields = '';
                    let $itemFields = $('[id^="<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_Field_"]');
                    if ($itemFields.length) {
                        itemFields = {};
                        $itemFields.each(function(){
                            let val = $(this).val();
                            itemFields[$(this).attr('data-key')] = (val === 'true') ? true : ((val === 'false') ? false : val);
                        });
                    }

                    newCargo.items.push({
                        id:       itemId,
                        name:     $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_Name').val(),
                        length:   $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_Length').val(),
                        width:    $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_Width').val(),
                        height:   $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_Height').val(),
                        weight:   $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_Weight').val(),
                        quantity: $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_Quantity').val(),
                        articul:  $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_Articul').val(),
                        cost:     {amount: $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_CostAmount').val(), currency: $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_CostCurrency').val()},
                        price:    {amount: $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_PriceAmount').val(), currency: $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_PriceCurrency').val()},
                        vatRate:  $('#<?=self::$MODULE_LBL?>cargoItem_' + curId + '_' + itemId + '_VatRate').val(),
                        fields:   itemFields,
                    });

                });

                newCargo.ui = link.self.copyObj(link.cargoes.cargoes[curId].ui);

                newCargo.fields = '';
                let $cargoFields = $('[id^="<?=self::$MODULE_LBL?>cargoFields_' + curId + '_Field_"]');
                if ($cargoFields.length) {
                    newCargo.fields = {};
                    $cargoFields.each(function(){
                        let val = $(this).val();
                        newCargo.fields[$(this).attr('data-key')] = (val === 'true') ? true : ((val === 'false') ? false : val);
                    });
                }

                cargoObj.push(newCargo);
            });

            this.cargoes.cargoes = cargoObj;
        },

        checkSave : function(){
            var obReturn = {success: true, reason: false};

            var errorString = '';
            for (var i in this.cargoes.cargoes) {
                var cargoLbl = parseInt(i) + 1;
                if (this.self.isEmpty(this.cargoes.cargoes[i].items)) {
                    errorString += '<?=Tools::getMessage('GDS_CARGO')?> ' + cargoLbl + " <?=Tools::getMessage('GDS_ERROR_NOITEMS')?>\n";
                    obReturn.success = false;
                }

                if (
                    !parseInt(this.cargoes.cargoes[i].length) ||
                    !parseInt(this.cargoes.cargoes[i].width) ||
                    !parseInt(this.cargoes.cargoes[i].height)
                ) {
                    errorString += '<?=Tools::getMessage('GDS_CARGO')?> ' + cargoLbl + " <?=Tools::getMessage('GDS_ERROR_NOGABS')?>\n";
                    obReturn.success = false;
                }

                if (!parseInt(this.cargoes.cargoes[i].weight)) {
                    errorString += '<?=Tools::getMessage('GDS_CARGO')?> ' + cargoLbl + " <?=Tools::getMessage('GDS_ERROR_NOWEIGHT')?>\n";
                    obReturn.success = false;
                }

                /*for (let j in this.cargoes.cargoes[i].items) {
                    let idPrefix = '<?=self::$MODULE_LBL?>cargoItem_' + i + '_' + this.cargoes.cargoes[i].items[j].id + '_';
                    if (parseInt($('#' +idPrefix + 'VatRate').val()) === this.vatUnknown) {
                        errorString += '<?=Tools::getMessage('GDS_CARGO')?> ' + cargoLbl + " <?=Tools::getMessage('GDS_ERROR_VAT')?>\n";
                        obReturn.success = false;
                        break;
                    }
                }*/

                for (let j in this.cargoes.cargoes[i].items) {
                    let idPrefix = '<?=self::$MODULE_LBL?>cargoItem_' + i + '_' + this.cargoes.cargoes[i].items[j].id + '_',
                        l=Number($('#' +idPrefix + 'Length').val()),
                        w=Number($('#' +idPrefix + 'Width').val()),
                        h=Number($('#' +idPrefix + 'Height').val()),
                        wei=Number($('#' +idPrefix + 'Weight').val());
                    if (isNaN(l)) l=0;
                    if (isNaN(w)) w=0;
                    if (isNaN(h)) h=0;
                    if (isNaN(wei)) wei=0;
                    if (
                        (l==0)
                        || (w==0)
                        || (h==0)
                        || (wei==0)
                    ) {
                        errorString += '<?=Tools::getMessage('GDS_CARGO')?> ' + cargoLbl + " <?=Tools::getMessage('GDS_U_TOVARA')?> \"" + this.cargoes.cargoes[i].items[j].name + "\" <?=Tools::getMessage('GDS_NET_GABARITOV')?>\n";
                        obReturn.success = false;
                        //break;
                    }
                }

            }

            if (!obReturn.success) {
                obReturn.reason = errorString;
            }

            return obReturn;
        },

        mover: {
            curId: false,
            from:  false,
            cnt:   false,
            to:    false,
            link:  false
        },

        moveInit: function(id, from){
            if (!this.cargoes.cargoes || this.cargoes.cargoes.length < 2) {
                alert('<?=Tools::getMessage('GDS_ERROR_NOCARGO')?>');
                return;
            }

            this.preSave();

            const left = $('#<?=self::$MODULE_LBL?>cargoMover_' + from + '_' + id).position().left - 160; //popup width + popup paddings + offset
            let top = $('#<?=self::$MODULE_LBL?>cargoMover_' + from + '_' + id).position().top;
            if ((this.cargoes.cargoes.length - 1) == Number(from)) {
                top = $('#<?=self::$MODULE_LBL?>cargoMover_' + from + '_' + id).position().top + 15 - 200;
            }
            const win = $('#<?=self::$MODULE_LBL?>cargoesEdit').parents('.bx-core-adm-dialog');
            if ( (top + 250) > win.height() ) {
                top = win.height() - 250;
            }

            this.self.popup('<?=self::$MODULE_LBL?>cargoMover', $('#<?=self::$MODULE_LBL?>cargoMover_' + from + '_' + id), '#<?=self::$MODULE_LBL?>cargoesEdit', left, top);

            this.mover.curId = id;
            this.mover.from  = from;
            this.mover.to    = false;
            this.mover.cnt   = false;

            $('#<?=self::$MODULE_LBL?>cargoMove_to').children().each(function(){
                $(this).replaceWith("")
            });
            for (var i in this.cargoes.cargoes) {
                if (i != from) {
                    $('#<?=self::$MODULE_LBL?>cargoMove_to').append("<option value='" + i + "'><?=Tools::getMessage('GDS_CARGO')?> " + (parseInt(i) + 1) + "</option>");
                }
            }

            for (var j in this.cargoes.cargoes[from].items) {
                if (this.cargoes.cargoes[from].items[j].id == id) {
                    $('#<?=self::$MODULE_LBL?>cargoMove_left').html(this.cargoes.cargoes[from].items[j].quantity);
                    this.mover.link = j;
                    break;
                }
            }

            $('#<?=self::$MODULE_LBL?>cargoMove_operate').removeClass('<?=self::$MODULE_LBL?>errInput');
        },

        moveConfirm: function(){
            this.mover.to  = $('#<?=self::$MODULE_LBL?>cargoMove_to').val();
            this.mover.cnt = Number($('#<?=self::$MODULE_LBL?>cargoMove_operate').val());
            if (isNaN(this.mover.cnt)) this.mover.cnt = 0;

            if (this.mover.cnt > Number(this.cargoes.cargoes[this.mover.from].items[this.mover.link].quantity) || this.mover.cnt == 0) {
                $('#<?=self::$MODULE_LBL?>cargoMove_operate').addClass('<?=self::$MODULE_LBL?>errInput');
                return;
            }

            $('#<?=self::$MODULE_LBL?>cargoMove_operate').removeClass('<?=self::$MODULE_LBL?>errInput');
            this.moveItem();
        },

        moveItem: function(noRewrite){
            this.preSave();

            if (
                !this.mover.link ||
                typeof(this.cargoes.cargoes[this.mover.from].items[this.mover.link]) === 'undefined' ||
                this.cargoes.cargoes[this.mover.from].items[this.mover.link].id !== this.mover.curId
            ) {
                for (var i in this.cargoes.cargoes[this.mover.from].items) {
                    if (this.cargoes.cargoes[this.mover.from].items[i].id === this.mover.curId)
                        this.mover.link = i;
                }
            }

            var exisLink = false;
            for (var i in this.cargoes.cargoes[this.mover.to].items) {
                if (this.cargoes.cargoes[this.mover.to].items[i].id == this.mover.curId) {
                    exisLink = i;
                    break;
                }
            }

            if (exisLink !== false) {
                this.cargoes.cargoes[this.mover.to].items[i].quantity = parseInt(this.mover.cnt) + parseInt(this.cargoes.cargoes[this.mover.to].items[i].quantity);
            } else {
                var newItem = this.self.copyObj(this.cargoes.cargoes[this.mover.from].items[this.mover.link]);
                newItem.quantity = this.mover.cnt;
                this.cargoes.cargoes[this.mover.to].items.push(newItem);
            }

            if (this.cargoes.cargoes[this.mover.from].items[this.mover.link].quantity === this.mover.cnt) {
                this.cargoes.cargoes[this.mover.from].items.splice(this.mover.link, 1);
            } else {
                this.cargoes.cargoes[this.mover.from].items[this.mover.link].quantity -= this.mover.cnt;
            }

            var weight = 0;
            for (var j in this.cargoes.cargoes[this.mover.from].items) {
                weight += this.cargoes.cargoes[this.mover.from].items[j].weight * this.cargoes.cargoes[this.mover.from].items[j].quantity;
            }
            this.cargoes.cargoes[this.mover.from].weight = weight;
            weight = 0;
            for (var j in this.cargoes.cargoes[this.mover.to].items) {
                weight += this.cargoes.cargoes[this.mover.to].items[j].weight * this.cargoes.cargoes[this.mover.to].items[j].quantity;
            }
            this.cargoes.cargoes[this.mover.to].weight = weight;

            for (var i in this.cargoes.cargoes[this.mover.from].items) {
                if (this.cargoes.cargoes[this.mover.from].items[i].quantity == 0) this.cargoes.cargoes[this.mover.from].items.splice(i, 1);
            }


            if (typeof(noRewrite) !== 'undefined' || noRewrite !== true) {
                this.html();
            }
            this.changed = true;
        },

        submit: function(){
            this.preSave();
            this.self.cargochanged = true;
            var check = this.checkSave();
            if (check.success) {
                this.wnd.close();
                this.self.getPage('main').act.saveCustomCargoData(this.cargoes, /*this.changed*/ true);
                // Some main script calls there
                //if (this.changed) {
                    this.self.cargoes = this.self.copyObj(this.cargoes);
                    this.self.getPage('main').act.selectNewDeliveryType();
                //}
            } else {
                alert(check.reason);
            }
        },

        erase: function(){
            if (confirm('<?=Tools::getMessage('GDS_MSG_ERASE')?>')) {
                $('#<?=self::$MODULE_LBL?>cargoesEdit').html('');
                this.cargoes = this.self.copyObj(this.self.cargoes);
                this.html();
                this.changed = true;
            }
        },
        winclose: function() {
            this.wnd.close();
        },
    });
</script>

<style>
    .b-popup {
        background-color: #FEFEFE;
        border: 1px solid #9A9B9B;
        box-shadow: 0 0 10px #B9B9B9;
        display: none;
        font-size: 12px;
        padding: 19px 13px 15px;
        position: absolute;
        top: 38px;
        width: 300px;
        z-index: 12;
    }
    .b-popup .pop-text {
        margin-bottom: 10px;
        color:#000;
    }
    .pop-text i {
        color:#AC12B1;
    }
    .b-popup .close {
        background: url('/bitrix/images/catapulto.delivery/popup_close.gif') no-repeat transparent;
        cursor: pointer;
        height: 10px;
        position: absolute;
        right: 4px;
        top: 4px;
        width: 10px;
    }
    .<?=self::$MODULE_LBL?>gdscnt {
        display: inline-block;
        margin: 0 20px 0 0;
        position: relative;
        top: -5px;
    }
    a.<?=self::$MODULE_LBL?>cargoMover {
        display: inline-block;
        position:relative;
        width: 20px;
        height: 20px;
    }
    a.<?=self::$MODULE_LBL?>cargoMover:before,
    a.<?=self::$MODULE_LBL?>cargoMover:after {
        display: block;
        content:"";
        position:absolute;
        left:0;
        top:0;
        width:100%;
        height:100%;
        transition: 0.5s;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center center;
    }
    a.<?=self::$MODULE_LBL?>cargoMover:before {
        background-image: url('/bitrix/images/catapulto.delivery/move.svg');
    }
    a.<?=self::$MODULE_LBL?>cargoMover:after {
        background-image: url('/bitrix/images/catapulto.delivery/move_blue.svg');
        opacity:0;
    }
    a.<?=self::$MODULE_LBL?>cargoMover:hover:before {
        opacity:0;
    }
    a.<?=self::$MODULE_LBL?>cargoMover:hover:after {
        opacity:1;
    }
    .<?=self::$MODULE_LBL?>warn td {
        color: #f00;
    }
</style>
