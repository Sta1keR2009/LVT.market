<script type="text/javascript">
    <?=$LABEL?>setups.addPage('main',{
        init: function(){

        },

        delogin: function() {
            if (confirm('<?=\Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_REALLYDELOGIN')?>')) {
                this.self.ajax({
                    data: {<?=$LABEL?>action: 'delogin'},
                    success: <?=$LABEL?>setups.reload
                });
            }
        },

        clearCache: function(){
            $('#CATAPULTO_DELIVERY_chearCache').attr('disabled','disabled');
            this.self.ajax({
                data: {<?=$LABEL?>action: 'clearCache'},
                success: function(){
                    alert('<?=\Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_CLEARED')?>');
                    $('#CATAPULTO_DELIVERY_chearCache').removeAttr('disabled');
                }
            })
        },

        clearLog: function (code) {
            $('#<?=$LABEL?>clear'+code).closest('.adm-info-message-wrap').hide();
            this.self.ajax({
                data: {<?=$LABEL?>action: 'clearLog',src: code}
            })
        },

        showHidden: function(link){
            link.closest('tr').nextUntil(".heading").each(function(){
                $(this).removeClass('<?=$LABEL?>hidden');
            });
        },

        showLogin : function () {
            $('#<?=$LABEL?>loginShort').addClass('<?=$LABEL?>hidden');
            $('#<?=$LABEL?>loginFull').removeClass('<?=$LABEL?>hidden');
        },

        onMarkupAdd: function() {
            this.addOperatorWin.show();
            //window.catapulto_add_operator.show()
        },

        onMarkupKeyup: function(t) {
            let rep = /^[\.|0]|[-;":'a-zA-Zа-яА-Я\\=`ёЁ/\*++!@#$%\^&_№?><\s|~(),\[\]{}]/g;
            if (rep.test(t.value)) t.value = t.value.replace(rep, '');
        },

        onMarkupBlur: function(th) {
            if (th.value.length==0) th.value = '0';
        },

        addOperatorWin: null,

        onDeleteMarkupOption: function(t) {
            let p = $(t).parents('tr.<?=$LABEL?>markupOperators'), inpName = p.find('input').attr('name');
            p.replaceWith('<input type="hidden" id="<?=$LABEL?>'+inpName+'" name="'+inpName+'" value="0" />');
            if ($('tr.<?=$LABEL?>markupOperators').length == 0) {
                $('tr.<?=$LABEL?>markupTitles').removeClass('sh');
            }
        },

    });
    BX.ready(function() {
        (function() {
            "use strict";
            var picker = new BX.ColorPicker({
                bindElement: null,
                //defaultColor: "#000000",
                popupOptions: {
                    offsetTop: 10,
                    offsetLeft: 10,
                    angle: true
                }
            });
            let colorPickerInputs = document.querySelectorAll('input.color-picker-box');
            if(colorPickerInputs.length > 0) {
                colorPickerInputs.forEach(function(input) {
                    BX.bind(BX(input), "click", colorPickerShow);
                    if(input.value) {
                        updatePreview(input);
                    }
                });
            }
            
            function updatePreview(input)
            {
                let previewSpan = input.nextElementSibling;

                if(!previewSpan || !previewSpan.classList.contains('picker-preview')) {
                    previewSpan = document.createElement('span');
                    previewSpan.className = 'picker-preview';
                    input.parentNode.insertBefore(previewSpan, input.nextSibling);
                    BX.bind(BX(previewSpan), "click", colorPickerShow);
                }

                previewSpan.style.backgroundColor = input.value;
            }
           
            function colorPickerShow(event)
            {
                let input = event.target;
                if(!input.classList.contains('color-picker-box')) {
                    input = event.target.parentNode.querySelector('input.color-picker-box');
                }
                
                let color = BX.type.isNotEmptyString(input.value) ? input.value : null;
                
                picker.open({
                    selectedColor: color,
                    colorPreview: true,
                    bindElement: input,
                    onColorSelected: onColorSelected.bind(input)
                });
                picker.previewColor(color);
            }

            function onColorSelected(color, picker)
            {
                let input = picker.bindElement;
                let previewSpan = picker.bindElement;
                if(!input.classList.contains('color-picker-box')) {
                    input = input.parentNode.querySelector('input.color-picker-box');
                }
                
                input.value = color;
                updatePreview(input);
            }

        })();
    });
    
</script>
<style>
    #<?=$LABEL?>services td{
        text-align: center;
    }
    #<?=$LABEL?>loginFull {
        display: inline-block;
        max-width: 350px;
        font-weight: bold;
    }
    #catapulto_operator_select {
        width:100%;
    }
    .picker-preview {
        display: inline-block;
        width: 25px;
        height: 25px;
        margin-left: 10px;
        vertical-align: middle;
        border: 1px solid #ccc;
        border-radius: 3px;
        cursor: pointer;
    }
    .<?=$LABEL?>tbl {
        width: 100%;
        display: table;
    }
    .<?=$LABEL?>tbl p.mkVal {
        float:left;
        width: 30%;
        margin-bottom: 0;
        margin-right: 10px !important;
    }
    .<?=$LABEL?>tbl p.mkType {
        float:left;
        width: 30%;
        margin-left: 10px;
        margin-bottom: 0;
        margin-right: 10px !important;
    }
    .<?=$LABEL?>tbl p.mkDel {
        float:right;
        margin-bottom: 0;
        text-align:right;
    }
    .<?=$LABEL?>tbl input {
        float:left;
        width: 30%;
        margin-right: 10px !important;
    }
    .<?=$LABEL?>tbl select {
        float:left;
        width: 30%;
        margin-right: 10px !important;
    }
    .<?=$LABEL?>tbl span.delete {
        display:block;
        float:right;
        cursor: pointer;
        width: 26px;
        height: 26px;
        position:relative;
    }
    .<?=$LABEL?>tbl span.delete span:first-child {
        display:block;
        position:absolute;
        left:50%;
        top:50%;
        transform:translate(-50%,-50%) rotate(45deg);
        background: #ff635b;
        width: 22px;
        height: 3px;
        opacity: 0.5;
        transition: 0.5s;
    }
    .<?=$LABEL?>tbl span.delete span:last-child {
        display:block;
        content"";
        position:absolute;
        left:50%;
        top:50%;
        transform:translate(-50%,-50%) rotate(-45deg);
        background: #ff635b;
        width: 22px;
        height: 3px;
        opacity: 0.5;
        transition: 0.5s;
    }
    .<?=$LABEL?>tbl span.delete:hover span {
        opacity: 1;
    }
    .<?=$LABEL?>markupTitles:not(.sh) {
        display: none;
    }
    .<?=$LABEL?>markupTitles p.h {
        font-weight: 700;
        font-size: 14px;
    }

    .<?=$LABEL?>freedlv {
        width: 80px;
        min-width: 80px;
    }
    .<?=$LABEL?>freedlv label {
        top: 50%;
        position: relative;
        left: 50%;
        transform: translate(-50%,-50%);
        margin-top: 10px;
    }

    textarea[name=ctptGeoEmptyMessage],
    textarea[name=ctptCustomDefaultWidgetText] {
        width: 100%;
    }

</style>

<?php
    $strInfo = '';
    foreach(array('tarifs','statuses') as $logCode){
        if(\Ipol\Catapulto\Admin\Logger::getLogInfo($logCode)){
            $strInfo .= \Ipol\Catapulto\Bitrix\Tools::getMessage('NOTE_'.$logCode)." <a href='#CATAPULTO_DELIVERY_clear".$logCode."'>".\Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_GOTO')."</a><br><br>";
        }
    }
    if($strInfo){
        \Ipol\Catapulto\Bitrix\Tools::placeWarningLabel($strInfo);
    }
?>

<tr>
    <td class="adm-detail-content-cell-l" width="50%">
        <?=\Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_YOULOGIN')?>
        <strong id="<?=$LABEL?>loginShort"><?=substr(\Ipol\Catapulto\Bitrix\Entity\Options::fetchOption('apikey'),0,8)?>(<a href="javascript:void(0)" onclick="<?=$LABEL?>setups.getPage('main').showLogin()">...</a>)</strong>
        <input type="text" id="<?=$LABEL?>loginFull" class="<?=$LABEL?>hidden" value="<?=\Ipol\Catapulto\Bitrix\Entity\Options::fetchOption('apikey')?>" disabled readonly>
        <input type="hidden" name="apikey" value="<?=\Ipol\Catapulto\Bitrix\Entity\Options::fetchOption('apikey')?>">
    </td>
    <td class="adm-detail-content-cell-r" width="50%"><input type="button" onclick="<?=$LABEL?>setups.getPage('main').delogin()" id="CATAPULTO_DELIVERY_delogin" value="<?=\Ipol\Catapulto\Bitrix\Tools::getMessage('BTN_DELOGIN')?>"/><?=(\Ipol\Catapulto\Option::get('isTest') == 'Y') ? '&nbsp;&nbsp;<span class="'.$LABEL.'warning">'.\Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_TESTMODE').'</span>' : ''?></td>
</tr>
<tr>
    <td colspan="2"><input type="button" onclick="<?=$LABEL?>setups.getPage('main').clearCache()" id="CATAPULTO_DELIVERY_chearCache" value="<?=\Ipol\Catapulto\Bitrix\Tools::getMessage('BTN_CLEARCACHE')?>"/></td>
</tr>

<?php
/*
 * Вывод блоков опций по группам
 * */
?>

<?php // common?>
<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionBlock('common');?>
<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionBlock('defaultCargo');?>
<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionBlock('delivery');?>
<?//php \Ipol\Catapulto\Bitrix\Tools::placeOptionTerminalMarkups();?>
<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionBlock('statuses');?>
<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionBlock('orderProps');?>
<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionBlock('widget');?>
<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionBlock('payments');?>
<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionBlock('colors');?>
<?php // service?>
<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionBlock('service',true);?>

<script type="text/javascript">
    if (document.getElementById('isTest').checked) {
        document.querySelector('input[name=customApiUrl]').parentNode.parentNode.style.display = '';
        document.querySelector('input[name=customWSSUrl]').parentNode.parentNode.style.display = '';
    } else {
        document.querySelector('input[name=customApiUrl]').parentNode.parentNode.style.display = 'none';
        document.querySelector('input[name=customWSSUrl]').parentNode.parentNode.style.display = 'none';
    }
    document.getElementById('isTest').onchange = function() {
        if (this.checked) {
            document.querySelector('input[name=customApiUrl]').parentNode.parentNode.style.display = '';
            document.querySelector('input[name=customWSSUrl]').parentNode.parentNode.style.display = '';
        } else {
            document.querySelector('input[name=customApiUrl]').parentNode.parentNode.style.display = 'none';
            document.querySelector('input[name=customWSSUrl]').parentNode.parentNode.style.display = 'none';
        }
    }
    function checkCustomUrl() {
        let t=this,url = t.value;
        if (url.substr(-1)=='/') url = url.substr(0,url.length-1);
        t.value = url;
    }

    document.querySelector('input[name=customApiUrl]').onchange = checkCustomUrl;
    document.querySelector('input[name=customWSSUrl]').onchange = checkCustomUrl;
    document.querySelector('input[name=customApiUrl]').onblur = checkCustomUrl;
    document.querySelector('input[name=customWSSUrl]').onblur = checkCustomUrl;

    if (document.querySelector('input[name=markupValue]') != null) {
      document.querySelector('input[name=markupValue]').onkeyup = checkMarkupKeyup;
      document.querySelector('input[name=markupValue]').onblur = checkMakrupBlur;
    }

    function checkMarkupKeyup() {
        let t=this,rep = /^[\.|0]|[-;":'a-zA-Zа-яА-Я\\=`ёЁ/\*++!@#$%\^&_№?><\s|~(),\[\]{}]/g;
        if (rep.test(t.value)) t.value = t.value.replace(rep, '');
    }

    function checkMakrupBlur() {
        if (this.value.length==0) this.value = '0';
    }

    document.querySelector('textarea[name=ctptGeoEmptyMessage]').onkeyup = function(e) {
        const t = this;
        if (t.value.length > 300) t.value = t.value.substring(0, 300);
    }

    const isFitting = document.querySelector('#isFitting'),
        fittingDefaultEnabled = document.querySelector('#fittingDefaultEnabled'),
        onFittingOff = () => {
            fittingDefaultEnabled.checked = false;
            fittingDefaultEnabled.setAttribute('disabled', 'disabled');
        };
    isFitting.onchange = function() {
        if (isFitting.checked) {
            fittingDefaultEnabled.removeAttribute('disabled');
        } else onFittingOff();
    }
    if (!isFitting.checked) onFittingOff();
    fittingDefaultEnabled.onchange = function() {
        if (fittingDefaultEnabled.checked && !isFitting.checked) fittingDefaultEnabled.checked = false;
    }

    BX.ready(function () {
        let operatorOptions = '';
        for (let i in window.catapulto_operators) {
            operatorOptions += '<option value="'+catapulto_operators[i].i+'">'+catapulto_operators[i].n+'</option>';
        }

        <?=$LABEL?>setups.getPage('main').addOperatorWin = BX.PopupWindowManager.create("popup-message", null, {
            content: '<p>Выберите оператора</p><select id="catapulto_operator_select">'+operatorOptions+'</select>',
            width: 400,
            zIndex: 100,
            closeIcon: {
                opacity: 1
            },
            titleBar: 'Добавить оператора для наценки',
            closeByEsc: true,
            darkMode: false,
            autoHide: true,
            draggable: false,
            resizable: true,
            min_height: 100,
            min_width: 100,
            lightShadow: true,
            angle: false,
            overlay: {
                backgroundColor: 'black',
                opacity: 500
            },
            buttons: [
                new BX.PopupWindowButton({
                    text: 'Добавить',
                    id: 'copy-btn',
                    className: 'ui-btn ui-btn-primary',
                    events: {
                        click: function() {
                            const selId = $('#catapulto_operator_select').val();
                            const selText = $('#catapulto_operator_select option[value='+selId+']').html();
                            const inputTypeId = 'markupOperatorsTypes_' + selId;
                            const inputValId = 'markupOperatorsVals_' + selId;

                            if ($('#<?=$LABEL?>' + inputValId).attr('type') == 'hidden')
                                $('#<?=$LABEL?>' + inputValId).replaceWith('<tr class="<?=$LABEL?>markupOperators" id="<?=$LABEL?>'+inputTypeId+'_wr"><td width="50%" class="adm-detail-content-cell-l">'+selText+'</td><td width="50%" class="adm-detail-content-cell-r"><div class="<?=$LABEL?>tbl"><input type="text" name="'+inputValId+'" id="<?=$LABEL?>'+inputValId+'" value="0" onkeyup="<?=$LABEL?>setups.getPage(\'main\').onMarkupKeyup(this)" onblur="<?=$LABEL?>setups.getPage(\'main\').onMarkupBlur(this)" /> <select name="'+inputTypeId+'" id="<?=$LABEL?>'+inputTypeId+'"><option value="0" selected>Фиксированная</option><option value="1">Процент от стоимости доставки (%)</option></select> <span class="delete" onclick="<?=$LABEL?>setups.getPage(\'main\').onDeleteMarkupOption(this)"><span></span><span></span></span>  </div></td></tr>');
                            $('tr.<?=$LABEL?>markupTitles').addClass('sh');
                            <?=$LABEL?>setups.getPage('main').addOperatorWin.close();
                        }
                    }
                })
            ],
            events: {
                onPopupShow: function() {
                    $('#catapulto_operator_select option').prop('selected',false);
                    $('#catapulto_operator_select option:first-child').prop('selected',true);
                },
                onPopupClose: function() {

                }
            }
        });

    });

</script>
