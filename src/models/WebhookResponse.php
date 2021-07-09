<?php
/**
 * craft-shopify-cms module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\models;


use craft\base\Model;

/**
 * @author    One Design Company
 * @package   craft-shopify-cms
 * @since     1.0.0
 */
class WebhookResponse extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var string|null
     */
    public $topic;

    /**
     * @var string|null
     */
    public $uid;

    /**
     * @var string|null
     */
    public $payload;

    /**
     * @var string|null
     */
    public $errors;
}
