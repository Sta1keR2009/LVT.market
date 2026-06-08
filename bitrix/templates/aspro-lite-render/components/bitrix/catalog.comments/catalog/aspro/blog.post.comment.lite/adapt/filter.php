<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var int $totalComments
 */

$arFilterButtons = $arParams['REVIEW_FILTER_BUTTONS'] ?? [];
$arAvailableFilter = [];
$arSessionFilter = $_SESSION['filter'];

if (in_array('RATING', $arFilterButtons)) {
	$arAvailableFilter['RATING'] = [
		"NAME" => GetMessage("T_FILTER_RATING"),
		"TYPE" => "LIST",
		"INPUT_TYPE" => "checkbox",
		"VALUES" => array_filter([
			'1' => GetMessage("T_FILTER_RATING_1"),
			'2' => GetMessage("T_FILTER_RATING_2"),
			'3' => GetMessage("T_FILTER_RATING_3"),
			'4' => GetMessage("T_FILTER_RATING_4"),
			'5' => GetMessage("T_FILTER_RATING_5"),
		], fn($key) => in_array($key, $arParams['AVAILABLE_RATING']), ARRAY_FILTER_USE_KEY),
	];
}
if (in_array('PHOTO', $arFilterButtons)) {
	$arAvailableFilter['PHOTO'] = [
		"NAME" => GetMessage("T_FILTER_PHOTO"),
		"TYPE" => "CHECKBOX",
	];
}
if (in_array('TEXT', $arFilterButtons)) {
	$arAvailableFilter['TEXT'] = [
		"NAME" => GetMessage("T_FILTER_TEXT"),
		"TYPE" => "CHECKBOX",
	];
}
?>
<?foreach ($arAvailableFilter as $filter => $arOption):?>
	<?
	switch($arOption['TYPE']) {
		case 'CHECKBOX':
			$currentValue = $_SESSION['REVIEW_FILTER'][$filter] ?? 'N';
			break;
		case 'LIST':
			$currentValue = match ($arOption['INPUT_TYPE']) {
				'checkbox' => (isset($_SESSION['REVIEW_FILTER'][$filter]) ? (array)$_SESSION['REVIEW_FILTER'][$filter] : []),
				default => ($_SESSION['REVIEW_FILTER'][$filter] ?? ''),
			};
			break;
	}
	?>
	<?// Filter type list?>
	<?if ($arOption['TYPE'] === 'LIST' && count($arOption['VALUES']) > 1):?>
		<?if ($filter === 'RATING'):?>
			<div class="line-block line-block--gap line-block--gap-8 line-block--flex-wrap">
				<?foreach ($arOption['VALUES'] as $key => $value):?>
					<div class="form-input form-input--<?=$arOption['INPUT_TYPE'];?> form-input--rating flex-1">
						<input id="filter-panel-<?=strtolower($filter);?>-<?=$key;?>" 
							class="filter-panel__sort-form-input form-radiobox__input"
							name="filter[<?=$filter;?>]<?=$arOption['INPUT_TYPE'] === 'checkbox' ? '[]' : '';?>" 
							type="<?=$arOption['INPUT_TYPE'];?>" 
							value="<?=$key;?>" 
							<?=in_array($key, $currentValue) ? 'checked' : '';?>
						/>
						<label class="filter-radiobox__label--chip chip chip--transparent bordered" for="filter-panel-<?=strtolower($filter);?>-<?=$key;?>">
							<span class="chip__label">
								<span class="line-block line-block--gap line-block--gap-6 line-block--justify-center">
									<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . "/images/svg/catalog/item_icons.svg#star-13-13", 'rating__star-svg--filled', [
										'WIDTH' => 16,
										'HEIGHT' => 16,
									]);?>
									<span><?=$key;?></span>
								</span>
							</span>
						</label>
					</div>
				<?endforeach;?>
			</div>
		<?else:?>
			<div class="bx_filter_parameters_box filter-panel__sort-form__item dropdown-select dropdown-select--with-dropdown">
				<div class="bx_filter_block limited_block">
					<div class="bx_filter_parameters_box_title title filter-panel__sort-form-item-title dropdown-select__title font_14 font_large fill-dark-light bordered rounded-x<?=$currentValue ? ' filter-panel__sort-form-item-title--active' : ''?>">
						<div class="form-checkbox line-block line-block--gap">
							<div><?=$arOption['NAME'];?></div>
							<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', 'dropdown-select__icon-down', [
								'WIDTH' => 7,
								'HEIGHT' => 5,
							]);?>
						</div>
						<?if ($currentValue):?>
							<button type="button" class="btn--no-btn-appearance delete_filter filter-panel__sort-form-item-clear">
								<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/catalog/item_icons.svg#close-8-8', '', [
									'HEIGHT' => 8,
									'WIDTH' => 8,
								]);?>
							</button>
						<?endif;?>
					</div>
					
					<div class="mobile-lineblock-opened bx_filter_block dropdown-select__list dropdown-menu-wrapper" role="menu">
						<div class="dropdown-menu-inner rounded-x filter_values">
							<div class="bx_filter_parameters_box_container">
								<div class="form-<?=$arOption['INPUT_TYPE'];?> form-<?=$arOption['INPUT_TYPE'];?>--margined scrolled scrollbar">
									<?foreach($arOption['VALUES'] as $key => $value):?>
										<?
										$selectedValue = in_array($key, $currentValue);
										$labelClassList = ['filter-panel__sort-form-input bx_filter_param_label dropdown-menu-item color_222'];
										$labelClassList[] = 'form-'.$arOption['INPUT_TYPE'].'__label';
										if (
											$filter === 'RATING' 
											&& !in_array($key, $arParams['AVAILABLE_RATING'])
										) {
											$labelClassList[] = 'dropdown-menu-item--disabled';
										}
										?>
										<input class="filter-panel__sort-form-input form-<?=$arOption['INPUT_TYPE'];?>__input"
											id="filter-panel-<?=strtolower($filter);?>-<?=$key;?>" 
											name="filter[<?=$filter;?>]<?=$arOption['INPUT_TYPE'] === 'checkbox' ? '[]' : '';?>" 
											type="<?=$arOption['INPUT_TYPE'];?>" 
											value="<?=$key ?: '';?>" 
											<?=$selectedValue ? 'checked' : '';?> 
										/>	
										<label class="<?=TSolution\Utils::implodeClasses($labelClassList);?>"
											for="filter-panel-<?=strtolower($filter);?>-<?=$key;?>"
										>
											<span class="bx_filter_input_checkbox"><?=$value;?></span>
											<span class="form-checkbox__box form-box"></span>
										</label>
									<?endforeach;?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?endif;?>
	<?endif;?>

	<?if ($arOption['TYPE'] === 'CHECKBOX'): // Filter type checkbox?>
		<div class="filter-panel__sort-form__item filter label_block">
			<input class="filter-panel__sort-form-input form-checkbox__input"
				id="filter-panel-<?=strtolower($filter);?>" 
				name="filter[<?=$filter;?>]" 
				type="checkbox" 
				value="Y" 
				<?=$currentValue === 'Y' ? 'checked' : '';?> 
			/>
			<label class="form-checkbox__label form-checkbox__label--sm" for="filter-panel-<?=strtolower($filter);?>">
				<span class="form-checkbox__box form-checkbox__box--static"></span>
				<?=$arOption['NAME'];?>
			</label>
		</div>
	<?endif;?>
<?endforeach;?>