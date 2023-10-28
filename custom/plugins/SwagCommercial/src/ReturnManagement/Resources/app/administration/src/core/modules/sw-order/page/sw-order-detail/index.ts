import template from './sw-order-detail.html.twig';
import type CriteriaType from '@administration/core/data/criteria.data';
import { TOGGLE_KEY } from '../../../../../config';

const { Component } = Shopware;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    computed: {
        hasOrderReturn(): boolean {
            return this.order?.extensions?.returns?.length > 0;
        },

        orderCriteria(): CriteriaType {
            const criteria = this.$super('orderCriteria');

            if (this.acl.can('order_return.viewer')
                && Shopware.License.get(TOGGLE_KEY)) {
                criteria.addAssociation('returns.createdBy')
                    .addAssociation('returns.updatedBy')
                    .addAssociation('returns.state')
                    .addAssociation('lineItems.state')
                    .addAssociation('lineItems.returns');
            }

            return criteria;
        }
    },

    methods: {
        reloadEntityData(): Promise<void> {
            const oldValue = this.order?.extensions?.returns;

            return this.$super('reloadEntityData').then(() => {
                const newValue = this.order?.extensions?.returns;

                // Move to Return tab after creating an order return
                if (oldValue?.length < newValue?.length) {
                    this.$router.push({
                        name: 'swag.return.management.order.detail.returns',
                        id: this.order.id
                    });
                }
            });
        },

        onCancelEditing(): void {
            return this.$super('onCancelEditing').then(() => {
                this.$root.$emit('order-edit-cancel');
            });
        },
    },
});
