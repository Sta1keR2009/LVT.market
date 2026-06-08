<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>
<?use \Bitrix\Main\Localization\Loc;?>
<?if($arResult['ITEMS']):?>

	<?if (!function_exists('showAsproLandingItems')):?>
		<?function showAsproLandingItems($arParams = [
			'ITEMS' => [],
			'COUNT' => 10,
			'PARAMS' => []
		]){
			ob_start();?>
				<?$i = 0;?>
				<?$bFilled = ($arParams['PARAMS']['BG_FILLED'] == 'Y');?>
				<?$bModile = ($arParams['PARAMS']['MOBILED'] == 'Y');?>
				<?$bSlider = ($arParams['PARAMS']['VIEW_TYPE'] == 'slider' && $bModile);?>
				<?if ($bModile):?>
					<!-- noindex -->
				<?endif;?>
				<div class="landings-list__info landings-list__info--mobiled">
					<?$compare_field = (isset($arParams['PARAMS']["COMPARE_FIELD"]) && $arParams['PARAMS']["COMPARE_FIELD"] ? $arParams['PARAMS']["COMPARE_FIELD"] : "DETAIL_PAGE_URL");
					$bProp = (isset($arParams['PARAMS']["COMPARE_PROP"]) && $arParams['PARAMS']["COMPARE_PROP"] == "Y");

					$textExpand = Loc::getMessage("SHOW_STEP_ALL");
					$textHide = Loc::getMessage("HIDE");
					$opened = "N";
					$classOpened = "";
					$arParams["COUNT"] = (int)$arParams["COUNT"];
					$count = count($arParams['ITEMS']);

					if ($bSlider) {
						$arParams["COUNT"] = 0;
					}

					$bWithHidden = $bCheckItemActive = $bHiddenOK = false;?>

					<?foreach ($arParams['ITEMS'] as $key => $arItem) {
						++$i;
						$bHidden = ($i > $arParams["COUNT"] && $arParams["COUNT"]);
						
						if ($bHidden) {
							$bWithHidden = true;
						}

						$url = $arItem[$compare_field];
						if ($bProp) {
							$url = $arItem["PROPERTIES"][$compare_field]["VALUE"];
						}

						if ($url) {
							$arFilterQuery = TSolution\Functions::checkActiveFilterPage($arParams['PARAMS']["SEF_CATALOG_URL"], $url);
							$bActiveFilter = ($arFilterQuery && !in_array('clear', $arFilterQuery));
							$curDir = $GLOBALS['APPLICATION']->GetCurDir();
							$curDirDec = urldecode(str_replace(' ', '+', $curDir));
							$urlDec= urldecode($url); 
							$urlDecCP = iconv("utf-8","windows-1251", $urlDec);
							$bCurrentUrl = ($curDirDec == $urlDec) || ($curDir == $urlDec) || ($curDir == $urlDecCP);

							if ($bCurrentUrl) {
								if($bActiveFilter){
									$arParams['ITEMS'][$key]['ACTIVE'] = 'Y';
									$arParams['ITEMS'][$key]['ACTIVE_URL'] = $bCurrentUrl ? 'Y' : 'N';
								}
							}
						}
					}?>
					<?$i = 0;?>
					<div class="landings-list__info-wrapper <?=($bWithHidden ? 'last' : '');?> mobile-scrolled mobile-scrolled--items-auto mobile-offset">
						<div class="line-block line-block--gap line-block--gap-8 line-block--flex-wrap">
							<?foreach($arParams['ITEMS'] as $arItem):?>
								<?
								++$i;
								$bHidden = ($i > $arParams["COUNT"] && $arParams["COUNT"]);

								$url = $arItem[$compare_field];
								if ($bProp) {
									$url = $arItem["PROPERTIES"][$compare_field]["VALUE"];
								}
								$class = '';
								if ($bHidden) {
									if ($arItem['ACTIVE_URL'] != 'Y') {
										$class .= 'hidden js-hidden';
									} else {
										if ($i > $arParams['COUNT'] && $count == $i) {
											$bHidden = false;
										}
									}
								}
								if ($arItem['ACTIVE_URL'] == 'Y') {
									$class = 'active';
								}
								$wrapperClass = 'landings-list__name ';
								$innerClass = 'chip chip--toggle bg-theme-active color-theme-hover-no-active ';
								$itemClass = 'chip__label';
								?>
								<div class="line-block__item landings-list__item <?=$class;?>">
									<div class="font_14 <?=$wrapperClass;?>" id="<?//=$this->GetEditAreaId($arItem['ID']);?>">
										<?if(strlen($url)):?>
											<?if($arItem['ACTIVE_URL'] == 'Y'):?>
												<span class="<?=$innerClass;?> landings-list__item--active bg-theme-active active <?=($bActiveFilter ? 'landings-list__item--reset' : '');?>">
													<span class="<?=$itemClass;?>">
														<span><?=$arItem['NAME']?></span>
													</span>
													<?if($arItem['ACTIVE']):?>
														<span class="landings-list__clear-filter chip__icon" title="<?=Loc::getMessage('RESET_LANDING');?>">
															<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/catalog/item_icons.svg#close-8-8', 'delete_filter', ['WIDTH' => 8, 'HEIGHT' => 8]);?>
														</span>
													<?endif;?>
												</span>
											<?else:?>
												<a class="<?=$innerClass;?>" href="<?=$url?>"><span class="<?=$itemClass;?>"><?=$arItem['NAME']?></span></a>
											<?endif;?>
										<?else:?>
											<span class="<?=$innerClass;?>"><span class="<?=$itemClass;?>"><?=$arItem['NAME']?></span></span>
										<?endif?>
									</div>
								</div>
							<?endforeach?>
							<?if($bHidden):?>
								<div class="line-block__item landings-list__item font_14">
									<span class="landings-list__name landings-list__item--js-more colored <?=$classOpened;?>" data-opened="<?=$opened;?>" data-visible="<?=$arParams['COUNT'];?>">
										<span data-opened="<?=$opened;?>" data-text="<?=$textHide;?>"><?=$textExpand;?></span>
									</span>
								</div>
							<?endif?>
						</div>
					</div>
				</div>
				<?if ($bModile):?>
					<!-- /noindex -->
				<?endif;?>

			<?$html = ob_get_clean();
			return $html;
		}?>
	<?endif;?>

	<div class="landings-list <?=$templateName;?> with-<?=$arParams['VIEW_TYPE']?>">
		<?=showAsproLandingItems([
			'ITEMS' => $arResult['ITEMS'],
			'COUNT' => $arParams['SHOW_COUNT'],
			'PARAMS' => $arParams + ['MOBILED' => 'Y']
		])?>
	</div>

<?endif?>