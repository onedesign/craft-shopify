<?php
/**
 * slumberkins module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */

namespace onedesign\craftshopify\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use onedesign\craftshopify\elements\Product;


/**
 * @author    One Design Company
 * @package   slumberkins
 * @since     1.0.0
 *
 * @method Product|null one($db = null)
 * @method Product[]|array|null all($db = null)
 */
class ProductQuery extends ElementQuery
{
    /**
     * @var int ID of product within Shopify
     */
    public $shopifyId;

    /**
     * @var string Product Type
     */
    public $productType;

    /**
     * @param mixed $value
     * @return $this
     */
    public function shopifyId($value): ProductQuery
    {
        $this->shopifyId = $value;
        return $this;
    }

    public function productType($value): ProductQuery
    {
        $this->productType = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('shopifyproducts');

        // select the price column
        $this->query->select([
            'shopifyproducts.shopifyId',
            'shopifyproducts.jsonData',
            'shopifyproducts.productType',
            'shopifyproducts.bodyHtml',
            'shopifyproducts.bodyHtmlMetafieldId'
        ]);

        if ($this->shopifyId) {
            $this->subQuery->andWhere(Db::parseParam('shopifyproducts.shopifyId', $this->shopifyId));
        }

        if ($this->productType) {
            $this->subQuery->andWhere(Db::parseParam('shopifyproducts.productType', $this->productType));
        }

        return parent::beforePrepare();
    }

}
