if (!funcDefined('showItemStoresAmount')) {
	var showItemStoresAmount = function() {
		let blocks = $('.status-icon.instock .status-amount--stores:not(.status-amount--loaded)');
		if (blocks.length) {
			let stores = [];
			let ids = [];

			for (let i = 0, cnt = blocks.length; i < cnt; ++i) {
				$(blocks[i]).addClass('status-amount--loaded');

				var data = $(blocks[i]).data('param-amount');
				if (
					typeof data === 'object' &&
					data
				) {
					ids.push(data.ID);
					stores = data.STORES;
				}
			}

			if (ids) {
				let data = {
					ids: ids,
					stores: stores,
				};
				
				$.ajax({
					url: arAsproOptions.SITE_DIR + 'ajax/amount.php',
					type: 'POST',
					data: data,
					dataType: 'json',
					success: function(result) {
						if (
							typeof result === 'object' &&
							result &&
							result.success &&
							result.amount
						) {
							for (let i = 0, cnt = blocks.length; i < cnt; ++i) {          
								var data = $(blocks[i]).data('param-amount');
								if (
									typeof data === 'object' &&
									data
								) {
									if (result.amount[data.ID]) {
										$(blocks[i]).html(result.amount[data.ID]);
									}

									$(blocks[i]).addClass('status-amount--visible');
								}
							}
						}
					},
				});
			}
		}
	}
}

BX.ready(function () {
	showItemStoresAmount();
});