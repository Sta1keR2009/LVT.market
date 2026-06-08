var basketTimeout;
var totalSum;

function deleteProduct(basketId, item, th) {
	function _deleteProduct(basketId, product_id){		
		$.post(arAsproOptions['SITE_DIR'] + 'ajax/item.php', 'ID=' + item + '&action=basket&sessid=' + BX.bitrix_sessid(), $.proxy(function () {
			basketTop('reload');
			document.querySelectorAll('.to_cart[data-id="' + product_id + '"]').forEach(function (element) {
				let itemAction = JItemAction.factory(element);
				itemAction.state = false;
				itemAction.resetQuantity();

				BX.onCustomEvent('onCounterGoals', [{
					goal: itemAction.getStateGoalCode(false),
					params: {
						id: product_id,
					}
				}]);
			});
			reloadCounters();
		}));
	}

	if(checkCounters()){
		setTimeout(function(){
			_deleteProduct(basketId, item);
		}, 100);
	}
	else{
		_deleteProduct(basketId, item);
	}
}