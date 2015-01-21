/**
 * Dependencies:
 * jquery.js
 * jquery.ui.widget.js
 */
(function ($, log) {
    
    function CurrencySelect() {}

    /**
     * @type {Object}
     */
    CurrencySelect.prototype.options = {
        currencyApplier: null
    };

    /**
     * @type {Object}
     */
    CurrencySelect.prototype.storage = null;

    /**
     * @private
     */
    CurrencySelect.prototype._create = function () {
        log('CurrencySelector: creating for #' + this.element.attr('id'));
        this._on(this.element, {"click" : "onEvent"});
    };

    /**
     * @param event
     * @private
     */
    CurrencySelect.prototype.onEvent = function (event) {
        event.preventDefault();
        this.option('currencyApplier').currencyApply('removeCurrency');
        $.ongrCurrency().set("currency", this.element.attr('data-currency'));
        this.option('currencyApplier').currencyApply('applyCurrency');
    };

    // register the widget
    $.widget('ongr.currencySelect', CurrencySelect.prototype);

})(jQuery, log);
