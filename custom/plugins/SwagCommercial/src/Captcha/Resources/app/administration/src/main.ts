const { License } = Shopware;

/**
 * @package checkout
 */

if (License.get('CAPTCHA-3581792')) {
    Shopware.Component.override(
        'sw-settings-captcha-select-v2',
        () => import('./core/modules/sw-settings-basic-information/component/sw-settings-captcha-select-v2')
    );
}
