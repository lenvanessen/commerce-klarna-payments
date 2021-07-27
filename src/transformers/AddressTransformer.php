<?php

namespace lenvanessen\commerce\klarna\transformers;

use craft\commerce\models\Address;
use lenvanessen\commerce\klarna\CommerceKlarnaPayments;
use lenvanessen\commerce\klarna\events\FormatAddressEvent;

class AddressTransformer {

    public static function transform(Address $address, string $email = '')
    {
        $formattedAddress = [
            'organization_name' => $address->businessName,
            'given_name' => $address->firstName,
            'family_name' => $address->lastName,
            'title' => $address->title,
            'email' => $email,
            'street_address' => $address->address1,
            'street_address2' => $address->address2,
            'postal_code' => $address->zipCode,
            'city' => $address->city,
            'region' => $address->stateName,
            'phone' => $address->phone,
            'country' => $address->country->iso,
        ];


        $event = new FormatAddressEvent(['address' => $formattedAddress, 'sourceAddress' => $address]);
        CommerceKlarnaPayments::getInstance()->trigger(CommerceKlarnaPayments::EVENT_FORMAT_ADDRESS, $event);

        return $event->address;
    }
}