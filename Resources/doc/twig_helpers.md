Using Currency Helpers in Twig
===

This bundle provides two Twig filters to display converted price and a function
to list available currencies. To learn more read usage details below. 

`ongr_price` filter
---

This filter can be applied on price to get converted and formatted price. Price
format can be changed by passing following parameters in corresponding order:

- decimals - count of decimal digits. Default is 2.
- to_currency - currency code price will be converted to.
- from_currency - currency code price will be converted from.
- custom_format - custom format for printing price.
    
> __Note:__ Currency codes must follow [ISO 4217][1] standard.

Usage example in Twig:

```twig
Formatted price: {{ 1000|ongr_price(2, "USD", "EUR", "%s dollars.") }}
```

This example converts price from Euros to Dollars and prints it in given format.

`ongr_price_list` filter                                                          
---                                                                              

This filter can be used when you need to list prices in all configured
currencies. Common use case is to have price in all currencies and allow user
to switch the shown one without reloading the page.  

This filter has optional parameters:

- from_currency - currency to convert price from. If missing, default value from config will be used.
- template - custom template to render price list.

For detailed usage example check [Switching Currency on Client Side][2] page.
    
### Custom Price List Template

You can use custom template if you want. When template is rendered `prices`
array will be passed to it. Here is the list of available keys:

- `value` - a string representation of the converted price.
- `currency` - currency code (ISO 4217).

Example twig template:

```twig
{% for price in prices %}
    <span class="currency-{{ price.currency }}">
        {{ price.value }}
    </span>
{% endfor %}
```

When your custom template is ready you can use it by passing it directly to the
filter.

`ongr_currency_list` function
---

This function prints a list of all configured currencies. If no arguments given,
default template will be used. Pass template name to use custom template.

Example, using default template:

```twig
{{ ongr_currency_list() }}
```

Or you can pass custom template on runtime:
                                    
```twig
{{ ongr_currency_list('AppBundle::currency_list.html.twig') }}
```                                                          

### Custom Currency List Template

You can use custom template if you want. When template is rendered `currencies`
array will be passed to it. Here is the list of available keys:

- `value` - a string representation of the converted price.
- `code` - currency code.
- `default` - TRUE if this currency is default, FALSE otherwise.
    
[1]: http://en.wikipedia.org/wiki/ISO_4217
