import type Repository from 'src/core/data/repository.data';
import template from './sw-settings-subscription-plans.html.twig';
import './sw-settings-subscription-plans.scss';
import type { TCriteria, DataGridColumn, SortDirection, TEntityCollection } from '../../../../type/types';

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
        plans: TEntityCollection<'subscription_plan'> | null,
        isLoading: boolean,
        term: string,
        sortBy: string,
        sortDirection: SortDirection
        } {
        return {
            plans: null,
            isLoading: true,
            term: '',
            sortBy: 'name',
            sortDirection: 'ASC',
        };
    },

    computed: {
        planCriteria(): TCriteria {
            const criteria = new Criteria(1, 25);
            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            return criteria;
        },
        planRepository(): Repository<'subscription_plan'> {
            return this.repositoryFactory.create('subscription_plan');
        },
        planColumns(): DataGridColumn[] {
            return [{
                property: 'name',
                label: 'commercial.subscriptions.subscriptions.listing.columnName',
                allowResize: true,
            }, {
                property: 'active',
                label: 'commercial.subscriptions.subscriptions.listing.columnActive',
                width: '100px',
                allowResize: false,
            }, {
                property: 'description',
                label: 'commercial.subscriptions.subscriptions.listing.columnDescription',
                allowResize: true,
            }];
        },
    },

    created(): void {
        this.createdComponent();
    },

    destroyed(): void {
        this.destroyedComponent();
    },

    methods: {
        createdComponent(): void {
            this.$root.$on('on-change-application-language', this.loadPlans);
            void this.loadPlans();
        },
        destroyedComponent(): void {
            this.$root.$off('on-change-application-language', this.loadPlans);
        },
        async loadPlans(): Promise<void> {
            this.isLoading = true;

            this.$root.$emit('on-subscription-entities-loading');

            this.plans = await this.planRepository.search(this.planCriteria);

            this.$root.$emit('on-subscription-entities-loaded', this.plans.total);

            this.isLoading = false;
        },
        onTermChange(term: string): void {
            this.term = term;

            void this.loadPlans();
        },
        onDeleteFinish(): void {
            void this.loadPlans();
        }
    },
});
