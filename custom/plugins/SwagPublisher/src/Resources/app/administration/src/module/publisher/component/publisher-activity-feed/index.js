import template from './publisher-activity-feed.html.twig';
import './publisher-activity-feed.scss';

const { Component, State } = Shopware;

export default Component.wrapComponentConfig({
    template,
    props: ['entity'],
    inject: ['acl'],
    computed: {
        publisherState() {
            return State.get('sw-publisher');
        },
        hasActivity() {
            return this.activity.length;
        },
        activity() {
            return this.publisherState.activity;
        },
        pageLocked() {
            const page = State.get('cmsPageState').currentPage;

            return page ? page.locked : true;
        }
    }
});
