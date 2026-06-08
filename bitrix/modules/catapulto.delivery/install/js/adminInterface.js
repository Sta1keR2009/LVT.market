/**
 * Created by Mebiys on 07.06.2017.
 */
function catapulto_delivery_adminInterface(params){

    // initing

    var self = this;

    var options = {
        logging  : true,
        label    : false,
        ajaxPath : false
    };

    if(typeof(params) !== 'object')
        params = {};

    function init(params){
        for(var i in params){
            if(typeof(params[i]) !== 'function'){
                options[i] = params[i];
            }
        }
    }

    init(params);

    // merger (insanity was intense)

    this.expander = function(obj){
        for(var i in obj){
            if(typeof(self[i] === 'undefined'))
                self[i] = (typeof(obj[i]) === 'object') ? self.copyObj(obj[i]) : obj[i];
            else
                log('Duplicate key '+i+' in expander');
        }
    };

    // ajax

    var ajax = function(params){
        if(!options.ajaxPath && typeof(params.url) === 'undefined'){
            error('admin interface ajax error: no url setted');
            return false;
        }
        if(typeof(params) !== 'object')
            params = {};
        var ajaxOptions = {
            url  : (typeof(options.ajaxPath) === 'undefined') ? false : options.ajaxPath,
            data : false,
            type : 'POST',
            success : function(data){},
            error   : function (a,b,c) {
                error(['admin interface ajax error',b,c]);
            }
        };
        if (typeof(options.dataType) !== 'undefined') ajaxOptions.dataType = options.dataType;

        var allowedKeys = ['accepts','async','beforeSend','cache','complete','contents','contentType','context','converters','crossDomain','data','dataFilter','dataType','error','global','headers','ifModified','isLocal','jsonp','jsonpCallback','mimeType','password','processData','scriptCharset','statusCode','success','timeout','traditional','type','url','username','xhr','xhrFields'];

        for(var i in params){
            if(self.inArray(i,allowedKeys))
                ajaxOptions[i] = params[i];
        }

        log('>>> requesting data from '+ajaxOptions.url);
        log(ajaxOptions.data);

        $.ajax(ajaxOptions);
    };

    this.ajax = function(params){
        ajax(params);
    };

    // pages
    var pages = {};

    function getPage(index){
        if(typeof(pages[index]) !== 'undefined')
            return pages[index];
        return false;
    }

    this.addPage = function(index,wat){
        pages[index]      = this.copyObj(wat);
        pages[index].self = self;
    };

    this.getPage = function(index){
        return getPage(index);
    };

    this.init = function(index){
        if(typeof(index) !== 'string'){
            for(var i in pages){
                if(typeof(pages[i].init) === 'function' && (typeof(pages[i].inited) === 'undefined' || !pages[i].inited)){
                    pages[i].init();
                    pages[i].inited = true;
                }
            }
        }else{
            var page = getPage(index);
            if(page && typeof(page.init) === 'function' && (typeof(page.inited) === 'undefined' || !page.inited)){
                page.init();
                page.inited = true;
            }else{
                log('Unable to init page of index '+index);
            }
        }
    };

    this.reinit = function(index){
        if(typeof(index) !== 'string'){
            for(var i in pages){
                pages[i].inited = false;
            }
        }else{
            var page = getPage(index);
            page.inited = false;
        }

        self.init(index);
    };

    // ui
    this.popup = function (code, info, basement, left = false, top = false) {
        let topOffset = $(info).position().top,
            leftOffset = parseInt($(info).position().left),
            leftOffsetWin = parseInt($(info).offset().left),
            obj;

        if (code === 'next') obj = $(info).next();
        else obj = $('#' + code);

        let subj = $(window).width();
        let newLeftOffset = leftOffset - parseInt(obj.outerWidth());
        if(newLeftOffset < 0) {
            newLeftOffset = 10;
        }

        if(basement && typeof(basement) !== 'undefined') {
            subj = $(basement).offset().left;
            let newLeftOffset = (parseInt(subj) - parseInt(obj.outerWidth())) / 2;
            let diffWidth = (leftOffsetWin + newLeftOffset) - $(window).width();
            if(diffWidth > 0) {
                newLeftOffset -= diffWidth;
            }
        }

        obj.css({top: (topOffset + 15) + 'px', display: 'block'});
        obj.css('left', newLeftOffset);
        if (top !== false) obj.css('top', top);
        if (left !== false) obj.css('left', left);
        return false;
    };

    this.reload = function(){
        window.location.reload();
    };

    // service

    this.isEmpty = function(obj){
        if(typeof(obj) === 'object')
            for(var i in obj)
                return false;
        return true;
    };

    this.checkFloat = function(wat){
        var val = parseFloat(wat.val().replace(',','.'));
        wat.val((isNaN(val)) ? 0 : val);
    };

    this.copyObj = function(obj){
        if(obj === null || typeof(obj) !== 'object')
            return obj;
        if(obj.constructor === Array)
            return [].concat(obj);
        var temp = {};
        for(var key in obj)
            temp[key] = this.copyObj(obj[key]);
        return temp;
    };

    this.inArray = function(wat,arr){
        return arr.filter(function(item){return item == wat}).length;
    };

    this.concatObj = function(main,sub){
        if(typeof(main) === 'object' && typeof(sub) === 'object')
            for(var i in sub)
                main[i] = sub[i];
        return main;
    };

    this.replaceAll = function(string,search,replace){
        return string.split(search).join(replace);
    };

    // logging
    function log(wat){
        if(options.logging) {
            if (options.label)
                console.log(options.label+": ",wat);
            else
                console.log(wat);
        }

    }
    function error(wat){
        if (options.label)
            console.error(options.label+": ",wat);
        else
            console.error(wat);
    }
    this.log = function(wat){
        log(wat);
    };
}
