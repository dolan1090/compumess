export default {
    type: ['imageupload', 'fileupload'],

    validate: ({ element, operator }) => {
        const uploadPlugin = window.PluginManager.getPluginInstanceFromElement(
            element.closest('.customized-products-upload'),
            'SwagCustomizedProductsFileUpload',
        );

        return (operator === 'X' ? uploadPlugin.registry.size > 0 : !uploadPlugin.registry.size);
    },
};
