<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?if($arResult['ERRORS']):?>
	<?
	global $USER;
	if($USER->IsAdmin()):?>
		<div class="alert alert-danger">
			<?=$arResult['ERRORS']['MESSAGE']?>
		</div>
	<?endif;?>
<?else:?>
	<?if($arResult['ITEMS']):

		$srcYouTube = "https://www.youtube.com";
		$bWide = $arParams["WIDE"] === 'Y';
		$arParams["SHOW_TITLE"] = $arParams['TITLE'] && $arParams['SHOW_TITLE'];
		$bBordered = ($arParams["BORDERED"] === 'Y');

		$bMaxWidthWrap = (
			!isset($arParams['MAXWIDTH_WRAP']) ||
			(isset($arParams['MAXWIDTH_WRAP']) && $arParams['MAXWIDTH_WRAP'] !== "N")
		);

		$bMobileScrolledItems = (
			!isset($arParams['MOBILE_SCROLLED']) || 
			(isset($arParams['MOBILE_SCROLLED']) && $arParams['MOBILE_SCROLLED'])
		);

		$itemClasses = 'height-100 color-theme-parent-all outer-rounded-x';

		$arParams['RIGHT_LINK'] = isset($arParams["CHANNEL_ID_YOUTUBE"]) && !empty($arParams["CHANNEL_ID_YOUTUBE"]) ? $arResult['RIGHT_LINK'].$arParams["CHANNEL_ID_YOUTUBE"] : "";

		$gridClass = 'grid-list grid-items-'.$arParams['ELEMENTS_ROW'];
		$gridClass .= \TSolution\Functions::getGridClassByCount(['992', '1200'], $arParams['ELEMENTS_ROW']);

		if ($bMobileScrolledItems) {
			$gridClass .= ' mobile-scrolled mobile-scrolled--items-2 mobile-offset';
		} else {
			$gridClass .= ' grid-list--normal';
		}

		?>
		<div class="youtube-list <?=$templateName?>-template type-<?=$typeBlock?>">
			<?=\TSolution\Functions::showTitleBlock([
				'PATH' => '',
				'PARAMS' => $arParams,
			]);?>

			<?if($bMaxWidthWrap):?>
				<div class="maxwidth-theme <?=$bWide ? ' maxwidth-theme--no-maxwidth' : '';?>">
			<?endif;?>
					<div class="<?=$gridClass?> youtube-list__items">
						<?foreach ($arResult['ITEMS'] as $item):?>
							<div class="grid-list__item">
								<div class="youtube-list__item flexbox <?=$itemClasses?>">
									<div class="youtube-list__item-video_wrapper height-100">
										<div class="youtube-list__item-video_wrapper-iframe youtube-list__item-preview outer-rounded-x" style="background:url(<?=$item['IMAGE']?>) center center/cover no-repeat;" data-video-id="<?=$item['ID']?>">
											<div id="youtube-player-id-<?=$item['ID']?>"></div>
										</div>
										<a class="youtube-list__item-title-link dark_link color-theme-target" href="<?=$srcYouTube . "/watch?v=" . $item["ID"]?>" target="_blank">
											<span class="youtube-list__item-title linecamp-3 font_normal font_14--to-600"><?=$item["TITLE"]?></span>
										</a>
									</div>
									<div class="youtube-list__item-period_wrapper">
										<span class="youtube-list__item-period-date font_13">
											<span class="color_999"><?=FormatDate('d.m.Y', strtotime($item['DATE_FROM']), 'SHORT');?></span>
										</span>
									</div>
								</div>
							</div>
						<?endforeach;?>
					</div>
			<?if($bMaxWidthWrap):?>
				</div>
			<?endif;?>
		</div>
	<?endif;?>
<?endif;?>