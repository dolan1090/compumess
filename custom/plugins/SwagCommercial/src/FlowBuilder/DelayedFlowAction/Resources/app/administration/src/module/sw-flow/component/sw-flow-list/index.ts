import template from './sw-flow-list.html.twig';
import type RepositoryType from 'src/core/data/repository.data';
import type CriteriaType from 'src/core/data/criteria.data';
import {Sequence} from "../../../../type/types";

const { Component, Data: { Criteria } } = Shopware;

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    data(): {
        flowsDelayedName: [],
        showDelayWarning: boolean
    } {
        return {
            flowsDelayedName: [],
            showDelayWarning: false,
        };
    },

    computed: {
        flowCriteria():CriteriaType {
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.term);
            criteria
                .addAssociation('sequences')
                .addSorting(Criteria.sort(this.sortBy, this.sortDirection))
                .addSorting(Criteria.sort('updatedAt', 'DESC'));

            return criteria;
        },

        delayedActionsRepository(): RepositoryType {
            return this.repositoryFactory.create('swag_delay_action');
        }
    },

    watch: {
        currentFlow(value: Sequence): void {
            this.flowsDelayedName = [];
            this.findDelayAction(value);
            this.showDelayWarning = false;
        },

        selectedItems(values: Sequence[]): void {
            if (values.length === 0) {
                return;
            }

            this.flowsDelayedName = [];
            values.forEach(item => {
                this.findDelayAction(item);
            });

            this.showDelayWarning = false;
        },
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        findDelayAction(item: Sequence): void {
            if (!item.id) {
                return;
            }

            if (this.getLicense('FLOW_BUILDER-8415866')) {
                const initContainer = Shopware.Application.getContainer('init');
                initContainer.httpClient.get(
                    'api/_info/config',
                    {
                        headers: {
                            Accept: 'application/vnd.api+json',
                            Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                            'Content-Type': 'application/json',
                            'sw-license-toggle': 'FLOW_BUILDER-8415866',
                        },
                    },
                );
                return;
            }

            const criteria = new Criteria();
            criteria.addFilter(Criteria.equalsAny('flowId', [item.id]));
            criteria.addFilter(Criteria.not('and', [Criteria.equals('sequence.children.id', null)]));

            this.delayedActionsRepository.search(criteria).then(result => {
                if (result.length > 0) {
                    this.showDelayWarning = true;
                    this.flowsDelayedName.push(item.name);
                }
            }).catch(() => {
                this.showDelayWarning = false;
            });
        },
    }
});
