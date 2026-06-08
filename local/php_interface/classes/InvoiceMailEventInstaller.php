<?php

use Bitrix\Main\Config\Option;

class InvoiceMailEventInstaller
{
    private const OPTION_MODULE = 'main';
    private const OPTION_NAME = 'sale_invoice_ready_event_installed';
    private const EVENT_NAME = 'SALE_INVOICE_READY';

    public static function ensure(): void
    {
        if (Option::get(self::OPTION_MODULE, self::OPTION_NAME, 'N') === 'Y') {
            return;
        }

        self::ensureEventType();
        self::ensureEventMessage();

        Option::set(self::OPTION_MODULE, self::OPTION_NAME, 'Y');
    }

    private static function ensureEventType(): void
    {
        $db = CEventType::GetList(['TYPE_ID' => self::EVENT_NAME]);
        $existing = [];
        while ($row = $db->Fetch()) {
            $existing[$row['LID']] = true;
        }

        $langRes = CLanguage::GetList($by = 'sort', $order = 'asc', ['ACTIVE' => 'Y']);
        while ($lang = $langRes->Fetch()) {
            if (isset($existing[$lang['LID']])) {
                continue;
            }
            $type = new CEventType();
            $type->Add([
                'LID' => $lang['LID'],
                'EVENT_NAME' => self::EVENT_NAME,
                'NAME' => 'Счет на оплату готов',
                'DESCRIPTION' =>
                    '#EMAIL# - Email клиента' . "\n" .
                    '#ORDER_ID# - Номер заказа' . "\n" .
                    '#ORDER_REAL_ID# - ID заказа' . "\n" .
                    '#INVOICE_NUMBER# - Номер счета' . "\n" .
                    '#INVOICE_DATE# - Дата счета' . "\n" .
                    '#INVOICE_TOTAL# - Сумма счета' . "\n" .
                    '#SITE_NAME# - Домен сайта',
            ]);
        }
    }

    private static function ensureEventMessage(): void
    {
        $sites = [];
        $siteRes = CSite::GetList($by = 'sort', $order = 'asc', ['ACTIVE' => 'Y']);
        while ($site = $siteRes->Fetch()) {
            $sites[] = $site['LID'];
        }
        if (empty($sites)) {
            return;
        }

        $exists = CEventMessage::GetList('', '', [
            'EVENT_NAME' => self::EVENT_NAME,
            'ACTIVE' => 'Y',
        ])->Fetch();

        if ($exists) {
            return;
        }

        $message = new CEventMessage();
        $message->Add([
            'ACTIVE' => 'Y',
            'EVENT_NAME' => self::EVENT_NAME,
            'LID' => $sites,
            'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
            'EMAIL_TO' => '#EMAIL#',
            'SUBJECT' => 'Счет на оплату по заказу №#ORDER_ID#',
            'BODY_TYPE' => 'html',
            'MESSAGE' =>
                '<p>Здравствуйте!</p>' .
                '<p>По вашему заказу № <strong>#ORDER_ID#</strong> сформирован счет на оплату.</p>' .
                '<p>Счет № <strong>#INVOICE_NUMBER#</strong> от #INVOICE_DATE#, сумма: <strong>#INVOICE_TOTAL#</strong>.</p>' .
                '<p>Файл счета приложен к письму. Также его можно скачать в личном кабинете на сайте #SITE_NAME#.</p>',
        ]);
    }
}
