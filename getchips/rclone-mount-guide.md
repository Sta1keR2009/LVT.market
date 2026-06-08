# Монтирование облака через rclone

## 1. Установка rclone

**Debian/Ubuntu:**
```bash
sudo apt update
sudo apt install rclone
```

**Или официальный скрипт:**
```bash
curl https://rclone.org/install.sh | sudo bash
```

## 2. Настройка удалённого хранилища

```bash
rclone config
```

- Выберите `n` (new remote)
- Укажите имя (например `mydrive`, `yandex`, `dropbox`)
- Выберите тип облака из списка (Google Drive, Yandex Disk, Dropbox и т.д.)
- Следуйте подсказкам для авторизации (логин, пароль или OAuth)

## 3. Монтирование

**Базовая команда:**
```bash
# Создайте точку монтирования
sudo mkdir -p /mnt/cloud

# Монтирование (замените REMOTE и PATH на свои)
rclone mount REMOTE:PATH /mnt/cloud --vfs-cache-mode full
```

**Примеры:**

```bash
# Весь диск Google Drive
rclone mount mydrive: /mnt/cloud --vfs-cache-mode full

# Папка в облаке
rclone mount mydrive:Documents /mnt/cloud --vfs-cache-mode full

# С опциями для стабильной работы в фоне
rclone mount mydrive: /mnt/cloud \
  --vfs-cache-mode full \
  --vfs-cache-max-size 1G \
  --dir-cache-time 72h \
  --allow-other \
  --umask 002
```

## 4. Запуск в фоне (демон)

```bash
# С nohup
nohup rclone mount mydrive: /mnt/cloud --vfs-cache-mode full &

# Или через systemd (рекомендуется)
```

## 5. Unit-файл systemd (автозапуск)

Создайте `/etc/systemd/system/rclone-mount.service`:

```ini
[Unit]
Description=Rclone mount cloud storage
After=network-online.target

[Service]
Type=notify
User=www-data
ExecStart=/usr/bin/rclone mount mydrive: /mnt/cloud \
  --vfs-cache-mode full \
  --vfs-cache-max-size 1G \
  --dir-cache-time 72h
ExecStop=/bin/fusermount -uz /mnt/cloud
Restart=on-failure
RestartSec=10

[Install]
WantedBy=default.target
```

Затем:
```bash
sudo systemctl daemon-reload
sudo systemctl enable rclone-mount
sudo systemctl start rclone-mount
sudo systemctl status rclone-mount
```

## 6. Размонтирование

```bash
# Если монтирование в текущем терминале — Ctrl+C

# Принудительно
fusermount -uz /mnt/cloud
# или
sudo umount /mnt/cloud
```

## Полезные опции

| Опция | Описание |
|-------|----------|
| `--vfs-cache-mode full` | Кэширование файлов (рекомендуется) |
| `--vfs-cache-max-size 1G` | Лимит размера кэша |
| `--dir-cache-time 72h` | Кэш списка каталогов |
| `--allow-other` | Доступ для других пользователей (нужен allow_other в /etc/fuse.conf) |
| `--read-only` | Только чтение |

## Список настроенных удалений

```bash
rclone listremotes
```

## Проверка после монтирования

```bash
ls -la /mnt/cloud
df -h /mnt/cloud
```
