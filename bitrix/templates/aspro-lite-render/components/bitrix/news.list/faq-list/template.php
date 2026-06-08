<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?$this->setFrameMode(true);?>
<?
$bColumns = $arParams['VIEW_TYPE'] === 'columns';
if ($bColumns) {
	$arItemColumns = ['', ''];
}
?>
<?if($arResult['ITEMS']):?>
	<?
	$templateData['ITEMS'] = true;

	$bShowTitle = $arParams['TITLE'] && $arParams['SHOW_TITLE'];
	$bShowTitleLink = $arParams['RIGHT_TITLE'] && $arParams['RIGHT_LINK'];
	$bShowLeftBlock = $arParams['LEFT_BLOCK'] && !$arParams['HIDE_LEFT_TEXT_BLOCK'];

	$arParams['NAME_SIZE'] = $arParams['NAME_SIZE'] ?: 18;
	?>
	<div class="faq-list <?=$templateName;?>-template">

		<?if (!$bShowLeftBlock):?>
			<?=\TSolution\Functions::showTitleBlock([
				'PATH' => 'faq-list',
				'PARAMS' => $arParams
			]);?>
		<?endif;?>

		<?if($arParams['MAXWIDTH_WRAP']):?>
			<div class="maxwidth-theme">
		<?endif;?>

			<div class="faq-container">
				<div class="flexbox flexbox--direction-row flexbox--column-t991">
					<?=\TSolution\Functions::showTitleInLeftBlock([
						'PATH' => 'faq-list',
						'PARAMS' => $arParams,
						'VISIBLE' => $bShowLeftBlock
					]);?>

					<div class="faq-items flex-grow-1<?= $bColumns ? ' grid-list grid-list--items-2-768 grid-list--no-gap grid-list--gap-column-24' : ''; ?>">
						<div class="accordion<?= count($arResult['ITEMS']) > 1 ? ' accordion--column-left' : ''; ?> accordion-type-1">
							<?foreach($arResult['ITEMS'] as $key => $arItem):?>
								<?
								// edit/add/delete buttons for edit mode
								$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
								$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

								$bShowBtn = $arItem['DISPLAY_PROPERTIES']['TITLE_BUTTON']['VALUE'] && ($arItem['DISPLAY_PROPERTIES']['LINK_BUTTON']['VALUE'] || $arItem['DISPLAY_PROPERTIES']['FORM_CODE']['VALUE']);

								if ($bColumns)
									ob_start();
								?>
								<div class="item-accordion-wrapper shadow-hovered shadow-no-border-hovered" id="<?=$this->GetEditAreaId($arItem['ID'])?>"<?= $bColumns ? " data-order='".$key."'" : ''; ?>>
									<a class="accordion-head accordion-close stroke-theme-hover" data-toggle="collapse" data-parent="#accordion<?=$arItem['ID']?>" href="#accordion<?=$arItem['ID']?>">
										<span class="switcher-title color_222 font_<?=$arParams['NAME_SIZE']?>"><?=$arItem['NAME']?></span>
										<?=TSolution::showIconSvg('right-arrow', SITE_TEMPLATE_PATH.'/images/svg/Plus_lg.svg');?>
									</a>
									<?if ($arItem['PREVIEW_TEXT'] || $bShowBtn ):?>
										<div id="accordion<?=$arItem['ID']?>" class="panel-collapse collapse">
											<div class="accordion-body color_666">
												<?if ($arItem['PREVIEW_TEXT']):?>
													<div class="accordion-preview">
														<?=$arItem['PREVIEW_TEXT']?>
													</div>
												<?endif;?>
												<?if ($bShowBtn):?>
													<div class="accordion-btn">
														<?if ($arItem['DISPLAY_PROPERTIES']['FORM_CODE']['VALUE']):?>
															<span class="btn btn-default btn-transparent-border animate-load" data-event="jqm" data-param-id="<?=TSolution::getFormID($arItem['DISPLAY_PROPERTIES']['FORM_CODE']['VALUE'])?>" data-name="question"><?=$arItem['DISPLAY_PROPERTIES']['TITLE_BUTTON']['VALUE'];?></span>
														<?else:?>
															<a class="btn btn-default" href="<?=$arItem['DISPLAY_PROPERTIES']['LINK_BUTTON']['VALUE'];?>" target="_blank">
																<?=$arItem['DISPLAY_PROPERTIES']['TITLE_BUTTON']['VALUE'];?>
															</a>
														<?endif;?>
													</div>
												<?endif;?>
											</div>
										</div>
									<?endif;?>
								</div>
								<?
								if ($bColumns)
									$arItemColumns[intval($key%2 !== 0)] .= trim(ob_get_clean());
								?>
							<?endforeach;?>
							
							<?= $bColumns ? $arItemColumns[0] : ''; ?>
						</div>
						<? if ($bColumns): ?>
							<div class="accordion accordion--column-right accordion-type-1">
								<?= $arItemColumns[1]; ?>
							</div>
						<? endif; ?>
					</div>
				</div>
			</div>

		<?if($arParams['MAXWIDTH_WRAP']):?>
			</div>
		<?endif;?>
	</div>
<?endif;?>