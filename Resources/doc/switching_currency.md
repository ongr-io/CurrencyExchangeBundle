Switching Currency on Client Side
===

This document shows an example how to implement language switching on the
client side using helpers provided by this bundle. 

This bundle provides widgets based on jQuery, so first step is to include
jQuery and other needed dependencies:

```html
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
```

Now you can include the widgets mentioned earlier:

```twig
{% javascripts 
    'CurrencyExchangeBundle/Resources/public/scripts/utils/browserStorage.js',
    'CurrencyExchangeBundle/Resources/public/scripts/utils/jquery.ongrCurrency.js',
    'CurrencyExchangeBundle/Resources/public/scripts/utils/log.js',
    'CurrencyExchangeBundle/Resources/public/scripts/widgets/jquery.ongr.currencyApply.js',
    'CurrencyExchangeBundle/Resources/public/scripts/widgets/jquery.ongr.currencySelector.js',
    'CurrencyExchangeBundle/Resources/public/scripts/main.js' 
%}
    <script src="{{ asset_url }}"></script>
{% endjavascripts %}
```

That's it for scripts part. It's time to add some styles:
  
```css
.currency-item {
    display: none;
}
.currency-item.active {
    display: inline-block;
}

.currency-status,
.currency-info-price {
    display: inline-block;
}
```

Now include currency switch somewhere at the top of the page:

```
{{ ongr_currency_list() }}
```

And apply `ongr_price_list` filter to your prices:

```twig
{{ product.price|ongr_price_list }}
```

You are ready to check the result. Compile assets and reload your browser.
