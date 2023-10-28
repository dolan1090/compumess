import template from './sw-cms-list-item.html.twig';
import './sw-cms-list-item.scss';

export default Shopware.Component.wrapComponentConfig({
    template,

    computed: {
        isDraft() {
            return !!this.page.draftVersion;
        },

        hasDrafts() {
            if (!this.page.extensions || !this.page.extensions.drafts) {
                return false;
            }

            return this.page.extensions.drafts.length;
        },

        hasActivities() {
            if (!this.page.extensions || !this.page.extensions.activities) {
                return false;
            }

            return this.page.extensions.activities.length;
        }
    },

    methods: {
        onDraftsClick() {
            this.$emit('showDraftsModal', this.page);
        }
    }
});
