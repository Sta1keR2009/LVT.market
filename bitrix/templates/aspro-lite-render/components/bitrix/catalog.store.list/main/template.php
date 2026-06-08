<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

$blockClasses = 'contacts-list--items-close';
$gridClass = 'grid-list grid-list--items-1 grid-list--no-gap';
$itemWrapperClasses = 'grid-list__item stroke-theme-parent-all colored_theme_hover_bg-block grid-list-border-outer';
$itemClasses = 'height-100 flexbox flexbox--direction-row animate-arrow-hover color-theme-parent-all bordered outer-rounded-x shadow-hovered shadow-no-border-hovered';
$imageWrapperClasses = '';
$imageClasses = 'rounded-x';
?>
<?if(strlen($arResult["ERROR_MESSAGE"])>0) ShowError($arResult["ERROR_MESSAGE"]);?>
<div class="contacts-list <?=$blockClasses?> <?=$templateName?>-template">
    <div class="<?=$gridClass?>">
        <?foreach ($arResult['STORES'] as $arStore):?>
            <?
            // detail url
            $detailUrl = $arStore['URL'];
            $bDetailLink = true;

            // preview picture
            $bImage = isset($arStore['DETAIL_IMG']) && strlen($arStore['DETAIL_IMG']['SRC']);
            $imageSrc = ($bImage ? $arStore['DETAIL_IMG']['SRC'] : false);

            // address
            $address = $arStore['ADDRESS'];

            $bHasCoords = ($arStore['GPS_N'] && $arStore['GPS_S']);

            // clear fix for items without coords
            $htmlCoord = '';
            ?>
            <div class="contacts-list__wrapper <?=$itemWrapperClasses?>">
                <div class="contacts-list__item <?=$itemClasses?><?=($imageSrc ? '' : ' contacts-list__item-without-image')?>">
                    <?if($bDetailLink):?>
                        <a class="arrow-all arrow-all--wide stroke-theme-target" href="<?=$detailUrl?>">
                            <?=TSolution::showIconSvg(' arrow-all__item-arrow', SITE_TEMPLATE_PATH.'/images/svg/Arrow_lg.svg');?>
                            <div class="arrow-all__item-line colored_theme_hover_bg-el"></div>
                        </a>
                    <?endif;?>
                    
                    <?if($imageSrc):?>
                        <div class="contacts-list__item-image-wrapper <?=$imageWrapperClasses?>">
                            <?if($bDetailLink):?>
                                <a class="contacts-list__item-link" href="<?=$detailUrl?>">
                            <?else:?>
                                <span class="contacts-list__item-link">
                            <?endif;?>
                                <span class="contacts-list__item-image <?=$imageClasses?>" style="background-image: url(<?=$imageSrc?>);"></span>
                            <?if($bDetailLink):?>
                                </a>
                            <?else:?>
                                </span>
                            <?endif;?>
                        </div>
                    <?endif;?>

                    <div class="contacts-list__item-text-wrapper flex-1">
                        <div class="contacts-list__item-text-top-part flexbox flexbox--direction-row">
                            <div class="contacts-list__item-col contacts-list__item-col--left">
                                <?// element name?>
                                <div class="contacts-list__item-title switcher-title font_16">
                                    <?if($bDetailLink):?>
                                        <a class="dark_link color-theme-target" href="<?=$detailUrl?>"><?=$address?></a>
                                    <?else:?>
                                        <span class="color_222"><?=$address?></span>
                                    <?endif;?>
                                </div>

                                <?if(
                                    $arParams['USE_MAP'] === 'Y' &&
                                    $bHasCoords
                                ):?>
                                    <?ob_start();?>
                                    <span class="text_wrap font_14 color-theme" data-coordinates="<?=($bHasCoords ? $arStore['GPS_N'].','.$arStore['GPS_S'] : '')?>">
                                        <?=TSolution::showIconSvg('on_map fill-theme', SITE_TEMPLATE_PATH.'/images/svg/show_on_map.svg');?>
                                        <span class="text dotted"><?=GetMessage('SHOW_ON_MAP')?></span>
                                    </span>
                                    <?$htmlCoord = trim(ob_get_clean());?>

                                    <?if($htmlCoord):?>
                                        <div class="contacts-list__item-coord show_on_map contacts-list__item--hidden-f1300"><?=$htmlCoord?></div>
                                    <?endif;?>
                                <?endif;?>

                                <?if($arStore['METRO']):?>
                                    <div class="contacts-list__item-metro">
                                        <?foreach((array)$arStore['METRO'] as $metro):?>
                                            <div class="contacts-list__item-metro__value"><?=TSolution::showSpriteIconSvg(
                                                SITE_TEMPLATE_PATH.'/images/svg/map_icons.svg#metro-14-10',
                                                '',
                                                [
                                                    'WIDTH' => 14,
                                                    'HEIGHT' => 10,
                                                ]
                                            );?><span class="font_14 color_666"><?=$metro?></span></div>
                                        <?endforeach;?>
                                    </div>
                                <?endif;?>

                                <?if(
                                    $arParams['SCHEDULE'] === 'Y' &&
                                    $arStore['SCHEDULE']
                                ):?>
                                    <div class="contacts-list__item-schedule"><?=TSolution::showIconSvg('schedule', SITE_TEMPLATE_PATH.'/images/svg/Schedule.svg');?><span class="font_14 color_666"><?=$arStore['SCHEDULE']?></span></div>
                                <?endif;?>

                                <?ob_start();?>
                                <div class="line-block line-block--5-6-vertical line-block--align-normal flexbox--wrap flexbox--justify-beetwen">
                                    <?if(
                                        $arParams['PHONE'] === 'Y' &&
                                        $arStore['PHONE']
                                    ):?>
                                        <div class="contacts-list__item-phones line-block__item">
                                            <?foreach((array)$arStore['PHONE'] as $phone):?>
                                                <div class="contacts-list__item-phone">
                                                    <a class="dark_link" href="tel:<?=str_replace(array(' ', ',', '-', '(', ')'), '', $phone)?>"><?=$phone?></a>
                                                </div>
                                            <?endforeach;?>
                                        </div>
                                    <?endif;?>

                                    <?if($arStore['EMAIL']):?>
                                        <div class="contacts-list__item-emails line-block__item">
                                            <?foreach((array)$arStore['EMAIL'] as $email):?>
                                                <div class="contacts-list__item-email">
                                                    <a class="dark_link" href="mailto:<?=$email?>"><?=$email?></a>
                                                </div>
                                            <?endforeach;?>
                                        </div>
                                    <?endif;?>
                                </div>
                                <?$htmlInfo = trim(ob_get_clean());?>

                                <?if($htmlInfo):?>
                                    <div class="contacts-list__item-info contacts-list__item--hidden-f1300"><?=$htmlInfo?></div>
                                <?endif;?>
                            </div>

                            <div class="contacts-list__item-col contacts-list__item-col--right contacts-list__item--hidden-t1299">
                                <?if($htmlInfo):?>
                                    <div class="contacts-list__item-info"><?=$htmlInfo?></div>
                                <?endif;?>

                                <?if($htmlCoord):?>
                                    <div class="contacts-list__item-coord show_on_map"><?=$htmlCoord?></div>
                                <?endif;?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?endforeach;?>
    </div>
</div>