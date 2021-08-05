<?php

namespace lenvanessen\commerce\klarna\transformers;

use craft\commerce\models\LineItem;
use craft\commerce\Plugin;
use lenvanessen\commerce\klarna\helpers\VatCalculator;

class LineItemTransformer
{
    public static function transform(LineItem $lineItem)
    {
        $vatCalculator = new VatCalculator($lineItem);

        return [
            "type" => "physical",
            "reference" => $lineItem->getSku(),
            "name" => $lineItem->getDescription(),
            "quantity" => (int)$lineItem->qty,
            "unit_price" => $vatCalculator->getUnitGross(),
            "tax_rate" => $vatCalculator->getTaxRate(),
            "total_amount" => $vatCalculator->getTotalGross(),
            "total_tax_amount" => $vatCalculator->getTaxTotal()
        ];
    }
}