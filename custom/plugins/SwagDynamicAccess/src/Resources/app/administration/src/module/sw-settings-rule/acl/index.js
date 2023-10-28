Shopware.Service('privileges')
    .addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'settings',
        key: 'rule',
        roles: {
            viewer: {
                privileges: [
                    'category:read',
                    'landing_page:read',
                ],
            },
            editor: {
                privileges: [
                    'swag_dynamic_access_product_rule:create',
                    'swag_dynamic_access_product_rule:delete',
                    'swag_dynamic_access_category_rule:create',
                    'swag_dynamic_access_category_rule:delete',
                    'swag_dynamic_access_landing_page_rule:create',
                    'swag_dynamic_access_landing_page_rule:delete',
                ],
            },
        },
    });
