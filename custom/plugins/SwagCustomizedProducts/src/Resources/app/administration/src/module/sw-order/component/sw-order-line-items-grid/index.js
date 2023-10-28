import template from './sw-order-line-items-grid.html.twig';
import './sw-order-line-items-grid.scss';

const { Component } = Shopware;
const { EntityCollection } = Shopware.Data;

Component.override('sw-order-line-items-grid', {
    template,

    inject: [
        'acl',
        'feature',
    ],

    data() {
        return {
            unfilteredOrderLineItems: [],
            isCustomProductModalOpen: false,
            currentProduct: null,
        };
    },

    computed: {
        orderLineItems() {
            const orderLineItems = this.$super('orderLineItems');
            return this.hydrateLineItems(orderLineItems);
        },

    },

    methods: {
        /**
         * Hydrates line items for the usage in the template. The method flattens the nested line items and registers
         * and filters them accordingly.
         *
         * @param {Array} originalLineItems
         * @returns {Array}
         */
        hydrateLineItems(originalLineItems) {
            const flattenLineItems = (accumulator, item) => {
                accumulator.push(item);

                if (item.children && item.children.length > 0) {
                    item.children.reduce(flattenLineItems, accumulator);
                }

                return accumulator;
            };

            const flattenedLineItems = originalLineItems.reduce(flattenLineItems, []);
            this.unfilteredOrderLineItems = this.createUnfilteredEntityCollection(flattenedLineItems);

            const filteredLineItems = this.filterLineItems(flattenedLineItems);
            filteredLineItems.forEach((item) => {
                if (!this.productHasCustomizedProduct(item) || item.type !== 'product') {
                    return;
                }

                item.parent = flattenedLineItems.find(element => element.id === item.parentId);
            });

            return filteredLineItems;
        },

        /**
         * Creates a new entity collection and fills it with the provided items.
         *
         * @param {Object[]} items
         * @returns {Object.EntityCollection}
         */
        createUnfilteredEntityCollection(items = []) {
            const collection = new EntityCollection(
                this.order.lineItems.source,
                this.order.lineItems.entity,
                this.order.lineItems.context,
                this.order.lineItems.criteria,
            );
            collection.push(...items);
            return collection;
        },

        /**
         * Filters line items based on their type
         *
         * @param orderLineItems
         * @returns {Object[]}
         */
        filterLineItems(orderLineItems) {
            return orderLineItems.filter((item) => {
                return !['customized-products', 'customized-products-option', 'option-values'].includes(item.type);
            });
        },

        /**
         * Checks if the provided item is a product which is assigned to a customized product container.
         * @param item
         * @returns {boolean|*}
         */
        productHasCustomizedProduct(item) {
            if (!item.parentId) {
                return false;
            }
            return this.order.lineItems.has(item.parentId);
        },

        /**
         * Event handler which opens a modal box to display the user configuration of the customized product.
         * @param {Object} item
         */
        onOpenCustomProductConfiguration(item) {
            if (!this.acl.can('swag_customized_products_template.viewer')) {
                return;
            }
            this.isCustomProductModalOpen = true;
            this.currentProduct = item;
        },

        /**
         * Closes the modal box which display the user configuration of the customized product.
         * @returns {void}
         */
        onCloseCustomProductConfigurationModal() {
            this.isCustomProductModalOpen = false;
            this.currentProduct = null;
        },

        onConfirmDelete() {
            let original = this.unfilteredOrderLineItems.get(this.showDeleteModal);

            if (original.parent !== undefined) {
                this.showDeleteModal = original.parentId;
            }

            this.orderLineItemRepository.delete(this.showDeleteModal, this.context).then(() => {
                this.$emit('item-delete');
            });

            this.showDeleteModal = false;
        },
    },
});
