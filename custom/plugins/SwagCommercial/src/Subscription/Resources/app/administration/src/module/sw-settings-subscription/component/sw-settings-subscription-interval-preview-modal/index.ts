import template from './sw-settings-subscription-interval-preview-modal.html.twig';
import './sw-settings-subscription-interval-preview-modal.scss';

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    props: {
        dates: {
            required: true,
            type: Array<string>,
        },
    },
});
