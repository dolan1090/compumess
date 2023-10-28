/**
 * @package inventory
 */

import template from './sw-settings-translator-index.html.twig';

const { Mixin } = Shopware;

if (Shopware.License.get('REVIEW_TRANSLATOR-1649854')) {
    Shopware.Component.register('sw-settings-translator-index', {
        template,

        data() {
            return {
                isLoading: false,
                isSaveSuccessful: false,
            };
        },
        metaInfo() {
            return {
                title: this.$createTitle(),
            };
        },
        mixins: [
            Mixin.getByName('notification'),
        ],
        methods: {
            saveFinish() {
                this.isSaveSuccessful = false;
            },

            onSave() {
                this.isSaveSuccessful = false;
                this.isLoading = true;

                this.$refs.systemConfig.saveAll().then(() => {
                    this.isLoading = false;
                    this.isSaveSuccessful = true;
                }).catch((err) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        message: err,
                    });
                });
            },

            onLoadingChanged(loading) {
                this.isLoading = loading;
            },
        },
    });
}
