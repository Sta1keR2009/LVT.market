<?php

use \Bitrix\Main\Localization\Loc;

?>
<? if($arParams['SHOW_LINK_GOODS'] == 'Y' && 
	$arParams['LINK_GOODS_IBLOCK_ID'] > 0 && 
	in_array('sections', $GLOBALS["SHOW_TYPE_ITEMS"]) 
): ?>
	<? ob_start(); ?>
		<?
		$GLOBALS['arrFilterSectionsBrand'] = [];

		$arItemsFilter = array(
			'PROPERTY_' . $arParams['LINK_GOODS_PROP_CODE'] => $arResult['ID'],
			'SECTION_GLOBAL_ACTIVE' => 'Y',
			"ACTIVE"=>"Y",
			"IBLOCK_ID" => $arParams['LINK_GOODS_IBLOCK_ID']
		);
		TSolution::makeElementFilterInRegion($arItemsFilter);
		if(is_array($GLOBALS['arRegionLink'])){
			$arItemsFilter = array_merge($GLOBALS['arRegionLink'], $arItemsFilter);
		}
		$arSelect = ["ID", "IBLOCK_ID", "IBLOCK_SECTION_ID"];

		$arItems = \TSolution\Cache::CIBlockElement_GetList(
			[
				"CACHE" => [
					"TAG" => \TSolution\Cache::GetIBlockCacheTag($arParams['LINK_GOODS_IBLOCK_ID']),
					"MULTI" => 'Y'
				]
			],
			$arItemsFilter,
			false,
			false,
			$arSelect
		);
		if ($arItems) {
			$arSectionsID = [];
			foreach ($arItems as $arItem) {
				if ($arItem["IBLOCK_SECTION_ID"]) {
					if (is_array($arItem["IBLOCK_SECTION_ID"])) {
						$arSectionsID = array_merge($arSectionsID, $arItem["IBLOCK_SECTION_ID"]);
					} else {
						$arSectionsID[] = $arItem["IBLOCK_SECTION_ID"];
					}
				}
			}

			if($arSectionsID){
				$arSectionsID = array_unique($arSectionsID);
			}

			$GLOBALS['arrFilterSectionsBrand'] = ['ID' => $arSectionsID];
			$GLOBALS['arrFilterSectionsBrandsCount'] = $arItemsFilter;
		}
		?>
		<?if ($GLOBALS['arrFilterSectionsBrand']):?>
			<?TSolution\Functions::showBlockHtml([
				'FILE' => '/detail_linked_sections.php',
				'PARAMS' => array_merge(
					$arParams,
					array(
						"FILTER_ELEMENTS_CNT" => $arItemsFilter,
						"USE_FILTER_SECTION" => "Y",
						"BRAND_NAME" => $arResult["NAME"],
						"BRAND_CODE" => $templateData["ELEMENT_CODE"],
					)
				)
			]);?>
		<?endif;?>
	<? $html = trim(ob_get_clean()); ?>
	<? if ($html && strpos($html, 'error') === false): ?>
		<div class="detail-block ordered-block">
			<h3 class="switcher-title">
				<?=
				$arParams['T_LINK_SECTIONS']
					? str_replace('#NAME#', $arResult['NAME'], $arParams['T_LINK_SECTIONS'])
					: Loc::getMessage('EPILOG_BLOCK__SECTIONS', [
						'#NAME#' => $arResult['NAME'],
					])
				?>
			</h3>
			<?= $html ?>
		</div>
	<? endif; ?>
<? endif; ?>