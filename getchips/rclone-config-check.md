# Проверка конфигурации rclone

**Файл:** `~/.config/rclone/rclone.conf`

---

## Куда что смонтировано

**Сейчас ничего не смонтировано.** Rclone-удаления доступны только через команды (`rclone ls`, `rclone copy` и т.д.), точек монтирования в системе нет.

| Удаление   | Точка монтирования | Статус        |
|-----------|--------------------|---------------|
| yandex    | —                  | не смонтировано |
| webdav    | —                  | не смонтировано |
| mega-s4   | —                  | не смонтировано |

**Если нужно смонтировать** (пример для одной точки):

```bash
# Создать каталоги и смонтировать
sudo mkdir -p /mnt/rclone-webdav /mnt/rclone-mega

rclone mount webdav: /mnt/rclone-webdav --vfs-cache-mode full &
rclone mount mega-s4: /mnt/rclone-mega --vfs-cache-mode full &
```

После монтирования: `ls /mnt/rclone-webdav`, `ls /mnt/rclone-mega`. Размонтировать: `fusermount -uz /mnt/rclone-webdav`.

---

## Удаления (remotes)

| Удаление   | Тип      | Статус |
|-----------|----------|--------|
| **yandex**  | Yandex (OAuth) | ❌ Требуется переподключение |
| **webdav**  | WebDAV (Яндекс.Диск) | ✅ Работает |
| **mega-s4** | S3 (Mega) | ✅ Работает |

---

## 1. yandex — нужна повторная авторизация

Ошибка: `couldn't read OAuth token: empty token found`

**Что сделать:**
```bash
rclone config reconnect yandex:
```
Откроется браузер для входа в Яндекс; после входа токен сохранится и удаление заработает.

---

## 2. webdav — работает

Доступ к каталогам есть. Найдены папки: Старое, .sync, backup, backups, bcb, datasheet, Загрузки, lvt.

---

## 3. mega-s4 — работает

S3-совместимое хранилище Mega отвечает. Видна папка: lvt.

---

## Рекомендации по безопасности

1. **Пароли в конфиге** — в `rclone.conf` хранятся пароль WebDAV и ключи S3 в открытом виде. Рекомендуется шифрование конфига:
   ```bash
   rclone config create crypt-crypt type crypt remote=webdav:folder password=XXX password2=XXX
   ```
   Либо использовать системный ключ:
   ```bash
   rclone config password webdav pass
   # ввести пароль — rclone может хранить его в ключевой обвязке ОС
   ```
   Или включить шифрование всего конфига: в [документации](https://rclone.org/docs/#config-file) — `crypt` и `obscure`.

2. **Права на файл конфига:**
   ```bash
   chmod 600 ~/.config/rclone/rclone.conf
   ```

3. **yandex** — в конфиге только `client_id` и `client_secret`, самого токена нет. После `rclone config reconnect yandex:` токен будет записан в этот же файл.

---

## Итог

- Конфиг корректен, три удаления заданы верно.
- **webdav** и **mega-s4** доступны.
- Для **yandex** выполните: `rclone config reconnect yandex:`
