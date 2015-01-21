/**
 * Dependencies:
 * jquery.js
 * jquery.ui.widget.js
 */
(function ($, log) {

    function OngrCurrencyApply() {}

    /**
     * @type {Object}
     */
    OngrCurrencyApply.prototype.storage = null;

    /**
     * @private
     */
    OngrCurrencyApply.prototype._create = function () {
        log('OngrCurrencyApply: creating for #' + this.element.attr('id'));
        if($.ongrCurrency().get("currency") == null )
        {
            log('OngrCurrencyApply: currency not found, setting the default one');
            $.ongrCurrency().set("currency", $('.currency-default').first().attr('data-currency'));
        }
        this.applyCurrency();
    };

    /**
     * @public
     */
    OngrCurrencyApply.prototype.removeCurrency = function(){
        this.element.find(".currency-item.active").removeClass("active");
    };

    /**
     * @public
     */
    OngrCurrencyApply.prototype.applyCurrency = function () {
        this.element.find(".currency-item.currency-" + $.ongrCurrency().get("currency")).addClass("active");
    };

    // register the widget
    $.widget('ongr.currencyApply', OngrCurrencyApply.prototype);

})(jQuery, log);
