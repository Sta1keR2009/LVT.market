function catapulto_delivery_pvzWidjet(params, deliveries, paysystems, savingInput, savingInputDelivery, postField, pvzPicker, LANG, defPaysys, markupMode, markup, markupOperatorsData, enableSuggestions = false, runWidgetOnStart = false, includesJs = []) {
    var self = this, city = params.location.address, label = 'Catapulto pvzWidjet', pvz = false,
        deliveryVariant = false, paysys = false, ready = false, PAY_SYSTEM_ID = false, PERSON_TYPE_ID = false,
        DELIVERY_ID = false, delType = false, receiver_locality_id = false, rate_result = false,
        sender_locality_id = false,
        rate_result_id = false, rate_cost = false, rate_term = false, inputIsCleaned = false,
        location_name = false, location_type = false/*, tarifInvOps = []*/;

    this.error = false;
    window.ctpt_wAutoStarted = false;

    this.widjetController = null;
    window.rateWithPartialRed = params.is_partial_redemption ?? false;

    if (!city) {
        error('No city given');
        this.error = true;
    }

    // bitrix deliveries for PVZ
    if (typeof (deliveries) !== 'object' || isEmpty(deliveries)) {
        error('No module deliveries found');
        this.error = true;
        return false;
    }

    if (typeof (LANG) !== 'object' || isEmpty(LANG)) {
        LANG = {
            'WRONG_PAY': 'Incorrect payment system for this pvz'
        };
    }

    // moduleLabel
    if (typeof (LANG.iPE) === 'undefined') {
        LANG.iPE = 'CATAPULTO_DELIVERY_';
    }

    // where in Request will be saved PVZ
    if (typeof (savingInput) === 'undefined' || !savingInput) {
        savingInput = 'POINT_GUID';
    }

    // where in Request will be saved delivery type and more ids
    if (typeof (savingInputDelivery) === 'undefined' || !savingInputDelivery) {
        savingInputDelivery = 'DELIVERY_VARIANT_ID';
    }

    if (typeof (pvzPicker) === 'undefined') pvzPicker = false;

    var oldTemplate = $('#ORDER_FORM').length;

    var currentDelivery = false;


    /*for (let i in markupOperatorsData) {
        if (markupOperatorsData[i].tarifinv === 'Y') tarifInvOps.push(markupOperatorsData[i].id);
    }*/

    this.jq_loaded = false
    function loadJs() {
        for (let i in includesJs) {
            const path = includesJs[i].split('/'),
                tag = document.createElement('script');
            tag.src = includesJs[i];
            if (path.includes('main') && path.includes('bitrix') && path.includes('jquery')) {
                if (typeof window.jQuery !== 'function') document.body.appendChild(tag);
            } else document.body.appendChild(tag)
        }
    }

    const waitSuggestionsReady = {
        _promise: null,

        then(onFulfilled, onRejected) {
            if (!this._promise) {
                this._promise = new Promise((resolve, reject) => {
                    let cnt = 0;

                    function checkReady() {
                        cnt++;
                        if (cnt > 100) {
                            reject(new Error('Timeout Suggesting load'));
                            return;
                        }
                        if (window.jQuery &&
                            window.jQuery.ui &&
                            window.jQuery.ui.autocomplete &&
                            typeof window.jQuery.ui.autocomplete === 'function' &&
                            typeof window.jQuery.Suggestions === 'function') {
                            resolve();
                            return;
                        }
                        setTimeout(checkReady, 100);
                    }

                    checkReady(); // Запускается ТОЛЬКО при первом вызове .then()
                });
            }

            return this._promise.then(onFulfilled, onRejected);
        },

        catch(onRejected) {
            return this.then(undefined, onRejected);
        },

        finally(onFinally) {
            return this.then().finally(onFinally);
        }
    };

    this.onLoad = function (ajaxAns) {
        if (typeof (ajaxAns) === 'object' && typeof (ajaxAns.order) === 'undefined') return;
        if (!this.jq_loaded) loadJs();

        var newTemplateAjax = (typeof (ajaxAns) !== 'undefined' && ajaxAns !== null && typeof (ajaxAns[postField]) === 'object');

        if (typeof (ajaxAns) === 'undefined') {
            ajaxAns = false;
        }

        if (newTemplateAjax) {
            if (typeof (ajaxAns[postField]) !== 'undefined') {
                city = ajaxAns[postField].city;
                location_name = ajaxAns[postField].location_name;
                location_type = ajaxAns[postField].location_type;
                paysys = ajaxAns[postField].paysys;
                PAY_SYSTEM_ID = ajaxAns[postField].PAY_SYSTEM_ID;
                PERSON_TYPE_ID = ajaxAns[postField].PERSON_TYPE_ID;
                DELIVERY_ID = ajaxAns[postField].DELIVERY_ID;
                delType = ajaxAns[postField].DELIVERY_TYPE;
                receiver_locality_id = ajaxAns[postField].receiver_locality_id;
                rate_result = ajaxAns[postField].rate_result;
                sender_locality_id = ajaxAns[postField].sender_locality_id;
                rate_result_id = ajaxAns[postField].rate_result_id;
                rate_cost = ajaxAns[postField].rate_cost;
                rate_term = ajaxAns[postField].rate_term;
            }
        } else {
            var ajaxData = $('#' + postField);
            if (ajaxData.length) {
                var saved = $.parseJSON(ajaxData.val());
                city = saved.city;
                location_name = saved.location_name;
                location_type = saved.location_type;
                paysys = saved.paysys;
                PAY_SYSTEM_ID = saved.PAY_SYSTEM_ID;
                PERSON_TYPE_ID = saved.PERSON_TYPE_ID;
                DELIVERY_ID = saved.DELIVERY_ID;
                delType = saved.DELIVERY_TYPE;
                receiver_locality_id = saved.receiver_locality_id;
                rate_result = saved.rate_result;
                sender_locality_id = saved.sender_locality_id;
                rate_result_id = saved.rate_result_id;
                rate_cost = ajaxAns[postField].rate_cost;
                rate_term = ajaxAns[postField].rate_term;
            }
        }

        params.location.address = city;

        if (typeof (location_type) !== 'undefined' && location_type !== false) {
            switch (location_type) {
                case 'CITY':
                    params.location.settlement = false;
                    params.location.city = location_name;
                    break;

                case 'VILLAGE':
                    params.location.city = false;
                    params.location.settlement = location_name;
                    break;

                default:
                    break;
            }
        }

        if (!paysys) {
            for (var id in paysystems) {
                if (paysysHandler.guess(id)) {
                    paysys = paysystems[id];
                }
            }
            if (!paysys) {
                paysys = defPaysys;
            }
        }

        if (ready) {

            if (catapulto_delivery_pvzWidjet.widjetController) {
                if (
                    typeof (ajaxAns) === 'object'
                    && !deliveryHandler.check(deliveries[labelController.getLink()].link, ajaxAns.order.DELIVERY)) {
                    catapulto_delivery_pvzWidjet.widjetController.hide();
                } else {
                    if (catapulto_delivery_pvzWidjet.widjetController.getParams().defaultAddress !== city) {
                        catapulto_delivery_pvzWidjet.widjetController.getParams().defaultAddress = city;
                        deliveryVariant = false;
                        pvz = false;
                        catapulto_delivery_pvzWidjet.widjetController.refresh();
                    }
                }
            }

            preserveDeliveryType();
        }
        initLabels();

        if (enableSuggestions) {
            let addressInput = addressPropertyController.getInput();
            waitSuggestionsReady.then(()=>{
                addressInput.suggestions({
                    token: params.dadata_token,
                    type: "ADDRESS",
                    onSuggestionsFetch: function (suggestions) {
                        let cityFilter = '', suggestionFiltered = [];
                        if ((typeof (params.location) != 'undefined') && (typeof (params.location.city) == 'string'))
                            cityFilter = params.location.city.toLowerCase();
                        if (cityFilter != '') {
                            for (let i in suggestions) {
                                if (
                                    ((typeof (suggestions[i].data.city) == 'string') && (suggestions[i].data.city.toLowerCase() == cityFilter))
                                    ||
                                    ((typeof (suggestions[i].data.settlement) == 'string') && (suggestions[i].data.settlement.toLowerCase() == cityFilter))
                                )
                                    suggestionFiltered.push(suggestions[i]);
                            }
                        }
                        return suggestionFiltered;
                    },
                    onSelect: function(suggestion) {
                        let result = '';
                        if (suggestion.data.street_with_type != null) result += suggestion.data.street_with_type;

                        let house = '';
                        if ((suggestion.data.house_type != null) && (suggestion.data.house != null)) {
                            house = suggestion.data.house_type + ' ' + suggestion.data.house;
                            if ((suggestion.data.block_type != null) && (suggestion.data.block != null)) house += ' ' + suggestion.data.block_type + ' ' + suggestion.data.block;
                        }

                        let flat = '';
                        if ( (suggestion.data.flat_type != null) && (suggestion.data.flat != null) ) flat = suggestion.data.flat_type + ' ' + suggestion.data.flat;

                        if (house.length > 0) {
                            if (result.length > 0) result += ', ';
                            result += house;
                        }

                        if (flat.length > 0) {
                            if (result.length > 0) result += ', ';
                            result += flat;
                        }

                        addressInput.val(result);
                    },
                });
            }).catch((err)=>{
                console.log('Error loading JQuery Suggestions lib...', err);
            })
        }
    };

    function init() {
        let widgetParams = Object.assign(
            params,
            {
                //inverse_delivery_type_for_operators: tarifInvOps,
                onPopupClose: () => { // обработчик после закрытия кнопкой в режиме попапа
                    $('body').css('overflow','');
                },
                onSelectPvzItem: (Item) => { // Событие при выборе ПВЗ варианта
                    selectPvzItem(Item);
                },
                onSelectCourierItem: (Item) => {// Событие при выборе курьерского варианта
                    selectCourierItem(Item);
                },
                onTariffResponse: function (Tariff) { // Событие получения возможной даты и времени доставки для варианта курьерской доставки с примером получения рейта для него.
                    let courierItems = catapulto_delivery_pvzWidjet.widjetController.getData().getCourierItems(); // варианты курьерской доставки
                    let Variant = false;
                    for (let i in courierItems) {
                        if (courierItems[i].id === Tariff.id) { // определение какому варианту курьерской доставки принадлежит только что полученный "тариф" (список возможных дат и времени доставки)
                            Variant = courierItems[i];
                            break;
                        }
                    }
                },
                onRateResponse: function (Rate) {
                    if (typeof(Rate.results) != 'undefined') {
                        Rate.results.forEach(function(item){
                            if (typeof(item.price_orig) == 'undefined')
                                item.price_orig = item.price;
                            if (typeof(item.markup) == 'undefined') {
                                item.price += (markupMode===0)?markup:( Math.round(markup * item.price_orig)/100 );
                                item.markup = true;
                            }
                            for (let i in markupOperatorsData) {
                                if (markupOperatorsData[i].id == item.operator) {
                                    let mkType = Number(markupOperatorsData[i].mktp);
                                    let mkVal = Number(markupOperatorsData[i].mkvl);
                                    if (isNaN(mkType)) mkType = 0;
                                    if (isNaN(mkVal)) mkVal = 0;
                                    if (mkVal > 0) {
                                        item.price = item.price_orig + ((mkType===0)?mkVal:( Math.round(mkVal * item.price_orig)/100 ));
                                        item.markup = true;
                                    }
                                }
                            }
                        });
                    }
                }
            }
        );

        if (!catapulto_delivery_pvzWidjet.widjetController) {
            catapulto_delivery_pvzWidjet.widjetController = new CatapultoWidget2(widgetParams);
        } else {
            catapulto_delivery_pvzWidjet.widjetController.reinitialize(widgetParams);
            console.log('Catapulto: reinitialization');
        }

        catapulto_delivery_pvzWidjet.widjetController.show();
        $('body').css('overflow','hidden');
        ready = true;
    }

    function getCookie(name) {
        function escape(s) { return s.replace(/([.*+?\^$(){}|\[\]\/\\])/g, '\\$1'); }
        var match = document.cookie.match(RegExp('(?:^|;\\s*)' + escape(name) + '=([^;]*)'));
        return match ? match[1] : null;
    }

    function initLabels(ajaxAns) {
        if (typeof (ajaxAns) == 'undefined') {
            ajaxAns = false;
        }

        labelController.find(ajaxAns);
        //addressPropertyController.do();
    }

    function checkPreloader() {
        $('#' + LANG.iPE + 'subLair').css('display', 'block');
    }

    // subscribes
    if (typeof BX !== 'undefined' && BX.addCustomEvent) BX.addCustomEvent('onAjaxSuccess', self.onLoad);

    this.blockAlert = false;


    function selectPvzItem(Item) {
        let data = Item;
        if (data && (typeof (data.Variant) !== 'undefined' || typeof (data.variant) !== 'undefined')) {
            if (typeof (data.Variant) !== 'undefined') {
                data.variant = data.Variant;
            }
            deliveryVariant = data;
            deliveryVariant['rate_param'] = catapulto_delivery_pvzWidjet.widjetController.getData().RateParams;
            deliveryVariant['dadata'] = catapulto_delivery_pvzWidjet.widjetController.getData().getVariant();
            preserveDeliveryType();
            pvz = data.Terminal;
            if (checkPayAvailabel(pvz)) {
                preservePVZ();
                addressPropertyController.do();
            } else {
                if (!self.blockAlert) {
                    self.blockAlert = true;
                    alert(LANG.WRONG_PAY);
                    window.setTimeout(function (what) {
                        what.blockAlert = false;
                    }, 100, self);
                }
                pvz = false;
                deliveryVariant = false;
            }
        } else {
            pvz = false;
        }

        if (deliveryVariant) {
            catapulto_delivery_pvzWidjet.widjetController.hide();
            reloadForm();
        }

        if (getCookie('catapulto_reselect') === '0') {
            let date = new Date(Date.now() + 86400e3);
            date = date.toUTCString();
            document.cookie = "catapulto_reselect=1; path=/; expires=" + date;
        }
    }

    function selectCourierItem(Item) {
        let data = Item;
        if (data && typeof (data.variant.id) !== 'undefined') {
            deliveryVariant = data;
            deliveryVariant['rate_param'] = catapulto_delivery_pvzWidjet.widjetController.getData().RateParams;
            deliveryVariant['dadata'] = catapulto_delivery_pvzWidjet.widjetController.getData().getVariant();
            preserveDeliveryType();
            pvz = false;
        }

        if (deliveryVariant) {
            addressPropertyController.do();
            catapulto_delivery_pvzWidjet.widjetController.hide();
            reloadForm();
        }

        if (getCookie('catapulto_reselect') === '0') {
            let date = new Date(Date.now() + 86400e3);
            date = date.toUTCString();
            document.cookie = "catapulto_reselect=1; path=/; expires=" + date;
        }
    }

    function checkPayAvailabel(pvz) {
        var allow = false;
        switch (paysys) {
            case 'CASH' :
                allow = (pvz.cash);
                break;
            case 'CARD' :
                allow = (pvz.card);
                break;
            default     :
                allow = true;
                break;
        }
        return allow;
    }

    // deals with button "open widjet"
    var labelController = {
        // puts tag 4 labels
        find: function (ajaxAns) {
            var tag = false;
            for (var i in deliveries) {
                tag = false;

                if (oldTemplate) {
                    var parentNd = $('#' + deliveryHandler.makeId(i));
                    if (!parentNd.length) continue;
                    if (parentNd.closest('td', '#ORDER_FORM').length > 0) tag = parentNd.closest('td', '#ORDER_FORM').siblings('td:last'); else tag = parentNd.siblings('label').find('.bx_result_price');
                } else {
                    if (
                        (typeof (ajaxAns.order) !== 'undefined'
                        && deliveryHandler.check(i, ajaxAns.order.DELIVERY))
                        || (!ajaxAns && deliveryHandler.guess(deliveries[i].link))
                    ) {
                        var lair = 'injectHere';
                        if (!$('#' + LANG.iPE + lair).length) {
                            $('#bx-soa-delivery').find('.bx-soa-pp-company-desc').after('<div id="' + LANG.iPE + lair + '"></div>');
                        }
                        if (!$('#' + LANG.iPE + lair).length) {
                            labelController.loader.listner();
                        } else tag = $('#' + LANG.iPE + lair);
                    }
                }

                if (tag.length > 0 && !tag.find('.' + LANG.iPE + 'selectServices').length) {
                    deliveries[i].tag = tag;
                    labelController.place(i, deliveries[i].type);
                } else {
                    deliveries[i].tag = false;
                }
            }
        },

        // adds block for opening widjet
        place: function (deliveryId, type) {
            if (typeof (deliveries) === 'undefined') return false;

            let tmpHTML = labelController.getHtmlInfo(deliveryId);
            deliveries[deliveryId].tag.html(tmpHTML);

            if (ready && deliveryVariant) {
                let action = LANG.iPE + 'action',
                    ajaxData = {
                        'operator_id': deliveryVariant.variant.operator
                    };
                ajaxData[action] = 'widgetGetOperatorAjax';

                $.ajax({
                    url: LANG.jsPath + 'ajax.php',
                    data: ajaxData,
                    type: 'POST',
                    success: function (operator) {
                        if (operator) {
                            operator = JSON.parse(operator);
                        }
                        tmpHTML = labelController.getHtmlInfo(deliveryId, operator);
                        deliveries[deliveryId].tag.html(tmpHTML);
                    }
                });
            }

            deliveries[deliveryId].tag.off().on('click', function () {
                init()
            });
        },

        getHtmlInfo: function (deliveryId, operator) {
            let opName = (operator ? operator.OPERATOR_DISPLAY : ''),
                opIcon = (operator ? operator.PNG_ICON : ''),
                opImg = '',
                tmpHTML = "<div class='" + LANG.iPE + "pvzLair'>" + '<a id="ctpt_runwdgt" href="javascript:void(0);" class="' + LANG.iPE + 'selectPVZ' + (!oldTemplate ? ' btn btn-default btn-primary' : '') + '">' + LANG.buttonLabel + '</a>' + "<br>";

            if (deliveryVariant && !opName) {
                opName = deliveryVariant.variant.operator;
            }

            if (opIcon) {
                opImg = '<img class="' + LANG.iPE + 'opIcon" src="' + opIcon + '" alt="' + opName + '">';
            }

            if (pvz) {
                tmpHTML += "<span class='" + LANG.iPE + "pvzAddr'>" + LANG.operator + ': ' + opName + opImg + "\n<br>" + LANG.tariff + ': ' + deliveryVariant.variant.rate + "\n<br>" + LANG.pvz + ': ' + pvz.address + (pvz.code ? ' #' + pvz.code : '') + "</span><br>";
            } else if (deliveryVariant) {
                tmpHTML += "<span class='" + LANG.iPE + "pvzAddr'>" + LANG.operator + ': ' + opName + opImg + "\n<br>" + LANG.tariff + ': ' + deliveryVariant.variant.rate + "</span><br>";
            }

            tmpHTML += "</div>";

            if (!ready) {
                tmpHTML = '<div id="' + LANG.iPE + 'subLair">' + tmpHTML + "</div>";
            }

            if ( (opName == '') && (opIcon == '') && (opImg == '') ) {
                if (runWidgetOnStart && !window.ctpt_wAutoStarted) {
                    window.ctpt_wAutoStarted = true;
                    init();
                }
            }

            return tmpHTML;
        },

        getLink: function () {
            if (typeof (deliveries) === 'undefined') return false;

            for (var i in deliveries) {
                return i;
            }

            return false;
        },

        // loader 4 new templates
        loader: {
            timer: false, listner: function () {
                if (labelController.loader.timer) {
                    clearTimeout(labelController.loader.timer);
                    labelController.loader.timer = false;
                    initLabels();
                } else {
                    labelController.loader.timer = setTimeout(labelController.loader.listner, 1000);
                }
            }
        }
    };

    // deals with user property for saving address there
    var addressPropertyController = {
        do: function () {
            if (addressPropertyController.checkCorrespond()) {
                addressPropertyController.label();
                addressPropertyController.markUnable();
            }
        }, // can be done without it - but better check so because of old template
        checkCorrespond: function () {
            return true;
        },

        label: function () {
            var input = addressPropertyController.getInput();
            if (input) {
                if (pvz) {
                    input.val(pvz.address + (pvz.code ? ' #' + pvz.code : ''));
                } else if (deliveryVariant) {
                    let addressData = catapulto_delivery_pvzWidjet.widjetController.getData().getVariant(),
                        address = addressData.value;

                    input.val(address);

                    /*if(addressData.data.street_with_type) {
                        //address = address.replace(addressData.data.city_with_type + ',', '').trim();
                        address = addressData.value.trim();
                        input.val(address);
                    }
                    else {
                        if (!inputIsCleaned || input.val() === address.value) {
                            address = '';
                            input.val(address);
                            inputIsCleaned = true;
                        }
                    }*/



                } else if (!deliveryVariant && ready && deliveryHandler.guess(deliveries[labelController.getLink()].link)) {
                    input.val('');
                }
            }
        },

        markUnable: function () {
            if (pvz) {
                var input = addressPropertyController.getInput();
                if (input && pvz) {
                    input.css('background-color', '#eee').attr('readonly', 'readonly');
                }
            }
        },

        getInput: function () {
            var chznPnkt = false;
            if (typeof (pvzPicker) === 'object') {
                for (var i in pvzPicker) {
                    if (typeof (pvzPicker[i]) === 'string') {
                        chznPnkt = $('[name="ORDER_PROP_' + pvzPicker[i] + '"]');
                        if (chznPnkt.length) {
                            break;
                        }
                    }
                }
            }

            return chznPnkt;
        }
    };

    // deals with deliveries: which is chosen
    var deliveryHandler = {
        // defining of chosen delivery
        check: function (delId, delivery) {
            for (let i in delivery) if (delivery[i].CHECKED === 'Y') {
                return delId.includes(delivery[i].ID);
            }
            return false;
        },

        guess: function (delId) {
            const dlvIdslbl = [];
            for (let i in delId) dlvIdslbl.push(deliveryHandler.makeId(delId[i]));
            return dlvIdslbl.includes($('[name="DELIVERY_ID"]:checked').attr('ID'));
        },

        makeId: function (id) {
            return 'ID_DELIVERY_ID_' + id;
        }
    };

    var paysysHandler = {
        check: function (psId, paysysts) {
            for (var i in paysysts) if (paysysts[i].CHECKED === 'Y') {
                return (paysysts[i].ID === psId);
            }
            return false;
        },

        guess: function (psId) {
            return (paysysHandler.makeId(psId) === $('[name="PAY_SYSTEM_ID"]:checked').attr('ID'));
        },

        makeId: function (id) {
            return 'ID_PAY_SYSTEM_ID_' + id;
        }
    };

    // saving PVZ for future workout
    function preservePVZ() {
        var input = $('#' + savingInput);
        if (!input.length) {
            var handler = false;
            if (oldTemplate) {
                handler = $('#ORDER_FORM');
            } else {
                handler = $('[name="ORDER_FORM"]');
            }
            if (handler.length) {
                handler.append('<input type="hidden" name="' + savingInput + '" id="' + savingInput + '" value="">');
            }
            input = $('#' + savingInput);
        }
        if (pvz) {
            input.val(pvz.id);
        } else {
            input.val('');
        }
    }

    // saving delivery type for future workout
    function preserveDeliveryType() {
        var input = $('#' + savingInputDelivery);
        if (!input.length) {
            var handler = false;
            if (oldTemplate) {
                handler = $('#ORDER_FORM');
            } else {
                handler = $('[name="ORDER_FORM"]');
            }
            if (handler.length) {
                handler.append('<input type="hidden" name="' + savingInputDelivery + '" id="' + savingInputDelivery + '" value="">');
            }
            input = $('#' + savingInputDelivery);
        }

        if (deliveryVariant) {
            const newAddress = deliveryVariant.dadata;
            console.log('dlvVariant', deliveryVariant);

            let obRateResult = {
                id: deliveryVariant.variant.id,
                receiver_loc_id: deliveryVariant.rate_param.receiver_locality_id,
                receiver_zip: deliveryVariant.locations?.contact?.zip ?? '',
                sender_loc_id: deliveryVariant.rate_param.sender_locality_id,
                sender_zip: deliveryVariant.locations?.sender?.zip ?? '',
                cargoes: deliveryVariant.rate_param.cargoes,
                delivery_date: deliveryVariant.selected ? deliveryVariant.selected.delivery_date : deliveryVariant.variant.delivery_day,
                delivery_time: deliveryVariant.selected ? deliveryVariant.selected.delivery_time : deliveryVariant.variant.delivery_time,
                operator: deliveryVariant.variant.operator,
                pickup_date: deliveryVariant.selected ? deliveryVariant.selected.pickup_date : deliveryVariant.variant.pickup_day,
                pickup_time: deliveryVariant.selected ? deliveryVariant.selected.pickup_time : null,
                rate: deliveryVariant.variant.rate,
                shipping_type: deliveryVariant.variant.shipping_type,
                price: deliveryVariant.variant.price,
                price_orig: deliveryVariant.variant.price_orig,
                was_cod: false,
                services_filter: window.catapulto_widget_params.services_filter,
                extraPrice: window.catapulto_widget_params.extraPrice || {},
                services: deliveryVariant.variant.additional_services,
                base_price: deliveryVariant.variant._priceWithoutAdditionalServices,
                base_price_with_services: deliveryVariant.variant._priceWithAdditionalServices,
                is_fitting: deliveryVariant.variant._isFitting,
                with_partial_red: window.rateWithPartialRed,
                insurance_config: deliveryVariant.variant.insurance_config ?? 0,
            }, isPVZ = false, PVZAddress = '';
            if (window.catapulto_widget_params.services_filter.search('COD') >= 0)
                obRateResult.was_cod = true;

            if(deliveryVariant.Terminal) {
                obRateResult['terminal_cash'] = deliveryVariant.Terminal.cash;
                obRateResult['terminal_card'] = deliveryVariant.Terminal.card;
                obRateResult['terminal_code'] = deliveryVariant.Terminal.code;
                isPVZ = true;
                PVZAddress = ((newAddress.data.settlement) ? newAddress.data.settlement : newAddress.data.city_with_type) + ', ' + deliveryVariant.Terminal.address + '#' + deliveryVariant.Terminal.code;
            }

            let rateData = {
                rate_result_id: deliveryVariant.variant.id,
                sender_locality_id: deliveryVariant.rate_param.sender_locality_id,
                sender_warehouse_id: deliveryVariant.rate_param.sender_warehouse_id,
                receiver_locality_id: deliveryVariant.rate_param.receiver_locality_id,
                rate_result: JSON.stringify(obRateResult),
                rate_cost: deliveryVariant.variant.price,
                rate_term: (deliveryVariant.selected && deliveryVariant.selected.delivery_date) ? formatDate(deliveryVariant.selected.delivery_date) : '',
                isPVZ: isPVZ,
                PVZAddress: PVZAddress,
                dadata: JSON.stringify({
                    zip: newAddress.data.postal_code,
                    country: newAddress.data.country,
                    region: newAddress.data.region_with_type,
                    city: newAddress.data.city_with_type,
                    settlement: newAddress.data.settlement,
                    street: newAddress.data.street_with_type,
                    houset: newAddress.data.house_type,
                    house: newAddress.data.house,
                    blockt: newAddress.data.block_type,
                    block: newAddress.data.block,
                    flatt: newAddress.data.flat_type,
                    flat: newAddress.data.flat,
                    value: newAddress.value,
                    unrestricted_value: newAddress.unrestricted_value,
                    fias_level: newAddress.data.fias_level,
                    city_fias_id: newAddress.data.city_fias_id,
                    settlement_type: newAddress.data.settlement_type,
                    settlement_fias_id: newAddress.data.settlement_fias_id,
                    country_iso_code: newAddress.data.country_iso_code,
                }),
            };

            input.val(JSON.stringify(rateData));
        } else {
            input.val(JSON.stringify({
                rate_cost: LANG.default_cost, rate_term: LANG.default_term
            }));
        }
    }

    // reload form
    function reloadForm() {
        if (oldTemplate) {
            if (typeof ('submitForm') !== 'undefined') {
                submitForm();
            }
        } else {
            if (typeof (BX.Sale) !== 'undefined') {
                BX.Sale.OrderAjaxComponent.sendRequest();
            }
        }
    }

    setTimeout(self.onLoad, 1000);

// service
    function isEmpty(obj) {
        if (typeof (obj) === 'object') for (var i in obj) return false;
        return true;
    }

    // logging
    function log(wat) {
        if (true) {
            if (label) console.log(label + ": ", wat); else console.log(wat);
        }

    }

    function error(wat) {
        if (label) console.error(label + ": ", wat); else console.error(wat);
    }

    function formatDate(d) {
        if (d.indexOf('-') === 4) {
            let arD = d.split('-');
            return arD[2] + '.' + arD[1] + '.' + arD[0];
        } else {
            return d;
        }
    }

    this.log = function (wat) {
        log(wat);
    };
}
