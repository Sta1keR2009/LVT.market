window.appAspro = window.appAspro || {}

if (!window.appAspro.phone) {
    window.appAspro.phone = {
        get checkIntlPhone() {
            return typeof window.intlTelInput === 'function'
        },

        get checkInputmask() {
            return typeof window.Inputmask === 'function'
        },

        get checkIntlTelInputUtils() {
            return typeof window.intlTelInputUtils === 'function'
        },

        errorMap: ["JS_INVALID_NUMBER", "JS_INVALID_COUNTRY_CODE", "JS_TOO_SHORT", "JS_TOO_LONG", "JS_INVALID_NUMBER", "JS_INVALID_NUMBER"],

        get config() {
            return this._config || null
        },

        set config(config) {
            this._config = config
        },

        init: function(els, config) {
            const self = this;

            const defaultConfig = {
                useValidate: true,
                mask: arAsproOptions.THEME.PHONE_MASK,
                onlyCountries: arAsproOptions.THEME.PHONE_CITIES,
                preferredCountries: arAsproOptions.THEME.PHONE_CITIES_FAVORITE,
                async: true,
            };

            if (!config && this.config) {
                config = this.config
            }

            config = $.extend({}, defaultConfig, (config || {}))

            try {
                if (this.checkIntlPhone) {
                    // load all countries
                    this.loadIntlPhoneCountries(config.async)
                    .then((countries) => {
                        config.countries = countries
                        this.initIntlPhone(els, config)
                    })
                    .catch(() => {
                        // error when all countries load
                        throw 0;
                    });
                }
                else {
                    throw 0;
                }
            }
            catch (e) {
                // use inputmask
                if (
                    this.checkInputmask &&
                    config.mask
                ) {
                    this.initNormalPhone(els, config)
                }
            }
        },

        initIntlPhone: function(els, config) {
            let defaultConfig = {
                utilsScript: arAsproOptions.SITE_TEMPLATE_PATH + '/vendor/intl.phone/js/utils.js',
                preferredCountries: ['ru'],
                autoPlaceholder: 'aggressive',
                nationalMode: false,
                onlyCountries: ['ru'],
                formatOnDisplay: true,
                autoHideDialCode: true,
            };
            config = config || {}
            const pluginOptions = $.extend({}, defaultConfig, config)

            if (pluginOptions.onlyCountries.length && typeof pluginOptions.onlyCountries === 'string') {
                pluginOptions.onlyCountries = pluginOptions.onlyCountries.split(',')
            }
            if (pluginOptions.preferredCountries.length && typeof pluginOptions.preferredCountries === 'string') {
                pluginOptions.preferredCountries = pluginOptions.preferredCountries.split(',')
            }

            // load utils.js without waiting window.load event
            window.intlTelInputGlobals.loadUtils(pluginOptions.utilsScript);

            const self = this

            els.each(function(i, node) {
                let iti = window.intlTelInput(node, pluginOptions)
                let _this = $(node)
                _this.data('iti', iti)

                // if (!~_this.val().indexOf('+')) {
                //     _this.val('+'+_this.val())
                // }

                _this.on("change", function () {

                    const inputVal = _this.val();
                    if (!~inputVal.indexOf('+') && inputVal.length) {
                        _this.val('+'+inputVal)
                    }

                    if (typeof intlTelInputUtils !== 'undefined') {
                        var currentText = iti.getNumber(intlTelInputUtils.numberFormat.E164);
                        if (typeof currentText === 'string') {
                            iti.setNumber(currentText);
                        }
                    }
                    else {
                        let t = setInterval(() => {
                            if (typeof intlTelInputUtils !== 'undefined') {
                                clearInterval(t);

                                var currentText = iti.getNumber(intlTelInputUtils.numberFormat.E164);
                                if (typeof currentText === 'string') {
                                    iti.setNumber(currentText);
                                }
                            }
                        }, 100);
                    }

                    /* paste fix */
                    if (!iti.getSelectedCountryData().name) {
                        _this.val(_this.val().replace('+8', '+7'))
                        iti.setNumber(_this.val());
                    }
                    /* */
                });

                _this.on("input", function (e) {
                    const _this = $(this)
                    const telInput = _this.data('iti');
                    let inputVal = _this.val();

                    // console.log('input',
                    // telInput.getValidationError(),
                    //     self.errorMap[telInput.getValidationError()],
                    //     inputVal,
                    //     telInput.isValidNumber(),
                    //     telInput.getSelectedCountryData(),
                    //     e
                    // );

                    if (inputVal.length >= 1 && !inputVal.includes('+')) {
                        _this.val('+'+inputVal);
                        inputVal = _this.val();
                    }
                    if (inputVal.length > 3) {
                        if (!telInput.getSelectedCountryData().name) {
                            _this.val(inputVal.replace('+8', '+7'))
                        } else {
                            // let inputCountryLength = _this.attr('placeholder').replace(/\D/g,'').length
                            // let inputValLength = inputVal.replace(/\D/g,'').length
                            // if (inputValLength > inputCountryLength) {
                            //     // _this.val(inputVal.slice(0, inputCountryLength - inputValLength))
                            // }
                        }
                    }

                    if (typeof intlTelInputUtils !== 'undefined') {
                        var currentText = iti.getNumber(intlTelInputUtils.numberFormat.E164);
                        if (typeof currentText === 'string') {
                            iti.setNumber(currentText);
                            // _this[0].selectionStart =_this[0].selectionEnd = pos
                        }
                    }
                });

                _this.on("keypress", function (e) {
                    let key = String.fromCharCode(!e.charCode ? e.which : e.charCode);

                    if (e.target.value === '') {
                        e.target.value = '+'
                    }
                    return /\d/.test(key)
                });

                //manual change trigger
                _this.trigger('change')
            });

            this.bindPhoneMask(els)
            if (config.useValidate) {
                this.addValidationIntlPhone()
            }
        },

        initNormalPhone: function(els, config) {
            let base_mask = config.mask.replace( /(\d)/g, '_' );
            els.inputmask("mask", { mask: config.mask });
            els.blur(function() {
                if( $(this).val() == base_mask || $(this).val() == '' ) {
                    if( $(this).hasClass('required') ) {
                        $(this).parent().find('label.error').html(BX.message('JS_REQUIRED'));
                    }
                }
            });

            if (config.useValidate) {
                this.addValidationPhone()
            }
        },

        addValidationIntlPhone: function() {
            $.validator.addMethod(
                "intl_phone",
                function (value, element, param) {
                    const telInput = $(element).data('iti');
                    let valid = telInput.isValidNumber();
                    if (value.length) {
                        if (valid) {
                            element.classList.remove('error')
                        } else {
                            element.classList.add('error')
                        }
                    } else {
                        valid = true
                    }
                    return valid
                },
                function (param, element) {
                    const telInput = $(element).data('iti');

                    return BX.message(param[telInput.getValidationError()]) || BX.message(param[0])
                }
            );
            $.validator.addClassRules({
                phone: {
                    intl_phone: this.errorMap,
                },
                phone_input: {
                    intl_phone: this.errorMap,
                },
            })
        },

        addValidationPhone: function() {
            if (arAsproOptions.THEME.VALIDATE_PHONE_MASK) {
                $.validator.addClassRules({
                    phone: {
                        regexp: arAsproOptions.THEME.VALIDATE_PHONE_MASK,
                    },
                    phone_input: {
                        regexp: arAsproOptions.THEME.VALIDATE_PHONE_MASK,
                    },
                })
            }
        },

        bindPhoneMask: function(els) {
            let _this = this
            els.each(function(i, node) {
                this.addEventListener('countrychange', function(e) {
                    e.stopImmediatePropagation()
                    const _this = $(e.target);

                    if (typeof e.detail === 'object' && e.detail) {
                        if (e.detail.type === "_selectListItem") {
                            _this.trigger('input')
                            _this.valid()
                        }
                    }
                })
            })
        },

        loadIntlPhoneCountries: function(async = true) {
            if (!this.loadIntlPhoneCountriesPromise) {
                this.loadIntlPhoneCountriesPromise = new Promise(
					(resolve, reject) => {
                        if (typeof window.allCountries !== 'undefined') {
                            resolve(window.allCountries);
                        }
                        else {
                            BX.ajax({
                                url: arAsproOptions.SITE_TEMPLATE_PATH + '/vendor/intl.phone/js/data.php',
                                data: {},
                                method: 'GET',
                                dataType: 'script',
                                async: async,
                                scriptsRunFirst: true,
                                start: true,
                                cache: false,
                                onsuccess: function() {
                                    resolve(window.allCountries);
                                },
                                onfailure: function() {
                                    reject();
                                }
                            });
                        }
					}
				);
            }

            return this.loadIntlPhoneCountriesPromise;
        },
    }
}
