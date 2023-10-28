export default {
    computed: {
        orderCriteria() {
            const criteria = this.$super('orderCriteria');
            criteria.addAssociation('orderEmployee');

            return criteria;
        },
    }
}
