<?
file_put_contents("/tmp/epilog_called.txt", date("Y-m-d H:i:s") . " LOCAL EPILOG CALLED
", FILE_APPEND);
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
global $arTheme, $APPLICATION;

$arExtensions = ['fancybox', 'detail', 'swiper', 'swiper_events', 'rounded_columns', 'viewed', 'gallery', 'fancybox', 'stores_amount', 'video'];

if ($templateData['SHOW_DISCOUNT_COUNTER']) {
    $arExtensions[] = 'countdown';
}

if ($arParams['SHOW_RATING']) {
    $arExtensions[] = 'rating';
}

if ($templateData['POPUP_VIDEO']) {
    $arExtensions[] = 'video';
}

if ($templateData['SHOW_REVIEW']) {
    $arExtensions[] = 'reviews';
}

if ($templateData["USE_SHARE"]) {
    $arExtensions[] = 'share';
}

if($templateData['SHOW_CHARACTERISTICS']){
    $arExtensions[] = 'hint';
}

if ($templateData["BRAND"]) {
    $arExtensions[] = 'chip';
}
if($templateData["USE_OFFERS_SELECT"]){
    $arExtensions[] = 'select_offer';
    $arExtensions[] = 'select_offer_load';
}
// top banner
if ($templateData['SECTION_BNR_CONTENT']) {
    $GLOBALS['SECTION_BNR_CONTENT'] = true;
    $GLOBALS['bodyDopClass'] .= ' has-long-banner ' . ($templateData['SECTION_BNR_UNDER_HEADER'] === 'YES' ? 'header_opacity front_page' : '');
    if ($templateData['SECTION_BNR_COLOR'] !== 'dark') {
        $APPLICATION->SetPageProperty('HEADER_COLOR', 'light');
        $APPLICATION->SetPageProperty('HEADER_LOGO', 'light');
    }
    if ($templateData['SECTION_BNR_UNDER_HEADER'] === 'YES') {
        $arExtensions[] = 'header_opacity';
    }
    $arExtensions[] = 'banners';
}

define('ASPRO_PAGE_WO_TITLE', true); // remove h1 from page_title

// can order?
$bOrderViewBasket = $templateData["ORDER"];

// use tabs?
if ($arParams['USE_DETAIL_TABS'] === 'Y') {
    $bUseDetailTabs = true;
} elseif ($arParams['USE_DETAIL_TABS'] === 'N') {
    $bUseDetailTabs = false;
} else {
    $bUseDetailTabs = $arTheme['USE_DETAIL_TABS']['VALUE'] != 'N';
}

// blocks order
if (
    !$bUseDetailTabs &&
    array_key_exists('DETAIL_BLOCKS_ALL_ORDER', $arParams) &&
    $arParams["DETAIL_BLOCKS_ALL_ORDER"]
) {
    $arBlockOrder = explode(",", $arParams["DETAIL_BLOCKS_ALL_ORDER"]);
} else {
    $arBlockOrder = explode(",", $arParams["DETAIL_BLOCKS_ORDER"]);
    $arTabOrder = explode(",", $arParams["DETAIL_BLOCKS_TAB_ORDER"]);
}

if (!\Bitrix\Main\Loader::includeModule('blog') || !$templateData['SHOW_REVIEW']) {
    $arBlockOrder = array_diff($arBlockOrder, ['reviews']);

    if ($arTabOrder)
        $arTabOrder = array_diff($arTabOrder, ['reviews']);
}

\TSolution\Extensions::init($arExtensions);

// Include parent epilog for block rendering
include __DIR__ . '/../../../../../../../bitrix/templates/aspro-lite-render/components/bitrix/catalog.element/main/component_epilog.php';

// DEBUG: test parent include
$parentFile = __DIR__ . '/../../../../../../../bitrix/templates/aspro-lite-render/components/bitrix/catalog.element/main/component_epilog.php';
if (file_exists($parentFile)) {
    include $parentFile;
} else {
    file_put_contents('/tmp/epilog_debug.txt', 'PARENT NOT FOUND: ' . $parentFile);
}

<script>
(function () {
    function collectCharItems(root) {
        var selectors = [
            '.props_block .char',
            '.properties.list .properties__item',
            '.properties.table .properties__item',
            '.properties .properties__item',
            '.properties-group__item'
        ];
        var seen = new Set();
        var out = [];
        selectors.forEach(function (selector) {
            root.querySelectorAll(selector).forEach(function (el) {
                if (seen.has(el)) return;
                seen.add(el);
                out.push(el);
            });
        });
        return out;
    }

    function initCharCollapse(scope) {
        var wraps = (scope || document).querySelectorAll('.js-lvt-char-collapse');
        if (wraps.length === 0) {
            // Если нет обёртки — создаём вокруг первого блока характеристик
            var charBlock = document.querySelector('.detail-block.char, .ordered-block.char');
            var h3 = charBlock ? charBlock.querySelector('h3.switcher-title') : null;
            var content = charBlock ? charBlock.querySelector('.props_block') : null;
            if (h3 && content) {
                var wrap = document.createElement('div');
                wrap.className = 'js-lvt-char-collapse';
                wrap.setAttribute('data-limit', '10');
                h3.insertAdjacentElement('afterend', wrap);
                wrap.appendChild(content);
                wraps = [wrap];
            }
        }
        [].forEach.call(wraps, function (wrap) {
            if (wrap.dataset.lvtCharCollapseInit === 'Y') return;
            var limit = parseInt(wrap.dataset.limit || '10', 10);
            if (isNaN(limit) || limit < 1) limit = 10;
            var items = collectCharItems(wrap);
            var btn = wrap.querySelector('.js-lvt-char-show-all');
            if (!btn || items.length <= limit) {
                if (btn) btn.classList.add('hidden');
                wrap.dataset.lvtCharCollapseInit = 'Y';
                return;
            }
            items.forEach(function (item, idx) {
                if (idx >= limit) {
                    item.classList.add('hidden');
                    item.setAttribute('data-lvt-char-hidden', 'Y');
                }
            });
            btn.classList.remove('hidden');
            btn.addEventListener('click', function () {
                wrap.querySelectorAll('[data-lvt-char-hidden=Y]').forEach(function (item) {
                    item.classList.remove('hidden');
                    item.removeAttribute('data-lvt-char-hidden');
                });
                btn.classList.add('hidden');
            });
            wrap.dataset.lvtCharCollapseInit = 'Y';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initCharCollapse(document); });
    } else {
        initCharCollapse(document);
    }
})();
</script>
<script>
(function () {
    function initCharCollapse() {
        var allH3 = document.querySelectorAll('h3');
        for (var i = 0; i < allH3.length; i++) {
            if (allH3[i].textContent.trim() !== 'Характеристики') continue;
            var container = allH3[i].parentElement;
            if (!container) continue;
            if (container.querySelector('.js-lvt-char-show-all')) continue; // уже инициализирован

            var items = container.querySelectorAll('.properties-group__item, .props_block .char, .properties__item');
            if (items.length <= 10) continue;

            var limit = 10;
            for (var j = limit; j < items.length; j++) {
                items[j].classList.add('hidden');
                items[j].setAttribute('data-lvt-char-hidden', 'Y');
            }

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'catalog-detail__pseudo-link pointer dark_link font_13 js-lvt-char-show-all';
            btn.innerHTML = '<span class="choise dotted">Показать все</span>';
            btn.addEventListener('click', function () {
                var parent = this.parentElement;
                parent.querySelectorAll('[data-lvt-char-hidden="Y"]').forEach(function (item) {
                    item.classList.remove('hidden');
                    item.removeAttribute('data-lvt-char-hidden');
                });
                this.classList.add('hidden');
            });
            container.appendChild(btn);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharCollapse);
    } else {
        initCharCollapse();
    }
})();
</script>
