<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    0 => [
        'id' => '1',
        'template_id' => '1',
        'name' => 'Option 1',
        'description' => 'This is option description',
        'ordernumber' => null,
        'required' => 0,
        'type' => 'textfield',
        'position' => 1,
        'default_value' => null,
        'placeholder' => null,
        'is_once_surcharge' => 1,
        'max_text_length' => null,
        'min_value' => null,
        'max_value' => null,
        'max_file_size' => null,
        'min_date' => null,
        'max_date' => null,
        'max_files' => null,
        'interval' => null,
        'allows_multiple_selection' => null,
        'price' => [
            'option_id' => '1',
            'value_id' => null,
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
    ],
    1 => [
        'id' => '2',
        'template_id' => '1',
        'name' => 'Option 2',
        'description' => 'This is option description',
        'ordernumber' => null,
        'required' => 0,
        'type' => 'time',
        'position' => 2,
        'default_value' => null,
        'placeholder' => null,
        'is_once_surcharge' => 1,
        'max_text_length' => null,
        'min_value' => null,
        'max_value' => null,
        'max_file_size' => null,
        'min_date' => null,
        'max_date' => null,
        'max_files' => null,
        'interval' => null,
        'allows_multiple_selection' => null,
        'price' => [
            'option_id' => '2',
            'value_id' => null,
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
    ],
];
