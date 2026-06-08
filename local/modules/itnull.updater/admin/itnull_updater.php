<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

// Подключаем модуль для автозагрузки классов
Loader::includeModule('itnull.updater');

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$APPLICATION->SetTitle(Loc::getMessage("ITNULL_UPDATER_ADMIN_PAGE_TITLE"));

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

// Подключаем стили и скрипты напрямую (для корректной работы в админке)
?>
<link rel="stylesheet" href="/local/modules/itnull.updater/public/css/styles.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/local/modules/itnull.updater/public/js/app.js"></script>
<script src="/local/modules/itnull.updater/public/js/alpine.js" defer></script>

<div class="iu-container" x-data="updaterApp()">

    <!-- Блок сообщений -->
    <div class="messages" x-show="messages.length > 0">
        <template x-for="message in messages" :key="message.id">
            <div :class="'message message-' + message.type" x-text="message.text"></div>
        </template>
    </div>

    <!-- Секция лицензионного ключа -->
    <div class="iu-card">
        <div class="iu-card-header" @click="toggleCollapse('license')" :class="{'collapsed': isCollapsed('license')}">
            <h2>
                <span class="iu-card-header-icon">🔑</span>
                <?= Loc::getMessage("ITNULL_UPDATER_LICENSE_KEY") ?: 'Лицензионный ключ' ?>
            </h2>
            <span class="iu-collapse-icon" :class="{'collapsed': isCollapsed('license')}">▼</span>
        </div>
        <div class="iu-card-body" :class="{'collapsed': isCollapsed('license')}">
            <div class="iu-license-form">
                <div class="iu-input-wrapper">
                    <span class="iu-input-icon">🔐</span>
                    <input
                        type="text"
                        class="iu-input"
                        x-model="key"
                        placeholder="<?= Loc::getMessage("ITNULL_UPDATER_KEY_PLACEHOLDER") ?: 'Введите лицензионный ключ Bitrix' ?>"
                        :disabled="loading"
                        @keyup.enter="loadKeyInfo()"
                    >
                </div>
                <button
                    @click="loadKeyInfo()"
                    :disabled="loading || key.length < 23"
                    class="iu-btn iu-btn-primary"
                >
                    <span x-show="!loading"><?= Loc::getMessage("ITNULL_UPDATER_GET_INFO") ?: 'Получить информацию' ?></span>
                    <span x-show="loading" class="loading-spinner"></span>
                    <span x-show="loading"><?= Loc::getMessage("ITNULL_UPDATER_LOADING") ?: 'Загрузка...' ?></span>
                </button>
                <button
                    @click="resetData()"
                    class="iu-btn iu-btn-secondary"
                    type="button"
                >
                    <?= Loc::getMessage("ITNULL_UPDATER_RESET") ?: 'Сбросить' ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Информация о ключе -->
    <div class="iu-card" x-show="keyInfo" x-transition>
        <div class="iu-card-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" @click="toggleCollapse('keyInfo')" :class="{'collapsed': isCollapsed('keyInfo')}">
            <h3>
                <span class="iu-card-header-icon">✓</span>
                <?= Loc::getMessage("ITNULL_UPDATER_KEY_INFO") ?: 'Информация о лицензии' ?>
            </h3>
            <span class="iu-collapse-icon" :class="{'collapsed': isCollapsed('keyInfo')}">▼</span>
        </div>
        <div class="iu-card-body" :class="{'collapsed': isCollapsed('keyInfo')}">
            <div class="iu-key-info">
                <div class="iu-info-item">
                    <div class="iu-info-label"><?= Loc::getMessage("ITNULL_UPDATER_KEY_NAME") ?: 'Название' ?></div>
                    <div class="iu-info-value" x-text="keyInfo?.NAME || 'N/A'"></div>
                </div>
                <div class="iu-info-item">
                    <div class="iu-info-label"><?= Loc::getMessage("ITNULL_UPDATER_KEY_SUPPORT") ?: 'Период поддержки' ?></div>
                    <div class="iu-info-value">
                        <span x-text="(keyInfo?.DATE_FROM || 'N/A') + ' — ' + (keyInfo?.DATE_TO || 'N/A')"></span>
                    </div>
                </div>
                <div class="iu-info-item">
                    <div class="iu-info-label"><?= Loc::getMessage("ITNULL_UPDATER_KEY_EDITION") ?: 'Редакция' ?></div>
                    <div class="iu-info-value" x-text="keyInfo?.EDITION || 'N/A'"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Список модулей -->
    <div class="iu-card" x-show="modules.length > 0" x-transition>
        <div class="iu-card-header" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);" @click="toggleCollapse('modules')" :class="{'collapsed': isCollapsed('modules')}">
            <h3>
                <span class="iu-card-header-icon">📦</span>
                <?= Loc::getMessage("ITNULL_UPDATER_MODULE_LIST") ?: 'Список модулей' ?>
                <span class="iu-badge iu-badge-info" style="margin-left: 12px; background: rgba(255,255,255,0.2); color: white;" x-text="filteredModules.length + ' шт.'"></span>
            </h3>
            <button
                @click.stop="refreshModules()"
                :disabled="loading"
                class="iu-btn iu-btn-sm"
                style="background: rgba(255,255,255,0.2); color: white; margin-right: 8px;"
                title="<?= Loc::getMessage("ITNULL_UPDATER_REFRESH") ?: 'Обновить список' ?>"
            >
                <span x-show="!loading">🔄</span>
                <span x-show="loading" class="loading-spinner"></span>
            </button>
            <span class="iu-collapse-icon" :class="{'collapsed': isCollapsed('modules')}">▼</span>
        </div>
        <div class="iu-card-body" :class="{'collapsed': isCollapsed('modules')}">
            <!-- Статистика -->
            <div class="iu-stats">
                <div class="iu-stat-card">
                    <div class="iu-stat-value" x-text="modules.length"></div>
                    <div class="iu-stat-label"><?= Loc::getMessage("ITNULL_UPDATER_TOTAL") ?: 'Всего' ?></div>
                </div>
                <div class="iu-stat-card">
                    <div class="iu-stat-value" style="color: var(--iu-success);" x-text="modules.filter(m => m.installed).length"></div>
                    <div class="iu-stat-label"><?= Loc::getMessage("ITNULL_UPDATER_INSTALLED") ?: 'Установлено' ?></div>
                </div>
                <div class="iu-stat-card">
                    <div class="iu-stat-value" style="color: var(--iu-warning);" x-text="modules.filter(m => m.updateVersion && m.installedVersion && m.updateVersion !== m.installedVersion).length"></div>
                    <div class="iu-stat-label"><?= Loc::getMessage("ITNULL_UPDATER_UPDATES") ?: 'Обновлений' ?></div>
                </div>
                <div class="iu-stat-card">
                    <div class="iu-stat-value" style="color: var(--iu-info);" x-text="modules.filter(m => m.canDownload).length"></div>
                    <div class="iu-stat-label"><?= Loc::getMessage("ITNULL_UPDATER_AVAILABLE") ?: 'Доступно' ?></div>
                </div>
            </div>

            <!-- Фильтры -->
            <div class="filters">
                <button @click="filter = 'all'" :class="{active: filter === 'all'}" type="button">
                    <?= Loc::getMessage("ITNULL_UPDATER_FILTER_ALL") ?: 'Все' ?>
                </button>
                <button @click="filter = 'installed'" :class="{active: filter === 'installed'}" type="button">
                    <?= Loc::getMessage("ITNULL_UPDATER_FILTER_INSTALLED") ?: 'Установленные' ?>
                </button>
                <button @click="filter = 'available'" :class="{active: filter === 'available'}" type="button">
                    <?= Loc::getMessage("ITNULL_UPDATER_FILTER_AVAILABLE") ?: 'Доступные' ?>
                </button>
            </div>

            <!-- Список модулей -->
            <div class="iu-modules-list">
                <template x-for="module in filteredModules" :key="module.id">
                    <div class="iu-module-item">
                        <!-- Заголовок модуля (кликабельный для раскрытия версий) -->
                        <div
                            class="iu-module-header"
                            @click="toggleModuleVersions(module.id)"
                            :class="{'expanded': isModuleExpanded(module.id)}"
                        >
                            <!-- Логотип модуля -->
                            <div class="iu-module-logo">
                                <template x-if="module.logo">
                                    <img :src="module.logo" :alt="module.name">
                                </template>
                                <template x-if="!module.logo">
                                    <span class="iu-module-logo-placeholder" x-text="module.id.split('.')[0].substring(0, 2)"></span>
                                </template>
                            </div>

                            <div class="iu-module-info">
                                <span class="iu-module-name" x-text="module.name"></span>
                                <code class="iu-module-id" x-text="module.id"></code>
                                <span class="iu-module-description" x-show="module.description" x-text="module.description" :title="module.description"></span>
                                <span class="iu-module-date" x-show="module.dateTo" x-text="'Лицензия до ' + module.dateTo"></span>
                            </div>
                            <div class="iu-module-versions-info">
                                <span class="iu-version" :class="module.installedVersion ? 'iu-version-installed' : 'iu-version-none'">
                                    <small><?= Loc::getMessage("ITNULL_UPDATER_COL_INSTALLED") ?: 'Установлена' ?>:</small>
                                    <span x-text="module.installedVersion || '—'"></span>
                                </span>
                                <span class="iu-version" :class="module.updateVersion ? 'iu-version-available' : 'iu-version-none'">
                                    <small><?= Loc::getMessage("ITNULL_UPDATER_COL_AVAILABLE") ?: 'Доступна' ?>:</small>
                                    <span x-text="module.updateVersion || '—'"></span>
                                </span>
                            </div>
                            <div class="iu-module-status">
                                <div class="status-icons">
                                    <span
                                        :title="module.licenseValid ? '<?= Loc::getMessage("ITNULL_UPDATER_LICENSE_ACTIVE") ?: 'Лицензия активна' ?>' : '<?= Loc::getMessage("ITNULL_UPDATER_LICENSE_INACTIVE") ?: 'Лицензия не активна' ?>'"
                                        :class="{'valid': module.licenseValid, 'invalid': !module.licenseValid}"
                                        class="status-icon"
                                    >L</span>
                                    <span
                                        :title="module.fileExists ? '<?= Loc::getMessage("ITNULL_UPDATER_FILE_EXISTS") ?: 'Файл присутствует' ?>' : '<?= Loc::getMessage("ITNULL_UPDATER_FILE_MISSING") ?: 'Файл отсутствует' ?>'"
                                        :class="{'valid': module.fileExists, 'invalid': !module.fileExists}"
                                        class="status-icon"
                                    >F</span>
                                    <span
                                        :title="module.installed ? '<?= Loc::getMessage("ITNULL_UPDATER_IS_INSTALLED") ?: 'Установлено' ?>' : '<?= Loc::getMessage("ITNULL_UPDATER_NOT_INSTALLED") ?: 'Не установлено' ?>'"
                                        :class="{'valid': module.installed, 'invalid': !module.installed}"
                                        class="status-icon"
                                    >I</span>
                                    <span
                                        x-show="module.isHidden"
                                        title="<?= Loc::getMessage("ITNULL_UPDATER_MODULE_HIDDEN") ?: 'Модуль исключён из обновлений (сторонний разработчик)' ?>"
                                        class="status-icon hidden-icon"
                                    >H</span>
                                </div>
                            </div>
                            <div class="iu-module-actions" @click.stop>
                                <!-- Кнопка скачивания модуля (полная версия - mod) -->
                                <template x-if="module.canDownload && !isDownloading(module.id, 'mod')">
                                    <button
                                        @click="downloadModule(module.id, 'mod')"
                                        class="iu-btn iu-btn-success iu-btn-sm"
                                    >
                                        <?= Loc::getMessage("ITNULL_UPDATER_DOWNLOAD") ?: 'Скачать' ?>
                                    </button>
                                </template>
                                <template x-if="isDownloading(module.id, 'mod')">
                                    <button disabled class="iu-btn iu-btn-sm">
                                        <span class="loading-spinner"></span>
                                    </button>
                                </template>

                                <!-- Кнопка установки -->
                                <template x-if="module.canInstall && !isInstalling(module.id, 'mod')">
                                    <button
                                        @click="installUpdate(module.id, 'mod')"
                                        class="iu-btn iu-btn-primary iu-btn-sm"
                                    >
                                        <?= Loc::getMessage("ITNULL_UPDATER_INSTALL") ?: 'Установить' ?>
                                    </button>
                                </template>
                                <template x-if="isInstalling(module.id, 'mod')">
                                    <button disabled class="iu-btn iu-btn-sm">
                                        <span class="loading-spinner"></span>
                                    </button>
                                </template>
                            </div>
                            <div class="iu-module-expand" x-show="module.versions && module.versions.length > 0">
                                <span class="iu-expand-icon" :class="{'expanded': isModuleExpanded(module.id)}">▼</span>
                                <span class="iu-versions-count" x-text="module.versions.length + ' версий'"></span>
                            </div>
                        </div>

                        <!-- Список версий (сворачиваемый) -->
                        <div
                            class="iu-module-versions"
                            x-show="isModuleExpanded(module.id) && module.versions && module.versions.length > 0"
                            x-transition
                        >
                            <div class="iu-versions-header">
                                <span><?= Loc::getMessage("ITNULL_UPDATER_VERSION") ?: 'Версия' ?></span>
                                <span><?= Loc::getMessage("ITNULL_UPDATER_COL_STATUS") ?: 'Статус' ?></span>
                                <span><?= Loc::getMessage("ITNULL_UPDATER_COL_ACTIONS") ?: 'Действия' ?></span>
                            </div>
                            <template x-for="ver in module.versions" :key="module.id + '-' + ver.version">
                                <div class="iu-version-row" :class="{'installed': ver.isInstalled}">
                                    <div class="iu-version-number">
                                        <span x-text="ver.version"></span>
                                        <small x-show="ver.description" x-html="ver.description" class="iu-version-desc"></small>
                                    </div>
                                    <div class="iu-version-status">
                                        <span
                                            class="status-icon"
                                            :class="{'valid': ver.isInstalled, 'invalid': !ver.isInstalled}"
                                            :title="ver.isInstalled ? 'Установлено' : 'Не установлено'"
                                        >I</span>
                                        <span
                                            class="status-icon"
                                            :class="{'valid': ver.fileExists, 'invalid': !ver.fileExists}"
                                            :title="ver.fileExists ? 'Файл скачан' : 'Файл не скачан'"
                                        >F</span>
                                    </div>
                                    <div class="iu-version-actions">
                                        <!-- Скачать версию (показываем если: можно скачать, файла нет, версия НЕ установлена) -->
                                        <template x-if="ver.canDownload && !ver.fileExists && !ver.isInstalled && !isDownloading(module.id, ver.version)">
                                            <button
                                                @click="downloadModule(module.id, 'delta', ver.version, ver.prevVersion)"
                                                class="iu-btn iu-btn-success iu-btn-sm"
                                            >
                                                <?= Loc::getMessage("ITNULL_UPDATER_DOWNLOAD") ?: 'Скачать' ?>
                                            </button>
                                        </template>
                                        <template x-if="isDownloading(module.id, ver.version)">
                                            <button disabled class="iu-btn iu-btn-sm">
                                                <span class="loading-spinner"></span>
                                            </button>
                                        </template>

                                        <!-- Установить версию (показываем если: можно установить И версия НЕ установлена) -->
                                        <template x-if="ver.canInstall && !ver.isInstalled && !isInstalling(module.id, ver.version)">
                                            <button
                                                @click="installUpdate(module.id, 'delta', ver.version)"
                                                class="iu-btn iu-btn-primary iu-btn-sm"
                                            >
                                                <?= Loc::getMessage("ITNULL_UPDATER_INSTALL") ?: 'Установить' ?>
                                            </button>
                                        </template>
                                        <template x-if="isInstalling(module.id, ver.version)">
                                            <button disabled class="iu-btn iu-btn-sm">
                                                <span class="loading-spinner"></span>
                                            </button>
                                        </template>

                                        <!-- Уже установлено -->
                                        <span x-show="ver.isInstalled" class="iu-installed-label">
                                            <?= Loc::getMessage("ITNULL_UPDATER_ALREADY_INSTALLED") ?: 'Установлено' ?>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Пустое состояние для отфильтрованного списка -->
            <div class="iu-empty-state" x-show="modules.length > 0 && filteredModules.length === 0">
                <div class="iu-empty-state-icon">🔍</div>
                <div class="iu-empty-state-title"><?= Loc::getMessage("ITNULL_UPDATER_NO_MODULES") ?: 'Модули не найдены' ?></div>
                <div class="iu-empty-state-text"><?= Loc::getMessage("ITNULL_UPDATER_CHANGE_FILTER") ?: 'Попробуйте изменить фильтр' ?></div>
            </div>
        </div>
    </div>

    <!-- Пустое состояние -->
    <div class="iu-card" x-show="!keyInfo && modules.length === 0">
        <div class="iu-card-body">
            <div class="iu-empty-state">
                <div class="iu-empty-state-icon">🔑</div>
                <div class="iu-empty-state-title"><?= Loc::getMessage("ITNULL_UPDATER_ENTER_KEY") ?: 'Введите лицензионный ключ' ?></div>
                <div class="iu-empty-state-text"><?= Loc::getMessage("ITNULL_UPDATER_ENTER_KEY_DESC") ?: 'Для получения списка модулей введите ваш лицензионный ключ Bitrix' ?></div>
            </div>
        </div>
    </div>

</div>

<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>
