import type Repository from 'src/core/data/repository.data';
import template from './sw-settings-subscription-plan-detail.html.twig';
import swSubscriptionState from '../../../../state';
import type { ComponentHelper } from '../../../../type/types';
import type { PlanState } from '../../../../state/plan.store';

const { Criteria } = Shopware.Data;
const { warn } = Shopware.Utils.debug;
const { mapState } = Shopware.Component.getComponentHelper() as ComponentHelper;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [
        Shopware.Mixin.getByName('placeholder'),
    ],

    data(): {
        isPlanLoading: boolean;
        isProcessLoading: boolean;
        isSaveSuccessful: boolean;
        } {
        return {
            isPlanLoading: false,
            isProcessLoading: false,
            isSaveSuccessful: false,
        };
    },

    computed: {
        ...mapState<PlanState>('swSubscriptionPlan', [
            'plan',
        ]),

        repository(): Repository<'subscription_plan'> {
            return this.repositoryFactory.create('subscription_plan');
        },

        intervalRepository(): Repository<'subscription_interval'> {
            return this.repositoryFactory.create('subscription_interval');
        },

        routeBaseName(): string {
            return this.$route.params.id ? 'sw.settings.subscription.planDetail' : 'sw.settings.subscription.planCreate';
        },
    },

    beforeCreate(): void {
        Shopware.State.registerModule('swSubscriptionPlan', swSubscriptionState.modules.plan);
    },

    created(): void {
        this.createdComponent();
    },

    beforeDestroy(): void {
        Shopware.State.unregisterModule('swSubscriptionPlan');
    },

    methods: {
        createdComponent(): void {
            if (this.$route.params.id) {
                void this.loadPlanById(this.$route.params.id);

                return;
            }

            this.createNewPlan();

            if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
                Shopware.State.commit('context/resetLanguageToDefault');
            }
        },

        createNewPlan(): void {
            const newPlan = this.repository.create();
            newPlan.active = false;

            Shopware.State.commit('swSubscriptionPlan/setPlan', newPlan);
        },

        async loadPlanById(id: string): Promise<void> {
            this.isPlanLoading = true;

            const criteria = new Criteria(1, 25);
            criteria.addAssociation('subscriptionIntervals');
            criteria.addAssociation('products');

            const context = { ...Shopware.Context.api, inheritance: true };

            const plan = await this.repository.get(id, context, criteria);
            Shopware.State.commit('swSubscriptionPlan/setPlan', plan);

            this.isPlanLoading = false;
        },

        onCancel(): void {
            void this.$router.push({ name: 'sw.settings.subscription.index.plans' });
        },

        async onSave(): Promise<void> {
            this.isProcessLoading = true;
            this.isSaveSuccessful = false;

            try {
                await this.repository.save(this.plan);

                this.isSaveSuccessful = true;

                if (!this.$route.params.id) {
                    await this.$router.replace({
                        name: 'sw.settings.subscription.planDetail',
                        params: { id: this.plan.id },
                    });
                }

                await this.loadPlanById(this.$route.params.id);
            } catch (error: any) {
                warn(this._name, error.message, error.response);
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
