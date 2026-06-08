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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/local/modules/itnull.updater/public/js/alpine.js" defer></script>

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
    padding: 16px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s ease;
    user-select: none;
}

.iu-options-header:hover {
    filter: brightness(1.05);
}

.iu-options-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.iu-collapse-icon {
    font-size: 12px;
    transition: transform 0.3s ease;
    opacity: 0.8;
}

.iu-collapse-icon.collapsed {
    transform: rotate(-90deg);
}

.iu-options-body {
    padding: 24px;
    transition: all 0.3s ease;
}

.iu-options-body.collapsed {
    display: none;
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
    min-height: 200px;
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

.iu-btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.iu-btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
}

.iu-btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.iu-btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);
}

.iu-btn-secondary {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
}

.iu-btn-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(100, 116, 139, 0.3);
}

.iu-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.iu-stats {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.iu-stat-item {
    background: #f1f5f9;
    padding: 16px 20px;
    border-radius: 8px;
    text-align: center;
    flex: 1;
    min-width: 120px;
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

/* Demo Section Styles */
.iu-demo-status {
    margin-bottom: 24px;
}

.iu-demo-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

.iu-demo-section-title {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 12px 0;
}

.iu-demo-section-desc {
    font-size: 14px;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 16px;
}

.iu-demo-warning {
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 16px;
    font-size: 13px;
    color: #92400e;
}

.iu-demo-warning code {
    background: rgba(0,0,0,0.1);
    padding: 2px 6px;
    border-radius: 4px;
}

.iu-demo-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.iu-select {
    padding: 10px 16px;
    font-size: 14px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #1e293b;
    cursor: pointer;
    min-width: 200px;
    transition: all 0.2s ease;
}

.iu-select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.iu-select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Стили для всех select внутри настроек модуля - переопределяем .adm-workarea select */
.adm-workarea .iu-options-container .iu-options-body select,
.adm-workarea .iu-options-container .iu-demo-section select,
.adm-workarea .iu-options-container select.iu-select {
    margin: 0;
    padding: 10px 36px 10px 16px;
    height: auto;
    font-size: 14px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    box-shadow: none;
    color: #1e293b;
    cursor: pointer;
    min-width: 200px;
    transition: all 0.2s ease;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    -webkit-font-smoothing: auto;
    vertical-align: baseline;
}

.adm-workarea .iu-options-container .iu-options-body select:focus,
.adm-workarea .iu-options-container .iu-demo-section select:focus,
.adm-workarea .iu-options-container select.iu-select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.adm-workarea .iu-options-container .iu-options-body select:disabled,
.adm-workarea .iu-options-container .iu-demo-section select:disabled,
.adm-workarea .iu-options-container select.iu-select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #f8fafc;
}

.adm-workarea .iu-options-container .iu-options-body select:hover:not(:disabled),
.adm-workarea .iu-options-container .iu-demo-section select:hover:not(:disabled),
.adm-workarea .iu-options-container select.iu-select:hover:not(:disabled) {
    border-color: #cbd5e1;
}

.iu-link {
    color: #6366f1;
    text-decoration: none;
    word-break: break-all;
}

.iu-link:hover {
    text-decoration: underline;
}

.loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

[x-cloak] {
    display: none !important;
}

/* Блок информации о разработчике */
.iu-developer-block {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    color: white;
}

.iu-developer-logo {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: 2px;
    padding: 15px 20px;
    background: rgba(255,255,255,0.2);
    border-radius: 8px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.iu-developer-info {
    flex: 1;
}

.iu-developer-name {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 4px;
}

.iu-developer-copyright {
    font-size: 13px;
    opacity: 0.9;
    margin-bottom: 8px;
}

.iu-developer-links {
    font-size: 13px;
}

.iu-developer-links .iu-link {
    color: white;
    opacity: 0.9;
}

.iu-developer-links .iu-link:hover {
    opacity: 1;
}
</style>

<script>
// Определяем компонент для страницы настроек
window.optionsApp = function() {
    return {
        settingsCollapsed: false,
        demoCollapsed: true,
        aboutCollapsed: false,
        demoStatus: null,
        licenseTypes: {},
        selectedLicenseType: '',
        demoLoading: false,

        async loadDemoStatus() {
            try {
                const formData = new FormData();
                formData.append('action', 'demo_status');
                formData.append('sessid', BX.bitrix_sessid());

                const response = await fetch('/bitrix/admin/itnull_updater_ajax.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.demoStatus = data.data;
                    this.licenseTypes = data.licenseTypes || {};
                }
            } catch (error) {
                console.error('Demo status load error:', error);
            }
        },

        async getNewDemoKey() {
            if (!this.selectedLicenseType) {
                Swal.fire({ icon: 'warning', title: 'Внимание', text: 'Выберите тип лицензии' });
                return;
            }

            const result = await Swal.fire({
                title: 'Получить новый демо-ключ?',
                html: '<p>Будет сгенерирован новый демо-ключ для выбранной лицензии.</p>' +
                      '<p style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 5px; color: #856404;">' +
                      '⚠️ Текущий ключ будет заменён!</p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Получить ключ',
                cancelButtonText: 'Отмена'
            });

            if (!result.isConfirmed) return;

            this.demoLoading = true;
            Swal.fire({
                title: 'Генерация ключа...',
                html: 'Пожалуйста, подождите',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('action', 'demo_get_key');
                formData.append('licenseType', this.selectedLicenseType);
                formData.append('sessid', BX.bitrix_sessid());

                const response = await fetch('/bitrix/admin/itnull_updater_ajax.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    this.demoStatus = data.data;
                    Swal.fire({
                        icon: 'success',
                        title: 'Успешно!',
                        html: data.message + '<br><code style="font-size: 12px;">' + (data.key || '') + '</code>'
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Ошибка', text: data.message });
                }
            } catch (error) {
                console.error('Get demo key error:', error);
                Swal.fire({ icon: 'error', title: 'Ошибка', text: 'Произошла ошибка при генерации ключа' });
            } finally {
                this.demoLoading = false;
            }
        },

        async extendDemo() {
            const result = await Swal.fire({
                title: 'Продлить демо-режим?',
                html: '<p>Демо-режим будет продлён на 1 год.</p>' +
                      '<p style="margin-top: 10px; padding: 10px; background: #f8d7da; border-radius: 5px; color: #721c24;">' +
                      '⚠️ <strong>ВНИМАНИЕ!</strong><br>Не используйте эту функцию на Битриксе в исходниках!</p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Продлить',
                cancelButtonText: 'Отмена'
            });

            if (!result.isConfirmed) return;

            this.demoLoading = true;
            Swal.fire({
                title: 'Продление демо...',
                html: 'Пожалуйста, подождите',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('action', 'demo_extend');
                formData.append('sessid', BX.bitrix_sessid());

                const response = await fetch('/bitrix/admin/itnull_updater_ajax.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    this.demoStatus = data.data;
                    Swal.fire({ icon: 'success', title: 'Успешно!', text: data.message });
                } else {
                    Swal.fire({ icon: 'error', title: 'Ошибка', text: data.message });
                }
            } catch (error) {
                console.error('Extend demo error:', error);
                Swal.fire({ icon: 'error', title: 'Ошибка', text: 'Произошла ошибка при продлении демо-режима' });
            } finally {
                this.demoLoading = false;
            }
        },

        async toggleDemoMessage() {
            const isHidden = this.demoStatus?.isMessageHidden;

            if (isHidden) {
                // Показать сообщение
                const result = await Swal.fire({
                    title: 'Показать сообщение о демо?',
                    html: '<p>Сообщение о пробном периоде снова станет видимым в админке.</p>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Показать',
                    cancelButtonText: 'Отмена'
                });

                if (!result.isConfirmed) return;

                this.demoLoading = true;

                try {
                    const formData = new FormData();
                    formData.append('action', 'demo_show_message');
                    formData.append('sessid', BX.bitrix_sessid());

                    const response = await fetch('/bitrix/admin/itnull_updater_ajax.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.demoStatus = data.data;
                        Swal.fire({ icon: 'success', title: 'Успешно!', text: data.message });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Ошибка', text: data.message });
                    }
                } catch (error) {
                    console.error('Show demo message error:', error);
                    Swal.fire({ icon: 'error', title: 'Ошибка', text: 'Произошла ошибка' });
                } finally {
                    this.demoLoading = false;
                }
            } else {
                // Скрыть сообщение
                const result = await Swal.fire({
                    title: 'Скрыть сообщение о демо?',
                    html: '<p>Сообщение о пробном периоде будет скрыто.</p>' +
                          '<p style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 5px; color: #856404;">' +
                          '⚠️ <strong>Будьте осторожны!</strong><br>Если забудете продлить демо-режим — сайт перестанет работать!</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6c757d',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Скрыть',
                    cancelButtonText: 'Отмена'
                });

                if (!result.isConfirmed) return;

                this.demoLoading = true;

                try {
                    const formData = new FormData();
                    formData.append('action', 'demo_hide_message');
                    formData.append('sessid', BX.bitrix_sessid());

                    const response = await fetch('/bitrix/admin/itnull_updater_ajax.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.demoStatus = data.data;
                        Swal.fire({ icon: 'success', title: 'Успешно!', text: data.message });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Ошибка', text: data.message });
                    }
                } catch (error) {
                    console.error('Hide demo message error:', error);
                    Swal.fire({ icon: 'error', title: 'Ошибка', text: 'Произошла ошибка при скрытии сообщения' });
                } finally {
                    this.demoLoading = false;
                }
            }
        }
    };
};
</script>

<div class="iu-options-container" x-data="optionsApp()" x-init="loadDemoStatus()">

    <?php if ($saved): ?>
    <div class="iu-alert iu-alert-success">
        <span class="iu-alert-icon">✓</span>
        <span><?= Loc::getMessage("ITNULL_UPDATER_OPTIONS_SAVED") ?: 'Настройки успешно сохранены' ?></span>
    </div>
    <?php endif; ?>

    <!-- Блок настроек модуля -->
    <div class="iu-options-card">
        <div class="iu-options-header" @click="settingsCollapsed = !settingsCollapsed">
            <h2>
                <span>⚙️</span>
                <?= Loc::getMessage("ITNULL_UPDATER_OPTIONS_TITLE") ?: 'Настройки модуля' ?>
            </h2>
            <span class="iu-collapse-icon" :class="{'collapsed': settingsCollapsed}">▼</span>
        </div>
        <div class="iu-options-body" :class="{'collapsed': settingsCollapsed}">
            <form method="post" action="<?= $APPLICATION->GetCurPage() ?>?mid=itnull.updater&lang=<?= LANGUAGE_ID ?>">
                <?= bitrix_sessid_post() ?>

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
            </form>
        </div>
    </div>

    <!-- Блок Демо-режима -->
    <div class="iu-options-card">
        <div class="iu-options-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" @click="demoCollapsed = !demoCollapsed">
            <h2>
                <span>⏱️</span>
                <?= Loc::getMessage("ITNULL_UPDATER_DEMO_MODE") ?: 'Демо-режим' ?>
            </h2>
            <span class="iu-collapse-icon" :class="{'collapsed': demoCollapsed}">▼</span>
        </div>
        <div class="iu-options-body" :class="{'collapsed': demoCollapsed}">

            <!-- Статус демо -->
            <div class="iu-demo-status" x-show="demoStatus" x-cloak>
                <div class="iu-stats">
                    <div class="iu-stat-item">
                        <div class="iu-stat-value" :style="demoStatus?.hasLicense ? 'color: #10b981' : 'color: #ef4444'">
                            <span x-text="demoStatus?.hasLicense ? '✓' : '✗'"></span>
                        </div>
                        <div class="iu-stat-label" x-text="demoStatus?.licenseType || 'Лицензия'"></div>
                    </div>
                    <div class="iu-stat-item">
                        <div class="iu-stat-value" style="font-size: 14px;" x-text="demoStatus?.currentKey || '—'"></div>
                        <div class="iu-stat-label">Текущий ключ</div>
                    </div>
                    <div class="iu-stat-item">
                        <div class="iu-stat-value" :style="demoStatus?.isMessageHidden ? 'color: #10b981' : 'color: #f59e0b'">
                            <span x-text="demoStatus?.isMessageHidden ? 'Скрыто' : 'Видно'"></span>
                        </div>
                        <div class="iu-stat-label">Сообщение о демо</div>
                    </div>
                </div>
            </div>

            <!-- Секция 1: Обновление демо ключа -->
            <div class="iu-demo-section">
                <h4 class="iu-demo-section-title">🔑 Обновление демо ключа</h4>
                <div class="iu-demo-section-desc">
                    Для получения обновлений может понадобиться свежий демо ключ. Эта функция генерирует новый ключ и сразу прописывает его в систему.
                </div>
                <div class="iu-demo-actions">
                    <select x-model="selectedLicenseType" class="iu-select" :disabled="demoLoading">
                        <option value="">Выберите тип лицензии</option>
                        <template x-for="(name, type) in licenseTypes" :key="type">
                            <option :value="type" x-text="name"></option>
                        </template>
                    </select>
                    <button
                        @click="getNewDemoKey()"
                        :disabled="demoLoading || !selectedLicenseType"
                        class="iu-btn iu-btn-success"
                    >
                        <span x-show="!demoLoading">Получить свежий ключ</span>
                        <span x-show="demoLoading" class="loading-spinner"></span>
                    </button>
                </div>
            </div>

            <!-- Секция 2: Продление демо режима -->
            <div class="iu-demo-section">
                <h4 class="iu-demo-section-title">⏰ Продление демо режима</h4>
                <div class="iu-demo-section-desc">
                    Если вдруг с вашей копией Битрикса что-то произошло и появилось сообщение "Срок работы пробной версии продукта истек...", используйте ссылку для восстановления:
                    <a :href="demoStatus?.restoreUrl || '/bitrix/admin/itnullrestore.php'" target="_blank" class="iu-link" x-text="demoStatus?.restoreUrl || '/bitrix/admin/itnullrestore.php'"></a>
                </div>
                <div class="iu-demo-warning">
                    ⚠️ <strong>ВНИМАНИЕ!</strong> Не используйте эту функцию на Битриксе в исходниках!
                </div>
                <div class="iu-demo-actions">
                    <button
                        @click="extendDemo()"
                        :disabled="demoLoading"
                        class="iu-btn iu-btn-warning"
                    >
                        <span x-show="!demoLoading">Продлить демо на 1 год</span>
                        <span x-show="demoLoading" class="loading-spinner"></span>
                    </button>
                </div>
            </div>

            <!-- Секция 3: Скрытие/показ сообщения о демо -->
            <div class="iu-demo-section">
                <h4 class="iu-demo-section-title" x-text="(demoStatus && demoStatus.isMessageHidden) ? '👁️ Показать сообщение о пробном периоде' : '🙈 Спрятать сообщение о пробном периоде'"></h4>
                <div class="iu-demo-section-desc" x-show="!(demoStatus && demoStatus.isMessageHidden)">
                    Скрывает надоедливое сообщение о пробном периоде в админке.
                </div>
                <div class="iu-demo-section-desc" x-show="demoStatus && demoStatus.isMessageHidden">
                    Сообщение о пробном периоде сейчас скрыто. Нажмите кнопку, чтобы показать его снова.
                </div>
                <div class="iu-demo-warning" x-show="!(demoStatus && demoStatus.isMessageHidden)">
                    ⚠️ Будьте осторожны! Если забудете продлить демо-режим — сайт перестанет работать.
                </div>
                <div class="iu-demo-actions">
                    <button
                        @click="toggleDemoMessage()"
                        :disabled="demoLoading"
                        :class="(demoStatus && demoStatus.isMessageHidden) ? 'iu-btn iu-btn-success' : 'iu-btn iu-btn-secondary'"
                    >
                        <span x-show="!demoLoading" x-text="(demoStatus && demoStatus.isMessageHidden) ? 'Показать сообщение' : 'Спрятать сообщение'"></span>
                        <span x-show="demoLoading" class="loading-spinner"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Блок информации о модуле и разработчике -->
    <div class="iu-options-card">
        <div class="iu-options-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" @click="aboutCollapsed = !aboutCollapsed">
            <h2>
                <span>🛡️</span>
                О модуле
            </h2>
            <span class="iu-collapse-icon" :class="{'collapsed': aboutCollapsed}">▼</span>
        </div>
        <div class="iu-options-body" :class="{'collapsed': aboutCollapsed}">

            <!-- Информация о разработчике -->
            <?php
            $developerInfo = \Itnull\Updater\IntegrityChecker::getDeveloperInfo();
            $integrityStatus = \Itnull\Updater\IntegrityChecker::getStatus();
            ?>

            <div class="iu-developer-block">
                <div class="iu-developer-logo">ITNULL</div>
                <div class="iu-developer-info">
                    <div class="iu-developer-name"><?= htmlspecialchars($developerInfo['name']) ?></div>
                    <div class="iu-developer-copyright"><?= htmlspecialchars($developerInfo['copyright']) ?></div>
                    <div class="iu-developer-links">
                        <a href="<?= htmlspecialchars($developerInfo['url']) ?>" target="_blank" class="iu-link"><?= htmlspecialchars($developerInfo['url']) ?></a>
                        <span style="margin: 0 8px; color: #cbd5e1;">|</span>
                        <a href="mailto:<?= htmlspecialchars($developerInfo['email']) ?>" class="iu-link"><?= htmlspecialchars($developerInfo['email']) ?></a>
                    </div>
                </div>
            </div>

            <!-- Статус целостности -->
            <div class="iu-demo-section" style="margin-top: 20px;">
                <h4 class="iu-demo-section-title">🔒 Статус защиты</h4>
                <div class="iu-stats">
                    <div class="iu-stat-item">
                        <div class="iu-stat-value" style="color: <?= $integrityStatus['isValid'] ? '#10b981' : '#ef4444' ?>">
                            <?= $integrityStatus['isValid'] ? '✓' : '✗' ?>
                        </div>
                        <div class="iu-stat-label">Целостность</div>
                    </div>
                    <div class="iu-stat-item">
                        <div class="iu-stat-value" style="color: <?= !empty($integrityStatus['isBlocked']) ? '#ef4444' : '#10b981' ?>">
                            <?= !empty($integrityStatus['isBlocked']) ? '🔒' : '🔓' ?>
                        </div>
                        <div class="iu-stat-label"><?= !empty($integrityStatus['isBlocked']) ? 'Заблокирован' : 'Активен' ?></div>
                    </div>
                    <div class="iu-stat-item">
                        <div class="iu-stat-value" style="font-size: 16px;"><?= htmlspecialchars($integrityStatus['version']) ?></div>
                        <div class="iu-stat-label">Версия</div>
                    </div>
                    <div class="iu-stat-item">
                        <div class="iu-stat-value" style="font-size: 14px;"><?= htmlspecialchars($integrityStatus['lastCheck']) ?></div>
                        <div class="iu-stat-label">Проверка</div>
                    </div>
                </div>

                <?php if (!empty($integrityStatus['isBlocked'])): ?>
                <div class="iu-demo-warning" style="background: #450a0a; border-color: #7f1d1d; color: #fecaca;">
                    🔒 <strong>МОДУЛЬ ЗАБЛОКИРОВАН!</strong><br>
                    Обнаружены несанкционированные изменения. Работа модуля приостановлена.<br>
                    Обратитесь к разработчику для восстановления.
                </div>
                <?php elseif (!$integrityStatus['isValid']): ?>
                <div class="iu-demo-warning" style="background: #fee2e2; border-color: #fecaca; color: #dc2626;">
                    ⚠️ <strong>Обнаружены изменения в файлах модуля:</strong><br>
                    <?php foreach ($integrityStatus['errors'] as $error): ?>
                        • <?= htmlspecialchars($error) ?><br>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="iu-alert iu-alert-success" style="margin-bottom: 0;">
                    <span class="iu-alert-icon">✓</span>
                    <span>Все файлы модуля в целостности, изменения не обнаружены.</span>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>
