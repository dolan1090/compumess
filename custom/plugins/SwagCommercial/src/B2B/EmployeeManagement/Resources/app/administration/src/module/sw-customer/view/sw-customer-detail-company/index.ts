import template from './sw-customer-detail-company.html.twig';
import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

export default Shopware.Component.wrapComponentConfig({
    template,

    inject: [
        'acl',
    ],

    props: {
        customer: {
            type: Object as PropType<Entity<'b2b_employee'>>,
            required: true,
        },
    },
});
