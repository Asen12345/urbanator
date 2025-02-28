<?php

return [
    'product_default_properties' => ['DETALI', 'TABLE_R'],
    'catalog_id' => 21,
    'offers_id' => 25,
    'catalog_filter_properties' => [
        'product' => [['link', 'PROPERTY_BRAND', 'product']],
        'offers' => [
            ['list', 'PROPERTY_SOLES', 'product'],
            ['list', 'PROPERTY_RAZMER', 'offer'],
            ['list', 'PROPERTY_COLOR_SL1', 'product'],
            ['list', 'PROPERTY_UPPERMATERIAL', 'product'],
            ['list', 'PROPERTY_BRYUKI_ZHENSKIE', 'offer'],
            ['list', 'PROPERTY_RAZMER_ODEZHDY', 'offer']
        ]
    ],
];
