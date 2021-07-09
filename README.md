# Craft Shopify plugin for Craft CMS 3.x

Bring Shopify products into Craft. 

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Add the repo to the "repositories" key in your composer.json

        "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/onedesign/craft-shopify"
            }
        ],

3. Then tell Composer to load the plugin:

        composer require onedesign/craft-shopify

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Craft Shopify.

## Craft Shopify Overview

This plugin will allow you to use Craft in order to manage custom data for a Shopify install. Products from Shopify can be pulled into Craft and stored as custom elements where you can attach and render fields as desired. On save, templates are rendered and data is stored within Shopify metafields for rendering on their platform.

The plugin runs on the `AFTER_SAVE` event for Product elements. On this event, a queue task is created that will render the template set in the settings and send that HTML to Shopify to be stored in a `cms.body_html` metafield on the product. This allows you to render the crft data with `{{ product.metafields.cms.body_html }}` in your liquid tempaltes. 

If you save an entry that is related to one of your product elements, the plugin will attempt to update all products that effected by your change. 

**Note** Because Shopify caches metafields fairly aggressively it can take up to a minute before changes pushed by the plugin are displayed on the page. 

## Configuring Craft Shopify

Three settings are required for the connection to Shopify. We highly recommend storing these as environment variables as to not accidentally expose them.

### Field Layouts

Add fields and tabs to the custom product type

### Templates

#### Template Path

The path to the template you would like to render when sending data to Shopify. This template will be provided the Product element when rendering. 

#### Preview Template

If you'd like to have a preview hosted within Craft, enter a template path here. This template will be rendered when using the Preview button within Craft. 

### Shopify

This plugin requires a [Private App](https://shopify.dev/apps/getting-started/app-types#capabilities-and-requirements) to communicate with Shopify. Private apps communicate via [Basic HTTP Auth](https://shopify.dev/apps/auth/basic-http). You'll need to [generate API credentials](https://shopify.dev/apps/auth/basic-http#2-generate-api-credentials) and add those to Craft for the communication. 

#### Hostname
The hostname of your shopify instance (example.myshopify.com). Defaults to an environment variable named `SHOPIFY_HOSTNAME`. 

#### API Key
API Key used to communicate with Shopify. Defaults to an environment variable named `SHOPIFY_API_KEY`. 

#### API Password
API password for communication with Shopify. Defaults to an environment variable named `SHOPIFY_API_PASSWORD`

#### Webhook Secret (optional)
If you'd like Shopify to communicate back to Craft, enter the Webhook secret found in the notification settings. Currently the plugin will only respond to `product/create` webhooks. 

## Using Craft Shopify

### Utilities

#### Sync All Products
If you'd like to pull all the products from your store into Craft, you can use the "Sync all Products" button on the utilities page. This will get _all_ products from your store that have a `published_status` of "published" and a `status` of either "draft" or "active". 

#### Sync by Shopify ID
If you'd only like to sync a few products, you can enter one or more Shopify product IDs into the table field. Clicking Sync products will pull those produts into Craft.


Brought to you by [One Design Company](https://onedesigncompany.com/)
