import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

type Options = {
    permissionGroupSelector?: string,
    permissionSelector?: string,
};

export default class B2bRolePermissionTree extends Plugin {
    static options: Options = {
        permissionGroupSelector: null,
        permissionSelector: null,
    };

    private el: HTMLElement;

    public init(): void {
        this._updateAllGroupCheckedStates();
        this._addEventListeners();
    }

    private _updateAllGroupCheckedStates(): void {
        const permissionGroups = DomAccess.querySelectorAll(this.el, this.options.permissionGroupSelector);

        permissionGroups.forEach((group: HTMLInputElement) => {
            const permissionsOfGroup = this._getPermissionsOfGroup(group.value);
            this._updateGroupChecked(group, permissionsOfGroup);
        });
    }

    private _addEventListeners(): void {
        const permissionGroups = DomAccess.querySelectorAll(this.el, this.options.permissionGroupSelector);
        permissionGroups.forEach((group: HTMLInputElement) => {
            group.addEventListener('change', this._handlePermissionGroupChange.bind(this))
        });

        const permissions = DomAccess.querySelectorAll(this.el, this.options.permissionSelector);
        permissions.forEach((permission: HTMLInputElement) => {
            permission.addEventListener('change', this._handlePermissionChange.bind(this))
        });
    }

    private _handlePermissionGroupChange(event: Event): void {
        const targetElement = event.currentTarget as HTMLInputElement;
        const allPermissions = DomAccess.querySelectorAll(this.el, this.options.permissionSelector);

        const permissionsOfGroup = this._getPermissionsOfGroup(targetElement.value);
        permissionsOfGroup.forEach((permission) => {
            const oldCheckedState = permission.checked;
            permission.checked = targetElement.checked;

            if (targetElement.checked && oldCheckedState !== permission.checked) {
                const dependencies: string[] = JSON.parse(permission.dataset.b2bPermissionDependencies) || [];
                this._makePermissionsCheckedNested(allPermissions, dependencies);
            }
        });

        if (!targetElement.checked) {
            this._uncheckAllPermissionsMissingDependenciesNested(allPermissions);
        }

        this._updateAllGroupCheckedStates();
    }

    private _handlePermissionChange(event: Event): void {
        const targetElement = event.currentTarget as HTMLInputElement;
        const allPermissions = DomAccess.querySelectorAll(this.el, this.options.permissionSelector);

        if (targetElement.checked) {
            const dependencies: string[] = JSON.parse(targetElement.dataset.b2bPermissionDependencies) || [];
            this._makePermissionsCheckedNested(allPermissions, dependencies);
        } else {
            this._uncheckAllPermissionsMissingDependenciesNested(allPermissions);
        }

        this._updateAllGroupCheckedStates();
    }

    private _makePermissionsCheckedNested(allPermissions: NodeListOf<HTMLInputElement>, firstLevelDependencies: string[]): void {
        let dependencyNames = firstLevelDependencies;
        while (dependencyNames.length > 0) {
            allPermissions.forEach((permission) => {
                const isDependency = dependencyNames.includes(permission.value);

                if (!permission.checked && isDependency) {
                    permission.checked = true;
                    dependencyNames = dependencyNames.filter((dep) => dep !== permission.value);

                    const dependencies: string[] = JSON.parse(permission.dataset.b2bPermissionDependencies) || [];
                    dependencyNames.push(...dependencies);
                } else if (isDependency) {
                    // remove dependency which already has checkmark to prevent circular infinite loop
                    dependencyNames = dependencyNames.filter((dep) => dep !== permission.value);
                }
            });
        }
    }

    private _uncheckAllPermissionsMissingDependenciesNested(allPermissions: NodeListOf<HTMLInputElement>): void {
        let checked = Array.from(allPermissions).flatMap((permission) => {
            if (!permission.checked) {
                return [];
            }

            return [permission.value];
        });

        let checkedStateChange = true;
        while (checkedStateChange) {
            checkedStateChange = false;
            allPermissions.forEach((permission) => {
                if (!permission.checked) {
                    return;
                }

                const dependencies: string[] = JSON.parse(permission.dataset.b2bPermissionDependencies) || [];
                const missingDep = dependencies.some((dep) => !checked.includes(dep));

                if (missingDep) {
                    permission.checked = false;
                    checked = checked.filter((permissionName) => permissionName !== permission.value);
                    checkedStateChange = true; // Another iteration is needed to verify all dependencies
                }
            });
        }
    }

    private _updateGroupChecked(permissionGroup: HTMLInputElement, permissionsOfGroup: NodeListOf<HTMLInputElement>): void {
        const isAllPermissionsChecked = Array.from(permissionsOfGroup)
            .every((permission: HTMLInputElement) => permission.checked);
        const hasAtLeastOneChecked = Array.from(permissionsOfGroup)
            .some((permission: HTMLInputElement) => permission.checked);

        if (isAllPermissionsChecked) {
            permissionGroup.indeterminate = false;
            permissionGroup.checked = true;

            return;
        }

        if (hasAtLeastOneChecked) {
            permissionGroup.indeterminate = true;
            permissionGroup.checked = false;

            return;
        }

        permissionGroup.indeterminate = false;
        permissionGroup.checked = false;
    }

    private _getPermissionsOfGroup(permissionGroupName: string): NodeListOf<HTMLInputElement> {
        return DomAccess.querySelectorAll(
            this.el,
            `${this.options.permissionSelector}[data-b2b-permission-group="${permissionGroupName}"]`
        );
    }
}
