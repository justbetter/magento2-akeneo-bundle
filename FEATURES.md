# Features Documentation

This document provides detailed information about all features available in the JustBetter Akeneo Bundle.

**[← Back to README](README.md)**

## Table of Contents

- [Configuration Guide](#configuration-guide)
- [Product Import Features](#product-import-features)
  - [Important Attributes](#important-attributes)
  - [Tier Prices](#tier-prices)
  - [Default Store Values for Required Attributes](#default-store-values)
  - [Exclude Families from Import](#exclude-families)
  - [Insert New Products](#insert-new-products)
  - [Set Products Active](#set-products-active)
  - [Enable Manage Stock by Default](#enable-manage-stock)
  - [Set Stock Status](#set-stock-status)
  - [Remove Redundant EAV Attributes](#remove-redundant-eav)
- [Category Features](#category-features)
  - [Category Exist - Skip URL Path Regeneration](#category-exist)
- [Tax & Pricing Features](#tax--pricing-features)
  - [Set Tax Class](#set-tax-class)
- [Attribute Features](#attribute-features)
  - [Metric Units Import](#metric-units)
  - [Format Media Name (SEO Friendly)](#format-media-name)
- [Visibility Features](#visibility-features)
  - [Set Families to Not Visible Individually](#not-visible-individually)
- [Website Association Features](#website-association-features)
  - [Required Attribute Mapping - Website Validation](#required-attribute-mapping)
- [Management & Administration](#management--administration)
  - [Akeneo Manager](#akeneo-manager)
- [Notification Features](#notification-features)
  - [Slack Notifications](#slack-notifications)
- [Event System](#event-system)
  - [Import Finished Events](#import-finished-events)

---

## Configuration Guide
<a id="configuration-guide"></a>

All features are configured via the Magento Admin Panel under:

**`Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo`**

### Configuration Sections

#### Main Configuration
**Path:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo`

#### Products Configuration
**Path:** `Stores > Configuration > Catalog > Akeneo Connector > Products`

- **Customer Group Pricing:** Grid mapping Akeneo attribute codes to Magento customer groups
- **Tax Class Mapping:** Grid mapping Akeneo tax codes to Magento tax class IDs
- **Required Attribute Mapping:** Grid defining required attributes per website

#### Products Filters
**Path:** `Stores > Configuration > Catalog > Akeneo Connector > Products Filters`

- **Excluded Families:** Multiselect of families to exclude from import

---

## Product Import Features

### Important Attributes
<a id="important-attributes"></a>

Select attributes that should always be imported and added to product temporary tables even when all values are empty. This ensures attribute columns are always present during import, fixing cases where bulk emptying attributes in Akeneo isn't reflected in Magento.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Important Attributes`

---

### Tier Prices
<a id="tier-prices"></a>

Maps Akeneo attribute codes to Magento customer groups, enabling customer group-specific pricing imported directly from Akeneo.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > Products > Customer Group Pricing`  
**Mapping:** Define Akeneo Attribute → Magento Customer Group pairs in grid configuration.

---

### Default Store Values for Required Attributes
<a id="default-store-values"></a>

Automatically sets default language values for required product attributes when the admin channel value is missing. Ensures all required attributes are populated using a fallback language.

**Configuration:**
- Enable: `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Default Store Values`
- Fallback Language: `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Default Language` (e.g., `nl_NL`)

---

### Exclude Families from Import
<a id="exclude-families"></a>

Prevents specific product families from being imported. Products belonging to excluded families will be completely skipped during the Akeneo import process.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > Products Filters > Excluded Families`

**Note:** This feature works in combination with the "Family Attribute as Filter" configuration.

---

### Insert New Products
<a id="insert-new-products"></a>

Control whether new products from Akeneo are inserted into Magento. When disabled, only existing products will be updated.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Insert New Products` (Default: Yes)

---

### Set Products Active
<a id="set-products-active"></a>

Automatically enables all products imported from Akeneo by setting their status to "Enabled".

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Set Products Active`

---

### Enable Manage Stock by Default
<a id="enable-manage-stock"></a>

Sets "Manage Stock" to "Yes" for all imported products by default.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Enable Manage Stock`

---

### Set Stock Status
<a id="set-stock-status"></a>

Automatically sets imported products' stock status to "In Stock" when they are backorderable.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Set Stock Status`

---

### Remove Redundant EAV Attributes
<a id="remove-redundant-eav"></a>

Automatically cleans up orphaned EAV values when a product's family changes in Akeneo. Removes attribute values that no longer belong to the product's new attribute set.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Remove Redundant EAV Attributes`

**Example:** Product changes from Family A (attributes: name, price, color) to Family B (attributes: name, price, weight). The "color" EAV values are automatically deleted.

---

## Category Features

### Category Exist - Skip URL Path Regeneration
<a id="category-exist"></a>

When enabled, preserves existing category URL keys instead of regenerating them during import. Improves performance by skipping unnecessary URL path updates for categories that already exist.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Category Exist`

---

## Tax & Pricing Features

### Set Tax Class
<a id="set-tax-class"></a>

Maps Akeneo tax class attribute values to Magento tax class IDs. Supports both non-localizable and localizable tax attributes across multiple channels and locales.

**Configuration:**
- Enable: `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Set Tax Class`
- Mapping: `Stores > Configuration > Catalog > Akeneo Connector > Products > Tax Class Mapping` (Grid: Akeneo Code → Magento Tax Class)

**Requirements:** Map the Akeneo tax attribute in the Attribute Types configuration with type "tax".

---

## Attribute Features

### Metric Units Import
<a id="metric-units"></a>

Imports Akeneo metric attribute units into Magento's `eav_attribute.unit` field. Supports channel-specific unit conversions.

**Configuration:**
- Enable: `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Enable Metric Units`
- Channel: `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Metric Conversion Channel`

**Usage:**
- Automatically runs after attribute import
- Manual execution: `bin/magento metric:import`

---

### Format Media Name (SEO Friendly)
<a id="format-media-name"></a>

Replaces underscores with hyphens in media file names for SEO-friendly URLs.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Format Media Name`

**Example:** `product_image_2024.jpg` → `product-image-2024.jpg`

---

## Visibility Features

### Set Families to Not Visible Individually
<a id="not-visible-individually"></a>

Automatically sets visibility to "Not Visible Individually" for all products belonging to selected families. Useful for components, configurable product children, or internal-use products.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Not Visible Families`

**Usage:**
- Automatically runs after product import
- Manual execution: `bin/magento akeneo:setfamilynotvisible`

---

## Website Association Features

### Required Attribute Mapping - Website Validation
<a id="required-attribute-mapping"></a>

Validates that required product attributes contain values before assigning website associations. Automatically removes websites from products when required attributes are empty for that website's channel/locale.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > Products > Required Attribute Mapping` (Grid: Akeneo Attribute)

**How it works:**
1. Gets mapped channel from website configuration
2. Checks if required attribute values exist for channel locales
3. Removes website from association if any required attribute is empty

**Example:** Product mapped to US website requires "description". If "description-en_US-ecommerce" is empty, US website association is removed.

---

## Management & Administration

### Akeneo Manager
<a id="akeneo-manager"></a>

Manual mapping interface for adjusting Akeneo codes versus Magento entity IDs in the connector mapping tables.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Akeneo Manager`  
**Access:** `JUSTBETTER > Akeneo Manager` (when enabled)

**Features:** Create, edit, and delete mappings for families, categories, attributes, and other entities synced between Akeneo and Magento.

---

## Notification Features

### Slack Notifications
<a id="slack-notifications"></a>

Sends daily import status notifications to Slack at 8:00 AM. Reports successful imports, failures, or imports still in processing state.

**Configuration:** `Stores > Configuration > Catalog > Akeneo Connector > JustBetter Akeneo > Slack`
- Enable Slack: Yes/No
- Token: Slack bot token
- Username: Bot display name
- Channel: Slack channel ID or name
- API: Slack API URL

**Schedule:** Daily at 08:00 (cron: `0 8 * * *`)

**Message Formats:**
- ✅ Success: "All of today's imports in *Store Name* have been successfully completed."
- ⚠️ Warning: Lists failed imports with timestamp and name
- ⚠️ Processing: Lists imports still running with timestamp and name
- ⚠️ No Imports: "No imports have been made today."

**Usage:** Manual execution: `bin/magento slack:notification`

---

## Event System

### Import Finished Events
<a id="import-finished-events"></a>

Custom event system that dispatches entity-specific events when imports are fully completed, enabling custom post-import logic.

**Available Events:**
```php
akeneo_connector_import_finish_category
akeneo_connector_import_finish_family
akeneo_connector_import_finish_attribute
akeneo_connector_import_finish_option
akeneo_connector_import_finish_product
```

**Event Data:** `JobExecutor` instance available via `$observer->getData('import')`

**Important:** Product import runs per family (since Akeneo Connector 102.1.1), so `akeneo_connector_import_finish_product` fires multiple times if importing multiple families.

**Note:** The `akeneo_connector_import_finish_product` event fires multiple times (once per family) since Akeneo Connector 102.1.1. Use `$import->getFamily()` to process specific families only.

---

**[← Back to README](README.md)**
