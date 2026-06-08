$.fn.phonecode = function(params, callback){
	if($(this).length){
		$(this).each(function(){
			init(this, params, callback);
		});
	}

	function init(input, params, callback){
		var $input = $(input);
		var keyTimeOut = false;

		$input.bind({
			// not use keyup, because it is not work through teamviewer
			keydown: function(e){
				if(e.which == 255){
					return;
				}

				var that = this;
				if(keyTimeOut){
					clearTimeout(keyTimeOut);
				}
				keyTimeOut = setTimeout(function(){
					checkSmsCode(that, params, callback);
				}, 50);
			},
			paste: function(e){
				var that = this;
				if(keyTimeOut){
					clearTimeout(keyTimeOut);
				}
				keyTimeOut = setTimeout(function(){
					checkSmsCode(that, params, callback);
				}, 50);
			},
			cut: function(e){
				var that = this;
				if(keyTimeOut){
					clearTimeout(keyTimeOut);
				}
				keyTimeOut = setTimeout(function(){
					checkSmsCode(that, params, callback);
				}, 50);
			}
		});

		checkSmsCode(input, params, callback);
	}

	function checkSmsCode(input, params, callback){
		let $input = $(input);
		let val = $input.val();
		let $form = $input.closest('form');

		if(
			val.length > 3 &&
			$form.length &&
			!$form.find('button[type=submit].loadings').length
		){
			let data = {
				AUTH: params.AUTH,
				USER_ID: params.USER_ID,
				USER_LOGIN: params.USER_LOGIN,
				USER_PHONE_NUMBER: params.USER_PHONE_NUMBER,
				SMS_CODE: val,
			};

			$.ajax({
				url: arAsproOptions['SITE_DIR'] + 'ajax/check-phonecode.php',
				data: data,
				type: 'POST',
				success: function(response) {
					if(typeof callback === 'function'){
						callback(input, data, response);
					}
				},
			});
		}
	}
} 