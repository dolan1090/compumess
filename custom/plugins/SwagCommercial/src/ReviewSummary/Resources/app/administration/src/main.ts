// Admin entry
import './module/sw-product/view/sw-product-detail-reviews';
import './module/sw-product/page/sw-product-detail';
import ReviewSummaryService from './module/sw-product/service/review-summary.service';

Shopware.Service().register('reviewSummaryService', (container) => {
    const initContainer = Shopware.Application.getContainer('init');

    return new ReviewSummaryService(initContainer.httpClient, Shopware.Service('loginService'));
});
