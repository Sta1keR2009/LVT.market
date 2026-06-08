<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<style>
.itnull-uninstall-result {
    max-width: 600px;
    margin: 20px 0;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
}

.itnull-uninstall-result__header {
    display: flex;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border-radius: 8px 8px 0 0;
    color: #fff;
}

.itnull-uninstall-result__icon {
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

.itnull-uninstall-result__title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.itnull-uninstall-result__subtitle {
    font-size: 13px;
    opacity: 0.9;
    margin-top: 4px;
}

.itnull-uninstall-result__body {
    padding: 24px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-top: none;
    border-radius: 0 0 8px 8px;
}

.itnull-uninstall-result__info {
    display: flex;
    align-items: flex-start;
    padding: 12px 16px;
    background: #fff3cd;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #856404;
}

.itnull-uninstall-result__info-icon {
    margin-right: 12px;
    font-size: 18px;
}

.itnull-uninstall-result__removed {
    margin-bottom: 20px;
}

.itnull-uninstall-result__removed-title {
    font-size: 14px;
    font-weight: 600;
    color: #666;
    margin-bottom: 12px;
}

.itnull-uninstall-result__removed-list {
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 13px;
    color: #666;
}

.itnull-uninstall-result__removed-list li {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 6px;
}

.itnull-uninstall-result__removed-list li:last-child {
    margin-bottom: 0;
}

.itnull-uninstall-result__removed-list li::before {
    content: "\2713";
    margin-right: 10px;
    color: #6c757d;
    font-weight: bold;
}

.itnull-uninstall-result__links {
    list-style: none;
    padding: 0;
    margin: 0;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.itnull-uninstall-result__link {
    display: inline-flex;
    align-items: center;
    padding: 12px 20px;
    background: #1976d2;
    border: 1px solid #1976d2;
    border-radius: 6px;
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.itnull-uninstall-result__link:hover {
    background: #1565c0;
    border-color: #1565c0;
    color: #fff;
}

.itnull-uninstall-result__link-icon {
    margin-right: 10px;
    font-size: 16px;
}
</style>

<div class="itnull-uninstall-result">
    <div class="itnull-uninstall-result__header">
        <div class="itnull-uninstall-result__icon">&#10003;</div>
        <div>
            <h3 class="itnull-uninstall-result__title"><?= Loc::getMessage("ITNULL_UPDATER_UNINSTALL_SUCCESS_TITLE") ?: "Модуль успешно удален" ?></h3>
            <div class="itnull-uninstall-result__subtitle">ITNULL Updater</div>
        </div>
    </div>

    <div class="itnull-uninstall-result__body">
        <div class="itnull-uninstall-result__info">
            <span class="itnull-uninstall-result__info-icon">&#9888;</span>
            <div>
                <?= Loc::getMessage("ITNULL_UPDATER_UNINSTALL_SUCCESS_INFO") ?: "Все компоненты модуля были удалены. Системные файлы восстановлены из резервных копий." ?>
            </div>
        </div>

        <div class="itnull-uninstall-result__removed">
            <div class="itnull-uninstall-result__removed-title"><?= Loc::getMessage("ITNULL_UPDATER_REMOVED_ITEMS") ?: "Удаленные компоненты:" ?></div>
            <ul class="itnull-uninstall-result__removed-list">
                <li><?= Loc::getMessage("ITNULL_UPDATER_REMOVED_PATCHES") ?: "Патчи системных файлов удалены" ?></li>
                <li><?= Loc::getMessage("ITNULL_UPDATER_REMOVED_OPTIONS") ?: "Настройки модуля очищены" ?></li>
                <li><?= Loc::getMessage("ITNULL_UPDATER_REMOVED_FILES") ?: "Административные файлы удалены" ?></li>
            </ul>
        </div>

        <ul class="itnull-uninstall-result__links">
            <li>
                <a href="/bitrix/admin/partner_modules.php" class="itnull-uninstall-result__link">
                    <span class="itnull-uninstall-result__link-icon">&#8592;</span>
                    <?= Loc::getMessage("ITNULL_UPDATER_BACK_TO_MODULES") ?: "Вернуться к списку модулей" ?>
                </a>
            </li>
        </ul>
    </div>
</div>
