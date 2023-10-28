/**
 * @package system-settings
 */
import deDeSnippets from './snippets/de-DE.json';
import enGBSnippets from './snippets/en-GB.json';

if (Shopware.License.get('EXPORT_ASSISTANT-2007020')) {
    Shopware.Locale.extend('de-DE', deDeSnippets);
    Shopware.Locale.extend('en-GB', enGBSnippets);
}
