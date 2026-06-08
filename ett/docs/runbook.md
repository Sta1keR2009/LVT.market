# Runbook

## Restart service

```bash
sudo systemctl restart ett.service
sudo systemctl status ett.service
```

## Logs

```bash
journalctl -u ett.service -n 200 --no-pager
```

## Refresh reference data

```bash
cd /var/www/www-root/data/www/lvtgroup.ru/ett
npm run import:tnved
npm run import:duty
npm run import:honest
```

## GTD phases in current implementation

- A (reference): implemented in lookup and XLSX/PDF output.
- B (broker export prep): implemented as machine-readable columns in XLSX.
- C (full electronic submission): not implemented; requires external operator integration.
