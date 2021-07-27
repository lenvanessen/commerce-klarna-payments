<?php

namespace lenvanessen\commerce\klarna\controllers;

use Craft;
use craft\commerce\Plugin;
use craft\web\Controller;
use lenvanessen\commerce\klarna\gateways\Gateway;

class ApiController extends Controller
{
    protected $allowAnonymous = ['disable'];
    public $enableCsrfValidation = false;

    public function actionDisable()
    {
        $this->requirePostRequest();

        // Remove selected option
        $cart = Plugin::getInstance()->getCarts()->getCart();

        if($cart->gateway instanceof Gateway) {
            $cart->gatewayId = null;
            Craft::$app->getElements()->saveElement($cart);
        }

        // Set lock
        Craft::$app->getSession()->set('klarna_locked', true);

        return true;
    }
}