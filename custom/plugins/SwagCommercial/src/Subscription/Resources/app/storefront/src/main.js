// Import all necessary Storefront plugins
import SubscriptionProductBox from './plugin/subscription-product-box.plugin';

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register(
    'SubscriptionProductBox',
    SubscriptionProductBox,
    '[data-subscription-product-box]',
);
