<?if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();?>
<?$this->setFrameMode(true);?>
<?if($arResult):?>
	<div class="cabinet-dropdown <?=$arParams['DROPDOWN_TOP'] ? 'cabinet-dropdown--top' : ''?> dropdown-menu-wrapper dropdown-menu-wrapper--visible dropdown-menu-wrapper--woffset dropdown-menu-wrapper--toright">
		<div class=" dropdown-menu-inner rounded-x">
			<?$counter = 1;?>
			<?foreach($arResult as $arItem):?>
				<?$bWithSvg = isset($arItem["PARAMS"]["SVG_ICON"]) && $arItem["PARAMS"]["SVG_ICON"] ;?>
				<div class="cabinet-dropdown__item <?=($arItem["CHILD"] ? "child" : "")?> <?=($bWithSvg ? "stroke-theme-hover stroke-dark-light-block" : "")?>">
					<?if( strpos($arItem["LINK"] ,'?logout=yes') !== false ){
						$arItem["LINK"].= '&'.bitrix_sessid_get();
					}?>
					<?if($arItem["SELECTED"]):?>
						<span class="font_15 dropdown-menu-item dropdown-menu-item--current">
					<?else:?>	
						<a class="font_15 dark_link dropdown-menu-item " href="<?=$arItem["LINK"]?>">
					<?endif;?>
							<?=$arItem["TEXT"]?>
							<?
							$svgIcon = (isset($arItem["PARAMS"]["SVG_ICON"]) && $arItem["PARAMS"]["SVG_ICON"] ? $arItem["PARAMS"]["SVG_ICON"] : "");
							if($svgIcon){
								$fileName = explode('#', $svgIcon)[0];
								$svgPath = $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/images/svg/' . $fileName;
								$svgSize = TSolution::getSvgSizeFromName($svgIcon, ['WIDTH' => 11, 'HEIGHT' => 9]);
								
								if(file_exists($svgPath)){
									echo TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/' . $svgIcon, ' ', $svgSize);
								}
							}							
							?>
					<?if($arItem["SELECTED"]):?>
						</span>
					<?else:?>
						</a>
					<?endif;?>
				</div>
				<?$counter++;?>
			<?endforeach;?>
		</div>
	</div>
<?endif;?>