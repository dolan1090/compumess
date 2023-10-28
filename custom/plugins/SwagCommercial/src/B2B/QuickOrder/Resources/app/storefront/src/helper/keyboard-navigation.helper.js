import ArrowNavigationHelper from 'src/helper/arrow-navigation.helper';
import DomAccess from 'src/helper/dom-access.helper';
import Iterator from 'src/helper/iterator.helper';

const ARROW_NAVIGATION_ACTIVE_CLASS = 'is-active';
const ARROW_NAVIGATION_ITERATOR_DEFAULT = -1;
const PADDING = 8;

export default class KeyboardNavigationHelper extends ArrowNavigationHelper {
    /**
     * When pressing "Enter" the link inside the currently
     * selected result item shall be clicked
     * @param {Event} event
     * @private
     */
    _onPressEnter(event) {
        // handle the original form submit event only if no search result has been selected before
        if (this._iterator <= ARROW_NAVIGATION_ITERATOR_DEFAULT) {
            return;
        }

        try {
            const currentSelection = this._getCurrentSelection();
            event.preventDefault();
            currentSelection.click();
        } catch (e) {
            // do nothing, if no current selection has been found
        }
    }

    /**
     * Handle 'keydown' event
     * @param {Event} event
     * @private
     */
    _onKeyDown(event) {
        const parent = DomAccess.querySelector(document, this._parentSelector, false);

        if (!parent) return;

        this._items = parent.querySelectorAll(this._itemSelector);
        // early return if no items exist
        if (this._items.length === 0) return;

        let currentSelection = this._getCurrentSelection();

        switch (event.key) {
            case 'Enter':
                this._onPressEnter(event);
                return;
            case 'Tab':
            case 'ArrowDown':
                event.preventDefault();
                this._iterator++;
                break;
            case 'ArrowUp':
                event.preventDefault();
                this._iterator--;

                // Stop scrolling up at first item if the list is scrollable
                if (parent.scrollHeight > parent.clientHeight
                    && parent.scrollTop === 0
                    && currentSelection?.offsetTop === PADDING) {
                    return;
                }
                break;
            default:
                return;
        }

        this._clampIterator();

        // remove all active classes
        Iterator.iterate(this._items, (item) => item.classList.remove(ARROW_NAVIGATION_ACTIVE_CLASS));

        // add active class to current iteration
        this._getCurrentSelection().classList.add(ARROW_NAVIGATION_ACTIVE_CLASS);

        // Get new current selection
        currentSelection = this._getCurrentSelection();

        // Scroll down
        if (event.key === 'ArrowDown' || event.key === 'Tab') {
            parent.scrollTop = currentSelection.offsetTop - parent.offsetTop;
        }

        // Scroll up
        if (event.key === 'ArrowUp') {
            parent.scrollTop = parent.scrollTop - currentSelection.clientHeight;
        }
    }
}
