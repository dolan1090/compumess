<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem;

use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Framework\Context;
use SwagPublisher\VersionControlSystem\Data\DraftCollection;
use SwagPublisher\VersionControlSystem\Internal\CommonService;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use SwagPublisher\VersionControlSystem\Internal\VersionControlService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class ReleaseAsNewAction
{
    public function __construct(
        private readonly VersionControlService $vcsService,
        private readonly CommonService $commonService,
        private readonly VersionControlCmsGateway $cmsGateway
    ) {
    }

    #[Route('/api/_action/cms_page/{pageId}/releaseAsNew/{draftVersion}', name: 'api.action.cms_page.version_control_system.releaseAsNew', methods: ['POST'])]
    public function onReleaseAsNewAction(
        string $pageId,
        string $draftVersion,
        Context $context
    ): JsonResponse {
        return new JsonResponse($this->releaseAsNew($pageId, $draftVersion, $context));
    }

    public function releaseAsNew(
        string $pageId,
        string $draftVersion,
        Context $context
    ): string {
        $drafts = $this->commonService
            ->requireDraftsByPageIdAndVersion($pageId, $draftVersion, $context);
        $versionContext = $context->createWithVersionId($draftVersion);

        $newPageId = $this->vcsService
            ->releaseAsNew($pageId, CmsPageDefinition::ENTITY_NAME, $versionContext);

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($drafts, $draftVersion): void {
            $this->removeDrafts($drafts, $context);
            $this->setActivitiesReleased($draftVersion, $context);
        });

        return $newPageId;
    }

    private function removeDrafts(DraftCollection $drafts, Context $context): void
    {
        $this->cmsGateway->deleteDrafts($drafts->asDelete(), $context);
    }

    private function setActivitiesReleased(string $draftVersion, Context $context): void
    {
        $criteria = CriteriaFactory::forDraftWithVersion($draftVersion);

        $updateData = $this->cmsGateway
            ->searchActivities($criteria, $context);

        $this->cmsGateway
            ->updateActivities($updateData->forUpdate(['isReleasedAsNew' => true]), $context);
    }
}
