<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;
$this->setFrameMode(true);

$bOrderViewBasket = false; //$arParams['ORDER_VIEW'];
$dataItem = $bOrderViewBasket ? TSolution::getDataItem($arResult) : false;
$bOrderButton = $arResult['PROPERTIES']['FORM_ORDER']['VALUE_XML_ID'] == 'YES';
$bAskButton = $arResult['PROPERTIES']['FORM_QUESTION']['VALUE_XML_ID'] == 'YES';
$bTopImg = $bTopSideImg = false;

$bTopDate = $arResult['DISPLAY_ACTIVE_FROM'] || strlen($arResult['DISPLAY_PROPERTIES']['DATA']['VALUE']);
$topDate = strlen($arResult['DISPLAY_PROPERTIES']['DATA']['VALUE']) ? $arResult['DISPLAY_PROPERTIES']['DATA']['VALUE'] : $arResult['DISPLAY_ACTIVE_FROM'];

// preview image
$bIcon = false;
$nImageID = is_array($arResult['FIELDS']['PREVIEW_PICTURE']) ? $arResult['FIELDS']['PREVIEW_PICTURE']['ID'] : $arResult['FIELDS']['PREVIEW_PICTURE'];
if(!$nImageID){
	if($nImageID = $arResult['DISPLAY_PROPERTIES']['ICON']['VALUE']){
		$bIcon = true;
	}
}
$imageSrc = ($nImageID ? CFile::getPath($nImageID) : SITE_TEMPLATE_PATH.'/images/svg/noimage_content.svg');

/*set array props for component_epilog*/
$templateData = array(
	'ORDER' => $bOrderViewBasket,
	'ORDER_BTN' => ($bOrderButton || $bAskButton),
	'PREVIEW_PICTURE' => $arResult['PREVIEW_PICTURE'],
	'TAGS' => $arResult['TAGS'],
	'SECTIONS' => $arResult['PROPERTIES']['SECTION']['VALUE'],
	'H3_GOODS' => $arResult['PROPERTIES']['H3_GOODS']['VALUE'],
	'FILTER_URL' => $arResult['PROPERTIES']['FILTER_URL']['VALUE'],
	'MAP' => $arResult['PROPERTIES']['MAP']['VALUE'],
	'MAP_DOP_INFO' => $arResult['PROPERTIES']['INFO']['~VALUE'],
	'FAQ' => TSolution\Functions::getCrossLinkedItems($arResult, array('LINK_FAQ')),
	'TIZERS' => TSolution\Functions::getCrossLinkedItems($arResult, array('LINK_TIZERS')),
	'REVIEWS' => TSolution\Functions::getCrossLinkedItems($arResult, array('LINK_REVIEWS')),
	'SALE' => TSolution\Functions::getCrossLinkedItems($arResult, array('LINK_SALE')),
	'ARTICLES' => TSolution\Functions::getCrossLinkedItems($arResult, array('LINK_ARTICLES')),
	'SERVICES' => TSolution\Functions::getCrossLinkedItems($arResult, array('LINK_SERVICES')),
	'GOODS' => TSolution\Functions::getCrossLinkedItems($arResult, array('LINK_GOODS', 'LINK_GOODS_FILTER')),
    'SHOW_DISCOUNT_COUNTER' => in_array('ACTIVE_TO', $arParams['FIELD_CODE']),
);
?>

<?if($arResult['CATEGORY_ITEM']):?>
	<meta itemprop="category" content="<?=$arResult['CATEGORY_ITEM'];?>" />
<?endif;?>
<?if($arResult['DETAIL_PICTURE']):?>
	<meta itemprop="image" content="<?=$arResult['DETAIL_PICTURE']['SRC'];?>" />
<?endif;?>
<meta itemprop="name" content="<?=$arResult['NAME'];?>" />
<link itemprop="url" href="<?=$arResult['DETAIL_PAGE_URL'];?>" />

<?// top banner?>
<?if($arResult['FIELDS']['DETAIL_PICTURE']):?>
	<?
	// single detail image
	$templateData['BANNER_TOP_ON_HEAD'] = isset($arResult['PROPERTIES']['PHOTOPOS']) && $arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] == 'TOP_ON_HEAD';

	$atrTitle = (strlen($arResult['DETAIL_PICTURE']['DESCRIPTION']) ? $arResult['DETAIL_PICTURE']['DESCRIPTION'] : (strlen($arResult['DETAIL_PICTURE']['TITLE']) ? $arResult['DETAIL_PICTURE']['TITLE'] : $arResult['NAME']));
	$atrAlt = (strlen($arResult['DETAIL_PICTURE']['DESCRIPTION']) ? $arResult['DETAIL_PICTURE']['DESCRIPTION'] : (strlen($arResult['DETAIL_PICTURE']['ALT']) ? $arResult['DETAIL_PICTURE']['ALT'] : $arResult['NAME']));

	$bTopImg = (strpos($arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'], 'TOP') !== false);
	$templateData['IMG_TOP_SIDE'] = isset($arResult['PROPERTIES']['PHOTOPOS']) && $arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] == 'TOP_SIDE';
	?>

	<?if (!$templateData['IMG_TOP_SIDE']):?>
		<?if ($bTopImg):?>
			<?if ($templateData['BANNER_TOP_ON_HEAD']):?>
				<?$this->SetViewTarget('side-over-title');?>
			<?else:?>
				<?$this->SetViewTarget('top_section_filter_content');?>
			<?endif;?>
		<?endif;?>

		<?TSolution\Functions::showBlockHtml([
			'FILE' => '/images/detail_single.php',
			'PARAMS' => [
				'TYPE' => $arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'],
				'URL' => $arResult['DETAIL_PICTURE']['SRC'],
				'ALT' => $atrAlt,
				'TITLE' => $atrTitle,
				'TOP_IMG' => $bTopImg
			],
		])?>

		<?if ($bTopImg):?>
			<?$this->EndViewTarget();?>
		<?endif;?>
	<?endif;?>
<?endif;?>

<?ob_start();?>
	<div class="btn btn-default btn-wide btn-lg min_width--300 <?=($bOrderButton && $bAskButton) ? 'btn-transparent-border' : '';?> animate-load" data-event="jqm" data-param-id="<?=TSolution::getFormID("aspro_lite_question");?>" data-autoload-need_product="<?=TSolution::formatJsName($arResult['NAME'])?>" data-name="question">
		<span><?=(strlen($arParams['S_ASK_QUESTION']) ? $arParams['S_ASK_QUESTION'] : Loc::getMessage('S_ASK_QUESTION'))?></span>
	</div>
<?$askButtonHtml = ob_get_clean()?>

<?ob_start();?>
	<div class="btn btn-default btn-wide btn-lg animate-load"
	data-event="jqm"
	data-param-id="<?=TSolution::getFormID($arParams['FORM_ID_ORDER_SERVISE']);?>" data-autoload-need_product="<?=TSolution::formatJsName($arResult['NAME'])?>"
	data-autoload-service="<?=\TSolution::formatJsName($arResult["NAME"]);?>"
	data-autoload-sale="<?=\TSolution::formatJsName($arResult["NAME"]);?>"
	data-name="order_project">
		<span><?=(strlen($arParams['S_ORDER_SERVISE']) ? $arParams['S_ORDER_SERVISE'] : Loc::getMessage('S_ORDER_SERVISE'))?></span>
	</div>
<?$orderButtonHtml = ob_get_clean()?>
<?
// discount value
$bSaleNumber = strlen($arResult['DISPLAY_PROPERTIES']['SALE_NUMBER']['VALUE']);
// dicount counter
$bDiscountCounter = ($arResult['ACTIVE_TO'] && in_array('ACTIVE_TO', $arParams['FIELD_CODE']));
?>

<?if ($bSaleNumber || $bDiscountCounter):?>
	<div class="top-meta">
		<div class="line-block line-block--20 line-block--16-vertical line-block--flex-wrap">
			<?if ($bSaleNumber || $bDiscountCounter):?>
				<div class="line-block__item">
					<div class="line-block line-block--8 line-block--16-vertical line-block--flex-wrap">
						<?if ($bSaleNumber):?>
							<div class="line-block__item">
								<div class="top-meta__discount sale-list__item-sticker-value sticker__item sticker__item--sale rounded-x">
									<?=$arResult['DISPLAY_PROPERTIES']['SALE_NUMBER']['VALUE'];?>
								</div>
							</div>
						<?endif;?>
						<?if ($bDiscountCounter):?>
							<?TSolution\Functions::showDiscountCounter([
								'WRAPPER' => true,
								'WRAPPER_CLASS' => 'line-block__item',
								'TYPE' => 'block',
								'ITEM' => $arResult,
								'ICONS' => true
							]);?>
						<?endif;?>
					</div>
				</div>
			<?endif;?>
		</div>
	</div>
<?endif;?>

<?$bTopInfo = false;?>
<?if (
	$arResult['DISPLAY_PROPERTIES']['TASK_PROJECT']['~VALUE']['TEXT'] &&
	($bOrderButton || $bAskButton || $arResult["CHARACTERISTICS"])
):?>
	<?$bTopInfo = true;?>
	<?$templateData['ORDER_BTN'] = false?>
<?endif;?>

<?if ($templateData['IMG_TOP_SIDE']):?>
	<?$this->SetViewTarget('top_section_filter_content');?>
		<div class="maxwidth-theme">
			<div class="outer-rounded-x bordered top-info">
				<div class="flexbox flexbox--direction-row ">
					<div class="top-info__picture flex-1">
						<div class="owl-carousel owl-carousel--color-dots owl-carousel--nav-hover-visible owl-bg-nav owl-carousel--light owl-carousel--button-wide" data-plugin-options='{"items": "1", "autoplay" : false, "autoplayTimeout" : "3000", "smartSpeed":1000, "dots": true, "dotsContainer": false, "nav": true, "loop": false, "index": true, "margin": 0}'>
							<?foreach($arResult['TOP_GALLERY'] as $arPhoto):?>
								<div class="top-info__picture-item">
									<a href="<?=$arPhoto['DETAIL']['SRC']?>" class="top-info__link fancy" data-fancybox="big-gallery" target="_blank" title="<?=$arPhoto['TITLE']?>">
										<span class="top-info__img" style="background-image: url(<?=$arPhoto['PREVIEW']['src']?>)"></span>
									</a>
								</div>
							<?endforeach;?>
						</div>
					</div>
					<?if ($bTopInfo):?>
						<div class="top-info__text flex-1">
							<div class="top-info__text-inner">
								<?if ($arResult['DISPLAY_PROPERTIES']['TASK_PROJECT']['~VALUE']['TEXT']):?>
									<div class="top-info__task">
										<?if ($bTopDate):?>
											<div class="font_13 color_999">
												<?=$topDate?>
											</div>
										<?endif;?>
										<div class="font_18 color_222 font_large top-info__task-value">
											<?=$arResult['DISPLAY_PROPERTIES']['TASK_PROJECT']['~VALUE']['TEXT']?>
										</div>
									</div>
								<?endif;?>
								<?if ($arResult['CHARACTERISTICS'] || ($bOrderButton || $bAskButton)):?>
									<div class="line-block line-block--align-normal line-block--40 top-info__bottom">
										<?if ($arResult['CHARACTERISTICS']):?>
											<div class="line-block__item flex-1">
												<div class="properties list">
													<?
													$cntChars = count($arResult['CHARACTERISTICS']);
													$j = 0;
													?>
													<?foreach($arResult['CHARACTERISTICS'] as $code => $arProp):?>
														<?if($j < $arParams['VISIBLE_PROP_COUNT']):?>
															<div class="properties__item">
																<div class="properties__title font_13 color_999">
																	<?=$arProp['NAME']?>
																	<?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?>
																		<div class="hint hint--down">
																			<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
																			<div class="tooltip"><?=$arProp["HINT"]?></div>
																		</div>
																	<?endif;?>
																</div>
																<div class="properties__value color_222 font_15 font_short">
																	<?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
																		<?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
																	<?elseif($code == 'SITE'):?>
																		<?$valProp = preg_replace('#(http|https)(://)|((\?.*)|(\/\?.*))#', '', $arProp['VALUE']);?>
																		<!--noindex-->
																		<a class="dark_link" href="<?=(strpos($arProp['VALUE'], 'http') === false ? 'http://' : '').$arProp['VALUE'];?>" rel="nofollow" target="_blank">
																			<?=$valProp?>
																		</a>
																		<!--/noindex-->
																	<?else:?>
																		<?=$arProp["DISPLAY_VALUE"];?>
																	<?endif;?>
																</div>
															</div>
															<?$j++;?>
														<?endif;?>
													<?endforeach;?>
												</div>
												<?if($cntChars > $arParams['VISIBLE_PROP_COUNT']):?>
													<div class="more-char-link">
														<span class="choise dotted colored pointer" data-block="char"><?=Loc::getMessage('MORE_CHAR_BOTTOM');?></span>
													</div>
												<?else:?>
													<?$arResult['CHARACTERISTICS'] = [];?>
												<?endif;?>
											</div>
										<?endif;?>
										<?if ($bOrderButton || $bAskButton):?>
											<div class="line-block__item flex-1 buttons-block">
												<?if ($bOrderButton):?>
													<div>
														<?=$orderButtonHtml;?>
													</div>
												<?endif;?>
												<?if ($bAskButton):?>
													<div>
														<?=$askButtonHtml;?>
													</div>
												<?endif;?>
											</div>
										<?endif;?>
									</div>
								<?endif;?>
							</div>
						</div>
					<?endif;?>
				</div>
			</div>
		</div>
	<?$this->EndViewTarget();?>
<?elseif($bTopInfo):?>
	<?$this->SetViewTarget('top_detail_content');?>
		<?$class = 'bordered grey-bg';?>
		<div class="detail-info-wrapper <?=($templateData['SECTION_BNR_CONTENT'] || $bTopImg ? 'detail-info-wrapper--with-img' : '');?>">
			<?if (
				$arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] != 'TOP' &&
				$arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] != 'TOP_ON_HEAD' &&
				!$templateData['SECTION_BNR_CONTENT']
			):?>
				<div class="maxwidth-theme">
				<?$class .= ' outer-rounded-x'?>
			<?endif;?>

		<div class="<?=$class?>">
			<?if(
				$templateData['SECTION_BNR_CONTENT'] ||
				(
					$arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] != 'TOP_CONTENT' &&
					$bTopImg
				)
			):?>
				<div class="maxwidth-theme">
			<?endif;?>

			<div class="detail-info">
				<div class="line-block line-block--align-normal line-block--40">
					<?if ($arResult['DISPLAY_PROPERTIES']['TASK_PROJECT']['~VALUE']['TEXT'] || $arResult["CHARACTERISTICS"] ):?>
						<div class="line-block__item detail-info__inner flex-grow-1">
							<?if ($bTopDate):?>
								<div class="detail-info__date font_13 color_999">
									<?=$topDate?>
								</div>
							<?endif;?>
							<?if ($arResult['DISPLAY_PROPERTIES']['TASK_PROJECT']['~VALUE']['TEXT']):?>
								<div class="detail-info__text font_18 color_222 font_large">
									<?=$arResult['DISPLAY_PROPERTIES']['TASK_PROJECT']['~VALUE']['TEXT']?>
								</div>
							<?endif;?>
							<?if($arResult['CHARACTERISTICS']):?>
								<div class="detail-info__chars">
									<div class="properties list detail-info__chars-inner">
										<div class="line-block line-block--align-normal">
											<?
											$cntChars = count($arResult['CHARACTERISTICS']);
											$j = 0;
											?>
											<?foreach($arResult['CHARACTERISTICS'] as $code => $arProp):?>
												<?if($j < $arParams['VISIBLE_PROP_COUNT']):?>
													<div class="line-block__item col-lg-3 col-md-4 col-sm-6 detail-info__chars-item">
														<div class="properties__title font_13 color_999">
															<?=$arProp['NAME']?>
															<?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?>
																<div class="hint hint--down">
																	<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
																	<div class="tooltip"><?=$arProp["HINT"]?></div>
																</div>
															<?endif;?>
														</div>
														<div class="properties__value color_222 font_15 font_short">
															<?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
																<?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
															<?elseif($code == 'SITE'):?>
																<?$valProp = preg_replace('#(http|https)(://)|((\?.*)|(\/\?.*))#', '', $arProp['VALUE']);?>
																<!--noindex-->
																<a class="dark_link" href="<?=(strpos($arProp['VALUE'], 'http') === false ? 'http://' : '').$arProp['VALUE'];?>" rel="nofollow" target="_blank">
																	<?=$valProp?>
																</a>
																<!--/noindex-->
															<?else:?>
																<?=$arProp["DISPLAY_VALUE"];?>
															<?endif;?>
														</div>
													</div>
													<?$j++;?>
												<?endif;?>
											<?endforeach;?>
										</div>
									</div>
									<?if($cntChars > $arParams['VISIBLE_PROP_COUNT']):?>
										<div class="more-char-link">
											<span class="choise dotted colored pointer" data-block="char"><?=Loc::getMessage('MORE_CHAR_BOTTOM');?></span>
										</div>
									<?else:?>
										<?$arResult['CHARACTERISTICS'] = [];?>
									<?endif;?>
								</div>
							<?endif;?>
						</div>
					<?endif;?>
					<?if ($bOrderButton || $bAskButton):?>
						<div class="line-block__item detail-info__btns buttons-block">
							<?if ($bOrderButton):?>
								<div>
									<?=$orderButtonHtml;?>
								</div>
							<?endif;?>
							<?if ($bAskButton):?>
								<div>
									<?=$askButtonHtml;?>
								</div>
							<?endif;?>
						</div>
					<?endif;?>
				</div>
			</div>

			<?if(
				$templateData['SECTION_BNR_CONTENT'] ||
				(
					$arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] != 'TOP_CONTENT' &&
					$bTopImg
				)
			):?>
				</div>
			<?endif;?>
		</div>

			<?if (
				$arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] != 'TOP' &&
				$arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] != 'TOP_ON_HEAD' &&
				!$templateData['SECTION_BNR_CONTENT']
			):?>
				</div>
			<?endif;?>
		</div>
	<?$this->EndViewTarget();?>
<?endif;?>
<?if (($bOrderButton || $bAskButton) && !$bTopInfo):?>

	<?$this->SetViewTarget('PRODUCT_ORDER_SALE_INFO');?>
		<div class="order-info-block" itemprop="offers" itemscope itemtype="http://schema.org/Offer" data-id="<?=$arResult['ID']?>"<?=($bOrderViewBasket ? ' data-item="'.$dataItem.'"' : '')?>>
			<div class="line-block line-block--align-normal line-block--40">
				<div class="line-block__item flex-1">
					<div>
						<?$APPLICATION->IncludeComponent(
							'bitrix:main.include',
							'',
							array(
								'AREA_FILE_SHOW' => 'page',
								'AREA_FILE_SUFFIX' => 'ask',
								'EDIT_TEMPLATE' => ''
							)
						);?>
					</div>
				</div>
				<div class="line-block__item order-info-btns">
					<?=TSolution\Product\Price::show([
						'ITEM' =>$arResult,
						'PARAMS' => $arParams,
						'SHOW_SCHEMA' => true,
						'BASKET' => $bOrderViewBasket,
						'PRICE_FONT' => 18,
						'PRICEOLD_FONT' => 12,
						'PRICE_BLOCK_CLASS' => "color_222",
						'SERVICES_PAGE' => true,
					]);
					?>
					<?
					$arBtnConfig = [
						'DETAIL_PAGE' => true,
						'BTN_CLASS' => 'btn-lg btn-wide',
						'BTN_CALLBACK_CLASS' => 'btn-transparent-border',
						'CATALOG_IBLOCK_ID' => $arResult['IBLOCK_ID'],
						'ITEM' => $arResult,
						'BTN_ORDER_CLASS' => 'btn-lg btn-wide min_width--300',
						'ORDER_FORM_ID' => $arParams['FORM_ID_ORDER_SERVISE'],
						'CONFIG' => array("EXPRESSION_ORDER_BUTTON" => $arParams["S_ORDER_SERVISE"] ? $arParams["S_ORDER_SERVISE"] : Loc::getMessage("S_ORDER_SERVISE"))
					];

					$arBasketConfig = TSolution\Product\Basket::getOrderButton($arBtnConfig);
					?>
					<div class="order-info-btn line-block line-block--align-normal line-block--12">
						<?if ($bOrderButton):?>
							<div class="line-block__item ">
								<?=$arBasketConfig?>
							</div>
							<?if ($bAskButton):?>
								<div class="line-block__item">
									<a
									href="javascript:void(0)"
									rel="nofollow"
									class="btn btn-default btn-lg btn-transparent-border animate-load btn-wide"
									data-param-id="<?=\TSolution::getFormID("aspro_lite_question");?>"
									data-name="question"
									data-event="jqm"
									title="<?=Loc::getMessage('QUESTION_FORM_TITLE')?>"
									data-autoload-need_product="<?=\TSolution::formatJsName($arResult['NAME'])?>"
									>
										<span>?</span>
									</a>
								</div>
							<?endif;?>
						<?elseif ($bAskButton):?>
							<div class="line-block__item">
								<?=$askButtonHtml;?>
							</div>
						<?endif;?>
					</div>
				</div>
			</div>
		</div>
	<?$this->EndViewTarget();?>
<?endif;?>

<?$templateData['PREVIEW_TEXT'] = boolval(strlen($arResult['FIELDS']['PREVIEW_TEXT']) && !$templateData['SECTION_BNR_CONTENT']);?>

<?if (boolval(strlen($arResult['FIELDS']['PREVIEW_TEXT'])) && boolval(strlen($arResult['PROPERTIES']['ANONS']['VALUE'])) && $templateData['SECTION_BNR_CONTENT']) {
	$templateData['PREVIEW_TEXT'] = true;
}?>

<?if (
	$templateData['PREVIEW_TEXT'] &&
	(in_array($arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'], ['LEFT', 'RIGHT']))
):?>
	<div class="introtext">
		<?if($arResult['PREVIEW_TEXT_TYPE'] == 'text'):?>
			<p><?=$arResult['FIELDS']['PREVIEW_TEXT'];?></p>
		<?else:?>
			<?=$arResult['FIELDS']['PREVIEW_TEXT'];?>
		<?endif;?>
	</div>
	<?unset($templateData['PREVIEW_TEXT']);?>
<?endif;?>

<?// detail description?>
<?$templateData['DETAIL_TEXT'] = boolval(strlen($arResult['DETAIL_TEXT']));?>
<?if($templateData['DETAIL_TEXT'] || $templateData['PREVIEW_TEXT']):?>
	<?$this->SetViewTarget('PRODUCT_DETAIL_TEXT_INFO');?>
		<div class="content" itemprop="description">
			<?if ($templateData['PREVIEW_TEXT']):?>
				<div class="introtext">
					<?if($arResult['PREVIEW_TEXT_TYPE'] == 'text'):?>
						<p><?=$arResult['FIELDS']['PREVIEW_TEXT'];?></p>
					<?else:?>
						<?=$arResult['FIELDS']['PREVIEW_TEXT'];?>
					<?endif;?>
				</div>
			<?endif?>
			<?if ($templateData['DETAIL_TEXT']):?>
				<?=$arResult['DETAIL_TEXT'];?>
			<?endif;?>
		</div>
	<?$this->EndViewTarget();?>
<?endif;?>

<?// props content?>
<?$templateData['CHARACTERISTICS'] = boolval($arResult['CHARACTERISTICS']);?>
<?if($arResult['CHARACTERISTICS']):?>
	<?$this->SetViewTarget('PRODUCT_PROPS_INFO');?>
		<?$strGrupperType = $arParams["GRUPPER_PROPS"];?>
		<?if($strGrupperType == "GRUPPER"):?>
			<div class="props_block bordered rounded-x">
				<div class="props_block__wrapper">
					<?$APPLICATION->IncludeComponent(
						"redsign:grupper.list",
						"",
						Array(
							"CACHE_TIME" => "3600000",
							"CACHE_TYPE" => "A",
							"COMPOSITE_FRAME_MODE" => "A",
							"COMPOSITE_FRAME_TYPE" => "AUTO",
							"DISPLAY_PROPERTIES" => $arResult["CHARACTERISTICS"]
						),
						$component, array('HIDE_ICONS'=>'Y')
					);?>
				</div>
			</div>
		<?elseif($strGrupperType == "WEBDEBUG"):?>
			<div class="props_block bordered rounded-x">
				<div class="props_block__wrapper">
					<?$APPLICATION->IncludeComponent(
						"webdebug:propsorter",
						"linear",
						array(
							"IBLOCK_TYPE" => $arResult['IBLOCK_TYPE'],
							"IBLOCK_ID" => $arResult['IBLOCK_ID'],
							"PROPERTIES" => $arResult['CHARACTERISTICS'],
							"EXCLUDE_PROPERTIES" => array(),
							"WARNING_IF_EMPTY" => "N",
							"WARNING_IF_EMPTY_TEXT" => "",
							"NOGROUP_SHOW" => "Y",
							"NOGROUP_NAME" => "",
							"MULTIPLE_SEPARATOR" => ", "
						),
						$component, array('HIDE_ICONS'=>'Y')
					);?>
				</div>
			</div>
		<?elseif($strGrupperType == "YENISITE_GRUPPER"):?>
			<div class="props_block bordered rounded-x">
				<div class="props_block__wrapper">
					<?$APPLICATION->IncludeComponent(
						'yenisite:ipep.props_groups',
						'',
						array(
							'DISPLAY_PROPERTIES' => $arResult['CHARACTERISTICS'],
							'IBLOCK_ID' => $arParams['IBLOCK_ID']
						),
						$component, array('HIDE_ICONS'=>'Y')
					)?>
				</div>
			</div>
		<?else:?>
			<div class="props_block props_block--table props_block--nbg">
				<table class="props_block__wrapper">
					<?foreach($arResult["CHARACTERISTICS"] as $arProp):?>
						<tr class="char" >
							<td class="char_name font_15 color_666">
								<div class="props_item <?if($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y"){?>whint<?}?>">
									<span><?=$arProp["NAME"]?></span>
									<?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?><div class="hint hint--down"><span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div><?endif;?>
								</div>
							</td>
							<td class="char_value font_15 color_222">
								<span>
									<?if(count((array)$arProp["DISPLAY_VALUE"]) > 1):?>
										<?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
									<?else:?>
										<?=$arProp["DISPLAY_VALUE"];?>
									<?endif;?>
								</span>
							</td>
						</tr>
					<?endforeach;?>
				</table>
			</div>
		<?endif;?>
	<?$this->EndViewTarget();?>
<?endif;?>

<?// files?>
<?$templateData['DOCUMENTS'] = boolval($arResult['DOCUMENTS']);?>
<?if($templateData['DOCUMENTS']):?>
	<?$this->SetViewTarget('PRODUCT_FILES_INFO');?>
		<div class="doc-list-inner__list  grid-list grid-list--rounded  grid-list--items-1 grid-list--no-gap ">
			<?foreach($arResult['DOCUMENTS'] as $arItem):?>
				<?
				$arDocFile = TSolution::GetFileInfo($arItem);
				$docFileDescr = $arDocFile['DESCRIPTION'];
				$docFileSize = $arDocFile['FILE_SIZE_FORMAT'];
				$docFileType = $arDocFile['TYPE'];
				$bDocImage = false;
				if ($docFileType == 'jpg' || $docFileType == 'jpeg' || $docFileType == 'bmp' || $docFileType == 'gif' || $docFileType == 'png') {
					$bDocImage = true;
				}
				?>
				<div class="doc-list-inner__wrapper grid-list__item grid-list__item--rounded colored_theme_hover_bg-block grid-list-border-outer fill-theme-parent-all">
					<div class="doc-list-inner__item height-100 shadow-hovered shadow-no-border-hovered">
						<?if($arDocFile):?>
							<div class="doc-list-inner__icon-wrapper">
								<a class="file-type doc-list-inner__icon">
									<i class="file-type__icon file-type__icon--<?=$docFileType?>"></i>
								</a>
							</div>
						<?endif;?>
						<div class="doc-list-inner__content-wrapper">
							<div class="doc-list-inner__top">
								<?if($arDocFile):?>
									<?if($bDocImage):?>
										<a href="<?=$arDocFile['SRC']?>" class="doc-list-inner__name fancy dark_link color-theme-target switcher-title" data-caption="<?=htmlspecialchars($docFileDescr)?>"><?=$docFileDescr?></a>
									<?else:?>
										<a href="<?=$arDocFile['SRC']?>" target="_blank" class="doc-list-inner__name dark_link color-theme-target switcher-title" title="<?=htmlspecialchars($docFileDescr)?>">
											<?=$docFileDescr?>
										</a>
									<?endif;?>
									<div class="doc-list-inner__label"><?=$docFileSize?></div>
								<?else:?>
									<div class="doc-list-inner__name switcher-title"><?=$docFileDescr?></div>
								<?endif;?>
								<?if($arDocFile):?>
									<?if($bDocImage):?>
										<a class="doc-list-inner__icon-preview-image doc-list-inner__link-file fancy fill-theme-parent" data-caption="<?= htmlspecialchars($docFileDescr)?>" href="<?=$arDocFile['SRC']?>">
											<?=\TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/documents_icons.svg#image-preview-18-16', 'image-preview fill-theme-target fill-dark-light-block', ['WIDTH' => 18,'HEIGHT' => 16]);?>
										</a>
									<?else:?>
										<a class="doc-list-inner__icon-preview-image doc-list-inner__link-file fill-theme-parent" target="_blank" href="<?=$arDocFile['SRC']?>">
											<?=\TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/documents_icons.svg#file-download-18-16', 'image-preview fill-theme-target fill-dark-light-block', ['WIDTH' => 18,'HEIGHT' => 16]);?>
										</a>
									<?endif;?>
								<?endif;?>
							</div>
						</div>
					</div>
				</div>
			<?endforeach;?>
		</div>
	<?$this->EndViewTarget();?>
<?endif;?>

<?// big gallery?>
<?$templateData['BIG_GALLERY'] = boolval($arResult['BIG_GALLERY']);?>
<?if($arResult['BIG_GALLERY']):?>
	<?$this->SetViewTarget('PRODUCT_BIG_GALLERY_INFO');?>
		<?
		$arGallery = array_map(function($array){
			return [
				'src' => $array['DETAIL']['SRC'],
				'preview' => $array['PREVIEW']['src'],
				'alt' => $array['ALT'],
				'title' => $array['TITLE']
			];
		}, $arResult['BIG_GALLERY']);
		?>
		<?= TSolution\Functions::showGallery($arGallery, [
			'CONTAINER_CLASS' => 'gallery-detail font_24',
		]); ?>
	<?$this->EndViewTarget();?>
<?endif;?>

<?// video?>
<?$templateData['VIDEO'] = boolval($arResult['VIDEO']);
$bOneVideo = count($arResult['VIDEO']) == 1;
?>
<?if($arResult['VIDEO']):?>
	<?$this->SetViewTarget('PRODUCT_VIDEO_INFO');?>
		<?TSolution\Functions::showBlockHtml([
			'FILE' => 'video/detail_video_block.php',
			'PARAMS' => [
				'VIDEO' => $arResult['VIDEO'],
			],
		])?>
	<?$this->EndViewTarget();?>
<?endif;?>
