/**
 * handles storage for Ongr currency
 *
 * Dependencies:
 * jquery.js
 */
(function($){

    var storage = null;

    /*
     * Returns storage by a given type on demand
     */
    $.ongrCurrency = function() {
        if (!storage) {
            storage = $.browserStorage($.ongrCurrency.defaults.storage);
        }
        return storage;
    };

    $.ongrCurrency.defaults = {
        storage: 'sessionStorage'
    };

}(jQuery));
