import template from './swag-customized-products-option-type-imageupload.html.twig';
import './swag-customized-products-option-type-imageupload.scss';

const { Component } = Shopware;

Component.extend('swag-customized-products-option-type-imageupload', 'swag-customized-products-option-type-base', {
    template,

    computed: {
        extensions() {
            return [
                { value: 'jpg', label: 'JPG' },
                { value: 'png', label: 'PNG' },
                { value: 'gif', label: 'GIF' },
                { value: 'webp', label: 'WEBP' },
                { value: 'svg', label: 'SVG' },
                { value: 'bmp', label: 'BMP' },
                { value: 'tif', label: 'TIF' },
                { value: 'eps', label: 'EPS' },
            ];
        },
    },

    created() {
        if (this.option.typeProperties.maxFileSize === undefined) {
            this.$set(
                this.option.typeProperties,
                'maxFileSize',
                10,
            );
        }

        if (this.option.typeProperties.maxCount === undefined) {
            this.$set(
                this.option.typeProperties,
                'maxCount',
                1,
            );
        }

        if (this.option.typeProperties.excludedExtensions === undefined) {
            this.$set(
                this.option.typeProperties,
                'excludedExtensions',
                [],
            );
        }
    },

    methods: {
        validateInput(value) {
            return this.checkRequired(value.typeProperties.maxFileSize)
                && this.checkRequired(value.typeProperties.maxCount);
        },
    },
});
