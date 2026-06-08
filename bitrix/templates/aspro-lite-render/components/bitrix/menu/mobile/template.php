<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;
?>
<?if($arResult):?>
	<div class="mobilemenu__menu mobilemenu__menu--top">
		<ul class="mobilemenu__menu-list">
			<?foreach($arResult as $arItem):?>
				<?$bShowChilds = $arParams['MAX_LEVEL'] > 1;?>
				<?$bParent = $arItem['CHILD'] && $bShowChilds;?>
				<li class="mobilemenu__menu-item<?=($arItem['SELECTED'] ? ' mobilemenu__menu-item--selected' : '')?><?=($bParent ? ' mobilemenu__menu-item--parent' : '')?>">
					<div class="link-wrapper fill-theme-parent-all">
						<a class="dark_link" href="<?=$arItem['LINK']?>" title="<?=htmlspecialcharsbx($arItem['TEXT'])?>">
							<span class=" font_18"><?=$arItem['TEXT']?></span>
							<?if($bParent):?>
								<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-5', 'down menu-arrow bg-opacity-theme-target fill-theme-target fill-dark-light-block', ['WIDTH' => 7, 'HEIGHT' => 5]); ?>
							<?endif;?>
							
							<?if($bParent):?>
								<span class="toggle_block"></span>
							<?endif;?>
						</a>
					</div>
					<?if($bParent):?>
						<ul class="mobilemenu__menu-dropdown dropdown">
							<li class="mobilemenu__menu-item mobilemenu__menu-item--back">
								<div class="link-wrapper stroke-theme-parent-all colored_theme_hover_bg-block color-theme-parent-all">
									<a class="dark_link arrow-all stroke-theme-target" href="" rel="nofollow">
										<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#left-7-12', 'arrow-all__item-arrow', ['WIDTH' => 7, 'HEIGHT' => 12]); ?>
										<?= GetMessage('LITE_T_MENU_BACK'); ?>
									</a>
								</div>
							</li>
							<li class="mobilemenu__menu-item mobilemenu__menu-item--title">
								<div class="link-wrapper">
									<a class="dark_link stroke-theme-hover stroke-dark-light mobilemenu__menu-parent-link" href="<?=$arItem['LINK']?>">
										<span class="font_18 font_bold"><?=$arItem['TEXT']?></span>
										<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-12', 'arrow-parent__item-arrow', ['WIDTH' => 7, 'HEIGHT' => 12]); ?>
									</a>
								</div>
							</li>
							<?foreach($arItem['CHILD'] as $arSubItem):?>
								<?$bShowChilds = $arParams['MAX_LEVEL'] > 2;?>
								<?$bParent = $arSubItem['CHILD'] && $bShowChilds;?>
								<li class="mobilemenu__menu-item<?=($arSubItem['SELECTED'] ? ' mobilemenu__menu-item--selected' : '')?><?=($bParent ? ' mobilemenu__menu-item--parent' : '')?>">
									<div class="link-wrapper fill-theme-parent-all">
										<a class="dark_link" href="<?=$arSubItem['LINK']?>" title="<?=htmlspecialcharsbx($arSubItem['TEXT'])?>">
											<span class="font_15"><?=$arSubItem['TEXT']?></span>
											<?if($bParent):?>
												<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-5', 'down menu-arrow bg-opacity-theme-target fill-theme-target fill-dark-light-block', ['WIDTH' => 7, 'HEIGHT' => 5]); ?>
											<?endif;?>
											
											<?if($bParent):?>
												<span class="toggle_block"></span>
											<?endif;?>
										</a>
									</div>
									<?if($bParent):?>
										<ul class="mobilemenu__menu-dropdown dropdown">
											<li class="mobilemenu__menu-item mobilemenu__menu-item--back">
												<div class="link-wrapper stroke-theme-parent-all colored_theme_hover_bg-block color-theme-parent-all">
													<a class="dark_link arrow-all stroke-theme-target" href="" rel="nofollow">
														<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#left-7-12', 'arrow-all__item-arrow', ['WIDTH' => 7, 'HEIGHT' => 12]); ?>
														<?= GetMessage('LITE_T_MENU_BACK'); ?>
													</a>
												</div>
											</li>
											<li class="mobilemenu__menu-item mobilemenu__menu-item--title">
												<div class="link-wrapper">
													<a class="dark_link stroke-theme-hover stroke-dark-light mobilemenu__menu-parent-link" href="<?=$arSubItem['LINK']?>">
														<span class="font_18 font_bold"><?=$arSubItem['TEXT']?></span>
														<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-12', 'arrow-parent__item-arrow', ['WIDTH' => 7, 'HEIGHT' => 12]); ?>
													</a>
												</div>
											</li>
											<?foreach($arSubItem["CHILD"] as $arSubSubItem):?>
												<?$bShowChilds = $arParams['MAX_LEVEL'] > 3;?>
												<?$bParent = $arSubSubItem['CHILD'] && $bShowChilds;?>
												<li class="mobilemenu__menu-item<?=($arSubSubItem['SELECTED'] ? ' mobilemenu__menu-item--selected' : '')?><?=($bParent ? ' mobilemenu__menu-item--parent' : '')?>">
													<div class="link-wrapper fill-theme-parent-all">
														<a class="dark_link" href="<?=$arSubSubItem['LINK']?>" title="<?=htmlspecialcharsbx($arSubSubItem['TEXT'])?>">
															<span class="font_15"><?=$arSubSubItem['TEXT']?></span>
															<?if($bParent):?>
																<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-5', 'down menu-arrow bg-opacity-theme-target fill-theme-target fill-dark-light-block', ['WIDTH' => 7, 'HEIGHT' => 5]); ?>
															<?endif;?>
															
															<?if($bParent):?>
																<span class="toggle_block"></span>
															<?endif;?>
														</a>
													</div>
													<?if($bParent):?>
														<ul class="mobilemenu__menu-dropdown dropdown">
															<li class="mobilemenu__menu-item mobilemenu__menu-item--back">
																<div class="link-wrapper stroke-theme-parent-all colored_theme_hover_bg-block color-theme-parent-all">
																	<a class="dark_link arrow-all stroke-theme-target" href="" rel="nofollow">
																		<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#left-7-12', 'arrow-all__item-arrow', ['WIDTH' => 7, 'HEIGHT' => 12]); ?>
																		<?= GetMessage('LITE_T_MENU_BACK'); ?>
																	</a>
																</div>
															</li>
															<li class="mobilemenu__menu-item mobilemenu__menu-item--title">
																<div class="link-wrapper">
																	<a class="dark_link stroke-theme-hover stroke-dark-light mobilemenu__menu-parent-link" href="<?=$arSubSubItem['LINK']?>">
																		<span class="font_18 font_bold"><?=$arSubSubItem['TEXT']?></span>
																		<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-12', 'arrow-parent__item-arrow', ['WIDTH' => 7, 'HEIGHT' => 12]); ?>
																	</a>
																</div>
															</li>
															<?foreach($arSubSubItem["CHILD"] as $arSubSubSubItem):?>
																<li class="mobilemenu__menu-item bg-opacity-theme-parent-hover fill-theme-parent-all<?=($arSubSubSubItem['SELECTED'] ? ' mobilemenu__menu-item--selected' : '')?>">
																	<div class="link-wrapper fill-theme-parent-all">
																		<a class="dark_link" href="<?=$arSubSubSubItem['LINK']?>" title="<?=htmlspecialcharsbx($arSubSubSubItem['TEXT'])?>">
																			<span class="font_15"><?=$arSubSubSubItem['TEXT']?></span>
																		</a>
																	</div>
																</li>
															<?endforeach;?>
														</ul>
													<?endif;?>
												</li>
											<?endforeach;?>
										</ul>
									<?endif;?>
								</li>
							<?endforeach;?>
						</ul>
					<?endif;?>
				</li>
			<?endforeach;?>
		</ul>
	</div>
<?endif;?>