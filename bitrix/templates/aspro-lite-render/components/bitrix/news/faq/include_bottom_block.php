<?php
use \Bitrix\Main\Localization\Loc;
?>
<div class="rounded-x grey-bg order-block__wrapper">
	<div class="order-info-block">
		<div class="line-block line-block--align-normal line-block--40">
			<div class="line-block__item flex-1">
				<? 
				$APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => SITE_DIR . 'include/ask_question_faq.php', 'EDIT_TEMPLATE' => '']);
				?>
			</div>
			<div class="line-block__item order-info-btns">
				<div class="line-block line-block--align-normal line-block--12">
					<div class="line-block__item">
						<span class="btn btn-default btn-lg min_width--300 " data-event="jqm"
							data-param-id="<?= \TSolution::getFormID("aspro_lite_question"); ?>"
							data-name="question">
							<span><?= (strlen($arParams['S_ASK_QUESTION']) ? $arParams['S_ASK_QUESTION'] : Loc::getMessage('FAQ__BTN__ASK_QUESTION')) ?></span>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>