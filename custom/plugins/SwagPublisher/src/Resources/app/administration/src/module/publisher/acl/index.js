Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'content',
    key: 'publisher',
    roles: {
        viewer: {
            privileges: [
                'cms_page_draft:read',
                'cms_page_activity:read',
                'user:read',
                'version:read',
                'version_commit:read',
                'version_commit_data:read',
                'sales_channel_domain:read',
            ],
            dependencies: [
                'cms.viewer',
            ],
        },
        editor: {
            privileges: [
                'cms_page_draft:update',
                'cms_page_activity:update',
                'version:update',
                'version_commit:update',
                'version_commit_data:update',
            ],
            dependencies: [
                'cms.editor',
            ],
        },
        creator: {
            privileges: [
                'cms_page_draft:create',
                'cms_page_activity:create',
                'version:create',
                'version:delete',
                'version_commit:create',
                'version_commit_data:create',
                'version_commit:delete',
                'version_commit_data:delete',
            ],
            dependencies: [
                'cms.creator',
            ],
        },
        deleter: {
            privileges: [
                'cms_page_draft:delete',
                'cms_page_activity:delete',
                'version:delete',
                'version_commit:delete',
                'version_commit_data:delete',
            ],
            dependencies: [
                'cms.deleter',
            ],
        }
    }
});