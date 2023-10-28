import template from './swag-customized-products-option-list.html.twig';
import './swag-customized-products-option-list.scss';

const { Component } = Shopware;

Component.extend('swag-customized-products-option-list', 'sw-one-to-many-grid', {
    template,
});
