# Craft Shopify Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.2.1
- Change `deleteByShopifyId` to a hard delete. Without that, products will get trashed and conflict with new imports

## 1.2.0

### Added
- `craft-shopify/webhook/purge` command to purge webhooks older than X days
- `CraftShopify::$plugin->webhook->purgeResponses()` Method to purge webhook records via queue
- Utility for purging webhook records

## Updated
- Now requires Craft 3.2 or higher

## 1.1.0 

### Added
- Delete product from Craft when `products/delete` webhook received
- Add utility to purge all products no longer in Shopify

## 1.0.0

### Added
- Utility to sync individual product from Shopify (via Product ID)
- Utility to sync all products from Shopify
- Ability to associate fields with Product element
- Push Product Element updates to Shopify Metafields
- When saving Entries check for related products and update the metafield data
