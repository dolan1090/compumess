/**
 * @package buyers-experience
 */
interface StorefrontWindow extends Window {
    PluginManager: {
        register: (pluginName: string, plugin: any, selector: string|NodeList|HTMLElement) => any;
    };
}

declare interface NodeModule {
    hot: {
        accept(path?: () => void, callback?: () => void): void
    }
}

declare module "src/plugin-system/plugin.class" {
    const Plugin: any;
    export = Plugin;
}

declare module "src/helper/feature.helper" {
    const Feature: any;
    export = Feature;
}
