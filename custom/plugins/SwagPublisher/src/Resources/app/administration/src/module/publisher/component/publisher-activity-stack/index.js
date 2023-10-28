import template from './publisher-activity-stack.html.twig';
import './publisher-activity-stack.scss';

const { Component, Mixin } = Shopware;

export default Component.wrapComponentConfig({
    template,
    mixins: [
        Mixin.getByName('sw-publisher-activity')
    ],
    computed: {
        hasActivity() {
            return this.userActivity.length;
        },
        displayUserActivity() {
            return this.userActivity.slice(0, 4);
        },
        additionalUserActivityLength() {
            return this.userActivity.length - 4;
        },
        userActivity() {
            return Shopware.State.getters['sw-publisher/userActivity'];
        }
    },
    methods: {
        getTooltipOptions(log) {
            return {
                message: this.getTooltipText(log),
                position: 'bottom'
            }
        },
        getTooltipText({ user, date }) {
            const { firstName, lastName, userName } = user;
            let name = '';

            if (firstName || lastName) {
                name = `${firstName} ${lastName}`;
            } else if (userName) {
                name = userName;
            }

            return `${name} <div style="margin-top: 10px;">${this.getDateString(date)}</div>`;
        }
    }
});
