import template from './swag-customized-products-tree-item-position.html.twig';

const { Component, Mixin } = Shopware;

Component.register('swag-customized-products-tree-item-position', {
    template,

    mixins: [
        Mixin.getByName('position'),
    ],

    props: {
        collection: {
            type: Array,
            required: true,
        },
        item: {
            type: Object,
            required: true,
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    computed: {
        itemMin() {
            return this.collection.every((entity) => this.item.position <= entity.position);
        },

        itemMax() {
            return this.collection.every((entity) => this.item.position >= entity.position);
        },
    },

    methods: {
        onLowerPositionValue() {
            this.lowerPositionValue(this.collection, this.item);
            this.$emit('lower-position-value', this.collection);
            this.$emit('position-changed', this.collection);
        },

        onRaisePositionValue() {
            this.raisePositionValue(this.collection, this.item);
            this.$emit('raise-position-value', this.collection);
            this.$emit('position-changed', this.collection);
        },
    },
});
