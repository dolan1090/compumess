import template from './sw-rule-builder-preview.html';
import './sw-rule-builder-preview.scss';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;
const utils = Shopware.Utils;

/**
 * @private
 */
Component.register('sw-rule-builder-preview', {
    template,

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    inject: [
        'ruleBuilderPreviewService',
    ],

    props: {
        conditionTree: {
            type: Object,
            required: true,
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            orderId: null,
            dateTime: null,
            isLoading: false,
            previewModeActive: false,
            requiredFields: [
                'id',
                'type',
                'value',
                'children',
                'scriptId',
            ],
            timeRules: [
                'dayOfWeek',
                'timeRange',
                'dateRange',
                'scriptRule',
            ],
            debouncedResults: utils.debounce(() => {
                this.getResults();
            }, 500, { leading: true }),
        };
    },

    watch: {
        previewModeActive() {
            this.getResults();
        },

        orderId() {
            this.getResults();
        },

        dateTime() {
            this.getResults();
        },

        conditionTree: {
            deep: true,
            handler() {
                this.debouncedResults();
            },
        },

        previewEnabled(enabled) {
            this.$emit('preview-enabled', enabled);
        },
    },

    computed: {
        previewEnabled() {
            return this.previewModeActive && this.orderId;
        },

        orderCriteria() {
            const criteria = new Criteria();

            criteria.addSorting(Criteria.sort('createdAt', 'DESC', false));

            return criteria;
        },

        hasTimeCondition() {
            return this.hasConditionOfType(this.conditionTree, this.timeRules);
        },

        ruleBuilderPreviewClasses() {
            return {
                'is--preview-mode-active': this.previewModeActive,
            }
        }
    },

    methods: {
        getLicense(toggle) {
            return Shopware.License.get(toggle);
        },

        getResults() {
            if (this.getLicense('RULE_BUILDER-8759907')) {
                const initContainer = Shopware.Application.getContainer('init');
                initContainer.httpClient.get(
                    'api/_info/config',
                    {
                        headers: {
                            Accept: 'application/vnd.api+json',
                            Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                            'Content-Type': 'application/json',
                            'sw-license-toggle': 'RULE_BUILDER-8759907',
                        },
                    },
                );
                return;
            }

            if (!this.previewEnabled) {
                this.$emit('preview-results', null);
                return;
            }

            const rootCondition = this.getConditionParameters(this.conditionTree);

            this.isLoading = true;

            return this.ruleBuilderPreviewService.preview(
                this.orderId,
                [rootCondition],
                this.dateTime
            ).then((results) => {
                this.$emit('preview-results', results);
                this.isLoading = false;
            }).catch((errorResponse) => {
                this.$emit('preview-results', false);
                this.isLoading = false;

                const errors = errorResponse?.response?.data?.errors;
                if (!errors) {
                    return;
                }

                Object.values(errors).forEach((error) => {
                    const segments = error.source.pointer.split('/');

                    if (segments[0] === '') {
                        segments.shift();
                    }

                    Shopware.State.dispatch('error/addApiError', {
                        expression: segments.join('.'),
                        error: new Shopware.Classes.ShopwareError(error),
                    });
                });
            });
        },

        getConditionParameters(conditionEntity) {
            const condition = {};

            this.requiredFields.forEach((fieldName) => {
                if (fieldName !== 'children') {
                    condition[fieldName] = conditionEntity[fieldName];

                    return;
                }

                condition[fieldName] = [];

                conditionEntity[fieldName].forEach((child) => {
                    condition[fieldName].push(this.getConditionParameters(child));
                });
            });

            return condition;
        },

        hasConditionOfType(conditionEntity, types) {
            if (types.includes(conditionEntity.type)) {
                return true;
            }

            let hasType = false;

            conditionEntity.children.forEach((child) => {
                if (hasType) {
                    return;
                }

                hasType = this.hasConditionOfType(child, types);
            })

            return hasType;
        },
    },
});
