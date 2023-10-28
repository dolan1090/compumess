import template from './sw-customer-detail-base.html.twig';
import type {SpecificFeature} from '../../../../../type/types';

const { Component } = Shopware;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template: template,

    inject: ['specificFeaturesApiService'],

    data(): {
        features: SpecificFeature[],
    } {
        return {
            features: [],
        };
    },

    computed: {
        showFeatureCard() {
            return !this.customer.guest;
        },
    },

    methods: {
        createdComponent(): void {
            this.$super('createdComponent');
            this.getSpecificFeatures();
        },

        getSpecificFeatures(): Promise<void> {
            return this.specificFeaturesApiService.getSpecificFeatures()
                .then(res => {
                    this.features = res.data.filter((feature: SpecificFeature) => feature.enabled);
                })
        },
    }
})
