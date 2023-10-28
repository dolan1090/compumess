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
class UpdateFromLiveVersionAction
{
    private const DRAFT_VERSION_KEY = 'draftVersion';

    public function __construct(
        private readonly VersionControlService $versionControlService,
        private readonly CommonService $commonService,
        private readonly VersionControlCmsGateway $cmsGateway
    ) {
    }

    #[Route('/api/_action/cms_page/{pageId}/updateFromLiveVersion/{draftVersion}', name: 'api.action.cms_page.version_control_system.updateFromLiveVersion', methods: ['POST'])]
    public function onUpdateFromLiveVersionAction(
        string $pageId,
        string $draftVersion,
        Context $context
    ): JsonResponse {
        return new JsonResponse($this->updateFromLiveVersion($pageId, $draftVersion, $context));
    }

    public function updateFromLiveVersion(
        string $pageId,
        string $draftVersion,
        Context $context
    ): string {
        $drafts = $this->commonService
            ->requireDraftsByPageIdAndVersion($pageId, $draftVersion, $context);

        $versionContext = $context->createWithVersionId($draftVersion);

        $newVersionId = $this->versionControlService
            ->updateFromLiveVersion($pageId, CmsPageDefinition::ENTITY_NAME, $versionContext);

        $context->scope(Context::SYSTEM_SCOPE, function (Context $systemContext) use ($drafts, $newVersionId): void {
            $this->updateDrafts($drafts, $newVersionId, $systemContext);
            $this->updateActivities($newVersionId, $systemContext);
        });

        return $newVersionId;
    }

    private function updateDrafts(
        DraftCollection $drafts,
        string $newVersionId,
        Context $context
    ): void {
        $this->cmsGateway
            ->updateDrafts($drafts->forUpdate([self::DRAFT_VERSION_KEY => $newVersionId]), $context);
    }

    private function updateActivities(
        string $newVersionId,
        Context $context
    ): void {
        $updateData = $this->cmsGateway
            ->searchActivities(CriteriaFactory::forDraftWithVersion($newVersionId), $context);

        $this->cmsGateway
            ->updateActivities($updateData->forUpdate([self::DRAFT_VERSION_KEY => $newVersionId]), $context);
    }
}
