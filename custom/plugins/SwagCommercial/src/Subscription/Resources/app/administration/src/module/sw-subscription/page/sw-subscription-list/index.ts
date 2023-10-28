import type Repository from 'src/core/data/repository.data';
import template from './sw-subscription-list.html.twig';
import type { TCriteria, DataGridColumn, TEntity, TEntityCollection } from '../../../../type/types';

const { Criteria } = Shopware.Data;

type SortDirectionOptions = 'ASC' | 'DESC';

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template: template,

    inject: [
        'repositoryFactory',
        'stateStyleDataProviderService',
        'acl',
        'subscriptionApiService',
    ],

    mixins: [
        Shopware.Mixin.getByName('listing'),
        Shopware.Mixin.getByName('notification'),
    ],

    data(): {
        subscriptions: TEntityCollection<'subscription'> | null,
        isLoading: boolean,
        subscriptionColumns: DataGridColumn[],
        limit: number,
        term: string,
        sortBy: string,
        sortDirection: SortDirectionOptions,
        toBeTerminatedSubscription: TEntity<'subscription'> | null,
        } {
        return {
            subscriptions: null,
            isLoading: true,
            limit: 25,
            sortBy: 'subscriptionNumber',
            sortDirection: 'DESC',
            subscriptionColumns: [{
                property: 'subscriptionNumber',
                label: 'commercial.subscriptions.subscriptions.listing.columnSubscriptionNumber',
                allowResize: true,
            }, {
                property: 'salesChannel.name',
                label: 'commercial.subscriptions.subscriptions.listing.columnSalesChannelName',
                allowResize: true,
            }, {
                property: 'subscriptionCustomer.firstName',
                dataIndex: 'subscriptionCustomer.lastName,subscriptionCustomer.firstName',
                label: 'commercial.subscriptions.subscriptions.listing.columnSubscriptionCustomerName',
                allowResize: true,
            }, {
                property: 'subscriptionPlan.name',
                label: 'commercial.subscriptions.subscriptions.listing.columnSubscriptionPlanName',
                allowResize: true,
            }, {
                property: 'subscriptionInterval.name',
                label: 'commercial.subscriptions.subscriptions.listing.columnSubscriptionInterval',
                allowResize: true,
            }, {
                property: 'nextSchedule',
                label: 'commercial.subscriptions.subscriptions.listing.columnNextSchedule',
                allowResize: false,
            }, {
                property: 'stateMachineState.name',
                label: 'commercial.subscriptions.subscriptions.listing.columnState',
                allowResize: false,
            }, {
                property: 'createdAt',
                label: 'commercial.subscriptions.subscriptions.listing.columnCreatedAt',
                allowResize: true,
            }],
            term: '',
            toBeTerminatedSubscription: null,
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        subscriptionCriteria(): TCriteria {
            const criteria = new Criteria(this.page, this.limit);

            criteria.addAssociation('subscriptionPlan');
            criteria.addAssociation('stateMachineState');
            criteria.addAssociation('subscriptionInterval');
            criteria.addAssociation('subscriptionCustomer');
            criteria.addAssociation('salesChannel');

            criteria.setTerm(this.term);

            this.sortBy.split(',').forEach(sortBy => {
                criteria.addSorting(Criteria.sort(sortBy, this.sortDirection));
            });

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

            let criteria = this.subscriptionCriteria;
            criteria = await this.addQueryScores(this.term, criteria);

            try {
                this.subscriptions = await this.subscriptionRepository.search(criteria);
            } catch {
                this.subscriptions = null;
            } finally {
                this.isLoading = false;
            }
        },

        getVariantFromSubscriptionState(subscription: TEntity<'subscription'>): string {
            const style = this.stateStyleDataProviderService
                .getStyle('subscription.state', subscription.stateMachineState.technicalName);

            return style.colorCode;
        },

        onChangeLanguage(): void {
            void this.getList();
        },

        onTerminate(subscription: TEntity<'subscription'>): void {
            this.toBeTerminatedSubscription = subscription;
        },

        onCloseTerminateModal(): void {
            this.toBeTerminatedSubscription = null;
        },

        onConfirmTerminate(subscription: TEntity<'subscription'>): void {
            this.subscriptionApiService
                .subscriptionStateTransition(subscription.id, 'cancel')
                .then(() => void this.getList())
                .catch((e) => this.createNotificationError({
                    message: this.$tc('commercial.subscriptions.subscriptions.detail.general.stateErrorStateChange', 0, { message: e.message }),
                }))
                .finally(() => this.toBeTerminatedSubscription = null);
        },
    },
});
