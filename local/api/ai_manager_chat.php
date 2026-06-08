<?php
/**
 * Веб-панель менеджера: просмотр чата виджета lvt.market
 * URL: ?session=w-...&token=...
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/ai_manager_lib.php';

$sessionId = isset($_GET['session']) ? trim((string)$_GET['session']) : '';
$token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';

if ($sessionId === '' || $token === '' || !lvt_ai_manager_verify_token($sessionId, $token)) {
    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><title>Доступ запрещён</title></head><body><p>Ссылка недействительна или истекла.</p></body></html>';
    exit;
}

$apiBase = '/local/api/ai_manager_api.php';
$q = 'session=' . rawurlencode($sessionId) . '&token=' . rawurlencode($token);
$apiUrl = htmlspecialchars($apiBase . '?' . $q, ENT_QUOTES, 'UTF-8');
$sessionEsc = htmlspecialchars($sessionId, ENT_QUOTES, 'UTF-8');
header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex,nofollow" />
  <title>Чат <?= $sessionEsc ?> — LVT</title>
  <style>
    :root { --bg:#f4f6f8; --card:#fff; --accent:#1a5f9e; --user:#e8f4fc; --bot:#f0f0f0; --mgr:#e8f8e8; --sys:#fff8e6; }
    * { box-sizing: border-box; }
    body { margin:0; font:15px/1.45 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; background:var(--bg); color:#222; }
    .wrap { max-width:720px; margin:0 auto; padding:12px; min-height:100vh; display:flex; flex-direction:column; }
    header { background:var(--card); border-radius:10px; padding:12px 16px; margin-bottom:10px; box-shadow:0 1px 4px rgba(0,0,0,.06); }
    header h1 { margin:0 0 6px; font-size:17px; }
    .meta { font-size:13px; color:#555; word-break:break-all; }
    .badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:12px; font-weight:600; }
    .badge-ai { background:#dbeafe; color:#1e40af; }
    .badge-human { background:#dcfce7; color:#166534; }
    #msgs { flex:1; background:var(--card); border-radius:10px; padding:12px; overflow-y:auto; max-height:calc(100vh - 220px); min-height:280px; box-shadow:0 1px 4px rgba(0,0,0,.06); }
    .msg { margin-bottom:10px; padding:10px 12px; border-radius:8px; max-width:95%; }
    .msg-user { background:var(--user); margin-left:auto; }
    .msg-assistant { background:var(--bot); }
    .msg-manager { background:var(--mgr); border-left:3px solid #16a34a; }
    .msg-system { background:var(--sys); font-size:13px; color:#666; }
    .msg-label { font-size:11px; font-weight:700; text-transform:uppercase; color:#888; margin-bottom:4px; }
    .toolbar { display:flex; gap:8px; flex-wrap:wrap; margin:10px 0; }
    .toolbar button { padding:8px 14px; border-radius:8px; border:1px solid #ccc; background:#fff; cursor:pointer; font-size:14px; }
    .toolbar button.primary { background:var(--accent); color:#fff; border-color:var(--accent); }
    .toolbar button:disabled { opacity:.5; cursor:not-allowed; }
    form.reply { display:flex; gap:8px; margin-top:10px; }
    form.reply input { flex:1; padding:10px 12px; border:1px solid #ccc; border-radius:8px; font-size:15px; }
    form.reply button { padding:10px 18px; background:var(--accent); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:15px; }
    .status { font-size:12px; color:#888; margin-top:6px; }
    .err { color:#b91c1c; font-size:13px; }
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Чат с клиентом</h1>
      <p class="meta">Сессия: <code><?= $sessionEsc ?></code></p>
      <p class="meta" id="page-meta"></p>
      <p><span id="mode-badge" class="badge badge-ai">ИИ-консультант</span></p>
    </header>
    <div class="toolbar">
      <button type="button" id="btn-takeover" class="primary">Забрать чат</button>
      <button type="button" id="btn-close">Завершить диалог</button>
    </div>
    <div id="msgs" aria-live="polite"></div>
    <form class="reply" id="reply-form">
      <input type="text" id="reply-input" placeholder="Ответ клиенту…" autocomplete="off" />
      <button type="submit">Отправить</button>
    </form>
    <p class="status" id="status">Загрузка…</p>
  </div>
  <script>
(function () {
  var API = <?= json_encode($apiBase . '?' . $q, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  var lastId = 0;
  var humanMode = false;
  var operatorName = localStorage.getItem('lvt_mgr_name') || '';

  var msgsEl = document.getElementById('msgs');
  var statusEl = document.getElementById('status');
  var pageMeta = document.getElementById('page-meta');
  var modeBadge = document.getElementById('mode-badge');
  var btnTakeover = document.getElementById('btn-takeover');
  var btnClose = document.getElementById('btn-close');
  var replyForm = document.getElementById('reply-form');
  var replyInput = document.getElementById('reply-input');

  function labelFor(role) {
    if (role === 'user') return 'Клиент';
    if (role === 'assistant') return 'ИИ';
    if (role === 'manager') return 'Менеджер';
    return 'Система';
  }

  function classFor(role) {
    if (role === 'user') return 'msg-user';
    if (role === 'assistant') return 'msg-assistant';
    if (role === 'manager') return 'msg-manager';
    return 'msg-system';
  }

  function renderMessage(m) {
    if (!m || m.id <= lastId) return;
    lastId = Math.max(lastId, m.id);
    var div = document.createElement('div');
    div.className = 'msg ' + classFor(m.role);
    var label = labelFor(m.role);
    if (m.role === 'manager' && m.operator_name) label += ' (' + m.operator_name + ')';
    div.innerHTML = '<div class="msg-label">' + label + '</div><div class="msg-body"></div>';
    div.querySelector('.msg-body').textContent = m.content;
    msgsEl.appendChild(div);
    msgsEl.scrollTop = msgsEl.scrollHeight;
  }

  function setHumanMode(on) {
    humanMode = !!on;
    modeBadge.textContent = on ? 'Оператор' : 'ИИ-консультант';
    modeBadge.className = 'badge ' + (on ? 'badge-human' : 'badge-ai');
    btnTakeover.disabled = on;
  }

  function apiGet(action, extra) {
    var url = API + '&action=' + action + (extra || '');
    return fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); });
  }

  function apiPost(action, body) {
    return fetch(API + '&action=' + action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(body || {})
    }).then(function (r) { return r.json(); });
  }

  function applySession(j) {
    if (!j || !j.ok) return;
    setHumanMode(!!j.human_mode);
    if (j.meta) {
      var parts = [];
      if (j.meta.display_name) parts.push('Имя: ' + j.meta.display_name);
      if (j.meta.page_url) parts.push('Страница: ' + j.meta.page_url);
      if (j.meta.user_phone) parts.push('Тел: ' + j.meta.user_phone);
      pageMeta.textContent = parts.join(' · ');
    }
    if (j.messages) {
      for (var i = 0; i < j.messages.length; i++) renderMessage(j.messages[i]);
    }
    if (j.last_id) lastId = Math.max(lastId, j.last_id);
  }

  function poll() {
    apiGet('poll', '&since=' + lastId).then(function (j) {
      if (!j || !j.ok) return;
      setHumanMode(!!j.human_mode);
      if (j.messages) {
        for (var i = 0; i < j.messages.length; i++) renderMessage(j.messages[i]);
      }
    }).catch(function () {});
  }

  function load() {
    apiGet('session').then(function (j) {
      if (!j || !j.ok) {
        statusEl.innerHTML = '<span class="err">Не удалось загрузить чат.</span>';
        return;
      }
      applySession(j);
      statusEl.textContent = 'Обновление каждые 3 с';
      setInterval(poll, 3000);
    }).catch(function () {
      statusEl.innerHTML = '<span class="err">Ошибка связи с сервером.</span>';
    });
  }

  btnTakeover.addEventListener('click', function () {
    var name = prompt('Ваше имя для клиента:', operatorName || 'Менеджер');
    if (name === null) return;
    operatorName = String(name).trim() || 'Менеджер';
    localStorage.setItem('lvt_mgr_name', operatorName);
    btnTakeover.disabled = true;
    apiPost('takeover', { operator_name: operatorName }).then(function (j) {
      if (j && j.ok) {
        setHumanMode(true);
        statusEl.textContent = 'Вы подключились к чату';
        poll();
      } else {
        btnTakeover.disabled = false;
        statusEl.innerHTML = '<span class="err">Не удалось забрать чат</span>';
      }
    });
  });

  btnClose.addEventListener('click', function () {
    if (!confirm('Завершить диалог с клиентом?')) return;
    apiPost('close', {}).then(function (j) {
      if (j && j.ok) {
        setHumanMode(false);
        statusEl.textContent = 'Диалог завершён';
        poll();
      }
    });
  });

  replyForm.addEventListener('submit', function (e) {
    e.preventDefault();
    var text = replyInput.value.trim();
    if (!text) return;
  if (!operatorName) {
      operatorName = prompt('Ваше имя:', 'Менеджер') || 'Менеджер';
      localStorage.setItem('lvt_mgr_name', operatorName);
    }
    replyInput.value = '';
    apiPost('reply', { text: text, operator_name: operatorName }).then(function (j) {
      if (j && j.ok) {
        setHumanMode(true);
        poll();
      } else {
        statusEl.innerHTML = '<span class="err">Ошибка отправки</span>';
        replyInput.value = text;
      }
    });
  });

  load();
})();
  </script>
</body>
</html>
