import template from './sw-sales-channel-detail-domains.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-sales-channel-detail-domains', {
    template,
    data() {
        return {
            isLoading: true,
            socialShoppingSalesChannels: []
        };
    },
    inject: [
        'repositoryFactory',
    ],
    computed: {
        socialShoppingSalesChannelRepository() {
            return this.repositoryFactory.create('swag_social_shopping_sales_channel');
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        isUsedBySocialShoppingSalesChannel(record) {
            return this.socialShoppingSalesChannels.find(socialShoppingSalesChannel => socialShoppingSalesChannel.salesChannelDomainId === record.id);
        },
        async loadSocialShoppingSalesChannels() {
            const criteria = new Criteria();

            criteria.addFilter(Criteria.equalsAny('salesChannelDomainId', this.salesChannel.domains.map(domain => domain.id)));
            criteria.addAssociation('salesChannel');

            this.socialShoppingSalesChannels = await this.socialShoppingSalesChannelRepository.search(criteria, Shopware.Context.api);
        },
        createdComponent() {
            this.loadSocialShoppingSalesChannels();
            this.isLoading = false;
        },
    },
});
