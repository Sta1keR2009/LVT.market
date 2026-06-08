<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?$this->setFrameMode(true);?>
<?if($arResult):?>
	<div class="bottom-menu">
		<div class="items">
			<div class="line-block line-block--gap line-block--gap-40 line-block--align-normal line-block--flex-wrap">
				<?foreach($arResult as $i => $arItem):?>
					<?$bLink = strlen($arItem['LINK']);?>
					<div class="item-link line-block__item item-link">
						<div class="item<?=($arItem["SELECTED"] ? " active" : "")?>">
							<div class="title font_weight--600 font_short">
								<?if($bLink):?>
									<a class="dark_link" href="<?=$arItem['LINK']?>"><?=$arItem['TEXT']?></a>
								<?else:?>
									<span><?=$arItem['TEXT']?></span>
								<?endif;?>
							</div>
						</div>
					</div>
				<?endforeach;?>
			</div>
		</div>
	</div>
<?endif;?>