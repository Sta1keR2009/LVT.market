<?php
if(!check_bitrix_sessid()) return;
?>
<form action="<?php echo $APPLICATION->GetCurPage()?>">
    <input type="hidden" name="lang" value="<?php echo LANG?>">

    <?=GetMessage("CATAPULTO_DELIVERY_INSTALL_TEXT")?><br>

    <input style='display:none' type="submit" name="" value="OK">
</form>