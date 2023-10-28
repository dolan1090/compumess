/**
 * @package checkout
 */
import { TOGGLE_KEY } from '../config';

import deDeSnippets from './de-DE.json';
import enGBSnippets from './en-GB.json';

if (Shopware.License.get(TOGGLE_KEY)) {
    Shopware.Locale.extend('de-DE', deDeSnippets);
    Shopware.Locale.extend('en-GB', enGBSnippets);
}
