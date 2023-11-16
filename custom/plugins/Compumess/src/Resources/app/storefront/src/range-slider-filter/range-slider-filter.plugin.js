import FilterRangePlugin from 'src/plugin/listing/filter-range.plugin';
import DomAccess from 'src/helper/dom-access.helper';
import deepmerge from 'deepmerge';

export default class FilterRangeSliderPlugin extends FilterRangePlugin {
  static options = deepmerge(FilterRangePlugin.options, {
    containerSelector: '.filter-range-slider-container',
    inputTimeout: 500,
  });
  init() {
    const { name, minInputValue, maxInputValue, min, max } = this.options;
    const sliderSelector = `#range-slider-${name}`;
    const rangeInfoSelector = '.compumess-range-slider-info';
    const rangeInfoMinSelector = '#range-slider-info-min';
    const rangeInfoMaxSelector = '#range-slider-info-max';
    const me = this;

    this.sliderElem = DomAccess.querySelector(this.el, sliderSelector);
    this.slider = $(this.sliderElem);
    this.rangeInfo = DomAccess.querySelector(this.el, rangeInfoSelector);
    this.rangeInfoMin = DomAccess.querySelector(
      this.rangeInfo,
      rangeInfoMinSelector
    );
    this.rangeInfoMax = DomAccess.querySelector(
      this.rangeInfo,
      rangeInfoMaxSelector
    );
    this.slider.slider({
      range: true,
      min: min || minInputValue,
      max: max || maxInputValue,
      values: [minInputValue, maxInputValue],
      stop: me._onSliderStop.bind(me),
      slide: me._onSliderSlide.bind(me),
    });
    super.init();
  }

  setValuesFromUrl(params) {
    const values = this.slider.slider('values');
    const { name, minKey, maxKey } = this.options;
    let stateChanged = false;

    Object.keys(params).forEach((key) => {
      const value = params[key];
      if (key.includes(name) && value) {
        if (key === minKey) {
          values[0] = value;
          stateChanged = true;
        } else if (key === maxKey) {
          values[1] = value;
          stateChanged = true;
        }
      }
    });

    this.setSliderValues(values);

    return stateChanged;
  }

  getValues() {
    const [minValue, maxValue] = this.getSliderValues();
    const { name, minKey, maxKey } = this.options;

    return {
      [minKey]: minValue,
      [maxKey]: maxValue,
    };
  }

  getSliderValues() {
    return this.slider.slider('values');
  }

  setSliderValues(values) {
    this.slider.slider('values', values);
  }

  getLabels() {
    return [];
  }

  _onSliderStop() {
    window.clearTimeout(this.timeout);

    this.timeout = window.setTimeout(() => {
      this.listing.changeListing();
    }, this.options.inputTimeout);
  }

  _onSliderSlide(_, ui) {
    const [minValue, maxValue] = ui.values;
    this.rangeInfoMin.textContent = minValue;
    this.rangeInfoMax.textContent = maxValue;
  }
}
