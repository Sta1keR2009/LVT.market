<?
use \Bitrix\Main\Localization\Loc;

if (defined('LVT_CATALOG_ELEMENT_EPILOG_DONE')) {
    return;
}
define('LVT_CATALOG_ELEMENT_EPILOG_DONE', true);

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

if ($templateData['SHOW_CHARACTERISTICS']) {
    $arExtensions[] = 'hint';
}

if ($templateData["BRAND"]) {
    $arExtensions[] = 'chip';
}
if ($templateData["USE_OFFERS_SELECT"]) {
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

    if ($arTabOrder) {
        $arTabOrder = array_diff($arTabOrder, ['reviews']);
    }
}

if (!in_array('catapulto_delivery', $arBlockOrder, true)) {
    $paymentPos = array_search('payment', $arBlockOrder, true);
    if ($paymentPos !== false) {
        array_splice($arBlockOrder, $paymentPos, 0, ['catapulto_delivery']);
    } else {
        $arBlockOrder[] = 'catapulto_delivery';
    }
}

// Наличие/цены внизу карточки: выбор рендера по расположению каталога
$lvtRequestPath = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$lvtIsPromelecCatalogPath = (strpos($lvtRequestPath, '/catalog/') === 0);
$lvtIsEtmCatalogPath = (strpos($lvtRequestPath, '/katalog/') === 0);

if ($lvtIsPromelecCatalogPath) {
    $GLOBALS['bodyDopClass'] = ($GLOBALS['bodyDopClass'] ?? '') . ' lvt-detail--catalog-path';
    // /catalog/ -> Promelec/Getchips рендер
    $arBlockOrder = array_values(array_diff($arBlockOrder, ['etm_variants']));
    if (!in_array('getchips_offers', $arBlockOrder, true)) {
        $descPosGetchips = array_search('desc', $arBlockOrder, true);
        if ($descPosGetchips !== false) {
            array_splice($arBlockOrder, $descPosGetchips, 0, ['getchips_offers']);
        } else {
            $arBlockOrder[] = 'getchips_offers';
        }
    }
    $arBlockOrder = array_values(array_diff($arBlockOrder, ['lvt_specification']));
} elseif ($lvtIsEtmCatalogPath) {
    $GLOBALS['bodyDopClass'] = ($GLOBALS['bodyDopClass'] ?? '') . ' lvt-detail--katalog-path';
    // /katalog/ -> ETM рендер (склады внизу карточки, варианты — перед описанием)
    $arBlockOrder = array_values(array_diff($arBlockOrder, ['getchips_offers']));
    if (!in_array('etm_store_offers', $arBlockOrder, true)) {
        $descPosStores = array_search('desc', $arBlockOrder, true);
        if ($descPosStores !== false) {
            array_splice($arBlockOrder, $descPosStores , 0, ['etm_store_offers']);
        } else {
            $arBlockOrder[] = 'etm_store_offers';
        }
    }
}

\TSolution\Extensions::init($arExtensions);
?>

<?
// custom blocks
$customBlocks = new TSolution\Product\Blocks(blockParams: $arParams['~CUSTOM_DETAIL_BLOCKS']);
$customBlocks->resolveHtml(params: $arParams, result: $arResult, templateData: $templateData);
?>

<div class="catalog-detail__bottom-info">
    <div class="grid-list grid-list--items-1 grid-list--gap-48">
        <?php
        $arEpilogBlocks = new TSolution\Template\Epilog\Blocks([
            'BEFORE_ORDERED' => ['sale', 'tizers'],
            'ORDERED' => $arBlockOrder,
            'TABS' => $arTabOrder ?? [],
        ], templatePath: __DIR__, customBlocks: $customBlocks);

        foreach ($arEpilogBlocks->beforeOrdered as $path) {
            include $path;
        }

        foreach ($arEpilogBlocks->ordered as $code => $path) {
            include $path;
        }
        ?>
    </div>
</div>
<div class="hidden">
    <?$APPLICATION->ShowViewContent('PRODUCT_SIDE_INFO')?>
</div>
<script type="text/javascript">
    var viewedCounter = {
        path: '/bitrix/components/bitrix/catalog.element/ajax.php',
        params: {
            AJAX: 'Y',
            SITE_ID: '<?= SITE_ID ?>',
            PRODUCT_ID: '<?= $arResult['ID'] ?>',
            PARENT_ID: '<?= $arResult['ID'] ?>',
        }
    };
    BX.ready(
        BX.defer(function() {
            BX.ajax.post(
                viewedCounter.path,
                viewedCounter.params
            );
        })
    );

    viewItemCounter('<?= $arResult['ID'] ?>', '<?= current($arParams['PRICE_CODE']) ?>');
</script>

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
                wrap.querySelectorAll('[data-lvt-char-hidden="Y"]').forEach(function (item) {
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
