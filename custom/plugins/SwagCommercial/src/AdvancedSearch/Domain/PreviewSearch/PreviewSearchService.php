<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\PreviewSearch;

use Shopware\Commercial\AdvancedSearch\Domain\Search\MultiSearchResult;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\Search\AbstractProductSearchRoute;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[Package('buyers-experience')]
class PreviewSearchService
{
    public const PREVIEW_MODE_STATE = 'PREVIEW_MODE_STATE';

    public function __construct(
        private readonly AbstractProductSearchRoute $productSearchRoute
    ) {
    }

    public function search(Request $request, SalesChannelContext $context): EntitySearchResult
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            throw new LicenseExpiredException();
        }

        $entity = $request->query->getString('entity');

        $criteria = new Criteria();
        $criteria->addState(self::PREVIEW_MODE_STATE);

        $response = $this->productSearchRoute->load($request, $context, $criteria);

        $listingResult = $response->getListingResult();

        if ($entity === ProductDefinition::ENTITY_NAME) {
            return $listingResult;
        }

        $request->query->set('p', $request->query->getInt('p', 1));

        $multiSearchResult = $listingResult->getExtension('multiSearchResult');

        $emptyResponse = new EntitySearchResult(
            $entity,
            0,
            new EntityCollection(),
            null,
            $criteria,
            $context->getContext()
        );

        if (!$multiSearchResult instanceof MultiSearchResult) {
            return $emptyResponse;
        }

        return $multiSearchResult->getResult($entity) ?? $emptyResponse;
    }
}
