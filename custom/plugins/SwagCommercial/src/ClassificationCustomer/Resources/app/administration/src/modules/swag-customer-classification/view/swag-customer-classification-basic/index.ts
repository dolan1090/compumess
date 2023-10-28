/**
 * @package checkout
 */

import type { PropType } from 'vue';
import template from './swag-customer-classification-basic.html';
import './swag-customer-classification-basic.scss';

const { Component, Mixin } = Shopware;

interface TagData {
    id: string,
    name: string,
    description: string,
    ruleBuilder: string,
}

interface TagColumn {
    property: string,
    label: string,
    primary?: boolean,
    multiLine?: boolean,
    width?: string,
    inlineEdit?: boolean,
}

interface TagSelection {
    [key:string]: TagData
}

export default Component.wrapComponentConfig({
    template,

    inject: [
        'customerClassifyApiService',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        tagData: {
            type: Array as PropType<Array<TagData>>,
            required: true,
        },
    },

    data(): {
        isGenerateLoading: boolean,
        isGenerateSuccess: boolean,
        tagColumns: TagColumn[],
        additionalInfo: string,
        selectedTag: TagData,
        selectedTags: TagData[],
        numberOfTags: number,
    } {
        return {
            isGenerateLoading: false,
            isGenerateSuccess: false,
            tagColumns: [
                {
                    property: 'name',
                    primary: true,
                    label: this.$tc('swag-customer-classification.labelGrid.columnTag'),
                    inlineEdit: true,
                    multiLine: true,
                    width: '20%',
                },
                {
                    property: 'description',
                    label: this.$tc('swag-customer-classification.labelGrid.columnDescription'),
                    multiLine: true,
                    inlineEdit: true,
                    width: '40%',
                },
                {
                    property: 'ruleBuilder',
                    label: this.$tc('swag-customer-classification.labelGrid.columnCondition'),
                    inlineEdit: true,
                    multiLine: true,
                    width: '40%',
                },
            ],
            additionalInfo: this.$tc('swag-customer-classification.placeholderAdditionalInfo'),
            selectedTag: null,
            selectedTags: [],
            numberOfTags: 3,
        };
    },

    computed: {
        hasTagData(): boolean {
            return this.tagData?.length > 0;
        },

        noSelectedTags(): boolean {
            return this.selectedTags.length === 0;
        }
    },

    methods: {
        onTagSelectionChange(value: TagSelection): void {
            this.selectedTags = Object.values(value);
            this.$emit('tag-select', Object.values(value));
        },

        onRemoveTag(item: TagData): void {
            const tagData = this.tagData.filter(tag => tag.id !== item.id);
            this.$emit('tag-update', tagData);
        },

        onEditTag(item: TagData): void {
            this.selectedTag = {...item};
        },

        generateTags(): Promise<void> {
            this.isGenerateLoading = true;

            return this.customerClassifyApiService.generateTags(
                this.additionalInfo,
                this.numberOfTags,
            ).then((response) => {
                const tagData = response?.data?.classifications?.map(item => {
                    return {
                        id: Shopware.Utils.createId(),
                        ...item,
                    };
                });

                this.$emit('tag-update', tagData);
                this.isGenerateSuccess = true;
            }).catch(error => {
                const message = error?.response?.data?.errors[0]?.detail;

                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message,
                });
            }).finally(() => {
                this.isGenerateLoading = false;
            });
        },

        generateFinish(): void {
            this.isGenerateSuccess = false;
        },

        onCloseEditTagModal(): void {
            this.selectedTag = null;
        },

        onSaveTag(item: TagData): void {
            const tagIndex = this.tagData.findIndex(tag => tag.id == item.id);
            this.$set(this.tagData, tagIndex, item);
            this.$emit('tag-update', this.tagData);
            this.onCloseEditTagModal();
        },

        onStartClick(): void {
            this.$emit('start-classify');
        }
    }
});
