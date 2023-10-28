const { Module } = Shopware;

Module.register('acris-customer-prices-product-tab', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.product.detail') {
            currentRoute.children.push({
                name: 'acris.customer.prices.product.tab',
                path: '/sw/product/detail/:id/customer-prices',
                component: 'acris-customer-prices-product-tab',
                meta: {
                    parentPath: "sw.product.index"
                }
            });
        }
        next(currentRoute);
    }
});
