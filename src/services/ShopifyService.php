<?php
/**
 * slumberkins module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\services;


use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use onedesign\craftshopify\CraftShopify;
use PHPShopify\ShopifySDK;

/**
 * @author    One Design Company
 * @package   slumberkins
 * @since     1.0.0
 */
class ShopifyService extends Component
{
    protected $client = null;

    public function getClient()
    {
        if (!$this->client) {
            $settings = CraftShopify::$plugin->getSettings();

            // TODO: add error if these don't exist
            $this->client = ShopifySDK::config([
                'ShopUrl' => Craft::parseEnv($settings['hostname']),
                'ApiKey' => Craft::parseEnv($settings['apiKey']),
                'Password' => Craft::parseEnv($settings['apiPassword'])
            ]);
        }

        return $this->client;
    }

    /**
     * @throws \PHPShopify\Exception\ApiException
     * @throws \PHPShopify\Exception\CurlException
     */
    public function getAllProducts(array $params = []): array
    {
        $shopify = $this->getClient();
        $resource = $shopify->Product;

        $nextPageParams = ArrayHelper::merge([
            'limit' => 100
        ], $params);

        /**
         * if -1 was passed in for the limit we need to go through the
         * pagination to get _everything_
         */
        if ($nextPageParams['limit'] === -1) {
            $products = [];
            $nextPageParams['limit'] = 100;

            do {
                $response = $resource->get($nextPageParams);
                $nextPageParams = $resource->getNextPageParams();
                $products = ArrayHelper::merge($products, $response);
            } while (count($nextPageParams) > 0);

            return $products;
        } else {
            return $resource->get($nextPageParams);
        }
    }

    /**
     * @param string | int $id
     * @return array|null
     * @throws \PHPShopify\Exception\ApiException
     * @throws \PHPShopify\Exception\CurlException
     */
    public function getProductById($id): ?array
    {
        if (!$id) {
            return null;
        }

        return $this->getClient()->Product($id)->get();
    }
}
