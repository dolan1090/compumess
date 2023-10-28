import PluginManager from 'src/plugin-system/plugin.manager';

import B2bBaseQuickOrderPlugin from './plugin/quick-order/base-quick-order.plugin';
import B2bQuickOrderListPlugin from './plugin/quick-order/listing.plugin';
import B2bQuickOrderItemPlugin from './plugin/quick-order/list-item.plugin';
import B2bQuickOrderUploadModalPlugin from './plugin/quick-order/file-upload-modal.plugin';
import B2bQuickOrderPaginationPlugin from './plugin/quick-order/list-pagination.plugin';

// Register your plugin via the existing PluginManager
PluginManager.register('B2bBaseQuickOrder', B2bBaseQuickOrderPlugin, '[data-b2b-base-quick-order]');
PluginManager.register('B2bQuickOrderList', B2bQuickOrderListPlugin, '[data-b2b-quick-order-list]');
PluginManager.register('B2bQuickOrderItem', B2bQuickOrderItemPlugin, '[data-b2b-quick-order-item]');
PluginManager.register('B2bQuickOrderUploadModal', B2bQuickOrderUploadModalPlugin, '[data-b2b-quick-order-upload-modal]');
PluginManager.register('B2bQuickOrderPagination', B2bQuickOrderPaginationPlugin, '[data-b2b-quick-order-pagination]');
