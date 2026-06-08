BX.ready(function () {
  BX.Aspro.Loader.addExt(["validate"]).then(() => {
    $("form.subscribe-form").validate({
      rules: {
        EMAIL: {
          email: true,
        },
      },
      messages: {
        licenses_subscribe: {
          required: BX.message("JS_REQUIRED_LICENSES"),
        },
      },
    });
  });
});
