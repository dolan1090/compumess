import template from './sw-customer-base-info.html.twig';

const { Component, Mixin } = Shopware;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    mixins: [
        Mixin.getByName('notification'),
    ],

    data(): {
        turnover: number|null,
    } {
        return {
            turnover: null,
        };
    },
    methods: {
        getCustomerTurnover(): Promise<void> {
            return Shopware.Service('customerApiService')
                .getTurnover(this.customer.id)
                .then((resp) => {
                    this.turnover = resp.data
                })
                .catch(error => {
                    const errorDetailMsg = error?.response?.data?.errors[0]?.detail;
                    this.createNotificationError({
                        message: errorDetailMsg,
                    });
                }
            )
        },

        createdComponent(): void {
            this.$super('createdComponent');
            this.getCustomerTurnover();
        },
    },
});
