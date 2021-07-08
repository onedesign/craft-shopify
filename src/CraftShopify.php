<?php
/**
 * Craft Shopify plugin for Craft CMS 3.x
 *
 * Bring Shopify products into Craft
 *
 * @link      https://onedesigncompany.com/
 * @copyright Copyright (c) 2021 One Design Company
 */

namespace onedesign\craftshopify;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\elements\Entry;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\ElementHelper;
use craft\helpers\UrlHelper;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use onedesign\craftshopify\elements\Product;
use onedesign\craftshopify\models\Settings;
use onedesign\craftshopify\services\ProductService;
use onedesign\craftshopify\services\ShopifyService;
use onedesign\craftshopify\services\WebhookService;
use onedesign\craftshopify\utilities\CraftShopifyUtility as CraftShopifyUtilityUtility;
use yii\base\Event;
use yii\base\ModelEvent;

/**
 * Class CraftShopify
 *
 * @author    One Design Company
 * @package   CraftShopify
 * @since     1.0.0
 *
 * @property ShopifyService $shopify
 * @property ProductService $product
 * @property WebhookService $webhook
 */
class CraftShopify extends Plugin
{
    /**
     * @var CraftShopify
     */
    public static CraftShopify $plugin;

    /**
     * @var string
     */
    public $schemaVersion = '1.1.1';

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'shopify' => ShopifyService::class,
            'product' => ProductService::class,
            'webhook' => WebhookService::class
        ]);

        Craft::$app->projectConfig
            ->onAdd(ProductService::CONFIG_PRODUCT_FIELDLAYOUT_KEY, [$this->product, 'handleChangedFieldLayout'])
            ->onUpdate(ProductService::CONFIG_PRODUCT_FIELDLAYOUT_KEY, [$this->product, 'handleChangedFieldLayout'])
            ->onRemove(ProductService::CONFIG_PRODUCT_FIELDLAYOUT_KEY, [$this->product, 'handleChangedContactFieldLayout']);

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'onedesign\craftshopify\console\controllers';
        }

        Event::on(
            Entry::class,
            Entry::EVENT_AFTER_SAVE,
            function(ModelEvent $event) {
                /** @var Entry $entry */
                $entry = $event->sender;

                if (ElementHelper::isDraftOrRevision($entry)) {
                    return;
                }

                CraftShopify::$plugin->product->updateRelatedProducts($entry);
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['webhook'] = 'craft-shopify/webhook/index';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['craft-shopify'] = 'craft-shopify/default';
                $event->rules['craft-shopify/products'] = 'craft-shopify/product';
                $event->rules['craft-shopify/products/<productId:\d+>'] = 'craft-shopify/product/edit-product';
                $event->rules['craft-shopify/products/<productId:\d+>/preview'] = 'craft-shopify/product/preview';

                $event->rules['craft-shopify/settings/shopify'] = 'craft-shopify/settings/shopify';
                $event->rules['craft-shopify/settings/field-layouts'] = 'craft-shopify/settings/field-layouts';
                $event->rules['craft-shopify/settings/templates'] = 'craft-shopify/settings/templates';
            }
        );

        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = Product::class;
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
            }
        );

        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = CraftShopifyUtilityUtility::class;
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
            }
        );

        Craft::info(
            Craft::t(
                'craft-shopify',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function getCpNavItem()
    {
        $item = parent::getCpNavItem();
        $item['subnav'] = [
            'products' => [
                'label' => 'Products',
                'url' => 'craft-shopify/products'
            ],
            'utilities' => [
                'label' => 'Utilities',
                'url' => UrlHelper::cpUrl('utilities/craft-shopify')
            ],
        ];

        if (Craft::$app->config->general->allowAdminChanges) {
            $item['subnav']['settings'] = [
                'label' => 'Settings',
                'url' => 'craft-shopify/settings'
            ];
        }

        return $item;
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Slightly more complex settings
     *
     * @inheritdoc
     */
    public function getSettingsResponse()
    {
        $url = UrlHelper::cpUrl('craft-shopify/settings');
        return Craft::$app->getResponse()->redirect($url);
    }
}
