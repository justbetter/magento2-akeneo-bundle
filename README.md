<h1>JustBeter - Magento 2 Akeneo Bundle</h1>
<a id="readme-top"></a>

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![GPL-3.0 License][license-shield]][license-url]

<br />
<div align="center">
  <a href="https://justbetter.nl">
    <img src="https://raw.githubusercontent.com/justbetter/art/master/justbetter-logo.png" alt="Logo" width="200">
  </a>

  <h3 align="center">JustBeter - Magento 2 Akeneo Bundle</h3>

  <p align="center">
    Powerful extensions for the Akeneo Connector Community Edition
    <br />
    <a href="https://github.com/justbetter/magento2-akeneo-bundle/issues">Report Bug</a>
    ·
    <a href="https://github.com/justbetter/magento2-akeneo-bundle/issues">Request Feature</a>
  </p>
</div>

<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#features">Features</a></li>
    <li><a href="#configuration">Configuration</a></li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#events">Events</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
  </ol>
</details>

## About The Project

This Magento 2 extension made by [JustBetter](https://justbetter.nl) extends the official [Akeneo Connector](https://github.com/akeneo/magento2-connector-community) with several features and optimizations.

These features can be enabled / disabled via an extra configuration section called `JustBetter Akeneo` that is added to the default Akeneo Connector Configuration in Magento.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Built With

* [![PHP][PHP-badge]][PHP-url]
* [![Magento][Magento-badge]][Magento-url]
* [![Akeneo][Akeneo-badge]][Akeneo-url]

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Getting Started

### Prerequisites

* PHP >=8.2 <=8.4
* Magento 2.4.4+
* Akeneo Connector Community Edition

### Installation

1. Install via Composer
   ```sh
   composer require justbetter/magento2-akeneo-bundle
   ```

2. Enable the module
   ```sh
   bin/magento module:enable JustBetter_AkeneoBundle
   ```

3. Run setup upgrade and flush cache
   ```sh
   bin/magento setup:upgrade && bin/magento cache:flush
   ```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Features

| Feature                                                                                          | Description                                                                                                                                                                                                                                 |
|--------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Important Attributes                                                                             | Select attributes that should always be added to the product tables even if all are empty, this fixes cases where you bulk empty attributes and it is not reflected in Magento.                                                             |
| Tier Prices                                                                                      | Maps specific Akeneo attribute code with a Magento Customer group. This ensures that the tier prices from Akeneo are imported into Magento customer tier prices                                                                             |
| Set default value for required attributes                                                        | Set a default value for required attributes if the value is missing                                                                                                                                                                         |
| Category exist                                                                                   | Skip inserting url paths when the category already exist                                                                                                                                                                                    |
| Akeneo Manager                                                                                   | Manual adjustment of Akeneo codes vs magento entity id’s connector mapping. When enabled you can make adjustments of the values via the Menu option `JUSTBETTER > Akeneo Manager`                                                           |
| Insert New Products                                                                              | Disable the insertion of new products                                                                                                                                                                                                       |
| Set Tax Class                                                                                    | When you have multiple tax classes in Akeneo and you want to use them in Magento. Map Akeneo tax class codes to Magento tax class - See configuration                                                                                       |
| Set Required admin attribute                                                                     | When having multiple stores and channels, the main attribute for de admin channel isn't always set. This means adding an attribute with the default language to do this for you.                                                            |
| Set products active                                                                              | Enable all products from Akeneo                                                                                                                                                                                                             |
| Enable Manage stock by default                                                                   | This sets the manage stock to value `Yes` for imported products by default                                                                                                                                                                  |
| Set stock status                                                                                 | Automatically sets the stock status of imported products to "In Stock" when backorder-able                                                                                                                                                  |
| Apply SEO friendly media name formatting                                                         | Formats the Media name from "_" to "-"                                                                                                                                                                                                      |
| <a href="#metric-units">Enable retrieving metric units</a>                                       | Sets Akeneo's metric unit in the eav_attribute - See configuration                                                                                                                                                                          |
| Channel for metric conversions                                                                   | What channel to use for metric conversions                                                                                                                                                                                                  |
| <a href="#not-visible-individually">Set families to not visible individually after importing</a> | Sets products in selected families to `Not Visible Individually`                                                                                                                                                                            |
| Unset Website when empty Product Attribute Mapping                                               | When enabled this will unset the website from the product when a required attribute has no specific value. For example when the Name attribute in Akeneo is empty for the associated website                                                |
| Slack Akeneo import notifications                                                                | Setup Slack notifications of Akeneo imports                                                                                                                                                                                                 |
| <a href="#import-finished-events">Import finished events</a>                                     | Fires an event for every job that is fully finished                                                                                                                                                                                         |
| Exclude Families from Import                                                                     | Allows you to exclude specific families from being imported from Akeneo. _(Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo)_                                                                                        |

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Configuration

- Enable and disable different Akeneo features. Go to `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo`.
- To map specific Akeneo attribute code with a Magento Customer group. Simply go to `Stores > Configuration > Catalog > Akeneo Connector > Products > Customer Group Pricing`
- When you would like to use the Tax Class Mapping: map the Akeneo Attribute Option Codes to the Magento Tax Classes. Don't forget to define the Tax attribute within the Attribute configuration for this feature to work.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Usage

### Metric Units
<a id="metric-units"></a>

When enabled the default metric unit for metric attributes will be added to the `unit` field in the `eav_attribute` table.
This can be overridden at a channel, currently we only support one channel for this which is configurable in the backend.

You can run this from the command line using:
```sh
bin/magento metric:import
```

It is also automatically run after the attribute import.

### Family - Not Visible Individually
<a id="not-visible-individually"></a>

If you need to set the visibility of all products that belong to certain families to `Not Visible Individually` you can select those families.
After each import this will run and set products to not visible.

You can also run this from the command line using:
```sh
bin/magento akeneo:setfamilynotvisible
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Events
<a id="import-finished-events"></a>

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

> **Note:** Please keep in mind that the Akeneo Products Import is executed per family ([since 102.1.1](https://github.com/akeneo/magento2-connector-community/blob/master/CHANGELOG.md#version-10211-)). So if you import products from multiple families the `akeneo_connector_import_finish_product` event will be called multiple times.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

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


> **Note:** Please keep in mind that the Akeneo Products Import is executed per family ([since 102.1.1](https://github.com/akeneo/magento2-connector-community/blob/master/CHANGELOG.md#version-10211-)). So if you import products from multiple families the `akeneo_connector_import_finish_product` event will be called multiple times.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## License

Distributed under the GPL-3.0 License. See `LICENSE` for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Contact

[JustBetter B.V.](https://justbetter.nl/)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- MARKDOWN LINKS & IMAGES -->
[contributors-shield]: https://img.shields.io/github/contributors/justbetter/magento2-akeneo-bundle.svg?style=for-the-badge
[contributors-url]: https://github.com/justbetter/magento2-akeneo-bundle/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/justbetter/magento2-akeneo-bundle.svg?style=for-the-badge
[forks-url]: https://github.com/justbetter/magento2-akeneo-bundle/network/members
[stars-shield]: https://img.shields.io/github/stars/justbetter/magento2-akeneo-bundle.svg?style=for-the-badge
[stars-url]: https://github.com/justbetter/magento2-akeneo-bundle/stargazers
[issues-shield]: https://img.shields.io/github/issues/justbetter/magento2-akeneo-bundle.svg?style=for-the-badge
[issues-url]: https://github.com/justbetter/magento2-akeneo-bundle/issues
[license-shield]: https://img.shields.io/github/license/justbetter/magento2-akeneo-bundle.svg?style=for-the-badge
[license-url]: https://github.com/justbetter/magento2-akeneo-bundle/blob/master/LICENSE

[PHP-badge]: https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white
[PHP-url]: https://www.php.net/
[Magento-badge]: https://img.shields.io/badge/Magento-EE672F?style=for-the-badge&logo=magento&logoColor=white
[Magento-url]: https://business.adobe.com/products/magento/magento-commerce.html
[Akeneo-badge]: https://img.shields.io/badge/Akeneo-7C1B8A?style=for-the-badge&logo=akeneo&logoColor=white
[Akeneo-url]: https://www.akeneo.com/
