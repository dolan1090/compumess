import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type RepositoryType from '@administration/core/data/repository.data';
import template from './swag-b2b-features-customer-specific-features.html.twig';
import { SpecificFeature } from '../../../../type/types';
import './swag-b2b-features-customer-specific-features.scss';

const { Component } = Shopware;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template: template,

    inject: [
        'repositoryFactory'
    ],

    computed: {
        specificFeaturesRepository(): RepositoryType<'customer_specific_features'> {
            return this.repositoryFactory.create('customer_specific_features');
        },
    },

    props: {
        customer: {
            type: Object as PropType<Entity<'customer'>>,
            required: true,
        },

        customerEditMode: {
            type: Boolean,
            required: true,
            default: false,
        },

        features: {
            type: Array as PropType<SpecificFeature>,
            required: true,
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            this.customer.extensions.specificFeatures = this.customer?.extensions?.specificFeatures ?? this.specificFeaturesRepository.create();
            this.customer.extensions.specificFeatures.features = this.customer.extensions.specificFeatures.features ?? {};
        }
    }
})
