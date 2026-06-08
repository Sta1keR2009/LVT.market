if (typeof window.JBigData === 'undefined') {
	window.JBigData = function(config) {
		config = (typeof config === 'object' && config) ? config : {};

        var _private = {
            inited: false,
        };

        let _config = JSON.stringify(config);

		Object.defineProperties(this, {
			inited: {
				get: function () {
					return _private.inited;
				},
				set: function (value) {
					if (value) {
					_private.inited = true;
					}
				},
			},

			config: {
				get: function() {
					return JSON.parse(_config);
				},
			},
		});

		if (this.valid) {
			this.init();
		}
    }

	window.JBigData.prototype = {
		nodes: {
			wrapper: null,
			block: null,
		},

		get valid () {
			let bigData = this.config.bigData;
			return bigData && (typeof bigData === 'object') && !(bigData instanceof Array);
		},
		
		get data() {
			if (this.valid) {
				let bigData = this.config.bigData;
				return BX.ajax.prepareData(bigData.params);
			}

			return null;
		},

		get rcmLoadUrl() {
			let url = 'https://analytics.bitrix.info/crecoms/v1_0/recoms.php';
			let data = this.data;

			if (data) {
				url += (url.indexOf('?') !== -1 ? '&' : '?') + data;
			}

			return url;
		},

		get catalogLoadUrl() {
			return arAsproOptions.SITE_DIR + 'ajax/catalog_section.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : '')
		},

		init: function() {
            if (!this.inited) {
                this.inited = true;

				this.nodes.wrapper = document.querySelector('.bigdata-wrapper');
				if (this.nodes.wrapper) {
					this.nodes.block = this.nodes.wrapper.closest('.personal__main-block') || this.nodes.wrapper.closest('.personal__block');
				}

                this.rcmLoad(
					BX.proxy(
						function(result) {
							this.onRcmReady(result);
						}, this
					),
					BX.proxy(
						function(result) {
							this.onRcmReady(result);
						}, this
					),
				);
            }
        },

		rcmLoad: function (onSuccess, onFailure) {
			if (this.valid) {
				let bigData = this.config.bigData;

				BX.cookie_prefix = bigData.js.cookiePrefix || '';
				BX.cookie_domain = bigData.js.cookieDomain || '';
				BX.current_server_time = bigData.js.serverTime;

				let ajaxConfig = {
					url: this.rcmLoadUrl,
					method: 'GET',
					dataType: 'json',
					timeout: 3,
				};

				if (
					onSuccess &&
					typeof onSuccess === 'function'
				) {
					ajaxConfig.onsuccess = onSuccess;
				}

				if (
					onFailure &&
					typeof onFailure === 'function'
				) {
					ajaxConfig.onfailure = onFailure;
				}
	
				BX.ajax(ajaxConfig);
			}
		},

		onRcmReady: function(result) {
			let count = this.config.count > 0 ? this.config.count : 10;
			let data = {
				action: 'deferredLoad',
				bigData: 'Y',
				items: (result && result.items) || [],
				rid: result && result.id,
				count: count,
				rowsRange:[count],
				shownIds: (this.config.bigData && this.config.bigData.shownIds) || [],
				siteId: this.config.siteId,
				template: this.config.template,
				parameters: this.config.parameters,
			}

			this.catalogLoad(data);
		},

		catalogLoad: function(data) {
			if (
				data &&
				typeof data === 'object' &&
				this.nodes.wrapper
			) {
				this.nodes.wrapper.classList.add('form', 'sending');

				BX.ajax({
					url: this.catalogLoadUrl,
					method: 'POST',
					dataType: 'json',
					timeout: 60,
					data: data,
					onsuccess: BX.proxy(function(result) {
						this.show();

						this.nodes.wrapper.classList.remove('form', 'sending');

						if (
							!result ||
							typeof result !== 'object' ||
							typeof result.JS !== 'string' ||
							typeof result.items !== 'string' ||
							!result.items
						) {
							this.remove();

							return;
						}

						let obData = BX.processHTML(result.items);
						let html = obData.HTML;
						this.nodes.wrapper.innerHTML = html;
						
						if (typeof InitAppear === 'function') {
							InitAppear();
						}
						
						BX.ajax.processScripts(obData.SCRIPT);

						obData = BX.processHTML(result.JS);
						BX.ajax.processScripts(obData.SCRIPT);
		
						// actualize item-actions states
						BX.onCustomEvent(
							'onCompleteAction',
							[
								{
									action: 'ajaxContentLoaded',
								},
								this.nodes.wrapper
							]
						);
					}, this),
					onfailure: BX.proxy(function() {
						this.nodes.wrapper.classList.remove('form', 'sending');
					}, this)
				});
			}
		},

		remove: function() {
			if (this.nodes.block) {
				this.nodes.block.remove();
			}
			else if (this.nodes.wrapper) {
				this.nodes.wrapper.remove();
			}
		},

		show: function() {
			if (this.nodes.block) {
				let block = this.nodes.block.querySelector('.personal__block')
				if (block) {
					block.classList.remove('hidden');
				}
			}
		},
	}

	window.JBigData.getCookie = function(name) {
		var matches = document.cookie.match(new RegExp(
			"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
		));
	
		return matches ? decodeURIComponent(matches[1]) : null;
	}

	window.JBigData.rememberProductRecommendation = function(rcmId, productId) {
		if (productId > 0) {
			rcmId = rcmId ? rcmId : 'mostviewed';

			const cookieName = BX.cookie_prefix + '_RCM_PRODUCT_LOG';
			let cookie = window.JBigData.getCookie(cookieName);
			let itemFound = false;
		
			let cItems = [],
				cItem;
	
			if (cookie) {
				cItems = cookie.split('.');
			}
	
			let i = cItems.length;
	
			while (i--) {
				cItem = cItems[i].split('-');
	
				if (cItem[0] == productId) {
					// it's already in recommendations, update the date
					cItem = cItems[i].split('-');
	
					// update rcmId and date
					cItem[1] = rcmId;
					cItem[2] = BX.current_server_time;
	
					cItems[i] = cItem.join('-');
					itemFound = true;
				}
				else {
					if ((BX.current_server_time - cItem[2]) > 3600 * 24 * 30) {
						cItems.splice(i, 1);
					}
				}
			}
	
			if (!itemFound) {
				// add recommendation
				cItems.push([productId, rcmId, BX.current_server_time].join('-'));
			}
	
			// serialize
			let plNewCookie = cItems.join('.'),
				cookieDate = new Date(new Date().getTime() + 1000 * 3600 * 24 * 365 * 10).toUTCString();
			document.cookie = cookieName + "=" + plNewCookie + "; path=/; expires=" + cookieDate + "; domain=" + BX.cookie_domain;
		}
	}

	BX.bind(
		document,
		'click',
		function(event) {
			event = event || window.event;

			let target = event.target;
			if (target) {
				let wrapperItem = target.closest('[data-bigdata]');
				if (wrapperItem) {
					if (
						target.closest('a') ||
						target.closest('[data-event="jqm"]') ||
						target.closest('.item-action')
					) {
						let rcmId = wrapperItem.dataset.rcm;
						let infoItem = wrapperItem.querySelector('[data-id][data-item]');
						if (infoItem) {
							let productId = infoItem.dataset.id;
							window.JBigData.rememberProductRecommendation(rcmId, productId);
						}
					}
				}
			}
		}
	); 
}
