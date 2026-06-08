<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

global $arTheme, $APPLICATION;
\TSolution\Extensions::init('contacts');

$bUseMap = TSolution::GetFrontParametrValue('CONTACTS_USE_MAP', SITE_ID) != 'N';
$typeMap = TSolution::GetFrontParametrValue('CONTACTS_TYPE_MAP', SITE_ID);
$bUseTabs = $bUseMap && TSolution::GetFrontParametrValue('CONTACTS_USE_TABS', SITE_ID) != 'N';

Bitrix\Main\Loader::includeModule('catalog');

$arSelect = [
	'ID',
	'ADDRESS',
	'SORT',
	'TITLE',
	'GPS_N',
	'GPS_S',
	'SCHEDULE',
	'EMAIL',
	'PHONE',
	'UF_METRO',
	'UF_PHONES',
];

$arFilter = array('ACTIVE' => 'Y');

if (
	strlen($arParams['FILTER_NAME']) &&
	$GLOBALS[$arParams['FILTER_NAME']]
) {
	$arFilter = array_merge($arFilter, $GLOBALS[$arParams['FILTER_NAME']]);
}

$arStores = [];
$dbRes = CCatalogStore::GetList(
	[
		'ID' => 'ASC'
	],
	$arFilter,
	false,
	false, 
	$arSelect
);
while ($store = $dbRes->GetNext()) {
	$store['TITLE'] = htmlspecialchars_decode($store['TITLE']);
	$store['ADDRESS'] = htmlspecialchars_decode($store['ADDRESS']);
	$store['UF_METRO'] = TSolution::unserialize($store['~UF_METRO']);
	$store['UF_PHONES'] = TSolution::unserialize($store['~UF_PHONES']);

	$url = CComponentEngine::makePathFromTemplate($arParams['SEF_URL_TEMPLATES']["element"], array("store_id" => $store["ID"]));

	$arStores[] = [
		'SORT' => $store['SORT'],
		'URL' => $url,
		'TITLE' => $store['TITLE'],
		'ADDRESS' => $store['TITLE'].((strlen($store['TITLE']) && strlen($store['ADDRESS'])) ? ', ' : '').$store['ADDRESS'],
		'EMAIL' => htmlspecialchars_decode($store['EMAIL']),
		'PHONE' => htmlspecialchars_decode($store['PHONE']),
		'SCHEDULE' => htmlspecialchars_decode($store['SCHEDULE']),
		'GPS_N' => $store['GPS_N'],
		'GPS_S' => $store['GPS_S'],
		'METRO' => $store['UF_METRO'],
		'PHONE' => $store['UF_PHONES'] ? array_unique(array_merge((array)$store['PHONE'], (array)$store['UF_PHONES'])) : $store['PHONE'],
	];
}

$itemsCnt = count($arStores);
?>
<div class="contacts-v2" itemscope itemtype="http://schema.org/Organization">
	<?//hidden text for validate microdata?>
	<div class="hidden">
		<?global $arSite;?>
		<span itemprop="name"><?=$arSite["NAME"]?></span>
	</div>

	<div class="contacts__row">
		<div class="contacts__col contacts__col--left flex-1">
			<div class="contacts__content-wrapper">
				<div class="contacts__panel-wrapper">
					<?
					// tabs
					if($bUseTabs && $bUseMap){
						include realpath(__DIR__.'/include_tabs.php');
					}
					?>
				</div>

				<div class="contacts__ajax_items <?=($bUseTabs && $bUseMap ? 'contacts__tab-content contacts__tab-content--map' : '')?>">
					<?if($itemsCnt):?>
						<?
						if($bUseMap){
							include realpath(__DIR__.'/include_map.php');
						}
						?>
					<?else:?>
						<div class="alert alert-warning"><?=GetMessage('SECTION_EMPTY')?></div>
					<?endif;?>

					<?$APPLICATION->IncludeComponent(
						"bitrix:catalog.store.list",
						"main",
						Array(
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"PHONE" => $arParams["PHONE"],
							"SCHEDULE" => $arParams["SCHEDULE"],
							"MIN_AMOUNT" => $arParams["MIN_AMOUNT"],
							"TITLE" => $arParams["TITLE"],
							"FILTER_NAME" => $arParams['FILTER_NAME'],
							"SET_TITLE" => "N",
							"PATH_TO_ELEMENT" => $arResult["PATH_TO_ELEMENT"],
							"PATH_TO_LISTSTORES" => $arResult["PATH_TO_LISTSTORES"],
							"USE_MAP" => $bUseMap ? "Y" : "N",
							"MAP_TYPE" => $typeMap,
						),
						$component
					);?>
				</div>
			</div>
		</div>

		<?if ($arParams['STICKY_PANEL'] !== 'N' && (!defined('STORES_PAGE') || !STORES_PAGE)):?>
			<div class="contacts__col contacts__col--right">
				<?ob_start();?>
				<?TSolution::showContactImg();?>
				<?$htmlImage = trim(ob_get_clean());?>

				<div class="contacts__sticky-panel sticky-block outer-rounded-x<?=($htmlImage ? '' : ' contacts__sticky-panel--without-image')?>">
					<?if($htmlImage):?>
						<div class="contacts__sticky-panel__image dark-block-after outer-rounded-x">
							<?=$htmlImage?>
							<?TSolution::showContactAddr(Loc::getMessage('T_CONTACTS_MAIN_OFFICE'), false);?>
						</div>
					<?endif;?>

					<div class="contacts__sticky-panel__info">
						<?TSolution::showContactAddr(Loc::getMessage('T_CONTACTS_MAIN_OFFICE'), false, 'font_18 color_222 switcher-title');?>
						<div class="contacts__sticky-panel__properties">
							<div class="contacts__sticky-panel__property">
								<?TSolution::showContactSchedule(Loc::getMessage('T_CONTACTS_SCHEDULE'), false);?>
							</div>
							<div class="contacts__sticky-panel__property">
								<?TSolution::showContactPhones(Loc::getMessage('T_CONTACTS_PHONE'), false);?>
							</div>
							<div class="contacts__sticky-panel__property">
								<?TSolution::showContactEmail(Loc::getMessage('T_CONTACTS_EMAIL'), false);?>
							</div>
						</div>
						<?if($bUseFeedback):?>
							<div class="contacts__sticky-panel__btn-wraper">
								<span>
									<span class="btn btn-default btn-wide btn-transparent-border bg-theme-target border-theme-target animate-load" data-event="jqm" data-param-id="aspro_lite_question" data-name="question"><?=Loc::getMessage('T_CONTACTS_QUESTION2')?></span>
								</span>
							</div>
						<?endif;?>
					</div>
				</div>
			</div>
		<?endif;?>
	</div>
</div>