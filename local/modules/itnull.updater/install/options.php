<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION;

// Подключаем модуль для автозагрузки классов
Loader::includeModule('itnull.updater');

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$request = Context::getCurrent()->getRequest();
$saved = $request->get('saved') === 'Y';

if ($request->isPost() && check_bitrix_sessid()) {
    $hiddenModules = $request->getPost('HIDDEN_MODULES');
    // Разбиваем по строкам и фильтруем
    $modulesArray = array_filter(array_map('trim', explode("\n", $hiddenModules)));
    Option::set('itnull.updater', 'hidden_modules', implode("\n", $modulesArray));

    LocalRedirect($APPLICATION->GetCurPage() . '?mid=itnull.updater&lang=' . LANGUAGE_ID . '&saved=Y');
}

// Используем метод из ModuleList для получения списка скрытых модулей
// Он автоматически инициализирует значения по умолчанию из default_option.php
$hiddenModulesList = \Itnull\Updater\ModuleList::getHiddenModules();
$hiddenModules = implode("\n", $hiddenModulesList);
$modulesCount = count($hiddenModulesList);

$APPLICATION->SetTitle(Loc::getMessage("ITNULL_UPDATER_OPTIONS_TITLE") ?: 'Настройки ITNULL Updater');
?>

<style>
.iu-options-container {
    max-width: 900px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.iu-options-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    margin-bottom: 20px;
    overflow: hidden;
}

.iu-options-header {
    padding: 20px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.iu-options-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.iu-options-body {
    padding: 24px;
}

.iu-alert {
    padding: 14px 18px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.iu-alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.iu-alert-info {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #7dd3fc;
}

.iu-alert-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.iu-alert-success .iu-alert-icon {
    background: #10b981;
    color: white;
}

.iu-alert-info .iu-alert-icon {
    background: #0ea5e9;
    color: white;
}

.iu-form-group {
    margin-bottom: 20px;
}

.iu-form-label {
    display: block;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
    font-size: 14px;
}

.iu-form-hint {
    font-size: 13px;
    color: #64748b;
    margin-top: 8px;
    line-height: 1.5;
}

.iu-textarea {
    width: 100%;
    min-height: 300px;
    padding: 14px 16px;
    font-size: 14px;
    font-family: 'SF Mono', Monaco, Consolas, monospace;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    color: #1e293b;
    resize: vertical;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.iu-textarea:focus {
    outline: none;
    border-color: #6366f1;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.iu-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.iu-btn-primary {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
}

.iu-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
}

.iu-stats {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
}

.iu-stat-item {
    background: #f1f5f9;
    padding: 16px 20px;
    border-radius: 8px;
    text-align: center;
    flex: 1;
}

.iu-stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #6366f1;
}

.iu-stat-label {
    font-size: 12px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

.iu-example {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-top: 16px;
}

.iu-example-title {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 8px;
}

.iu-example-code {
    font-family: 'SF Mono', Monaco, Consolas, monospace;
    font-size: 13px;
    color: #1e293b;
    line-height: 1.6;
}
</style>

<div class="iu-options-container">
    <?php if ($saved): ?>
    <div class="iu-alert iu-alert-success">
        <span class="iu-alert-icon">✓</span>
        <span><?= Loc::getMessage("ITNULL_UPDATER_OPTIONS_SAVED") ?: 'Настройки успешно сохранены' ?></span>
    </div>
    <?php endif; ?>

    <form method="post" action="<?= $APPLICATION->GetCurPage() ?>?mid=itnull.updater&lang=<?= LANGUAGE_ID ?>">
        <?= bitrix_sessid_post() ?>

        <div class="iu-options-card">
            <div class="iu-options-header">
                <h2>
                    <span>⚙️</span>
                    <?= Loc::getMessage("ITNULL_UPDATER_OPTIONS_TITLE") ?: 'Настройки модуля' ?>
                </h2>
            </div>
            <div class="iu-options-body">
                <div class="iu-alert iu-alert-info">
                    <span class="iu-alert-icon">i</span>
                    <span><?= Loc::getMessage("ITNULL_UPDATER_OPTIONS_INFO") ?: 'Укажите ID модулей, которые нужно исключить из проверки обновлений. Каждый модуль с новой строки.' ?></span>
                </div>

                <div class="iu-stats">
                    <div class="iu-stat-item">
                        <div class="iu-stat-value"><?= $modulesCount ?></div>
                        <div class="iu-stat-label"><?= Loc::getMessage("ITNULL_UPDATER_EXCLUDED_COUNT") ?: 'Исключено модулей' ?></div>
                    </div>
                </div>

                <div class="iu-form-group">
                    <label class="iu-form-label" for="HIDDEN_MODULES">
                        <?= Loc::getMessage("ITNULL_UPDATER_HIDDEN_MODULES") ?: 'Список исключённых модулей' ?>
                    </label>
                    <textarea
                        id="HIDDEN_MODULES"
                        name="HIDDEN_MODULES"
                        class="iu-textarea"
                        placeholder="<?= Loc::getMessage("ITNULL_UPDATER_MODULES_PLACEHOLDER") ?: 'vendor.module&#10;another.module' ?>"
                    ><?= htmlspecialcharsbx($hiddenModules) ?></textarea>
                    <div class="iu-form-hint">
                        <?= Loc::getMessage("ITNULL_UPDATER_HIDDEN_MODULES_HINT") ?: 'Введите ID модулей, которые не должны проверяться на обновления. Каждый модуль с новой строки.' ?>
                    </div>

                    <div class="iu-example">
                        <div class="iu-example-title"><?= Loc::getMessage("ITNULL_UPDATER_EXAMPLE") ?: 'Пример:' ?></div>
                        <div class="iu-example-code">
                            bitrix.main<br>
                            bitrix.iblock<br>
                            vendor.custommodule
                        </div>
                    </div>
                </div>

                <button type="submit" name="save" class="iu-btn iu-btn-primary">
                    <span>💾</span>
                    <?= Loc::getMessage("ITNULL_UPDATER_SAVE") ?: 'Сохранить настройки' ?>
                </button>
            </div>
        </div>
    </form>
</div>
