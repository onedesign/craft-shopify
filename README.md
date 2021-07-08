# Craft Shopify plugin for Craft CMS 3.x

Bring Shopify products into Craft

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require onedesign/craft-shopify

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Craft Shopify.

## Craft Shopify Overview

This plugin will allow you to use Craft in order to manage custom data for a Shopify install. Products from Shopify can be pulled into Craft and stored as custom elements where you can attach and render fields as desired. On save, templates are rendered and data is stored within Shopify metafields for rendering on their platform.

## Configuring Craft Shopify

Three settings are required for the connection to Shopify. We highly recommend storying these as environment variables as to not accidentally expose them.

`SHOPIFY_HOSTNAME` - The hostname of your shopify instance (example: example.myshopify.com)
`SHOPIFY_API_KEY` - API key used to communicate with Shopify
`SHOPIFY_API_PASSWORD` - API password for communication with Shopify

For help generating the keys for your store [check out the Shopify Documentation](https://shopify.dev/tutorials/authenticate-a-private-app-with-shopify-admin)

## Using Craft Shopify

TODO:
* How to configure templates for rendering

Brought to you by [One Design Company](https://onedesigncompany.com/)
