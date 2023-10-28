/**
 * @package buyers-experience
 */
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type RepositoryType from '@administration/src/core/data/repository.data';
import type CriteriaType from '@administration/src/core/data/criteria.data';
import licenseDecoratorHelper from '../../../../core/helper/license-decorator.helper';
import { LICENSE_TRAP_PRIMARY as TRAP_PRIMARY } from '../../../../constants';
import template from './swag-advanced-search-boosting-modal.html';

const { Criteria } = Shopware.Data;
const { ShopwareError } = Shopware.Classes;

type BoostingModalData = {
    isLoading: boolean,
    boosting: Entity<'advanced_search_boosting'>,
    entityStream: Entity<'advanced_search_entity_stream'>,
    entityStreamFilters: Entity<'advanced_search_entity_stream_filter'>,
    entityStreamFiltersTree: [],
    entityStreamFiltersToDelete: [],
}

export default {
    template,

    inject: [
        'repositoryFactory',
        'entityStreamConditionService',
    ],

    mixins: [
        Shopware.Mixin.getByName('placeholder'),
        Shopware.Mixin.getByName('notification'),
    ],

    props: {
        boostingId: {
            type: String,
            require: false,
            default: null,
        },
    },

    data(): BoostingModalData {
        return {
            isLoading: false,
            boosting: null,
            entityStream: null,
            // @ts-expect-error The initial properties can be ignored
            entityStreamFilters: [],
            entityStreamFiltersTree: [],
            entityStreamFiltersToDelete: [],
        };
    },

    computed: {
        boostingRepository(): RepositoryType<'advanced_search_boosting'> {
            return licenseDecoratorHelper(this.repositoryFactory.create('advanced_search_boosting'), TRAP_PRIMARY);
        },

        entityStreamRepository(): RepositoryType<'advanced_search_entity_stream'> {
            return licenseDecoratorHelper(this.repositoryFactory.create('advanced_search_entity_stream'), TRAP_PRIMARY);
        },

        entityStreamFiltersRepository(): RepositoryType<'advanced_search_entity_stream_filter'> {
            return this.repositoryFactory.create(this.entityStream.filters.entity, this.entityStream.filters.source);
        },

        entityStreamFiltersCriteria(): CriteriaType {
            const criteria = new Criteria(1, null);
            criteria.addFilter(Criteria.equals('entityStreamId', this.entityStream.id));

            return criteria;
        },

        advancedSearchConfigId(): string {
            return Shopware.State.getters['swAdvancedSearchState/advancedSearchConfigId'];
        },

        boostingBoostError() {
            if (this.boosting.boost !== null && this.boosting.boost <= 0) {
                return new ShopwareError({ code: 'ADVANCED_SEARCH_BOOSTING_BOOST_INVALID' });
            }

            return null;
        },

        boostingValidToError() {
            if (this.boosting.validFrom !== null && (this.boosting.validFrom > this.boosting.validTo)) {
                return new ShopwareError({ code: 'ADVANCED_SEARCH_BOOSTING_VALID_TO_INVALID' });
            }

            return null;
        },

        isDisabled(): boolean {
            return this.isLoading
                || !this.boosting.name
                || !this.boosting.boost
                || !this.boosting.type
                || (this.boosting.type === 'product_stream' && !this.boosting.productStreamId)
                || (this.boosting.type === 'entity_stream' && !this.entityStream?.type)
                || Boolean(this.boostingBoostError)
                || Boolean(this.boostingValidToError);
        },
    },

    watch: {
        'boosting.type'(value: string | null) {
            if (value === 'product_stream') {
                this.boosting.entityStreamId = null;

                if (this.entityStream) {
                    this.entityStream.type = null;
                }
            }

            if (value === 'entity_stream') {
                this.boosting.productStreamId = null;
            }
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent(): Promise<void> {
            await this.getBoosting().finally(() => {
                this.updateBoosting();
            });

            await this.getEntityStream();

            this.getEntityStreamFilters();
        },

        async getBoosting() {
            this.isLoading = true;

            if (!this.boostingId) {
                const entity = this.boostingRepository.create();

                this.boosting = Object.assign(entity, {
                    configId: this.advancedSearchConfigId,
                    name: null,
                    boost: null,
                    active: false,
                    validFrom: null,
                    validTo: null,
                    productStreamId: null,
                    entityStreamId: null,
                });

                this.isLoading = false;

                return;
            }

            try {
                const response = await this.boostingRepository.get(this.boostingId);
                this.boosting = response;
            } catch (error) {
                this.boosting = null;
                this.createNotificationError({
                    message: error.message,
                });
            } finally {
                this.isLoading = false;
            }
        },

        updateBoosting(): void {
            if (!this.boosting) {
                return;
            }

            this.boosting.type = null;

            if (this.boosting.productStreamId) {
                this.boosting.type = 'product_stream';
            }

            if (this.boosting.entityStreamId) {
                this.boosting.type = 'entity_stream';
            }
        },

        async getEntityStream() {
            this.isLoading = true;

            if (!this.boosting) {
                this.isLoading = false;
                this.entityStream = null;

                return;
            }

            if (!this.boosting.entityStreamId) {
                const entity = this.entityStreamRepository.create();

                this.entityStream = Object.assign(entity, {
                    type: null,
                });

                this.isLoading = false;

                return;
            }

            await this.fetchEntityStream(this.boosting.entityStreamId);
        },

        async fetchEntityStream(entityStreamId: string): Promise<void> {
            this.isLoading = true;

            try {
                const response = await this.entityStreamRepository.get(entityStreamId);
                this.entityStream = response;
            } catch (error) {
                this.entityStream = null;
                this.createNotificationError({
                    message: error.message,
                });
            } finally {
                this.isLoading = false;
            }
        },

        async getEntityStreamFilters(collection = null): Promise<void> {
            if (!this.entityStream?.filters) {
                return Promise.resolve();
            }

            if (collection === null) {
                const response = await this.entityStreamFiltersRepository.search(this.entityStreamFiltersCriteria);

                return this.getEntityStreamFilters(response);
            }

            if (collection.length >= collection.total) {
                this.entityStreamFilters = collection;
                return Promise.resolve();
            }

            const nextCriteria = Criteria.fromCriteria(collection.criteria);
            nextCriteria.page += 1;

            const response = await this.entityStreamFiltersRepository.search(nextCriteria, collection.context);
            collection.push(...response);
            collection.criteria = response.criteria;
            collection.total = response.total;

            return this.getEntityStreamFilters(collection);
        },

        onChangeConditions({ conditions, deletedIds }): void {
            this.entityStreamFiltersTree = conditions;
            this.entityStreamFiltersToDelete = this.entityStreamFiltersToDelete.concat(deletedIds);
        },

        onCancel(): void {
            this.$emit('modal-cancel');
        },

        async onSave(): Promise<void> {
            this.isLoading = true;

            try {
                if (this.boosting.type === 'entity_stream') {
                    this.boosting.entityStreamId = this.entityStream.id;
                    await this.saveEntityStream();
                }

                delete this.boosting.type;

                await this.boostingRepository.save(this.boosting);
                this.createNotificationSuccess({
                    message: this.$tc('swag-advanced-search.boostingTab.messageBoostingSaved'),
                });
                this.$emit('modal-save-finish');
            } catch (error) {
                this.createNotificationError({
                    message: error.message,
                });
            } finally {
                this.isLoading = false;
            }
        },

        async saveEntityStream(): Promise<void> {
            this.isLoading = true;

            if (this.entityStream.isNew()) {
                this.entityStream.filters = this.entityStreamFiltersTree;
                await this.entityStreamRepository.save(this.entityStream);
                this.isLoading = false;

                return;
            }

            try {
                await this.entityStreamRepository.save(this.entityStream);
                await this.syncEntityStreamFilters();
                this.fetchEntityStream(this.entityStream.id);
            } catch (error) {
                this.createNotificationError({
                    message: error.message,
                });
            } finally {
                this.isLoading = false;
            }
        },

        async syncEntityStreamFilters(): Promise<void> {
            try {
                await this.entityStreamFiltersRepository.sync(this.entityStreamFiltersTree);

                if (this.entityStreamFiltersToDelete.length > 0) {
                    await this.entityStreamFiltersRepository.syncDeleted(this.entityStreamFiltersToDelete);
                    this.entityStreamFiltersToDelete = [];
                }
            } catch (error) {
                this.createNotificationError({
                    message: error.message,
                });
            }
        },
    },
};
