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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class DiscardAction
{
    public function __construct(
        private readonly VersionControlService $vcsService,
        private readonly CommonService $commonService,
        private readonly VersionControlCmsGateway $cmsGateway
    ) {
    }

    #[Route('/api/_action/cms_page/{pageId}/discard/{draftVersion}', name: 'api.action.cms_page.version_control_system.discard', methods: ['POST'])]
    public function onDiscard(
        string $pageId,
        string $draftVersion,
        Context $context
    ): JsonResponse {
        $this->discard($pageId, $draftVersion, $context);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function discard(
        string $pageId,
        string $draftVersion,
        Context $context
    ): void {
        $drafts = $this->commonService
            ->requireDraftsByPageIdAndVersion($pageId, $draftVersion, $context);

        $this->vcsService
            ->discard(
                $pageId,
                CmsPageDefinition::ENTITY_NAME,
                $context->createWithVersionId($draftVersion)
            );

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($drafts, $draftVersion): void {
            $this->removeDrafts($drafts, $context);
            $this->setActivityDiscarded($draftVersion, $context);
        });
    }

    private function removeDrafts(DraftCollection $drafts, Context $context): void
    {
        $this->cmsGateway->deleteDrafts($drafts->asDelete(), $context);
    }

    private function setActivityDiscarded(string $draftVersion, Context $context): void
    {
        $criteria = CriteriaFactory::forDraftWithVersion($draftVersion);

        $activities = $this->cmsGateway
            ->searchActivities($criteria, $context);

        $this->cmsGateway
            ->updateActivities($activities->forUpdate(['isDiscarded' => true]), $context);
    }
}
