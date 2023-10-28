import Na15FilterLabelsMultiselectPlugin from './js/na15-filter-labels-multiselect.plugin';
import Na15FilterLabelsPropertyPlugin from './js/na15-filter-labels-property.plugin';
import Na15FilterLabelsBooleanPlugin from './js/na15-filter-labels-boolean.plugin';
import Na15ListingPlugin from './js/na15-listing.plugin';

const PluginManager = window.PluginManager;

PluginManager.override('FilterMultiSelect', Na15FilterLabelsMultiselectPlugin, '[data-filter-multi-select]');
PluginManager.override('FilterPropertySelect', Na15FilterLabelsPropertyPlugin, '[data-filter-property-select]');
PluginManager.override('FilterBoolean', Na15FilterLabelsBooleanPlugin, '[data-filter-boolean]');
PluginManager.override('Listing', Na15ListingPlugin, '[data-listing]');

// Necessary for the webpack hot module reloading server
if (module.hot) {
    module.hot.accept();
}
