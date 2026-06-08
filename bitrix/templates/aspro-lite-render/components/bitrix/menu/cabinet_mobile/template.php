<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;
global $USER;
?>
<?if($arResult):?>
	<?
	$bParent = $arResult && $USER->IsAuthorized();
	?>
	<div class="mobilemenu__menu mobilemenu__menu--cabinet">
		<ul class="mobilemenu__menu-list">
			<li class="mobilemenu__menu-item mobilemenu__menu-item--with-icon mobilemenu__menu-item--parent<?=(TSolution::isPersonalPage() ? ' mobilemenu__menu-item--selected' : '')?>">
				<div class="link-wrapper">
					<?$link = TSolution::GetFrontParametrValue('PERSONAL_PAGE_URL', SITE_ID);?>
					<a class="icon-block dark_link bg-opacity-theme-parent-hover fill-theme-parent-all color-theme-parent-all fill-dark-light-block fill-theme-use-svg-hover" href="<?=$link;?>" title="<?=Loc::getMessage('MY_CABINET')?>">
						<?= $USER->IsAuthorized() 
							? TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/header_icons.svg#log-in-22-20', 'cabinet mobilemenu__menu-item-svg fill-theme-target', ['WIDTH' => 22, 'HEIGHT' => 20])
							: TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/header_icons.svg#log-out-18-18', 'cabinet mobilemenu__menu-item-svg fill-theme-target', ['WIDTH' => 18, 'HEIGHT' => 18])
						?>
						<span class="icon-block__content">
							<span class="font_15"><?=Loc::getMessage('MY_CABINET')?></span>
							<?if($bParent):?>
								<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-5', 'down menu-arrow fill-theme-target fill-dark-light-block', ['WIDTH' => 7, 'HEIGHT' => 5]); ?>
							<?endif;?>
						</span>
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
								<a class="dark_link" href="<?=$link;?>">
									<span class="font_18 font_bold"><?=Loc::getMessage('MY_CABINET')?></span>
								</a>
							</div>
						</li>
						<?foreach($arResult as $arItem):?>
							<?$bShowChilds = $arParams['MAX_LEVEL'] > 1;?>
							<?$bParent = $arItem['CHILD'] && $bShowChilds;?>
							<li class="mobilemenu__menu-item<?=($arItem['SELECTED'] ? ' mobilemenu__menu-item--selected' : '')?><?=($bParent ? ' mobilemenu__menu-item--parent' : '')?>">
								<div class="link-wrapper bg-opacity-theme-parent-hover fill-theme-parent-all">
									<?if( strpos($arItem["LINK"] ,'?logout=yes') !== false ){
										$arItem["LINK"].= '&'.bitrix_sessid_get();
									}?>
									<a class="dark_link" href="<?=$arItem["LINK"]?>" title="<?=htmlspecialcharsbx($arItem["TEXT"])?>">
										<span class="font_15"><?=$arItem['TEXT']?></span>
										<?if($bParent):?>
											<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-5', 'down menu-arrow bg-opacity-theme-target fill-theme-target fill-dark-light-block', ['WIDTH' => 7, 'HEIGHT' => 5]); ?>
										<?endif;?>
									</a>
									<?if($bParent):?>
										<span class="toggle_block"></span>
									<?endif;?>
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
												<a class="dark_link" href="<?=$arItem['LINK']?>">
													<span class="font_18 font_bold"><?=$arItem['TEXT']?></span>
												</a>
											</div>
										</li>
										<?foreach($arItem['CHILD'] as $arSubItem):?>
											<?$bShowChilds = $arParams['MAX_LEVEL'] > 2;?>
											<?$bParent = $arSubItem['CHILD'] && $bShowChilds;?>
											<li class="mobilemenu__menu-item<?=($arSubItem['SELECTED'] ? ' mobilemenu__menu-item--selected' : '')?><?=($bParent ? ' mobilemenu__menu-item--parent' : '')?>">
												<div class="link-wrapper stroke-theme-parent-all colored_theme_hover_bg-block animate-arrow-hover color-theme-parent-all">
													<a class="dark_link" href="<?=$arSubItem["LINK"]?>" title="<?=htmlspecialcharsbx($arSubItem["TEXT"])?>">
														<span class="font_15"><?=$arSubItem['TEXT']?></span>
														<?if($bParent):?>
															<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-5', 'down menu-arrow bg-opacity-theme-target fill-theme-target fill-dark-light-block', ['WIDTH' => 7, 'HEIGHT' => 5]); ?>
														<?endif;?>
													</a>
													<?if($bParent):?>
														<span class="toggle_block"></span>
													<?endif;?>
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
																<a class="dark_link" href="<?=$arSubItem['LINK']?>">
																	<span class="font_18 font_bold"><?=$arSubItem['TEXT']?></span>
																</a>
															</div>
														</li>
														<?foreach($arSubItem["CHILD"] as $arSubSubItem):?>
															<?$bShowChilds = $arParams['MAX_LEVEL'] > 3;?>
															<?$bParent = $arSubSubItem['CHILD'] && $bShowChilds;?>
															<li class="mobilemenu__menu-item<?=($arSubSubItem['SELECTED'] ? ' mobilemenu__menu-item--selected' : '')?><?=($bParent ? ' mobilemenu__menu-item--parent' : '')?>">
																<div class="link-wrapper stroke-theme-parent-all">
																	<a class="dark_link" href="<?=$arSubSubItem["LINK"]?>" title="<?=htmlspecialcharsbx($arSubSubItem["TEXT"])?>">
																		<span class="font_15"><?=$arSubSubItem['TEXT']?></span>
																		<?if($bParent):?>
																			<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-5', 'down menu-arrow bg-opacity-theme-target fill-theme-target fill-dark-light-block', ['WIDTH' => 7, 'HEIGHT' => 5]); ?>
																		<?endif;?>
																	</a>
																	<?if($bParent):?>
																		<span class="toggle_block"></span>
																	<?endif;?>
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
																				<a class="dark_link" href="<?=$arSubSubItem['LINK']?>">
																					<span class="font_18 font_bold"><?=$arSubSubItem['TEXT']?></span>
																				</a>
																			</div>
																		</li>
																		<?foreach($arSubSubItem["CHILD"] as $arSubSubSubItem):?>
																			<li class="mobilemenu__menu-item<?=($arSubSubSubItem['SELECTED'] ? ' mobilemenu__menu-item--selected' : '')?>">
																				<div class="link-wrapper stroke-theme-parent-all">
																					<a class="dark_link" href="<?=$arSubSubSubItem["LINK"]?>" title="<?=htmlspecialcharsbx($arSubSubSubItem["TEXT"])?>">
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
				<?endif;?>
			</li>
		</ul>
	</div>
<?endif;?>