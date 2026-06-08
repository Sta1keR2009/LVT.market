<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<?
	use Bitrix\Main\Localization\Loc;
?>
<div class="form-header">
	<div class="text">
		<div class="title font_24 color_222"><?= Loc::getMessage('SUBSCRIBE__POPUP__TITLE') ?></div>
	</div>
</div>
<div class="form-body">
	<div class="form-inner form-inner--popup">
		<div class="form-send rounded-4 bordered">
			<div class="flexbox flexbox--direction-column flexbox--align-center">
				<div class="form-send__icon">
					<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/form_icons.svg#success-80-80', 'fill-more-theme', ['WIDTH' => 80,'HEIGHT' => 80]);?>
				</div>
				<div class="form-send__info">
					<div class="form-send__info-title switcher-title font_24"><?= Loc::getMessage('SUBSCRIBE__POPUP__TITLE_SUCCESS') ?></div>
					<div class="form-send__info-text">
						<?= Loc::getMessage('SUBSCRIBE__POPUP__MESSAGE_SUCCESS') ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="form-footer">
	<div class="btn btn-transparent-border btn-lg jqmClose">
		<?= Loc::getMessage('SUBSCRIBE__POPUP__CLOSE') ?>
	</div>
</div>

<script>
$(document).ready(function () {
	$('.jqmClose').closest('.jqmWindow').jqmAddClose('.jqmClose');
});
</script>