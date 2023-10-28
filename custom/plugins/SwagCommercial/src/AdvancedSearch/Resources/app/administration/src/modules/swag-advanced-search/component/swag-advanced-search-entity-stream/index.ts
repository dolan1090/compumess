/**
 * @package buyers-experience
 */
export default {
    provide() {
        return {
            entityStream: this.entityStream,
        };
    },

    props: {
        entityStream: {
            type: Object,
            required: true,
        },
    },
};
