@php
    $tpl = rtrim((string) ($aspro ?? ''), '/');
    $icons = $tpl . '/images/svg/header_icons.svg?1764098472';
    $arrows = $tpl . '/images/svg/arrows.svg?1764098472';
    $themeIcons = rtrim((string) $site, '/') . '/bitrix/components/aspro/theme.selector.lite/templates/.default/images/svg/icons.svg?1752963450';
    $logo = rtrim((string) $site, '/') . '/upload/CLite/f1f/fqrjw4cme6l7ng2zpnmckraxri0f213g.png';
@endphp
<header class="header_2 header header--narrow">
    <div class="header__inner header__inner--paddings">
        <div class="header__top-part header__top-part--longer part-with-search" data-ajax-load-block="HEADER_TOP_PART">
            <div class="maxwidth-theme">
                <div class="header__top-inner part-with-search__inner">
                    <div class="header__top-item">
                        <div class="line-block line-block--40">
                            <div class="logo line-block__item no-shrinked">
                                <a class="menu-light-icon-fill banner-light-icon-fill" href="{{ $site }}/">
                                    <img src="{{ $logo }}" alt="LVT.market — электронные компоненты" title="LVT.market — электронные компоненты" width="200" height="48" loading="eager">
                                </a>
                            </div>
                        </div>
                    </div>

                    @if ($showHeaderSearch ?? true)
                    <div class="header__top-item header__search header__search--limited" data-ajax-load-block="HEADER_TOGGLE_SEARCH">
                        <div class="search-wrapper relative">
                            <div id="title-search">
                                <div class="search" role="search">
                                    <button class="search-input-close btn-close fill-dark-light-block" type="button" aria-label="Закрыть" tabindex="-1">
                                        <i class="svg inline clear inline" aria-hidden="true"><svg width="16" height="16"><use xlink:href="{{ $icons }}#close-16-16"></use></svg></i>
                                    </button>
                                    <div class="search-input-div">
                                        <input class="search-input font_16 banner-light-text form-control"
                                               id="title-search-input"
                                               type="text"
                                               name="componentNum"
                                               value="{{ $componentNum }}"
                                               placeholder="Парт-номер компонента (Getchips)"
                                               size="40"
                                               maxlength="255"
                                               autocomplete="off"
                                               required>
                                    </div>
                                    <div class="search-button-div">
                                        <button class="btn btn--no-rippple btn-clear-search fill-dark-light-block banner-light-icon-fill light-opacity-hover" type="button" name="rs" id="getchips-header-clear" title="Очистить">
                                            <i class="svg inline clear inline" aria-hidden="true"><svg width="9" height="9"><use xlink:href="{{ $icons }}#close-9-9"></use></svg></i>
                                        </button>
                                        <button class="btn btn-search btn--no-rippple fill-dark-light-block banner-light-icon-fill light-opacity-hover" type="submit" id="search-button-v2" name="s" value="1" title="Найти">
                                            <i class="svg inline search inline" aria-hidden="true"><svg width="18" height="18"><use xlink:href="{{ $icons }}#search-18-18"></use></svg></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="title-search-result title-search-input"></div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="header__top-item no-shrinked" data-ajax-load-block="HEADER_TOGGLE_PHONE">
                        <div class="phones">
                            <div class="phones__phones-wrapper">
                                <div class="phones__inner phones__inner--big fill-theme-parent">
                                    <a class="phones__phone-link phones__phone-first dark_link banner-light-text menu-light-text icon-block__name" href="tel:+74952601369">+7 (495) 260-13-69</a>
                                    <div class="phones__dropdown">
                                        <div class="dropdown dropdown--relative">
                                            <div class="phones__dropdown-items phones__dropdown-items--phones">
                                                <div class="phones__phone-more dropdown__item color-theme-hover dropdown__item--first dropdown__item--last">
                                                    <a class="phones__phone-link dark_link phones__phone-link--no_descript" rel="nofollow" href="tel:+74952601369">+7 (495) 260-13-69</a>
                                                </div>
                                            </div>
                                            <div class="phones__dropdown-item callback-item">
                                                <a href="{{ $site }}/" class="animate-load btn btn-default btn-wide btn-sm">Заказать звонок</a>
                                            </div>
                                        </div>
                                        <div class="dropdown dropdown--relative">
                                            <div class="phones__dropdown-item">
                                                <div class="email__title phones__dropdown-title">E-mail</div>
                                                <div class="phones__dropdown-value">
                                                    <div>
                                                        <a href="mailto:info@lvtgroup.ru">info@lvtgroup.ru</a><br>
                                                        <a href="mailto:Snab@lvtgroup.ru">snab@lvtgroup.ru</a><br>
                                                        <a href="mailto:zakaz@lvtgroup.ru">zakaz@lvtgroup.ru</a><br>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="phones__dropdown-item">
                                                <div class="address__title phones__dropdown-title">Адрес</div>
                                                <div class="phones__dropdown-value">
                                                    <div class="address__text address__text--large">140080, Московская обл., г. Лыткарино, тер. промзона Тураево, стр. 16</div>
                                                </div>
                                            </div>
                                            <div class="phones__dropdown-item">
                                                <div class="schedule__title phones__dropdown-title">Режим работы</div>
                                                <div class="phones__dropdown-value">
                                                    <div class="schedule__text">Пн. – Пт.: с 9:00 до 18:00</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="more-arrow banner-light-icon-fill menu-light-icon-fill fill-dark-light-block">
                                        <i class="svg inline inline" aria-hidden="true"><svg width="7" height="5"><use xlink:href="{{ $arrows }}#down-7-5"></use></svg></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="phones__callback light-opacity-hover dark_link banner-light-text menu-light-text hide-1200">
                                        <a href="{{ $site }}/" class="dark_link">Заказать звонок</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="header__top-item" data-ajax-load-block="HEADER_TOGGLE_THEME_SELECTOR">
                        <div class="header-theme-selector">
                            <div id="theme-selector--g0" class="theme-selector" title="Тема оформления (на основном сайте)">
                                <div class="theme-selector__inner">
                                    <div class="theme-selector__items">
                                        <div class="theme-selector__item theme-selector__item--light current">
                                            <div class="theme-selector__item-icon"><i class="svg inline light-16-16 inline" aria-hidden="true"><svg width="16" height="16"><use xlink:href="{{ $themeIcons }}#light-16-16"></use></svg></i></div>
                                        </div>
                                        <a class="theme-selector__item theme-selector__item--dark dark_link" href="{{ $site }}/" title="Перейти на сайт для переключения темы">
                                            <div class="theme-selector__item-icon"><i class="svg inline dark-14-14 inline" aria-hidden="true"><svg width="14" height="14"><use xlink:href="{{ $themeIcons }}#dark-14-14"></use></svg></i></div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
