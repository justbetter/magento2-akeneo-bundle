# Magento2 Akeneo Bundle

This Magento2 extension made by JustBetter extends the [Akeneo Connector](https://github.com/akeneo/magento2-connector-community) (the default Akeneo connector extension that allows you to import productdata from Akeneo to Magento2). 

The following configurable options are included in the JustBetter Akeneo Bundle extension:

| Bundle extension                                      | Description                                                                                   |
| ----------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| FixProductUrls                                        | Replaces variant product urls with the original url + sku for unique URL’s.       |
| InsertNewProducts                                     | Adds the possibility to disable insertion of new products from akeneo.            |
| CategoryExist                                         | Adds the possibility to skip inserting url paths when the category already exist. |
| SlackNotificationCommand                              | Adds the possibility to receive slack notifications about akeneo imports.         |
| MailNotificationCommand                               | Adds the possibility to receive e-mail notifications about akeneo imports.        |
| AkeneoManager                                         | Manual adjustment of akeneo codes vs magento entity id’s connector mapping.       |
| SetTierPrices                                         | Maps specific Akeneo attribute code with a Magento Customer group. This ensures that the tier prices from Akeneo are imported into Magento customer tier prices      |
| SetTaxClass                                           | Map Akeneo tax class codes to Magento tax class  
| <a href="#import-finished-events">ImportFinished</a>  | Fires an event for every job that is fully finished.

## Installation
- `composer require justbetter/magento2-akeneo-bundle`
- `bin/magento module:enable JustBetter_AkeneoBundle`
- `bin/magento setup:upgrade && bin/magento cache:flush`

## Configuration
- Enable and disable different akeneo fixes under Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo.
- To map specific Akeneo attribute code with a Magento Customer group. simply go to Stores > Configuration > Catalog > Akeneo Connector > Products > Customer Group Pricing

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
