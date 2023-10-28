<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    0 => [
        'id' => '1',
        'option_id' => '1',
        'name' => 'Value 1',
        'ordernumber' => null,
        'value' => '{"_value": ""}',
        'is_default_value' => 0,
        'position' => 1,
        'is_once_surcharge' => 1,
        'price' => [
            'option_id' => null,
            'value_id' => '1',
            'surcharge' => null,
            'percentage' => 10,
            'is_percentage_surcharge' => 1,
            'tax' => [
                'id' => '1',
                'tax' => '19.00',
                'description' => '19%',
            ],
        ],
        'currencyShortName' => [
            'currencyShortName' => 'EUR',
        ],
        '_locale' => 'de-DE',
    ],
    1 => [
        'id' => '2',
        'option_id' => '1',
        'name' => 'Value 2',
        'ordernumber' => null,
        'value' => '{"_value": ""}',
        'is_default_value' => 0,
        'position' => 2,
        'is_once_surcharge' => 1,
        'price' => [
            'option_id' => null,
            'value_id' => '2',
            'surcharge' => 10,
            'percentage' => null,
            'is_percentage_surcharge' => 0,
            'tax' => [
                'id' => '1',
                'tax' => '19.00',
                'description' => '19%',
            ],
        ],
        'currencyShortName' => [
            'currencyShortName' => 'EUR',
        ],
        '_locale' => 'de-DE',
    ],
];
