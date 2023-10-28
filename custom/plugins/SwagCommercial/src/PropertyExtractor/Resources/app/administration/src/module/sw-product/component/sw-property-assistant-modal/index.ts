import template from "./sw-property-assistant-modal.html.twig";
import "./sw-property-assistant-modal.scss";
import type Repository from 'src/core/data/repository.data';
import type EntityCollectionType from 'src/core/data/entity-collection.data';
import { GridColumn } from "../../../../../../../../../ReturnManagement/Resources/app/administration/src/type/types";

const { Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;
const { mapState } = Shopware.Component.getComponentHelper();

/**
 * @package business-ops
 */
export default {
    template,

    inject: [
        'propertyExtractorService',
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            isLoading: false,
            isSaving: false,
            hasChanged: false,
            showPropertyTable: false,
            descriptionLimit: 4000,
            description: '',
            searchTerm: null,
            properties: [],
            page: 1,
            limit: 10,
            disableRouteParams: true,
            guessed: [],
            newOptions: [],
            disableGenerate: false,
            infoSnippet: null,
        };
    },

    computed: {
        ...mapState('swProductDetail', [
            'apiContext',
            'product',
        ]),

        filteredProperties(): object[] {
            if (!this.searchTerm) {
                return this.properties;
            }

            return this.properties.filter(propertyGroup => {
                const foundMatchInGroup = (propertyGroup.translated?.name || propertyGroup.name).toLowerCase()
                    .includes(this.searchTerm.toLowerCase());

                const foundMatchInOptions = propertyGroup.options.some(option => {
                    return (option.translated?.name || option.name).toLowerCase()
                        .includes(this.searchTerm.toLowerCase());
                });

                return foundMatchInGroup || foundMatchInOptions;
            });
        },

        generateButtonDisabled(): boolean {
            return this.description.length < 200 || this.description.length > this.descriptionLimit || this.disableGenerate;
        },

        generateButtonHelpText(): string {
            if (this.description.length < 200) {
                return this.$tc('property-extractor.assistant-modal.help-text.to-short');
            }

            if (this.description.length > this.descriptionLimit) {
                return this.$tc('property-extractor.assistant-modal.help-text.to-long');
            }

            return '';
        },

        generateButtonLabel(): string {
            return this.properties.length < 1 ?
                this.$tc('property-extractor.assistant-modal.generate-button.default') :
                this.$tc('property-extractor.assistant-modal.generate-button.new')
        },

        generateButtonTooltip(): object {
            return {
                message: this.$tc('property-extractor.assistant-modal.generate-button.tooltip'),
                disabled: !this.disableGenerate,
            };
        },

        showResetDescriptionButton(): boolean {
          return this.hasChanged && this.description !== this.sanitizedProductDescription;
        },

        paginatedProperties(): object[] {
            return this.filteredProperties.slice(
                (this.page - 1) * this.limit,
                this.page * this.limit,
            );
        },

        propertyGroupRepository(): Repository {
            return this.repositoryFactory.create('property_group');
        },

        propertyGroupOptionRepository(): Repository {
            return this.repositoryFactory.create('property_group_option');
        },

        sanitizedProductDescription(): string {
            const div = document.createElement('div');
            div.innerHTML = this.product.translated?.description || this.product.description;

            return div.textContent;
        },
    },

    mounted() {
        this.setProductDescription();
    },

    methods: {
        getList() {
            return;
        },

        setProductDescription() {
            this.description = this.sanitizedProductDescription;
            this.disableGenerate = false;
            this.infoSnippet = null;
        },

        async onGenerate() {
            this.showPropertyTable = true;
            this.isLoading = true;

            try {
                this.guessed = await this.propertyExtractorService.generate(
                    this.description,
                    {
                        'sw-language-id': this.apiContext.languageId,
                    }
                );

                if (typeof this.guessed === 'object' && Object.keys(this.guessed).length) {
                    this.properties = await this.searchGroups(this.guessed);
                } else {
                    this.properties = [];
                    this.showPropertyTable = false;
                    this.infoSnippet = 'property-extractor.assistant-modal.no-result';
                    return;
                }

                if (!this.properties.length) {
                    this.showPropertyTable = false;
                    this.infoSnippet = 'property-extractor.assistant-modal.no-suggestions';
                }

                this.disableGenerate = true;
            } catch (err) {
                this.showPropertyTable = false;
                this.createNotificationError({
                    message: this.$tc('property-extractor.assistant-modal.table.error-message')
                });
            } finally {
                this.isLoading = false;
            }
        },

        async onSave() {
            this.isSaving = true;

            try {
                await this.propertyGroupRepository.saveAll(this.properties, this.apiContext).then(async () => {
                   this.newOptions = await this.searchPropertyOptions();
                });
            } catch(err) {
                this.createNotificationError({
                    message: this.$tc('property-extractor.assistant-modal.table.error-message')
                });
            } finally {
                this.$emit('modal-save', this.newOptions);
                this.isSaving = false;
            }
        },

        onCancel() {
            this.$emit('close-property-assistant-modal');
        },

        onDismiss(itemIndex, propertyIndex) {
            const property = this.properties.at(itemIndex);
            const option = property.options.at(propertyIndex);

            // prevent deleting existing property options by removing them from the changeset origin
            const originIndex = property.getOrigin().options.findIndex(original => original.id === option.id);

            if (originIndex !== -1) {
                property.getOrigin().options.splice(originIndex, 1);
            }

            property.options.splice(propertyIndex, 1);

            if (property.options.length === 0) {
                this.properties.splice(itemIndex, 1);
            }

            if (this.properties.length === 0) {
                this.showPropertyTable = false;
            }
        },

        onDelete(index) {
            this.properties.splice(index, 1);
            if (this.properties.length === 0) {
                this.showPropertyTable = false;
            }
        },

        onChange() {
            this.hasChanged = true;
            this.disableGenerate = false;
            this.infoSnippet = null;
        },

        getAssistantColumns(): GridColumn[] {
            return [{
                property: 'name',
                label: 'property-extractor.assistant-modal.table.name-label',
                allowResize: false,
                width: '120px',
                inlineEdit: 'string',
            }, {
                property: 'options',
                label: 'property-extractor.assistant-modal.table.options-label',
                allowResize: false,
                inlineEdit: true,
            }];
        },

        async searchGroups(guessedProperties: Record<string, string[]>): EntityCollectionType {
            const existingGroups = await this.searchPropertyGroups(guessedProperties);
            const groupCollection = new EntityCollection(
                this.propertyGroupRepository.route,
                this.propertyGroupRepository.entityName,
                this.apiContext,
            );

            // iterate guessed property group name and values
            Object.entries(guessedProperties).forEach(([propertyName, propertyValues]: [string, string[]]) => {
                propertyValues = Object.values(propertyValues);

                // find existing group by name
                const existingGroupIndex = existingGroups
                    .findIndex(group => (group.translated?.name || group.name).toLowerCase() === propertyName.toLowerCase());

                // if no group with identical name exists create new group with all new options
                if (existingGroupIndex === -1) {
                    const newGroup = this.propertyGroupRepository.create(this.apiContext);
                    newGroup.name = propertyName;

                    // iterate guessed property options and add new option for new group
                    propertyValues.forEach((optionName) => {
                        const option = this.propertyGroupOptionRepository.create(this.apiContext);
                        option.name = optionName;
                        newGroup.options.add(option)
                    });

                    groupCollection.add(newGroup);

                    return;
                }

                const existingGroup = existingGroups.at(existingGroupIndex);
                const optionCollection = new EntityCollection(
                    this.propertyGroupOptionRepository.route,
                    this.propertyGroupOptionRepository.entityName,
                    this.apiContext,
                );

                // iterate guessed property options for existing group
                propertyValues.forEach((optionName) => {
                    // find existing option by name
                    const existingOptionIndex = existingGroup.options
                        .findIndex(option => (option.translated?.name || option.name).toLowerCase() === optionName.toLowerCase());

                    // if no option with identical name is found in the existing group create and add new option
                    if (existingOptionIndex === -1) {
                        const option = this.propertyGroupOptionRepository.create(this.apiContext);
                        option.name = optionName;
                        optionCollection.add(option);

                        return;
                    }

                    const existingOption = existingGroup.options.at(existingOptionIndex);

                    // if the existing option is already assigned to the product skip iteration
                    if (this.product.properties.map(option => option.id).includes(existingOption.id)) {
                        return;
                    }

                    optionCollection.add(existingOption);
                });

                // if all guessed options already exist and are also assigned to the product don't suggest new properties
                if (optionCollection.length === 0) {
                    return;
                }

                existingGroup.options = optionCollection;
                groupCollection.add(existingGroup);
            });

            return groupCollection;
        },

        async searchPropertyGroups(guessedProperties: Record<string, string[]>): EntityCollectionType {
            const criteria = new Criteria();
            criteria.addAssociation('options');
            criteria.addFilter(Criteria.equalsAny('name', Object.keys(guessedProperties)));

            criteria.getAssociation('options')
                .addFilter(Criteria.equalsAny('name', [].concat(...Object.values(guessedProperties))));

            return await this.propertyGroupRepository.search(criteria, this.apiContext);
        },

        async searchPropertyOptions(): EntityCollectionType {
            const criteria = new Criteria();
            const ids = this.properties.reduce((acc, property) => {
                return [
                    ...acc,
                    ...property.options.map(option => option.id).filter(id => id)
                ]
            }, []);
            criteria.setIds(ids);

            if (ids.length === 0) {
                return;
            }

            return await this.propertyGroupOptionRepository.search(criteria, this.apiContext);
        },

        onPageChange({ page, limit }) {
            this.page = page;
            this.limit = limit;
        },
    }
};
