<?
if(!function_exists("ShowFilterHint")){
	function ShowFilterHint($arItem){?>
		<span class="hint hint--inline">
			<span class="hint__icon rounded bg-theme-hover border-theme-hover">
				<i>?</i>
			</span>
			<span class="tooltip tooltip--manual" style="display: none;"><?=$arItem["FILTER_HINT"]?></span>
		</span>
	<?}
}?>


<?
if(!function_exists("ShowFilterItemExt")){
	function ShowFilterItemExt($key, $arItem, $arParams){?>
		<?
		global $APPLICATION;
		if ($key!="TOP_BLOCK"){
			if (empty($arItem["VALUES"])|| isset($arItem["PRICE"])) {
				return;
			}
			if (
				$arItem["DISPLAY_TYPE"] == "A"
				&& (
					$arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0
				)
			) {
				return;
			}
		}
		$class="";
		if ($arItem["DISPLAY_EXPANDED"]=="Y") {
			$class = "active";
		}
		if (isset($arItem['PROPERTY_SET']) && $arItem['PROPERTY_SET'] == 'Y') {
			$class .= " opened";
		}
		$numVisiblePropValues = 5;

		if (!$arItem["FILTER_HINT"]) {
			$prop = CIBlockProperty::GetByID($arItem["ID"], $arItem["IBLOCK_ID"])->GetNext();
			$arItem["FILTER_HINT"] = $prop["HINT"];
		}
		?>
		<div class="bx_filter_parameters_box <?=$class;?>" data-expanded="<?=($arItem["DISPLAY_EXPANDED"] ? $arItem["DISPLAY_EXPANDED"] : "N");?>" data-prop_code=<?=strtolower($arItem["CODE"]);?> data-property_id="<?=$arItem["ID"]?>">
			<?$bWithFilterHint = strlen($arItem['FILTER_HINT']) && $arParams['SHOW_HINTS'] == 'Y' && strpos( $arItem["FILTER_HINT"],'line')===false;?>
			<?if ( !$arItem['IS_PROP_INLINE'] ):?>
				<div class="bx_filter_parameters_box_title  dropdown-select__title fill-dark-light" >
					<div class="bx_filter_parameter_label">
						<?=$arItem["NAME"]?>
						<?if ($bWithFilterHint):?>
							<?ShowFilterHint($arItem);?>
						<?endif;?>
					</div>
					<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', 'dropdown-select__icon-down', ['WIDTH' => 7, 'HEIGHT' => 5]);?>
				</div>
			<?endif;?>

			<?
				$style = "";

				if ($arItem['IS_PROP_INLINE']) {
					$style="style='display:block;'";
				} elseif($arItem["DISPLAY_EXPANDED"] !== "Y") {
					$style="style='display:none;'";
				}
			?>
			<div class="bx_filter_block <?= $arItem['IS_PROP_INLINE'] ? " limited_block" : "";?> <?=$bWithFilterHint ? " bx_filter_block--whint" : "";?>" <?=$style;?>>
				<span class="bx_filter_container_modef"></span>
				<div class="bx_filter_parameters_box_container  <?=(in_array($arItem["DISPLAY_TYPE"], ["G", "H"]) ? 'scrolled scrollbar' : '');?>">
				<?
					$arCur = current($arItem["VALUES"]);
						switch ($arItem["DISPLAY_TYPE"]){
							case "A"://NUMBERS_WITH_SLIDER
								?>
								<?$isConvert=true;
								$value1 = $arItem["VALUES"]["MIN"]["VALUE"];
								$value2 = $arItem["VALUES"]["MIN"]["VALUE"] + round(($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])/4);
								$value3 = $arItem["VALUES"]["MIN"]["VALUE"] + round(($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])/2);
								$value4 = $arItem["VALUES"]["MIN"]["VALUE"] + round((($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"])*3)/4);
								$value5 = $arItem["VALUES"]["MAX"]["VALUE"];
								if($isConvert){
									$value1 =number_format($value1, 0, ".", " ");
									$value2 =number_format($value2, 0, ".", " ");
									$value3 =number_format($value3, 0, ".", " ");
									$value4 =number_format($value4, 0, ".", " ");
									$value5 =number_format($value5, 0, ".", " ");
								}?>
								<div class="fullwidth-input">
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
											window['trackBar<?=$key?>'] = new BX.Iblock.SmartFilter(<?=CUtil::PhpToJSObject($arJsParams)?>);
										});
									</script>
								</div>
								<?
								break;
							case "B"://NUMBERS
								?>
								<div class="fullwidth-input fullwidth-input--margined clearfix">
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
											<? echo $ar["DISABLED"] ? 'disabled class="disabled"': '' ?>
											<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
										/>
										<?
										$class = "";
										if ($ar["CHECKED"])
											$class.= " active";
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
									<div class="inner_expand_text font_14"><span class="expand_block dotted colored-link"><?=GetMessage("FILTER_EXPAND_VALUES");?></span></div>
								<?endif;?>
								<?/*foreach ($arItem["VALUES"] as $val => $ar):?>
									<input
										style="display: none"
										type="checkbox"
										name="<?=$ar["CONTROL_NAME"]?>"
										id="<?=$ar["CONTROL_ID"]?>"
										value="<?=$ar["HTML_VALUE"]?>"
										<? echo $ar["DISABLED"] ? 'disabled class="disabled"': '' ?>
										<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
									/>
									<?
									$class = "";
									if ($ar["CHECKED"])
										$class.= " active";
									if ($ar["DISABLED"])
										$class.= " disabled";
									?>
									<label for="<?=$ar["CONTROL_ID"]?>" data-role="label_<?=$ar["CONTROL_ID"]?>" class="bx_filter_param_label<?=$class?> pal nab" onclick="smartFilter.keyup(BX('<?=CUtil::JSEscape($ar["CONTROL_ID"])?>')); BX.toggleClass(this, 'active');">
										<span class="bx_filter_param_btn bx_color_sl" title="<?=$ar["VALUE"]?>">
											<?if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])):?>
												<span class="bx_filter_btn_color_icon" style="background-image:url('<?=$ar["FILE"]["SRC"]?>');"></span>
											<?endif?>
										</span>
										<span class="bx_filter_param_text" title="<?=$ar["VALUE"];?>"><?=$ar["VALUE"];?><?
										if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N" && isset($ar["ELEMENT_COUNT"])):
											?> (<span data-role="count_<?=$ar["CONTROL_ID"]?>"><? echo $ar["ELEMENT_COUNT"]; ?></span>)<?
										endif;?></span>
									</label>
								<?endforeach*/?>
								<?
								break;
							case "P"://DROPDOWN
								$checkedItemExist = false;
								?>
								<div class="bx_filter_select_container">
									<div class="bx_filter_select_block s_<?=CUtil::JSEscape($key)?>" data-id="<?=CUtil::JSEscape($key)?>" onclick="smartFilter.showDropDownPopup(this, '<?=CUtil::JSEscape($key)?>')">
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
												echo GetMessage("CT_BCSF_FILTER_ALL");
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
										/>
										<?foreach ($arItem["VALUES"] as $val => $ar):?>
											<input
												style="display: none"
												type="radio"
												name="<?=$ar["CONTROL_NAME_ALT"]?>"
												id="<?=$ar["CONTROL_ID"]?>"
												value="<? echo $ar["HTML_VALUE_ALT"] ?>"
												<? echo $ar["DISABLED"] ? 'disabled class="disabled"': '' ?>
												<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
											/>
										<?endforeach?>
										<div class="bx_filter_select_popup" data-role="dropdownContent" style="display: none;">
											<div class="dropdown-select1">
												<div class="font_15">
													<label for="<?="all_".$arCur["CONTROL_ID"]?>" class="bx_filter_param_label dropdown-menu-item dark_link color_222" data-role="all_label_<?=$arCur["CONTROL_ID"]?>" onclick="smartFilter.selectDropDownItem(this, '<?=CUtil::JSEscape("all_".$arCur["CONTROL_ID"])?>')">
														<? echo GetMessage("CT_BCSF_FILTER_ALL"); ?>
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
									<div class="bx_filter_select_block s_<?=CUtil::JSEscape($key)?>" data-id="<?=CUtil::JSEscape($key)?>" onclick="smartFilter.showDropDownPopup(this, '<?=CUtil::JSEscape($key)?>')">
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
												<?echo GetMessage("CT_BCSF_FILTER_ALL");
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
										/>
										<?foreach ($arItem["VALUES"] as $val => $ar):?>
											<input
												style="display: none"
												type="radio"
												name="<?=$ar["CONTROL_NAME_ALT"]?>"
												id="<?=$ar["CONTROL_ID"]?>"
												value="<?=$ar["HTML_VALUE_ALT"]?>"
												<? echo $ar["DISABLED"] ? 'disabled class="disabled"': '' ?>
												<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
											/>
										<?endforeach?>
										<div class="bx_filter_select_popup bx_filter_select_popup--padded" data-role="dropdownContent" style="display: none">
											<div class="dropdown-select1">
												<label for="<?="all_".$arCur["CONTROL_ID"]?>" class="bx_filter_param_label label-mixed" data-role="label_<?=$arCur["CONTROL_ID"]?>" onclick="smartFilter.selectDropDownItem(this, '<?=CUtil::JSEscape("all_".$arCur["CONTROL_ID"])?>')">
													<span class="bx_filter_param_text font_14 font_short label-mixed__text"><? echo GetMessage("CT_BCSF_FILTER_ALL"); ?></span>
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
										/>
										<label class="bx_filter_param_label form-radiobox__label color-theme-hover" for="<? echo "all_".$arCur["CONTROL_ID"] ?>">
											<span><? echo GetMessage("CT_BCSF_FILTER_ALL"); ?></span>
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
													/>
											<label data-role="label_<?=$ar["CONTROL_ID"]?>" class="bx_filter_param_label form-radiobox__label color-theme-hover <? echo $ar["DISABLED"] ? 'disabled': '' ?>" for="<? echo $ar["CONTROL_ID"] ?>">
												<span class="bx_filter_input_checkbox ">

													<span class="bx_filter_param_text1"><?=$ar["VALUE"];?><?
													if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N" && isset($ar["ELEMENT_COUNT"])):
														?> (<span data-role="count_<?=$ar["CONTROL_ID"]?>"><? echo $ar["ELEMENT_COUNT"]; ?></span>)<?
													endif;?></span>
												</span>
												<span class="form-radiobox__box form-box"></span>
											</label>
										</div>
										<?$j++;?>
									<?endforeach;?>
									<?if($isHidden):?>
										</div>
										<div class="inner_expand_text font_14"><span class="expand_block dotted colored-link"><?=GetMessage("FILTER_EXPAND_VALUES");?></span></div>
									<?endif;?>
								</div>
								<?
								break;
							case "U"://CALENDAR
								?>
								<div class="fullwidth-input fullwidth-input--margined clearfix">
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
								</div>
								<?
								break;
							default://CHECKBOXES
								$count=count($arItem["VALUES"]);
								$i=1;
								if ($arItem["IBLOCK_ID"]!=$arParams["IBLOCK_ID"] && strpos($arItem["FILTER_HINT"],'line')!==false) {
									$isSize=true;
								} else {
									$isSize=false;
								}?>
								<?$j=1;
								$isHidden = false;?>

								<? if($arItem['IS_PROP_INLINE']): ?>
									<div class="bx_filter_parameters_box_title title dropdown-select__title fill-dark-light prices1 one-value">
								<? endif; ?>

								<?if ($count):?>
									<div class="form-checkbox <?=(!$arItem['IS_PROP_INLINE'] ? 'form-checkbox--margined scrolled scrollbar' : 'flex-1');?>">
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
										<? echo $ar["DISABLED"] ? 'disabled ': '' ?>
										<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
										onclick="smartFilter.click(this)"
										class="form-checkbox__input"
									/>
									<label
										data-role="label_<?=$ar["CONTROL_ID"]?>" class="bx_filter_param_label <?=$classLabel;?> <?=($arItem['IS_PROP_INLINE'] ? "form-checkbox__label--toggle" : "");?> <?=($i==$count ? "last" : "");?> <? echo $ar["DISABLED"] ? 'disabled': '' ?>"
										for="<? echo $ar["CONTROL_ID"] ?>"
									>
										<span class="bx_filter_input_checkbox">
											<span class="bx_filter_param_text"><?=$ar["VALUE"];?>
											<?if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N" && isset($ar["ELEMENT_COUNT"]) && !$isSize):
												?> (<span data-role="count_<?=$ar["CONTROL_ID"]?>"><? echo $ar["ELEMENT_COUNT"]; ?></span>)<?
											endif;?>
											</span>
											<?if( $arItem['IS_PROP_INLINE'] && $bWithFilterHint ):?>
												<?ShowFilterHint($arItem);?>
											<?endif;?>
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
										<div class="inner_expand_text font_14"><span class="expand_block dotted colored-link"><?=GetMessage("FILTER_EXPAND_VALUES");?></span></div>
									<?endif;?>
									</div>
								<?endif;?>

								<?if ($isSize):?>
									</div>
								<?endif;?>

								<? if( $arItem['IS_PROP_INLINE'] ): ?>
									</div>
								<? endif; ?>
						<?}?>
						</div>
						<div class="clearfix"></div>
					</div>
		</div>
	<?}
}?>
