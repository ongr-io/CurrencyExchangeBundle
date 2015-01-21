/**
 * handles various types browser storage
 *
 * currently supports only strings
 * Dependencies:
 * jquery.js
 * jquery.cookie.js
 */
(function($){

    /**
     * Register all drivers here
     * @type {Object}
     */
    var Drivers = {};

    /**
     * Returns storage driver
     * @param driver
     * @return {AbstractBrowserStorage}
     */
    var storage = function(driver) {
        if(Drivers[driver]) {
            if (Drivers[driver].available()) {
                return Drivers[driver];
            } else if (Drivers[driver].fallBack()) {
                return storage(Drivers[driver].fallBack());
            }
        }
        // return empty interface if we can't help anything
        return new AbstractBrowserStorage();
    };

    // jQuery support
    $.browserStorage = storage;

    //==================================================================================================================
    // abstract storage
    //==================================================================================================================

    /**
     * @constructor
     */
    function AbstractBrowserStorage() {}

    /**
     * Returns true if driver is available in current browser
     * @return {Boolean}
     */
    AbstractBrowserStorage.prototype.available = function() {
        return true;
    };

    /**
     * Returns possible fall-back driver name, return false if no fall back available
     * @return {String|Boolean}
     */
    AbstractBrowserStorage.prototype.fallBack = function() {
        return false;
    };

    /**
     * Returns value from storage
     * @param {String} key
     * @return {*}
     */
    AbstractBrowserStorage.prototype.get = function(key) {
        return false;
    };

    /**
     * Sets value to storage
     * @param {String} key
     * @param {String} value
     * @param {Object=} options
     */
    AbstractBrowserStorage.prototype.set = function(key, value, options) {};

    /**
     * Delete specific key
     * @param {String} key
     */
    AbstractBrowserStorage.prototype.remove = function(key) {};

    /**
     * Add json encode support
     * @type {Function}
     */
    AbstractBrowserStorage.prototype.encodeValue = JSON.stringify;

    /**
     * Add json Function support
     * @type {Function}
     */
    AbstractBrowserStorage.prototype.rawParseValue = JSON.parse;

    /**
     * Add json parse support
     * @param {*} value
     */
    AbstractBrowserStorage.prototype.parseValue = function(value) {
        try {
            return this.rawParseValue(value);
        } catch (e) {
            return null;
        }
    };

    //==================================================================================================================
    // Cookie driver
    //==================================================================================================================

    /**
     * Depends on jQuery cookie
     * @extends {AbstractBrowserStorage}
     * @constructor
     */
    function BrowserStorageCookie() {}

    $.extend(BrowserStorageCookie.prototype,AbstractBrowserStorage.prototype);

    /**
     * Default options
     * @returns {Object}
     */
    BrowserStorageCookie.prototype.cookieDefaults = function() {
        return {
            'path' : '/'
        };
    };

    BrowserStorageCookie.prototype.available = function() {
        if($.cookie) {
            return true;
        }
        return false;
    };

    BrowserStorageCookie.prototype.get = function(key) {
        return this.parseValue($.cookie(key));
    };

    BrowserStorageCookie.prototype.set = function(key, value, options) {
        $.cookie(key, this.encodeValue(value), $.extend({}, this.cookieDefaults(), options ? options : {}));
    };

    BrowserStorageCookie.prototype.remove = function(key, options) {
        $.removeCookie(key, $.extend({}, this.cookieDefaults(), options ? options : {}));
    };

    // register driver
    Drivers['cookie'] = new BrowserStorageCookie();

    //==================================================================================================================
    // session storage driver
    //==================================================================================================================

    /**
     * Depends on jQuery cookie
     * @extends {AbstractBrowserStorage}
     * @constructor
     */
    function BrowserStorageSession() {}

    $.extend(BrowserStorageSession.prototype,AbstractBrowserStorage.prototype);

    BrowserStorageSession.prototype.available = function() {
        if(window.sessionStorage) {
            return true;
        }
        return false;
    };

    BrowserStorageSession.prototype.get = function(key) {
        return this.parseValue(sessionStorage.getItem(key));
    };

    BrowserStorageSession.prototype.set = function(key, value, options) {
        sessionStorage.setItem(key, this.encodeValue(value));
    };

    BrowserStorageSession.prototype.remove = function(key) {
        sessionStorage.removeItem(key);
    };

    BrowserStorageSession.prototype.fallBack = function() {
        return 'cookie';
    };

    // register driver
    Drivers['sessionStorage'] = new BrowserStorageSession();

    //==================================================================================================================
    // Local storage
    //==================================================================================================================


    /**
     * Depends on jQuery cookie
     * @extends {AbstractBrowserStorage}
     * @constructor
     */
    function BrowserStorageLocal() {}

    $.extend(BrowserStorageLocal.prototype,AbstractBrowserStorage.prototype);

    BrowserStorageLocal.prototype.available = function() {
        if(window.localStorage) {
            return true;
        }
        return false;
    };

    BrowserStorageLocal.prototype.get = function(key) {
        return this.parseValue(localStorage.getItem(key));
    };

    BrowserStorageLocal.prototype.set = function(key, value, options) {
        localStorage.setItem(key, this.encodeValue(value));
    };

    BrowserStorageLocal.prototype.remove = function(key) {
        localStorage.removeItem(key);
    };

    BrowserStorageLocal.prototype.fallBack = function() {
        return 'cookie';
    };

    // register driver
    Drivers['localStorage'] = new BrowserStorageLocal();

})(jQuery);
