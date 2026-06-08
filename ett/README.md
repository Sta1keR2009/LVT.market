# ETT Service (`/ett`)

MVP service for TN VED lookup, duty, Honest Sign status and declaration preparation.

## Stack

- Node.js LTS + TypeScript + Fastify
- Vite + React
- SQLite by default (`storage/ett.sqlite`)
- Read-only MySQL connector to Bitrix infoblock 11 (`627`, `103`, `512`)

## Run

```bash
npm install
npm run import:tnved
npm run import:duty
npm run import:honest
npm run dev
```

## Environment

Create `.env`:

```env
PORT=3217
HOST=127.0.0.1
SQLITE_PATH=/var/www/www-root/data/www/lvtgroup.ru/ett/storage/ett.sqlite

BITRIX_DB_HOST=127.0.0.1
BITRIX_DB_PORT=3306
BITRIX_DB_NAME=bitrix_db
BITRIX_DB_USER=bitrix_ro
BITRIX_DB_PASSWORD=secret

EXTERNAL_LOOKUP_ENABLED=0
MAX_UPLOAD_BYTES=10000000
```

## Nginx (`location /ett`)

```nginx
location /ett-api/ {
  proxy_pass http://127.0.0.1:3217/ett-api/;
  proxy_http_version 1.1;
  proxy_set_header Host $host;
  proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
}

location /ett/ {
  proxy_pass http://127.0.0.1:3217/ett/;
  proxy_http_version 1.1;
  proxy_set_header Host $host;
}
```

## systemd

`/etc/systemd/system/ett.service`

```ini
[Unit]
Description=ETT TNVED service
After=network.target

[Service]
Type=simple
WorkingDirectory=/var/www/www-root/data/www/lvtgroup.ru/ett
ExecStart=/usr/bin/npm run start
Restart=always
EnvironmentFile=/var/www/www-root/data/www/lvtgroup.ru/ett/.env
User=www-root
Group=www-root

[Install]
WantedBy=multi-user.target
```

## Migration later (separate host)

Keep source access behind an interface:
- current: local SQL connector
- later: Bitrix read-only HTTP API, periodic export import, or MySQL read-only replica via VPN
