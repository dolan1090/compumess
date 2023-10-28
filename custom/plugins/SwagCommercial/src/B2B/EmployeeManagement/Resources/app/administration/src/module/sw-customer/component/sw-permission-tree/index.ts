import './sw-permission-tree.scss';
import template from './sw-permission-tree.html.twig';
import type { PermissionEvent, PermissionTreeType, PermissionTreeState } from './permission-tree';
import PermissionTree from './permission-tree';

const { Component } = Shopware;

interface SwPermissionTreeState {
    selected: string[];
    permissionTree?: PermissionTree;
}

export default Component.wrapComponentConfig({
    template,

    props: {
        permissions: {
            type: Array<PermissionEvent>,
            required: true,
        },
        preSelectedPermissions: {
            type: Array<string>,
            required: true,
        },
    },

    data(): SwPermissionTreeState {
        return {
            selected: [],
            permissionTree: new PermissionTree({
                permissions: this.permissions,
                selected: this.preSelectedPermissions,
            }),
        };
    },

    watch: {
        combinedPermissions: {
            immediate: true,
            handler(values: PermissionTreeState) {
                this.permissionTree.updateState(values);

                this.$forceUpdate();
            },
        },
    },

    computed: {
        tree() {
            return this.permissionTree.getPermissionTree();
        },

        combinedPermissions(): PermissionTreeState {
            return {
                selected: this.preSelectedPermissions,
                permissions: this.permissions,
            };
        },
    },

    methods: {
        onChange(eventValue: boolean, permissions: PermissionTreeType) {
            if (eventValue) {
                this.permissionTree.select(permissions);
            } else {
                this.permissionTree.unselect(permissions);
            }

            this.$emit('change-permissions', this.permissionTree.getSelected());
        },

        getPermissionGroupLabel(permissionGroupName: string): string {
            return this.$tc(`sw-customer.role.create.permissions.tree.groups.${this.$sanitize(permissionGroupName)}`);
        },

        getPermissionLabel(permissionName: string): string {
            return this.$tc(`sw-customer.role.create.permissions.tree.permissions.${this.$sanitize(permissionName)}`);
        },

        getPermissionClass(permissionName: string): string {
            return `sw-permission-switch__${permissionName.replace(/\./g, '-')}`;
        },

        getDependenciesTooltip(dependencies: string[]): { message: string, disabled: boolean, width?: number } {
            if (dependencies.length === 0) {
                return {
                    message: '',
                    disabled: true,
                };
            }

            const dependencyStr = dependencies.map((dependency: string) => {
                return `<li style="margin-left: 15px">${this.getPermissionLabel(dependency)}</li>`;
            }).join('');

            return {
                message: `<p>${this.$tc('sw-customer.role.create.permissions.tooltipDependenciesTitle')}</p><ul>${dependencyStr}</ul>`,
                disabled: false,
                width: 220,
            };
        },
    },
});
