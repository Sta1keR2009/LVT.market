if (typeof BX.Aspro.xPopover === 'undefined') {
	BX.namespace('Aspro.xPopover');

	BX.Aspro.xPopover = function (toggleNode, options) {
		toggleNode = (typeof toggleNode === 'object' && toggleNode && toggleNode instanceof Node) ? toggleNode : null;
		options = (typeof options === 'object' && options) ? options : {};

		this.toggleNode = toggleNode;

		var _private = {
			inited: false,
		};

		let _options = JSON.stringify(options);

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

			options: {
				get: function() {
					return JSON.parse(_options);
				},
			},
		});

		this.init();
	};

	BX.Aspro.xPopover.prototype = {
		node: null,
		toggleNode: null,
		drag: null,
		overlay: null,

		get visible() {
			return this.node && BX.hasClass(this.node, 'show');
		},

		get trigger() {
			let tmp = this.options.trigger;
			if (typeof tmp === 'string') {
				tmp = tmp.split(',');
			}

			if (typeof tmp === 'object' && tmp && tmp.length) {
				for (let i = 0, cnt = tmp.length; i < cnt; ++i) {
					tmp[i] = tmp[i].trim();
				}
			}
			else {
				tmp = [];
			}

			let trigger = [];
			if (tmp.indexOf('click') !== -1) {
				trigger.push('click');
			}
			if (tmp.indexOf('hover') !== -1) {
				trigger.push('hover');
			}
			if (!trigger.length) {
				trigger.push('click');
			}

			return trigger;
		},

		get placement() {
			let tmp = this.options.placement;
			if (typeof tmp === 'string') {
				tmp = tmp.split(',');
			}
			else if (typeof tmp === 'object' && tmp && tmp.length) {
				tmp = [tmp[0], tmp[1]];
			}
			else {
				tmp = ['top', ''];
			}

			let placement = [
				tmp.indexOf('bottom') !== -1 ? 'bottom' : (tmp.indexOf('top') !== -1 ? 'top' : ''),
				tmp.indexOf('right') !== -1 ? 'right' : (tmp.indexOf('left') !== -1 ? 'left' : ''),
			];

			return placement;
		},

		get offset() {
			let tmp = this.options.offset;
			if (typeof tmp === 'string') {
				tmp = tmp.split(',');
			}
			else if (typeof tmp === 'object' && tmp && tmp.length) {
				tmp = [tmp[0], tmp[1]];
			}
			else {
				tmp = [0, 0];
			}

			let offset = [
				typeof tmp[0] === 'string' ? tmp[0] : 0,
				typeof tmp[1] === 'string' ? tmp[1] : 0,
			];

			return offset;
		},

		get sticky() {
			let tmp = this.options.sticky;
			let sticky = typeof tmp === 'boolean' ? tmp : true;

			return sticky;
		},

		get hideOnChanged() {
			let tmp = this.options.hideOnChanged;
			let hideOnChanged = typeof tmp === 'boolean' ? tmp : true;

			return hideOnChanged;
		},

		get interval() {
			let interval = 200;
			let tmp = this.options.interval;
			if (typeof tmp === 'string') {
				interval = tmp * 1;
			}
			else if (typeof tmp === 'number') {
				interval = tmp;
			}

			return interval;
		},

		init: function() {
			if (!this.inited) {
				this.inited = true;
				this.toggleNode.xpopover = this;

				this.isRAF = !this.hideOnChanged && BX.Type.isFunction(requestAnimationFrame);
				this.delayedHide.cnt = 0;
				this.delayedHide.timer = false;
				this.setPosition.timer = false;
				this.resetPosition();
			}
		},

		bindEvents: function() {
			if (this.trigger.indexOf('hover') !== -1) {
				if (typeof this.handlers.onLeave === 'function') {
					BX.bind(this.toggleNode, 'mouseleave', BX.proxy(this.handlers.onLeave, this));
					BX.bind(this.node, 'mouseleave', BX.proxy(this.handlers.onLeave, this));
				}

				if (typeof this.handlers.onEnter === 'function') {
					BX.bind(this.toggleNode, 'mouseenter', BX.proxy(this.handlers.onEnter, this));
					BX.bind(this.node, 'mouseenter', BX.proxy(this.handlers.onEnter, this));
				}
			}

			if (BX.Aspro.xPopover.isTouch()) {
				BX.bind(this.node, 'touchstart', BX.proxy(this.handlers.onTouchStart, this));
				BX.bind(this.node, "touchmove", BX.proxy(this.handlers.onTouchMove, this), {capture: false, passive: false});
				BX.bind(this.node, 'touchend', BX.proxy(this.handlers.onTouchEnd, this));
				BX.bind(this.node, 'touchcancel', BX.proxy(this.handlers.onTouchCancel, this));
			}
		},

		unbindEvents: function() {
			if (this.trigger.indexOf('hover') !== -1) {
				if (typeof this.handlers.onLeave === 'function') {
					BX.unbind(this.toggleNode, 'mouseleave', BX.proxy(this.handlers.onLeave, this));
					BX.unbind(this.node, 'mouseleave', BX.proxy(this.handlers.onLeave, this));
				}

				if (typeof this.handlers.onEnter === 'function') {
					BX.unbind(this.toggleNode, 'mouseenter', BX.proxy(this.handlers.onEnter, this));
					BX.unbind(this.node, 'mouseenter', BX.proxy(this.handlers.onEnter, this));
				}
			}

			if (BX.Aspro.xPopover.isTouch()) {
				BX.unbind(this.node, 'touchstart', BX.proxy(this.handlers.onTouchStart, this));
				BX.unbind(this.node, "touchmove", BX.proxy(this.handlers.onTouchMove, this), {capture: false, passive: false});
				BX.unbind(this.node, 'touchend', BX.proxy(this.handlers.onTouchEnd, this));
				BX.unbind(this.node, 'touchcancel', BX.proxy(this.handlers.onTouchCancel, this));
			}
		},

		append: function() {
			let content = this.getContent();
			if (content) {
				let translateX = this.offset[0] === 'center' ? '-50%' : this.offset[0];
				let translateY = this.offset[1] === 'center' ? '-50%' : this.offset[1];

				let styles = {
					transform: `translate(${translateX}, ${translateY})`,
					zIndex: this.calcZIndex(),
				};

				let containerClassList = 'xpopover';
				if (this.options?.containerClass.length) {
					containerClassList += ` ${this.options.containerClass}`;
				}

				this.node = BX.create({
					tag: 'div',
					attrs: {
						class: containerClassList,
					},
					style: styles
				});

				this.drag = BX.create({
					tag: 'div',
					attrs: {
						class: 'xpopover-drag',
					},
				});

				this.node.append(this.drag);
				this.node.append(content);

				this.node.xpopover = this;

				BX.append(this.node, document.body);
			}
		},

		show: function() {
			if (!this.visible) {
				BX.Aspro.xPopover.hide();

				if (!this.node) {
					this.append();
				}

				if (this.node) {
					this.bindEvents();

					this.setPosition(true);
					BX.addClass(this.node, 'show');

					if (this.setPosition.timer) {
						if (this.isRAF) {
							cancelAnimationFrame(this.setPosition.timer);
							this.setPosition.timer = false;
						}
						else {
							clearInterval(this.setPosition.timer);
						}
					}

					if (this.isRAF) {
						let h = () => {
							this.setPosition();
							if (this.setPosition.timer) {
								this.setPosition.timer = requestAnimationFrame(h);
							}
						};

						this.setPosition.timer = requestAnimationFrame(h);
					}
					else {
						this.setPosition.timer = setInterval(() => {
							this.setPosition();
						}, this.interval);
					}
				}
			}
		},

		hide: function() {
			if (this.visible) {
				if (this.setPosition.timer) {
					if (this.isRAF) {
						cancelAnimationFrame(this.setPosition.timer);
						this.setPosition.timer = false;
					}
					else {
						clearInterval(this.setPosition.timer);
					}
				}

				this.resetPosition();

				this.removeOverlay();
				this.unbindEvents();
				BX.removeClass(this.node, 'show');

				setTimeout(() => {
					BX.remove(this.node);
					this.node = null;
				}, 200);
			}
		},

		toggle: function() {
			if (this.visible) {
				this.hide();
			}
			else {
				this.show();
			}
		},

		delayedHide: function() {
			if (this.delayedHide.cnt > 0) {
				clearTimeout(this.delayedHide.timer);
				this.delayedHide.timer = false;
			}
			else {
				this.delayedHide.cnt = 0;

				if (!this.delayedHide.timer) {
					this.delayedHide.timer = setTimeout(
						() => {
							this.hide();
							this.delayedHide.timer = false;
							this.delayedHide.cnt = 0;
						}, 1000
					);
				}
			}
		},

		position: {
			real: {
				left: null,
				top: null,
			},
			applyed: {
				left: null,
				top: null,
				ww: null,
				wh: null,
				changed: false,
			},
			calculated: {
				left: null,
				top: null,
				ww: null,
				wh: null,
				changed: false,
			},
			last: {
				left: null,
				top: null,
				ww: null,
				wh: null,
			},
		},

		resetPosition: function() {
			this.position = {
				real: {
					left: null,
					top: null,
				},
				applyed: {
					left: null,
					top: null,
					ww: null,
					wh: null,
					changed: false,
				},
				calculated: {
					left: null,
					top: null,
					ww: null,
					wh: null,
					changed: false,
				},
				last: {
					left: null,
					top: null,
					ww: null,
					wh: null,
				},
			};
		},

		calcPosition: function() {
			if (
				this.toggleNode &&
				this.node
			) {
				let toggleNodeOffset = this.toggleNode.getBoundingClientRect();
				this.position.calculated.left = parseInt(toggleNodeOffset.left + (this.placement[1] === 'right' ? toggleNodeOffset.width : (this.placement[1] === 'left' ? -1 * this.node.offsetWidth : 0)));
				this.position.calculated.top = parseInt(toggleNodeOffset.top + (this.placement[0] === 'bottom' ? toggleNodeOffset.height : (this.placement[0] === 'top' ? -1 * this.node.offsetHeight : 0)));
				this.position.calculated.ww = document.body.offsetWidth;
				this.position.calculated.wh = window.innerHeight;

				this.position.calculated.changed = this.position.calculated.top !== this.position.last.top || this.position.calculated.left !== this.position.last.left || this.position.calculated.wh !== this.position.last.wh || this.position.calculated.ww !== this.position.last.ww;

				this.position.applyed.changed = this.position.calculated.top !== this.position.applyed.top || this.position.calculated.left !== this.position.applyed.left || this.position.calculated.wh !== this.position.applyed.wh || this.position.calculated.ww !== this.position.applyed.ww;
			}
		},

		swapPosition: function() {
			this.position.last.left = this.position.calculated.left;
			this.position.last.top = this.position.calculated.top;
			this.position.last.ww = this.position.calculated.ww;
			this.position.last.wh = this.position.calculated.wh;
		},

		setPosition: function(force = false) {
			if (
				this.toggleNode &&
				this.toggleNode.offsetParent !== null // toggle visible
			) {
				if (
					this.node &&
					(force || !BX.Aspro.xPopover.skipSetPosition)
				) {
					if (BX.Aspro.xPopover.isMobile()) {
						this.addOverlay();
					}
					else {
						this.removeOverlay();

						this.calcPosition();

						if (this.position.calculated.changed) {
							this.swapPosition();

							if (
								force ||
								(
									!this.hideOnChanged &&
									!this.isOverflow()
								)
							) {
								if (this.sticky) {
									this.applyStickyPosition();
								}
								else {
									this.applyPosition();
								}
							}
							else {
								this.hide();
							}
						}
					}
				}
			}
			else {
				this.hide();
			}
		},

		applyPosition: function(left = null, top = null) {
			if (this.node) {
				this.position.real.left = (typeof left !== 'undefined' && left !== null ? left : this.position.calculated.left);
				this.position.real.top = (typeof top !== 'undefined' && top != null ? top : this.position.calculated.top);

				BX.style(this.node, 'left', this.position.real.left + 'px');
				BX.style(this.node, 'top', this.position.real.top + 'px');

				this.position.applyed.left = this.position.calculated.left;
				this.position.applyed.top = this.position.calculated.top;
				this.position.applyed.ww = this.position.calculated.ww;
				this.position.applyed.wh = this.position.calculated.wh;
			}
		},

		applyStickyPosition: function() {
			if (
				this.sticky &&
				this.toggleNode &&
				this.node
			) {
				if (!BX.Aspro.xPopover.isMobile()) {
					this.calcPosition();

					let clone = this.node.cloneNode(true);
					clone.classList.add('clone');
					clone.style.left = this.position.calculated.left + 'px';
					clone.style.top = this.position.calculated.top + 'px';
					BX.append(clone, document.body);

					let cloneOffset = clone.getBoundingClientRect();
					cloneOffset = {
						x: Math.round(cloneOffset.x),
						left: Math.round(cloneOffset.x),
						right: Math.round(cloneOffset.x + cloneOffset.width),
						y: Math.round(cloneOffset.y),
						top: Math.round(cloneOffset.y),
						bottom: Math.round(cloneOffset.y + cloneOffset.height),
					};

					let left = parseInt(clone.style.left);
					left = isNaN(left) ? 0 : left;
					if (cloneOffset.x <= 0) {
						left += -1 * cloneOffset.x;
					}
					else if (cloneOffset.right >= document.body.offsetWidth) {
						left -= cloneOffset.right - document.body.offsetWidth;
					}

					let top = parseInt(clone.style.top);
					top = isNaN(top) ? 0 : top;
					if (cloneOffset.y <= 0) {
						top += -1 * cloneOffset.y;
					}
					else if (cloneOffset.bottom >= window.innerHeight) {
						top -= cloneOffset.bottom - window.innerHeight;
					}

					clone.remove();

					this.applyPosition(left, top);
				}
			}
		},

		getContent: function() {
			if (this.toggleNode) {
				let template = this.toggleNode.querySelector('.xpopover-template');
				if (template) {
					return template.content.cloneNode(true);
				}

				return null;
			}
		},

		setContent: function(content) {
			if (this.toggleNode) {
				let template = this.toggleNode.querySelector('.xpopover-template');
				if (!template) {
					template = document.createElement('template');
					template.classList.add('xpopover-template');
					this.toggleNode.appendChild(template);
				}

				if (template) {
					template.innerHTML = '';

					let tmp = BX.create({
						tag: 'div',
						html: content,
					});

					if (tmp.querySelector(':scope > *')) {
						tmp.querySelectorAll(':scope > *').forEach((child) => {
							template.content.appendChild(child);
						});
					}
					else {
						template.content.appendChild(tmp);
					}
				}
			}
		},

		calcZIndex: function() {
			let zIndex = 2;
			let tmp = this.options.zIndex;
			if (typeof tmp === 'string') {
				zIndex = parseInt(tmp);
			}
			else if (typeof tmp === 'number') {
				zIndex = parseInt(tmp);
			}
			else {
				if (this.toggleNode) {
					let n = this.toggleNode;
					do {
						let styles = getComputedStyle(n);
						if (styles.getPropertyValue('position') === 'fixed') {
							zIndex = styles.getPropertyValue('z-index');
						}

						n = n.parentElement;
					}
					while (n);
				}
			}

			if (isNaN(zIndex)) {
				zIndex = 1;
			}

			return zIndex;
		},

		isOverflow: function() {
			if (this.toggleNode) {
				let scrollBar = this.toggleNode.closest('.scrollbar') || this.toggleNode.closest('.swiper-wrapper');
				if (scrollBar) {
					let scrollBarHeight = scrollBar.clientHeight;
					let scrollBarWidth = scrollBar.clientHeight;
					let scrollBarRect = scrollBar.getBoundingClientRect();
					let rect = this.toggleNode.getBoundingClientRect();

					let diffTop = rect.top - scrollBarRect.top;
					let diffBottom = rect.bottom - scrollBarRect.top;
					let diffLeft = rect.left - scrollBarRect.left;
					let diffRight = rect.right - scrollBarRect.left;

					return diffBottom <= 0 || diffTop >= scrollBarHeight || diffRight <= 0 || diffLeft >= scrollBarWidth;
				}
			}

			return false;
		},

		touch: null,

		handlers: {
			onLeave: function(event) {
				if (!BX.Aspro.xPopover.isMobile()) {
					event = event || window.event;
					--this.delayedHide.cnt;
					this.delayedHide();
				}
			},

			onEnter: function(event) {
				if (!BX.Aspro.xPopover.isMobile()) {
					event = event || window.event;
					++this.delayedHide.cnt;
					this.delayedHide();
				}
			},

			onTouchStart: function(event) {
				if (BX.Aspro.xPopover.isMobile()) {
					event = event || window.event;

					let target = event.target || event.srcElement;
					if (target) {
						let scroll = target.closest('.scrollbar');
						if (scroll) {
							if (scroll.scrollTop) {
								return;
							}
						}
					}

					if (
						event.changedTouches &&
						event.changedTouches.length
					) {
						this.touch = event.changedTouches[0];
						this.touch.time = new Date().getTime()
					}
				}
			},

			onTouchMove: function(event) {
				if (BX.Aspro.xPopover.isMobile()) {
					event = event || window.event;

					if (
						event.changedTouches &&
						event.changedTouches.length &&
						this.touch
					) {
						let touch = event.changedTouches[0];
						let bottom = touch.screenY - this.touch.screenY;
						if (bottom >= 0) {
							this.node.style.setProperty('bottom', `-${bottom}px`, 'important');
							event.preventDefault();
						} else {
							// scroll up
							this.touch = null;
						}
					}
				}
			},

			onTouchEnd: function(event) {
				if (BX.Aspro.xPopover.isMobile()) {
					event = event || window.event;

					if (
						event.changedTouches &&
						event.changedTouches.length &&
						this.touch
					) {
						let touch = event.changedTouches[0];
						let bottom = touch.screenY - this.touch.screenY;
						let height = this.node.clientHeight;
						let time = new Date().getTime();
						let speed = (time - this.touch.time) / bottom;
						if (bottom > 0) {
							if (
								bottom >= 100 ||
								bottom > height / 2 ||
								speed < 2
							) {
								this.node.style.setProperty('transform', 'translate(0,100%)', 'important');
								setTimeout(() => {
									this.hide();
								}, 300);

								return;
							}
						}

						this.node.style.bottom = '';
						this.touch = null;
					}
				}
			},

			onTouchCancel: function(event) {
				if (BX.Aspro.xPopover.isMobile()) {
					event = event || window.event;

					this.node.style.bottom = '';
					this.touch = null;
				}
			},
		},

		addOverlay: function() {
			if (
				!this.overlay &&
				this.node
			) {
				this.overlay = BX.create({
					tag: 'div',
					attrs: {
						class: 'xpopover-overlay',
					},
				});

				this.node.parentNode.insertBefore(this.overlay, this.node);
				this.overlay.xpopover = this;
				this.fixBody();
			}
		},

		removeOverlay: function() {
			if (this.overlay) {
				this.unfixBody();
				this.overlay.remove();
				this.overlay = null;
			}
		},

		fixBody: function() {
			let diffWidth = window.innerWidth - document.documentElement.clientWidth;
			if (diffWidth) {
				document.body.style.paddingRight = diffWidth + 'px';
			}

			document.body.style.overflow = 'hidden';
		},

		unfixBody: function() {
			document.body.style.paddingRight = '';
			document.body.style.overflow = '';
		},
	};

	BX.Aspro.xPopover.handlers = {
		onToggleNodeClick: function(event) {
			event = event || window.event;

			let toggleNode = this.closest('[data-xpopover]');
			let popover = BX.Aspro.xPopover.get(toggleNode);
			if (popover) {
				if (
					popover.trigger.indexOf('click') !== -1 ||
					BX.Aspro.xPopover.isMobile() ||
					BX.Aspro.xPopover.isTouch()
				) {
					event.preventDefault();
					popover.toggle();
				}
			}
		},

		onDocOver: function(event) {
			if (BX.Aspro.xPopover.isTouch()) {
				return;
			}

			event = event || window.event;
			let target = event.target;
			if (target) {
				let toggleNode = target.closest('[data-xpopover]');
				let popover = BX.Aspro.xPopover.get(toggleNode);
				if (popover) {
					if (
						popover.trigger.indexOf('hover') !== -1 &&
						!popover.visible
					) {
						if (
							BX.Aspro.xPopover.handlers.onDocOver.timer &&
							BX.Aspro.xPopover.handlers.onDocOver.toggleNode &&
							!BX.Aspro.xPopover.handlers.onDocOver.toggleNode.isEqualNode(toggleNode)
						) {
							clearTimeout(BX.Aspro.xPopover.handlers.onDocOver.timer);
							BX.Aspro.xPopover.handlers.onDocOver.timer = false;
						}

						if (!BX.Aspro.xPopover.handlers.onDocOver.timer) {
							BX.Aspro.xPopover.handlers.onDocOver.toggleNode = toggleNode;
							let toggleNodePosition = toggleNode.getBoundingClientRect();

							BX.Aspro.xPopover.handlers.onDocOver.timer = setTimeout(function() {
								BX.Aspro.xPopover.handlers.onDocOver.timer = false;

								let toggleNodePositionNew = popover.toggleNode.getBoundingClientRect();
								if (
									(toggleNodePosition.top == toggleNodePositionNew.top) &&
									(toggleNodePosition.left == toggleNodePositionNew.left)
								) {
									popover.show();
								}
							}, 1000);
						}

						return;
					}
				}
			}

			if (BX.Aspro.xPopover.handlers.onDocOver.timer) {
				clearTimeout(BX.Aspro.xPopover.handlers.onDocOver.timer);
				BX.Aspro.xPopover.handlers.onDocOver.timer = false;
			}
		},

		onOverlayClick: function(event) {
			event = event || window.event;

			let target = event.target;
			if (target) {
				let popover = target.xpopover;

				if (
					typeof popover !== 'undefined' &&
					popover &&
					popover instanceof BX.Aspro.xPopover
				) {
					popover.hide();
				}
				else {
					target.remove();
				}
			}
		},

		onDocClick: function (event) {
			event = event || window.event;

			let target = event.target;
			if (target) {
				let toggleNode = target.closest('.xpopover-toggle');
				if (toggleNode) {
					return;
				}

				let overlay = target.closest('.xpopover-overlay');
				if (overlay) {
					return;
				}

				let popover = target.closest('.xpopover');
				if (popover) {
					return;
				}
			}

			BX.Aspro.xPopover.hide();
		},

		onWindowResize: function(event) {
			if (!BX.Aspro.xPopover.isMobile()) {
				BX.Aspro.xPopover.hideOnScroll();
			}
		},
	}

	BX.Aspro.xPopover.get = function(toggleNode) {
		if (toggleNode) {
			if (
				typeof toggleNode.xpopover === 'undefined' ||
				!(toggleNode.xpopover instanceof BX.Aspro.xPopover)
			) {
				let options = {};

				let data = toggleNode.getAttribute('data-xpopover');
				if (data) {
					try {
						options = JSON.parse(data);
					}
					catch (e) {
						options = {};
					}
				}

				return new BX.Aspro.xPopover(toggleNode, options);
			}

			return toggleNode.xpopover;
		}

		return null;
	}

	BX.Aspro.xPopover.hide = function() {
		let visiblePopovers = document.querySelectorAll('.xpopover.show');
		if (visiblePopovers.length) {
			visiblePopovers.forEach((popover) => {
				if (
					typeof popover.xpopover !== 'undefined' &&
					popover.xpopover instanceof BX.Aspro.xPopover
				) {
					popover.xpopover.hide();
				}
			});
		}
	}

	BX.Aspro.xPopover.hideOnScroll = function() {
		let visiblePopovers = document.querySelectorAll('.xpopover.show');
		if (visiblePopovers.length) {
			visiblePopovers.forEach((popover) => {
				if (
					typeof popover.xpopover !== 'undefined' &&
					popover.xpopover instanceof BX.Aspro.xPopover &&
					popover.xpopover.hideOnChanged
				) {
					popover.xpopover.hide();
				}
			});
		}
	}

	BX.Aspro.xPopover.isMobile = function() {
		return window.matchMedia('(max-width: 380px)').matches ||
			 (
				window.devicePixelRatio >= 2 &&
				window.matchMedia('(max-width: 760px)').matches
			);
	}

	BX.Aspro.xPopover.isTouch = function() {
		return document.documentElement.classList.contains('bx-touch');
	}

	BX.Aspro.xPopover.bindEvents = function() {
		if (typeof BX.Aspro.xPopover.handlers.onToggleNodeClick === 'function') {
			BX.bindDelegate(document, 'click', {className: 'xpopover-toggle'}, BX.Aspro.xPopover.handlers.onToggleNodeClick);
		}

		if (!BX.Aspro.xPopover.isTouch()) {
			if (typeof BX.Aspro.xPopover.handlers.onDocOver === 'function') {
				BX.bind(document, 'mouseover', BX.Aspro.xPopover.handlers.onDocOver);
			}
		}

		if (typeof BX.Aspro.xPopover.handlers.onDocClick === 'function') {
			BX.bind(document, 'click', BX.Aspro.xPopover.handlers.onDocClick);
		}

		if (typeof BX.Aspro.xPopover.handlers.onOverlayClick === 'function') {
			BX.bindDelegate(document, 'click', {className: 'xpopover-overlay'}, BX.Aspro.xPopover.handlers.onOverlayClick);
		}

		if (typeof BX.Aspro.xPopover.handlers.onWindowResize === 'function') {
			window.addEventListener('resize', BX.Aspro.xPopover.handlers.onWindowResize, true);
		}
	}

	BX.Aspro.xPopover.bindEvents();
}
