<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? $this->setFrameMode(true); ?>
<?if($arParams['IS_AJAX']):?>
	<link rel="stylesheet" href="<?=$stylesPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__).'/style.css';?>">
<?endif;?>
<?
$siteSelectorName = isset($arParams['SITE_SELECTOR_NAME']) ? $arParams['SITE_SELECTOR_NAME'] : '';
				
switch($siteSelectorName){
	case 'FROM_LANG': 
		$nameField = "LANG";
		break;
	case 'FROM_SITE_NAME':
		$nameField = "NAME";
		break;
	default:
		$nameField = "NAME";
		break;
}
?>
<div class="sites fill-theme-parent-all color-theme-parent-all">
	<div class="sites__dropdown <?=$arParams['DROPDOWN_TOP'] ? 'sites__dropdown--top' : ''?>">
		<div class="dropdown dropdown--relative">
			<?
			$counter = 1;
			foreach ($arResult["SITES"] as $key => $arSite):?>
				<?
				$siteLink = '';

				if(
					(
						is_array($arSite['DOMAINS']) && 
						strlen($arSite['DOMAINS'][0])
					) || 
					strlen($arSite['DOMAINS'])
				){
					$siteLink = is_array($arSite['DOMAINS']) ? $arSite['DOMAINS'][0] : $arSite['DOMAINS'];
					$siteLink .= $arSite['DIR'];

					if(strpos($siteLink, 'http://') === false && strpos($siteLink, 'https://') === false){
						$siteLink = '//'.$siteLink;
					}
				}
				
				if($arSite["CURRENT"] == "Y" && !$arParams['ONLY_ICON']) {
					$arCurrent = $arSite;
					$arCurrentLink = $siteLink;
				}?>
				<?if($arSite["CURRENT"] == "Y"):?>
					<div class="sites__option <?=$counter == 1 ? 'sites__option--first' : ''?> <?=$counter == count($arResult["SITES"]) ? 'sites__option--last' : ''?> sites__option--current font_xs"><?=$arSite[$nameField]?></div>
				<?else:?>
					<a class="dark_link sites__option <?=$counter == 1 ? 'sites__option--first' : ''?> <?=$counter == count($arResult["SITES"]) ? 'sites__option--last' : ''?> color-theme-hover font_xs" href="<?=$siteLink?>"><?=$arSite[$nameField]?></a>
				<?endif;?>
			<?
			$counter++;
			endforeach;?>
		</div>
	</div>

	<div class="sites__select light-opacity-hover">
		<span class="icon-block__icon icon-block__only-icon fill-theme-target banner-light-icon-fill menu-light-icon-fill light-opacity-hover">
			<?=TSolution::showIconSvg("", str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__."/images/World.svg") );?>
		</span>
		<?if(!$arParams['ONLY_ICON']):?>
			<div class="sites__current <?=($nameField === 'LANG') ? 'sites__current--upper' : '' ?> color-theme-target icon-block__name font_xs banner-light-text menu-light-text"><?=$arCurrent[$nameField]?></div>
			<?if( is_array($arResult["SITES"]) && count($arResult["SITES"]) > 1 ):?>
				<span class="more-arrow fill-theme-target banner-light-icon-fill menu-light-icon-fill fill-dark-light-block"><?=TSolution::showIconSvg("", SITE_TEMPLATE_PATH."/images/svg/more_arrow.svg", "", "", false);?></span>
			<?endif;?>
		<?endif;?>
	</div>
</div>