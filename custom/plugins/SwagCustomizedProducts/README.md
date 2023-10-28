# SwagCustomizedProducts

The extension offers you the possibility to add individualization options to products. This is very useful for products, when a simple split into variants is not sufficient and further individualization is desired. These can be products where an individual lettering, a special color or a certain date-time specification is desired.

In the extension Custom Products you create product templates with selectable options. In a second step, you link these templates to existing articles. The selectable options from the product template will be displayed on the item detail page so that the customer can individualize the product accordingly.

Optionally, you can freely add user-defined charges to the specific options.

## Links
- [English End-User Documentation](https://docs.shopware.com/en/shopware-6-en/extensions/customproducts)
- [German End-User Documentation](https://docs.shopware.com/de/shopware-6-de/erweiterungen/customproducts)

## Testing
If you're using Docker have a look at the Makefile.

### Unit tests local with DEVENV
```
devenv shell
cd custom/plugins/SwagCustomizedProducts/src/Resources/app/administration/
npm i
npm run unit
```

### E2E test local with DEVENV
```
devenv shell
cd custom/plugins/SwagCustomizedProducts/src/Resources/app/administration/test/e2e/
export CYPRESS_localUsage=1; export APP_ENV=e2e; export CYPRESS_shopwareRoot="../../../../../../../../../"; npm run cypress:open
```
