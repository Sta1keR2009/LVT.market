if (typeof window.JViewed === 'undefined') {
    JViewed = function() {
    }

    JViewed.prototype = {
        get site() {
            return arAsproOptions.SITE_ID;
        },

        get cookieName() {
            return 'LITE_VIEWED_ITEMS_' + this.site;
        },

        get cookieParams() {
            return  {
                path: '/',
                expires: 30,
            };
        },

        get cookieValue() {
            let value = {};

            let bCookieJson = $.cookie.json;
            $.cookie.json = true;

            try {
                value = $.cookie(this.cookieName);
                if (
                    typeof value !== 'object' ||
                    !value
                ) {
                    value = {};
                }
            } catch (e) {
                console.error(e);
                value = {};
            } finally {
                $.cookie.json = bCookieJson;
            }

            return value;
        },

        set cookieValue(value) {
            let bCookieJson = $.cookie.json;
            $.cookie.json = true;

            try {
                if (
                    typeof value !== 'object' ||
                    !value
                ) {
                    value = {};
                }

                $.cookie(this.cookieName, value, this.cookieParams);
            } catch (e) {
                console.error(e);
            } finally {
                $.cookie.json = bCookieJson;
            }
        },

        get storageValue() {
            let value = {};

            try {
                if (typeof BX.localStorage !== 'undefined') {
                    value = BX.localStorage.get(this.cookieName)
                    if (
                        typeof value !== 'object' ||
                        !value
                    ) {
                        value = {};
                    }
                }
            } catch (e) {
                console.error(e);
                value = {};
            }

            return value;
        },

        set storageValue(value) {
            try {
                if (typeof BX.localStorage !== 'undefined') {
                    if (
                        typeof value !== 'object' ||
                        !value
                    ) {
                        value = {};
                    }

                    BX.localStorage.set(this.cookieName, value, 30 * 86400);
                }
            } catch (e) {
                console.error(e);
            }
        },

        getProducts: function() {
            let storageValue = this.storageValue;
            let cookieValue = this.cookieValue;
            
            for (var i in storageValue) {
                if (typeof cookieValue[i] === 'undefined') {
                    delete storageValue[i];
                }
            }

            return storageValue;
        },

        addProduct: function(id, data) {
            if (
                typeof id !== 'undefined' &&
                id &&
                typeof data === 'object' &&
                data
            ) {
                try {
                    let productId = typeof data.PRODUCT_ID !== 'undefined' ? data.PRODUCT_ID : id;
                    let products = this.getProducts();

                    // delete item if other item (offer) of that productId is exists
                    if (typeof products[productId] !== 'undefined') {
                        if (products[productId].ID != id) {
                            delete products[productId];
                        }
                    }

                    let time = new Date().getTime();
                    data.ID = id;
                    data.ACTIVE_FROM = time;
                    products[productId] = data;

                    let cookieValue = {};
                    for (var i in products) {
                        cookieValue[i] = [
                            products[i].ACTIVE_FROM.toString(),
                            products[i].PICTURE_ID,
                            products[i].IBLOCK_ID,
                        ];
                    }

                    this.cookieValue = cookieValue;
                    this.storageValue = products;
                } catch (e) {
                    console.error(e);
                }
            }
        },

        clearProducts: function() {
            try {
                // remove local storage
                if (typeof BX.localStorage !== 'undefined') {
                    BX.localStorage.set(this.cookieName, {}, 0);
                }

                // remove cookie
                $.removeCookie(this.cookieName, this.cookieParams);
            } catch (e) {
                console.error(e);
            }
        },
    }

    JViewed.get = function() {
        if (
            typeof JViewed.instance !== 'object' ||
            !JViewed.instance ||
            !(JViewed.instance instanceof JViewed)
        ) {
            JViewed.instance = new JViewed();
        }

        return JViewed.instance;
    }
}
