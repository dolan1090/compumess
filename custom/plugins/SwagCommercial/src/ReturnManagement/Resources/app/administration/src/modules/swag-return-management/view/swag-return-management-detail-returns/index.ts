import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import template from './swag-return-management-detail-returns.html';

const { Component, State } = Shopware;
const { mapState } = Component.getComponentHelper();

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    inject: ['repositoryFactory'],

    computed: {
        ...mapState('swOrderDetail', [
            'order',
        ]),

        orderReturns(): Entity<'order_return'>[] {
            return this.order?.extensions?.returns || [];
        },
    },

    watch: {
        orderReturns(newValue: Array<Entity<'order_return'>>) {
            if (!newValue.length) {
                this.redirectToGeneralTab();
            }
        }
    },

    created(): void {
        if (!this.orderReturns.length) {
            this.redirectToGeneralTab();
        }
    },

    methods: {
        redirectToGeneralTab(): void {
            this.$router.push({ name: 'sw.order.detail.general', params: { id: this.$route.params.id } });
        },

        reloadOrder(): void {
            this.$emit('save-and-reload');
        },

        saveOrder(): void {
            this.$emit('save-edits');
        }
    },
});
