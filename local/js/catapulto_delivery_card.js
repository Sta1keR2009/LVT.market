(function () {
    'use strict';

    var API_URL = '/local/api/catapulto_delivery_quote.php';
    var MAX_RETRIES = 3;
    var RETRY_DELAY_MS = 3000;

    var OPERATOR_NAMES = {
        'cdek': 'СДЭК',
        'cse': 'КСЭ',
        'dostavista': 'Dostavista',
        'yandex_dostavka': 'Яндекс Доставка',
        'dpd': 'DPD',
        'boxberry': 'Boxberry',
        'pochta': 'Почта России',
        'pek': 'ПЭК',
        'dl': 'Деловые Линии',
        'ems': 'EMS',
        'iml': 'IML',
        'hermes': 'Hermes'
    };

    var modalState = {
        productId: '',
        quantity: '1',
        container: null
    };

    function init() {
        ensureModalShell();
        bindCalculateDeliveryButton();
    }

    function ensureModalShell() {
        if (document.getElementById('lvt-delivery-modal')) return;

        var root = document.createElement('div');
        root.id = 'lvt-delivery-modal';
        root.className = 'lvt-delivery-modal';
        root.style.display = 'none';
        root.innerHTML =
            '<div class="lvt-delivery-modal__overlay"></div>' +
            '<div class="lvt-delivery-modal__dialog bordered rounded-4" role="dialog" aria-modal="true" aria-labelledby="lvt-delivery-modal-title">' +
            '<button type="button" class="lvt-delivery-modal__close" aria-label="Закрыть">&times;</button>' +
            '<div class="lvt-delivery-modal__head">' +
            '<h3 id="lvt-delivery-modal-title" class="lvt-delivery-modal__title font_18 color_222">Расчёт доставки</h3>' +
            '</div>' +
            '<div class="lvt-delivery-modal__body" id="lvt-delivery-modal-body"></div>' +
            '</div>';
        document.body.appendChild(root);

        root.querySelector('.lvt-delivery-modal__overlay').addEventListener('click', closeDeliveryModal);
        root.querySelector('.lvt-delivery-modal__close').addEventListener('click', closeDeliveryModal);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDeliveryModal();
        });
    }

    function bindCalculateDeliveryButton() {
        document.addEventListener('click', function (e) {
            var link = e.target && e.target.closest
                ? e.target.closest(
                    '[data-lvt-calculate-delivery="1"], .js-lvt-calculate-delivery, ' +
                    '[data-event="jqm"][data-param-form_id="delivery"], [data-event="jqm"][data-name="delivery"]'
                )
                : null;
            if (!link) return;

            e.preventDefault();
            e.stopPropagation();
            if (typeof e.stopImmediatePropagation === 'function') {
                e.stopImmediatePropagation();
            }

            link.removeAttribute('data-event');
            link.removeAttribute('data-param-form_id');
            link.removeAttribute('data-name');
            link.classList.remove('animate-load', 'clicked');

            var productId = link.getAttribute('data-param-product_id') || '';
            if (!productId) {
                var holder = document.getElementById('catapulto-delivery-block');
                if (holder) productId = holder.getAttribute('data-product-id') || '';
            }
            if (!productId || productId === '0') return;

            openDeliveryModal(productId, getQuantity());
        }, true);
    }

    function getQuantity() {
        var input = document.querySelector('.catalog-detail .counter__input, .catalog-detail .counter input[type="text"], .counter__input');
        if (input && String(input.value || '').trim() !== '') {
            return String(input.value).trim();
        }
        return '1';
    }

    function openDeliveryModal(productId, quantity) {
        ensureModalShell();
        var root = document.getElementById('lvt-delivery-modal');
        var body = document.getElementById('lvt-delivery-modal-body');
        if (!root || !body) return;

        modalState.productId = String(productId);
        modalState.quantity = String(quantity || '1');
        modalState.container = body;

        root.style.display = 'block';
        document.body.classList.add('lvt-delivery-modal-open');
        showLoader(body);
        fetchDelivery(body, modalState.productId, modalState.quantity, 0, '');
    }

    function closeDeliveryModal() {
        var root = document.getElementById('lvt-delivery-modal');
        if (!root) return;
        root.style.display = 'none';
        document.body.classList.remove('lvt-delivery-modal-open');
    }

    function showLoader(container) {
        container.innerHTML =
            '<div class="catapulto-delivery">' +
            '<div class="catapulto-delivery__loader">' +
            '<div class="catapulto-delivery__loader-bar"></div>' +
            '<div class="catapulto-delivery__loader-bar"></div>' +
            '<div class="catapulto-delivery__loader-bar"></div>' +
            '</div>' +
            '<p class="catapulto-delivery__loader-text">Рассчитываем стоимость доставки...</p>' +
            '</div>';
    }

    function showError(container, message) {
        container.innerHTML =
            '<div class="catapulto-delivery">' +
            '<p class="catapulto-delivery__loader-text">' + esc(message || 'Не удалось рассчитать доставку. Попробуйте позже.') + '</p>' +
            '</div>';
    }

    function fetchDelivery(container, productId, quantity, attempt, forcedCity) {
        var url = API_URL +
            '?product_id=' + encodeURIComponent(productId) +
            '&quantity=' + encodeURIComponent(quantity) +
            '&_dc=' + Date.now();
        if (forcedCity) {
            url += '&city=' + encodeURIComponent(forcedCity);
        }

        fetch(url, { cache: 'no-store', credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) {
                    showError(container, data.error || '');
                    return;
                }
                if ((!data.deliveries || data.deliveries.length === 0) && attempt < MAX_RETRIES) {
                    setTimeout(function () {
                        fetchDelivery(container, productId, quantity, attempt + 1, forcedCity);
                    }, RETRY_DELAY_MS);
                    return;
                }
                render(container, data, productId, quantity);
            })
            .catch(function () {
                showError(container, '');
            });
    }

    function render(container, data, productId, quantity) {
        var html = '<div class="catapulto-delivery">';

        html += '<div class="catapulto-delivery__headline">';
        html += '<h4 class="catapulto-delivery__title">Способы доставки в ' + esc(data.city) + '</h4>';
        html += '<a href="#" class="catapulto-delivery__change-city dark_link dotted">Изменить город</a>';
        html += '</div>';
        html += '<p class="catapulto-delivery__subtitle">Доставка <b>' + esc(data.productName) + '</b> в город <b>' + esc(data.city) + '</b></p>';

        if (data.pickup) {
            html += '<div class="catapulto-delivery__pickup">';
            html += '<span class="catapulto-delivery__pickup-name">' + esc(data.pickup.name);
            if (data.pickup.locations && data.pickup.locations.length) {
                html += ' из ' + esc(data.pickup.locations.join(' и '));
            }
            html += '</span>';
            html += '<span class="catapulto-delivery__pickup-price">' + esc(data.pickup.priceFormatted) + '</span>';
            html += '</div>';
        }

        var groups = groupByOperator(data.deliveries || []);
        var operatorKeys = Object.keys(groups);

        if (!operatorKeys.length) {
            html += '<p class="catapulto-delivery__loader-text">Для выбранного города тарифы не найдены.</p>';
        }

        for (var i = 0; i < operatorKeys.length; i++) {
            var opKey = operatorKeys[i];
            var tariffs = groups[opKey];
            var opName = tariffs[0].operatorName || OPERATOR_NAMES[opKey.toLowerCase()] || opKey.toUpperCase();

            html += '<div class="catapulto-delivery__operator">';
            html += '<div class="catapulto-delivery__operator-name">' + esc(opName) + '</div>';

            for (var j = 0; j < tariffs.length; j++) {
                var t = tariffs[j];
                html += '<div class="catapulto-delivery__tariff">';
                html += '<span class="catapulto-delivery__tariff-name">' + esc(t.rateName || '') + '</span>';
                html += '<span class="catapulto-delivery__tariff-date">' + esc(t.deliveryDay || t.periodText || '') + '</span>';
                html += '<span class="catapulto-delivery__tariff-price">' + esc(t.priceFormatted) + '</span>';
                html += '</div>';
            }
            html += '</div>';
        }

        html += '<div class="catapulto-delivery__footer">';
        if (data.disclaimer) {
            html += '<p class="catapulto-delivery__disclaimer">* ' + esc(data.disclaimer) + '</p>';
        }
        html += '</div>';
        html += '</div>';

        container.innerHTML = html;

        var changeLink = container.querySelector('.catapulto-delivery__change-city');
        if (changeLink) {
            changeLink.addEventListener('click', function (e) {
                e.preventDefault();
                if (typeof window.lvtOpenCityModal === 'function') {
                    window.lvtOpenCityModal(data.city || '', { title: 'Выберите ваш город' });
                } else if (typeof window.lvtApplyCityByName === 'function') {
                    var fallbackCity = window.prompt('Введите город доставки', data.city || '');
                    if (fallbackCity) {
                        window.lvtApplyCityByName(fallbackCity).then(function (ok) {
                            if (!ok) fetchDelivery(container, productId, quantity, 0, fallbackCity);
                        });
                    }
                }
            });
        }
    }

    function groupByOperator(deliveries) {
        var groups = {};
        for (var i = 0; i < deliveries.length; i++) {
            var d = deliveries[i];
            var key = d.operator || 'other';
            if (!groups[key]) groups[key] = [];
            groups[key].push(d);
        }
        return groups;
    }

    function esc(text) {
        if (!text) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(text));
        return d.innerHTML;
    }

    window.lvtOpenDeliveryModal = openDeliveryModal;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
