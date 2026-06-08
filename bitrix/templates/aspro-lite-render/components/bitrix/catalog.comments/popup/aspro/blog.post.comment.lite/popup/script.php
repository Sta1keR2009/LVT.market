<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
?>
<script>
function waitResult(id) {
	id = parseInt(id);
	r = 'new_comment_' + id;
	ob = BX(r);

	if (ob && ob.innerHTML.length > 0) {
		var obNew = BX.processHTML(ob.innerHTML, true);
		scripts = obNew.SCRIPT;
		BX.ajax.processScripts(scripts, true);

		let jqmWindow = ob.closest('.jqmWindow');
		let scrollbar = jqmWindow ? jqmWindow.querySelector('.scrollbar') : null;

		if (window.commentEr && window.commentEr == "Y") {
			BX('err_comment_' + id).innerHTML = ob.innerHTML;
			ob.innerHTML = '';

			$('.blog-comment__form').removeClass('sending');

			if (scrollbar) {
				$(scrollbar).animate({ scrollTop: 0 }, 500);
			}
		}
		else {
			if (scrollbar) {
				scrollbar.innerHTML = ob.innerHTML;
			}

			BX.onCustomEvent('onIblockCatalogCommentAdd');
		}
		window.commentEr = false;

		BX.closeWait();
		BX.onCustomEvent('onIblockCatalogCommentSubmit');
	}
	else {
		setTimeout("waitResult('" + id + "')", 300);
	}
}

function submitComment() {
	BX('post-button').focus();

	obForm = BX('form_comment');
	const bCommentRequired = <?= isset($arParams['REVIEW_COMMENT_REQUIRED']) && $arParams['REVIEW_COMMENT_REQUIRED'] === 'N' ? 'false' : 'true'; ?>;
	<?
	if ($arParams["AJAX_POST"] == "Y") {
	?>
		val = 0;
		id = 'new_comment_' + val;

		if (BX('err_comment_' + val)) {
			BX('err_comment_' + val).innerHTML = '';
		}

		if (!prepareFormInfo(obForm, bCommentRequired)) {
			return false;
		}

		BX.ajax.submitComponentForm(obForm, id);

		setTimeout("waitResult('" + val + "')", 100);
	<?
	}
	?>

	var eventdata = {
		type: 'form_submit',
		form: $(obForm)
	};
	BX.onCustomEvent('onSubmitForm', [eventdata]);
}

function prepareFormInfo(obForm, bCommentRequired) {
	let isValid = true;

	var form = $(obForm);

	// remove hidden comment field
	form.find('input[type=hidden][name=comment]').remove();

	var comment = form.find('[name=comment]');
	var limitations = form.find('[name=limitations]');
	var virtues = form.find('[name=virtues]');
	var rating = form.find('[name=rating]');
	var edit_id = form.find('[name=edit_id]') ?
		parseInt(form.find('[name=edit_id]').val()) :
		false;
	var parent_id = form.find('[name=parentId]') ?
		parseInt(form.find('[name=parentId]').val()) :
		false;

	var ratingVal = rating.closest('.votes_block').data('rating');
	if (ratingVal) {
		rating.val(ratingVal);
	}

	var resultCommentText = '';
	if (virtues.val()) {
		resultCommentText += `<virtues>${virtues.val().replace(/(<([^>]+)>)/gi, "")}</virtues>\n`;
	}
	if (limitations.val()) {
		resultCommentText += `<limitations>${limitations.val().replace(/(<([^>]+)>)/gi, "")}</limitations>\n`;
	}
	if (comment.val()) {
		resultCommentText += `<comment>${comment.val().replace(/(<([^>]+)>)/gi, "")}</comment>`;
	}
	else if ((!bCommentRequired || edit_id) && !parent_id) {
		resultCommentText += '<comment></comment>';
	}

	if (bCommentRequired && !resultCommentText) {
		const $label = form.find('.form__text-field:visible:first label:not(.error)');
		const $error = form.find('.comments-error');
		if (!$error.length) {
			const error = BX.create({
				tag: 'label',
				text: "<?=Loc::getMessage('NO_COMMENT_TEXT')?>",
				attrs: {
					class: 'error comments-error',
					for: 'virtues'
				},
			});
			BX.insertAfter(error, $label[0]);
		}
		isValid = false;
	}

	isValid = isValid && $(obForm).valid();
	
	if (!isValid) {
		return false;
	}

	window.commentOr = comment.val();

	// add hidden comment field
	if(resultCommentText) {
		let commentHidden = BX.create({
			tag: 'input',
			attrs: {
				type: 'hidden',
				name: comment.attr('name'),
				value: resultCommentText,
			},
		});

		BX.insertAfter(commentHidden, comment[0]);
	}

	return isValid;
}

$(document).ready(function() {
	$('.popup form[name="form_comment"]').validate({
		highlight: function(element) {
			$(element).parent().addClass('error');
		},
		unhighlight: function(element) {
			$(element).parent().removeClass('error');
		},
		submitHandler: function(form) {
			if ($('.popup form[name="form_comment"]').valid()) {
				setTimeout(function() {
					$(form).find('button[type="submit"]').attr("disabled", "disabled");
				}, 300);
				var eventdata = {type: 'form_submit', form: form, form_name: 'form_comment'};
				BX.onCustomEvent('onSubmitForm', [eventdata]);
			}
		},
		errorPlacement: function(error, element) {
			let uploader = element.closest('.uploader');
			if (uploader.length) {
				error.insertAfter(uploader);
			}
			else {
				error.insertAfter(element);
			}
		},
	});

	fileInputInit('<?=Loc::getMessage('INPUT_FILE_DEFAULT')?>', 'Y');
});

$(document).on('click', '.jqmClose', function(e){
	e.preventDefault();
	$(this).closest('.jqmWindow').jqmHide();
});

<?if(isset($arParams['REVIEW_COMMENT_REQUIRED']) && $arParams['REVIEW_COMMENT_REQUIRED'] === 'Y'):?>
	$(document).on('paste, change, keyup', '.form.blog-comment-fields textarea', function() {
		let value = $(this).val();
		if (value.length) {
			$(this).closest('.blog-comment__form').find('.comments-error').remove();
		}
	});
<?endif;?>
</script>