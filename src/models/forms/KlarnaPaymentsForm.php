<?php

namespace lenvanessen\commerce\klarna\models\forms;

use craft\commerce\models\payments\BasePaymentForm;

class KlarnaPaymentsForm extends BasePaymentForm
{
    public $dateOfBirth;
    public $gender;
}