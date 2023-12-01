# Magento2 Akeneo Bundle

This Magento2 extension made by [JustBetter](https://justbetter.nl) extends the official [Akeneo Connector](https://github.com/akeneo/magento2-connector-community) with several features and optimizations.

These features can be enabled / disabled via an extra configuration section called `JustBetter Akeneo` that is added to the default Akeneo Connector Configuration in Magento.

## Features

| Feature                                                                                          | Description                                                                                                                                                                                                                                 |
|--------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Important Attributes                                                                             | Select attributes that should always be added to the product tables even if all are empty, this fixes cases where you bulk empty attributes and it is not reflected in Magento.
| Fix Configurable Urls                                                                            | Replaces the variant product url with the original url + sku to keep them unique                                                                                                                                                            |
| Tier Prices                                                                                      | Maps specific Akeneo attribute code with a Magento Customer group. This ensures that the tier prices from Akeneo are imported into Magento customer tier prices                                                                             |
| Set default value for required attributes                                                        | Set a default value for required attributes if the value is missing                                                                                                                                                                         |
| Category exist                                                                                   | Skip inserting url paths when the category already exist                                                                                                                                                                                    |
| Akeneo Manager                                                                                   | Manual adjustment of Akeneo codes vs magento entity id’s connector mapping. When enabled you can make adjustments of the values via the Menu option `JUSTBETTER > Akeneo Manager`                                                           |
| Insert New Products                                                                              | Disable the insertion of new products                                                                                                                                                                                                       |
| Set Tax Class                                                                                    | When you have multiple tax classes in Akeneo and you want to use them in Magento. Map Akeneo tax class codes to Magento tax class - See configuration                                                                                       |
| Set products active                                                                              | Enable all products from Akeneo                                                                                                                                                                                                             |
| Enable Manage stock by default                                                                   | This sets the manage stock to value `Yes` for imported products by default                                                                                                                                                                  |
| Set stock status                                                                                 | Automatically sets the stock status of imported products to "In Stock" when backorder-able                                                                                                                                                  |
| Apply SEO friendly media name formatting                                                         | Formats the Media name from "_" to "-"                                                                                                                                                                                                      |
| <a href="#metric-units">Enable retrieving metric units</a>                                       | Sets Akeneo's metric unit in the eav_attribute - See configuration                                                                                                                                                                          |
| Channel for metric conversions                                                                   | What channel to use for metric conversions                                                                                                                                                                                                  |
| <a href="#not-visible-individually">Set families to not visible individually after importing</a> | Sets products in selected families to `Not Visible Individually`                                                                                                                                                                            |
| Akeneo Import e-mail notifications                                                               | Setup e-mail notifications of Akeneo imports                                                                                                                                                                                                |
| Slack Akeneo import notifications                                                                | Setup Slack notifications of Akeneo imports                                                                                                                                                                                                 |
| *Akeneo import log cleaner*                                                                      | Cleans the Akeneo logs that are older then the configured number of days *Deprecated - the Akeneo Connector has this functionality built in since* [v102.2.0](https://github.com/akeneo/magento2-connector-community/releases/tag/v102.2.0) |
| <a href="#import-finished-events">Import finished events</a>                                     | Fires an event for every job that is fully finished                                                                                                                                                                                         |                                                                                                                                                                                               |

## Installation
```
composer require justbetter/magento2-akeneo-bundle
bin/magento module:enable JustBetter_AkeneoBundle
bin/magento setup:upgrade && bin/magento cache:flush
```
## Configuration
- Enable and disable different Akeneo features. Go to `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo`.
- To map specific Akeneo attribute code with a Magento Customer group. Simply go to `Stores > Configuration > Catalog > Akeneo Connector > Products > Customer Group Pricing`
- When you would like to use the Tax Class Mapping: map the Akeneo Attribute Option Codes to the Magento Tax Classes. Don't forget to define the Tax attribute within the Attribute configuration for this feature to work.

## Import finished events
We added a total of 5 events:
```
akeneo_connector_import_finish_category
akeneo_connector_import_finish_family
akeneo_connector_import_finish_attribute
akeneo_connector_import_finish_option
akeneo_connector_import_finish_product
```

These events are fired before the `cleanCache` function which only runs at the end of the job execution.
That way the cache will still be flushed after your hook.

*Please keep in mind that the Akeneo Products Import is executed per family ([since 102.1.1](https://github.com/akeneo/magento2-connector-community/blob/master/CHANGELOG.md#version-10211-)). So if you import products from multiple families the `akeneo_connector_import_finish_product` event will be called multiple times.*

## Metric Units
When enabled the default metric unit for metric attributes will be added to the `unit` field in the `eav_attribute` table.
This can be overridden at a channel, currently we only support one channel for this which is configurable in the backend.

You can run this from the command line using `bin/magento metric:import`

It is also automatically run after the attribute import

## Family - Not Visible Individually
If you need to set the visibility of all products that belong to certain families to `Not Visible Individually` you can select those families.
After each import this will run and set products to not visible.

You can also run this from the command line using `bin/magento akeneo:setfamilynotvisible`

## Ideas, bugs or suggestions?
It would be awesome if you can submit an [issue](https://github.com/justbetter/magento2-akeneo-bundle/issues) if you encounter any problems or for kudos create a [pull request](https://github.com/justbetter/magento2-akeneo-bundle/pulls).

## About us
We are an innovative development agency from The Netherlands building awesome websites, webshops and web applications with Laravel and Magento2. Check out our website [justbetter.nl](https://justbetter.nl) and our [open source projects](https://github.com/justbetter).

## License
[GNU GENERAL PUBLIC LICENSE](LICENSE)

---

<a href="https://justbetter.nl" title="JustBetter"><img src="https://raw.githubusercontent.com/justbetter/art/master/justbetter-logo.png" width="200px" alt="JustBetter logo"></a>
