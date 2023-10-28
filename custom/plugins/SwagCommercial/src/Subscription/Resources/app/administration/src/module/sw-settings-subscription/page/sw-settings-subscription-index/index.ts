import template from './sw-settings-subscription-index.html.twig';
import './sw-settings-subscription-index.scss';

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    data(): {
        activeItem: string|null,
        total: number,
        isLoading: boolean,
        /** @internal */
        newFeatureInfoClosed: boolean,
        /** @internal */
        newFeatureInfoLocalStorageKey: string,
        } {
        return {
            activeItem: null,
            total: 0,
            isLoading: true,
            /** @internal */
            newFeatureInfoClosed: true,
            /** @internal */
            newFeatureInfoLocalStorageKey: 'sw-commercial.subscription-feature-alert.closed',
        };
    },

    created() {
        this.createdComponent();
    },

    destroyed() {
        this.destroyedComponent();
    },

    methods: {
        createdComponent(): void {
            this.$root.$on('on-subscription-entities-loading', this.onLoading);
            this.$root.$on('on-subscription-entities-loaded', this.onLoaded);
            this.newFeatureInfoClosed = window.localStorage.getItem(this.newFeatureInfoLocalStorageKey) === 'true';
        },

        destroyedComponent(): void {
            this.$root.$off('on-subscription-entities-loading', this.onLoading);
            this.$root.$off('on-subscription-entities-loaded', this.onLoaded);
        },

        onNewItemActive(item: Vue): void {
            this.activeItem = item.$attrs.title;
        },

        onLoading(): void {
            this.isLoading = true;
        },

        onLoaded(total: number): void {
            this.total = total;
            this.isLoading = false;
        },

        /**
         * @internal
         */
        closeNewFeatureInfo(): void {
            this.newFeatureInfoClosed = true;

            window.localStorage.setItem(this.newFeatureInfoLocalStorageKey, 'true');
        },
    },
});
