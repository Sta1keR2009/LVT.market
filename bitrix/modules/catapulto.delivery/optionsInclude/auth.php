<?php

use Ipol\Catapulto\Bitrix\Tools as Tools;

// страница авторизации, представлена для примера
?>

<script type="text/javascript">
    <?=$LABEL?>setups.addPage('auth', {
        init: function () {

        },

        auth: function (mode) {
            this.demarkError();
            this.handleBlock(true);
            var apikey = $('#CATAPULTO_DELIVERY_apikey').val(),customUrl = $('#CATAPULTO_DELIVERY_customApiUrl').val();
            if (customUrl.substr(-1)=='/') customUrl = customUrl.substr(0,customUrl.length-1);
            if (!apikey) {
                this.markError($('#CATAPULTO_DELIVERY_apikey'));
                this.handleBlock();
            } else {
                this.self.ajax({
                    cache: false,
                    data: {<?=$LABEL?>action: 'auth', apikey: apikey, custom_url: customUrl},
                    success: function (data) {
                        data = JSON.parse(data);
                        if (data.success) {
                            alert('<?=\Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_AUTHORIZED')?>');
                            <?=$LABEL?>setups.reload();
                        } else {
                            var text = "<?=\Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_NOTAUTHORIZED')?> \n" + data.error;
                            alert(text);
                            <?=$LABEL?>setups.getPage('auth').handleBlock();
                            $('.ipol_header').click();
                        }
                    }
                });
            }
        },

        markError: function (wat) {
            wat.addClass('CATAPULTO_DELIVERY_errInput');
        },

        demarkError: function () {
            $('.CATAPULTO_DELIVERY_errInput').removeClass('CATAPULTO_DELIVERY_errInput');
        },

        handleBlock: function (block) {
            if (typeof (block) === 'undefined' || !block) {
                $('#CATAPULTO_DELIVERY_auth').removeAttr('disabled');
            } else {
                $('#CATAPULTO_DELIVERY_auth').attr('disabled', 'disabled');
            }
        },

        checkCustomApiUrl: function() {
            let url = document.getElementById('CATAPULTO_DELIVERY_customApiUrl').value;
            if (url.substr(-1)=='/') url = url.substr(0,url.length-1);
            document.getElementById('CATAPULTO_DELIVERY_customApiUrl').value = url;
        },

        setAuthTestMode: function() {
            let checked = document.getElementById('CATAPULTO_DELIVERY_isTestAuth').checked;
            if (checked) {
                document.querySelector('tr.ipol_catapulto_authtesturl').style.display = '';
            } else {
                document.querySelector('tr.ipol_catapulto_authtesturl').style.display = 'none';
                document.getElementById('CATAPULTO_DELIVERY_customApiUrl').value = '';
            }
        },

    });
</script>

<tr>
    <td class="adm-detail-content-cell-l" width="30%"><?= \Ipol\Catapulto\Bitrix\Tools::getMessage('API_KEY') ?></td>
    <td class="adm-detail-content-cell-r" width="80%"><input size="60" type="text" name="CATAPULTO_DELIVERY_apikey" id="CATAPULTO_DELIVERY_apikey"/></td>
</tr>
<tr>
    <td class="adm-detail-content-cell-l" width="30%"><?= \Ipol\Catapulto\Bitrix\Tools::getMessage('EN_TESTMODE') ?></td>
    <td class="adm-detail-content-cell-r" width="80%"><input type="checkbox" id="CATAPULTO_DELIVERY_isTestAuth" name="CATAPULTO_DELIVERY_isTestAuth" value="Y" class="adm-designed-checkbox" onchange="<?= $LABEL ?>setups.getPage('auth').setAuthTestMode();"><label class="adm-designed-checkbox-label" for="CATAPULTO_DELIVERY_isTestAuth" title=""></label></td>
</tr>
<tr class="ipol_catapulto_authtesturl" style="display:none;">
    <td class="adm-detail-content-cell-l" width="30%">
        <label for="CATAPULTO_DELIVERY_customApiUrl"><?= \Ipol\Catapulto\Bitrix\Tools::getMessage('OPT_customApiUrl') ?></td>
    <td width="80%" class="adm-detail-content-cell-r">
        <input size="60" type="text" name="CATAPULTO_DELIVERY_customApiUrl" id="CATAPULTO_DELIVERY_customApiUrl" onblur="<?= $LABEL ?>setups.getPage('auth').checkCustomApiUrl();" onchange="<?= $LABEL ?>setups.getPage('auth').checkCustomApiUrl();"/>
    </td>
</tr>
<tr>
    <td colspan="2"><input id="CATAPULTO_DELIVERY_auth" type="button" onclick="<?= $LABEL ?>setups.getPage('auth').auth();" value="<?= \Ipol\Catapulto\Bitrix\Tools::getMessage('BTN_AUTH') ?>"/></td>
</tr>

<tr>
    <td colspan="2">
        <div style="margin-top: 30px">
            <?php Tools::placeFAQ('ACCESS') ?>
        </div>
    </td>
</tr>