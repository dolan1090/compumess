Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'b2b',
    key: 'b2b_employee_management',
    roles: {
        viewer: {
            privileges: [
                'b2b_employee:read',
                'b2b_components_role:read',
                'b2b_business_partner:read',
                'customer_specific_features:read',
            ],
            dependencies: [
                'customer.viewer',
            ],
        },
        editor: {
            privileges: [
                'b2b_employee:update',
                'b2b_components_role:update',
                'b2b_business_partner:create',
                'b2b_business_partner:update',
                'b2b_business_partner:delete',
                'customer_specific_features:create',
                'customer_specific_features:update',
            ],
            dependencies: [
                'b2b_employee_management.viewer',
                'customer.editor',
            ],
        },
        creator: {
            privileges: [
                'b2b_employee:create',
                'b2b_components_role:create',
                'b2b_business_partner:create',
                'customer_specific_features:create',
            ],
            dependencies: [
                'b2b_employee_management.editor',
            ],
        },
        deleter: {
            privileges: [
                'b2b_employee:delete',
                'b2b_components_role:delete',
            ],
            dependencies: [
                'b2b_employee_management.editor',
            ],
        },
    },
});
