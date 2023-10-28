import { searchRankingPoint } from '@administration/app/service/search-ranking.service';

/**
 * @package checkout
 */

const overwriteDefaultSearchConfiguration = {
    returns: {
        returnNumber: {
            _searchable: true,
            _score: searchRankingPoint.HIGH_SEARCH_RANKING,
        }
    },
}

export default overwriteDefaultSearchConfiguration;
