/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function ($) {
        var body = $('body');
        body.currencyApply();
        $('.currency_list a').currencySelect({currencyApplier: body});
})(jQuery);
