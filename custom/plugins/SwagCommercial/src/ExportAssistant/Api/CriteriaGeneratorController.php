<?php declare(strict_types=1);

namespace Shopware\Commercial\ExportAssistant\Api;

use Shopware\Commercial\ExportAssistant\Service\CriteriaGenerator;
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
#[Package('system-settings')]
#[Route(defaults: ['_routeScope' => ['api']])]
class CriteriaGeneratorController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CriteriaGenerator $criteriaGenerator,
        private readonly DataValidator $dataValidator
    ) {
    }

    #[Route(
        path: '/api/_action/generate-criteria',
        name: 'commercial.api.generate-criteria.get',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EXPORT_ASSISTANT-4992823\')'
    )]
    public function generate(Request $request): JsonResponse
    {
        $cmsContentEditorDefinition = new DataValidationDefinition();
        $cmsContentEditorDefinition->add('prompt', new NotBlank(), new Type('string'));
        $cmsContentEditorDefinition->add('entity', new Type('string'));
        $this->dataValidator->validate($request->request->all(), $cmsContentEditorDefinition);

        $criteria = $this->criteriaGenerator->generate((string) $request->request->get('prompt'), (string) $request->request->get('entity'));

        return new JsonResponse($criteria);
    }
}
