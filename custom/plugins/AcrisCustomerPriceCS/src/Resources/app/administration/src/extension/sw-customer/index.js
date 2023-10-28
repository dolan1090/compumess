const { Module } = Shopware;

Module.register('acris-customer-prices-customer-tab', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.customer.detail') {
            currentRoute.children.push({
                name: 'acris.customer.prices.customer.tab',
                path: '/sw/customer/detail/:id/customer-price',
                component: 'acris-customer-prices-customer-tab',
                meta: {
                    parentPath: "sw.customer.index"
                }
            });
        }
        next(currentRoute);
    }
});
