<?php
$MESS ['CATAPULTO_DELIVERY_DELIVERY_NAME'] = "Курьером или в пункт выдачи";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_DESCRIPTION'] = "Доставка курьером или в пункт выдачи.";

$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_MAIN_TAB_TITLE'] = "Дополнительные настройки";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_MAIN_TAB_DESCR'] = "Дополнительные настройки службы доставки";

$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_MAIN_TAB_WAREHOUSE_ID'] = "Склад отправки отправлений";

$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_MAIN_TAB_CONFIG_DEFAULT'] = "По умолчанию (как в настройках модуля)";

$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_STATUS_TAB_TITLE'] = "Состояние службы доставки";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_STATUS_TAB_DESCR'] = "Дополнительная информация о состоянии службы доставки и текущих настройках модуля";

$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_STATUS_TAB_APIKEY']            = "Текущий apikey:";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_STATUS_TAB_SYNC_DATA']         = "Синхронизация данных:";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_STATUS_TAB_SYNC_DATA_Y']       = "<span style='color:green'>Выполнена</span>";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_STATUS_TAB_SYNC_DATA_N']       = "<span style='color:red'>Не выполнена</span> -> <a href='/bitrix/admin/catapulto_delivery_sync_data.php' target='_blank'>Перейти к синхронизации</a>";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_STATUS_TAB_VARIANTS_LOADED']   = "Варианты доставки:";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_STATUS_TAB_WAREHOUSES_LOADED'] = "Склады отправки отправлений:";

$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_ERROR_NO_AUTH_TITLE'] = "Ошибка авторизации";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_ERROR_NO_AUTH_DESCR'] = "<p>Для использования службы доставки необходимо авторизоваться на <a href='/bitrix/admin/settings.php?lang=ru&mid=catapulto.delivery&mid_menu=1' target='_blank'>странице настроек модуля</a><p>";

$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_ERROR_NO_SYNC_TITLE'] = "Не выполнена загрузка и синхронизация внешних данных";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_ERROR_NO_SYNC_DESCR']
                                                              = "<p>Для использования службы доставки необходимо <a href='/bitrix/admin/catapulto_delivery_sync_data.php' target='_blank'>выполнить загрузку и синхронизацию внешних данных</a> по вариантам доставки. <br>В противном случае обработчик не сможет работать и служба доставки не будет выводиться на странице оформления заказа.<p>";

$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_ERROR_WAREHOUSE_UNAVAILABLE_TITLE'] = "Необходимо проверить настройки складов отправки отправлений";
$MESS ['CATAPULTO_DELIVERY_DELIVERY_HANDLER_ERROR_WAREHOUSE_UNAVAILABLE_DESCR']
                                                                            = "<p>Cклад отправки отправлений не выбран в настройках модуля, либо ранее выбранный склад отправки отправлений стал недоступен.<br>Без выбора доступного склада отправки отправлений невозможен расчет стоимости доставки и служба доставки модуля не будет выводиться на странице оформления заказа.</p>";

$MESS ['CATAPULTO_DELIVERY_DELIVERY_CALC_ERROR_NO_DIRECT_CALL'] = "Расчет стоимости доставки возможен только конкретным профилем обработчика службы доставки";

$MESS['CATAPULTO_DELIVERY_DELIVERY_CALC_ERROR_NO_AUTH']               = "Не удалось рассчитать доставку: не выполнена авторизация в настройках модуля";
$MESS['CATAPULTO_DELIVERY_DELIVERY_NEED_YANDEX_API_KEY']              = "Для работы модуля необходимо в настройках заполнить обязательное поле \"Ключ API Яндекс.Карты\"";
$MESS['CATAPULTO_DELIVERY_DELIVERY_CALC_ERROR_NO_PROPS']              = "Не удалось рассчитать доставку: нет объекта свойств заказа";
$MESS['CATAPULTO_DELIVERY_DELIVERY_CALC_ERROR_NO_LOCATION_PROP']      = "Не удалось рассчитать доставку: нет свойства заказа с типом местоположение (LOCATION)";
$MESS['CATAPULTO_DELIVERY_DELIVERY_CALC_ERROR_NO_LOCATION_CODE']      = "Не удалось рассчитать доставку: не задан код местоположения для расчета доставки";
$MESS['CATAPULTO_DELIVERY_DELIVERY_CALC_ERROR_WAREHOUSE_UNAVAILABLE'] = "Не удалось рассчитать доставку: склад отправки отправлений не выбран в настройках модуля, либо ранее выбранный склад отправки отправлений стал недоступен";

$MESS['CATAPULTO_DELIVERY_DELIVERY_HANDLER_PROFILE_MAIN_TAB_TITLE'] = "Дополнительные настройки";
$MESS['CATAPULTO_DELIVERY_DELIVERY_HANDLER_PROFILE_MAIN_TAB_DESCR'] = "Дополнительные настройки профиля службы доставки";

$MESS['CATAPULTO_DELIVERY_DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_TYPE']         = "Тип наценки";
$MESS['CATAPULTO_DELIVERY_DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_TYPE_PERCENT'] = "%";
$MESS['CATAPULTO_DELIVERY_DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_TYPE_FIXED']   = "Фиксированная сумма";

$MESS['CATAPULTO_DELIVERY_DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_VALUE'] = "Наценка";

$MESS['CATAPULTO_DELIVERY_DELIVERY_HANDLER_PROFILE_MAIN_TAB_ROUND_TO'] = "Округлять стоимость доставки вверх до суммы, кратной (0 - не округлять)";
