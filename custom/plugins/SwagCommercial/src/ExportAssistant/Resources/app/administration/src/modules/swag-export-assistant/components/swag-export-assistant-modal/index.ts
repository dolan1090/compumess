/**
 * @package system-settings
 */
import type CriteriaType from '@shopware-ag/admin-extension-sdk/es/data/Criteria';
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';
import COLUMN_MAPPING from './column-mapping.constant';
import template from './swag-export-assistant-modal.html';
import './swag-export-assistant-modal.scss';

const { Criteria } = Shopware.Data;

export default {
    template,

    inject: [
        'repositoryFactory',
        'importExport',
        'criteriaGeneratorService',
    ],

    mixins: [
        Shopware.Mixin.getByName('notification'),
    ],

    props: {
        searchTerm: {
            type: String as unknown as string,
            required: true,
        },
    },

    data(): {
        isLoading: boolean,
        entities: EntityCollection<'unknown'> | null,
        columns: [],
        entity: string | null,
        profileId: string | null,
        criteria: CriteriaType | null,
        } {
        return {
            isLoading: false,
            entities: null,
            columns: [],
            entity: null,
            profileId: null,
            criteria: null,
        };
    },

    computed: {
        profileRepository() {
            return this.repositoryFactory.create('import_export_profile');
        },

        profileCriteria() {
            const criteria = new Criteria();

            criteria.setLimit(1);
            criteria.addFilter(Criteria.equals('systemDefault', true));
            criteria.addFilter(Criteria.equals('sourceEntity', this.entity));

            return criteria;
        },

        modalTitle() {
            const itemsFound = this.isLoading
                ? ''
                : this.$tc('swag-export-assistant.modal.foundItem', this.entities && this.entities.length, {
                    total: this.entities?.total,
                });

            return this.$tc('swag-export-assistant.default.preview') + itemsFound;
        },

        emptySubline() {
            return `"${this.searchTerm}"`;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            void this.getExportData();
        },

        async getExportData() {
            this.isLoading = true;

            try {
                this.criteria = new Criteria();

                const { entity } = await this.criteriaGeneratorService.generate({
                    prompt: this.searchTerm,
                    criteria: this.criteria,
                });

                const previewCriteria = Shopware.Utils.object.cloneDeep(this.criteria);
                previewCriteria.setLimit(25);

                const repository = this.repositoryFactory.create(entity);
                this.entity = entity;

                const [searchResult, profiles]: any = await Promise.all([
                    repository.search(previewCriteria),
                    this.profileRepository.search(this.profileCriteria),
                ]).catch((error) => {
                    if (error?.response?.status === 400) {
                        throw new Error(this.$tc('global.swag-search-assistant.messageMissingEntityOrCriteria'));
                    }

                    if (
                        error.code === 'ERR_BAD_REQUEST' &&
                        error.response?.data?.errors[0]?.code === 'FRAMEWORK__UNMAPPED_FIELD'
                    ) {
                        throw new Error(`Assistant: ${error.response?.data?.errors[0]?.detail}`);
                    }

                    this.turnOffModalPreview();

                    this.createNotificationError({
                        message: `Assistant: ${error.response?.data?.errors[0]?.detail}` ?? error.message,
                    });
                });

                this.entities = searchResult;
                this.columns = COLUMN_MAPPING[entity];
                this.profileId = profiles.first() ? profiles.first().id : null;
            } catch (error) {
                this.turnOffModalPreview();

                this.createNotificationError({
                    message: error.message,
                });
            } finally {
                this.isLoading = false;
            }
        },

        turnOffModalPreview(): void {
            this.$emit('turn-off-modal-preview');
        },

        async onStartExport() {
            await this.importExport.export(this.profileId, this.handleProgress, {
                parameters: {
                    criteria: this.criteria.parse(),
                },
            }).catch((error) => {
                this.isLoading = false;

                if (!error.response || !error.response.data || !error.response.data.errors) {
                    this.createNotificationError({
                        message: error.message,
                    });

                    return;
                }

                error.response.data.errors.forEach((singleError) => {
                    this.createNotificationError({
                        message: `${singleError.code}: ${singleError.detail}`,
                    });
                });
            });

            this.turnOffModalPreview();
            await this.$nextTick();

            this.createNotificationSuccess({
                message: this.$tc('swag-export-assistant.base.messageExportDone'),
            });
        },

        handleProgress(log: any) {
            this.createNotificationInfo({
                message: this.$tc('sw-import-export.exporter.messageExportStarted'),
            });

            this.isLoading = false;
            this.$emit('export-started', log);
        },
    },
};
