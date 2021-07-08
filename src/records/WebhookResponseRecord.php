<?php
/**
 * slumberkins-cms module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\records;


use craft\db\ActiveRecord;
use craft\helpers\Json;

/**
 * @author    One Design Company
 * @package   slumberkins-cms
 * @since     1.0.0
 *
 * @property string $payload
 * @property string $errors
 * @property string $type
 */
class WebhookResponseRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shopifywebhooks}}';
    }

    /**
     * Get the JSON payload
     *
     * @return mixed|null
     */
    public function getPayload()
    {
        return Json::decode($this->payload);
    }
}
