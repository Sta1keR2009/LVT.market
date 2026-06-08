(function (global) {
    'use strict';

    var CACHE_PREFIX = 'lvt_etm_offers_v2_';
    var CACHE_TTL_MS = 172800000; // 2 суток

    function cacheKey(elementId, layout) {
        return CACHE_PREFIX + elementId + '_' + (layout || 'desktop');
    }

    function readCacheEntry(elementId, layout) {
        try {
            var raw = localStorage.getItem(cacheKey(elementId, layout));
            if (!raw) {
                return null;
            }
            var data = JSON.parse(raw);
            if (!data || typeof data.ts !== 'number' || !data.payload) {
                return null;
            }
            if (Date.now() - data.ts > CACHE_TTL_MS) {
                localStorage.removeItem(cacheKey(elementId, layout));
                return null;
            }
            return data.payload;
        } catch (e) {
            return null;
        }
    }

    function writeCacheEntry(elementId, layout, payload) {
        try {
            localStorage.setItem(cacheKey(elementId, layout), JSON.stringify({
                ts: Date.now(),
                payload: payload
            }));
        } catch (e) {
            // quota / private mode
        }
    }

    function formatRub(value) {
        var num = typeof value === 'number' ? value : parseFloat(value);
        if (isNaN(num) || num <= 0) {
            return '—';
        }
        try {
            return new Intl.NumberFormat('ru-RU', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num) + ' ₽';
        } catch (e) {
            return num.toFixed(2).replace('.', ',') + ' ₽';
        }
    }

    function resolveTotalQty(payload) {
        var totalQty = parseInt(payload && payload.totalQty || '0', 10) || 0;
        if (totalQty > 0 || !payload || !payload.storePriceData || !payload.storePriceData.stores) {
            return totalQty;
        }
        Object.keys(payload.storePriceData.stores).forEach(function (storeId) {
            totalQty += parseInt(payload.storePriceData.stores[storeId].quantity || '0', 10) || 0;
        });
        return totalQty;
    }

    function syncCardPriceAndQty(payload) {
        if (!payload || !payload.ok) {
            return;
        }

        var totalQty = resolveTotalQty(payload);
        var qtyNode = document.getElementById('lvt-catalog-available-qty-sum');
        if (qtyNode && totalQty >= 0) {
            qtyNode.textContent = totalQty > 0 ? String(totalQty) : '0';
        }

        var minPriceRub = parseFloat(payload.priceRub || '0');
        if (!(minPriceRub > 0)) {
            var ext = payload.storePriceData && Array.isArray(payload.storePriceData.extendedPrices)
                ? payload.storePriceData.extendedPrices
                : [];
            if (ext.length) {
                minPriceRub = ext.reduce(function (best, tier) {
                    var p = parseFloat((tier && tier.PRICE) || tier && tier.price || 0);
                    return (p > 0 && (best <= 0 || p < best)) ? p : best;
                }, 0);
            }
        }

        if (minPriceRub > 0) {
            var priceNode =
                document.querySelector('.catalog-detail__forms .js-lvt-etm-card-price') ||
                document.querySelector('.catalog-detail__buy-block .js-lvt-etm-card-price') ||
                document.querySelector('.catalog-detail__buy-block .price__new .price__new-val');
            if (priceNode) {
                priceNode.textContent = 'от ' + formatRub(minPriceRub);
            }

            var schemaPrice = document.getElementById('lvt-schema-price');
            if (schemaPrice) {
                schemaPrice.setAttribute('content', minPriceRub.toFixed(2));
            }
        } else {
            var emptyPriceNode =
                document.querySelector('.catalog-detail__forms .js-lvt-etm-card-price') ||
                document.querySelector('.catalog-detail__buy-block .js-lvt-etm-card-price') ||
                document.querySelector('.catalog-detail__buy-block .price__new .price__new-val');
            if (emptyPriceNode) {
                emptyPriceNode.textContent = 'от —';
            }

            var schemaPriceEmpty = document.getElementById('lvt-schema-price');
            if (schemaPriceEmpty) {
                schemaPriceEmpty.setAttribute('content', '0.00');
            }
        }
    }

    function patchStockStatus(totalQty) {
        var inStock = totalQty > 0;
        var statusEl = document.querySelector('.js-replace-status.status-icon');
        var statusContainer = statusEl ? statusEl.closest('.status-container') : null;
        var availabilityLink = document.querySelector('link[itemprop="availability"]');
        var statusUse = statusContainer ? statusContainer.querySelector('use') : null;
        var statusWrap = document.getElementById('lvt-etm-status-wrap');

        if (statusWrap) {
            statusWrap.style.visibility = 'visible';
        }

        if (statusEl) {
            statusEl.textContent = inStock ? 'В наличии' : 'Под заказ';
            statusEl.classList.remove('nostock', 'instock');
            statusEl.classList.add(inStock ? 'instock' : 'nostock');
        }
        if (statusContainer) {
            statusContainer.classList.remove('nostock', 'instock');
            statusContainer.classList.add(inStock ? 'instock' : 'nostock');
            statusContainer.setAttribute('data-state', inStock ? 'instock' : 'nostock');
        }
        if (statusUse) {
            var href = statusUse.getAttribute('xlink:href') || statusUse.getAttribute('href') || '';
            var nextHref;
            if (href) {
                nextHref = inStock
                    ? href.replace('#nostock_lg', '#instock_lg').replace('#nostock', '#instock')
                    : href.replace('#instock_lg', '#nostock_lg').replace('#instock', '#nostock');
            } else {
                nextHref = '/bitrix/templates/aspro-lite/images/svg/catalog/item_status_icons.svg#' + (inStock ? 'instock_lg' : 'nostock_lg');
            }
            statusUse.setAttribute('xlink:href', nextHref);
            statusUse.setAttribute('href', nextHref);
        }
        if (availabilityLink) {
            availabilityLink.setAttribute('href', inStock ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock');
        }

        if (typeof global.lvtSyncDetailBuyBtn === 'function') {
            global.lvtSyncDetailBuyBtn(totalQty);
        }
    }

    function mergeStorePriceData(payload) {
        if (!payload || !payload.storePriceData) {
            return;
        }
        if (typeof global.storePriceData !== 'object' || global.storePriceData === null) {
            global.storePriceData = { stores: {}, extendedPrices: [] };
        }
        var next = payload.storePriceData;
        if (next.extendedPrices) {
            global.storePriceData.extendedPrices = next.extendedPrices;
        }
        if (next.stores) {
            global.storePriceData.stores = next.stores;
        }
        if (typeof next.basePrice === 'number') {
            global.storePriceData.basePrice = next.basePrice;
        }
        if (typeof next.minOrderQuantity === 'number' && next.minOrderQuantity > 0) {
            global.storePriceData.minOrderQuantity = next.minOrderQuantity;
        }
        if (typeof next.usdToRub === 'number' && next.usdToRub > 0) {
            global.storePriceData.usdToRub = next.usdToRub;
        }
    }

    function isUsablePayload(payload) {
        return !!(payload && payload.ok && payload.html);
    }

    function initDesktopStores() {
        var attempts = 0;
        function tryInit() {
            attempts += 1;
            var ready = typeof global.initStoreCurrencySwitch === 'function'
                && typeof global.initPriceTiersNavigation === 'function';
            if (!ready && attempts < 40) {
                setTimeout(tryInit, 100);
                return;
            }
            if (typeof global.initStoreCurrencySwitch === 'function') {
                global.initStoreCurrencySwitch();
            }
            if (typeof global.initPriceTiersNavigation === 'function') {
                global.initPriceTiersNavigation();
            }
            if (typeof global.updateStoreTotal === 'function' && global.storePriceData && global.storePriceData.stores) {
                Object.keys(global.storePriceData.stores).forEach(function (storeId) {
                    global.updateStoreTotal(storeId);
                });
            }
            if (typeof global.loadCartState === 'function') {
                setTimeout(global.loadCartState, 300);
            }
        }
        tryInit();
    }

    function runInjectedScripts(container) {
        if (!container) {
            return;
        }
        container.querySelectorAll('script').forEach(function (oldScript) {
            var script = document.createElement('script');
            if (oldScript.src) {
                script.src = oldScript.src;
                script.defer = oldScript.defer;
            } else {
                script.textContent = oldScript.textContent;
            }
            oldScript.parentNode.replaceChild(script, oldScript);
        });
    }

    function revealRoot(root) {
        if (!root) {
            return;
        }
        if (root.style.display === 'none') {
            root.style.display = '';
        }
        var title = root.parentNode ? root.parentNode.querySelector('.store-prices__title-new') : null;
        if (title && title.style.display === 'none') {
            title.style.display = '';
        }
    }

    function syncFromPayload(payload) {
        if (!isUsablePayload(payload)) {
            return;
        }
        mergeStorePriceData(payload);
        patchStockStatus(resolveTotalQty(payload));
        syncCardPriceAndQty(payload);
    }

    function applyPayload(root, payload, fromCache) {
        var loading = root.querySelector('.lvt-etm-store-offers__loading');
        var content = root.querySelector('.lvt-etm-store-offers__content');
        if (!content) {
            return;
        }
        if (loading) {
            loading.style.display = 'none';
        }
        if (!payload || !payload.ok || !payload.html) {
            if (loading) {
                loading.innerHTML = '<span class="lvt-etm-store-offers__error">Не удалось загрузить цены и остатки ETM.</span>';
                loading.style.display = '';
            }
            patchStockStatus(0);
            syncCardPriceAndQty({ ok: true, totalQty: 0, priceRub: 0, storePriceData: null });
            return;
        }

        revealRoot(root);
        content.innerHTML = payload.html;
        mergeStorePriceData(payload);

        var layout = root.getAttribute('data-layout') || 'desktop';
        if (layout === 'mobile') {
            runInjectedScripts(content);
        } else {
            initDesktopStores();
            global.document.dispatchEvent(new CustomEvent('lvt:supplier-offers-rendered', { detail: { root: content } }));
        }

        patchStockStatus(resolveTotalQty(payload));
        syncCardPriceAndQty(payload);
        global.document.dispatchEvent(new CustomEvent('lvt:etm-store-offers-rendered', { detail: { root: root, payload: payload, fromCache: !!fromCache } }));
    }

    function restoreCardFromCache(elementId, layout) {
        var payload = readCacheEntry(elementId, layout || 'desktop');
        if (!isUsablePayload(payload)) {
            return false;
        }
        syncCardPriceAndQty(payload);
        patchStockStatus(resolveTotalQty(payload));
        return true;
    }

    function markCachedIfReady(root) {
        if (!root) {
            return;
        }
        var elementId = root.getAttribute('data-element-id') || '0';
        var layout = root.getAttribute('data-layout') || 'desktop';
        if (!readCacheEntry(elementId, layout)) {
            return;
        }
        root.classList.add('lvt-etm-store-offers--cached');
        restoreCardFromCache(elementId, layout);
    }

    function readServerRenderedPayload(root) {
        if (!root || root.getAttribute('data-ssr-ready') !== 'Y') {
            return null;
        }
        var jsonNode = global.document.getElementById('lvt-etm-offers-ssr-json');
        if (!jsonNode || !jsonNode.textContent) {
            return null;
        }
        try {
            var payload = JSON.parse(jsonNode.textContent);
            return isUsablePayload(payload) ? payload : null;
        } catch (e) {
            return null;
        }
    }

    function hasRenderedTable(root) {
        if (!root) {
            return false;
        }
        var content = root.querySelector('.lvt-etm-store-offers__content');
        return !!(content && content.querySelector('.js-getchips-offer-row, .js-getchips-offers-table tbody tr td:not([colspan="6"])'));
    }

    function loadRoot(root) {
        if (!root || root.dataset.loaded === 'Y') {
            return;
        }
        if (root.dataset.loading === 'Y') {
            return;
        }
        root.dataset.loading = 'Y';
        var elementId = root.getAttribute('data-element-id') || '0';
        var layout = root.getAttribute('data-layout') || 'desktop';

        var ssrPayload = readServerRenderedPayload(root);
        if (isUsablePayload(ssrPayload)) {
            writeCacheEntry(elementId, layout, ssrPayload);
            applyPayload(root, ssrPayload, true);
            root.dataset.loading = 'N';
            root.dataset.loaded = 'Y';
            return;
        }

        if (hasRenderedTable(root)) {
            var ssrPayloadFromDom = readServerRenderedPayload(root);
            if (isUsablePayload(ssrPayloadFromDom)) {
                mergeStorePriceData(ssrPayloadFromDom);
                patchStockStatus(resolveTotalQty(ssrPayloadFromDom));
                syncCardPriceAndQty(ssrPayloadFromDom);
            } else {
                var qtyFromDom = 0;
                root.querySelectorAll('.js-getchips-offer-row').forEach(function (row) {
                    var cells = row.querySelectorAll('td');
                    if (cells.length >= 4) {
                        qtyFromDom += parseInt(cells[3].textContent || '0', 10) || 0;
                    }
                });
                patchStockStatus(qtyFromDom);
            }
            var loadingNode = root.querySelector('.lvt-etm-store-offers__loading');
            if (loadingNode) {
                loadingNode.style.display = 'none';
            }
            root.dataset.loading = 'N';
            root.dataset.loaded = 'Y';
            initDesktopStores();
            return;
        }

        var cachedPayload = readCacheEntry(elementId, layout);
        if (isUsablePayload(cachedPayload)) {
            applyPayload(root, cachedPayload, true);
            root.dataset.loading = 'N';
            root.dataset.loaded = 'Y';
            return;
        }

        var formData = new FormData();
        formData.append('element_id', elementId);
        formData.append('layout', layout);

        fetch('/local/api/lvt_etm_offers_render.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                if (isUsablePayload(payload)) {
                    writeCacheEntry(elementId, layout, payload);
                }
                applyPayload(root, payload, false);
                root.dataset.loading = 'N';
                if (isUsablePayload(payload)) {
                    root.dataset.loaded = 'Y';
                }
            })
            .catch(function () {
                applyPayload(root, { ok: false }, false);
                root.dataset.loading = 'N';
            });
    }

    function boot() {
        global.document.querySelectorAll('.lvt-etm-store-offers[data-element-id]').forEach(loadRoot);
    }

    function scheduleBoot() {
        boot();
        if (global.document.readyState === 'loading') {
            global.document.addEventListener('DOMContentLoaded', boot);
        }
        global.window.addEventListener('load', boot);
    }

    global.LvtEtmStoreOffers = {
        boot: boot,
        applyPayload: applyPayload,
        syncFromPayload: syncFromPayload,
        restoreCardFromCache: restoreCardFromCache,
        markCachedIfReady: markCachedIfReady,
        readCacheEntry: readCacheEntry
    };

    scheduleBoot();
})(window);
