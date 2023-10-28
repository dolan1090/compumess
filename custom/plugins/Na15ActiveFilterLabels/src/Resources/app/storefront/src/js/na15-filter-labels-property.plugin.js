import FilterPropertySelectPlugin from 'src/plugin/listing/filter-property-select.plugin';
import DomAccess from 'src/helper/dom-access.helper';
import Iterator from 'src/helper/iterator.helper';

export default class Na15FilterLabelsPropertyPlugin extends FilterPropertySelectPlugin {

    init() {
        super.init();
    }

    getLabels() {
        const activeCheckboxes =
            DomAccess.querySelectorAll(this.el, `${this.options.checkboxSelector}:checked`, false);

        let labels = [];

        if (activeCheckboxes) {
            Iterator.iterate(activeCheckboxes, (checkbox) => {
                labels.push({
                    label: checkbox.dataset.label,
                    propertyLabel: checkbox.dataset.propertylabel,
                    propertyDescription: checkbox.dataset.propertydescription,
                    id: checkbox.id,
                    previewHex: checkbox.dataset.previewHex,
                    previewImageUrl: checkbox.dataset.previewImageUrl,
                });
            });
        } else {
            labels = [];
        }

        return labels;
    }
}
