const ajaxRequest = function (method, url, obConfig) {
  const objUrl = parseUrlQuery();
  const obData = {
    ajax_get: "Y",
    // ajax: "y",
    AJAX_REQUEST: "Y",
    AJAX_POST: "Y",
    bitrix_include_areas: "N",
    TABS_REQUEST: "Y",
  };
  const obDataExt = obConfig["data"] || {};
  const methods = method || "GET";
  let add_url = "";

  //merge data
  const obTransferData = Object.assign({}, obData, obDataExt);

  //set data to FormData
  const formData = new FormData();
  const arData = Object.keys(obTransferData);
  for (let i = 0; i < arData.length; i++) {
    formData.append(arData[i], obTransferData[arData[i]]);
  }

  //new xhr request
  const xhr = new XMLHttpRequest();

  xhr.open(methods, url + add_url);
  xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

  xhr.send(formData);

  //set event
  xhr.onload = function () {
    let responseObj = xhr.response;
    obConfig["context"].classList.remove("loading-state");
    if (xhr.status != 200) {
      obConfig["context"].innerHTML = "Error " + xhr.status + ": " + xhr.statusText;
    } else {
      const obData = BX.processHTML(responseObj);
      obConfig["context"].innerHTML = obData.HTML;
      BX.onCustomEvent("onSetHeightBanner");

      setTimeout(function () {
        BX.ajax.processScripts(obData.SCRIPT);
      }, 0);

      var eventdata = {action: 'ajaxContentLoaded', content: responseObj};
      BX.onCustomEvent('onCompleteAction', [eventdata, obConfig.tab]);
    }
  };

  //set event
  xhr.onerror = function () {
    obConfig["context"].classList.remove("loading-state");
    obConfig["context"].innerHTML = "Error";
  };
};

const readyHandler = function () {
  document.addEventListener("click", function (e) {
    const $tab = e.target.closest(".element-list .tab-nav .tab-nav__item");

    if ($tab) {
      const $tabContainer = $tab.closest(".element-list");
      const $tabItems = $tabContainer.querySelectorAll(".tab-nav__item");
      const $jsParamsBlock = $tabContainer.querySelector(".js-request-data");

      if ($tabItems.length) {
        // check target element - is tab-nav__item
        if ($tab.classList.contains("tab-nav__item") && !$tab.classList.contains("active")) {
          const tabCode = $tab.dataset["code"];
          const $tabsContent = $tabContainer.querySelectorAll(".tab-content-block");
          const $curTab = $tabContainer.querySelector(".tab-content-block[data-code='" + tabCode + "']");

          // remove all class nav link
          for (let i = 0; i < $tabItems.length; i++) {
            $tabItems[i].classList.remove("active");
          }
          //set active nav link
          $tab.classList.toggle("active");

          // remove all class nav content
          for (let i = 0; i < $tabsContent.length; i++) {
            $tabsContent[i].classList.remove("active");
          }

          //set active nav content
          $curTab.classList.toggle("active");

          // ajax request
          if (!$tab.classList.contains("clicked")) {
            const url = $jsParamsBlock.dataset.action ? window.location.origin + $jsParamsBlock.dataset.action : window.location.href;
            const action = new URL(url);
            const obData = {
              FILTER_HIT_PROP: tabCode,
              AJAX_PARAMS: $jsParamsBlock.dataset["value"],
              GLOBAL_FILTER: $curTab.dataset["filter"],
              TYPE_TEMPLATE: $tabContainer.querySelector(".tab-nav").dataset["template"],
            };

            if (document.querySelector(".body.index")) {
              const class_block = e.target.closest(".drag-block").dataset.class.replace("_drag", "").replace(/\s/g, "");
              action.searchParams.set("BLOCK", class_block);
            }
            ajaxRequest("POST", action.href, {
              data: obData,
              context: $curTab,
              tab: $tab,
            });

            //set clicked to prevent same ajax
            $tab.classList.add("clicked");
          } else {
            BX.onCustomEvent("onSetHeightBanner");
          }
        }
      }
    }
  });
};
document.addEventListener("DOMContentLoaded", readyHandler);

window.tabsInitOnReady = function (block) {
  $(".element-list[data-block_key='" + block + "'] .tab-nav-wrapper").scrollTab({
    tabs_wrapper: ".tab-nav",
    tab_item: ".tab-nav__item",
    offset: 8,
    outer_wrapper: ".element-list[data-block_key='" + block + "'] .index-block__title-wrapper",
    /*onResize: function (options) {
      const top_wrapper = options.outer_wrapper_node;
      let all_width = top_wrapper[0].getBoundingClientRect().width;

      if (top_wrapper.length) {
        const tabs_wrapper = options.scrollTab;

        if (window.matchMedia("(max-width: 767px)").matches) {
          tabs_wrapper.css({
            "max-width": all_width,
          });
          return true;
        }

        const title = top_wrapper.find(".index-block__part--left");
        // const right_link = top_wrapper.find(".index-block__part--right");

        if (title.length) {
          all_width -= title.outerWidth(true);
        }

        // if (right_link.length) {
        //   all_width -= right_link.outerWidth(true);
        // }

        all_width -= Number.parseInt(tabs_wrapper.css("margin-left"));
        all_width -= Number.parseInt(tabs_wrapper.css("margin-right"));
        tabs_wrapper.css({
          "max-width": all_width,
          width: "",
        });
      }
      options.width = all_width;
    },*/
  });
};
