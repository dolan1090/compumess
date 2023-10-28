<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

class CoreVersionTest extends TestCase
{
    use IntegrationTestBehaviour;

    private const CREATE_VERSIONS = 10;

    private $languages = [
        'en-GB', 'de-DE',
    ];

    public function testVersionCommitAndVersionCommitDataAreResetAfterMerge(): void
    {
        $context = Context::createDefaultContext();
        $manufacturerId = Uuid::randomHex();
        $manufacturerRepository = $this->getContainer()->get('product_manufacturer.repository');

        $this->createManufacturer($manufacturerRepository, $manufacturerId, $context);

        $beforeCount = $this->getVersionLogCounts();

        $versionContexts = $this->branchIntoVersions($manufacturerRepository, $manufacturerId, $context);
        $inBetweenCount = $this->getVersionLogCounts();

        static::assertGreaterThan($beforeCount['product_manufacturer_count'], $inBetweenCount['product_manufacturer_count']);
        static::assertGreaterThan($beforeCount['product_manufacturer_translation_count'], $inBetweenCount['product_manufacturer_translation_count']);
        static::assertGreaterThan($beforeCount['version_commit_data_count'], $inBetweenCount['version_commit_data_count']);
        static::assertGreaterThan($beforeCount['version_commit_count'], $inBetweenCount['version_commit_count']);

        $this->mergeVersions($versionContexts, $manufacturerRepository, $context);

        $afterCount = $this->getVersionLogCounts();

        static::assertSame($beforeCount['product_manufacturer_count'], $afterCount['product_manufacturer_count']);
        static::assertSame($beforeCount['product_manufacturer_translation_count'], $afterCount['product_manufacturer_translation_count']);

        static::markTestIncomplete('The core is not clearing the tables');
        /** @phpstan-ignore-next-line */
        static::assertSame($beforeCount['version_commit_data_count'], $afterCount['version_commit_data_count']);
        static::assertSame($beforeCount['version_commit_count'], $afterCount['version_commit_count']);
        static::assertSame($afterCount, $beforeCount);
    }

    private function getVersionLogCounts(): array
    {
        return $this->getContainer()->get(Connection::class)->fetchAssociative('
                SELECT
                       (SELECT COUNT(*) FROM version_commit_data) AS version_commit_data_count,
                       (SELECT COUNT(*) FROM version_commit) AS version_commit_count,
                       (SELECT COUNT(*) FROM product_manufacturer) AS product_manufacturer_count,
                       (SELECT COUNT(*) FROM product_manufacturer_translation) AS product_manufacturer_translation_count
       ');
    }

    private function createManufacturer(EntityRepository $productManufacturerRepository, string $productManufacturerId, Context $context): void
    {
        $translations = [];
        foreach ($this->languages as $locale) {
            $translations[$locale] = ['name' => 'original-' . $locale];
        }
        $productManufacturerRepository->create([[
            'id' => $productManufacturerId,
            'translations' => $translations,
        ]], $context);
    }

    /**
     * @return Context[]
     */
    private function branchIntoVersions(EntityRepository $manufacturerRepository, string $manufacturerId, Context $context): array
    {
        $versionContexts = [];

        for ($i = 0; $i < self::CREATE_VERSIONS; ++$i) {
            $versionId = $manufacturerRepository->createVersion($manufacturerId, $context);

            $versionContext = $context->createWithVersionId($versionId);
            $manufacturerRepository->update([[
                'id' => $manufacturerId,
                'name' => (string) \random_int(0, \PHP_INT_MAX),
            ]], $versionContext);

            $versionContexts[] = $versionContext;
        }

        return $versionContexts;
    }

    private function mergeVersions(array $versionContexts, ?EntityRepository $manufacturerRepository, Context $context): void
    {
        foreach ($versionContexts as $versionContext) {
            $manufacturerRepository->merge($versionContext->getVersionId(), $context);
        }
    }
}
