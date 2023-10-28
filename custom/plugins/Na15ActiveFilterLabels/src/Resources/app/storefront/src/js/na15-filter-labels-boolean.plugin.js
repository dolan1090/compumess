import FilterBooleanPlugin from 'src/plugin/listing/filter-boolean.plugin';

export default class Na15FilterLabelsBooleanPlugin extends FilterBooleanPlugin {

    init() {
        super.init();
    }

    getLabels() {
        let labels = [];

        if (this.checkbox.checked) {
            labels.push({
                label: this.options.displayName,
                propertyLabel: this.options.propertyName,
                id: this.options.name,
            });
        } else {
            labels = [];
        }

        return labels;
    }
}
