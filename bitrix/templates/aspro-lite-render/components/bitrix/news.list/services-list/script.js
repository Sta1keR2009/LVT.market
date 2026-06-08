$(document).ready(function () {
  $(document).on(
    "mousewheel mouseenter mouseleave mousemove touchstart touchmove",
    ".services-list__item--has-additional-text",
    function (e) {
      var $scroll = $(this).find(".scroll-deferred:not(.mCustomScrollbar)");
      if ($scroll.length) {
        $scroll.mCustomScrollbar($scroll.data("plugin-options"));
      }
    }
  );

  $(document).on("click", ".services-list__item--has-additional-text", function (e) {
    if (e && e.target && e.target.tagName !== "A" && !$(e.target).closest(".mCSB_scrollTools").length) {
      var $link = $(this).find(".services-list__item-title a");
      if ($link.length) {
        if (
          (window.getSelection && !window.getSelection().toString()) ||
          (document.selection && !document.selection.createRange().text)
        ) {
          location.href = $link.attr("href");
        }
      }
    }
  });
});
