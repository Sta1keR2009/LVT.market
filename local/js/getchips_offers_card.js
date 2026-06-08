(function () {
    'use strict';

    var API_URL = '/local/api/getchips_basket_add.php';
    var API_UPDATE_URL = '/local/api/getchips_basket_update_qty.php';
    var API_STATE_URL = '/local/api/getchips_basket_state.php';
    var OFFERS_RENDER_URL = '/local/api/getchips_offers_render.php';
    var MODAL_ID = 'getchips-offers-modal-root';

    function resolveCartStoreUrl() {
        if (window.LVT_CART_STORE_URL) {
            return window.LVT_CART_STORE_URL;
        }
        if (typeof arAsproOptions !== 'undefined' && arAsproOptions.SITE_TEMPLATE_PATH) {
            return String(arAsproOptions.SITE_TEMPLATE_PATH).replace(/\/?$/, '') + '/ajax/cart_store.php';
        }
        return '/bitrix/templates/aspro-lite-render/ajax/cart_store.php';
    }

    var CART_STORE_URL = resolveCartStoreUrl();
    var CBR_RATE_URL = '/local/api/lvt_cbr_usd_rate.php';
    var cbrRateRefreshPromise = null;

    function getSessid() {
        if (typeof BX !== 'undefined' && BX.bitrix_sessid) {
            return BX.bitrix_sessid();
        }
        var el = document.querySelector('[data-bitrix-sessid]');
        return el ? el.getAttribute('data-bitrix-sessid') || '' : '';
    }

    function parseTiersJson(str) {
        if (!str) {
            return [];
        }
        try {
            var parsed = JSON.parse(str);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function snapQty(q, min, step) {
        var m = Math.max(1, parseInt(min, 10) || 1);
        var s = Math.max(1, parseInt(step, 10) || 1);
        var n = parseInt(q, 10);
        if (isNaN(n) || n < 1) {
            n = m;
        }
        if (n < m) {
            n = m;
        } else {
            var rem = (n - m) % s;
            if (rem !== 0) {
                n += s - rem;
            }
        }
        return n;
    }

    function unitRubForQty(tiers, qty) {
        if (!tiers.length) {
            return null;
        }
        var sorted = tiers.slice().sort(function (a, b) {
            return (a.qty || 0) - (b.qty || 0);
        });
        var best = null;
        for (var i = 0; i < sorted.length; i++) {
            if (sorted[i].qty <= qty) {
                best = parseFloat(sorted[i].rub);
            }
        }
        if (best === null || isNaN(best)) {
            best = parseFloat(sorted[0].rub);
        }
        return best > 0 ? best : null;
    }

    function formatRubJs(v) {
        var n = typeof v === 'number' ? v : parseFloat(v);
        if (isNaN(n) || n <= 0) {
            return '—';
        }
        try {
            return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n) + ' ₽';
        } catch (e2) {
            return n.toFixed(2).replace('.', ',') + ' ₽';
        }
    }

    function formatPriceTierLine(qty, priceRub, currency, rate, tierEl) {
        var q = parseInt(qty, 10) || 1;
        var rub = typeof priceRub === 'number' ? priceRub : parseFloat(priceRub);
        if (!isFinite(rub) || rub <= 0) {
            return '—';
        }
        if (currency === 'USD') {
            var usdRate = isFinite(rate) && rate > 0 ? rate : 74.8806;
            var priceSrc = tierEl ? parseFloat(tierEl.getAttribute('data-price-src') || '0') : NaN;
            var srcCcy = tierEl ? String(tierEl.getAttribute('data-src-currency') || 'USD').toUpperCase() : 'USD';
            var usdVal;
            if (isFinite(priceSrc) && priceSrc > 0 && (srcCcy === 'USD' || srcCcy === 'EUR')) {
                usdVal = priceSrc;
            } else {
                usdVal = rub / usdRate;
            }
            return 'х ' + q + ' шт. $' + formatCurrencyValue(usdVal, 'USD') + ';';
        }
        return 'х ' + q + ' шт. ' + formatCurrencyValue(rub, 'RUB') + ' ₽;';
    }

    function formatCurrencyValue(v, currency) {
        var n = typeof v === 'number' ? v : parseFloat(v);
        if (isNaN(n) || n <= 0) {
            return '—';
        }
        if (currency === 'USD') {
            try {
                return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 4 }).format(n);
            } catch (e2) {
                return n.toFixed(4).replace('.', ',');
            }
        }
        try {
            return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
        } catch (e3) {
            return n.toFixed(2).replace('.', ',');
        }
    }

    function formatByTableCurrency(amountRub, table) {
        var amount = typeof amountRub === 'number' ? amountRub : parseFloat(amountRub);
        if (!isFinite(amount) || amount <= 0) return '—';
        var ccy = String((table && table.getAttribute('data-display-currency')) || 'RUB').toUpperCase();
        var rate = parseFloat((table && table.getAttribute('data-usd-to-rub')) || '0');
        if (!isFinite(rate) || rate <= 0) rate = 74.8806;
        if (ccy === 'USD') {
            return '$' + formatCurrencyValue(amount / rate, 'USD');
        }
        return formatCurrencyValue(amount, 'RUB') + ' ₽';
    }

    function parseRubFromTierText(txt) {
        var raw = String(txt || '').replace(/\s+/g, ' ').trim();
        var m = raw.match(/([0-9]+(?:[.,][0-9]+)?)\s*(?:₽|руб\.?)/i);
        if (!m) {
            m = raw.match(/(?:шт\.?\s*|—\s*)([0-9]+(?:[.,][0-9]+)?)/i);
        }
        if (!m) {
            var all = raw.match(/([0-9]+(?:[.,][0-9]+)?)/g);
            if (all && all.length) {
                m = [null, all[all.length - 1]];
            }
        }
        if (!m) return null;
        var n = parseFloat(String(m[1] || '').replace(',', '.'));
        return isNaN(n) ? null : n;
    }

    function applyCurrencyToTable(table, currency) {
        if (!table) return;
        var nextCurrency = String(currency || '').toUpperCase() === 'USD' ? 'USD' : 'RUB';
        var rate = parseFloat(table.getAttribute('data-usd-to-rub') || '0');
        if (!isFinite(rate) || rate <= 0) rate = 74.8806;
        table.setAttribute('data-display-currency', nextCurrency);

        table.querySelectorAll('.js-getchips-price-tier').forEach(function (el) {
            var qty = parseInt(el.getAttribute('data-tier-qty') || '1', 10) || 1;
            var priceRub = parseFloat(el.getAttribute('data-price-rub') || '0');
            if (!isFinite(priceRub) || priceRub <= 0) {
                var parsed = parseRubFromTierText(el.textContent || '');
                if (parsed !== null) {
                    priceRub = parsed;
                    el.setAttribute('data-price-rub', String(priceRub));
                }
            }
            if (!isFinite(priceRub) || priceRub <= 0) return;
            el.textContent = formatPriceTierLine(qty, priceRub, nextCurrency, rate, el);
        });

        table.querySelectorAll('.js-getchips-offer-row').forEach(function (row) {
            var btn = row.querySelector('.js-getchips-add-basket');
            updateGetchipsRow(row, btn || null);
        });

        var switcher = table.querySelector('.js-getchips-currency-switch');
        if (switcher) {
            switcher.setAttribute('data-display-currency', nextCurrency);
            var label = switcher.querySelector('.js-getchips-currency-label');
            if (label) label.textContent = nextCurrency === 'USD' ? '$ USD' : '₽ RUB';
            switcher.querySelectorAll('.getchips-currency-switch__item').forEach(function (btn) {
                var active = (btn.getAttribute('data-currency') || '').toUpperCase() === nextCurrency;
                btn.classList.toggle('is-active', active);
            });
        }

        var rateEl = getCbrRateElement(table);
        if (rateEl) {
            var rateText = Number(rate).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            rateEl.textContent = rateText + ' ₽/$';
        }
        var rubAlert = table.querySelector('.js-getchips-rub-alert');
        if (rubAlert) {
            var rubInHead = !!rubAlert.closest('thead') && !rubAlert.closest('.getchips-currency-switch__menu');
            rubAlert.style.display = (!rubInHead && nextCurrency === 'RUB') ? 'inline-flex' : 'none';
        }
        var usdAlert = table.querySelector('.js-getchips-usd-alert');
        if (usdAlert) {
            var usdInHead = !!usdAlert.closest('thead') && !usdAlert.closest('.getchips-currency-switch__menu');
            usdAlert.style.display = (!usdInHead && nextCurrency === 'USD') ? 'inline-flex' : 'none';
        }
        var rateAlert = table.querySelector('.js-getchips-rate-alert');
        if (rateAlert) {
            rateAlert.style.display = 'inline-flex';
        }
    }

    function getCbrRateElement(table) {
        if (!table) {
            return null;
        }
        return table.querySelector('.getchips-currency-switch__rate.js-getchips-cbr-rate')
            || table.querySelector('.js-getchips-cbr-rate');
    }

    function normalizeLegacyCurrencyHeader(table) {
        if (!table) {
            return;
        }
        table.querySelectorAll('.getchips-currency-switch-label').forEach(function (el) {
            el.style.display = 'none';
        });
        table.querySelectorAll('.js-getchips-cbr-rate').forEach(function (el) {
            if (!el.classList.contains('getchips-currency-switch__rate')) {
                el.style.display = 'none';
            }
        });
        table.querySelectorAll('th .getchips-price-head__controls > .getchips-currency-alert, th .getchips-currency-switch-wrap > .getchips-currency-alert').forEach(function (el) {
            el.style.display = 'none';
        });
        table.querySelectorAll('.js-getchips-rate-alert').forEach(function (el) {
            el.style.display = 'inline-flex';
        });
    }

    function closeCurrencySwitchMenu(switcher) {
        if (!switcher) {
            return;
        }
        switcher.classList.remove('is-open');
        var trigger = switcher.querySelector('.getchips-currency-switch__trigger');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
    }

    function applyCbrRateToTable(table, rate, rateDate) {
        if (!table || !isFinite(rate) || rate <= 0) {
            return;
        }
        table.setAttribute('data-usd-to-rub', String(rate));
        var rateEl = getCbrRateElement(table);
        if (rateEl) {
            if (rateDate) {
                rateEl.setAttribute('data-rate-date', rateDate);
            }
            var rateText = Number(rate).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            rateEl.textContent = rateText + ' ₽/$';
        }
        var usdAlert = table.querySelector('.js-getchips-usd-alert');
        if (usdAlert && rateDate) {
            usdAlert.setAttribute('data-notice', 'Курс ЦБ получен: ' + rateDate + '.');
        }
        var rateAlert = table.querySelector('.js-getchips-rate-alert');
        if (rateAlert) {
            rateAlert.setAttribute(
                'data-notice',
                'Если курс изменится более чем на 2%, стоимость заказа будет пересчитана.'
            );
        }
        applyCurrencyToTable(table, table.getAttribute('data-display-currency') || 'RUB');
    }

    function refreshCbrRates(root) {
        root = root || document;
        if (!root.querySelectorAll) {
            return Promise.resolve(null);
        }
        if (cbrRateRefreshPromise) {
            return cbrRateRefreshPromise;
        }
        cbrRateRefreshPromise = fetch(CBR_RATE_URL, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store'
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    return null;
                }
                var rate = parseFloat(data.rate || '0');
                var rateDate = String(data.rate_date || '');
                if (!isFinite(rate) || rate <= 0) {
                    return null;
                }
                root.querySelectorAll('.js-getchips-offers-table').forEach(function (table) {
                    applyCbrRateToTable(table, rate, rateDate);
                });
                return { rate: rate, rateDate: rateDate };
            })
            .catch(function () { return null; })
            .finally(function () {
                cbrRateRefreshPromise = null;
            });
        return cbrRateRefreshPromise;
    }

    function initCurrencySwitches(root) {
        root = root || document;
        root.querySelectorAll('.js-getchips-offers-table').forEach(function (table) {
            var switcher = table.querySelector('.js-getchips-currency-switch');
            if (!switcher || switcher.__currencyBound) return;
            switcher.__currencyBound = true;
            normalizeLegacyCurrencyHeader(table);

            var trigger = switcher.querySelector('.getchips-currency-switch__trigger');
            if (trigger) {
                trigger.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var willOpen = !switcher.classList.contains('is-open');
                    document.querySelectorAll('.js-getchips-currency-switch.is-open').forEach(function (sw) {
                        if (sw !== switcher) {
                            closeCurrencySwitchMenu(sw);
                        }
                    });
                    switcher.classList.toggle('is-open', willOpen);
                    trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                });
            }

            switcher.addEventListener('click', function (e) {
                var item = e.target.closest('.getchips-currency-switch__item');
                if (!item) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                applyCurrencyToTable(table, item.getAttribute('data-currency') || 'RUB');
                closeCurrencySwitchMenu(switcher);
            });

            applyCurrencyToTable(table, table.getAttribute('data-display-currency') || 'RUB');
        });

        if (!document.__getchipsCurrencyDocBound) {
            document.__getchipsCurrencyDocBound = true;
            document.addEventListener('click', function (e) {
                if (e.target.closest('.js-getchips-currency-switch')) {
                    return;
                }
                document.querySelectorAll('.js-getchips-currency-switch.is-open').forEach(function (sw) {
                    sw.classList.remove('is-open');
                });
            });
        }
    }

    function bestTierBreakQty(tiers, qty) {
        if (!tiers.length) {
            return null;
        }
        var sorted = tiers.slice().sort(function (a, b) {
            return (a.qty || 0) - (b.qty || 0);
        });
        var best = sorted[0].qty;
        for (var i = 0; i < sorted.length; i++) {
            if (sorted[i].qty <= qty) {
                best = sorted[i].qty;
            }
        }
        return best;
    }

    function updateAddBasketBtnState(btn, n, orderOk) {
        btn.classList.remove('getchips-offers__add-basket--in-cart', 'getchips-offers__add-basket--qty-changed');
        if (!btn.getAttribute('data-getchips-basket-item-id')) {
            btn.textContent = 'В корзину';
            return;
        }
        var synced = btn.getAttribute('data-getchips-synced-qty');
        if (orderOk && synced !== null && synced !== undefined && synced !== '' && String(n) === String(synced)) {
            btn.classList.add('getchips-offers__add-basket--in-cart');
            btn.textContent = 'В корзине';
            return;
        }
        btn.classList.add('getchips-offers__add-basket--qty-changed');
        btn.textContent = 'Обновить';
    }

    function buildOfferKey(data) {
        var provider = String(data.provider || '').trim().toLowerCase();
        var part = String(data.part || '').trim().toLowerCase();
        var supplier = String(data.supplier || '').trim().toLowerCase();
        var url = String(data.url || '').trim().toLowerCase();
        var sourcePriceRaw = String(data.sourcePrice || '').trim().replace(',', '.');
        var sourcePriceNum = parseFloat(sourcePriceRaw);
        var sourcePrice = isNaN(sourcePriceNum) ? sourcePriceRaw : sourcePriceNum.toFixed(4);
        var leadDays = String(data.leadDays || '').trim();
        return [provider, part, supplier, url, sourcePrice, leadDays].join('|');
    }

    function buildOfferLooseKey(data) {
        var provider = String(data.provider || '').trim().toLowerCase();
        var part = String(data.part || '').trim().toLowerCase();
        var supplier = String(data.supplier || '').trim().toLowerCase();
        return [provider, part, supplier].join('|');
    }

    function hydrateRowsFromBasketState(root) {
        root = root || document;
        fetch(API_STATE_URL, { method: 'GET', credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok || !Array.isArray(data.items)) {
                    return;
                }
                var map = {};
                var mapLoose = {};
                var basketKeys = [];
                data.items.forEach(function (it) {
                    var key = buildOfferKey({
                        provider: it.provider,
                        part: it.part_number,
                        supplier: it.supplier,
                        url: it.url,
                        sourcePrice: it.source_price,
                        leadDays: it.lead_days
                    });
                    var keyLoose = buildOfferLooseKey({
                        provider: it.provider,
                        part: it.part_number,
                        supplier: it.supplier
                    });
                    if (!map[key]) {
                        map[key] = [];
                    }
                    map[key].push(it);
                    if (!mapLoose[keyLoose]) {
                        mapLoose[keyLoose] = [];
                    }
                    mapLoose[keyLoose].push(it);
                    basketKeys.push({ strict: key, loose: keyLoose });
                });

                var bound = 0;
                var boundStrict = 0;
                var boundLoose = 0;
                var rowKeys = [];
                root.querySelectorAll('.js-getchips-offer-row .js-getchips-add-basket').forEach(function (btn) {
                    if (btn.hasAttribute('data-bitrix-store-id')) {
                        return;
                    }
                    var tr = btn.closest('tr');
                    var strictKey = buildOfferKey({
                        provider: btn.getAttribute('data-provider') || '',
                        part: btn.getAttribute('data-part') || '',
                        supplier: btn.getAttribute('data-supplier') || '',
                        url: btn.getAttribute('data-url') || '',
                        sourcePrice: btn.getAttribute('data-source-price') || '',
                        leadDays: btn.getAttribute('data-lead-days') || ''
                    });
                    var looseKey = buildOfferLooseKey({
                        provider: btn.getAttribute('data-provider') || '',
                        part: btn.getAttribute('data-part') || '',
                        supplier: btn.getAttribute('data-supplier') || ''
                    });
                    rowKeys.push({ strict: strictKey, loose: looseKey });

                    var list = map[strictKey];
                    var matchedBy = 'strict';
                    if ((!list || !list.length) && mapLoose[looseKey] && mapLoose[looseKey].length) {
                        list = mapLoose[looseKey];
                        matchedBy = 'loose';
                    }
                    if (!list || !list.length) {
                        return;
                    }
                    var it = list.shift();
                    if (!it || !it.basket_item_id) {
                        return;
                    }
                    btn.setAttribute('data-getchips-basket-item-id', String(it.basket_item_id));
                    btn.setAttribute('data-getchips-synced-qty', String(it.quantity || ''));
                    var inp = tr ? tr.querySelector('.js-getchips-qty-input') : null;
                    if (inp && it.quantity > 0) {
                        inp.value = String(parseInt(it.quantity, 10));
                    }
                    updateGetchipsRow(tr, btn);
                    bound++;
                    if (matchedBy === 'strict') {
                        boundStrict++;
                    } else {
                        boundLoose++;
                    }
                });
            })
            .catch(function () {});
    }

    function updateGetchipsRow(tr, btn) {
        if (!tr) {
            return;
        }
        var inp = tr.querySelector('.js-getchips-qty-input');
        if (!inp) {
            return;
        }
        var minO = parseInt(inp.getAttribute('data-min-order') || tr.getAttribute('data-min-order') || '1', 10) || 1;
        var st = parseInt(inp.getAttribute('data-order-step') || tr.getAttribute('data-order-step') || '1', 10) || 1;
        var tiersSrc = (btn && btn.getAttribute('data-tiers-rub-json')) || tr.getAttribute('data-tiers-rub-json') || '';
        var tiers = parseTiersJson(tiersSrc);
        var raw = (inp.value || '').trim();
        var n = raw === '' ? null : parseInt(raw, 10);
        var hintEl = tr.querySelector('.js-getchips-qty-hint');
        var totalEl = tr.querySelector('.js-getchips-row-total');
        var hint = '';
        var available = getRowAvailableQty(tr);
        if (raw !== '' && (n === null || isNaN(n))) {
            hint = 'Укажите целое количество.';
        } else if (available !== null && n !== null && !isNaN(n) && n > available) {
            hint = 'Доступно ' + available + ' шт.';
        } else if (n !== null && !isNaN(n) && n < minO) {
            hint = 'Минимальный заказ: ' + minO + ' шт.';
        } else if (n !== null && !isNaN(n) && n >= minO && (n - minO) % st !== 0) {
            hint = 'Кратность заказа: ' + st + ' шт. (от мин. ' + minO + '). Ближайшее: ' + snapQty(n, minO, st) + ' шт.';
        }
        if (hintEl) {
            hintEl.textContent = hint;
            hintEl.classList.toggle('getchips-offers__qty-hint--warn', hint !== '');
        }

        var orderOk = n !== null && !isNaN(n) && n >= minO && (n - minO) % st === 0 && (available === null || n <= available);
        var qtyForTier;
        if (orderOk) {
            qtyForTier = n;
        } else if (n !== null && !isNaN(n) && n >= 1) {
            qtyForTier = Math.max(n, minO);
        } else {
            qtyForTier = minO;
        }

        var unit = unitRubForQty(tiers, qtyForTier);
        if (btn && unit !== null) {
            btn.setAttribute('data-price-rub', String(unit));
        }

        var activeBreak = tiers.length ? bestTierBreakQty(tiers, qtyForTier) : null;
        var priceCell = tr.querySelector('.getchips-offers__price-cell');
        if (priceCell) {
            var tierEls = priceCell.querySelectorAll('.js-getchips-price-tier');
            if (tierEls.length) {
                var hasActive = false;
                tierEls.forEach(function (el) {
                    var tq = parseInt(el.getAttribute('data-tier-qty') || '0', 10);
                    var isActive = activeBreak !== null && tq === activeBreak;
                    el.classList.toggle('is-active', isActive);
                    if (isActive) {
                        hasActive = true;
                    }
                });
                if (!hasActive && tierEls[0]) {
                    tierEls[0].classList.add('is-active');
                }
            }
            var rubHint = priceCell.querySelector('.getchips-offers__rub-hint');
            if (rubHint) {
                rubHint.style.display = 'none';
            }
        }

        if (totalEl) {
            if (orderOk && unit !== null) {
                var table = tr.closest('.js-getchips-offers-table');
                totalEl.textContent = 'Итого: ' + formatByTableCurrency(n * unit, table);
            } else {
                totalEl.textContent = '';
            }
        }

        if (btn) {
            updateAddBasketBtnState(btn, n, orderOk);
        }
    }

    function initGetchipsRows(root) {
        root = root || document;
        if (!root.querySelectorAll) {
            return;
        }
        root.querySelectorAll('.js-getchips-offer-row').forEach(function (row) {
            var b = row.querySelector('.js-getchips-add-basket');
            updateGetchipsRow(row, b || null);
        });
        initCurrencySwitches(root);
        refreshCbrRates(root);
    }

    function initGetchipsShowAll(root) {
        root = root || document;
        if (!root.querySelectorAll) {
            return;
        }
        root.querySelectorAll('.js-getchips-show-all').forEach(function (btn) {
            if (btn.__getchipsShowAllBound) {
                return;
            }
            btn.__getchipsShowAllBound = true;
            btn.addEventListener('click', function () {
                var wrap = btn.closest('.getchips-offers__table-wrap');
                var table = wrap && wrap.querySelector('.js-getchips-offers-table');
                if (!table || !table.classList.contains('getchips-offers__table--collapsible')) {
                    return;
                }
                var expanded = !table.classList.contains('is-expanded');
                table.classList.toggle('is-expanded', expanded);
                btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                var le = btn.getAttribute('data-label-expand') || 'Показать все';
                var lc = btn.getAttribute('data-label-collapse') || 'Свернуть';
                btn.textContent = expanded ? lc : le;
            });
        });
    }

    function resetOffersRowVisibility(root) {
        root = root || document;
        if (!root.querySelectorAll) {
            return;
        }
        root.querySelectorAll('.js-getchips-offers-table').forEach(function (table) {
            table.removeAttribute('data-visible-rows');
        });
        root.querySelectorAll('.js-getchips-offer-row').forEach(function (row) {
            row.style.display = '';
        });
        root.querySelectorAll('.js-getchips-show-more-chunk').forEach(function (btn) {
            if (btn.parentNode) {
                btn.parentNode.removeChild(btn);
            }
        });
    }

    var offerColLabels = ['Наименование', 'Бренд', 'Цена', 'Доступно, шт.', 'Срок', 'Кол-во'];

    function applyOffersResponsiveLabels(root) {
        var scopes = [];
        if (root && root.nodeType === 1) {
            if (root.classList && root.classList.contains('lvt-supplier-offers')) {
                scopes.push(root);
            } else if (root.closest) {
                var parentOffers = root.closest('.lvt-supplier-offers');
                if (parentOffers) {
                    scopes.push(parentOffers);
                }
            }
            if (!scopes.length && root.querySelectorAll) {
                root.querySelectorAll('.lvt-supplier-offers').forEach(function (el) {
                    scopes.push(el);
                });
            }
        }
        if (!scopes.length && document.querySelectorAll) {
            document.querySelectorAll('.lvt-supplier-offers').forEach(function (el) {
                scopes.push(el);
            });
        }
        scopes.forEach(function (scope) {
            scope.querySelectorAll('.js-getchips-offers-table tbody tr.js-getchips-offer-row').forEach(function (row) {
                var cells = row.querySelectorAll('td');
                cells.forEach(function (td, idx) {
                    if (offerColLabels[idx]) {
                        td.setAttribute('data-label', offerColLabels[idx]);
                    }
                });
            });
        });
    }

    function fireBasketChanged() {
        if (typeof BX !== 'undefined' && BX.onCustomEvent) {
            BX.onCustomEvent('OnBasketChange');
        }
        if (typeof jQuery !== 'undefined') {
            jQuery(document).trigger('BasketChanged');
        }
    }

    function notifyAddedToCart(node) {
        if (!(node instanceof Node)) {
            return;
        }
        if (typeof JNoticeSurface === 'function') {
            try {
                var surface = JNoticeSurface.get();
                if (surface && typeof surface.onAdd2Cart === 'function') {
                    surface.onAdd2Cart([node]);
                }
            } catch (e) {}
        }
    }

    function syncGetchipsBasketQty(btn, tr, qty, done) {
        var bid = btn.getAttribute('data-getchips-basket-item-id');
        if (!bid) {
            if (done) {
                done(true);
            }
            return;
        }
        var stubId = parseInt(btn.getAttribute('data-stub-product-id'), 10);
        var minO = btn.getAttribute('data-min-order') || '1';
        var st = btn.getAttribute('data-order-step') || '1';
        var fd = new FormData();
        fd.append('sessid', getSessid());
        fd.append('basket_item_id', bid);
        fd.append('product_id', String(stubId));
        fd.append('part_number', btn.getAttribute('data-part') || '');
        fd.append('quantity', String(qty));
        fd.append('min_order', minO);
        fd.append('order_step', st);
        fetch(API_UPDATE_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    alert((data && data.error) || 'Не удалось обновить количество в корзине');
                    if (done) {
                        done(false);
                    }
                    return;
                }
                btn.setAttribute('data-getchips-synced-qty', String(qty));
                fireBasketChanged();
                updateGetchipsRow(tr, btn);
                if (done) {
                    done(true);
                }
            })
            .catch(function () {
                alert('Сетевая ошибка');
                if (done) {
                    done(false);
                }
            });
    }

    function trySyncBasketAfterQtyBlur(tr) {
        if (!tr) {
            return;
        }
        var btn = tr.querySelector('.js-getchips-add-basket');
        if (!btn || !btn.getAttribute('data-getchips-basket-item-id')) {
            return;
        }
        var inp = tr.querySelector('.js-getchips-qty-input');
        if (!inp) {
            return;
        }
        var q = parseInt(inp.value, 10);
        if (isNaN(q) || q < 1) {
            return;
        }
        if (btn.getAttribute('data-getchips-synced-qty') === String(q)) {
            return;
        }
        syncGetchipsBasketQty(btn, tr, q, null);
    }

    function syncButtonPrice(btn) {
        var tr = btn.closest('tr');
        if (!tr) {
            return;
        }
        updateGetchipsRow(tr, btn);
    }

    function getRowAvailableQty(tr) {
        if (!tr) {
            return null;
        }
        var fromAttr = parseInt(tr.getAttribute('data-available-qty') || '', 10);
        if (!isNaN(fromAttr) && fromAttr >= 0) {
            return fromAttr;
        }
        var tds = tr.querySelectorAll('td');
        if (!tds || tds.length < 4) {
            return null;
        }
        var txt = (tds[3].textContent || '').replace(/\s+/g, ' ').trim();
        var m = txt.match(/\d+/);
        if (!m) {
            return null;
        }
        var parsed = parseInt(m[0], 10);
        return isNaN(parsed) ? null : parsed;
    }

    function validateQtyByAvailable(tr, qty) {
        var available = getRowAvailableQty(tr);
        if (available === null || available < 0) {
            return { ok: true, available: available };
        }
        if (qty > available) {
            var msg = 'Доступно ' + available + ' шт.';
            var hintEl = tr ? tr.querySelector('.js-getchips-qty-hint') : null;
            if (hintEl) {
                hintEl.textContent = msg;
                hintEl.classList.add('getchips-offers__qty-hint--warn');
            }
            return { ok: false, available: available, message: msg };
        }
        return { ok: true, available: available };
    }

    function addBitrixStoreToCart(btn, tr, qty) {
        var storeId = btn.getAttribute('data-bitrix-store-id') || '';
        var productId = btn.getAttribute('data-product-id') || '';
        var sessid = btn.getAttribute('data-sessid') || getSessid() || '';
        var fd = new FormData();
        fd.append('action', 'update_stores_in_cart');
        fd.append('product_id', String(productId || 0));
        fd.append('store_quantities', JSON.stringify((function () { var o = {}; o[String(storeId)] = qty; return o; })()));
        fd.append('sessid', String(sessid || ''));

        btn.disabled = true;
        fetch(CART_STORE_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled = false;
                if (!data || data.status !== 'success') {
                    alert((data && data.message) || 'Не удалось добавить со склада');
                    return;
                }
                fireBasketChanged();
                btn.textContent = 'В корзине';
                updateGetchipsRow(tr, btn);
                notifyAddedToCart(btn);
            })
            .catch(function () {
                btn.disabled = false;
                alert('Сетевая ошибка');
            });
    }

    function getLeadSortValue(row) {
        if (!row) {
            return 9999999;
        }
        var attrVal = parseFloat(row.getAttribute('data-sort-lead') || '');
        if (isFinite(attrVal) && attrVal >= 0) {
            return attrVal;
        }

        var cells = row.querySelectorAll('td');
        var leadText = '';
        if (cells && cells.length >= 5) {
            leadText = (cells[4].textContent || '');
        }
        leadText = String(leadText).replace(/\s+/g, ' ').trim().toLowerCase();
        if (!leadText) {
            return 9999999;
        }
        if (leadText.indexOf('в наличии') !== -1) {
            return 0;
        }

        var numMatch = leadText.match(/(\d+(?:[.,]\d+)?)/);
        if (!numMatch) {
            return 9999999;
        }
        var base = parseFloat(String(numMatch[1]).replace(',', '.'));
        if (!isFinite(base) || base < 0) {
            return 9999999;
        }

        if (/мес/.test(leadText)) {
            return base * 30;
        }
        if (/нед|week/.test(leadText)) {
            return base * 7;
        }
        if (/дн|day/.test(leadText)) {
            return base;
        }
        return base;
    }

    function getSupplierSortValue(row) {
        if (!row) {
            return '';
        }
        var byAttr = String(row.getAttribute('data-sort-supplier') || '').trim().toLowerCase();
        if (byAttr) {
            return byAttr;
        }
        var bySupplierEl = row.querySelector('.getchips-offers__supplier');
        if (bySupplierEl) {
            return String(bySupplierEl.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
        }
        var btn = row.querySelector('.js-getchips-add-basket');
        if (btn) {
            return String(btn.getAttribute('data-supplier') || '').replace(/\s+/g, ' ').trim().toLowerCase();
        }
        return '';
    }

    function isPinnedOwnStockRow(row) {
        if (!row) {
            return false;
        }
        if (row.classList && row.classList.contains('lvt-bitrix-store-row')) {
            return true;
        }
        var supplierEl = row.querySelector('.getchips-offers__supplier');
        var supplier = supplierEl ? String(supplierEl.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase() : '';
        return supplier === 'собственный склад' || supplier === 'свой склад';
    }

    function sortTable(table, sortKey, dir) {
        var tb = table.querySelector('tbody');
        if (!tb) {
            return;
        }
        var rows = Array.prototype.slice.call(tb.querySelectorAll('.js-getchips-offer-row'));
        var pinnedRows = [];
        var sortableRows = [];
        rows.forEach(function (row) {
            if (isPinnedOwnStockRow(row)) {
                pinnedRows.push(row);
            } else {
                sortableRows.push(row);
            }
        });

        sortableRows.sort(function (a, b) {
            var av;
            var bv;
            if (sortKey === 'lead') {
                av = getLeadSortValue(a);
                bv = getLeadSortValue(b);
            } else if (sortKey === 'supplier') {
                av = getSupplierSortValue(a);
                bv = getSupplierSortValue(b);
                if (av === bv) {
                    return 0;
                }
                return dir === 'asc' ? (av < bv ? -1 : 1) : (av > bv ? -1 : 1);
            } else {
                av = parseFloat(a.getAttribute('data-sort-price')) || 0;
                bv = parseFloat(b.getAttribute('data-sort-price')) || 0;
            }
            return dir === 'asc' ? av - bv : bv - av;
        });

        pinnedRows.concat(sortableRows).forEach(function (r) {
            tb.appendChild(r);
        });
    }

    function ensureModal() {
        var el = document.getElementById(MODAL_ID);
        if (el) {
            return el;
        }
        el = document.createElement('div');
        el.id = MODAL_ID;
        el.className = 'getchips-modal';
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-modal', 'true');
        el.setAttribute('aria-hidden', 'true');
        el.innerHTML = ''
            + '<div class="getchips-modal__backdrop js-getchips-modal-close" aria-hidden="true"></div>'
            + '<div class="getchips-modal__dialog" role="document">'
            + '<div class="getchips-modal__header">'
            + '<span class="getchips-modal__title">Наличие по складам</span>'
            + '<button type="button" class="getchips-modal__close js-getchips-modal-close" aria-label="Закрыть">&times;</button>'
            + '</div>'
            + '<div class="getchips-modal__body"></div>'
            + '</div>';
        document.body.appendChild(el);

        el.addEventListener('click', function (ev) {
            if (ev.target.closest('.js-getchips-modal-close')) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape' && el.getAttribute('aria-hidden') === 'false') {
                closeModal();
            }
        });

        return el;
    }

    function openModal() {
        var el = ensureModal();
        el.setAttribute('aria-hidden', 'false');
        el.classList.add('is-open');
        document.body.classList.add('getchips-modal-open');
    }

    function closeModal() {
        var el = document.getElementById(MODAL_ID);
        if (!el) {
            return;
        }
        el.setAttribute('aria-hidden', 'true');
        el.classList.remove('is-open');
        document.body.classList.remove('getchips-modal-open');
    }

    function setModalBody(html) {
        var el = ensureModal();
        var body = el.querySelector('.getchips-modal__body');
        if (body) {
            body.innerHTML = html || '';
            initGetchipsRows(body);
            initGetchipsShowAll(body);
        }
    }

    function setModalLoading() {
        setModalBody('<div class="getchips-modal__loading"><span class="getchips-modal__spinner" aria-hidden="true"></span> Загрузка…</div>');
    }

    function showOffersInModal(html) {
        openModal();
        setModalBody(html);
    }

    document.addEventListener('click', function (e) {
        var loadOffersBtn = e.target.closest('.js-getchips-load-offers-section');
        if (loadOffersBtn) {
            e.preventDefault();
            var wrap = loadOffersBtn.closest('.js-getchips-list-teaser');
            if (!wrap) {
                return;
            }
            var art = wrap.getAttribute('data-getchips-article') || '';
            if (!art || art.length < 3) {
                return;
            }
            if (wrap.__getchipsCachedHtml) {
                showOffersInModal(wrap.__getchipsCachedHtml);
                return;
            }

            loadOffersBtn.classList.add('is-loading');
            loadOffersBtn.disabled = true;
            openModal();
            setModalLoading();

            var fd = new FormData();
            fd.append('article', art);
            fd.append('page_catalog_element_id', wrap.getAttribute('data-getchips-element-id') || '0');
            fd.append('detail_page_url', wrap.getAttribute('data-getchips-detail-url') || '');
            fd.append('product_image', wrap.getAttribute('data-getchips-product-img') || '');
            fetch(OFFERS_RENDER_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    loadOffersBtn.classList.remove('is-loading');
                    loadOffersBtn.disabled = false;
                    if (!data || !data.ok) {
                        setModalBody('<p class="getchips-modal__err">Не удалось загрузить предложения.</p>');
                        return;
                    }
                    var inner = data.html && data.html.length
                        ? data.html
                        : '<p class="getchips-modal__err">Нет предложений поставщиков по этому артикулу.</p>';
                    wrap.__getchipsCachedHtml = inner;
                    setModalBody(inner);
                })
                .catch(function () {
                    loadOffersBtn.classList.remove('is-loading');
                    loadOffersBtn.disabled = false;
                    setModalBody('<p class="getchips-modal__err">Ошибка сети.</p>');
                });
            return;
        }

        var sortBtn = e.target.closest('.js-getchips-sort');
        if (sortBtn) {
            e.preventDefault();
            var table = sortBtn.closest('.js-getchips-offers-table');
            if (table) {
                sortTable(table, sortBtn.getAttribute('data-sort') || 'price', sortBtn.getAttribute('data-dir') || 'asc');
            }
            return;
        }

        var btn = e.target.closest('.js-getchips-add-basket');
        if (!btn) {
            return;
        }
        e.preventDefault();
        var tr = btn.closest('tr');
        var inp = tr ? tr.querySelector('.js-getchips-qty-input') : null;
        var minO = btn.getAttribute('data-min-order') || '1';
        var st = btn.getAttribute('data-order-step') || '1';
        var qty = inp ? snapQty(inp.value, minO, st) : snapQty(minO, minO, st);
        if (inp) {
            inp.value = String(qty);
        }

        updateGetchipsRow(tr, btn);
        var availCheck = validateQtyByAvailable(tr, qty);
        if (!availCheck.ok) {
            alert(availCheck.message || ('Доступно ' + (availCheck.available || 0) + ' шт.'));
            return;
        }

        if (btn.hasAttribute('data-bitrix-store-id')) {
            addBitrixStoreToCart(btn, tr, qty);
            return;
        }

        var stubId = parseInt(btn.getAttribute('data-stub-product-id'), 10);
        if (!stubId) {
            alert('Не настроен товар-заглушка Getchips (GETCHIPS_STUB_PRODUCT_ID).');
            return;
        }
        var existingBid = btn.getAttribute('data-getchips-basket-item-id');
        if (existingBid) {
            btn.disabled = true;
            syncGetchipsBasketQty(btn, tr, qty, function () {
                btn.disabled = false;
            });
            return;
        }

        var priceRub = btn.getAttribute('data-price-rub');
        if (priceRub === '' || priceRub == null || parseFloat(priceRub) <= 0) {
            alert('Нет цены в рублях для этой позиции.');
            return;
        }

        var section = btn.closest('.getchips-offers-section');
        var ctxId = section && section.getAttribute('data-page-catalog-element-id')
            ? section.getAttribute('data-page-catalog-element-id')
            : '';
        var productImg = '';
        var ctxDetailUrl = '';
        if (section) {
            productImg = section.getAttribute('data-product-image-src') || '';
            ctxDetailUrl = section.getAttribute('data-context-detail-url') || '';
        }
        if (!productImg) {
            productImg = btn.getAttribute('data-product-image') || '';
        }
        if (!productImg) {
            var teaser = btn.closest('.js-getchips-list-teaser');
            if (teaser) {
                productImg = teaser.getAttribute('data-getchips-product-img') || '';
                if (!ctxDetailUrl) {
                    ctxDetailUrl = teaser.getAttribute('data-getchips-detail-url') || '';
                }
            }
        }
        var fd = new FormData();
        fd.append('sessid', getSessid());
        fd.append('context_catalog_element_id', ctxId);
        fd.append('context_detail_page_url', ctxDetailUrl);
        fd.append('product_image', productImg);
        fd.append('product_id', String(stubId));
        fd.append('quantity', String(qty));
        fd.append('price_rub', priceRub);
        fd.append('tiers_rub_json', btn.getAttribute('data-tiers-rub-json') || '[]');
        fd.append('min_order', minO);
        fd.append('order_step', st);
        fd.append('lead_days', btn.getAttribute('data-lead-days') || '0');
        fd.append('brand_display', btn.getAttribute('data-brand-display') || '');
        fd.append('source_currency', btn.getAttribute('data-source-currency') || '');
        fd.append('source_price', btn.getAttribute('data-source-price') || '');
        fd.append('supplier', btn.getAttribute('data-supplier') || '');
        fd.append('part_number', btn.getAttribute('data-part') || '');
        fd.append('provider', btn.getAttribute('data-provider') || '');
        fd.append('url', btn.getAttribute('data-url') || '');
        btn.disabled = true;
        fetch(API_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) {
                    alert(data.error || 'Ошибка добавления в корзину');
                    btn.disabled = false;
                    return;
                }
                if (data.basket_item_id) {
                    btn.setAttribute('data-getchips-basket-item-id', String(data.basket_item_id));
                }
                btn.setAttribute('data-getchips-synced-qty', String(qty));
                fireBasketChanged();
                btn.textContent = 'В корзине';
                btn.disabled = false;
                updateGetchipsRow(tr, btn);
                notifyAddedToCart(btn);
            })
            .catch(function () {
                alert('Сетевая ошибка');
                btn.disabled = false;
            });
    });

    document.addEventListener('input', function (e) {
        var inp = e.target.closest('.js-getchips-qty-input');
        if (!inp) {
            return;
        }
        var tr = inp.closest('tr');
        if (!tr) {
            return;
        }
        var btn = tr.querySelector('.js-getchips-add-basket');
        updateGetchipsRow(tr, btn || null);
    });

    document.addEventListener('focusout', function (e) {
        var inp = e.target;
        if (!inp || !inp.classList || !inp.classList.contains('js-getchips-qty-input')) {
            return;
        }
        var tr = inp.closest('tr');
        if (!tr) {
            return;
        }
        var btn = tr.querySelector('.js-getchips-add-basket');
        var minO = inp.getAttribute('data-min-order') || tr.getAttribute('data-min-order') || '1';
        var st = inp.getAttribute('data-order-step') || tr.getAttribute('data-order-step') || '1';
        var raw = (inp.value || '').trim();
        if (raw === '') {
            inp.value = String(snapQty(minO, minO, st));
        } else {
            var n = parseInt(raw, 10);
            if (isNaN(n) || n < 1) {
                inp.value = String(snapQty(minO, minO, st));
            } else {
                inp.value = String(snapQty(n, minO, st));
            }
        }
        updateGetchipsRow(tr, btn || null);
        trySyncBasketAfterQtyBlur(tr);
    }, true);

    function syncDetailCardPriceFromSupplierRows() {
        var section = document.getElementById('lvt-supplier-offers');
        if (!section) {
            return;
        }
        var prices = [];
        section.querySelectorAll('.js-getchips-offer-row').forEach(function (tr) {
            var p = parseFloat(tr.getAttribute('data-sort-price') || '');
            if (!isNaN(p) && p > 0) {
                prices.push(p);
            }
        });
        var minTablePrice = prices.length ? Math.min.apply(Math, prices) : null;
        if (minTablePrice === null || isNaN(minTablePrice) || minTablePrice <= 0) {
            return;
        }
        var priceNode =
            document.querySelector('.catalog-detail__buy-block .catalog-detail__price .price__new-val') ||
            document.querySelector('.price__new .price__new-val');
        if (!priceNode) {
            return;
        }
        priceNode.textContent = formatRubJs(minTablePrice);
    }

    function bootGetchipsCard() {
        initGetchipsRows(document);
        initGetchipsShowAll(document);
        resetOffersRowVisibility(document);
        applyOffersResponsiveLabels(document);
        hydrateRowsFromBasketState(document);
        syncDetailCardPriceFromSupplierRows();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootGetchipsCard);
    } else {
        bootGetchipsCard();
    }

    document.addEventListener('lvt:supplier-offers-rendered', function (ev) {
        var root = ev && ev.detail && ev.detail.root ? ev.detail.root : document;
        initGetchipsRows(root);
        initGetchipsShowAll(root);
        resetOffersRowVisibility(root);
        applyOffersResponsiveLabels(root);
        hydrateRowsFromBasketState(root);
        syncDetailCardPriceFromSupplierRows();
    });
})();
