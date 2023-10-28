import template from './sw-rule-builder-preview-indicator.html';
import './sw-rule-builder-preview-indicator.scss';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Component } = Shopware;

/**
 * @private
 */
Component.register('sw-rule-builder-preview-indicator', {
    template,

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    inject: [
        'getRulePreviewResults',
        'getIsRulePreviewEnabled',
    ],

    props: {
        conditionName: {
            type: String,
            required: true,
        },
        conditionId: {
            type: String,
            required: false,
            default: null,
        },
        size: {
            type: String,
            required: false,
            default: 'small',
            validValues: ['small', 'medium'],
            validator(value) {
                return ['small', 'medium'].includes(value);
            },
        },
    },

    computed: {
        previewResults() {
            return this.getRulePreviewResults();
        },

        previewEnabled() {
            return this.getIsRulePreviewEnabled();
        },

        ruleMatch() {
            if (!this.previewResults) {
                return null;
            }

            const filteredRuleResult = this.previewResults.filter((result) => {
                return result.name === this.conditionName && result.ruleReferenceId === this.conditionId;
            });

            if (!filteredRuleResult.hasOwnProperty(0)) {
                return null;
            }

            return filteredRuleResult[0].match;
        },

        hasValidationError() {
            return Shopware.State.getters['error/getErrorsForEntity']('rule_condition', this.conditionId) !== null;
        },

        indicatorClasses() {
            return {
                [`sw-rule-builder-preview-indicator--size-${this.size}`]: true,
                'has--error': this.hasValidationError,
            };
        },
    },
});
