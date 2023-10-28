<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Search;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\RoutingException;
use Symfony\Component\HttpFoundation\Request;

#[Package('buyers-experience')]
class SearchTermExtractor
{
    /**
     * @internal
     */
    public function __construct(private readonly int $searchTermMaxLength = 300)
    {
    }

    public function fromRequest(Request $request): string
    {
        /** @var string[]|string|int|float|bool|null $term */
        $term = $request->get('search');

        if (\is_array($term)) {
            $term = implode(' ', $term);
        } else {
            $term = (string) $term;
        }

        $term = mb_substr(trim($term), 0, $this->searchTermMaxLength);
        $terms = explode(' ', $term);

        $filtered = [];
        foreach ($terms as $term) {
            $term = trim($term);

            if (empty($term)) {
                continue;
            }

            $filtered[] = $term;
        }

        $term = implode(' ', $filtered);

        if (empty($term)) {
            throw RoutingException::missingRequestParameter('search');
        }

        $request->query->set('search', $term);

        return $term;
    }
}
