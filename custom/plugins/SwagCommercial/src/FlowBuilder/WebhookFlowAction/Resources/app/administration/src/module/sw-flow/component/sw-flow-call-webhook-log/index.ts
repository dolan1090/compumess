import template from './sw-flow-call-webhook-log.html';
import type RepositoryType from 'src/core/data/repository.data';
import type CriteriaType from 'src/core/data/criteria.data';
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';
import type {Entity} from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import {LogColumn, StatusOption, Properties} from '../../../../type/types';
import './sw-flow-call-webhook-log.scss';

const { Mixin, Application, Service, Component } = Shopware;
const { mapState } = Component.getComponentHelper();
const { Criteria } = Shopware.Data;

/**
 * @package business-ops
 */
export default {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('placeholder'),
        'notification',
    ],

    props: {
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data(): {
        isWebhookLogLoading: boolean,
        sortBy: string,
        sortDirection: string,
        disableRouteParams: boolean,
        webhookLogs: EntityCollection<'webhook_event_log'> | [],
        displayedLog: Entity<'webhook_event_log'> | null,
        fromDate: string,
        toDate: string,
        selectedStatus: string,
        statusOptions: Array<StatusOption>,
    } {
        return {
            isWebhookLogLoading: false,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            disableRouteParams: true,
            webhookLogs: [],
            displayedLog: null,
            fromDate: '',
            toDate: '',
            selectedStatus: '',
            statusOptions: [
                {
                    id: 'success',
                    name: this.$tc('sw-flow-call-webhook.log.list.labelSuccess')
                },
                {
                    id: 'failed',
                    name: this.$tc('sw-flow-call-webhook.log.list.labelFailed')
                }
            ]
        };
    },

    computed: {
        ...mapState('swFlowState', ['flow']),

        webhookLogRepository(): RepositoryType<'webhook_event_log'> {
            return this.repositoryFactory.create('webhook_event_log');
        },

        logColumns(): LogColumn[] {
            return this.getLogColumns();
        },

        reloadProperties(): Properties {
            const { isLoading, toDate, fromDate, selectedStatus } = this;

            return {
                isLoading,
                toDate,
                fromDate,
                selectedStatus
            }
        },

        webhookLogCriteria(): CriteriaType {
            const criteria = new Criteria(this.page, this.limit);

            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addFilter(Criteria.equals('flowSequences.flowId', this.flow.id));

            if (this.fromDate) {
                criteria.addFilter(Criteria.range('createdAt', { gte: this.fromDate }));
            }

            if (this.toDate) {
                criteria.addFilter(Criteria.range('createdAt', { lte: this.toDate }));
            }

            if (this.selectedStatus) {
                criteria.addFilter(Criteria.equals('deliveryStatus', this.selectedStatus));
            }

            return criteria;
        },
    },

    watch: {
        reloadProperties(): void | Promise<[]> {
            this.getList();
        },
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        getList(): void | Promise<[]> {
            // Wait until flow data is loaded completely
            if (this.isLoading) {
                return;
            }

            if (this.getLicense('FLOW_BUILDER-4884308')) {
                const initContainer = Application.getContainer('init');
                initContainer.httpClient.get(
                    'api/_info/config',
                    {
                        headers: {
                            Accept: 'application/vnd.api+json',
                            Authorization: `Bearer ${Service('loginService').getToken()}`,
                            'Content-Type': 'application/json',
                            'sw-license-toggle': 'FLOW_BUILDER-4884308',
                        },
                    },
                );
                return;
            }

            this.isWebhookLogLoading = true;

            return this.webhookLogRepository.search(this.webhookLogCriteria).then((response) => {
                this.total = response.total;
                this.webhookLogs = response;
            }).catch(() => {
                this.createNotificationError({
                    message: this.$tc('sw-flow-call-webhook.log.list.fetchErrorMessage'),
                });
            }).finally(() => {
                this.isWebhookLogLoading = false;
            });
        },

        showInfoModal(content: Entity<'webhook_event_log'>): void {
            this.displayedLog = content;
        },

        closeInfoModal(): void {
            this.displayedLog = null;
        },

        getLogColumns(): Array<LogColumn> {
            return [{
                property: 'createdAt',
                dataIndex: 'createdAt',
                label: 'sw-flow-call-webhook.log.list.columnDate',
                allowResize: true,
                primary: true,
            }, {
                property: 'webhookName',
                dataIndex: 'webhookName',
                label: 'sw-flow-call-webhook.log.list.columnName',
                allowResize: true,
            }, {
                property: 'deliveryStatus',
                dataIndex: 'deliveryStatus',
                label: 'sw-flow-call-webhook.log.list.columnStatus',
                allowResize: true,
            }];
        },

        getLabelVariant(item: Entity<'webhook_event_log'>): string {
            return item.deliveryStatus === 'success' ? 'success' : 'danger';
        },

        getLabelStatus(item: Entity<'webhook_event_log'>): string {
            return item.deliveryStatus === 'success'
                ? this.$tc('sw-flow-call-webhook.log.list.labelSuccess')
                : this.$tc('sw-flow-call-webhook.log.list.labelFailed');
        },
    }
};
