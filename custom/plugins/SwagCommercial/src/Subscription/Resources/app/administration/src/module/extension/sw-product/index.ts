/**
 * @package checkout
 *
 * @private
 */
import { componentRegister, componentOverride } from '../../../helper/license.helper';

componentRegister('sw-product-subscription-card', () => import('./component/sw-product-subscription-card'));

componentOverride('sw-product-detail-base', () => import('./view/sw-product-detail-base'));
componentOverride('sw-product-detail', () => import('./page/sw-product-detail'));
