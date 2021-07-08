<?php
/**
 * slumberkins module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\services;


use benf\neo\elements\Block;
use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\helpers\ProjectConfig;
use craft\helpers\Queue;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use craft\web\View;
use onedesign\craftshopify\CraftShopify;
use onedesign\craftshopify\elements\Product;
use onedesign\craftshopify\jobs\PushProductData;
use onedesign\craftshopify\records\ProductRecord;
use PHPShopify\Exception\ApiException;
use PHPShopify\Exception\CurlException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

/**
 * @author    One Design Company
 * @package   slumberkins
 * @since     1.0.0
 */
class ProductService extends Component
{
    const CONFIG_PRODUCT_FIELDLAYOUT_KEY = 'craftshopify.productFieldLayout';
    const METAFIELD_NAMESPACE = 'cms';

    /**
     * Get the model to put Shopify data into
     */
    public function getProductModel($shopifyId): Product
    {
        if (!$product = Product::find()->shopifyId($shopifyId)->one()) {
            $product = new Product();
            $product->shopifyId = (int)$shopifyId;

            return $product;
        }

        return $product;
    }

    /**
     * Populate the Craft element with Shopify response data
     *
     * @param Product $product
     * @param $shopifyProduct
     */
    public function populateProductModel(Product $product, $shopifyProduct)
    {
        $product->jsonData = Json::encode($shopifyProduct);
        $product->title = $shopifyProduct['title'];
        $product->slug = $shopifyProduct['handle'];
        $product->dateCreated = $shopifyProduct['created_at'];
        $product->dateUpdated = $shopifyProduct['updated_at'];
        $product->productType = $shopifyProduct['product_type'];
        $product->bodyHtml = $shopifyProduct['body_html'] ?? '';
    }

    /**
     * Get the Craft element for a Shopify ID
     *
     * @param int $productId
     * @return array|ElementInterface|Product|null
     */
    public function getProductById(int $productId)
    {
        return Product::find()
            ->id($productId)
            ->one();
    }

    public function getAllProductTypes()
    {
        $types = ProductRecord::find()
            ->select(['productType'])
            ->distinct()
            ->column();

        ArrayHelper::removeValue($types, '');

        return $types;
    }

    /**
     * Update all products related to an entry
     *
     * @param Entry $entry
     * @throws Exception
     */
    public function updateRelatedProducts(Entry $entry)
    {
        $relatedProducts = Product::find()
            ->relatedTo($entry)
            ->all();

        if (count($relatedProducts)) {
            foreach ($relatedProducts as $product) {
                Queue::push(new PushProductData([
                    'productId' => $product->id
                ]));
            }
        }

        $relatedBlocks = Block::find()
            ->relatedTo($entry)
            ->all();

        if (count($relatedBlocks) > 0) {
            foreach ($relatedBlocks as $block) {
                if ($owner = $block->getOwner()) {
                    if ($owner instanceof Product) {
                        Queue::push(new PushProductData([
                            'productId' => $owner->id
                        ]));
                    }
                }
            }
        }
    }

    /**
     * Update Data in Shopify
     *
     * @param Product $product
     * @throws ApiException
     * @throws CurlException
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function pushDataToShopify(Product $product)
    {
        $client = CraftShopify::$plugin->shopify->getClient();
        $productRecord = ProductRecord::findOne($product->id);

        if (!$productRecord) {
            Craft::error('Product record not found ' . $product->id, __METHOD__);
            return;
        }

        if (!$productRecord->bodyHtmlMetafieldId) {
            $metafield = $client->Product($product->shopifyId)->Metafield->post([
                'namespace' => self::METAFIELD_NAMESPACE,
                'key' => 'body_html',
                'value' => '<!-- CMS CONTENT -->',
                'value_type' => 'string'
            ]);

            $productRecord->bodyHtmlMetafieldId = $metafield['id'];
            $productRecord->save();
        } else {
            $templatePath = CraftShopify::$plugin->getSettings()->templatePath;
            $html = Craft::$app->getView()->renderTemplate($templatePath, [
                'product' => $product
            ], View::TEMPLATE_MODE_SITE);

            $client->Product($product->shopifyId)->Metafield($productRecord->bodyHtmlMetafieldId)->put([
                'id' => $productRecord->bodyHtmlMetafieldId,
                'value' => $html,
                'value_type' => 'string'
            ]);
        }
    }

    /**
     * @param array|null $shopifyData
     * @return Product|void
     */
    public function updateProduct(array $shopifyData = null)
    {
        if (!$shopifyData) {
            return;
        }

        $shopifyId = $shopifyData['id'];

        $product = $this->getProductModel($shopifyId);
        $this->populateProductModel($product, $shopifyData);

        return $product;
    }


    /**
     * Saves the product field layout
     *
     * @param FieldLayout $fieldLayout
     * @return bool
     * @throws ErrorException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     * @throws \Exception
     */
    public function saveFieldLayout(FieldLayout $fieldLayout): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $fieldLayoutConfig = $fieldLayout->getConfig();
        $uid = StringHelper::UUID();

        $projectConfig->set(self::CONFIG_PRODUCT_FIELDLAYOUT_KEY, [$uid => $fieldLayoutConfig], 'Save the contact field layout');

        return true;
    }

    /**
     * Handle project config changes
     *
     * @throws Exception
     */
    public function handleChangedFieldLayout()
    {
        // Use this because we want this to trigger this if anything changes inside but ONLY ONCE
        static $parsed = false;
        if ($parsed) {
            return;
        }

        $parsed = true;
        $data = Craft::$app->getProjectConfig()->get(self::CONFIG_PRODUCT_FIELDLAYOUT_KEY, true);

        $fieldsService = Craft::$app->getFields();

        if (empty($data) || empty($config = reset($data))) {
            $fieldsService->deleteLayoutsByType(Product::class);
            return;
        }

        // Make sure fields are processed
        ProjectConfig::ensureAllFieldsProcessed();

        // Save the field layout
        $layout = FieldLayout::createFromConfig($config);
        $layout->id = $fieldsService->getLayoutByType(Product::class)->id;
        $layout->type = Product::class;
        $layout->uid = key($data);
        $fieldsService->saveLayout($layout);
    }
}
