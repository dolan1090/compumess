import template from './sw-subscription-detail-details.html.twig';
import './sw-subscription-detail-details.scss';
import type { ComponentHelper } from '../../../../type/types';
import type { SubscriptionState } from '../../../../state/subscription.store';

const { mapState, mapPropertyErrors } = Shopware.Component.getComponentHelper() as ComponentHelper;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: ['acl'],

    computed: {
        ...mapState<SubscriptionState>('swSubscription', [
            'subscription',
        ]),

        ...mapPropertyErrors('subscription', [
            'nextSchedule',
        ]),

        billingAddress(): string {
            const billingAddress = this.subscription.billingAddress;
            return `${billingAddress.street}, ${billingAddress.zipcode} ${billingAddress.city}`;
        },

        paymentMethod(): string {
            return this.subscription.paymentMethod.name;
        },

        shippingAddress(): string {
            const shippingAddress = this.subscription.shippingAddress;
            return `${shippingAddress.street}, ${shippingAddress.zipcode} ${shippingAddress.city}`;
        },

        shippingMethod(): string {
            return this.subscription.shippingMethod.name;
        },

        shippingCosts(): number {
            return this.subscription.convertedOrder.deliveries?.[0]?.shippingCosts?.totalPrice || 0.00;
        },

        currencySymbol(): string {
            return this.subscription.currency.symbol;
        },

        customerEmail(): string {
            return this.subscription.subscriptionCustomer.email;
        },

        customerPhoneNumber(): string {
            return this.subscription.billingAddress?.phoneNumber || '';
        },

        salesChannel(): string {
            return this.subscription.salesChannel.name;
        },

        datepickerConfig(): { [key: string]: any } {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);

            let minDate = tomorrow;
            let disableDate = {};

            const nextScheduleDate = new Date(this.subscription.nextSchedule);

            if (nextScheduleDate < new Date(minDate)) {
                minDate = this.subscription.nextSchedule;
                disableDate = {
                    from: nextScheduleDate.toISOString(),
                    to: new Date().toISOString(),
                };
            }

            return {
                minDate,
                disable: [disableDate],
            };
        },

        orderLanguage(): string {
            return this.subscription.language.name;
        },

        numberOfDeliveries(): number {
            return this.subscription.initialExecutionCount - this.subscription.remainingExecutionCount;
        },
    },
});
