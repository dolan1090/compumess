import type Repository from 'src/core/data/repository.data';
import template from './sw-settings-subscription-plan-products.html.twig';
import type { TCriteria, TEntityCollection, TEntity, ComponentHelper, DataGridColumn } from '../../../../type/types';
import type { PlanState } from '../../../../state/plan.store';
import {SortDirection} from "../../../../type/types";

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

    props: {
        isPlanLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data(): {
        products: TEntityCollection<'product'> | null,
        isLoading: boolean,
        page: number,
        limit: number,
        total: number,
        term: string,
        sortBy: string,
        sortDirection: SortDirection,
        showProductsModal: boolean;
        } {
        return {
            products: null,
            isLoading: true,
            term: '',
            page: 1,
            limit: 25,
            total: 0,
            showProductsModal: false,
            sortBy: 'name',
            sortDirection: 'ASC',
        };
    },

    computed: {
        ...mapState<PlanState>('swSubscriptionPlan', [
            'plan',
        ]),

        productRepository(): Repository<'product'> {
            return this.repositoryFactory.create('product');
        },

        planRepository(): Repository<'subscription_plan'> {
            return this.repositoryFactory.create('subscription_plan');
        },

        productCriteria(): TCriteria {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTotalCountMode(1);

            criteria.addAssociation('subscriptionPlans');
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addSorting(Criteria.sort('productNumber', 'ASC'));
            criteria.addFilter(
                Criteria.equals('product.subscriptionPlans.id', this.plan.id),
            );

            if (this.term) {
                criteria.setTerm(this.term);
            }

            return criteria;
        },

        productColumns(): DataGridColumn[] {
            return [
                {
                    property: 'name',
                    label: 'commercial.subscriptions.subscriptions.listing.columnProductName',
                    allowResize: true,
                },
                {
                    property: 'productNumber',
                    label: 'commercial.subscriptions.subscriptions.listing.columnProductNumber',
                    allowResize: true,
                },
            ];
        },
    },

    created(): void {
        this.createdComponent();
    },

    watch: {
        plan(): void {
            this.loadProducts();
        },
    },

    methods: {
        createdComponent(): void {
            this.loadProducts();
        },

        loadProducts(): void {
            if (!this.plan?.id) return;

            const context = { ...Shopware.Context.api, inheritance: true };

            this.isLoading = true;

            this.productRepository.search(this.productCriteria, context)
                .then((products: TEntityCollection<'product'>) => {
                    this.products = products;
                    this.total = products.total ?? products.length;

                    if (this.total > 0 && this.products.length <= 0) {
                        this.page = (this.page === 1) ? 1 : this.page - 1;
                        this.loadProducts();
                    }
                })
                .catch(() => this.products = null)
                .finally(() => this.isLoading = false);
        },

        onChangeTerm(term: string): void {
            this.term = term;

            if (term) {
                this.page = 1;
            }

            this.loadProducts();
        },

        async onProductsModalSave(products: TEntity<'product'>[]): Promise<void> {
            try {
                const plan = await this.getEditablePlan(false);

                Object.values(products).forEach(product => plan.products.add(product));

                await this.planRepository.save(plan);

                this.closeProductsModal();
            } finally {
                this.loadProducts();
            }
        },

        onRemoveProduct(productId: string): void {
            const product = this.products.get(productId);

            product.extensions.subscriptionPlans?.remove(this.plan.id);

            this.isLoading = true;
            this.productRepository.save(product)
                .then(() => this.loadProducts())
                .finally(() => this.isLoading = false);
        },

        async getEditablePlan(includeSelection: boolean): TEntity<'subscription_plan'> {
            const context = { ...Shopware.Context.api, inheritance: true };

            const criteria = new Criteria();
            criteria.addAssociation('products');
            if (includeSelection) {
                criteria.addFilter(
                    // @ts-expect-error - $refs are not type safe
                    Criteria.equalsAny('products.id', Object.keys(this.$refs.entityListing.selection)),
                );
            }

            return await this.planRepository.get(this.plan.id, context, criteria);
        },

        async onRemoveProducts(): Promise<void> {
            this.isLoading = true;

            try {
                const plan = await this.getEditablePlan(true);

                // @ts-expect-error - $refs are not type safe
                Object.keys(this.$refs.entityListing.selection)
                    .forEach(productId => plan.products.remove(productId));

                await this.planRepository.save(plan);

                // @ts-expect-error - $refs are not type safe
                this.$refs.entityListing.resetSelection();

                this.loadProducts();
            } finally {
                this.isLoading = false;
            }
        },

        onChangePage(data: { page: number, limit: number}): void {
            this.page = data.page;
            this.limit = data.limit;

            this.loadProducts();
        },

        openProductsModal(): void {
            this.showProductsModal = true;
        },

        closeProductsModal(): void {
            this.showProductsModal = false;
        },
    },
});
