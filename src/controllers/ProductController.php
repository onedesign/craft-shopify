<?php
/**
 * slumberkins module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\controllers;


use Craft;
use craft\errors\ElementNotFoundException;
use craft\helpers\Queue;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use craft\web\twig\TemplateLoaderException;
use craft\web\View;
use onedesign\craftshopify\CraftShopify;
use onedesign\craftshopify\elements\Product;
use onedesign\craftshopify\jobs\SyncProduct;
use Throwable;
use yii\base\Exception;
use yii\base\ExitException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author    One Design Company
 * @package   slumberkins
 * @since     1.0.0
 */
class ProductController extends Controller
{
    /**
     * @var string[]
     */
    protected $allowAnonymous = ['sync-product'];


    /**
     * @throws ExitException
     */
    public function actionIndex()
    {
        return $this->renderTemplate('craft-shopify/products');
    }

    /**
     * Preps product edit variables
     *
     * @param array $variables
     */
    private function prepEditProductVariables(array &$variables)
    {
        if (empty($variables['product'])) {
            if (!empty($variables['productId'])) {
                $variables['product'] = Product::find()->id($variables['productId'])->one();

                if (!$variables['product']) {
                    throw new NotFoundHttpException('Product not found');
                }
            }
        }
    }

    /**
     * Preview the Craft part of a shopify product
     *
     * @param int|null $productId
     * @return Response
     */
    public function actionPreview(int $productId = null): ?Response
    {
        if (!$productId) {
            return null;
        }

        $product = Product::find()->id($productId)->one();
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $previewPath = CraftShopify::$plugin->getSettings()->previewPath;

        return $this->renderTemplate($previewPath, [
            'product' => $product
        ], View::TEMPLATE_MODE_SITE);
    }

    /**
     * Edit view for a product element
     *
     * @param int|null $productId
     * @return Response
     */
    public function actionEditProduct(int $productId = null, Product $product = null): Response
    {
        $variables = [
            'productId' => $productId,
            'product' => $product
        ];

        $this->prepEditProductVariables($variables);

        /** @var Product $product */
        $variables['bodyClass'] = 'edit-product';
        if (CraftShopify::$plugin->getSettings()->previewPath) {
            $variables['previewUrl'] = UrlHelper::cpUrl('craft-shopify/products/' . $product->id . '/preview');
        }

        return $this->renderTemplate('craft-shopify/products/_edit', $variables);
    }

    /**
     * Save Product element
     *
     * @return Response|null
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        $productId = $this->request->getRequiredParam('productId');
        $product = Product::find()->id($productId)->one();

        $product->setFieldValuesFromRequest('fields');

        if (!Craft::$app->getElements()->saveElement($product)) {
            if ($this->request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $product->getErrors()
                ]);
            }

            $this->setFailFlash('Couldn\'t save Product');
            Craft::$app->getUrlManager()->setRouteParams([
                'product' => $product
            ]);

            return null;
        }

        if ($this->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'id' => $product->id,
                'title' => $product->title,
                'shopifyId' => $product->shopifyId,
                'cpEditUrl' => $product->getCpEditUrl()
            ]);
        }

        $this->setSuccessFlash('Product Saved');
        return $this->redirectToPostedUrl($product);
    }

    /**
     * Sync Craft product with Shopify
     *
     * @throws BadRequestHttpException
     */
    public function actionSyncProducts(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $productIds = $request->getRequiredParam('productIds');

        $params = [
            'published_status' => 'published',
            'status' => 'active,draft',
            'limit' => -1
        ];

        if ($productIds !== '*') {
            $productIds = $this->normalizeArguments($productIds);
            $params['ids'] = implode(',', $productIds);
        }

        $products = CraftShopify::$plugin->shopify->getAllProducts($params);

        foreach ($products as $product) {
            $job = new SyncProduct();
            $job->productData = $product;

            Queue::push($job);
        }

        if ($this->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true
            ]);
        }

        $this->setSuccessFlash('Product Sync Started');
        return $this->redirectToPostedUrl();
    }

    /**
     * Normalizes values as an array of arguments.
     *
     * @param string|array|null $values
     *
     * @return string[]
     */
    private function normalizeArguments($values): array
    {

        if (is_string($values)) {
            $values = StringHelper::split($values);
        }

        if (is_array($values)) {
            // Flatten multi-dimensional arrays
            array_walk($values, function(&$value) {
                if (is_array($value)) {
                    $value = reset($value);
                }
            });

            // Remove empty values
            return array_filter($values);
        }

        return [];
    }
}
