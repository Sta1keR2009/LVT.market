$(document).on("click", ".section-gallery-nav .section-gallery-nav__item", function () {
  const _this = $(this);
  const index = _this.index();
  const items = _this.closest(".image-list-wrapper").find(".section-gallery-wrapper .section-gallery-wrapper__item");

  _this.siblings().removeClass("active");
  _this.addClass("active");

  items.removeClass("active");
  items.filter(":eq(" + index + ")").addClass("active");
});
