const {Component} = Shopware;
import customerPriceState from './state';
import template from './acris-customer-prices-customer-tab.html.twig';

const {Criteria} = Shopware.Data;

Component.register('acris-customer-prices-customer-tab', {
    template,

    inject: ['repositoryFactory', 'acl'],

    props: {
        customer: {
            type: Object,
            required: true,
        },

        customerEditMode: {
            type: Boolean,
            required: true,
            default: false,
        },

        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        }
    },

    data() {
        return {
            customerPrices: null,
            showAddCustomerPriceModal: false,
            showEditCustomerPriceModal: false,
            showDeleteCustomerPriceModal: false,
            customerPriceSortProperty: null,
            customerPriceSortDirection: '',
            currentCustomerPrice: null,
            helpTextCustomerPrice: this.$tc('acris-customer-prices-customer-tab.fieldTitleHelpTextCustomerPrice'),
            isProductPage: false
        };
    },

    beforeCreate() {
        Shopware.State.registerModule('customerPriceStateCustomer', customerPriceState);
    },

    created() {
        this.createdComponent();
    },

    computed: {
        customerPriceRepository() {
            return this.repositoryFactory.create('acris_customer_price');
        },

        productVariantContext() {
            return {
                ...Shopware.Context.api,
                inheritance: true,
            };
        },

        customerPriceColumns() {
            return this.getCustomerPriceColumns();
        },

        customerPriceExist() {
            return Shopware.State.get('customerPriceStateCustomer').customerPriceExist;
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        }
    },

    methods: {
        getProductInfo(item) {
            const customerPrice = item;
            if (customerPrice && customerPrice.product && customerPrice.product.parentId) {
                this.productRepository.get(customerPrice.product.parentId, Shopware.Context.api)
                    .then((parentProduct) => {
                        customerPrice.product.name = parentProduct.name;
                        customerPrice.product.translated.name = parentProduct.translated.name;
                    });
            }
        },

        createdComponent() {
            this.isLoading = true;

            this.refreshList();
        },


        getCustomerPriceColumns() {
            return [{
                property: 'product.productNumber',
                label: 'acris-customer-price.list.columnProductNumber',
            }, {
                property: 'product',
                label: 'acris-customer-price.list.columnProduct',
            }, {
                property: 'acrisPrices',
                label: 'acris-customer-price.list.columnListPrice',
            }, {
                property: 'rules',
                label: 'acris-customer-price.list.columnRules',
            }, {
                property: 'active',
                label: 'acris-customer-price.list.columnActive',
                width: '80px',
                align: 'center'
            }];
        },

        onChange(term) {
            this.customerPrices.criteria.setPage(1);
            this.customerPrices.criteria.setTerm(term);

            this.refreshList();
        },

        refreshList() {
            let criteria = new Criteria(1, 25);

            if (!this.customerPrices || !this.customerPrices.criteria) {
                criteria.addFilter(Criteria.equals('customerId', this.customer.id));
            } else {
               criteria = this.customerPrices.criteria;
            }
            criteria.addAssociation('product')
                .addAssociation('product.options.group')
                .addAssociation('acrisPrices')
                .addAssociation('rules');

            this.customerPriceRepository.search(criteria).then((prices) => {
                this.customerPrices = prices;
                this.customer.extensions.acrisCustomerPrice = prices;
                if (this.customer && this.customerPrices && this.customerPrices.total > 0) {
                    this.setVariantProductNames();
                }
                this.isLoading = false;
            });
        },

        setVariantProductNames() {
            this.customerPrices.forEach((customerPrice) => {
                if (customerPrice.product && customerPrice.product.parentId) {
                    this.productRepository.get(customerPrice.product.parentId, Shopware.Context.api)
                        .then((parentProduct) => {
                            customerPrice.product.name = parentProduct.name;
                            customerPrice.product.translated.name = parentProduct.translated.name;
                        });
                }
            })
        },

        setCustomerPriceSorting(column) {
            this.customerPriceSortProperty = column.property;

            let direction = 'ASC';
            if (this.customerPriceSortProperty === column.dataIndex) {
                if (this.customerPriceSortDirection === direction) {
                    direction = 'DESC';
                }
            }
            this.customerPriceSortProperty = column.dataIndex;
            this.customerPriceSortDirection = direction;
        },

        onCreateNewCustomerPrice() {
            this.showAddCustomerPriceModal = true;
            this.createNewCustomerPrice();
        },

        createNewCustomerPrice() {
            const newCustomerPrice = this.customerPriceRepository.create(Shopware.Context.api);
            newCustomerPrice.customerId = this.customer.id;
            newCustomerPrice.listPriceType = 'replace';
            newCustomerPrice.active = true;

            this.currentCustomerPrice = newCustomerPrice;
        },

        onSaveCustomerPrice() {
            if (this.currentCustomerPrice === null) {
                return;
            }

            this.currentCustomerPrice.acrisPrices.forEach((advancedPrice) => {
                if (advancedPrice.price) {
                    advancedPrice.price.forEach((price) => {
                        if (price.listPrice && (price.listPrice.net <= 0 || price.listPrice.gross <= 0)) {
                            price.listPrice = null;
                        }
                    });
                }
            });

            let customerPrice = this.customerPrices.get(this.currentCustomerPrice.id);

            if (typeof customerPrice === 'undefined' || customerPrice === null) {
                customerPrice = this.customerPriceRepository.create(Shopware.Context.api, this.currentCustomerPrice.id);
            }

            Object.assign(customerPrice, this.currentCustomerPrice);

            if (!this.customerPrices.has(customerPrice.id)) {
                this.customerPrices.push(customerPrice);

            }
            this.customer.extensions.acrisCustomerPrice = this.customerPrices;
            this.currentCustomerPrice = null;
            this.$refs.customerPriceGrid.applyResult(this.customer.extensions.acrisCustomerPrice);
        },

        onCloseCustomerPriceModal() {
            if (this.$route.query.hasOwnProperty('detailId')) {
                this.$route.query.detailId = null;
            }

            this.currentCustomerPrice = null;
        },

        onEditCustomerPrice(item) {
            this.currentCustomerPrice = item;
            this.showEditCustomerPriceModal = item.id;
        },

        onDeleteCustomerPrice(id) {
            this.showDeleteCustomerPriceModal = id;
        },

        onConfirmDeleteCustomerPrice(id) {
            this.onCloseDeleteCustomerPriceModal();

            this.customer.extensions.acrisCustomerPrice = this.customerPrices;

            this.$nextTick(() => {
                this.customer.extensions.acrisCustomerPrice.remove(id);
            });

            this.$refs.customerPriceGrid.applyResult(this.customer.extensions.acrisCustomerPrice);
            this.customerPriceRepository.delete(id, Shopware.Context.api);
        },

        onCloseDeleteCustomerPriceModal() {
            this.showDeleteCustomerPriceModal = false;
        }
    }
});
