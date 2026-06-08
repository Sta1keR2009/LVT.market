function catapulto_delivery_serviceWidjet(deliveries,savingInput,inputname,postField,iPE){
    var self     = this;
    var label    = 'PE serviceWidjet';
    var services = {};
    this.error = false;

    if(typeof(deliveries) !== 'object' || isEmpty(deliveries)){
        error('No module deliveries found');
        this.error = true;
        return false;
    }

    if(typeof(iPE) === 'undefined'){
        iPE = 'CATAPULTO_DELIVERY_';
    }

    if(typeof(inputname) == 'undefined'){
        inputname = iPE + 'service';
    }

    if(typeof(savingInput) === 'undefined' || !savingInput){
        savingInput = iPE + 'courier_tarif';
    }

    if(typeof(postField) === 'undefined' || !postField){
        postField = iPE + 'calculatedServices';
    }

    var chosenTarif = false;
    if(typeof(chosenTarif) === 'undefined' || !chosenTarif){
        chosenTarif = 'chosenTarif';
    }

    var oldTemplate = $('#ORDER_FORM').length;

    this.addServices = function(newServices){
        services = newServices;
    };

    this.onLoad = function (ajaxAns) {
        if(typeof(ajaxAns) === 'object' && typeof(ajaxAns.order) === 'undefined')
            return;

        var newTemplateAjax = (typeof(ajaxAns) !== 'undefined' && ajaxAns !== null && typeof(ajaxAns[iPE+'serviceWidjet']) === 'object');

        if(typeof(ajaxAns) === 'undefined'){
            ajaxAns = false;
        }
// TODO : mind deliveries

        var checkedChosen = false;
        if(newTemplateAjax) {
            if(typeof(ajaxAns[iPE+'serviceWidjet']) !== 'undefined'){
                if(typeof(ajaxAns[iPE+'serviceWidjet'][postField]) !== 'undefined'){
                    services = ajaxAns[iPE+'serviceWidjet'][postField];
                } else{
                    services = {};
                }

                if(typeof(ajaxAns[iPE+'serviceWidjet'][chosenTarif]) !== 'undefined'){
                    for(var i in services){
                        if(
                            typeof(services[i]) !== 'function' &&
                            i === ajaxAns[iPE+'serviceWidjet'][chosenTarif]
                        )
                        {
                            services[i].checked = true;
                            checkedChosen = true;
                            break;
                        }
                    }
                }
            }
        }else{
            var inputServices = $('#'+iPE+postField);
            if(inputServices.length){
                var calculatedServices = $.parseJSON(inputServices.val());
                if(calculatedServices && typeof(calculatedServices) === 'object'){
                    services = calculatedServices;
                    var savedService = $('#'+iPE+chosenTarif);
                    if(savedService.length){
                        for(var i in services){
                            if(
                                typeof(services[i]) !== 'function' &&
                                services[i] === savedService.val()
                            )
                            {
                                services[i].checked = true;
                                checkedChosen = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if(!checkedChosen){
            var indexLowest = false;
            for(var i in services){
                if(typeof(services[i]) !== 'function'){
                  if(!indexLowest || parseFloat(services[indexLowest].PRICE) > parseFloat(services[i].PRICE)){
                      indexLowest = i;
                  }
                }
            }

            if(indexLowest){
                services[indexLowest].checked = true;
            }
        }

        makeReload(ajaxAns);
        preserveServices();
    };

    if(typeof BX !== 'undefined' && BX.addCustomEvent)
        BX.addCustomEvent('onAjaxSuccess', self.onLoad);

    // finds places to add the html
    function makeReload(ajaxAns){
        var tag = false;
        for(var i in deliveries){
            tag = false;
            if(deliveries[i].self) {
                tag = $('#' + i);
            }else{
                if(oldTemplate){
                    var parentNd=$('#'+makeHTMLId(i));
                    if(!parentNd.length) continue;
                    if(parentNd.closest('td', '#ORDER_FORM').length>0)
                        tag = parentNd.closest('td', '#ORDER_FORM').siblings('td:last');
                    else
                        tag = parentNd.siblings('label').find('.bx_result_price');
                }
                else {
                    if (
                        (typeof(ajaxAns.order) !== 'undefined' && checkCheckedDel(i, ajaxAns.order.DELIVERY))
                        ||
                        (!ajaxAns && guessCheckedDel(i))
                    ) {
                        var lair = 'injectHere';
                        if (!$('#' + iPE + lair).length) {
                            $('#bx-soa-delivery').find('.bx-soa-pp-company-desc').after('<div id="' + iPE + lair + '"></div>');
                        }
                        if (!$('#' + iPE + lair).length) {
                            newTemplateLoader.listner();
                        } else
                            tag = $('#' + iPE + lair);
                    }
                }
            }
        }

        if(tag.length>0 && !tag.find('.'+iPE+'selectServices').length){
            deliveries[i].tag = tag;
            placeHtml(i);
        }
    }

    var newTemplateLoader = {
        timer   : false,
        listner : function (){
            if(newTemplateLoader.timer){
                clearTimeout(newTemplateLoader.timer);
                newTemplateLoader.timer = false;
                self.onLoad();
            }else{
                newTemplateLoader.timer = setTimeout(newTemplateLoader.listner, 1000);
            }
        }
    };

    // add selector
    function placeHtml(deliveryId){
        var tmpHTML = "<table class='"+iPE+"selectServices'></table>";
        deliveries[deliveryId].tag.html(tmpHTML);

        if(!isEmpty(services)) {
            for (var i in services) {
                if (services[i].PRICE !== false) {
                    var curId = iPE + "service_" + i;
                    tmpHTML = "<tr><td>" +
                        "<input type='radio' value='"+i+"' name='" + inputname + "' id='" + curId + "' " + (services[i].checked ? "checked" : "") + "></td><td>" +
                        "<label for='" + curId + "'>" + services[i].NAME;
                    if (typeof(services[i].HINT) !== 'undefined' && services[i].HINT) {
                        tmpHTML += "&nbsp;<a href='javascript:void(0)' title='" + services[i].HINT + "' class='" + iPE + "PropHint'></a>";
                    }
                    tmpHTML += "<br>" + services[i].PRICE + "&nbsp;&nbsp;" + services[i].TERM +
                        "</label></td></tr>";
                    deliveries[deliveryId].tag.children('.' + iPE + 'selectServices').append(tmpHTML);
                    $('#' + curId).on('click', self.choseService);
                }
            }
        }
    }

    // click on service
    this.choseService = function(wat){
        var handle    = false;
        var serviceId = false;
        if(typeof(wat) === 'object'){
            handle    = $(wat.currentTarget);
            serviceId = handle.attr('id').substr(iPE.length+8);
        } else {
            handle    = $('#'+iPE+"service_"+wat);
            serviceId = wat;
        }

        for(var i in services){
            if(typeof(services[i]) !== 'function'){
                if(i == serviceId){
                    services[i].checked = true;
                } else {
                    services[i].checked = false;
                }
            }
        }

        preserveServices();
        reloadForm();
    };

    // saving services for future workout
    function preserveServices(){
        if(!$('#'+savingInput).length)
            $('[name="ORDER_FORM"]').append('<input type="hidden" name="'+savingInput+'" id="'+savingInput+'" value="">');
        var serviceSave = '';
        for(var i in services){
            if(services[i].checked){
                serviceSave = i;
                break;
            }
        }
        $('#'+savingInput).val(serviceSave);
    }

    // reload form
    function reloadForm(){
        if(oldTemplate){
            if(typeof ('submitForm') !== 'undefined') {
                submitForm();
            }
        }else {
            if (typeof(BX.Sale) !== 'undefined') {
                BX.Sale.OrderAjaxComponent.sendRequest();
            }
        }
    }

    // defining of chosen delivery
    function checkCheckedDel (delId,delivery){
        for(var i in delivery)
            if(delivery[i].CHECKED === 'Y'){
                return (delivery[i].ID === delId);
            }
        return false;
    }

    function makeHTMLId(id){
        return 'ID_DELIVERY_ID_'+id;
    }

    function guessCheckedDel(delId){
        return (makeHTMLId(delId) === $('[name="DELIVERY_ID"]:checked').attr('ID'));
    }

    setTimeout(self.onLoad,1000);

// service
    function isEmpty(obj){
        if(typeof(obj) === 'object')
            for(var i in obj)
                return false;
        return true;
    }
    // logging
    function log(wat){
        if(true) {
            if (label)
                console.log(label+": ",wat);
            else
                console.log(wat);
        }

    }

    function error(wat){
        if (label)
            console.error(label+": ",wat);
        else
            console.error(wat);
    }

    this.log = function(wat){
        log(wat);
    };
}