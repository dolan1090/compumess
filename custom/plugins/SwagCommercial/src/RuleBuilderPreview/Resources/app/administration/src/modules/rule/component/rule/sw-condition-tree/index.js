import template from './sw-condition-tree.html.twig';

const { Component } = Shopware;

Component.override('sw-condition-tree', {
    template,

    provide() {
        return {
            getRulePreviewResults: this.getRulePreviewResults,
            getIsRulePreviewEnabled: this.getIsRulePreviewEnabled,
        };
    },

    data() {
        return {
            previewEnabled: false,
            previewResults: null,
        };
    },

    computed: {
        isLineItemScope() {
            return this.scopes !== null && this.scopes.length === 1 && this.scopes.includes('lineItem');
        },

        displayPreview() {
            return this.conditionRepository.entityName === 'rule_condition' && !this.isLineItemScope;
        },

        previewClasses() {
            return {
                'is--preview-enabled': this.getIsRulePreviewEnabled(),
            };
        },
    },

    methods: {
        onPreviewToggle(enabled) {
            this.previewEnabled = enabled;
        },

        onPreviewResults(results) {
            this.previewResults = results;
        },

        getRulePreviewResults() {
            return this.previewResults;
        },

        getIsRulePreviewEnabled() {
            return this.previewEnabled && this.previewResults !== null;
        },
    }
});
