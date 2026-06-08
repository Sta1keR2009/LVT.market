<?php
foreach( $arResult['ITEMS'] as &$arItem ){
    if( isset($arItem['DISPLAY_PROPERTIES']['PERIOD']) && $arItem['DISPLAY_PROPERTIES']['PERIOD']['VALUE'] ){
        $arItem['PERIOD'] = $arItem['DISPLAY_PROPERTIES']['PERIOD']['VALUE'];
    }
}
?>