import template from './sw-flow-download-modal.html.twig';
import './sw-flow-download-modal.scss';
import type {
    Rule,
    MailTemplate,
    FlowData,
} from '../../../flow.types';

const { State } = Shopware;

/**
 * @package business-ops
 */
export default {
    template,

    inject: [
        'acl',
        'repositoryFactory',
        'flowSharingService'
    ],

    props: {
        flowId: {
            type: String,
            required: true,
        },
    },

    data(): {
        flowData: FlowData,
        rules: Array<Rule>,
        mailTemplates: Array<MailTemplate>,
        notSelectedRuleIds: Array<string>,
        notSelectedMailIds: Array<string>,
        hasError: boolean,
    } {
        return {
            flowData: {} as FlowData,
            rules: [],
            mailTemplates: [],
            notSelectedRuleIds: [],
            notSelectedMailIds: [],
            hasError: false,
        };
    },

    computed: {
        downloadData(): FlowData {
            // eslint-disable-next-line camelcase
            const { rule, mail_template } = this.flowData.dataIncluded;

            if (rule && this.notSelectedRuleIds.length > 0) {
                this.notSelectedRuleIds.forEach(ruleId => {
                    delete rule[`${ruleId}`];
                });
            }

            // eslint-disable-next-line camelcase
            if (mail_template && this.notSelectedMailIds.length > 0) {
                this.notSelectedMailIds.forEach(mailId => {
                    delete mail_template[`${mailId}`];
                });
            }

            const downloadData = Object.assign({}, this.flowData);

            if (rule && Object.keys(rule).length > 0) {
                downloadData.dataIncluded.rule = rule;
            } else {
                delete downloadData.dataIncluded.rule;
            }

            // eslint-disable-next-line camelcase
            if (mail_template && Object.keys(mail_template).length > 0) {
                // eslint-disable-next-line camelcase
                downloadData.dataIncluded.mail_template = mail_template;
            } else {
                delete downloadData.dataIncluded.mail_template;
            }

            return downloadData;
        },
    },

    created(): void {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            this.getDataIncluded();
        },

        onCancel(): void {
            this.$emit('modal-close');
        },

        onDownload(): void {
            if (this.hasError || this.flowData.length <= 0) {
                this.$emit('download-finish', false);

                return;
            }

            const filename = `${this.flowData.flow.name}.json`;
            const link = document.createElement('a');

            link.href = `data:application/json;charset=utf-8,${encodeURIComponent(JSON.stringify(this.downloadData))}`;
            link.download = filename;
            link.dispatchEvent(new MouseEvent('click'));
            link.remove();

            this.$emit('download-finish', true);
        },

        getDataIncluded(): void {
            this.flowSharingService.downloadFlow(this.flowId).then((data) => {
                this.flowData = data;

                if (data.dataIncluded && data.dataIncluded.rule) {
                    this.rules = Object.values(data.dataIncluded.rule);
                }

                if (data.dataIncluded && data.dataIncluded.mail_template) {
                    const mailTemplateIncluded = Object.values(data.dataIncluded.mail_template) as unknown as Array<Array<MailTemplate>>;

                    mailTemplateIncluded.forEach(item => {
                        const currentLocaleEmail = item.find(email => email.locale === State.get('session').currentLocale);
                        if (currentLocaleEmail) {
                            this.mailTemplates.push(currentLocaleEmail);
                        }
                    });
                }
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
    },
};
