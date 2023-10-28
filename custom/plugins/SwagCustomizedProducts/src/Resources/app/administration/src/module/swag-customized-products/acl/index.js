Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'catalogues',
    key: 'swag_customized_products_template',
    roles: {
        viewer: {
            privileges: [
                Shopware.Service('privileges').getPrivileges('media.viewer'),
                'version:read',
                'tax:read',
                'currency:read',
                'swag_customized_products_template:read',
                'swag_customized_products_template_media:read',
                'swag_customized_products_template_option:read',
                'swag_customized_products_template_option_value:read',
                'swag_customized_products_template_option_price:read',
                'swag_customized_products_template_options:read',
                'swag_customized_products_template_products:read',
                'swag_customized_products_template_exclusion:read',
                'swag_customized_products_template_exclusions:read',
                'swag_customized_products_template_exclusion_condition:read',
                'swag_customized_products_template_exclusion_operator:read',
                'swag_customized_products_template_configurations:read',
                'swag_customized_products_template_option_value_price:read',
            ],
            dependencies: [],
        },

        editor: {
            privileges: [
                Shopware.Service('privileges').getPrivileges('media.creator'),
                'version:update',
                'tax:update',
                'currency:update',
                'swag_customized_products_template:update',
                'swag_customized_products_template_media:update',
                'swag_customized_products_template_option:update',
                'swag_customized_products_template_option_value:update',
                'swag_customized_products_template_option_price:update',
                'swag_customized_products_template_options:update',
                'swag_customized_products_template_products:update',
                'swag_customized_products_template_exclusion:update',
                'swag_customized_products_template_exclusions:update',
                'swag_customized_products_template_exclusion_condition:update',
                'swag_customized_products_template_exclusion_operator:update',
                'swag_customized_products_template_configurations:update',
                'swag_customized_products_template_option_value_price:update',
                'swag_customized_products_template_media:create',
                'swag_customized_products_template_option:create',
                'swag_customized_products_template_option_value:create',
                'swag_customized_products_template_option_price:create',
                'swag_customized_products_template_options:create',
                'swag_customized_products_template_exclusion:create',
                'swag_customized_products_template_exclusions:create',
                'swag_customized_products_template_exclusion_condition:create',
                'swag_customized_products_template_exclusion_operator:create',
                'swag_customized_products_template_configurations:create',
                'swag_customized_products_template_option_value_price:create',
                'version:delete',
                'swag_customized_products_template_option:delete',
                'swag_customized_products_template_option_value:delete',
                'swag_customized_products_template_option_price:delete',
                'swag_customized_products_template_options:delete',
                'swag_customized_products_template_exclusion:delete',
                'swag_customized_products_template_exclusions:delete',
                'swag_customized_products_template_exclusion_condition:delete',
                'swag_customized_products_template_exclusion_operator:delete',
                'swag_customized_products_template_configurations:delete',
                'swag_customized_products_template_option_value_price:delete',
            ],
            dependencies: [
                'swag_customized_products_template.viewer',
            ],
        },

        deleter: {
            privileges: [
                Shopware.Service('privileges').getPrivileges('media.deleter'),
                'version:delete',
                'tax:delete',
                'currency:delete',
                'swag_customized_products_template:delete',
                'swag_customized_products_template_media:delete',
                'swag_customized_products_template_option:delete',
                'swag_customized_products_template_option_value:delete',
                'swag_customized_products_template_option_price:delete',
                'swag_customized_products_template_options:delete',
                'swag_customized_products_template_products:delete',
                'swag_customized_products_template_exclusion:delete',
                'swag_customized_products_template_exclusions:delete',
                'swag_customized_products_template_exclusion_condition:delete',
                'swag_customized_products_template_exclusion_operator:delete',
                'swag_customized_products_template_configurations:delete',
                'swag_customized_products_template_option_value_price:delete',
            ],
            dependencies: [
                'swag_customized_products_template.viewer',
            ],
        },

        creator: {
            privileges: [
                Shopware.Service('privileges').getPrivileges('media.creator'),
                'version:create',
                'tax:create',
                'currency:create',
                'swag_customized_products_template:create',
                'swag_customized_products_template_media:create',
                'swag_customized_products_template_option:create',
                'swag_customized_products_template_option_value:create',
                'swag_customized_products_template_option_price:create',
                'swag_customized_products_template_options:create',
                'swag_customized_products_template_products:create',
                'swag_customized_products_template_exclusion:create',
                'swag_customized_products_template_exclusions:create',
                'swag_customized_products_template_exclusion_condition:create',
                'swag_customized_products_template_exclusion_operator:create',
                'swag_customized_products_template_configurations:create',
                'swag_customized_products_template_option_value_price:create',
            ],
            dependencies: [
                'swag_customized_products_template.viewer',
                'swag_customized_products_template.editor',
            ],
        },
    },
});

Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'catalogues',
    key: 'product',
    roles: {
        viewer: {
            privileges: [
                'swag_customized_products_template:read',
            ],
        },
    },
});