import template from './sw-sales-channel-detail.html.twig';

const { Component } = Shopware;

Component.override('sw-sales-channel-detail', {
    template,

    inject: [
        'socialShoppingService',
        'acl'
    ],

    data() {
        return {
            isNewEntity: false,
            networkClasses: null,
            networkName: 'base',
            salesChannelData: null,
            productExportData: null,
        };
    },

    computed: {
        isSocialShopping() {
            return this.salesChannel && this.salesChannel.typeId.indexOf(Shopware.Defaults.SalesChannelTypeSocialShopping) !== -1;
        },

        socialShoppingType() {
            if (!this.salesChannel
                || !this.salesChannel.extensions
                || !this.salesChannel.extensions.socialShoppingSalesChannel
                || !this.networkClasses
            ) {
                return '';
            }

            return `sw-social-shopping-channel-network-${this.getNetworkByFQCN(
                this.salesChannel.extensions.socialShoppingSalesChannel.network,
            )}`;
        },

        shouldShowSidebar() {
            return !!this.salesChannel?.extensions?.socialShoppingSalesChannel
                && this.salesChannelData
                && this.productExportData
                && this.isTemplateEditable(this.socialShoppingType);
        },
    },

    watch: {
        salesChannel() {
            if (this.isSocialShopping && !this.salesChannel.extensions.socialShoppingSalesChannel.configuration) {
                this.salesChannel.extensions.socialShoppingSalesChannel.configuration = {};
                this.setNetworkName();
            }

            this.$forceUpdate();
        },

        networkName() {
            this.$forceUpdate();
        },

        isSocialShopping() {
            this.salesChannelData = this.salesChannel;
            this.productExportData = this.productExport;

            this.$forceUpdate();
        },
    },

    methods: {
        createdComponent() {
            this.$super('createdComponent');

            this.socialShoppingService.getNetworks().then((networks) => {
                this.networkClasses = networks;
                this.setNetworkName();
            });
        },
        async onSave(){
            if (this.isSocialShopping) {
                this.mapProductExportConfiguration();
            }

            this.$super('onSave')
        },
        mapProductExportConfiguration() {
            if (!this.salesChannel.productExports || this.salesChannel.productExports.length === 0) {
                return;
            }

            Array.from(Object.entries(this.salesChannel.extensions.socialShoppingSalesChannel.configuration)).forEach(([key,value]) => {
                this.salesChannel.productExports[0][key] = value;
            });
        },
        getNetworkByFQCN(fqcn) {
            return Object.keys(this.networkClasses).filter((key) => {
                return this.networkClasses[key] === fqcn;
            })[0];
        },

        setNetworkName() {
            if (!this.salesChannel
                || !this.salesChannel.extensions
                || !this.salesChannel.extensions.socialShoppingSalesChannel
                || !this.networkClasses
            ) {
                return;
            }

            this.networkName = this.getNetworkByFQCN(this.salesChannel.extensions.socialShoppingSalesChannel.network);
        },

        isTemplateEditable(socialShoppingType) {
            return socialShoppingType !== 'sw-social-shopping-channel-network-pinterest';
        },

        getLoadSalesChannelCriteria() {
            const criteria = this.$super('getLoadSalesChannelCriteria');

            criteria.addAssociation('socialShoppingSalesChannel');

            if (criteria.includes) {
                criteria.addIncludes({
                    sales_channel: ['socialShoppingSalesChannel'],
                    swag_social_shopping_sales_channel: ['network', 'configuration'],
                });
            }

            return criteria;
        },
    },
});
