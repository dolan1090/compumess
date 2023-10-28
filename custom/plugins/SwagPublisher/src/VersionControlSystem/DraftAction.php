<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem;

use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use SwagPublisher\VersionControlSystem\Internal\CommonService;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use SwagPublisher\VersionControlSystem\Internal\VersionControlService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class DraftAction
{
    public function __construct(
        private readonly VersionControlService $vcsService,
        private readonly CommonService $commonService,
        private readonly VersionControlCmsGateway $cmsGateway
    ) {
    }

    #[Route('/api/_action/cms_page/{pageId}/draft', name: 'api.action.cms_page.version_control_system.draft', methods: ['POST'])]
    public function onDraft(
        string $pageId,
        RequestDataBag $request,
        Context $context
    ): JsonResponse {
        return new JsonResponse($this->draft($pageId, $request->get('name'), $context));
    }

    public function draft(
        string $pageId,
        ?string $name,
        Context $context
    ): string {
        $userId = $this->commonService
            ->extractUserId($context);

        $versionId = $this->branchPage($pageId, $context);

        $context->scope(Context::SYSTEM_SCOPE, function (Context $systemContext) use ($pageId, $versionId, $userId, $name): void {
            $inheritedDraftData = $this->cmsGateway
                ->fetchInheritedDraftData($pageId, $systemContext);

            if ($name) {
                $inheritedDraftData['name'] = $name;
            }

            $this->addDraft($inheritedDraftData, $versionId, $userId, $systemContext);
            $this->logActivity($inheritedDraftData, $versionId, $userId, $systemContext);
        });

        return $versionId;
    }

    private function branchPage(string $pageId, Context $context): string
    {
        return $this->vcsService
            ->branch($pageId, CmsPageDefinition::ENTITY_NAME, $context)->getVersionId();
    }

    private function addDraft(array $inheritedDraftData, string $versionId, ?string $userId, Context $context): void
    {
        $draftData = \array_merge([
            'ownerId' => $userId,
            'draftVersion' => $versionId,
            'deepLinkCode' => Random::getBase64UrlString(32),
        ], $inheritedDraftData);

        $this->cmsGateway->createDrafts([$draftData], $context);
    }

    private function logActivity(array $inheritedDraftData, string $versionId, ?string $userId, Context $context): void
    {
        $activityData = \array_merge([
            'draftVersion' => $versionId,
            'userId' => $userId,
        ], $inheritedDraftData);

        $this->cmsGateway->createActivities([$activityData], $context);
    }
}
