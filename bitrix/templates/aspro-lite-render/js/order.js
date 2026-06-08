function orderAction(e) {
  const showPhoneMask = (className) => {
    if (typeof appAspro === "object" && appAspro && appAspro.phone) {
      appAspro.phone.init($(className));
    }
  };

  //phone
  if ($("#bx-soa-order input[autocomplete=tel]").length) {
    // get property phone
    var arPropertyPhone = null;
    for (var i = 0; i < BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.length; ++i) {
      if (BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties[i].IS_PHONE == "Y") {
        arPropertyPhone = BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties[i];
      }
    }

    // validate input type=tel
    if (
      typeof BX.Sale.OrderAjaxComponent === "object" &&
      typeof arPropertyPhone == "object" &&
      arPropertyPhone
    ) {
      const ext = ['aspro_phone_input'];
      if (arAsproOptions.THEME.PHONE_MASK.length) {
        ext.push('aspro_phone_mask');
      }
      if (arAsproOptions.THEME.USE_INTL_PHONE === 'Y') {
        ext.push('aspro_intl_phone_input');
      }

      BX.loadExt(ext).then(() => {
        if (
          typeof appAspro === 'object' &&
          appAspro && appAspro.phone
        ) {
          BX.Sale.OrderAjaxComponent.validatePhone = function (input, arProperty, fieldName) {
            if (!input || !arProperty) return [];
            var value = input.value,
              errors = [],
              name = BX.util.htmlspecialchars(arProperty.NAME),
              field = BX.message("SOA_FIELD") + ' "' + name + '"';

            if (arProperty.REQUIRED == "Y" && value.length == 0) {
              errors.push(field + " " + BX.message("SOA_REQUIRED"));
            }

            if (arProperty.IS_PHONE == "Y" && value.length > 0) {

            this.validateNormalPhone(input, errors, field)
              this.validateIntlPhone(input, errors, field)
            }

            return errors;
          };

          BX.Sale.OrderAjaxComponent.validateNormalPhone = function (input, errors, field) {

            if (arAsproOptions["THEME"]["PHONE_MASK"]
                  && arAsproOptions["THEME"]["VALIDATE_PHONE_MASK"]
                  && !appAspro.phone.checkIntlPhone
                ) {
              var validPhone = new RegExp(arAsproOptions["THEME"]["VALIDATE_PHONE_MASK"]).test($(input).val());

              if (!validPhone) {
                errors.push(field + " " + BX.message("JS_FORMAT_ORDER"));
              }
            }
          };

          BX.Sale.OrderAjaxComponent.validateIntlPhone = function (input, errors, field) {
            if (appAspro.phone.checkIntlPhone) {
              const telInput = $(input).data('iti');
              if (typeof telInput === 'object' && telInput) {
                const validPhone = telInput.isValidNumber()
                if (!validPhone) {
                  errors.push(BX.message(appAspro.phone.errorMap[telInput.getValidationError()]));
                }
              }
            }
          };

          BX.Sale.OrderAjaxComponent.getValidationDataPhone = function (arProperty, propContainer) {
            var data = {},
              inputs;
            switch (arProperty.TYPE) {
              case "STRING":
                data.action = "blur";
                data.func = BX.delegate(function (input, fieldName) {
                  return this.validatePhone(input, arProperty, fieldName);
                }, this);

                inputs = propContainer.querySelectorAll("input[type=tel]");
                if ($(inputs).length) {
                  data.inputs = inputs;
                  break;
                }
            }

            return data;
          };

          BX.Sale.OrderAjaxComponent.bindValidationPhone = function (id, propContainer) {
            if (!this.validation.properties || !this.validation.properties[id]) return;

            var arProperty = this.validation.properties[id],
              data = this.getValidationDataPhone(arProperty, propContainer),
              i,
              k;

            if (data && data.inputs && data.action) {
              for (let i = 0, c = data.inputs.length; i < c; ++i) {
                if (BX.type.isElementNode(data.inputs[i])) {
                  appAspro.phone.init($(data.inputs[i]), {
                    useValidate: false,
                    async: false,
                  });

                  BX.bind(
                    data.inputs[i],
                    data.action,
                    BX.delegate(function () {
                      this.isValidProperty(data);
                    }, this)
                  );
                }
              }
            }
          };

          BX.Sale.OrderAjaxComponent.isValidPropertiesBlock = function (excludeLocation) {
            if (!this.options.propertyValidation) return [];

            var props = this.orderBlockNode.querySelectorAll(".bx-soa-customer-field[data-property-id-row]"),
              propsErrors = [],
              id,
              propContainer,
              arProperty,
              data,
              i;

            for (i = 0; i < props.length; i++) {
              id = props[i].getAttribute("data-property-id-row");

              if (!!excludeLocation && this.locations[id]) continue;

              propContainer = props[i].querySelector(".soa-property-container");
              if (propContainer) {
                arProperty = this.validation.properties[id];
                if (typeof arProperty !== 'undefined') {
                  data = this.getValidationData(arProperty, propContainer);
                  dataPhone = this.getValidationDataPhone(arProperty, propContainer);
                  data = $.extend({}, data, dataPhone);
                  propsErrors = propsErrors.concat(this.isValidProperty(data, true));
                }
              }
            }

            return propsErrors;
          };

          // create input type=tel
          var inputs = document.querySelectorAll("input[autocomplete=tel][type=text]:not(.phone_cloned)");
          inputs.forEach((input) => {
              inputHTML = input.outerHTML,
              value = input.value,
              newInput = input.outerHTML.replace('type="text"', 'type="tel" value="' + value + '"');
    
              $(input).addClass('phone_cloned').hide();
              $(newInput).insertAfter(input);
          });

          // showPhoneMask("input[autocomplete=tel][type=tel]");

          // change value input type=text when change input type=tel
          $("input[autocomplete=tel][type=tel]").on("blur", function () {
            var $this = $(this);

            var value = $this.val();
            $this.closest('.soa-property-container').find("input[autocomplete=tel][type=text]").val(value);
          });

          if (inputs.length) {
            BX.Sale.OrderAjaxComponent.bindValidationPhone(arPropertyPhone.ID, $(inputs).parent()[0]);
          }
        }
      });
    }
  }

  if ($(".bx-soa-cart-total").length) {
    if (!$(".change_basket").length)
      $(".bx-soa-cart-total").prepend(
        '<div class="change_basket">' +
          BX.message("BASKET_CHANGE_TITLE") +
          '<a href="' +
          arAsproOptions["SITE_DIR"] +
          'basket/" class="change_link">' +
          BX.message("BASKET_CHANGE_LINK") +
          "</a></div>"
      );

    if (typeof BX.Sale.OrderAjaxComponent == "object") {
      if (arAsproOptions["COUNTERS"]["USE_FULLORDER_GOALS"] !== "N") {
        if (typeof BX.Sale.OrderAjaxComponent.reachgoalbegin === "undefined") {
          BX.Sale.OrderAjaxComponent.reachgoalbegin = true;
          var eventdata = { goal: "goal_order_begin" };
          BX.onCustomEvent("onCounterGoals", [eventdata]);
        }
      }

      if (BX.Sale.OrderAjaxComponent.hasOwnProperty("params")) {
        $(".bx-soa-cart-total .change_link").attr("href", BX.Sale.OrderAjaxComponent.params.PATH_TO_BASKET);
        if (arAsproOptions["PRICES"]["MIN_PRICE"]) {
          if (arAsproOptions["PRICES"]["MIN_PRICE"] > Number(BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_PRICE)) {
            $('<div class="fademask_ext"></div>').appendTo($("body"));
            location.href = BX.Sale.OrderAjaxComponent.params.PATH_TO_BASKET;
          }
        }
      }

      // update oreder auth form
      if ($("#bx-soa-auth").length && !$("#bx-soa-auth .redisigned").length) {
        // update input USER_LOGIN
        if ($('input[name="USER_LOGIN"]').length) {
          var $label = $('input[name="USER_LOGIN"]')
            .closest(".bx-authform-formgroup-container")
            .find(".bx-authform-label-container");
          if (!$label.find(".bx-authform-starrequired").length) {
            $label.html($label.html() + '<span class="bx-authform-starrequired"> *</span>');
          }
        }

        // update input USER_PASSWORD
        if ($('input[name="USER_PASSWORD"]').length) {
          var $label = $('input[name="USER_PASSWORD"]')
            .closest(".bx-authform-formgroup-container")
            .find(".bx-authform-label-container");
          if (!$label.find(".bx-authform-starrequired").length) {
            $label.html($label.html() + '<span class="bx-authform-starrequired"> *</span>');
          }
        }

        if ($('input[name="USER_REMEMBER"]').length) {
          var $label = $('input[name="USER_REMEMBER"]')
            .attr("id", "ORDER_AUTH_USER_REMEMBER")
            .closest("label")
            .attr("for", "ORDER_AUTH_USER_REMEMBER");
          var html = $('input[name="USER_REMEMBER"]')
            .attr("id", "ORDER_AUTH_USER_REMEMBER")
            .wrap('<div id="trem_"></div>')
            .parent()
            .html();
          $("#trem_").remove();
          $label.html($label.text());
          $(html).insertBefore($label).closest(".bx-authform-formgroup-container").addClass("filter");
          var html = $("#bx-soa-auth .bx-authform>a")
            .addClass("pull-right")
            .addClass("forgot")
            .wrap('<div id="trem_"></div>')
            .parent()
            .html();
          $("#trem_").remove();
          $(html).insertAfter($label.closest(".checkbox"));
        }

        $("#bx-soa-auth .bx-soa-reg-block .btn")
          .removeClass("btn-default")
          .removeClass("btn-lg")
          .addClass("transparent")
          .addClass("btn-lg")
          .text(BX.message("ORDER_REGISTER_BUTTON"));

        $("#bx-soa-auth").append('<div class="redisigned hidden></div>');
      }

      // update oreder register form
      if ($(".bx-soa-section-content.reg").length && !$(".bx-soa-section-content.reg .redisigned").length) {
        var bRebindRegSubmit = false;

        if (arAsproOptions.THEME.LOGIN_EQUAL_EMAIL === "Y") {
          bRebindRegSubmit = true;

          // update input NEW_LOGIN
          if ($('input[name="NEW_LOGIN"]').length) {
            $('input[name="NEW_LOGIN"]').closest(".bx-authform-formgroup-container").hide();
          }
        }

        if (arAsproOptions.THEME.PERSONAL_ONEFIO === "Y") {
          bRebindRegSubmit = true;

          // update input NEW_NAME
          if ($('input[name="NEW_NAME"]').length) {
            $('input[name="NEW_NAME"]')
              .closest(".bx-authform-formgroup-container")
              .find(".bx-authform-label-container")
              .html(BX.message("ORDER_FIO_LABEL") + '<span class="bx-authform-starrequired"> *</span>');
          }

          // update input NEW_LAST_NAME
          if ($('input[name="NEW_LAST_NAME"]').length) {
            $('input[name="NEW_LAST_NAME"]').closest(".bx-authform-formgroup-container").hide();
            $('input[name="NEW_LAST_NAME"]').val(" ");
          }
        }

        if (bRebindRegSubmit) {
          // bind new handler for submit button
          var $regSubmit = $("#do_register~input[type=submit]");
          if ($regSubmit.length) {
            BX.unbindAll($regSubmit[0]);
            $(document).on("click", "#do_register~input[type=submit]", function (e) {
              e.preventDefault();
              e.stopImmediatePropagation();

              if (arAsproOptions.THEME.LOGIN_EQUAL_EMAIL === "Y") {
                var email = BX.findChild(BX("bx-soa-auth"), { attribute: { name: "NEW_EMAIL" } }, true, false);
                var login = BX.findChild(BX("bx-soa-auth"), { attribute: { name: "NEW_LOGIN" } }, true, false);

                if (login && email) {
                  login.value = email.value;
                }
              }

              BX("do_register").value = "Y";
              BX.Sale.OrderAjaxComponent.sendRequest("showAuthForm");
            });
          }
        }

        // update captcha
        var $captcha = $(".bx-soa-section-content.reg").find(".bx-captcha");
        if ($captcha.length) {
          $captcha.addClass("captcha_image");
          if (!$captcha.find('.captcha_reload').length) {
            $captcha.append('<div class="captcha_reload"></div>');
          }
          $captcha
            .closest(".bx-authform-formgroup-container")
            .addClass("captcha-row")
            .find("input[name=captcha_word]")
            .closest(".bx-authform-input-container")
            .addClass("captcha_input");
          BX.onCustomEvent("onRenderCaptcha");
        }

        //update show password
        $(".bx-authform-input-container:not(.eye-password-ignore) [type=password]").each(function (item) {
          $(this).closest(".bx-authform-input-container").addClass("eye-password");
        });

        // update input NEW_NAME && NEW_LAST_NAME
        if (
          $("input[name=NEW_NAME]").length &&
          $("input[name=NEW_LAST_NAME]").length &&
          arAsproOptions.THEME.PERSONAL_ONEFIO !== "Y"
        ) {
          if (!$("input[name=NEW_NAME]").closest(".bx-authform-formgroup-container.col-md-6").length) {
            $("input[name=NEW_NAME],input[name=NEW_LAST_NAME]")
              .closest(".bx-authform-formgroup-container")
              .addClass("col-md-6");
            var html = $("input[name=NEW_LAST_NAME]")
              .closest(".bx-authform-formgroup-container")
              .wrap('<div id="trem_"></div>')
              .parent()
              .html();
            $("#trem_").remove();
            $(html).insertAfter(
              $("input[name=NEW_NAME]").closest(".bx-authform-formgroup-container").wrap('<div class="row"></div>')
            );
          }
        }

        // update input NEW_EMAIL && PHONE_NUMBER
        if ($("input[name=NEW_EMAIL]").length && $("input[name=PHONE_NUMBER]").length) {
          if (!$("input[name=PHONE_NUMBER]").closest(".bx-authform-formgroup-container.col-md-6").length) {
            $("input[name=NEW_EMAIL],input[name=PHONE_NUMBER]")
              .closest(".bx-authform-formgroup-container")
              .addClass("col-md-6");
            var html = $("input[name=PHONE_NUMBER]")
              .closest(".bx-authform-formgroup-container")
              .wrap('<div id="trem_"></div>')
              .parent()
              .html();
            $("#trem_").remove();
            $(html).insertAfter(
              $("input[name=NEW_EMAIL]").closest(".bx-authform-formgroup-container").wrap('<div class="row"></div>')
            );
          }
        }

        // update input NEW_PASSWORD && NEW_PASSWORD_CONFIRM
        if ($("input[name=NEW_PASSWORD]").length && $("input[name=NEW_PASSWORD_CONFIRM]").length) {
          if (!$("input[name=NEW_PASSWORD]").closest(".bx-authform-formgroup-container.col-md-6").length) {
            $("input[name=NEW_PASSWORD],input[name=NEW_PASSWORD_CONFIRM]")
              .closest(".bx-authform-formgroup-container")
              .addClass("col-md-6");
            var html = $("input[name=NEW_PASSWORD_CONFIRM]")
              .closest(".bx-authform-formgroup-container")
              .wrap('<div id="trem_"></div>')
              .parent()
              .html();
            $("#trem_").remove();
            $(html).insertAfter(
              $("input[name=NEW_PASSWORD]").closest(".bx-authform-formgroup-container").wrap('<div class="row"></div>')
            );
          }
        }

        // update input PHONE_NUMBER
        if ($("input[name=PHONE_NUMBER]").length) {
          var input = $("input[name=PHONE_NUMBER]"),
            inputHTML = input[0].outerHTML,
            value = input.val(),
            newInput = input[0].outerHTML.replace('type="text"', 'type="tel" value="' + value + '"');

          if ($(input).length < 2) {
            input.hide();
            $(newInput).insertAfter(input);

            showPhoneMask("input[name=PHONE_NUMBER][type=tel]");

            $("input[name=PHONE_NUMBER][type=tel]").on("blur", function () {
              var $this = $(this);
              var value = $this.val();
              $this.parent().find("input[name=PHONE_NUMBER][type=text]").val(value);
            });

            var $label = $("input[name=PHONE_NUMBER][type=tel]")
              .closest(".bx-authform-formgroup-container")
              .find(".bx-authform-label-container");
            $label.html(
              BX.message("ORDER_PHONE_LABEL") +
                ($label.find(".bx-authform-starrequired").length
                  ? '<span class="bx-authform-starrequired"> *</span>'
                  : "")
            );
          }
        }

        $(".bx-soa-section-content.reg").append('<div class="redisigned hidden></div>');
      }

      var asproShowLicence = arAsproOptions["THEME"]["SHOW_LICENCE"] == "Y";
      var asproShowOffer = arAsproOptions["THEME"]["SHOW_OFFER"] == "Y";

      if ($(".bx-soa-cart-total-line-total").length && (asproShowLicence || asproShowOffer)) {
        if (typeof e === "undefined") {
          BX.Sale.OrderAjaxComponent.state_licence = arAsproOptions["THEME"]["LICENCE_CHECKED"] == "Y" ? "checked" : "";
          BX.Sale.OrderAjaxComponent.state_offer = arAsproOptions["THEME"]["OFFER_CHECKED"] == "Y" ? "checked" : "";
        }

        if (
          (!$(".licence_block.filter").length && asproShowLicence) ||
          (!$(".offer_block.filter").length && asproShowOffer)
        ) {
          $('<div class="form"><div class="license_order_wrap"></div></div>').insertBefore($("#bx-soa-orderSave"));

          if (!$(".licence_block.filter").length && asproShowLicence)
            $(
              '<div class="licence_block filter label_block onoff"><label data-for="licenses_order" class="hidden error">' +
                BX.message("JS_REQUIRED_LICENSES") +
                '</label><input type="checkbox" name="licenses_order" required ' +
                BX.Sale.OrderAjaxComponent.state_licence +
                ' value="Y"><label data-for="licenses_order" class="license">' +
                BX.message("LICENSES_TEXT") +
                "</label></div>"
            ).appendTo($(".license_order_wrap"));

          if (!$(".offer_block.filter").length && asproShowOffer)
            $(
              '<div class="offer_block filter label_block onoff"><label data-for="offer_order" class="hidden error">' +
                BX.message("JS_REQUIRED_OFFER") +
                '</label><input type="checkbox" name="offer_order" required ' +
                BX.Sale.OrderAjaxComponent.state_offer +
                ' value="Y"><label data-for="offer_order" class="offer_pub">' +
                BX.message("OFFER_TEXT") +
                "</label></div>"
            ).appendTo($(".license_order_wrap"));

          if (asproShowLicence) {
            $(document).on("click", ".bx-soa .licence_block label.license", function () {
              var id = $(this).data("for");
              $(".bx-soa .licence_block label.error").addClass("hidden");
              if (!$("input[name=" + id + "]").prop("checked")) {
                $("input[name=" + id + "]").prop("checked", "checked");
                BX.Sale.OrderAjaxComponent.state_licence = "checked";
              } else {
                $("input[name=" + id + "]").prop("checked", "");
                BX.Sale.OrderAjaxComponent.state_licence = "";
              }
            });
          }

          if (asproShowOffer) {
            $(document).on("click", ".bx-soa .offer_block label.offer_pub", function () {
              var id = $(this).data("for");
              $(".bx-soa .offer_block label.error").addClass("hidden");
              if (!$("input[name=" + id + "]").prop("checked")) {
                $("input[name=" + id + "]").prop("checked", "checked");
                BX.Sale.OrderAjaxComponent.state_offer = "checked";
              } else {
                $("input[name=" + id + "]").prop("checked", "");
                BX.Sale.OrderAjaxComponent.state_offer = "";
              }
            });
          }

          $(document).on("click", ".lic_condition a", function () {
            if (BX.hasClass(BX("bx-soa-order"), "orderform--v1")) {
              if (BX.Sale.OrderAjaxComponent.isValidForm()) {
                BX.Sale.OrderAjaxComponent.animateScrollTo($(".licence_block, .offer_block")[0], 800, 50);
              }
            } else {
              var iCountErrors = BX.Sale.OrderAjaxComponent.isValidPropertiesBlock().length;
              if (!BX.Sale.OrderAjaxComponent.activeSectionId || !iCountErrors) {
                BX.Sale.OrderAjaxComponent.animateScrollTo($(".licence_block, .offer_block")[0], 800, 50);
              }
            }
          });
        }

        $("#bx-soa-orderSave, .bx-soa-cart-total-button-container").addClass("lic_condition");

        if (
          typeof BX.Sale.OrderAjaxComponent.oldClickOrderSaveAction === "undefined" &&
          typeof BX.Sale.OrderAjaxComponent.clickOrderSaveAction !== "undefined"
        ) {
          BX.Sale.OrderAjaxComponent.oldClickOrderSaveAction = BX.Sale.OrderAjaxComponent.clickOrderSaveAction;
          BX.Sale.OrderAjaxComponent.clickOrderSaveAction = function (event) {
            if (
              ($('input[name="licenses_order"]').prop("checked") || !asproShowLicence) &&
              ($('input[name="offer_order"]').prop("checked") || !asproShowOffer)
            ) {
              $(".bx-soa .licence_block label.error").addClass("hidden");
              $(".bx-soa .offer_block label.error").addClass("hidden");

              if (BX.Sale.OrderAjaxComponent.isValidForm()) {
                if (typeof BX.Sale.OrderAjaxComponent.allowOrderSave == "function")
                  BX.Sale.OrderAjaxComponent.allowOrderSave();
                if (typeof BX.Sale.OrderAjaxComponent.doSaveAction == "function")
                  BX.Sale.OrderAjaxComponent.doSaveAction();
                else BX.Sale.OrderAjaxComponent.oldClickOrderSaveAction(event);
              }
            } else {
              if (!$('input[name="licenses_order"]').prop("checked"))
                $(".bx-soa .licence_block label.error").removeClass("hidden");

              if (!$('input[name="offer_order"]').prop("checked"))
                $(".bx-soa .offer_block label.error").removeClass("hidden");
            }
          };
          if (BX.Sale.OrderAjaxComponent.orderSaveBlockNode.querySelector(".checkbox")) {
            if (typeof browser == "object") {
              if ("msie" in browser && browser.msie)
                $(BX.Sale.OrderAjaxComponent.orderSaveBlockNode.querySelector(".checkbox")).remove();
              else BX.Sale.OrderAjaxComponent.orderSaveBlockNode.querySelector(".checkbox").remove();
            }
          }
          BX.unbindAll(BX.Sale.OrderAjaxComponent.totalInfoBlockNode.querySelector("a.btn-order-save"));
          BX.unbindAll(BX.Sale.OrderAjaxComponent.mobileTotalBlockNode.querySelector("a.btn-order-save"));
          BX.unbindAll(BX.Sale.OrderAjaxComponent.orderSaveBlockNode.querySelector("a"));
          BX.bind(
            BX.Sale.OrderAjaxComponent.totalInfoBlockNode.querySelector("a.btn-order-save"),
            "click",
            BX.proxy(BX.Sale.OrderAjaxComponent.clickOrderSaveAction, BX.Sale.OrderAjaxComponent)
          );
          BX.bind(
            BX.Sale.OrderAjaxComponent.mobileTotalBlockNode.querySelector("a.btn-order-save"),
            "click",
            BX.proxy(BX.Sale.OrderAjaxComponent.clickOrderSaveAction, BX.Sale.OrderAjaxComponent)
          );
          BX.bind(
            BX.Sale.OrderAjaxComponent.orderSaveBlockNode.querySelector("a"),
            "click",
            BX.proxy(BX.Sale.OrderAjaxComponent.clickOrderSaveAction, BX.Sale.OrderAjaxComponent)
          );
        }
      }

      // fix hide total block
      $(window).scroll();

      if (checkCounters() && typeof BX.Sale.OrderAjaxComponent.oldSaveOrder === "undefined") {
        var saveFunc = typeof BX.Sale.OrderAjaxComponent.saveOrder !== "undefined" ? "saveOrder" : "saveOrderWithJson";
        if (typeof BX.Sale.OrderAjaxComponent[saveFunc] !== "undefined") {
          BX.Sale.OrderAjaxComponent.oldSaveOrder = BX.Sale.OrderAjaxComponent[saveFunc];
          BX.Sale.OrderAjaxComponent[saveFunc] = function (result) {
            var res = BX.parseJSON(result);
            if (res && res.order) {
              if (!res.order.SHOW_AUTH) {
                if (
                  res.order.REDIRECT_URL &&
                  res.order.REDIRECT_URL.length &&
                  (!res.order.ERROR || BX.util.object_keys(res.order.ERROR).length < 1)
                ) {
                  if (
                    (arMatch = res.order.REDIRECT_URL.match(/ORDER_ID\=[^&=]*/g)) &&
                    arMatch.length &&
                    (_id = arMatch[0].replace(/ORDER_ID\=/g, "", arMatch[0]))
                  ) {
                    $.ajax({
                      url: arAsproOptions["SITE_DIR"] + "ajax/check_order.php",
                      dataType: "json",
                      type: "POST",
                      data: { ID: _id },
                      success: function (id) {
                        if (parseInt(id)) {
                          purchaseCounter(parseInt(id), BX.message("FULL_ORDER"), function (d) {
                            if (typeof localStorage !== "undefined" && typeof d === "object") {
                              localStorage.setItem("gtm_e_" + _id, JSON.stringify(d));
                            }
                            BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                          });
                        } else {
                          BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                        }
                      },
                      error: function () {
                        BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                      },
                    });
                  } else {
                    BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                  }
                } else {
                  BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                }
              } else {
                BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
              }
            } else {
              BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
            }
          };
        }
      }

      if ($("#bx-soa-order-form .captcha-row").length) {
        if (BX.Aspro?.Captcha) {
          $('.bx-soa-section-content.reg .captcha-row .bx-authform-label-container').hide();

          // fix ya smartcaptcha height
          if ($('.bx-soa-section-content.reg ' + BX.Aspro.Captcha.selector).length) {
            $('.bx-soa-section-content.reg ' + BX.Aspro.Captcha.selector).css('height', '');
          }

          if (BX.Aspro.Captcha.isInvisible()) {
            $("#bx-soa-order-form .captcha-row").addClass("invisible");

            if (BX.Aspro.Captcha.params.showLogo === "n") {
              $("#bx-soa-order-form .captcha-row").addClass("logo_captcha_n");
            }
          }

          if (
            BX.Aspro.Captcha.isGoogleRecaptcha3() ||
            BX.Aspro.Captcha.isInvisible()
          ) {
            if (
              typeof BX.Sale.OrderAjaxComponent.oldSendRequest === "undefined" &&
              typeof BX.Sale.OrderAjaxComponent.sendRequest !== "undefined"
            ) {
              BX.Sale.OrderAjaxComponent.oldSendRequest = BX.Sale.OrderAjaxComponent.sendRequest;
              BX.Sale.OrderAjaxComponent.sendRequest = function (action, actionData) {
                let form = BX("bx-soa-order-form");

                (new Promise((resolve, reject) => {
                  if (BX.Aspro.Captcha.api) {
                    BX.Aspro.Captcha.onSubmit({form}).then((result) => {
                      resolve(result)
                    }).catch((e) => {
                      reject(e);
                    });

                    return;
                  }

                  resolve(true);
                })).then((result) => {
                  if (result) {
                    BX.Sale.OrderAjaxComponent.oldSendRequest(action, actionData);
                  }
                });
              };
            }
          }
        }
      }
    }

    $(".bx-ui-sls-quick-locations.quick-locations").on("click", function () {
      $(this).siblings().removeClass("active");
      $(this).addClass("active");
    });
  }
}

function orderConfirmAction() {
  var arUrl = parseUrlQuery();
  if ('ORDER_ID' in arUrl) {
    var _id = arUrl['ORDER_ID'];
    if (arAsproOptions['COUNTERS']['USE_FULLORDER_GOALS'] !== 'N') {
      var eventdata = {
        goal: 'goal_order_success',
        result: _id,
      };
      BX.onCustomEvent('onCounterGoals', [eventdata]);
    }

    if (checkCounters()) {
      if (typeof localStorage !== 'undefined') {
        var val = localStorage.getItem('gtm_e_' + _id),
          d = "";
        try {
          d = JSON.parse(val);
        } catch (e) {
          d = val;
        }
        if (typeof d === 'object') {
          window.dataLayer = window.dataLayer || [];
          dataLayer.push({ event: arAsproOptions['COUNTERS']['GOOGLE_EVENTS']['PURCHASE'], ecommerce: d });
        }

        if (typeof localStorage !== 'undefined') {
          localStorage.removeItem('gtm_e_' + _id);
        }
      }
    }
  }
}

readyDOM(function(){
  // ecommerce order
  orderConfirmAction();

  // full order
  orderAction();

  BX.addCustomEvent(window, "onAjaxSuccess", function (e) {
    orderAction(e);
  });
});
