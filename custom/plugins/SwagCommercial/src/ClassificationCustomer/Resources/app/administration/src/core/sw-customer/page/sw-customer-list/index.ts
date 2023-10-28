/**
 * @package checkout
 */

import template from './sw-customer-list.html.twig';
import './sw-customer-list.scss';
import type CriteriaType from '@administration/core/data/criteria.data';
import type EntityCollectionType from '@administration/core/data/entity-collection.data';
import { TOGGLE_KEY, TRAP_KEY_1 } from '../../../../config'
const { Component } = Shopware;

interface CustomerColumn {
    property: string,
    label: string,
    primary?: boolean,
    multiLine?: boolean,
    width?: string,
    inlineEdit?: boolean,
    sortable?: boolean,
    allowResize?: boolean,
    useCustomSort?: boolean,
    naturalSorting?: boolean,
    dataIndex?: string,
    align?: string,
    visible?: boolean,
}

export default Component.wrapComponentConfig({
    template,

    computed: {
        defaultCriteria(): CriteriaType {
            const criteria = this.$super('defaultCriteria');
            criteria.addAssociation('tags')

            return criteria;
        },

        customerColumns(): CustomerColumn[] {
            const temp = [...this.getCustomerColumns()];

            if (this.hasToggleKey) {
                temp.splice(1, 0, {
                    property: 'tags',
                    label: 'sw-customer.baseForm.labelTags',
                    sortable: false,
                    allowResize: true,
                    multiLine: true,
                });
            }

            return temp;
        },

        hasToggleKey(): boolean {
            return Shopware.License.get(TOGGLE_KEY);
        },
    },

    methods: {
        onClickClassify(): void {
            if (Shopware.License.get(TRAP_KEY_1)) {
                const initContainer = Shopware.Application.getContainer('init');
                initContainer.httpClient.get(
                    '_info/config',
                    {
                        headers: {
                            Accept: 'application/vnd.api+json',
                            Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                                'Content-Type': 'application/json',
                                'sw-license-toggle': TRAP_KEY_1,
                        },
                    },
                );

                return;
            }

            this.$router.push({ name: 'swag.customer.classification.index' });
        },
    }
});
