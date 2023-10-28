import type Repository from 'src/core/data/repository.data';
import template from './sw-subscription-detail-orders.html.twig';
import type { TCriteria, TEntityCollection, ComponentHelper, DataGridColumn } from '../../../../type/types';
import type { SubscriptionState } from '../../../../state/subscription.store';

const { mapState } = Shopware.Component.getComponentHelper() as ComponentHelper;
const { Criteria } = Shopware.Data;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: ['repositoryFactory', 'acl'],

    data(): {
        orders: TEntityCollection<'order'> | null,
        isLoading: boolean,
        page: number,
        limit: number,
        total: number,
        term: string,
        sortBy: string,
        sortDirection: string
        } {
        return {
            orders: null,
            isLoading: false,
            page: 1,
            limit: 25,
            total: 0,
            term: '',
            sortBy: 'name',
            sortDirection: 'ASC',
        };
    },

    computed: {
        ...mapState<SubscriptionState>('swSubscription', [
            'subscription',
        ]),

        orderRepository(): Repository<'order'> {
            return this.repositoryFactory.create('order');
        },

        orderCriteria(): TCriteria {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTotalCountMode(1);

            criteria.addAssociation('subscription');
            criteria.addAssociation('currency');
            criteria.addFilter(
                Criteria.equals('order.subscription.id', this.subscription.id),
            );

            if (this.term) {
                criteria.setTerm(this.term);
            }

            return criteria;
        },

        orderColumns(): DataGridColumn[] {
            return [
                {
                    property: 'orderNumber',
                    label: 'sw-customer.detailOrder.columnNumber',
                },
                {
                    property: 'amountTotal',
                    label: 'sw-customer.detailOrder.columnAmount',
                    align: 'right',
                },
                {
                    property: 'stateMachineState.name',
                    label: 'sw-customer.detailOrder.columnOrderState',
                },
                {
                    property: 'orderDateTime',
                    label: 'sw-customer.detailOrder.columnOrderDate',
                },
            ];
        },
    },

    created(): void {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            this.loadOrders();
        },

        loadOrders(): void {
            if (!this.subscription?.id) return;

            this.isLoading = true;

            this.orderRepository.search(this.orderCriteria)
                .then((orders: TEntityCollection<'order'>) => {
                    this.orders = orders;
                    this.total = orders.total ?? orders.length;

                    if (this.total > 0 && this.orders.length <= 0) {
                        this.page = (this.page === 1) ? 1 : this.page - 1;
                        this.loadProducts();
                    }
                })
                .catch(() => {
                    this.orders = null;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        onChangeTerm(term: string): void {
            this.term = term;

            if (term) {
                this.page = 1;
            }

            this.loadOrders();
        },

        onChangePage(data: { page: number, limit: number }): void {
            this.page = data.page;
            this.limit = data.limit;

            this.loadOrders();
        },
    },
});
