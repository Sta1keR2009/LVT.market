<?$bFirst = true;?>
<?
$bGroups = is_array($arResult['GROUPS']) && !empty($arResult['GROUPS']);
$bOffersMode = $arParams['OFFERS_MODE'] === 'Y';
$iblockId = (int)($arParams['IBLOCK_ID'] ?? 0);
$smartFilterMap = is_array($arParams['SMART_FILTER'] ?? null) ? $arParams['SMART_FILTER'] : [];
$sectionPageUrl = (string)($arParams['SECTION_PAGE_URL'] ?? '');
if ($sectionPageUrl === '') {
	$sectionPageUrl = '/katalog/';
}

$lvtResolveSmartFilterPropertyId = static function (array $smartFilterMap, string $propertyCode, int $fallbackId = 0): int {
	if ($propertyCode !== '' && isset($smartFilterMap[$propertyCode])) {
		$mapEntry = $smartFilterMap[$propertyCode];
		if (is_array($mapEntry)) {
			return (int)($mapEntry['ID'] ?? 0);
		}

		return (int)$mapEntry;
	}

	return $fallbackId;
};

$lvtIsPropertyFilterable = static function (
	int $iblockId,
	array $smartFilterMap,
	string $propertyCode,
	int $propertyId,
	string $rawValue
) use ($lvtResolveSmartFilterPropertyId): bool {
	if ($rawValue === '') {
		return false;
	}

	$propertyId = $lvtResolveSmartFilterPropertyId($smartFilterMap, $propertyCode, $propertyId);

	return $propertyId > 0 && (
		!empty($smartFilterMap[$propertyCode])
		|| $iblockId === 41
	);
};

?>
<?if ($bGroups):?>
<form action="<?=$sectionPageUrl?>" method="get" class="js-filter analog-filter-form">
<input type="hidden" name="set_filter" value="Y" />
<?
	$__filterablePropsCount = 0;
?>
	<div class="properties-group properties-group--table js-offers-group-wrap">
		<?foreach ($arResult['GROUPS'] as $arGroup):?>
			<?
			$bNoGroup = $arGroup['CODE'] === 'no-group' || $arGroup['NAME'] === 'NO-GROUP';
			$bOfferGroup = $bOffersMode || (isset($arGroup['OFFER_GROUP']) && $arGroup['OFFER_GROUP']);
			?>
			<div class="properties-group__group<?=($bOfferGroup ? ' js-offers-group' : '')?>" data-group-code="<?=($arGroup['CODE'] ?? 'no-group')?>">
				<?if (
					!$bNoGroup && 
					!empty($arGroup['NAME'])
				):?>
					<div class="properties-group__group-name switcher-title<?=($bFirst ? ' properties-group__group-name--first' : '')?>">
						<?=$arGroup['NAME']?>
					</div>
				<?endif;?>
				<div class="properties-group__items js-offers-group__items-wrap font_15">
					<?foreach ($arGroup['DISPLAY_PROPERTIES'] as $arProp):
						$rawValue = $arProp['~VALUE'] ?? $arProp['VALUE'] ?? '';
						if (is_array($rawValue)) {
							$rawValue = reset($rawValue);
						}
						$rawValue = is_scalar($rawValue) ? trim((string)$rawValue) : '';
						$propertyCode = (string)($arProp['CODE'] ?? '');
						$propertyId = (int)($arProp['ID'] ?? 0);
						$propertyId = $lvtResolveSmartFilterPropertyId($smartFilterMap, $propertyCode, $propertyId);
						$isFilterable = $lvtIsPropertyFilterable($iblockId, $smartFilterMap, $propertyCode, $propertyId, $rawValue);
						if ($isFilterable) {
							$__filterablePropsCount++;
						}
						
						?>
						<?$bHint = $arProp['HINT'] && $arParams['SHOW_HINTS'] == 'Y';?>
						<div class="properties-group__item<?=($bOffersMode || $arProp['IS_OFFER'] ? ' js-offers-group__item' : '')?>" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
							<div class="properties-group__name-wrap<?=($bHint ? ' properties-group__name-wrap--whint' : '')?>">
								<span itemprop="name" class="properties-group__name color_666"><?=$arProp['NAME']?></span>
								<?if ($bHint):?>
									<div class="hint hint--down">
										<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
										<div class="tooltip"><?=$arProp['HINT']?></div>
									</div>
								<?endif;?>
							</div>

							<div class="properties-group__value-wrap">
								<div class="properties-group__value color_222" itemprop="value">
									<?if (is_array($arProp['DISPLAY_VALUE']) && count($arProp['DISPLAY_VALUE']) > 1):?>
										<?=implode(', ', $arProp['DISPLAY_VALUE'])?>
									<?else:?>
										<?=$arProp['DISPLAY_VALUE']?>
									<?endif;?>
								</div>
							</div>
							<div class="checkbox_block">
								<?if($isFilterable):?>
								<input type="checkbox" value="<?=htmlspecialcharsbx($rawValue)?>" name="ANALOG_FILTER[<?=$propertyId?>][]" class="char-filter-checkbox" />
								<?endif;?>
							</div>
						</div>
					<?endforeach;?>
				</div>
			</div>
			<?$bFirst = false;?>
		<?endforeach;?>

			<div class="properties-group__groups">

				<div class="properties-group__items js-offers-group__items-wrap font_15">


						<div class="properties-group__item" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
							<div class="properties-group__name-wrap">

							</div>

							<div class="properties-group__value-wrap">
								<div class="properties-group__value color_222" itemprop="value">
								</div>
							</div>
							<div class="">
							<?$__shouldRenderSubmit = $__filterablePropsCount > 0;?>

							<?if ($__shouldRenderSubmit):?>
							<input type="submit" class="btn btn-default" value="Подобрать похожие" />
							<?endif;?>

							</div>
						</div>



				</div>

			</div>


	</div>


		</form>
<?else:?>
	<?if ($arResult['DISPLAY_TYPE'] != 'TABLE'):?>
		<div class="properties-group properties-group--block js-offers-group-wrap">
			<div class="properties-group__group<?=($bOffersMode ? ' js-offers-group' : '')?>" data-group-code="no-group">
				<div class="properties-group__items js-offers-group__items-wrap font_15">
					<?foreach ($arResult['DISPLAY_PROPERTIES'] as $arProp):?>
						<?$bHint = $arProp['HINT'] && $arParams['SHOW_HINTS'] == 'Y';?>
						<div class="properties-group__item<?=($bOffersMode || $arProp['IS_OFFER'] ? ' js-offers-group__item' : '')?>" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
							<div class="properties-group__name-wrap<?=($bHint ? ' properties-group__name-wrap--whint' : '')?>">
								<span itemprop="name" class="properties-group__name color_666"><?=$arProp['NAME']?></span>
								<?if ($bHint):?>
									<div class="hint hint--down">
										<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
										<div class="tooltip"><?=$arProp['HINT']?></div>
									</div>
								<?endif;?>
							</div>

							<div class="properties-group__value-wrap">
								<div class="properties-group__value color_222" itemprop="value">
									<?if (is_array($arProp['DISPLAY_VALUE']) && count($arProp['DISPLAY_VALUE']) > 1):?>
										<?=implode(', ', $arProp['DISPLAY_VALUE'])?>
									<?else:?>
										<?=$arProp['DISPLAY_VALUE']?>
									<?endif;?>
								</div>
							</div>
						</div>
					<?endforeach;?>

					<?if ($arResult['OFFER_DISPLAY_PROPERTIES']):?>
						<?foreach ($arResult['OFFER_DISPLAY_PROPERTIES'] as $arProp):?>
							<?$bHint = $arProp['HINT'] && $arParams['SHOW_HINTS'] == 'Y';?>
							<div class="properties-group__item js-offers-group__item" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
								<div class="properties-group__name-wrap<?=($bHint ? ' properties-group__name-wrap--whint' : '')?>">
									<span itemprop="name" class="properties-group__name color_666"><?=$arProp['NAME']?></span>
									<?if ($bHint):?>
										<div class="hint hint--down">
											<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
											<div class="tooltip"><?=$arProp['HINT']?></div>
										</div>
									<?endif;?>
								</div>

								<div class="properties-group__value-wrap">
									<div class="properties-group__value color_222" itemprop="value">
										<?if (is_array($arProp['DISPLAY_VALUE']) && count($arProp['DISPLAY_VALUE']) > 1):?>
											<?=implode(', ', $arProp['DISPLAY_VALUE'])?>
										<?else:?>
											<?=$arProp['DISPLAY_VALUE']?>
										<?endif;?>
									</div>
								</div>
							</div>
						<?endforeach;?>
					<?endif;?>
				</div>
			</div>
		</div>
	<?else:?>
		<?php
		$__filterablePropsCount = 0;
		foreach ((array)$arResult['DISPLAY_PROPERTIES'] as $__arProp) {
			$__rawValue = $__arProp['~VALUE'] ?? $__arProp['VALUE'] ?? '';
			if (is_array($__rawValue)) {
				$__rawValue = reset($__rawValue);
			}
			$__rawValue = is_scalar($__rawValue) ? trim((string)$__rawValue) : '';
			$__propertyCode = (string)($__arProp['CODE'] ?? '');
			$__propertyId = (int)($__arProp['ID'] ?? 0);
			if ($lvtIsPropertyFilterable($iblockId, $smartFilterMap, $__propertyCode, $__propertyId, $__rawValue)) {
				$__filterablePropsCount++;
			}
		}
		foreach ((array)$arResult['OFFER_DISPLAY_PROPERTIES'] as $__arProp) {
			$__rawValue = $__arProp['~VALUE'] ?? $__arProp['VALUE'] ?? '';
			if (is_array($__rawValue)) {
				$__rawValue = reset($__rawValue);
			}
			$__rawValue = is_scalar($__rawValue) ? trim((string)$__rawValue) : '';
			$__propertyCode = (string)($__arProp['CODE'] ?? '');
			$__propertyId = (int)($__arProp['ID'] ?? 0);
			if ($lvtIsPropertyFilterable($iblockId, $smartFilterMap, $__propertyCode, $__propertyId, $__rawValue)) {
				$__filterablePropsCount++;
			}
		}
		?>
		<form action="<?=$sectionPageUrl?>" method="get" class="js-filter analog-filter-form">
<input type="hidden" name="set_filter" value="Y" />
		<div class="properties-group properties-group--table js-offers-group-wrap">
			<div class="properties-group__group<?=($bOffersMode ? ' js-offers-group' : '')?>" data-group-code="no-group">
				<div class="properties-group__items js-offers-group__items-wrap font_15">
					<?foreach ($arResult['DISPLAY_PROPERTIES'] as $arProp):
						$rawValue = $arProp['~VALUE'] ?? $arProp['VALUE'] ?? '';
						if (is_array($rawValue)) {
							$rawValue = reset($rawValue);
						}
						$rawValue = is_scalar($rawValue) ? trim((string)$rawValue) : '';
						$propertyCode = (string)($arProp['CODE'] ?? '');
						$propertyId = (int)($arProp['ID'] ?? 0);
						$propertyId = $lvtResolveSmartFilterPropertyId($smartFilterMap, $propertyCode, $propertyId);
						$isFilterable = $lvtIsPropertyFilterable($iblockId, $smartFilterMap, $propertyCode, $propertyId, $rawValue);
						$bHint = $arProp['HINT'] && $arParams['SHOW_HINTS'] == 'Y';
						?>
						<div class="properties-group__item<?=($bOffersMode || $arProp['IS_OFFER'] ? ' js-offers-group__item' : '')?>" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
							<div class="properties-group__name-wrap<?=($bHint ? ' properties-group__name-wrap--whint' : '')?>">
								<span itemprop="name" class="properties-group__name color_666"><?=$arProp['NAME']?></span>
								<?if ($bHint):?>
									<div class="hint hint--down">
										<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
										<div class="tooltip"><?=$arProp['HINT']?></div>
									</div>
								<?endif;?>
							</div>

							<div class="properties-group__value-wrap">
								<div class="properties-group__value color_222" itemprop="value">
									<?if (is_array($arProp['DISPLAY_VALUE']) && count($arProp['DISPLAY_VALUE']) > 1):?>
										<?=implode(', ', $arProp['DISPLAY_VALUE'])?>
									<?else:?>
										<?=$arProp['DISPLAY_VALUE']?>
									<?endif;?>
								</div>
							</div>
							<div class="checkbox_block">
								<?if ($isFilterable):?>
								<input type="checkbox" value="<?=htmlspecialcharsbx($rawValue)?>" name="ANALOG_FILTER[<?=$propertyId?>][]" class="char-filter-checkbox" />
								<?endif;?>
							</div>
						</div>
					<?endforeach;?>

					<?if ($arResult['OFFER_DISPLAY_PROPERTIES']):?>
						<?foreach ($arResult['OFFER_DISPLAY_PROPERTIES'] as $arProp):
							$rawValue = $arProp['~VALUE'] ?? $arProp['VALUE'] ?? '';
							if (is_array($rawValue)) {
								$rawValue = reset($rawValue);
							}
							$rawValue = is_scalar($rawValue) ? trim((string)$rawValue) : '';
							$propertyCode = (string)($arProp['CODE'] ?? '');
							$propertyId = (int)($arProp['ID'] ?? 0);
							$propertyId = $lvtResolveSmartFilterPropertyId($smartFilterMap, $propertyCode, $propertyId);
							$isFilterable = $lvtIsPropertyFilterable($iblockId, $smartFilterMap, $propertyCode, $propertyId, $rawValue);
							$bHint = $arProp['HINT'] && $arParams['SHOW_HINTS'] == 'Y';
							?>
							<div class="properties-group__item js-offers-group__item" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
								<div class="properties-group__name-wrap<?=($bHint ? ' properties-group__name-wrap--whint' : '')?>">
									<span itemprop="name" class="properties-group__name color_666"><?=$arProp['NAME']?></span>
									<?if ($bHint):?>
										<div class="hint hint--down">
											<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
											<div class="tooltip"><?=$arProp['HINT']?></div>
										</div>
									<?endif;?>
								</div>

								<div class="properties-group__value-wrap">
									<div class="properties-group__value color_222" itemprop="value">
										<?if (is_array($arProp['DISPLAY_VALUE']) && count($arProp['DISPLAY_VALUE']) > 1):?>
											<?=implode(', ', $arProp['DISPLAY_VALUE'])?>
										<?else:?>
											<?=$arProp['DISPLAY_VALUE']?>
										<?endif;?>
									</div>
								</div>
								<div class="checkbox_block">
									<?if ($isFilterable):?>
									<input type="checkbox" value="<?=htmlspecialcharsbx($rawValue)?>" name="ANALOG_FILTER[<?=$propertyId?>][]" class="char-filter-checkbox" />
									<?endif;?>
								</div>
							</div>
						<?endforeach;?>
					<?endif;?>
				</div>
			</div>
			<?if ($__filterablePropsCount > 0):?>
			<div class="properties-group__groups">
				<div class="properties-group__items js-offers-group__items-wrap font_15">
					<div class="properties-group__item">
						<div class="analog-filter-actions" style="display:flex; flex-direction:column; align-items:flex-start; gap:8px;">
							<span class="analog-filter-hint" style="display: none;">Выберите хотя бы 1 параметр</span>
							<input type="submit" class="btn btn-default" value="Подобрать похожие" />
						</div>
					</div>
				</div>
			</div>
			<?endif;?>
		</div>
		</form>
	<?endif;?>
<?endif;?>
