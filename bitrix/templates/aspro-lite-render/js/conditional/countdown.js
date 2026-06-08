if (!funcDefined("initCountdown")) {
	var initCountdown = function initCountdown() {
		$(".countdown:not(.countdown-inited)").each(function () {
			var _this = $(this);

			if (_this.hasClass("init-if-visible") && !_this.is(":visible")) return;
			_this.addClass("countdown-inited");

			var activeTo = _this.find(".countdown__active-to").text(),
				dateTo = new Date(activeTo.replace(/(\d+)\.(\d+)\.(\d+)/, "$3/$2/$1"));

			var checkDate = new Date() < dateTo;

			if (checkDate) {
				if (_this.hasClass("compact"))
					_this.find(".countdown__items").countdown(
						{
							until: dateTo,
							format: "dHMS",
							compact: true,
							padZeroes: true,
							layout:
								'{d<}<span class="days countdown__item">{dn}<div class="text">{dl}</div></span>{d>} <span class="hours countdown__item">{hn}<div class="text">{hl}</div></span> <span class="minutes countdown__item">{mn}<div class="text">{ml}</div></span> <span class="sec countdown__item">{sn}<div class="text">{sl}</div></span>',
							onExpiry: onExpiryCountdown,
						},
						$.countdown.regionalOptions["ru"]
					);
				else
					_this.find(".countdown__items").countdown(
						{
							until: dateTo,
							format: "dHMS",
							padZeroes: true,
							layout:
								'{d<}<span class="days countdown__item">{dnn}<div class="text">{dl}</div></span>{d>} <span class="hours countdown__item">{hnn}<div class="text">{hl}</div></span> <span class="minutes countdown__item">{mnn}<div class="text">{ml}</div></span> <span class="sec countdown__item">{snn}<div class="text">{sl}</div></span>',
							onExpiry: onExpiryCountdown,
						},
						$.countdown.regionalOptions["ru"]
					);
			} else {
				_this.hide();
			}
		});
	};
}

if (!funcDefined("onExpiryCountdown")) {
	function onExpiryCountdown() {
		var _this = $(this);
		var countdownBlock = _this.parents(".countdown");

		if (countdownBlock.length) {
			countdownBlock.hide();
		}
	}
}

if (!funcDefined("initCountdownTime")) {
	var initCountdownTime = function initCountdownTime(block, time) {
		if (time) {
			var dateTo = new Date(time.replace(/(\d+)\.(\d+)\.(\d+)/, "$3/$2/$1"));
			block.find(".countdown__items").countdown("destroy");
			if (block.hasClass("compact"))
				block.find(".countdown__items").countdown(
					{
						until: dateTo,
						format: "dHM",
						compact: true,
						padZeroes: true,
						layout:
							'{d<}<span class="days countdown__item">{dn}<div class="text">{dl}</div></span>{d>} <span class="hours countdown__item">{hn}<div class="text">{hl}</div></span> <span class="minutes countdown__item">{mn}<div class="text">{ml}</div></span> <span class="sec countdown__item">{sn}<div class="text">{sl}</div></span>',
						onExpiry: onExpiryCountdownTime,
					},
					$.countdown.regionalOptions["ru"]
				);
			elsecountdown__items;
			block.find(".countdown__items").countdown(
				{
					until: dateTo,
					format: "dHMS",
					padZeroes: true,
					layout:
						'{d<}<span class="days countdown__item">{dnn}<div class="text">{dl}</div></span>{d>} <span class="hours countdown__item">{hnn}<div class="text">{hl}</div></span> <span class="minutes countdown__item">{mnn}<div class="text">{ml}</div></span> <span class="sec countdown__item">{snn}<div class="text">{sl}</div></span>',
					onExpiry: onExpiryCountdownTime,
				},
				$.countdown.regionalOptions["ru"]
			);
			block.find(".countdown").show();
		} else {
			block.find(".countdown").hide();
		}
	};
}

if (!funcDefined("onExpiryCountdownTime")) {
	function onExpiryCountdownTime() { }
}

readyDOM(() => {
	initCountdown();
});