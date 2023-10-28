<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    0 => [
        'id' => '1',
        'internal_name' => 'Template 1',
        'display_name' => 'Template 1',
        'description' => 'This is a template description',
        'step_by_step_configurator' => 1,
        'active' => 1,
        'confirm_input' => 1,
        'media_id' => '1',
        'media' => [
            'id' => '1',
            'albumID' => '-1',
            'name' => 'handschuh',
            'description' => 'media description',
            'path' => 'media/image/handschuh.jpg',
            'type' => 'IMAGE',
            'extension' => 'jpg',
            'file_size' => '57069',
            'width' => '1280',
            'height' => '1280',
            'userID' => '50',
            'created' => '2017-10-05',
            'album' => [
                'id' => '-1',
                'name' => 'Artikel',
                'parentID' => null,
                'position' => '2',
                'garbage_collectable' => '1',
                'settings' => [
                    'id' => '10',
                    'albumID' => '-1',
                    'create_thumbnails' => '1',
                    'thumbnail_size' => '200x200;600x600;1280x1280',
                    'icon' => 'sprite-inbox',
                    'thumbnail_high_dpi' => '1',
                    'thumbnail_quality' => '90',
                    'thumbnail_high_dpi_quality' => '60',
                ],
            ],
            'uri' => 'http://sw55.internal/media/image/02/ba/72/handschuh.jpg',
        ],
        '_locale' => 'de-DE',
    ],
];
