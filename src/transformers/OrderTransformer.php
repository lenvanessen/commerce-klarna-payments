<?php

namespace lenvanessen\commerce\klarna\transformers;
use craft\commerce\elements\Order;
use craft\commerce\Plugin;

class OrderTransformer
{
    public static function format(Order $order): array
    {
        return [
            'purchase_country' => Plugin::getInstance()->getAddresses()->getStoreLocationAddress()->getCountry()->iso,
            'purchase_currency'=> $order->paymentCurrency,
            'locale' => $order->orderLanguage,
            'order_amount' => round($order->getTotalPrice()) * 100,
            'order_tax_amount' => ($order->getTotalTaxIncluded() + $order->getTotalTax()) * 100,
            'billing_address' => $order->billingAddress ? AddressTransformer::transform($order->billingAddress, $order->email) : null,
            'shipping_address' => $order->shippingAddress ? AddressTransformer::transform($order->shippingAddress, $order->email) : null,
            'order_lines' => array_map(fn($item) => LineItemTransformer::transform($item), $order->lineItems)
        ];
    }
}