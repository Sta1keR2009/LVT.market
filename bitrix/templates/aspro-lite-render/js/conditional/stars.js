$(document).on("click", ".form .votes_block.with-text .item-rating", function () {
  var $this = $(this),
    rating = $this.closest(".votes_block").data("rating"),
    index = $this.index() + 1,
    ratingMessage = $this.data("message");

  $this.closest(".votes_block").data("rating", index);
  if ($this.closest(".form-group").find("input[name=RATING]").length) {
    $this.closest(".form-group").find("input[name=RATING]").val(index);
  } else {
    $this.closest(".form-group").find("input[data-sid=RATING]").val(index);
  }
  $this.closest(".votes_block").find(".rating_message").data("message", ratingMessage);
  $this.closest(".form").find(".error").remove();

  $(this).addClass("rating__star-svg--filled");
  $this.siblings().each(function () {
    if ($(this).index() <= index - 1) $(this).addClass("rating__star-svg--filled");
    else $(this).removeClass("rating__star-svg--filled");
  });
  $this.closest(".votes_block").find(".rating_message").text(ratingMessage);
});
