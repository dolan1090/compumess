/**
 * @package inventory
 */

/* istanbul ignore else */
if (Shopware.License.get('MULTI_INVENTORY-3711815')) {
    Shopware.Component.override('sw-product-detail', {
        computed: {
            productCriteria() {
                const criteria = this.$super('productCriteria');
                criteria.addAssociation('warehouses');
                criteria.addAssociation('warehouseGroups');
                criteria.getAssociation('warehouseGroups').addAssociation('warehouses');

                criteria.addIncludes({
                    warehouseGroups: ['id', 'name'],
                    warehouse: ['id'],
                });

                return criteria;
            },
        },
    });
}
