$(function () {
    $('.js-lvt-search-mode-param').each(function () {
        if ($(this).val() !== 'partnumber') {
            return;
        }
        var $form = $(this).closest('form');
        var $q = $form.find('.js-lvt-header-search-q');
        var ph = $q.attr('data-placeholder-part');
        if (ph) {
            $q.attr('placeholder', ph);
        }
        $(this).closest('[id^="title-search"]').find('.lvt-header-partnumber-examples').addClass('is-visible');
    });
});

$(document).on('click', '.dropdown-select.searchtype .dropdown-menu-item:not(.dropdown-menu-item--current)', function(){
    let $this = $(this);
    let $title = $this.closest('.dropdown-select').find('.dropdown-select__title');
    let $menu = $this.closest('.dropdown-select').find('.dropdown-select__list');
    let bVisibleMeu = $menu.is(':visible');
    let animate = !bVisibleMeu ? 'transition.slideUpIn' : 'fadeOut';
    let type = $this.data('type');
    let name = type === 'all' ? BX.message('SEARCH_IN_SITE') : BX.message('SEARCH_IN_CATALOG');

    if (!$title.hasClass('clicked')) {
        $title.addClass('clicked');

        $title.toggleClass('opened');
        $menu.stop().slideUp(300, function() {
            $title.removeClass("clicked");
        });
    }

    $.cookie('searchtitle_type', type);

    $this.closest('.dropdown-select').find('input[name=type]').val(type);

    // remove already visible results
    $('.title-search-result').hide().empty();

    // fire new search request
    BX.fireEvent($this.closest('.dropdown-select').find('input[name=type]')[0], 'change');

    $('.dropdown-select.searchtype').each(function(){
        $(this).closest('form').attr('action', type === 'all' ? arAsproOptions.PAGES.SEARCH_PAGE_URL : arAsproOptions.PAGES.CATALOG_PAGE_URL);
        $(this).find('.dropdown-select__title>span').text(name);
        $(this).find('input[name=type]').val(type);

        $(this).find('.dropdown-menu-item').removeClass('dropdown-menu-item--current');
        $(this).find('.dropdown-menu-item[data-type=' + type + ']').addClass('dropdown-menu-item--current');
    });

    try {
        $this.closest('form').find('input[name=q]')[0].focus();
    }
    catch (e) {
    }
});

$(document).on('click', '.lvt-search-mode-dropdown .js-lvt-search-mode-item:not(.dropdown-menu-item--current)', function () {
    var $this = $(this);
    var mode = $this.data('lvt-search-mode');
    var $dd = $this.closest('.lvt-search-mode-dropdown');
    var $title = $dd.find('.dropdown-select__title');
    var $menu = $dd.find('.dropdown-select__list');
    var $form = $this.closest('form');

    if (!$title.hasClass('clicked')) {
        $title.addClass('clicked');
        $title.toggleClass('opened');
        $menu.stop().slideUp(300, function () {
            $title.removeClass('clicked');
        });
    }

    var catalogUrl = (typeof arAsproOptions !== 'undefined' && arAsproOptions.PAGES && arAsproOptions.PAGES.CATALOG_PAGE_URL)
        ? arAsproOptions.PAGES.CATALOG_PAGE_URL
        : '/catalog/';
    $form.attr('action', catalogUrl);

    var $modeInput = $form.find('.js-lvt-search-mode-param');
    var $q = $form.find('.js-lvt-header-search-q');
    var $block = $this.closest('[id^="title-search"]');
    var $examples = $block.find('.lvt-header-partnumber-examples');

    if (mode === 'partnumber') {
        $modeInput.val('partnumber');
        $.cookie('lvt_search_mode', 'partnumber', {path: '/'});
        $dd.find('.js-lvt-search-mode-label').text('Поиск по партномеру');
        var phP = $q.attr('data-placeholder-part') || '';
        if (phP) {
            $q.attr('placeholder', phP);
        }
        $examples.addClass('is-visible');
    } else {
        $modeInput.val('');
        $.removeCookie('lvt_search_mode', {path: '/'});
        $dd.find('.js-lvt-search-mode-label').text('Поиск по сайту');
        var phS = $q.attr('data-placeholder-site') || '';
        if (phS) {
            $q.attr('placeholder', phS);
        }
        $examples.removeClass('is-visible');
    }

    $dd.find('.js-lvt-search-mode-item').removeClass('color_222 dropdown-menu-item--current').addClass('dark_link');
    $this.removeClass('dark_link').addClass('color_222 dropdown-menu-item--current');

    $('.title-search-result').hide().empty();
    if ($modeInput.length) {
        BX.fireEvent($modeInput[0], 'change');
    }

    try {
        $form.find('input[name=q]')[0].focus();
    } catch (e) {
    }

    $('.lvt-search-mode-dropdown').each(function () {
        var $d = $(this);
        var $f = $d.closest('form');
        $f.attr('action', catalogUrl);
        var m = $f.find('.js-lvt-search-mode-param').val();
        if (m === 'partnumber') {
            $d.find('.js-lvt-search-mode-label').text('Поиск по партномеру');
        } else {
            $d.find('.js-lvt-search-mode-label').text('Поиск по сайту');
        }
    });
});
