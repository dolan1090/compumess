<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Api;

use OpenApi\Annotations as OA;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Message\GenerateDecisionTreeMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class TemplateController extends AbstractController
{
    private const SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_DELETE_PRIVILEGE = 'swag_customized_products_template:delete';

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EntityRepository $templateRepository
    ) {
    }

    /**
     * @OA\Post(
     *      path="/_action/swag-customized-products-template/{templateId}/tree",
     *      description="Dispatch a decision tree generation message for {templateId}",
     *      operationId="dispatchDecisionTreeMessage",
     *      tags={"Admin Api", "SwagCustomizedProductsActions"},
     *
     *     @OA\Parameter(
     *         name="{templateId}",
     *         description="The template id for which the message should be queued",
     *         in="path",
     *         required=true,
     *         allowEmptyValue=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="204",
     *         description="Empty response",
     *     )
     * )
     *
     * @Route(
     *     "/api/_action/swag-customized-products-template/{templateId}/tree",
     *     name="api.action.swag-customized-products-template.tree",
     *     methods={"POST"},
     *     defaults={"_acl"={"swag_customized_products_template.editor"}}
     * )
     */
    public function addTreeGenerationMessageToQueue(string $templateId, Context $context): Response
    {
        $messageBus = $this->messageBus;
        $msg = new GenerateDecisionTreeMessage($templateId);

        $context->scope(Context::SYSTEM_SCOPE, static function (Context $inlineContext) use ($msg, $messageBus): void {
            $messageBus->dispatch($msg->withContext($inlineContext));
        });

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route(
     *     "/api/_action/swag-customized-products-template/{templateId}/{versionId}",
     *     name="api.action.swag-customized-products-template.delete-version",
     *     methods={"DELETE"},
     *     defaults={"_acl"={"swag_customized_products_template.viewer"}}
     * )
     */
    public function deleteVersion(string $templateId, string $versionId, Context $context): Response
    {
        if (!Uuid::isValid($versionId)) {
            throw new InvalidUuidException($versionId);
        }

        if ($versionId === Defaults::LIVE_VERSION) {
            throw CartException::insufficientPermission();
        }

        /** @var AdminApiSource $source */
        $source = $context->getSource();
        $source->setPermissions([
            self::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_DELETE_PRIVILEGE,
        ]);

        $context = $context->createWithVersionId($versionId);
        $this->templateRepository->delete([['id' => $templateId]], $context);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
