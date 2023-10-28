<?php declare(strict_types=1);

namespace Shopware\Commercial\ContentGenerator\Api;

use Shopware\Commercial\ContentGenerator\Domain\Cms\ContentCreator;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @final
 *
 * @internal
 */
#[Package('content')]
#[Route(defaults: ['_routeScope' => ['api']])]
class CmsContentController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ContentCreator $contentCreator,
        private readonly DataValidator $dataValidator
    ) {
    }

    #[Route(
        path: '/api/_action/cms-content/generate',
        name: 'commercial.api.cms_content.get',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'CONTENT_GENERATOR-1759573\')'
    )]
    public function generate(Request $request): JsonResponse
    {
        $cmsContentGeneratorDefinition = new DataValidationDefinition();
        $cmsContentGeneratorDefinition->add('sentence', new NotBlank(), new Type('string'));
        $this->dataValidator->validate($request->request->all(), $cmsContentGeneratorDefinition);

        $content = $this->contentCreator->generate((string) $request->request->get('sentence'));

        return new JsonResponse($content);
    }

    #[Route(
        path: '/api/_action/cms-content/edit',
        name: 'commercial.api.cms_content.edits.get',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'CONTENT_GENERATOR-1759573\')'
    )]
    public function editContent(Request $request): JsonResponse
    {
        $cmsContentEditorDefinition = new DataValidationDefinition();
        $cmsContentEditorDefinition->add('input', new NotBlank(), new Type('string'));
        $cmsContentEditorDefinition->add('instruction', new NotBlank(), new Type('string'));
        $this->dataValidator->validate($request->request->all(), $cmsContentEditorDefinition);

        $content = $this->contentCreator->edit(
            (string) $request->request->get('input'),
            (string) $request->request->get('instruction')
        );

        return new JsonResponse($content);
    }
}
