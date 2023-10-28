/**
 * @package inventory
 */
import template from './sw-warehouse-messages.html.twig';
import './sw-warehouse-messages.scss';

const { Criteria } = Shopware.Data;

Shopware.Component.register('sw-warehouse-messages', {
    template,
    inject: [
        'repositoryFactory',
    ],
    data() {
        return {
            warehouseGroups: null,
            ruleInfoVisible: false,
        }
    },
    computed: {
        warehouseGroupCriteria() {
            const criteria = new Criteria();
            criteria.addFilter(
                Criteria.equals('ruleId', null)
            );

            criteria.addIncludes({
                warehouseGroup: ['id', 'name'],
            });

            return criteria;
        },
        warehouseGroupRepository() {
            return this.repositoryFactory.create('warehouse_group');
        },
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.getWarehouseGroups();
        },
        async getWarehouseGroups() {
            this.warehouseGroups = await this.warehouseGroupRepository.search(this.warehouseGroupCriteria, Shopware.Context.api);
            this.ruleInfoVisible = this.warehouseGroups.length > 0;
        },
        buildNoRuleAssignedAlertMessage(snippet, collection) {
            const data = {
                warehouseGroups: collection.map((item) => (
                    `<b><li>${item.name}</li></b>`
                )).join('\n')
            }

            return this.$tc(snippet, collection.length, data);
        }
    },
});
