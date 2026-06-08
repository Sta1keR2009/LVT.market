$(document).on("click", ".mobilemenu__menu-item--city a", function (e) {
  e.preventDefault();

  const _this = $(this);
  const id = _this.data("id");
  if (typeof window.asproLiteSetCurrentRegionCookie === "function") {
    window.asproLiteSetCurrentRegionCookie(id);
  } else {
    $.removeCookie("current_region", { path: "/" });
    $.cookie("current_region", id, { path: "/" });
  }
});



BX.addCustomEvent("onBefore.moveMobileMenuWrapNext.updateScrollerHeight", (e) => {
  const { bMobileRegions, $fixedCities } = getMobileRegionParams(e.dropdownNext);

  if (bMobileRegions) {
    const height = e.menu.height() - $fixedCities.position().top - parseInt(e.dropdownNext.css("padding-bottom"));
    $fixedCities.css("height", height + "px");
  }
});

BX.addCustomEvent("onAfter.moveMobileMenuWrapNext.updateScrollerHeight", e => init(e));

function ensureJqueryUiAutocomplete() {
  if (typeof $ !== "undefined" && $.fn && typeof $.fn.autocomplete === "function") {
    return Promise.resolve();
  }
  return new Promise(function (resolve, reject) {
    if (typeof BX !== "undefined" && typeof BX.loadExt === "function") {
      BX.loadExt(["aspro_ui"])
        .then(resolve)
        .catch(function () {
          var base =
            typeof arAsproOptions !== "undefined" && arAsproOptions.SITE_TEMPLATE_PATH
              ? arAsproOptions.SITE_TEMPLATE_PATH
              : "/bitrix/templates/aspro-lite";
          var s = document.createElement("script");
          s.src = String(base).replace(/\/?$/, "/") + "js/jquery-ui.min.js";
          s.onload = function () {
            resolve();
          };
          s.onerror = reject;
          document.head.appendChild(s);
        });
    } else {
      reject(new Error("no BX"));
    }
  });
}

const init = (() => {
  return function (e) {
    const { bMobileRegions, $fixedCities } = getMobileRegionParams(e.dropdownNext);
    if (!bMobileRegions) {
      return;
    }

    const $search = $("#mobile-region-search");
    if (!$search.length || $search.data("ui-autocomplete")) {
      return;
    }

    setTimeout(() => $fixedCities.css("overflow", ""), 200);

    const $appendTarget = $(".mobilemenu__menu--regions .js-autocomplete-block-mobile");

    ensureJqueryUiAutocomplete()
      .then(() => {
        if (!$search.length || $search.data("ui-autocomplete")) {
          return;
        }
        const siteDir = String(arAsproOptions["SITE_DIR"] || "/").replace(/\/?$/, "/");
        $search.autocomplete({
          minLength: 2,
          source(request, response) {
            $.getJSON(`${siteDir}local/api/sale_location_suggest.php`, {
              term: request.term,
            })
              .done(data => {
                if (Array.isArray(data) && data.length) {
                  response(data);
                } else {
                  $(".mobilemenu__menu--regions .dropdown .mobile-cities").hide().siblings().empty().show();
                }
              })
              .fail(() => {
                $(".mobilemenu__menu--regions .dropdown .mobile-cities").hide().siblings().empty().show();
              });
          },
          select(event, ui) {
            event.preventDefault();
            const code = ui.item.saleCode || ui.item.code;
            if (!code || typeof BX === "undefined" || !BX.bitrix_sessid) {
              return false;
            }
            $.post(`${siteDir}local/api/apply_sale_city.php`, {
              sessid: BX.bitrix_sessid(),
              sale_location_code: code,
            }).done(res => {
              if (res && res.ok) {
                if (typeof window.asproLiteSetCurrentRegionCookie === "function") {
                  window.asproLiteSetCurrentRegionCookie(String(res.regionId));
                }
                location.reload();
              }
            });
            return false;
          },
          appendTo: $appendTarget.length ? $appendTarget : document.body,
          position: { my: "left top", at: "left bottom", collision: "flip" },
          open() {
            $(".mobilemenu__menu--regions .dropdown .mobile-cities").hide().siblings().show();
          },
          close() {
            $(".mobilemenu__menu--regions .dropdown .mobile-cities").siblings().show();
          },
          classes: {
            "ui-autocomplete": "dropdown-city-autocomplete",
          },
        }).data("ui-autocomplete")._renderItem = (ul, item) => {
          const code = item.saleCode || item.code;
          if (code) {
            return $("<li>")
              .append(
                `<a href="#" class="dark_link" data-sale-code="${code}"><div class="flexbox flexbox--row flexbox--justify-beetwen"><span class="font_15 ">${item.label}</span></div>${item.REGION ? `<div class="color_999 font_13">${item.REGION}</div>` : ""}</a>`
              )
              .appendTo(ul);
          }
          return $(renderCityItem(item)).appendTo(ul);
        };

        if ($(".mobilemenu__menu--regions .dropdown .loadings").length) {
          getMainCities();
        }
      })
      .catch(function () {});
  };
})();

function getMainCities() {
  const url = location.pathname + location.search;
  const $loadings = $(".mobilemenu__menu--regions .dropdown .loadings").closest("li");

  if (!$loadings.length) return;

  $.getJSON(arAsproOptions["SITE_DIR"] + "ajax/city_select.php", {
    term: "",
    url: url,
  }).done(data => {
    if (Array.isArray(data) && data.length) {
      const items = data.map(renderCityItem).join("");
      $(items).insertBefore($loadings);
      $loadings.remove();
    }
  });
};

function getMobileRegionParams($dropdownNext) {
  const $regionWrapper = $dropdownNext.closest(".mobilemenu__menu--regions");
  const bMobileRegions = $regionWrapper.length > 0;
  const $fixedCities = bMobileRegions ? $dropdownNext.find(".menu-item-fixed") : null;

  return { bMobileRegions, $fixedCities };
}

function renderCityItem(item) {
  return `
    <li class="mobilemenu__menu-item mobilemenu__menu-item--city ${item.CURRENT ? ' mobilemenu__menu-item--city--selected' : ''}">
      <div class="link-wrapper">

        <a href="${item.HREF}" class="dark_link" rel="nofollow" data-id="${item.ID}">
        <div class="flexbox flexbox--row flexbox--justify-beetwen">
          <span class="font_15 ">${item.label}</span>
          ${item.CURRENT ? `<i class='svg inline stroke-dark-light' aria-hidden="true"><svg width='12' height='9'><use xlink:href='${arAsproOptions.SITE_TEMPLATE_PATH}/images/svg/check.svg#check'></use></svg></i>` : ""}
          </div>
          ${item.REGION ? `<div class="color_999 font_13">${item.REGION}</div>` : ""}
        </a>
      </div>
    </li>
  `;
}

$(document).on("click", ".mobilemenu__menu--regions .menu_autocomplete .clean_icon", function () {
  const $input = $(this).closest(".wrapper").find("input[type=text]");
  $input.val("").focus().trigger("change");
  $(this).hide();
});

$(document).on("keyup change paste", ".mobilemenu__menu--regions .menu_autocomplete input[type=text]", function () {
  const $btn = $(this).closest(".wrapper").find(".clean_icon");
  const $dropdown = $(this).closest(".dropdown");

  if ($(this).val().length) {
    $btn.show();
  } else {
    $btn.hide();
    $dropdown.find(".mobile-cities").show().siblings().hide().empty();
  }
});
