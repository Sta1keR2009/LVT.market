/**
 * ITNULL Updater - Alpine.js Application
 * Version: 2.0
 *
 * Реактивное приложение для управления обновлениями модулей Bitrix
 */

// Определяем компонент глобально ДО загрузки Alpine.js
window.updaterApp = function() {
    return {
        // Состояние формы
        key: '',
        keyInfo: null,
        modules: [],

        // Состояние загрузки
        loading: false,
        downloading: {},
        installing: {},

        // Сообщения
        messages: [],

        // Фильтр: all, available, installed
        filter: 'all',

        // Состояние сворачивания карточек
        collapsed: {
            license: false,
            keyInfo: false,
            modules: false,
            demo: true  // По умолчанию свёрнуто
        },

        // Состояние раскрытия версий модулей
        expandedModules: {},

        /**
         * Инициализация приложения
         */
        init() {
            // Восстановление ключа из localStorage если есть
            const savedKey = localStorage.getItem('itnull_updater_key');
            if (savedKey) {
                this.key = savedKey;
            }

            // Прослушивание события beforeunload для сохранения состояния
            window.addEventListener('beforeunload', () => {
                if (this.key) {
                    localStorage.setItem('itnull_updater_key', this.key);
                }
            });
        },

        /**
         * Загрузка информации о ключе
         */
        async loadKeyInfo() {
            // Валидация длины ключа
            if (this.key.length < 23) {
                this.showMessage('Ключ должен содержать минимум 23 символа', 'error');
                return;
            }

            this.loading = true;
            this.clearMessages();

            try {
                const formData = new FormData();
                formData.append('action', 'load_key_info');
                formData.append('key', this.key);
                formData.append('sessid', BX.bitrix_sessid());

                const response = await fetch('/bitrix/admin/itnull_updater_ajax.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.keyInfo = data.keyInfo;
                    this.modules = data.modules || [];
                    this.showMessage('Информация о ключе успешно получена', 'success');

                    // Сохраняем ключ
                    localStorage.setItem('itnull_updater_key', this.key);
                } else {
                    this.showMessage(data.message || 'Ошибка получения информации', 'error');
                }
            } catch (error) {
                console.error('ITNULL Updater Error:', error);
                this.showMessage('Ошибка соединения с сервером', 'error');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Скачивание модуля
         */
        async downloadModule(moduleId, type, version, prevVersion = null) {
            const key = this.getActionKey(moduleId, version);

            // Находим информацию о модуле
            const module = this.modules.find(m => m.id === moduleId);
            const moduleName = module ? module.name : moduleId;

            // Устанавливаем состояние загрузки
            this.downloading = { ...this.downloading, [key]: true };

            // Показываем toast о начале скачивания
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            try {
                const formData = new FormData();
                formData.append('action', 'download');
                formData.append('moduleId', moduleId);
                formData.append('type', type);
                formData.append('version', version || '');
                formData.append('prevVersion', prevVersion || '');
                formData.append('sessid', BX.bitrix_sessid());

                const response = await fetch('/bitrix/admin/itnull_updater_ajax.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Если пришёл обновлённый список модулей, используем его
                    if (data.modules && data.modules.length > 0) {
                        this.modules = data.modules;
                    } else {
                        // Обновляем данные модуля
                        this.updateModuleData(moduleId, data.data);
                    }

                    // Показываем успех через toast
                    Toast.fire({
                        icon: 'success',
                        title: data.message || `Модуль ${moduleName} скачан`
                    });
                } else {
                    // Показываем ошибку через toast
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Ошибка скачивания'
                    });
                }
            } catch (error) {
                console.error('ITNULL Updater Download Error:', error);
                Toast.fire({
                    icon: 'error',
                    title: 'Ошибка скачивания модуля'
                });
            } finally {
                this.downloading = { ...this.downloading, [key]: false };
            }
        },

        /**
         * Установка обновления
         */
        async installUpdate(moduleId, type, version = null, prevVersion = null) {
            const key = this.getActionKey(moduleId, type === 'mod' ? 'mod' : version);

            // Находим информацию о модуле для отображения
            const module = this.modules.find(m => m.id === moduleId);
            const moduleName = module ? module.name : moduleId;
            const versionText = version ? ` (v${version})` : '';

            // Подтверждение через SweetAlert2
            const result = await Swal.fire({
                title: 'Установить обновление?',
                html: `
                    <div style="text-align: left; margin-top: 10px;">
                        <p><strong>Модуль:</strong> ${moduleName}</p>
                        <p><strong>ID:</strong> <code>${moduleId}</code>${versionText}</p>
                        <p style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 5px; color: #856404;">
                            <strong>⚠️ Рекомендация:</strong><br>
                            Перед установкой сделайте резервную копию сайта и базы данных.
                        </p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '✓ Установить',
                cancelButtonText: 'Отмена',
                reverseButtons: true,
                focusCancel: true
            });

            if (!result.isConfirmed) {
                return;
            }

            // Устанавливаем состояние установки
            this.installing = { ...this.installing, [key]: true };

            // Показываем индикатор загрузки
            Swal.fire({
                title: 'Установка...',
                html: `Устанавливается модуль <strong>${moduleName}</strong>`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const formData = new FormData();
                formData.append('action', 'install');
                formData.append('moduleId', moduleId);
                formData.append('type', type);
                formData.append('version', version || '');
                formData.append('prevVersion', prevVersion || '');
                formData.append('sessid', BX.bitrix_sessid());

                const response = await fetch('/bitrix/admin/itnull_updater_ajax.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Если пришёл обновлённый список модулей, используем его
                    if (data.modules && data.modules.length > 0) {
                        this.modules = data.modules;
                    } else {
                        // Обновляем данные модуля
                        this.updateModuleData(moduleId, data.data);
                    }

                    // Показываем успех
                    Swal.fire({
                        title: 'Успешно!',
                        html: data.message || `Модуль <strong>${moduleName}</strong> успешно установлен`,
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'Отлично!'
                    });
                } else {
                    // Показываем ошибку
                    Swal.fire({
                        title: 'Ошибка установки',
                        html: data.message || 'Не удалось установить модуль',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Закрыть'
                    });
                }
            } catch (error) {
                console.error('ITNULL Updater Install Error:', error);
                Swal.fire({
                    title: 'Ошибка!',
                    html: 'Произошла ошибка при установке модуля.<br>Проверьте консоль для деталей.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Закрыть'
                });
            } finally {
                this.installing = { ...this.installing, [key]: false };
            }
        },

        /**
         * Обновление данных модуля в списке
         */
        updateModuleData(moduleId, newData) {
            if (!newData) return;

            const moduleIndex = this.modules.findIndex(m => m.id === moduleId);
            if (moduleIndex !== -1) {
                const module = { ...this.modules[moduleIndex] };

                // Обновляем основные данные модуля
                Object.assign(module, newData);

                // Если обновляем версию, обновляем и массив версий
                if (newData.version && module.versions) {
                    module.versions = module.versions.map(v => {
                        if (v.version === newData.version) {
                            return {
                                ...v,
                                fileExists: newData.fileExists !== undefined ? newData.fileExists : v.fileExists,
                                canInstall: newData.canInstall !== undefined ? newData.canInstall : v.canInstall,
                                isInstalled: newData.installed || newData.isInstalled || v.isInstalled
                            };
                        }
                        return v;
                    });
                }

                // Создаём новый массив для триггера реактивности
                this.modules = [
                    ...this.modules.slice(0, moduleIndex),
                    module,
                    ...this.modules.slice(moduleIndex + 1)
                ];
            }
        },

        /**
         * Сброс данных
         */
        resetData() {
            this.key = '';
            this.keyInfo = null;
            this.modules = [];
            this.messages = [];
            this.filter = 'all';
            this.downloading = {};
            this.installing = {};
            this.expandedModules = {};

            // Очищаем localStorage
            localStorage.removeItem('itnull_updater_key');
        },

        /**
         * Обновление списка модулей
         */
        async refreshModules() {
            if (this.loading) return;

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('action', 'refresh');
                formData.append('sessid', BX.bitrix_sessid());

                const response = await fetch('/bitrix/admin/itnull_updater_ajax.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.modules = data.modules || [];
                    this.showMessage('Данные обновлены', 'success');
                } else {
                    this.showMessage(data.message || 'Ошибка обновления данных', 'error');
                }
            } catch (error) {
                console.error('ITNULL Updater Refresh Error:', error);
                this.showMessage('Ошибка обновления данных', 'error');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Показать сообщение
         */
        showMessage(text, type = 'info') {
            const message = {
                text: text,
                type: type,
                id: Date.now() + Math.random()
            };

            this.messages = [...this.messages, message];

            // Автоматически скрываем через 5 секунд
            setTimeout(() => {
                this.removeMessage(message.id);
            }, 5000);
        },

        /**
         * Удалить сообщение
         */
        removeMessage(id) {
            this.messages = this.messages.filter(m => m.id !== id);
        },

        /**
         * Очистить все сообщения
         */
        clearMessages() {
            this.messages = [];
        },

        /**
         * Получить ключ для действия
         */
        getActionKey(moduleId, version) {
            return `${moduleId}-${version || 'mod'}`;
        },

        /**
         * Геттер: отфильтрованный список модулей
         */
        get filteredModules() {
            if (!this.modules || this.modules.length === 0) {
                return [];
            }

            switch (this.filter) {
                case 'installed':
                    return this.modules.filter(m => m.installed && m.installedVersion);
                case 'available':
                    // Модули доступны, если лицензия валидна и есть обновление
                    return this.modules.filter(m => m.licenseValid && m.updateVersion);
                default:
                    return this.modules;
            }
        },

        /**
         * Проверка: идёт ли скачивание модуля
         */
        isDownloading(moduleId, version) {
            const key = this.getActionKey(moduleId, version);
            return this.downloading[key] === true;
        },

        /**
         * Проверка: идёт ли установка модуля
         */
        isInstalling(moduleId, typeOrVersion) {
            // Для mod типа используем 'mod' как ключ, для delta - версию
            const key = this.getActionKey(moduleId, typeOrVersion);
            return this.installing[key] === true;
        },

        /**
         * Геттер: количество модулей по категориям
         */
        get stats() {
            return {
                total: this.modules.length,
                installed: this.modules.filter(m => m.installed).length,
                updates: this.modules.filter(m => m.updateVersion && m.installedVersion && m.updateVersion !== m.installedVersion).length,
                available: this.modules.filter(m => m.canDownload).length
            };
        },

        /**
         * Переключение состояния сворачивания карточки
         */
        toggleCollapse(cardName) {
            this.collapsed[cardName] = !this.collapsed[cardName];
        },

        /**
         * Проверка: свёрнута ли карточка
         */
        isCollapsed(cardName) {
            return this.collapsed[cardName] === true;
        },

        /**
         * Переключение состояния раскрытия версий модуля
         */
        toggleModuleVersions(moduleId) {
            this.expandedModules[moduleId] = !this.expandedModules[moduleId];
        },

        /**
         * Проверка: раскрыт ли список версий модуля
         */
        isModuleExpanded(moduleId) {
            return this.expandedModules[moduleId] === true;
        }
    };
};

/**
 * ITNULL Updater - Demo Mode Alpine.js Component
 * Компонент для управления демо-режимом
 */
window.demoApp = function() {
    return {
        // Состояние
        demoStatus: null,
        licenseTypes: {},
        selectedLicenseType: '',
        demoLoading: false,
        demoCollapsed: true,  // По умолчанию свёрнуто

        /**
         * Загрузка статуса демо-режима
         */
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

        /**
         * Получение нового демо-ключа
         */
        async getNewDemoKey() {
            if (!this.selectedLicenseType) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Внимание',
                    text: 'Выберите тип лицензии'
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Получить новый демо-ключ?',
                html: `
                    <p>Будет сгенерирован новый демо-ключ для выбранной лицензии.</p>
                    <p style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 5px; color: #856404;">
                        ⚠️ Текущий ключ будет заменён!
                    </p>
                `,
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
                        html: `${data.message}<br><code style="font-size: 12px;">${data.key || ''}</code>`
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ошибка',
                        text: data.message
                    });
                }
            } catch (error) {
                console.error('Get demo key error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: 'Произошла ошибка при генерации ключа'
                });
            } finally {
                this.demoLoading = false;
            }
        },

        /**
         * Продление демо-режима
         */
        async extendDemo() {
            const result = await Swal.fire({
                title: 'Продлить демо-режим?',
                html: `
                    <p>Демо-режим будет продлён на 10 дней.</p>
                    <p style="margin-top: 10px; padding: 10px; background: #f8d7da; border-radius: 5px; color: #721c24;">
                        ⚠️ <strong>ВНИМАНИЕ!</strong><br>
                        Не используйте эту функцию на Битриксе в исходниках!
                    </p>
                `,
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Успешно!',
                        text: data.message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ошибка',
                        text: data.message
                    });
                }
            } catch (error) {
                console.error('Extend demo error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: 'Произошла ошибка при продлении демо-режима'
                });
            } finally {
                this.demoLoading = false;
            }
        },

        /**
         * Скрытие сообщения о демо-режиме
         */
        async hideDemoMessage() {
            const result = await Swal.fire({
                title: 'Скрыть сообщение о демо?',
                html: `
                    <p>Сообщение о пробном периоде будет скрыто.</p>
                    <p style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 5px; color: #856404;">
                        ⚠️ <strong>Будьте осторожны!</strong><br>
                        Если забудете продлить демо-режим — сайт перестанет работать!
                    </p>
                    <p style="margin-top: 10px; font-size: 12px; color: #666;">
                        Для восстановления удалите файл: <code>/bitrix/.config.php</code>
                    </p>
                `,
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Успешно!',
                        text: data.message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ошибка',
                        text: data.message
                    });
                }
            } catch (error) {
                console.error('Hide demo message error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: 'Произошла ошибка при скрытии сообщения'
                });
            } finally {
                this.demoLoading = false;
            }
        }
    };
};
