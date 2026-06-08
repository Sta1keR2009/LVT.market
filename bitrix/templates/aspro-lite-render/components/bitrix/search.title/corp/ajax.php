<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (empty($arResult["CATEGORIES"])) return;
?>
<div class="searche-result scrollbar">
	<?foreach($arResult["CATEGORIES"] as $category_id => $arCategory):?>
		<?foreach($arCategory["ITEMS"] as $i => $arItem):?>
			<?//=$arCategory["TITLE"]?>
			<?if($category_id !== "all"):?>
				<?if(
					$arItem["MODULE_ID"] === 'iblock'
					&& $arItem["ITEM_ID"]
				):?>
					<?if (strpos($arItem["ITEM_ID"], "S") === false):?>
						<?if(isset($arResult["ELEMENTS"][$arItem["ITEM_ID"]])):?>
							<?$arElement = $arResult["ELEMENTS"][$arItem["ITEM_ID"]];?>
							<a class="bx_item_block searche-result__item rounded-x dark_link" href="<?=$arItem["URL"]?>">
								<span class="searche-result__inner flexbox flexbox--direction-row">
									<?if(isset($arElement["PICTURE"]) && is_array($arElement["PICTURE"])):?>
										<span class="searche-result__item-image">
											<img class="img-responsive" src="<?=$arElement["PICTURE"]["src"]?>"/>
										</span>
									<?endif;?>
									<span class="searche-result__item-text">
										<span><?=$arItem["NAME"]?></span>
										<?if (is_array($arElement) && TSolution\Product\Price::check($arElement)):?>
											<div class="title-search-price">
												<?
												$arPrices = TSolution\Product\Price::show([
													'ITEM' => $arElement,
													'PARAMS' => $arParams,
													'SHOW_SCHEMA' => false,
													'RETURN' => true,
													'APART_ECONOMY' => true,
													'PRICE_FONT' => 13,
													'PRICEOLD_FONT' => 12,
												]);
												if ($arPrices['PRICES']) {
													$priceHtml = $arPrices['PRICES'];
												}
												if ($arPrices['ECONOMY']) {
													$discountHtml = $arPrices['ECONOMY'];
												}
												?>
												<?if($priceHtml){?>
													<?=$priceHtml?>
												<?}?>
											</div>
										<?elseif($arItem['PARENT']):?>
											<div class="item-parent font_13"><?=$arItem['PARENT']?></div>
										<?endif;?>
									</span>
								</span>
							</a>
						<?endif;?>
					<?else:?>
						<?$sectionId = str_replace('S', '', $arItem["ITEM_ID"]);?>
						<?if(isset($arResult["SECTIONS"][$sectionId])):?>
							<?$arSection = $arResult["SECTIONS"][$sectionId];?>
							<a class="bx_item_block searche-result__item rounded-x dark_link" href="<?=$arItem["URL"]?>">
								<span class="searche-result__inner flexbox flexbox--direction-row">
									<?if(is_array($arSection["PICTURE"])):?>
										<span class="searche-result__item-image">
											<img class="img-responsive" src="<?=$arSection["PICTURE"]["src"]?>"/>
										</span>
									<?endif;?>
									<span class="searche-result__item-text">
										<span><?=$arItem["NAME"]?></span>
										<?if($arItem['PARENT']):?>
											<div class="item-parent font_13"><?=$arItem['PARENT']?></div>
										<?endif;?>
									</span>
								</span>
							</a>
						<?endif;?>
					<?endif;?>
				<?elseif ($arItem['TYPE'] !== 'all'):?>
					<a class="bx_item_block searche-result__item rounded-x dark_link others_result" href="<?=$arItem["URL"]?>">
						<span class="searche-result__inner flexbox flexbox--direction-row">
							<span class="searche-result__item-text">
								<span><?=$arItem["NAME"]?></span>
								<?if($arItem['PARENT']):?>
									<div class="item-parent font_13"><?=$arItem['PARENT']?></div>
								<?endif;?>
							</span>
						</span>
					</a>
				<?endif;?>
			<?endif;?>
		<?endforeach;?>
	<?endforeach;?>
</div>

<?if(isset($arResult["CATEGORIES"]['all']) ):?>
	<?foreach($arResult["CATEGORIES"]['all']["ITEMS"] as $i => $arItem):?>
		<div class="searche-result__all">
			<a class="all_result_title btn btn-transparent btn-wide bx_item_block" href="<?=$arItem["URL"]?>"><?=$arItem["NAME"]?></a>
		</div>
	<?endforeach;?>
<?endif;?>