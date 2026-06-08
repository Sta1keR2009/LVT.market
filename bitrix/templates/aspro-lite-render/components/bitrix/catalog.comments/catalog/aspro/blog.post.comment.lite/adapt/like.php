<?
global $USER, $pathForAjax;
if ($USER->IsAuthorized()) {
	$userId = $USER->GetID();
}

if ($userId) {
	global $USER_FIELD_MANAGER;
	$ufId = ($userId % 1000) . ($comment['ID'] % 1000);
	$fields = $USER_FIELD_MANAGER->GetUserFields("BLOG_COMMENT_ID", $ufId);
	$fieldValueLike = $fields['UF_LIKE_ID']['VALUE'];
	$fieldValueLike = TSolution::unserialize((string)$fieldValueLike);

	if (isset($fieldValueLike[$userId])) {
		$valuelike = $fieldValueLike[$userId];
	} else {
		$valuelike = 'N';
	}

	$bActiveLike = $valuelike == 'Y';

	$fieldValueDisLike = $fields['UF_DISLIKE_ID']['VALUE'];
	$fieldValueDisLike = TSolution::unserialize((string)$fieldValueDisLike);

	if (isset($fieldValueDisLike[$userId])) {
		$valuedislike = $fieldValueDisLike[$userId];
	} else {
		$valuedislike = 'N';
	}

	$bActiveDisLike = $valuedislike == 'Y';
}
?>
<span class="rating-vote" data-comment_id="<?= $comment['ID'] ?>" data-user_id="<?= $userId ?>" data-ajax_url="<?= $pathForAjax . '/ajaxLike.php'; ?>">
	<? // like button ?>
	<a href="javascript:void(0)" class="rating-vote__item rating-vote__item-like stroke-dark-light-block dark_link plus<?= $userId ? '' : ' disable' ?><?= $bActiveLike ? ' active' : '' ?>" data-action="plus" title="<?= GetMessage('LIKE'); ?>">
		<span class="rating-vote__icon">
			<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . "/images/svg/catalog/item_icons.svg#like-20-22", '', ['WIDTH' => 20, 'HEIGHT' => 22]); ?>
		</span>

		<span class="rating-vote__result font_14">
			<?= intval($comment['UF_ASPRO_COM_LIKE']); ?>
		</span>
	</a>

	<? // dislike button ?>
	<a href="javascript:void(0)" class="rating-vote__item rating-vote__item-dislike stroke-dark-light-block dark_link minus<?= $userId ? '' : ' disable'; ?><?= $bActiveDisLike ? ' active' : ''; ?>" data-action="minus" title="<?= GetMessage('DISLIKE'); ?>">
		<span class="rating-vote__icon">
			<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . "/images/svg/catalog/item_icons.svg#dislike-20-22", '', ['WIDTH' => 20, 'HEIGHT' => 22]); ?>
		</span>

		<span class="rating-vote__result font_14">
			<?= intval($comment['UF_ASPRO_COM_DISLIKE']); ?>
		</span>
	</a>
</span>