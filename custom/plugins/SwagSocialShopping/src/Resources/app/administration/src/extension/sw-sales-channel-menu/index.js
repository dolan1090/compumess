import networkIconMapping from '../../network-icon-mapping';

const { Component } = Shopware;

Component.override('sw-sales-channel-menu', {
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

        buildMenuTree() {
            const tree = this.$super('buildMenuTree');
            const iconById = {};

            this.salesChannels.forEach((salesChannel) => {
                if (!salesChannel.extensions || !salesChannel.extensions.socialShoppingSalesChannel) {
                    return;
                }

                iconById[salesChannel.id] = networkIconMapping[
                    salesChannel.extensions.socialShoppingSalesChannel.network
                ];
            });

            tree.forEach((menuItem) => {
                if (iconById[menuItem.id] !== undefined) {
                    menuItem.icon = iconById[menuItem.id];
                }
            });

            return tree;
        },
    },
});
