<?php
/**
 * craft-shopify module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\jobs;


use Craft;
use craft\helpers\Json;
use craft\queue\BaseJob;
use Exception;
use onedesign\craftshopify\CraftShopify;

/**
 * @author    One Design Company
 * @package   craft-shopify
 * @since     1.0.0
 */
class SyncProduct extends BaseJob
{
    /**
     * @var array
     */
    public $productData = null;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        if (!$this->productData) {
            throw new Exception('Product Data is required.');
        }

        $shopifyId = $this->productData['id'];
        $product = CraftShopify::$plugin->product->getProductModel($shopifyId);
        CraftShopify::$plugin->product->populateProductModel($product, $this->productData);

        if (Craft::$app->getElements()->saveElement($product)) {
            return $product;
        }

        if ($product->getErrors()) {
            throw new Exception('Product ' . $product->id . ' - ' . Json::encode($product->getErrors()));
        }

        throw new Exception('Unknown error saving product ' . $product->id);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('craft-shopify', "Sync Product ID {$this->productData['id']} from Shopify");
    }
}
