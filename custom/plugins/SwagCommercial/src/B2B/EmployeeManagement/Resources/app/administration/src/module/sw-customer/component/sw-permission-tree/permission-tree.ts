export type PermissionEvent = {
    permissionName: string;
    permissionGroupName: string;
    permissionDependencies: string[];
}

export type PermissionTreeType = {
    [key: string]: PermissionEvent;
}

export interface PermissionTreeState {
    permissions: PermissionEvent[];
    selected: string[];
}

export default class PermissionTree {
    private permissions: PermissionEvent[] = [];
    private selected: string[] = [];

    constructor({permissions, selected}: PermissionTreeState) {
        this.permissions = permissions;
        this.selected = selected;
    }

    public updateState({ permissions = null, selected = null }: { permissions?: PermissionEvent[], selected?: string[]}) {
        if (permissions) {
            this.permissions = permissions;
        }

        if (selected) {
            this.selected = selected;
        }
    };

    public getPermissions() {
        return this.permissions;
    };

    public getSelected() {
        return this.selected;
    };

    public unselect(permissions: PermissionTreeType) {
        const names = this.getPermissionNames(permissions);
        const selectedPermissions = this.getPermissionsByName(this.selected, this.permissions);

        const dependencyNames = this.findDependencies(
            names,
            selectedPermissions,
            (parent: PermissionEvent, current: PermissionEvent): boolean => {
                return current.permissionDependencies.includes(parent.permissionName)
            }
        );

        this.selected = this.selected.filter((permissionName: string) => {
            return !dependencyNames.includes(permissionName);
        });
    };

    public select(permissions: PermissionTreeType) {
        const names = this.getPermissionNames(permissions);

        const dependencyNames = this.findDependencies(
            names,
            this.permissions,
            (parent: PermissionEvent, current: PermissionEvent): boolean => {
                return parent.permissionDependencies.includes(current.permissionName);
            }
        );

        this.selected = this.getUniquePermissionNames(
            this.selected,
            names,
            dependencyNames,
        );
    };

    public getPermissionTree(): PermissionTreeType {
        const tree = {};

        this.permissions.forEach((permission: PermissionEvent) => {
            const { permissionGroupName } = permission;

            if (!tree[permissionGroupName]) {
                tree[permissionGroupName] = [];
            }

            tree[permissionGroupName].push(permission);
        });

        return tree;
    };

    public isGroupSelected(permissions: PermissionTreeType): boolean {
        const permissionNames = this.getPermissionNames(permissions);

        return permissionNames.every((permissionName: string) => {
            return this.selected.includes(permissionName);
        });
    };

    public isGroupPartialSelected(permissions: PermissionTreeType): boolean {
        const permissionNames = this.getPermissionNames(permissions);

        return permissionNames.some((permissionName: string) => {
            return this.selected.includes(permissionName);
        });
    }

    public isSelected(permissionName: string): boolean {
        return this.selected.includes(permissionName);
    };

    public isEverythingSelected(): boolean {
        return this.selected.length === this.permissions.length && this.permissions.length > 0;
    }

    private findDependencies(names: string[], permissions: PermissionEvent[], check: (parent: PermissionEvent, current: PermissionEvent) => boolean): string[] {
        const dependencyNames = new Set<string>();
        const visited = new Set<string>();

        const findDependentPermissions = (permissionName: string) => {
            const selectedPermission = this.getPermissionsByName([permissionName], permissions)[0];

            if (visited.has(permissionName)) {
                return;
            }

            visited.add(permissionName);

            permissions.forEach((permission: PermissionEvent) => {
                if (check(selectedPermission, permission)) {
                    dependencyNames.add(permission.permissionName);
                    findDependentPermissions(permission.permissionName);
                }
            });
        };

        names.forEach((permissionName: string) => {
            findDependentPermissions(permissionName);
        });

        return this.getUniquePermissionNames(names, dependencyNames);
    };

    private getPermissionsByName(names: string[], permissions: PermissionEvent[]): PermissionEvent[] {
        return permissions.filter((permission: PermissionEvent) => {
            return names.includes(permission.permissionName);
        });
    };

    private getPermissionNames(permissions: PermissionTreeType|PermissionEvent[]): string[] {
        return Object.values(permissions).map((permission) => {
            return permission.permissionName;
        });
    };

    private getUniquePermissionNames(...names: Array<Set<string> | string[]>): string[] {
        const mergedNames = new Set<string>();

        names.forEach(permissionSet => {
            permissionSet.forEach(permission => mergedNames.add(permission));
        });

        return Array.from(mergedNames);
    };
}
