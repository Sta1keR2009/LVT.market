<?php
/**
 * Options -------------------------------
 */

// auth
$MESS['CATAPULTO_DELIVERY_OPT_apikey'] = "API ключ";

// common
$MESS ['CATAPULTO_DELIVERY_OPT_showInOrders'] = "Отображать кнопку заявки в заказах";
$MESS ['CATAPULTO_DELIVERY_OPT_dadataApikey'] = "API ключ DaData";
$MESS ['CATAPULTO_DELIVERY_OPT_widgetYandexKey'] = "API-ключ Яндекс.карт";

// sender
$MESS ['CATAPULTO_DELIVERY_OPT_senderLocalityId']      = "ID города отправителя";
$MESS ['CATAPULTO_DELIVERY_OPT_senderZip']             = "Индекс отправителя";
$MESS ['CATAPULTO_DELIVERY_OPT_senderCity']            = "Город отправителя";
$MESS ['CATAPULTO_DELIVERY_OPT_senderId']              = "ID отправителя по умолчанию";
$MESS ['CATAPULTO_DELIVERY_OPT_poaEnabled']            = "Включить отправку по доверенности (для \"Деловые линии\")";
$MESS ['CATAPULTO_DELIVERY_OPT_poaFromName']           = "ФИО контактного лица";
$MESS ['CATAPULTO_DELIVERY_OPT_poaFromPassportSeries'] = "Серия паспорта (4 цифры)";
$MESS ['CATAPULTO_DELIVERY_OPT_poaFromPassportNumber'] = "Номер паспорта (6 цифр)";
$MESS ['CATAPULTO_DELIVERY_OPT_poaFromPassportDate']   = "Дата выдачи (дд.мм.гггг)";
$MESS ['CATAPULTO_DELIVERY_OPT_poaSendReceiverEmail']  = "Email для отправки доверенности (не обязательно)";

// defaultCargo
$MESS ['CATAPULTO_DELIVERY_OPT_defMode']       = "Рассчитывать средние габариты для";
$MESS ['CATAPULTO_DELIVERY_OPT_defaultWidth']  = "Ширина по умолчанию, мм";
$MESS ['CATAPULTO_DELIVERY_OPT_defaultHeight'] = "Высота по умолчанию, мм";
$MESS ['CATAPULTO_DELIVERY_OPT_defaultLength'] = "Длина по умолчанию, мм";
$MESS ['CATAPULTO_DELIVERY_OPT_defaultWeight'] = "Вес по умолчанию, гр";

// delivery
$MESS ['CATAPULTO_DELIVERY_OPT_termIncrease']               = "Увеличить срок доставки на (дн.)";
$MESS ['CATAPULTO_DELIVERY_OPT_deliveryDefaultPrice']       = "Стоимость доставки по умолчанию";
$MESS ['CATAPULTO_DELIVERY_OPT_mindEnsurance']              = "Включать страхование по умолчанию";
$MESS ['CATAPULTO_DELIVERY_OPT_smsAmount']                  = "SMS-информирование получателя";
$MESS ['CATAPULTO_DELIVERY_OPT_deliveryFreeCourierFrom']    = "Бесплатная курьерская доставка от";
$MESS ['CATAPULTO_DELIVERY_OPT_deliveryFreePVZFrom']        = "Бесплатная доставка на ПВЗ от";
$MESS ['CATAPULTO_DELIVERY_OPT_deliveryFreeByCity']         = "Отдельные настройки бесплатной доставки действуют для";
$MESS ['CATAPULTO_DELIVERY_OPT_noPVZnoOrder']               = "Не давать оформить заказ без выбранного в виджете способа доставки";
$MESS ['CATAPULTO_DELIVERY_OPT_needReselect']               = "Не давать оформить заказ после смены способа оплаты";
$MESS ['CATAPULTO_DELIVERY_OPT_markupType']                 = "Тип наценки";
$MESS ['CATAPULTO_DELIVERY_OPT_markupValue']                = "Наценка";
$MESS ['CATAPULTO_DELIVERY_OPT_isFitting']                  = "Примерка доступна";
$MESS ['CATAPULTO_DELIVERY_OPT_fittingDefaultEnabled']      = "Включать примерку по умолчанию";
$MESS ['CATAPULTO_DELIVERY_OPT_partialRedemptionEnabled']   = "Включать частичный выкуп по умолчанию";
$MESS ['CATAPULTO_DELIVERY_OPT_updateOrderDelivery']        = "Менять у заказа стоимость доставки и оплаты при перевыборе способа доставки";
$MESS ['CATAPULTO_DELIVERY_OPT_deliveryPvzMarkupType']      = "Наценка на доставку в пункты выдачи";
$MESS ['CATAPULTO_DELIVERY_OPT_deliveryPvzMarkupValue']     = "Величина";
$MESS ['CATAPULTO_DELIVERY_OPT_deliveryCourierMarkupType']  = "Наценка на доставку курьером";
$MESS ['CATAPULTO_DELIVERY_OPT_deliveryCourierMarkupValue'] = "Величина";

// statuses
$MESS ['CATAPULTO_DELIVERY_OPT_status_courier_take']     = "Курьер забирает";
$MESS ['CATAPULTO_DELIVERY_OPT_status_on_road']          = "В пути";
$MESS ['CATAPULTO_DELIVERY_OPT_status_delivery']         = "На доставке";
$MESS ['CATAPULTO_DELIVERY_OPT_status_delivery_problem'] = "Проблема с доставкой";
$MESS ['CATAPULTO_DELIVERY_OPT_status_reject']           = "Отмена";
$MESS ['CATAPULTO_DELIVERY_OPT_status_are_cleared']      = "Проходит таможенную очистку";
$MESS ['CATAPULTO_DELIVERY_OPT_status_created']          = "Создан, идет обработка";
$MESS ['CATAPULTO_DELIVERY_OPT_status_forwarding']       = "Переадресация";
$MESS ['CATAPULTO_DELIVERY_OPT_status_return_to_sender'] = "Возврат отправителю";
$MESS ['CATAPULTO_DELIVERY_OPT_status_completed']        = "Доставлено";
$MESS ['CATAPULTO_DELIVERY_OPT_status_return_doc']       = "Возврат документов";
$MESS ['CATAPULTO_DELIVERY_OPT_status_ready_to_pickup']  = "Готов к выдаче";

$MESS ['CATAPULTO_DELIVERY_OPT_addTracking']         = "Выставлять отправленным заказам идентификатор отправления";
$MESS ['CATAPULTO_DELIVERY_OPT_markPayed']           = "Отмечать доставленный заказ оплаченным";
$MESS ['CATAPULTO_DELIVERY_OPT_useTrackingStatuses'] = "Использовать статусы трекинга отправлений";
$MESS ['CATAPULTO_DELIVERY_OPT_blockingStatus']      = "Не менять статус заказа Битрикса, если заказ уже находится в статусе";


// orderProps
$MESS ['CATAPULTO_DELIVERY_OPT_firstName'] = "Контактное лицо";
$MESS ['CATAPULTO_DELIVERY_OPT_company']   = "Компания-получатель";
$MESS ['CATAPULTO_DELIVERY_OPT_email']     = "E-mail";
$MESS ['CATAPULTO_DELIVERY_OPT_phone']     = "Телефон";
$MESS ['CATAPULTO_DELIVERY_OPT_line']      = "Адрес доставки";

// widget
$MESS ['CATAPULTO_DELIVERY_OPT_widgetDateType']              = "Формат отображения даты доставки";
$MESS ['CATAPULTO_DELIVERY_OPT_widgetIsPopup']               = "Открывать виджет во всплывающем окне (popup)";
$MESS ['CATAPULTO_DELIVERY_OPT_pvzPicker']                   = "Код свойства, куда будет сохранен выбранный пункт выдачи";
$MESS ['CATAPULTO_DELIVERY_OPT_requireFullAddress']          = "Требовать ввод полного адреса в виджете";
$MESS ['CATAPULTO_DELIVERY_OPT_enDadataSuggestions']         = "Подключить подсказки Dadata для свойства ввода адреса";
$MESS ['CATAPULTO_DELIVERY_OPT_widgetDeliveryTypes']         = "Способы доставки в виджете";
$MESS ['CATAPULTO_DELIVERY_OPT_widgetDeliveryFrom']          = "Способ передачи отправлений по умолчанию";
$MESS ['CATAPULTO_DELIVERY_OPT_enMapOpenMode']               = "Активная вкладка по умолчанию";
$MESS ['CATAPULTO_DELIVERY_OPT_runWidgetOnStart']            = "Запускать виджет после выбора службы доставки Catapulto";
$MESS ['CATAPULTO_DELIVERY_OPT_ctptGeoEmptyMessage']         = "Текст ошибки в виджете при поиске местоположений";
$MESS ['CATAPULTO_DELIVERY_OPT_ctptCustomDefaultWidgetText'] = "Текст виджета \"Срок доставки\" по умолчанию";
// service
$MESS ['CATAPULTO_DELIVERY_OPT_isTest']           = "Работа в тестовом режиме";
$MESS ['CATAPULTO_DELIVERY_OPT_customApiUrl']     = "Произвольный адрес для запросов API";
$MESS ['CATAPULTO_DELIVERY_OPT_customWSSUrl']     = "Произвольный адрес для запросов WS";
$MESS ['CATAPULTO_DELIVERY_OPT_timeout']          = "Таймаут расчета, сек";
$MESS ['CATAPULTO_DELIVERY_OPT_debug']            = "Включение режима отладки";
$MESS ['CATAPULTO_DELIVERY_OPT_ymapsAPIKey']      = "API-ключ Яндекс.карт";
$MESS ['CATAPULTO_DELIVERY_OPT_client_notify']    = "Отправлять уведомления о трек-номере";
$MESS ['CATAPULTO_DELIVERY_OPT_use_widget_local'] = "Использовать локальную копию виджета";

// payments
$MESS ['CATAPULTO_DELIVERY_OPT_payNal']     = "Оплата наличными (наложенный платеж)";
$MESS ['CATAPULTO_DELIVERY_OPT_payCard']    = "Оплата картой при получении (наложенный платеж)";
$MESS ['CATAPULTO_DELIVERY_OPT_checkPayed'] = "Считать заказ оплаченным только при оплате всех платежных систем";
$MESS ['CATAPULTO_DELIVERY_OPT_payTypeNP']  = "Тип наложенного платежа";

$MESS ['CATAPULTO_DELIVERY_OPT_sync_data_completed'] = "Флаг проведенной первичной синхронизации данных";

//colors
$MESS ['CATAPULTO_DELIVERY_OPT_search_button_background'] = "Цвет фона кнопки \"Найти\"";
$MESS ['CATAPULTO_DELIVERY_OPT_search_button_border']     = "Цвет окантовки кнопки \"Найти\"";
$MESS ['CATAPULTO_DELIVERY_OPT_search_button_text']       = "Цвет текста кнопки \"Найти\"";
$MESS ['CATAPULTO_DELIVERY_OPT_search_button_hover']      = "Цвет фона кнопки \"Найти\" при наведении";
$MESS ['CATAPULTO_DELIVERY_OPT_primary_widget_color']     = "Основной цвет виджета";
$MESS ['CATAPULTO_DELIVERY_OPT_cluster_color']            = "Цвет кластеров терминалов на карте";


// labels
$MESS ['CATAPULTO_DELIVERY_LBL_CLEARED']         = "Очищено";
$MESS ['CATAPULTO_DELIVERY_ALWAYS']              = "Всегда";
$MESS ['CATAPULTO_DELIVERY_LBL_YOULOGIN']        = "Ваш логин";
$MESS ['CATAPULTO_DELIVERY_LBL_GOTO']            = "Перейти";
$MESS ['CATAPULTO_DELIVERY_LBL_toOrders']        = "К заказам";
$MESS ['CATAPULTO_DELIVERY_LBL_toSunc']          = "К синхронизации модуля";
$MESS ['CATAPULTO_DELIVERY_LBL_toMassOrders']    = "К массовой отправке заказов";
$MESS ['CATAPULTO_DELIVERY_LBL_defModeO']        = "Заказа";
$MESS ['CATAPULTO_DELIVERY_LBL_defModeG']        = "1 Товара";
$MESS ['CATAPULTO_DELIVERY_LBL_ALWAYS']          = "Всегда";
$MESS ['CATAPULTO_DELIVERY_LBL_ONLYMODULE']      = "Доставка CATAPULTO";
$MESS ['CATAPULTO_DELIVERY_LBL_NONDS']           = "Без НДС";
$MESS ['CATAPULTO_DELIVERY_LBL_PAYMENT_CASH']    = "Оплата наличными";
$MESS ['CATAPULTO_DELIVERY_LBL_PAYMENT_CARD']    = "Картой на месте";
$MESS ['CATAPULTO_DELIVERY_LBL_PAYMENT_BILL']    = "Предоплата";
$MESS ['CATAPULTO_DELIVERY_LBL_AUTHORIZED']      = "Вы успешно авторизовались";
$MESS ['CATAPULTO_DELIVERY_LBL_NOTAUTHORIZED']   = "Ошибка авторизации. Проверьте введенные данные. ";
$MESS ['CATAPULTO_DELIVERY_LBL_CHECKEDIP']       = "Запрос сервера произведен с ip ";
$MESS ['CATAPULTO_DELIVERY_LBL_NOTCHECKEDIP']    = "Невозможно определить ip сервера, отправившего запрос.";
$MESS ['CATAPULTO_DELIVERY_LBL_REALLYDELOGIN']   = "Вы собираетесь разлогиниться в модуле. Службы доставки, возможность отправки заказа и синхронизация - все будет отключено. Продолжить?";
$MESS ['CATAPULTO_DELIVERY_LBL_ALL']             = "Все";
$MESS ['CATAPULTO_DELIVERY_LBL_PVZ']             = "Пункты самовывоза";
$MESS ['CATAPULTO_DELIVERY_LBL_COURIER']         = "Курьерская доставка";
$MESS ['CATAPULTO_DELIVERY_LBL_POSTAMAT']        = "Постаматы";
$MESS ['CATAPULTO_DELIVERY_LBL_PVZ_SHORT']       = "ПВЗ";
$MESS ['CATAPULTO_DELIVERY_LBL_COURIER_SHORT']   = "Курьерская";
$MESS ['CATAPULTO_DELIVERY_LBL_NP']              = "Наложенный платеж";
$MESS ['CATAPULTO_DELIVERY_LBL_COD']             = "Предоплата магазину";
$MESS ['CATAPULTO_DELIVERY_LBL_PAY_TYPE_NP']     = "Оплата товаров";
$MESS ['CATAPULTO_DELIVERY_LBL_PAY_TYPE_COD']    = "Оплата доставки";
$MESS ['CATAPULTO_DELIVERY_LBL_PAY_TYPE_NP_COD'] = "Оплата товаров и доставки";
$MESS ['CATAPULTO_DELIVERY_LBL_DOOR']            = "От двери";
$MESS ['CATAPULTO_DELIVERY_LBL_WAREHOUSE']       = "На склад";
$MESS['CATAPULTO_DELIVERY_LBL_TESTMODE']         = "Работа в тестовом режиме";
$MESS['CATAPULTO_DELIVERY_FAQ_TESTMODE_TITLE']   = "- Тестовый аккаунт";
$MESS['CATAPULTO_DELIVERY_FAQ_TESTMODE_DESCR']   = "<p>Модуль поддерживает работу с тестовым контуром: вы можете авторизоваться с тестовым ключом, чтобы проверить его работу – в таком случае рядом с аккаунтом будет надпись \"Работа в тестовом режиме\".</p>";
$MESS['CATAPULTO_DELIVERY_LBL_MARKUP_T0']        = "Фиксированная";
$MESS['CATAPULTO_DELIVERY_LBL_MARKUP_T1']        = "Процент от стоимости доставки (%)";

$MESS['CATAPULTO_DELIVERY_LBL_DELIVERY_MARKUP_N'] = "Нет";
$MESS['CATAPULTO_DELIVERY_LBL_DELIVERY_MARKUP_R'] = "₽";
$MESS['CATAPULTO_DELIVERY_LBL_DELIVERY_MARKUP_P'] = "%";

$MESS['CATAPULTO_DELIVERY_LBL_STARTWID_COURIER']             = "Курьером до двери";
$MESS['CATAPULTO_DELIVERY_LBL_STARTWID_MAP']                 = "Пункт выдачи";
$MESS['CATAPULTO_DELIVERY_LBL_defaultSenderTerminal_SETALL'] = "Установить отправку для всех курьерских служб";

//Настройки складов
$MESS['CATAPULTO_DELIVERY_WH_NAME']                          = 'Склад №';
$MESS['CATAPULTO_DELIVERY_WH_REMOVE']                        = 'Удалить склад';
$MESS['CATAPULTO_DELIVERY_WH_ADD_BTN']                       = 'Добавить';
$MESS['CATAPULTO_DELIVERY_WH_SWITCH_ON']                     = 'Включен';
$MESS['CATAPULTO_DELIVERY_WH_COORDS']                        = 'Координаты:';
$MESS['CATAPULTO_DELIVERY_WH_COORDS_HINT']                   = '(широта, долгота):';
$MESS['CATAPULTO_DELIVERY_WH_BXLOC']                         = 'Город отправителя:';
$MESS['CATAPULTO_DELIVERY_WH_BXLOC_HINT']                    = 'Город склада, из которого вы отправляете заказы.';
$MESS['CATAPULTO_DELIVERY_WH_FREE_DELIVERY_BXLOC_HINT']      = 'Здесь указан список населённых пунктов и регионов, куда действует бесплатная доставка с этого склада. Если бесплатная доставка не зависит от того, где находится получатель, то просто не указывайте здесь ничего.';
$MESS['CATAPULTO_DELIVERY_WH_CATAPULTO_CITY']                = 'ID города отправителя:';
$MESS['CATAPULTO_DELIVERY_WH_CATAPULTO_CITY_HINT']           = 'ID города из которого вы отправляете заказы. Уточните данную информацию у менеджера Catapulto.';
$MESS['CATAPULTO_DELIVERY_WH_CATAPULTO_CITY_INDEX']          = 'Индекс отправителя:';
$MESS['CATAPULTO_DELIVERY_WH_CATAPULTO_CITY_INDEX_HINT']     = 'Индекс города из которого вы отправляете заказы. Уточните данную информацию у менеджера Catapulto.';
$MESS['CATAPULTO_DELIVERY_WH_CATAPULTO_CONTACT']             = 'ID отправителя по умолчанию:';
$MESS['CATAPULTO_DELIVERY_WH_CATAPULTO_CONTACT_HINT']        = "ID контакта из адресной книги в личном кабинете Catapulto, который будет выбран отправителем в модуле.<br>Уточнить нужное значение для этой настройки можно у менеджера Catapulto";
$MESS['CATAPULTO_DELIVERY_WH_POA_ENABLED']                   = 'Включить отправку по доверенности (для "Деловые линии"):';
$MESS['CATAPULTO_DELIVERY_WH_POA_ENABLED_HINT']              = 'Поля ниже заполняются, если эта настройка включена';
$MESS['CATAPULTO_DELIVERY_WH_POA_FROM_DATA_FIO']             = 'ФИО контактного лица:';
$MESS['CATAPULTO_DELIVERY_WH_POA_FROM_DATA_PASSPORT_SERIA']  = 'Серия паспорта (4 цифры):';
$MESS['CATAPULTO_DELIVERY_WH_POA_FROM_DATA_PASSPORT_NUMBER'] = 'Номер паспорта (6 цифр):';
$MESS['CATAPULTO_DELIVERY_WH_POA_FROM_DATA_PASSPORT_DATE']   = 'Дата выдачи (дд.мм.гггг):';
$MESS['CATAPULTO_DELIVERY_WH_POA_FROM_DATA_EMAIL']           = 'Email для отправки доверенности (не обязательно):';
$MESS['CATAPULTO_DELIVERY_WH_SENDER']                        = 'Отправитель';
$MESS['CATAPULTO_DELIVERY_WH_OPERATORS']                     = 'Способ отправки и терминалы отправки';
$MESS['CATAPULTO_DELIVERY_WH_DELIVERY_FROM_DEFAULT']         = 'Способ передачи отправлений по умолчанию:';
$MESS['CATAPULTO_DELIVERY_WH_FROM_DOOR']                     = 'От двери';
$MESS['CATAPULTO_DELIVERY_WH_FROM_WH']                       = 'На склад';
$MESS['CATAPULTO_DELIVERY_WH_DEFAULT_STORE']                 = 'Склад по умолчанию';
$MESS['CATAPULTO_DELIVERY_WH_CONFIRM_REMOVE']                = "Вы уверены, что хотите удалить склад?\nЕсли ранее были отправлены заявки с этим складом, то в них теперь будет \"Не выбран\" вместо указания склада.";
$MESS['CATAPULTO_DELIVERY_WH_INPUT_NAME']                    = "Введите название";
$MESS['CATAPULTO_DELIVERY_WH_CLEAN_OUT']                     = "Очистить";
$MESS['CATAPULTO_DELIVERY_WH_NOT_FOUND']                     = "Ничего не найдено";
$MESS['CATAPULTO_DELIVERY_WH_ERROR']                         = "Ошибка";


// logs & debug
$MESS ['CATAPULTO_DELIVERY_LBL_CLEAR']   = "Очистить лог";
$MESS ['CATAPULTO_DELIVERY_LBL_NOLOGS']  = "Данные лога не обнаружены";
$MESS ['CATAPULTO_DELIVERY_LBL_openLog'] = "Открыть лог запросов к API";
$MESS ['CATAPULTO_DELIVERY_LBL_haslog']  = "Имеются данные запросов к API";
$MESS ['CATAPULTO_DELIVERY_LBL_nolog']   = "Запросы к API не логировались";
$MESS ['CATAPULTO_DELIVERY_LBL_NOCACHE'] = "Кэш отключен";
/**
 * Hints -------------------------------
 */
// options
$MESS ['CATAPULTO_DELIVERY_HINT_showInOrders'] = "Отображать в заказах";

// headers
$MESS ['CATAPULTO_DELIVERY_HDR_common']                = "Общие";
$MESS ['CATAPULTO_DELIVERY_HDR_sender']                = "Отправитель по умолчанию";
$MESS ['CATAPULTO_DELIVERY_HDR_defaultSenderTerminal'] = "Способ отправки, получения и терминалы отправки по умолчанию";
$MESS ['CATAPULTO_DELIVERY_HDR_defaultCargo']          = "Габариты по умолчанию";
$MESS ['CATAPULTO_DELIVERY_HDR_orderProps']            = "Свойства заказа";
$MESS ['CATAPULTO_DELIVERY_HDR_widget']                = "Настройки виджета";
$MESS ['CATAPULTO_DELIVERY_HDR_payments']              = "Настройки соответствия платежных систем";
$MESS ['CATAPULTO_DELIVERY_HDR_colors']                = "Настройки цветового оформления виджета";
$MESS ['CATAPULTO_DELIVERY_HDR_warehouse']             = "Склады для отправки отправлений";
$MESS ['CATAPULTO_DELIVERY_HDR_service']               = "Сервисные свойства";
$MESS ['CATAPULTO_DELIVERY_HDR_delivery']              = "Настройки доставки";
$MESS ['CATAPULTO_DELIVERY_HDR_markups']               = "Управление наценками";
$MESS ['CATAPULTO_DELIVERY_HDR_statuses']              = "Синхронизация статусов";
$MESS ['CATAPULTO_DELIVERY_HDR_debug_request']         = "Логирование запросов к API";
// tabs
$MESS['CATAPULTO_DELIVERY_TAB_FAQ']           = "FAQ";
$MESS['CATAPULTO_DELIVERY_TAB_TITLE_FAQ']     = "FAQ";
$MESS['CATAPULTO_DELIVERY_TAB_AUTH']          = "Авторизация";
$MESS['CATAPULTO_DELIVERY_TAB_TITLE_AUTH']    = "Введите авторизационные данные";
$MESS['CATAPULTO_DELIVERY_TAB_RIGHRTS']       = "Права";
$MESS['CATAPULTO_DELIVERY_TAB_TITLE_RIGHRTS'] = "Управление правами на доступ к модулю";
$MESS['CATAPULTO_DELIVERY_TAB_DEBUG']         = "Отладка";
$MESS['CATAPULTO_DELIVERY_TAB_TITLE_DEBUG']   = "Управление логированием и отладочная информация";
$MESS['CATAPULTO_DELIVERY_TAB_WH_TITLE']      = 'Склады';
$MESS['CATAPULTO_DELIVERY_TAB_WH_HDR']        = 'Управление складами';

// buttons
$MESS['CATAPULTO_DELIVERY_BTN_AUTH']        = "Авторизоваться";
$MESS['CATAPULTO_DELIVERY_BTN_DELOGIN']     = "Разлогиниться";
$MESS ['CATAPULTO_DELIVERY_BTN_CLEARCACHE'] = "Сбросить кэш";

// errors & warnings
$MESS['CATAPULTO_DELIVERY_ERROR_OPTSAVE_TITLE']            = "Ошибка сохранения опций.";
$MESS['CATAPULTO_DELIVERY_ERROR_SYNC_DATA_REQUIRED_TITLE'] = "Необходимо провести загрузку и синхронизацию внешних данных";

$MESS['CATAPULTO_DELIVERY_ERROR_SYNC_DATA_REQUIRED_DESCR']
    = "Для корректной работы модуля необходимо произвести загрузку и синхронизацию внешних данных по доступным вариантам доставки и складам отправки отправлений.<br>В противном случае модуль не сможет работать и служба доставки модуля не будет выводиться на странице оформления заказа.<br><a href='/bitrix/admin/catapulto_delivery_sync_data.php' target='_blank'><b>Перейти к загрузке и синхронизации внешних данных</b></a>";

$MESS['CATAPULTO_DELIVERY_ERROR_OPTSAVE_UNGIVEN']  = "значение не указано.";
$MESS['CATAPULTO_DELIVERY_ERROR_NODELIVERY_TITLE'] = "Служба доставки не найдена.";
$MESS['CATAPULTO_DELIVERY_ERROR_NODELIVERY_DESCR']
                                                   = "Служба доставки модуля не найдена или неактивна.<br><a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>Добавьте автоматизированную службу доставки</a> с кодом Catapulto, руководствуясь пунктом FAQ \"Настройка службы доставки\".";

$MESS['CATAPULTO_DELIVERY_WARNING_services'] = "Процедура обработки Услуг";
$MESS['CATAPULTO_DELIVERY_WARNING_tarifs']   = "Процедура обработки Услуг";

/**
 * HELPERS -------------------------------
 */

// statuses
$MESS['CATAPULTO_DELIVERY_HELPER_markPayed']
    = "При получении заказом финального статуса \"Отправление выдано покупателю\" его можно отметить оплаченным. <br><br>Опция может быть полезна для автоматизации бизнес-процессов, в случаях когда клиент оплачивает заказ наложенным платежом при получении у курьера, либо на точке самовывоза.";
$MESS['CATAPULTO_DELIVERY_HELPER_useTrackingStatuses']
    = "<b>Внимательно ознакомьтесь с разделом документации модуля \"Начало работы - Отслеживание состояний (статусов) - Статусы заказов, отгрузок и отправлений\".</b><br><br>При включении опции будут возвращаться статусы отправлений, зависящие от текущего статуса заявки в Catapulto. При выключенной опции всегда будет возвращаться статус отправления \"Информация отсутствует\".";
$MESS['CATAPULTO_DELIVERY_HELPER_blockingStatus']
    = "При выборе какого-либо статуса в этой опции модуль перестанет обновлять статусы заказа Битрикса согласно настройкам соответствий статусов блока \"Синхронизация статусов\", если заказ Битрикса уже находится в этом статусе. В своей таблице заявок модуль продолжит обновлять статусы заказов, успешно переданных в Catapulto.<br><br><b>Если ваш бизнес-процесс обработки заказов не требует принудительной остановки обновления статусов, не выбирайте ничего в данной опции.</b>";

// common
$MESS['CATAPULTO_DELIVERY_HELPER_showInOrders']      = "Указывает модулю, когда добавлять на страницу заказа кнопку оформления заявки на доставку модуля: она отображается либо всегда, либо только если выбрана служба доставки модуля. Это актуально, если установлено несколько модулей интеграции.";
$MESS ['CATAPULTO_DELIVERY_HELPER_dadataApikey']     = "Для работы модуля требуется ввести API ключ от сервиса DaData.<br>Пройдите регистрацию в сервисе <a href='https://dadata.ru/' target='_blank'>dadata.ru</a>, скопируйте API ключ из личного кабинета и вставьте в это поле.";
$MESS ['CATAPULTO_DELIVERY_HELPER_widgetYandexKey']     = "Для работы скриптов Яндекс.карт требуется ввод API-ключа. Получить его можно в <a href='https://yandex.ru/dev/jsapi-v2-1/doc/ru/' target='_blank' title='Перейти в Кабинет разработчика Яндекса'>Кабинете разработчика</a>.";
$MESS ['CATAPULTO_DELIVERY_HELPER_senderLocalityId'] = "ID города из которого вы отправляете заказы. Уточните данную информацию у менеджера Catapulto.";
$MESS ['CATAPULTO_DELIVERY_HELPER_senderZip']        = "Индекс города из которого вы отправляете заказы. Уточните данную информацию у менеджера Catapulto.";
$MESS ['CATAPULTO_DELIVERY_HELPER_senderCity']       = "Город из которого вы отправляете заказы.<br>Например, \"Москва\" (без кавычек)";
$MESS ['CATAPULTO_DELIVERY_HELPER_senderId']         = "ID контакта из адресной книги в личном кабинете Catapulto, который будет выбран отправителем в модуле.<br>Уточнить нужное значение для этой настройки можно у менеджера Catapulto";

//Operators
$MESS['CATAPULTO_DELIVERY_OPERATORS']            = 'Операторы доставки';
$MESS['CATAPULTO_DELIVERY_FREE_DELIVERY']        = 'Бесплатная доставка';
$MESS['CATAPULTO_DELIVERY_SEND_TYPE']            = 'Способ отправки';
$MESS['CATAPULTO_DELIVERY_TERMINAL_CODE']        = 'Код терминала отправления';
$MESS['CATAPULTO_DELIVERY_DELIVERY_TYPE']        = 'Способ получения';
$MESS['CATAPULTO_DELIVERY_DELIVERY_TYPE_HINT']   = 'Важно: способ получения для покупателя зависит от настроек виджета. Если в виджете выбран «Курьерская доставка», ПВЗ показываться не будут – даже если они выбраны у курьерской службы.';
$MESS['CATAPULTO_DELIVERY_DELIVERY_TYPE_SELECT'] = 'Выберите способ получения';

// delivery
$MESS['CATAPULTO_DELIVERY_HELPER_noPVZnoOrder']             = "Модуль не даст оформить заказ, если не выбран способ доставки в виджете.";
$MESS['CATAPULTO_DELIVERY_HELPER_termIncrease']             = "Увеличивает срок доставки на указанное количество дней. <br><br>Используется для учета срока на комплектацию и отправку заказа в службу доставки.";
$MESS['CATAPULTO_DELIVERY_HELPER_smsAmount']                = "Включает услугу SMS-информирования получателя при расчете стоимости. При наличии услуги Catapulto дополнительно уведомляет получателя о статусе отправления по SMS.";
$MESS['CATAPULTO_DELIVERY_HELPER_deliveryDefaultPrice']     = "Стоимость доставки по умолчанию, которая показывается на странице оформления заказа до момента выбора конкретного способа доставки в виджете.";
$MESS['CATAPULTO_DELIVERY_HELPER_mindEnsurance']            = "Включает страхование при расчете стоимости и при отправке заявки из администратовной части";
$MESS['CATAPULTO_DELIVERY_HELPER_needReselect']             = "Модуль не даст оформить заказ, если после выбора способа доставки был поменян способ оплаты заказа. В таком случае будет показана ошибка с просьбой покупателя заново выбрать способ доставки в виджете Catapulto";
$MESS['CATAPULTO_DELIVERY_HELPER_isFitting']                = "Включить возможность примерки. При выборе примерки в виджете отобразятся только тарифы доставки с доступной примеркой.";
$MESS['CATAPULTO_DELIVERY_HELPER_fittingDefaultEnabled']    = "По умолчанию режим примерки включен при запуске виджета.";
$MESS['CATAPULTO_DELIVERY_HELPER_partialRedemptionEnabled'] = "Включить услугу частичного выкупа по умолчанию.";
$MESS['CATAPULTO_DELIVERY_HELPER_updateOrderDelivery']      = "Включить автоматическое обновление доставки и оплаты заказа при выборе другого способа доставки в форме отправления заявки в Catapulto в административной части.";
$MESS['CATAPULTO_DELIVERY_HELPER_deliveryPvzMarkupType']       = "Выберите, как рассчитывается наценка: фиксированной суммой в рублях, процентом от стоимости доставки или не применяется вовсе.";
$MESS['CATAPULTO_DELIVERY_HELPER_deliveryPvzMarkupValue']      = "Укажите размер наценки в соответствии с выбранным типом: сумму в рублях или значение процента от стоимости доставки.";
$MESS['CATAPULTO_DELIVERY_HELPER_deliveryCourierMarkupType']   = "Выберите, как рассчитывается наценка: фиксированной суммой в рублях, процентом от стоимости доставки или не применяется вовсе.";
$MESS['CATAPULTO_DELIVERY_HELPER_deliveryCourierMarkupValue']  = "Укажите размер наценки в соответствии с выбранным типом: сумму в рублях или значение процента от стоимости доставки.";

// widget
$MESS['CATAPULTO_DELIVERY_HELPER_pvzPicker']
                                                               = "В это свойство будет сохранен выбранный пункт выдачи. <br><br>Должно использоваться <b>текстовое, не служебное свойство заказа</b>, например, \"Адрес доставки\". <br><br>Коды свойств берутся из <a href='/bitrix/admin/sale_order_props.php' target='_blank'>настроек свойств заказа</a>. У всех типов плательщиков должен быть задан одинаковый код.";
$MESS['CATAPULTO_DELIVERY_HELPER_requireFullAddress']           = 'При включении опции в виджете пользователь должен будет вводить полный адрес доставки. При этом пункты выдачи во вкладке с терминалами будут показаны как и раньше, даже при вводе только города. Если не введён полный адрес, то курьерские тарифы не будут показаны, вместо них будет выводиться предупреждение с просьбой ввести полный адрес.';
$MESS['CATAPULTO_DELIVERY_HELPER_enDadataSuggestions']
                                                               = "В выбранном свойстве сохранения адреса будут подключены подсказки ввода адреса от Dadata.";
$MESS['CATAPULTO_DELIVERY_HELPER_ymapsAPIKey']
                                                               = "Для работы скриптов Яндекс.карт требуется ввод API-ключа. Получить его можно в <a href='https://yandex.ru/dev/maps/jsapi/doc/2.1/quick-start/index.html' target='_blank'>Кабинете разработчика</a>. <br><br>Учтите, что если у вас подключается несколько скриптов Яндекс.карт - не факт, что модуль может на них повлиять.";
$MESS['CATAPULTO_DELIVERY_HELPER_enMapOpenMode']               = "Выбранная вкладка будет открываться по умолчанию при расчёте.";
$MESS['CATAPULTO_DELIVERY_HELPER_runWidgetOnStart']            = "При активации варианта доставки Catapulto на странице оформления заказа виджет запускается сразу.";
$MESS['CATAPULTO_DELIVERY_HELPER_ctptGeoEmptyMessage']         = "Текст, указанный здесь, будет добавлен к тексту ошибки в виджете, если введенный адрес в поиске не будет найден.";
$MESS['CATAPULTO_DELIVERY_HELPER_ctptCustomDefaultWidgetText'] = "Текст, указанный здесь, будет отображаться на странице оформления заказа в виджете до выбора конкретного варианта доставки.";
// payments
$MESS['CATAPULTO_DELIVERY_HELPER_checkPayed']
    = "Опция определяет поведение флага \"Заказ оплачен\" в форме отправки заявки. <br><br>По умолчанию (не проставлен) заказ считается оплаченным, если у него выставлены платежные системы, не являющиеся наличными или картой. При установке - флаг \"Заказ оплачен\" будет выставлен по умолчанию только в случае, если все платежные системы в заказе имеют статус \"Оплачено\".<br><br>Не забывайте, что флаг \"Заказ оплачен\" всегда может быть вручную изменен в форме оформления заказа.";

$MESS['CATAPULTO_DELIVERY_HELPER_widgetDeliveryTypes'] = "Ограничение способов доставки в виджете. Данная опция позволяет установить доступными определенные способы доставки в виджете (например: только курьерская доставка).";
$MESS['CATAPULTO_DELIVERY_HELPER_payTypeNP']           = "<p><b>Оплата товаров</b> — при выборе платёжной системы из списка, расчёт запускается с услугой NP (pod_amount).</p>
<p><b>Оплата доставки</b> — при выборе платёжной системы из списка, расчёт запускается с услугой COD (cod_amount).</p>
</p><b>Оплата товаров и доставки</b> — при выборе платёжной системы из списка, расчёт запускается как с услугой NP, так и с услугой COD.</p>";

//colors
$MESS['CATAPULTO_DELIVERY_HELPER_search_button_background'] = "Настройте цвет фона кнопки \"Найти\" в виджете. Если оставить пустым, будет применен цвет по умолчанию. Введите шестнадцатеричный код цвета, например #FFFFFF.";
$MESS['CATAPULTO_DELIVERY_HELPER_search_button_border'] = "Настройте цвет окантовки кнопки \"Найти\" в виджете. Если оставить пустым, будет применен цвет по умолчанию. Введите шестнадцатеричный код цвета, например #FFFFFF.";
$MESS['CATAPULTO_DELIVERY_HELPER_search_button_text'] = "Настройте цвет текста кнопки \"Найти\" в виджете. Если оставить пустым, будет применен цвет по умолчанию. Введите шестнадцатеричный код цвета, например #FFFFFF.";
$MESS['CATAPULTO_DELIVERY_HELPER_search_button_hover'] = "Настройте цвет фона кнопки \"Найти\" в виджете. Если оставить пустым, будет применен цвет по умолчанию. Введите шестнадцатеричный код цвета, например #FFFFFF.";
$MESS['CATAPULTO_DELIVERY_HELPER_primary_widget_color'] = "Настройте основной цвет виджета (цвет активной вкладки, например). Если оставить пустым, будет применен цвет по умолчанию. Введите шестнадцатеричный код цвета, например #FFFFFF.";
$MESS['CATAPULTO_DELIVERY_HELPER_cluster_color'] = "Настройте цвет кластеров терминалов на карте. Если оставить пустым, будет применен цвет по умолчанию. Введите шестнадцатеричный код цвета, например #FFFFFF.";


// service
$MESS['CATAPULTO_DELIVERY_HELPER_timeout']       = "Максимальное время ожидания ответа при запросе к серверу Catapulto";
$MESS['CATAPULTO_DELIVERY_HELPER_isTest']        = "Включение обращения к тестовому контуру API. В виджете будет вывод всех имеющихся точек ПВЗ, отправленные заказы не появятся в личном кабинете. Используется только для первоначальной отладки работы модуля.";
$MESS['CATAPULTO_DELIVERY_HELPER_customApiUrl']  = "Если необходимо, укажите здесь альтернативный адрес для отправки запросов к API.";
$MESS['CATAPULTO_DELIVERY_HELPER_customWSSUrl']  = "Если необходимо, укажите здесь альтернативный адрес для отправки запросов к WS. Пример: \"wss://ws.catapulto.ru\"";
$MESS['CATAPULTO_DELIVERY_HELPER_debug']         = "Включение логирования работы модуля. Результаты будут отражены на вкладке \"Отладка\".";
$MESS['CATAPULTO_DELIVERY_HELPER_client_notify'] = "После отправки заявки и появления ссылки отслеживания заказа покупателю отправляется уведомление о возможности отслеживать заказ с ссылкой на статус заявки.";

/**
 * FAQ -------------------------------
 */
// Auth
$MESS['CATAPULTO_DELIVERY_API_KEY']     = "API ключ";
$MESS['CATAPULTO_DELIVERY_EN_TESTMODE'] = "Работа в тестовом режиме";


$MESS['CATAPULTO_DELIVERY_FAQ_HDR_MODULE'] = "О модуле";

$MESS['CATAPULTO_DELIVERY_FAQ_ABOUT_TITLE'] = "- Для чего нужен модуль";
$MESS['CATAPULTO_DELIVERY_FAQ_ABOUT_DESCR'] = "<p>Рассказываем о модуле.</p>";

$MESS['CATAPULTO_DELIVERY_FAQ_HIW_TITLE'] = "- Как работает модуль";
$MESS['CATAPULTO_DELIVERY_FAQ_HIW_DESCR'] = "<p>Рассказываем как оно работает.</p>";

$MESS['CATAPULTO_DELIVERY_FAQ_ACCESS_TITLE'] = "- Как получить доступ";
$MESS['CATAPULTO_DELIVERY_FAQ_ACCESS_DESCR'] = "<p>Обратитесь к менеджеру Catapulto для заключения договора и получения доступа к API по электронной почте <a href=\"mailto:im@catapulto.ru\">im@catapulto.ru</a> или по номеру телефона <a href=\"tel:+78005552241\">8-800-555-22-41</a>.</p>";

$MESS['CATAPULTO_DELIVERY_FAQ_HDR_ABOUT'] = "Начало работы";

$MESS['CATAPULTO_DELIVERY_FAQ_TURNINGON_TITLE'] = "- Включение функционала";
$MESS['CATAPULTO_DELIVERY_FAQ_TURNINGON_DESCR'] = "<p>Как вообще включить эту штуку</p>";

$MESS['CATAPULTO_DELIVERY_FAQ_HDR_HELP'] = "Справочная информация";

$MESS['CATAPULTO_DELIVERY_FAQ_EXTERNAL'] = "Ознакомиться инструкциями по настройке модуля и справочной информацией можно на сайте Catapulto.<br><a href='https://catapulto.ru/help/module-1c-bitrix-us/' target='_blank'>Документация</a>";

// defaultSenderTerminal
$MESS['CATAPULTO_DELIVERY_FAQ_defaultSenderTerminal_TITLE'] = "- Показать описание настроек";
$MESS['CATAPULTO_DELIVERY_FAQ_defaultSenderTerminal_DESCR']
                                                            = "<p>Данная группа настроек предназначена для управления логистикой заказов: здесь настраивается, каким образом заказ передается в курьерскую службу (от двери или на склад), указывается код терминала (ID) для отгрузки заказа на склад, задаются условия предоставления бесплатной доставки (в зависимости от суммы заказа и типа доставки), а так же определяется какие варианты получения заказа будут доступны покупателю.</p>";

// defaultGabarites
$MESS['CATAPULTO_DELIVERY_FAQ_defaultCargo_TITLE'] = "- О габаритах";
$MESS['CATAPULTO_DELIVERY_FAQ_defaultCargo_DESCR'] = "<p>Данная группа настроек предназначена для определения габаритов тех заказов, где присутствуют товары без заполненных размеров и/или веса. Здесь можно задать значения, которые будут использоваться по умолчанию. Можно также настроить порядок применения этих габаритов: либо они будут применяться для всего заказа, либо для каждого товара.</p>
<p>При возникновении ситуации смешанных заказов, когда в корзине присутствуют как товары без габаритов, так и с заданными параметрами, проверяется общий размер и вес тех товаров, у которых габариты заданы и берется большее из рассчитанных и заданных по умолчанию значений.</p>
<p>Все габариты и вес берутся из штатных параметров Торгового каталога у конкретных товаров.</p>";

//markups
$MESS['CATAPULTO_DELIVERY_FAQ_markups_TITLE'] = "- О наценках";
$MESS['CATAPULTO_DELIVERY_FAQ_markups_DESCR']
                                              = "<p>Данная группа настроек нужна для того, чтобы Вы смогли добавлять дополнительную наценку за услугу доставки. Для этого следует у каждого оператора доставки установить значение наценки а также указать тип наценки.</p><p>Вы можете установить наценку на стоимость доставки, указав фиксированную сумму в рублях либо процент от стоимости доставки</p>";

// statuses
$MESS['CATAPULTO_DELIVERY_FAQ_statuses_TITLE'] = "- О статусах";
$MESS['CATAPULTO_DELIVERY_FAQ_statuses_DESCR'] = "<p>Данная группа настроек нужна для того, чтобы оперативно отслеживать статусы заказов. Раз в 30 минут запрашивается информация по статусам отправленных заявок. При получении ответа заказы выставятся в указанные статусы если они приняты, или по каким-то причинам отклонены. Также отслеживаются статусы доставки заказов.</p>
<p>Рекомендуется создать статусы заказа, чтобы удобнее было отслеживать по ним состояние заявок, а так же задать специальные правила в <a href='/bitrix/admin/type_admin.php' target='_blank'>Типах почтовых событий</a>, чтобы отсылать письма о смене статусов заказа только менеджерам магазина, а не покупателям. Создавать статус в Битриксе под каждый статус в модуле не обязательно: действуйте согласно вашей бизнес-схеме.</p>";


// orderProps
$MESS['CATAPULTO_DELIVERY_FAQ_orderProps_TITLE'] = "- О свойствах заказа";
$MESS['CATAPULTO_DELIVERY_FAQ_orderProps_DESCR'] = "<p>Эта группа настроек отвечает за экспорт свойств заказа в форму оформления заявки через указание <a href='/bitrix/admin/sale_order_props.php' target='_blank'>кодов свойств заказа</a>.</p>
<p>Если в магазине есть несколько типов плательщиков, в аналогичных свойствах нужно задать одинаковый символьный код (например, код FIO для Ф.И.О. Физического лица и Контактного лица Юридического лица).</p>";


// payments
$MESS['CATAPULTO_DELIVERY_FAQ_payments_TITLE'] = "- Пояснение к платежным системам";
$MESS['CATAPULTO_DELIVERY_FAQ_payments_DESCR'] = "<p>Настройка предназначена для корректности учета способа оплаты, выбранного покупателем (клиентом сайта).</p>
<p>Необходимо указать какие именно платежные системы, установленные на сайте, считаются оплатой наличными, а какие - оплатой картой при получении клиентом сайта заказа у курьера или на пункте самовывоза. Иными словами, при выборе каких платежных систем считается, что оплата заказа производится наложенным платежом при получении.</p>
<p>Если имеются платежные системы, не подразумевающие наложенный платеж: оплата пластиковой картой на сайте, выставление счета, банковский перевод и т.д. - не отмечайте их в этих селекторах! Все это предоплатные платежные системы, подразумевающие оплату клиентом сайта напрямую интернет-магазину, без приема оплаты заказа наложенным платежом.</p>";


// logging
$MESS['CATAPULTO_DELIVERY_FAQ_debug_request_TITLE'] = "- Логирование API";
$MESS['CATAPULTO_DELIVERY_FAQ_debug_request_DESCR']
                                                    = "<p>Встроенный функционал отладки позволяет получать данные по запросам и расчетам, в том числе - тело запроса и ответа к API.<br>Логирование запросов к API позволит ознакомиться с ответом от сервера Catapulto для проверки расчетов, синхронизации и отправки данных. Не рекомендуется включать логирование на постоянной основе ввиду разрастания файла лога. Учтите, что в случае кэширования запрос выполнен не будет.</p>";


$MESS['CATAPULTO_DELIVERY_FAQ_TROUBLES_TITLE'] = "- Частые проблемы";
$MESS['CATAPULTO_DELIVERY_FAQ_TROUBLES_DESCR'] = "<p><strong>Проблемы со стоимостями доставки</strong></p>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Расчет не совпадает с личным кабинетом.</a>
		<div class='ipol_inst'>Внимательно ознакомьтесь с пунктом FAQ \"Особенности расчета стоимости доставки\": в нем детально расписано, как считается вес и габариты товара. Убедитесь, что вы работаете с боевыми доступами. Так же убедитесь, что вы добавили корректную службу доставки (CATAPULTO).</div>
    </div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Доставка не считается.</a>
		<div class='ipol_inst'>Убедитесь, что вы не отключили все услуги доставки в группе опций \"Настройки услуг доставки\". Сбросьте кэш модуля и проведите логирование запроса - возможно, API недоступен или выдает ошибку (см. \"Отладка\").</div>
    </div>

<p><strong>Проблемы в оформлении заказа</strong></p>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Служба доставки не отображается.</a>
		<div class='ipol_inst'><ul>
			<li>Убедитесь, что вы авторизованы в модуле.</li>
			<li>Убедитесь, что в настройках модуля определен Город отправления.</li>
			<li>Проверьте активность у <a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>службы доставки</a> и ее профилей.</li>
			<li>Проверьте выставленные ограничения у <a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>службы доставки</a> и ее профилей.</li>
			<li>Проверьте доступность службы доставка в настройках <a href='/bitrix/admin/sale_pay_system.php' target='_blank'>платежных систем</a>.</li>
			<li>Проверьте, что у службы доставки есть профили. Если их нет - переавторизуйтесь в модуле.</li>
			<li>Проверьте, что вы добавили службу доставки с корректным SID (CATAPULTO)</a>.</li>
		</ul></div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Показывается кнопка \"расcчитать стоимость\" или доставка не рассчитывается, пока не выбрана.</a>
		<div class='ipol_inst'><ul>
			<li>Новый компонент: В параметрах компонента оформления заказа (sale.order.ajax) необходимо поставить опцию \"Когда рассчитывать доставки с внешними системами расчета\" в \"Рассчитывать сразу\" - учтите, что это повысит время генерации страницы.</li>
			<li>Старый компонент: В параметрах компонента оформления заказа (sale.order.ajax) необходимо поставить галочку \"Рассчитывать стоимость доставки сразу\".</li>
        </ul></div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не отображается виджет выбора услуг.</a>
		<div class='ipol_inst'><ul>
			<li>Убедитесь, что есть активные услуги, определенные при расчете доставки (\"Настройки услуг доставки\").</li>
			<li>Убедитесь, что в консоли нет javascript-ошибок.</li>
			<li>Убедитесь, что используется актуальный стандартный компонент оформления заказа со стандартным шаблоном.</li>
			<li>Есть незначительная вероятность, что виджет не может определить место, куда поставить свою разметку - можно попробовать воспользоваться опцией \"ID тега, куда вставлять виджет выбора услуг\".</li>
        </ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Возвращается ошибка при расчете доставки, если сумма доставки - 0.</a>
		<div class='ipol_inst'>Это ошибка Битрикса. При применении изменений все сохранится корректно.</div>
	</div>
	
<p><strong>Проблемы с виджетом выбора ПВЗ</strong></p>
    <div class='ipol_subFaq'>
        <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не показывается кнопка выбора ПВЗ.</a>
        <div class='ipol_inst'><ul>
            <li>Убедитесь, что вы используете актуальный стандартный компонент и шаблон оформления заказа Битрикса.</li>
            <li>Убедитесь, что в консоли нет js-ошибок.</li>
            <li>Если вы используете опцию \"ID тега, куда привязывать ссылку \"Выбрать пункт самовывоза\"\" - убедитесь в ее необходимости и корректности: она предназначена только для опытных программистов.</li>
        </ul></div>
    </div>
    
    <div class='ipol_subFaq'>
        <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; В виджет не подгружается город, указанный в компоненте оформления заказа.</a>
        <div class='ipol_inst'>Это штатный режим работы, см. \"Виджет выбора ПВЗ и самовывоз\".</div>
    </div>
    
    <div class='ipol_subFaq'>
        <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Выбранный клиентов пункт самовывоза находится в другом городе, нежели указанный в заказе.</a>
        <div class='ipol_inst'>Это штатный режим работы, см. \"Виджет выбора ПВЗ и самовывоз\".</div>
    </div>

<p><strong>Проблемы в административной части</strong></p>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Город отправления не определяется</a>
		<div class='ipol_inst'><ul>
			<li>Убедитесь, что <a href='/bitrix/admin/settings.php?lang=ru&mid=sale' target='_blank'>настройка</a> задана.</li>
		</ul></div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не сохраняется выбранный ПВЗ или услуга</a>
		<div class='ipol_inst'><ul>
			<li>Убедитесь, что <a href='/bitrix/admin/sale_order_props.php' target='_blank'>списке свойств заказа</a> есть свойства с кодами CATAPULTO_DELIVERY_RECEIVER_CONTACT_ID, CATAPULTO_DELIVERY_RECEIVER_CITY,  CATAPULTO_DELIVERY_SENDER_CONTACT_ID и CATAPULTO_DELIVERY_RATE_RESULT_ID.</li>
			<li>Убедитесь, что компонент оформления заказа работает в режиме совместимости (параметр компонента \"Режим совместимости для предыдущего шаблона\").</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не отображается кнопка \"CATAPULTO доставка\" для оформления заявки.</a>
		<div class='ipol_inst'><ul>
		<li>Убедитесь, что вы авторизованы в модуле.</li>
		<li>Убедитесь, что вы находитесь на странице детальной информации о заказа (sale_order_detail.php), а не его редактирования.</li>
		<li>Убедитесь, что в консоли (страница оформления заказа -> F12) нет ошибок в JavaScript.</li>
		<li>Если задана настройка \"Отображать кнопку заявки в заказах\" в \"Доставка CATAPULTO\" - что доставкой выбрана служба доставки модуля.</li>
		<li>Проверьте, что для группы пользователей, от которых идет попытка оформить заявку, стоит разрешение во вкладке \"Права\".</li>
		</ul></div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не отсылается заявка.</a>
		<div class='ipol_inst'><ul>
		<li>Убедитесь, что исправлены все возможные ошибки в полях (неверный формат телефона, заполнены все необходимые поля, определен Город отправления).</li>
		<li>Удалите (замените) из полей символы кавычек, углобые скобки, итп.</li>
		<li>Убедитесь, что на странице оформления доставок после очистки кэша (в настройках модуля) продолжают отображаться доставки. Если нет - сервер CATAPULTO \"лежит\".</li>
		<li>Проверьте права на доступ к модулю у пользователя.</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Заявка отправилась, но не появилась в ЛК.</a>
		<div class='ipol_inst'><ul>
		<li>Убедитесь, что сервер CATAPULTO доступен (нет оповещения об этом в настройках, после очистки кэша службы доставки продолжают отображаться), иначе - нужно ждать, пока сервер не \"поднимется\".</li>
		<li>Убедитесь, что заявка была отправлена в боевом режиме.</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Проблема с выставлением оплат.</a>
		<div class='ipol_inst'>Блок оплат работает по следующим правилам:
		    <ul>
                <li>Если стоит флаг \"Заказ оплачен\"- он оплачен полностью: предоплата - стоимость товаров, способ оплаты - предоплата</li>
                <li>Если задана предоплата (полностью или частично) - заказ нельзя оформить с режимом продажи \"Частичный выкуп возможен\"</li>
            </ul>
		</div>
	</div>

<p><strong>Прочие проблемы</strong></p>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Проблемы с правами.</a>
		<div class='ipol_inst'>Если модуль выдает ошибку, связанную с правами и доступом - проверьте, выставлены ли группе пользователей, к которой принадлежит пользователь, получающий ошибку, права на полный доступ к модулю (При полном доступе админка модуля ему все равно не будет показываться, если не прописаны права к папке и файлу с настройками).</div>
	</div>
";

$MESS['CATAPULTO_DELIVERY_DELIVERY_HANDLER_TERMS']      = 'От 1 дня.';
$MESS['CATAPULTO_DELIVERY_DELIVERY_HANDLER_TERMS_HINT'] = 'Финальная стоимость доставки будет рассчитана после того, как вы выберете способ доставки.';

