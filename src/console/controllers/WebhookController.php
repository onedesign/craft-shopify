<?php
/**
 * slumberkins-cms module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */

namespace onedesign\craftshopify\console\controllers;

use craft\console\Controller;
use onedesign\craftshopify\CraftShopify;
use yii\console\ExitCode;


/**
 * Allows you to manage webhooks
 *
 * @author    One Design Company
 * @package   slumberkins-cms
 * @since     1.0.0
 */
class WebhookController extends Controller
{
    public $defaultAction = 'purge';

    /**
     * Length in days to purge before
     *
     * @var int
     */
    public $olderThan = 30;

    /**
     * @param string $actionID
     * @return array
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);
        $options[] = 'olderThan';

        return $options;
    }

    /**
     * Purge webhook records
     */
    public function actionPurge()
    {
        if (!$this->olderThan) {
            $this->olderThan = $this->prompt('Older than how many days?', [
                'default' => 30
            ]);
        }

        CraftShopify::$plugin->webhook->purgeResponses($this->olderThan);
        return ExitCode::OK;
    }

}
