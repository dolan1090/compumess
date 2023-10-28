import B2bDeleter from './plugin/b2b-deleter.plugin';
import B2bEmployeeDeactivator from './plugin/b2b-employee-deactivator.plugin';
import B2bRolePermissionTree from './plugin/b2b-role-permission-tree.plugin';
import B2bRestoreSwitch from './plugin/b2b-restore-switch.plugin';

interface StorefrontWindow extends Window {
    PluginManager: {
        register: (pluginName: string, plugin: any, selector: string) => void;
    };
}

declare var window: StorefrontWindow;

const PluginManager = window.PluginManager;

PluginManager.register('B2bDeleter', B2bDeleter, '[data-b2b-deleter]');
PluginManager.register('B2bEmployeeDeactivator', B2bEmployeeDeactivator, '[data-b2b-employee-deactivator]');
PluginManager.register('B2bRolePermissionTree', B2bRolePermissionTree, '[data-b2b-role-permission-tree]');
PluginManager.register('B2bRestoreSwitch', B2bRestoreSwitch, '[data-b2b-restore-switch]');
