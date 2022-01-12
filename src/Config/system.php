<?php

return [
    [
        'key'  => 'sales',
        'name' => 'admin::app.admin.system.sales',
        'sort' => 5
    ], [
        'key'  => 'sales.carriers',
        'name' => 'admin::app.admin.system.shipping-methods',
        'sort' => 1,
    ], [
        'key'    => 'sales.carriers.dhl',
        'name'   => 'dhl::app.admin.system.dhl',
        'sort'   => 3,
        'fields' => [

            [
                'name'          => 'title',
                'title'         => 'dhl::app.admin.system.title',
                'type'          => 'depends',
                'depend'        => 'active:1',
                'validation'    => 'required_if:active,1',
                'channel_based' => false,
                'locale_based'  => true,

            ], [
                'name'          => 'description',
                'title'         => 'admin::app.admin.system.description',
                'type'          => 'textarea',
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'active',
                'title'         => 'dhl::app.admin.system.enable-checkout',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'is_calculate_tax',
                'title'         => 'admin::app.admin.system.calculate-tax',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => false,
            ], [
                'name'          => 'sandbox_mode',
                'title'         => 'dhl::app.admin.system.sandbox-mode',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'access_id',
                'title'         => 'dhl::app.admin.system.access-id',
                'type'          => 'depends',
                'depend'        => 'active:1',
                'validation'    => 'required_if:active,1',
                'channel_based' => false,
                'locale_based'  => true
            ], [
                'name'          => 'password',
                'title'         => 'dhl::app.admin.system.password',
                'type'          => 'depends',
                'depend'        => 'active:1',
                'validation'    => 'required_if:active,1',
                'channel_based' => false,
                'locale_based'  => true
            ], [
                'name'          => 'account_number',
                'title'         => 'dhl::app.admin.system.account-number',
                'type'          => 'depends',
                'depend'        => 'active:1',
                'validation'    => 'required_if:active,1',
                'channel_based' => false,
                'locale_based'  => true
            ], [
                'name'          => 'weight_unit',
                'title'         => 'dhl::app.admin.system.weight-unit',
                'type'          => 'select',
                'channel_based' => false,
                'locale_based'  => true,
                'options'       => [
                    [
                        'title' => 'dhl::app.admin.system.kilograms',
                        'value' => 'KG'
                    ], [
                        'title' => 'dhl::app.admin.system.pounds',
                        'value' => 'LB'
                    ]
                ]
            ], [
                'name'          => 'dimension_unit',
                'title'         => 'dhl::app.admin.system.dimension-unit',
                'type'          => 'select',
                'channel_based' => false,
                'locale_based'  => true,
                'options'       => [
                    [
                        'title' => 'dhl::app.admin.system.inches',
                        'value' => 'IN'
                    ], [
                        'title' => 'dhl::app.admin.system.cms',
                        'value' => 'CM'
                    ]
                ]
            ], [
                'name'          => 'height',
                'title'         => 'dhl::app.admin.system.height',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true
            ], [
                'name'          => 'depth',
                'title'         => 'dhl::app.admin.system.depth',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true
            ], [
                'name'          => 'width',
                'title'         => 'dhl::app.admin.system.width',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true
            ], [
                'name'          => 'content_type',
                'title'         => 'dhl::app.admin.system.content-type',
                'type'          => 'select',
                'channel_based' => false,
                'locale_based'  => true,
                'options'       => [
                    [
                        'title' => 'dhl::app.admin.system.documents',
                        'value' => 'documents'
                    ], [
                        'title' => 'dhl::app.admin.system.non-documents',
                        'value' => 'non documents'
                    ]
                ]
            ], [
                'name'          => 'allowed_methods',
                'title'         => 'dhl::app.admin.system.allowed-methods',
                'type'          => 'multiselect',
                'depend'        => 'content_type:documents',
                'channel_based' => false,
                'locale_based'  => true,
                'options'       => [
                    [
                        'title' => 'Easy shop',
                        'value' => 'Easy shop'],
                    [
                        'title' => 'Sprintline',
                        'value' => 'Sprintline'],
                    [
                        'title' => 'Secureline',
                        'value' => 'Secureline'],
                    [
                        'title' => 'Express easy',
                        'value' => 'Express easy'],
                    [
                        'title' =>  'Europack',
                        'value' =>  'Europack'],
                    [
                        'title' =>  'Break bulk express',
                        'value' =>  'Break bulk express'],
                    [
                        'title' => 'Medical express',
                        'value' => 'Medical express'],
                    [
                        'title' => 'Express worldwide',
                        'value' => 'Express worldwide'],
                    [
                        'title' => 'Express worldwide',
                        'value' => 'Express worldwide'],
                    [
                        'title' => 'Express 9:00',
                        'value' => 'Express 9:00'],
                    [
                        'title' => 'Express 10:30',
                        'value' => 'Express 10:30'],
                    [
                        'title' => 'Domestic economy select',
                        'value' => 'Domestic economy select'],
                    [
                        'title' => 'Economy select',
                        'value' => 'Economy select'],
                    [
                        'title' => 'Domestic express 9:00',
                        'value' => 'Domestic express 9:00'],
                    [
                        'title' => 'Domestic express',
                        'value' => 'Domestic express'],
                    [
                        'title' => 'Others',
                        'value' => 'Others'],
                    [
                        'title' => 'Globalmail business',
                        'value' => 'Globalmail business'],
                    [
                        'title' => 'Same day',
                        'value' => 'Same day'],
                    [
                        'title' => 'Express 12:00',
                        'value' => 'Express 12:00'],
                    [
                        'title' => 'Express envelope',
                        'value' => 'Express envelope'],

                    [
                        'title' =>  'Domestic express 12:00 (Doc)',
                        'value' => 'Domestic express 12:00 (Doc)'
                    ],
                    [
                        'title' =>  'Easy shop (Doc)',
                        'value' => 'Easy shop (Doc)'
                    ],
                    [
                        'title' =>  'Jetline (Doc)',
                        'value' => 'Jetline (Doc)'
                    ],
                    [
                        'title' =>  'Express easy',
                        'value' => 'Express easy (Doc)'
                    ],
                    [
                        'title' =>  'Express worldwide (Doc)',
                        'value' => 'Express worldwide (Doc)'
                    ],
                    [
                        'title' =>  'Medical express (Doc)',
                        'value' => 'Medical express (Doc)'
                    ],
                    [
                        'title' =>  'Express 9:00',
                        'value' => 'Express 9:00 (Doc)'
                    ],
                    [
                        'title' =>  'Freight worldwide (Doc)',
                        'value' => 'Freight worldwide (Doc)'
                    ],
                    [
                        'title' =>  'Economy select (Doc)',
                        'value' => 'Economy select (Doc)'
                    ],
                    [
                        'title' =>  'Jumbo box (Doc)',
                        'value' => 'Jumbo box (Doc)'
                    ],
                    [
                        'title' =>  'Express 10:30 (Doc)',
                        'value' => 'Express 10:30 (Doc)'
                    ],
                    [
                        'title' =>  'Europack (Doc)',
                        'value' => 'Europack (Doc)'
                    ],
                    [
                        'title' =>  'Express 12:00 (Doc)',
                        'value' => 'Express 12:00 (Doc)'
                    ],
                ]
            ], [
                'name'          => 'ready_time',
                'title'         => 'dhl::app.admin.system.ready-time',
                'type'          => 'text',
                'info'          => 'dhl::app.admin.system.note',
                'channel_based' => false,
                'locale_based'  => true
            ], [
                'name'          => 'allowed_country',
                'title'         => 'dhl::app.admin.system.allow-country',
                'type'          => 'multiselect',
                'channel_based' => true,
                'locale_based'  => true,
                'repository'    => 'Webkul\DHLShipping\Repositories\DhlDetailRepository@getCountries'
            ],
        ]
    ]
];