import networkIconMapping from '../../network-icon-mapping';
import template from './sw-sales-channel-list.html.twig';

const { Component } = Shopware;

Component.override('sw-sales-channel-list', {
    template,

    computed: {
        salesChannelCriteria() {
            const criteria = this.$super('salesChannelCriteria');

            criteria.addAssociation('socialShoppingSalesChannel');

            if (criteria.includes) {
                criteria.addIncludes({
                    sales_channel: ['socialShoppingSalesChannel'],
                    swag_social_shopping_sales_channel: ['network'],
                });
            }

            return criteria;
        },
    },

    methods: {
        getIconName(salesChannel) {
            const socialShoppingSalesChannel = salesChannel.extensions.socialShoppingSalesChannel;

            if (!socialShoppingSalesChannel) {
                return salesChannel.type.iconName;
            }

            return networkIconMapping[socialShoppingSalesChannel.network];
        },
    },
});
