<?php
/**
 * Commerce Klarna Payments plugin for Craft CMS 3.x
 *
 * Adds Klarna Payments as a payment option for Craft
 *
 * @link      vanessen.io
 * @copyright Copyright (c) 2021 Len van Essen
 */

namespace lenvanessen\commerce\klarna;


use Craft;
use craft\base\Plugin;
use craft\commerce\elements\Order;
use craft\commerce\services\Gateways;
use craft\events\ModelEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use Klarna\Rest\Payments\Sessions;
use lenvanessen\commerce\klarna\gateways\Gateway;
use lenvanessen\commerce\klarna\transformers\OrderTransformer;
use yii\base\Event;

/**
 * Class commerce-klarna-payments
 *
 * @author    Len van Essen
 * @package   commerce-klarna-payments
 * @since     1.0.0
 *
 */
class CommerceKlarnaPayments extends Plugin
{
    const EVENT_FORMAT_ADDRESS = 'onFormatAddress';

    /**
     * @var CommerceKlarnaPayments
     */
    public static $plugin;

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    public function __construct($id, $parent = null, array $config = [])
    {
        parent::__construct($id, $parent, $config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Gateways::class,
            Gateways::EVENT_REGISTER_GATEWAY_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = Gateway::class;
            }
        );

        Event::on(
            Order::class,
            Order::EVENT_AFTER_SAVE,
            function(ModelEvent $event)
            {
                $order = $event->sender;
                if(! $order->gateway || ! $order->gateway instanceof Gateway) {
                    return;
                }

                $this->updateKlarnaSession($order);
            }
        );

        $this->registerRoutes();
    }

    private function registerRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['disableKlarna'] = 'commerce-klarna-payments/api/disable';
            }
        );
    }

    /**
     * Create or update the session in Klarna, and then store the session ID
     *
     * @param Order $order
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    private function updateKlarnaSession(Order $order): void
    {
        $sessionId = Craft::$app->getSession()->get('klarna_session_id');
        $orderPayload = OrderTransformer::format($order);

        $sessionsService = new Sessions($order->getGateway()->connector(), $sessionId);

        if($sessionId !== null) {
            $sessionsService->update($orderPayload);
        } else {
            $sessionsService->create($orderPayload);

            Craft::$app->getSession()->set('klarna_session_id', $sessionsService->getId());
            Craft::$app->getSession()->set('klarna_client_id', $sessionsService->getArrayCopy()['client_token']);
        }
    }
}
