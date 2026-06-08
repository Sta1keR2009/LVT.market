<?php

if (!function_exists('lvt_getchips_price_header_html')) {
    /**
     * Компактный заголовок колонки «Цена» с выпадающим списком валют (RUB по умолчанию).
     */
    function lvt_getchips_price_header_html(float $usdToRub, string $rateDate = ''): string
    {
        if ($rateDate === '') {
            $rateDate = date('d.m.Y');
        }
        $rateDateEsc = htmlspecialchars($rateDate, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $usdRateLabel = htmlspecialchars(number_format($usdToRub, 2, ',', ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $rateNoticeEsc = htmlspecialchars(
            'Если курс изменится более чем на 2%, стоимость заказа будет пересчитана.',
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        return '<span class="getchips-price-head">'
            . '<span class="getchips-price-head__controls">'
            . '<span class="getchips-currency-switch js-getchips-currency-switch" data-display-currency="RUB">'
            . '<button type="button" class="getchips-currency-switch__trigger" aria-haspopup="listbox" aria-expanded="false" title="Валюта отображения цен">'
            . '<span class="js-getchips-currency-label">₽ RUB</span>'
            . '<span class="getchips-currency-switch__caret" aria-hidden="true"></span>'
            . '</button>'
            . '<span class="getchips-currency-switch__menu" role="listbox">'
            . '<button type="button" class="getchips-currency-switch__item is-active" data-currency="RUB" role="option">₽ RUB</button>'
            . '<button type="button" class="getchips-currency-switch__item" data-currency="USD" role="option">$ USD</button>'
            . '<span class="getchips-currency-switch__rate-row">'
            . '<span class="getchips-currency-switch__rate js-getchips-cbr-rate" data-rate-date="' . $rateDateEsc . '">' . $usdRateLabel . ' ₽/$</span>'
            . '<span class="getchips-currency-alert getchips-currency-alert--inline js-getchips-rate-alert" data-notice="' . $rateNoticeEsc . '">!</span>'
            . '</span>'
            . '</span>'
            . '</span>'
            . '</span>'
            . '</span>';
    }
}
