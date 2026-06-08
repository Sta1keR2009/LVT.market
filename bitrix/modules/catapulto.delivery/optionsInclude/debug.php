<?php
$controller = new \Ipol\Catapulto\Bitrix\Controller\AbstractController($module_id,$LABEL);
?>
<script type="text/javascript">
    <?=$LABEL?>setups.addPage('debug', {
        init: function () {
        },

        killAPILog : function () {
            <?=$LABEL?>setups.ajax({
                data : {
                    <?=$LABEL?>action: 'clearLog',
                    src: '<?=$controller->getLoggerName()?>'
                },
                success : function () {
                    window.location.reload();
                }
            })
        }
    });
</script>

<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionBlock('debug_request')?>

<?php
$logPath = Ipol\Catapulto\OptionsHandler::getAPILogs();
\Ipol\Catapulto\Bitrix\Tools::placeWarningLabel(
    ($logPath) ? '<a href="/bitrix/admin/fileman_file_view.php?path=%2Fbitrix%2Fmodules%2FCatapulto_API.txt&lang=ru" target="_blank">'.\Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_openLog').'</a>' : '',
    ($logPath) ? \Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_haslog') : \Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_nolog')
);
?>

<?php \Ipol\Catapulto\Bitrix\Tools::placeOptionRow(false,'<input type="button" onclick="'.$LABEL.'setups.getPage(\'debug\').killAPILog()" value="'.\Ipol\Catapulto\Bitrix\Tools::getMessage('LBL_CLEAR').'">')?>



