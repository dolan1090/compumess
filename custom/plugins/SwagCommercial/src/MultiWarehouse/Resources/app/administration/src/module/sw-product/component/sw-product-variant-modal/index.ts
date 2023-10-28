/**
 * @package inventory
 */
import template from './sw-product-variant-modal.html.twig';

/* istanbul ignore else */
if (Shopware.License.get('MULTI_INVENTORY-3711815')) {
    Shopware.Component.override('sw-product-variant-modal', {
        template,
        computed: {
            productRepository() {
                const repository = this.$super('productRepository');

                const search = repository.search;

                repository.search = (criteria) => {
                    criteria.addAssociation('warehouseGroups');

                    return search.call(repository, criteria);
                }

                return repository;
            },
        },
        methods: {
            hasWarehouseGroups(variant) {
                if (variant.extensions.warehouseGroups && variant.extensions.warehouseGroups.length > 0) {
                    return true;
                }

                return false;
            },
        },
    });
}
