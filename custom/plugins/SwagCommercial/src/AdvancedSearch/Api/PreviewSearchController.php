<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Api;

use Shopware\Commercial\AdvancedSearch\Domain\PreviewSearch\PreviewSearchService;
use Shopware\Core\Framework\Api\Response\ResponseFactoryInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 */
#[Package('buyers-experience')]
#[Route(defaults: ['_routeScope' => ['api']])]
class PreviewSearchController
{
    public function __construct(
        private readonly PreviewSearchService $previewSearchService,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry
    ) {
    }

    #[Route(
        path: '/api/_action/preview-search/search',
        name: 'api.action.preview_search.search',
        methods: ['GET'],
        condition: 'service(\'license\').check(\'ADVANCED_SEARCH-1376205\')'
    )]
    public function search(Request $request, Context $context, ResponseFactoryInterface $responseFactory): Response
    {
        if (!$request->query->has('salesChannelId')) {
            throw RoutingException::missingRequestParameter('salesChannelId');
        }

        if (!$request->query->has('entity')) {
            throw RoutingException::missingRequestParameter('entity');
        }

        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), $request->query->getString('salesChannelId'));

        $result = $this->previewSearchService->search($request, $salesChannelContext);

        $definition = $this->definitionInstanceRegistry->getByEntityName($request->query->getString('entity'));

        return $responseFactory->createListingResponse(new Criteria(), $result, $definition, $request, $context);
    }
}
