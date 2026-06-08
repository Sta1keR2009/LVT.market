(function () {
  var cfg = window.LVT_AI_WIDGET || {};
  var apiUrl = cfg.apiUrl || "/local/api/ai_chat_proxy.php";
  var bridgeUrl = cfg.bridgeUrl || "/local/api/ai_widget_bridge.php";
  var leadUrl = cfg.leadUrl || "/local/api/ai_consultant_lead.php";
  var registerUrl = cfg.registerUrl || "/auth/?register=yes";
  var pdAgreementUrl = cfg.pdAgreementUrl || "/include/licenses_detail.php";
  var callbackFormId = cfg.callbackFormId || 0;

  var storageKey = "lvt_ai_session_id";
  var inviteKey = "lvt_ai_invite_done";
  var panelSizeKey = "lvt_ai_panel_wh";
  var panelPosKey = "lvt_ai_panel_pos";
  var leadLocalKey = "lvt_ai_lead_local";
  var displayNameKey = "lvt_ai_display_name";
  var humanModeKey = "lvt_ai_human_mode";
  var messagesKeyPrefix = "lvt_ai_msgs_";
  var MAX_STORED_MSGS = 50;
  var humanMode = false;
  var pollTimer = null;
  var lastPollId = 0;

  function pageCtx() {
    return window.LVT_AI_PAGE || {};
  }

  function ensureLvtAiPage() {
    if (!window.LVT_AI_PAGE) window.LVT_AI_PAGE = {};
    return window.LVT_AI_PAGE;
  }

  var productId = cfg.productId || pageCtx().productId || null;
  if (!productId) {
    var pel = document.querySelector("[data-product-id]");
    if (pel) productId = parseInt(pel.getAttribute("data-product-id"), 10) || null;
  }

  function sessionId() {
    try {
      var s = localStorage.getItem(storageKey);
      if (!s) {
        s =
          "w-" +
          (crypto.randomUUID
            ? crypto.randomUUID()
            : String(Date.now()) + Math.random());
        localStorage.setItem(storageKey, s);
      }
      return s;
    } catch (e) {
      return "w-fallback";
    }
  }

  function msgsStorageKey() {
    return messagesKeyPrefix + sessionId();
  }

  function loadMessages() {
    try {
      var raw = localStorage.getItem(msgsStorageKey());
      if (!raw) return [];
      var arr = JSON.parse(raw);
      return Array.isArray(arr) ? arr : [];
    } catch (e) {
      return [];
    }
  }

  function saveMessages(msgs) {
    try {
      var slice = msgs.slice(-MAX_STORED_MSGS);
      localStorage.setItem(msgsStorageKey(), JSON.stringify(slice));
    } catch (e) {}
  }

  function pushMessage(role, text) {
    var msgs = loadMessages();
    msgs.push({ role: role, content: text });
    saveMessages(msgs);
  }

  function getLeadSortValue(row) {
    if (!row) return 9999999;
    var attrVal = parseFloat(row.getAttribute("data-sort-lead") || "");
    if (isFinite(attrVal) && attrVal >= 0) return attrVal;
    var cells = row.querySelectorAll("td");
    var leadText = "";
    if (cells && cells.length >= 5) leadText = cells[4].textContent || "";
    leadText = String(leadText).replace(/\s+/g, " ").trim().toLowerCase();
    if (!leadText) return 9999999;
    if (leadText.indexOf("в наличии") !== -1) return 0;
    var numMatch = leadText.match(/(\d+(?:[.,]\d+)?)/);
    if (!numMatch) return 9999999;
    var base = parseFloat(String(numMatch[1]).replace(",", "."));
    if (!isFinite(base) || base < 0) return 9999999;
    if (/мес/.test(leadText)) return base * 30;
    if (/нед|week/.test(leadText)) return base * 7;
    if (/дн|day/.test(leadText)) return base;
    return base;
  }

  function refreshSupplierMinLeadDays() {
    var table = document.getElementById("lvt-supplier-offers");
    if (!table) return;
    var rows = table.querySelectorAll(".js-getchips-offer-row");
    var minDays = 9999999;
    for (var i = 0; i < rows.length; i++) {
      var v = getLeadSortValue(rows[i]);
      if (v < minDays) minDays = v;
    }
    var p = ensureLvtAiPage();
    if (minDays === 9999999 || !isFinite(minDays)) {
      p.supplierMinLeadDays = null;
      p.supplierLeadReady = true;
    } else {
      p.supplierMinLeadDays = Math.round(minDays);
      p.supplierLeadReady = true;
    }
  }

  document.addEventListener("lvt:supplier-offers-rendered", refreshSupplierMinLeadDays);
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", refreshSupplierMinLeadDays);
  } else {
    refreshSupplierMinLeadDays();
  }

  function getDisplayName() {
    try {
      var s = localStorage.getItem(displayNameKey);
      return s ? String(s).trim() : "";
    } catch (e) {
      return "";
    }
  }

  function setDisplayName(name) {
    try {
      localStorage.setItem(displayNameKey, String(name).trim());
    } catch (e) {}
  }

  function profileName() {
    var u = window.LVT_AI_USER;
    if (u && u.authorized && u.name) return String(u.name).trim();
    return "";
  }

  function welcomeText() {
    var dn = getDisplayName() || profileName();
    if (dn)
      return (
        "Здравствуйте, " +
        dn +
        "! Я могу помочь оформить заказ, рассчитать доставку и подобрать нужные компоненты. Чем могу быть полезен?"
      );
    return "Здравствуйте! Я могу помочь оформить заказ, рассчитать доставку и подобрать нужные компоненты. Чем могу быть полезен?";
  }

  var NO_TEXT = "Если возникнут вопросы - пишите.";

  function sanitizeOutboundMessage(raw, extraNames) {
    var t = String(raw || "");
    var names = extraNames || [];
    for (var i = 0; i < names.length; i++) {
      var n = String(names[i] || "").trim();
      if (n.length >= 3) {
        try {
          t = t.split(n).join("[скрыто]");
        } catch (e) {}
      }
    }
    t = t.replace(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g, "[email]");
    t = t.replace(
      /(?:\+7|8)[\s\-]?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}/g,
      "[телефон]"
    );
    t = t.replace(/\b\d{10,11}\b/g, "[телефон]");
    return t.replace(/\s+/g, " ").trim();
  }

  function collectRedactNames() {
    var out = [];
    var u = window.LVT_AI_USER;
    if (u && u.name) out.push(u.name);
    try {
      var raw = localStorage.getItem(leadLocalKey);
      if (raw) {
        var o = JSON.parse(raw);
        if (o && o.name) out.push(o.name);
      }
    } catch (e) {}
    var dn = getDisplayName();
    if (dn) out.push(dn);
    return out;
  }

  function loadHumanModeFlag() {
    try {
      return localStorage.getItem(humanModeKey) === "1";
    } catch (e) {
      return false;
    }
  }

  function saveHumanModeFlag(on) {
    try {
      if (on) localStorage.setItem(humanModeKey, "1");
      else localStorage.removeItem(humanModeKey);
    } catch (e) {}
  }

  function collectChatHistoryForOperator() {
    var list = loadMessages();
    var out = [];
    for (var i = 0; i < list.length; i++) {
      var m = list[i];
      if (!m || !m.content) continue;
      var text = String(m.content).trim();
      if (!text) continue;
      if (text.indexOf("👤 Менеджер:") === 0) continue;
      var role = m.role === "user" ? "user" : "assistant";
      out.push({ role: role, content: text.slice(0, 2000) });
    }
    return out.slice(-30);
  }

  function bridgeMeta(extra) {
    var meta = {
      session_id: sessionId(),
      page_url: location.href,
      chat_history: collectChatHistoryForOperator(),
    };
    var dn = getDisplayName() || profileName();
    if (dn) meta.display_name = dn;
    var u = window.LVT_AI_USER;
    if (u && u.authorized) {
      if (u.phone) meta.user_phone = u.phone;
      if (u.email) meta.user_email = u.email;
    }
    if (extra && extra.reason) meta.reason = extra.reason;
    return meta;
  }

  function setHumanMode(on) {
    humanMode = !!on;
    saveHumanModeFlag(humanMode);
    if (humanMode) {
      input.placeholder = "Сообщение оператору…";
      startHumanPoll();
    } else {
      input.placeholder = "Вопрос по товару, доставке…";
      stopHumanPoll();
    }
  }

  function stopHumanPoll() {
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
  }

  function pollHumanMessages() {
    if (!humanMode) return;
    var url =
      bridgeUrl +
      "?action=poll&session_id=" +
      encodeURIComponent(sessionId()) +
      "&since=" +
      lastPollId;
    fetch(url, { method: "GET", credentials: "same-origin" })
      .then(function (r) {
        return r.json();
      })
      .then(function (j) {
        if (!j || !j.ok || !j.messages) return;
        for (var i = 0; i < j.messages.length; i++) {
          var m = j.messages[i];
          if (!m || m.id <= lastPollId) continue;
          lastPollId = m.id;
          var who = m.from === "manager" ? "manager" : "system";
          addMsg(m.text, who, m.from === "system", {
            operatorName: m.operator_name || "",
          });
        }
        if (j.human_mode === false && humanMode) setHumanMode(false);
      })
      .catch(function () {});
  }

  function startHumanPoll() {
    stopHumanPoll();
    if (!humanMode || !open) return;
    pollHumanMessages();
    pollTimer = setInterval(pollHumanMessages, 2500);
  }

  function enterHumanMode(reason) {
    setHumanMode(true);
    return fetch(bridgeUrl + "?action=enter", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Session-Id": sessionId(),
      },
      body: JSON.stringify(bridgeMeta({ reason: reason || "запрос оператора" })),
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (j) {
        if (j && j.ok) pollHumanMessages();
        return j;
      });
  }

  function sendHumanMessage(text) {
    typing.textContent = "…";
    return fetch(bridgeUrl + "?action=message", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Session-Id": sessionId(),
      },
      body: JSON.stringify(
        Object.assign(bridgeMeta({}), { message: text })
      ),
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (j) {
        typing.textContent = "";
        if (!j || !j.ok) {
          addMsg(
            "Не удалось отправить сообщение оператору. Попробуйте ещё раз.",
            "system",
            true
          );
        }
        return j;
      })
      .catch(function () {
        typing.textContent = "";
        addMsg("Нет связи с оператором.", "system", true);
      });
  }

  function activateHumanFromChat(reason) {
    if (!humanMode) setHumanMode(true);
    else startHumanPoll();
    fetch(bridgeUrl + "?action=enter", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Session-Id": sessionId(),
      },
      body: JSON.stringify(
        bridgeMeta({ reason: reason || "эскалация от ИИ" })
      ),
    })
      .then(function () {
        pollHumanMessages();
      })
      .catch(function () {
        pollHumanMessages();
      });
  }

  var CHAT_ICON_SVG =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12zM7 9h10v2H7V9zm0-3h10v2H7V6zm0 6h7v2H7v-2z"/></svg>';

  var CALL_ICON_SVG =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.091 15.091 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 011 1V21a1 1 0 01-1 1C11.4 22 2 12.6 2 2a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.46.57 3.59a1 1 0 01-.25 1.01l-2.2 2.19z"/></svg>';

  var root = document.createElement("div");
  root.id = "lvt-ai-widget-root";

  var invite = document.createElement("div");
  invite.className = "lvt-ai-invite";
  invite.setAttribute("role", "dialog");
  invite.innerHTML =
    '<button type="button" class="lvt-ai-invite__close" aria-label="Закрыть">&times;</button>' +
    '<p class="lvt-ai-invite__title">Вам нужна помощь?</p>' +
    '<div class="lvt-ai-invite__actions">' +
    '<button type="button" class="btn btn-default btn-lg lvt-ai-invite-yes">Да</button>' +
    '<button type="button" class="btn btn-lg btn-transparent-border lvt-ai-invite-no">Нет</button>' +
    "</div>" +
    '<p class="lvt-ai-invite__nudge" style="display:none"></p>';

  var btn = document.createElement("button");
  btn.className = "lvt-ai-chat-btn btn btn-default btn-lg";
  btn.type = "button";
  btn.setAttribute("aria-label", "Консультант");
  btn.innerHTML = '<span class="lvt-ai-chat-btn__icon">' + CHAT_ICON_SVG + "</span>";

  var panel = document.createElement("div");
  panel.className = "lvt-ai-chat-panel";
  panel.setAttribute("role", "dialog");
  panel.setAttribute("aria-hidden", "true");

  var resizeLayer = document.createElement("div");
  resizeLayer.className = "lvt-ai-resize-layer";
  var dirs = ["n", "s", "e", "w", "ne", "nw", "se", "sw"];
  for (var ri = 0; ri < dirs.length; ri++) {
    var h = document.createElement("div");
    h.className = "lvt-ai-resize-handle lvt-ai-resize-" + dirs[ri];
    h.setAttribute("data-dir", dirs[ri]);
    resizeLayer.appendChild(h);
  }

  var head = document.createElement("div");
  head.className = "lvt-ai-chat-head";
  head.innerHTML =
    '<div class="lvt-ai-chat-head-row">' +
    '<div class="lvt-ai-chat-head-drag">' +
    '<motion class="lvt-ai-chat-head-title">Консультант</motion>' +
    "</div>" +
    '<div class="lvt-ai-chat-toolbar">' +
    '<button type="button" class="lvt-ai-btn-call" title="Заказать звонок" aria-label="Заказать звонок">' +
    CALL_ICON_SVG +
    "</button>" +
    '<button type="button" class="lvt-ai-btn-min" title="Свернуть">&#8211;</button>' +
    '<button type="button" class="lvt-ai-btn-fs" title="На весь экран">&#9974;</button>' +
    '<button type="button" class="lvt-ai-btn-close" title="Закрыть">&#215;</button>' +
    "</div></div>" +
    '<div class="lvt-ai-chat-head-actions"></div>';

  var messages = document.createElement("div");
  messages.className = "lvt-ai-chat-messages";

  var cartBanner = document.createElement("div");
  cartBanner.className = "lvt-ai-banner lvt-ai-banner-cart";
  cartBanner.style.display = "none";
  cartBanner.innerHTML =
    '<div>Товар доступен к заказу на странице.</div>' +
    '<button type="button" class="btn btn-default btn-lg lvt-ai-add-cart">Добавить в корзину (как на странице)</button>';

  var typing = document.createElement("div");
  typing.className = "lvt-ai-typing";

  var form = document.createElement("form");
  form.className = "lvt-ai-chat-form";

  var input = document.createElement("input");
  input.type = "text";
  input.placeholder = "Вопрос по товару, доставке…";
  input.autocomplete = "off";

  var send = document.createElement("button");
  send.type = "submit";
  send.textContent = "Отправить";

  var foot = document.createElement("div");
  foot.className = "lvt-ai-chat-footer";
  var user = window.LVT_AI_USER;
  if (!user || !user.authorized) {
    var regLink = document.createElement("a");
    regLink.href = registerUrl;
    regLink.target = "_blank";
    regLink.rel = "noopener";
    regLink.textContent = "Зарегистрируйтесь";
    foot.appendChild(regLink);
    foot.appendChild(
      document.createTextNode(" — уведомления о заказе и быстрое оформление.")
    );
  }

  form.appendChild(input);
  form.appendChild(send);

  panel.appendChild(resizeLayer);
  panel.appendChild(head);
  panel.appendChild(messages);
  panel.appendChild(cartBanner);
  panel.appendChild(typing);
  panel.appendChild(form);
  panel.appendChild(foot);

  var modal = document.createElement("div");
  modal.className = "lvt-ai-modal";
  modal.setAttribute("aria-hidden", "true");
  modal.innerHTML =
    '<div class="lvt-ai-modal__box">' +
    '<div class="lvt-ai-modal__title">Заказать звонок</div>' +
    '<div class="lvt-ai-modal__err" style="display:none"></div>' +
    '<div class="lvt-ai-modal__field"><label>Имя *</label><input type="text" name="lname" autocomplete="name" required /></div>' +
    '<div class="lvt-ai-modal__field"><label>Телефон *</label><input type="tel" name="lphone" autocomplete="tel" required /></div>' +
    '<div class="lvt-ai-modal__field"><label>Email</label><input type="email" name="lemail" autocomplete="email" /></div>' +
    '<div class="lvt-ai-modal__field"><label>Комментарий</label><input type="text" name="lmsg" /></div>' +
    '<div class="lvt-ai-modal__consent">' +
    '<label><input type="checkbox" name="lconsent" value="1" />' +
    '<span>Я согласен с <a href="https://lvt.market/include/licenses_detail.php" class="lvt-ai-pd-link" target="_blank" rel="noopener">соглашением на обработку персональных данных</a></span></label>' +
    "</div>" +
    '<div class="lvt-ai-modal__actions">' +
    '<button type="button" class="btn btn-lg btn-transparent-border lvt-ai-modal-cancel">Отмена</button>' +
    '<button type="button" class="btn btn-default btn-lg lvt-ai-modal-send">Отправить</button>' +
    "</div></div>";

  var legalModal = document.createElement("div");
  legalModal.className = "lvt-ai-legal-modal";
  legalModal.setAttribute("aria-hidden", "true");
  legalModal.innerHTML =
    '<div class="lvt-ai-legal-modal__box">' +
    '<div class="lvt-ai-legal-modal__head">' +
    '<h2 class="lvt-ai-legal-modal__title">Соглашение на обработку персональных данных</h2>' +
    '<button type="button" class="lvt-ai-legal-modal__close" aria-label="Закрыть">&times;</button>' +
    "</div>" +
    '<div class="lvt-ai-legal-modal__body"><iframe title="Соглашение" src=""></iframe></div>' +
    "</div>";

  root.appendChild(invite);
  root.appendChild(btn);
  root.appendChild(panel);
  root.appendChild(modal);
  root.appendChild(legalModal);
  document.body.appendChild(root);

  var legalIframe = legalModal.querySelector("iframe");
  var pdLink = modal.querySelector(".lvt-ai-pd-link");

  function openLegalModal(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    var src = pdAgreementUrl;
    if (src.indexOf("http") !== 0) src = location.origin + (src.charAt(0) === "/" ? "" : "/") + src;
    legalIframe.src = src;
    legalModal.classList.add("open");
    legalModal.setAttribute("aria-hidden", "false");
  }
  function closeLegalModal() {
    legalModal.classList.remove("open");
    legalModal.setAttribute("aria-hidden", "true");
    try {
      legalIframe.src = "about:blank";
    } catch (e) {}
  }
  pdLink.addEventListener("click", openLegalModal);
  legalModal.addEventListener("click", function (e) {
    if (e.target === legalModal) closeLegalModal();
  });
  legalModal.querySelector(".lvt-ai-legal-modal__close").addEventListener("click", closeLegalModal);

  var open = false;
  var welcomeShown = false;
  var minimized = false;
  var fullscreen = false;
  var prevBodyOverflow = "";
  var awaitingDisplayName = false;
  var useLeftTop = false;

  function applyPanelSize() {
    if (fullscreen || minimized) return;
    try {
      var raw = localStorage.getItem(panelSizeKey);
      if (!raw) return;
      var o = JSON.parse(raw);
      if (o.w) panel.style.width = Math.min(o.w, window.innerWidth - 24) + "px";
      if (o.h) panel.style.height = Math.min(o.h, window.innerHeight - 100) + "px";
    } catch (e) {}
  }

  function savePanelSize() {
    if (fullscreen || minimized) return;
    try {
      localStorage.setItem(
        panelSizeKey,
        JSON.stringify({
          w: panel.offsetWidth,
          h: panel.offsetHeight,
        })
      );
    } catch (e) {}
  }

  function applyPanelPos() {
    if (fullscreen || minimized) return;
    try {
      var raw = localStorage.getItem(panelPosKey);
      if (!raw) return;
      var o = JSON.parse(raw);
      if (o.left == null || o.top == null) return;
      useLeftTop = true;
      panel.classList.add("lvt-ai-chat-panel--lt");
      panel.style.right = "auto";
      panel.style.bottom = "auto";
      panel.style.left = Math.max(8, Math.min(o.left, window.innerWidth - panel.offsetWidth - 8)) + "px";
      panel.style.top = Math.max(8, Math.min(o.top, window.innerHeight - panel.offsetHeight - 8)) + "px";
    } catch (e) {}
  }

  function savePanelPos() {
    if (fullscreen || minimized || !useLeftTop) return;
    try {
      var r = panel.getBoundingClientRect();
      localStorage.setItem(
        panelPosKey,
        JSON.stringify({
          left: r.left,
          top: r.top,
        })
      );
    } catch (e) {}
  }

  var ro = typeof ResizeObserver !== "undefined" ? new ResizeObserver(savePanelSize) : null;
  if (ro) ro.observe(panel);

  function setFullscreen(on) {
    fullscreen = on;
    root.classList.toggle("lvt-ai--fullscreen", on);
    panel.classList.toggle("lvt-ai-chat-panel--fullscreen", on);
    if (on) {
      prevBodyOverflow = document.body.style.overflow;
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = prevBodyOverflow || "";
      applyPanelSize();
      applyPanelPos();
    }
  }

  function setMinimized(on) {
    minimized = on;
    panel.classList.toggle("lvt-ai-chat-panel--minimized", on);
  }

  function maybeAskDisplayName() {
    if (awaitingDisplayName) return;
    if (getDisplayName() || profileName()) return;
    var list = loadMessages();
    if (list.length > 0) return;
    awaitingDisplayName = true;
    addMsg("Как к вам обращаться? Напишите одним коротким сообщением.", "system", true);
  }

  function setPanelOpen(isOpen) {
    open = isOpen;
    panel.classList.toggle("open", open);
    panel.setAttribute("aria-hidden", open ? "false" : "true");
    if (open) {
      if (!minimized) input.focus();
      updateCartBanner();
      applyPanelSize();
      applyPanelPos();
      if (humanMode) startHumanPoll();
    } else {
      stopHumanPoll();
      setFullscreen(false);
      setMinimized(false);
    }
  }

  function togglePanel() {
    var willOpen = !open;
    setPanelOpen(willOpen);
    if (willOpen && !welcomeShown) {
      welcomeShown = true;
      addMsg(welcomeText(), "bot", false);
      maybeAskDisplayName();
    }
  }

  btn.addEventListener("click", function () {
    hideInvite();
    togglePanel();
  });

  head.querySelector(".lvt-ai-btn-close").addEventListener("click", function (e) {
    e.stopPropagation();
    setPanelOpen(false);
  });
  head.querySelector(".lvt-ai-btn-min").addEventListener("click", function (e) {
    e.stopPropagation();
    setMinimized(!minimized);
  });
  head.querySelector(".lvt-ai-btn-fs").addEventListener("click", function (e) {
    e.stopPropagation();
    var next = !fullscreen;
    setFullscreen(next);
    if (next) {
      setMinimized(false);
    } else {
      applyPanelSize();
      applyPanelPos();
    }
  });

  head.querySelector(".lvt-ai-chat-head-row").addEventListener("click", function (e) {
    if (minimized && !e.target.closest(".lvt-ai-chat-toolbar")) {
      setMinimized(false);
    }
  });

  var callBtn = head.querySelector(".lvt-ai-btn-call");
  if (callBtn) {
    callBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      var u = window.LVT_AI_USER || null;
      var name = (u && u.authorized && u.name) || "";
      var phone = (u && u.authorized && u.phone) || "";
      var email = (u && u.authorized && u.email) || "";
      if (!name || !phone) {
        openLeadModal();
        return;
      }
      sendLeadToTelegram({
        name: name,
        phone: phone,
        email: email,
        message: "",
        onDone: function (res) {
          openAsproCallback(name, phone);
          if (res && res.ok) {
            addMsg(
              "Заявка отправлена менеджеру. Ожидайте звонка.",
              "bot",
              false
            );
          } else {
            addMsg(
              "Форма вызова менеджера открыта на сайте. Если ответ из мессенджера не пришёл, завершите заявку в открывшемся окне.",
              "bot",
              false
            );
          }
        },
      });
    });
  }

  var dragZone = head.querySelector(".lvt-ai-chat-head-drag");
  var dragState = null;
  dragZone.addEventListener("pointerdown", function (e) {
    if (fullscreen || minimized) return;
    if (e.target.closest(".lvt-ai-chat-toolbar")) return;
    e.preventDefault();
    var r = panel.getBoundingClientRect();
    if (!useLeftTop) {
      useLeftTop = true;
      panel.classList.add("lvt-ai-chat-panel--lt");
      panel.style.right = "auto";
      panel.style.bottom = "auto";
      panel.style.left = r.left + "px";
      panel.style.top = r.top + "px";
      panel.style.width = r.width + "px";
      panel.style.height = r.height + "px";
    }
    dragState = {
      pid: e.pointerId,
      sx: e.clientX,
      sy: e.clientY,
      sl: panel.getBoundingClientRect().left,
      st: panel.getBoundingClientRect().top,
    };
    dragZone.setPointerCapture(e.pointerId);
  });
  dragZone.addEventListener("pointermove", function (e) {
    if (!dragState || e.pointerId !== dragState.pid) return;
    var nl = dragState.sl + (e.clientX - dragState.sx);
    var nt = dragState.st + (e.clientY - dragState.sy);
    var pw = panel.offsetWidth;
    var ph = panel.offsetHeight;
    nl = Math.max(8, Math.min(nl, window.innerWidth - pw - 8));
    nt = Math.max(8, Math.min(nt, window.innerHeight - ph - 8));
    panel.style.left = nl + "px";
    panel.style.top = nt + "px";
  });
  dragZone.addEventListener("pointerup", function (e) {
    if (!dragState || e.pointerId !== dragState.pid) return;
    dragState = null;
    try {
      dragZone.releasePointerCapture(e.pointerId);
    } catch (err) {}
    savePanelPos();
  });
  dragZone.addEventListener("pointercancel", function (e) {
    dragState = null;
  });

  var resizeState = null;
  resizeLayer.addEventListener("pointerdown", function (e) {
    if (fullscreen || minimized) return;
    var h = e.target.closest(".lvt-ai-resize-handle");
    if (!h) return;
    e.preventDefault();
    e.stopPropagation();
    var dir = h.getAttribute("data-dir") || "";
    var r = panel.getBoundingClientRect();
    if (!useLeftTop) {
      useLeftTop = true;
      panel.classList.add("lvt-ai-chat-panel--lt");
      panel.style.right = "auto";
      panel.style.bottom = "auto";
      panel.style.left = r.left + "px";
      panel.style.top = r.top + "px";
      panel.style.width = r.width + "px";
      panel.style.height = r.height + "px";
    }
    resizeState = {
      pid: e.pointerId,
      dir: dir,
      sx: e.clientX,
      sy: e.clientY,
      sl: r.left,
      st: r.top,
      sw: r.width,
      sh: r.height,
      el: h,
    };
    h.classList.add("lvt-ai-resize--active");
    h.setPointerCapture(e.pointerId);
  });
  resizeLayer.addEventListener("pointermove", function (e) {
    if (!resizeState || e.pointerId !== resizeState.pid) return;
    var dx = e.clientX - resizeState.sx;
    var dy = e.clientY - resizeState.sy;
    var d = resizeState.dir;
    var l = resizeState.sl;
    var t = resizeState.st;
    var w = resizeState.sw;
    var h = resizeState.sh;
    if (d.indexOf("e") >= 0) w = Math.max(280, resizeState.sw + dx);
    if (d.indexOf("w") >= 0) {
      w = Math.max(280, resizeState.sw - dx);
      l = resizeState.sl + dx;
    }
    if (d.indexOf("s") >= 0) h = Math.max(260, resizeState.sh + dy);
    if (d.indexOf("n") >= 0) {
      h = Math.max(260, resizeState.sh - dy);
      t = resizeState.st + dy;
    }
    w = Math.min(w, window.innerWidth - 16);
    h = Math.min(h, window.innerHeight - 16);
    if (d.indexOf("w") >= 0 && l < 8) {
      w -= 8 - l;
      l = 8;
    }
    if (d.indexOf("n") >= 0 && t < 8) {
      h -= 8 - t;
      t = 8;
    }
    panel.style.left = l + "px";
    panel.style.top = t + "px";
    panel.style.width = w + "px";
    panel.style.height = h + "px";
  });
  resizeLayer.addEventListener("pointerup", function (e) {
    if (!resizeState || e.pointerId !== resizeState.pid) return;
    if (resizeState.el) resizeState.el.classList.remove("lvt-ai-resize--active");
    try {
      resizeState.el.releasePointerCapture(e.pointerId);
    } catch (err) {}
    resizeState = null;
    savePanelSize();
    savePanelPos();
  });

  function updateCartBanner() {
    var p = pageCtx();
    if (p.inStockLytkarino && p.addToCartSelector) {
      cartBanner.style.display = "block";
    } else {
      cartBanner.style.display = "none";
    }
  }

  cartBanner.querySelector(".lvt-ai-add-cart").addEventListener("click", function () {
    var p = pageCtx();
    var sel = p.addToCartSelector || ".js-add-all-stores-to-cart";
    var el = document.querySelector(sel);
    if (el) el.click();
    else addMsg("Не найдена кнопка корзины на странице.", "bot");
  });

  function hideInvite() {
    invite.classList.remove("lvt-ai-invite--visible");
    invite.style.display = "none";
  }

  function showInviteDelayed() {
    try {
      if (sessionStorage.getItem(inviteKey)) return;
    } catch (e) {}
    invite.style.display = "block";
    requestAnimationFrame(function () {
      invite.classList.add("lvt-ai-invite--visible");
    });
  }

  var inviteTimerId = setTimeout(showInviteDelayed, 10000);
  btn.addEventListener(
    "click",
    function () {
      clearTimeout(inviteTimerId);
    },
    { once: true }
  );

  var yesBtn = invite.querySelector(".lvt-ai-invite-yes");
  var noBtn = invite.querySelector(".lvt-ai-invite-no");
  var closeBtn = invite.querySelector(".lvt-ai-invite__close");
  var nudgeEl = invite.querySelector(".lvt-ai-invite__nudge");

  function markInviteDone() {
    try {
      sessionStorage.setItem(inviteKey, "1");
    } catch (e) {}
  }

  yesBtn.addEventListener("click", function () {
    markInviteDone();
    hideInvite();
    setPanelOpen(true);
    if (!welcomeShown) {
      welcomeShown = true;
      addMsg(welcomeText(), "bot", false);
      maybeAskDisplayName();
    }
  });

  noBtn.addEventListener("click", function () {
    markInviteDone();
    invite.classList.add("lvt-ai-invite--dim");
    var title = invite.querySelector(".lvt-ai-invite__title");
    var actions = invite.querySelector(".lvt-ai-invite__actions");
    if (title) title.style.display = "none";
    if (actions) actions.style.display = "none";
    if (nudgeEl) {
      nudgeEl.style.display = "block";
      nudgeEl.textContent = NO_TEXT;
    }
    invite.style.display = "block";
    invite.classList.add("lvt-ai-invite--visible");
    setTimeout(function () {
      invite.classList.remove("lvt-ai-invite--visible");
      setTimeout(function () {
        invite.style.display = "none";
      }, 400);
    }, 5000);
  });

  closeBtn.addEventListener("click", function () {
    markInviteDone();
    hideInvite();
  });

  function addMsg(text, who, skipStore, opts) {
    opts = opts || {};
    var d = document.createElement("div");
    var cls =
      who === "user"
        ? "user"
        : who === "system"
          ? "system"
          : who === "manager"
            ? "manager"
            : "bot";
    d.className = "lvt-ai-msg " + cls;
    if (who === "manager" && opts.operatorName) {
      var nameEl = document.createElement("div");
      nameEl.className = "lvt-ai-msg__operator-name";
      nameEl.textContent = opts.operatorName;
      d.appendChild(nameEl);
      var bodyEl = document.createElement("div");
      bodyEl.className = "lvt-ai-msg__operator-text";
      bodyEl.textContent = text;
      d.appendChild(bodyEl);
    } else {
      d.textContent = text;
    }
    messages.appendChild(d);
    messages.scrollTop = messages.scrollHeight;
    if (!skipStore && who !== "system") {
      var role = who === "user" ? "user" : "assistant";
      var prefix =
        who === "manager"
          ? "👤 " + (opts.operatorName || "Менеджер") + ": "
          : "";
      pushMessage(role, prefix + text);
    }
  }

  function restoreChat() {
    var list = loadMessages();
    for (var i = 0; i < list.length; i++) {
      var m = list[i];
      if (!m || !m.role || !m.content) continue;
      var who = m.role === "user" ? "user" : "bot";
      var d = document.createElement("div");
      d.className = "lvt-ai-msg " + (who === "user" ? "user" : "bot");
      d.textContent = m.content;
      messages.appendChild(d);
    }
    if (list.length) welcomeShown = true;
  }

  restoreChat();

  if (loadHumanModeFlag()) {
    humanMode = true;
    input.placeholder = "Сообщение оператору…";
  }

  function loadLeadDraft() {
    try {
      var raw = localStorage.getItem(leadLocalKey);
      if (!raw) return null;
      return JSON.parse(raw);
    } catch (e) {
      return null;
    }
  }

  function saveLeadDraft(o) {
    try {
      localStorage.setItem(leadLocalKey, JSON.stringify(o));
    } catch (e) {}
  }

  function prefillLeadFromProfile() {
    var box = modal.querySelector(".lvt-ai-modal__box");
    var u = window.LVT_AI_USER;
    var draft = loadLeadDraft();
    var nameEl = box.querySelector('[name="lname"]');
    var phoneEl = box.querySelector('[name="lphone"]');
    var emailEl = box.querySelector('[name="lemail"]');
    if (nameEl && !nameEl.value.trim()) {
      nameEl.value =
        (u && u.name) || (draft && draft.name) || profileName() || "";
    }
    if (phoneEl && !phoneEl.value.trim()) {
      phoneEl.value = (u && u.phone) || (draft && draft.phone) || "";
    }
    if (emailEl && !emailEl.value.trim()) {
      emailEl.value = (u && u.email) || (draft && draft.email) || "";
    }
  }

  function sendLeadToTelegram(opts) {
    var name = opts.name || "";
    var phone = opts.phone || "";
    var email = opts.email || "";
    var msg = opts.message || "";
    var onDone = typeof opts.onDone === "function" ? opts.onDone : function () {};
    if (!name || !phone) {
      onDone({ ok: false, error: "missing_profile" });
      return;
    }
    fetch(leadUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        name: name,
        phone: phone,
        email: email,
        message: msg,
        consent: true,
        session_id: sessionId(),
        product_id: productId || pageCtx().productId || 0,
        page_url: location.href,
      }),
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (j) {
        onDone(j || { ok: false, error: "bad_response" });
      })
      .catch(function () {
        onDone({ ok: false, error: "network" });
      });
  }

  function openLeadModal() {
    modal.classList.add("open");
    modal.setAttribute("aria-hidden", "false");
    modal.querySelector(".lvt-ai-modal__err").style.display = "none";
    modal.querySelector('[name="lconsent"]').checked = false;
    prefillLeadFromProfile();
  }
  function closeLeadModal() {
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden", "true");
  }

  function openAsproCallback(name, phone) {
    var $ = window.jQuery;
    var id = callbackFormId;
    if (!$ || !$.fn || !$.fn.jqmEx || !id) {
      addMsg(
        "Если форма не открылась, нажмите «Заказать звонок» в шапке сайта.",
        "bot"
      );
      return false;
    }
    var $a = $(
      '<a href="#" style="position:fixed;left:-9999px;top:0;width:1px;height:1px;overflow:hidden" data-event="jqm" data-param-id="' +
        id +
        '" data-name="callback"></a>'
    );
    $a.attr("data-autoload-client_name", name);
    $a.attr("data-autoload-phone", phone);
    $("body").append($a);
    $a[0].click();
    setTimeout(function () {
      $a.remove();
    }, 3000);
    return true;
  }

  modal.addEventListener("click", function (e) {
    if (e.target === modal) closeLeadModal();
  });
  modal.querySelector(".lvt-ai-modal-cancel").addEventListener("click", closeLeadModal);
  modal.querySelector(".lvt-ai-modal-send").addEventListener("click", function () {
    var box = modal.querySelector(".lvt-ai-modal__box");
    var name = box.querySelector('[name="lname"]').value.trim();
    var phone = box.querySelector('[name="lphone"]').value.trim();
    var email = box.querySelector('[name="lemail"]').value.trim();
    var msg = box.querySelector('[name="lmsg"]').value.trim();
    var consent = box.querySelector('[name="lconsent"]').checked;
    var err = modal.querySelector(".lvt-ai-modal__err");
    if (!name || !phone) {
      err.textContent = "Заполните имя и телефон.";
      err.style.display = "block";
      return;
    }
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      err.textContent = "Некорректный email.";
      err.style.display = "block";
      return;
    }
    if (!consent) {
      err.textContent = "Нужно согласие на обработку персональных данных.";
      err.style.display = "block";
      return;
    }
    err.style.display = "none";

    saveLeadDraft({ name: name, phone: phone, email: email, message: msg, ts: Date.now() });

    fetch(leadUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        name: name,
        phone: phone,
        email: email,
        message: msg,
        consent: true,
        session_id: sessionId(),
        product_id: productId || pageCtx().productId || 0,
        page_url: location.href,
      }),
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (j) {
        openAsproCallback(name, phone);
        closeLeadModal();
        if (j && j.ok) {
          addMsg(
            "Заявка принята: данные отправлены менеджеру. Подтвердите или дополните контакты в открывшейся форме на сайте.",
            "bot"
          );
        } else {
          addMsg(
            "Форма на сайте открыта. Отправка в мессенджер менеджеру временно недоступна — заполните форму на экране.",
            "bot"
          );
        }
      })
      .catch(function () {
        openAsproCallback(name, phone);
        closeLeadModal();
        addMsg(
          "Форма на сайте открыта. Не удалось связаться с сервером уведомлений — заполните форму на экране.",
          "bot"
        );
      });
  });

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    var raw = (input.value || "").trim();
    if (!raw) return;

    if (awaitingDisplayName) {
      var cand = raw.replace(/\s+/g, " ").trim();
      if (cand.length > 60 || cand.indexOf("?") >= 0) {
        addMsg("Пожалуйста, укажите коротко, как к вам обращаться (без вопроса).", "system", true);
        return;
      }
      setDisplayName(cand);
      awaitingDisplayName = false;
      input.value = "";
      addMsg(raw, "user");
      addMsg("Спасибо! Можете задать вопрос по товару или доставке.", "bot", false);
      return;
    }

    if (humanMode) {
      input.value = "";
      addMsg(raw, "user");
      sendHumanMessage(raw);
      return;
    }

    var safe = sanitizeOutboundMessage(raw, collectRedactNames());
    if (!safe || safe.length < 2) {
      addMsg("Сообщение не содержит запроса по товару после проверки. Уточните вопрос без личных данных.", "system", true);
      return;
    }
    input.value = "";
    addMsg(raw, "user");
    typing.textContent = "…";
    var body = {
      message: safe,
      session_id: sessionId(),
      channel: "widget",
      page_url: location.href,
      page_context: {},
    };
    var dnChat = getDisplayName() || profileName();
    if (dnChat) body.display_name = dnChat;
    var pid = productId || pageCtx().productId;
    if (pid) body.page_context.product_id = pid;
    var p = pageCtx();
    if (p.supplierLeadReady && p.supplierMinLeadDays != null && isFinite(p.supplierMinLeadDays)) {
      body.page_context.supplier_min_lead_days = p.supplierMinLeadDays;
    }
    fetch(apiUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Session-Id": sessionId(),
      },
      body: JSON.stringify(body),
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (j) {
        typing.textContent = "";
        if (j && j.error === "human_mode") {
          activateHumanFromChat("human_mode");
          sendHumanMessage(safe);
          return;
        }
        if (j && j.reply) addMsg(j.reply, "bot");
        else addMsg("Ошибка сервиса. Попробуйте позже.", "bot");
        if (j && (j.human_mode || j.handoff)) {
          activateHumanFromChat(j.handoff_reason || "эскалация от ИИ");
        }
        if (j && j.session_id)
          try {
            localStorage.setItem(storageKey, j.session_id);
          } catch (err) {}
      })
      .catch(function () {
        typing.textContent = "";
        addMsg("Нет связи с сервером.", "bot");
      });
  });
})();
