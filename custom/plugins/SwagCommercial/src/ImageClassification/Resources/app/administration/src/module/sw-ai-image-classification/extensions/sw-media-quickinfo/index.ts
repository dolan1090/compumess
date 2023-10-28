import template from './sw-media-quickinfo.html.twig';
import './sw-media-quickinfo.scss';

const { Criteria } = Shopware.Data;

export default Shopware.Component.wrapComponentConfig({
    template,

    data() : { showProcessingInfo: boolean } {
        return {
            showProcessingInfo: false,
        }
    },

    watch: {
        'item.id': function () {
            this.createdComponent();
        },
    },

    methods: {
        createdComponent() {
            this.$super('createdComponent');

            const criteria = new Criteria(1, 1);
            criteria.addAssociation('mediaAiTag');

            this.mediaRepository.get(this.item.id, Shopware.Context.api, criteria).then((media) => {
                if (!media || !media.extensions.mediaAiTag.needsAnalysis) {
                    this.showProcessingInfo = false;

                    return;
                }

                this.showProcessingInfo = true;
            })
        }
    }
})
