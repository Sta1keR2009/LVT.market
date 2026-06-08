<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/footer/settings.php');
?>
<footer id="footer" class="footer footer_simple footer_simple_1 footer--color-<?=htmlspecialcharsbx($footerColor)?>">
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
				</div>
			</div>
		</div>
	</div>

	<?if(
		$bAjax &&
		preg_match('/^FOOTER_/', $ajaxBlock)
	) {
		$APPLICATION->restartBuffer();
		die();
	}?>
</footer>
