import template from './acris-customer-price-list.html.twig';
import './acris-customer-price-list.scss';

const {Component, Mixin} = Shopware;
const { Criteria } = Shopware.Data;

Component.register('acris-customer-price-list', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            items: null,
            isLoading: false,
            showDeleteModal: false,
            repository: null,
            total: 0
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        entityRepository() {
            return this.repositoryFactory.create('acris_customer_price');
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        customerPriceCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.term);
            criteria.addAssociation('product');
            criteria.addAssociation('product.options.group');
            criteria.addAssociation('customer');
            criteria.addAssociation('rules');
            criteria.addAssociation('acrisPrices');

            return criteria;
        },

        columns() {
            return this.getColumns();
        }
    },

    methods: {
        getList() {
            this.isLoading = true;

            this.entityRepository.search(this.customerPriceCriteria, Shopware.Context.api).then((items) => {
                this.total = items.total;
                this.items = items;
                this.items.forEach((item) => {
                    if (item.product && item.product.parentId) {
                        this.productRepository.get(item.product.parentId, Shopware.Context.api)
                            .then((parentProduct) => {
                                item.product.name = parentProduct.name;
                                item.product.translated.name = parentProduct.translated.name;
                            });
                    }
                });
                this.isLoading = false;

                return items;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        onDelete(id) {
            this.showDeleteModal = id;
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onEditCustomerPrice(itemId) {
            this.$router.push({ name: 'acris.customer.price.detail', params: { id: itemId } });
        },

        getColumns() {
            return [{
                property: 'product.productNumber',
                inlineEdit: 'string',
                label: 'acris-customer-price.list.columnProductNumber',
                routerLink: 'acris.customer.price.detail',
                allowResize: true,
                primary: true
            }, {
                property: 'product',
                inlineEdit: 'string',
                label: 'acris-customer-price.list.columnProduct',
                routerLink: 'acris.customer.price.detail',
                allowResize: true,
                primary: true
            }, {
                property: 'customer.customerNumber',
                inlineEdit: 'string',
                label: 'acris-customer-price.list.columnCustomerNumber',
                routerLink: 'acris.customer.price.detail',
                allowResize: true,
                primary: true
            }, {
                property: 'customer.email',
                inlineEdit: 'string',
                label: 'acris-customer-price.list.columnCustomerEmail',
                routerLink: 'acris.customer.price.detail',
                allowResize: true,
                primary: true
            }, {
                property: 'customer',
                inlineEdit: 'string',
                label: 'acris-customer-price.list.columnCustomer',
                routerLink: 'acris.customer.price.detail',
                allowResize: true,
                primary: true
            }, {
                property: 'acrisPrices',
                inlineEdit: 'string',
                label: 'acris-customer-price.list.columnListPrice',
                routerLink: 'acris.customer.price.detail',
                allowResize: true
            }, {
                property: 'rules',
                dataIndex: 'rules',
                label: 'acris-customer-price.list.columnRules',
                sortable: false,
                allowResize: true,
                multiLine: true
            }, {
                property: 'active',
                label: 'acris-customer-price.list.columnActive',
                inlineEdit: 'boolean',
                width: '80px',
                allowResize: true,
                align: 'center'
            }];
        },

        onDuplicate(referenceCustomerPrice) {
            this.entityRepository.clone(referenceCustomerPrice.id, Shopware.Context.api).then((newCustomerPrice) => {
                this.reloadEntity(referenceCustomerPrice, newCustomerPrice);
            });
        },

        reloadEntity(referenceCustomerPrice, newCustomerPrice) {
            this.entityRepository
                .get(referenceCustomerPrice.id, Shopware.Context.api, this.customerPriceCriteria)
                .then((entity) => {
                    this.item = entity;
                    this.item.rules = referenceCustomerPrice.rules;
                    this.entityRepository
                        .save(this.item, Shopware.Context.api)
                        .then(() => {
                            this.$router.push(
                                {
                                    name: 'acris.customer.price.detail',
                                    params: { id: newCustomerPrice.id }
                                }
                            );
                        });
                });
        }
    }
});

