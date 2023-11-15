import FilterRangePlugin from 'src/plugin/listing/filter-range.plugin';
import deepmerge from 'deepmerge';

const INFINITY_LOWER_BOUND_FILTER_NAME = 'ausgangsspannungnum';
export default class CustomFilterRangePlugin extends FilterRangePlugin {
  init() {
    if (this.options.name === INFINITY_LOWER_BOUND_FILTER_NAME)
      this.options = deepmerge(this.options, {
        lowerBound: Number.NEGATIVE_INFINITY,
      });
    super.init();
  }
}
