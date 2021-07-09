<?php
/**
 * craft-shopify module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\controllers;


use craft\web\Controller;
use onedesign\craftshopify\CraftShopify;
use onedesign\craftshopify\models\Settings;
use yii\web\Response;

/**
 * @author    One Design Company
 * @package   craft-shopify
 * @since     1.0.0
 */
class SettingsController extends Controller
{

    /**
     * Render the Shopify settings route
     *
     * @param Settings|null $settings
     * @return Response
     */
    public function actionShopify(Settings $settings = null): Response
    {
        if ($settings === null) {
            $settings = CraftShopify::$plugin->getSettings();
        }

        return $this->renderTemplate('craft-shopify/settings/shopify', [
            'settings' => $settings,
            'plugin' => CraftShopify::getInstance()
        ]);
    }

    public function actionTemplates(Settings $settings = null)
    {
        if ($settings === null) {
            $settings = CraftShopify::$plugin->getSettings();
        }

        return $this->renderTemplate('craft-shopify/settings/templates', [
            'settings' => $settings,
            'plugin' => CraftShopify::getInstance()
        ]);
    }

    /**
     * Render the field layout settings route
     *
     * @param Settings|null $settings
     * @return Response
     */
    public function actionFieldLayouts(Settings $settings = null): Response
    {
        if ($settings === null) {
            $settings = CraftShopify::$plugin->getSettings();
        }

        return $this->renderTemplate('craft-shopify/settings/field-layouts', [
            'settings' => $settings
        ]);
    }

}
