<?php
/**
 * slumberkins module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\jobs;


use Craft;
use craft\queue\BaseJob;
use Exception;
use onedesign\craftshopify\CraftShopify;
use onedesign\craftshopify\elements\Product;

/**
 * @author    One Design Company
 * @package   slumberkins
 * @since     1.0.0
 */
class PushProductData extends BaseJob
{

    /**
     * @var array
     */
    public $productId = null;

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function execute($queue)
    {
        if (!$this->productId) {
            throw new Exception('Product ID is required.');
        }

        $product = Product::find()->id($this->productId)->one();
        if (!$product) {
            throw new Exception("Product {$this->productId} not found");
        }

        $this->setProgress($queue, 0, $product->title);
        CraftShopify::$plugin->product->pushDataToShopify($product);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('craft-shopify', "Sync Product ID {$this->productId} to Shopify");
    }
}
