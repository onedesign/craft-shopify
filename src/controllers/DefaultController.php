<?php
/**
 * Craft Shopify plugin for Craft CMS 3.x
 *
 * Bring Shopify products into Craft
 *
 * @link      https://onedesigncompany.com/
 * @copyright Copyright (c) 2021 One Design Company
 */

namespace onedesign\craftshopify\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use onedesign\craftshopify\CraftShopify;
use onedesign\craftshopify\elements\Product;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * @author    One Design Company
 * @package   CraftShopify
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    public function actionIndex()
    {
        return $this->redirect(UrlHelper::cpUrl('craft-shopify/products'));
    }

    /**
     * Save the product field layout
     *
     * @return Response|null
     * @throws Exception
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws ErrorException
     */
    public function actionSaveFieldLayout()
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = Product::class;

        if (!CraftShopify::$plugin->product->saveFieldLayout($fieldLayout)) {
            $this->setFailFlash(Craft::t('craft-shopify', "Couldn't save product fields"));
            return null;
        }

        $this->setSuccessFlash(Craft::t('craft-shopify', "Product fields saved"));
        return $this->redirectToPostedUrl();
    }
}
