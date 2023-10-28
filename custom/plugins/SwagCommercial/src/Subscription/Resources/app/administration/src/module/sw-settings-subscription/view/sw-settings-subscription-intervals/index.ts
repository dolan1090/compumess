import template from './sw-settings-subscription-intervals.html.twig';
import './sw-settings-subscription-intervals.scss';
import type Repository from 'src/core/data/repository.data';
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
        intervals: TEntityCollection<'subscription_interval'> | null,
        isLoading: boolean,
        term: string,
        sortBy: string,
        sortDirection: SortDirection
        } {
        return {
            intervals: null,
            isLoading: true,
            term: '',
            sortBy: 'name',
            sortDirection: 'ASC',
        };
    },

    computed: {
        intervalCriteria(): TCriteria {
            const criteria = new Criteria(1, 25);
            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            return criteria;
        },
        intervalRepository(): Repository<'subscription_interval'> {
            return this.repositoryFactory.create('subscription_interval');
        },
        intervalColumns(): DataGridColumn[] {
            return [{
                property: 'name',
                label: 'commercial.subscriptions.subscriptions.listing.columnName',
                allowResize: true,
            }, {
                property: 'active',
                label: 'commercial.subscriptions.subscriptions.listing.columnActive',
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
            this.$root.$on('on-change-application-language', this.loadIntervals);
            void this.loadIntervals();
        },
        destroyedComponent(): void {
            this.$root.$off('on-change-application-language', this.loadIntervals);
        },
        async loadIntervals(): Promise<void> {
            this.isLoading = true;

            this.$root.$emit('on-subscription-entities-loading');

            this.intervals = await this.intervalRepository.search(this.intervalCriteria);

            this.$root.$emit('on-subscription-entities-loaded', this.intervals.total);

            this.isLoading = false;
        },
        onTermChange(term: string): void {
            this.term = term;

            void this.loadIntervals();
        },
        onDeleteFinish(): void {
            void this.loadIntervals();
        }
    },
});
