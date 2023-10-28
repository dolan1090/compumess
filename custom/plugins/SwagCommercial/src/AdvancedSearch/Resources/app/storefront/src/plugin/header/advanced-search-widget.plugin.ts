/**
 * @package buyers-experience
 */
import Plugin from 'src/plugin-system/plugin.class';

/**
 * Advanced search suggestion
 * @extends Plugin
 * @private
 */
export default class AdvancedSearchWidgetPlugin extends Plugin {
    public static readonly options = {
        searchWidgetEventAfterSuggest: 'afterSuggest',
        searchWidgetResultSelector: '.js-search-result',
        searchWidgetInputFieldSelector: 'input[type=search]',
        searchWidgetResultItemSelector: '.js-search-result .search-suggest-product-name'
    }

    private el: HTMLElement;
    private $emitter: any;

    init(): void {
        this.$emitter.subscribe(AdvancedSearchWidgetPlugin.options.searchWidgetEventAfterSuggest, this.handleAfterSuggest.bind(this));
        this.$emitter.publish(AdvancedSearchWidgetPlugin.options.searchWidgetEventAfterSuggest);
    }

    /**
     * Handle after suggest
     *
     * @private
     */
    private handleAfterSuggest(): void {
        const searchTerms = this.getSearchTerms();
        const searchResult = this.getSearchResult();

        if (!searchResult || !searchTerms || !searchTerms.length) {
            return;
        }

        const searchResultItems = this.getResultItems(searchResult);

        if (searchResultItems.length === 0) {
            return;
        }

        searchResultItems.forEach((item) => {
            this.highlightSearchTerm(item, searchTerms);
        });
    }

    /**
     * Highlight search term
     *
     * @private
     */
    private highlightSearchTerm(item: Element, terms: Array<string>): void {
        terms.forEach((term: string) => {
            const regex = this.getRegex(term);

            item.innerHTML = item.innerHTML.replace(regex, '<b>$1</b>');
        });
    }

    /**
     * Get the regular expression according to the rule
     *
     * @private
     * @param term string to be used as a regular expression
     */
    private getRegex(term: string): RegExp {
        const termsToReplace = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;'
        };

        return new RegExp(`(${termsToReplace[term] || term})`, 'gi');
    }

    /**
     * Get search terms
     *
     * @private
     */
    private getSearchTerms(): Array<string> | void {
        const searchInputElement = this.getSearchInput();
        const terms = searchInputElement.value.split(' ');

        return terms.filter(term => term !== '');
    }

    /**
     * Get search input
     *
     * @private
     */
    private getSearchInput(): HTMLInputElement | null {
        return this.el.querySelector(AdvancedSearchWidgetPlugin.options.searchWidgetInputFieldSelector);
    }

    /**
     * Get search result
     *
     * @private
     */
    private getSearchResult(): Element | null {
        return this.el.querySelector(AdvancedSearchWidgetPlugin.options.searchWidgetResultSelector);
    }

    /**
     * Get search result items
     *
     * @private
     */
    private getResultItems(container: Element): NodeListOf<Element> {
        return container.querySelectorAll(AdvancedSearchWidgetPlugin.options.searchWidgetResultItemSelector);
    }
}
