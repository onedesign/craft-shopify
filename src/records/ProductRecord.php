<?php
/**
 * craft-shopify module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\records;


use craft\db\ActiveQuery;
use craft\db\ActiveRecord;
use craft\records\Element;

/**
 * @author    One Design Company
 * @package   craft-shopify
 * @since     1.0.0
 *
 * @property int $shopifyId
 * @property string $jsonData
 * @property string $productType
 * @property int $id
 * @property string $bodyHtml
 * @property int $bodyHtmlMetafieldId
 */
class ProductRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shopifyproducts}}';
    }

    public function getElement(): ActiveQuery
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }

}
