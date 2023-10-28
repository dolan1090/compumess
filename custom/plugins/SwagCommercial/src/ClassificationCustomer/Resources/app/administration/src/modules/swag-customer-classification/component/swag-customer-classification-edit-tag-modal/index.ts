/**
 * @package checkout
 */

import type { PropType } from 'vue';
import template from './swag-customer-classification-edit-tag-modal.html';

const { Component } = Shopware;

interface TagData {
    id: string,
    name: string,
    description: string,
    ruleBuilder: string,
}
export default Component.wrapComponentConfig({
    template,

    props: {
        item: {
            type: Object as PropType<TagData>,
        }
    },

    methods: {
        onCancel(): void {
            this.$emit('modal-close');
        },

        onApply(): void {
            this.$emit('modal-apply', this.item);
        }
    }
});
