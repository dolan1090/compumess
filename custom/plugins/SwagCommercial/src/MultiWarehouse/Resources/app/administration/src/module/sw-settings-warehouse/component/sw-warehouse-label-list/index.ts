/**
 * @package inventory
 */
import template from './sw-warehouse-label-list.html.twig';
import './sw-warehouse-label-list.scss';

Shopware.Component.register('sw-warehouse-label-list', {
    template,
    props: {
        items: {
            type: Array,
            required: true,
        },
        labelField: {
            type: String,
            required: true,
        },
        limit: {
            type: Number,
            default: 4,
        },
    },
    data() {
        return {
            expand: false
        }
    },
    computed: {
        visibleItems() {
            const { expand, items, limit } = this;

            if (expand) {
                return this.extractLabels(items);
            }

            return this.extractLabels(items.slice(0, limit));
        },
    },
    methods: {
        onClickExpand() {
            this.expand = true;
        },
        extractLabels(items) {
            const { labelField } = this;

            return items.map((label) => label[labelField]);
        }
    },
});
