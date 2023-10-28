/**
 * @package system-settings
 */
import template from './swag-export-assistant-base.html';
import './swag-export-assistant-base.scss';

export default {
    template,

    data(): {
        searchTerm: null | string,
        showModalPreview: boolean,
        } {
        return {
            searchTerm: null,
            showModalPreview: false,
        };
    },

    computed: {
        disabled(): boolean {
            return this.searchTerm === null || this.searchTerm.length <= 0;
        },
    },

    methods: {
        turnOnModalPreview() {
            this.showModalPreview = true;
        },

        turnOffModalPreview() {
            this.showModalPreview = false;
        },

        onEnter(event: {
            code: string,
        }) {
            if (event.code.toUpperCase() !== 'ENTER') {
                return;
            }

            if (this.searchTerm?.length <= 0) {
                return;
            }

            this.turnOnModalPreview();
        },
    },
};
