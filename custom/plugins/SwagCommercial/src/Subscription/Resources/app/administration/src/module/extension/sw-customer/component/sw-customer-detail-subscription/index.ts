import type Repository from 'src/core/data/repository.data';
import template from './sw-customer-detail-subscription.html.twig';
import './sw-customer-detail-subscription.scss';
import type { TCriteria, SortDirection, TEntity, TEntityCollection, DataGridColumn } from '../../../../../type/types';

const { Criteria, EntityCollection } = Shopware.Data;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template: template,

    inject: ['repositoryFactory', 'stateStyleDataProviderService'],

    mixins: [
        Shopware.Mixin.getByName('listing'),
    ],

    props: {
        customer: {
            type: Object,
            required: true,

        },
    },

    data(): {
        subscriptions: TEntityCollection<'subscription'>,
        isLoading: boolean,
        subscriptionColumns: DataGridColumn[],
        limit: number,
        term: string,
        sortBy: string,
        sortDirection: SortDirection,
        toBeTerminatedSubscription: TEntity<'subscription'> | null,
        } {
        return {
            subscriptions: new EntityCollection<'subscription'>('', 'subscription', Shopware.Context.api),
            isLoading: true,
            limit: 25,
            sortBy: 'subscriptionNumber',
            sortDirection: 'DESC',
            subscriptionColumns: [{
                property: 'subscriptionNumber',
                label: 'commercial.subscriptions.subscriptions.listing.columnSubscriptionNumber',
                allowResize: false,
                align: 'left',
            }, {
                property: 'subscriptionPlan.name',
                label: 'commercial.subscriptions.subscriptions.listing.columnSubscriptionPlanName',
                allowResize: false,
            }, {
                property: 'nextSchedule',
                label: 'commercial.subscriptions.subscriptions.listing.columnNextSchedule',
                allowResize: false,
            }, {
                property: 'stateMachineState.name',
                label: 'commercial.subscriptions.subscriptions.listing.columnState',
                allowResize: false,
            }],
            term: '',
            toBeTerminatedSubscription: null,
        };
    },

    created() {
        this.createdComponent();

        Shopware.State.watch(state => state.context.api.languageId, (newValue, oldValue) => {
            if (newValue !== oldValue) {
                void this.getList();
            }
        });
    },

    computed: {
        subscriptionCriteria(): TCriteria {
            const criteria = new Criteria(null, this.limit);

            criteria.addFilter(Criteria.equals('subscriptionCustomer.customerId', this.customer.id));
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            criteria.addAssociation('subscriptionPlan');
            criteria.addAssociation('stateMachineState');
            criteria.addAssociation('subscriptionInterval');
            criteria.addAssociation('salesChannel');

            criteria.setTerm(this.term);

            return criteria;
        },

        subscriptionRepository(): Repository<'subscription'> {
            return this.repositoryFactory.create('subscription');
        },
    },

    methods: {
        createdComponent(): void {
            void this.getList();
        },

        async getList(): Promise<void> {
            this.isLoading = true;

            const criteria = this.subscriptionCriteria;

            return this.subscriptionRepository.search(criteria)
                .then((result: TEntityCollection<'subscription'>) => {
                    this.subscriptions = result;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        getVariantFromSubscriptionState(subscription: TEntity<'subscription'>): string {
            const style = this.stateStyleDataProviderService
                .getStyle('subscription.state', subscription.stateMachineState.technicalName);

            return style.colorCode;
        },

        onChangeTerm(term: string): void {
            this.term = term;

            void this.getList();
        },
    },
});
