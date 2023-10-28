<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait PublisherCmsFixtures
{
    abstract protected static function getContainer(): ContainerInterface;

    private function importPage(?Context $context = null): string
    {
        $fixture = $this->getCmsPageFixture();

        if (!$context) {
            $context = Context::createDefaultContext();
        }

        $this->getContainer()
            ->get('cms_page.repository')
            ->create($fixture, $context);

        return $fixture[0]['id'];
    }

    private function updatePageType(string $pageId, string $type, $context): void
    {
        $this->getContainer()->get('cms_page.repository')->update(
            [[
                'id' => $pageId,
                'type' => $type,
            ]],
            $context
        );
    }

    private function getCmsPageFixture(): array
    {
        return [[
            'id' => Uuid::randomHex(),
            'name' => 'enterprise',
            'type' => 'page',
            'locked' => 0,
            'sections' => [
                [
                    'id' => Uuid::randomHex(),
                    'position' => 1,
                    'type' => 'default',
                    'name' => 'NULL',
                    'locked' => 0,
                    'sizing_mode' => 'boxed',
                    'mobile_behavior' => 'wrap',
                    'blocks' => [
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 1,
                            'section_position' => 'main',
                            'type' => 'form',
                            'name' => 'test form',
                            'locked' => 0,
                            'slots' => [[
                                'id' => Uuid::randomHex(),
                                'type' => 'form',
                                'slot' => 'content',
                            ]],
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 2,
                            'section_position' => 'main',
                            'type' => 'text',
                            'name' => 'test text',
                            'locked' => 0,
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 3,
                            'section_position' => 'main',
                            'type' => 'text',
                            'name' => 'test text',
                            'locked' => 0,
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 4,
                            'section_position' => 'main',
                            'type' => 'text',
                            'name' => 'test locked',
                            'locked' => 1,
                        ],
                    ],
                ],
                [
                    'id' => Uuid::randomHex(),
                    'position' => 2,
                    'type' => 'default',
                    'name' => 'NULL',
                    'locked' => 0,
                    'sizing_mode' => 'boxed',
                    'mobile_behavior' => 'wrap',
                    'blocks' => [
                    ],
                ],
                [
                    'id' => Uuid::randomHex(),
                    'position' => 3,
                    'type' => 'default',
                    'name' => 'NULL',
                    'locked' => 0,
                    'sizing_mode' => 'boxed',
                    'mobile_behavior' => 'wrap',
                    'blocks' => [
                    ],
                ],
                [
                    'id' => Uuid::randomHex(),
                    'position' => 4,
                    'type' => 'default',
                    'name' => 'NULL',
                    'locked' => 0,
                    'sizing_mode' => 'boxed',
                    'mobile_behavior' => 'wrap',
                    'blocks' => [
                    ],
                ],
            ],
        ]];
    }
}
