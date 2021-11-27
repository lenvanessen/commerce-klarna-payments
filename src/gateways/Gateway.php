<?php

namespace lenvanessen\commerce\klarna\gateways;

use Craft;
use craft\commerce\base\Gateway as BaseGateway;
use craft\commerce\base\RequestResponseInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\PaymentSource;
use craft\commerce\models\Transaction;
use craft\commerce\Plugin;
use craft\web\Response as WebResponse;
use craft\web\View;
use Klarna\Rest\Payments\Orders;
use Klarna\Rest\Transport\ConnectorInterface;
use Klarna\Rest\Transport\GuzzleConnector;
use lenvanessen\commerce\klarna\CommerceKlarnaPayments;
use lenvanessen\commerce\klarna\models\forms\KlarnaPaymentsForm;
use lenvanessen\commerce\klarna\responses\PurchaseResponse;
use lenvanessen\commerce\klarna\transformers\OrderTransformer;
use yii\base\NotSupportedException;

class Gateway extends BaseGateway
{
    public static function displayName(): string
    {
        return Craft::t('commerce-klarna-payments', 'Klarna Payments');
    }

    /**
     * Setting: UserName
     *
     * @var string
     */
    public $userName;

    /**
     * Setting: Password
     *
     * @var string
     */
    public $password;

    /**
     * Setting: Acquiring Channel
     *
     * @var string
     */
    public $acquiringChannel;

    /**
     * Setting: TestMode
     * @var string
     */
    public $testMode;

    protected $connector;

    /**
     * @return GuzzleConnector
     */
    public function connector(): GuzzleConnector
    {
        if(! $this->connector) {
            $this->connector = GuzzleConnector::create(
                Craft::parseEnv($this->userName),
                Craft::parseEnv($this->password),
                $this->isTestMode()
                    ? ConnectorInterface::EU_TEST_BASE_URL
                    : ConnectorInterface::EU_BASE_URL
            );
        }

        return $this->connector;
    }

    public function getPaymentFormHtml(array $params)
    {
        $view = Craft::$app->getView();

        $previousMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);

        $html = $view->renderTemplate('commerce-klarna-payments/paymentsForm', $params);
        $view->setTemplateMode($previousMode);

        return $html;
    }

    /**
     * Makes an authorize request.
     *
     * @param Transaction $transaction The authorize transaction
     * @param BasePaymentForm $form A form filled with payment info
     * @return RequestResponseInterface
     */
    public function authorize(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
    {
        throw new NotSupportedException(Craft::t('commerce', 'Authorize is not supported by this gateway'));
    }

    /**
     * Makes a capture request.
     *
     * @param Transaction $transaction The capture transaction
     * @param string $reference Reference for the transaction being captured.
     * @return RequestResponseInterface
     */
    public function capture(Transaction $transaction, string $reference): RequestResponseInterface
    {
        throw new NotSupportedException(Craft::t('commerce', 'Capture is not supported by this gateway'));
    }

    /**
     * Complete the authorization for offsite payments.
     *
     * @param Transaction $transaction The transaction
     * @return RequestResponseInterface
     */
    public function completeAuthorize(Transaction $transaction): RequestResponseInterface
    {
        throw new NotSupportedException(Craft::t('commerce', 'Complete Authorize is not supported by this gateway'));
    }

    /**
     * Complete the purchase for offsite payments.
     *
     * @param Transaction $transaction The transaction
     * @return RequestResponseInterface
     */
    public function completePurchase(Transaction $transaction): RequestResponseInterface
    {
        throw new NotSupportedException(Craft::t('commerce', 'Completing Purchases is not supported by this gateway'));
    }

    /**
     * Creates a payment source from source data and user id.
     *
     * @param BasePaymentForm $sourceData
     * @param int $userId
     * @return PaymentSource
     */
    public function createPaymentSource(BasePaymentForm $sourceData, int $userId): PaymentSource
    {
        throw new NotSupportedException(Craft::t('commerce', 'Payment sources is not supported by this gateway'));
    }

    /**
     * Deletes a payment source on the gateway by its token.
     *
     * @param string $token
     * @return bool
     */
    public function deletePaymentSource($token): bool
    {
        throw new NotSupportedException(Craft::t('commerce', 'Payment sources is not supported by this gateway'));
    }

    /**
     * Returns payment form model to use in payment forms.
     *
     * @return BasePaymentForm
     */
    public function getPaymentFormModel(): BasePaymentForm
    {
        return new KlarnaPaymentsForm();
    }

    /**
     * Makes a purchase request.
     *
     * @param Transaction $transaction The purchase transaction
     * @param BasePaymentForm $form A form filled with payment info
     * @return RequestResponseInterface
     */
    public function purchase(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
    {
        $orders = new Orders($this->connector(), Craft::$app->getRequest()->getParam('authorizationToken'));
        $data = $orders->create(
            OrderTransformer::format($transaction->getOrder())
        );

        Craft::$app->getSession()->remove(CommerceKlarnaPayments::STORAGE_SESSION_ID);
        Craft::$app->getSession()->remove(CommerceKlarnaPayments::STORAGE_CLIENT_ID);

        return new PurchaseResponse($data);
    }

    /**
     * Makes an refund request.
     *
     * @param Transaction $transaction The refund transaction
     * @return RequestResponseInterface
     */
    public function refund(Transaction $transaction): RequestResponseInterface
    {
        throw new NotSupportedException(Craft::t('commerce', 'Refunds is not supported by this gateway'));
    }

    /**
     * Processes a webhook and return a response
     *
     * @return WebResponse
     * @throws Throwable if something goes wrong
     */
    public function processWebHook(): WebResponse
    {
        throw new NotSupportedException(Craft::t('commerce', 'Webhooks is not supported by this gateway'));
    }

    /**
     * Returns true if gateway supports authorize requests.
     *
     * @return bool
     */
    public function supportsAuthorize(): bool
    {
        return false;
    }

    /**
     * Returns true if gateway supports capture requests.
     *
     * @return bool
     */
    public function supportsCapture(): bool
    {
        return false;
    }

    /**
     * Returns true if gateway supports completing authorize requests
     *
     * @return bool
     */
    public function supportsCompleteAuthorize(): bool
    {
        return false;
    }

    /**
     * Returns true if gateway supports completing purchase requests
     *
     * @return bool
     */
    public function supportsCompletePurchase(): bool
    {
        return false;
    }

    /**
     * Returns true if gateway supports storing payment sources
     *
     * @return bool
     */
    public function supportsPaymentSources(): bool
    {
        return false;
    }

    /**
     * Returns true if gateway supports purchase requests.
     *
     * @return bool
     */
    public function supportsPurchase(): bool
    {
        return true;
    }

    /**
     * Returns true if gateway supports refund requests.
     *
     * @return bool
     */
    public function supportsRefund(): bool
    {
        return false;
    }

    /**
     * Returns true if gateway supports partial refund requests.
     *
     * @return bool
     */
    public function supportsPartialRefund(): bool
    {
        return false;
    }

    /**
     * Returns true if gateway supports partial payment requests.
     *
     * @return bool
     */
    public function supportsPartialPayment(): bool
    {
        return false;
    }

    /**
     * Returns true if gateway supports webhooks.
     *
     * If `true` is returned, this show the webhook url
     * to the person setting up your gateway (after the gateway is saved).
     * This also affects whether the webhook controller should route webhook requests to your
     * `processWebHook()` method in this class.
     *
     * @return bool
     */
    public function supportsWebhooks(): bool
    {
        return false;
    }

    /**
     * Returns `true` if gateway supports payments for the supplied order.
     *
     * This method is called before a payment is made for the supplied order. It can be
     * used by developers building a checkout and deciding if this gateway should be shown as
     * and option to the customer.
     *
     * It also can prevent a gateway from being used with a particular order.
     *
     * An example of this can be found in the manual payment gateway: It has a setting that can limit its use
     * to only be used with orders that are of a zero value amount. See below for an example of how it uses this
     * method to reject the gateway's use on orders that are not $0.00 if the setting is turned on
     *
     * ```php
     * public function availableForUseWithOrder($order): bool
     *  if ($this->onlyAllowForZeroPriceOrders && $order->getTotalPrice() != 0) {
     *    return false;
     *  }
     * return true;
     * }
     * ```
     *
     * @param $order Order The order this gateway can or can not be available for payment with.
     * @return bool
     */
    public function availableForUseWithOrder(Order $order): bool
    {
        $cart = Plugin::getInstance()->getCarts()->getCart();
        $sessionService = Craft::$app->getSession();
        $country = $cart->shippingAddress ? strtolower($cart->shippingAddress->countryIso) : 'nl';


        return ! $sessionService->has(CommerceKlarnaPayments::STORAGE_NOT_AVAILABLE)
            && $sessionService->has(CommerceKlarnaPayments::STORAGE_CLIENT_ID)
            && in_array($country, ['nl', 'be']);
    }

    /**
     * Retrieves the transaction hash from the webhook data. This could be a query string
     * param or part of the response data.
     *
     * @return mixed
     * @since 3.1.9
     */
    public function getTransactionHashFromWebhook()
    {
        // Not implemented
    }

    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('commerce-klarna-payments/gatewaySettings', ['gateway' => $this]);
    }

    public function isTestMode()
    {
        return (getenv('KLARNA_TEST_MODE') ?? $this->testMode) == '1';
    }
}