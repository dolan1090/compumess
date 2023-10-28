const { Component } = Shopware;

Component.override('sw-condition-type-select', {
    inject: ['getIsRulePreviewEnabled'],

    computed: {
        previewEnabled() {
            return this.getIsRulePreviewEnabled();
        },

        arrowColor() {
            if (!this.disabled && this.previewEnabled) {
                return {
                    primary: '#758CA3',
                    secondary: '#ffffff',
                };
            }

            return this.$super('arrowColor');
        }
    },

    methods: {
        changeType(type) {
            if (type) {
                const errorPath = `${this.condition.getEntityName()}.${this.condition.id}.type`;
                this.$store.commit('error/removeApiError', { expression: errorPath });
            }

            this.$super('changeType', type);
        }
    }
});
