export default {
    type: ['htmleditor'],

    validate: ({ element, operator }) => {
        const plugin = window.PluginManager.getPluginInstanceFromElement(element, 'SwagCustomizedProductsHtmlEditor');
        return (operator === 'X' ? !plugin.isEmpty() : plugin.isEmpty());
    },
};
