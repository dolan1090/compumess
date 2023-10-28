/*
 * @package inventory
 */

import template from './sw-product-basic-form.html.twig';
import './sw-product-basic-form.scss';

const { mapState } = Shopware.Component.getComponentHelper();

Shopware.Component.override('sw-product-basic-form', {
    template,

    inject: [
        'acl',
    ],

    data(): {
        displayProductGeneration: boolean,
    } {
        return {
            displayProductGeneration: false,
        };
    },

    computed: {
        ...mapState('swProductDetail', [
            'product',
            'parentProduct',
        ]),

        isDisabled(): boolean {
            return !this.acl.can('product.editor') || this.getProductName === '';
        },

        getProductName(): string {
            return this.product?.name ||
                this.product.translated?.name ||
                this.parentProduct?.name ||
                this.parentProduct?.translated?.name ||
                '';
        }
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        onModalProductGenerationOpen(): void {
            this.displayProductGeneration = true;
        },

        onProductGenerationModalClose(): void {
            this.displayProductGeneration = false;
        },
    }
});
