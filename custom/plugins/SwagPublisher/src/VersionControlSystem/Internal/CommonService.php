<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Internal;

use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use SwagPublisher\VersionControlSystem\Data\DraftCollection;
use SwagPublisher\VersionControlSystem\Exception\NoDraftFound;

class CommonService
{
    private VersionControlCmsGateway $cmsGateway;

    public function __construct(
        VersionControlCmsGateway $cmsGateway
    ) {
        $this->cmsGateway = $cmsGateway;
    }

    public function requireDraftsByPageIdAndVersion(string $pageId, string $draftVersion, Context $context): DraftCollection
    {
        $criteria = CriteriaFactory::forDraftWithPageAndVersion($pageId, $draftVersion);

        $result = $this->cmsGateway
            ->searchDrafts($criteria, $context);

        if (!$result->count()) {
            throw new NoDraftFound();
        }

        return $result;
    }

    public function extractUserId(Context $context): ?string
    {
        $source = $context->getSource();

        $userId = null;
        if ($source instanceof AdminApiSource) {
            $userId = $source->getUserId();
        }

        return $userId;
    }
}
