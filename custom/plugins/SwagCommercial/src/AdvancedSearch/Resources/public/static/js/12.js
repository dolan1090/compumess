(this["webpackJsonpPluginadvanced-search"]=this["webpackJsonpPluginadvanced-search"]||[]).push([[12],{"3ODC":function(e,n,s){"use strict";s.r(n);n.default={template:'{% block sw_settings_search_view_live_search %}\n<div class="sw-settings-search__view-live-search">\n    <sw-settings-search-search-index\n        v-if="!esEnabled"\n        :is-loading="isLoading"\n        v-on="$listeners"\n    />\n\n    {% block sw_settings_search_view_live_search_content_card %}\n    <sw-settings-search-live-search\n        v-bind="$props"\n        v-on="$listeners"\n    />\n    {% endblock %}\n</div>\n{% endblock %}\n',computed:{esEnabled:function(){return Shopware.State.getters["swAdvancedSearchState/esEnabled"]}}}}}]);