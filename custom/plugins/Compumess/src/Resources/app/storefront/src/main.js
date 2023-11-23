import FilterRangeSliderPlugin from './range-slider-filter/range-slider-filter.plugin';

const PluginManager = window.PluginManager;

PluginManager.register(
  'FilterRangeSliderPlugin',
  FilterRangeSliderPlugin,
  '[data-filter-range-slider]'
);
