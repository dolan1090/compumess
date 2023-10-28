/**
 * @package inventory
 */
import deDESnippets from './de-DE.json';
import enGBSnippets from './en-GB.json';

/* istanbul ignore next */
if (Shopware.License.get('REVIEW_TRANSLATOR-1649854')) {
    Shopware.Locale.extend('de-DE', deDESnippets);
    Shopware.Locale.extend('en-GB', enGBSnippets);
}
