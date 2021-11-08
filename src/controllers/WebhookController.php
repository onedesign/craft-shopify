<?php
/**
 * craft-shopify module for Craft CMS 3.x
 *
 * @link      https://onedesigncompany.com
 * @copyright Copyright (c) 2021 One Design Company
 */


namespace onedesign\craftshopify\controllers;


use Craft;
use craft\errors\ElementNotFoundException;
use craft\helpers\Json;
use craft\web\Controller;
use onedesign\craftshopify\CraftShopify;
use onedesign\craftshopify\elements\Product;
use onedesign\craftshopify\models\WebhookResponse;
use Throwable;
use yii\base\Action;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * @author    One Design Company
 * @package   craft-shopify
 * @since     1.0.0
 */
class WebhookController extends Controller
{
    /**
     * @inheritdoc
     */
    protected $allowAnonymous = ['index'];

    /**
     * @inheritdoc
     * @param Action $action
     * @return bool
     */
    public function beforeAction($action): bool
    {
        if ($action->id === 'index') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Verify the incoming request
     *
     * @return bool
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    protected function verifySignature(): bool
    {
        $signature = Craft::$app->getRequest()->getHeaders()->get('X-Shopify-Hmac-SHA256');
        if (!$signature) {
            throw new BadRequestHttpException('Request did not contain signature');
        }
        $data = Craft::$app->getRequest()->getRawBody();
        $secret = Craft::parseEnv(CraftShopify::$plugin->getSettings()->webhookSecret);

        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));

        if (!$secret) {
            throw new BadRequestHttpException('Signing secret is not set. Make sure to set this in the plugin settings.');
        }

        if (!hash_equals($signature, $calculatedHmac)) {
            Craft::error('Invalid Signature: ' . $signature . ' Calculated:' . $calculatedHmac, __METHOD__);
            throw new ForbiddenHttpException('The signature is invalid. Please check your configuration and try again.');
        }

        return true;
    }


    /**
     * Handle incoming webhooks
     *
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    public function actionIndex(): Response
    {
        $this->requirePostRequest();
        // $this->verifySignature();
        $request = Craft::$app->getRequest();

        $topic = $request->getHeaders()->get('X-Shopify-Topic');
        $webhookId = $request->getHeaders()->get('X-Shopify-Webhook-Id');
        $payload = $request->getRawBody();

        $response = new WebhookResponse([
            'topic' => $topic,
            'payload' => $payload,
            'webhookId' => $webhookId
        ]);

        $data = Json::decode($payload);

        switch ($topic) {
            case 'products/update':
            case 'products/create':
                $product = CraftShopify::$plugin->product->updateProduct($data);
                $product->isWebhookUpdate = true;

                if (!Craft::$app->getElements()->saveElement($product)) {
                    Craft::error('Failed to save product. ' . $product->getErrors(), __METHOD__);
                    $response->errors = Json::encode($product->getErrors());
                }
                break;
            case 'products/delete':
                if (!CraftShopify::$plugin->product->deleteByShopifyId($data['id'])) {
                    Craft::error('Failed to delete product '. $data['id'], __METHOD__);
                    $response->errors = "Failed to delete product " . $data['id'];
                }
                break;
        }

        CraftShopify::$plugin->webhook->saveResponse($response);
        return $this->asRaw('Webhook received');
    }
}
