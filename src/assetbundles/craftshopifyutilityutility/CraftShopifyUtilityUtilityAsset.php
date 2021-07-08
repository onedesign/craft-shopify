<?php
/**
 * Craft Shopify plugin for Craft CMS 3.x
 *
 * Bring Shopify products into Craft
 *
 * @link      https://onedesigncompany.com/
 * @copyright Copyright (c) 2021 One Design Company
 */

namespace onedesign\craftshopify\assetbundles\craftshopifyutilityutility;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    One Design Company
 * @package   CraftShopify
 * @since     1.0.0
 */
class CraftShopifyUtilityUtilityAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@onedesign/craftshopify/assetbundles/craftshopifyutilityutility/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/CraftShopifyUtility.js',
        ];

        $this->css = [
            'css/CraftShopifyUtility.css',
        ];

        parent::init();
    }
}
