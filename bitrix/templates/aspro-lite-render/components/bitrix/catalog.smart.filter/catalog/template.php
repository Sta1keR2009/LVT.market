<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>
<?use \Bitrix\Main\Localization\Loc;?>
<?if($arResult["ITEMS"]){?>
    <?include_once("function.php");?>
    <?$bActiveFilter = TSolution\Functions::checkActiveFilterPage([
        'SEF_URL' => $arParams["SEF_RULE_FILTER"],
        'GLOBAL_FILTER' => $arParams['FILTER_NAME']
    ]);?>
    <div class="bx_filter bx_filter_<?=strtolower($arParams["FILTER_VIEW_MODE"]);?> catalog swipeignore visible-by-block-presence__condition">
        <form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get" class="smartfilter">
            <input type="hidden" name="del_url" id="del_url" value="<?echo $arResult["SEF_DEL_FILTER_URL"]?>" />
            <?foreach($arResult["HIDDEN"] as $arItem):?>
            <input type="hidden" name="<?echo $arItem["CONTROL_NAME"]?>" id="<?echo $arItem["CONTROL_ID"]?>" value="<?echo $arItem["HTML_VALUE"]?>" />
            <?endforeach;?>
            <div class="bx_filter_parameters_box title color_222 font_12 text-upper font-bold visible-991">
                <div class="bx_filter_parameters_box_title filter_title <?=($bActiveFilter && $bActiveFilter[1] != 'clear' ? 'active-filter' : '')?>">
                    <?=TSolution::showIconSvg("catalog fill-dark-light", SITE_TEMPLATE_PATH.'/images/svg/catalog/filter_gears.svg', '', '', true, false);?>
                    <span><?=Loc::getMessage("CT_BCSF_FILTER_TITLE");?></span>
                    <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/header_icons.svg#close-14-14', 'svg-close close-icons fill-theme-hover fill-use-svg-999', ['WIDTH' => 14, 'HEIGHT' => 14]);?>
                </div>
            </div>
            <div class="bx_filter_parameters">
                <?$isFilter=false;
                //prices
                foreach($arResult["ITEMS"] as $key=>$arItem){
                    $key = $arItem["ENCODED_ID"];
                    if(isset($arItem["PRICE"])):
                        if ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
                            continue;
                        ?>

                            <div class="bx_filter_parameters_box dropdown-select active">
                                <span class="bx_filter_container_modef" data-f="<?=Loc::getMessage('CT_BCSF_SET_FILTER')?>"></span>
                                <div class="bx_filter_parameters_box_title dropdown-select__title fill-dark-light" ><?=(count($arParams['PRICE_CODE']) > 1 ? $arItem["NAME"] : Loc::getMessage("PRICE"));?><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', 'dropdown-select__icon-down', ['WIDTH' => 7, 'HEIGHT' => 5]);?></div>
                                <div class="bx_filter_block">
                                    <div class="bx_filter_parameters_box_container">
                                        <div class="wrapp_all_inputs wrap_md">
                                            <?$isConvert=true;
                                            $price1 = $arItem["VALUES"]["MIN"]["VALUE"];
                                            $price2 = $arItem["VALUES"]["MIN"]["VALUE"] + round(($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])/4);
                                            $price3 = $arItem["VALUES"]["MIN"]["VALUE"] + round(($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])/2);
                                            $price4 = $arItem["VALUES"]["MIN"]["VALUE"] + round((($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])*3)/4);
                                            $price5 = $arItem["VALUES"]["MAX"]["VALUE"];
                                            if($isConvert){
                                                $price1 =number_format($price1, 0, ".", " ");
                                                $price2 =number_format($price2, 0, ".", " ");
                                                $price3 =number_format($price3, 0, ".", " ");
                                                $price4 =number_format($price4, 0, ".", " ");
                                                $price5 =number_format($price5, 0, ".", " ");
                                            }?>
                                            <div class="wrapp_change_inputs iblock">
                                                <div class="flexbox flexbox--row form-control">
                                                    <input
                                                        class="min-price"
                                                        type="text"
                                                        name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
                                                        id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
                                                        value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
                                                        size="5"
                                                        placeholder="<?echo $price1;?>"
                                                        onkeyup="smartFilter.keyup(this)"
                                                        autocomplete="off"
                                                    />
                                                    <input
                                                        class="max-price"
                                                        type="text"
                                                        name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
                                                        id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
                                                        value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
                                                        size="5"
                                                        placeholder="<?echo $price5;?>"
                                                        onkeyup="smartFilter.keyup(this)"
                                                        autocomplete="off"
                                                    />
                                                </div>
                                            </div>
                                            <div class="wrapp_slider iblock">
                                                <div class="bx_ui_slider_track" id="drag_track_<?=$key?>">

                                                    <div class="bx_ui_slider_part first p1"><span><?=$price1?></span></div>
                                                    <div class="bx_ui_slider_part p2"><span><?=$price2?></span></div>
                                                    <div class="bx_ui_slider_part p3"><span><?=$price3?></span></div>
                                                    <div class="bx_ui_slider_part p4"><span><?=$price4?></span></div>
                                                    <div class="bx_ui_slider_part last p5"><span><?=$price5?></span></div>

                                                    <div class="bx_ui_slider_pricebar_VD" style="left: 0;right: 0;" id="colorUnavailableActive_<?=$key?>"></div>
                                                    <div class="bx_ui_slider_pricebar_VN" style="left: 0;right: 0;" id="colorAvailableInactive_<?=$key?>"></div>
                                                    <div class="bx_ui_slider_pricebar_V"  style="left: 0;right: 0;" id="colorAvailableActive_<?=$key?>"></div>
                                                    <div class="bx_ui_slider_range" id="drag_tracker_<?=$key?>"  style="left: 0%; right: 0%;">
                                                        <a class="bx_ui_slider_handle left"  style="left:0;" href="javascript:void(0)" id="left_slider_<?=$key?>"></a>
                                                        <a class="bx_ui_slider_handle right" style="right:0;" href="javascript:void(0)" id="right_slider_<?=$key?>"></a>
                                                    </div>
                                                </div>
                                                <div style="opacity: 0;height: 1px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?
                            $isFilter=true;
                            $precision = 0;
                            /*if (Bitrix\Main\Loader::includeModule("currency"))
                            {
                                $res = CCurrencyLang::GetFormatDescription($arItem["VALUES"]["MIN"]["CURRENCY"]);
                                $precision = $res['DECIMALS'];
                            }*/
                            $arJsParams = array(
                                "leftSlider" => 'left_slider_'.$key,
                                "rightSlider" => 'right_slider_'.$key,
                                "tracker" => "drag_tracker_".$key,
                                "trackerWrap" => "drag_track_".$key,
                                "minInputId" => $arItem["VALUES"]["MIN"]["CONTROL_ID"],
                                "maxInputId" => $arItem["VALUES"]["MAX"]["CONTROL_ID"],
                                "minPrice" => $arItem["VALUES"]["MIN"]["VALUE"],
                                "maxPrice" => $arItem["VALUES"]["MAX"]["VALUE"],
                                "curMinPrice" => $arItem["VALUES"]["MIN"]["HTML_VALUE"],
                                "curMaxPrice" => $arItem["VALUES"]["MAX"]["HTML_VALUE"],
                                "fltMinPrice" => intval($arItem["VALUES"]["MIN"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MIN"]["FILTERED_VALUE"] : $arItem["VALUES"]["MIN"]["VALUE"] ,
                                "fltMaxPrice" => intval($arItem["VALUES"]["MAX"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MAX"]["FILTERED_VALUE"] : $arItem["VALUES"]["MAX"]["VALUE"],
                                "precision" => $precision,
                                "colorUnavailableActive" => 'colorUnavailableActive_'.$key,
                                "colorAvailableActive" => 'colorAvailableActive_'.$key,
                                "colorAvailableInactive" => 'colorAvailableInactive_'.$key,
                            );
                            ?>
                            <script type="text/javascript">
                                BX.ready(function(){
                                    window['trackBar<?=$key?>'] = new BX.Iblock.SmartFilter(<?=CUtil::PhpToJSObject($arJsParams)?>);
                                });
                            </script>

                    <?endif;?>
                <?}
                //not prices
                ?>

                <?$isFilter=true;?>
                <?if(isset($arResult["ITEMS"]["TOP_BLOCK"])){?>
                    </div>
                    <div class="row line_row">
                        <?
                            $countSubElement=count($arItem);
                            $colSub=12;
                            if($countSubElement>2){
                                $colSub=4;
                            }elseif($countSubElement>1 && $countSubElement<3){
                                $colSub=6;
                            }
                            foreach($arItem as $keySub=>$arSubItem){?>
                                <?ShowFilterItemExt($keySub, $arSubItem, $colSub, $arParams);?>
                            <?}
                        ?>
                    </div>
                    <hr class="filter_hor" />
                    <div class="row">
                <?}?>
                <?foreach($arResult["ITEMS"] as $key=>$arItem){
                    if($key!="TOP_BLOCK"){?>
                        <?ShowFilterItemExt($key, $arItem, $arParams);?>
                    <?}?>
                <?}?>
            </div>
            <?if ($isFilter):?>
                <div class="bx_filter_button_box active">
                    <div class="bx_filter_block">
                        <div class="bx_filter_parameters_box_container flexbox flexbox--direction-row">
                            <?if($arParams["FILTER_VIEW_MODE"] == "VERTICAL"):?>
                                <div class="bx_filter_popup_result right" id="modef_mobile" <?if(!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"';?>>
                                    <?echo GetMessage("CT_BCSF_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num_mobile1">'.intval($arResult["ELEMENT_COUNT"]).'</span>'));?>
                                    <a href="<?echo $arResult["FILTER_URL"]?>" class="button white_bg"><?echo GetMessage("CT_BCSF_FILTER_SHOW")?><span id="modef_num_mobile"><?=intval($arResult["ELEMENT_COUNT"]);?></span></a>
                                </div>
                            <?endif?>
                            <div class="bx_filter_popup_result right font_14" id="modef" <?if(!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"';?>>
                                <!-- noindex -->
                                <a href="<?echo $arResult["FORM_ACTION"]?>" class="popup-result-link btn btn-sm btn-default" rel="nofollow"><?echo GetMessage("CT_BCSF_FILTER_SHOW")?> <span id="modef_num"><?=intval($arResult["ELEMENT_COUNT"]);?></span></a>
                                <!-- /noindex -->
                            </div>
                            <input class="bx_filter_search_button btn btn-default hidden" type="submit" id="set_filter" name="set_filter"  value="<?=GetMessage("CT_BCSF_SET_FILTER")?>" />
                            <button class="bx_filter_search_reset btn btn-transparent-bg btn-default <?=($bActiveFilter && $bActiveFilter[1] != 'clear' ? '' : ' hidden');?>"" type="reset" id="del_filter" name="del_filter">
                                <?=GetMessage("CT_BCSF_DEL_FILTER")?>
                            </button>
                        </div>
                    </div>
                </div>
            <?endif;?>
        </form>
    </div>
    <script>
        var smartFilter = new JCSmartFilter('<?echo CUtil::JSEscape($arParams["FORM_URL"])?>', '<?=$arParams["FILTER_VIEW_MODE"];?>', <?=CUtil::PhpToJSObject($arResult["JS_FILTER_PARAMS"])?>);
        <?if(!$isFilter){?>$('.bx_filter_vertical').remove();<?}?>
        function checkVisibleProps(){
            $('.bx_filter_parameters_box').each(function(){
                if ($.cookie("LITE_filter_prop_"+$(this).data('prop_code'))==2) {
                    $(this).removeClass('active');
                    $(this).find('.bx_filter_block').hide();
                } else if($.cookie("LITE_filter_prop_"+$(this).data('prop_code'))==3) {
                    $(this).addClass('active');
                    $(this).find('.bx_filter_block').show();
                }
            })
        }
        $(document).ready(function(){
            $(document).on('click', '.bx_filter_parameters_box_title:not(.one-value)', function(e){
                var active=2;
                if ($(this).closest(".bx_filter_parameters_box").hasClass("active")) {
                    $(this).next(".bx_filter_block").slideUp(100);
                } else {
                    $(this).next(".bx_filter_block").slideDown(200);
                }
                $(this).closest(".bx_filter_parameters_box").toggleClass("active");

                if ($(this).closest(".bx_filter_parameters_box").hasClass("active")) {
                    active=3;
                }

                $.cookie.json = true;
                $.cookie("LITE_filter_prop_"+$(this).closest(".bx_filter_parameters_box").data('prop_code'), active,{
                    path: '/',
                    domain: '',
                    expires: 360
                });

                e.preventDefault();
            });
            checkVisibleProps()
        })
    </script>
<?}?>
