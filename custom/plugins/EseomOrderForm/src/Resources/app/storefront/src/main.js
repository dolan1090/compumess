// Import all necessary Storefront plugins and scss files
import EseomOrderForm from './order-form/order-form';

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('EseomOrderForm', EseomOrderForm, '[data-eseom-order-form]');
