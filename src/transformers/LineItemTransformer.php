<?php

namespace lenvanessen\commerce\klarna\transformers;

use craft\commerce\models\LineItem;
use craft\commerce\Plugin;
use lenvanessen\commerce\klarna\helpers\VatCalculator;

class LineItemTransformer
{
    public static function transform(LineItem $lineItem)
    {
        return [
            "type" => "physical",
            "reference" => $lineItem->getSku(),
            "name" => $lineItem->getDescription(),
            "quantity" => (int)$lineItem->qty,
            "unit_price" => ($lineItem->getSalePrice() + $lineItem->getTax()) * 100,
            "tax_rate" => (int) round($lineItem->getTaxIncluded() / ($lineItem->getSalePrice() - $lineItem->getTaxIncluded()) * 10000),
            "total_amount" => ($lineItem->getTotal() + $lineItem->getTax()) * 100,
            "total_tax_amount" => ($lineItem->getTax() + $lineItem->getTaxIncluded()) * 100
        ];
    }
}