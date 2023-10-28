<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayEntity;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait DraftIntegrationTrait
{
    abstract protected static function getContainer(): ContainerInterface;

    private function fetchDraftByPageAndVersion(string $pageId, string $versionId): ?ArrayEntity
    {
        return $this->getContainer()->get(VersionControlCmsGateway::class)
            ->searchDrafts(CriteriaFactory::forDraftWithPageAndVersion($pageId, $versionId), Context::createDefaultContext())
            ->first();
    }

    private function fetchFirstMediaId(): string
    {
        return $this->getContainer()
            ->get('media.repository')
            ->searchIds(new Criteria(), Context::createDefaultContext())
            ->firstId();
    }
}
