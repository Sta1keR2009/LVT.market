<?
$bClick2show4Depth = $arTheme['CLICK_TO_SHOW_4DEPTH']['VALUE'] === 'Y';

$liClass= '';
$liClass .= $sCountElementsMenu;
if($bShowChilds) {
    $liClass .= ' header-menu__dropdown-item--with-dropdown';
}
if($arSubItem["SELECTED"]) {
    $liClass .= ' active';
}
if($bHasPicture) {
    $liClass .= ' has_img';
}

$liClass .= ' header-menu__dropdown-item--img-'.$arTheme['IMAGES_WIDE_MENU_POSITION']['VALUE'];
?>
<li class="header-menu__dropdown-item <?=$liClass?>">
    <?if($bHasPicture):
        
        if($bIcon) {
            $arImg = CFile::ResizeImageGet($arSubItem['PARAMS']['ICON'], array('width' => 56, 'height' => 56), BX_RESIZE_IMAGE_PROPORTIONAL_ALT);
        }
        elseif($bTransparentPicture) {
            $arImg = CFile::ResizeImageGet($arSubItem['PARAMS']['TRANSPARENT_PICTURE'], array('width' => 56, 'height' => 56), BX_RESIZE_IMAGE_PROPORTIONAL_ALT);
        }
        elseif($bPicture) {
            $arImg = CFile::ResizeImageGet($arSubItem['PARAMS']['PICTURE'], array('width' => 56, 'height' => 56), BX_RESIZE_IMAGE_PROPORTIONAL_ALT);
        }
        $imgClass = '';
        $imgClass .= ' header-menu__dropdown-item-img--'.$arTheme['IMAGES_WIDE_MENU_POSITION']['VALUE'];
        if(is_array($arImg)):?>
            <div class="header-menu__dropdown-item-img <?=$imgClass?>">
				<div class="header-menu__dropdown-item-img-inner">
                    <a href="<?=$arSubItem["LINK"]?>">
                        <?if($bIcon):?>
                            <?=TSolution::showIconSvg(' fill-theme', $arImg['src']);?>
                        <?else:?>
                            <img src="<?=$arImg["src"]?>" alt="<?=$arSubItem["TEXT"]?>" title="<?=$arSubItem["TEXT"]?>" />
                        <?endif;?>
                    </a>
				</div>
            </div>
        <?endif;?>
    <?endif;?>

    <div class="header-menu__wide-item-wrapper">
        <a class="font_16 font_weight--500 dark_link switcher-title header-menu__wide-child-link fill-theme-hover" href="<?=$arSubItem["LINK"]?>">
            <span><?=$arSubItem["TEXT"]?></span>
            <?if($bShowChilds):?>
                <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', ' header-menu__wide-submenu-right-arrow fill-dark-light-block only_more_items', ['WIDTH' => 7,'HEIGHT' => 5]);?>
            <?endif;?>
        </a>
        <?if($bShowChilds):?>
            <?$iCountChilds = count($arSubItem["CHILD"]);?>
            <ul class="header-menu__wide-submenu">
                <?
                $counterWide = 1;
                foreach($arSubItem["CHILD"] as $key => $arSubSubItem):?>
                    <?$bShowChilds = $arSubSubItem["CHILD"] && $arParams["MAX_LEVEL"] > 3;?>
                    <li class="<?=($counterWide > $iVisibleItemsMenu ? 'collapsed' : '');?> header-menu__wide-submenu-item <?=$counterWide == count($arSubItem["CHILD"]) ? 'header-menu__wide-submenu-item--last' : ''?> <?=($bShowChilds ? "header-menu__wide-submenu-item--with-dropdown" : "")?> <?=($arSubSubItem["SELECTED"] ? "active" : "")?>" <?=($counterWide > $iVisibleItemsMenu ? 'style="display: none;"' : '');?>>
                        <span class="header-menu__wide-submenu-item-inner">
                            <a class="font_15 dark_link fill-theme-hover fill-dark-light-block header-menu__wide-child-link" href="<?=$arSubSubItem["LINK"]?>">
                                <span class="header-menu__wide-submenu-item-name"><?=$arSubSubItem["TEXT"]?></span><?if( $bShowChilds && $bClick2show4Depth ):?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?endif;/*!!!hack for correct move icon to new line!!!*/?><?
                                ?><?if(
                                    $bShowChilds &&
                                    $bClick2show4Depth
                                ):?><?
                                    ?><span class="toggle_block"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', 'down header-menu__wide-submenu-right-arrow menu-arrow bg-opacity-theme-target fill-theme-target ', ['WIDTH' => 7,'HEIGHT' => 5]);?></span>
                                <?endif;?>
                                <?if($bShowChilds):?>
                                    <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', ' header-menu__wide-submenu-right-arrow fill-dark-light-block only_more_items', ['WIDTH' => 7,'HEIGHT' => 5]);?>
                                <?endif;?>
                            </a>
                            <?if($bShowChilds):?>
                                <div class="submenu-wrapper"<?=($bClick2show4Depth ? ' style="display:none"' : '')?>>
                                    <ul class="header-menu__wide-submenu">
                                        <?foreach($arSubSubItem["CHILD"] as $arSubSubSubItem):?>
                                            <li class="header-menu__wide-submenu-item <?=($arSubSubSubItem["SELECTED"] ? "active" : "")?>">
                                                <span class="header-menu__wide-submenu-item-inner">
                                                    <a class="font_14 dark_link header-menu__wide-child-link" href="<?=$arSubSubSubItem["LINK"]?>"><span class="header-menu__wide-submenu-item-name"><?=$arSubSubSubItem["TEXT"]?></span></a>
                                                </span>
                                            </li>
                                        <?endforeach;?>
                                    </ul>
                                </div>
                            <?endif;?>
                        </span>
                    </li>
                    <?$counterWide++;?>
                <?endforeach;?>
                <?if($iCountChilds > $iVisibleItemsMenu && $bWideMenu):?>
                    <li class="header-menu__wide-submenu-item--more_items">
                        <span class="dark_link with_dropdown font_15 fill-dark-light-block svg">
                            <?=\Bitrix\Main\Localization\Loc::getMessage("S_MORE_ITEMS");?>
                            <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', ' menu-arrow', ['WIDTH' => 7,'HEIGHT' => 5]);?>
                        </span>
                        
                    </li>
                <?endif;?>
            </ul>
        <?endif;?>
    </div>
</li>