const { Component } = Shopware;
const utils = Shopware.Utils;

import template from './acris-customer-price-create.html.twig';

Component.extend('acris-customer-price-create', 'acris-customer-price-detail', {
    template,

    beforeRouteEnter(to, from, next) {
        if (to.name.includes('acris.customer.price.create') && !to.params.id) {
            to.params.id = utils.createId();
            to.params.newItem = true;
        }

        next();
    },

    methods: {
        getEntity() {
            this.item = this.repository.create(Shopware.Context.api);
            this.item.listPriceType = 'replace';
            this.item.active = true;
        },

        createdComponent() {
            this.$super('createdComponent');
        },

        saveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({ name: 'acris.customer.price.detail', params: { id: this.item.id } });
        },

        onClickSave() {
            this.isLoading = true;
            const titleSaveError = this.$tc('acris-customer-price.detail.titleSaveError');
            const messageSaveError = this.$tc('acris-customer-price.detail.messageSaveError');
            const titleSaveSuccess = this.$tc('acris-customer-price.detail.titleSaveSuccess');
            const messageSaveSuccess = this.$tc('acris-customer-price.detail.messageSaveSuccess');

            this.item.acrisPrices.forEach((advancedPrice) => {
                if (advancedPrice.price) {
                    advancedPrice.price.forEach((price) => {
                        if (price.listPrice && (price.listPrice.net <= 0 || price.listPrice.gross <= 0)) {
                            price.listPrice = null;
                        }
                    });
                }
            });

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.createNotificationSuccess({
                        title: titleSaveSuccess,
                        message: messageSaveSuccess
                    });
                    this.$router.push({ name: 'acris.customer.price.detail', params: { id: this.item.id } });
                }).catch(() => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: titleSaveError,
                        message: messageSaveError
                    });
                });
        }
    }
});
