import { componentOverride } from '../../../helper/license.helper';

/**
 * @package checkout
 *
 * @private
 */
componentOverride('sw-order-list', () => import('./page/sw-order-list'));
