const { Component, Context } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-settings-rule-detail-assignments', {
    computed: {
        associationEntitiesConfig() {
            const associationEntitiesConfig = this.$super('associationEntitiesConfig');
            associationEntitiesConfig.push(...this.swagDynamicAccessRuleEntityConfigs);
            return associationEntitiesConfig;
        },

        swagDynamicAccessRuleEntityConfigs() {
            if (!this.getRuleAssignmentConfiguration) {
                return [
                    {
                        entityName: 'product',
                        associationName: 'swagDynamicAccessProducts',
                        label: this.$tc('swag-dynamic-access.sw-settings-rule.detail.associations.productVisibility'),
                        criteria: () => {
                            const criteria = new Criteria();
                            criteria.setLimit(this.associationLimit);
                            criteria.addFilter(Criteria.equals('swagDynamicAccessRules.id', this.rule.id));

                            return criteria;
                        },
                        api: () => {
                            const api = Object.assign({}, Context.api);
                            api.inheritance = true;

                            return api;
                        },
                        detailRoute: 'sw.product.detail.base',
                        gridColumns: [
                            {
                                property: 'name',
                                label: 'Name',
                                rawData: true,
                                sortable: false,
                                routerLink: 'sw.product.detail.base',
                                allowEdit: false,
                            },
                        ],
                    },
                    {
                        entityName: 'category',
                        associationName: 'swagDynamicAccessCategories',
                        label: this.$tc('swag-dynamic-access.sw-settings-rule.detail.associations.categoryVisibility'),
                        criteria: () => {
                            const criteria = new Criteria();
                            criteria.setLimit(this.associationLimit);
                            criteria.addFilter(Criteria.equals('swagDynamicAccessRules.id', this.rule.id));

                            return criteria;
                        },
                        detailRoute: 'sw.category.detail.base',
                        gridColumns: [
                            {
                                property: 'name',
                                label: 'Name',
                                rawData: true,
                                sortable: false,
                                routerLink: 'sw.category.detail.base',
                                allowEdit: false,
                            },
                        ],
                    },
                    {
                        entityName: 'landing_page',
                        associationName: 'swagDynamicAccessLandingPages',
                        label: this.$tc('swag-dynamic-access.sw-settings-rule.detail.associations.landingPageVisibility'),
                        criteria: () => {
                            const criteria = new Criteria();
                            criteria.setLimit(this.associationLimit);
                            criteria.addFilter(Criteria.equals('swagDynamicAccessRules.id', this.rule.id));

                            return criteria;
                        },
                        detailRoute: 'sw.category.landingPageDetail.base',
                        gridColumns: [
                            {
                                property: 'name',
                                label: 'Name',
                                rawData: true,
                                sortable: false,
                                routerLink: 'sw.category.landingPageDetail.base',
                                allowEdit: false,
                            },
                        ],
                    },
                ];
            }

            return [
                {
                    id: 'swagDynamicAccessProducts',
                    notAssignedDataTotal: 0,
                    allowAdd: true,
                    entityName: 'product',
                    associationName: 'swagDynamicAccessProducts',
                    label: 'swag-dynamic-access.sw-settings-rule.detail.associations.productVisibility',
                    criteria: () => {
                        const criteria = new Criteria();
                        criteria.setLimit(this.associationLimit);
                        criteria.addFilter(Criteria.equals('swagDynamicAccessRules.id', this.rule.id));
                        criteria.addAssociation('options.group');
                        criteria.addAssociation('swagDynamicAccessRules');

                        return criteria;
                    },
                    api: () => {
                        const api = Object.assign({}, Context.api);
                        api.inheritance = true;

                        return api;
                    },
                    detailRoute: 'sw.product.detail.base',
                    gridColumns: [
                        {
                            property: 'name',
                            label: 'Name',
                            rawData: true,
                            sortable: true,
                            routerLink: 'sw.product.detail.prices',
                            allowEdit: false,
                        },
                    ],
                    deleteContext: {
                        type: 'many-to-many',
                        entity: 'product',
                        column: 'extensions.swagDynamicAccessRules',
                    },
                    addContext: {
                        type: 'many-to-many',
                        entity: 'swag_dynamic_access_product_rule',
                        column: 'productId',
                        searchColumn: 'name',
                        criteria: () => {
                            const criteria = new Criteria();
                            criteria.addFilter(
                                Criteria.not('AND', [Criteria.equals('swagDynamicAccessRules.id', this.rule.id)]),
                            );
                            criteria.addAssociation('options.group');

                            return criteria;
                        },
                        gridColumns: [
                            {
                                property: 'name',
                                label: 'Name',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                            {
                                property: 'productNumber',
                                label: 'Product number',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                            {
                                property: 'manufacturer.name',
                                label: 'Manufacturer',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                            {
                                property: 'active',
                                label: 'Active',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                            {
                                property: 'stock',
                                label: 'Stock',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                            {
                                property: 'availableStock',
                                label: 'Available',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                        ],
                    },
                },
                {
                    id: 'swagDynamicAccessCategories',
                    associationName: 'swagDynamicAccessCategories',
                    notAssignedDataTotal: 0,
                    allowAdd: true,
                    entityName: 'category',
                    label: 'swag-dynamic-access.sw-settings-rule.detail.associations.categoryVisibility',
                    criteria: () => {
                        const criteria = new Criteria();
                        criteria.setLimit(this.associationLimit);
                        criteria.addFilter(Criteria.equals('swagDynamicAccessRules.id', this.rule.id));
                        criteria.addAssociation('swagDynamicAccessRules');

                        return criteria;
                    },
                    detailRoute: 'sw.category.detail.base',
                    gridColumns: [
                        {
                            property: 'name',
                            label: 'Name',
                            rawData: true,
                            sortable: true,
                            routerLink: 'sw.category.detail.base',
                            allowEdit: false,
                        },
                    ],
                    deleteContext: {
                        type: 'many-to-many',
                        entity: 'category',
                        column: 'extensions.swagDynamicAccessRules',
                    },
                    addContext: {
                        type: 'many-to-many',
                        entity: 'swag_dynamic_access_category_rule',
                        column: 'categoryId',
                        searchColumn: 'name',
                        association: 'swagDynamicAccessRules',
                        criteria: () => {
                            const criteria = new Criteria();
                            criteria.addFilter(Criteria.equals('parentId', null));

                            return criteria;
                        },
                        gridColumns: [
                            {
                                property: 'eventName',
                                label: 'Event',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                            {
                                property: 'title',
                                label: 'Title',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                            {
                                property: 'active',
                                label: 'Active',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                        ],
                    },
                },
                {
                    id: 'swagDynamicAccessLandingPages',
                    associationName: 'swagDynamicAccessLandingPages',
                    notAssignedDataTotal: 0,
                    allowAdd: true,
                    entityName: 'landing_page',
                    label: 'swag-dynamic-access.sw-settings-rule.detail.associations.landingPageVisibility',
                    criteria: () => {
                        const criteria = new Criteria();
                        criteria.setLimit(this.associationLimit);
                        criteria.addFilter(Criteria.equals('swagDynamicAccessRules.id', this.rule.id));
                        criteria.addAssociation('swagDynamicAccessRules');

                        return criteria;
                    },
                    detailRoute: 'sw.category.landingPageDetail.base',
                    gridColumns: [
                        {
                            property: 'name',
                            label: 'Name',
                            rawData: true,
                            sortable: true,
                            routerLink: 'sw.category.landingPageDetail.base',
                            allowEdit: false,
                        },
                    ],
                    deleteContext: {
                        type: 'many-to-many',
                        entity: 'landing_page',
                        column: 'extensions.swagDynamicAccessRules',
                    },
                    addContext: {
                        type: 'many-to-many',
                        entity: 'swag_dynamic_access_landing_page_rule',
                        searchColumn: 'name',
                        column: 'landingPageId',
                        criteria: () => {
                            const criteria = new Criteria();
                            criteria.addFilter(
                                Criteria.not('AND', [Criteria.equals('swagDynamicAccessRules.id', this.rule.id)]),
                            );

                            return criteria;
                        },
                        gridColumns: [
                            {
                                property: 'name',
                                label: 'Name',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                            {
                                property: 'url',
                                label: 'URL',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                            {
                                property: 'active',
                                label: 'Active',
                                rawData: true,
                                sortable: true,
                                allowEdit: false,
                            },
                        ],
                    },
                },
            ];
        },
    },
});
