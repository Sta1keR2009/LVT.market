<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();

use \Bitrix\Main\Localization\Loc;
$this->setFrameMode(true);

$bCompact = boolval($arParams['COMPACT']);

$blockClasses = '';
if ($bCompact) {
	$blockClasses .= ' sale-linked--compact';
}

$elTitle = $arParams['ELEMENT_TITLE'] ? $arParams['ELEMENT_TITLE'] : GetMessage('PRODUCT_TITLE');
?>

<?if($arResult['ITEMS']):?>
	<div class="sale-linked<?=$blockClasses?> <?=$templateName?>-template line-block line-block--gap line-block--gap-16">
		<div class="line-block__item flexbox--align-self-start">	
			<div class="sale-linked__icon outer-rounded-x"></div>
		</div>
		<div class="line-block__item">
			<div class="sale-linked__list">
				<?foreach($arResult['ITEMS'] as $i => $arItem):?>
					<?
					// edit/add/delete buttons for edit mode
					$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
					$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
					?>
					<div class="sale-linked__wrapper">
						<div class="sale-linked__item" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
							<div class="sale-linked__item-title font_normal <?= $bCompact ? 'font_14' : 'font_15'; ?>">
								<span class="dark_link dotted" data-event="jqm" data-param-form_id="fast_view_sale" data-name="fast_view_sale" data-param-iblock_id="<?=$arItem["IBLOCK_ID"];?>" data-param-id="<?=$arItem["ID"];?>">
									<?=$arItem['NAME'];?>
								</span>
							</div>	
						</div>
					</div>
				<?endforeach;?>
			</div>
		</div>
	</div>
<?endif;?>