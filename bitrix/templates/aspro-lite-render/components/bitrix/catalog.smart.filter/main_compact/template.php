<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
use \Bitrix\Main\Localization\Loc;
if($arResult["ITEMS"]){?>
	<?global $filter_exists;?>
	<?$filter_exists = "filter_exists";?>
	<?$bActiveFilter = TSolution\Functions::checkActiveFilterPage([
		'SEF_URL' => $arParams["SEF_RULE_FILTER"],
		'GLOBAL_FILTER' => $arParams['FILTER_NAME']
	]);?>
	<div class="filter-compact-block swipeignore">
		<div class="bx_filter bx_filter_vertical compact swipeignore <?=(isset($arResult['EMPTY_ITEMS']) ? 'empty-items': '');?>">
			<div class="bx_filter_section clearfix">
				<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get" class="smartfilter">

					<div class="bx_filter_parameters_box title color_222 font_12 text-upper font-bold visible-767">
						<div class="bx_filter_parameters_box_title filter_title <?=($bActiveFilter && $bActiveFilter[1] != 'clear' ? 'active-filter' : '')?>">
							<?=TSolution::showIconSvg("catalog fill-dark-light", SITE_TEMPLATE_PATH.'/images/svg/catalog/filter_gears.svg', '', '', true, false);?>
							<span><?=Loc::getMessage("FILTER_TITLE_COMPACT");?></span>
							<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/header_icons.svg#close-14-14', 'svg-close close-icons fill-theme-hover fill-use-svg-999', ['WIDTH' => 14, 'HEIGHT' => 14]);?>
						</div>
					</div>

					<div class="bx_filter_parameters compact__parameters line-block line-block--gap line-block--gap-8 line-block--flex-wrap">
						<?=$arParams['~SORT_HTML'];?>
						<input type="hidden" name="del_url" id="del_url" value="<?echo str_replace('/filter/clear/apply/','/',$arResult["SEF_DEL_FILTER_URL"]);?>" />
						<?foreach($arResult["HIDDEN"] as $arItem):?>
							<input type="hidden" name="<?echo $arItem["CONTROL_NAME"]?>" id="<?echo $arItem["CONTROL_ID"]?>" value="<?echo $arItem["HTML_VALUE"]?>" />
						<?endforeach;
						$isFilter = $titlePrice = false;
						$numVisiblePropValues = 2;

						//ASPRO_FILTER_SORT
						foreach($arResult["ITEMS"] as $key => $arItem){
							if (isset($arItem["PRICE"])) {
								if ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] > 0) {
									$titlePrice = true;
								}
							}
						}

						//prices?>
						<?if ($titlePrice):?>
							<div class="bx_filter_parameters_box prices<?=(isset($arResult['PRICE_SET']) && $arResult['PRICE_SET'] == 'Y' ? ' set' : '');?> dropdown-select dropdown-select--with-dropdown" data-visible_by_class="#mobilefilter-overlay">
								<span data-f="<?=Loc::getMessage('CT_BCSF_SET_FILTER')?>" data-fi="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TI')?>" data-fr="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TR')?>" data-frm="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TRM')?>" class="bx_filter_container_modef"></span>
								<div class="bx_filter_parameters_box_title title dropdown-select__title font_14 font_large fill-dark-light bordered rounded-x shadow-hovered shadow-no-border-hovered">
									<span><?=Loc::getMessage("PRICE");?></span>
									<span class="delete_filter colored_more_theme_bg2_hover">
										<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/catalog/item_icons.svg#close-8-8', '', ['WIDTH' => 8, 'HEIGHT' => 8]);?>
									</span>
									<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', 'dropdown-select__icon-down', ['WIDTH' => 7, 'HEIGHT' => 5]);?>
								</div>
								<div class="bx_filter_block dropdown-select__list dropdown-menu-wrapper">
									<div class="dropdown-menu-inner rounded-x filter_values">
										<?foreach($arResult["ITEMS"] as $key=>$arItem)
										{
											$key = $arItem["ENCODED_ID"];
											if(isset($arItem["PRICE"])):
												if ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
													continue;
												?>
												<div class="price_block swipeignore">
													<div class="bx_filter_parameters_box_title rounded3 prices"><?=(count($arParams['PRICE_CODE']) > 1 ? $arItem["NAME"] : Loc::getMessage("PRICE"));?></div>
													<div class="bx_filter_parameters_box_container numbers">
														<div class="wrapp_all_inputs wrap_md">
															<?
															$isConvert=false;
															if($arParams["CONVERT_CURRENCY"]=="Y"){
																$isConvert=true;
															}
															$price1 = $arItem["VALUES"]["MIN"]["VALUE"];
															$price2 = $arItem["VALUES"]["MIN"]["VALUE"] + round(($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])/4);
															$price3 = $arItem["VALUES"]["MIN"]["VALUE"] + round(($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])/2);
															$price4 = $arItem["VALUES"]["MIN"]["VALUE"] + round((($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])*3)/4);
															$price5 = $arItem["VALUES"]["MAX"]["VALUE"];

															if($isConvert){
																$price1 =SaleFormatCurrency($price1, $arParams["CURRENCY_ID"], true);
																$price2 =SaleFormatCurrency($price2, $arParams["CURRENCY_ID"], true);
																$price3 =SaleFormatCurrency($price3, $arParams["CURRENCY_ID"], true);
																$price4 =SaleFormatCurrency($price4, $arParams["CURRENCY_ID"], true);
																$price5 =SaleFormatCurrency($price5, $arParams["CURRENCY_ID"], true);
															}
															?>
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
													<?
													$isFilter=true;
													$precision = 0;
													/*if (Bitrix\Main\Loader::includeModule("currency")) {
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
															if(typeof window['trackBarOptions'] === 'undefined'){
																window['trackBarOptions'] = {}
															}
															window['trackBarOptions']['<?=$key?>'] = <?=CUtil::PhpToJSObject($arJsParams)?>;
															window['trackBar<?=$key?>'] = new BX.Iblock.SmartFilter(window['trackBarOptions']['<?=$key?>']);
														});
													</script>
												</div>
											<?endif;
										}?>
										<div class="bx_filter_button_box active">
											<span data-f="<?=Loc::getMessage('CT_BCSF_SET_FILTER')?>" data-fi="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TI')?>" data-fr="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TR')?>" data-frm="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TRM')?>" class="bx_filter_container_modef btn btn-default btn-sm btn-wide"><?=Loc::getMessage("CT_BCSF_SET_FILTER")?><span></span></span>
										</div>
									</div>
								</div>
							</div>
						<?endif;?>

						<?//not prices
						foreach($arResult["ITEMS"] as $key=>$arItem)
						{
							if (empty($arItem["VALUES"]) || isset($arItem["PRICE"])) continue;

							if (
								$arItem["DISPLAY_TYPE"] == "A"
								&& (
									$arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0
								)
							)
								continue;
							$isFilter=true;

							if(!$arItem["FILTER_HINT"]){
								$prop = CIBlockProperty::GetByID($arItem["ID"], $arParams["IBLOCK_ID"])->GetNext();
								$arItem["FILTER_HINT"]=$prop["HINT"];
							}

							$bExpandedProp = $arItem["DISPLAY_EXPANDED"] == "Y" ?: false;

							$bWithFilterHint = strlen($arItem['FILTER_HINT']) && $arParams['SHOW_HINTS'] == 'Y' && strpos( $arItem["FILTER_HINT"],'line')===false;
							?>
							<div class="bx_filter_parameters_box prop_type_<?=$arItem["PROPERTY_TYPE"];?><?=(isset($arItem['PROPERTY_SET']) && $arItem['PROPERTY_SET'] == 'Y' ? ' opened' : '');?> dropdown-select <?= $arItem['IS_PROP_INLINE'] ? '' : 'dropdown-select--with-dropdown'; ?>" data-prop_code="<?=strtolower($arItem["CODE"]);?>" data-check_prop_inline="<?= $arItem['IS_PROP_INLINE'] ? 'true' : 'false'; ?>" data-property_id="<?=$arItem["ID"]?>" data-visible_by_class="#mobilefilter-overlay">
								<span data-f="<?=Loc::getMessage('CT_BCSF_SET_FILTER')?>" data-fi="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TI')?>" data-fr="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TR')?>" data-frm="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TRM')?>" class="bx_filter_container_modef"></span>
								<?if( !$arItem['IS_PROP_INLINE'] ):?>
									<div class="bx_filter_parameters_box_title title font_14 dropdown-select__title font_14 font_large fill-dark-light bordered rounded-x shadow-hovered shadow-no-border-hovered" data-expanded-prop="<?=$bExpandedProp?>">
										<span class="text">
											<span><?=( $arItem["CODE"] == "MINIMUM_PRICE" ? Loc::getMessage("PRICE") : $arItem["NAME"] );?></span>
											<span class="count_selected"><?=(isset($arItem['COUNT_SELECTED']) && $arItem['COUNT_SELECTED'] ? ': '.$arItem['COUNT_SELECTED'] : '');?></span>
										</span>
										<span class="delete_filter colored_more_theme_bg2_hover" title="<?=Loc::getMessage("CLEAR_VALUE")?>">
											<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/catalog/item_icons.svg#close-8-8', '', ['WIDTH' => 8, 'HEIGHT' => 8]);?>
										</span>

										<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', 'dropdown-select__icon-down', ['WIDTH' => 7, 'HEIGHT' => 5]);?>
									</div>
								<?endif;?>
								<?$style = $mobileOpened = "";
								if( $arItem['IS_PROP_INLINE'] ){
									$style = "style='display:block;'";

								}if($arItem["DISPLAY_EXPANDED"] === "Y") {
									$mobileOpened = 'mobile-lineblock-opened';
								}

								?>
								<div class="<?=$mobileOpened?> bx_filter_block<?= $arItem['IS_PROP_INLINE'] ? " limited_block" : ' dropdown-select__list dropdown-menu-wrapper';?> <?= $bWithFilterHint ? " bx_filter_block--whint" : '';?>" <?= $style; ?>>
									<div class="dropdown-menu-inner rounded-x <?=(!$arItem['IS_PROP_INLINE'] ? 'filter_values' : '');?>">
										<div class="bx_filter_parameters_box_container <?=(in_array($arItem["DISPLAY_TYPE"], ["G", "H"]) ? 'scrolled scrollbar' : '');?>">
											<?
											$arCur = current($arItem["VALUES"]);
											switch ($arItem["DISPLAY_TYPE"]){
												case "A"://NUMBERS_WITH_SLIDER
													?>
													<?$isConvert=false;
													if($arItem["CODE"] == "MINIMUM_PRICE" && $arParams["CONVERT_CURRENCY"]=="Y"){
														$isConvert=true;
													}
													$value1 = $arItem["VALUES"]["MIN"]["VALUE"];
													$value2 = $arItem["VALUES"]["MIN"]["VALUE"] + round(($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])/4);
													$value3 = $arItem["VALUES"]["MIN"]["VALUE"] + round(($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])/2);
													$value4 = $arItem["VALUES"]["MIN"]["VALUE"] + round((($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])*3)/4);
													$value5 = $arItem["VALUES"]["MAX"]["VALUE"];
													if($isConvert){
														$value1 =SaleFormatCurrency($value1, $arParams["CURRENCY_ID"], true);
														$value2 =SaleFormatCurrency($value2, $arParams["CURRENCY_ID"], true);
														$value3 =SaleFormatCurrency($value3, $arParams["CURRENCY_ID"], true);
														$value4 =SaleFormatCurrency($value4, $arParams["CURRENCY_ID"], true);
														$value5 =SaleFormatCurrency($value5, $arParams["CURRENCY_ID"], true);
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
																placeholder="<?echo $value1;?>"
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
																placeholder="<?echo $value5;?>"
																onkeyup="smartFilter.keyup(this)"
																autocomplete="off"
															/>
														</div>
													</div>
													<div class="wrapp_slider iblock">
														<div class="bx_ui_slider_track" id="drag_track_<?=$key?>">

															<div class="bx_ui_slider_part first p1"><span><?=$value1?></span></div>
															<div class="bx_ui_slider_part p2"><span><?=$value2?></span></div>
															<div class="bx_ui_slider_part p3"><span><?=$value3?></span></div>
															<div class="bx_ui_slider_part p4"><span><?=$value4?></span></div>
															<div class="bx_ui_slider_part last p5"><span><?=$value5?></span></div>

															<div class="bx_ui_slider_pricebar_VD" style="left: 0;right: 0;" id="colorUnavailableActive_<?=$key?>"></div>
															<div class="bx_ui_slider_pricebar_VN" style="left: 0;right: 0;" id="colorAvailableInactive_<?=$key?>"></div>
															<div class="bx_ui_slider_pricebar_V"  style="left: 0;right: 0;" id="colorAvailableActive_<?=$key?>"></div>
															<div class="bx_ui_slider_range" 	id="drag_tracker_<?=$key?>"  style="left: 0;right: 0;">
																<a class="bx_ui_slider_handle left"  style="left:0;" href="javascript:void(0)" id="left_slider_<?=$key?>"></a>
																<a class="bx_ui_slider_handle right" style="right:0;" href="javascript:void(0)" id="right_slider_<?=$key?>"></a>
															</div>
														</div>
														<?
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
															"precision" => $arItem["DECIMALS"]? $arItem["DECIMALS"]: 0,
															"colorUnavailableActive" => 'colorUnavailableActive_'.$key,
															"colorAvailableActive" => 'colorAvailableActive_'.$key,
															"colorAvailableInactive" => 'colorAvailableInactive_'.$key,
														);
														?>
														<script type="text/javascript">
															BX.ready(function(){
																if(typeof window['trackBarOptions'] === 'undefined'){
																	window['trackBarOptions'] = {}
																}
																window['trackBarOptions']['<?=$key?>'] = <?=CUtil::PhpToJSObject($arJsParams)?>;
																window['trackBar<?=$key?>'] = new BX.Iblock.SmartFilter(window['trackBarOptions']['<?=$key?>']);
															});
														</script>
													</div>
													<?
													break;
												case "B"://NUMBERS
													?>
													<div class="wrapp_change_inputs iblock">
														<div class="flexbox flexbox--row form-control">
															<input
																class="min-price"
																type="text"
																name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
																id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
																value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
																placeholder="<?echo $arItem["VALUES"]["MIN"]["VALUE"];?>"
																size="5"
																onkeyup="smartFilter.keyup(this)"
																autocomplete="off"
																/>
															<input
																class="max-price"
																type="text"
																name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
																id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
																value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
																placeholder="<?echo $arItem["VALUES"]["MAX"]["VALUE"];?>"
																size="5"
																onkeyup="smartFilter.keyup(this)"
																autocomplete="off"
																/>
														</div>
													</div>
													<?
													break;
												case "G"://CHECKBOXES_WITH_PICTURES
													?>
													<div class="line-block line-block--flex-wrap line-block--gap line-block--gap-6">
														<?$j=1;
														$isHidden = false;?>
														<?foreach ($arItem["VALUES"] as $val => $ar):?>
															<?if($ar["VALUE"]){?>
																<div class="line-block__item">
																	<?/*if($j > $numVisiblePropValues && !$isHidden):
																		$isHidden = true;?>
																		<div class="hidden_values filter label_block">
																	<?endif;*/?>
																	<div class="pict">
																		<input
																			style="display: none"
																			type="checkbox"
																			name="<?=$ar["CONTROL_NAME"]?>"
																			id="<?=$ar["CONTROL_ID"]?>"
																			value="<?=$ar["HTML_VALUE"]?>"
																			autocomplete="off"
																			<? echo $ar["DISABLED"] ? 'disabled class="disabled"': '' ?>
																			<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
																		/>
																		<?
																		$class = "";
																		if ($ar["CHECKED"])
																			$class.= " active sku-props__value--active";
																		if ($ar["DISABLED"])
																			$class.= " disabled";

																		?>
																		<label for="<?=$ar["CONTROL_ID"]?>" data-role="label_<?=$ar["CONTROL_ID"]?>" class="sku-props__value sku-props__value--pict <?=$class?>" title="<?=$ar["VALUE"];?>" onclick="smartFilter.keyup(BX('<?=CUtil::JSEscape($ar["CONTROL_ID"])?>')); BX.toggleClass(this, 'active');" style="background-image: url('<?=((isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])) ? $ar["FILE"]["SRC"] : SITE_TEMPLATE_PATH.'/images/noimage.png');?>');"></label>
																	</div>
																	<?$j++;?>
																</div>
															<?}?>
														<?endforeach?>
														<?/*if($isHidden):?>
															</div>
															<div class="inner_expand_text font_14"><span class="expand_block dotted colored-link"><?=Loc::getMessage("FILTER_EXPAND_VALUES");?></span></div>
														<?endif;*/?>
													</div>
													<?
													break;
												case "H"://CHECKBOXES_WITH_PICTURES_AND_LABELS
													?>
													<?$j=1;
													$isHidden = false;?>
													<?foreach ($arItem["VALUES"] as $val => $ar):?>
														<?if($ar["VALUE"]){?>
															<?if($j > $numVisiblePropValues && !$isHidden):
																$isHidden = true;?>
																<div class="hidden_values filter1 label_block">
															<?endif;?>
															<input
																style="display: none"
																type="checkbox"
																name="<?=$ar["CONTROL_NAME"]?>"
																id="<?=$ar["CONTROL_ID"]?>"
																value="<?=$ar["HTML_VALUE"]?>"
																autocomplete="off"
																<? echo $ar["DISABLED"] ? 'disabled class="disabled"': '' ?>
																<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
															/>
															<?
															$class = "";
                                                            if ($ar["CHECKED"])
                                                                $class.= " active sku-props__value--active";
															if ($ar["DISABLED"])
																$class.= " disabled";
															?>
															<label for="<?=$ar["CONTROL_ID"]?>" data-role="label_<?=$ar["CONTROL_ID"]?>" class="bx_filter_param_label<?=$class?> label-mixed" onclick="smartFilter.keyup(BX('<?=CUtil::JSEscape($ar["CONTROL_ID"])?>')); BX.toggleClass(this, 'active');">
																<?/*<span class="bx_filter_param_btn bx_color_sl" title="<?=$ar["VALUE"]?>">*/?>
																	<?if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])):?>
																		<span class="bx_filter_btn_color_icon sku-props__value sku-props__value--pict label-mixed__image" style="background-image:url('<?=$ar["FILE"]["SRC"]?>');"></span>
																	<?endif?>
																<?/*</span>*/?>
																<span class="bx_filter_param_text font_14 font_short label-mixed__text" title="<?=$ar["VALUE"];?>"><?=$ar["VALUE"];?><?
																if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N" && isset($ar["ELEMENT_COUNT"])):
																	?> (<span data-role="count_<?=$ar["CONTROL_ID"]?>"><? echo $ar["ELEMENT_COUNT"]; ?></span>)<?
																endif;?></span>
															</label>
															<?$j++;?>
														<?}?>
													<?endforeach?>
													<?if($isHidden):?>
														</div>
														<div class="inner_expand_text font_14"><span class="expand_block dotted colored-link"><?=Loc::getMessage("FILTER_EXPAND_VALUES");?></span></div>
													<?endif;?>
													<?
													break;
												case "P"://DROPDOWN
													$checkedItemExist = false;
													?>
													<div class="bx_filter_select_container">
														<div class="bx_filter_select_block" onclick="smartFilter.showDropDownPopup(this, '<?=CUtil::JSEscape($key)?>')">
															<div class="bx_filter_select_text" data-role="currentOption">
																<?
																foreach ($arItem["VALUES"] as $val => $ar)
																{
																	if ($ar["CHECKED"])
																	{
																		echo $ar["VALUE"];
																		$checkedItemExist = true;
																	}
																}
																if (!$checkedItemExist)
																{
																	echo Loc::getMessage("CT_BCSF_FILTER_ALL");
																}
																?>
															</div>
															<div class="bx_filter_select_arrow">
																<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', 'fill-dark-light', ['WIDTH' => 7, 'HEIGHT' => 5]);?>
															</div>
															<input
																style="display: none"
																type="radio"
																name="<?=$arCur["CONTROL_NAME_ALT"]?>"
																id="<? echo "all_".$arCur["CONTROL_ID"] ?>"
																value=""
																autocomplete="off"
															/>
															<?foreach ($arItem["VALUES"] as $val => $ar):?>
																<input
																	style="display: none"
																	type="radio"
																	name="<?=$ar["CONTROL_NAME_ALT"]?>"
																	id="<?=$ar["CONTROL_ID"]?>"
																	value="<? echo $ar["HTML_VALUE_ALT"] ?>"
																	autocomplete="off"
																	<? echo $ar["DISABLED"] ? 'disabled class="disabled"': '' ?>
																	<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
																/>
															<?endforeach?>
															<div class="bx_filter_select_popup " data-role="dropdownContent" style="display: none;">
																<div class="dropdown-select1">
																	<div class="font_15">
																		<label for="<?="all_".$arCur["CONTROL_ID"]?>" class="bx_filter_param_label dropdown-menu-item dark_link color_222" data-role="all_label_<?=$arCur["CONTROL_ID"]?>" onclick="smartFilter.selectDropDownItem(this, '<?=CUtil::JSEscape("all_".$arCur["CONTROL_ID"])?>')">
																			<? echo Loc::getMessage("CT_BCSF_FILTER_ALL"); ?>
																		</label>
																	</div>
																	<?
																	foreach ($arItem["VALUES"] as $val => $ar):
																		$class = "";
																		if ($ar["CHECKED"])
																			$class.= " dropdown-menu-item--current";
																		if ($ar["DISABLED"])
																			$class.= " disabled";
																	?>
																		<div class="font_15">
																			<label for="<?=$ar["CONTROL_ID"]?>" class="bx_filter_param_label<?=$class?> dropdown-menu-item dark_link color_222" data-role="label_<?=$ar["CONTROL_ID"]?>" onclick="smartFilter.selectDropDownItem(this, '<?=CUtil::JSEscape($ar["CONTROL_ID"])?>')"><?=$ar["VALUE"]?></label>
																		</div>
																	<?endforeach?>
																</div>
															</div>
														</div>
													</div>
													<?
													break;
												case "R"://DROPDOWN_WITH_PICTURES_AND_LABELS
													?>
													<div class="bx_filter_select_container">
														<div class="bx_filter_select_block" onclick="smartFilter.showDropDownPopup(this, '<?=CUtil::JSEscape($key)?>')">
															<div class="bx_filter_select_text label-mixed" data-role="currentOption">
																<?
																$checkedItemExist = false;
																foreach ($arItem["VALUES"] as $val => $ar):
																	if ($ar["CHECKED"])
																	{
																	?>
																		<?if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])):?>
																			<span class="bx_filter_btn_color_icon  sku-props__value sku-props__value--pict label-mixed__image" style="background-image:url('<?=$ar["FILE"]["SRC"]?>');"></span>
																		<?endif?>
																		<span class="bx_filter_param_text font_14 font_short label-mixed__text">
																			<?=$ar["VALUE"]?>
																		</span>
																	<?
																		$checkedItemExist = true;
																	}
																endforeach;
																if (!$checkedItemExist){?>
																	<?echo Loc::getMessage("CT_BCSF_FILTER_ALL");
																}
																?>
															</div>
															<div class="bx_filter_select_arrow">
																<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', 'fill-dark-light', ['WIDTH' => 7, 'HEIGHT' => 5]);?>
															</div>
															<input
																style="display: none"
																type="radio"
																name="<?=$arCur["CONTROL_NAME_ALT"]?>"
																id="<? echo "all_".$arCur["CONTROL_ID"] ?>"
																value=""
																autocomplete="off"
															/>
															<?foreach ($arItem["VALUES"] as $val => $ar):?>
																<input
																	style="display: none"
																	type="radio"
																	name="<?=$ar["CONTROL_NAME_ALT"]?>"
																	id="<?=$ar["CONTROL_ID"]?>"
																	value="<?=$ar["HTML_VALUE_ALT"]?>"
																	autocomplete="off"
																	<? echo $ar["DISABLED"] ? 'disabled class="disabled"': '' ?>
																	<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
																/>
															<?endforeach?>
															<div class="bx_filter_select_popup bx_filter_select_popup--padded" data-role="dropdownContent" style="display: none">
																<div class="dropdown-select1">
																	<label for="<?="all_".$arCur["CONTROL_ID"]?>" class="bx_filter_param_label label-mixed" data-role="label_<?=$arCur["CONTROL_ID"]?>" onclick="smartFilter.selectDropDownItem(this, '<?=CUtil::JSEscape("all_".$arCur["CONTROL_ID"])?>')">
																		<span class="bx_filter_param_text font_14 font_short label-mixed__text"><? echo Loc::getMessage("CT_BCSF_FILTER_ALL"); ?></span>
																	</label>
																	<?
																	foreach ($arItem["VALUES"] as $val => $ar):
																		$class = "";
																		if ($ar["CHECKED"])
																			$class.= " active";
																		if ($ar["DISABLED"])
																			$class.= " disabled";
																	?>
																		<label for="<?=$ar["CONTROL_ID"]?>" data-role="label_<?=$ar["CONTROL_ID"]?>" class="bx_filter_param_label<?=$class?> font_14 font_short label-mixed" onclick="smartFilter.selectDropDownItem(this, '<?=CUtil::JSEscape($ar["CONTROL_ID"])?>')">
																			<?if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])):?>
																				<span class="bx_filter_btn_color_icon sku-props__value sku-props__value--pict label-mixed__image" title="<?=$ar["VALUE"]?>" style="background-image:url('<?=$ar["FILE"]["SRC"]?>');"></span>
																			<?endif?>
																			<span class="bx_filter_param_text font_14 font_short label-mixed__text">
																				<?=$ar["VALUE"]?>
																			</span>
																		</label>
																	<?endforeach?>
																</div>
															</div>
														</div>
													</div>
													<?
													break;
												case "K"://RADIO_BUTTONS
													?>
													<div class="scrolled scrollbar">
														<div class="form-radiobox">
															<input
																type="radio"
																value=""
																name="<? echo $arCur["CONTROL_NAME_ALT"] ?>"
																id="<? echo "all_".$arCur["CONTROL_ID"] ?>"
																onclick="smartFilter.click(this)"
																class="form-radiobox__input"
																autocomplete="off"
															/>
															<label data-role="all_label_<?=$arCur["CONTROL_ID"]?>" class="bx_filter_param_label form-radiobox__label color-theme-hover " for="<? echo "all_".$arCur["CONTROL_ID"] ?>">
																<span class="bx_filter_input_checkbox"><span><? echo Loc::getMessage("CT_BCSF_FILTER_ALL"); ?></span></span>
																<span class="form-radiobox__box"></span>
															</label>
														</div>
														<?$j=1;
														$isHidden = false;?>
														<?foreach($arItem["VALUES"] as $val => $ar):?>
															<?if($j > $numVisiblePropValues && !$isHidden):
																$isHidden = true;?>
																<div class="hidden_values">
															<?endif;?>
															<div class="form-radiobox">
																<input
																	type="radio"
																	value="<? echo $ar["HTML_VALUE_ALT"] ?>"
																	name="<? echo $ar["CONTROL_NAME_ALT"] ?>"
																	id="<? echo $ar["CONTROL_ID"] ?>"
																	<? echo $ar["DISABLED"] ? 'disabled': '' ?>
																	<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
																	onclick="smartFilter.click(this)"
																	class="form-radiobox__input"
																	autocomplete="off"
																/>
																<?$class = "";
																if ($ar["CHECKED"])
																	$class.= " selected";
																if ($ar["DISABLED"])
																	$class.= " disabled";?>
																<label data-role="label_<?=$ar["CONTROL_ID"]?>" class="bx_filter_param_label form-radiobox__label color-theme-hover <?=$class;?>" for="<? echo $ar["CONTROL_ID"] ?>">
																	<span class="bx_filter_input_checkbox">

																		<span class="bx_filter_param_text1" title="<?=$ar["VALUE"];?>"><?=$ar["VALUE"];?><?
																		if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N" && isset($ar["ELEMENT_COUNT"])):
																			?> (<span data-role="count_<?=$ar["CONTROL_ID"]?>"><? echo $ar["ELEMENT_COUNT"]; ?></span>)<?
																		endif;?></span>
																	</span>
																	<span class="form-radiobox__box"></span>
																</label>
															</div>
															<?$j++;?>
														<?endforeach;?>
														<?if($isHidden):?>
															</div>
															<div class="inner_expand_text font_14"><span class="expand_block dotted colored-link"><?=Loc::getMessage("FILTER_EXPAND_VALUES");?></span></div>
														<?endif;?>
													</div>
													<?
													break;
												case "U"://CALENDAR
													?>
													<div class="bx_filter_parameters_box_container_block">
														<div class="bx_filter_input_container bx_filter_calendar_container">
															<?$APPLICATION->IncludeComponent(
																'bitrix:main.calendar',
																'',
																array(
																	'FORM_NAME' => $arResult["FILTER_NAME"]."_form",
																	'SHOW_INPUT' => 'Y',
																	'INPUT_ADDITIONAL_ATTR' => 'class="calendar" placeholder="'.FormatDate("SHORT", $arItem["VALUES"]["MIN"]["VALUE"]).'" onkeyup="smartFilter.keyup(this)" onchange="smartFilter.keyup(this)"',
																	'INPUT_NAME' => $arItem["VALUES"]["MIN"]["CONTROL_NAME"],
																	'INPUT_VALUE' => $arItem["VALUES"]["MIN"]["HTML_VALUE"],
																	'SHOW_TIME' => 'N',
																	'HIDE_TIMEBAR' => 'Y',
																),
																null,
																array('HIDE_ICONS' => 'Y')
															);?>
														</div>
													</div>
													<div class="bx_filter_parameters_box_container_block">
														<div class="bx_filter_input_container bx_filter_calendar_container">
															<?$APPLICATION->IncludeComponent(
																'bitrix:main.calendar',
																'',
																array(
																	'FORM_NAME' => $arResult["FILTER_NAME"]."_form",
																	'SHOW_INPUT' => 'Y',
																	'INPUT_ADDITIONAL_ATTR' => 'class="calendar" placeholder="'.FormatDate("SHORT", $arItem["VALUES"]["MAX"]["VALUE"]).'" onkeyup="smartFilter.keyup(this)" onchange="smartFilter.keyup(this)"',
																	'INPUT_NAME' => $arItem["VALUES"]["MAX"]["CONTROL_NAME"],
																	'INPUT_VALUE' => $arItem["VALUES"]["MAX"]["HTML_VALUE"],
																	'SHOW_TIME' => 'N',
																	'HIDE_TIMEBAR' => 'Y',
																),
																null,
																array('HIDE_ICONS' => 'Y')
															);?>
														</div>
													</div>
													<?
													break;
												default://CHECKBOXES
													$count=count($arItem["VALUES"]);
													$i=1;
													if($arItem["IBLOCK_ID"]!=$arParams["IBLOCK_ID"] && strpos($arItem["FILTER_HINT"],'line')!==false){
														$isSize=true;
													}else{
														$isSize=false;
													}?>
													<?$j=1;
													$isHidden = false;?>

													<? if($arItem['IS_PROP_INLINE']): ?>
														<div class="bx_filter_parameters_box_title title dropdown-select__title font_14 font_large fill-dark-light bordered rounded-x shadow-hovered shadow-no-border-hovered prices1">
													<? endif; ?>

													<?if ($count):?>
														<div class="form-checkbox <?=(!$arItem['IS_PROP_INLINE'] ? 'form-checkbox--margined scrolled scrollbar' : '');?>">
													<?endif;?>

													<?if ($isSize):?>
														<div class="line-block line-block--flex-wrap line-block--gap line-block--gap-6">
													<?endif;?>

													<?foreach($arItem["VALUES"] as $val => $ar):?>
														<?$classLabel = 'form-checkbox__label color-theme-hover'?>
														<?if($j > $numVisiblePropValues && !$isHidden && !$isSize):
															$isHidden = true;?>
															<div class="hidden_values">
														<?endif;?>
														<?if ($isSize):?>
															<div class="line-block__item">
																<?$classLabel = 'sku-props__value font_14 ';
																if ($ar["CHECKED"]) $classLabel .= ' sku-props__value--active';?>
														<?endif;?>
														<input
															type="checkbox"
															value="<? echo $ar["HTML_VALUE"] ?>"
															name="<? echo $ar["CONTROL_NAME"] ?>"
															id="<? echo $ar["CONTROL_ID"] ?>"
															<? echo $ar["DISABLED"] ? 'disabled': '' ?>
															<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
															onclick="smartFilter.click(this)"
															class="form-checkbox__input"
															autocomplete="off"
														/>
														<label data-role="label_<?=$ar["CONTROL_ID"]?>" class="bx_filter_param_label <?=$classLabel;?> <?=($arItem['IS_PROP_INLINE'] ? "form-checkbox__label--toggle" : "");?> <?=($i==$count ? "last" : "");?> <? echo $ar["DISABLED"] ? 'disabled': '' ?>" for="<? echo $ar["CONTROL_ID"] ?>">
															<span class="bx_filter_input_checkbox">

																<span class="bx_filter_param_text" title="<?= $arItem['IS_PROP_INLINE'] ? $arItem['NAME'] : $ar["VALUE"]; ?>"><?= $arItem['IS_PROP_INLINE'] ? $arItem['NAME'] : $ar["VALUE"]; ?><?
																if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N" && isset($ar["ELEMENT_COUNT"]) && !$isSize):
																	?> (<span data-role="count_<?=$ar["CONTROL_ID"]?>"><? echo $ar["ELEMENT_COUNT"]; ?></span>)<?
																endif;?></span>
															</span>
															<?if (!$isSize):?>
															<span class="form-checkbox__box form-box"></span>
															<?endif;?>
														</label>
														<?$i++;?>
														<?$j++;?>

														<?if ($isSize):?>
															</div>
														<?endif;?>
													<?endforeach;?>
													<?if ($count):?>
														<?if($isHidden):?>
															</div>
															<div class="inner_expand_text font_14"><span class="expand_block dotted colored-link"><?=Loc::getMessage("FILTER_EXPAND_VALUES");?></span></div>
														<?endif;?>
														</div>
													<?endif;?>

													<?if ($isSize):?>
														</div>
													<?endif;?>

													<? if( $arItem['IS_PROP_INLINE'] ): ?>
														<span class="delete_filter colored_more_theme_bg2_hover" title="<?=Loc::getMessage("CLEAR_VALUE")?>">
															<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/catalog/item_icons.svg#close-8-8', '', ['WIDTH' => 8, 'HEIGHT' => 8]);?>
														</span>
														</div>
													<? endif; ?>
											<?}?>
										</div>
										<?if ($bWithFilterHint):?>
											<div class="char_name">
												<div class="hint font_13">
													<span class="hint__icon rounded colored_more_theme_bg2_hover border-theme-hover bordered">
														<i>?</i>
													</span>
													<span class="hint__text font_13 color_999">
														<?=Loc::getMessage('HINT');?>
													</span>
													<div class="tooltip tooltip--manual" style="display: none;"><?=$arItem["FILTER_HINT"]?></div>
												</div>
											</div>
										<?endif;?>
										<? if( !$arItem['IS_PROP_INLINE'] ): ?>
											<div class="bx_filter_button_box active">
												<span data-f="<?=Loc::getMessage('CT_BCSF_SET_FILTER')?>" data-fi="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TI')?>" data-fr="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TR')?>" data-frm="<?=Loc::getMessage('CT_BCSF_SET_FILTER_TRM')?>" class="bx_filter_container_modef btn btn-default btn-sm btn-wide "><?=Loc::getMessage("CT_BCSF_SET_FILTER")?> <span></span></span>
											</div>
										<? endif; ?>
									</div>
								</div>
							</div>
						<?}?>
						<?if($isFilter):?>
							<button class="bx_filter_search_reset btn-link-text font_13 colored-link<?=($bActiveFilter && $bActiveFilter[1] != 'clear' ? '' : ' hidden');?>" type="reset" id="del_filter" name="del_filter" data-href="">
								<span><?=Loc::getMessage("CT_BCSF_DEL_FILTER")?></span>
							</button>
						<?endif;?>
					</div>

					<?if ($isFilter):?>
						<div class="bx_filter_button_box active hidden">
							<div class="bx_filter_block">
								<div class="bx_filter_parameters_box_container flexbox flexbox--direction-row">
									<?if($arParams["FILTER_VIEW_MODE"] == "VERTICAL"):?>
										<div class="bx_filter_popup_result right" id="modef_mobile" <?if(!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"';?>>
											<?echo GetMessage("CT_BCSF_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num_mobile">'.intval($arResult["ELEMENT_COUNT"]).'</span>'));?>
											<a href="<?echo $arResult["FILTER_URL"]?>" class="button white_bg"><?echo GetMessage("CT_BCSF_FILTER_SHOW")?></a>
										</div>
									<?endif?>
									<div class="bx_filter_popup_result right font_14" id="modef" <?if(!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"';?>>
										<?echo GetMessage("CT_BCSF_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num">'.intval($arResult["ELEMENT_COUNT"]).'</span>'));?>
										<!-- noindex -->
										<a href="<?echo $arResult["FORM_ACTION"]?>" class="popup-result-link animate-arrow-hover" rel="nofollow" title="<?echo GetMessage("CT_BCSF_FILTER_SHOW")?>">
											<span class="arrow-all arrow-all--light-stroke">
												<?//=TSolution::showIconSvg(' arrow-all__item-arrow', SITE_TEMPLATE_PATH.'/images/svg/Arrow_map.svg');?>
												<span class="arrow-all__item-line arrow-all--light-bgcolor"></span>
											</span>
										</a>
										<!-- /noindex -->
									</div>
									<input class="bx_filter_search_button btn btn-default" type="submit" id="set_filter" name="set_filter"  value="<?=GetMessage("CT_BCSF_SET_FILTER")?>" />
									<button class="bx_filter_search_reset btn btn-transparent-bg btn-default <?=($bActiveFilter && $bActiveFilter[1] != 'clear' ? '' : ' hidden');?>" type="reset" id="del_filter" name="del_filter">
										<?=GetMessage("CT_BCSF_DEL_FILTER")?>
									</button>
								</div>
							</div>
						</div>
					<?endif;?>
				</form>
				<div style="clear: both;"></div>
			</div>
		</div>
		<script>
			var smartFilter = new JCSmartFilter('<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>', '<?=$arParams["VIEW_MODE"];?>', <?=CUtil::PhpToJSObject($arResult["JS_FILTER_PARAMS"])?>);
			BX.message({SELECTED: '<? echo GetMessage("SELECTED"); ?>'});
		</script>
	</div>
<?}?>
