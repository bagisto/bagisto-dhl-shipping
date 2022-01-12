<?php

return [
    'dhl' => [
        'code' => 'dhl',
        'title' => 'DHL Shipping',
        'description' => 'DHL Shipping',
        'active' => true,
        'default_rate' => '15',
        'type' => 'per_unit',
        'class' => 'Webkul\DHLShipping\Carriers\Dhl'
    ],
];