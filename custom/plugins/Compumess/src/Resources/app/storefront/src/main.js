import CustomFilterRangePlugin from './custom-filter-range/custom-filter-range.plugin';

const PluginManager = window.PluginManager;

PluginManager.override(
  'FilterRange',
  CustomFilterRangePlugin,
  '[data-filter-range]'
);
