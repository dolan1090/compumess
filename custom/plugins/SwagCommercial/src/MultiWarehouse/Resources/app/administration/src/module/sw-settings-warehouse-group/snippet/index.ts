/**
 * @package inventory
 */
import deDeSnippets from './de-DE.json';
import enGBSnippets from './en-GB.json';

/* istanbul ignore next */
if (Shopware.License.get('MULTI_INVENTORY-3711815')) {
    Shopware.Locale.extend('de-DE', deDeSnippets);
    Shopware.Locale.extend('en-GB', enGBSnippets);
}
