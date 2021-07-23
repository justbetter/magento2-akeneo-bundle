# Magento2 Akeneo Bundle

This Magento2 extension made by JustBetter extends the [Akeneo Connector](https://github.com/akeneo/magento2-connector-community) with several features you can enable/disable, so you can configure for example customer tier-price attributes and different product Tax classes within the Akeneo connector configuration.

The following features are included in the JustBetter Akeneo Bundle extension:

| Bundle extension                                      | Description                                                                                   |
| ----------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| FixProductUrls                                        | Replaces variant product urls with the original url + sku for unique URL’s.                   |
| InsertNewProducts                                     | Adds the possibility to disable insertion of new products from akeneo.                        |
| SetProductsActive                                     | Adds the possibility to enable all products from akeneo.                                      |
| EnableManageStock                                     | Adds the possibility to enable manage stock for products from akeneo.                                      |
| CategoryExist                                         | Adds the possibility to skip inserting url paths when the category already exist.             |
| SlackNotificationCommand                              | Adds the possibility to receive slack notifications about akeneo imports.                     |
| MailNotificationCommand                               | Adds the possibility to receive e-mail notifications about akeneo imports.                    |
| AkeneoManager                                         | Manual adjustment of akeneo codes vs magento entity id’s connector mapping.                   |
| SetTierPrices                                         | Maps specific Akeneo attribute code with a Magento Customer group. This ensures that the tier prices from Akeneo are imported into Magento customer tier prices      |
| SetTaxClass                                           | Map When you have multiple tax classes in Akeneo and want to use them in Magento. Akeneo tax class codes to Magento tax class - See confguration
| <a href="#import-finished-events">ImportFinished</a>  | Fires an event for every job that is fully finished.

## Installation
- `composer require justbetter/magento2-akeneo-bundle`
- `bin/magento module:enable JustBetter_AkeneoBundle`
- `bin/magento setup:upgrade && bin/magento cache:flush`

## Configuration
- Enable and disable different Akeneo features. Go to Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo.
- To map specific Akeneo attribute code with a Magento Customer group. simply go to Stores > Configuration > Catalog > Akeneo Connector > Products > Customer Group Pricing
- When using the TAX mapping, map to Akeneo codes to the Magento Tax classes. Don't forget to define the Tax attribute within the Attribute configuration for this feature to work.

## Import finished events
There are a total of 5 events:
```
akeneo_connector_import_finish_category
akeneo_connector_import_finish_family
akeneo_connector_import_finish_attribute
akeneo_connector_import_finish_option
akeneo_connector_import_finish_product
```

These events are fired before the `cleanCache` function which only runs at the end of the job. 
That way the cache will still me flushed after your hook.

## Ideas, bugs or suggestions?
Would be awesome if you can submit an [issue](https://github.com/justbetter/magento2-akeneo-bundle/issues) or for kudos create a [pull request](https://github.com/justbetter/magento2-akeneo-bundle/pulls).

## About us
We’re a innovative development agency from The Netherlands building awesome websites, webshops and web applications with Laravel and Magento2. Check out our website [justbetter.nl](https://justbetter.nl) and our [open source projects](https://github.com/justbetter).

## License
[GNU GENERAL PUBLIC LICENSE](LICENSE)

---

<a href="https://justbetter.nl" title="JustBetter"><img src="https://raw.githubusercontent.com/justbetter/art/master/justbetter-logo.png" width="200px" alt="JustBetter logo"></a>
