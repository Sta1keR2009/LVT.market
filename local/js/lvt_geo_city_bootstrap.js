(function () {
  function siteDir() {
    if (typeof arLiteOptions !== "undefined" && arLiteOptions.SITE_DIR) {
      return String(arLiteOptions.SITE_DIR).replace(/\/?$/, "/");
    }
    if (typeof arAsproOptions !== "undefined" && arAsproOptions.SITE_DIR) {
      return String(arAsproOptions.SITE_DIR).replace(/\/?$/, "/");
    }
    return "/";
  }

  function readCookie(name) {
    var p = name + "=";
    var parts = document.cookie.split(";");
    for (var i = 0; i < parts.length; i++) {
      var c = parts[i].trim();
      if (c.indexOf(p) === 0) return c.substring(p.length);
    }
    return "";
  }

  function hasCookie(name) {
    return readCookie(name) !== "";
  }

  function getSessionFlag(name) {
    try {
      return window.sessionStorage.getItem(name) === "1";
    } catch (e) {
      return false;
    }
  }

  function setSessionFlag(name, value) {
    try {
      if (value) {
        window.sessionStorage.setItem(name, "1");
      } else {
        window.sessionStorage.removeItem(name);
      }
    } catch (e) {}
  }

  function dropCookie(name) {
    document.cookie = name + "=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax";
    document.cookie = name + "=; path=/; max-age=0; SameSite=Lax";
  }

  function decodeCityValue(raw) {
    var v = String(raw || "");
    for (var i = 0; i < 3; i++) {
      try {
        var next = decodeURIComponent(v.replace(/\+/g, " "));
        if (next === v) break;
        v = next;
      } catch (e) {
        break;
      }
    }
    return v;
  }

  function resolveCurrentCityName() {
    var city = decodeCityValue(readCookie("lvt_display_city")).trim();
    if (city) return city;

    if (readCookie("lvt_city_confirmed") === "1" || readCookie("lvt_geo_city_done") === "1") {
      var regionEl = document.querySelector(".regions__name");
      if (regionEl) {
        var regionName = String(regionEl.textContent || "").trim();
        if (regionName) return regionName;
      }

      var mobRegion = document.querySelector(".mobilemenu__menu--regions .icon-block__content > span.font_15");
      if (mobRegion) {
        var mobName = String(mobRegion.textContent || "").trim();
        if (mobName) return mobName;
      }
    }

    return "";
  }

  function getPersistedCityInput() {
    try {
      return String(sessionStorage.getItem("lvt_city_pending_input") || "").trim();
    } catch (e) {
      return "";
    }
  }

  function persistCityInput(value) {
    try {
      var v = String(value || "").trim();
      if (v) {
        sessionStorage.setItem("lvt_city_pending_input", v);
      } else {
        sessionStorage.removeItem("lvt_city_pending_input");
      }
    } catch (e) {}
  }

  function clearPersistedCityInput() {
    try {
      sessionStorage.removeItem("lvt_city_pending_input");
    } catch (e) {}
  }

  function resolveInitialCityName() {
    return getPersistedCityInput() || resolveCurrentCityName();
  }

  function isCityInputLocked() {
    if (window.lvtCityApplying) return true;
    if (window.lvtCityUserEditing) return true;
    var active = document.activeElement;
    return !!(active && active.classList && active.classList.contains("lvt-city-input"));
  }

  function markCityUserEditing() {
    window.lvtCityUserEditing = true;
  }

  function getDisplayCity() {
    var city = resolveInitialCityName() || resolveCurrentCityName();
    return city || "Москва";
  }

  function updateAllCityInputs(city, force) {
    if (!force && isCityInputLocked()) return;
    document.querySelectorAll(".lvt-city-input").forEach(function (input) {
      if (!force && input === document.activeElement) return;
      if (!force && input.dataset && input.dataset.lvtDirty === "1") return;
      input.value = String(city || "");
      var wrap = input.closest("#lvt-city-control, #lvt-city-control-mobile, .lvt-city-control");
      if (!wrap) return;
      var clearBtn = wrap.querySelector(".lvt-city-clear");
      if (clearBtn) {
        clearBtn.style.display = input.value.trim() ? "block" : "none";
      }
    });
  }

  function isMobileView() {
    return window.matchMedia("(max-width: 991px)").matches;
  }

  function findCityControlHost() {
    return document.getElementById("lvt-city-control") || document.getElementById("lvt-city-control-mobile");
  }

  function applySaleCode(code) {
    if (!code || typeof BX === "undefined" || typeof BX.bitrix_sessid !== "function") {
      return Promise.resolve(false);
    }
    window.lvtCityApplying = true;
    var fd = new FormData();
    fd.append("sale_location_code", code);
    fd.append("sessid", BX.bitrix_sessid());
    return fetch(siteDir() + "local/api/apply_sale_city.php", {
      method: "POST",
      body: fd,
      credentials: "same-origin",
      cache: "no-store",
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data && data.ok) {
          clearPersistedCityInput();
          window.lvtCityUserEditing = false;
          document.cookie = "lvt_city_confirmed=1; path=/; max-age=31536000; SameSite=Lax";
          window.location.reload();
          return true;
        }
        window.lvtCityApplying = false;
        return false;
      })
      .catch(function () {
        window.lvtCityApplying = false;
        return false;
      });
  }

  function applyCityByName(cityName) {
    var city = String(cityName || "").trim();
    if (city.length < 2) {
      return Promise.resolve(false);
    }
    return fetch(siteDir() + "local/api/sale_location_suggest.php?term=" + encodeURIComponent(city), {
      credentials: "same-origin",
      cache: "no-store",
    })
      .then(function (r) { return r.json(); })
      .then(function (items) {
        if (!Array.isArray(items) || !items.length) return false;
        var hit = items[0];
        return applySaleCode(hit.saleCode || hit.code || "");
      })
      .catch(function () { return false; });
  }

  function setCityDefaultMoscow() {
    return applyCityByName("Москва");
  }

  function openCityModal(initialValue, opts) {
    opts = opts || {};
    var modalTitle = opts.title || "Выберите ваш город";
    if (document.getElementById("lvt-city-modal")) return;

    var root = document.createElement("div");
    root.id = "lvt-city-modal";
    root.className = "lvt-city-modal";
    root.innerHTML =
      '<div class="lvt-city-modal__dialog">' +
      '<div class="lvt-city-modal__head">' + modalTitle.replace(/</g, "&lt;").replace(/>/g, "&gt;") + "</div>" +
      '<div class="lvt-city-modal__body">' +
      '<input id="lvt-city-modal-input" class="lvt-city-modal-input" type="text" autocomplete="off" placeholder="Введите город">' +
      '<div id="lvt-city-modal-list" class="lvt-city-modal-list"></div>' +
      '</div>' +
      '<div class="lvt-city-modal__footer">' +
      '<button type="button" id="lvt-city-modal-cancel" class="btn btn-transparent-border btn-sm">Отмена</button>' +
      '</div>' +
      '</div>';
    document.body.appendChild(root);

    var input = document.getElementById("lvt-city-modal-input");
    var list = document.getElementById("lvt-city-modal-list");
    var cancel = document.getElementById("lvt-city-modal-cancel");
    if (!input || !list) return;
    input.value = String(initialValue || "").trim();
    input.focus();

    function close() {
      if (root && root.parentNode) root.parentNode.removeChild(root);
    }
    function hideList() {
      list.style.display = "none";
      list.innerHTML = "";
    }
    function renderItems(items) {
      if (!items.length) {
        list.innerHTML = '<div class="lvt-city-list-empty">Ничего не найдено</div>';
        list.style.display = "block";
        return;
      }
      var html = "";
      for (var i = 0; i < items.length; i++) {
        var it = items[i];
        var code = String(it.saleCode || it.code || "");
        var label = String(it.label || "");
        if (!code || !label) continue;
        html += '<button type="button" class="lvt-city-modal-item" data-code="' + code.replace(/"/g, "&quot;") + '" data-label="' + label.replace(/"/g, "&quot;") + '">' + label + "</button>";
      }
      list.innerHTML = html || '<div class="lvt-city-list-empty">Ничего не найдено</div>';
      list.style.display = "block";
    }
    function search(term) {
      var q = String(term || "").trim();
      if (q.length < 2) {
        hideList();
        return;
      }
      fetch(siteDir() + "local/api/sale_location_suggest.php?term=" + encodeURIComponent(q), {
        credentials: "same-origin",
        cache: "no-store",
      })
        .then(function (r) { return r.json(); })
        .then(function (items) { renderItems(Array.isArray(items) ? items : []); })
        .catch(function () { hideList(); });
    }

    var t = null;
    input.addEventListener("input", function () {
      clearTimeout(t);
      t = setTimeout(function () { search(input.value); }, 180);
    });
    list.addEventListener("click", function (e) {
      var btn = e.target.closest(".lvt-city-modal-item");
      if (!btn) return;
      var code = btn.getAttribute("data-code") || "";
      applySaleCode(code).then(function (ok) {
        if (!ok) window.alert("Не удалось применить город. Попробуйте еще раз.");
      });
    });
    if (cancel) cancel.addEventListener("click", close);
    root.addEventListener("click", function (e) {
      if (e.target === root) close();
    });
  }

  function openCityConfirmPopup() {
    if (getSessionFlag("lvt_city_confirm_opened")) return;
    if (readCookie("lvt_city_confirmed") === "1") return;
    if (document.getElementById("lvt-city-confirm-popup")) return;

    var city = getDisplayCity();
    var cityHost = findCityControlHost();
    var host = cityHost || document.querySelector(".header__top-item--regions") || document.querySelector(".header__top-inner") || document.querySelector("header");
    var fixedFallback = !cityHost;

    var wrap = document.createElement("div");
    wrap.id = "lvt-city-confirm-popup";
    if (fixedFallback) {
      wrap.className = "lvt-city-confirm-popup lvt-city-confirm-popup--fixed";
      wrap.innerHTML =
        '<div class="lvt-city-confirm-popup__text">Ваш город <b>' + city.replace(/</g, "&lt;").replace(/>/g, "&gt;") + "</b>?</div>" +
        '<div class="lvt-city-confirm-popup__actions">' +
        '<button type="button" id="lvt-city-confirm-yes" class="btn btn-default btn-sm">Все верно</button>' +
        '<button type="button" id="lvt-city-confirm-change" class="btn btn-transparent-border btn-sm">Сменить город</button>' +
        "</div>";
      document.body.appendChild(wrap);
      setSessionFlag("lvt_city_confirm_opened", true);

      function close() {
        if (wrap && wrap.parentNode) wrap.parentNode.removeChild(wrap);
      }

      var yesBtn = document.getElementById("lvt-city-confirm-yes");
      var changeBtn = document.getElementById("lvt-city-confirm-change");
      if (yesBtn) {
        yesBtn.addEventListener("click", function () {
          document.cookie = "lvt_city_confirmed=1; path=/; max-age=31536000; SameSite=Lax";
          close();
        });
      }
      if (changeBtn) {
        changeBtn.addEventListener("click", function () {
          close();
          openCityModal(city, { title: "Выберите ваш город" });
        });
      }

      setTimeout(function () {
        document.addEventListener("click", function onDocClick(e) {
          if (!wrap.contains(e.target)) {
            document.removeEventListener("click", onDocClick);
            close();
          }
        });
      }, 400);
      return;
    }

    wrap.className = "lvt-city-confirm-popup";
    wrap.innerHTML =
      '<div class="lvt-city-confirm-popup__arrow">▲</div>' +
      '<div class="lvt-city-confirm-popup__text">Ваш город <b>' + city.replace(/</g, "&lt;").replace(/>/g, "&gt;") + "</b>?</div>" +
      '<div class="lvt-city-confirm-popup__actions">' +
      '<button type="button" id="lvt-city-confirm-yes" class="btn btn-default btn-sm">Все верно</button>' +
      '<button type="button" id="lvt-city-confirm-change" class="btn btn-transparent-border btn-sm">Сменить город</button>' +
      "</div>";

    if (host && getComputedStyle(host).position === "static") {
      host.style.position = "relative";
    }
    if (host) host.appendChild(wrap);
    setSessionFlag("lvt_city_confirm_opened", true);

    function close() {
      if (wrap && wrap.parentNode) wrap.parentNode.removeChild(wrap);
    }

    var yesBtn = document.getElementById("lvt-city-confirm-yes");
    var changeBtn = document.getElementById("lvt-city-confirm-change");
    if (yesBtn) {
      yesBtn.addEventListener("click", function () {
        document.cookie = "lvt_city_confirmed=1; path=/; max-age=31536000; SameSite=Lax";
        close();
      });
    }
    if (changeBtn) {
      changeBtn.addEventListener("click", function () {
        close();
        openCityModal(city, { title: "Выберите ваш город" });
      });
    }

    setTimeout(function () {
      document.addEventListener("click", function onDocClick(e) {
        if (!wrap.contains(e.target)) {
          document.removeEventListener("click", onDocClick);
          close();
        }
      });
    }, 400);
  }

  function findHeaderSearchNode() {
    return (
      document.querySelector(".header__top-part .header__search") ||
      document.querySelector(".header__search.header__top-item") ||
      document.querySelector(".header__search") ||
      document.querySelector(".header__main-item.header__search") ||
      document.querySelector("[class*='header__search']")
    );
  }

  function findMobileHeaderNode() {
    return (
      document.querySelector("#mobileheader .mobileheader") ||
      document.querySelector(".mobileheader")
    );
  }

  function buildCityControlMarkup() {
    return (
      '<input class="lvt-city-input" type="text" autocomplete="off" placeholder="Введите город" />' +
      '<button class="lvt-city-clear" type="button" aria-label="Очистить">×</button>' +
      '<div class="lvt-city-list"></div>'
    );
  }

  function attachCityControl(wrap, initialCity) {
    var input = wrap.querySelector(".lvt-city-input");
    var list = wrap.querySelector(".lvt-city-list");
    var clearBtn = wrap.querySelector(".lvt-city-clear");
    if (!input || !list) return false;

    if (initialCity) input.value = initialCity;

    function toggleClear() {
      if (!clearBtn) return;
      clearBtn.style.display = input.value.trim() ? "block" : "none";
    }
    toggleClear();

    var timer = null;
    var latestReq = 0;

    input.addEventListener("focus", markCityUserEditing);
    input.addEventListener("input", function () {
      input.dataset.lvtDirty = "1";
      markCityUserEditing();
      persistCityInput(input.value);
      toggleClear();
      clearTimeout(timer);
      timer = setTimeout(function () { search(input.value); }, 180);
    });

    function hideList() {
      list.style.display = "none";
      list.innerHTML = "";
    }

    function renderItems(items) {
      if (!items.length) {
        list.innerHTML = '<div class="lvt-city-list-empty">Ничего не найдено</div>';
        list.style.display = "block";
        return;
      }
      var html = "";
      for (var i = 0; i < items.length; i++) {
        var it = items[i];
        var code = String(it.saleCode || it.code || "");
        var label = String(it.label || "");
        if (!code || !label) continue;
        html += '<button type="button" class="lvt-city-item" data-code="' + code.replace(/"/g, "&quot;") + '" data-label="' + label.replace(/"/g, "&quot;") + '">' + label + "</button>";
      }
      list.innerHTML = html || '<div class="lvt-city-list-empty">Ничего не найдено</div>';
      list.style.display = "block";
    }

    function search(term) {
      var q = String(term || "").trim();
      if (q.length < 2) {
        hideList();
        return;
      }
      latestReq += 1;
      var reqId = latestReq;
      fetch(siteDir() + "local/api/sale_location_suggest.php?term=" + encodeURIComponent(q), {
        credentials: "same-origin",
        cache: "no-store",
      })
        .then(function (r) { return r.json(); })
        .then(function (items) {
          if (reqId !== latestReq) return;
          renderItems(Array.isArray(items) ? items : []);
        })
        .catch(function () {
          if (reqId !== latestReq) return;
          hideList();
        });
    }

    input.addEventListener("blur", function () {
      window.setTimeout(function () {
        if (!window.lvtCityApplying && document.activeElement !== input) {
          window.lvtCityUserEditing = false;
        }
      }, 150);
    });

    if (clearBtn) {
      clearBtn.addEventListener("click", function () {
        input.value = "";
        input.dataset.lvtDirty = "0";
        clearPersistedCityInput();
        toggleClear();
        hideList();
        dropCookie("current_region");
        dropCookie("lvt_display_city");
        dropCookie("lvt_sale_location_code");
        dropCookie("lvt_geo_city_done");
        dropCookie("lvt_city_confirmed");
        setSessionFlag("lvt_geo_auto_reloaded", false);
        setSessionFlag("lvt_geo_auto_inflight", false);
        setSessionFlag("lvt_city_confirm_opened", false);

        setTimeout(function () {
          openCityModal("", { title: "Выберите ваш город" });
        }, 0);
      });
    }

    list.addEventListener("click", function (e) {
      var btn = e.target.closest(".lvt-city-item");
      if (!btn) return;
      var code = btn.getAttribute("data-code") || "";
      var label = btn.getAttribute("data-label") || "";
      markCityUserEditing();
      if (label) {
        input.value = label;
        persistCityInput(label);
      }
      hideList();
      applySaleCode(code).then(function (ok) {
        if (!ok) {
          window.alert("Не удалось применить город. Попробуйте еще раз.");
        }
      });
    });

    document.addEventListener("click", function onDocClick(e) {
      if (!wrap.contains(e.target)) hideList();
    });

    return true;
  }

  function renderCityControl() {
    if (isMobileView()) return false;
    var searchNode = findHeaderSearchNode();
    if (!searchNode) return false;
    if (document.getElementById("lvt-city-control")) return true;

    var currentCity = resolveInitialCityName();
    var wrap = document.createElement("div");
    wrap.id = "lvt-city-control";
    wrap.className = "lvt-city-control";
    wrap.innerHTML = buildCityControlMarkup();
    searchNode.parentNode.insertBefore(wrap, searchNode);
    return attachCityControl(wrap, currentCity);
  }

  function renderMobileCityControl() {
    if (!isMobileView()) return false;
    var mobileHeader = findMobileHeaderNode();
    if (!mobileHeader) return false;
    if (document.getElementById("lvt-city-control-mobile")) return true;

    var currentCity = resolveInitialCityName();
    var row = document.createElement("div");
    row.className = "mobileheader__city-row";

    var wrap = document.createElement("div");
    wrap.id = "lvt-city-control-mobile";
    wrap.className = "lvt-city-control lvt-city-control--mobile";
    wrap.innerHTML = buildCityControlMarkup();
    row.appendChild(wrap);
    mobileHeader.appendChild(row);
    return attachCityControl(wrap, currentCity);
  }

  function renderAllCityControls() {
    var ok = false;
    if (isMobileView()) {
      ok = renderMobileCityControl() || ok;
    } else {
      ok = renderCityControl() || ok;
    }
    if (ok && getPersistedCityInput()) {
      updateAllCityInputs(getPersistedCityInput(), true);
    }
    return ok;
  }

  function scheduleCityConfirmPopup() {
    var tries = 0;
    function tick() {
      tries += 1;
      if (!findCityControlHost()) {
        renderAllCityControls();
      }
      if (findCityControlHost() || tries >= 50) {
        openCityConfirmPopup();
        return;
      }
      setTimeout(tick, 120);
    }
    setTimeout(tick, 200);
  }

  function autoDetect(force) {
    force = !!force;
    if (getSessionFlag("lvt_geo_auto_inflight")) {
      return Promise.resolve(false);
    }

    if (!force && readCookie("lvt_city_confirmed") === "1") {
      return Promise.resolve(false);
    }
    if (!force && hasCookie("lvt_geo_city_done") && hasCookie("lvt_display_city")) {
      return Promise.resolve(false);
    }
    if (typeof BX === "undefined" || typeof BX.bitrix_sessid !== "function") {
      return Promise.resolve(false);
    }

    var fd = new FormData();
    fd.append("auto", "1");
    fd.append("sessid", BX.bitrix_sessid());
    setSessionFlag("lvt_geo_auto_inflight", true);
    return fetch(siteDir() + "local/api/apply_sale_city.php", {
      method: "POST",
      body: fd,
      credentials: "same-origin",
      cache: "no-store",
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        setSessionFlag("lvt_geo_auto_inflight", false);
        if (data && data.ok) {
          clearPersistedCityInput();
          var detectedCity = String(data.displayCity || data.city || "").trim();
          if (detectedCity && !isCityInputLocked()) {
            updateAllCityInputs(detectedCity, true);
            syncRegionTitles(detectedCity);
          }
        }
        return !!(data && data.ok);
      })
      .catch(function () {
        setSessionFlag("lvt_geo_auto_inflight", false);
        return false;
      });
  }

  function syncRegionTitles(cityName) {
    var name = String(cityName || resolveCurrentCityName() || "").trim();
    if (!name) return;
    document.querySelectorAll(".regions__name").forEach(function (el) {
      el.textContent = name;
    });
    var mob = document.querySelector(".mobilemenu__menu--regions .icon-block__content > span.font_15");
    if (mob) mob.textContent = name;
  }

  window.lvtApplyCityByName = applyCityByName;
  window.lvtOpenCityModal = openCityModal;
  window.lvtRenderCityControl = renderAllCityControls;

  function boot() {
    renderAllCityControls();
    if (readCookie("lvt_city_confirmed") === "1") {
      syncRegionTitles();
      if (!getPersistedCityInput()) {
        updateAllCityInputs(resolveCurrentCityName());
      }
      watchHeaderForCityControl();
      return;
    }

    if (!hasCookie("lvt_geo_city_done") || !hasCookie("lvt_display_city")) {
      autoDetect().then(function () {
        scheduleCityConfirmPopup();
      });
    } else {
      syncRegionTitles();
      if (!getPersistedCityInput()) {
        updateAllCityInputs(resolveCurrentCityName());
      }
      scheduleCityConfirmPopup();
    }
    watchHeaderForCityControl();
  }

  function watchHeaderForCityControl() {
    if (window.lvtGeoCityHeaderObserver) return;
    var timer = null;
    window.lvtGeoCityHeaderObserver = new MutationObserver(function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        if (!findCityControlHost()) {
          renderAllCityControls();
        }
      }, 120);
    });
    window.lvtGeoCityHeaderObserver.observe(document.body, { childList: true, subtree: true });
  }

  window.lvtGeoCityBoot = boot;

  if (typeof BX !== "undefined" && BX.ready) {
    BX.ready(boot);
  } else if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
