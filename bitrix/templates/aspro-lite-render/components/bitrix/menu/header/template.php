<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>
<?$this->setFrameMode(true);?>
<?php
global $arTheme;
$iVisibleItemsMenu = ($arTheme['MAX_VISIBLE_ITEMS_MENU']['VALUE'] ? $arTheme['MAX_VISIBLE_ITEMS_MENU']['VALUE'] : 10);
// $sViewTypeMenu = $arTheme['VIEW_TYPE_MENU']['VALUE'];
$sCountElementsMenu = 'count_'.$arTheme['COUNT_ITEMS_IN_LINE_MENU']['VALUE'];
$bRightPart = $arTheme['SHOW_RIGHT_SIDE']['VALUE'] == 'Y';
$bOnlyCatalog = $arParams['ONLY_CATALOG'] === 'Y';
$bLargeCatalogButton = $arParams['LARGE_CATALOG_BUTTON'] === 'Y';
$bNloMenu = $arParams['USE_NLO_MENU'] === 'Y' && $bOnlyCatalog;
$nloMenuCode = $arParams['NLO_MENU_CODE'] ?? 'menu-fixed';
$menuType = $arTheme['MEGA_MENU_TYPE']['VALUE'];

$bManyItemsMenu = ($arTheme['USE_BIG_MENU']['VALUE'] === 'Y');
?>
<?if ($arResult):?>
    <div class="catalog_icons_<?=$arTheme['SHOW_CATALOG_SECTIONS_ICONS']['VALUE'];?>">
        <div class="header-menu__wrapper">
            <?php
            $counter = 1;
            foreach ($arResult as $arItem):?>
                <?php
                $bShowChilds = $arItem['CHILD'] && $arParams['MAX_LEVEL'] > 1;
                $bWideMenu = ($arItem['PARAMS']['WIDE_MENU'] == 'Y') || ($arParams['CATALOG_WIDE'] === 'Y');
                $arItem['bManyItemsMenu'] = $bManyItemsMenu && $arItem['PARAMS']['MENU_NOT_BIG'] !== 'Y';
                if (!$bWideMenu) {
                    $arItem['bManyItemsMenu'] = false;
                }
                $topMenuLinkClassList = [];
                if ($bOnlyCatalog) {
                    $topMenuLinkClassList[] = 'header-menu__link--only-catalog fill-use-fff btn btn-default btn--no-rippple';
                } else {
                    $topMenuLinkClassList[] = 'header-menu__link header-menu__link--top-level light-opacity-hover fill-theme-hover banner-light-text dark_link';
                }

                if ($bLargeCatalogButton) {
                    $topMenuLinkClassList[] = 'btn-lg';
                }
                ?>
                <div class="header-menu__item unvisible <?= $counter == 1 ? 'header-menu__item--first' : '';?> <?= $counter == count($arResult) ? 'header-menu__item--last' : '';?> <?= $bShowChilds ? 'header-menu__item--dropdown' : '';?><?= $bWideMenu ? ' header-menu__item--wide' : '';?><?= $arItem['SELECTED'] ? ' active' : '';?>">
                    <?if (!$bOnlyCatalog || (!$bShowChilds && !$bNloMenu)):?>
                        <a class="<?=TSolution\Utils::implodeClasses($topMenuLinkClassList);?>" href="<?=$arItem['LINK'];?>">
                            <span class="header-menu__title font_14">
                                <?=$arItem['TEXT'];?>
                            </span>
                            <?if ($bShowChilds && !$bOnlyCatalog):?>
                                <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', ' header-menu__wide-submenu-right-arrow fill-dark-light-block banner-light-icon-fill', ['WIDTH' => 7, 'HEIGHT' => 5]);?>
                            <?endif;?>
                        </a>
                    <?else:?>
                        <button type="button" class="<?=TSolution\Utils::implodeClasses($topMenuLinkClassList);?>">
                            <?if ($bOnlyCatalog):?>
                                <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/header_icons.svg#burger-16-16', 'header-menu__burger-icon', ['WIDTH' => 16, 'HEIGHT' => 16]);?>
                            <?endif;?>
                            <span class="header-menu__title font_14">
                                <?=$arItem['TEXT'];?>
                            </span>
                        </button>
                    <?endif;?>
                    <?if ($bNloMenu) {
                        $bShowChilds = TSolution::nlo($nloMenuCode) && $bShowChilds;
                    }?>
                    <?if ($bShowChilds):?>
                        <div class="header-menu__dropdown-menu dropdown-menu-wrapper dropdown-menu-wrapper--visible <?=$bWideMenu ? '' : 'dropdown-menu-wrapper--woffset';?>">
                            <div class="dropdown-menu-inner rounded-x <?=$arItem['bManyItemsMenu'] ? 'long-menu-items' : '';?>">

                                <?if ($arItem['bManyItemsMenu']):?>
                                    <div class="menu-navigation">
                                        <div class="menu-navigation__sections-wrapper">
                                            <div class="menu-navigation__scroll scrollbar">
                                                <div class="menu-navigation__sections">
                                                    <?foreach ($arItem['CHILD'] as $arChild):?>
                                                        <div class="menu-navigation__sections-item<?= $arChild['SELECTED'] ? ' active' : '';?>">
                                                            <a
                                                                href="<?=$arChild['LINK'];?>"
                                                                class="font_15 font_weight--500 color_dark rounded-x menu-navigation__sections-item-link <?= $arChild['SELECTED'] ? 'menu-navigation__sections-item-link--active' : '';?> <?= $arChild['CHILD'] ? ' menu-navigation__sections-item-dropdown' : '';?>"
                                                            >
                                                                <span class="name"><?=$arChild['TEXT'];?></span>
                                                                <?if ($arChild['CHILD']):?>
                                                                    <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', ' header-menu__dropdown-right-arrow fill-dark-light-block', ['WIDTH' => 7, 'HEIGHT' => 5]);?>
                                                                <?endif;?>
                                                            </a>
                                                        </div>
                                                    <?endforeach;?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="menu-navigation__content">
                                <?endif;?>

                                <?if ($bWideMenu):?>
                                    <div class="header-menu__wide-limiter scrollbar">
                                <?endif;?>

                                <?if ($bRightPart && $bWideMenu && !$arItem['bManyItemsMenu']):?>
                                    <?php
                                    $GLOBALS['rightBannersFilter'] = ['PROPERTY_SHOW_MENU' => $arItem['LINK']];
                                    include 'side_banners.php';
                                    ?>
                                    <?if ($bannersHTML):?>
                                        <div class="header-menu__wide-right-part">
                                            <?=$bannersHTML;?>
                                        </div>
                                    <?endif;?>
                                <?endif;?>

                                <ul class="header-menu__dropdown-menu-inner <?=$bWideMenu && !$arItem['bManyItemsMenu'] ? ' header-menu__dropdown-menu--grids' : '';?>">
                                    <?foreach ($arItem['CHILD'] as $arSubItem):?>
                                        <?$bShowChilds = $arSubItem['CHILD'] && $arParams['MAX_LEVEL'] > 2;?>
                                        <?if ($arItem['bManyItemsMenu']) {?>
                                            <li class="parent-items <?= $arSubItem['SELECTED'] ? 'parent-items--active' : '';?>">
                                                <div class="parent-items__item-title">
                                                    <?if (strlen($arSubItem['URL']) || $menuType != 3):?>
                                                        <a href="<?=$arSubItem['LINK'];?>" class="dark_link stroke-theme-hover">
                                                            <span class="parent-items__item-name font_weight--500 font_20 font_large"><?=$arSubItem['TEXT'];?></span>
                                                            <span class="parent-items__item-arrow rounded-x"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-12', '', ['WIDTH' => 7, 'HEIGHT' => 12]);?></span>
                                                        </a>
                                                    <?else:?>
                                                        <span class="parent-items__item-name font_weight--500 font_20 font_large"><?=$arSubItem['TEXT'];?></span>
                                                    <?endif;?>
                                                </div>
                                                <div class="parent-items__info">
                                                    <?if ($bRightPart && $bWideMenu && $arItem['bManyItemsMenu']):?>
                                                        <?php
                                                        $GLOBALS['rightBannersFilter'] = ['PROPERTY_SHOW_MENU' => $arSubItem['LINK']];
                                                        include 'side_banners.php';
                                                        ?>
                                                        <?if ($bannersHTML):?>
                                                            <div class="header-menu__wide-right-part">
                                                                <?=$bannersHTML;?>
                                                            </div>
                                                        <?endif;?>
                                                    <?endif;?>
                                                    <div class="header-menu__many-items">
                                                        <ul class="header-menu__dropdown-menu-inner  header-menu__dropdown-menu--grids" >
                                                            <?foreach ($arSubItem['CHILD'] as $arSubItem2):?>
                                                                <?php
                                                                $arSubItem = $arSubItem2;
                                                                $bShowChilds = $arSubItem['CHILD'] && $arParams['MAX_LEVEL'] > 3;
                                                                $bIcon = $arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'ICONS' && $arSubItem['PARAMS']['ICON'];
                                                                $bTransparentPicture = array_key_exists('TRANSPARENT_PICTURE', $arSubItem['PARAMS']) && $arSubItem['PARAMS']['TRANSPARENT_PICTURE'] && ($arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'TRANSPARENT_PICTURES' || ($arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'ICONS' && !$bIcon)) ? $arSubItem['PARAMS']['TRANSPARENT_PICTURE'] : false;
                                                                $bPicture = array_key_exists('PICTURE', $arSubItem['PARAMS']) && $arSubItem['PARAMS']['PICTURE'] && ($arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'PICTURES' || ($arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'TRANSPARENT_PICTURES' && !$bTransparentPicture) || ($arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'ICONS' && !$bIcon && !$bTransparentPicture)) ? $arSubItem['PARAMS']['PICTURE'] : false;
                                                                $bHasPicture = $bIcon || $bTransparentPicture || $bPicture;

                                                                include 'wide_menu.php';
                                                                ?>
                                                            <?endforeach;?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                        <?} elseif ($bWideMenu) {
                                            $bIcon = $arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'ICONS' && $arSubItem['PARAMS']['ICON'];
                                            $bTransparentPicture = array_key_exists('TRANSPARENT_PICTURE', $arSubItem['PARAMS']) && $arSubItem['PARAMS']['TRANSPARENT_PICTURE'] && ($arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'TRANSPARENT_PICTURES' || ($arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'ICONS' && !$bIcon)) ? $arSubItem['PARAMS']['TRANSPARENT_PICTURE'] : false;
                                            $bPicture = array_key_exists('PICTURE', $arSubItem['PARAMS']) && $arSubItem['PARAMS']['PICTURE'] && ($arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'PICTURES' || ($arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'TRANSPARENT_PICTURES' && !$bTransparentPicture) || ($arTheme['IMAGES_WIDE_MENU']['VALUE'] == 'ICONS' && !$bIcon && !$bTransparentPicture)) ? $arSubItem['PARAMS']['PICTURE'] : false;
                                            $bHasPicture = $bIcon || $bTransparentPicture || $bPicture;

                                            include 'wide_menu.php';
                                        } else {?>
                                            <li class="header-menu__dropdown-item <?= $bShowChilds ? 'header-menu__dropdown-item--with-dropdown' : '';?> <?=$sCountElementsMenu;?> <?= $arSubItem['SELECTED'] ? 'active' : '';?>">
                                                <a class="font_15 dropdown-menu-item <?= $arSubItem['SELECTED'] ? 'dropdown-menu-item--current' : 'dark_link';?> fill-dark-light-block " href="<?=$arSubItem['LINK'];?>">
                                                    <?=$arSubItem['TEXT'];?>
                                                    <?if ($arSubItem['CHILD'] && count($arSubItem['CHILD']) && $bShowChilds):?>
                                                        <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', ' header-menu__dropdown-right-arrow fill-dark-light-block', ['WIDTH' => 7, 'HEIGHT' => 5]);?>
                                                    <?endif;?>
                                                </a>
                                                <?if ($bShowChilds):?>
                                                    <?$iCountChilds = count($arSubItem['CHILD']);?>
                                                    <div class="header-menu__dropdown-menu header-menu__dropdown-menu--submenu dropdown-menu-wrapper dropdown-menu-wrapper--visible dropdown-menu-wrapper--woffset">
                                                        <ul class="dropdown-menu-inner rounded-x">
                                                            <?foreach ($arSubItem['CHILD'] as $key => $arSubSubItem):?>
                                                                <?$bShowChilds = $arSubSubItem['CHILD'] && $arParams['MAX_LEVEL'] > 3;?>
                                                                <li class="<?= ++$key > $iVisibleItemsMenu ? 'collapsed' : '';?> header-menu__dropdown-item <?= $bShowChilds ? 'header-menu__dropdown-item--with-dropdown' : '';?> <?= $arSubSubItem['SELECTED'] ? 'active' : '';?>">
                                                                    <a class="font_15 fill-dark-light-block dropdown-menu-item <?= $arSubSubItem['SELECTED'] ? 'dropdown-menu-item--current' : 'dark_link';?>" href="<?=$arSubSubItem['LINK'];?>">
                                                                        <?=$arSubSubItem['TEXT'];?>
                                                                        <?if (count($arSubItem['CHILD']) && $bShowChilds):?>
                                                                            <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', ' header-menu__dropdown-right-arrow fill-dark-light-block', ['WIDTH' => 7, 'HEIGHT' => 5]);?>
                                                                        <?endif;?>
                                                                    </a>
                                                                    <?if ($bShowChilds):?>
                                                                        <div class="header-menu__dropdown-menu header-menu__dropdown-menu--submenu  dropdown-menu-wrapper dropdown-menu-wrapper--visible dropdown-menu-wrapper--woffset">
                                                                        <ul class="dropdown-menu-inner rounded-x">
                                                                            <?foreach ($arSubSubItem['CHILD'] as $arSubSubSubItem):?>
                                                                                <li class="header-menu__dropdown-item <?= $arSubSubSubItem['SELECTED'] ? 'active' : '';?>">
                                                                                    <a class="font_15 dropdown-menu-item <?= $arSubSubSubItem['SELECTED'] ? 'dropdown-menu-item--current' : 'dark_link';?>" href="<?=$arSubSubSubItem['LINK'];?>"><?=$arSubSubSubItem['TEXT'];?></a>
                                                                                </li>
                                                                            <?endforeach;?>
                                                                        </ul>

                                                                    <?endif;?>
                                                                </li>
                                                            <?endforeach;?>
                                                            <?if ($iCountChilds > $iVisibleItemsMenu && $bWideMenu):?>
                                                                <li>
                                                                    <span class="colored more_items with_dropdown">
                                                                        <?=Bitrix\Main\Localization\Loc::getMessage('S_MORE_ITEMS');?>
                                                                        <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', '', ['WIDTH' => 7, 'HEIGHT' => 5]);?>
                                                                    </span>
                                                                </li>
                                                            <?endif;?>
                                                        </ul>
                                                    </div>
                                                <?endif;?>
                                            </li>
                                        <?}?>
                                    <?endforeach;?>
                                </ul>

                                <?if ($bWideMenu):?>
                                    </div>
                                <?endif;?>
                                <?if ($arItem['bManyItemsMenu']):?>
                                        </div>
                                    </div>
                                <?endif;?>
                            </div>
                        </div>
                    <?endif;?>
                    <?php
                    if ($bNloMenu) {
                        TSolution::nlo($nloMenuCode);
                    }
                    ?>
                </div>
                <?php
                ++$counter;

                if ($bOnlyCatalog) {
                    break;
                }
                ?>
            <?endforeach;?>

            <?if (!$bOnlyCatalog):?>
                <div class="header-menu__item header-menu__item--more-items unvisible">
                    <div class="header-menu__link banner-light-icon-fill fill-dark-light-block light-opacity-hover">
                        <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/header_icons.svg#dots-15-3', '', ['WIDTH' => 15, 'HEIGHT' => 3]);?>
                    </div>
                    <div class="header-menu__dropdown-menu dropdown-menu-wrapper dropdown-menu-wrapper--visible dropdown-menu-wrapper--woffset">
                        <ul class="header-menu__more-items-list dropdown-menu-inner rounded-x"></ul>
                    </div>
                </div>
            <?endif;?>
        </div>
    </div>
    <script data-skip-moving="true">
        if (typeof topMenuAction !== 'function'){
            function topMenuAction() {
                //CheckTopMenuPadding();
                //CheckTopMenuOncePadding();
                if (typeof CheckTopMenuDotted !== 'function'){
                    let timerID = setInterval(function(){
                        if (typeof CheckTopMenuDotted === 'function'){
                            CheckTopMenuDotted();
                            clearInterval(timerID);
                        }
                    }, 100);
                } else {
                    CheckTopMenuDotted();
                }
            }
        }

    </script>
<?endif;?>
