(this["webpackJsonpPluginadvanced-search"]=this["webpackJsonpPluginadvanced-search"]||[]).push([[9],{hwbk:function(e,t,n){"use strict";n.r(t);var s=Shopware.Data.Criteria;t.default={template:'{% block sw_settings_search_searchable_content_customfields_searchable_label %}\n<sw-data-grid-column-boolean\n    v-model="item.searchable"\n/>\n{% endblock %}\n\n{% block sw_settings_search_searchable_content_customfields_tokenize_label %}\n<sw-data-grid-column-boolean\n    v-model="item.tokenize"\n/>\n{% endblock %}\n\n{% block sw_settings_search_searchable_content_customfields_columns_actions_edit %}\n<sw-context-menu-item\n    class="sw-settings-search__searchable-content-list-action sw-settings-search__searchable-content-list-edit"\n    @click="onInlineEditItem(item)"\n>\n    {{ $tc(\'global.default.edit\') }}\n</sw-context-menu-item>\n{% parent %}\n{% endblock %}\n',computed:{customFieldFilteredCriteria:function(){var e=this.$super("customFieldFilteredCriteria");return this.esEnabled?(e.addFilter(s.equals("customFieldSet.relations.entityName",this.entity)),e.addSorting(s.sort("config.customFieldPosition")),e):e},salesChannelId:function(){return Shopware.State.getters["swAdvancedSearchState/salesChannelId"]},esEnabled:function(){return Shopware.State.getters["swAdvancedSearchState/esEnabled"]},entity:function(){return Shopware.State.getters["swAdvancedSearchState/entity"]}},watch:{salesChannelId:function(){this.createdComponent()},esEnabled:function(){this.createdComponent()}},methods:{onInlineEditItem:function(e){this.$refs.customGrid.onDbClickCell(e)}}}}}]);