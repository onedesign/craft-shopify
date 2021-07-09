<?php
/**
 * craft-shopify-cms module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\services;


use craft\base\Component;
use craft\helpers\StringHelper;
use onedesign\craftshopify\models\WebhookResponse;
use onedesign\craftshopify\records\WebhookResponseRecord;

/**
 * @author    One Design Company
 * @package   craft-shopify-cms
 * @since     1.0.0
 */
class WebhookService extends Component
{

    /**
     * @param WebhookResponse $request
     * @return bool
     * @throws \Exception
     */
    public function saveResponse(WebhookResponse $request)
    {
        $record = new WebhookResponseRecord();
        $record->payload = $request->payload;
        $record->errors = $request->errors;
        $record->uid = StringHelper::UUID();
        $record->type = $request->topic;
        $record->webhookId = $request->webhookId;

        return $record->save();
    }

}
