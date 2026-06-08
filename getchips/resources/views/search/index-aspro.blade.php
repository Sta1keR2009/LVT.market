@php
    $aspro = (string) config('services.getchips.aspro_assets_base', 'https://lvt.market/bitrix/templates/aspro-lite');
    $site = rtrim((string) config('services.lvt_market.base_url', 'https://lvt.market'), '/');
    $asproHost = parse_url($aspro, PHP_URL_HOST);
    $asproScheme = parse_url($aspro, PHP_URL_SCHEME) ?: 'https';
    $gcCur = ($displayCurrency ?? 'rub') === 'usd' ? 'usd' : 'rub';
    $gcCurCode = $gcCur === 'usd' ? 'USD' : 'RUB';
    $gcCurSign = $gcCur === 'usd' ? '$' : '₽';
    $gcCurLabel = $gcCur === 'usd' ? 'USD' : '₽';

@endphp
<!DOCTYPE html>
<html lang="ru" class="bx_editmode ">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Поиск по сайту — LVT Market</title>
    @if ($asproHost)
        <link rel="preconnect" href="{{ $asproScheme }}://{{ $asproHost }}">
    @endif
    <link rel="stylesheet" href="{{ $aspro }}/css/lite.bootstrap.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/template_styles.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/header.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/theme-elements.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/buttons.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/form.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/search-page.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/page-title-breadcrumb-pagination.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/catalog.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/item-views.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/responsive.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/blocks/flexbox.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/blocks/line-block.min.css">
    <link rel="stylesheet" href="{{ $aspro }}/css/blocks/grid-list.min.css">
    <style>
        .gc-v2-toolbar-form { margin: 0; padding: 0; border: 0; background: transparent; }
        .gc-v2-toolbar {
            margin-top: 16px;
            padding: 16px 18px;
            background: var(--card_bg_black, #fff);
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px;
        }
        .gc-v2-toolbar__part { flex: 1 1 220px; min-width: 180px; }
        .gc-v2-toolbar__qty { flex: 0 0 140px; width: 140px; max-width: 100%; }
        .gc-v2-toolbar__actions { display: flex; gap: 8px; flex-shrink: 0; }
        .gc-v2-toolbar__search {
            width: 48px; height: 42px; padding: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 4px;
        }
        .gc-v2-toolbar__search svg { display: block; }
        .gc-v2-toolbar__view {
            width: 48px; height: 42px; padding: 0;
            border: 1px solid var(--stroke_black, #ddd);
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
        }
        .gc-v2-toolbar__view.is-active { background: var(--darkerblack_bg_black, #f0f0f0); }
        .getchips-search-v2 {
            margin-top: 14px;
            padding: 0 0 16px;
            background: transparent;
        }
        .gc-filters-row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px 16px;
            margin-top: 4px;
        }
        .gc-dd {
            position: relative;
            min-width: 160px;
            flex: 1 1 160px;
            max-width: 260px;
        }
        .gc-dd > summary {
            list-style: none;
            cursor: pointer;
            border: 1px solid var(--stroke_black, #ddd);
            border-radius: 4px;
            padding: 10px 12px;
            background: #fff;
        }
        .gc-dd > summary::-webkit-details-marker { display: none; }
        .gc-dd__label { display: block; font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.02em; margin-bottom: 2px; }
        .gc-dd__value { font-size: 14px; color: #222; }
        .gc-dd__body {
            position: absolute;
            z-index: 20;
            left: 0;
            right: 0;
            top: calc(100% + 4px);
            max-height: 240px;
            overflow-y: auto;
            padding: 10px;
            background: #fff;
            border: 1px solid var(--stroke_black, #ddd);
            border-radius: 4px;
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
        }
        .gc-dd__body label {
            display: flex;
            gap: 8px;
            align-items: flex-start;
            font-size: 13px;
            margin-bottom: 8px;
            cursor: pointer;
        }
        .gc-dd__body input { margin-top: 3px; flex-shrink: 0; }
        .gc-filters-row__more { min-width: 200px; flex: 1 1 200px; max-width: 280px; }
        .gc-filters-row__more .gc-dd__body { max-height: 320px; }
        .gc-clear-filters {
            width: 44px; height: 44px;
            border: 1px solid var(--stroke_black, #ddd);
            border-radius: 4px;
            background: #fff;
            cursor: pointer;
            flex-shrink: 0;
            font-size: 18px;
            line-height: 1;
            color: #666;
        }
        .gc-clear-filters:hover { background: #fafafa; }
        .gc-per-page { margin-left: auto; display: flex; align-items: flex-end; gap: 8px; }
        .gc-per-page label { font-size: 12px; color: #888; margin: 0; white-space: nowrap; }
        .gc-per-page select { min-width: 72px; }
        .gc-results-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px 20px;
            margin: 20px 0 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--stroke_black, #e5e5e5);
        }
        .gc-results-head__title { font-size: 20px; font-weight: 600; margin: 0; }
        .gc-results-head__tools { display: flex; gap: 8px; }
        .gc-icon-btn {
            width: 40px; height: 40px;
            border: 1px solid var(--stroke_black, #ddd);
            border-radius: 4px;
            background: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            color: #555;
        }
        .gc-icon-btn:hover { background: #f8f8f8; }
        .getchips-search-v2__table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .getchips-results-root--loading {
            opacity: 0.55;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .getchips-search-v2__table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
            font-size: 13px;
        }
        .getchips-search-v2__table thead th {
            padding: 12px 14px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid var(--stroke_black, #e5e5e5);
            background: var(--darkerblack_bg_black, #f8f8f8);
            vertical-align: bottom;
        }
        .getchips-search-v2__table tbody td {
            padding: 14px 14px;
            vertical-align: top;
            border-bottom: 1px solid var(--stroke_black, #e8e8e8);
        }
        .getchips-search-v2__table tbody tr:hover { background: var(--lighter_bg_black, rgba(0,0,0,.02)); }
        .gc-results--compact .getchips-search-v2__table { font-size: 12px; }
        .gc-results--compact .getchips-search-v2__table tbody td { padding: 8px 10px; }
        .gc-name__pn { font-weight: 700; font-size: 14px; color: #111; }
        .gc-name__sub { font-size: 12px; color: #888; margin-top: 4px; line-height: 1.35; }
        .gc-th-sort { display: flex; align-items: flex-end; justify-content: space-between; gap: 8px; }
        .gc-th-sort__btns { display: flex; flex-direction: column; gap: 0; }
        .gc-th-sort__btn {
            border: none; background: none; padding: 0 2px; cursor: pointer;
            font-size: 11px; color: #999; line-height: 1;
        }
        .gc-th-sort__btn:hover { color: #333; }
        .gc-th-price-switch { position: relative; display: inline-flex; align-items: center; }
        .gc-th-price-switch__trigger {
            border: 0;
            background: transparent;
            padding: 0;
            color: inherit;
            font: inherit;
            line-height: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .gc-th-price-switch__badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 0 6px;
            border-radius: 4px;
            background: #ececec;
            font-size: 12px;
            line-height: 1.5;
            font-weight: 600;
            white-space: nowrap;
        }
        .gc-th-price-switch__caret {
            width: 0;
            height: 0;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 6px solid #8a8a8a;
            margin-left: 1px;
        }
        .gc-th-price-switch__menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 112px;
            z-index: 6;
            border: 1px solid #d8d8d8;
            border-radius: 6px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            padding: 4px;
        }
        .gc-th-price-switch__menu::before {
            content: "";
            position: absolute;
            top: -6px;
            left: 18px;
            width: 10px;
            height: 10px;
            background: #fff;
            border-left: 1px solid #d8d8d8;
            border-top: 1px solid #d8d8d8;
            transform: rotate(45deg);
        }
        .gc-th-price-switch:hover .gc-th-price-switch__menu,
        .gc-th-price-switch:focus-within .gc-th-price-switch__menu { display: block; }
        .gc-th-price-switch__opt {
            width: 100%;
            border: 0;
            background: transparent;
            padding: 6px 8px;
            text-align: left;
            cursor: pointer;
            border-radius: 4px;
            font-size: 13px;
            line-height: 1.3;
            white-space: nowrap;
        }
        .gc-th-price-switch__opt:hover { background: #f4f4f4; }
        .gc-th-price-switch__opt.is-active {
            background: #eef8f1;
            color: #2b8f42;
            font-weight: 600;
        }
        .getchips-price-tiers { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
        .getchips-price-tiers__row {
            display: flex; justify-content: space-between; align-items: baseline;
            gap: 10px; font-size: 12px; padding: 2px 0;
            border-bottom: 1px solid #eee;
        }
        .getchips-price-tiers__row:last-child { border-bottom: none; }
        .getchips-price-tiers__row--first { font-weight: 700; font-size: 13px; }
        .getchips-price-tiers__price { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .getchips-price-tiers__qty { color: #666; font-variant-numeric: tabular-nums; }
        .gc-row-qty-wrap input.gc-row-qty { max-width: 100px; }
        .gc-row-meta { font-size: 11px; color: #888; margin-top: 6px; line-height: 1.4; }
        .gc-row-sum { font-weight: 700; font-variant-numeric: tabular-nums; font-size: 14px; }
        .gc-cart-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 44px; height: 44px;
            border-radius: 4px;
            background: #2fa84e;
            color: #fff;
            border: none;
            text-decoration: none;
            cursor: pointer;
        }
        .gc-cart-btn:hover { background: #259544; color: #fff; }
        .gc-cart-btn:disabled { opacity: 0.45; cursor: not-allowed; }
        .getchips-v2-note { font-size: 13px; color: #666; margin: 10px 0 0; }
        .getchips-v2-note a { text-decoration: underline; }
        .getchips-timings { font-size: 12px; color: #888; }
        .getchips-pricing-note { margin-top: 14px; font-size: 12px; color: #888; }
        /* --- Product card (lvt.market) --- */
        .gc-product-card {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            padding: 20px 24px;
            background: var(--card_bg_black, #fff);
            border-radius: 6px;
        }
        .gc-product-card__gallery {
            display: flex;
            flex-direction: row;
            gap: 10px;
            flex-shrink: 0;
            align-items: flex-start;
        }
        .gc-product-card__thumbs {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .gc-product-card__thumb {
            width: 64px;
            height: 64px;
            border: 1px solid var(--stroke_black, #e0e0e0);
            border-radius: 4px;
            object-fit: contain;
            cursor: pointer;
            background: #fafafa;
            padding: 2px;
        }
        .gc-product-card__thumb.is-active { border-color: var(--color_theme, #2fa84e); }
        .gc-product-card__main-img-wrap {
            width: 320px;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--stroke_black, #e0e0e0);
            border-radius: 6px;
            overflow: hidden;
            background: #fafafa;
        }
        .gc-product-card__main-img {
            max-width: 100%;
            max-height: 280px;
            object-fit: contain;
        }
        .gc-product-card__main-img--placeholder {
            width: 240px;
            height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #bbb;
            font-size: 13px;
            text-align: center;
            gap: 10px;
        }
        .gc-product-card__info { flex: 1 1 300px; min-width: 260px; }
        .gc-product-card__title { font-size: 22px; font-weight: 700; color: #111; margin: 0 0 12px; }
        .gc-product-card__badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 14px;
        }
        .gc-product-card__badge {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            padding: 3px 8px;
            border-radius: 3px;
            color: #fff;
            text-transform: uppercase;
        }
        .gc-badge--hit { background: #e74c3c; }
        .gc-badge--recommend { background: #f39c12; }
        .gc-badge--new { background: #2fa84e; }
        .gc-badge--sale { background: #8e44ad; }
        .gc-badge--custom { background: #555; }
        .gc-product-card__brand-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--stroke_black, #eee);
        }
        .gc-product-card__brand-logo { max-height: 40px; max-width: 120px; }
        .gc-product-card__brand-links {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
        }
        .gc-product-card__brand-links a {
            font-size: 13px;
            color: var(--color_theme, #2fa84e);
            text-decoration: none;
        }
        .gc-product-card__brand-links a:hover { text-decoration: underline; }
        .gc-product-card__section-title {
            font-size: 17px;
            font-weight: 700;
            margin: 0 0 12px;
        }
        .gc-product-card__specs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .gc-product-card__specs-table tr:not(:last-child) td { border-bottom: 1px solid var(--stroke_black, #f0f0f0); }
        .gc-product-card__specs-table td { padding: 7px 0; vertical-align: top; }
        .gc-product-card__specs-table td:first-child { color: #888; width: 48%; padding-right: 12px; }
        .gc-product-card__specs-table td:last-child { font-weight: 500; color: #111; }
        .gc-product-card__link-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            padding: 10px 20px;
            border-radius: 4px;
            background: var(--card_bg_black, #fff);
            border: 1px solid var(--stroke_black, #ddd);
            color: #333;
            font-size: 14px;
            text-decoration: none;
            transition: background 0.15s;
        }
        .gc-product-card__link-btn:hover { background: #f5f5f5; color: #111; text-decoration: none; }
        .gc-row--lvt { background: var(--lighter_bg_black, rgba(47,168,78,.04)) !important; }
        .gc-row--lvt td:first-child::before {
            content: "lvt";
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            background: #2fa84e;
            color: #fff;
            border-radius: 3px;
            padding: 1px 5px;
            margin-right: 6px;
            vertical-align: middle;
            line-height: 1.6;
        }
        @media (max-width: 767px) {
            .gc-filters-row .gc-per-page { margin-left: 0; width: 100%; }
            .gc-dd { max-width: none; }
            .gc-product-card { flex-direction: column; padding: 14px; }
            .gc-product-card__main-img-wrap { width: 100%; }
            .gc-product-card__gallery { flex-direction: column-reverse; }
            .gc-product-card__thumbs { flex-direction: row; flex-wrap: wrap; }
        }
        @media (max-width: 991px) {
            .gc-product-card__main-img-wrap { width: 240px; }
        }
    </style>
</head>
<body class="site_1" id="main" data-site="/">
    <div class="body index">
        @include('search._aspro_header_getchips', ['site' => $site, 'aspro' => $aspro, 'componentNum' => $componentNum, 'showHeaderSearch' => false])
        <div class="main banners-auto">
            <div class="page-top-info">
                <div class="page-top-wrapper page-top-wrapper--white">
                    <section class="page-top maxwidth-theme">
                        <div class="cowl">
                            <div id="navigation">
                                <div class="breadcrumbs swipeignore" itemscope itemtype="http://schema.org/BreadcrumbList">
                                    <div class="breadcrumbs__item font_13">
                                        <a class="breadcrumbs__link" href="{{ $site }}/" title="Главная">
                                            <span class="breadcrumbs__item-name">Главная</span>
                                        </a>
                                    </div>
                                    <span class="breadcrumbs__separator relative">&ndash;</span>
                                    <span class="breadcrumbs__item link-opacity-color link-opacity-color--secondary-color category-separator-sibling category-separator-sibling--inline font_12 cat_last">
                                        <span><span class="breadcrumbs__item-name font_13">Поиск компонентов</span></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="topic">
                            <div class="topic__inner">
                                <div class="topic__heading">
                                    <h1 id="pagetitle" class="switcher-title">Поиск по сайту</h1>
                                    <p class="font_15 color_666">Поиск товаров в каталоге <strong>lvtec.ru</strong> с автоматическим fallback в <strong>Mouser</strong>.</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="container">
                <div class="row">
                    <div class="maxwidth-theme wide_N">
                        <div class="col-md-12 col-sm-12 col-xs-12 content-md">
                            <div class="right_block narrow_Y">

                                <form id="gc-toolbar-search-form" class="gc-v2-toolbar-form" method="GET" action="{{ route('search.index.v2') }}" accept-charset="UTF-8">
                                    <input type="hidden" name="sort" id="gc-sort-input" value="{{ $sort }}">
                                    <div class="gc-v2-toolbar bordered outer-rounded-x">
                                        <div class="gc-v2-toolbar__part">
                                            <label for="gc-v2-part" class="font_13 color_999" style="display:block;margin-bottom:6px;">Поиск по сайту</label>
                                            <input id="gc-v2-part" class="form-control" type="text" name="componentNum"
                                                   value="{{ $componentNum }}" placeholder="Например RCT032KFLF или название товара" maxlength="255" autocomplete="off" required>
                                        </div>
                                        <div class="gc-v2-toolbar__qty">
                                            <label for="gc-v2-amount" class="font_13 color_999" style="display:block;margin-bottom:6px;">Количество</label>
                                            <input id="gc-v2-amount" class="form-control" type="number" name="amount" min="1" value="{{ $amount }}" placeholder="Количество">
                                        </div>
                                        <div class="gc-v2-toolbar__actions">
                                            <button type="submit" class="btn btn-success gc-v2-toolbar__search" id="search-button-v2" title="Найти" aria-label="Найти">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
                                            </button>
                                            <button type="button" class="gc-v2-toolbar__view" id="gc-view-toggle" title="Компактный вид таблицы" aria-pressed="false">▤</button>
                                        </div>
                                    </div>
                                </form>

                                <p class="getchips-v2-note color_666">
                                    Используется только поиск по сайту. Поиск по парт-номеру через Getchips отключен.
                                </p>

                                <div id="search-form-v2">
                                <div class="getchips-search-v2">
                                    @if ($componentNum !== '' && ($totalUnfiltered ?? 0) > 0)
                                        <div class="gc-filters-row">
                                            @if (count($filterPartNumbersAll ?? []) > 1)
                                                <details class="gc-dd js-gc-dd">
                                                    <summary>
                                                        <span class="gc-dd__label">Наименование</span>
                                                        <span class="gc-dd__value" data-gc-dd="pn">Любой</span>
                                                    </summary>
                                                    <div class="gc-dd__body">
                                                        @foreach ($filterPartNumbersAll as $pn)
                                                            <label>
                                                                <input type="checkbox" name="part_numbers[]" value="{{ $pn }}" class="js-getchips-filter-auto"
                                                                       @checked(in_array($pn, $filterPartNumbers ?? [], true))>
                                                                <span>{{ $pn }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @endif

                                            @if (!empty($filterBrandsAll))
                                                <details class="gc-dd js-gc-dd">
                                                    <summary>
                                                        <span class="gc-dd__label">Бренд</span>
                                                        <span class="gc-dd__value" data-gc-dd="br">Любой</span>
                                                    </summary>
                                                    <div class="gc-dd__body">
                                                        @foreach ($filterBrandsAll as $br)
                                                            <label>
                                                                <input type="checkbox" name="brands[]" value="{{ $br }}" class="js-getchips-filter-auto"
                                                                       @checked(in_array($br, $filterBrands ?? [], true))>
                                                                <span>{{ $br }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @endif

                                            @if (!empty($filterSuppliersAll))
                                                <details class="gc-dd js-gc-dd">
                                                    <summary>
                                                        <span class="gc-dd__label">Поставщик</span>
                                                        <span class="gc-dd__value" data-gc-dd="sup">Любой</span>
                                                    </summary>
                                                    <div class="gc-dd__body">
                                                        @foreach ($filterSuppliersAll as $sup)
                                                            <label>
                                                                <input type="checkbox" name="suppliers[]" value="{{ $sup }}" class="js-getchips-filter-auto"
                                                                       @checked(in_array($sup, $filterSuppliers ?? [], true))>
                                                                <span>{{ $sup }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @endif

                                            <details class="gc-dd gc-filters-row__more js-gc-dd">
                                                <summary>
                                                    <span class="gc-dd__label">Параметры</span>
                                                    <span class="gc-dd__value">Валюта и цена</span>
                                                </summary>
                                                <div class="gc-dd__body">
                                                    <div class="font_12 color_999" style="margin-bottom:8px;">Валюта таблицы</div>
                                                    <label class="d-block mb-2" style="cursor:pointer;">
                                                        <input type="radio" name="display_currency" value="rub" class="js-getchips-filter-auto" @checked($gcCur === 'rub')>
                                                        <span class="font_13"> Рубли (₽)</span>
                                                    </label>
                                                    <label class="d-block mb-3" style="cursor:pointer;">
                                                        <input type="radio" name="display_currency" value="usd" class="js-getchips-filter-auto" @checked($gcCur === 'usd')>
                                                        <span class="font_13"> Доллары (USD)</span>
                                                    </label>
                                                    <label for="price_min-v2" class="font_12 color_999">Цена от ({{ $gcCurLabel }})</label>
                                                    <input id="price_min-v2" class="form-control js-getchips-filter-debounce mb-2" type="number" name="price_min" min="0" step="0.0001"
                                                           value="{{ old('price_min', $priceMin ?? '') }}" placeholder="—">
                                                    <label for="price_max-v2" class="font_12 color_999">Цена до</label>
                                                    <input id="price_max-v2" class="form-control js-getchips-filter-debounce" type="number" name="price_max" min="0" step="0.0001"
                                                           value="{{ old('price_max', $priceMax ?? '') }}" placeholder="—">
                                                </div>
                                            </details>

                                            <button type="button" class="gc-clear-filters" id="gc-clear-filters" title="Сбросить фильтры">⌀</button>

                                            <div class="gc-per-page">
                                                <label for="per_page-v2">На стр.</label>
                                                <select id="per_page-v2" class="form-control js-getchips-filter-auto" name="per_page">
                                                    @foreach ([10, 25, 50, 100] as $size)
                                                        <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <p class="getchips-pricing-note">
                                            Наценка <strong>{{ number_format((float) ($markupPercent ?? 0), 2, '.', ' ') }}%</strong> (Mouser / Getchips)
                                            · курс USD <strong>{{ number_format((float) ($usdToRub ?? 1), 2, '.', ' ') }} ₽</strong>
                                        </p>
                                    @endif
                                </div>
                                </div>

                                @if (!empty($provider_errors))
                                    <div class="alert alert-warning" style="margin-top: 16px;">
                                        <strong>Часть источников недоступна:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($provider_errors as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @error('price_max')
                                    <div class="alert alert-danger" style="margin-top: 12px;">{{ $message }}</div>
                                @enderror

                                @if ($componentNum !== '' && !empty($lvtMarketCard))
                                @php
                                    $lvtRaw    = $lvtMarketCard['raw'] ?? [];
                                    $lvtTitle  = $lvtRaw['productTitle'] ?? $lvtMarketCard['part_number'] ?? $componentNum;
                                    $lvtImages = !empty($lvtRaw['imageUrls']) ? $lvtRaw['imageUrls'] : (!empty($lvtRaw['imageUrl']) ? [$lvtRaw['imageUrl']] : []);
                                    $lvtMainImg = $lvtImages[0] ?? null;
                                    $lvtSpecs  = $lvtRaw['specs'] ?? [];
                                    $lvtBadges = $lvtRaw['badges'] ?? [];
                                    $lvtBrandName = $lvtMarketCard['brand'] ?? $lvtRaw['brandName'] ?? null;
                                    $lvtBrandLogo = $lvtRaw['brandLogoUrl'] ?? null;
                                    $lvtBrandUrl  = $lvtRaw['brandCatalogUrl'] ?? null;
                                    $lvtCatUrl    = $lvtRaw['categoryCatalogUrl'] ?? null;
                                    $lvtPageUrl   = $lvtRaw['url'] ?? $lvtMarketCard['url'] ?? null;
                                    $badgeClasses = ['ХИТ' => 'gc-badge--hit', 'СОВЕТУЕМ' => 'gc-badge--recommend', 'НОВИНКА' => 'gc-badge--new', 'АКЦИЯ' => 'gc-badge--sale'];
                                @endphp
                                <div class="gc-product-card bordered outer-rounded-x">
                                    <div class="gc-product-card__gallery">
                                        @if (count($lvtImages) > 1)
                                            <div class="gc-product-card__thumbs">
                                                @foreach ($lvtImages as $i => $imgUrl)
                                                    <img src="{{ $imgUrl }}" class="gc-product-card__thumb {{ $i === 0 ? 'is-active' : '' }}"
                                                         alt="{{ $lvtTitle }}" loading="lazy"
                                                         onclick="gcSwitchImg(this,'{{ $imgUrl }}')">
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="gc-product-card__main-img-wrap" id="gc-main-img-wrap">
                                            @if ($lvtMainImg)
                                                <img src="{{ $lvtMainImg }}" class="gc-product-card__main-img" id="gc-main-img" alt="{{ $lvtTitle }}" loading="eager">
                                            @else
                                                <div class="gc-product-card__main-img--placeholder">
                                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                                    <span>Фото отсутствует</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="gc-product-card__info">
                                        <div class="gc-product-card__title">{{ $lvtTitle }}</div>

                                        @if (!empty($lvtBadges))
                                            <div class="gc-product-card__badges">
                                                @foreach ($lvtBadges as $badge)
                                                    @php $badgeUp = strtoupper(trim($badge)); $cls = $badgeClasses[$badgeUp] ?? 'gc-badge--custom'; @endphp
                                                    <span class="gc-product-card__badge {{ $cls }}">{{ $badge }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($lvtBrandName || $lvtBrandUrl || $lvtCatUrl)
                                            <div class="gc-product-card__brand-row">
                                                @if ($lvtBrandLogo)
                                                    <img src="{{ $lvtBrandLogo }}" class="gc-product-card__brand-logo" alt="{{ $lvtBrandName ?? '' }}" loading="lazy">
                                                @elseif ($lvtBrandName)
                                                    <strong class="font_15">{{ $lvtBrandName }}</strong>
                                                @else
                                                    <span></span>
                                                @endif
                                                <div class="gc-product-card__brand-links">
                                                    @if ($lvtBrandUrl)
                                                        <a href="{{ $lvtBrandUrl }}" target="_blank" rel="noopener">Все товары {{ $lvtBrandName }}</a>
                                                    @endif
                                                    @if ($lvtCatUrl)
                                                        <a href="{{ $lvtCatUrl }}" target="_blank" rel="noopener">Все товары категории</a>
                                                    @endif
                                                    @if (!$lvtBrandUrl && !$lvtCatUrl && $lvtPageUrl)
                                                        <a href="{{ $lvtPageUrl }}" target="_blank" rel="noopener">Перейти в каталог</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if (!empty($lvtSpecs))
                                            <div class="gc-product-card__section-title">Характеристики</div>
                                            <table class="gc-product-card__specs-table">
                                                <tbody>
                                                    @foreach ($lvtSpecs as $specLabel => $specValue)
                                                        <tr>
                                                            <td>{{ $specLabel }}</td>
                                                            <td>{{ $specValue }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif

                                        @if ($lvtPageUrl)
                                            <a href="{{ $lvtPageUrl }}" class="gc-product-card__link-btn" target="_blank" rel="noopener">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                                Перейти в каталог lvt.market
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                <div id="getchips-results-root">
                                @if ($componentNum !== '')
                                    @if ($offers->total() > 0)
                                        <div class="gc-results-head">
                                            <h2 class="gc-results-head__title">Результаты</h2>
                                            <div class="gc-results-head__tools">
                                                <button type="button" class="gc-icon-btn" id="gc-share-btn" title="Поделиться ссылкой" aria-label="Поделиться">↗</button>
                                                <button type="button" class="gc-icon-btn" id="gc-export-btn" title="Экспорт CSV" aria-label="Экспорт">⭳</button>
                                            </div>
                                            <div class="font_13 color_666" style="margin-left:auto;">
                                                Найдено: <strong>{{ $offers->total() }}</strong>
                                                @if (isset($totalUnfiltered, $totalFiltered) && $totalUnfiltered > $totalFiltered)
                                                    <span class="color_999">из {{ $totalUnfiltered }}</span>
                                                @endif
                                                @if (!empty($timingsMs))
                                                    <span class="getchips-timings" style="display:block;margin-top:4px;">
                                                        @foreach ($timingsMs as $provider => $timing)
                                                            {{ $provider }}: {{ $timing }} ms{{ $loop->last ? '' : ', ' }}
                                                        @endforeach
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if ($offers->total() === 0)
                                        <div class="alert alert-info mt-3">
                                            По запросу «{{ $componentNum }}» предложений не найдено. Проверьте парт-номер или попробуйте другой.
                                        </div>
                                    @else
                                        <div class="catalog-items catalog_table_template" id="gc-results-layout">
                                            <div class="catalog-table">
                                                <div class="catalog-table__outer-wrapper bordered outer-rounded-x">
                                                    <div class="list grid-list grid-list--items-1 grid-list--compact grid-list--no-gap">
                                                        <div class="getchips-search-v2__table-wrap">
                                                            <table class="getchips-search-v2__table" id="gc-results-table">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Наименование</th>
                                                                        <th>Бренд</th>
                                                                        <th>Доступно</th>
                                                                        <th>
                                                                            <div class="gc-th-sort">
                                                                                <span>Срок</span>
                                                                                <span class="gc-th-sort__btns">
                                                                                    <button type="button" class="gc-th-sort__btn" data-gc-set-sort="lead_asc" title="По сроку ↑">↑</button>
                                                                                </span>
                                                                            </div>
                                                                        </th>
                                                                        <th>
                                                                            <div class="gc-th-sort">
                                                                                <div class="gc-th-price-switch">
                                                                                    <button type="button" class="gc-th-price-switch__trigger" aria-label="Выбрать валюту цены">
                                                                                        <span>Цена</span>
                                                                                        <span class="gc-th-price-switch__badge">{{ $gcCurSign }} {{ $gcCurCode }}</span>
                                                                                        <span class="gc-th-price-switch__caret" aria-hidden="true"></span>
                                                                                    </button>
                                                                                    <div class="gc-th-price-switch__menu" role="menu" aria-label="Валюта цены">
                                                                                        <button type="button" class="gc-th-price-switch__opt js-gc-cur-option {{ $gcCur === 'rub' ? 'is-active' : '' }}" data-gc-cur="rub" role="menuitem">🇷🇺 ₽ RUB</button>
                                                                                        <button type="button" class="gc-th-price-switch__opt js-gc-cur-option {{ $gcCur === 'usd' ? 'is-active' : '' }}" data-gc-cur="usd" role="menuitem">🇺🇸 $ USD</button>
                                                                                    </div>
                                                                                </div>
                                                                                <span class="gc-th-sort__btns">
                                                                                    <button type="button" class="gc-th-sort__btn" data-gc-set-sort="price_asc" title="Цена ↑">↑</button>
                                                                                    <button type="button" class="gc-th-sort__btn" data-gc-set-sort="price_desc" title="Цена ↓">↓</button>
                                                                                </span>
                                                                            </div>
                                                                        </th>
                                                                        <th>Кол-во</th>
                                                                        <th>Сумма {{ $gcCurSign }}</th>
                                                                        <th>Запросить квоту</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($offers as $offer)
                                                                        @php
                                                                            $dc = $gcCur;
                                                                            $tiers = $offer['price_tiers'] ?? [];
                                                                            $ld = $offer['lead_time_days'] ?? null;
                                                                            $leadStr = '—';
                                                                            if ($ld !== null) {
                                                                                $ld = (int) $ld;
                                                                                $leadStr = ($ld >= 7 && $ld % 7 === 0) ? ((int) ($ld / 7)) . ' нед.' : $ld . ' дн.';
                                                                            }
                                                                            $moq = max(1, (int) ($offer['min_order_qty'] ?? 1));
                                                                            $step = max(1, (int) ($offer['pack_size'] ?? 1));
                                                                            $packNote = $offer['packaging'] ?? ($offer['pack_size'] ? '×' . $offer['pack_size'] : '—');
                                                                        @endphp
                                                                        <tr data-gc-tiers='@json($tiers)' data-gc-cur="{{ $dc }}" class="{{ ($offer['provider'] ?? '') === 'lvt.market' ? 'gc-row--lvt' : '' }}">
                                                                            <td>
                                                                                <div class="gc-name">
                                                                                    <div class="gc-name__pn">{{ $offer['part_number'] ?? '—' }}</div>
                                                                                    <div class="gc-name__sub">{{ $offer['supplier'] ?? '—' }}@if(!empty($offer['provider']))<br><span>{{ $offer['provider'] }}</span>@endif</div>
                                                                                </div>
                                                                            </td>
                                                                            <td>{{ $offer['brand'] ?? '—' }}</td>
                                                                            <td>{{ $offer['stock'] ?? '—' }}</td>
                                                                            <td>{{ $leadStr }}</td>
                                                                            <td class="getchips-search-v2__price-cell">
                                                                                @if (!empty($tiers))
                                                                                    <div class="getchips-price-tiers">
                                                                                        @foreach ($tiers as $idx => $tier)
                                                                                            <div class="getchips-price-tiers__row {{ $idx === 0 ? 'getchips-price-tiers__row--first' : '' }}">
                                                                                                <span class="getchips-price-tiers__price">
                                                                                                    {{ $dc === 'usd' ? number_format((float) $tier['usd'], 4, '.', ' ') : number_format((float) $tier['rub'], 2, '.', ' ') }}
                                                                                                </span>
                                                                                                <span class="getchips-price-tiers__qty">× {{ $tier['qty'] }}</span>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                @else
                                                                                    —
                                                                                @endif
                                                                            </td>
                                                                            <td class="gc-row-qty-wrap">
                                                                                <input type="number" class="form-control gc-row-qty" min="{{ $moq }}" step="{{ $step }}" value="{{ $amount }}" data-gc-default="{{ $amount }}" aria-label="Количество для расчёта">
                                                                                <div class="gc-row-meta">
                                                                                    MIN {{ $moq }}@if($step > 1)<br>Кратность {{ $step }}@endif<br>Норма уп. {{ $packNote }}
                                                                                </div>
                                                                            </td>
                                                                            <td class="gc-row-sum-cell"><span class="gc-row-sum">—</span></td>
                                                                            <td>
                                                                                @if (!empty($offer['url']))
                                                                                    <a href="{{ $offer['url'] }}" target="_blank" rel="noopener" class="gc-cart-btn" title="Перейти к предложению" aria-label="Корзина / ссылка">
                                                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.15.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                                                                    </a>
                                                                                @else
                                                                                    <span class="gc-cart-btn" style="opacity:.35;pointer-events:none;" aria-disabled="true">
                                                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.15.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                                                                    </span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <nav class="mt-3" aria-label="Страницы">{!! $offers->links('pagination::bootstrap-4') !!}</nav>
                                    @endif
                                @endif
                                </div>

                                @if ($componentNum === '' && !empty($timingsMs))
                                    <div class="getchips-timings" style="margin-top: 16px;">
                                        @foreach ($timingsMs as $provider => $timing)
                                            {{ $provider }}: {{ $timing }} ms{{ $loop->last ? '' : ', ' }}
                                        @endforeach
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        /* Переключение главного изображения в карточке */
        function gcSwitchImg(thumb, url) {
            var img = document.getElementById('gc-main-img');
            if (img) img.src = url;
            var wrap = document.getElementById('gc-main-img-wrap');
            if (wrap) { wrap.querySelectorAll('.gc-product-card__thumb').forEach(function (t) { t.classList.remove('is-active'); }); }
            thumb.classList.add('is-active');
        }

        (function () {
            /* Панель поиска — обычный GET submit (кнопка «Найти» и Enter в полях формы) */
            var toolbarForm = document.getElementById('gc-toolbar-search-form');
            if (toolbarForm) {
                toolbarForm.addEventListener('submit', function () {
                    var inp = document.getElementById('gc-v2-part');
                    if (inp && inp.value) {
                        inp.value = inp.value.trim().toUpperCase();
                    }
                });
            }

            var filterFormEl = document.getElementById('search-form-v2');
            var resultsRoot = document.getElementById('getchips-results-root');
            var searchRoute = {{ json_encode(route('search.index.v2')) }};

            /* -------- AJAX-фильтры (чекбоксы / радио / per_page) -------- */
            if (!filterFormEl || !resultsRoot) return;

            function filterQueryUrl() {
                var u = new URL(searchRoute, window.location.href);
                var params = new URLSearchParams();
                var pn = (document.getElementById('gc-v2-part') || {}).value;
                pn = (pn || '').trim().toUpperCase();
                if (pn) params.set('componentNum', pn);
                var amt = parseInt((document.getElementById('gc-v2-amount') || {}).value, 10) || 1;
                params.set('amount', amt);
                params.set('sort', (document.getElementById('gc-sort-input') || {}).value || 'price_asc');
                filterFormEl.querySelectorAll(
                    'input[name="part_numbers[]"]:checked,' +
                    'input[name="brands[]"]:checked,' +
                    'input[name="suppliers[]"]:checked,' +
                    'input[name="display_currency"]:checked,' +
                    'input[name="price_min"],' +
                    'input[name="price_max"],' +
                    'select[name="per_page"]'
                ).forEach(function (el) {
                    var v = el.value;
                    if (v === '' || v === undefined) return;
                    if (el.tagName === 'INPUT' && el.type === 'checkbox' && !el.checked) return;
                    if (el.tagName === 'INPUT' && el.type === 'radio'    && !el.checked) return;
                    params.append(el.name, v);
                });
                u.search = params.toString();
                return u.toString();
            }

            function loadResults(url) {
                resultsRoot.classList.add('getchips-results-root--loading');
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    credentials: 'same-origin'
                })
                    .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
                    .then(function (html) {
                        var doc = new DOMParser().parseFromString(html, 'text/html');
                        var next = doc.getElementById('getchips-results-root');
                        if (next) resultsRoot.innerHTML = next.innerHTML;
                        try {
                            var uu = new URL(url, window.location.href);
                            window.history.replaceState(null, '', uu.pathname + uu.search);
                        } catch (err) {}
                    })
                    .catch(function () { window.location.href = url; })
                    .finally(function () {
                        resultsRoot.classList.remove('getchips-results-root--loading');
                        gcAfterResultsReplace();
                    });
            }

            var filterTimer;
            function scheduleFilterReload() {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(function () { loadResults(filterQueryUrl()); }, 480);
            }

            filterFormEl.querySelectorAll('.js-getchips-filter-auto').forEach(function (el) {
                el.addEventListener('change', function () {
                    gcUpdateDdSummaries();
                    scheduleFilterReload();
                });
            });
            filterFormEl.querySelectorAll('.js-getchips-filter-debounce').forEach(function (el) {
                el.addEventListener('input', scheduleFilterReload);
                el.addEventListener('change', function () {
                    clearTimeout(filterTimer);
                    loadResults(filterQueryUrl());
                });
            });

            document.addEventListener('click', function (e) {
                var a = e.target.closest('#getchips-results-root .pagination a.page-link');
                if (!a || !a.getAttribute('href')) return;
                e.preventDefault();
                loadResults(a.href);
            });

            document.addEventListener('click', function (e) {
                var curBtn = e.target.closest('.js-gc-cur-option');
                if (curBtn) {
                    e.preventDefault();
                    var nextCur = curBtn.getAttribute('data-gc-cur');
                    if (nextCur !== 'rub' && nextCur !== 'usd') return;
                    var radio = filterFormEl.querySelector('input[name="display_currency"][value="' + nextCur + '"]');
                    if (!radio) return;
                    if (!radio.checked) {
                        radio.checked = true;
                    }
                    gcUpdateDdSummaries();
                    clearTimeout(filterTimer);
                    loadResults(filterQueryUrl());
                    return;
                }

                var btn = e.target.closest('[data-gc-set-sort]');
                if (!btn) return;
                e.preventDefault();
                var v = btn.getAttribute('data-gc-set-sort');
                var inp = document.getElementById('gc-sort-input');
                if (inp && v) {
                    inp.value = v;
                    clearTimeout(filterTimer);
                    loadResults(filterQueryUrl());
                }
            });

            function gcUpdateDdSummaries() {
                function count(name) {
                    return filterFormEl.querySelectorAll('input[name="' + name + '"]:checked').length;
                }
                [['pn', 'part_numbers[]'], ['br', 'brands[]'], ['sup', 'suppliers[]']].forEach(function (pair) {
                    var el = filterFormEl.querySelector('[data-gc-dd="' + pair[0] + '"]');
                    if (!el) return;
                    var n = count(pair[1]);
                    el.textContent = n === 0 ? 'Любой' : ('Выбрано ' + n);
                });
            }

            gcUpdateDdSummaries();

            var clearBtn = document.getElementById('gc-clear-filters');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    filterFormEl.querySelectorAll('input[name="part_numbers[]"], input[name="brands[]"], input[name="suppliers[]"]').forEach(function (cb) {
                        cb.checked = false;
                    });
                    var pm = filterFormEl.querySelector('#price_min-v2');
                    var px = filterFormEl.querySelector('#price_max-v2');
                    if (pm) pm.value = '';
                    if (px) px.value = '';
                    var rub = filterFormEl.querySelector('input[name="display_currency"][value="rub"]');
                    if (rub) rub.checked = true;
                    gcUpdateDdSummaries();
                    clearTimeout(filterTimer);
                    loadResults(filterQueryUrl());
                });
            }

            function gcPickTier(tiers, qty) {
                if (!tiers || !tiers.length) return null;
                var q = Math.max(1, parseInt(String(qty), 10) || 1);
                var best = null;
                for (var i = 0; i < tiers.length; i++) {
                    if (tiers[i].qty <= q) best = tiers[i];
                }
                return best || tiers[0];
            }

            function gcFmtSum(n, cur) {
                if (cur === 'usd') {
                    return n.toLocaleString('ru-RU', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
                }
                return n.toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function gcUpdateRow(tr) {
                var raw = tr.getAttribute('data-gc-tiers');
                if (!raw) return;
                var tiers;
                try { tiers = JSON.parse(raw); } catch (e) { return; }
                var cur = tr.getAttribute('data-gc-cur') || 'rub';
                var inp = tr.querySelector('.gc-row-qty');
                var out = tr.querySelector('.gc-row-sum');
                if (!inp || !out) return;
                var qty = parseInt(inp.value, 10);
                if (isNaN(qty) || qty < 1) qty = 1;
                var t = gcPickTier(tiers, qty);
                if (!t) { out.textContent = '—'; return; }
                var unit = cur === 'usd' ? t.usd : t.rub;
                var total = unit * qty;
                out.textContent = gcFmtSum(total, cur);
            }

            function gcAfterResultsReplace() {
                resultsRoot.querySelectorAll('tbody tr[data-gc-tiers]').forEach(gcUpdateRow);
                gcUpdateDdSummaries();
            }
            window.gcV2AfterResultsReplace = gcAfterResultsReplace;
            gcAfterResultsReplace();

            document.addEventListener('input', function (e) {
                if (!e.target.classList.contains('gc-row-qty')) return;
                var tr = e.target.closest('tr');
                if (tr) gcUpdateRow(tr);
            });

            var viewToggle = document.getElementById('gc-view-toggle');
            var resultsLayout = function () { return document.getElementById('gc-results-layout'); };
            if (viewToggle) {
                viewToggle.addEventListener('click', function () {
                    var el = resultsLayout();
                    if (!el) return;
                    el.classList.toggle('gc-results--compact');
                    viewToggle.classList.toggle('is-active');
                    viewToggle.setAttribute('aria-pressed', el.classList.contains('gc-results--compact') ? 'true' : 'false');
                });
            }

            document.addEventListener('click', function (e) {
                if (!e.target.closest('#gc-share-btn')) return;
                var url = window.location.href;
                if (navigator.share) {
                    navigator.share({ title: document.title, url: url }).catch(function () {});
                } else if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(function () { alert('Ссылка скопирована'); }).catch(function () { prompt('Ссылка', url); });
                } else {
                    prompt('Ссылка', url);
                }
            });

            function gcEscapeCsv(s) {
                s = String(s == null ? '' : s).replace(/"/g, '""');
                if (/[",\n\r]/.test(s)) return '"' + s + '"';
                return s;
            }

            document.addEventListener('click', function (e) {
                if (!e.target.closest('#gc-export-btn')) return;
                var table = document.getElementById('gc-results-table');
                if (!table) return;
                var rows = table.querySelectorAll('tr');
                var lines = [];
                rows.forEach(function (tr) {
                    var cells = tr.querySelectorAll('th, td');
                    var parts = [];
                    cells.forEach(function (cell) {
                        var q = cell.querySelector('.gc-row-qty');
                        if (q) {
                            parts.push(gcEscapeCsv(q.value));
                            return;
                        }
                        parts.push(gcEscapeCsv(cell.innerText.replace(/\s+/g, ' ').trim()));
                    });
                    lines.push(parts.join(';'));
                });
                var blob = new Blob(['\ufeff' + lines.join('\n')], { type: 'text/csv;charset=utf-8' });
                var a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'getchips-results.csv';
                a.click();
                URL.revokeObjectURL(a.href);
            });
        })();
    </script>
</body>
</html>
