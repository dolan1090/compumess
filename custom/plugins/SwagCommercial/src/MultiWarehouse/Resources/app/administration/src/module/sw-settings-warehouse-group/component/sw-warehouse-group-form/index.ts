/**
 * @package inventory
 */
import template from './sw-warehouse-group-form.html.twig';
import './sw-warehouse-group-form.scss';

const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Shopware.Component.register('sw-warehouse-group-form', {
    template,
    props: {
        warehouseGroup: {
            type: Object,
            required: true,
        },
    },
    computed: {
        ...mapPropertyErrors(
            'warehouseGroup',
            ['name'],
        ),
    },
    methods: {
        setRuleId(ruleId) {
            this.warehouseGroup.ruleId = ruleId;
        },
    },
});
