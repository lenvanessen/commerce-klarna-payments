<?php

namespace lenvanessen\commerce\klarna\responses;

use craft\commerce\base\RequestResponseInterface;

class PurchaseResponse implements RequestResponseInterface
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function isRedirect(): bool
    {
        return false;
    }

    public function isProcessing(): bool
    {
        return $this->data['fraud_status'] === 'PENDING';
    }

    public function isSuccessful(): bool
    {
        return true;
    }

    public function getTransactionReference(): string
    {
        return $this->data['order_id'];
    }

    public function getData()
    {
        return $this->data;
    }

    public function redirect()
    {
        // TODO: Implement redirect() method.
    }

    public function getRedirectData(): array
    {
        // TODO: Implement getRedirectData() method.
    }

    public function getRedirectMethod(): string
    {
        // TODO: Implement getRedirectMethod() method.
    }

    public function getCode(): string
    {
        return 200;
    }

    public function getMessage(): string
    {
        return '';
    }

    public function getRedirectUrl(): string
    {
        // TODO: Implement getRedirectUrl() method.
    }
}