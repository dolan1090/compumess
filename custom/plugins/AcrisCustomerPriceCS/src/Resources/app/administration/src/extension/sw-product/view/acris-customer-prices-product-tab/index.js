const { Component } = Shopware;
const { mapGetters, mapState} = Shopware.Component.getComponentHelper();
import customerPriceState from './state';
import template from './acris-customer-prices-product-tab.html.twig';

const {Criteria} = Shopware.Data;

Component.register('acris-customer-prices-product-tab', {
    template,

    inject: ['repositoryFactory', 'acl'],

    props: {
        allowEdit: {
            type: Boolean,
            required: false,
            default: true
        }
    },

    data() {
        return {
            isLoading: false,
            customerPrices: null,
            showAddCustomerPriceModal: false,
            showEditCustomerPriceModal: false,
            showDeleteCustomerPriceModal: false,
            customerPriceSortProperty: null,
            customerPriceSortDirection: '',
            currentCustomerPrice: null,
            helpTextCustomerPrice: this.$tc('acris-customer-prices-product-tab.fieldTitleHelpTextCustomerPrice'),
            isProductPage: true
        };
    },

    beforeCreate() {
        Shopware.State.registerModule('customerPriceState', customerPriceState);
    },

    created() {
        this.createdComponent();
    },

    computed: {
        customerPriceRepository() {
            return this.repositoryFactory.create('acris_customer_price');
        },

        customerPriceColumns() {
            return this.getCustomerPriceColumns();
        },

        ...mapState('swProductDetail', [
            'product'
        ]),

        ...mapGetters('swProductDetail', [
            'isLoading'
        ]),

        customerPriceExist() {
            return Shopware.State.get('customerPriceState').customerPriceExist;
        },
    },

    methods: {
        getCustomerPriceColumns() {
            return [{
                property: 'customer.customerNumber',
                label: 'acris-customer-price.list.columnCustomerNumber',
            }, {
                property: 'customer.email',
                label: 'acris-customer-price.list.columnCustomerEmail',
            }, {
                property: 'customer',
                label: 'acris-customer-price.list.columnCustomer',
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

        createdComponent() {
            this.isLoading = true;

            this.refreshList();
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

        onChange(term) {
            this.customerPrices.criteria.setPage(1);
            this.customerPrices.criteria.setTerm(term);

            this.refreshList();
        },

        refreshList() {
            let criteria = new Criteria(1, 25);

            if (!this.customerPrices || !this.customerPrices.criteria) {
                criteria.addFilter(Criteria.equals('productId', this.product.id));
            } else {
                criteria = this.customerPrices.criteria;
            }
            criteria.addAssociation('customer')
                .addAssociation('acrisPrices')
                .addAssociation('rules');

            this.customerPriceRepository.search(criteria).then((prices) => {
                this.customerPrices = prices;
                this.product.extensions.acrisCustomerPrice = prices;
                this.isLoading = false;
            });
        },

        onCreateNewCustomerPrice() {
            this.showAddCustomerPriceModal = true;
            this.createNewCustomerPrice();
        },

        createNewCustomerPrice() {
            const newCustomerPrice = this.customerPriceRepository.create(Shopware.Context.api);
            newCustomerPrice.productId = this.product.id;
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
            this.product.extensions.acrisCustomerPrice = this.customerPrices;
            this.currentCustomerPrice = null;
            this.$refs.customerPriceGrid.applyResult(this.product.extensions.acrisCustomerPrice);
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

            this.product.extensions.acrisCustomerPrice = this.customerPrices;

            this.$nextTick(() => {
                this.product.extensions.acrisCustomerPrice.remove(id);
            });
            this.$refs.customerPriceGrid.applyResult(this.product.extensions.acrisCustomerPrice);
            this.customerPriceRepository.delete(id, Shopware.Context.api);
        },

        onCloseDeleteCustomerPriceModal() {
            this.showDeleteCustomerPriceModal = false;
        }
    }
});
