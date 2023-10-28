<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem;

use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Framework\Util\Random;
use SwagPublisher\VersionControlSystem\Internal\CommonService;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use SwagPublisher\VersionControlSystem\Internal\VersionControlService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class DuplicateAction
{
    public function __construct(
        public readonly VersionControlService $vcsService,
        public readonly CommonService $commonService,
        public readonly VersionControlCmsGateway $cmsGateway,
        public readonly EntityRepository $cmsPageRepository
    ) {
    }

    #[Route('/api/_action/cms_page/{pageId}/duplicate/{draftVersion}', name: 'api.action.cms_page.version_control_system.duplicate', methods: ['POST'])]
    public function onDuplicate(
        string $pageId,
        string $draftVersion,
        Context $context
    ): JsonResponse {
        return new JsonResponse($this->duplicate($pageId, $draftVersion, $context));
    }

    public function duplicate(string $pageId, string $draftVersion, Context $context): string
    {
        $userId = $this->commonService
            ->extractUserId($context);

        /** @var ArrayEntity $originalDraft */
        $originalDraft = $this->commonService
            ->requireDraftsByPageIdAndVersion($pageId, $draftVersion, $context)
            ->first();

        $newVersionContext = $this->vcsService
            ->duplicate($pageId, CmsPageDefinition::ENTITY_NAME, $context->createWithVersionId($draftVersion));

        /** @var CmsPageEntity $newVersionPage */
        $newVersionPage = $this->cmsPageRepository
            ->search(CriteriaFactory::withIds($pageId), $newVersionContext)
            ->first();

        $context->scope(Context::SYSTEM_SCOPE, function (Context $systemContext) use ($newVersionPage, $originalDraft, $userId): void {
            $this->createDraft($newVersionPage, $originalDraft, $userId, $systemContext);
            $this->logActivity($newVersionPage, $originalDraft->get('name'), $userId, $systemContext);
        });

        return $newVersionContext->getVersionId();
    }

    private function createDraft(
        CmsPageEntity $newVersionPage,
        ArrayEntity $originalDraft,
        ?string $userId,
        Context $context
    ): void {
        $this->cmsGateway->createDrafts([[
            'name' => $originalDraft->get('name'),
            'previewMediaId' => $originalDraft->get('previewMediaId'),
            'pageId' => $newVersionPage->getId(),
            'ownerId' => $userId,
            'draftVersion' => $newVersionPage->getVersionId(),
            'deepLinkCode' => Random::getBase64UrlString(32),
        ]], $context);
    }

    private function logActivity(CmsPageEntity $newVersionPage, string $name, ?string $userId, Context $context): void
    {
        $this->cmsGateway->createActivities([[
            'pageId' => $newVersionPage->getId(),
            'draftVersion' => $newVersionPage->getVersionId(),
            'userId' => $userId,
            'name' => $name,
        ]], $context);
    }
}
