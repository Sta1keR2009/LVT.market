<?if($GLOBALS['arTheme']['NLO_MENU']['VALUE'] === 'Y'):?>
    <script src="<?=$GLOBALS['APPLICATION']->oAsset->getFullAssetPath($this->{'__template'}->{'__folder'}.'/script.js')?>"></script>
    <script>BX.loadCSS(['<?=$GLOBALS['APPLICATION']->oAsset->getFullAssetPath($this->{'__template'}->{'__folder'}.'/style.css');?>']);</script>
<?endif;?>