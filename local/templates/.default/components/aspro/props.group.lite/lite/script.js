$(document).ready(function () {
	$(document).on('submit', '.js-filter.analog-filter-form', function (e) {
		e.preventDefault();

		var $form = $(this);
		var $checked = $form.find('input[type=checkbox]:checked');
		var $hint = $form.find('.analog-filter-hint');

		if (!$checked.length) {
			if ($hint.length) {
				$hint.show();
			}
			return false;
		}

		if ($hint.length) {
			$hint.hide();
		}

		var action = ($form.attr('action') || '/katalog/').split('?')[0];
		var params = $checked.serialize();

		params += (params ? '&' : '') + 'set_filter=Y';
		window.location.href = action + '?' + params;

		return false;
	});
});
