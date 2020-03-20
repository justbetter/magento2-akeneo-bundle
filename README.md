# Magento 2 Akeneo Bundle

This Magento 2 module made by JustBetter extends the akeneo_connector (A module that allows you to export your data from Akeneo to Magento 2) on different aspects. Such as: urls and notifications. Below you will find all extensions included in the module and a short description about the extensions.

| Module                   | Description                                                                       |
| ------------------------ | --------------------------------------------------------------------------------- |
| Metrics                  | Add the right Akeneo metric unit to the Magento attribute for use in frontend     |
| FixProductUrls           | Replaces variant product urls with the original url + sku for unique URL’s.       |
| InsertNewProducts        | Adds the possibility to disable insertion of new products from akeneo.            |
| CategoryExist            | Adds the possibility to skip inserting url paths when the category already exist. |
| SlackNotificationCommand | Adds the possibility to receive slack notifications about akeneo imports.         |
| MailNotificationCommand  | Adds the possibility to receive e-mail notifications about akeneo imports.        |
| AkeneoManager            | Manual adjustment of akeneo codes vs magento entity id’s connector mapping.       |
| SetTierPrices            | Maps specific Akeneo attribute code with a Magento Customer group. This ensures that the tier prices from Akeneo are imported into Magento customer tier prices      |
| SetTaxClass              | Map Akeneo tax class codes to Magento tax class                                   |

## Installation
- `composer require justbetter/magento2-akeneo-bundle`
- `bin/magento module:enable JustBetter_AkeneoBundle`
- `bin/magento setup:upgrade && bin/magento cache:flush`

## Configuration
- Enable and disable different akeneo fixes under Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo.
- To map specific Akeneo attribute code with a Magento Customer group. simply go to Stores > Configuration > Catalog > Akeneo Connector > Products > Customer Group Pricing

## Compability
The module is tested on magento version 2.2.x

## Ideas, bugs or suggestions?
Please create a [issue](https://github.com/justbetter/magento2-akeneo-bundle/issues) or a [pull request](https://github.com/justbetter/magento2-akeneo-bundle/pulls).

## About us
We’re a innovative development agency from The Netherlands building awesome websites, webshops and web applications with Laravel and Magento. Check out our website [justbetter.nl](https://justbetter.nl) and our [open source projects](https://github.com/justbetter).

## License
[GNU GENERAL PUBLIC LICENSE](LICENSE)

---

<a href="https://justbetter.nl" title="JustBetter"><img src="https://raw.githubusercontent.com/justbetter/art/master/justbetter-logo.png" width="200px" alt="JustBetter logo"></a>
