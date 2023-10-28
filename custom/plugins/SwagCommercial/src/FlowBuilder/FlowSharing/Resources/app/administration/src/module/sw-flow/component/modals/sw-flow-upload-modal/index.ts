import './sw-flow-upload-modal.scss';
import type Repository from 'src/core/data/repository.data';
import template from './sw-flow-upload-modal.html';
import type EntityCollectionType from 'src/core/data/entity-collection.data';
import type {
    RuleEntity,
    Rule,
    MailTemplateEntity,
    MailTemplate,
    Requirement,
    FlowData,
    Condition,
    RuleConflictOption
} from '../../../flow.types';

const { Criteria, EntityCollection } = Shopware.Data;
const { State, Context, Service } = Shopware;
const { fileReader } = Shopware.Utils;

/**
 * @package business-ops
 */
export default {
    template,

    inject: [
        'acl',
        'repositoryFactory',
        'flowSharingService',
        'ruleConditionsConfigApiService',
    ],

    props: {
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data(): {
        disableUpload: boolean,
        report: {},
        jsonFile: Blob,
        flowData: FlowData,
        rules: Array<Rule>,
        mailTemplates: Array<MailTemplate>,
        notSelectedRuleIds: Array<string>,
        notSelectedMailIds: Array<string>,
        requiredSWVersion: string,
        requiredPlugins: Array<string>,
        requiredApps: Array<string>,
        mailTemplateTypes?: EntityCollectionType<'mail_template_type'>,
        ruleConflict: boolean,
        affectedRules: Array<RuleEntity>,
        keepLocalRules: boolean,
        hasError: boolean,
    } {
        return {
            disableUpload: false,
            report: {},
            jsonFile: null,
            flowData: null,
            rules: [],
            mailTemplates: [],
            notSelectedRuleIds: [],
            notSelectedMailIds: [],
            requiredSWVersion: null,
            requiredPlugins: [],
            requiredApps: [],
            mailTemplateTypes: null,
            ruleConflict: false,
            affectedRules: [],
            keepLocalRules: true,
            hasError: false,
        };
    },

    computed: {
        ruleRepository(): Repository {
            return this.repositoryFactory.create('rule');
        },

        ruleConditionRepository(): Repository {
            return this.repositoryFactory.create('rule_condition');
        },

        mailTemplateRepository(): Repository {
            return this.repositoryFactory.create('mail_template');
        },

        mailTemplateTypeRepository(): Repository {
            return this.repositoryFactory.create('mail_template_type');
        },

        showWarning(): boolean {
            return this.requiredSWVersion || this.requiredPlugins.length || this.requiredApps.length;
        },

        resolveRulesConflictOptions(): Array<RuleConflictOption> {
            return [
                {
                    value: true,
                    name: this.$tc('sw-flow-sharing.uploadModal.keepLocalRulesLabel'),
                    description: this.$tc('sw-flow-sharing.uploadModal.keepLocalRulesDescription'),
                },
                {
                    value: false,
                    name: this.$tc('sw-flow-sharing.uploadModal.overrideLocalRulesLabel'),
                    description: this.$tc('sw-flow-sharing.uploadModal.overrideLocalRulesDescription', 'flowFile', { flowFile: this.jsonFile.name }),
                },
            ];
        },
    },

    watch: {
        jsonFile(value: Blob) {
            this.resetData();
            if (!value) {
                this.disableUpload = true;
                return;
            }

            this.disableUpload = false;
        },

        flowData(value: FlowData) {
            if (value.requirements) {
                this.validateRequirements(value.requirements);
            }
        },

        report(value: Requirement): void {
            if (value.shopwareVersion) {
                this.requiredSWVersion = value.shopwareVersion;
            }

            if (value.pluginInstalled) {
                this.requiredPlugins = value.pluginInstalled;
            }

            if (value.appInstalled) {
                this.requiredApps = value.appInstalled;
            }
        },
    },

    created(): void {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            this.ruleConditionsConfigApiService.load();

            this.resetData();
            if (!this.jsonFile) {
                this.disableUpload = true;
            }
        },

        resetData(): void {
            this.rules = [];
            this.mailTemplates = [];
            this.notSelectedRuleIds = [];
            this.notSelectedMailIds = [];
            this.report = {};
            this.requiredSWVersion = null;
            this.requiredPlugins = [];
            this.requiredApps = [];
            this.ruleConflict = false;
            this.affectedRules = [];
            this.keepLocalRules = true;
            State.dispatch('swFlowSharingState/resetFlowSharingState');
        },

        onCancel(): void {
            this.$emit('modal-close');
        },

        getMailTemplateCollection(): Promise<EntityCollectionType<'mail_template_type'>> {
            return this.mailTemplateTypeRepository.search(new Criteria(1, 25));
        },

        async saveEmailTemplates(): Promise<void> {
            const mailTemplates = [];

            this.mailTemplates.map(mailTemplate => {
                if (this.notSelectedMailIds.includes(mailTemplate.id)) {
                    return;
                }

                mailTemplate = this.createMailTemplate(mailTemplate);

                if (mailTemplate) {
                    mailTemplates.push(mailTemplate);
                }
            });

            await this.mailTemplateRepository.saveAll(mailTemplates);
        },

        async saveRules(): Promise<void> {
            const rules = [];

            this.rules.map((rule: Rule) => {
                if (this.notSelectedRuleIds.includes(rule.id)) {
                    return;
                }

                rules.push(this.createRule(rule));
            });

            await this.ruleRepository.saveAll(rules);
        },

        async onUpload(): Promise<void> {
            if (this.affectedRules.length > 0 && !this.ruleConflict) {
                this.ruleConflict = true;
                return;
            }

            if (this.hasError || this.flowData.length <= 0) {
                this.$emit('modal-upload-finished', false);
                return;
            }

            this.mailTemplateTypes = await this.getMailTemplateCollection();

            const dataIncluded = Object.assign({}, this.flowData.dataIncluded);

            if (this.ruleConflict && !this.keepLocalRules) {
                this.rules.push(...Object.values(this.affectedRules));
            }

            await Promise.all([
                this.saveEmailTemplates(),
                this.saveRules()
            ]);

            const referenceIncluded = Object.assign({}, this.flowData.referenceIncluded);

            State.commit('swFlowSharingState/setFlow', this.flowData.flow);
            State.commit('swFlowSharingState/setDataIncluded', dataIncluded);
            State.commit('swFlowSharingState/setReferenceIncluded', referenceIncluded);

            this.$emit('modal-upload-finished', true);
        },

        onFileChange(): Promise<void> {
            if (!this.jsonFile) {
                return null;
            }

            return fileReader.readAsText(this.jsonFile).then((data) => {
                this.flowData = JSON.parse(data as string);
                if (this.flowData) {
                    this.generateData(this.flowData);
                }
            });
        },

        isMatchCondition(item: Condition, other: Condition): boolean {
            // Handle case default condition container
            const isDefaultContainer = (item.type === 'orContainer' || item.type === 'andContainer')
                && (JSON.stringify(item.value) === '[]' || JSON.stringify(item.value) === '{}');

            return item.id == other.id
                && item.type == other.type
                && (isDefaultContainer || JSON.stringify(item.value) === JSON.stringify(other.value))
        },

        hasRuleConditionsConflict(uploadedConditions: Array<Condition>, localConditions: Array<Condition>): boolean {
            if (uploadedConditions.length !== localConditions.length) {
                return true;
            }

            const onlyInUploaded = uploadedConditions.filter(item => !localConditions.some(other => this.isMatchCondition(item, other)));
            const onlyInLocal = localConditions.filter(item => !uploadedConditions.some(other => this.isMatchCondition(item, other)));
            const affectedRuleConditions = [...onlyInUploaded, ...onlyInLocal];

            return affectedRuleConditions.length > 0;
        },

        async generateRuleData(rules: Array<Rule>): Promise<void> {
            const ruleIds = rules.map(rule => rule.id)

            const criteria = new Criteria(1, null);
            criteria.addFilter(Criteria.equalsAny('id', ruleIds));
            criteria.addAssociation('conditions');
            const localRules = await this.ruleRepository.search(criteria);
            const localRulesIds = Object.values(localRules).map((rule: Rule) => rule.id);

            rules.map(rule => {
                if (!localRulesIds.includes(rule.id)) {
                    this.rules.push(rule);
                    return;
                }

                let localRule = null;

                for (let index = 0; index < localRules.length; index++) {
                    if (localRules[index].id = rule.id) {
                        localRule = localRules[index];
                    }
                    break;
                }

                const uploadedConditions = [...rule.conditions];
                const localConditions = [...localRule.conditions];

                if (this.hasRuleConditionsConflict(uploadedConditions, localConditions)) {
                    this.affectedRules.push({ ...rule, _isNew: false });
                }
            });
        },

        async generateMailTemplateData(data: Array<Array<MailTemplate>>): Promise<void> {
            const mailTemplates = [];
            const mailTemplateIds = [];

            data.forEach(mail => {
                const currentLocaleEmail = mail.find(item => item.locale === State.get('session').currentLocale);
                if (!currentLocaleEmail) {
                    return;
                }

                mailTemplateIds.push(currentLocaleEmail.id);
                mailTemplates.push(currentLocaleEmail);
            });

            const criteria = new Criteria(1, null);
            criteria.addFilter(Criteria.equalsAny('id', mailTemplateIds));

            const existingMailTemplates = await this.mailTemplateRepository.searchIds(criteria);

            mailTemplates.forEach((mail) => {
                if (!existingMailTemplates.data.includes(mail.id)) {
                    this.mailTemplates.push(mail);
                }
            });
        },

        async generateData(flowData: FlowData): Promise<void> {
            const { dataIncluded } = flowData;

            if (!dataIncluded) return;

            if (dataIncluded.rule) {
                this.generateRuleData(Object.values(dataIncluded.rule));
            }

            if (dataIncluded.mail_template) {
                this.generateMailTemplateData(Object.values(dataIncluded.mail_template));
            }
        },

        // eslint-disable-next-line no-unused-vars
        validateRequirements(requirements: Requirement): void {
            this.flowSharingService.checkRequirements({ requirements }).then((data) => {
                this.report = data;
                this.disableUpload = !!Object.keys(data).length;
            }).catch(() => {
                this.hasError = true;
            });
        },

        handleSelectRule(item: Rule, checked: boolean): void {
            this.notSelectedRuleIds = checked
                ? this.notSelectedRuleIds.filter(ruleId => ruleId !== item.id)
                : [...this.notSelectedRuleIds, item.id];
        },

        handleSelectMail(item: MailTemplate, checked: boolean): void {
            this.notSelectedMailIds = checked
                ? this.notSelectedMailIds.filter(mailId => mailId !== item.id)
                : [...this.notSelectedMailIds, item.id];
        },

        createRule(rule: Rule): RuleEntity {
            const ruleEntity = this.ruleRepository.create();

            Object.keys(rule).forEach((key) => {
                ruleEntity[key] = rule[key];
            });

            const conditions = new EntityCollection(
                this.ruleConditionRepository.route,
                this.ruleConditionRepository.entityName,
                Context.api,
            );

            Service('flowBuilderService').rearrangeArrayObjects(rule.conditions).forEach((condition) => {
                const conditionEntity = this.ruleConditionRepository.create();

                Object.keys(condition).forEach((key) => {
                    conditionEntity[key] = condition[key];
                });

                conditions.add(conditionEntity);
            });

            ruleEntity.conditions = conditions;

            return ruleEntity;
        },

        createMailTemplate(mailTemplate: MailTemplate): MailTemplateEntity {
            const criteria = new Criteria(1, 25);
            criteria.addFilter(
                Criteria.equals('technicalName', mailTemplate.technicalName),
            );

            const mailTemplateEntity = this.mailTemplateRepository.create();

            const mailTemplateType = this.mailTemplateTypes.find(item => item.technicalName === mailTemplate.technicalName);

            if (!mailTemplateType) {
                return null;
            }

            mailTemplateEntity.id = mailTemplate.id;
            mailTemplateEntity.mailTemplateTypeId = mailTemplateType?.id;
            mailTemplateEntity.senderName = mailTemplate.senderName;
            mailTemplateEntity.subject = mailTemplate.subject;
            mailTemplateEntity.description = mailTemplate.description;
            mailTemplateEntity.contentHtml = mailTemplate.contentHtml;
            mailTemplateEntity.contentPlain = mailTemplate.contentPlain;
            mailTemplateEntity.customFields = mailTemplate.customFields;

            return mailTemplateEntity;
        },
    },
};
