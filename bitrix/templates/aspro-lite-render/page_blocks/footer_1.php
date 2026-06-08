<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/footer/settings.php');
?>
<footer id="footer" class="footer-1 footer footer--color-<?=htmlspecialcharsbx($footerColor)?>">
	<div class="footer__main-part">
		<div class="maxwidth-theme">
			<div class="footer__main-part-inner">
				<div class="footer__part footer__part--left flex-100-991 flex-100-767 flex-1">
					<?//check subscribe text?>
					<?$blockOptions = array(
						'PARAM_NAME' => 'FOOTER_TOGGLE_SUBSCRIBE',
						'BLOCK_TYPE' => 'SUBSCRIBE',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'SUBSCRIBE_TEMPLATE' => 'footer',
						'VISIBLE' => $bShowSubscribe && \Bitrix\Main\ModuleManager::isModuleInstalled("subscribe"),
						'SUBSCRIBE_PARAMS' => array(),
						'WRAPPER' => 'footer__top-part',
					);?>
					<?=\TSolution\Functions::showFooterBlock($blockOptions);?>
					<div class="footer__main-part-menu flexbox flexbox--direction-row">
						<div class="footer__part-item flex-50-991">
							<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
								array(
									"COMPONENT_TEMPLATE" => ".default",
									"PATH" => SITE_DIR."include/footer/menu/menu_bottom1.php",
									"AREA_FILE_SHOW" => "file",
									"AREA_FILE_SUFFIX" => "",
									"AREA_FILE_RECURSIVE" => "Y",
									"EDIT_TEMPLATE" => "include_area.php"
								),
								false, array("HIDE_ICONS" => "Y")
							);?>
						</div>

						<div class="footer__part-item flex-50-991">
							<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
								array(
									"COMPONENT_TEMPLATE" => ".default",
									"PATH" => SITE_DIR."include/footer/menu/menu_bottom2.php",
									"AREA_FILE_SHOW" => "file",
									"AREA_FILE_SUFFIX" => "",
									"AREA_FILE_RECURSIVE" => "Y",
									"EDIT_TEMPLATE" => "include_area.php"
								),
								false, array("HIDE_ICONS" => "Y")
							);?>
						</div>

						<div class="footer__part-item flex-50-991">
							<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
								array(
									"COMPONENT_TEMPLATE" => ".default",
									"PATH" => SITE_DIR."include/footer/menu/menu_bottom3.php",
									"AREA_FILE_SHOW" => "file",
									"AREA_FILE_SUFFIX" => "",
									"AREA_FILE_RECURSIVE" => "Y",
									"EDIT_TEMPLATE" => "include_area.php"
								),
								false, array("HIDE_ICONS" => "Y")
							);?>
						</div>

						<div class="footer__part-item flex-50-991">
							<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
								array(
									"COMPONENT_TEMPLATE" => ".default",
									"PATH" => SITE_DIR."include/footer/menu/menu_bottom4.php",
									"AREA_FILE_SHOW" => "file",
									"AREA_FILE_SUFFIX" => "",
									"AREA_FILE_RECURSIVE" => "Y",
									"EDIT_TEMPLATE" => "include_area.php"
								),
								false, array("HIDE_ICONS" => "Y")
							);?>
						</div>
					</div>
				</div>
				<?//show phone, address, email wrapper, social wrapper?>
				<?$visible = (($bShowPhone && $bPhone) || $bShowEmail || $bShowAddress || $bShowSocial);?>
				<?$blockOptions = array(
					'PARAM_NAME' => 'FOOTER_ALL_BLOCK',
					'BLOCK_TYPE' => 'FOOTER_ALL_BLOCK',
					'TITLE' => GetMessage('FOOTER_CONTACTS'),
					'IS_AJAX' => $bAjax,
					'AJAX_BLOCK' => $ajaxBlock,
					'VISIBLE' => $visible,
					'WRAPPER' => 'footer__part footer__part--right footer--mw318 flex-100-767',
					'INNER_WRAPPER' => 'footer__info',
					'ITEMS' => [
						[ //show phone and callback
							'PARAM_NAME' => 'FOOTER_TOGGLE_PHONE',
							'BLOCK_TYPE' => 'PHONE',
							'VISIBLE' => $bShowPhone && $bPhone,
							'DROPDOWN_TOP' => true,
							'WRAPPER' => 'footer__phone footer__info-item',
							'CALLBACK' => false,
							'MESSAGE' => GetMessage("S_CALLBACK"),
						],
						[ //show email
							'PARAM_NAME' => 'FOOTER_TOGGLE_EMAIL',
							'BLOCK_TYPE' => 'EMAIL',
							'VISIBLE' => $bShowEmail,
							'WRAPPER' => 'footer__info-item',
							'NO_ICON' => true,
						],
						[ //show address
							'PARAM_NAME' => 'FOOTER_TOGGLE_ADDRESS',
							'BLOCK_TYPE' => 'ADDRESS',
							'VISIBLE' => $bShowAddress,
							'WRAPPER' => 'footer__address footer__info-item',
							'NO_ICON' => true,
						],
						[ //show social
							'PARAM_NAME' => 'FOOTER_TOGGLE_SOCIAL',
							'BLOCK_TYPE' => 'SOCIAL',
							'VISIBLE' => $bShowSocial,
							'HIDE_MORE' => false,
							'WRAPPER' => 'footer__social footer__info-item',
							'NO_ICON' => true,
						]
					]
				);?>
				<?=\TSolution\Functions::showFooterBlock($blockOptions);?>
			</div>
		</div>
	</div>

	<div class="footer__bottom-part">
		<div class="maxwidth-theme">
			<div class="footer__bottom-part-inner">
				<div class="footer__bottom-part-items-wrapper">
					<div class="footer__part-item">
						<div class="footer__copy font_14 color_999">
							<?$APPLICATION->IncludeFile(SITE_DIR."include/footer/copy.php", Array(), Array(
									"MODE" => "php",
									"NAME" => "Copyright",
									"TEMPLATE" => "include_area.php",
								)
							);?>
						</div>
					</div>

					<?//show pay systems?>
					<?$blockOptions = array(
						'PARAM_NAME' => 'FOOTER_TOGGLE_PAY_SYSTEMS',
						'BLOCK_TYPE' => 'PAY_SYSTEMS',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bShowPaySystems,
						'WRAPPER' => 'footer__pays footer__pays footer__part-item',
					);?>
					<?=\TSolution\Functions::showFooterBlock($blockOptions);?>

					<div class=footer__part-item-confidentiality>
						<div class="footer__part-item">
							<div class="footer__license font_14">
								<?$APPLICATION->IncludeFile(SITE_DIR."include/footer/confidentiality.php", Array(), Array(
										"MODE" => "php",
										"NAME" => "Confidentiality",
										"TEMPLATE" => "include_area.php",
									)
								);?>
							</div>
						</div>
						<?if($arTheme['SHOW_OFFER']['VALUE'] === "Y"):?>
							<div class="footer__part-item">
								<div class="footer__offer font_14">
									<?$APPLICATION->IncludeFile(SITE_DIR."include/footer/offer.php", Array(), Array(
											"MODE" => "php",
											"NAME" => "Offer",
											"TEMPLATE" => "include_area.php",
										)
									);?>
								</div>
							</div>
						<?endif;?>
					</div>

					<?if($arTheme['PRINT_BUTTON'] == 'Y'):?>
						<div class="footer__part-item">
							<div class="footer__print font_14 color_999">
								<?=\TSolution::ShowPrintLink();?>
							</div>
						</div>
					<?endif;?>

					<div id="bx-composite-banner" class="footer__part-item"></div>

					<?//show developer block?>
					<?$blockOptions = array(
						'PARAM_NAME' => 'FOOTER_TOGGLE_DEVELOPER',
						'BLOCK_TYPE' => 'DEVELOPER',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bShowDeveloper,
						'WRAPPER' => 'footer__developer footer__part-item font_14 color_999',
					);?>
					<?=\TSolution\Functions::showFooterBlock($blockOptions);?>
				</div>
			</div>
		</div>
	</div>
</footer>
