<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $pathForAjax;
?>
<div class="reviews_sort">
	<div class="filter-panel sort_header">
		<!--noindex-->
			<div class="filter-panel__sort">
				<form class="filter-panel__sort-form" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="reviews_sort" value="Y" />
					<input type="hidden" name="reviews_filter" value="Y" />
					<input type="hidden" name="ajax_url" value="<?=$pathForAjax.'/ajax.php';?>">
					<div class="filter-panel__sort-form__inner flexbox flexbox--row flexbox--wrap">
						<?
						$obSort = new TSolution\Template\Sort\Review($arParams, [
							'ajaxPath' => $pathForAjax.'/ajax.php',
							'session' => [
								'sort' => 'REVIEW_SORT_PROP',
								'order' => 'REVIEW_SORT_ORDER'
							],
						]);
						$obSort->show();
						?>
						<?include 'filter.php';?>
					</div>
				</form>
			</div>
		<!--/noindex-->
	</div>
</div>