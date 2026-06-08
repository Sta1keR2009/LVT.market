// (function () {
// "use strict";
BX.addCustomEvent("onShowBasketHeadingBtn", function (eventdata) {
  addTextToPrintBtn();   
});

function addTextToPrintBtn() {
  const $print = document.querySelector(".btn_basket_heading--print");
  if ($print) {
    if (!$print.classList.contains("btn_heading--with_title")) {
      const $title = BX.create({
        tag: "span",
        attrs: {
          class: "title",
        },
        html: arAsproOptions["THEME"]["EXPRESSION_FOR_PRINT_PAGE"],
      });
      $print.classList.add("btn_heading--with_title");
      $print.appendChild($title);
    }
  }
}
// })();
