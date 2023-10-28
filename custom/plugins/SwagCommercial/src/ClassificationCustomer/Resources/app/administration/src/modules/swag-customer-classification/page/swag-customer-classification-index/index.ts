/**
 * @package checkout
 */

import type RepositoryType from '@administration/core/data/repository.data';

import template from './swag-customer-classification-index.html';
import './swag-customer-classification-index.scss';

const { Component, Mixin } = Shopware;
const { EntityCollection } = Shopware.Data;

interface TagData {
    id: string,
    name: string,
    description: string,
    ruleBuilder: string,
}

export default Component.wrapComponentConfig({
    template,

    inject: [
        'acl',
        'systemConfigApiService',
        'repositoryFactory',
        'customerClassifyApiService'
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data(): {
        isLoading: boolean,
        tagData: TagData[],
        selectedTags: TagData[],
        formatResponse: string,
        processStatus: string,
        hasOldTags: boolean,
        chunkSize: number,
    } {
        return {
            isLoading: false,
            tagData: [],
            selectedTags: [],
            formatResponse: '{"[group.id]": ["customer.customer_number(s)"],...}',
            processStatus: '',
            hasOldTags: false,
            chunkSize: 50,
        };
    },

    computed: {
        tagRepository(): RepositoryType<'tag'> {
            return this.repositoryFactory.create('tag');
        },

        customerIds(): string[] {
            return Shopware.State.get('shopwareApps').selectedIds;
        },

        totalCustomer(): number {
            return this.customerIds.length ?? 0;
        }
    },

    created(): void {
        this.createdComponent();
    },

    methods: {
        async createdComponent(): Promise<void> {
            this.tagData = await this.getSystemConfigLabels() ?? [];
            this.hasOldTags = this.tagData?.length > 0;
        },

        onStartClick(): void {
            this.$router.push({ name: 'swag.customer.classification.index.save' });
        },

        async startClassification(): void {
            this.isLoading = true;

            try {
                if (this.hasOldTags) {
                    await this.removeOldTags();
                }

                const newTagData = await this.saveTagsToDatabase();
                await this.saveTagsToSystemConfig(newTagData);

                for (let index = 0; index < this.totalCustomer; index += this.chunkSize) {
                    const chunk = this.customerIds.slice(index, index + this.chunkSize);
                    await this.customerClassifyApiService
                        .classify(
                            this.selectedTags,
                            chunk,
                            this.formatResponse,
                        );
                }

                this.processStatus = 'success';
            } catch {
                this.processStatus = 'error';
            } finally {
                this.isLoading = false
            }
        },

        async saveTagsToDatabase(): Promise<void> {
            const tags = [];
            const newTagData = [...this.tagData];

            this.tagData.forEach((data, index) => {
                const tag = this.tagRepository.create();
                tag.id = data.id;
                tag.name = data.name;
                tags.push(tag);
                newTagData[index].id = tag.id;
            });

            const tagCollection = new EntityCollection(
                this.tagRepository.source,
                this.tagRepository.entityName,
                Shopware.Context.api,
                null,
                tags,
            );

            return this.tagRepository.saveAll(tagCollection).then(() => {
                return Promise.resolve(newTagData);
            });
        },

        async removeOldTags(): Promise<void> {
            const response = await this.getSystemConfigLabels();
            const tagIds = response.map(item => item.id);

            return this.tagRepository.syncDeleted(tagIds);
        },

        saveTagsToSystemConfig(tagData: TagData[]): Promise<void> {
            return this.systemConfigApiService.saveValues({
                ['core.customer.classification.labels']: tagData
            });
        },

        updateSelectTags(selection: TagData[]): void {
            this.selectedTags = selection;
        },

        updateTagList(tagData: TagData[]): void {
            this.tagData = tagData;
        },

        async getSystemConfigLabels(): Promise<void> {
            try {
                const response = await this.systemConfigApiService.getValues('core.customer');
                const labels = response['core.customer.classification.labels'];
                return Promise.resolve(labels);
            } catch(error) {
                const message = error?.response?.data?.errors[0]?.detail;

                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message,
                });
            }
        },

        onCloseModal(): void {
            this.$router.push({ name: 'swag.customer.classification.index' });
        },
    }
});
