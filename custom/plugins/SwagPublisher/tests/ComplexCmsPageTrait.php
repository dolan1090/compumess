<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

trait ComplexCmsPageTrait
{
    abstract public function getConnection(): Connection;

    private function fetchGermanLanguageId(): string
    {
        $id = $this->getConnection()
            ->fetchOne('SELECT `id` FROM `language` WHERE `name` = "Deutsch"');

        return Uuid::fromBytesToHex($id);
    }

    private function getComplexCmsPageFixture(string $pageId): array
    {
        $germanLanguageId = $this->fetchGermanLanguageId();

        return [[
            'id' => $pageId,
            'name' => 'enterprise',
            'type' => 'page',
            'locked' => 0,
            'translations' => [
                [
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'name' => 'enterprise',
                ],
                [
                    'languageId' => $germanLanguageId,
                    'name' => 'enterprise',
                ],
            ],
            'sections' => $this->getSectionFixture($germanLanguageId),
        ]];
    }

    private function getSectionFixture(?string $germanLanguageId = null): array
    {
        if (!$germanLanguageId) {
            $germanLanguageId = $this->fetchGermanLanguageId();
        }

        return [
            [
                'id' => Uuid::randomHex(),
                'position' => 0,
                'type' => 'default',
                'name' => 'NULL',
                'locked' => 0,
                'sizing_mode' => 'boxed',
                'mobile_behavior' => 'wrap',
                'blocks' => [
                    $this->createBlockFixture($germanLanguageId),
                    $this->createBlockFixture($germanLanguageId),
                    $this->createBlockFixture($germanLanguageId),
                    $this->createBlockFixture($germanLanguageId),
                ],
            ],
        ];
    }

    private function createBlockFixture(string $germanLanguageId): array
    {
        return [
            'id' => Uuid::randomHex(),
            'position' => 0,
            'section_position' => 'main',
            'type' => 'text',
            'name' => 'test form',
            'locked' => 0,
            'slots' => [
                [
                    'id' => Uuid::randomHex(),
                    'type' => 'form',
                    'slot' => 'content',
                    'translations' => [
                        Defaults::LANGUAGE_SYSTEM => ['config' => ['foo' => ['source' => FieldConfig::SOURCE_MAPPED, 'value' => 'en']]],
                        $germanLanguageId => ['config' => ['boo' => ['source' => FieldConfig::SOURCE_MAPPED, 'value' => 'de']]],
                    ],
                ],
                [
                    'id' => Uuid::randomHex(),
                    'type' => 'form',
                    'slot' => 'content',
                    'translations' => [
                        Defaults::LANGUAGE_SYSTEM => ['config' => ['foo' => ['source' => FieldConfig::SOURCE_MAPPED, 'value' => 'en']]],
                        $germanLanguageId => ['config' => ['boo' => ['source' => FieldConfig::SOURCE_MAPPED, 'value' => 'de']]],
                    ],
                ],
                [
                    'id' => Uuid::randomHex(),
                    'type' => 'form',
                    'slot' => 'content',
                    'translations' => [
                        Defaults::LANGUAGE_SYSTEM => ['config' => ['foo' => ['source' => FieldConfig::SOURCE_MAPPED, 'value' => 'en']]],
                        $germanLanguageId => ['config' => ['boo' => ['source' => FieldConfig::SOURCE_MAPPED, 'value' => 'de']]],
                    ],
                ],
            ],
        ];
    }
}
