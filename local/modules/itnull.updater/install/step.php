<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<style>
.itnull-install-result {
    max-width: 600px;
    margin: 20px 0;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
}

.itnull-install-result__header {
    display: flex;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 8px 8px 0 0;
    color: #fff;
}

.itnull-install-result__icon {
    width: 48px;
    height: 48px;
    margin-right: 16px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.itnull-install-result__title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.itnull-install-result__subtitle {
    font-size: 13px;
    opacity: 0.9;
    margin-top: 4px;
}

.itnull-install-result__body {
    padding: 24px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-top: none;
    border-radius: 0 0 8px 8px;
}

.itnull-install-result__info {
    display: flex;
    align-items: flex-start;
    padding: 12px 16px;
    background: #e8f5e9;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #2e7d32;
}

.itnull-install-result__info-icon {
    margin-right: 12px;
    font-size: 18px;
}

.itnull-install-result__links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.itnull-install-result__links li {
    margin-bottom: 12px;
}

.itnull-install-result__links li:last-child {
    margin-bottom: 0;
}

.itnull-install-result__link {
    display: inline-flex;
    align-items: center;
    padding: 12px 20px;
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    width: 100%;
    box-sizing: border-box;
}

.itnull-install-result__link:hover {
    background: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.itnull-install-result__link-icon {
    margin-right: 12px;
    font-size: 18px;
    opacity: 0.7;
}

.itnull-install-result__link--primary {
    background: #1976d2;
    border-color: #1976d2;
    color: #fff;
}

.itnull-install-result__link--primary:hover {
    background: #1565c0;
    border-color: #1565c0;
    color: #fff;
}

.itnull-install-result__features {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.itnull-install-result__features-title {
    font-size: 14px;
    font-weight: 600;
    color: #666;
    margin-bottom: 12px;
}

.itnull-install-result__features-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 13px;
    color: #666;
}

.itnull-install-result__features-list li {
    display: flex;
    align-items: center;
}

.itnull-install-result__features-list li::before {
    content: "\2713";
    margin-right: 8px;
    color: #28a745;
    font-weight: bold;
}
</style>

<div class="itnull-install-result">
    <div class="itnull-install-result__header">
        <div class="itnull-install-result__icon">&#10003;</div>
        <div>
            <h3 class="itnull-install-result__title"><?= Loc::getMessage("ITNULL_UPDATER_INSTALL_SUCCESS_TITLE") ?: "Модуль успешно установлен!" ?></h3>
            <div class="itnull-install-result__subtitle">ITNULL Updater v<?= $arModuleVersion["VERSION"] ?? "1.0.0" ?></div>
        </div>
    </div>

    <div class="itnull-install-result__body">
        <div class="itnull-install-result__info">
            <span class="itnull-install-result__info-icon">&#9432;</span>
            <div>
                <?= Loc::getMessage("ITNULL_UPDATER_INSTALL_SUCCESS_INFO") ?: "Модуль готов к использованию. Системные файлы пропатчены. Настройте исключённые модули в настройках." ?>
            </div>
        </div>

        <ul class="itnull-install-result__links">
            <li>
                <a href="/bitrix/admin/itnull_updater.php" class="itnull-install-result__link itnull-install-result__link--primary">
                    <span class="itnull-install-result__link-icon">&#9881;</span>
                    <?= Loc::getMessage("ITNULL_UPDATER_GOTO_MODULE") ?: "Перейти к модулю" ?>
                </a>
            </li>
            <li>
                <a href="/bitrix/admin/settings.php?mid=itnull.updater&lang=<?= LANGUAGE_ID ?>" class="itnull-install-result__link">
                    <span class="itnull-install-result__link-icon">&#9881;</span>
                    <?= Loc::getMessage("ITNULL_UPDATER_GOTO_SETTINGS") ?: "Настройки модуля" ?>
                </a>
            </li>
            <li>
                <a href="/bitrix/admin/partner_modules.php" class="itnull-install-result__link">
                    <span class="itnull-install-result__link-icon">&#8592;</span>
                    <?= Loc::getMessage("ITNULL_UPDATER_BACK_TO_MODULES") ?: "Вернуться к списку модулей" ?>
                </a>
            </li>
        </ul>

        <div class="itnull-install-result__features">
            <div class="itnull-install-result__features-title"><?= Loc::getMessage("ITNULL_UPDATER_FEATURES_TITLE") ?: "Возможности модуля:" ?></div>
            <ul class="itnull-install-result__features-list">
                <li><?= Loc::getMessage("ITNULL_UPDATER_FEATURE_1") ?: "Управление обновлениями" ?></li>
                <li><?= Loc::getMessage("ITNULL_UPDATER_FEATURE_2") ?: "Скачивание модулей" ?></li>
                <li><?= Loc::getMessage("ITNULL_UPDATER_FEATURE_3") ?: "Delta-обновления" ?></li>
                <li><?= Loc::getMessage("ITNULL_UPDATER_FEATURE_4") ?: "Blacklist модулей" ?></li>
            </ul>
        </div>
    </div>
</div>
