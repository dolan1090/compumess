import type Repository from 'src/core/data/repository.data';
import template from './sw-subscription-detail.html.twig';
import swSubscriptionState from '../../../../state';
import type { TCriteria, ComponentHelper } from '../../../../type/types';
import type { SubscriptionState } from '../../../../state/subscription.store';

const { Criteria } = Shopware.Data;
const { mapState } = Shopware.Component.getComponentHelper() as ComponentHelper;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template: template,

    inject: ['repositoryFactory', 'acl'],

    data(): {
        isProcessLoading: boolean;
        isSaveSuccessful: boolean;
        } {
        return {
            isProcessLoading: false,
            isSaveSuccessful: false,
        };
    },

    beforeCreate(): void {
        Shopware.State.registerModule('swSubscription', swSubscriptionState.modules.subscription);
    },

    created() {
        this.createdComponent();
    },

    beforeDestroy(): void {
        Shopware.State.unregisterModule('swSubscription');
    },

    computed: {
        ...mapState<SubscriptionState>('swSubscription', [
            'subscription',
            'isLoading',
        ]),

        criteria(): TCriteria {
            const criteria = new Criteria(1, 1);

            criteria.addAssociation('subscriptionPlan');
            criteria.addAssociation('subscriptionInterval');
            criteria.addAssociation('subscriptionCustomer');
            criteria.addAssociation('stateMachineState');
            criteria.addAssociation('billingAddress.country');
            criteria.addAssociation('paymentMethod');
            criteria.addAssociation('shippingMethod');
            criteria.addAssociation('shippingAddress.country');
            criteria.addAssociation('salesChannel');
            criteria.addAssociation('language');
            criteria.addAssociation('currency');
            criteria.addAssociation('tags');

            return criteria;
        },

        repository(): Repository<'subscription'> {
            return this.repositoryFactory.create('subscription');
        },

        routeBaseName(): string {
            return this.$route.params.id ? 'sw.subscription.detail' : 'sw.subscription.create';
        },

        headlineTitle(): string {
            return this.$tc('commercial.subscriptions.subscriptions.detail.headlineTitle', 0, {
                number: this.subscription.subscriptionNumber,
            });
        },
    },

    methods: {
        createdComponent(): void {
            if (!this.$route.params.id) {
                throw new Error('Route parameter id is required.');
            }

            void this.loadSubscriptionById(this.$route.params.id);
        },

        async loadSubscriptionById(id: string): Promise<void> {
            Shopware.State.commit('swSubscription/setLoading', true);

            const subscription = await this.repository.get(id, Shopware.Context.api, this.criteria);
            Shopware.State.commit('swSubscription/setSubscription', subscription);

            Shopware.State.commit('swSubscription/setLoading', false);
        },

        onCancel(): void {
            void this.$router.push({ name: 'sw.subscription.index' });
        },

        async onSave(): Promise<void> {
            if (!this.acl.can('subscription.editor')) {
                return;
            }

            this.isProcessLoading = true;
            this.isSaveSuccessful = false;

            try {
                await this.repository.save(this.subscription);

                this.isSaveSuccessful = true;

                await this.loadSubscriptionById(this.$route.params.id);
            } catch (error: any) {
                Shopware.Utils.debug.warn(this._name, error.message, error.response);

                throw error;
            } finally {
                this.isProcessLoading = false;
            }
        },

        onChangeLanguage(languageId: string): void {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.createdComponent();
        },
    },
});
