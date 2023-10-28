import type Repository from 'src/core/data/repository.data';
import template from './sw-settings-subscription-plan-products-modal.html.twig';
import type { TCriteria, TEntityCollection, ComponentHelper, DataGridColumn, SortDirection } from '../../../../type/types';
import type { PlanState } from '../../../../state/plan.store';

const { Criteria } = Shopware.Data;
const { mapState } = Shopware.Component.getComponentHelper() as ComponentHelper;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    data(): {
        products: TEntityCollection<'product'> | [],
        selectedProducts: TEntityCollection<'product'> | [],
        term: string,
        sortBy: string,
        sortDirection: SortDirection,
        isLoading: boolean,
        page: number,
        limit: number,
        total: number,
        } {
        return {
            products: [],
            selectedProducts: [],
            term: '',
            sortBy: 'name',
            sortDirection: 'ASC',
            isLoading: false,
            page: 1,
            limit: 25,
            total: 0,
        };
    },

    computed: {
        ...mapState<PlanState>('swSubscriptionPlan', ['plan']),

        productRepository(): Repository<'product'> {
            return this.repositoryFactory.create('product');
        },

        productCriteria(): TCriteria {
            const productCriteria = new Criteria(this.page, this.limit);

            productCriteria.setTerm(this.term);
            productCriteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            productCriteria.addSorting(Criteria.sort('productNumber', 'ASC'));
            productCriteria.addAssociation('options.group');


            productCriteria.addFilter(
                Criteria.not('and', [
                    Criteria.equals('product.subscriptionPlans.id', this.plan.id),
                ]),
            );

            return productCriteria;
        },

        productColumns(): DataGridColumn[] {
            return [
                {
                    property: 'name',
                    label: 'commercial.subscriptions.subscriptions.listing.columnProductName',
                    allowResize: true,
                    primary: true,
                    routerLink: 'sw.product.detail',
                },
                {
                    property: 'productNumber',
                    label: 'commercial.subscriptions.subscriptions.listing.columnProductNumber',
                    allowResize: true,
                },
            ];
        },

        productsCount(): number {
            return Object.keys(this.selectedProducts).length;
        },
    },

    created(): void {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            void this.loadProducts();
        },

        async loadProducts(): Promise<void> {
            this.isLoading = true;

            this.products = await this.productRepository.search(this.productCriteria, { ...Shopware.Context.api, inheritance: true });

            this.isLoading = false;
        },

        onChangeTerm(term: string): void {
            this.term = term;

            if (term) {
                this.page = 1;
            }

            void this.loadProducts();
        },

        onCancel(): void {
            this.$emit('close');
        },

        onSave(): void {
            this.$emit('save', this.selectedProducts);
        },

        onSelectionChange(selectedProducts: TEntityCollection<'product'>): void {
            this.selectedProducts = selectedProducts;
        },
    },
});
