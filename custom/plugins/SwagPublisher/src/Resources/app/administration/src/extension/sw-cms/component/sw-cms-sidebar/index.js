import template from './sw-cms-sidebar.html.twig';

export default Shopware.Component.wrapComponentConfig({
    template,
    computed: {
        isDraft() {
            return !!Shopware.State.get('sw-publisher').versionId;
        }
    }
});
