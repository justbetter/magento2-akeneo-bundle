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
| SetTaxClass                                           | Map When you have multiple tax classes in Akeneo and want to use them in Magento. Akeneo tax class codes to Magento tax class - See confguration |
| <a href="#import-finished-events">ImportFinished</a>  | Fires an event for every job that is fully finished. |
| <a href="#metric-units">Metric Units</a>              | Sets Akeneo's metric unit in the eav_attribute |
| <a href="#not-visible-individually">Family - Not Visible Individually</a>              | Sets products in selected families to Not Visible Individually |

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

## Metric Units
When enabled the default metric unit for metric attributes will be added to the `unit` field in the `eav_attribute` table.
This can be overidden at a channel, currently we only support one channel for this which is configurable in the backend.

You can run this from the command line using `bin/magento metric:import`

It is also automatically run after the attribute import

## Family - Not Visible Individually
If you need all products of a certain families to be set to not visible individually you can select those families.
After each import this will run and set products to not visible.

You can also run this from the command line using `bin/magento set:notvisible`

## Ideas, bugs or suggestions?
Would be awesome if you can submit an [issue](https://github.com/justbetter/magento2-akeneo-bundle/issues) or for kudos create a [pull request](https://github.com/justbetter/magento2-akeneo-bundle/pulls).

## About us
We’re a innovative development agency from The Netherlands building awesome websites, webshops and web applications with Laravel and Magento2. Check out our website [justbetter.nl](https://justbetter.nl) and our [open source projects](https://github.com/justbetter).

## License
[GNU GENERAL PUBLIC LICENSE](LICENSE)

---

<a href="https://justbetter.nl" title="JustBetter"><img src="https://raw.githubusercontent.com/justbetter/art/master/justbetter-logo.png" width="200px" alt="JustBetter logo"></a>
