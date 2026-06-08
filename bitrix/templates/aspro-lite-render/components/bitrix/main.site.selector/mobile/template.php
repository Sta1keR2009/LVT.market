<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;
?>
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
<?if($arResult['SITES']):?>
	<div class="mobilemenu__menu mobilemenu__menu--sites">
		<ul class="mobilemenu__menu-list">
			<li class="mobilemenu__menu-item mobilemenu__menu-item--with-icon mobilemenu__menu-item--parent">
				<div class="link-wrapper bg-opacity-theme-parent-hover fill-theme-parent-all color-theme-parent-all">
					<?foreach($arResult['SITES'] as $arSite):?>
						<?if($arSite['CURRENT'] === 'Y'):?>
							<a class="dark_link <?=($nameField == 'LANG' ? 'link-sites--uppercase' : '')?>" href="" title="<?=htmlspecialcharsbx($arSite[$nameField])?>">
								<?=TSolution::showIconSvg(' mobilemenu__menu-item-svg fill-theme-target', str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__.'/images/World.svg') );?>
								<span class="font_15"><?=$arSite[$nameField]?></span>
								<?=TSolution::showIconSvg(' down menu-arrow bg-opacity-theme-target fill-theme-target fill-dark-light-block', SITE_TEMPLATE_PATH.'/images/svg/Triangle_right.svg', '', '', true, false);?>
							</a>
							<span class="toggle_block"></span>
						<?endif;?>
					<?endforeach;?>
				</div>
				<ul class="mobilemenu__menu-dropdown dropdown">
					<li class="mobilemenu__menu-item mobilemenu__menu-item--back">
						<div class="link-wrapper stroke-theme-parent-all colored_theme_hover_bg-block animate-arrow-hover color-theme-parent-all">
							<a class="arrow-all arrow-all--wide stroke-theme-target" href="" rel="nofollow">
								<?=TSolution::showIconSvg(' arrow-all__item-arrow', SITE_TEMPLATE_PATH.'/images/svg/Arrow_lg.svg');?>
								<div class="arrow-all__item-line colored_theme_hover_bg-el"></div>
							</a>
						</div>
					</li>
					<li class="mobilemenu__menu-item mobilemenu__menu-item--title">
						<div class="link-wrapper">
							<a class="dark_link" href="">
								<span class="font_18 font_bold"><?=Loc::getMessage('T_'.$nameField)?></span>
							</a>
						</div>
					</li>
					<?foreach($arResult['SITES'] as $arSite):?>
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
						?>
						<li class="mobilemenu__menu-item<?=($arSite['CURRENT'] === 'Y' ? ' mobilemenu__menu-item--selected' : '')?>">
							<div class="link-wrapper bg-opacity-theme-parent-hover fill-theme-parent-all">
								<a class="dark_link" href="<?=$siteLink?>" title="<?=htmlspecialcharsbx($arSite[$nameField])?>">
									<span class="font_15"><?=$arSite[$nameField]?></span>
								</a>
							</div>
						</li>
					<?endforeach;?>
				</ul>
			</li>
		</ul>
	</div>
<?endif;?>