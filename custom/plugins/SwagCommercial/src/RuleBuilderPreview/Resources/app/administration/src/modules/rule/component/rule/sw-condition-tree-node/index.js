import template from './sw-condition-tree-node.html.twig';
import './sw-condition-tree-node.scss';

const { Component } = Shopware;

Component.override('sw-condition-tree-node', {
    template,
});
