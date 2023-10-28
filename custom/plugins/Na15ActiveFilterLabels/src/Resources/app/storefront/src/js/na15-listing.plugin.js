import ListingPlugin from 'src/plugin/listing/listing.plugin';

export default class Na15ListingPlugin extends ListingPlugin {

    init() {
        super.init();
    }

    getLabelTemplate(label) {
        let propertyLabel = "";
        let propertyDescription = "";
        let labelValue = label.label;

        if (label.propertyLabel != undefined) {
            propertyLabel = label.propertyLabel;
        }

        if (label.propertyDescription != undefined) {
            propertyDescription = label.propertyDescription;
        }

        if (labelValue.includes(".000000")) {
            labelValue = labelValue.replace(".000000", ".00")
        }

        return `
        <span class="${this.options.activeFilterLabelClass}">
            ${this.getLabelPreviewTemplate(label)}
            ${propertyLabel} ${labelValue} ${propertyDescription}
            <button class="${this.options.activeFilterLabelRemoveClass}"
                    data-id="${label.id}">
                &times;
            </button>
        </span>
        `;
    }

    getLabelPreviewTemplate(label) {
        const previewClass = this.options.activeFilterLabelPreviewClass;

        if (label.previewHex) {
            return `
                <span class="${previewClass}" style="background-color: ${label.previewHex};"></span>
            `;
        }

        if (label.previewImageUrl) {
            return `
                <span class="${previewClass}" style="background-image: url('${label.previewImageUrl}');"></span>
            `;
        }

        return '';
    }
}
