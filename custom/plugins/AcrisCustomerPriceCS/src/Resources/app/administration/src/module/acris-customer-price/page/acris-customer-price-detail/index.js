const { Component } = Shopware;
const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

import template from './acris-customer-price-detail.html.twig';
import './acris-customer-price-detail.scss';

Component.register('acris-customer-price-detail', {
    template,

    inject: ['repositoryFactory', 'context'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            item: null,
            isLoading: false,
            processSuccess: false,
            repository: null,
            isSaveSuccessful: false,
            currencies: [],
            taxes: [],
            defaultCustomCurrency: null,
            defaultCustomPrice: null,
            rules: [],
            totalRules: 0,
            isInherited: false,
            showListPrices: {},
            customerPriceExist: false
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        customerAdvancedPriceRepository() {
            if (this.item && this.item.acrisPrices) {
                return this.repositoryFactory.create(
                    this.item.acrisPrices.entity,
                    this.item.acrisPrices.source,
                );
            }
            return null;
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        currencyRepository() {
            return this.repositoryFactory.create('currency');
        },

        taxRepository() {
            return this.repositoryFactory.create('tax');
        },

        taxCriteria() {
            const criteria = new Criteria(1, 500);
            criteria.addSorting(Criteria.sort('position'));

            return criteria;
        },

        ruleRepository() {
            return this.repositoryFactory.create('rule');
        },

        priceRuleGroupsExists() {
            return Object.values(this.priceGroups).length > 0;
        },

        canAddPriceRule() {
            const usedRules = Object.keys(this.priceGroups).length;
            const availableRules = this.rules.length;

            return usedRules !== availableRules;
        },

        emptyPriceRuleExists() {
            return typeof this.priceGroups.null !== 'undefined';
        },

        currencyColumns() {
            this.sortCurrencies();

            return this.currencies.map((currency) => {
                return {
                    property: `price-${currency.isoCode}`,
                    label: currency.translated.name || currency.name,
                    visible: true,
                    allowResize: true,
                    primary: false,
                    rawData: false,
                    width: '250px',
                    multiLine: true,
                };
            });
        },

        pricesColumns() {
            const priceColumns = [
                {
                    property: 'quantityStart',
                    label: 'sw-product.advancedPrices.columnFrom',
                    visible: true,
                    allowResize: true,
                    primary: true,
                    rawData: false,
                    width: '95px',
                }, {
                    property: 'quantityEnd',
                    label: 'sw-product.advancedPrices.columnTo',
                    visible: true,
                    allowResize: true,
                    primary: true,
                    rawData: false,
                    width: '95px',
                },
                {
                    property: 'type',
                    label: 'sw-product.advancedPrices.columnType',
                    visible: true,
                    allowResize: true,
                    width: '250px',
                    multiLine: true,
                },
            ];

            return [...priceColumns, ...this.currencyColumns];
        },

        productTaxRate() {
            if (!this.taxes) {
                return {};
            }

            if (this.item && this.item.product && this.item.product.taxId) {
                return this.taxes.find((tax) => tax.id === this.item.product.taxId);
            }

            return this.taxes[0];
        },

        customerPriceCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('product');
            criteria.addAssociation('customer');
            criteria.addAssociation('rules');
            criteria.addAssociation('acrisPrices');

            return criteria;
        },

        productSelectContext() {
            return {
                ...Shopware.Context.api,
                inheritance: true,
            };
        },

        productCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('options.group');

            return criteria;
        },

        priceGroups() {
            const priceGroups = {};

            if (!this.item.acrisPrices) {
                return priceGroups;
            }

            const prices = this.item.acrisPrices;

            prices.forEach((price) => {
                if (!priceGroups[price.customerPriceId]) {
                    priceGroups[price.customerPriceId] = {
                        customerPriceId: price.customerPriceId,
                        prices: this.findPricesByPriceId(price.customerPriceId),
                    };
                }
            });

            // Sort prices
            Object.values(priceGroups).forEach((priceRule) => {
                priceRule.prices.sort((a, b) => {
                    return a.quantityStart - b.quantityStart;
                });
            });

            return priceGroups;
        },

        listPriceTypes() {
            return [{
                label: this.$tc('acris-customer-price.detail.replaceOption'),
                value: 'replace'
            }, {
                label: this.$tc('acris-customer-price.detail.ifEmptyUseOriginalOption'),
                value: 'ifEmptyUseOriginal'
            }, {
                label: this.$tc('acris-customer-price.detail.ifBothEmptyUseNormalPriceOption'),
                value: 'ifBothEmptyUseNormalPrice'
            }];
        },
    },

    methods: {
        createdComponent(){
            this.repository = this.repositoryFactory.create('acris_customer_price');
            this.loadAll();
        },

        getEntity() {
            this.repository
                .get(this.$route.params.id, Shopware.Context.api, this.customerPriceCriteria)
                .then((entity) => {
                    this.item = entity;
                    if (this.item.product) {
                        this.defaultCustomPrice = this.getDefaultCustomPrice(this.item.product);
                        if (this.item.product.parentId) {
                            this.productRepository.get(this.item.product.parentId, Shopware.Context.api)
                                .then((parentProduct) => {
                                    this.item.product.taxId = parentProduct.taxId;
                                });
                        }
                        if (this.item.acrisPrices.length <= 0) {
                            this.onAddNewPriceGroup();
                        }
                    }
                });
        },

        loadAll() {
            return Promise.all([
                this.getEntity(),
                this.loadCurrencies(),
                this.loadTaxes()
            ]);
        },

        loadCurrencies() {
            return this.currencyRepository.search(new Criteria(1, 500)).then((res) => {
                this.currencies = res;
                this.defaultCustomCurrency = this.defaultCurrency();
            });
        },

        loadTaxes() {
            return this.taxRepository.search(this.taxCriteria).then((res) => {
                this.taxes = res;
            });
        },

        defaultCurrency() {
            return this.currencies.find(currency => currency.isSystemDefault);
        },

        getDefaultCustomPrice(product) {
            if (product && product.price && this.defaultCustomCurrency) {
                let productPrice = product.price;

                // get default price bases on currency
                return productPrice.find((price) => {
                    return price.currencyId === this.defaultCustomCurrency.id;
                });
            } else {
                return null;
            }
        },

        onClickSave() {
            this.isLoading = true;
            const titleSaveError = this.$tc('acris-customer-price.detail.titleSaveError');
            const messageSaveError = this.$tc('acris-customer-price.detail.messageSaveError');
            const titleSaveSuccess = this.$tc('acris-customer-price.detail.titleSaveSuccess');
            const messageSaveSuccess = this.$tc('acris-customer-price.detail.messageSaveSuccess');

            this.isSaveSuccessful = false;
            this.isLoading = true;

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
                    this.loadAll();
                    this.isLoading = false;
                    this.processSuccess = true;
                    this.createNotificationSuccess({
                        title: titleSaveSuccess,
                        message: messageSaveSuccess
                    });
                }).catch(() => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: titleSaveError,
                        message: messageSaveError
                });
            });
        },

        mountedComponent() {
            const ruleCriteria = new Criteria(1, 500);
            ruleCriteria.addFilter(
                Criteria.multi('OR', [
                    Criteria.contains('rule.moduleTypes.types', 'price'),
                    Criteria.equals('rule.moduleTypes', null),
                ]),
            );

            Shopware.State.commit('swProductDetail/setLoading', ['rules', true]);
            this.ruleRepository.search(ruleCriteria).then((res) => {
                this.rules = res;
                this.totalRules = res.total;

                Shopware.State.commit('swProductDetail/setLoading', ['rules', false]);
            });

            this.isInherited = this.isChild && !this.item.acrisPrices.total;
        },

        sortCurrencies() {
            this.currencies.sort((a, b) => {
                if (a.isSystemDefault) {
                    return -1;
                }
                if (b.isSystemDefault) {
                    return 1;
                }
                if (a.translated.name < b.translated.name) {
                    return -1;
                }
                if (a.translated.name > b.translated.name) {
                    return 1;
                }
                return 0;
            });
        },

        onRuleChange(value, ruleId) {
            this.item.acrisPrices.forEach((priceRule) => {
                if (priceRule.ruleId === ruleId) {
                    priceRule.ruleId = value;
                }
            });
        },

        onAddNewPriceGroup() {
            if (!this.defaultCustomPrice) return;
            const newPrice = this.customerAdvancedPriceRepository.create();

            newPrice.customerPriceId = this.item.id;
            newPrice.quantityStart = 1;
            newPrice.quantityEnd = null;
            newPrice.currencyId = this.defaultCustomCurrency.id;
            newPrice.price = [{
                currencyId: this.defaultCustomCurrency.id,
                gross: this.defaultCustomPrice.gross,
                linked: this.defaultCustomPrice.linked,
                net: this.defaultCustomPrice.net,
                listPrice: null,
            }];

            if (this.defaultCustomPrice.listPrice) {
                newPrice.price[0].listPrice = {
                    currencyId: this.defaultCustomCurrency.id,
                    gross: this.defaultCustomPrice.listPrice.gross,
                    linked: this.defaultCustomPrice.listPrice.linked,
                    net: this.defaultCustomPrice.listPrice.net,
                };
            }

            this.item.acrisPrices.add(newPrice);

            this.$nextTick(() => {
                const scrollableArea = this.$parent.$el.children.item(0);

                if (scrollableArea) {
                    scrollableArea.scrollTo({
                        top: scrollableArea.scrollHeight,
                        behavior: 'smooth',
                    });
                }
            });
        },

        onPriceGroupDelete(ruleId) {
            const allPriceRules = this.item.acrisPrices.map(priceRule => {
                return { id: priceRule.id, ruleId: priceRule.ruleId };
            });

            allPriceRules.forEach((priceRule) => {
                if (ruleId !== priceRule.ruleId) {
                    return;
                }

                this.item.acrisPrices.remove(priceRule.id);
            });
        },

        onPriceGroupDuplicate(priceGroup) {
            if (typeof this.priceGroups.null !== 'undefined') {
                return;
            }

            // duplicate each price rule
            priceGroup.prices.forEach((price) => {
                this.duplicatePriceRule(price, null);
            });
        },

        onPriceRuleDelete(priceRule) {
            // get the priceRuleGroup for the priceRule
            const matchingPriceRuleGroup = this.priceGroups[priceRule.customerPriceId];

            // if it is the only item in the priceRuleGroup
            if (matchingPriceRuleGroup.prices.length <= 1) {
                this.createNotificationError({
                    message: this.$tc('sw-product.advancedPrices.deletionNotPossibleMessage'),
                });

                return;
            }

            // get actual rule index
            const actualRuleIndex = matchingPriceRuleGroup.prices.indexOf(priceRule);

            // if it is the last item
            if (typeof priceRule.quantityEnd === 'undefined' || priceRule.quantityEnd === null) {
                // get previous rule
                const previousRule = matchingPriceRuleGroup.prices[actualRuleIndex - 1];

                // set the quantityEnd from the previous rule to null
                previousRule.quantityEnd = null;
            } else {
                // get next rule
                const nextRule = matchingPriceRuleGroup.prices[actualRuleIndex + 1];

                // set the quantityStart from the next rule to the quantityStart from the actual rule
                nextRule.quantityStart = priceRule.quantityStart;
            }

            // delete rule
            this.item.acrisPrices.remove(priceRule.id);
        },

        onInheritanceRestore(rule, currency) {
            // remove price from rule.price with the currency id
            const indexOfPrice = rule.price.findIndex((price) => price.currencyId === currency.id);
            this.$delete(rule.price, indexOfPrice);
        },

        onInheritanceRemove(rule, currency) {
            // create new price based on the default price
            const defaultCustomPrice = this.findDefaultCustomPriceOfRule(rule);
            const newPrice = {
                currencyId: currency.id,
                gross: this.convertPrice(defaultCustomPrice.gross, currency),
                linked: defaultCustomPrice.linked,
                net: this.convertPrice(defaultCustomPrice.net, currency),
                listPrice: null,
            };

            if (defaultCustomPrice.listPrice) {
                newPrice.listPrice = {
                    currencyId: currency.id,
                    gross: this.convertPrice(defaultCustomPrice.listPrice.gross, currency),
                    linked: defaultCustomPrice.listPrice.linked,
                    net: this.convertPrice(defaultCustomPrice.listPrice.net, currency),
                };
            }

            // add price to rule.price
            this.$set(rule.price, rule.price.length, newPrice);
        },

        isPriceFieldInherited(rule, currency) {
            return rule.price.findIndex((price) => price.currencyId === currency.id) < 0;
        },

        convertPrice(value, currency) {
            const calculatedPrice = value * currency.factor;
            const priceRounded = calculatedPrice.toFixed(currency.decimalPrecision);
            return Number(priceRounded);
        },

        findRuleById(ruleId) {
            return this.rules.find((rule) => {
                return rule.id === ruleId;
            });
        },

        findPricesByPriceId(priceId) {
            return this.item.acrisPrices.filter((item) => {
                return item.customerPriceId === priceId;
            });
        },

        findDefaultCustomPriceOfRule(rule) {
            return rule.price.find((price) => price.currencyId === this.defaultCustomCurrency.id);
        },

        onQuantityEndChange(price, priceGroup) {
            // when not last price
            if (priceGroup.prices.indexOf(price) + 1 !== priceGroup.prices.length) {
                return;
            }

            this.createPriceRule(priceGroup);
        },

        createPriceRule(priceGroup) {
            // create new price rule
            const newPrice = this.customerAdvancedPriceRepository.create();
            newPrice.customerPriceId = this.item.id;

            const highestEndValue = Math.max(...priceGroup.prices.map((price) => price.quantityEnd));
            newPrice.quantityStart = highestEndValue + 1;

            newPrice.price = [{
                currencyId: this.defaultCustomCurrency.id,
                gross: this.defaultCustomPrice.gross,
                linked: this.defaultCustomPrice.linked,
                net: this.defaultCustomPrice.net,
                listPrice: null,
            }];

            if (this.defaultCustomPrice.listPrice) {
                newPrice.price[0].listPrice = {
                    currencyId: this.defaultCustomCurrency.id,
                    gross: this.defaultCustomPrice.listPrice ? this.defaultCustomPrice.listPrice.gross : null,
                    linked: this.defaultCustomPrice.listPrice ? this.defaultCustomPrice.listPrice.linked : true,
                    net: this.defaultCustomPrice.listPrice ? this.defaultCustomPrice.listPrice.net : null,
                };
            }

            this.item.acrisPrices.add(newPrice);
        },

        canCreatePriceRule(priceGroup) {
            const emptyPrices = priceGroup.prices.filter((price) => {
                return !price.quantityEnd;
            });

            return !!emptyPrices.length;
        },

        onProductChange(productId, product) {
            if (this.item.customerId) {
                this.checkCustomerPriceExisting(productId, this.item.customerId, this.item.id);
            }
            this.item.product = product;
            this.defaultCustomPrice = this.getDefaultCustomPrice(this.item.product);
            if (this.item.acrisPrices.length <= 0) {
                this.onAddNewPriceGroup();
            }
        },

        onCustomerChange(customerId) {
            if (this.item.productId) {
                this.checkCustomerPriceExisting(this.item.productId, customerId, this.item.id);
            }
        },

        checkCustomerPriceExisting(productId, customerId, itemId) {
            // customerPriceExist
            const criteria = new Criteria(1, 1);
            criteria.addFilter(Criteria.equals('productId', productId));
            criteria.addFilter(Criteria.equals('customerId', customerId));
            criteria.addFilter(Criteria.not('and', [Criteria.equals('id', itemId)]));
            this.repository.search(criteria, Shopware.Context.api).then((result) => {
                this.customerPriceExist = result.total > 0;
            });
        },

        duplicatePriceRule(referencePrice, ruleId = null) {
            const newPrice = this.customerAdvancedPriceRepository.create();

            newPrice.customerPriceId = referencePrice.customerPriceId;
            newPrice.quantityEnd = referencePrice.quantityEnd;
            newPrice.quantityStart = referencePrice.quantityStart;

            // add prices
            newPrice.price = [];

            referencePrice.price.forEach((price, index) => {
                this.$set(newPrice.price, index, { ...price });
            });

            this.item.acrisPrices.add(newPrice);
        },

        getPriceRuleGroupClass(number) {
            return [
                `context-price-group-${number}`,
            ];
        },

        restoreInheritance() {
            this.isInherited = true;
        },

        removeInheritance() {
            this.isInherited = false;
        },

        onChangeShowListPrices(value, customerPriceId) {
            this.$set(this.showListPrices, customerPriceId, value);
        },

        getStartQuantityTooltip(itemIndex, quantity) {
            return {
                message: this.$tc('sw-product.advancedPrices.advancedPriceDisabledTooltip'),
                width: 275,
                showDelay: 200,
                disabled: (itemIndex !== 0 || quantity !== 1),
            };
        },

        findDefaultPriceOfRule(rule) {
            return rule.price.find((price) => price.currencyId === this.defaultCustomCurrency.id);
        },

        saveFinish() {
            this.processSuccess = false;
        }
    }
});
