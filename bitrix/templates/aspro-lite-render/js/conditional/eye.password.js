BX.ready(function () {
  $(".form-group:not(.eye-password-ignore) [type=password]").each(function (item) {
    let inputBlock = $(this).closest(".input");
    if (inputBlock.length) {
      inputBlock.addClass("eye-password");
    } else {
      let passBlock = $(this).closest(".form-group");
      let labelBlock = passBlock.find(".label_block");
      if (labelBlock.length) {
        labelBlock.addClass("eye-password");
      } else {
        passBlock.addClass("eye-password");
      }
    }
  });

  $(document).on("click", ".eye-password:not(.eye-password-ignore)", function (event) {
    let input = this.querySelector("input");
    let eyeWidth = 56;
    if (this.clientWidth - eyeWidth < event.offsetX) {
      if (input.type == "password") {
        input.type = "text";
        this.classList.add("password-show");
      } else if (input.type == "text") {
        input.type = "password";
        this.classList.remove("password-show");
      }
      event.stopPropagation();
    }
  });
});
