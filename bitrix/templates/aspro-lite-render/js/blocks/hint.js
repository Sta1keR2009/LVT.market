
$(document).ready(function () {
  $("html, body").on("mousedown", function (e) {
    if (typeof e.target.className == "string" && e.target.className.indexOf("adm") < 0) {
      e.stopPropagation();
      if (!$(e.target).closest(".hint.active").length) {
        $(".hint.active .hint__icon").trigger("click");
      }
    }
  })

  $(document).on("click", ".hint", function (e) {
    let _this = $(this);
    let target = e.target;
    
    e.stopImmediatePropagation();
    if (!(target.tagName === 'A')) {
      e.preventDefault();
    }
    
    if (!target.closest('.tooltip')) { 
      _this.toggleClass("active").find(".tooltip").stop().slideToggle(200);
    }
  });
})