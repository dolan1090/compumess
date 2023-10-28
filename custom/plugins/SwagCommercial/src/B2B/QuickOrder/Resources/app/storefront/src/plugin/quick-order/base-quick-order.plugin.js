import Plugin from 'src/plugin-system/plugin.class';

/**
 * @package checkout
 */

export default class B2bBaseQuickOrderPlugin extends Plugin {
    init() {
        this.products = [];
        this.notFoundProducts = [];
        this.page = 1;
        this.limit = 50;
        this.duplicatedItem = '';
    }

    load() {
        document.$emitter.publish('QuickOrder/onProductsLoaded', {
            products: this.getProducts(),
        });
    }

    addProduct(product) {
        const index = this.products.findIndex(item => item.id === product.id);

        if (index < 0) {
            this.updateDuplicatedItem('');
            this.products = [ product, ...this.products];
            this.page = 1;

            this.load();
            return;
        }

        // Merge quantity if there is a similar item added to the list
        const newQuantity = parseInt(product.quantity) + parseInt(this.products[index].quantity);

        this.updateDuplicatedItem(`${product.productNumber} - ${product.name}`);

        this.products = [
            ...this.products.slice(0, index),
            {
                ...this.products[index],
                quantity: newQuantity,
            },
            ...this.products.slice(index + 1)
        ];

        this.load();
    }

    updateProduct(product) {
        const productId = product.oldId ?? product.id;
        const index = this.products.findIndex(item => item.id === productId);

        if (index < 0) {
            return;
        }

        this.products = [
            ...this.products.slice(0, index),
            {
                ...this.products[index],
                ...product
            },
            ...this.products.slice(index + 1)
        ];

        if (this.duplicatedItem) {
            this.updateDuplicatedItem('');
        }

        this.load();
    }

    removeProduct(productId) {
        const index = this.products.findIndex(item => item.id === productId);
        this.products = [...this.products.slice(0, index), ...this.products.slice(index + 1)];

        if (this.duplicatedItem) {
            this.updateDuplicatedItem('');
        }

        if (this.products.length === 0) {
            this.updateNotFoundProducts([]);
        }

        this.load();
    }

    resetProductList() {
        this.products = [];
        this.page = 1;
        this.limit = 50;
        this.notFoundProducts = [];
        this.duplicatedItem = '';

        this.load();
    }

    updateNotFoundProducts(notFoundProducts) {
        this.notFoundProducts = notFoundProducts;

        this.$emitter.publish('QuickOrder/updateNotFoundProducts', {
            notFoundProducts
        });
    }

    updateDuplicatedItem(duplicatedItem) {
        this.duplicatedItem = duplicatedItem;

        this.$emitter.publish('QuickOrder/updateDuplicatedItem', {
            duplicatedItem
        });
    }

    updatePagination(page) {
        this.page = page;

        this.$emitter.publish('QuickOrder/onPageChanged');
        this.load();
    }

    handleUploadCSVItems(response) {
        this.updateNotFoundProducts(response.errorProducts);

        const result = this._getProductsFromQuantityMap(response);

        if (result && !result.length) {
            return;
        }

        this.products = response.option === 'add'
            ? this._mergeItems(this.products, result)
            : result;

        this.load();
    }

    _getProductsFromQuantityMap(response) {
        const { products, quantityMapping } = response;
        const result = [];

        products.forEach(product => {
            const { productNumber, name, id, minPurchase, calculatedMaxPurchase, purchaseSteps, childCount } = product;
            let quantity = quantityMapping && quantityMapping[productNumber]
                ? quantityMapping[productNumber]
                : product.quantity;

            if (quantity > calculatedMaxPurchase) {
                quantity = calculatedMaxPurchase;
            }

            if (quantity < minPurchase) {
                quantity = minPurchase;
            }

            let productName = name;
            let variant = '';

            if (product?.variation?.length) {
                variant = product.variation.map((item) => {
                    return `${item.group}: ${item.option}`;
                }).join(' | ');

                productName = `${productName} (${variant})`
            }

            if (quantity !== undefined && childCount === 0) {
                result.push({
                    id,
                    productNumber,
                    name: productName,
                    quantity,
                    calculatedMaxPurchase,
                    minPurchase,
                    purchaseSteps,
                });
            }
        });

        return result;
    }

    _mergeItems(csvProducts, currentProducts = []) {
        const mergeItems = [...currentProducts, ...csvProducts];
        const uniqueObj = {};

        mergeItems.forEach((item) => {
            if (!uniqueObj[item.id]) {
                uniqueObj[item.id] = item;
            }
        });

        return Object.values(uniqueObj);
    }

    getProducts() {
        if (!this.limit) {
            return this.products;
        }

        const startIndex = (this.page - 1) * this.limit;
        const endIndex = startIndex + this.limit;

        return this.products.slice(startIndex, endIndex);
    }

    getTotalPages() {
        if (!this.limit || !this.products.length) {
            return 1;
        }

        return Math.ceil(this.products.length / this.limit);
    }
}
