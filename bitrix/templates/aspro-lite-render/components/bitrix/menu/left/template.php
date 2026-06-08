<?if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();?>
<?$this->setFrameMode(true);?>
<?use \Bitrix\Main\Localization\Loc;?>
<?
if(!function_exists("ShowSubItemsLeft")){
    function ShowSubItemsLeft($arItem, $bCatalog = false){
        $selectedKey = TSolution\Functions::checkSelected($arItem["CHILD"]);
        $filterSelect = $bCatalog &&  $selectedKey!== false && $arItem['CHILD'][$selectedKey]['CHILD'];
        ?>
        <?if($arItem["CHILD"]):?>
            <?$noMoreSubMenuOnThisDepth = false;?>
            <div class="submenu-wrapper <?=$filterSelect ? 'submenu-wrapper--filtred' : ''?>">
                <ul class="submenu">
                    <?foreach($arItem["CHILD"] as $arSubItem):?>
                        <?
                        $activeLink = $bCatalog && TSolution\Functions::CheckSelected($arSubItem["CHILD"])!== false;
                        ?>
                        <?if(!$filterSelect || $arSubItem["SELECTED"]):?>
                            <li class="<?=($arSubItem["SELECTED"] ? "active opened" : "")?><?=($arSubItem["CHILD"] ? " child" : "")?>">
                                <span class="bg-opacity-theme-parent-hover link-wrapper font_short fill-theme-parent-all fill-dark-light <?=($activeLink && $arSubItem["SELECTED"]) ? "arrow-left-icon" : "";?>">
                                    <?if(($activeLink && $arSubItem["SELECTED"])):?>
                                        <?=\TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#down-7-5', ' dropdown-select__icon-down', ['WIDTH' => 7,'HEIGHT' => 5]);?>
                                    <?endif;?>
                                    <a href="<?=$arSubItem["LINK"]?>" class="dark_link sublink rounded-x <?=($arSubItem["CHILD"] ? " sublink--child" : "")?> <?=(!$activeLink && $arSubItem["SELECTED"] ? " link--active" : "")?>">
                                        <?=$arSubItem["TEXT"]?>
                                    </a>
                                </span>
                                <?if(!$noMoreSubMenuOnThisDepth):?>
                                    <?ShowSubItemsLeft($arSubItem, $bCatalog);?>
                                <?endif;?>
                            </li>
                        <?endif;?>
                        <?$noMoreSubMenuOnThisDepth = (boolean) TSolution::isChildsSelected($arSubItem["CHILD"]);?>
                    <?endforeach;?>
                </ul>
            </div>
        <?endif;?>
        <?
    }
}
?>
<?if($arResult):?>
    <?$bCatalog = TSolution::IsCatalogPage();?>
    <aside class="sidebar visible-by-block-presence__condition">
        <?if ($bCatalog):?>
            <div class="slide-block">
                <div class="slide-block__head title-menu stroke-theme-parent-all color_222 dropdown-select__title fill-dark-light <?=($_COOKIE['MENU_CLOSED'] == 'Y' ? ' closed' : ' opened');?>" data-id="MENU">
                    <?=Loc::getMessage('CATALOG_LINK');?>
                    <?=\TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#down-7-5', ' dropdown-select__icon-down', ['WIDTH' => 7,'HEIGHT' => 5]);?>
                </div>
                <div class="slide-block__body">
            <?endif;?>

        <ul class="nav nav-list side-menu">
            <?
                $selectedKey = TSolution\Functions::checkSelected($arResult);
                $filterSelect = $bCatalog &&  $selectedKey!== false && $arResult[$selectedKey]['CHILD'];
            ?>
            <?foreach($arResult as $arItem):?>
                <?
                $activeLink = $bCatalog && TSolution\Functions::CheckSelected($arItem["CHILD"])!== false;
                ?>
                <?if( !$filterSelect || $arItem["SELECTED"] ):?>
                    <li class="<?=($arItem["SELECTED"] ? "active opened" : "")?> <?=($arItem["CHILD"] && !isset($arItem["NO_PARENT"]) ? "child" : "")?>">
                        <span class="bg-opacity-theme-parent-hover link-wrapper font_short fill-theme-parent-all fill-dark-light <?=($arItem["SELECTED"] && $activeLink ) ? "arrow-left-icon" : "";?>">
                            <?if( strpos($arItem["LINK"] ,'?logout=yes') !== false ){
                                $arItem["LINK"].= '&'.bitrix_sessid_get();
                            }?>
                            <?if($arItem["SELECTED"] && $activeLink ):?>
                                <?=\TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#down-7-5', ' dropdown-select__icon-down', ['WIDTH' => 7,'HEIGHT' => 5]);?>
                            <?endif;?>
                            <a href="<?=$arItem["LINK"]?>" class="dark_link top-level-link rounded-x <?=($arItem["SELECTED"] && !$activeLink  ? " link--active" : "")?> link-with-flag"><?=(isset($arItem["PARAMS"]["BLOCK"]) && $arItem["PARAMS"]["BLOCK"] ? $arItem["PARAMS"]["BLOCK"] : "");?>
                                <?=$arItem["TEXT"]?>
                            </a>
                        </span>
                        <?ShowSubItemsLeft($arItem, $bCatalog);?>
                    </li>
                <?endif;?>
            <?endforeach;?>
        </ul>
        <?if ($bCatalog):?>
            </div>
            </div>
        <?endif;?>
    </aside>
<?endif;?>
