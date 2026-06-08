/**
 * @type {Object}
 * @name CatapultoYmapNamespace
 */
/**
 * @type {Function}
 * @name CatapultoYmapNamespace.SuggestView
 */
/**
 * @type {Function}
 * @name CatapultoYmapNamespace.vow
 */
/**
 * @type {Object}
 * @name CatapultoYmapNamespace.map
 */
/**
 * @type {Object}
 * @name CatapultoYmapNamespace.Map
 */
/**
 * @type {Object}
 * @name CatapultoYmapNamespace.control
 */
/**
 * @type {Function}
 * @name CatapultoYmapNamespace.control.ZoomControl
 */
/**
 * @type {Function}
 * @name CatapultoYmapNamespace.templateLayoutFactory
 */
/**
 * @type {Function}
 * @name CatapultoYmapNamespace.templateLayoutFactory.createClass
 */
/**
 * @type {Function}
 * @name CatapultoYmapNamespace.Map.setCenter
 */
/**
 * @type {Function}
 * @name CatapultoYmapNamespace.Map.geoObjects
 */
/**
 * @type {Function}
 * @name CatapultoYmapNamespace.Map.setZoom
 */
/**
 * @type {Function}
 * @name CatapultoYmapNamespace.Placemark
 */
/**
 * @type {Function}
 * @name PlaceMark.events
 */

/**
 * @type {{Suggestion: {country, city_district_fias_id, settlement_type_full, flat_fias_id, block_type, settlement_fias_id, city_district_kladr_id, region_type: string, source, area_type_full, okato: string, house_fias_id, geoname_id: string, street_with_type, fias_id: string, federal_district, area_kladr_id, qc, region_type_full: string, beltway_hit, square_meter_price, geo_lon: string, region_fias_id: string, block_type_full, block, city_district, entrance, city_type: string, area_fias_id, area, house_cadnum, flat_type, settlement_kladr_id, settlement_with_type, area_with_type, fias_actuality_state: string, fias_level: string, area_type, beltway_distance, country_iso_code: string, city_fias_id: string, fias_code: string, metro, kladr_id: string, tax_office: string, house_kladr_id, postal_box, region: string, settlement_type, flat_price, qc_complete, qc_house, city: string, timezone, flat_area, house, settlement, region_iso_code: string, capital_marker: string, region_kladr_id: string, street, flat, qc_geo: string, house_type, floor, city_district_with_type, city_kladr_id: string, city_type_full: string, city_area, street_kladr_id, flat_type_full, flat_cadnum, house_type_full, oktmo: string, city_district_type, geo_lat: string, history_values, street_type_full, city_district_type_full, tax_office_legal: string, street_type, region_with_type: string, postal_code: string, city_with_type: string, unparsed_parts, street_fias_id: null}, unrestricted_value: string, value: string}}
 * @name Suggestion
 */
(function() {

    const ApiObjectKeys = {
        delivery_date: 'delivery_day',
        delivery_rating: 'delivery_success_rating',
        rating: 'operator_rating',
    }

    const CSS_string_min = '@import url(https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap);.ctpl_popup_mode_close {width: 48px; height: 48px; content: url(\'data:image/svg+xml; utf8, <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="48" height="48" rx="4" fill="%23EFF2F5"/><path d="M33 16.8129L31.1871 15L24 22.1871L16.8129 15L15 16.8129L22.1871 24L15 31.1871L16.8129 33L24 25.8129L31.1871 33L33 31.1871L25.8129 24L33 16.8129Z" fill="%230073BC"/></svg>\');}.ctpl_terminal_date_icon_svg{top: 2px;position: relative;margin-right: 3px;content: url(\'data:image/svg+xml; utf8, <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.5 0.525146H1.5C0.671562 0.525146 0 1.19671 0 2.02515V13.0251C0 13.8536 0.671562 14.5251 1.5 14.5251H14.5C15.3284 14.5251 16 13.8536 16 13.0251V2.02515C16 1.19671 15.3284 0.525146 14.5 0.525146ZM5 13.5251H1.5C1.22384 13.5251 1 13.3013 1 13.0251V10.5251H5V13.5251ZM5 9.52515H1V6.52515H5V9.52515ZM5 5.52515H1V2.52515H5V5.52515ZM10 13.5251H6V10.5251H10V13.5251ZM10 5.52515H6V2.52515H10V5.52515ZM15 10.5251V13.0251C15 13.3013 14.7762 13.5251 14.5 13.5251H11V10.5251H15ZM15 9.52515H11V6.52515H15V9.52515ZM15 5.52515H11V2.52515H15V5.52515Z" fill="%2397A3B6"></path></svg>\');}.ctp_point_icon.selected {filter: invert(0) sepia(0) saturate(2) hue-rotate(0);} .ctp_point_icon {position: relative;} .ctp_point_icon img {position: absolute;top: -40px;left: -20px; width: 40px!important;height: 43px!important;max-width: 40px!important;max-height: 43px!important;} .ctpt-hidden{opacity:0}#ctpt-loader{position:absolute;width:100%;height:100%;z-index:3;background:rgba(0,0,0,.2)}.ctpt-preloader{position:absolute;width:40px;height:40px;left:calc(50% - 20px);top:calc(50% - 20px);background:#007dd5;transform:rotate(45deg);animation:preloader 2s linear infinite}.ctpt-loadBar{position:absolute;width:200px;height:2px;left:calc(50% - 100px);top:calc(50% + 60px);background:rgba(0,0,0,.2)}.ctpt-progress{position:relative;width:0%;height:inherit;background:#007dd5}@keyframes loading{0%{width:0%}100%{width:100%}}@keyframes preloader{0%,100%{transform:rotate(45deg)}60%{transform:rotate(405deg)}}.ctpt-widget__map .ymaps-2-1-79-searchbox__normal-layout{width:398px}.ctpt-widget__map .ymaps-2-1-79-searchbox-input__input{height:36px;line-height:36px;font-size:15px;font-family:Roboto,sans-serif;font-weight:400;-webkit-border-radius:0;-moz-border-radius:0;-ms-border-radius:0;border-radius:0}.ctpt-widget__map .ymaps-2-1-79-searchbox-input.ymaps-2-1-79-_focused{box-shadow:inset 0 0 0 2px #007dd5}.ctpt-widget__map .ymaps-2-1-79-searchbox-button{height:36px;background-color:#007dd5;border-color:#007dd5}.ctpt-widget__map .ymaps-2-1-79-searchbox-button-text{font-size:13px;line-height:36px;padding:0 12px 0 42px;color:#fff;position:relative}.ctpt-widget__map .ymaps-2-1-79-searchbox-button-text:before{content:"";position:absolute;top:50%;left:12px;width:20px;height:16px;background:url(../images/icon/search-icon.svg);background-repeat:no-repeat;background-size:cover;background-position:center;-moz-transform:translate(0,-50%);-o-transform:translate(0,-50%);-ms-transform:translate(0,-50%);-webkit-transform:translate(0,-50%);transform:translate(0,-50%)}.ctpt-widget__map .ymaps-2-1-79-searchbox-button:hover{background-color:#fff;color:#007dd5;border-color:#007dd5}.ctpt-widget__map .ymaps-2-1-79-searchbox-button:hover .ymaps-2-1-79-searchbox-button-text{color:#007dd5}.ctpt-widget__map .ymaps-2-1-79-searchbox-button:hover .ymaps-2-1-79-searchbox-button-text:before{background:url(../images/icon/search-icon-h.svg)}.ctpt-widget__logo-link{display:inline-block}.ctpt-widget__load{display:inline-block;width:24px;height:24px;margin-right:10px}.ctpt-widget__load:after{content:" ";display:block;width:100%;height:100%;border-radius:50%;border:2px solid #007dd5;border-color:#007dd5 #007dd5 #007dd5 transparent;animation:rotateLogo 1.2s linear infinite}.ctpt-widget__load-spinner{position:absolute;bottom:0;left:0;right:0;height:82px;background:#fff;z-index:2;color:#007dd5;font-size:15px;line-height:1.33;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;align-items:center;justify-content:center;font-family:Roboto,sans-serif;font-weight:400;animation:blink 4s linear infinite}.ctpt-widget__scroll{scrollbar-width:thin}.ctpt-widget__scroll::-webkit-scrollbar{width:12px;background-color:#eff2f5;-webkit-border-radius:0;-moz-border-radius:0;-ms-border-radius:0;border-radius:0}.ctpt-widget__scroll::-webkit-scrollbar-thumb{-webkit-border-radius:0;-moz-border-radius:0;-ms-border-radius:0;border-radius:0;background-color:rgba(151,163,182,.5);opacity:.5;width:12px}.ctpt-widget__delivery-rating{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center}.ctpt-widget__delivery-rating svg.current path{fill:#ff9600}.ctpt-widget__delivery-info-hint{position:absolute;top:50%;right:0;-moz-transform:translate(0,-50%);-o-transform:translate(0,-50%);-ms-transform:translate(0,-50%);-webkit-transform:translate(0,-50%);transform:translate(0,-50%);cursor:pointer}.ctpt-widget__delivery-info-hint:hover svg path{fill:#007dd5}.ctpt-widget__delivery-info-hint.is-visible .ctpt-widget__info-hint-text{display:block;z-index:20}.ctpt-widget__info-hint-text{-moz-transform: translate(30px, -47%); -o-transform: translate(30px, -47%); -ms-transform: translate(30px, -47%); -webkit-transform: translate(30px, -47%); transform: translate(30px, -47%);position:fixed;top:50%;left:30px;z-index:135;width:320px;height:270px;-webkit-transition:.3s ease-out;-o-transition:.3s ease-out;transition:.3s ease-out;display:none;background-color:#fff;border-radius:5px;padding:30px 0 15px 15px;-webkit-box-shadow:0 10px 50px 0 rgba(7,11,37,.2);box-shadow:0 10px 50px 0 rgba(7,11,37,.2)}.ctpt-widget__info-hint-text:before{content:"";border:10px solid transparent;border-right-color:#fff;position:absolute;top:50%;margin-top:-10px;left:-20px}.ctpt-widget__info-hide-cont{overflow:auto;padding-right:15px}.ctpt-widget__info-hide-title{font-size:16px;color:#333;font-family:Roboto,sans-serif;font-weight:500;margin-bottom:10px}.ctpt-widget__info-hide-desc{font-size:14px;color:#333;font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget__button{padding: 0;width:128px;height:40px;color:#007dd5;background:#fff;border:1px solid #007dd5;cursor:pointer;font-size:20px;line-height:1;text-transform:uppercase;font-family:Roboto,sans-serif;font-weight:500;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;align-items:center;justify-content:center;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s;-webkit-border-radius:4px;-moz-border-radius:4px;-ms-border-radius:4px;border-radius:4px}.ctpt-widget__disabled .ctpt-widget__panel-content__status-icon:before{content:url(\'data:image/svg+xml; utf8, <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M17.7778 2.22222V17.7778H2.22222V2.22222H17.7778ZM17.7778 0H2.22222C1 0 0 1 0 2.22222V17.7778C0 19 1 20 2.22222 20H17.7778C19 20 20 19 20 17.7778V2.22222C20 1 19 0 17.7778 0Z" fill="%23AAAAAA"/></svg>\')!important}.ctpt-widget__disabled .ctpt-widget__panel-content__logo img{-webkit-filter:grayscale(100%);-moz-filter:grayscale(100%);-ms-filter:grayscale(100%);-o-filter:grayscale(100%);filter:grayscale(100%);filter:gray;opacity:.33}.ctpt-widget__disabled .ctpt-widget__panel-content__info{opacity:.33}.ctpt-widget__close{border:none;background:0 0;position:absolute;right:6px;top:8px;padding:0;cursor:pointer;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s}.ctpt-widget__close svg{width:16px;height:16px;opacity:1}.ctpt-widget__primary-title{text-align:left;font-size:20px;line-height:1.17;color:#000;font-family:Roboto,sans-serif;font-weight:400;padding:18px 13px;position:relative}.ctpt-widget__primary-title span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:350px}.ctpt-widget__sidebar-button{width:100%;height:60px;cursor:pointer;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;align-items:center;justify-content:center;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s;position:relative}.ctpt-widget__sidebar-button:after{content:"";position:absolute;left:0;top:50%;width:0;height:0;opacity:0;visibility:hidden;border-style:solid;border-width:10px 0 10px 10px;border-color:transparent transparent transparent #fff;-moz-transform:translate(0,-50%);-o-transform:translate(0,-50%);-ms-transform:translate(0,-50%);-webkit-transform:translate(0,-50%);transform:translate(0,-50%)}.ctpt-widget__sidebar-button:nth-child(odd):before{content:"";position:absolute;left:0;right:0;bottom:0;height:1px;background:#eff2f5}.ctpt-widget__sidebar-button.current{background:#eff2f5}.ctpt-widget__sidebar-button-setting-icon{position:absolute;top:5px;right:5px;width:20px;height:20px;background:red;-webkit-border-radius:50%;-moz-border-radius:50%;-ms-border-radius:50%;border-radius:50%;display:none}.ctpt-widget__sidebar-button-setting-icon.current{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;align-items:center;justify-content:center}.ctpt-widget__sidebar-button-back-text{font-size:10px;color:#007dd5;font-family:Roboto,sans-serif;font-weight:500;text-align:center;margin-bottom:4px}.ctpt-widget__sidebar-button-wrap{flex-grow:1;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column}.ctpt-widget__sidebar-button-checked.current{background:#eff2f5}.ctpt-widget__sidebar-button-checked.current:after{display:none}.ctpt-widget__sidebar-button__hint{height:30px;position:absolute;right:-300px;background:#e6f2fb;color:#007dd5;padding:5px 12px;border-radius:30px;cursor:default;opacity:0;z-index:10;top:50%;width:max-content;font-family:Roboto,sans-serif;font-weight:500;-webkit-border-radius:30px;-moz-border-radius:30px;-ms-border-radius:30px;border-radius:30px;-webkit-transition:right .5s ease-out,opacity .3s ease-out;-moz-transition:right .5s ease-out,opacity .3s ease-out;-ms-transition:right .5s ease-out,opacity .3s ease-out;-o-transition:right .5s ease-out,opacity .3s ease-out;transition:right .5s ease-out,opacity .3s ease-out}.ctpt-widget__sidebar-button__hint.show{right:67px;opacity:1}.ctpt-widget__sidebar-button__hint.ctpt-widget-list{top:17px}.ctpt-widget__sidebar-button__hint.ctpt-widget-cash{top:76px}.ctpt-widget__sidebar-button__hint.ctpt-widget-cal{top:134px}.ctpt-widget__sidebar-button__hint.ctpt-widget-delivery{top:195px}@keyframes rotateLogo{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}@keyframes blink{0%{color:#007dd5}25%{color:rgba(0,125,213,.1)}50%{color:#007dd5}75%{color:rgba(0,125,213,.1)}100%{color:#007dd5}}.ctpt-widget__price{font-family:Roboto,sans-serif;font-weight:700;color:#333}.ctpt-widget__days{color:#333;font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget__delivery-container{padding:0 13px;border-bottom:1px solid rgba(0,0,0,.1);background:#fff}.ctpt-widget__delivery-container .ctpt-widget__primary-title{padding:10px 0}.ctpt-widget__delivery-type{overflow: hidden;line-height: 1;position:absolute;top:100%;left:50%;width:460px;z-index:7;height:calc(100% - 100px);background:#eff2f5;-webkit-transition:right .2s ease-out,opacity .1s ease-out;-moz-transition:right .2s ease-out,opacity .1s ease-out;-ms-transition:right .2s ease-out,opacity .1s ease-out;-o-transition:right .2s ease-out,opacity .1s ease-out;transition:right .2s ease-out,opacity .1s ease-out;-moz-transform:translate(-50%,0);-o-transform:translate(-50%,0);-ms-transform:translate(-50%,0);-webkit-transform:translate(-50%,0);transform:translate(-50%,0)}.ctpt-widget__delivery-type.current{top:85px;-webkit-transition:top .5s ease-out,opacity .3s ease-out;-moz-transition:top .5s ease-out,opacity .3s ease-out;-ms-transition:top .5s ease-out,opacity .3s ease-out;-o-transition:top .5s ease-out,opacity .3s ease-out;transition:top .5s ease-out,opacity .3s ease-out}.ctpt-widget__delivery-type.mapmode{background:transparent;height:auto;top:85px;-webkit-transition:top .5s ease-out,opacity .3s ease-out;-moz-transition:top .5s ease-out,opacity .3s ease-out;-ms-transition:top .5s ease-out,opacity .3s ease-out;-o-transition:top .5s ease-out,opacity .3s ease-out;transition:top .5s ease-out,opacity .3s ease-out}.ctpt-widget__delivery-type.ctpt-widget__delivery-type_only_pvz{display:none !important}.ctpt-widget__delivery-type.mapmode .ctpt-widget__delivery-filters-list,.ctpt-widget__delivery-type.mapmode .ctpt-widget__delivery-type__options-content{display:none}.ctpt-widget__delivery-type__options-content{height:calc(100% - 170px);overflow:auto;}.ctpt-widget__delivery-type__options-content.warnmode{height:calc(100% - 215px) !important}.ctpt-widget__delivery-type__options{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex}.ctpt-widget__delivery-type__item{padding:12px 16px;width:50%;cursor:pointer;font-size:14px;color:#333;position:relative;font-family:Roboto,sans-serif;font-weight:500;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s;-webkit-border-radius:4px 4px 0 0;-moz-border-radius:4px 4px 0 0;-ms-border-radius:4px 4px 0 0;border-radius:4px 4px 0 0}.ctpt-widget__delivery-type__item:after{content:"";position:absolute;bottom:0;left:0;right:0;height:2px;background:0 0}.ctpt-widget__delivery-type__item.current,.ctpt-widget__delivery-type__item:hover{color:#007dd5;background:rgba(0,125,213,.1)}.ctpt-widget__delivery-type__item.current:after,.ctpt-widget__delivery-type__item:hover:after{background:#007dd5}.ctpt-widget__delivery-type__item.current .ctpt-widget__price,.ctpt-widget__delivery-type__item:hover .ctpt-widget__price{color:#007dd5}.ctpt-widget__delivery-type__item-details span{display:block}.ctpt-widget__delivery-type__list-item{padding:12px 13px;height:80px;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;background:#fff}.ctpt-widget__delivery-type__list-item:hover{background:rgba(0,125,213,.05)}.ctpt-widget__delivery-type__info{width:86px;margin:0 auto}.ctpt-widget__delivery-type__info-date,.ctpt-widget__delivery-type__info-days,.ctpt-widget__delivery-type__info-term,.ctpt-widget__delivery-type__info-term-info,.ctpt-widget__delivery-type__info-type{display:block;font-size:16px;line-height:1.17;color:#333;font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget__delivery-type__info-date span,.ctpt-widget__delivery-type__info-days span,.ctpt-widget__delivery-type__info-term span,.ctpt-widget__delivery-type__info-term-info span,.ctpt-widget__delivery-type__info-type span{font-family:Roboto,sans-serif;font-weight:500}.ctpt-widget__delivery-type__info-days{margin-bottom:4px}.ctpt-widget__delivery-type__info-term{font-size:13px;line-height:1.17;color:#7d7d7e;margin-bottom:4px}.ctpt-widget__delivery-type__info-term-info{font-size:13px;line-height:1.17;color:#ff8a01;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center}.ctpt-widget__delivery-type__info-term-info svg{margin-right:4px}.ctpt-widget__delivery-type__logo{width:90px;flex-shrink:0;margin-right:16px;position:relative;padding-right:20px}.ctpt-widget__delivery-type__logo img{width:auto;height:auto;max-width:100%;max-height:100%}.ctpt-widget__delivery-type__price-wrap{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column;-webkit-box-align:center;-ms-flex-align:center;align-items:center}.ctpt-widget__delivery-type__price{font-family:Roboto,sans-serif;font-weight:500;font-size:20px;line-height:1.15;color:#333}.ctpt-widget__delivery-type__button{width:70px;height:28px;color:#fff;background:#007dd5;border:1px solid #007dd5;cursor:pointer;font-size:13px;font-family:Roboto,sans-serif;font-weight:500;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;align-items:center;justify-content:center;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s}.ctpt-widget__delivery-filters-list{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;padding:11px 13px}.ctpt-widget__delivery-filter{width:33.3333%;height:38px;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;align-items:center;justify-content:center;color:#2e3a4c;background:#fff;border:1px solid #d5dbe5;font-size:14px;line-height:1;font-family:Roboto,sans-serif;font-weight:400;cursor:pointer;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s}.ctpt-widget__delivery-filter.current,.ctpt-widget__delivery-filter:hover{color:#fff;background:#97a3b6}.ctpt-widget__delivery-filter:first-child.current,.ctpt-widget__delivery-filter:first-child:hover{-webkit-border-radius:4px 0 0 4px;-moz-border-radius:4px 0 0 4px;-ms-border-radius:4px 0 0 4px;border-radius:4px 0 0 4px}.ctpt-widget__delivery-filter:last-child.current,.ctpt-widget__delivery-filter:last-child:hover{-webkit-border-radius:0 4px 4px 0;-moz-border-radius:0 4px 4px 0;-ms-border-radius:0 4px 4px 0;border-radius:0 4px 4px 0}.ctpt-widget__delivery-button{line-height: 0.8;color:#333;background:#fff;height:36px;padding:0 12px;cursor:pointer;border:none;position:relative;z-index:2;font-family:Roboto,sans-serif;font-weight:500;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s;-webkit-box-shadow:0 2px 2px rgba(0,0,0,.15);-moz-box-shadow:0 2px 2px rgba(0,0,0,.15);box-shadow:0 2px 2px rgba(0,0,0,.15)}.ctpt-widget__delivery-button svg{margin-right:10px;fill:#a2abbb}.ctpt-widget__delivery-button.current,.ctpt-widget__delivery-button:hover{color:#007dd5;background:#e6f1f9;-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none}.ctpt-widget__delivery-button.current svg,.ctpt-widget__delivery-button:hover svg{fill:#007dd5}.ctpt-widget__delivery-button-wrap{display:none !important}.ctpt-widget__panel{width:460px;height:100%;overflow:hidden;position:absolute;top:0;right:60px;z-index:9;opacity:0;background:rgba(255,255,255,.95);-webkit-transform-origin:right;-ms-transform-origin:right;transform-origin:right;-moz-transform:rotateY(-101deg);-o-transform:rotateY(-101deg);-ms-transform:rotateY(-101deg);-webkit-transform:rotateY(-101deg);transform:rotateY(-101deg);-webkit-transition:transform .3s linear,opacity .5s ease-in;-moz-transition:transform .3s linear,opacity .5s ease-in;-ms-transition:transform .3s linear,opacity .5s ease-in;-o-transition:transform .3s linear,opacity .5s ease-in;transition:transform .3s linear,opacity .5s ease-in}.ctpt-widget__panel.open{opacity:1;-moz-transform:rotateY(0);-o-transform:rotateY(0);-ms-transform:rotateY(0);-webkit-transform:rotateY(0);transform:rotateY(0)}.ctpt-widget__panel>div{height:100%;width:460px;position:absolute;top:0;right:-460px}.ctpt-widget__panel .ctpt-widget__primary-title{border-bottom:1px solid rgba(0,0,0,.1)}.ctpt-widget__panel-list{left:0;-webkit-transition:left ease .5s;-moz-transition:left ease .5s;-ms-transition:left ease .5s;-o-transition:left ease .5s;transition:left ease .5s}.ctpt-widget__panel-list.current{left:-460px}.ctpt-widget__panel-list-points{height:100%}.ctpt-widget__panel-list-delivery .ctpt-widget__panel-content_list-item{height:78px;padding:16px 18px;cursor:pointer}.ctpt-widget__panel-list-delivery .ctpt-widget__panel-content__status{width:20px;height:20px;margin-right:8px;position:relative;flex-shrink:0}.ctpt-widget__panel-list-delivery .ctpt-widget__panel-content__status-icon{position:absolute;top:0;left:0;right:0;bottom:0;width:20px;height:20px}.ctpt-widget__panel-list-delivery .ctpt-widget__panel-content__status-icon:before{content:url(\'data:image/svg+xml; utf8, <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"> <path d="M17.7778 0H2.22224C0.994427 0 0 0.994427 0 2.22224V17.7778C0 19.0056 0.994427 20 2.22224 20H17.7778C19.0056 20 20 19.0056 20 17.7778V2.22224C20 0.994427 19.0056 0 17.7778 0ZM7.77776 15.5556L2.22224 10L3.79448 8.42776L7.77781 12.4111L16.2056 3.98333L17.7778 5.55557L7.77776 15.5556Z" fill="%2301BD6C"/> </svg>\');width:100%}.ctpt-widget__panel-content{height:calc(100% - 75px);overflow:auto}.ctpt-widget__panel-content_list-item{height:74px;padding:16px 10px;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;cursor:pointer;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s}.ctpt-widget__panel-content_list-item:hover{background:rgba(0,115,188,.05)}.ctpt-widget__panel-content__info{flex-grow:1}.ctpt-widget__panel-content__info-address,.ctpt-widget__panel-content__info-amount,.ctpt-widget__panel-content__info-delivery,.ctpt-widget__panel-content__info-price{display:block;font-size:16px;line-height:1.17;color:#333;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:238px}.ctpt-widget__panel-content__info-address,.ctpt-widget__panel-content__info-amount{font-family:Roboto,sans-serif;font-weight:500;}.ctpt-widget__panel-content__info-delivery{font-family:Roboto,sans-serif;font-weight:400;color:#7d7d7e;margin-top:4px;font-size:13px}.ctpt-widget__panel-content__info-delivery span{color:#7d7d7e}.ctpt-widget__panel-content__price{font-size:20px;line-height:1;color:#007dd5;font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget__panel-content__logo{width:60px;flex-shrink:0;margin:0 16px 4px 0;text-align:center}.ctpt-widget__panel-content__logo img{width:auto;height:auto;max-width:100%;max-height:100%;vertical-align:top}.ctpt-widget__select{position:relative;display:block;min-width:200px;margin-right:16px;flex-grow:1}.ctpt-widget__select__head{width:100%;max-width:100%;padding:9px 12px 8px;font-size:16px;line-height:1.19;color:#333;cursor:pointer;border:2px solid #ddd;-webkit-border-radius:4px;-moz-border-radius:4px;-ms-border-radius:4px;border-radius:4px;font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget__select__head svg{width:7px;position:absolute;right:14px;bottom:50%;-webkit-transition:.2s ease-in;-moz-transition:.2s ease-in;-ms-transition:.2s ease-in;-o-transition:.2s ease-in;transition:.2s ease-in;-moz-transform:translateY(50%) rotate(-90deg);-o-transform:translateY(50%) rotate(-90deg);-ms-transform:translateY(50%) rotate(-90deg);-webkit-transform:translateY(50%) rotate(-90deg);transform:translateY(50%) rotate(-90deg)}.ctpt-widget__select__head svg .path{fill:#007dd5}.ctpt-widget__select__head.open{-webkit-border-radius:4px 4px 0 0;-moz-border-radius:4px 4px 0 0;-ms-border-radius:4px 4px 0 0;border-radius:4px 4px 0 0}.ctpt-widget__select__head.open svg{transform:translateY(50%) rotate(90deg)}.ctpt-widget__select__list{display:none;position:absolute;top:100%;left:0;right:0;background:#fff;overflow-x:hidden;overflow-y:auto;z-index:100;margin:-2px 0 0;padding:0;font-size:16px;line-height:1.19;color:#333;-webkit-border-radius:0 0 4px 4px;-moz-border-radius:0 0 4px 4px;-ms-border-radius:0 0 4px 4px;border-radius:0 0 4px 4px;-webkit-box-shadow:0 2px 8px rgba(0,0,0,.15);-moz-box-shadow:0 2px 8px rgba(0,0,0,.15);box-shadow:0 2px 8px rgba(0,0,0,.15)}.ctpt-widget__select__item{position:relative;border-top:1px solid rgba(224,229,231,.5);padding:10px 15px;cursor:pointer;list-style-type:none;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s}.ctpt-widget__select__item:hover{background-color:#e6f1f9}.ctpt-widget__select__item__price{color:#007dd5;font-size:20px;line-height:1;font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget__select__item__plane{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column}.ctpt-widget__select__item__name{font-family:Roboto,sans-serif;font-weight:500;margin-bottom:4px}.ctpt-widget__select__item__info-delivery{font-size:13px;line-height:1;font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget__select__item__info-delivery span{color:#7d7d7e}.ctpt-widget__panel-details{-webkit-transition:right ease .5s;transition:right ease .5s}.ctpt-widget__panel-details.current{right:0}.ctpt-widget__panel-details__list{height:calc(100% - 60px);overflow:auto;padding-top:12px}.ctpt-widget__panel-details .ctpt-widget__primary-title{padding:8px 32px;height:60px;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center}.ctpt-widget__panel-details__back{width:32px;height:60px;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;position:absolute;top:0;left:0;cursor:pointer;z-index:4}.ctpt-widget__panel-details__back svg{width:12px;height:25px;margin:auto}.ctpt-widget__panel-details__back svg .path{fill:#97a3b6;-webkit-transition:.2s;-moz-transition:.2s;-ms-transition:.2s;-o-transition:.2s;transition:.2s}.ctpt-widget__panel-details__back:hover svg .path{fill:#333}.ctpt-widget__panel-details__item{padding:0 16px}.ctpt-widget__panel-details__item-header{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center}.ctpt-widget__panel-details__logo{width:60px;height:24px;flex-shrink:0;margin-right:16px;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center}.ctpt-widget__panel-details__logo img{width:auto;height:auto;max-width:100%;max-height:100%;flex-shrink:0}.ctpt-widget__panel-details__info{flex-grow:1}.ctpt-widget__panel-details__info-amount,.ctpt-widget__panel-details__info-delivery{display:block;font-size:16px;line-height:1.17;color:#333}.ctpt-widget__panel-details__info-amount{font-family:Roboto,sans-serif;font-weight:500}.ctpt-widget__panel-details__info-delivery{font-family:Roboto,sans-serif;font-weight:400;color:#000}.ctpt-widget__panel-details__info-delivery span{color:#7d7d7e;margin-left:12px}.ctpt-widget__panel-details__price-wrap{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-ms-flex-wrap:wrap;flex-wrap:wrap;-webkit-box-align:center;-ms-flex-align:center;align-items:center}.ctpt-widget__panel-details__address-wrap,.ctpt-widget__panel-details__description-wrap,.ctpt-widget__panel-details__phones-wrap,.ctpt-widget__panel-details__price-wrap,.ctpt-widget__panel-details__working-hours-wrap{margin-bottom:16px}.ctpt-widget__panel-details__price{font-family:Roboto,sans-serif;font-weight:500;color:#007dd5;font-size:20px;line-height:1.15;margin-right:16px}.ctpt-widget__panel-details__title{width:100%;color:#7d7d7e;font-size:14px;line-height:1.17;margin-bottom:4px;font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget__panel-details__info-wrap{color:#000;font-size:16px;line-height:1.17;font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget__panel-details__info-wrap span{display:block}.ctpt-widget__panel-details__phones-wrap .ctpt-widget__panel-details__info-wrap{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column}.ctpt-widget__panel-details__phones-wrap .ctpt-widget__panel-details__info-phone{color:#333;text-decoration:none;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s}.ctpt-widget__panel-details__phones-wrap .ctpt-widget__panel-details__info-phone:hover{color:#000}.ctpt-widget__sidebar{width:60px;height:100%;position:absolute;top:0;right:-60px;padding:0;z-index:11;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column;background:rgba(255,255,255,.95);border-left:1px solid rgba(0,0,0,.1);-moz-transform:translateX(0);-o-transform:translateX(0);-ms-transform:translateX(0);-webkit-transform:translateX(0);transform:translateX(0);-webkit-transition:right .5s ease-out,opacity .3s ease-out;-moz-transition:right .5s ease-out,opacity .3s ease-out;-ms-transition:right .5s ease-out,opacity .3s ease-out;-o-transition:right .5s ease-out,opacity .3s ease-out;transition:right .5s ease-out,opacity .3s ease-out}.ctpt-widget__sidebar.current{right:0}.ctpt-widget__sidebar-logo-wrap{width:60px;height:60px;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;align-items:center;justify-content:center}.ctpt-widget__sidebar-logo-wrap svg{width:40px;height:34px}.ctpt-widget__modal{visibility:hidden;position:fixed;top:0;left:0;bottom:0;right:0;z-index:15}.ctpt-widget__modal.is-visible{visibility:visible}.ctpt-widget__modal-overlay{position:fixed;z-index:5;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);visibility:hidden;opacity:0;transition:visibility 0s linear .3s,opacity .3s}.ctpt-widget__modal.is-visible .ctpt-widget__modal-overlay{opacity:1;visibility:visible;transition-delay:0s}.ctpt-widget__modal-wrapper{position:absolute;z-index:9;top:33%;left:50%;width:100%;max-width:440px;background-color:#fff;-moz-transform:translate(-50%,-50%);-o-transform:translate(-50%,-50%);-ms-transform:translate(-50%,-50%);-webkit-transform:translate(-50%,-50%);transform:translate(-50%,-50%);-webkit-box-shadow:inset 0 -1px 0 #d4d8dd;-moz-box-shadow:inset 0 -1px 0 #d4d8dd;box-shadow:inset 0 -1px 0 #d4d8dd}.ctpt-widget__modal-transition{transition:all .3s .12s;opacity:0}.ctpt-widget__modal.is-visible .ctpt-widget__modal-transition{opacity:1}.ctpt-widget__modal-content .ctpt-widget__button{width:110px;height:35px;font-size:18px}.ctpt-widget__modal-content-wrap{width:100%;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;position:relative;border-bottom:1px solid #007dd5;overflow:hidden}.ctpt-widget__modal-header{position:relative}.ctpt-widget__modal-heading{font-size:1.125em;margin:0;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.ctpt-widget__modal-date{height:56px;min-width:65px;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;padding:5px 10px;position:relative;flex-shrink:0;cursor:pointer}.ctpt-widget__modal-date:before{content:"";position:absolute;bottom:0;left:0;right:0;height:3px;background:0 0}.ctpt-widget__modal-date.disabled{background:#eff2f5;cursor:auto;-webkit-border-radius:4px 4px 0 0;-moz-border-radius:4px 4px 0 0;-ms-border-radius:4px 4px 0 0;border-radius:4px 4px 0 0}.ctpt-widget__modal-date.disabled .ctpt-widget__modal-date-title,.ctpt-widget__modal-date.disabled .ctpt-widget__modal-day-title{opacity:.5}.ctpt-widget__modal-date-title,.ctpt-widget__modal-day-title{color:#333;font-size:14px;line-height:1.14}.ctpt-widget__modal-day-title{font-family:Roboto,sans-serif;font-weight:500;margin-bottom:4px}.ctpt-widget__modal-date-title{font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget__modal-timetable-wrap{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column}.ctpt-widget__modal-body{padding:0 20px 24px}.ctpt-widget__modal-content{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column}.ctpt-widget__modal-item{padding:8px 16px;height:57px;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;border-bottom:1px solid #d6d9db}.ctpt-widget__modal-time{color:#333;font-size:16px;line-height:1.19;font-family:Roboto,sans-serif;font-weight:400}.ctpt-widget .swiper-slide-active{background:rgba(0,115,188,.1);-webkit-border-radius:4px 4px 0 0;-moz-border-radius:4px 4px 0 0;-ms-border-radius:4px 4px 0 0;border-radius:4px 4px 0 0}.ctpt-widget .swiper-slide-active:before{background:#007dd5}.ctpt-widget .swiper-slide-active .ctpt-widget__modal-date-title,.ctpt-widget .swiper-slide-active .ctpt-widget__modal-day-title{color:#007dd5}.ctpt-widget .swiper-button{position:absolute;top:0;bottom:0;width:66px;height:54px;z-index:10;margin:0;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;cursor:pointer}.ctpt-widget .swiper-button img{width:13px;height:20px}.ctpt-widget .swiper-button:after{display:none}.ctpt-widget .swiper-button.swiper-button-disabled{display:none}.ctpt-widget .swiper-button-prev{padding-left:10px;left:0;display:none;background:-moz-linear-gradient(left,#fff 0,rgba(255,255,255,.39) 100%);background:-webkit-linear-gradient(left,#fff 0,rgba(255,255,255,.39) 100%);background:linear-gradient(to right,#fff 0,rgba(255,255,255,.39) 100%);-webkit-box-pack:start;-ms-flex-pack:start;justify-content:flex-start}.ctpt-widget .swiper-button-prev img{-moz-transform:scaleX(-1);-o-transform:scaleX(-1);-ms-transform:scaleX(-1);-webkit-transform:scaleX(-1);transform:scaleX(-1)}.ctpt-widget .swiper-button-next{right:0;padding-right:10px;background:linear-gradient(270deg,#fff 39.06%,rgba(255,255,255,0) 100%);-webkit-box-pack:end;-ms-flex-pack:end;justify-content:flex-end} .ctpt-widget{font-family:Roboto,sans-serif;font-weight:400;font-size:16px;color:#333}.ctpt-widget *{-webkit-box-sizing:border-box;box-sizing:border-box}.ctpt-widget *:after,.ctpt-widget *:before{-webkit-box-sizing:border-box;box-sizing:border-box}.ctpt-widget button,.ctpt-widget img,.ctpt-widget input,.ctpt-widget textarea{outline:0;-webkit-appearance:none;-moz-appearance:none}.ctpt-widget{min-height: 210px;max-width:960px;height:600px;width:100%;position:relative;overflow:hidden;background:#e3e4e5;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;margin:0 auto;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;border:1px solid rgba(0,0,0,.1)}.ctpt-widget__map{z-index:2;position:relative}.ctpt-widget__search-wrap{max-width:460px;width:100%;position:absolute;top:50%;left:50%;z-index:8;-moz-transform:translate(-50%,-50%);-o-transform:translate(-50%,-50%);-ms-transform:translate(-50%,-50%);-webkit-transform:translate(-50%,-50%);transform:translate(-50%,-50%);-webkit-transition:top .5s ease-out,opacity .3s ease-out;-moz-transition:top .5s ease-out,opacity .3s ease-out;-ms-transition:top .5s ease-out,opacity .3s ease-out;-o-transition:top .5s ease-out,opacity .3s ease-out;transition:top .5s ease-out,opacity .3s ease-out}.ctpt-widget__search-wrap.current,.ctpt-widget__search-wrap.mapmode{top:15px;-moz-transform:translate(-50%,0);-o-transform:translate(-50%,0);-ms-transform:translate(-50%,0);-webkit-transform:translate(-50%,0);transform:translate(-50%,0);-webkit-transition:top .5s ease-out,opacity .3s ease-out;-moz-transition:top .5s ease-out,opacity .3s ease-out;-ms-transition:top .5s ease-out,opacity .3s ease-out;-o-transition:top .5s ease-out,opacity .3s ease-out;transition:top .5s ease-out,opacity .3s ease-out}.ctpt-widget__search-title{font-family:Roboto,sans-serif;font-weight:400;font-size:18px;color:#333;line-height:1.17;margin-bottom:12px}.ctpt-widget__search-form{margin-bottom:8px}.ctpt-widget__search-form form{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;position:relative;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between}.ctpt-widget__search-logo{position:absolute;top:50%;left:16px;width:28px;z-index:4;-moz-transform:translate(0,-50%);-o-transform:translate(0,-50%);-ms-transform:translate(0,-50%);-webkit-transform:translate(0,-50%);transform:translate(0,-50%)}.ctpt-widget__search-form input.ctpt-widget__search-input{width:99%;margin-right:8px;height:60px;background:#fff;border:1px solid #f1f1ec;padding: 15px 24px 10px 60px;font-family:Roboto,sans-serif;font-weight:400;font-size:20px;color:#333;-webkit-border-radius:4px;-moz-border-radius:4px;-ms-border-radius:4px;border-radius:4px;-webkit-box-shadow:0 2px 2px rgba(0,0,0,.15);-moz-box-shadow:0 2px 2px rgba(0,0,0,.15);box-shadow:0 2px 2px rgba(0,0,0,.15)}.ctpt-widget__search-input.placeholder{opacity:.5}.ctpt-widget__search-input:-moz-placeholder{opacity:.5}.ctpt-widget__search-input::-moz-placeholder{opacity:.5}.ctpt-widget__search-input::-webkit-input-placeholder{opacity:.5}.ctpt-widget__search-input[type=text]:focus{padding:10px 24px 5px 60px}.ctpt-widget__search-input[type=text]:focus~label{padding:5px 24px 10px 60px;font-size:12px}.ctpt-widget__search-floating{position:relative;width: calc(100% - 117px);}.ctpt-widget__search-floating>label{position:absolute;top:50%;left:0;height:auto;padding:0 0 0 60px;pointer-events:none;border:1px solid transparent;transform-origin:0 0;font-family:Roboto,sans-serif;font-weight:400;font-size:20px;line-height:1em;color:#333;opacity:.5;transition:opacity .1s ease-in-out,transform .1s ease-in-out;-webkit-transform:translateY(-50%);-moz-transform:translateY(-50%);-ms-transform:translateY(-50%);-o-transform:translateY(-50%);transform: translateY(-50%)}.ctpt-widget__search-button{width:117px;height:60px;position:relative;color:#fff;font-family:Roboto,sans-serif;font-weight:400;font-size:18px;line-height:1.17;border:1px solid #01bd6c;background:#01bd6c;cursor:pointer;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;align-items:center;justify-content:center;-webkit-border-radius:4px;-moz-border-radius:4px;-ms-border-radius:4px;border-radius:4px;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s;-webkit-box-shadow: 0 2px 2px rgba(0,0,0,.20);-moz-box-shadow: 0 2px 2px rgba(0,0,0,.20);box-shadow: 0 2px 2px rgba(0,0,0,.20)}.ctpt-widget__search-button.loading{padding-right:27px}.ctpt-widget__search-button .ctpt-widget__load{margin-right: 0;position:absolute;right:12px;top:50%;margin-top:-12px;opacity:0}.ctpt-widget__search-button.loading .ctpt-widget__load{opacity:1}.ctpt-widget__search-button .ctpt-widget__load:after{border-top-color:#fff;border-right-color:#fff;border-bottom-color:#fff;-webkit-transition:0.5s;-moz-transition:0.5s;-ms-transition:0.5s;-o-transition:0.5s;transition:0.5s}.ctpt-widget__delivery-type__item:before{display:block;content:"";position:absolute;left:0;top:0;width:100%;height:100%;background:url(\'data:image/svg+xml; utf8, <svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" stroke-width="1.1" fill="none" stroke="%23007dd5"><line x1="-290" y1="300" x2="10" y2="0"/><line x1="-280" y1="300" x2="20" y2="0"/><line x1="-270" y1="300" x2="30" y2="0"/><line x1="-260" y1="300" x2="40" y2="0"/><line x1="-250" y1="300" x2="50" y2="0"/><line x1="-240" y1="300" x2="60" y2="0"/><line x1="-230" y1="300" x2="70" y2="0"/><line x1="-220" y1="300" x2="80" y2="0"/><line x1="-210" y1="300" x2="90" y2="0"/><line x1="-200" y1="300" x2="100" y2="0"/><line x1="-190" y1="300" x2="110" y2="0"/><line x1="-180" y1="300" x2="120" y2="0"/><line x1="-170" y1="300" x2="130" y2="0"/><line x1="-160" y1="300" x2="140" y2="0"/><line x1="-150" y1="300" x2="150" y2="0"/><line x1="-140" y1="300" x2="160" y2="0"/><line x1="-130" y1="300" x2="170" y2="0"/><line x1="-120" y1="300" x2="180" y2="0"/><line x1="-110" y1="300" x2="190" y2="0"/><line x1="-100" y1="300" x2="200" y2="0"/><line x1="-90" y1="300" x2="210" y2="0"/><line x1="-80" y1="300" x2="220" y2="0"/><line x1="-70" y1="300" x2="230" y2="0"/><line x1="-60" y1="300" x2="240" y2="0"/><line x1="-50" y1="300" x2="250" y2="0"/><line x1="-40" y1="300" x2="260" y2="0"/><line x1="-30" y1="300" x2="270" y2="0"/><line x1="-20" y1="300" x2="280" y2="0"/><line x1="-10" y1="300" x2="290" y2="0"/><line x1="0" y1="300" x2="300" y2="0"/><line x1="10" y1="300" x2="310" y2="0"/><line x1="20" y1="300" x2="320" y2="0"/><line x1="30" y1="300" x2="330" y2="0"/><line x1="40" y1="300" x2="340" y2="0"/><line x1="50" y1="300" x2="350" y2="0"/><line x1="60" y1="300" x2="360" y2="0"/><line x1="70" y1="300" x2="370" y2="0"/><line x1="80" y1="300" x2="380" y2="0"/><line x1="90" y1="300" x2="390" y2="0"/><line x1="100" y1="300" x2="400" y2="0"/><line x1="110" y1="300" x2="410" y2="0"/><line x1="120" y1="300" x2="420" y2="0"/><line x1="130" y1="300" x2="430" y2="0"/><line x1="140" y1="300" x2="440" y2="0"/><line x1="150" y1="300" x2="450" y2="0"/><line x1="160" y1="300" x2="460" y2="0"/><line x1="170" y1="300" x2="470" y2="0"/><line x1="180" y1="300" x2="480" y2="0"/><line x1="190" y1="300" x2="490" y2="0"/><line x1="200" y1="300" x2="500" y2="0"/><line x1="210" y1="300" x2="510" y2="0"/><line x1="220" y1="300" x2="520" y2="0"/><line x1="230" y1="300" x2="530" y2="0"/><line x1="240" y1="300" x2="540" y2="0"/><line x1="250" y1="300" x2="550" y2="0"/><line x1="260" y1="300" x2="560" y2="0"/><line x1="270" y1="300" x2="570" y2="0"/><line x1="280" y1="300" x2="580" y2="0"/><line x1="290" y1="300" x2="590" y2="0"/><line x1="300" y1="300" x2="600" y2="0"/><line x1="310" y1="300" x2="610" y2="0"/><line x1="320" y1="300" x2="620" y2="0"/><line x1="330" y1="300" x2="630" y2="0"/><line x1="340" y1="300" x2="640" y2="0"/><line x1="350" y1="300" x2="650" y2="0"/><line x1="360" y1="300" x2="660" y2="0"/><line x1="370" y1="300" x2="670" y2="0"/><line x1="380" y1="300" x2="680" y2="0"/><line x1="390" y1="300" x2="690" y2="0"/><line x1="400" y1="300" x2="700" y2="0"/><line x1="410" y1="300" x2="710" y2="0"/><line x1="420" y1="300" x2="720" y2="0"/><line x1="430" y1="300" x2="730" y2="0"/><line x1="440" y1="300" x2="740" y2="0"/><line x1="450" y1="300" x2="750" y2="0"/><line x1="460" y1="300" x2="760" y2="0"/><line x1="470" y1="300" x2="770" y2="0"/><line x1="480" y1="300" x2="780" y2="0"/><line x1="490" y1="300" x2="790" y2="0"/><line x1="500" y1="300" x2="800" y2="0"/><line x1="510" y1="300" x2="810" y2="0"/><line x1="520" y1="300" x2="820" y2="0"/><line x1="530" y1="300" x2="830" y2="0"/><line x1="540" y1="300" x2="840" y2="0"/><line x1="550" y1="300" x2="850" y2="0"/><line x1="560" y1="300" x2="860" y2="0"/><line x1="570" y1="300" x2="870" y2="0"/><line x1="580" y1="300" x2="880" y2="0"/><line x1="590" y1="300" x2="890" y2="0"/><line x1="600" y1="300" x2="900" y2="0"/><line x1="610" y1="300" x2="910" y2="0"/><line x1="620" y1="300" x2="920" y2="0"/><line x1="630" y1="300" x2="930" y2="0"/><line x1="640" y1="300" x2="940" y2="0"/><line x1="650" y1="300" x2="950" y2="0"/><line x1="660" y1="300" x2="960" y2="0"/><line x1="670" y1="300" x2="970" y2="0"/><line x1="680" y1="300" x2="980" y2="0"/><line x1="690" y1="300" x2="990" y2="0"/><line x1="700" y1="300" x2="1000" y2="0"/><line x1="710" y1="300" x2="1010" y2="0"/><line x1="720" y1="300" x2="1020" y2="0"/><line x1="730" y1="300" x2="1030" y2="0"/><line x1="740" y1="300" x2="1040" y2="0"/><line x1="750" y1="300" x2="1050" y2="0"/><line x1="760" y1="300" x2="1060" y2="0"/><line x1="770" y1="300" x2="1070" y2="0"/><line x1="780" y1="300" x2="1080" y2="0"/><line x1="790" y1="300" x2="1090" y2="0"/><line x1="800" y1="300" x2="1100" y2="0"/></svg>\') left top no-repeat;opacity:0;-webkit-transition:0.5s;-moz-transition:0.5s;-o-transition:0.5s;-ms-transition:0.5s;transition:0.5s}.ctpt-widget__delivery-type__item.disabled{transition:0.5s;background:#f2f2f2}.ctpt-widget__delivery-type__item.disabled:before{opacity:1}.ctpt-widget__delivery-type__item + .ctpt-widget__delivery-type__item{border-top-left-radius:0;border-bottom-left-radius:0}.ctpt-widget__map_widget{padding-top:195px;padding-right:70px;padding-left:10px;padding-bottom:15px}.ctpt_pvzonly_mode .ctpt-widget__map_widget{padding-top:90px}.ctpt-widget__map_widget>ymaps,.ctpt-widget__map_widget>ymaps>ymaps{width:100% !important;height:100% !important}.ctpt-widget__search-hint{font-family:Roboto,sans-serif;font-weight:400;font-size:13px;line-height:1.17;color:#333;opacity:.5}.ctpt-widget__map-search-wrap{display:none !important}.ctpt-widget__map-search-form form{display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;position:relative}.ctpt-widget__map-search-logo{position:absolute;top:50%;left:16px;width:28px;-moz-transform:translate(0,-50%);-o-transform:translate(0,-50%);-ms-transform:translate(0,-50%);-webkit-transform:translate(0,-50%);transform:translate(0,-50%)}.ctpt-widget__map-search-input{width:calc(100% - 98px);height:36px;background:#fff;border:1px solid #f1f1ec;padding:10px 24px 10px 60px;font-family:Roboto,sans-serif;font-weight:400;font-size:20px;color:#333;-webkit-box-shadow:0 2px 2px rgba(0,0,0,.15);-moz-box-shadow:0 2px 2px rgba(0,0,0,.15);box-shadow:0 2px 2px rgba(0,0,0,.15)}.ctpt-widget__map-search-input.placeholder{opacity:.5}.ctpt-widget__map-search-input:-moz-placeholder{opacity:.5}.ctpt-widget__map-search-input::-moz-placeholder{opacity:.5}.ctpt-widget__map-search-input::-webkit-input-placeholder{opacity:.5}.ctpt-widget__map-search-button{padding: 1px 6px;width:98px;height:36px;color:#fff;font-family:Roboto,sans-serif;font-weight:400;font-size:18px;line-height:1.17;border:1px solid #01bd6c;background:#01bd6c;cursor:pointer;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;-webkit-transition:all .2s;-moz-transition:all .2s;-ms-transition:all .2s;-o-transition:all .2s;transition:all .2s}div.ctpt-widget__delivery-type_only_pvz{height:83px}div.ctpt-widget__delivery-type_only_c .ctpt-widget__delivery-type__item,div.ctpt-widget__delivery-type_only_pvz .ctpt-widget__delivery-type__item{width:100%;padding-left:12%}.ctpl-ymap-suggest-hidden{display:none!important}.ctpt-widget__modal-content{position:relative}.ctp_courier_price_info{width:128px;height:40px;color:#007dd5;font-size:20px;line-height:1;text-transform:uppercase;font-family:Roboto,sans-serif;font-weight:500;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;align-items:center;justify-content:center}.ctpl-suggestion-block{pointer-events:none;border-bottom:solid 1px #e5e5e5;line-height:23px}.ctpl-suggestion-block .ctpl-suggestion-base-name{font-weight:700;font-size:14px;white-space:break-spaces}.ctpl-suggestion-block .ctpl-suggestion-full-name{white-space:break-spaces}.ctpt-widget__delivery-type__info_icon{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}.ctpt-widget__info-hint-text{position:fixed!important}.ctpt-widget__modal-date.current:before{background:#007dd5}.ctpt-widget__modal-date.current{background:rgba(0,115,188,.1);-webkit-border-radius:4px 4px 0 0;-moz-border-radius:4px 4px 0 0;-ms-border-radius:4px 4px 0 0;border-radius:4px 4px 0 0}.ctpt-widget__modal-date{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}.ctpt-widget__modal-date.current .ctpt-widget__modal-date-title{color:#007dd5}.ctpt-widget__modal.is-visible{display:block}.ctpt-widget__modal-date-title{width:65px;text-align:center}div.ctpt-widget__modal-content .ctpt-widget__modal-content-wrap{width:calc(100% - 24px);margin-left:9px}div.ctpt-widget__modal-content .ctp_prev_slide_button{content:url(\'data:image/svg+xml; utf8, <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 10 16" fill="none"><path opacity="0.5" d="M0.12 14.1067L6.22667 8L0.12 1.88L2 0L10 8L2 16L0.12 14.1067Z" fill="%2397A3B6"/></svg>\');-moz-transform:scaleX(-1);-o-transform:scaleX(-1);-ms-transform:scaleX(-1);-webkit-transform:scaleX(-1);transform:scaleX(-1);width:25px!important;height:45px!important;top:5%!important;left:-15px;-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;background:0 0}div.ctpt-widget__modal-content .ctp_next_slide_button{content:url(\'data:image/svg+xml; utf8, <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 10 16" fill="none"><path opacity="0.5" d="M0.12 14.1067L6.22667 8L0.12 1.88L2 0L10 8L2 16L0.12 14.1067Z" fill="%2397A3B6"/></svg>\');width:25px!important;height:45px!important;top:5%!important;right:-15px!important;display:block;position:absolute;-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;background:0 0}.ctpt-widget__modal-body{user-select:none}.ctpt-widget__close span{display:block;width:16px;height:16px;content:url(\'data:image/svg+xml; utf8, <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.22" d="M16 1.61716L14.3828 0L7.99997 6.38287L1.61716 0L0 1.61716L6.38287 7.99997L0 14.3828L1.61716 16L7.99997 9.61713L14.3828 16L16 14.3828L9.61713 7.99997L16 1.61716Z" fill="black"></path></svg>\')}.ctpt-widget__modal-date{max-width:65px}.ctpt-widget__modal-dates-slider{transition:transform .5s;will-change:transform}div.ctpt-widget__select{margin-bottom:5px}span.ctpt-widget__map-search-logo{top:55%}.ctpt-widget__modal{visibility:visible;display:none}.ctpt-widget__modal-overlay{opacity:1;visibility:visible}.ctpt-widget__modal-dates-slider{display:flex;position:relative;left:0}.ctpt-widget__panel-details__back_svg{content:url(\'data:image/svg+xml; utf8, <svg width="13" height="20" viewBox="0 0 13 20" fill="%2397A3B6" xmlns="http://www.w3.org/2000/svg"><path class="path" d="M12.35 17.6333L4.71667 10L12.35 2.35L10 0L0 10L10 20L12.35 17.6333Z"></path></svg>\');width:12px;height:25px;margin:auto}.ctpt-delivery-widget-loader-bar{width:0%;height:2px;background-color:#007dd5;position:absolute;bottom:0;left:0;z-index:10}.ctpt-widget__load-spinner{border-top:solid 2px rgb(0 125 213 / 38%)}.ctpt-widget__delivery-type__info-term-info_clock{content:url(\'data:image/svg+xml; utf8, <svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.98587 3.50805L1.47401 2.88467L3.65863 0.914219L4.08837 1.43774C4.36086 1.27023 4.64269 1.11626 4.93991 0.989808L4.41499 0.3515C4.08837 -0.0460423 3.48459 -0.116965 3.06652 0.19379L0.881897 2.16424C0.463824 2.47499 0.389635 3.04938 0.716721 3.44739L1.50481 4.40765C1.64105 4.09363 1.80436 3.79454 1.98587 3.50805ZM14.1175 2.16424L11.9333 0.19379C11.5148 -0.116965 10.911 -0.0465089 10.5844 0.3515L10.0599 0.989808C10.3571 1.11626 10.639 1.26977 10.9115 1.43774L11.3412 0.914219L13.5258 2.88467L13.0135 3.50805C13.1955 3.79454 13.3583 4.09363 13.4941 4.40765L14.2822 3.44739C14.6102 3.04938 14.536 2.47499 14.1175 2.16424ZM13.4572 13.2343L11.5129 10.9008C12.4932 9.89252 13.0989 8.51839 13.0989 7.00101C13.0989 3.90885 10.5918 1.40182 7.49968 1.40182C4.40752 1.40182 1.90048 3.90885 1.90048 7.00101C1.90048 8.51839 2.5066 9.89252 3.48646 10.9008L1.54214 13.2343L1.5468 13.2385C1.47961 13.3197 1.43388 13.42 1.43388 13.5334C1.43388 13.7914 1.64245 14 1.90048 14C2.04466 14 2.16831 13.9309 2.25417 13.8283L2.25883 13.8325L4.19289 11.5121C5.12002 12.1929 6.26132 12.6002 7.50014 12.6002C8.7385 12.6002 9.88027 12.1929 10.8074 11.5121L12.741 13.8325L12.7457 13.8283C12.8315 13.9309 12.9552 14 13.0993 14C13.3574 14 13.5659 13.7914 13.5659 13.5334C13.5659 13.42 13.5202 13.3197 13.453 13.2385L13.4572 13.2343ZM7.50014 11.6665C4.92311 11.6665 2.83415 9.57757 2.83415 7.00054C2.83415 4.42398 4.84986 2.33455 7.50014 2.33455C10.0921 2.33455 12.1661 4.42398 12.1661 7.00054C12.1661 9.57757 10.0772 11.6665 7.50014 11.6665ZM9.51679 4.34372L7.24585 6.75698L5.94823 5.37818C5.76906 5.1878 5.47883 5.1878 5.29919 5.37818C5.12049 5.56855 5.12049 5.87697 5.29919 6.06734L6.92156 7.79096C7.1012 7.98133 7.39143 7.98133 7.5706 7.79096L10.1658 5.03336C10.345 4.84299 10.345 4.5341 10.1658 4.34326C9.98619 4.15382 9.69596 4.15382 9.51679 4.34372Z" fill="%23FF8A01"></path></svg>\');margin:0 3px 0 0}.ctpt-widget__delivery-rating_star_percent{display:inherit}.ctpt-widget__delivery-rating_star{content:url(\'data:image/svg+xml; utf8, <svg width="12" height="11" viewBox="0 0 12 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 8.67519L9.399 10.725L8.49974 6.85908L11.5 4.25922L7.54496 3.91986L6 0.275024L4.45506 3.91986L0.5 4.25922L3.50026 6.85908L2.601 10.725L6 8.67519Z" fill="%23D0D0D0"></path></svg>\')}.ctpt-widget__delivery-rating_star.current{content:url(\'data:image/svg+xml; utf8, <svg width="12" height="11" viewBox="0 0 12 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 8.67519L9.399 10.725L8.49974 6.85908L11.5 4.25922L7.54496 3.91986L6 0.275024L4.45506 3.91986L0.5 4.25922L3.50026 6.85908L2.601 10.725L6 8.67519Z" fill="%23FF9600"></path></svg>\')!important}.ctpt-widget__delivery-type__info_icon{content:url(\'data:image/svg+xml; utf8, <svg xmlns="http://www.w3.org/2000/svg" width="23.625" height="23.625"><g><title>background</title><rect fill="none" id="canvas_background" height="402" width="582" y="-1" x="-1"/></g><g><title>Layer 1</title><path id="svg_2" fill="%23536177" d="m11.812,0c-6.523,0 -11.812,5.289 -11.812,11.812s5.289,11.813 11.812,11.813s11.813,-5.29 11.813,-11.813s-5.29,-11.812 -11.813,-11.812zm2.459,18.307c-0.608,0.24 -1.092,0.422 -1.455,0.548c-0.362,0.126 -0.783,0.189 -1.262,0.189c-0.736,0 -1.309,-0.18 -1.717,-0.539s-0.611,-0.814 -0.611,-1.367c0,-0.215 0.015,-0.435 0.045,-0.659c0.031,-0.224 0.08,-0.476 0.147,-0.759l0.761,-2.688c0.067,-0.258 0.125,-0.503 0.171,-0.731c0.046,-0.23 0.068,-0.441 0.068,-0.633c0,-0.342 -0.071,-0.582 -0.212,-0.717c-0.143,-0.135 -0.412,-0.201 -0.813,-0.201c-0.196,0 -0.398,0.029 -0.605,0.09c-0.205,0.063 -0.383,0.12 -0.529,0.176l0.201,-0.828c0.498,-0.203 0.975,-0.377 1.43,-0.521c0.455,-0.146 0.885,-0.218 1.29,-0.218c0.731,0 1.295,0.178 1.692,0.53c0.395,0.353 0.594,0.812 0.594,1.376c0,0.117 -0.014,0.323 -0.041,0.617c-0.027,0.295 -0.078,0.564 -0.152,0.811l-0.757,2.68c-0.062,0.215 -0.117,0.461 -0.167,0.736c-0.049,0.275 -0.073,0.485 -0.073,0.626c0,0.356 0.079,0.599 0.239,0.728c0.158,0.129 0.435,0.194 0.827,0.194c0.185,0 0.392,-0.033 0.626,-0.097c0.232,-0.064 0.4,-0.121 0.506,-0.17l-0.203,0.827zm-0.134,-10.878c-0.353,0.328 -0.778,0.492 -1.275,0.492c-0.496,0 -0.924,-0.164 -1.28,-0.492c-0.354,-0.328 -0.533,-0.727 -0.533,-1.193c0,-0.465 0.18,-0.865 0.533,-1.196c0.356,-0.332 0.784,-0.497 1.28,-0.497c0.497,0 0.923,0.165 1.275,0.497c0.353,0.331 0.53,0.731 0.53,1.196c0,0.467 -0.177,0.865 -0.53,1.193z"/></g></svg>\');width:16px;height:16px}.ctpt-widget__delivery-type__info_close_svg{content:url(\'data:image/svg+xml; utf8, <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.22" d="M16 1.61716L14.3828 0L7.99997 6.38287L1.61716 0L0 1.61716L6.38287 7.99997L0 14.3828L1.61716 16L7.99997 9.61713L14.3828 16L16 14.3828L9.61713 7.99997L16 1.61716Z" fill="black"></path></svg>\')}.ctpt-widget__search-wrap.current{z-index:10}.ctpt-widget__delivery-type__info___default_logo{content:url(\'data:image/svg+xml; utf8, <svg xmlns="http://www.w3.org/2000/svg" width="34" height="24" viewBox="0 0 34 24" fill="none"><path d="M28.9999 5.99996H24.4999V0H3.50002C1.84247 0 0.5 1.34247 0.5 3.00002V19.5H3.50002C3.50002 21.9825 5.51749 24 8.00001 24C10.4825 24 12.5 21.9825 12.5 19.5H21.5C21.5 21.9825 23.5175 24 26 24C28.4825 24 30.5 21.9825 30.5 19.5H33.5V12L28.9999 5.99996ZM8.00001 21.7499C6.75499 21.7499 5.75001 20.745 5.75001 19.4999C5.75001 18.2549 6.75499 17.2499 8.00001 17.2499C9.24503 17.2499 10.25 18.2549 10.25 19.4999C10.25 20.745 9.24496 21.7499 8.00001 21.7499ZM26 21.7499C24.755 21.7499 23.75 20.745 23.75 19.4999C23.75 18.2549 24.755 17.2499 26 17.2499C27.245 17.2499 28.25 18.2549 28.25 19.4999C28.25 20.745 27.2449 21.7499 26 21.7499ZM24.4999 12V8.25003H28.2499L31.1974 12H24.4999Z" fill="%23B4BAC6"/></svg>\')!important;width:100%;height:28px;max-width:100%;max-height:100%;vertical-align:middle}.ctpt-widget_delivery-widget-ymap-placemark-selected{position: absolute; top: -40px;left: -20px; content:url(\'data:image/svg+xml; utf8, <svg width="40" height="43" viewBox="0 0 40 43" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(%23clip0)"><g filter="url(%23filter0_f)"><path d="M20 39.9999L7.27201 29.1628C4.75468 27.0195 3.04037 24.2886 2.34586 21.3157C1.65134 18.3428 2.00781 15.2613 3.3702 12.4608C4.73258 9.6604 7.03968 7.26683 9.99976 5.58281C12.9598 3.89878 16.4399 2.99994 20 2.99994C23.5601 2.99994 27.0402 3.89878 30.0002 5.58281C32.9603 7.26683 35.2674 9.6604 36.6298 12.4608C37.9922 15.2613 38.3487 18.3428 37.6541 21.3157C36.9596 24.2886 35.2453 27.0195 32.728 29.1628L20 39.9999Z" fill="url(%23paint0_linear)"></path></g><g filter="url(%23filter1_f)"><path d="M20 39.9999L8.68623 29.1628C6.44861 27.0195 4.92478 24.2886 4.30743 21.3157C3.69008 18.3428 4.00695 15.2613 5.21795 12.4608C6.42896 9.6604 8.47972 7.26683 11.1109 5.58281C13.7421 3.89878 16.8355 2.99994 20 2.99994C23.1645 2.99994 26.2579 3.89878 28.8891 5.58281C31.5203 7.26683 33.571 9.6604 34.782 12.4608C35.9931 15.2613 36.3099 18.3428 35.6926 21.3157C35.0752 24.2886 33.5514 27.0195 31.3138 29.1628L20 39.9999Z" fill="url(%23paint1_linear)"></path></g><path d="M19.3514 38.6305L20 39.2892L20.6486 38.6305L30.5482 28.5776L29.8996 27.9389L30.5482 28.5776C32.6323 26.4612 34.0501 23.7663 34.6244 20.8346C35.1986 17.9029 34.904 14.8641 33.7772 12.1017C32.6504 9.33916 30.7412 6.97574 28.2891 5.31189C25.8368 3.64792 22.9521 2.75889 20 2.75889C17.0479 2.75889 14.1632 3.64792 11.7109 5.31189C9.25875 6.97574 7.34963 9.33916 6.2228 12.1017C5.09602 14.8641 4.80141 17.9029 5.37564 20.8346C5.94989 23.7663 7.36766 26.4612 9.45181 28.5776L10.1004 27.9389L9.45182 28.5776L19.3514 38.6305Z" fill="%2300000082" stroke="white" stroke-width="1.82067"></path><circle cx="20" cy="18" r="12" fill="white"></circle></g><defs><filter id="filter0_f" x="-1.64134" y="-0.641397" width="43.2827" height="44.2827" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood><feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend><feGaussianBlur stdDeviation="1.82067" result="effect1_foregroundBlur"></feGaussianBlur></filter><filter id="filter1_f" x="0.358664" y="-0.641397" width="39.2827" height="44.2827" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood><feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend><feGaussianBlur stdDeviation="1.82067" result="effect1_foregroundBlur"></feGaussianBlur></filter><linearGradient id="paint0_linear" x1="25.1257" y1="2.99994" x2="25.1257" y2="39.9999" gradientUnits="userSpaceOnUse"><stop offset="0.354167" stop-opacity="0"></stop><stop offset="1"></stop></linearGradient><linearGradient id="paint1_linear" x1="24.5562" y1="2.99994" x2="24.5562" y2="39.9999" gradientUnits="userSpaceOnUse"><stop offset="0.354167" stop-opacity="0"></stop><stop offset="1"></stop></linearGradient><clipPath id="clip0"><rect width="40" height="43" fill="white"></rect></clipPath></defs></svg>\')!important}.ctpt-widget_delivery-widget-ymap-placemark{position:absolute;top: -40px;left: -20px;content:url(\'data:image/svg+xml; utf8, <svg width="40" height="43" viewBox="0 0 40 43" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(%23clip0)"><g filter="url(%23filter0_f)"><path d="M20 39.9999L7.27201 29.1628C4.75468 27.0195 3.04037 24.2886 2.34586 21.3157C1.65134 18.3428 2.00781 15.2613 3.3702 12.4608C4.73258 9.6604 7.03968 7.26683 9.99976 5.58281C12.9598 3.89878 16.4399 2.99994 20 2.99994C23.5601 2.99994 27.0402 3.89878 30.0002 5.58281C32.9603 7.26683 35.2674 9.6604 36.6298 12.4608C37.9922 15.2613 38.3487 18.3428 37.6541 21.3157C36.9596 24.2886 35.2453 27.0195 32.728 29.1628L20 39.9999Z" fill="url(%23paint0_linear)"></path></g><g filter="url(%23filter1_f)"><path d="M20 39.9999L8.68623 29.1628C6.44861 27.0195 4.92478 24.2886 4.30743 21.3157C3.69008 18.3428 4.00695 15.2613 5.21795 12.4608C6.42896 9.6604 8.47972 7.26683 11.1109 5.58281C13.7421 3.89878 16.8355 2.99994 20 2.99994C23.1645 2.99994 26.2579 3.89878 28.8891 5.58281C31.5203 7.26683 33.571 9.6604 34.782 12.4608C35.9931 15.2613 36.3099 18.3428 35.6926 21.3157C35.0752 24.2886 33.5514 27.0195 31.3138 29.1628L20 39.9999Z" fill="url(%23paint1_linear)"></path></g><path d="M19.3514 38.6305L20 39.2892L20.6486 38.6305L30.5482 28.5776L29.8996 27.9389L30.5482 28.5776C32.6323 26.4612 34.0501 23.7663 34.6244 20.8346C35.1986 17.9029 34.904 14.8641 33.7772 12.1017C32.6504 9.33916 30.7412 6.97574 28.2891 5.31189C25.8368 3.64792 22.9521 2.75889 20 2.75889C17.0479 2.75889 14.1632 3.64792 11.7109 5.31189C9.25875 6.97574 7.34963 9.33916 6.2228 12.1017C5.09602 14.8641 4.80141 17.9029 5.37564 20.8346C5.94989 23.7663 7.36766 26.4612 9.45181 28.5776L10.1004 27.9389L9.45182 28.5776L19.3514 38.6305Z" fill="%23599CB9" stroke="white" stroke-width="1.82067"></path><circle cx="20" cy="18" r="12" fill="white"></circle></g><defs><filter id="filter0_f" x="-1.64134" y="-0.641397" width="43.2827" height="44.2827" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood><feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend><feGaussianBlur stdDeviation="1.82067" result="effect1_foregroundBlur"></feGaussianBlur></filter><filter id="filter1_f" x="0.358664" y="-0.641397" width="39.2827" height="44.2827" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood><feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend><feGaussianBlur stdDeviation="1.82067" result="effect1_foregroundBlur"></feGaussianBlur></filter><linearGradient id="paint0_linear" x1="25.1257" y1="2.99994" x2="25.1257" y2="39.9999" gradientUnits="userSpaceOnUse"><stop offset="0.354167" stop-opacity="0"></stop><stop offset="1"></stop></linearGradient><linearGradient id="paint1_linear" x1="24.5562" y1="2.99994" x2="24.5562" y2="39.9999" gradientUnits="userSpaceOnUse"><stop offset="0.354167" stop-opacity="0"></stop><stop offset="1"></stop></linearGradient><clipPath id="clip0"><rect width="40" height="43" fill="white"></rect></clipPath></defs></svg>\')!important}.ctpl-sidebar-terminal {display: none}.ctpl-sidebar-pvz{display: none}.ctpt-widget .ctpt-widget__delivery-rating {padding-top: 5px;}.ctpt-widget__select__item .ctpt-widget__select__item__price {min-width: max-content;}.ctpt-widget__panel-details.current>div{height: inherit;}.ctpt-widget__select__head>span{max-width: calc(100% - 12px);display: inline-block;}.ctpt-widget__icon{display:none}.ctpt-widget__delivery-addresswarntext{width:100%;padding:0 13px;margin-bottom:10px}.ctpt-widget__delivery-addresswarntext.hide,.ctpt-widget__delivery-addresswarntext.mapmode{display:none !important}.ctpt-widget__delivery-addresswarntext > div{border:1px solid #d5dbe5;width:100%;min-height:38px;display:flex;align-items:center;padding:5px 10px;background:#fff}.ctpt-widget__delivery-addresswarntext_text{padding:0;margin:0;font-size:13px} @media screen and (max-width: 352px){.ctpt-widget__panel-details .ctpt-widget__primary-title span{width:140px}}.delivery_widget_svg_icon_cash.dis .p{fill:#97a3b6 !important}.delivery_widget_svg_icon_card.dis .p{fill:#97a3b6 !important} @media screen and (max-width: 382px){.ctpt-widget__panel-details .ctpt-widget__primary-title span{width:171px}} @media screen and (max-width: 390px){.ctpt-widget .ctpt-widget__delivery-type__info .ctpt-widget__delivery-type__info-days{font-size: 12px}.ctpt-widget .ctpt-widget__delivery-type__info .ctpt-widget__delivery-type__info-term-info{font-size:9px;line-height:10px}.ctpt-widget .ctpt-widget__delivery-type__logo{width:65px;margin-right:5px}.ctpt-widget .ctpt-widget__delivery-type__price-wrap{width:70px} .ctpt-widget__delivery-type__price-wrap .ctpt-widget__button{font-size:15px;width:70px}.ctpt-widget .ctpt-widget__delivery-rating span{width: 10px}.ctpt-widget .ctpt-widget__search-floating label{font-size: 9px!important}.ctpt-widget .ctpt-widget__delivery-filters-list div{font-size:10px!important}.ctpt-widget .ctpt-widget__delivery-type__options .ctpt-widget__delivery-type__item {font-size:13px!important}.ctpt-widget .ctpt-widget__delivery-type{height:calc(100% - 100px)}} @media screen and (max-width: 434px){.ctpt-widget__panel-details .ctpt-widget__primary-title span {width: 200px}} @media screen and (max-width: 490px){.ctpt-widget__panel-details .ctpt-widget__primary-title{display:block;padding-top:15px}.ctpt-widget__panel-details .ctpt-widget__primary-title span{width:calc(90% - 40px)}} @media only screen and (max-width:767px){.ctpt-widget__map .ymaps-2-1-79-searchbox__normal-layout{width:338px}.ctpt-widget__info-hint-text{width:186px;padding:24px 0 12px 12px}.ctpt-widget__info-hide-title{font-size:14px}.ctpt-widget__info-hide-desc{font-size:12px}.ctpt-widget__primary-title{padding:16px 0 15px;font-size:18px;height:40px;display:-webkit-box;display:-webkit-flex;display:-moz-flex;display:-ms-flexbox;display:flex;align-items:center;justify-content:center}.ctpt-widget__primary-title span{max-width:250px}.ctpt-widget__sidebar-button__hint.ctpt-widget-list{top:76px}.ctpt-widget__sidebar-button__hint.ctpt-widget-cash{top:134px}.ctpt-widget__sidebar-button__hint.ctpt-widget-cal{top:195px}.ctpt-widget__sidebar-button__hint.ctpt-widget-delivery{top:254px}.ctpt-widget__delivery-container{padding:0 10px;height: 107px}.ctpt-widget__delivery-type{width: calc(100% - 60px);height: calc(100% - 60px);margin-left: -30px}.ctpt-widget__delivery-type.current{-webkit-transition:top .5s ease-out,opacity .3s ease-out;-moz-transition:top .5s ease-out,opacity .3s ease-out;-ms-transition:top .5s ease-out,opacity .3s ease-out;-o-transition:top .5s ease-out,opacity .3s ease-out;transition:top .5s ease-out,opacity .3s ease-out}.ctpt-widget__delivery-type.current,.ctpt-widget__delivery-type.mapmode{top:60px}.ctpt-widget__delivery-type__item{padding:8px 12px;height: 65px}.ctpt-widget__delivery-type__price-wrap .ctpt-widget__button{width:96px}.ctpt-widget__delivery-filter{font-size:12px}.ctpt-widget__delivery-type__options-content{height: calc(100% - 160px)}.ctpt-widget__panel{top:60px;width:calc(100% - 60px);height:auto;bottom:0;z-index:7}.ctpt-widget__panel>div{width:100%;bottom:0;right:-100%}.ctpt-widget__panel-list.current{left:-100%}.ctpt-widget__panel-content-wrap{height:100%}.ctpt-widget__panel-content_list-item{-ms-flex-wrap:wrap;flex-wrap:wrap;height:auto}.ctpt-widget__panel-content__info{width:calc(100% - 80px)}.ctpt-widget__search-button{width:100%}.ctpt-widget__panel-content__info-address,.ctpt-widget__panel-content__info-amount,.ctpt-widget__panel-content__info-delivery,.ctpt-widget__panel-content__info-price{max-width:190px}.ctpt-widget__panel-content__price{margin-left:76px}.ctpt-widget__select{min-width:100%;margin:0 0 8px}.ctpt-widget__sidebar{top:60px;height:auto;bottom:0}.ctpt-widget.second-step{height: 600px;}.ctpt-widget.second-step .ctpt-widget__search-button{width:100%;display:none}.ctpt-widget__search-wrap{padding:0 15px}.ctpt-widget__search-wrap.current,.ctpt-widget__search-wrap.mapmode{top:0;width:100%;padding:0;max-width:100%}.ctpt-widget__delivery-type.mapmode{height: auto}.ctpt-widget__search-title{text-align:center}.ctpt-widget__search-form form{-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column}.ctpt-widget__search-wrap.current .ctpt-widget__search-form input.ctpt-widget__search-input,.ctpt-widget__search-wrap.mapmode .ctpt-widget__search-form input.ctpt-widget__search-input{width: 100%}.ctpt-widget__search-logo{top:19px;-moz-transform:translate(0,0);-o-transform:translate(0,0);-ms-transform:translate(0,0);-webkit-transform:translate(0,0);transform:translate(0,0)}.ctpt-widget__search-floating{width:100%}.ctpt-widget__search-input{width:100%;margin:0 0 4px}.ctpt-widget__search-hint{padding:0 30px;text-align:center}.ctpt-widget__map-search-form form{-webkit-box-shadow:0 2px 12px rgba(0,0,0,.15);-moz-box-shadow:0 2px 12px rgba(0,0,0,.15);box-shadow:0 2px 12px rgba(0,0,0,.15)}.ctpt-widget__map-search-input{height:60px;-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none}.ctpt-widget__map-search-button{height:60px}.ctpt-widget__search-wrap.current .ctpt-widget__search-button,.ctpt-widget__search-wrap.mapmode .ctpt-widget__search-button{display: none}.ctpt-widget__map_widget{padding:160px 60px 0 0}.ctpt_pvzonly_mode .ctpt-widget__map_widget{padding-top:60px}.ctpt_popup_mode.ctpt-widget .ctpl_popup_mode_close{right: -1px;top: 60px}.ctpt-widget__search-button .ctpt-widget__txt{display:none}.ctpt-widget__icon{display:block;position:absolute;left:50%;top:50%;width:40px;height:40px;margin-left:-20px;margin-top:-20px;opacity:1}.ctpt-widget:not(.ctpl_popup_first_step) .ctpt-widget__search-button .ctpt-widget__load{width:30px;height:30px;margin-right:-16px;margin-top:-16px;right:50%}.ctpt-widget__search-button.loading .ctpt-widget__icon{opacity:0}.ctpt-widget:not(.ctpl_popup_first_step) .ctpt-widget__search-floating{width: calc( 100% - 60px )}.ctpt-widget:not(.ctpl_popup_first_step) .ctpt-widget__search-wrap.current .ctpt-widget__search-button, .ctpt-widget:not(.ctpl_popup_first_step) .ctpt-widget__search-wrap.mapmode .ctpt-widget__search-button {display:flex;width:60px}.ctpt-widget:not(.ctpl_popup_first_step) .ctpt-widget__search-form form{-webkit-flex-direction:row;-moz-flex-direction:row;-o-flex-direction:row;-ms-flex-direction:row;flex-direction:row}.ctpt-widget.ctpl_popup_first_step .ctpt-widget__search-form input.ctpt-widget__search-input{width:100%}.ctpl_popup_first_step .ctpt-widget__icon{display:none}.ctpl_popup_first_step .ctpt-widget__txt{display:inline}.ctpt-widget__delivery-type__item{display:flex;align-items:center} } @media only screen and (min-width: 768px){.ctpt-widget__delivery-type{width:calc(100% - 100px);margin-left:-20px}.ctpt-widget__search-wrap{max-width:calc(100% - 100px);margin-left:-20px}.ctpt-widget__search-wrap,.ctpt-widget__delivery-type{max-width:calc(100% - 80px);margin-left:-30px}.ctpt-widget__delivery-type{width:calc(100% - 80px)}} @media only screen and (min-width:992px){.ctpt-widget__button:hover{color:#fff;background:#007dd5}.ctpt-widget__close:hover svg path{opacity:1;fill:#333}.ctpt-widget__sidebar-button:hover{background:#eff2f5}.ctpt-widget__delivery-type__button:hover{color:#007dd5;background:#fff}.ctpt-widget__search-button:hover{background:#fff;color:#01bd6c}.ctpt-widget__map-search-button:hover{background:#fff;color:#01bd6c}.ctpt-widget__map-search-button:hover svg path{fill:#01bd6c}.ctpt-widget__search-button:hover .ctpt-widget__load:after{border-top-color:#01bd6c;border-right-color:#01bd6c;border-bottom-color:#01bd6c}} '
    const CSS_string_popup = '.ctpt_popup_mode .ctpl_popup_mode_close {cursor: pointer;z-index: 11;position: absolute;right: 5px;top: 4px;-webkit-transition: top, right .5s ease-out,opacity .3s ease-out;-moz-transition: top, right .5s ease-out,opacity .3s ease-out;-ms-transition: top, right .5s ease-out,opacity .3s ease-out;-o-transition: top, right .5s ease-out,opacity .3s ease-out;transition: top, right .5s ease-out,opacity .3s ease-out;}  .ctpt_popup_mode .ctpt-widget__sidebar-button-wrap {padding-top: 56px;}  .ctpt_popup_mode .ctpt-widget__sidebar-button__hint_list {position: relative;top: 50px;} @media only screen and (max-width:767px){.ctpt_popup_mode.ctpt-widget .ctpl_popup_mode_close{width:60px;height:60px}.ctpt_close_popup_step .ctpt-widget__search-input{width:calc(100% - 13px)}  .ctpt_popup_mode.ctpt-widget.second-step .ctpl_popup_mode_close{padding:0;background:none}.ctpt_popup_mode.ctpt-widget.second-step .ctpt-widget__search-wrap{max-width:initial}  .ctpt_popup_mode.ctpt-widget.second-step .ctpt-widget__search-input{width:calc(100% - 13px)}.ctpt_popup_mode.ctpt-widget__sidebar-button-wrap{padding-top: 6px}.ctpt_popup_mode.ctpt-widget__sidebar-button__hint_list{top: 6px}.ctpt_popup_mode.ctpt-widget__map-search-form .ctpt-widget__map-search-button{display:none}.ctpt-widget .ctpt-widget__map-search-button{width:58px;font-size:0;padding-left:19px}.ctpt-widget .ctpt-widget__map-search-input{width: calc(100% - 55px)}  }   @media only screen and (max-width:991px){ .ctpt_popup_mode.ctpt-widget__sidebar-button__hint_list {top: 0;}}   @media only screen and (max-width: 767px) {  .ctpt_close_popup_step.ctpt_popup_mode.ctpt-widget .ctpt-widget__search-wrap {width: 100%;max-width: 100%;}}.ctpl_popup_first_step.ctpt_popup_mode.ctpt-widget .ctpl_popup_mode_close {top:4px;}';
    const CSS_string_only_PVZ = '.ctpt-widget__delivery-type_only_pvz.current {top: 329px;max-width: 464px;border-top: solid 4px rgb(0 125 213 / 38%);height: 83px;}@media only screen and (max-width: 767px) {.ctpt-widget__delivery-type_only_pvz.current {top: 362px;max-width: 430px;}}  ';
    class MapCover {
        constructor(WidgetObject, apiKey = '', lang = 'ru') {
            let self = this;
            self.Map = false;
            self.lang = lang === 'ru' ? 'ru_RU' : 'en_GB';
            self.scriptObject = false;
            self.srcPatch = 'https://api-maps.yandex.ru/';
            self.baseVersion = '2.1.66';
            let src = self.srcPatch + self.baseVersion + '/?lang=' + self.lang + '&ns=CatapultoYmapNamespace';
            if (apiKey) {
                src += '&apikey=' + apiKey;
            }
            self.src = src;
            self.Clusterer = false;
            self.WidgetObject = WidgetObject;
            self.baseIconLayout = false;
            self.selectedIconLayout = false;
            self.iconLayoutByOperator = {};
        }
        getWidgetObject = () => this.WidgetObject;
        setMap = map => {
            this.Map = map;
            if (!window.CtptWidgetMaps) {
                window.CtptWidgetMaps = {};
            }
            window.CtptWidgetMaps[this.getWidgetObject().getParams().getWidgetId()] = map;
        };
        setOperatorIcon = (code, src) => {
            this.iconLayoutByOperator[code] = [
                CatapultoYmapNamespace.templateLayoutFactory.createClass(
                    '<div class="ctp_point_icon"><img alt="catapulto" src="' + src + '"></div>'
                ),
                CatapultoYmapNamespace.templateLayoutFactory.createClass(
                    '<div class="ctp_point_icon selected"><img alt="catapulto" src="' + src + '"></div>'
                )
            ];
        };
        getOperatorIcons = (code) => {
            return this.iconLayoutByOperator[code] ? this.iconLayoutByOperator[code] : false;
        };
        createClusterer = () => {
            this.Clusterer = new CatapultoYmapNamespace.Clusterer({
                preset: 'islands#invertedVioletClusterIcons',
                disableClickZoom: false,
                groupByCoordinates: false,
                clusterDisableClickZoom: false,
                maxZoom: 16,
                minClusterSize: 3,
            });
            this.getMap().geoObjects.add(this.getClusterer());

            this.baseIconLayout = CatapultoYmapNamespace.templateLayoutFactory.createClass(
                '<div class="ctpt-widget_delivery-widget-ymap-placemark"></div>'
            );
            this.selectedIconLayout = CatapultoYmapNamespace.templateLayoutFactory.createClass(
                '<div class="ctpt-widget_delivery-widget-ymap-placemark-selected"></div>'
            );
        };
        centerOnPlaceMark = (PlaceMark) => {
            let Container = this.getWidgetObject().getStructure().getContainer().getDomElement();
            this.removePlaceMarksSelect();
            PlaceMark.options.set({iconLayout: PlaceMark.selectedIconLayout});
            PlaceMark.Selected = true;

            this.getMap().setZoom(16);
            this.getMap().setCenter(PlaceMark.geometry._coordinates);
            let pixelCenter = this.getMap().getGlobalPixelCenter(this.getMap().getCenter());
            pixelCenter = [
                pixelCenter[0] + Container.offsetWidth / 4,
                pixelCenter[1]
            ];
            let geoCenter = this.getMap().options.get('projection').fromGlobalPixels(pixelCenter, this.getMap().getZoom());
            this.getMap().setCenter(geoCenter);
            this.removeBalloon();
        };
        resetZoom = () => {
            this.getMap().setZoom(14);
        };
        resetView = () => {
            this.getMap().container.fitToViewport();
        };
        recenterMap = (zoom = 14) => {
            this.getMap().setZoom(zoom);
            let Container = this.getWidgetObject().getStructure().getContainer().getDomElement();
            let pixelCenter = this.getMap().getGlobalPixelCenter(this.getMap().getCenter());
            pixelCenter = [
                pixelCenter[0] + Container.offsetWidth / 4,
                pixelCenter[1]
            ];
            let geoCenter = this.getMap().options.get('projection').fromGlobalPixels(pixelCenter, this.getMap().getZoom());
            this.getMap().setCenter(geoCenter);
            this.removeBalloon();
        };
        resetCenter = () => {
            this.getMap().setCenter(this.getWidgetObject().getCenter());
        }
        removePlaceMarksSelect = () => {
            let PlaceMarks = this.getClusterer().getGeoObjects();
            for (let i in PlaceMarks) {
                let PlaceMark = PlaceMarks[i];
                PlaceMark.options.set({iconLayout: PlaceMark.baseIconLayout});
                PlaceMark.Selected = false;
            }
        };
        resetPlacemarksZIndex = () => {
            this.getWidgetObject().getStructure().getPvzList().getChild('List').getChilds().each(function(item){
                item.getPlaceMark().options.set({zIndex:110});
            });
        };
        removeBalloon = () => {
            let resBalloon = document.getElementsByClassName(
                this.getMap().container._element.classList.toString().replace('-map', '-islets_icon-with-caption')
            );
            if (resBalloon.length > 0) {
                resBalloon[0].remove()
            }
        };
        getBasePlaceMarkIconLayout = () => this.baseIconLayout;
        getSelectedPlaceMarkIconLayout = () => this.selectedIconLayout;
        getClusterer = () => this.Clusterer;
        createPlaceMark = (TerminalData, openCallback = () => {}) => {
            const CurrentRate = this.getWidgetObject().getData().getPvzByOperator()[TerminalData.operator];
            const namePlaceMark = CurrentRate && CurrentRate[0] ? CurrentRate[0].rate : '';
            const operatorIcons = this.getOperatorIcons(TerminalData.operator);
            let baseIconLayout;
            let selectedIconLayout;
            if (operatorIcons) {
                baseIconLayout = operatorIcons[0];
                selectedIconLayout = operatorIcons[1];
            } else {
                baseIconLayout = this.getBasePlaceMarkIconLayout();
                selectedIconLayout = this.getSelectedPlaceMarkIconLayout();
            }
            let PlaceMark = new CatapultoYmapNamespace.Placemark(TerminalData.coords, {
                hintContent: namePlaceMark,
            }, {
                iconLayout: baseIconLayout,
                iconImageSize: [40, 43],
                iconImageOffset: [0, 0],
                iconShape: {
                    type: 'Rectangle',
                    coordinates: [
                        [-20, -40], [20, 3]
                    ]
                }
            });
            PlaceMark.selectedIconLayout = selectedIconLayout;
            PlaceMark.baseIconLayout = baseIconLayout;

            PlaceMark.events.add('click', openCallback);
            PlaceMark.events.add('mouseenter', function () {
                PlaceMark.options.set({iconLayout: selectedIconLayout});
            }).add('mouseleave', function () {
                if ( PlaceMark.Selected !== true) {
                    PlaceMark.options.set({iconLayout: baseIconLayout});
                }
            });
            return PlaceMark;
        };
        getMap = () => this.Map;
        getScriptObject = () => {
            if (this.scriptObject) {
                return this.scriptObject;
            }
            let scriptsList = document.querySelectorAll('script');
            for (let i in scriptsList) {
                let src = scriptsList[i].src;
                if (src && src.includes('ns=CatapultoYmapNamespace')) {
                    this.scriptObject = scriptsList[i];
                    break;
                }
            }
            return this.scriptObject;
        };
        isLoaded = () => typeof this.getScriptObject() === 'object';
        checkYandexMapApiKey = () => {
            let scriptObject = this.getScriptObject();
            let matchResult = scriptObject.src.match(/\apikey=([\w\d\-]{0,})/i);
            if (!matchResult[1]) {
                throw new Error('Yandex Map need apikey for geocoder');
            }
        };
        loadChecker = callback => {
            let counterCheckYmapApiLoad = 0;
            let checkerYmapApiLoad = setInterval(function () {
                counterCheckYmapApiLoad++;
                if (counterCheckYmapApiLoad >= 50) {
                    clearInterval(checkerYmapApiLoad);
                    return false;
                }
                try {
                    if (typeof(CatapultoYmapNamespace.geocode) === 'function') {
                        clearInterval(checkerYmapApiLoad);
                        callback(CatapultoYmapNamespace);
                    }
                } catch (e) {

                }
            }, 500);
        };
        loadScript = () => {
            let loadedTag = false;
            loadedTag = document.createElement('script');
            loadedTag.src = this.src;
            let head = document.getElementsByTagName('head')[0];
            head.appendChild(loadedTag);
            loadedTag.onload = this.scriptLoad.bind(this);
        };
        load = () => {
            if (!this.isLoaded()) {
                this.loadScript(this.scriptLoad.bind(this));
            } else {
                this.scriptLoad();
            }
        };
        scriptLoad = () => {
            this.loadChecker((CatapultoYmapNamespace) => {
                CatapultoYmapNamespace.ready(()=>{
                    this.onReady();
                })
            });
        };
        onReady = () => {};
    }
    class Assets {
        constructor(lang = 'ru') {
            const exist_langs = ['ru'];

            if (!exist_langs.includes(lang)) {
                throw new Error('lang code: "' + lang + '" dont have assets');
            }
            let self = this;
            self.lang = lang;

            self.ru = {
                ui_text: {
                    currency: {
                        RUB: {short: ' ₽'},
                    },
                    delivery_variant_day: [' день', ' дня', ' дней'],
                    base_delivery_variant_day: [' день', ' дня', ' дней'],
                    filter_variant_day: ['от #TIME#-го дня', 'от #TIME#-х дней', 'от #TIME#-и дней'],
                    delivery_variant_price_from: 'от ',
                    base_search_title: 'Укажите своё местоположение для расчёта доставки',
                    base_search_label: 'Введите город или полный адрес',
                    base_search_description: 'Если вам требуется доставка курьером, укажите, ' +
                        'пожалуйста, ваш полный адрес для более точного расчёта',
                    map_search_placeholder: 'Введите адрес',
                    base_search_submit_text: 'Найти',
                    delivery_variant_container_title: 'Выберите способ доставки',
                    delivery_variant_courier_title: 'Курьер до двери',
                    delivery_variant_pvz_title: 'Пункт выдачи заказов',
                    delivery_variant_filter_speed: 'По скорости',
                    delivery_variant_filter_rate: 'По рейтингу',
                    delivery_variant_filter_price: 'По стоимости',
                    panel_pvz_list_title: 'Пункты выдачи (#COUNT# шт.)',
                    panel_filter_list_title: 'Курьерские компании',
                    map_selector_courier: 'Курьером',
                    map_selector_pvz: 'Пункт выдачи',
                    panel_info_pvz: 'Список',
                    panel_info_type_1: 'Постамат',
                    panel_info_type_2: 'ПВЗ',
                    panel_info_pay_all: 'Любая оплата',
                    panel_info_pay_cash: 'Оплата наличными',
                    panel_info_pay_card: 'Оплата картой',
                    panel_info_placeholder_all: 'ПВЗ и постаматы',
                    panel_info_placeholder_pvz: 'только ПВЗ',
                    panel_info_placeholder_terminal: 'только постаматы',
                    panel_info_filter: 'Выбор курьерских компаний',
                    map_search_button: 'Найти',
                    delivery_variant_item_time_title: 'Срок',
                    delivery_variant_item_time_title_date: 'Дата',
                    variant_day_date: 'с ',
                    load_spinner_text: 'Загрузка результатов',
                    pvz_item_time: '<span>Срок: </span>#time#',
                    count_template: ' (#COUNT# шт.)',
                    detail_price_title: 'Стоимость доставки:',
                    detail_address_title: 'Адрес пункта выдачи заказов:',
                    detail_work_title: 'Время работы:',
                    detail_phone_title: 'Телефоны:',
                    detail_how_to_find_title: 'Как к нам проехать:',
                    detail_select: 'Выбрать',
                    courier_variant_in_time_template: '#PERCENT#% в срок',
                    modal_title: 'Выберите дату и время получения',
                    refresh_btn_text: 'Перезапустить',
                    api_error_text: 'К сожалению, не удалось получить расчет по доставке',
                    api_error_text_r400: 'Bad Request (ошибка 400)',
                    api_error_text_r401: 'Ошибка ключа доступа',
                    api_error_text_r403: '403 Forbidden',
                    api_error_text_r404: 'Обработчик не найден (ошибка 404)',
                    api_error_text_r500: 'Произошла внутренняя ошибка сервера',
                    api_error_text_r502: '502 Bad Gateway',
                    api_error_text_r429: 'Превышен лимит расчётов',
                    yandes_warn_text: 'Чтобы получить расчет по курьерским службам с доставкой день в день, нужно указать точный адрес получателя.',
                },
                link: 'https://catapulto.ru',
            };
            self.svg_collection = {
                search_logo: '<svg width="28" height="24" viewBox="0 0 28 24" fill="none" xmlns="http://w' +
                    'ww.w3.org/2000/svg" class="ctpt-widget__search-logo"><path d="M0 12L5.56725 22.152L' +
                    '7.91814 18.0585L4.91228 12H0Z" fill="#AFDFF6"></path><path d="M0 12L5.56725 1.84795' +
                    'L7.91814 5.94152L4.91228 12H0Z" fill="#A2ABBB"></path><path d="M6.5498 0.21051L13.0' +
                    '176 12H17.7662L13.5907 3.64911H18.6319L23.4153 12H28.0001L21.3865 0.21051H6.5498Z" ' +
                    'fill="#20A5E8"></path><path d="M6.5498 23.7895L13.0176 12H17.7662L13.5907 20.3509H1' +
                    '8.6319L23.4153 12H28.0001L21.3865 23.7895H6.5498Z" fill="#089AE2"></path></svg>',
                courier_button: '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://' +
                    'www.w3.org/2000/svg"><path d="M6 0C6.79565 0 7.55871 0.31607 8.12132 0.87868C8.68393 1' +
                    '.44129 9 2.20435 9 3C9 3.79565 8.68393 4.55871 8.12132 5.12132C7.55871 5.68393 6.79565' +
                    ' 6 6 6C5.20435 6 4.44129 5.68393 3.87868 5.12132C3.31607 4.55871 3 3.79565 3 3C3 2.204' +
                    '35 3.31607 1.44129 3.87868 0.87868C4.44129 0.31607 5.20435 0 6 0ZM6 7.5C9.315 7.5 12 8' +
                    '.8425 12 10.5V12H0V10.5C0 8.8425 2.685 7.5 6 7.5Z" "=""></path></svg>',
                pvz_button: '<svg width="15" height="12" viewBox="0 0 15 12" fill="none" xmlns="http://www.' +
                    'w3.org/2000/svg"><path d="M11.8131 8.24982H3.19703C3.0939 8.24982 3.00952 8.3342 3.009' +
                    '52 8.43733L3.00718 9.56238C3.00718 9.66551 3.09156 9.74989 3.19469 9.74989H11.8131C11.' +
                    '9162 9.74989 12.0006 9.66551 12.0006 9.56238V8.43733C12.0006 8.3342 11.9162 8.24982 11' +
                    '.8131 8.24982ZM11.8131 10.4999H3.19C3.08687 10.4999 3.00249 10.5843 3.00249 10.6874L3.' +
                    '00015 11.8125C3.00015 11.9156 3.08453 12 3.18766 12H11.8131C11.9162 12 12.0006 11.9156' +
                    ' 12.0006 11.8125V10.6874C12.0006 10.5843 11.9162 10.4999 11.8131 10.4999ZM11.8131 5.99' +
                    '971H3.20172C3.09859 5.99971 3.01421 6.08409 3.01421 6.18722L3.01187 7.31227C3.01187 7.' +
                    '4154 3.09624 7.49978 3.19937 7.49978H11.8131C11.9162 7.49978 12.0006 7.4154 12.0006 7.' +
                    '31227V6.18722C12.0006 6.08409 11.9162 5.99971 11.8131 5.99971ZM14.3093 2.74174L7.93164' +
                    ' 0.0861385C7.79456 0.0292714 7.6476 0 7.49919 0C7.35079 0 7.20383 0.0292714 7.06675 0.' +
                    '0861385L0.69144 2.74174C0.274232 2.91753 0 3.32536 0 3.78007V11.8125C0 11.9156 0.08437' +
                    '91 12 0.187509 12H2.0626C2.16573 12 2.25011 11.9156 2.25011 11.8125V5.99971C2.25011 5.' +
                    '58719 2.59231 5.24967 3.01421 5.24967H11.9865C12.4084 5.24967 12.7506 5.58719 12.7506 ' +
                    '5.99971V11.8125C12.7506 11.9156 12.835 12 12.9381 12H14.8132C14.9164 12 15.0007 11.915' +
                    '6 15.0007 11.8125V3.78007C15.0007 3.32536 14.7265 2.91753 14.3093 2.74174Z"></path></s' +
                    'vg>',
                map_search:'<svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w' +
                    '3.org/2000/svg"><path d="M16.8731 9.03039L19.9526 15.3566C20.0778 15.6139 19.9462 15.8' +
                    '244 19.6601 15.8244H0.339955C0.0538124 15.8244 -0.0778555 15.6139 0.0474059 15.3566L3.' +
                    '12695 9.03039C3.15625 8.97764 3.19846 8.93318 3.24962 8.90118C3.30079 8.86918 3.35924 ' +
                    '8.85068 3.4195 8.84742H6.06005C6.15281 8.85285 6.24011 8.89301 6.3046 8.9599C6.48375 9' +
                    '.16693 6.66623 9.36919 6.84819 9.56938C7.021 9.75941 7.19473 9.95193 7.36708 10.149H4.' +
                    '23339C4.17313 10.1522 4.11467 10.1707 4.06351 10.2027C4.01234 10.2347 3.97013 10.2792 ' +
                    '3.94084 10.3319L1.90077 14.5228H18.0991L16.0592 10.3319C16.0299 10.2792 15.9877 10.234' +
                    '7 15.9366 10.2027C15.8854 10.1707 15.8269 10.1522 15.7667 10.149H12.6259C12.7982 9.951' +
                    '93 12.972 9.75942 13.1448 9.56938C13.3273 9.3686 13.5108 9.16674 13.6904 8.95964C13.75' +
                    '48 8.89291 13.8419 8.85285 13.9345 8.84742H16.5806C16.6409 8.85067 16.6993 8.86917 16.' +
                    '7505 8.90117C16.8016 8.93317 16.8438 8.97764 16.8731 9.03039ZM14.5275 4.70713C14.5275 ' +
                    '8.16386 11.6498 8.81216 10.2964 12.1341C10.2721 12.1938 10.2306 12.245 10.177 12.2809C' +
                    '10.1235 12.3169 10.0604 12.336 9.99595 12.3359C9.93147 12.3357 9.8685 12.3163 9.81512 ' +
                    '12.2801C9.76174 12.2439 9.7204 12.1926 9.69639 12.1328C8.47551 9.13765 6.01541 8.31631' +
                    ' 5.54473 5.64807C5.0813 3.02131 6.89999 0.448236 9.55548 0.197223C10.1847 0.135796 10.' +
                    '8198 0.206706 11.42 0.405394C12.0202 0.604082 12.5721 0.926152 13.0404 1.3509C13.5087 ' +
                    '1.77565 13.8829 2.29368 14.139 2.8717C14.3951 3.44971 14.5275 4.07491 14.5275 4.70713Z' +
                    'M12.3894 4.70713C12.3894 4.2339 12.249 3.7713 11.9861 3.37783C11.7232 2.98435 11.3495 ' +
                    '2.67768 10.9123 2.49658C10.4751 2.31548 9.99403 2.2681 9.5299 2.36042C9.06577 2.45274 ' +
                    '8.63943 2.68062 8.30481 3.01525C7.97019 3.34987 7.74231 3.7762 7.64998 4.24034C7.55766' +
                    ' 4.70447 7.60504 5.18556 7.78614 5.62277C7.96724 6.05997 8.27391 6.43366 8.66739 6.696' +
                    '57C9.06086 6.95948 9.52346 7.09981 9.99669 7.09981C10.3109 7.09981 10.622 7.03792 10.9' +
                    '123 6.91768C11.2026 6.79744 11.4664 6.62119 11.6886 6.39901C11.9108 6.17683 12.087 5.9' +
                    '1306 12.2072 5.62277C12.3275 5.33247 12.3894 5.02134 12.3894 4.70713H12.3894Z" fill="w' +
                    'hite"></path></svg>',
                map_search_logo: '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="24" viewBox="0' +
                    ' 0 28 24" fill="none"><path d="M0 12L5.56725 22.152L7.91814 18.0585L4.91228 12H0Z" fil' +
                    'l="#AFDFF6"/><path d="M0 12L5.56725 1.84795L7.91814 5.94152L4.91228 12H0Z" fill="#A2AB' +
                    'BB"/><path d="M6.5498 0.21051L13.0176 12H17.7662L13.5907 3.64911H18.6319L23.4153 12H28' +
                    '.0001L21.3865 0.21051H6.5498Z" fill="#20A5E8"/><path d="M6.5498 23.7895L13.0176 12H17.' +
                    '7662L13.5907 20.3509H18.6319L23.4153 12H28.0001L21.3865 23.7895H6.5498Z" fill="#089AE2' +
                    '"/></svg>',
                sidebar_pvz: '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www' +
                    '.w3.org/2000/svg"><path d="M8.19995 7.49998H0.199951V4.69998H8.19995V7.49998ZM8.19995 ' +
                    '11.7H0.199951V14.5H8.19995V11.7ZM0.199951 21.5H16.2V18.7H0.199951V21.5Z" fill="#007DD5' +
                    '"></path><path d="M16.1999 0.5C13.1079 0.5 10.5999 3.008 10.5999 6.1C10.5999 10.3 16.1' +
                    '999 16.5 16.1999 16.5C16.1999 16.5 21.7999 10.3 21.7999 6.1C21.8 3.008 19.292 0.5 16.1' +
                    '999 0.5ZM16.1999 8.10003C15.0959 8.10003 14.2 7.20404 14.2 6.10003C14.2 4.99603 15.095' +
                    '9 4.10001 16.1999 4.10001C17.3039 4.10001 18.1999 4.99599 18.1999 6.1C18.1999 7.204 17' +
                    '.3039 8.10003 16.1999 8.10003Z" fill="#007DD5"></path></svg>',
                sidebar_pay: '<svg class="delivery_widget_svg_icon_card" style="display: none;" width="26" ' +
                    'height="32" viewBox="0 0 26 32" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip' +
                    '-path="url(#clip0)"><path class="p" d="M13.0493 0C10.7435 0.00513411 8.48527 0.656888 6.5313 1.8' +
                    '8121C4.57734 3.10554 3.00592 4.85335 1.9956 6.92608C0.985274 8.99881 0.57654 11.3134 0' +
                    '.815836 13.6068C1.05513 15.9002 1.93286 18.0805 3.34931 19.9L13.0493 32V0Z" fill="#007' +
                    '3BC"></path><path class="p" d="M13.0493 32L22.6493 19.9C24.3443 17.7302 25.26 15.0533 25.2493 12' +
                    '.3C25.2701 10.6874 24.9695 9.08683 24.365 7.59167C23.7606 6.09651 22.8643 4.73673 21.7' +
                    '286 3.59171C20.5929 2.44669 19.2405 1.53937 17.7503 0.922697C16.2601 0.306026 14.662 -' +
                    '0.00763793 13.0493 1.46533e-05V32Z" fill="#0073BC"></path><path d="M19.9493 5.49998C19' +
                    '.8335 5.37662 19.6943 5.27769 19.5397 5.20899C19.3851 5.14029 19.2184 5.10323 19.0493 ' +
                    '5.09998H7.04927C6.87971 5.10053 6.71212 5.13638 6.55717 5.20525C6.40222 5.27412 6.2633' +
                    '1 5.37449 6.14927 5.49998C5.91773 5.74593 5.77668 6.06329 5.74927 6.39998V15.6C5.74982' +
                    ' 15.7695 5.78568 15.9371 5.85454 16.0921C5.92341 16.247 6.02378 16.3859 6.14927 16.5C6' +
                    '.26501 16.6233 6.40426 16.7223 6.55883 16.791C6.7134 16.8597 6.88015 16.8967 7.04927 1' +
                    '6.9H19.1493C19.3188 16.8994 19.4864 16.8636 19.6414 16.7947C19.7963 16.7258 19.9352 16' +
                    '.6255 20.0493 16.5C20.1726 16.3842 20.2716 16.245 20.3403 16.0904C20.409 15.9358 20.44' +
                    '6 15.7691 20.4493 15.6V6.39998C20.3437 6.06988 20.1738 5.76398 19.9493 5.49998ZM19.349' +
                    '3 15.6C19.3545 15.6395 19.3477 15.6796 19.3299 15.7153C19.3121 15.7509 19.284 15.7804 ' +
                    '19.2493 15.8C19.1954 15.8543 19.1251 15.8895 19.0493 15.9H7.04927C7.00976 15.9052 6.96' +
                    '96 15.8984 6.93395 15.8806C6.89831 15.8628 6.86881 15.8347 6.84927 15.8C6.79491 15.746' +
                    '1 6.75977 15.6758 6.74927 15.6V11H19.3493V15.6ZM19.3493 8.09998H6.74927V6.39998C6.7440' +
                    '7 6.36047 6.75081 6.3203 6.76864 6.28466C6.78646 6.24902 6.81454 6.21952 6.84927 6.199' +
                    '98C6.90317 6.14562 6.97344 6.11048 7.04927 6.09998H19.1493C19.1888 6.09478 19.2289 6.1' +
                    '0152 19.2646 6.11934C19.3002 6.13717 19.3297 6.16525 19.3493 6.19998C19.4036 6.25388 1' +
                    '9.4388 6.32415 19.4493 6.39998L19.3493 8.09998Z" fill="white"></path><path d="M17.5493' +
                    ' 13.0999H15.7493V14.7999H17.5493V13.0999Z" fill="white"></path></g><defs><clipPath id=' +
                    '"clip0"><rect width="24.5013" height="32" fill="white" transform="translate(0.749268)"' +
                    '></rect></clipPath></defs></svg><svg class="delivery_widget_svg_icon_cash" style="disp' +
                    'lay: none;" width="25" height="32" viewBox="0 0 25 32" fill="none" xmlns="http://www.w' +
                    '3.org/2000/svg"><g clip-path="url(#clip0)"><path class="p" d="M12.3 0.000136724C9.99419 0.005270' +
                    '84 7.736 0.657024 5.78204 1.88135C3.82807 3.10568 2.25666 4.85349 1.24633 6.92622C0.23' +
                    '6006 8.99895 -0.172727 11.3135 0.066568 13.6069C0.305863 15.9003 1.18359 18.0806 2.600' +
                    '04 19.9001L12.3 32.0001L21.9 19.9001C23.595 17.7303 24.5108 15.0535 24.5 12.3001C24.52' +
                    '09 10.6875 24.2202 9.08695 23.6158 7.59179C23.0113 6.09663 22.115 4.73685 20.9793 3.59' +
                    '184C19.8436 2.44682 18.4912 1.53949 17.001 0.922819C15.5108 0.306148 13.9128 -0.007515' +
                    '86 12.3 0.000136724Z" fill="#007DD5"></path><path fill-rule="evenodd" clip-rule="eveno' +
                    'dd" d="M15.4793 9.98557C15.1735 9.80802 14.6803 9.80802 14.3745 9.98557C14.0687 10.163' +
                    '1 14.072 10.4492 14.3778 10.6268C14.6836 10.8043 15.1801 10.8043 15.4826 10.6268C15.78' +
                    '84 10.4492 15.7851 10.1631 15.4793 9.98557ZM9.65605 6.62185C9.34697 6.44429 8.85375 6.' +
                    '44429 8.54796 6.62185C8.24217 6.79941 8.24546 7.08547 8.55454 7.26303C8.86033 7.44059 ' +
                    '9.35683 7.44059 9.65934 7.26303C9.96513 7.08547 9.96184 6.79941 9.65605 6.62185ZM13.13' +
                    '16 13.0271L4.40824 7.99299C3.90516 7.70035 3.90188 7.22686 4.40167 6.93751L9.07406 4.2' +
                    '1825C9.57714 3.9289 10.3959 3.92561 10.899 4.21825L19.6223 9.25562C20.1254 9.54497 20.' +
                    '1254 10.0185 19.6256 10.3111L14.9532 13.0271C14.4534 13.3197 13.6346 13.3197 13.1316 1' +
                    '3.0271ZM6.12463 6.83886C6.18711 6.86517 6.24629 6.89147 6.30219 6.92435C6.81184 7.2202' +
                    '8 6.81513 7.70035 6.30548 7.99299C6.24958 8.02587 6.18711 8.05546 6.12134 8.08177L12.8' +
                    '948 11.9946C12.9277 11.9683 12.9606 11.9453 13 11.9223C13.5064 11.6263 14.3317 11.6263' +
                    ' 14.8447 11.9223C14.9236 11.9683 14.9893 12.0176 15.0452 12.0702L17.8631 10.4328C17.77' +
                    '11 10.3999 17.6856 10.3637 17.6067 10.3177C17.0937 10.0217 17.0904 9.54497 17.6001 9.2' +
                    '4904C17.679 9.20301 17.7612 9.16684 17.8533 9.13396L11.0601 5.21126C11.0173 5.25071 10' +
                    '.968 5.28688 10.9088 5.31976C10.4024 5.6124 9.57385 5.61569 9.06419 5.31976C9.00829 5.' +
                    '28688 8.95897 5.254 8.91623 5.21783L6.12463 6.83886ZM4.96064 9.02545C4.74692 8.9005 4.' +
                    '40167 8.88406 4.18137 8.99915C3.94462 9.1208 3.94133 9.33124 4.16164 9.46277L13.3026 1' +
                    '4.7369L13.4308 14.8125L13.6149 14.9177C13.8714 15.0657 14.289 15.0657 14.5455 14.9177L' +
                    '19.7834 11.8729C19.9971 11.748 19.9971 11.5474 19.7801 11.4225C19.5664 11.2975 19.2178' +
                    ' 11.2975 19.0041 11.4225L14.0753 14.2864L4.96064 9.02545ZM14.5553 16.5651L19.7933 13.5' +
                    '203C20.007 13.3953 20.0037 13.1948 19.79 13.0698C19.573 12.9449 19.2244 12.9449 19.010' +
                    '7 13.0698L14.0851 15.9305L4.97051 10.6695C4.75678 10.5446 4.41153 10.5281 4.19123 10.6' +
                    '432C3.95449 10.7681 3.94791 10.9786 4.1715 11.1068L13.3091 16.3842L13.4407 16.4598L13.' +
                    '6248 16.5651C13.8813 16.713 14.2988 16.713 14.5553 16.5651ZM4.96064 12.3168C4.74692 12' +
                    '.1919 4.40167 12.1754 4.18137 12.2905C3.94462 12.4155 3.94133 12.6259 4.16164 12.7542L' +
                    '13.3026 18.0283L13.4308 18.1039L13.6149 18.2091C13.8714 18.3571 14.289 18.3571 14.5455' +
                    ' 18.2091L19.7834 15.1643C19.9971 15.0394 19.9971 14.8388 19.7801 14.7139C19.5664 14.58' +
                    '89 19.2178 14.5889 19.0041 14.7139L14.0753 17.5811L4.96064 12.3168ZM14.5553 19.8564L19' +
                    '.7933 16.8117C20.007 16.6867 20.0037 16.4861 19.7933 16.3579C19.5762 16.233 19.2277 16' +
                    '.233 19.014 16.3579L14.0851 19.2218L4.97051 13.9609C4.75678 13.8359 4.41153 13.8195 4.' +
                    '19123 13.9346C3.95449 14.0595 3.94791 14.27 4.1715 14.3982L13.3091 19.6756L13.4407 19.' +
                    '7512L13.6248 19.8564C13.8813 20.0044 14.2988 20.0044 14.5553 19.8564Z" fill="white"></' +
                    'path><path d="M10.0243 9.78171C11.1291 10.4196 12.9178 10.4196 14.0161 9.78171C15.1176' +
                    ' 9.14382 15.1077 8.10807 14.0029 7.47018C12.8981 6.83229 11.1094 6.83229 10.0112 7.470' +
                    '18C8.91294 8.10807 8.91952 9.14053 10.0243 9.78171Z" fill="white"></path></g><defs><cl' +
                    'ipPath id="clip0"><rect width="24.5013" height="32" fill="white"></rect></clipPath></d' +
                    'efs></svg><svg class="delivery_widget_svg_icon_all" width="25" height="32" viewBox="0 ' +
                    '0 25 32" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0)"><pa' +
                    'th d="M12.3 0.000136724C9.99419 0.00527084 7.736 0.657024 5.78204 1.88135C3.82807 3.10' +
                    '568 2.25666 4.85349 1.24633 6.92622C0.236006 8.99895 -0.172727 11.3135 0.066568 13.606' +
                    '9C0.305863 15.9003 1.18359 18.0806 2.60004 19.9001L12.3 32.0001L21.9 19.9001C23.595 17' +
                    '.7303 24.5108 15.0535 24.5 12.3001C24.5209 10.6875 24.2202 9.08695 23.6158 7.59179C23.' +
                    '0113 6.09663 22.115 4.73685 20.9793 3.59184C19.8436 2.44682 18.4912 1.53949 17.001 0.9' +
                    '22819C15.5108 0.306148 13.9128 -0.00751586 12.3 0.000136724Z" fill="#007DD5"></path><p' +
                    'ath d="M15.5853 6.33224H8.41796C8.04898 6.33224 7.74857 6.0351 7.75184 5.66612C7.75184' +
                    ' 5.29714 8.04898 5 8.41796 5H15.5853C15.9543 5 16.2514 5.29714 16.2514 5.66612C16.2514' +
                    ' 6.0351 15.9543 6.33224 15.5853 6.33224Z" fill="white"></path><path d="M6.41633 7.4163' +
                    '3H17.5837C17.9527 7.41633 18.2498 7.71673 18.2498 8.08245C18.2498 8.45143 17.9527 8.74' +
                    '857 17.5837 8.74857H6.41633C6.04735 8.74857 5.7502 8.45143 5.7502 8.08245C5.7502 7.713' +
                    '47 6.04735 7.41633 6.41633 7.41633Z" fill="white"></path><path d="M12.449 14.2082H11.2' +
                    '204V12.1347H12.5102C12.8476 12.1429 13.1116 12.2435 13.302 12.4367C13.4952 12.6299 13.' +
                    '5918 12.8789 13.5918 13.1837C13.5918 13.5238 13.4939 13.7796 13.298 13.951C13.102 14.1' +
                    '224 12.819 14.2082 12.449 14.2082Z" fill="white"></path><path fill-rule="evenodd" clip' +
                    '-rule="evenodd" d="M4.66612 9.78694H19.3339C19.7029 9.78694 20 10.0841 20 10.4531V17.9' +
                    '176C20 18.2865 19.7029 18.5837 19.3339 18.5837H4.66612C4.29714 18.5837 4 18.2865 4 17.' +
                    '9176V10.4531C4 10.0841 4.29714 9.78694 4.66612 9.78694ZM11.2204 16.302H12.5347V15.4735' +
                    'H11.2204V15.0367H12.5102C13.1741 15.0286 13.6925 14.8612 14.0653 14.5347C14.4381 14.20' +
                    '54 14.6245 13.7524 14.6245 13.1755C14.6245 12.6095 14.4286 12.1565 14.0367 11.8163C13.' +
                    '6449 11.4735 13.1211 11.302 12.4653 11.302H10.1918V14.2082H9.3551V15.0367H10.1918V15.4' +
                    '735H9.3551V16.302H10.1918V17.2449H11.2204V16.302Z" fill="white"></path></g><defs><clip' +
                    'Path id="clip0"><rect width="24.5013" height="32" fill="white"></rect></clipPath></def' +
                    's></svg>',
                sidebar_type:
                    '<svg class="ctpl-sidebar-all" width="25" height="32" viewBox="0 0 25 32" fill="none" x' +
                    'mlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_2112:7516)"><path d="M12.3 ' +
                    '0.000136724C9.99419 0.00527084 7.736 0.657024 5.78204 1.88135C3.82807 3.10568 2.25666 ' +
                    '4.85349 1.24633 6.92622C0.236006 8.99895 -0.172727 11.3135 0.066568 13.6069C0.305863 1' +
                    '5.9003 1.18359 18.0806 2.60004 19.9001L12.3 32.0001L21.9 19.9001C23.595 17.7303 24.510' +
                    '8 15.0535 24.5 12.3001C24.5209 10.6875 24.2202 9.08695 23.6158 7.59179C23.0113 6.09663' +
                    ' 22.115 4.73685 20.9793 3.59184C19.8436 2.44682 18.4912 1.53949 17.001 0.922819C15.510' +
                    '8 0.306148 13.9128 -0.00751586 12.3 0.000136724Z" fill="#007DD5"/><path d="M4 10.8182V' +
                    '18.0909H7.63636V13.7273H10.5455V18.0909H14.1818V10.8182L9.09091 7.18182L4 10.8182Z" fi' +
                    'll="white"/><path d="M10.5455 5V6.43273L15.6364 10.0691V10.8182H17.0909V12.2727H15.636' +
                    '4V13.7273H17.0909V15.1818H15.6364V18.0909H20V5H10.5455ZM17.0909 9.36364H15.6364V7.9090' +
                    '9H17.0909V9.36364Z" fill="white"/></g><defs><clipPath id="clip0_2112:7516"><rect width' +
                    '="24.5013" height="32" fill="white"/></clipPath></defs></svg>' +
                    '<svg class="ctpl-sidebar-pvz" width="25" height="32" viewBox="0 0 25 32" fill="none" x' +
                    'mlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_2112:7511)"><path d="M12.3 ' +
                    '0.000136724C9.99419 0.00527084 7.736 0.657024 5.78204 1.88135C3.82807 3.10568 2.25666 ' +
                    '4.85349 1.24633 6.92622C0.236006 8.99895 -0.172727 11.3135 0.066568 13.6069C0.305863 1' +
                    '5.9003 1.18359 18.0806 2.60004 19.9001L12.3 32.0001L21.9 19.9001C23.595 17.7303 24.510' +
                    '8 15.0535 24.5 12.3001C24.5209 10.6875 24.2202 9.08695 23.6158 7.59179C23.0113 6.09663' +
                    ' 22.115 4.73685 20.9793 3.59184C19.8436 2.44682 18.4912 1.53949 17.001 0.922819C15.510' +
                    '8 0.306148 13.9128 -0.00751586 12.3 0.000136724Z" fill="#007DD5"/><path d="M19.1111 5H' +
                    '4.88889V6.77778H19.1111V5ZM20 13.8889V12.1111L19.1111 7.66667H4.88889L4 12.1111V13.888' +
                    '9H4.88889V19.2222H13.7778V13.8889H17.3333V19.2222H19.1111V13.8889H20ZM12 17.4444H6.666' +
                    '67V13.8889H12V17.4444Z" fill="white"/></g><defs><clipPath id="clip0_2112:7511"><rect w' +
                    'idth="24.5013" height="32" fill="white"/></clipPath></defs></svg>' +
                    '<svg class="ctpl-sidebar-terminal"' +
                    ' width="26" height="32" viewBox="0 0 26 32" fill="none" xmlns="http:/' +
                    '/www.w3.org/2000/svg"> <g clip-path="url(#clip0)"> <path d="M13.0493 0C10.7435 0.00513' +
                    '411 8.48527 0.656888 6.5313 1.88121C4.57734 3.10554 3.00592 4.85335 1.9956 6.92608C0.9' +
                    '85274 8.99881 0.57654 11.3134 0.815836 13.6068C1.05513 15.9002 1.93286 18.0805 3.34931' +
                    ' 19.9L13.0493 32V0Z" fill="#007DD5"></path> <path d="M13.0493 32L22.6493 19.9C24.3443 ' +
                    '17.7302 25.26 15.0533 25.2493 12.3C25.2701 10.6874 24.9695 9.08683 24.365 7.59167C23.7' +
                    '606 6.09651 22.8643 4.73673 21.7286 3.59171C20.5929 2.44669 19.2405 1.53937 17.7503 0.' +
                    '922697C16.2601 0.306026 14.662 -0.00763793 13.0493 1.46533e-05V32Z" fill="#007DD5"></p' +
                    'ath> <path d="M19.5 5H6.5C5.67156 5 5 5.67156 5 6.5V17.5C5 18.3284 5.67156 19 6.5 19H1' +
                    '9.5C20.3284 19 21 18.3284 21 17.5V6.5C21 5.67156 20.3284 5 19.5 5ZM10 18H6.5C6.22384 1' +
                    '8 6 17.7762 6 17.5V15H10V18ZM10 14H6V11H10V14ZM10 10H6V7H10V10ZM15 18H11V15H15V18ZM15 ' +
                    '14H11V11H15V14ZM15 10H11V7H15V10ZM20 15V17.5C20 17.7762 19.7762 18 19.5 18H16V15H20ZM2' +
                    '0 14H16V11H20V14ZM20 10H16V7H20V10Z" fill="white"></path> </g> <defs> <clipPath id="cl' +
                    'ip0"> <rect width="24.5013" height="32" fill="white" transform="translate(0.749268)"><' +
                    '/rect> </clipPath> </defs> </svg>'
                ,
                sidebar_filter: '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://' +
                    'www.w3.org/2000/svg"> <rect width="32" height="32" rx="16" fill="#007DD5"></rect> <pat' +
                    'h d="M20.7059 16.1071H21.8824V17.4582H20.7059V16.1071Z" fill="white"></path> <path d="' +
                    'M13.0588 15.2063V14.3055H14.2353V15.2063H15.4118V14.3055H16.5882V15.2063H21.2941V8H17.' +
                    '7647V10.252H14.2353V8H10.7059V15.2063H13.0588Z" fill="white"></path> <path d="M15.4118' +
                    ' 8H16.5882V9.35118H15.4118V8Z" fill="white"></path> <path d="M23.0588 18.359H19.5294V1' +
                    '6.1071H16.5882V23.3133H18.9412V22.4126H20.1176V23.3133H21.2941V22.4126H22.4706V23.3133' +
                    'H26V16.1071H23.0588V18.359Z" fill="white"></path> <path d="M10.1176 16.1071H11.2941V17' +
                    '.4582H10.1176V16.1071Z" fill="white"></path> <path d="M12.4706 16.1071V18.359H8.94118V' +
                    '16.1071H6V23.3133H7.76471V22.4126H8.94118V23.3133H10.1176V22.4126H11.2941V23.3133H15.4' +
                    '118V16.1071H12.4706Z" fill="white"></path> </svg> <span class="ctpt-widget__sidebar-bu' +
                    'tton-setting-icon"> <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns=' +
                    '"http://www.w3.org/2000/svg"> <path d="M10.4241 6.63315C10.4241 6.45225 10.4844 6.2713' +
                    '4 10.4844 6.03014C10.4844 5.78893 10.4844 5.60803 10.4241 5.42713L11.6904 4.402C11.811' +
                    ' 4.2814 11.811 4.1608 11.7507 4.04019L10.5447 1.92964C10.4844 1.86934 10.3638 1.80904 ' +
                    '10.1829 1.86934L8.67535 2.47236C8.37384 2.23115 8.01203 2.05025 7.65022 1.86934L7.4090' +
                    '2 0.301507C7.46932 0.120603 7.28839 0 7.16781 0H4.75575C4.63513 0 4.45425 0.120603 4.4' +
                    '5425 0.241206L4.21304 1.86934C3.85123 1.98995 3.54973 2.23115 3.18792 2.47236L1.74068 ' +
                    '1.86934C1.55978 1.80904 1.43918 1.86934 1.31855 1.98995L0.112524 4.10049C0.0522223 4.1' +
                    '608 0.112524 4.3417 0.233127 4.4623L1.49946 5.42713C1.49946 5.60803 1.43915 5.78893 1.' +
                    '43915 6.03014C1.43915 6.27134 1.43915 6.45225 1.49946 6.63315L0.233127 7.65828C0.11252' +
                    '4 7.7789 0.112524 7.89948 0.172825 8.02011L1.37885 10.1307C1.43915 10.191 1.55973 10.2' +
                    '513 1.74066 10.191L3.2482 9.58794C3.5497 9.82915 3.91151 10.0101 4.27332 10.191L4.5145' +
                    '3 11.7588C4.51453 11.8794 4.63513 12 4.81603 12H7.22809C7.34871 12 7.52959 11.8794 7.5' +
                    '2959 11.7588L7.7708 10.191C8.13261 10.0101 8.49442 9.82915 8.79592 9.58794L10.3035 10.' +
                    '191C10.4241 10.2513 10.605 10.191 10.6653 10.0703L11.8713 7.95978C11.9316 7.83918 11.9' +
                    '316 7.65828 11.811 7.59798L10.4241 6.63315ZM5.96178 8.14069C4.81606 8.14069 3.85123 7.' +
                    '17587 3.85123 6.03014C3.85123 4.88441 4.81606 3.91959 5.96178 3.91959C7.10751 3.91959 ' +
                    '8.07233 4.88441 8.07233 6.03014C8.07233 7.17587 7.10751 8.14069 5.96178 8.14069Z" fill' +
                    '="white"></path></svg></span>',
                sidebar_logo: '<svg width="28" height="24" viewBox="0 0 28 24" fill="none" xmlns="http://ww' +
                    'w.w3.org/2000/svg"> <path d="M0 12L5.56725 22.152L7.91814 18.0585L4.91228 12H0Z" fill=' +
                    '"#AFDFF6"></path> <path d="M0 12L5.56725 1.84795L7.91814 5.94152L4.91228 12H0Z" fill="' +
                    '#A2ABBB"></path> <path d="M6.5498 0.21051L13.0176 12H17.7662L13.5907 3.64911H18.6319L2' +
                    '3.4153 12H28.0001L21.3865 0.21051H6.5498Z" fill="#20A5E8"></path> <path d="M6.5498 23.' +
                    '7895L13.0176 12H17.7662L13.5907 20.3509H18.6319L23.4153 12H28.0001L21.3865 23.7895H6.5' +
                    '498Z" fill="#089AE2"></path> </svg>',
                delivery_item_close_desc: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmln' +
                    's="http://www.w3.org/2000/svg"><path opacity="0.22" d="M16 1.61716L14.3828 0L7.99997 6' +
                    '.38287L1.61716 0L0 1.61716L6.38287 7.99997L0 14.3828L1.61716 16L7.99997 9.61713L14.382' +
                    '8 16L16 14.3828L9.61713 7.99997L16 1.61716Z" fill="black"></path></svg>',
                courier_variant_different_star_template: '<svg class="" width="12" height="11" viewBox="0 0' +
                    ' 12 11" fill="none" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="#UNIC' +
                    'AL_ID#"><stop offset="#PERCENT_1#" stop-color="#FF9600"></stop><stop offset="#PERCENT_' +
                    '2#" stop-color="#D0D0D0" stop-opacity="1"></stop></linearGradient></defs><path d="M6 8' +
                    '.67519L9.399 10.725L8.49974 6.85908L11.5 4.25922L7.54496 3.91986L6 0.275024L4.45506 3.' +
                    '91986L0.5 4.25922L3.50026 6.85908L2.601 10.725L6 8.67519Z" fill="url(##UNICAL_ID#)"></pa' +
                    'th></svg>',
                zoom_image: '<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M8.333,16.667C8.333,12.064 12.064,8.333 16.667,8.333C21.269,8.333 25,12.064 25,16.667C25,18.968 24.069,21.049 22.559,22.559C21.049,24.069 18.968,25 16.667,25C12.064,25 8.333,21.269 8.333,16.667ZM16.667,5C10.223,5 5,10.223 5,16.667C5,23.11 10.223,28.333 16.667,28.333C19.287,28.333 21.706,27.468 23.654,26.011L32.155,34.512C32.806,35.163 33.861,35.163 34.512,34.512C35.163,33.861 35.163,32.806 34.512,32.155L26.011,23.654C27.468,21.706 28.333,19.287 28.333,16.667C28.333,10.223 23.11,5 16.667,5Z" fill="#FFFFFF"/></svg>',

            };
            /**
             * @type {{getSelect: (function(): *), getTime: (function(*=): string), getHowToFindTitle: (function(): *), getInputSvg: (function(): string), getAddressTitle: (function(): *), getPhoneTitle: (function(): *), getWorkTitle: (function(): *), getPrice: (function(*=): string|string), getPriceTitle: (function(): *)}}
             */
            self.DetailGetter = {
                getTypeName: (type = '2') => {
                    return this.getUiText('panel_info_type_' + type);
                },
                getPriceTitle: () => this.getUiText('detail_price_title'),
                getAddressTitle: () => this.getUiText('detail_address_title'),
                getWorkTitle: () => this.getUiText('detail_work_title'),
                getPhoneTitle: () => this.getUiText('detail_phone_title'),
                getHowToFindTitle: () => this.getUiText('detail_how_to_find_title'),
                getSelect: () => this.getUiText('detail_select'),
                getTime: (time) => this.getPvzItemTime(time),
                getPrice: (price) => this.getDeliveryVariantItemPrice(price),
                getInputSvg: () => {return '<svg width="13" height="20" viewBox="0 0 13 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path class="path" d="M12.35 17.6333L4.71667 10L12.35 2.35L10 0L0 10L10 20L12.35 17.6333Z"></path></svg>'}
            };

            /**
             * @type {{getFilterPayCard: (function(): *), getFilterTypeAll: (function(): *), getFilterTitle: (function(): *), getFilterPayAll: (function(): *), getFilterPayCache: (function(): *), getFilterTypePvz: (function(): *), getPvzTitle: (function(): *), getFilterTypeTerminal: (function(): *)}}
             */
            self.PanelInfoGetter = {
                getPvzTitle: () => this.getUiText('panel_info_pvz'),
                getFilterPayAll: () => this.getUiText('panel_info_pay_all'),
                getFilterPayCard: () => this.getUiText('panel_info_pay_card'),
                getFilterPayCache: () => this.getUiText('panel_info_pay_cash'),
                getFilterTypeAll: () => this.getUiText('panel_info_placeholder_all'),
                getFilterTypePvz: () => this.getUiText('panel_info_placeholder_pvz'),
                getFilterTypeTerminal: () => this.getUiText('panel_info_placeholder_terminal'),
                getFilterTitle: () => this.getUiText('panel_info_filter'),
            };
            /**
             * @type {{getCourierTitle: (function(): *), getPvzTitle: (function(): *)}}
             */
            self.MapSelectorGetter = {
                getCourierTitle: () => this.getUiText('map_selector_courier'),
                getPvzTitle: () => this.getUiText('map_selector_pvz'),
            };
            /**
             * @type {{getLink: (function(): *), getSvg: (function(): *), getButtonTitle: (function(): *), getSvgLogo: (function(): *)}}
             */
            self.MapSearch = {
                getSvg: () => this.getSvgByCode('map_search'),
                getSvgLogo: () => this.getSvgByCode('map_search_logo'),
                getButtonTitle: () => this.getUiText('map_search_button'),
                getLink: () => this.getLink(),
                getMapSearchPlaceholder: () => this.getUiText('map_search_placeholder'),
            };
            /**
             * @type {{getTypeSvg: (function(): *), getFilterSvg: (function(): *), getLogoSvg: (function(): *), getPaySvg: (function(): *), getPvzSvg: (function(): *)}}
             */
            self.SidebarGetter = {
                getPvzSvg: () => this.getSvgByCode('sidebar_pvz'),
                getPaySvg: () => this.getSvgByCode('sidebar_pay'),
                getTypeSvg: () => this.getSvgByCode('sidebar_type'),
                getFilterSvg: () => this.getSvgByCode('sidebar_filter'),
                getLogoSvg: () => this.getSvgByCode('sidebar_logo'),
                getLink: () => this.getLink(),
            };
        }
        getRefreshBtnText = () => this.getUiText('refresh_btn_text');
        getApiErrorText = () => this.getUiText('api_error_text');
        getTextByCode = (textCode) => this.getUiText(textCode);
        getModalTitle = () => this.getUiText('modal_title');
        getVariantInTime = (percent) => {
            return this.getUiText('courier_variant_in_time_template').replace('#PERCENT#' , percent)
        };
        makeDifferentStar = (percent, id) => {
            let result = this.getSvgByCode('courier_variant_different_star_template');
            let percent_2 = 100 - percent;
            result = result.replaceAll('#UNICAL_ID#', id);
            result = result.replace('#PERCENT_1#', percent + '%');
            result = result.replace('#PERCENT_2#', percent_2 + '%');
            return result;
        }
        getDetailGetter = () => this.DetailGetter;
        getPvzItemTime = time => {
            let result = '';
            if (time >= 0 && time !== Infinity) {
                result = this.getUiText('pvz_item_time').replace('#time#', time+'');
                result += this.wordByNumber(time, this.getUiText('delivery_variant_day'));
            }
            return result;
        };
        getLoadSpinnerHTML = () => this.getUiText('load_spinner_text');
        getDeliveryVariantItemTimeTitle = () => this.getUiText('delivery_variant_item_time_title');
        getDeliveryVariantItemTimeTitleDate = () => this.getUiText('delivery_variant_item_time_title_date');
        /**
         * @returns {{getTypeSvg: (function(): *), getFilterSvg: (function(): *), getLogoSvg: (function(): *), getPaySvg: (function(): *), getPvzSvg: (function(): *)}}
         */
        getSidebar = () => this.SidebarGetter;
        /**
         * @returns {*|{getLink: (function(): *), getSvg: (function(): *), getButtonTitle: (function(): *), getSvgLogo: (function(): *)}}
         */
        getMapSearch = () => this.MapSearch;
        /**
         * @returns {{getFilterPayCard: (function(): *), getFilterTypeAll: (function(): *), getFilterTitle: (function(): *), getFilterPayAll: (function(): *), getFilterPayCache: (function(): *), getFilterTypePvz: (function(): *), getPvzTitle: (function(): *), getFilterTypeTerminal: (function(): *)}}
         */
        getPanelInfo = () => this.PanelInfoGetter;
        /**
         * @returns {*|{getCourierTitle: (function(): *), getPvzTitle: (function(): *)}}
         */
        getMapSelector = () => this.MapSelectorGetter;

        getPvzButtonSvg = () => this.getSvgByCode('pvz_button');
        getCourierButtonSvg = () => this.getSvgByCode('courier_button');
        getPanelFilterTitle = () => this.getUiText('panel_filter_list_title');
        getFilterCount = (count) => {
            count = count > 0 ? count : '0';
            return this.getUiText('count_template').replace('#COUNT#', count + '');
        };
        getPanelPvzTitle = (count) => {
            count = count > 0 ? count : '0';
            return this.getUiText('panel_pvz_list_title').replace('#COUNT#', count + '');
        };
        getDeliveryVariantFilterPrice = () => this.getUiText('delivery_variant_filter_price');
        getDeliveryVariantFilterRate = () => this.getUiText('delivery_variant_filter_rate');
        getDeliveryVariantFilterSpeed = () => this.getUiText('delivery_variant_filter_speed');
        getDeliveryVariantPvzTitle = () => this.getUiText('delivery_variant_pvz_title');
        getDeliveryVariantCourierTitle = () => this.getUiText('delivery_variant_courier_title');
        getDeliveryVariantContainerTitle = () => this.getUiText('delivery_variant_container_title');
        getBaseSearchSubmitText = () => this.getUiText('base_search_submit_text');
        getBaseSearchDescription = () => this.getUiText('base_search_description');
        getBaseSearchLabelText = () => this.getUiText('base_search_label');
        getSearchLogo = () => this.getSvgByCode('search_logo');
        getLink = () => this.getAsset('link');
        getBaseSearchTitle = () => this.getUiText('base_search_title');
        getDeliveryVariantPriceFrom = (price, currency = 'RUB') => {
            if (price !== null && price !== '' && price !== Infinity && price >= 0 && price !== undefined) {
                let price_text = this.getPriceFormatted(price);
                return this.getUiText('delivery_variant_price_from') + price_text + this.getUiTextCurrency(currency).short;
            } else {
                return '';
            }

        };
        getDeliveryVariantItemPrice = (price, currency = 'RUB') => {
            if (price !== null && price !== '' && price !== Infinity && price >= 0 && price !== undefined) {
                let price_text = this.getPriceFormatted(price);
                price_text += this.getUiTextCurrency(currency).short;
                return price_text;
            } else {
                return '';
            }
        };
        getDeliveryDateMonthDay = (date, addFrom = true) => {
            let DeliveryDate;
            if (typeof date === 'string') {
                DeliveryDate = new Date(date);
            } else if (typeof date === 'number') {
                DeliveryDate = new Date();
                DeliveryDate.setDate(DeliveryDate.getDate() + date);
            } else if (typeof date === 'object' && date instanceof Date) {
                DeliveryDate = date;
            } else {
                return '';
            }
            
            // Вычисляем текущую дату
            const currentDate = new Date();
            currentDate.setHours(0, 0, 0, 0);
            
            // Вычисляем дату доставки (обнуляем время для корректного сравнения)
            const deliveryDateCopy = new Date(DeliveryDate);
            deliveryDateCopy.setHours(0, 0, 0, 0);
            
            // Вычисляем разницу в днях
            const diffTime = deliveryDateCopy - currentDate;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            // Если дата доставки в прошлом или сегодня, используем старый формат
            if (diffDays <= 0) {
                const fromSrt = addFrom ? this.getUiText('variant_day_date') : '';
                return fromSrt + new Intl.DateTimeFormat(
                    'ru-RU',
                    {day: '2-digit', month: 'short'}
                ).format(DeliveryDate);
            }
            
            // Форматируем текущую дату как "06.02.2026"
            const day = String(currentDate.getDate()).padStart(2, '0');
            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
            const year = currentDate.getFullYear();
            const currentDateFormatted = `${day}.${month}.${year}`;
            
            // Форматируем дату доставки как "10 февраля"
            const deliveryDateFormatted = new Intl.DateTimeFormat(
                'ru-RU',
                {day: '2-digit', month: 'short'}
            ).format(DeliveryDate);
            
            // Склоняем слово "день"
            const dayWord = this.wordByNumber(diffDays, [' день', ' дня', ' дней']);
            
            // Формируем итоговую строку
            return `с текущей даты ${currentDateFormatted} ${diffDays}${dayWord}, то есть ${deliveryDateFormatted}`;
        }
        getDeliveryVariantDate = (number = 0) => this.getDynamicWithNumber(number, 'delivery_variant_day');
        getDeliveryVariantItemDate = (number = 0) => this.getDynamicWithNumber(number, 'base_delivery_variant_day');
        getFilterData = (number) => {
            return this.wordByNumber(number, this.getUiText('filter_variant_day')).replace('#TIME#', number);
        };
        getPriceFormatted = price => this.getUiTextByVariable(price).replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
        getSvgByCode = code => {
            if (!this.svg_collection[code]) {
                throw new Error('svg "' + code + '" don`t exist');
            }
            return this.svg_collection[code];
        };
        getUiTextCurrency = (currency = 'RUB') => {
            const currency_ui_text = this.getUiText('currency');
            if (!currency_ui_text[currency]) {
                throw new Error('asset for currency: "' + currency + '" don`t exist');
            }
            return currency_ui_text[currency];
        };
        getDynamicWithNumber = (number = 0, code) => {
            if (number >= 0) {
                return this.getUiTextByVariable(number) + this.wordByNumber(number, this.getUiText(code));
            } else {
                return '';
            }
        };
        getUiText = code => {
            const ui_text = this.getAsset('ui_text');
            if (!ui_text[code]) {
                throw new Error('asset for code: "' + code+ '" don`t exist');
            }
            return ui_text[code];
        };
        getAsset = code => {
            if (!code) {
                throw new Error('require asset code');
            }
            if (!this[this.lang][code]) {
                throw new Error('asset for code: "' + code+ '" don`t exist');
            }
            return this[this.lang][code];
        };
        wordByNumber = (n, text_forms) => {
            n = Math.abs(n) % 100;
            let n1 = n % 10;
            if (n > 10 && n < 20) { return text_forms[2]; }
            if (n1 > 1 && n1 < 5) { return text_forms[1]; }
            if (n1 === 1) { return text_forms[0]; }
            return text_forms[2];
        };
        getUiTextByVariable = string => {
            if (typeof(string) === 'string') {
                return string;
            }
            if (typeof(string) === 'number' && string >= 0 && string !== Infinity) {
                return string + '';
            }
            return '';
        };
    }
    class AjaxEngine {
        constructor(WidgetObject) {
            let self = this;
            self.Params = WidgetObject.getParams();
            self.Widget = WidgetObject;
        }
        getParams = () => this.Params;
        /**
         * @returns {DataCollection}
         */
        getData = () => this.Widget.getData();
        createRate = async (
            term,
            selectedCityValue,
            dadataVariant = false,
            iso = 'ru',
            limit = '1'
        ) => {
            let rateRequestData = new FormData();

            rateRequestData.append('METHOD', 'create_rate');

            const params = {
                location: {
                    term: term,
                    city_name: selectedCityValue,
                    iso: iso,
                    limit: limit,
                },
                sender_contact_data: this.getParams().getSenderContactParams(),
                cargo_data: this.getParams().getCargoData(),
            };
            if ( (typeof (dadataVariant) != 'undefined') && (dadataVariant != false) )
                if ( typeof (dadataVariant.data) != 'undefined' )
                    params.dadata_selected_choice = dadataVariant.data;

            rateRequestData.append('PARAMS', JSON.stringify(params));

            const response = await fetch(this.getParams().getServicePatch(), {
                method: "POST",
                body: rateRequestData
            });
            this.responseHandler(response);

            const CreatedRateObject = await response.json();
            if (CreatedRateObject.error) {
                throw new Error(CreatedRateObject.error);
            }
            if (!CreatedRateObject.key) {
                throw new Error(JSON.stringify(CreatedRateObject, null, 4))
            }
            return CreatedRateObject;
        };
        getTariff = async (tariffId) => {
            let TariffData = new FormData();

            TariffData.append('METHOD', 'get_tariff');
            const params = {
                tariff_id: tariffId,
                pickup_days_shift: this.getParams().getDaysShift(),
            };
            TariffData.append('PARAMS', JSON.stringify(params));

            const responseTariff = await fetch(this.getParams().getServicePatch(), {
                method: "POST",
                body: TariffData
            });
            this.responseHandler(responseTariff);
            const Tariffs = await responseTariff.json();
            if (Tariffs.error) {
                throw new Error(Tariffs.error);
            }
            if (!Tariffs[0]) {
                throw new Error(Tariff.detail);
            }
            let Tariff = Tariffs[0];
            if (this.getParams().hasOnTariffResponse()) {
                this.getParams().getOnTariffResponse()(Tariff, this.Widget);
            }
            this.getData().getTariffs()[tariffId] = Tariff;

            return Tariff;
        }
        getTerminal = async (terminalId) => {
            let TerminalData = new FormData();
            TerminalData.append('METHOD', 'get_terminal');
            const params = {
                terminal_id: terminalId,
            };
            TerminalData.append('PARAMS', JSON.stringify(params));


            const responseTerminal = await fetch(this.getParams().getServicePatch(), {
                method: "POST",
                body: TerminalData
            });
            this.responseHandler(responseTerminal);
            const Terminal = await responseTerminal.json();
            return Terminal.data[0];
        };
        getTerminals = async (RenderPromise) => {
            let TerminalData = new FormData();
            let RequestTerminalData = this.getData().getRateParams();

            TerminalData.append('METHOD', 'get_terminals');

            if (this.Widget.TerminalsPromiceCancel === true) {
                await Promise.reject(new Error('Reject'));
                return;
            }

            let operators = Object.keys(this.getData().getPvzByOperator());
            this.getData().setTerminals([]);
            const pushTerminalData = (Terminals) => {
                for (let i in Terminals.data.data) {
                    const data = Terminals.data.data[i];
                    this.getData().getTerminals().push(data);
                }
            };
            const resortTerminals = () => {
                let Terminals = this.getData().getTerminals();
                Terminals.sort((a,b)=>{
                    if (a.address.toLowerCase() < b.address.toLowerCase()) {
                        return -1;
                    }
                    if (a.address.toLowerCase() > b.address.toLowerCase()) {
                        return 1;
                    }
                    return 0;
                });
                this.getData().setTerminals(Terminals);
            };
            let percent = 0;
            let calcLoad = setInterval(() => {
                percent++;
                if (percent >= 100) {
                    percent = 0;
                }
                this.Widget.getStructure().getLoaderBar().setCss({width: percent+'%'});
            }, 10);

            let operatorFilter = '';
            for (let key in operators) {
                if (!operatorFilter) {
                    operatorFilter = operators[key];
                } else {
                    operatorFilter += ',' + operators[key];
                }
            }
            RequestTerminalData.company = operatorFilter;

            let params = {
                terminal_request_data: RequestTerminalData,
            };
            let filterParams='';
            if (this.Widget.getParams().hasServicesFilter()) filterParams=this.Widget.getParams().getServicesFilter()+',';
            if (this.Widget.getParams().getIsCardFilter()) filterParams+='CARD,';
            if (this.Widget.getParams().getIsCashFilter()) filterParams+='CASH,';
            filterParams = filterParams.slice(0,-1);
            if (filterParams.length>1) params.services_filter = filterParams;
            TerminalData.append('PARAMS', JSON.stringify(params));

            const responseTerminal = await fetch(this.getParams().getServicePatch(), {
                method: "POST",
                body: TerminalData
            });
            this.responseHandler(responseTerminal);
            const Terminals = await responseTerminal.json();
            pushTerminalData(Terminals);
            resortTerminals();

            await RenderPromise(this.getData().getTerminals());

            if (Terminals.data.next && Terminals.data.next > 0) {
                let next = Terminals.data.next;
                while (next) {
                    if (this.Widget.TerminalsPromiceCancel === true) {
                        await Promise.reject(new Error('Reject'));
                        break;
                    }
                    params.page = next;
                    TerminalData.set('PARAMS', JSON.stringify(params))
                    const responseTerminal = await fetch(this.getParams().getServicePatch(), {
                        method: "POST",
                        body: TerminalData
                    });
                    const Terminals = await responseTerminal.json();

                    pushTerminalData(Terminals);
                    resortTerminals();

                    await RenderPromise(this.getData().getTerminals());

                    if (Terminals.data.next > next) {
                        next = Terminals.data.next;
                    } else {
                        next = false;
                    }
                    if (this.getData().getTerminals().length === Terminals.data.count) {
                        next = false
                    }
                }
            }
            clearInterval(calcLoad);
        }
        getRate = async (rate_id, type = 'd2w') => {
            if (!['d2d', 'd2w', 'w2d', 'w2w'].includes(type)) {
                throw new Error('undefined rate type "' + type + '"');
            }
            let data = new FormData();

            data.append('METHOD', 'get_rate');
            const params = {
                shipping_type_filter: type,
                rate_id: rate_id,
                pickup_days_shift: this.getParams().getDaysShift(),
            };

            if (this.Widget.getParams().isNeedInsurance()) {
                params.need_insurance = true;
            }
            if (this.Widget.getParams().getInsuredValue() > 0 || this.Widget.getParams().getInsuredValue() === 0) {
                params.insured_value = this.Widget.getParams().getInsuredValue();
            }

            if (this.Widget.getParams().hasServicesFilter()) {
                params.services_filter = this.Widget.getParams().getServicesFilter();
            }

            data.append('PARAMS', JSON.stringify(params));

            const responseRate = await fetch(this.getParams().getServicePatch(), {
                method: "POST",
                body: data
            });
            this.responseHandler(responseRate);
            let RateJson = await responseRate.json();

            if (this.getParams().hasOnRateResponse()) {
                this.getParams().getOnRateResponse()(RateJson, this.Widget);
            }
            return RateJson;
        };
        getSuggestion = async (requestString, count = 5) => {
            const dadataRequest = await fetch("https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address", {
                method: "POST",
                mode: "cors",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": "Token " + this.getParams().getDaDataToken()
                },
                body: JSON.stringify({query: requestString, count: count})
            });
            this.responseHandler(dadataRequest);
            return await dadataRequest.json();
        };
        getAddressForCity = async (address = '', city = '', settlement = '') => {
            if (!address && city) {
                address = city;
            }
            let params = {
                query: address,
                count: 1,
            };
            if (city) {
                params.locations = {
                    city: city
                };
            }
            if (settlement) {
                params.locations = {
                    settlement: settlement
                };
            }
            const dadataRequest = await fetch("https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address", {
                method: "POST",
                mode: "cors",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": "Token " + this.getParams().getDaDataToken()
                },
                body: JSON.stringify(params)
            });
            this.responseHandler(dadataRequest);

            return await dadataRequest.json();
        };
        responseHandler = (response) => {
            switch (response.status) {
                case 404:
                    throw new Error('R404');
            }
        };
    }
    class ItemCollection {
        set = (id, Item) => {
            if (this.has(id)) {
                this.items[id] = Item;
            } else {
                this.add(Item, id)
            }
        };
        replace = id => {
            delete this.items[id];
            this.ids.filter(item => item !== id);
        };
        items = {};
        ids = [];
        add = (Item, id = false) => {
            let key = id ? id : Object.keys(this.items).length;
            Item.collectionReference = this;
            this.items[key] = Item;
            this.ids.push(key);
        };
        get = id => {
            let Item = false;
            for (let key in this.items) {
                if (key === id) {
                    Item = this.items[key];
                    break;
                }
            }
            return Item;
        };
        has = id => this.ids.includes(id);
        each = callback => {
            this.ids.forEach((id) => {
                callback(this.items[id],id);
            })
        };
    }
    class HtmlItem {
        constructor(name, params = {}, element = false) {
            let self = this;
            let tag = params.tagName ? params.tagName : 'div';
            delete params.tagName;
            self.name = name;
            self.childCollection = new ItemCollection();
            self.element = element !== false ? element :  document.createElement(tag);
            if (element === false) {
                self.setParamRecursive(self.element, params);
            }
            self.Suggest = false;
            self.ShowCallback = (self) => {self.getElement().style.display = 'block';};
            self.HideCallback = (self) => {self.getElement().style.display = 'none';};
            self.data = {};
            self.PlaceMark = {};
            return this;
        }
        getPlaceMark = () => this.PlaceMark;
        setPlaceMark = PlaceMark => {
            this.PlaceMark = PlaceMark;
        };
        getData = () => this.data;
        setData = data => {
            this.data = data;
        };
        setCurrent = () => {
            this.getElement().classList.add('current')
        };
        removeCurrent = () => {
            this.getElement().classList.remove('current')
        };
        setOpen = () => {
            this.getElement().classList.add('open')
        };
        removeOpen = () => {
            this.getElement().classList.remove('open')
        };
        hide = () => {
            this.HideCallback(this);
        };
        show = () => {
            this.ShowCallback(this);
        };
        bindShow = Callback => {
            this.ShowCallback = Callback;
        };
        bindHide = Callback => {
            this.HideCallback = Callback;
        };
        /**
         * @param (name {string})
         * @param (HtmlItem {HtmlItem})
         */
        replaceChild = (name, HtmlItem = false) => {
            let child = this.getChild(name);
            if (HtmlItem === false) {
                child.getElement().remove();
                this.childCollection.replace(name)
            } else {
                child.getElement().replaceWith(HtmlItem.getElement());
                this.childCollection.set(name, HtmlItem)
            }
        };
        /** @return HtmlItem**/
        getChild = name => this.childCollection.get(name);
        getChilds = () => this.childCollection;
        getName = () => this.name;
        /**
         * @param (Suggest {Object}}
         */
        setSuggest = Suggest => {
            this.Suggest = Suggest;
        };
        /** @return Object **/
        getSuggest = () => this.Suggest;
        getElement = () => this.element;
        /**
         * @param (object {{'StyleName' => 'StyleValue'}}
         */
        setCss = (object = {}) => {
            for (let styleName in object) {
                this.getElement().style[styleName] = object[styleName];
            }
        };
        /**
         * @param eventName {string}
         * @param callback {function}
         */
        handleEvent = (eventName, callback) => {
            this.getElement()[eventName] = callback;
        };
        /**
         * @param eventName {string}
         * @param callback {function}
         */
        addEventListener = (eventName, callback) => {
            this.getElement().addEventListener(eventName, callback);
        };
        setParamRecursive = (element, params) => {
            for (let key in params) {
                let value = params[key];
                if (Array.isArray(params[key]) && Array.isArray(value)) {
                    for (let i in value) {
                        element[key].add(value[i]);
                    }
                } else if (Array.isArray(value) || (value !== null && typeof value === 'object')) {
                    this.setParamRecursive(element[key], value);
                } else {
                    element[key] = value;
                }
            }
        };
        /** @return HtmlItem **/
        addChildMultiple = childs => {
            for (let i in childs) {
                if (!childs[i]) {
                    continue;
                }
                this.addChild(childs[i])
            }
            return this;
        };
        /** @return HtmlItem **/
        addChild = child => {
            if (!child) {
                return this;
            }
            let childCollection = this.getChilds();

            if (childCollection.has(child.getName())) {
                throw new Error('child Id duplicate');
            }
            this.element.appendChild(child.getElement());
            childCollection.add(child, child.getName());
            return this;
        };
        removeChilds = () => {
            let childCollection = this.getChilds();

            childCollection.each((item) => {
                item.getElement().remove();
            })
            delete childCollection.items;
            delete childCollection.ids
            childCollection.items = {};
            childCollection.ids = [];
            this.childCollection = childCollection;
            return this;
        };
        appendAsChild = element => {
            element.appendChild(this.getElement());
        };
    }
    class ParamsCollections {
        /**
         * @param {{need_insurance: boolean, insured_value: int, date_type: string, dadata_token: string, delivery_type: string, link: string, only_delivery_type: string, services_filter : string, sender_contact_params: { cityFrom: {city: string, address: string}, zip: number, locality_id: number, door_number: string, iso: string, street: string, name: string, comment: string, company: string, building: string}, onTariffResponse: onTariffResponse, onSelectPvzItem: onSelectPvzItem, only_info: boolean, service_path: string, cargo: {quantity: number, delivery_type: string, length: number, width: number, weight: number, cargo_comment: string, height: number}, courier_type: string, day_shift: number, onSelectCourierItem: params.onSelectCourierItem, onRateResponse: params.onRateResponse, location: {address: string, city: string}}} params
         * @param WidgetObject
         */
        constructor(params, WidgetObject) {
            let self = this;
            self.WidgetObject = WidgetObject;
            if (!params.service_path) {
                throw new Error('`service_path` required');
            }
            if (!params.sender_contact_params.cityFrom) {
                if (!params.sender_contact_params.locality_id) {
                    throw new Error('`sender_contact_params.locality_id required ');
                }
                if (!params.sender_contact_params.zip) {
                    throw new Error('`sender_contact_params.zip required ');
                }
            } else {
                self.cityFrom = params.sender_contact_params.cityFrom;
            }

            if (!params.link) {
                throw new Error('`link` required');
            }
            if (params.only_delivery_type && !['Pvz', 'Courier'].includes(params.only_delivery_type)) {
                throw new Error('`only_delivery_type` required be `Pvz` or `Courier`')
            } else {
                self.onlyOneDeliveryType = !!params.only_delivery_type;
            }

            if (!params.dadata_token) {
                throw new Error('`dadata_token` required')
            }
            self.dayShift = params.day_shift > 0 && params.day_shift < 365 ? params.day_shift : 0;
            self.servicepath = params.service_path;
            if (params.location) {
                self.defaultCity = params.location.city ? params.location.city : false;
                self.defaultAddress = params.location.address ? params.location.address : false;
                self.defaultSettlement = params.location.settlement ? params.location.settlement : false;
            } else {
                self.defaultAddress = false;
                self.defaultCity = false;
                self.defaultSettlement = false;
            }

            self.need_insurance = false;
            self.insured_value = false;
            if (params.need_insurance && params.need_insurance === true) {
                self.need_insurance = true;
            }

            if (params.insured_value && params.insured_value > 0) {
                self.insured_value = params.insured_value;
            }

            self.link = params.link;
            self.dadata_token = params.dadata_token;

            self.onlyCourier = params.only_delivery_type === 'Courier';
            self.onlyPvz = params.only_delivery_type === 'Pvz';

            const typeByApiType = {
                courier_type: {
                    door: 'd2d',
                    warehouse: 'w2d',
                },
                pvz_type: {
                    door: 'd2w',
                    warehouse: 'w2w',
                },
            };
            self.courier_type = ['door', 'warehouse'].includes(params.delivery_type) ? typeByApiType.courier_type[params.delivery_type] : 'd2d';
            self.pvz_type = ['door', 'warehouse'].includes(params.delivery_type) ? typeByApiType.pvz_type[params.delivery_type] : 'd2w';
            self.onlyInfo = params.only_info === true;
            self.dateType = ['date', 'diff'].includes(params.date_type) ? params.date_type : 'diff';

            self.services_filter = false;
            self.has_services_filter = false;
            if (params.services_filter && ['NP', 'COD', 'NP,COD'].includes(params.services_filter)) {
                self.services_filter = params.services_filter;
                self.has_services_filter = true;
            }
            self.iscardfilter = params.filter_card === true;
            self.iscashfilter = params.filter_cash === true;
            self.popup_mode = params.popup_mode === true;

            let handlersNames = [
                'onSelectCourierItem',
                'onSelectPvzItem',
                'onRateResponse',
                'onTariffResponse',
                'onPopupClose',
            ];
            self.onSelectCourierItem = false;
            self.onSelectPvzItem = false;
            self.onRateResponse = false;
            self.onTariffResponse = false;
            self.onPopupClose = false;
            for (let i in handlersNames) {
                let name = handlersNames[i];
                if (typeof(params[name]) === 'function') {
                    this[name] = params[name];
                }
            }
            const cargo_allow_params = ["delivery_type","cargo_comment","height","length", "width","quantity","weight",];
            self.CargoData = {};

            const filterCargoParams = (cargoParams) => {
                let cargoParamsResult = {};
                for (let key in cargoParams) {
                    if (!cargo_allow_params.includes(key)) {
                        continue;
                    }
                    cargoParamsResult[key] = cargoParams[key];
                }
                return cargoParamsResult;
            }

            if (Array.isArray(params.cargo)) {
                self.CargoData = [];
                for (let i in params.cargo) {
                    self.CargoData.push(filterCargoParams(params.cargo[i]));
                }
            } else {
                self.CargoData = filterCargoParams(params.cargo);
            }

            const contact_allow_params = ["locality_id","zip","street","building","door_number","comment","company","name","iso"];
            self.SenderData = {};

            for (let key in params.sender_contact_params) {
                if (!contact_allow_params.includes(key)) {
                    continue;
                }
                self.SenderData[key] = params.sender_contact_params[key];
            }
        }
        getInsuredValue = () => this.insured_value;
        isNeedInsurance = () => this.need_insurance === true;
        isPopupMode = () => this.popup_mode;
        getServicesFilter = () => this.services_filter;
        hasServicesFilter = () => this.has_services_filter;
        setSenderContactDataByAddress = async () => {
            if (!this.cityFrom || (this.SenderData.locality_id && this.SenderData.zip)) {
                return;
            }
            const JsonResult = await this.WidgetObject.getAjaxEngine().getAddressForCity(this.cityFrom.address, this.cityFrom.city)
            this.SenderData.iso = JsonResult.suggestions[0].data.country_iso_code;
            this.SenderData.cityFrom  = JsonResult.suggestions[0].data.postal_code;
        };
        isFullDate = () => this.getDateType() === 'date';
        getDateType = () => this.dateType;
        getDaysShift = () => this.dayShift;
        isOnlyInfo = () => this.onlyInfo;
        getSenderContactParams = () => this.SenderData;
        getCourierType = () => this.courier_type;
        getPvzType = () => this.pvz_type;
        getServicePatch = () => this.servicepath;
        getCargoData = () => this.CargoData;
        getDefaultCity = () => this.defaultCity;
        getDefaultAddress = () => this.defaultAddress;
        getDefaultSettlement =  () => this.defaultSettlement;
        getWidgetId = () => this.link;
        getDaDataToken = () => this.dadata_token;
        isOneDeliveryType = () => this.onlyOneDeliveryType;
        isCourierOnly = () => this.onlyCourier;
        isOnlyPvz = () => this.onlyPvz;
        hasHandlerOnPopupClose = () => this.onPopupClose !== false;
        getOnPopupCloseHandler = () => this.onPopupClose;
        hasHandlerOnSelectCourierItem = () => this.onSelectCourierItem !== false;
        getOnSelectCourierItemHandler = () => this.onSelectCourierItem;
        hasHandlerOnSelectPvzItem = () => this.onSelectPvzItem !== false;
        getOnSelectPvzItemHandler = () => this.onSelectPvzItem;
        getOnRateResponse = () => this.onRateResponse;
        hasOnRateResponse = () => this.onRateResponse !== false;
        getOnTariffResponse = () => this.onTariffResponse;
        hasOnTariffResponse = () => this.onTariffResponse !== false;
        getIsCardFilter = () => this.iscardfilter;
        getIsCashFilter = () => this.iscashfilter;
    }
    class StateLoader{
        constructor(WidgetObject, MapCover) {
            let self = this;
            self.WidgetObject = WidgetObject;
            self.MapCover = MapCover;
            self.states = [];
            self.isReady = false;
        }
        load = onLoadCallback => {
            this.onLoadCallback = onLoadCallback;
            if (document.readyState !== 'complete') {
                addEventListener("DOMContentLoaded", this.onDocumentReady.bind(this));
            } else {
                this.onDocumentReady();
            }

            this.MapCover.onReady = () => {
                this.states.push('YandexMapApiReady');
                this.stateChanged();
            };
            this.MapCover.load();
        };
        onDocumentReady = () => {
            this.states.push('documentReady');
            this.stateChanged();
        };
        stateChanged = () => {
            if (!this.isReady
                && this.states.includes('documentReady')
                && this.states.includes('YandexMapApiReady')
            ) {
                this.isReady = true;
                this.onLoadCallback();
            }
        };
    }
    class Structure{
        constructor(WidgetObject) {
            let self = this;
            self.WidgetObject = WidgetObject;
            self.Structure = {
                Container: {
                    domElement: false,
                    getDomElement: function () {
                        return this.domElement;
                    }
                },
                Loader: {},
                BaseSearch: {},
                VariantContainer: {},
                MapSearch: {},
                MapTypeSelector: {},
                PanelInfo: {},
                Sidebar: {},
                Panel: {},
                MapBox: {},
                LoaderBar: {},
                DetailPanel: {},
                DetailTariff: false,
                Description: {},
            };
            self.SideBarButtons = {
                getPvz: () => this.SideBarButtons.PvzList,
                getPay: () => this.SideBarButtons.PayFilter,
                getType: () => this.SideBarButtons.TypeFilter,
                getFilter: () => this.SideBarButtons.OperatorFilter,
                PvzList: null,
                PayFilter: null,
                TypeFilter: null,
                OperatorFilter: null,
            };
        }
        setDetailTariff = (DetailTariff) => {this.Structure.DetailTariff = DetailTariff;}
        getDetailTariff = () => this.Structure.DetailTariff;
        getSideBarButtons = () => this.SideBarButtons;
        createAll = () => {
            this.createLoaderBar();
            this.createBaseSearch();
            this.createVariantContainer();
            this.makeDescription();
            this.createMapSearch();
            this.createMapBox();
            this.createMapTypeSelector();
            this.createPanelInfo();
            this.createPanel();
            this.createSidebar();
        };
        createLoaderBar = () => {
            this.Structure.LoaderBar = new HtmlItem('LoaderBar', {
                classList: ['ctpt-delivery-widget-loader-bar']
            });
            this.getContainer().getDomElement().appendChild(this.getLoaderBar().getElement());
        };
        getLoaderBar = () => this.Structure.LoaderBar;
        createMapBox = () => {
            this.Structure.MapBox = new HtmlItem('MapBox',{
                classList: ['ctpt-widget__map_widget']
            });
            this.getMapBox().setCss({width: '960px', height: '100%'});
            this.getContainer().getDomElement().appendChild(this.getMapBox().getElement());
        }
        getMapBox = () => this.Structure.MapBox;
        makeDescription = () => {
            this.Structure.Description = (new HtmlItem('InfoBox', {
                tagName: 'span',
                classList: ['ctpt-widget__info-hint-text',]
            })).addChildMultiple([
                (new HtmlItem('CloseButton', {
                    classList: ['ctpt-widget__close']
                })).addChild(
                    (new HtmlItem('CloseIcon', {
                        classList: ['ctpt-widget__delivery-type__info_close_svg']
                    }))
                ),
                (new HtmlItem('InfoTextBox',
                    {classList: ['ctpt-widget__info-hide-cont', 'ctpt-widget__scroll']}
                )).addChildMultiple([
                    (new HtmlItem('Title', {
                        innerHTML: '',
                        classList: ['ctpt-widget__info-hide-title']
                    })),
                    (new HtmlItem('Description', {
                        innerHTML: '',
                        classList: ['ctpt-widget__info-hide-desc']
                    })),
                ]),
            ]);
            this.Structure.Description.setData({
                idInfo: false,
            })
            this.Structure.Description.hide();
            this.Structure.Description.hideDesc = () => {
                setTimeout(() => {
                    this.Structure.Description.setData({
                        idInfo: false,
                    });
                }, 100);
                this.Structure.Description.hide();
                this.Structure.Description.getElement().classList.remove('is-visible');
            }
            this.Structure.Description.getChild('CloseButton').handleEvent('onclick', this.Structure.Description.hideDesc);

            let ScrollContainer = this.getVariantContainer().getChild('ItemsBaseContainer').getElement();
            ScrollContainer.addEventListener('scroll', this.Structure.Description.hideDesc);
            window.addEventListener('scroll', this.Structure.Description.hideDesc);
            window.addEventListener('resize', this.Structure.Description.hideDesc);
            document.addEventListener('click', (e) => {
                if (e.target.id !== this.Structure.Description.getData().idInfo
                    && e.target.classList.contains('ctpt-widget__delivery-type__info_icon')) {
                } else {
                    this.Structure.Description.hideDesc();
                }

            });

            this.getContainer().getDomElement().appendChild(this.getDescription().getElement());
        }
        getDescription = () => this.Structure.Description;
        openDescription = (params) => {
            const Description = this.getDescription();
            Description.getChild('InfoTextBox').getChild('Title').getElement().innerHTML = params.name;
            Description.getChild('InfoTextBox').getChild('Description').getElement().innerHTML = params.desc;
            let DescHeight = 'max-content',TextHeight = 'max-content';
            if (params.desc.length > 355) {
                DescHeight = '270px';
                TextHeight = '225px';
            }
            const pos = params.targetElement.getElement().getBoundingClientRect();
            Description.setCss({
                top:  pos.top + 'px',
                left:  pos.left + 'px',
                height: DescHeight
            });
            Description.getChild('InfoTextBox').setCss({
                height: TextHeight
            });
            Description.getChild()
            Description.show();
            Description.getElement().classList.add('is-visible');
            setTimeout(() => {
                Description.setData({
                    idInfo: params.targetElement.getElement().id,
                });
            }, 100);
        };
        createVariantItem = (Item) => {
            let Assets = this.WidgetObject.getAssets();

            let itemLogo;
            if (this.WidgetObject.getData().getOperatorsIcons()[Item.operator]) {
                const IconData = this.WidgetObject.getData().getOperatorsIcons()[Item.operator];
                itemLogo = (new HtmlItem('Logo', {
                    tagName: 'img',
                    alt: 'logo',
                    src: IconData.icon,
                }));
            } else {
                itemLogo = (new HtmlItem('Logo', {
                    classList: ['ctpt-widget__delivery-type__info___default_logo']
                }));
            }
            let price = Item.price;
            let time = '';

            if (price !== null && price !== '') {
                price = Assets.getDeliveryVariantItemPrice(price)
            } else {
                price = '';
            }
            let TimeTitle = '';
            if (Item.transit_days !== null && Item.transit_days !== '' && Item.transit_days > 0) {
                if (this.WidgetObject.getParams().isFullDate()) {
                    TimeTitle = Assets.getDeliveryVariantItemTimeTitleDate();
                    time = Assets.getDeliveryDateMonthDay(Item[ApiObjectKeys.delivery_date]);
                } else {
                    TimeTitle = Assets.getDeliveryVariantItemTimeTitle();
                    time = Assets.getDeliveryVariantDate(Item.transit_days);
                }
            } else {
                Item.transit_days = Infinity;
            }
            let desc = Item.rate_description ? Item.rate_description : '';
            let rate = Item.rate ? Item.rate : '';

            let operator_rating_is_exist = Item[ApiObjectKeys.rating] > 0;

            Item[ApiObjectKeys.rating] = Item[ApiObjectKeys.rating] >= 0 ? Item[ApiObjectKeys.rating] : 0;

            let delivery_success_rating = Item[ApiObjectKeys.delivery_rating] >= 0 ? Item[ApiObjectKeys.delivery_rating] : false;

            let RatingBlock = (new HtmlItem('Rating', {
                tagName: 'span',
                classList: ['ctpt-widget__delivery-rating']
            }));

            if (operator_rating_is_exist) {
                let roundedRating = Math.round(Item[ApiObjectKeys.rating]);
                let stars = [1,2,3,4,5];
                for (let i in stars) {
                    const difference = Item[ApiObjectKeys.rating] - stars[i];
                    const nonFullStar = difference < 0 && difference > -1;
                    if (nonFullStar) {
                        let  percent = 100 - Math.abs(Math.ceil((difference)*100));
                        if (0 <= percent && percent <= 25) {
                            let classList = ['ctpt-widget__delivery-rating_star'];
                            RatingBlock.addChild(new HtmlItem('Star' + stars[i], {
                                tagName: 'span',
                                classList: classList,
                            }));
                        } else if (26 <= percent && percent <= 75) {
                            RatingBlock.addChild(new HtmlItem('Star' + stars[i], {
                                tagName: 'span',
                                classList: ['ctpt-widget__delivery-rating_star_percent'],
                                innerHTML: Assets.makeDifferentStar(50,'star_for' + Item.id),
                            }));
                        } else if (75 <= percent && percent <= 99) {
                            let classList = ['ctpt-widget__delivery-rating_star', 'current'];
                            RatingBlock.addChild(new HtmlItem('Star' + stars[i], {
                                tagName: 'span',
                                classList: classList,
                            }));
                        }
                    } else {
                        let classList = ['ctpt-widget__delivery-rating_star'];
                        if (stars[i] <= roundedRating) {
                            classList.push('current');
                        }
                        RatingBlock.addChild(new HtmlItem('Star' + stars[i], {
                            tagName: 'span',
                            classList: classList,
                        }));
                    }

                }
            }
            let InTomeBox = (new HtmlItem('Rate', {
                tagName: 'span',
                classList: ['ctpt-widget__delivery-type__info-term-info']
            }));
            if (delivery_success_rating) {
                InTomeBox.addChildMultiple([
                   new HtmlItem('Clock', {
                       tagName: 'span',
                       classList: ['ctpt-widget__delivery-type__info-term-info_clock']
                   }),
                   new HtmlItem('InTimeText', {
                       tagName: 'span',
                       classList: ['ctpt-widget__delivery-type__info-term-info_clock_text'],
                       innerHTML: Assets.getVariantInTime(delivery_success_rating),
                   })
                ]);
            }

            /**
             * @type {HtmlItem}
             */
            let HtmlItemElement = (new HtmlItem(Item.id,
                {classList: ['ctpt-widget__delivery-type__list-item']}
            )).addChildMultiple([
                (new HtmlItem('InfoContainer',
                    {classList: ['ctpt-widget__delivery-type__logo']}
                )).addChildMultiple([
                    itemLogo,
                    RatingBlock,
                    (new HtmlItem('InfoContainer', {
                        tagName: 'span',
                        classList: ['ctpt-widget__delivery-info-hint']
                    })).addChildMultiple([
                        (new HtmlItem('InfoButton', {
                                id: 'info' + Item.id,
                                tagName: 'span',
                                classList: ['ctpt-widget__delivery-type__info_icon']
                            }
                        )),
                    ]),
                ]),
                (new HtmlItem('BaseInfoContainer',
                    {classList: ['ctpt-widget__delivery-type__info']}
                )).addChildMultiple([
                    (new HtmlItem('TimeTitle', {
                        tagName: 'span',
                        innerHTML: TimeTitle,
                        classList: ['ctpt-widget__delivery-type__info-term']
                    })),
                    (new HtmlItem('TimeValue', {
                        tagName: 'span',
                        innerHTML: time,
                        classList: ['ctpt-widget__delivery-type__info-days']
                    })),
                    InTomeBox,
                ]),
                (new HtmlItem('PriceInfo',
                    {classList: ['ctpt-widget__delivery-type__price-wrap']}
                )).addChild(
                    this.WidgetObject.getParams().isOnlyInfo() ? new HtmlItem('PriceButton', {innerHTML: price, classList: ['ctp_courier_price_info']}) : (new HtmlItem('PriceButton', {
                            innerHTML: price,
                            classList: ['ctpt-widget__button']
                    }))
                ),
            ]);

            HtmlItemElement.setData(Item);
            let InfoBlock = HtmlItemElement.getChild('InfoContainer').getChild('InfoContainer');

            InfoBlock.getChild('InfoButton').handleEvent('onclick', () => {
                this.openDescription({
                    targetElement: InfoBlock.getChild('InfoButton'),
                    name: rate,
                    desc: desc,
                })
            });

            return HtmlItemElement;
        };
        /**
         * @param ItemParams
         * @param handlerSelectVariant
         * @returns {HtmlItem}
         */
        createVariantTimeSelector = (ItemParams, handlerSelectVariant) => {
            const Assets = this.WidgetObject.getAssets();
            let CloseButton = new HtmlItem('CloseButton', {
                classList: ['ctpt-widget__close']
            });
            CloseButton.addChild(new HtmlItem('SvgSpan', {tagName: 'span'}));
            let SliderItems = [];
            let VariantsItems = [];
            let itemWidth = 65;
            let SelectedPos = 0;

            const getSlideByVector = (FromKey, vector = 0) => {
                let nextKey;
                switch (vector) {
                    case 1:
                        nextKey = SliderItems[FromKey + vector] ? FromKey + vector : 0;
                        if (SliderItems[nextKey].getData().disabled) {
                            nextKey = getSlideByVector(nextKey, vector)
                        }
                        break;
                    case -1:
                        nextKey = SliderItems[FromKey + vector] ? FromKey + vector : SliderItems.length - 1;
                        if (SliderItems[nextKey].getData().disabled) {
                            nextKey = getSlideByVector(nextKey, vector)
                        }
                        break;
                    default:
                        nextKey = SliderItems[FromKey].getData().disabled ? false : FromKey;
                        break
                }
                return nextKey;
            }

            const onclickSliderItem = (FromKey, vector) => {
                if (typeof FromKey === 'object') {
                    FromKey = SelectedPos;
                }
                const clientWidth = SliderWrap.getElement().clientWidth;
                const countVisibleFloat = clientWidth/itemWidth;
                const countVisible = Math.round(countVisibleFloat);
                const floatVisible = +countVisible - +countVisibleFloat;
                const diffLast = +itemWidth * floatVisible;
                let currentPos;
                SelectedPos = getSlideByVector(FromKey, vector);
                if (SelectedPos === false) {
                    return;
                }
                let Slide = SliderItems[SelectedPos];
                let diff = 0;
                if (SelectedPos > (SliderItems.length - countVisible)) {
                    currentPos = SliderItems.length - countVisible;
                    PrevButton.show();
                    NextButton.hide();
                    diff = diffLast;
                } else if ((SelectedPos - (countVisible/2)) > 0) {
                    currentPos = SelectedPos - (countVisible/2);
                    PrevButton.show();
                    NextButton.show();
                    diff = itemWidth/2;
                } else {
                    currentPos = 0;
                    PrevButton.hide();
                    NextButton.show();
                }

                let translate = (currentPos * itemWidth) + diff;
                SliderList.setCss({transform: 'translateX(-' + translate + 'px)'});
                SliderList.getChilds().each((Item) => {
                    Item.getElement().classList.remove('current');
                });
                Slide.getElement().classList.add('current');
                Variant.getChild('VariantDates').getElement().innerHTML = Slide.getData().time_string;
                Variant.getChild('PriceButton').setData(Slide.getData());
            }
            for (let date in ItemParams['time-slots']) {
                const timeString = ItemParams['time-slots'][date];
                let CurrentDate = new Date(timeString['delivery_date']);
                let NowData = new Date();
                let TomorrowData = new Date();
                TomorrowData.setDate(NowData.getDate() + 1);

                const timeText = timeString['delivery_time'][0] ? timeString['delivery_time'][0] : '';

                const isCurrentMonth = NowData.getFullYear() === CurrentDate.getFullYear()
                    && NowData.getMonth() === CurrentDate.getMonth();
                let weekDayStr = '';
                // is today
                if (NowData.getDate() === CurrentDate.getDate() && isCurrentMonth) {
                    weekDayStr = 'Сегодня';
                    // is tomorrow
                } else if (TomorrowData.getDate() === CurrentDate.getDate() && isCurrentMonth) {
                    // other days
                } else {
                    weekDayStr = new Intl.DateTimeFormat(
                        'ru-RU',
                        {weekday: 'short'}
                    ).format(CurrentDate).toUpperCase();
                }

                let dateMonthDay = new Intl.DateTimeFormat(
                    'ru-RU',
                    {day: '2-digit', month: 'short'}
                ).format(CurrentDate);
                let slideClassList = ['ctpt-widget__modal-date'];
                if (!timeText) {
                    slideClassList.push('disabled')
                }

                let Slide = (new HtmlItem(date,
                    {classList: slideClassList}
                )).addChildMultiple([
                    (new HtmlItem('WeekDate', {
                        innerHTML: weekDayStr,
                        classList: ['ctpt-widget__modal-day-title']
                    })),
                    (new HtmlItem('DayDate', {
                        innerHTML: dateMonthDay,
                        classList: ['ctpt-widget__modal-date-title']
                    })),
                ]);
                Slide.setData({
                    variant: ItemParams.Variant,
                    time_string: timeText,
                    selected: timeString,
                    disabled: !timeText,
                    key: SliderItems.length
                });
                SliderItems.push(Slide);

                Slide.handleEvent('onclick', () => {
                    onclickSliderItem(Slide.getData().key);
                });
            }

            let price = Assets.getDeliveryVariantItemPrice(ItemParams.Variant.price);
            let Variant = (new HtmlItem('VariantItem',
                {classList: ['ctpt-widget__modal-item']}
            ));
            Variant.addChildMultiple([
                (new HtmlItem('VariantDates', {
                    innerHTML: '',
                    classList: ['ctpt-widget__modal-time']
                })),
                this.WidgetObject.getParams().isOnlyInfo() ? new HtmlItem('PriceButton', {innerHTML: price, classList: ['ctp_courier_price_info']}): (new HtmlItem('PriceButton',
                    {innerHTML: price, classList: ['ctpt-widget__button']}
                )),
            ])
            VariantsItems.push(Variant);

            let PrevButton = new HtmlItem('PrevButton',
                {
                    classList: ['swiper-button', 'swiper-button-prev', 'ctp_prev_slide_button']
                }
            );
            let NextButton = new HtmlItem('NextButton',
                {
                    classList: ['swiper-button', 'swiper-button-next', 'ctp_next_slide_button']
                }
            );

            NextButton.handleEvent('onclick', () => {
                onclickSliderItem(SelectedPos, 1);
            });
            PrevButton.handleEvent('onclick', () => {
                onclickSliderItem(SelectedPos, -1);
            });
            if (!this.WidgetObject.getParams().isOnlyInfo()) {
                Variant.getChild('PriceButton').handleEvent('onclick', (e) => {
                    handlerSelectVariant(e, Variant.getChild('PriceButton'));
                });
            }

            let SliderList = (new HtmlItem('SliderList',
                {classList: ['ctpt-widget__modal-dates-slider', 's-notransition']}
            ));
            SliderList.addChildMultiple(SliderItems);
            SliderItems[0].getElement().classList.add('current');

            let SliderWrap = (new HtmlItem('SliderCover',
                {classList: ['ctpt-widget__modal-content-wrap']}
            ));
            SliderWrap.addChildMultiple([
                SliderList,
            ])
            let ModalPopup = (new HtmlItem('ModalPopup',
                {classList: ['ctpt-widget__modal', 'is-visible']}
            )).addChildMultiple([
                (new HtmlItem('Overlay',
                    {classList: ['ctpt-widget__modal-overlay']}
                )),
                (new HtmlItem('Container',
                    {classList: ['ctpt-widget__modal-wrapper', 'ctpt-widget__modal-transition']}
                )).addChildMultiple([
                    (new HtmlItem('HeaderBlock',
                        {classList: ['ctpt-widget__modal-header']}
                    )).addChild(
                        (new HtmlItem('Header',
                            {classList: ['ctpt-widget__primary-title']}
                        )).addChildMultiple([
                            (new HtmlItem('Title', {
                                tagName: 'span',
                                innerHTML: Assets.getModalTitle(),
                                classList: ['ctpt_choose']
                            })),
                            CloseButton
                        ])
                    ),
                    (new HtmlItem('ContentCover',
                        {classList: ['ctpt-widget__modal-body']}
                    )).addChild((new HtmlItem('ContentBlock',
                        {classList: ['ctpt-widget__modal-content']}
                    )).addChildMultiple([
                        SliderWrap,
                        PrevButton,
                        NextButton,
                        (new HtmlItem('VariantsBlock',
                            {classList: ['ctpt-widget__modal-timetable-wrap']}
                        )).addChildMultiple(VariantsItems),
                    ])),
                ]),
            ]);
            ModalPopup.getChild('Overlay').handleEvent('onclick', () => {
                ModalPopup.getElement().classList.remove('is-visible');
                window.removeEventListener('resize', onclickSliderItem);
            });
            CloseButton.handleEvent('onclick', () => {
                ModalPopup.getElement().classList.remove('is-visible');
                window.removeEventListener('resize', onclickSliderItem);
            });

            window.addEventListener('resize', onclickSliderItem);
            onclickSliderItem(SliderItems.length, 1);

            return ModalPopup;
        };
        /**
         * @param name
         * @param title
         * @param price
         * @param time
         * @returns {HtmlItem}
         */
        createVariant = (name, title, price, time, addstyle='') => {
            let Assets = this.WidgetObject.getAssets();

            if (price !== null && price !== '' && price !== Infinity && price >= 0 && price !== undefined) {
                price = Assets.getDeliveryVariantPriceFrom(price)
            } else {
                price = '';
            }
            if (time !== null && time !== '' && time !== Infinity && time >= 0 && time !== undefined) {
                time = Assets.getDeliveryVariantItemDate(time);
            } else {
                time = '';
            }
            let classLists = ['ctpt-widget__delivery-type__item'];
            if (addstyle!='') classLists.push(addstyle);
            return (new HtmlItem(name,
                {classList: classLists,}
            )).addChild(
                new HtmlItem('DlvTypeItmCont',
                {tagName: 'div'}
                ).addChildMultiple([
                    (new HtmlItem('TitleContainer',
                        {classList: ['ctpt-widget__delivery-type__item-title',],}
                    )).addChild(
                        (new HtmlItem('Title',
                            {tagName: 'span', innerHTML: title,}
                        ))),
                    (new HtmlItem('DataContainer',
                        {classList: ['ctpt-widget__delivery-type__item-details',],}
                    )).addChildMultiple([
                        (new HtmlItem('Price', {
                            tagName: 'span',
                            innerHTML: price,
                            classList: ['ctpt-widget__price',],
                        })),
                        (new HtmlItem('Time', {
                            tagName: 'span',
                            innerHTML: time,
                            classList: ['ctpt-widget__days',],
                        })),]
                    ),
                ])
            );
        }
        /**
         * @param params
         * @returns {HtmlItem}
         */
        makeFilterItem = params => {
            let Assets = this.WidgetObject.getAssets();
            let operator = params.operator;

            let count = Assets.getFilterCount(params.count);
            let price = '';
            if (params.price >= 0 && params.price !== Infinity) {
                price = Assets.getDeliveryVariantItemPrice(params.price);
                if (params.max_price > params.price) {
                    price += '. - ' + Assets.getDeliveryVariantItemPrice(params.max_price);
                }
            }
            let date = '';
            if (params.time > 0 && params.time !== Infinity) {
                date = ', ' + Assets.getFilterData(params.time);
            }
            const operator_icon_data = this.WidgetObject.getData().getOperatorsIcons()[operator];
            const operator_name = operator_icon_data.operator_display ? operator_icon_data.operator_display : false;
            let countString = operator_name ? operator_name + count : operator + count;
            let priceString = price + date;
            let LogoItem;

            if (operator_icon_data.icon) {
                LogoItem = (new HtmlItem('Logo', {
                    tagName: 'img',
                    alt: 'logo',
                    src: operator_icon_data.icon,
                }));
            } else {
                LogoItem = (new HtmlItem('Logo', {
                    classList: ['ctpt-widget__delivery-type__info___default_logo']
                }));
            }

            return (new HtmlItem(operator,
                {classList: ['ctpt-widget__panel-content_list-item']}
            )).addChildMultiple([
                (new HtmlItem('StatusContainer',
                    {classList: ['ctpt-widget__panel-content__status']}
                )).addChild(
                    (new HtmlItem('StatusIcon',
                    {tagName: 'span',classList: ['ctpt-widget__panel-content__status-icon']}
                    ))
                ),
                (new HtmlItem('LogoContainer',
                    {classList: ['ctpt-widget__panel-content__logo']}
                )).addChild(LogoItem),
                (new HtmlItem('InfoContainer',
                    {classList: ['ctpt-widget__panel-content__info',],}
                )).addChildMultiple([
                    (new HtmlItem('CountInfo', {
                        tagName: 'span',
                        innerHTML: countString,
                        classList: ['ctpt-widget__panel-content__info-amount',],
                    })),
                    (new HtmlItem('Name', {
                        tagName: 'span',
                        innerHTML: priceString,
                        classList: ['ctpt-widget__panel-content__info-delivery',],
                    })),
                ]),
            ]);
        };
        /**
         * @param ItemParams
         * @returns {HtmlItem}
         */
        makePvzItem = ItemParams => {
            let Assets = this.WidgetObject.getAssets();
            let logoItem;
            if (this.WidgetObject.getData().getOperatorsIcons()[ItemParams.operator]) {
                const IconData = this.WidgetObject.getData().getOperatorsIcons()[ItemParams.operator];
                logoItem = (new HtmlItem('Logo', {
                    tagName: 'img',
                    alt: 'logo',
                    src: IconData.icon,
                }));
            } else {
                logoItem = (new HtmlItem('Logo', {
                    classList: ['ctpt-widget__delivery-type__info___default_logo']
                }));
            }

            let rateSvg = '';
            let priceStr = '';

            if (ItemParams.price >= 0 && ItemParams.price !== Infinity) {
                priceStr = Assets.getDeliveryVariantItemPrice(ItemParams.price);
            }

            let addressStr = ItemParams.address;
            let timeStr = Assets.getPvzItemTime(ItemParams.time);


            return (new HtmlItem(ItemParams.id,
                {classList: ['ctpt-widget__panel-content_list-item']}
            )).addChildMultiple([
                (new HtmlItem('LogoContainer',
                    {classList: ['ctpt-widget__panel-content__logo']}
                )).addChildMultiple([
                    logoItem,
                    (new HtmlItem('Rate', {
                        tagName: 'span',
                        innerHTML: rateSvg,
                        classList: ['ctpt-widget__delivery-rating',],
                    })),
                ]),
                (new HtmlItem('Info', {
                    classList: ['ctpt-widget__panel-content__info']
                })).addChildMultiple([
                    (new HtmlItem('InfoAddress', {
                        tagName: 'span',
                        innerHTML: addressStr,
                        classList: ['ctpt-widget__panel-content__info-address']
                    })),
                    (new HtmlItem('InfoDeliveryContainer', {
                        tagName: 'span',
                        classList: ['ctpt-widget__panel-content__info-delivery']
                    })).addChildMultiple([
                        (new HtmlItem('DateSpan', {
                            tagName: 'span',
                            classList: ['ctpl_terminal_date_icon_svg']
                        })),
                        (new HtmlItem('InfoDate', {
                            tagName: 'span',
                            innerHTML: timeStr,
                        }))
                    ]),
                ]),
                (new HtmlItem('InfoPrice',
                    {innerHTML: priceStr, classList: ['ctpt-widget__panel-content__price']}
                )),
            ]);
        };
        createPanel = () => {
            let Assets = this.WidgetObject.getAssets();

            let DetailPanel = new HtmlItem('DetailContainer', {classList: ['ctpt-widget__panel-details']});
            DetailPanel.setData({
                loaded_terminals_data: {},
                loaded_terminals_ids: [],
            })

            this.Structure.Panel = (new HtmlItem('Panel',
                {classList: ['ctpt-widget__panel']}
            )).addChildMultiple([
                (new HtmlItem('ListContainer',
                    {classList: ['ctpt-widget__panel-list']}
                )).addChildMultiple([
                    (new HtmlItem('PvzListContainer', {
                        classList: ['ctpt-widget__panel-content-wrap', 'ctpt-widget__panel-list-points']
                    })).addChildMultiple([
                        (new HtmlItem('Title',
                            {
                                innerHTML: Assets.getPanelPvzTitle(0),
                                classList: ['ctpt-widget__panel-headline', 'ctpt-widget__primary-title']
                            }
                        )).addChild(
                            (new HtmlItem('ButtonBack', {
                                classList: ['ctpt-widget__close']
                            }))
                        ),
                        (new HtmlItem('List',
                            {classList: ['ctpt-widget__panel-content', 'ctpt-widget__scroll']}
                        )),
                    ]),
                    (new HtmlItem('FilterListContainer', {
                        classList: ['ctpt-widget__panel-content-wrap', 'ctpt-widget__panel-list-delivery']
                    })).addChildMultiple([
                        (new HtmlItem('Title',
                            {
                                innerHTML: Assets.getPanelFilterTitle(),
                                classList: ['ctpt-widget__panel-headline', 'ctpt-widget__primary-title']
                            }
                        )),
                        (new HtmlItem('List', {
                            classList: ['ctpt-widget__panel-content', 'ctpt-widget__scroll']
                        }))
                    ])
                ]),
                DetailPanel
            ]);
            this.getContainer().getDomElement().appendChild(this.getPanel().getElement());
        };
        makeDetailPanel = (ItemParams) => {
            let Assets = this.WidgetObject.getAssets();
            let DetailGetter = Assets.getDetailGetter();

            let price = '';
            let pvzTime = '';

            let OperatorsMinData = this.WidgetObject.getData().getPvzByOperatorMinData();
            let OperatorsData = this.WidgetObject.getData().getPvzByOperator();

            if (OperatorsMinData[ItemParams.operator]) {
                const priceMin = OperatorsMinData[ItemParams.operator].price;
                if (priceMin >=0 && priceMin !== Infinity) {
                    price = DetailGetter.getPrice(priceMin);
                }
                const timeMin = OperatorsMinData[ItemParams.operator].date;
                if (timeMin > 0 && timeMin !== Infinity) {
                    let time = '';
                    if (this.WidgetObject.getParams().isFullDate()) {
                        time = ' ' + this.WidgetObject.getAssets().getDeliveryDateMonthDay(timeMin);
                    } else {
                        time = DetailGetter.getTime(timeMin);
                    }
                    pvzTime = DetailGetter.getTypeName(ItemParams['point-type']) + time;
                }
            }

            let phone = ItemParams.phone ? ItemParams.phone : '';
            let HowToFind = ItemParams.note ? ItemParams.note : '';
            let address = ItemParams.address ? ItemParams.address : '';

            let workTime = [];
            if (ItemParams.work_time) {
                // let SplitWorkTime = ItemParams.work_time.split(',');
                // for (let i in SplitWorkTime) {
                //     workTime.push((new HtmlItem(i, {
                //         tagName: 'span',
                //         innerHTML:SplitWorkTime[i],
                //     })))
                // }
                workTime.push((new HtmlItem(1, {
                    tagName: 'span',
                    innerHTML:ItemParams.work_time,
                })))
            }

            let LogoItem;
            if (this.WidgetObject.getData().getOperatorsIcons()[ItemParams.operator]) {
                const IconData = this.WidgetObject.getData().getOperatorsIcons()[ItemParams.operator];
                LogoItem = (new HtmlItem('Logo', {
                    tagName: 'img',
                    alt: 'logo',
                    src: IconData.icon,
                }));
            } else {
                LogoItem = (new HtmlItem('Logo', {
                    classList: ['ctpt-widget__delivery-type__info___default_logo']
                }));
            }

            let SelectView = new HtmlItem('SelectView',
                {
                    classList: ['ctpt-widget__select__head']
                }
            );
            let SelectList = new HtmlItem('SelectVariants', {
                    tagName: 'ul',
                    classList: ['ctpt-widget__select__list']
                }
            );
            SelectView.setData({
                open: false,
                SelectedItem: false,
            });
            let SelectButtonHeader = this.WidgetObject.getParams().isOnlyInfo() ? new HtmlItem('SelectButton'): new HtmlItem('SelectButton', {
                innerHTML: DetailGetter.getSelect(),
                classList: ['ctpt-widget__button',],
            });
            let SubmitSelectButton = this.WidgetObject.getParams().isOnlyInfo() ? new HtmlItem('SelectButton'): new HtmlItem('SelectButton', {
                innerHTML: DetailGetter.getSelect(),
                classList: ['ctpt-widget__button']
            });
            SelectView.handleEvent('onclick', () => {
                let DetailContainer = this.getDetailPanel()
                    .getChild('DetailBox')
                    .getChild('DetailContainer')
                    .getChild('DetailContentContainer');
                if (SelectView.getData().open === false) {
                    SelectView.getData().open = true;
                    SelectView.setOpen();
                    SelectList.show();
                    const height = SelectList.getElement().offsetHeight + SelectView.getElement().offsetHeight + 30;
                    if (DetailContainer.getElement().offsetHeight < height) {
                        DetailContainer.setCss({height: height + 'px'})
                    }
                } else {
                    SelectView.getData().open = false;
                    SelectView.removeOpen();
                    SelectList.hide();
                    DetailContainer.setCss({height: 'auto'})
                }
            })
            let SelectItems = [];
            if (OperatorsData[ItemParams.operator]) {
                OperatorsData[ItemParams.operator] = OperatorsData[ItemParams.operator].sort((a, b) => {
                    const AtimeDiff = a.transit_days;
                    const BtimeDiff = b.transit_days;
                    const aPrice = a.price >= 0 ? a.price : Infinity;
                    const bPrice = b.price >= 0 ? b.price : Infinity;

                    if (aPrice > bPrice) {
                        return 1;
                    }
                    if (aPrice < bPrice) {
                        return -1;
                    }
                    if (aPrice === bPrice) {
                        if (AtimeDiff > BtimeDiff) {
                            return 1;
                        }
                        if (AtimeDiff < BtimeDiff) {
                            return -1;
                        }
                        return 0;
                    }
                    return 0;
                })

                for (let i in OperatorsData[ItemParams.operator]) {
                    const Variant = OperatorsData[ItemParams.operator][i];
                    let VariantPrice = '';
                    if (Variant.price >=0 && Variant.price !== Infinity) {
                        VariantPrice = DetailGetter.getPrice(Variant.price);
                    }

                    let Time = '';
                    if (Variant.transit_days > 0 && Variant.transit_days !== Infinity) {
                        if (this.WidgetObject.getParams().isFullDate()) {
                            Time = ' ' + this.WidgetObject.getAssets().getDeliveryDateMonthDay(Variant[ApiObjectKeys.delivery_date]);
                        } else {
                            Time = DetailGetter.getTime(Variant.transit_days);
                        }
                    }
                    const Rate = Variant.rate + '';
                    let SelectItem = (new HtmlItem(Variant.id, {
                        tagName: 'li',
                        classList: ['ctpt-widget__select__item']
                    })).addChildMultiple([
                        (new HtmlItem('Name', {
                            tagName: 'span',
                            classList: ['ctpt-widget__select__item__plane']
                        })).addChildMultiple([
                            (new HtmlItem('NameTariff', {
                                tagName: 'span',
                                innerHTML: Rate,
                                classList: ['ctpt-widget__select__item__name']
                            })),
                            (new HtmlItem('Time', {
                                tagName: 'span',
                                innerHTML: Time,
                                classList: ['ctpt-widget__select__item__info-delivery']
                            })),
                        ]),
                        (new HtmlItem('Price', {
                            tagName: 'span',
                            innerHTML: VariantPrice,
                            classList: ['ctpt-widget__select__item__price']
                        })),
                    ]);
                    SelectItem.setData({
                        Variant: Variant,
                        Terminal: ItemParams,
                        Text: VariantPrice + ', ' + Time + ', ' + Rate,
                    });
                    SelectItem.handleEvent('onclick',() => {
                        SelectView.getData().open = false;
                        SelectView.removeOpen();
                        SelectList.hide();
                        SelectView.getChild('PriceText').getElement().innerHTML = SelectItem.getData().Text;

                        SelectView.getData().SelectedItem = SelectItem;
                    });
                    SelectItems.push(SelectItem);
                }
                SelectView.getData().SelectedItem = SelectItems[0];
            }
            if (!this.WidgetObject.getParams().isOnlyInfo()) {
                const OnSubmitSelect = () => {
                    if (SelectView.getData().SelectedItem) {
                        if (this.WidgetObject.getParams().hasHandlerOnSelectPvzItem()) {
                            this.WidgetObject.getParams().getOnSelectPvzItemHandler()(SelectView.getData().SelectedItem, this.WidgetObject);
                        }
                    }
                }
                SubmitSelectButton.handleEvent('onclick', OnSubmitSelect);
                SelectButtonHeader.handleEvent('onclick', OnSubmitSelect);
            }


            let SelectBox = SelectItems.length === 1 ? (new HtmlItem('SelectBox',
                {classList: ['ctpt-widget__panel-details__price-wrap',],}
            )).addChildMultiple([
                (new HtmlItem('PriceTitle', {
                    innerHTML: DetailGetter.getPriceTitle(),
                    classList: ['ctpt-widget__panel-details__title',],
                })),
                (new HtmlItem('Price', {
                    innerHTML: price,
                    classList: ['ctpt-widget__panel-details__price',],
                })),
                SelectButtonHeader,
            ]) : new HtmlItem('SelectBox');

            let TariffBox = SelectItems.length > 1 ? (new HtmlItem('TariffBox',
                {classList: ['ctpt-widget__panel-details__price-wrap']}
            )).addChildMultiple([
                (new HtmlItem('PriceTitle', {
                    innerHTML: DetailGetter.getPriceTitle(),
                    classList: ['ctpt-widget__panel-details__title']
                })),
                (new HtmlItem('SelectBox',
                    {classList: ['ctpt-widget__select']}
                )).addChildMultiple([
                    SelectView.addChildMultiple([
                        new HtmlItem('PriceText', {
                            tagName: 'span',
                            innerHTML: SelectItems[0].getData().Text
                        }),
                        new HtmlItem('Arrow', {
                            innerHTML: DetailGetter.getInputSvg()
                        })
                    ]),
                    SelectList.addChildMultiple(SelectItems),
                ]),
                SubmitSelectButton,
            ]) : new HtmlItem('TariffBox');

            let AddressBox = address ? (new HtmlItem('AddressBox',
                {classList: ['ctpt-widget__panel-details__address-wrap']}
            )).addChildMultiple([
                (new HtmlItem('AddressTitle', {
                    innerHTML: DetailGetter.getAddressTitle(),
                    classList: ['ctpt-widget__panel-details__title',],
                })),
                (new HtmlItem('AddressCover',
                    {classList: ['ctpt-widget__panel-details__info-wrap']}
                )).addChild((new HtmlItem('Address', {
                    tagName: 'span',
                    innerHTML: address,
                }))),
            ]) : new HtmlItem('AddressBox');

            let WorkTimeBox = workTime.length > 0 ? (new HtmlItem('WorkTimeBox',
                {classList: ['ctpt-widget__panel-details__working-hours-wrap']}
            )).addChildMultiple([
                (new HtmlItem('Title', {
                    innerHTML: DetailGetter.getWorkTitle(),
                    classList: ['ctpt-widget__panel-details__title']
                })),
                (new HtmlItem('WorkTimes',
                    {classList: ['ctpt-widget__panel-details__info-wrap']}
                )).addChildMultiple(workTime)
            ]) : new HtmlItem('WorkTimeBox');

            let PhonesBox = phone ? (new HtmlItem('PhonesBox',
                {classList: ['ctpt-widget__panel-details__phones-wrap']}
            )).addChildMultiple([
                (new HtmlItem('Title', {
                    innerHTML: DetailGetter.getPhoneTitle(),
                    classList: ['ctpt-widget__panel-details__title']
                })),
                (new HtmlItem('PhonesList',
                    {classList: ['ctpt-widget__panel-details__info-wrap']}
                )).addChildMultiple([
                    (new HtmlItem('Name1', {
                        tagName: 'a',
                        innerHTML: phone,
                        href: 'tel:' + phone,
                        classList: ['ctpt-widget__panel-details__info-phone']
                    })),
                ]),
            ]) : new HtmlItem('PhonesList');

            let HowToFindBox = HowToFind ? (new HtmlItem('HowToFindBox',
                {classList: ['ctpt-widget__panel-details__description-wrap',],}
            )).addChildMultiple([
                (new HtmlItem('TitleBox', {
                    innerHTML: DetailGetter.getHowToFindTitle(),
                    classList: ['ctpt-widget__panel-details__title',],
                })),
                (new HtmlItem('TextBox',
                    {classList: ['ctpt-widget__panel-details__info-wrap',],}
                )).addChild((new HtmlItem('Text', {
                    tagName: 'span',
                    innerHTML: HowToFind,
                }))),
            ]) : new HtmlItem('HowToFindBox');

            let BuckButton = new HtmlItem('BuckBox', {
                classList: ['ctpt-widget__panel-details__back']
            });
            BuckButton.handleEvent('onclick', () => {
                this.getPanel().getChild('ListContainer').removeCurrent();
                this.getDetailPanel().removeCurrent();
                this.WidgetObject.getMap().recenterMap();
            })

            return (new HtmlItem('DetailBox')).addChildMultiple([
                (new HtmlItem('TitleBox',
                    {classList: ['ctpt-widget__primary-title']}
                )).addChildMultiple([
                    BuckButton.addChild(
                        new HtmlItem('Svg', {classList: ['ctpt-widget__panel-details__back_svg']})),
                    (new HtmlItem('Header',
                        {classList: ['ctpt-widget__panel-details__item-header']}
                    )).addChildMultiple([
                        (new HtmlItem('HeaderLogoBox',
                            {classList: ['ctpt-widget__panel-details__logo']}
                        )).addChild(LogoItem),
                        (new HtmlItem('HeaderTitle',
                            {classList: ['ctpt-widget__panel-details__info']}
                        )).addChildMultiple([
                            (new HtmlItem('HeaderAddress', {
                                tagName: 'span',
                                innerHTML: address,
                                classList: ['ctpt-widget__panel-details__info-amount']
                            })),
                            (new HtmlItem('NHeaderTime', {
                                tagName: 'span',
                                innerHTML: pvzTime,
                                classList: ['ctpt-widget__panel-details__info-delivery']
                            })),
                        ]),
                    ]),
                ]),
                (new HtmlItem('DetailContainer',
                    {classList: ['ctpt-widget__panel-details__list', 'ctpt-widget__scroll']}
                )).addChild((new HtmlItem('DetailContentContainer',
                        {classList: ['ctpt-widget__panel-details__item']}
                    )).addChild((new HtmlItem('DetailItemList',
                            {classList: ['ctpt-widget__panel-details__item-content']}
                        )).addChildMultiple([
                            SelectBox,
                            TariffBox,
                            AddressBox,
                            WorkTimeBox,
                            PhonesBox,
                            HowToFindBox,
                        ])
                    )
                ),
            ]);
        }
        getDetailPanel = () => {
            return this.getPanel().getChild('DetailContainer');
        };
        getPvzList = () => {
            return this.getPanel()
                .getChild('ListContainer')
                .getChild('PvzListContainer');
        };
        getFilterList = () => {
            return this.getPanel()
                .getChild('ListContainer')
                .getChild('FilterListContainer');
        };
        setPvzCountTitle = count => {
            this.getPanel()
                .getChild('ListContainer')
                .getChild('PvzListContainer')
                .getChild('Title')
                .getElement().innerHTML = this.WidgetObject.getAssets().getPanelPvzTitle(count);
        };
        getPanel = () => this.Structure.Panel;
        createSidebar = () => {
            let SidebarAssets = this.WidgetObject.getAssets().getSidebar();

            this.SideBarButtons.PvzList = (new HtmlItem('PvzList',
                {
                    innerHTML: SidebarAssets.getPvzSvg(),
                    classList: ['ctpt-widget__sidebar-button', 'ctpt-widget__sidebar-burger', 'ctpt-widget__sidebar-button-js']
                }
            ));
            this.SideBarButtons.PayFilter = (new HtmlItem('PayFilter',
                {
                    innerHTML: SidebarAssets.getPaySvg(),
                    classList: ['ctpt-widget__sidebar-button', 'ctpt-widget__sidebar-button-cash', 'ctpt-widget__sidebar-button-checked']
                }
            ));
            this.SideBarButtons.TypeFilter = (new HtmlItem('TypeFilter',
                {
                    innerHTML: SidebarAssets.getTypeSvg(),
                    classList: ['ctpt-widget__sidebar-button', 'ctpt-widget__sidebar-button-cal', 'ctpt-widget__sidebar-button-checked']
                }
            ));
            this.SideBarButtons.OperatorFilter = (new HtmlItem('OperatorFilter',
                {
                    innerHTML: SidebarAssets.getFilterSvg(),
                    classList: ['ctpt-widget__sidebar-button', 'ctpt-widget__sidebar-button-delivery', 'ctpt-widget__sidebar-button-js']
                }
            ));
            this.Structure.Sidebar = (new HtmlItem('Sidebar',{classList: ['ctpt-widget__sidebar']}))
                .addChildMultiple([
                    (new HtmlItem('ButtonContainer', {classList: ['ctpt-widget__sidebar-button-wrap']}
                    )).addChildMultiple([
                        this.SideBarButtons.PvzList,
                        this.SideBarButtons.PayFilter,
                        this.SideBarButtons.TypeFilter,
                        this.SideBarButtons.OperatorFilter,
                    ]),
                    (new HtmlItem('LogoContainer', {
                        classList: ['ctpt-widget__sidebar-logo-wrap',],
                    })).addChild((new HtmlItem('LogoLink',
                        {
                            tagName: 'a',
                            target: '_blank',
                            href: SidebarAssets.getLink(),
                            innerHTML: SidebarAssets.getLogoSvg(),
                            classList: ['ctpt-widget__logo-link'],
                        }
                    )))
                ]);

            const SidebarButtonsPlaceholders = [
                [this.SideBarButtons.PvzList, 'PvzList'],
                [this.SideBarButtons.PayFilter, 'PriceFilter'],
                [this.SideBarButtons.TypeFilter, 'TypeFilter'],
                [this.SideBarButtons.OperatorFilter, 'OperatorFilter']
            ];
            const isMobile = this.isMobile();

            SidebarButtonsPlaceholders.forEach((Item) => {
                let Placeholder = this.getPanelInfo().getChild(Item[1]);
                /**
                 * @type HtmlItem
                 */
                let SidebarButton = Item[0];

                if (isMobile) {
                    let interValForPlaceholder;
                    SidebarButton.addEventListener('click', () => {
                        Placeholder.getElement().classList.add('show');
                        clearTimeout(interValForPlaceholder);
                        interValForPlaceholder = setTimeout(() => {
                            Placeholder.getElement().classList.remove('show');
                        }, 3000);
                    })
                } else {
                    SidebarButton.handleEvent('onmouseover', () => {
                        Placeholder.getElement().classList.add('show');
                    });
                    SidebarButton.handleEvent('onmouseout', () => {
                        Placeholder.getElement().classList.remove('show');
                    });
                }
            });
            /**
             * @type {HtmlItem}
             */
            let Panel = this.getPanel();
            let PvzList = this.getPvzList();
            let FilterList = this.getFilterList();
            /**
             * @type {HtmlItem}
             */
            let PvzButton = this.SideBarButtons.PvzList;
            let FilterButton = this.SideBarButtons.OperatorFilter;

            const removeSelectWhen = () => {
                PvzButton.removeCurrent();
                FilterButton.removeCurrent();
                FilterList.hide();
                PvzList.hide();
                Panel.removeOpen();
                Panel.getChild('ListContainer').removeCurrent();
                this.getDetailPanel().removeCurrent();
            };

            PvzButton.handleEvent('onclick', () => {
                this.WidgetObject.getMap().resetZoom();
                const hasSelect = PvzButton.getElement().classList.contains('current');
                removeSelectWhen();
                if (hasSelect) {
                    this.WidgetObject.getMap().resetCenter();
                    this.WidgetObject.getMap().getMap().setZoom(12);
                    return;
                }

                PvzList.show();
                PvzButton.setCurrent();
                Panel.setOpen();
                this.WidgetObject.getMap().resetCenter();
                this.WidgetObject.getMap().recenterMap(12);
            });
            FilterButton.handleEvent('onclick', () => {
                this.WidgetObject.getMap().resetZoom();
                const hasSelect = FilterButton.getElement().classList.contains('current');
                removeSelectWhen();
                if (hasSelect) {
                    this.WidgetObject.getMap().resetCenter();
                    this.WidgetObject.getMap().getMap().setZoom(12);
                    return;
                }
                FilterList.show();
                FilterButton.setCurrent();
                Panel.setOpen();
                this.WidgetObject.getMap().resetCenter();
                this.WidgetObject.getMap().recenterMap(12);
            });

            this.getMapBox().addEventListener('click', () => {
                const isOpenFilter = FilterButton.getElement().classList.contains('current');
                if (isOpenFilter) {
                    removeSelectWhen()
                }
            })

            this.getContainer().getDomElement().appendChild(this.getSidebar().getElement());
        };
        isMobile = () => {
            const toMatch = [
                /Android/i,
                /webOS/i,
                /iPhone/i,
                /iPad/i,
                /iPod/i,
                /BlackBerry/i,
                /Windows Phone/i
            ];
            return toMatch.some((toMatchItem) => {
                return navigator.userAgent.match(toMatchItem);
            });
        }
        getSidebar = () => this.Structure.Sidebar;
        createPanelInfo = () => {
            let AssetsPanelInfo = this.WidgetObject.getAssets().getPanelInfo();

            this.Structure.PanelInfo = (new HtmlItem('PanelInfo',{
                classList: ['ctpt-widget__sidebar-button__hint_list']
            })).addChildMultiple([
                (new HtmlItem('PvzList', {
                    innerHTML: AssetsPanelInfo.getPvzTitle(),
                    classList: ['ctpt-widget__sidebar-button__hint', 'ctpt-widget-list']
                })),
                (new HtmlItem('PriceFilter', {
                    innerHTML: AssetsPanelInfo.getFilterPayAll(),
                    classList: ['ctpt-widget__sidebar-button__hint', 'ctpt-widget-cash']
                })),
                (new HtmlItem('TypeFilter', {
                    innerHTML: AssetsPanelInfo.getFilterTypeAll(),
                    classList: ['ctpt-widget__sidebar-button__hint', 'ctpt-widget-cal']
                })),
                (new HtmlItem('OperatorFilter', {
                    innerHTML: AssetsPanelInfo.getFilterTitle(),
                    classList: ['ctpt-widget__sidebar-button__hint', 'ctpt-widget-delivery']
                })),
            ]);
            this.getContainer().getDomElement().appendChild(this.getPanelInfo().getElement());
        };
        getPanelInfo = () => this.Structure.PanelInfo;
        setPanelInfoPayTitle = (code = 'all') => {
            let AssetsPanelInfo = this.WidgetObject.getAssets().getPanelInfo();
            let newTitle = '';
            switch (code) {
                case 'card':
                    newTitle = AssetsPanelInfo.getFilterPayCard();
                    break;
                case 'cache':
                    newTitle = AssetsPanelInfo.getFilterPayCache();
                    break;
                case "all":
                default:
                    newTitle = AssetsPanelInfo.getFilterPayAll();
                    break;
            }
            this.getPanelInfo().getChild('PriceFilter').getElement().innerHTML = newTitle;
        };
        setPanelInfoTypeTitle = (code = 'all') => {
            let AssetsPanelInfo = this.WidgetObject.getAssets().getPanelInfo();
            let newTitle = '';
            switch (code) {
                case 'terminal':
                    newTitle = AssetsPanelInfo.getFilterTypeTerminal();
                    break;
                case 'pvz':
                    newTitle = AssetsPanelInfo.getFilterTypePvz();
                    break;
                case "all":
                default:
                    newTitle = AssetsPanelInfo.getFilterTypeAll();
                    break;
            }
            this.getPanelInfo().getChild('TypeFilter').getElement().innerHTML = newTitle;
        };
        createMapTypeSelector = () => {
            let Assets = this.WidgetObject.getAssets();
            let AssetsMapSelector = Assets.getMapSelector();
            this.Structure.MapTypeSelector =
                (new HtmlItem('MapTypeSelector',
                    {classList: ['ctpt-widget__delivery-button-wrap']}
                )).addChildMultiple([
                    (new HtmlItem('Courier', {
                        classList: ['ctpt-widget__delivery-button',],
                        })).addChildMultiple([
                            (new HtmlItem('Svg',
                                {tagName: 'span', innerHTML: Assets.getCourierButtonSvg()}
                            )),
                            (new HtmlItem('Title', {
                                tagName: 'span',
                                innerHTML: AssetsMapSelector.getCourierTitle(),
                            })),
                        ]),
                    (new HtmlItem('Pvz', {
                        classList: ['ctpt-widget__delivery-button', 'current',],
                        })).addChildMultiple([
                            (new HtmlItem('Svg',
                                {tagName: 'span', innerHTML: Assets.getPvzButtonSvg()}
                            )),
                            (new HtmlItem('Title', {
                                tagName: 'span',
                                innerHTML: AssetsMapSelector.getPvzTitle(),
                            })),
                        ]),
                    ]
                );
            this.getContainer().getDomElement().appendChild(this.getMapTypeSelector().getElement());
        };
        getMapSelectorCourier = () => this.getMapTypeSelector().getChild('Courier');
        getMapTypeSelector = () => this.Structure.MapTypeSelector;
        createMapSearch = () => {
            let AssetsMapSearch = this.WidgetObject.getAssets().getMapSearch();
            const randId = (Math.random() + 1).toString(36).substring(7);
            this.Structure.MapSearch = (new HtmlItem('MapSearch',
                {classList: ['ctpt-widget__map-search-wrap']}
            )).addChild(
                (new HtmlItem('MapSearchContainer', {classList: ['ctpt-widget__map-search-form']}))
                    .addChild((new HtmlItem('MapSearchInputContainer', {tagName: 'form'}))
                        .addChildMultiple([
                            (new HtmlItem('Link', {
                                tagName: 'a',
                                target: '_blank',
                                href: AssetsMapSearch.getLink(),
                                classList: ['ctpt-widget__logo-link']
                                })).addChild(
                                    (new HtmlItem('Image', {
                                    tagName: 'span',
                                    innerHTML: AssetsMapSearch.getSvgLogo(),
                                    classList: ['ctpt-widget__map-search-logo']
                                }))
                            ),
                            (new HtmlItem('Input', {
                                tagName: 'input',
                                id: 'ctpt-search' + randId,
                                placeholder: AssetsMapSearch.getMapSearchPlaceholder(),
                                classList: ['ctpt-widget__map-search-input']
                            })),
                            (new HtmlItem('ButtonSelect', {
                                innerHTML:  AssetsMapSearch.getSvg() + AssetsMapSearch.getButtonTitle(),
                                classList: ['ctpt-widget__map-search-button']
                            }))
                        ])
                    )
                );
            this.getMapSearchContainer().hide();
            this.getContainer().getDomElement().appendChild(this.getMapSearchContainer().getElement());
        };
        getMapSearchSubmitButton = () => this.getMapSearchContainer()
            .getChild('MapSearchContainer')
            .getChild('MapSearchInputContainer')
            .getChild('ButtonSelect');
        getMapSearchInput = () => this.getMapSearchContainer()
            .getChild('MapSearchContainer')
            .getChild('MapSearchInputContainer')
            .getChild('Input');
        getMapSearchContainer = () => this.Structure.MapSearch;
        getVariantTypeItemContainer = () => {
            return this.getVariantContainer().getChild('SubContainer').getChild('VariantContainer');
        };
        createVariantContainer = () => {
            let Assets = this.WidgetObject.getAssets();

            let VariantsTypes = [];
            let classList = ['ctpt-widget__delivery-type'];
            if (!this.WidgetObject.getParams().isOnlyPvz()) {
                VariantsTypes.push(this.createVariant('CourierVariant', Assets.getDeliveryVariantCourierTitle()))
            } else {
                classList.push('ctpt-widget__delivery-type_only_pvz');
            }

            if (!this.WidgetObject.getParams().isCourierOnly()) {
                VariantsTypes.push(this.createVariant('PvzVariant', Assets.getDeliveryVariantPvzTitle()))
            } else {
                classList.push('ctpt-widget__delivery-type_only_c');
            }
            this.Structure.VariantContainer = (new HtmlItem('Container', {
                    classList,
                })).addChildMultiple([
                    (new HtmlItem('SubContainer',
                        {classList: ['ctpt-widget__delivery-container']}
                    )).addChildMultiple([
                        this.WidgetObject.getParams().isOnlyPvz() ? false : (new HtmlItem('TitleContainer',
                            {classList: ['ctpt-widget__primary-title']}
                        )).addChild((new HtmlItem('Title', {
                            tagName: 'span',
                            innerHTML: Assets.getDeliveryVariantContainerTitle(),
                            classList: ['ctpt_choose']
                        }))),
                        (new HtmlItem('VariantContainer',
                            {classList: ['ctpt-widget__delivery-type__options', 'ctpt-widget__tab-js']}
                        )).addChildMultiple(VariantsTypes),
                    ]),
                    this.WidgetObject.getParams().isOnlyPvz() ? false : (new HtmlItem('FilterContainer',
                        {classList: ['ctpt-widget__delivery-filters-list']}
                    )).addChildMultiple([
                        (new HtmlItem('BySpeed', {
                            innerHTML: Assets.getDeliveryVariantFilterSpeed(),
                            classList: ['ctpt-widget__delivery-filter']
                        })),
                        (new HtmlItem('ByRate', {
                            innerHTML: Assets.getDeliveryVariantFilterRate(),
                            classList: ['ctpt-widget__delivery-filter']
                        })), (new HtmlItem('ByPrice', {
                            innerHTML: Assets.getDeliveryVariantFilterPrice(),
                            classList: ['ctpt-widget__delivery-filter', 'current']
                        })),
                    ]),
                    this.WidgetObject.getParams().isOnlyPvz() ? false : (new HtmlItem('AddressWarnText',
                        {classList: ['ctpt-widget__delivery-addresswarntext','hide']}
                    )).addChild(new HtmlItem('AddressWarnTextInner',{}).addChildMultiple([
                        (new HtmlItem('AddressWarnText_Text',{
                            tagName: 'p',
                            innerHTML: Assets.getTextByCode('yandes_warn_text'),
                            classList: ['ctpt-widget__delivery-addresswarntext_text']
                        }))
                    ])),
                    (new HtmlItem('ItemsBaseContainer',
                        {classList: [
                                'ctpt-widget__delivery-type__options-content',
                                'ctpt-widget__content-js',
                                'ctpt-widget__scroll',
                        ]})).addChildMultiple([
                        (new HtmlItem('LoaderBody',
                            {classList: ['ctpt-widget__load-spinner']}
                        )).addChildMultiple([
                            (new HtmlItem('LoaderInner', {
                                tagName: 'span',
                                classList: ['ctpt-widget__load']
                            })),
                            (new HtmlItem('LoaderinnerHTML', {
                                tagName: 'span',
                                innerHTML: Assets.getLoadSpinnerHTML()
                            })),
                        ]),
                        (new HtmlItem('ItemsList',
                            {classList: ['ctpt-widget__delivery-type__option-item']}
                        )),
                    ]),
                ]);
            if (!this.WidgetObject.getParams().isOnlyPvz()) {
                let FilterContainer = this.getVariantContainer().getChild('FilterContainer');
                let BySpeed = FilterContainer.getChild('BySpeed');
                let ByRate = FilterContainer.getChild('ByRate');
                let ByPrice = FilterContainer.getChild('ByPrice');
                let ItemList = this.getVariantContainer().getChild('ItemsBaseContainer').getChild('ItemsList');
                const RemoveCurrent = (Current, key) => {
                    try {
                        this.WidgetObject.sortBy(ItemList, key);
                        BySpeed.removeCurrent();
                        ByRate.removeCurrent();
                        ByPrice.removeCurrent();
                        Current.setCurrent();
                    } catch (e) {

                    }
                }
                const sortBinds = [
                    [BySpeed, "speed"],
                    [ByRate, "rate"],
                    [ByPrice, "price"]
                ];
                sortBinds.forEach(sortBind => {
                    sortBind[0].handleEvent('onclick', () => {
                        RemoveCurrent(sortBind[0], sortBind[1]);
                    });
                })
            }
            this.getContainer().getDomElement().appendChild(this.getVariantContainer().getElement());
        };
        getVariantContainer = () => this.Structure.VariantContainer;
        getVariantList = () => this.getVariantContainer().getChild('ItemsBaseContainer').getChild('ItemsList');
        createBaseSearch = () => {
            let Assets = this.WidgetObject.getAssets();
            const randId = (Math.random() + 1).toString(36).substring(7);
            this.Structure.BaseSearch = (new HtmlItem('Container', {classList: ['ctpt-widget__search-wrap'],}))
            .addChildMultiple([
                (new HtmlItem(
                    'Title',
                    {classList: ['ctpt-widget__search-title'], innerHTML: Assets.getBaseSearchTitle()}
                )),
                (new HtmlItem('FormContainer', {classList: ['ctpt-widget__search-form'],})
                    .addChild(
                        (new HtmlItem('Form', {tagName: 'form',}))
                        .addChildMultiple([
                            (new HtmlItem('link', {
                                tagName: 'a',
                                classList: ['ctpt-widget__logo-link'],
                                target: '_blank',
                                href: Assets.getLink(),
                                innerHTML: Assets.getSearchLogo(),
                            })),
                            (new HtmlItem(
                                'InputContainer',
                                {classList: ['ctpt-widget__search-floating'],}
                            )).addChildMultiple([
                                (new HtmlItem('Input', {
                                    classList: ['form-control', 'ctpt-widget__search-input'],
                                    tagName: 'input',
                                    id: 'ctpt-address' + randId,
                                })),
                                (new HtmlItem('InputLabel', {
                                    tagName: 'label',
                                    for: 'ctpt-address' + randId,
                                    innerHTML: Assets.getBaseSearchLabelText(),
                                })),
                            ]),
                            (new HtmlItem('ButtonSubmit', {
                                classList: ['ctpt-widget__search-button'],

                            })).addChildMultiple([
                                (new HtmlItem('ButtonSubmitText',{
                                    tagName: 'span',
                                    classList: ['ctpt-widget__txt'],
                                    innerHTML: Assets.getBaseSearchSubmitText(),
                                })),
                                (new HtmlItem('ButtonSubmitSpan',{
                                    tagName: 'span',
                                    classList: ['ctpt-widget__load']
                                })),
                                (new HtmlItem('ButtonSubmitIcon',{
                                    tagName: 'span',
                                    innerHTML: Assets.getSvgByCode('zoom_image'),
                                    classList: ['ctpt-widget__icon']
                                })),
                            ]),
                        ])
                    )
                ),
                (new HtmlItem('Description', {
                    classList: ['ctpt-widget__search-hint'],
                    innerHTML: Assets.getBaseSearchDescription(),
                }))
            ]);
            this.getContainer().getDomElement().appendChild(this.getBaseSearch().getElement());
        };
        getBaseSearchSubmitButton = () => this.getBaseSearch().getChild('FormContainer').getChild('Form').getChild('ButtonSubmit');
        getBaseSearchLabel = () => this.getBaseSearchForm().getChild('InputLabel');
        getBaseSearchInput = () => this.getBaseSearchForm().getChild('Input');
        getBaseSearchForm = () => this.getBaseSearch()
            .getChild('FormContainer')
            .getChild('Form')
            .getChild('InputContainer');
        getBaseSearch = () => this.Structure.BaseSearch;
        getContainer = () => {
            if (!this.Structure.Container.domElement) {
                let containerElement = document.getElementById(this.WidgetObject.getParams().getWidgetId());
                this.Structure.Container.domElement = containerElement;
                containerElement.classList.add('ctpt-widget');
                containerElement.classList.remove('second-step');
                containerElement.classList.remove('ctpt_popup_mode');
                containerElement.classList.remove('ctpt_close_popup_step');
                while (containerElement.firstChild) {
                    containerElement.removeChild(containerElement.firstChild);
                }
            }
            return this.Structure.Container;
        };
        static removeStyles = () => {
            let styles = document.querySelectorAll('.ctpt_style_assets');
            for (let i in styles) {
                if (styles[i] && styles[i].tagName === 'STYLE') {
                    styles[i].remove();
                }
            }
        }
    }
    class DataCollection {
        getRateParams = () => this.RateParams;
        setRateParams = RateParams => {this.RateParams = RateParams;};
        setCreatedRate = (RateData) => {this.CreatedRate = RateData};
        getCreatedRate = () => this.CreatedRate;
        getCourierItems = () => this.CourierItems;
        setCourierItems = CourierItems => {this.CourierItems = CourierItems;};
        getPvzItems = () => this.PvzItems;
        setPvzItems = PvzItems => {this.PvzItems = PvzItems;};
        getTerminals = () => this.Terminals;
        setTerminals = Terminals => {this.Terminals = Terminals;};
        getLastSort = () => this.LastSort;
        setLastSort = (key) => {this.LastSort = key;}
        getPvzByOperator = () => this.PvzByOperator;
        setPvzByOperator = PvzItems => {this.PvzByOperator = PvzItems;};
        getPvzByOperatorMinData = () => this.PvzByOperatorMinData;
        setPvzByOperatorMinData = PvzItems => {this.PvzByOperatorMinData = PvzItems;};
        getOperatorsIcons = () => this.OperatorsIcons;
        setOperatorsIcons = OperatorsIcons => {this.OperatorsIcons = OperatorsIcons;};
        getTariffs = () => this.Tariffs;
        setTariffs = Tariffs => {this.Tariffs = Tariffs;};
        setSelectedCity = (CityData) => {this.SelectedCity = CityData};
        getSelectedCity = () => this.SelectedCity;
        calkMinDatePrice = Items => {
            return {
                price: this.calkMin(Items, 'price'),
                date: this.calkMin(Items, 'transit_days'),
                item: this.calkMinTarif(Items,'price')
            };
        };
        calkMin(Items = [], key = 'price') {
            let ItemsWithValue = Items.filter((Item) => {
                if (key === 'transit_days') {
                    return Item[key] !== null && Item[key] > 0;
                }
                return Item[key] !== null;
            })
            let ItemsValue = ItemsWithValue.map((Item)=>{
                if (key === [ApiObjectKeys.delivery_date]) {
                    const diff = Math.abs(new Date(Item[key]).getTime() - new Date().getTime());
                    return Math.round(diff / (3600 * 1000 * 24));
                } else {
                    return Item[key];
                }
            });

            return Math.min(...ItemsValue);
        };
        calkMinTarif(Items = [], key = 'price') {
            let ItemsWithValue = Items.filter((Item) => {
                if (key === 'transit_days') { return Item[key] !== null && Item[key] > 0; }
                return Item[key] !== null;
            })
            let ItemsValue = ItemsWithValue.map((Item)=>{
                const diff = Math.abs(new Date(Item[ApiObjectKeys.delivery_date]).getTime() - new Date().getTime());
                Item.cntdays = Math.round(diff / (3600 * 1000 * 24));
                return [Item,Item[key]];
            });
            ItemsValue.sort((a,b)=>{ return a[1]-b[1]; });
            return ItemsValue[0];
        };
        SelectedCity;
        LastSort = 'price';
        RateParams;
        CreatedRate;
        CourierItems;
        PvzItems;
        Terminals;
        PvzByOperator;
        PvzByOperatorMinData;
        OperatorsIcons;
        Tariffs;
    }
    window.CatapultoWidget = class CatapultoWidget {
        constructor(params) {
            let self = this;
            self.DataCollection = new DataCollection();
            self.Params = new ParamsCollections(params, self);
            self.AjaxEngine = new AjaxEngine(self);
            self.MapCover = new MapCover(self);
            self.Assets = new Assets();
            self.Structure = new Structure(this);
            self.initError = false;
            self.getParams().setSenderContactDataByAddress().then(() => {
                self.initError = false;
            }).catch(e => {
                self.initError = true;
                this.onApiError(e);
            });
            self.SelectedDadataVariant = false;
            self.SelectedDadataMapVariant = false;
            self.SearchedCity = false;
            self.ClosedPopup = false;
            self.InRequest = false;
            let Loader = new StateLoader(this, self.getMap());
            Loader.load(self.ready.bind(this));
            self.getData().setTariffs({});
            self.TerminalsPromiceCancel = false;
            self.coordsCenter = [];
            self.step = 0;
        }
        // actions methods
        /**
         * @param selectedCity
         * @param selectedCityValue
         * @param iso
         * @param {function} callBackGetRateFunction
         * @returns {Promise<boolean>}
         */
        createRateAction = async (selectedCity, selectedCityValue, iso, callBackGetRateFunction) => {

            const createdRate = await this.getAjaxEngine().createRate(
                selectedCity,
                selectedCityValue,
                this.SelectedDadataVariant,
                iso
            );
            const research = () => {
                this.removeOldPromiceSearch = false;
                this.InRequest = false;
                this.submitSearch();
            }
            if (this.removeOldPromiceSearch === true) {
                research();
                await Promise.reject(new Error('Reject'));
            }
            if (createdRate.error) {
                throw new Error(createdRate.error);
            }
            this.getData().setSelectedCity({
                'dadata': this.SelectedDadataVariant,
                'api': createdRate.locations
            })
            this.getData().setRateParams(createdRate.params);
            this.getData().setCreatedRate(createdRate);
            this.getData().setOperatorsIcons(createdRate.icons);
            let tryCount = 0;
            let successRate = false;
            let progressiveTimeout = 0;
            let maxFillTimeLength = 25000;
            const startTime = new Date();
            while (successRate !== true) {
                if (this.removeOldPromiceSearch === true) {
                    research();
                    await Promise.reject(new Error('Reject'));
                }
                tryCount++;
                await new Promise(r => setTimeout(r, progressiveTimeout));
                if (progressiveTimeout==0) progressiveTimeout=3000;
                progressiveTimeout = progressiveTimeout * 1.3;

                let RatesDataCourier;
                let RatesDataPvz;
                const requests = [];
                if (!this.getParams().isOnlyPvz()) {
                    const RatesDataCourierRequest = this.getAjaxEngine().getRate(createdRate.key, this.getParams().getCourierType());
                    requests.push(RatesDataCourierRequest);
                }
                else {
                    requests.push(RatesDataCourier);
                }

                if (!this.getParams().isCourierOnly()) {
                    const RatesDataPvzrequest = this.getAjaxEngine().getRate(createdRate.key, this.getParams().getPvzType());
                    requests.push(RatesDataPvzrequest);
                }
                else {
                    requests.push(RatesDataPvz);
                }
                if (this.removeOldPromiceSearch === true) {
                    research();
                    await Promise.reject(new Error('Reject'));
                }
                [RatesDataCourier, RatesDataPvz] = await Promise.all(requests);
                let timeoutInterrupt = false;
                if (new Date() - startTime > maxFillTimeLength) {
                    timeoutInterrupt = true;
                }
                successRate = callBackGetRateFunction(RatesDataCourier, RatesDataPvz, timeoutInterrupt);
                if (timeoutInterrupt) {
                    successRate = true;
                }
            }
            return true;
        };
        renderTerminals = async (TerminalsData) => {
            this.getStructure().getPvzList().getChild('List').removeChilds();
            this.getMap().getClusterer().removeAll();
            let PvzList = this.getStructure().getPvzList().getChild('List');

            TerminalsData.forEach((TerminalData) => {
                if (PvzList.getChilds().has(TerminalData.id)) {
                    return;
                }
                TerminalData.coords = TerminalData.coordinates.split(';');

                const minData = this.getData().getPvzByOperatorMinData()[TerminalData.operator];
                let PvzItem = this.getStructure().makePvzItem({
                    id: TerminalData.id,
                    operator: TerminalData.operator,
                    address: TerminalData.address,
                    time: minData.date,
                    price: minData.price,
                });
                let PlaceMark = this.getMap().createPlaceMark(
                    TerminalData,
                    () => {
                        this.openDetailAction(PvzItem).then(this.loadSuccess)
                    }
                )
                this.getMap().getClusterer().add(PlaceMark);

                TerminalData.isVisible = true;
                TerminalData.wasVisible = true;
                TerminalData.isVisibleByOperator = true;
                TerminalData.isVisibleByPay = true;
                TerminalData.isVisibleByType = true;
                PvzItem.setData(TerminalData);
                PvzItem.setPlaceMark(PlaceMark);
                PvzItem.handleEvent('onclick', () => {
                    this.openDetailAction(PvzItem).then(this.loadSuccess)
                })

                PvzList.addChild(PvzItem);

            });
            this.getStructure().setPvzCountTitle(this.getData().getTerminals().length);
            let SidebarButtons = this.getStructure().getSideBarButtons();
            this.filterAction('pay', true, SidebarButtons.PayFilter.getData().filterBy).then();
            this.filterAction('type', true, SidebarButtons.TypeFilter.getData().filterBy).then();
        };
        openDetailAction = async Item => {
            this.getMap().resetPlacemarksZIndex();
            let percent = 10;
            let fakeLoader = setInterval(() => {
                percent+= 5;
                if (percent > 80) {
                    clearInterval(fakeLoader);
                }
                this.getStructure().getLoaderBar().setCss({width: percent + '%'});
            }, 100)
            let terminalData;
            if (this.getStructure().getDetailPanel().getData().loaded_terminals_ids.includes(Item.getData().id)) {
                terminalData = this.getStructure().getDetailPanel().getData().loaded_terminals_data[Item.getData().id];
            } else {
                terminalData = await this.getAjaxEngine().getTerminal(Item.getData().id);
                this.getStructure().getDetailPanel().getData().loaded_terminals_ids.push(Item.getData().id);
                this.getStructure().getDetailPanel().getData().loaded_terminals_data[Item.getData().id] = terminalData;
            }
            clearInterval(fakeLoader);
            this.getStructure().getSideBarButtons().PvzList.removeCurrent();
            this.getStructure().getSideBarButtons().OperatorFilter.removeCurrent();
            this.getStructure().getFilterList().hide();
            this.getStructure().getPvzList().show();
            this.getStructure().getPanel().setOpen();
            this.getStructure().getPanel().getChild('ListContainer').setCurrent();
            this.getStructure().getDetailPanel().setCurrent();

            this.getStructure().getSideBarButtons().PvzList.setCurrent();
            Item.getPlaceMark().options.set({zIndex:120});
            this.getMap().centerOnPlaceMark(Item.getPlaceMark());

            let DetailPanel = this.getStructure().makeDetailPanel(terminalData);

            if (this.getStructure().getDetailPanel().getChild('DetailBox')) {
                this.getStructure().getDetailPanel().replaceChild('DetailBox', DetailPanel);
            } else {
                this.getStructure().getDetailPanel().addChild(DetailPanel);
            }
            this.getStructure().getLoaderBar().setCss({width: '90%'});
        };
        openPvzAction = () => {
            this.getMap().resetView();
            this.getMap().resetCenter();
            this.getMap().getMap().setZoom(12);
            if (!this.getParams().isOnlyPvz()) this.getStructure().getVariantContainer().getChild('AddressWarnText').getElement().classList.add('mapmode');

            let Structure = this.getStructure();
            let ContainerDomElement = Structure.getContainer().getDomElement();
            ContainerDomElement.style.opacity = '0.7';

            if (!this.getParams().isOnlyPvz()) {
                let CourierButton = Structure.getVariantTypeItemContainer().getChild('CourierVariant');
                CourierButton.removeCurrent();
            }

            let PvzButton = Structure.getVariantTypeItemContainer().getChild('PvzVariant');
            PvzButton.setCurrent();
            this.getStructure().getPvzList().getChild('List').removeChilds();
            this.getMap().getClusterer().removeAll();

            this.getStructure().getSideBarButtons().OperatorFilter.hide();

            const afterGetData = () => {
                ContainerDomElement.style.opacity = '1';
                Structure.getBaseSearch().getElement().classList.add('mapmode');
                Structure.getBaseSearch().removeCurrent();
                Structure.getVariantContainer().getElement().classList.add('mapmode');
                Structure.getVariantContainer().removeCurrent();
                Structure.getContainer().getDomElement().classList.remove('second-step');
                this.step = 3;
                this.getStructure().getContainer().getDomElement().classList.remove('ctpt_close_popup_step');
                Structure.getMapSearchContainer().show();
                Structure.getMapBox().setCss({opacity: 1, 'pointer-events': 'auto'});

                if (!this.getParams().isOnlyPvz()) {
                    Structure.getMapTypeSelector().setCurrent();
                }

                Structure.getSidebar().setCurrent();

                let SidebarButtons = this.getStructure().getSideBarButtons();

                if (SidebarButtons.PayFilter) {
                    SidebarButtons.PayFilter.getData().filterBy = 'all';
                    SidebarButtons.PayFilter.getElement().childNodes[2].style.display = 'block';
                    SidebarButtons.PayFilter.getElement().childNodes[1].style.display = 'none';
                    SidebarButtons.PayFilter.getElement().childNodes[0].style.display = 'none';
                    this.filterAction('pay', true, 'all').then();
                }
                if (SidebarButtons.TypeFilter) {
                    SidebarButtons.TypeFilter.getData().filterBy = 'all';
                    SidebarButtons.TypeFilter.getElement().childNodes[0].style.display = 'block';
                    SidebarButtons.TypeFilter.getElement().childNodes[1].style.display = 'none';
                    SidebarButtons.TypeFilter.getElement().childNodes[2].style.display = 'none';
                    this.filterAction('type', true, 'all').then();
                }

            }
            afterGetData();

            this.getAjaxEngine().getTerminals(this.renderTerminals).then(this.renderFilterItems).catch(e => {
                if (e.message !== 'Reject') {
                } else {
                    this.onApiError(e)
                }
            });
        };
        renderFilterItems = () => {
            const PvzByOperatorsMinData = this.getData().getPvzByOperatorMinData();
            const Terminals = this.getData().getTerminals();
            const FilterListRef = this.getStructure().getFilterList().getChild('List');
            FilterListRef.removeChilds();
            if (Object.keys(PvzByOperatorsMinData).length > 1) {
                for (let operator_code in PvzByOperatorsMinData) {
                    const TerminalsForOperator = Terminals.filter((Terminal) => {
                        return Terminal.operator === operator_code
                    });
                    const date = PvzByOperatorsMinData[operator_code] ? PvzByOperatorsMinData[operator_code].item[0].cntdays : '';
                    const price = PvzByOperatorsMinData[operator_code] ? PvzByOperatorsMinData[operator_code].price : '';

                    let CurrentOperatorVariants = this.getData().getPvzByOperator()[operator_code];
                    let maxPrice = price;
                    if (CurrentOperatorVariants.length > 1) {
                        let ItemsWithValue = this.getData().getPvzByOperator()[operator_code].filter((Item) => {
                            return Item.price !== null && Item.price > 0;
                        })
                        let ItemsValue = ItemsWithValue.map((Item)=>{
                            return Item.price;
                        });
                        maxPrice = Math.max(...ItemsValue);
                    }

                    let FilterItem = this.getStructure().makeFilterItem({
                        operator: operator_code,
                        count: TerminalsForOperator.length,
                        time: date,
                        price: price,
                        max_price: maxPrice,
                    });
                    FilterItem.setData({
                        active: true,
                    })
                    FilterItem.handleEvent('onclick', () => {
                        const isSelected = FilterItem.getData().active;
                        if (isSelected) {
                            FilterItem.getData().active = false;
                            FilterItem.getElement().classList.add('ctpt-widget__disabled');
                            this.filterAction('filter', true, FilterItem.getName()).then();
                        } else {
                            FilterItem.getData().active = true;
                            FilterItem.getElement().classList.remove('ctpt-widget__disabled');
                            this.filterAction('filter', false, FilterItem.getName()).then();
                        }
                    })
                    FilterListRef.addChild(FilterItem);
                }
                this.getStructure().getSideBarButtons().OperatorFilter.setCss({display: 'flex'});
            } else {
                this.getStructure().getSideBarButtons().OperatorFilter.hide();
            }
            const payFilterBtns = this.getStructure().getSideBarButtons().PayFilter;
            if (this.getParams().getIsCashFilter() && !this.getParams().getIsCardFilter()) {
                this.filterAction('pay',true,'cache').then(); //card, cache
                payFilterBtns.getData().active = true;
                payFilterBtns.getData().filterBy = 'cache';
                payFilterBtns.setCurrent();
                this.getStructure().setPanelInfoPayTitle('cache');
                payFilterBtns.getElement().childNodes[0].style.display = 'none'; //card
                payFilterBtns.getElement().childNodes[1].style.display = 'block'; //cash
                payFilterBtns.getElement().childNodes[1].classList.add('dis');
                payFilterBtns.getElement().childNodes[2].style.display = 'none'; //all
            }
            if (this.getParams().getIsCardFilter() && !this.getParams().getIsCashFilter()) {
                this.filterAction('pay',true,'card').then();
                payFilterBtns.getData().active = true;
                payFilterBtns.getData().filterBy = 'card';
                payFilterBtns.setCurrent();
                this.getStructure().setPanelInfoPayTitle('card');
                payFilterBtns.getElement().childNodes[0].style.display = 'block';
                payFilterBtns.getElement().childNodes[0].classList.add('dis');
                payFilterBtns.getElement().childNodes[1].style.display = 'none';
                payFilterBtns.getElement().childNodes[2].style.display = 'none';
            }
            this.loadSuccess();
        };
        filterAction = async (by, action, code) => {
            const listAndPlaceMarkAction = (item) => {
                item.getData().isVisible = item.getData().isVisibleByPay === true
                    && item.getData().isVisibleByType === true
                    && item.getData().isVisibleByOperator === true;
                if (item.getData().isVisible === true && item.getData().wasVisible === false) {
                    item.setCss({display: 'flex'});
                    this.getMap().getClusterer().add(item.getPlaceMark());
                    item.getPlaceMark().options.set('visible', true);
                }
                if (item.getData().isVisible === false && item.getData().wasVisible === true) {
                    item.hide();
                    item.getPlaceMark().options.set('visible', false);
                    this.getMap().getClusterer().remove(item.getPlaceMark());
                }
            };
            const filterByFunction = async (filterCallback) => {
                let countPvz = 0;
                await this.getStructure().getPvzList().getChild('List').getChilds().each(async (item) => {
                    item.getData().wasVisible = item.getData().isVisible;
                    await filterCallback(item);
                    if (item.getData().isVisible) {
                        countPvz++;
                    }
                });
                this.getStructure().setPvzCountTitle(countPvz);
            };

            switch (by) {
                case 'filter':
                    let ActiveFilterIcon = this.getStructure().getSideBarButtons().OperatorFilter.getElement().childNodes[2];
                    let IsActiveFilter = false;
                    await filterByFunction(async (item) => {
                        const isFiltered = item.getData().operator === code;
                        if (!isFiltered) {
                            IsActiveFilter = item.getData().isVisibleByOperator === false ? true : IsActiveFilter;
                            return;
                        }
                        item.getData().isVisibleByOperator = !action;
                        IsActiveFilter = item.getData().isVisibleByOperator === false ? true : IsActiveFilter;
                        listAndPlaceMarkAction(item);
                    })
                    if (IsActiveFilter) {
                        ActiveFilterIcon.classList.add('current');
                    } else {
                        ActiveFilterIcon.classList.remove('current');
                    }
                    break;
                case 'pay':
                    await filterByFunction(async (item) => {
                        switch (code) {
                            case 'all':
                                item.getData().isVisibleByPay = true;
                                break;
                            case 'card':
                                item.getData().isVisibleByPay = item.getData().card;
                                break;
                            case 'cache':
                                item.getData().isVisibleByPay = item.getData().cash;
                                break;
                        }
                        listAndPlaceMarkAction(item);
                    });
                    break;
                case 'type':
                    await filterByFunction(async (item) => {
                        switch (code) {
                            case 'all':
                                item.getData().isVisibleByType = true;
                                break;
                            case 'terminal':
                                item.getData().isVisibleByType = item.getData()['point-type'] === '1';
                                break;
                            case 'pvz':
                                item.getData().isVisibleByType = item.getData()['point-type'] === '2';
                                break;
                        }
                        listAndPlaceMarkAction(item);
                    });
                    break;
            }
        };
        loadSuccess = () => {
            this.getStructure().getLoaderBar().setCss({width: '0%'});
        };
        reopenCourierAction = () => {
            this.getMap().resetZoom();
            let Structure = this.getStructure();

            if (!this.getParams().isOnlyPvz()) {
                this.getStructure().getVariantContainer().getChild('AddressWarnText').getElement().classList.remove('mapmode');
                let CourierButton = this.getStructure().getVariantTypeItemContainer().getChild('CourierVariant');
                CourierButton.setCurrent();
            }

            let PvzButton = this.getStructure().getVariantTypeItemContainer().getChild('PvzVariant');
            PvzButton.removeCurrent();

            const afterGetData = () => {
                Structure.getBaseSearch().show();

                if (!this.getParams().isOnlyPvz()) {
                    Structure.getContainer().getDomElement().classList.add('second-step');
                    if (this.getParams().isPopupMode()) {
                        Structure.getContainer().getDomElement().classList.add('ctpt_close_popup_step');
                    }
                    this.step = 1;
                    Structure.getContainer().getDomElement().classList.remove('ctpl_popup_first_step');
                    Structure.getMapTypeSelector().removeCurrent();
                    Structure.getBaseSearch().setCurrent();
                    Structure.getBaseSearch().getElement().classList.remove('mapmode');

                }
                Structure.getVariantContainer().setCurrent();
                Structure.getVariantContainer().getElement().classList.remove('mapmode');
                Structure.getMapSearchContainer().hide();
                Structure.getMapBox().setCss({opacity: 0, 'pointer-events': 'none'});
                Structure.getSidebar().removeCurrent();
                Structure.getPanel().removeOpen();
            }

            afterGetData();

        }
        submitSearch = () => {
            if (this.SelectedDadataVariant === false) {
                return;
            }
            if (this.InRequest === true) {
                this.removeOldPromiceSearch = true;
                this.getStructure().getVariantContainer().removeCurrent();
                return;
            }
            this.InRequest = true;
            this.TerminalsPromiceCancel = true
            this.SearchedCity = this.SelectedDadataVariant.data.city_with_type;
            let selectedCity = this.SelectedDadataVariant.value;
            let iso = this.SelectedDadataVariant.data.country_iso_code.toLowerCase();
            this.getStructure().getBaseSearchInput().getElement().value = selectedCity;
            this.getStructure().getBaseSearchLabel().setCss({'margin-top':'-17px'});
            if (!this.getParams().isCourierOnly()) {
                this.getStructure().getMapSearchInput().getElement().value = selectedCity;
                this.getMap().getMap().setCenter([
                    this.SelectedDadataVariant.data.geo_lat,
                    this.SelectedDadataVariant.data.geo_lon
                ]);
                let zoom = this.SelectedDadataVariant.data.street_with_type !== null ? 14 : 12;
                this.resetCenter();
                this.getMap().recenterMap(zoom);
            }

            this.suggestAction(false);

            this.getStructure().getSidebar().removeCurrent();
            this.getStructure().getVariantContainer().setCurrent();
            this.getStructure().getVariantContainer().getElement().classList.remove('mapmode');
            if (!this.getParams().isOnlyPvz()) {
                this.getStructure().getBaseSearch().setCurrent();
                this.getStructure().getBaseSearch().getElement().classList.remove('mapmode');
                this.getStructure().getContainer().getDomElement().classList.add('second-step');
                this.getStructure().getContainer().getDomElement().classList.remove('ctpl_popup_first_step');
                if (this.getParams().isPopupMode()) {
                    this.getStructure().getContainer().getDomElement().classList.add('ctpt_close_popup_step');
                }
                this.step = 1;
                this.getStructure().getMapBox().setCss({opacity: 0, 'pointer-events': 'none'});

            }
            this.getStructure().getBaseSearch().getChild('Title').hide();
            this.getStructure().getBaseSearch().getChild('Description').hide();
            this.getStructure()
                .getVariantContainer()
                .getChild('ItemsBaseContainer')
                .getChild('LoaderBody').setCss({display: 'flex'});
            this.getStructure().getBaseSearchSubmitButton().getElement().classList.add('loading');


            let Assets = this.getAssets();

            this.getStructure().getVariantList().removeChilds();

            let VariantTypeContainer = this.getStructure().getVariantTypeItemContainer();

            if (!this.getParams().isOnlyPvz()) {
                VariantTypeContainer.replaceChild('CourierVariant', this.getStructure().createVariant(
                    'CourierVariant',
                    Assets.getDeliveryVariantCourierTitle(),
                    '',
                    ''
                ));
            }

            if (!this.getParams().isCourierOnly()) {
                VariantTypeContainer.replaceChild('PvzVariant',this.getStructure().createVariant(
                    'PvzVariant',
                    Assets.getDeliveryVariantPvzTitle(),
                    '',
                    '',
                    'disabled'
                ))
            }
            const EndGetVariants = () => {
                this.getStructure()
                    .getVariantContainer()
                    .getChild('ItemsBaseContainer')
                    .getChild('LoaderBody').hide();
                this.getStructure().getBaseSearchSubmitButton().getElement().classList.remove('loading');
                this.suggestAction(true);
            }
            const selected_city_postcode = this.SelectedDadataVariant.data.postal_code;
            let selected_city_value = null;
            if (this.SelectedDadataVariant.data.city) {
                selected_city_value = this.SelectedDadataVariant.data.city;
            } else if (this.SelectedDadataVariant.data.settlement) {
                selected_city_value = this.SelectedDadataVariant.data.settlement;
            }
            let PvzTypeVariant,CourierTypeVariant;
            this.createRateAction(selected_city_postcode, selected_city_value, iso, (RatesDataCourier, RatesDataPvz, isTimeoutInterrupt = false) => {
                let showWarnMessage = false;
                const receiverLocality = this.getData().getSelectedCity().api.contact ? Number(this.getData().getSelectedCity().api.contact.locality_id) : Number(this.getData().getSelectedCity().api.receiver_locality_id);
                const senderLocality = this.getData().getSelectedCity().api.sender ? Number(this.getData().getSelectedCity().api.sender.locality_id) : Number(this.getData().getSelectedCity().api.sender_locality_id);
                if (!this.getParams().isCourierOnly()) { //PVZ Tarifs
                    if (
                        (receiverLocality == senderLocality)
                        && (this.getData().getSelectedCity().dadata.data.house == null)
                        && (!this.getParams().isNeedInsurance())
                        && (this.getParams().getCourierType() == 'd2d')
                    ) {
                        showWarnMessage = true;
                        for (let i in RatesDataPvz.results) {
                            if ( (RatesDataPvz.results[i].operator == 'dostavista') || (RatesDataPvz.results[i].operator == 'yandex_dostavka') ) {
                                showWarnMessage = false;
                            }
                        }
                    }
                }
                if (!this.getParams().isOnlyPvz()) { //Couries Tarifs
                    if (
                        (receiverLocality == senderLocality)
                        && (this.getData().getSelectedCity().dadata.data.house == null)
                        && (!this.getParams().isNeedInsurance())
                        && (this.getParams().getCourierType() == 'd2d')
                    ) {
                        showWarnMessage = true;
                        for (let i in RatesDataCourier.results) {
                            if ( (RatesDataCourier.results[i].operator == 'dostavista') || (RatesDataCourier.results[i].operator == 'yandex_dostavka') ) {
                                showWarnMessage = false;
                            }
                        }
                    }
                }

                if (!this.getParams().isCourierOnly()) {
                    this.getData().setPvzItems(RatesDataPvz.results);
                    const minPvzData = this.getData().calkMinDatePrice(this.getData().getPvzItems());
                    PvzTypeVariant = this.getStructure().createVariant(
                        'PvzVariant',
                        Assets.getDeliveryVariantPvzTitle(),
                        (minPvzData.item!=undefined)?minPvzData.item[1]:Infinity,
                        (minPvzData.item!=undefined)?minPvzData.item[0].cntdays:Infinity,
                        'disabled'
                    );
                    VariantTypeContainer.replaceChild(PvzTypeVariant.getName(), PvzTypeVariant);
                }

                if (!this.getParams().isOnlyPvz()) {
                    if (showWarnMessage) {
                        this.getStructure().getVariantContainer().getChild('AddressWarnText').getElement().classList.remove('hide');
                        this.getStructure().getVariantContainer().getChild('ItemsBaseContainer').getElement().classList.add('warnmode');
                    } else {
                        this.getStructure().getVariantContainer().getChild('AddressWarnText').getElement().classList.add('hide');
                        this.getStructure().getVariantContainer().getChild('ItemsBaseContainer').getElement().classList.remove('warnmode');
                    }
                    this.getData().setCourierItems(RatesDataCourier.results);
                    const minCourierData = this.getData().calkMinDatePrice(this.getData().getCourierItems());
                    CourierTypeVariant = this.getStructure().createVariant(
                        'CourierVariant',
                        Assets.getDeliveryVariantCourierTitle(),
                        (minCourierData.item!=undefined)?minCourierData.item[1]:Infinity,
                        (minCourierData.item!=undefined)?minCourierData.item[0].cntdays:Infinity,
                    );
                    CourierTypeVariant.setCurrent();
                    VariantTypeContainer.replaceChild(CourierTypeVariant.getName(), CourierTypeVariant);

                    let CourierVariantItemList = this.getStructure().getVariantList();
                    /**
                     * Set variants;
                     */
                    {
                        for (let i in RatesDataCourier.results) {
                            let CourierVariantItem = RatesDataCourier.results[i];
                            if (!CourierVariantItemList.getChilds().has(CourierVariantItem.id)) {
                                let CourierVariantItemObject = this.getStructure().createVariantItem(CourierVariantItem);
                                CourierVariantItemList.addChild(CourierVariantItemObject);
                                let isLoading = false;
                                CourierVariantItemObject.getChild('PriceInfo')
                                    .getChild('PriceButton')
                                    .handleEvent('onclick', (e) =>
                                    {
                                        e.preventDefault();
                                        if (isLoading) {
                                            return;
                                        }
                                        isLoading = true;
                                        const AfterGetData = (Tariff) => {
                                            Tariff.Variant = CourierVariantItemObject.getData();
                                            let CreatedItem = this.getStructure().createVariantTimeSelector(Tariff, (e, Data) => {
                                                CreatedItem.getElement().classList.remove('is-visible');
                                                if (this.getParams().hasHandlerOnSelectCourierItem()) {
                                                    this.getParams().getOnSelectCourierItemHandler()(Data, this);
                                                }
                                            });
                                            if (this.getStructure().getDetailTariff() !== false) {
                                                this.getStructure().getDetailTariff().getElement().remove();
                                                delete this.getStructure().getDetailTariff();
                                            }
                                            this.getStructure().setDetailTariff(CreatedItem);
                                            this.getStructure().getContainer().getDomElement().appendChild(CreatedItem.getElement());
                                            isLoading = false;
                                        }
                                        if (this.getData().getTariffs()[CourierVariantItemObject.getData().id]) {
                                            AfterGetData(this.getData().getTariffs()[CourierVariantItemObject.getData().id])
                                        } else {
                                            this.getAjaxEngine().getTariff(CourierVariantItemObject.getData().id).then(AfterGetData).catch(e => {
                                                this.onApiError(e);
                                            });
                                        }
                                    })
                            }
                        }
                    }
                    this.sortBy(CourierVariantItemList, this.getData().getLastSort());
                }
                if (!this.getParams().isCourierOnly()) {
                    if ((RatesDataPvz.rate_completed === true) || isTimeoutInterrupt) {
                        let PvzByOperator = {};
                        RatesDataPvz.results.forEach((Rate) => {
                            if (!PvzByOperator[Rate.operator]) {
                                PvzByOperator[Rate.operator] = [];
                            }
                            PvzByOperator[Rate.operator].push(Rate);
                        });
                        let PvzByOperatorMinData = {};
                        for (let operator_code in PvzByOperator) {
                            PvzByOperatorMinData[operator_code] = this.getData().calkMinDatePrice(PvzByOperator[operator_code]);
                        }
                        this.getData().setPvzByOperator(PvzByOperator);
                        this.getData().setPvzByOperatorMinData(PvzByOperatorMinData);
                    }
                }
                if (isTimeoutInterrupt) return true;
                if (this.getParams().isCourierOnly()) {
                    return RatesDataCourier.rate_completed === true;
                } else if (this.getParams().isOnlyPvz()) {
                    return RatesDataPvz.rate_completed === true;
                } else {
                    return RatesDataPvz.rate_completed === true && RatesDataCourier.rate_completed === true;
                }
            }).then(Success => {
                this.TerminalsPromiceCancel = false;
                if (!this.getParams().isCourierOnly()) {
                    if (this.getData().getPvzItems().length > 0) {
                        PvzTypeVariant.handleEvent('onclick', ()=>{
                            if (!this.getStructure().getBaseSearch().getElement().classList.contains('mapmode')) this.openPvzAction();
                        });
                        PvzTypeVariant.show();
                        PvzTypeVariant.getElement().classList.remove('disabled');
                    } else {
                        PvzTypeVariant.hide();
                    }

                    if (!this.getParams().isOnlyPvz())
                        CourierTypeVariant.handleEvent('onclick', () => {
                            if (this.getStructure().getBaseSearch().getElement().classList.contains('mapmode')) {
                                if (this.SearchedCity !== this.SelectedDadataVariant.data.city_with_type) {
                                    this.reopenCourierAction();
                                    this.submitSearch();
                                } else {
                                    this.reopenCourierAction();
                                }
                            }
                        });
                }
                for (let operator_code in this.getData().getOperatorsIcons()) {
                    let operatorIcons = this.getData().getOperatorsIcons()[operator_code];
                    if (operatorIcons.small_icon) {
                        this.getMap().setOperatorIcon(operator_code, operatorIcons.small_icon);
                    }
                }
                if (this.getParams().isOnlyPvz()) {
                    this.openPvzAction();
                }
                EndGetVariants();
                this.InRequest = false;

            }).catch(e => {
                if (e.message !== 'Reject') {
                    EndGetVariants();
                    this.onApiError(e);
                }
            });
        };
        suggestAction = (show = true) => {
            let BaseSearchForm = this.getStructure().getBaseSearchForm().getElement();
            if (BaseSearchForm.childNodes[0]
                && BaseSearchForm.childNodes[0].tagName === 'YMAPS') {
                if (show) {
                    BaseSearchForm.childNodes[0].classList.remove('ctpl-ymap-suggest-hidden');
                } else {
                    BaseSearchForm.childNodes[0].classList.add('ctpl-ymap-suggest-hidden');
                    setTimeout(() => {
                        BaseSearchForm.childNodes[0].style.display = 'none';
                    }, 200);
                }
            }

            if (!this.getParams().isCourierOnly()) {
                let MapSearchForm = this.getStructure()
                    .getMapSearchContainer()
                    .getChild('MapSearchContainer')
                    .getChild('MapSearchInputContainer');
                if (MapSearchForm.getElement().childNodes[1] &&
                    MapSearchForm.getElement().childNodes[1].tagName === 'YMAPS') {
                    if (show) {
                        MapSearchForm.getElement().childNodes[1].classList.remove('ctpl-ymap-suggest-hidden');
                    } else {
                        MapSearchForm.getElement().childNodes[1].classList.add('ctpl-ymap-suggest-hidden');
                        setTimeout(() => {
                            MapSearchForm.getElement().childNodes[1].style.display = 'none';
                        }, 200);
                    }
                }
            }
        }
        onApiError = (e) => {
            if (e.message === 'Failed to fetch') {
                return;
            }
            console.trace(e)
            let errorItem = new HtmlItem('error');
            errorItem.addChildMultiple([
                new HtmlItem('title', {
                    innerHTML: 'API ERROR',
                }),
                new HtmlItem('body', {
                    innerHTML: this.getAssets().getApiErrorText(),
                })
            ]);
            let errorCode = '';
            if (e.message.indexOf('R400')>=0) {
                errorCode = 'R400';
            } else if (e.message.indexOf('R401')>=0) {
                errorCode = 'R401';
            } else if (e.message.indexOf('R403')>=0) {
                errorCode = 'R403';
            } else if (e.message.indexOf('R404')>=0) {
                errorCode = 'R404';
            } else if (e.message.indexOf('R500')>=0) {
                errorCode = 'R500';
            } else if (e.message.indexOf('R502')>=0) {
                errorCode = 'R502';
            } else if (e.message.indexOf('R429')>=0) {
                errorCode = 'R429';
            }
            switch (errorCode) {
                case 'R400':
                    errorItem.addChild(new HtmlItem('error_text',{
                        innerHTML: this.getAssets().getTextByCode('api_error_text_r400'),
                    }));
                    break;
                case 'R401':
                    errorItem.addChild(new HtmlItem('error_text',{
                        innerHTML: this.getAssets().getTextByCode('api_error_text_r401'),
                    }));
                    errorItem.addChild(new HtmlItem('error_text1',{
                        innerHTML: e.message,
                    }));
                    break;
                case 'R403':
                    errorItem.addChild(new HtmlItem('error_text',{
                        innerHTML: this.getAssets().getTextByCode('api_error_text_r403'),
                    }));
                    break;
                case 'R404':
                    errorItem.addChild(new HtmlItem('error_text',{
                        innerHTML: this.getAssets().getTextByCode('api_error_text_r404'),
                    }));
                    break;
                case 'R500':
                    errorItem.addChild(new HtmlItem('error_text',{
                        innerHTML: this.getAssets().getTextByCode('api_error_text_r500'),
                    }));
                    break;
                case 'R502':
                    errorItem.addChild(new HtmlItem('error_text',{
                        innerHTML: this.getAssets().getTextByCode('api_error_text_r502'),
                    }));
                    break;
                case 'R429':
                    errorItem.addChild(new HtmlItem('error_text',{
                        innerHTML: this.getAssets().getTextByCode('api_error_text_r429'),
                    }));
                    break;
            }
            errorItem.setCss({
                maxWidth: '100%',
                maxHeight: '100%',
                overflow: 'hidden',
                padding: '15px',
                textAlign: 'center',
                lineHeight: '35px',
                paddingBottom: '0',
            });

            let ButtonRefresh = new HtmlItem('RefreshButton', {
                innerHTML: this.getAssets().getRefreshBtnText(),
                classList: ['ctpt-widget__search-button'],
            });

            ButtonRefresh.setCss({
                textAlign: 'center',
                width: '160px',
                margin: '40px auto',
                display: 'block',
                lineHeight: '55px',
            })
            ButtonRefresh.addEventListener('click', (e) => {
                e.preventDefault();
                this.refresh();
            })
            let ErrorContainer = (new HtmlItem('ErrorContainer')).addChildMultiple([
                errorItem,
                ButtonRefresh
            ]);

            let domContainer = this.getStructure().getContainer().getDomElement();
            while (domContainer.firstChild) {
                domContainer.removeChild(domContainer.firstChild);
            }
            domContainer.appendChild(ErrorContainer.getElement());

            if (this.getParams().isPopupMode()) {
                let PopupModeButton = new HtmlItem('PopupModeButton', {
                    classList: ['ctpl_popup_mode_close'],
                });
                PopupModeButton.handleEvent('onclick', () => {
                    this.hide();
                });
                domContainer.appendChild(PopupModeButton.getElement());
            }

            this.InRequest = false;

        };
        // helpers methods
        sortBy = (Container, key = 'speed') => {
            this.getData().setLastSort(key);
            let HtmlItemArray = [];
            Container.getChilds().each((HtmlItem) => {
                HtmlItemArray.push(HtmlItem);
            });
            let sortFunction;
            switch (key) {
                case "rate":
                    sortFunction = (a, b) => {
                        const idA = a.getData()[ApiObjectKeys.rating];
                        const idB = b.getData()[ApiObjectKeys.rating];
                        if (idA < idB) {
                            return 1;
                        }
                        if (idA > idB) {
                            return -1;
                        }
                        return 0;
                    };
                    break;
                case "price":
                    sortFunction = (a, b) => {
                        const aPrice = a.getData().price >= 0 ? a.getData().price : Infinity;
                        const bPrice = b.getData().price >= 0 ? b.getData().price : Infinity;
                        if (aPrice > bPrice) {
                            return 1;
                        }
                        if (aPrice < bPrice) {
                            return -1;
                        }
                        return 0;
                    };
                    break;
                case "speed":
                default:
                    sortFunction = (a, b) => {
                        const AtimeDiff = (new Date(a.getData().delivery_day)).getTime();
                        const BtimeDiff = (new Date(b.getData().delivery_day)).getTime();
                        if (AtimeDiff > BtimeDiff) {
                            return 1;
                        }
                        if (AtimeDiff < BtimeDiff) {
                            return -1;
                        }
                        const aPrice = a.getData().price >= 0 ? a.getData().price : Infinity;
                        const bPrice = b.getData().price >= 0 ? b.getData().price : Infinity;
                        if (aPrice > bPrice) {
                            return 1;
                        }
                        if (aPrice < bPrice) {
                            return -1;
                        }
                        return 0;
                    };
                    break;
            }
            HtmlItemArray.sort(sortFunction)
            HtmlItemArray.forEach((HtmlItem) => {
                Container.getElement().appendChild(HtmlItem.getElement())
            });
            return HtmlItemArray;
        };
        getShortDataForItem = (item) => {
            let operatorName;
            let price = false;
            let date = false;
            if (item.getData().variant) {
                operatorName = this.getData().getOperatorsIcons()[item.getData().variant.operator].operator_display;
                price = 'Стоимость : ' + this.getAssets().getDeliveryVariantItemPrice(item.getData().variant.price);
                date = item.getData().date;
            } else {
                operatorName = this.getData().getOperatorsIcons()[item.getData().Variant.operator].operator_display;
                price = 'Стоимость : ' + this.getAssets().getDeliveryVariantItemPrice(item.getData().Variant.price);
                date = item.getData().Variant[ApiObjectKeys.delivery_date];
            }
            let address = false;
            if (item.getData().Terminal) {
                address = 'Адрес: ' + item.getData().Terminal.address;
            }
            if (date) {
                date = 'Дата: '+ new Intl.DateTimeFormat(
                    'ru-RU',
                    {day: '2-digit', month: 'short', year: 'numeric'}
                ).format(new Date(date));
            }
            let title = 'Успешно выбрана доставка: ' + operatorName;
            return {
                title: title,
                date: date,
                price: price,
                address: address,
            }
        };
        // Widget method
        reinitialize = (params) => {
            this.destroy();
            this.Params = new ParamsCollections(params, this);
            this.ready();
        };
        destroy = () => {
            if (this.getMap().getMap()) {
                this.getMap().getMap().destroy();
            }
            let domContainer = this.getStructure().getContainer().getDomElement();
            while (domContainer.firstChild) {
                domContainer.removeChild(domContainer.firstChild);
            }
            Structure.removeStyles();
        };
        refresh = () => {
            const Params = this.Params;
            this.destroy();
            this.Params = Params;
            this.getStructure().Structure.Container.domElement = false;
            this.step = 0;
            this.ready();
        };
        show = () => {
            this.getStructure().getContainer().getDomElement().style.display = 'flex';
            this.ClosedPopup = false;
        };
        hide = () => {
            this.ClosedPopup = true;
            this.getStructure().getContainer().getDomElement().style.display = 'none';
            if (this.getParams().hasHandlerOnPopupClose()) {
                this.getParams().getOnPopupCloseHandler()(this);
            }
        };
        createStructure = () => {
            this.getStructure().createAll();
            let Structure = this.getStructure();

            let BaseSubmitButton = Structure.getBaseSearchSubmitButton();
            let BaseSearchInput = Structure.getBaseSearchInput();
            let BaseSearchLabel = Structure.getBaseSearchLabel();

            let MapSearchInput = false;
            let MapSearchSubmitButton = false;
            let needResearch = false;
            if (!this.getParams().isCourierOnly()) {
                MapSearchInput = Structure.getMapSearchInput();
                MapSearchSubmitButton = Structure.getMapSearchSubmitButton();
                this.getStructure().getMapSelectorCourier().handleEvent('onclick', () => {
                    if (this.SearchedCity !== this.SelectedDadataVariant.data.city_with_type || needResearch) {
                        needResearch = false;
                        this.reopenCourierAction();
                        this.submitSearch();
                    } else {
                        this.reopenCourierAction();
                    }
                });
            }
            const selectItem = (e, isMapSearch, Variant) => {
                if (isMapSearch && this.SearchedCity === Variant.data.city_with_type) {
                    this.getMap().getMap().setCenter([
                        Variant.data.geo_lat,
                        Variant.data.geo_lon
                    ]);
                    this.getMap().getMap().setZoom(14);
                    this.SelectedDadataVariant = Variant;
                    this.resetCenter();
                    needResearch = true;
                    e.preventDefault();
                } else {
                    this.SelectedDadataVariant = Variant;
                }
            }
            /**
             * Bind DaData Suggest for search inputs
             */
            {
                let InputsForSuggestBind = [BaseSearchInput, MapSearchInput];
                const dadataSuggestRequest = (requestString, inputElement) => {
                    inputElement.setData([]);
                    this.getAjaxEngine().getSuggestion(requestString, 5).then((Result) => {
                        let ResultsList = [];

                        /**
                         * @type {{suggestions: [Suggestion]}}
                         */
                        let Suggestions = Result.suggestions;
                        for (let i in Suggestions) {
                            /**
                             * @type Suggestion
                             */
                            let Suggestion = Suggestions[i];

                            if (Suggestion.data.postal_code === null) {
                                if (Suggestion.data.house === null)
                                    Suggestion.data.postal_code = '0';
                                else
                                    continue;
                            }

                            let HtmlValue = new HtmlItem('Suggestion', {
                                classList: 'ctpl-suggestion-block',
                                'data-index': i,
                            });
                            if (Suggestion.data.city_with_type === Suggestion.value || Suggestion.data.city_with_type === null) {
                                HtmlValue.addChild(new HtmlItem('FullName', {
                                    innerHTML: Suggestion.value,
                                    classList: 'ctpl-suggestion-full-name',
                                }));
                            } else {
                                HtmlValue.addChild(new HtmlItem('BaseName', {
                                    innerHTML: Suggestion.data.city_with_type ? Suggestion.data.city_with_type : '',
                                    classList: 'ctpl-suggestion-base-name',
                                }));
                                HtmlValue.addChild(new HtmlItem('FullName', {
                                    innerHTML: Suggestion.unrestricted_value.replace(Suggestion.data.city_with_type + ', ',''),
                                    classList: 'ctpl-suggestion-full-name',
                                }));
                                HtmlValue.getChild('BaseName').getElement().dataset.index = i;

                            }
                            HtmlValue.getChild('FullName').getElement().dataset.index = i;

                            ResultsList.push({displayName: HtmlValue.getElement().outerHTML, value: Suggestion.value, variant: Suggestion});
                        }
                        inputElement.getSuggest()._onDataRequestSuccess(ResultsList);
                        inputElement.setData(ResultsList)
                    }).catch(err => {
                        this.onApiError(err)
                    });
                }
                for (let key in InputsForSuggestBind) {
                    const inputElement = InputsForSuggestBind[key];

                    if (!inputElement) {
                        continue;
                    }
                    const isMapSearch = +key === 1;
                    let InputDadataProvider = {
                        suggest: function (requestString/*, options*/) {
                            dadataSuggestRequest(requestString, inputElement)
                            return CatapultoYmapNamespace.vow.resolve([]);
                        }
                    };
                    let suggestViewForCurrentInput = new CatapultoYmapNamespace.SuggestView(
                        inputElement.getElement().id,
                        {provider: InputDadataProvider, results: 5}
                    );
                    suggestViewForCurrentInput.events.add('select', (e) => {
                        this.getStructure().getBaseSearchInput().setCss({fontSize: '16px'});
                        let Variant = e.originalEvent.item.variant;
                        selectItem(e, isMapSearch, Variant);
                        inputElement.selectBySuggest = true;
                    });

                    inputElement.setSuggest(suggestViewForCurrentInput);

                }
            }

            /**
             * Bind Search Inputs Handlers
             */
            {
                /**
                 * Label actions
                 */
                if (!this.getParams().isCourierOnly()) {
                    MapSearchSubmitButton.handleEvent('onclick', (e) => {
                        e.preventDefault();
                        this.reopenCourierAction();
                        this.submitSearch();
                    });
                    MapSearchInput.addEventListener('keydown', (event) => {
                        if(event.code === 'Enter') {
                            event.preventDefault();
                            if (MapSearchInput.getData()[0] && MapSearchInput.selectBySuggest !== true) {
                                selectItem(event, true, MapSearchInput.getData()[0].variant);
                            }

                            if (this.SearchedCity !== this.SelectedDadataVariant.data.city_with_type) {
                                this.reopenCourierAction();
                                this.submitSearch();
                            }
                        }
                        MapSearchInput.selectBySuggest = false;
                    });
                }

                const labelHiderHandler = (e) => {
                    if (this.InRequest) {
                        this.suggestAction(true);
                    }
                    let needDeFocus = e.type === 'blur' && BaseSearchInput.getElement().value === '';
                    if (needDeFocus) {
                        BaseSearchLabel.setCss({
                            'margin-top':'',
                            fontSize: '20px',
                        });
                    } else {
                        BaseSearchLabel.setCss({
                            'margin-top':'-17px',
                            fontSize: '12px',
                        });
                    }
                    if (e.type === 'focus') {
                        BaseSearchInput.setCss({fontSize: '20px'});
                    }
                    if ( this.getStructure().getPanel().getElement().classList.contains('open') ) {
                        this.getStructure().getPanel().getElement().classList.remove('open');
                        this.getStructure().getSidebar().getChild('ButtonContainer').getChild('PvzList').removeCurrent();
                        this.getStructure().getSidebar().getChild('ButtonContainer').getChild('OperatorFilter').removeCurrent();
                    }
                }
                BaseSearchInput.handleEvent('onblur', labelHiderHandler)
                BaseSearchInput.handleEvent('onfocus', labelHiderHandler)

                const onclickInputHandler = (e) => {
                    e.preventDefault();
                    if (Structure.getBaseSearch().getElement().classList.contains('mapmode')) {
                        if (this.getData().getSelectedCity().dadata.data.city_with_type == this.SelectedDadataVariant.data.city_with_type) {
                            this.getMap().getMap().setCenter([
                                this.SelectedDadataVariant.data.geo_lat,
                                this.SelectedDadataVariant.data.geo_lon
                            ]);
                            this.getMap().getMap().setZoom(17);
                        } else {
                            this.reopenCourierAction();
                            this.submitSearch();
                        }
                    } else this.submitSearch();
                }

                BaseSubmitButton.handleEvent('onclick', onclickInputHandler);
                BaseSearchInput.addEventListener('keydown', (event) => {
                    this.suggestAction(true);
                    if(event.code === 'Enter') {
                        event.preventDefault();
                        if (BaseSearchInput.getData()[0] && BaseSearchInput.selectBySuggest !== true) {
                            selectItem(event, false, BaseSearchInput.getData()[0].variant);
                        }
                    }
                    BaseSearchInput.selectBySuggest = false;
                    if (this.SelectedDadataVariant !== false && event.code === 'Enter') {
                        BaseSearchInput.selectBySuggest = false;
                        if (Structure.getBaseSearch().getElement().classList.contains('mapmode')) {
                            if (this.getData().getSelectedCity().dadata.data.city_with_type == this.SelectedDadataVariant.data.city_with_type) {
                                this.getMap().getMap().setCenter([
                                    this.SelectedDadataVariant.data.geo_lat,
                                    this.SelectedDadataVariant.data.geo_lon
                                ]);
                                this.getMap().getMap().setZoom(17);
                            } else this.submitSearch();
                        } else this.submitSearch();
                    }
                });
            }
            /**
             * Bind variant type select
             */
            if (!this.getParams().isCourierOnly()) {
                let SidebarButtons = Structure.getSideBarButtons();
                SidebarButtons.PayFilter.setData({
                    active: false,
                    filterBy: 'all'
                })
                let allSvg = SidebarButtons.PayFilter.getElement().childNodes[2];
                let cacheSvg = SidebarButtons.PayFilter.getElement().childNodes[1];
                let cardSvg = SidebarButtons.PayFilter.getElement().childNodes[0];


                SidebarButtons.PayFilter.handleEvent('onclick', () => {
                    if (this.getParams().getIsCardFilter()!==this.getParams().getIsCashFilter()) return false;
                    const active = SidebarButtons.PayFilter.getData().active;
                    if (!active) {
                        SidebarButtons.PayFilter.getData().active = true;
                        SidebarButtons.PayFilter.getData().filterBy = 'cache';
                    } else if (active && SidebarButtons.PayFilter.getData().filterBy === 'cache') {
                        SidebarButtons.PayFilter.getData().filterBy = 'card';
                    } else {
                        SidebarButtons.PayFilter.getData().filterBy = 'all';
                        SidebarButtons.PayFilter.getData().active = false;
                    }
                    if (SidebarButtons.PayFilter.getData().active) {
                        SidebarButtons.PayFilter.setCurrent();
                    } else {
                        SidebarButtons.PayFilter.removeCurrent();
                    }
                    switch (SidebarButtons.PayFilter.getData().filterBy) {
                        case 'all':
                            cardSvg.style.display = 'none';
                            cacheSvg.style.display = 'none';
                            allSvg.style.display = 'block';
                            break;
                        case 'card':
                            cacheSvg.style.display = 'none';
                            allSvg.style.display = 'none';
                            cardSvg.style.display = 'block';
                            break;
                        case 'cache':
                            cardSvg.style.display = 'none';
                            allSvg.style.display = 'none';
                            cacheSvg.style.display = 'block';
                            break;
                    }
                    Structure.setPanelInfoPayTitle(SidebarButtons.PayFilter.getData().filterBy);

                    this.filterAction('pay', true, SidebarButtons.PayFilter.getData().filterBy).then();
                });

                SidebarButtons.TypeFilter.setData({
                    active: false,
                    filterBy: 'all'
                });
                let allTypeSvg = SidebarButtons.TypeFilter.getElement().childNodes[0];
                let pvzTypeSvg = SidebarButtons.TypeFilter.getElement().childNodes[1];
                let terminalTypeSvg = SidebarButtons.TypeFilter.getElement().childNodes[2];

                SidebarButtons.TypeFilter.handleEvent('onclick', () => {
                    const active = SidebarButtons.TypeFilter.getData().active;
                    if (!active) {
                        SidebarButtons.TypeFilter.getData().active = true;
                        SidebarButtons.TypeFilter.getData().filterBy = 'pvz';

                        allTypeSvg.style.display = 'none';
                        pvzTypeSvg.style.display = 'block';
                        terminalTypeSvg.style.display = 'none';

                    } else if (active && SidebarButtons.TypeFilter.getData().filterBy === 'pvz') {
                        SidebarButtons.TypeFilter.getData().filterBy = 'terminal';

                        allTypeSvg.style.display = 'none';
                        pvzTypeSvg.style.display = 'none';
                        terminalTypeSvg.style.display = 'block';
                    } else {
                        SidebarButtons.TypeFilter.getData().filterBy = 'all';
                        SidebarButtons.TypeFilter.getData().active = false;

                        allTypeSvg.style.display = 'block';
                        pvzTypeSvg.style.display = 'none';
                        terminalTypeSvg.style.display = 'none';
                    }
                    if (SidebarButtons.TypeFilter.getData().active) {
                        SidebarButtons.TypeFilter.setCurrent();
                    } else {
                        SidebarButtons.TypeFilter.removeCurrent();
                    }

                    Structure.setPanelInfoTypeTitle(SidebarButtons.TypeFilter.getData().filterBy);

                    this.filterAction('type', true, SidebarButtons.TypeFilter.getData().filterBy).then();
                });
            }

            let PopupModeButton = null;
            if (this.getParams().isPopupMode()) {
                this.getStructure().getContainer().getDomElement().classList.add('ctpt_popup_mode');
                PopupModeButton = new HtmlItem('PopupModeButton', {
                    classList: ['ctpl_popup_mode_close'],
                });
                this.getStructure().getContainer().getDomElement().appendChild(PopupModeButton.getElement());
                PopupModeButton.handleEvent('onclick', () => {
                    if ( this.getStructure().getPanel().getElement().classList.contains('open') ) {
                        this.getStructure().getPanel().getElement().classList.remove('open');
                        this.getStructure().getSidebar().getChild('ButtonContainer').getChild('PvzList').removeCurrent();
                        this.getStructure().getSidebar().getChild('ButtonContainer').getChild('OperatorFilter').removeCurrent();
                    } else this.hide();
                });
                this.getStructure().getContainer().getDomElement().classList.add('ctpl_popup_first_step')
            }
            this.getStructure().getContainer().getDomElement().classList.remove('ctpt_courieronly_mode');
            this.getStructure().getContainer().getDomElement().classList.remove('ctpt_pvzonly_mode');
            if (this.getParams().isCourierOnly()) this.getStructure().getContainer().getDomElement().classList.add('ctpt_courieronly_mode');
            if (this.getParams().isOnlyPvz()) this.getStructure().getContainer().getDomElement().classList.add('ctpt_pvzonly_mode');

        };
        ready = () => {
            if (window.CtptWidgetMaps && window.CtptWidgetMaps[this.getParams().getWidgetId()]) {
                window.CtptWidgetMaps[this.getParams().getWidgetId()].destroy();
            }
            Structure.removeStyles();

            let css = document.createElement('style');
            css.innerHTML = CSS_string_min;
            css.classList.add('ctpt_style_assets');
            document.head.appendChild(css);

            let css_popup = document.createElement('style');
            css_popup.classList.add('ctpt_style_assets');
            css_popup.innerHTML = CSS_string_popup;
            document.head.appendChild(css_popup);

            let css_only_pvz = document.createElement('style');
            css_only_pvz.classList.add('ctpt_style_assets');
            css_only_pvz.innerHTML = CSS_string_only_PVZ;
            document.head.appendChild(css_only_pvz);

            if (this.initError === true) {
                this.initError = false;
                return;
            }

            this.createStructure();

            if (this.getParams().getDefaultCity() || this.getParams().getDefaultAddress() || this.getParams().getDefaultSettlement()) {
                this.getAjaxEngine().getAddressForCity(this.getParams().getDefaultAddress(), this.getParams().getDefaultCity(), this.getParams().getDefaultSettlement()).then(Result => {
                    if (Result && Result.suggestions && Result.suggestions.length > 0) {
                        this.SelectedDadataVariant = Result.suggestions[0];
                        this.getStructure().getBaseSearchLabel().setCss({
                            padding: '5px 24px 10px 60px',
                            fontSize: '12px',
                        });
                        this.submitSearch();
                    }
                });
            }

            if (!this.getParams().isCourierOnly()) {

                this.getMap().setMap(
                    new CatapultoYmapNamespace.Map(this.getStructure().getMapBox().getElement(), {
                        zoom: 10,
                        controls: [],
                        center: [55.75396, 37.620393],
                    })
                );
                this.getMap().createClusterer();
                this.getMap().getMap().controls.add(
                    new CatapultoYmapNamespace.control.ZoomControl(),
                    {
                        position: {
                            left: 12,
                            bottom: 70
                        }
                    }
                );
                this.getStructure().getMapBox().setCss({opacity: 0, 'pointer-events': 'none'});
            }
        };
        // getters methods
        getCenter = () => this.coordsCenter;
        resetCenter = () => this.coordsCenter = this.getMap().getMap().getCenter();
        getStructure = () => this.Structure;
        getParams = () => this.Params;
        getMap = () => this.MapCover;
        getAjaxEngine = () => this.AjaxEngine;
        getAssets = () => this.Assets;
        getData = () => this.DataCollection;
        isClosedPopup = () => this.ClosedPopup;
    }
})();
