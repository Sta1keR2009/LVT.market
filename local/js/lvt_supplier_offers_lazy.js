(function(global) {
    'use strict';

    var CACHE_PREFIX = 'lvt_supplier_offers_v3_';
    var CACHE_TTL_MS = 300000; // 5 минут
    var REQUEST_TIMEOUT_MS = 12000;
    var MAX_RETRIES = 1;
    var SOURCES = [
        { source: 'promelec', label: 'Promelec' },
        { source: 'getchips', label: 'Getchips' }
    ];

    function bundleCacheKey(elementId) {
        return CACHE_PREFIX + elementId + '_bundle';
    }

    function readBundleCache(elementId) {
        try {
            var raw = localStorage.getItem(bundleCacheKey(elementId));
            if (!raw) {
                return null;
            }
            var data = JSON.parse(raw);
            if (!data || typeof data.ts !== 'number') {
                return null;
            }
            if (Date.now() - data.ts > CACHE_TTL_MS) {
                localStorage.removeItem(bundleCacheKey(elementId));
                return null;
            }
            return data;
        } catch (e) {
            return null;
        }
    }

    function writeBundleCache(elementId, entries, sourceSuccessCount) {
        try {
            localStorage.setItem(bundleCacheKey(elementId), JSON.stringify({
                ts: Date.now(),
                complete: true,
                source_success_count: sourceSuccessCount > 0 ? sourceSuccessCount : 0,
                promelec: typeof entries.promelec === 'string' ? entries.promelec : '',
                getchips: typeof entries.getchips === 'string' ? entries.getchips : ''
            }));
        } catch (e) {
            // ignore
        }
    }

    function cacheKey(elementId, source) {
        return CACHE_PREFIX + elementId + '_' + source;
    }

    function shouldHideFromCache(elementId) {
        // Never hide — always try to load
        return false;
    }

    
    function readLocalCacheEntry(elementId, source) {
        try {
            var raw = localStorage.getItem(cacheKey(elementId, source));
            if (!raw) {
                return null;
            }
            var data = JSON.parse(raw);
            if (!data || typeof data.ts !== 'number') {
                return null;
            }
            if (Date.now() - data.ts > CACHE_TTL_MS) {
                localStorage.removeItem(cacheKey(elementId, source));
                return null;
            }
            return {
                html: typeof data.html === 'string' ? data.html : '',
                ts: data.ts
            };
        } catch (e) {
            return null;
        }
    }

    function writeLocalCacheEntry(elementId, source, html) {
        try {
            localStorage.setItem(cacheKey(elementId, source), JSON.stringify({
                html: html || '',
                ts: Date.now()
            }));
        } catch (e) {
            // quota / private mode
        }
    }

    function readHiddenCache(elementId) {
        return shouldHideFromCache(elementId);
    }

    function hasFullLocalCache(elementId) {
        var bundle = readBundleCache(elementId);
        if (bundle && bundle.complete === true) {
            return true;
        }
        for (var i = 0; i < SOURCES.length; i++) {
            if (!readLocalCacheEntry(elementId, SOURCES[i].source)) {
                return false;
            }
        }
        return true;
    }

    /** Вызывается inline-скриптом в шаблоне до отрисовки скелетона. */
    function markCachedSectionIfReady(container, elementId) {
        if (!container || !elementId) {
            return;
        }
        if (shouldHideFromCache(elementId)) {
            container.classList.add('lvt-supplier-offers--cache-hidden');
            return;
        }
        if (hasFullLocalCache(elementId)) {
            container.classList.add('lvt-supplier-offers--cached');
        }
    }

    function init(container) {
        if (!container || container.dataset.loading === 'Y') {
            return;
        }
        container.dataset.loading = 'Y';

        var elementId = container.getAttribute('data-element-id') || '0';
        var loadingBlock = container.querySelector('.lvt-supplier-offers__loading');
        var muted = container.querySelector('.lvt-supplier-offers__muted');
        var spinner = container.querySelector('.lvt-supplier-offers__spinner');
        var content = container.querySelector('.lvt-supplier-offers__content');
        var skeleton = content ? content.querySelector('.lvt-supplier-skeleton') : null;

        function hideLoadingUi() {
            if (loadingBlock) {
                loadingBlock.style.display = 'none';
            }
            if (spinner) {
                spinner.style.display = 'none';
            }
            if (muted && muted.closest('.lvt-supplier-offers__loading')) {
                muted.textContent = '';
            } else if (muted) {
                muted.style.display = 'none';
            }
            if (skeleton && skeleton.parentNode) {
                skeleton.parentNode.removeChild(skeleton);
                skeleton = null;
            }
        }

        function getSupplierAvailableQtySum() {
            if (!content) {
                return 0;
            }
            var table = content.querySelector('.js-getchips-offers-table');
            if (!table) {
                return 0;
            }
            var sum = 0;
            table.querySelectorAll('tbody tr.js-getchips-offer-row').forEach(function(tr) {
                var raw = tr.getAttribute('data-available-qty') || '';
                var qty = parseInt(raw, 10);
                if (!isNaN(qty) && qty > 0) {
                    sum += qty;
                }
            });
            return sum;
        }

        function hideSupplierOffersSection() {
            container.style.display = 'none';
            container.setAttribute('hidden', 'hidden');
            container.setAttribute('aria-hidden', 'true');
        }

        function syncTopAvailabilityFromTable(scope) {
            var out = document.getElementById('lvt-catalog-available-qty-sum');
            if (!out) {
                return;
            }
            var root = scope && scope.querySelector ? scope : document;
            var table = root.querySelector('.js-getchips-offers-table') || document.querySelector('.js-getchips-offers-table');
            if (!table) {
                return;
            }
            var sum = 0;
            table.querySelectorAll('tbody tr.js-getchips-offer-row').forEach(function(tr) {
                var raw = tr.getAttribute('data-available-qty') || '';
                var qty = parseInt(raw, 10);
                if (!isNaN(qty) && qty > 0) {
                    sum += qty;
                }
            });
            out.textContent = String(Math.max(0, sum));
            if (typeof global.lvtSyncDetailBuyBtn === 'function') {
                global.lvtSyncDetailBuyBtn(sum);
            }
        }

        function parseQtyFromMouserRow(root) {
            var row = root.querySelector('.lvt-mouser-row');
            if (!row) {
                return 0;
            }
            var byAttr = parseInt(row.getAttribute('data-available-qty') || '0', 10);
            if (!isNaN(byAttr) && byAttr > 0) {
                return byAttr;
            }
            var cells = row.querySelectorAll('td');
            if (!cells || cells.length < 4) {
                return 0;
            }
            var txt = (cells[3].textContent || '').replace(/\s+/g, ' ').trim();
            var m = txt.match(/\d+/);
            return m ? (parseInt(m[0], 10) || 0) : 0;
        }

        function applyStatusFromMouserQty(root) {
            var qty = parseQtyFromMouserRow(root);
            var statusEl = document.querySelector('.js-replace-status.status-icon');
            var statusContainer = statusEl ? statusEl.closest('.status-container') : null;
            var availabilityLink = document.querySelector('link[itemprop="availability"]');
            var statusIcon = statusContainer ? statusContainer.querySelector('.status__svg-icon') : null;
            var statusUse = statusContainer ? statusContainer.querySelector('use') : null;
            var hrefBefore = statusUse ? (statusUse.getAttribute('xlink:href') || statusUse.getAttribute('href') || '') : '';
            var patched = false;

            if (qty > 0 && statusEl) {
                statusEl.textContent = 'В наличии';
                statusEl.classList.remove('nostock');
                statusEl.classList.add('instock');
                if (statusIcon && statusIcon.classList) {
                    statusIcon.classList.remove('nostock');
                    statusIcon.classList.add('instock');
                }
                if (statusUse) {
                    var nextHref = hrefBefore
                        ? hrefBefore.replace('#nostock_lg', '#instock_lg').replace('#nostock', '#instock')
                        : '';
                    if (!nextHref) {
                        nextHref = '/bitrix/templates/aspro-lite/images/svg/catalog/item_status_icons.svg#instock_lg';
                    }
                    statusUse.setAttribute('xlink:href', nextHref);
                    statusUse.setAttribute('href', nextHref);
                }
                if (statusContainer) {
                    statusContainer.classList.remove('nostock');
                    statusContainer.classList.add('instock');
                    statusContainer.setAttribute('data-state', 'instock');
                }
                if (availabilityLink) {
                    availabilityLink.setAttribute('href', 'http://schema.org/InStock');
                }
                patched = true;
            }

            if (!patched) {
                return;
            }
        }

        function extractOfferRowKey(row) {
            if (!row) {
                return '';
            }
            var btn = row.querySelector('.js-getchips-add-basket');
            var provider = btn ? (btn.getAttribute('data-provider') || '') : '';
            var part = btn ? (btn.getAttribute('data-part') || '') : '';
            var supplier = btn ? (btn.getAttribute('data-supplier') || '') : '';
            var sourcePrice = btn ? (btn.getAttribute('data-source-price') || '') : '';
            var lead = row.getAttribute('data-sort-lead') || '';
            return [provider, part, supplier, sourcePrice, lead].join('|').toLowerCase();
        }

        function appendChunkAsSingleTable(html, replaceTable) {
            if (!content || !html) {
                return false;
            }
            var tmp = document.createElement('div');
            tmp.innerHTML = html;

            var chunkTable = tmp.querySelector('.js-getchips-offers-table');
            if (!chunkTable) {
                return false;
            }

            var currentTable = content.querySelector('.js-getchips-offers-table');
            if (!currentTable || replaceTable) {
                var chunkSection = tmp.querySelector('.getchips-offers-section');
                if (!chunkSection) {
                    return false;
                }
                content.innerHTML = '';
                content.appendChild(chunkSection);
                skeleton = null;
                global.document.dispatchEvent(new CustomEvent('lvt:supplier-offers-rendered', { detail: { root: content } }));
                applyStatusFromMouserQty(container);
                return true;
            }

            var targetBody = currentTable.querySelector('tbody');
            var sourceBody = chunkTable.querySelector('tbody');
            if (!targetBody || !sourceBody) {
                return false;
            }

            var existingKeys = {};
            targetBody.querySelectorAll('.js-getchips-offer-row').forEach(function(row) {
                var key = extractOfferRowKey(row);
                if (key) {
                    existingKeys[key] = true;
                }
            });

            var added = 0;
            sourceBody.querySelectorAll('.js-getchips-offer-row').forEach(function(row) {
                var key = extractOfferRowKey(row);
                if (key && existingKeys[key]) {
                    return;
                }
                if (key) {
                    existingKeys[key] = true;
                }
                targetBody.appendChild(row);
                added++;
            });

            if (added > 0) {
                if (skeleton && skeleton.parentNode) {
                    skeleton.parentNode.removeChild(skeleton);
                    skeleton = null;
                }
                global.document.dispatchEvent(new CustomEvent('lvt:supplier-offers-rendered', { detail: { root: content } }));
                applyStatusFromMouserQty(container);
                syncTopAvailabilityFromTable(content);
            }
            return added > 0;
        }

        function finalizeAfterAllSources(gotAny, fromCache) {
            var supplierQtySum = getSupplierAvailableQtySum();
            if (supplierQtySum <= 0) {
                if (typeof global.lvtSyncDetailBuyBtn === 'function') {
                    global.lvtSyncDetailBuyBtn(0);
                }
                hideSupplierOffersSection();
                return;
            }
            syncTopAvailabilityFromTable(content);
            hideLoadingUi();
            if (!fromCache && muted && !muted.closest('.lvt-supplier-offers__loading')) {
                muted.style.display = '';
                muted.textContent = gotAny
                    ? 'Данные поставщиков обновлены.'
                    : 'Доступны только уже загруженные предложения.';
            }
        }

        function hydrateFromLocalCache() {
            if (shouldHideFromCache(elementId)) {
                hideSupplierOffersSection();
                return true;
            }

            var bundle = readBundleCache(elementId);
            if (bundle) {
                hideLoadingUi();
                var baseInserted = false;
                var gotAny = false;
                var bundleSources = [
                    { source: 'promelec', html: bundle.promelec || '' },
                    { source: 'getchips', html: bundle.getchips || '' }
                ];
                bundleSources.forEach(function(item) {
                    if (!item.html) {
                        return;
                    }
                    var appended = appendChunkAsSingleTable(item.html, !baseInserted);
                    if (appended && !baseInserted) {
                        baseInserted = true;
                    }
                    if (appended) {
                        gotAny = true;
                    }
                });
                finalizeAfterAllSources(gotAny, true);
                return true;
            }

            if (!hasFullLocalCache(elementId)) {
                return false;
            }

            hideLoadingUi();
            var baseInserted = false;
            var gotAny = false;

            SOURCES.forEach(function(item) {
                var entry = readLocalCacheEntry(elementId, item.source);
                if (!entry || !entry.html) {
                    return;
                }
                var appended = appendChunkAsSingleTable(entry.html, !baseInserted);
                if (appended && !baseInserted) {
                    baseInserted = true;
                }
                if (appended) {
                    gotAny = true;
                }
            });

            finalizeAfterAllSources(gotAny, true);
            return true;
        }

        if (hydrateFromLocalCache()) {
            return;
        }

        var baseInserted = false;
        var gotAny = false;
        var doneCount = 0;
        var sourceSuccessCount = 0;
        var cacheEntries = { promelec: '', getchips: '' };

        function loadSource(source) {
            var formData = new FormData();
            formData.append('element_id', elementId);
            formData.append('source', source);
            var canAbort = typeof AbortController !== 'undefined';
            var controller = canAbort ? new AbortController() : null;
            var timeoutId = setTimeout(function() {
                if (controller) {
                    try {
                        controller.abort();
                    } catch (e) {}
                }
            }, REQUEST_TIMEOUT_MS);
            return fetch('/local/api/lvt_supplier_offers_render.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData,
                signal: controller ? controller.signal : undefined
            }).then(function(response) {
                return response.json();
            }).finally(function() {
                clearTimeout(timeoutId);
            });
        }

        function loadSourceWithSoftFallback(source, label) {
            var slowTimer = setTimeout(function() {
                if (muted && !container.classList.contains('lvt-supplier-offers--cached')) {
                    muted.textContent = label + ' загружается дольше обычного...';
                }
            }, 2500);

            return loadSource(source).finally(function() {
                clearTimeout(slowTimer);
            });
        }

        function onSourceSettled() {
            doneCount += 1;
            if (doneCount < SOURCES.length) {
                return;
            }
            if (sourceSuccessCount > 0) {
                writeBundleCache(elementId, cacheEntries, sourceSuccessCount);
            }
            finalizeAfterAllSources(gotAny, false);
        }

        function runSourceTask(item) {
            var sourceSettled = false;

            function settleOnce() {
                if (sourceSettled) {
                    return;
                }
                sourceSettled = true;
                onSourceSettled();
            }

            function attemptLoad(attemptNo) {
                return loadSourceWithSoftFallback(item.source, item.label)
                .then(function(res) {
                    if (res && res.ok) {
                        sourceSuccessCount += 1;
                        cacheEntries[item.source] = res.html || '';
                        writeLocalCacheEntry(elementId, item.source, res.html || '');
                        if (res.html) {
                            var appended = appendChunkAsSingleTable(res.html, !baseInserted);
                            if (appended && !baseInserted) {
                                baseInserted = true;
                            }
                            if (appended) {
                                gotAny = true;
                                if (doneCount < SOURCES.length) {
                                    hideLoadingUi();
                                    if (muted && !muted.closest('.lvt-supplier-offers__loading')) {
                                        muted.style.display = '';
                                        muted.textContent = 'Подгружаем остальные предложения...';
                                    }
                                }
                            }
                        }
                    }
                    settleOnce();
                })
                .catch(function() {
                    if (attemptNo < (MAX_RETRIES + 1)) {
                        return attemptLoad(attemptNo + 1);
                    }
                    settleOnce();
                });
            }

            return attemptLoad(1);
        }

        SOURCES.forEach(runSourceTask);
    }

    global.LvtSupplierOffersLazy = {
        init: init,
        markCachedSectionIfReady: markCachedSectionIfReady,
        hasFullLocalCache: hasFullLocalCache,
        readHiddenCache: readHiddenCache
    };
})(window);
