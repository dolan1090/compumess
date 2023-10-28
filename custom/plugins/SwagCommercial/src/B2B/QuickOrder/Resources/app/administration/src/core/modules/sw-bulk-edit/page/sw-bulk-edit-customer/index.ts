import type { SpecificFeature } from '../../../../../type/types';
import template from './sw-bulk-edit-customer.html.twig';

const { Component } = Shopware;
const { cloneDeep } = Shopware.Utils.object;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    inject: ['specificFeaturesApiService'],

    data(): {
        features: Array<SpecificFeature>,
        specificFeatureToggles: { [key: string]: boolean }
    } {
        return {
            features: [],
            specificFeatureToggles: {},
        };
    },

    computed: {
        specificFeaturesFormFields(): {
            name: string,
            type: string,
            config: {
                type: string,
                changeLabel: string,
                isSpecificFeature: boolean,
            },
        }[] {
            return this.features.map((feature: SpecificFeature) => {
                return {
                    name: feature.code,
                    type: 'bool',
                    config: {
                        type: 'switch',
                        changeLabel: feature.name,
                        isSpecificFeature: true,
                    },
                };
            });
        },
    },

    methods: {
        async createdComponent(): Promise<void> {
            await this.getSpecificFeatures();
            this.$super('createdComponent');
            this.loadBulkEditFeatures();

        },

        getSpecificFeatures(): Promise<void> {
            return this.specificFeaturesApiService.getSpecificFeatures()
                .then(res => {
                    this.features = res.data.filter((feature: SpecificFeature) => feature.enabled);
                })
        },

        defineBulkEditData(name: string, value: any = null, type: string = 'overwrite', isChanged: boolean = false) {
            this.$super('defineBulkEditData', name, value, type, isChanged);
        },

        loadBulkEditFeatures(): void {
            this.specificFeaturesFormFields.forEach((bulkEditForm) => {
                this.defineBulkEditData(bulkEditForm.name);
            });

            this.defineBulkEditData('specificFeatures')
        },

        onProgressSpecificFeatureData(): void {
            const specificFeatureCodes: Array<string> = [];
            this.features.forEach((feature: SpecificFeature) => {
                specificFeatureCodes.push(feature.code);
            });

            this.specificFeatureToggles = {};
            Object.keys(this.bulkEditData).forEach(key => {
                const bulkEditField = cloneDeep(this.bulkEditData[key]);
                if (!specificFeatureCodes.includes(key) || !bulkEditField.isChanged) {
                    return;
                }

                let bulkEditValue = this.customer[key];

                if (!bulkEditValue) {
                    bulkEditValue = false;
                }

                this.specificFeatureToggles[key] = bulkEditValue;
            });
        },

        async onSave(): Promise<void> {
            this.onProgressSpecificFeatureData();

            return this.$super('onSave');
        },

        onProcessData() {
            const data = this.$super('onProcessData');

            const change = {
                field: 'specificFeatures',
                type: 'overwrite',
                value: { features: this.specificFeatureToggles },
            }

            data.syncData.push(change);

            return data;
        }
    }
})
