import template from './sw-product-detail-reviews.html.twig';
import './sw-product-detail-reviews.scss';

import type RepositoryType from 'src/core/data/repository.data';

const { mapState } = Shopware.Component.getComponentHelper();
const { Context, Data } = Shopware;
const { Criteria } = Data;

type PhrasingType = 'positive' | 'neutral';
type GenerateStatusType = 'success' | 'error' | null;

Shopware.Component.override('sw-product-detail-reviews', {
    template,

    inject: [
        'repositoryFactory',
        'reviewSummaryService'
    ],

    mixins: [
        Shopware.Mixin.getByName('placeholder'),
    ],

    data(): {
        phrasing: PhrasingType,
        isGenerating: boolean,
        showGenerateModal: boolean,
        deleteConfirmModalShown: boolean,
        generateStatus: GenerateStatusType,
        salesChannelId: string | null,
        salesChannels: Array<string>,
        deleteId: string,
        tempSummary: string | null,
        visibleReviewsTotal: number,
    } {
        return {
            phrasing: 'positive',
            isGenerating: false,
            showGenerateModal: false,
            deleteConfirmModalShown: false,
            generateStatus: null,
            salesChannelId: null,
            salesChannels: [],
            deleteId: '',
            tempSummary: null,
            visibleReviewsTotal: 0,
        }
    },

    async created(): Promise<void> {
        this.salesChannels = await this.salesChannelRepository.search(new Shopware.Data.Criteria(), this.apiContext);

        this.salesChannelId = this.salesChannels[0].id;

        await this.getAllVisibleReviews();
    },

    methods: {
        async getAllVisibleReviews(): Promise<void> {
            const criteria = new Criteria();

            criteria.addFilter(Criteria.equals('productId', this.product.id));
            criteria.addFilter(Criteria.equals('status', true));

            const visibleReviews = await this.reviewRepository.searchIds(criteria, Context.api);

            this.visibleReviewsTotal = visibleReviews.total;
        },

        onShowGenerateModal(): void {
            this.generateStatus = null;
            this.showGenerateModal = true;
        },

        onEdit() {
            this.tempSummary = this.product.extensions.reviewSummaries.first().summary;
            this.onShowGenerateModal();
        },

        onCloseGenerateModal(): void {
            this.showGenerateModal = false;
        },

        async onGenerateReviewSummary(): Promise<void> {
            this.isGenerating = true;

            const options = {
                mood: this.phrasing,
                languageIds: [ this.apiContext.languageId ],
                productId: this.product.id,
                salesChannelId: this.salesChannelId,
                fake: false,
            }

            try {
                const response = await this.reviewSummaryService.generate(options);
                this.tempSummary = response.data[this.apiContext.languageId];
                this.generateStatus = 'success';
            } catch (_error) {
                // Error
                this.generateStatus = 'error';
            } finally {
                this.isGenerating = false;
            }
        },

        onApply(): void {
            if (this.product.extensions.reviewSummaries.length > 0) {
                this.updateExistingSummary();
            } else {
                this.createNewSummaryEntity();
            }

            this.$nextTick(() => {
                this.onCloseGenerateModal();
            });
        },

        updateExistingSummary() {
            this.product.extensions.reviewSummaries.first().summary = this.tempSummary;
        },

        createNewSummaryEntity() {
            const newSummaryEntity = this.summaryRepository.create(this.apiContext);
            newSummaryEntity.summary = this.tempSummary;
            newSummaryEntity.languageId = this.apiContext.languageId;
            newSummaryEntity.salesChannelId = this.salesChannelId;
            newSummaryEntity.visible = false;
            this.product.extensions.reviewSummaries.add(newSummaryEntity);
        },

        onDeleteSummary(): void {
            // If translations exist, only empty current language, otherwise delete the whole entity
            if (this.product.extensions.reviewSummaries.first().translations.length > 1) {
                this.product.extensions.reviewSummaries.first().summary = null;
            } else {
                this.product.extensions.reviewSummaries.remove(this.deleteId);
                this.deleteId = '';
            }

            this.$nextTick(() => {
                this.deleteConfirmModalShown = false;
            });
        },

        confirmDelete(id: string): void {
            this.deleteId = id;
            this.deleteConfirmModalShown = true;
        },

        initGenerate(): void {
            this.onShowGenerateModal();
            this.onGenerateReviewSummary();
        },

        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },
    },

    computed: {
        salesChannelRepository(): RepositoryType<'sales_channel'> {
            return this.repositoryFactory.create('sales_channel');
        },

        summaryRepository(): RepositoryType<'product_review_summary'> {
            return this.repositoryFactory.create('product_review_summary');
        },

        phrasingOptions(): Array<{ label: 'string', value: PhrasingType }> {
            return [
                {
                    label: this.$tc('sw-product-detail-review-summary.phrasingOptions.positive'),
                    value: 'positive'
                },
                {
                    label: this.$tc('sw-product-detail-review-summary.phrasingOptions.neutral'),
                    value: 'neutral'
                },
            ];
        },

        ...mapState('swProductDetail', [
            'apiContext',
        ])
    }
});
