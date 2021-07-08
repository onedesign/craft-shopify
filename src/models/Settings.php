<?php
/**
 * Craft Shopify plugin for Craft CMS 3.x
 *
 * Bring Shopify products into Craft
 *
 * @link      https://onedesigncompany.com/
 * @copyright Copyright (c) 2021 One Design Company
 */

namespace onedesign\craftshopify\models;

use craft\base\Model;
use craft\validators\TemplateValidator;

/**
 * @author    One Design Company
 * @package   CraftShopify
 * @since     1.0.0
 */
class Settings extends Model
{
    /**
     * @var string
     */
    public string $apiKey = '$SHOPIFY_API_KEY';


    /**
     * @var string
     */
    public string $apiPassword = '$SHOPIFY_API_PASSWORD';

    /**
     * @var string
     */
    public string $hostname = '$SHOPIFY_HOSTNAME';

    /**
     * @var string
     */
    public string $webhookSecret = '$SHOPIFY_WEBHOOK_SECRET';

    /**
     * @var string
     */
    public $templatePath;

    /**
     * @var string
     */
    public $previewPath;


    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['apiKey', 'apiPassword', 'hostname', 'webhookSecret', 'templatePath', 'previewPath'], 'string'],
            [['apiKey', 'apiPassword', 'hostname', 'templatePath'], 'required'],
            [['templatePath', 'previewPath'], TemplateValidator::class]
        ];
    }
}
