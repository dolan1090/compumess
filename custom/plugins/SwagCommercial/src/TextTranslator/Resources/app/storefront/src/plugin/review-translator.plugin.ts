/**
 * @package inventory
*/
import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

declare var window: StorefrontWindow;

export default class ReviewTranslator extends Plugin {

    public static options = {
        reviewItemSelector: '.product-detail-review-item',
        buttonSelector: '[data-review-id]',
        reviewTranslateUrl: window.router['frontend.product.review.translate'],
        reviewTitleSelector: '.product-detail-review-item-title > .h5',
        reviewContentSelector: '.product-detail-review-item-content',
        reviewCommentSelector: '.product-detail-review-item-comment .blockquote-footer',
        alertWrapperSelector: '.review-tab-pane-alert-wrapper',
        translatedFromSelector: '.swag-text-translator_translated-from',
    };

    private el: HTMLElement;
    private alertWrapper: HTMLElement;

    private reviews: Review[] = [];

    private reviewTranslateOptions: ReviewTranslateOptions;

    init(): void {
        this.reviewTranslateOptions = JSON.parse(this.el.dataset.reviewTranslator);
        this.alertWrapper = document.querySelector(ReviewTranslator.options.alertWrapperSelector);

        document.body.addEventListener('click', this._onClickButton.bind(this))
    }

    private _onClickButton(event: Event): void {
        const target = event.target as HTMLElement;

        if (target.tagName !== 'BUTTON' || typeof target.dataset?.reviewId === 'undefined') {
            return;
        }

        const reviewItem: HTMLElement = target.closest(ReviewTranslator.options.reviewItemSelector);

        const reviewId = target.dataset.reviewId;

        let review = this.reviews.find(review => review.id === reviewId);
        if (typeof review  === 'undefined') {
            this.reviews.push({
                id: reviewId,
                originalTitle: reviewItem.querySelector(ReviewTranslator.options.reviewTitleSelector).innerHTML,
                originalContent: reviewItem.querySelector(ReviewTranslator.options.reviewContentSelector).innerHTML,
                originalComment: reviewItem.querySelector(ReviewTranslator.options.reviewCommentSelector)?.innerHTML ?? null,
                isTranslated: false,
            });

           review = this.reviews.find(review => review.id === reviewId);
        }

        this._translate(review, reviewItem);
    }

    /**
     * sends request to translate review or updates the text, if it is already available
     *
     * @private
     */
    private _translate(review: Review, reviewItem: HTMLElement): void {
        if (review?.translatedTitle || review?.translatedContent) {
            this._updateText(review);
            return;
        }

        // Else set loading spanner and fetch data
        ElementLoadingIndicatorUtil.create(reviewItem);

        const client = new HttpClient();

        client.post(ReviewTranslator.options.reviewTranslateUrl, JSON.stringify({ reviewId: review.id }), (responseText: string, response: XMLHttpRequest) => {
            if (response.status == 200) {
                this.alertWrapper.hidden = true;
                this._processTranslation(responseText);
            } else {
                this.alertWrapper.hidden = false;
            }

            ElementLoadingIndicatorUtil.remove(reviewItem);
        });
    }

    /**
     * processes the API response
     *
     * @private
     */
    private _processTranslation(response: string): void {
        const res = JSON.parse(response);

        const review = this.reviews.find(review => review.id === res.id);
        review.translatedTitle = res.title;
        review.translatedContent = res.content;
        review.translatedComment = res.comment;
        review.translatedLanguageName = res.language_name;

        this._updateText(review);
    }

    /**
     * update html to display correct html
     *
     * @private
     */
    private _updateText(review: Review): void {
        const button = document.querySelector(`[data-review-id="${review.id}"]`);
        const item = button.closest(ReviewTranslator.options.reviewItemSelector);

        const reviewTitle = review.isTranslated ? review.originalTitle : review.translatedTitle;
        const reviewContent = review.isTranslated ? review.originalContent : review.translatedContent;
        const reviewComment = review.isTranslated ? review.originalComment : ' ' + this.reviewTranslateOptions.snippets.ourFeedback + review.translatedComment;
        const buttonText = review.isTranslated
            ? this.reviewTranslateOptions.snippets.translateTo
            : this.reviewTranslateOptions.snippets.revertToOriginal;
        const translatedFrom = review.isTranslated ? '' : this.reviewTranslateOptions.snippets.translatedFrom.replace("%language%", review.translatedLanguageName);

        item.querySelector(ReviewTranslator.options.reviewTitleSelector).innerHTML = reviewTitle;
        item.querySelector(ReviewTranslator.options.reviewContentSelector).innerHTML = reviewContent;
        item.querySelector(ReviewTranslator.options.translatedFromSelector).innerHTML = translatedFrom;

        const commentElement = item.querySelector(ReviewTranslator.options.reviewCommentSelector);
        if (commentElement !== null) {
            commentElement.innerHTML = reviewComment;
        }

        button.innerHTML = buttonText;
        review.isTranslated = !review.isTranslated;
    }
}
