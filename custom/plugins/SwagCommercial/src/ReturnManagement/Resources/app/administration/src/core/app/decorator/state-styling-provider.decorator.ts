/**
 * @package checkout
 */
Shopware.Application.addServiceProviderDecorator('stateStyleDataProviderService', (stateStyleService) => {
    const stateMappingColor = {
        open: 'neutral',
        in_progress: 'progress',
        cancelled: 'danger',
        done: 'done',
        return_requested: 'neutral',
        returned_partially: 'warning',
        shipped_partially: 'warning',
        shipped: 'done',
        returned: 'done',
    };

    const orderReturnStates = ['open', 'in_progress', 'cancelled', 'done'];
    const orderLineItemStates = [
        'return_requested', 'open', 'returned_partially', 'shipped_partially', 'shipped', 'returned', 'cancelled'
    ];

    orderReturnStates.forEach(state => {
        stateStyleService.addStyle('order_return.state', state, {
            icon: stateMappingColor[state],
            color: stateMappingColor[state],
            variant: stateMappingColor[state],
        });
    })

    orderLineItemStates.forEach(state => {
        stateStyleService.addStyle('order_line_item.state', state, {
            icon: stateMappingColor[state],
            color: stateMappingColor[state],
            variant: stateMappingColor[state],
        });
    })

    return stateStyleService;
});
