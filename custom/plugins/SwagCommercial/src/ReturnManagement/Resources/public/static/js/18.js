(this["webpackJsonpPluginreturn-management"]=this["webpackJsonpPluginreturn-management"]||[]).push([[18],{"0beb":function(t,e,a){"use strict";a.r(e);var n=Shopware.Component,r=Shopware.Data.Criteria;e.default=n.wrapComponentConfig({template:'<sw-data-grid\n    :columns="statusHistoryColumns"\n    :data-source="stateHistories"\n    :is-loading="isLoading"\n    :plain-appearance="true"\n    :show-selection="false"\n    :show-actions="false"\n    :skeleton-item-amount="limit"\n>\n    <template #column-createdAt="{ item }">\n        <sw-time-ago :date="item.createdAt" />\n    </template>\n\n    <template #column-user="{ item }">\n        <span class="swag-return-management-return-card-state-history__username">\n            {{ getChangedByUserName(item) }}\n        </span>\n    </template>\n\n    <template #column-status="{ item }">\n        <sw-label\n            :variant="getVariantState(item.status)"\n            appearance="badged"\n        >\n            {{ item.status.translated.name }}\n        </sw-label>\n    </template>\n\n    <template #pagination>\n        <sw-pagination\n            :page="page"\n            :limit="limit"\n            :total="total"\n            :steps="steps"\n            @page-change="onPageChange">\n        </sw-pagination>\n    </template>\n</sw-data-grid>\n',inject:["repositoryFactory","stateStyleDataProviderService"],props:{orderReturn:{type:Object,required:!0}},data:function(){return{stateHistories:[],isLoading:!1,limit:10,page:1,total:0,steps:[5,10,25]}},computed:{stateMachineHistoryRepository:function(){return this.repositoryFactory.create("state_machine_history")},statusHistoryColumns:function(){return[{property:"createdAt",label:this.$tc("swag-return-management.returnCard.statusTab.columnDate")},{property:"user",label:this.$tc("swag-return-management.returnCard.statusTab.columnUser")},{property:"status",label:this.$tc("swag-return-management.returnCard.statusTab.columnStatus")}]},stateMachineHistoryCriteria:function(){var t=new r(this.page,this.limit);return t.addFilter(r.equals("state_machine_history.entityId.id",this.orderReturn.id)),t.addFilter(r.equals("state_machine_history.entityName","order_return")),t.addAssociation("fromStateMachineState"),t.addAssociation("toStateMachineState"),t.addAssociation("user"),t.addSorting({field:"state_machine_history.createdAt",order:"ASC"}),t}},created:function(){this.createdComponent()},methods:{createdComponent:function(){this.getStateHistoryEntries()},getChangedByUserName:function(t){var e,a;return null!==(e=null===(a=t.user)||void 0===a?void 0:a.username)&&void 0!==e?e:this.$tc("swag-return-management.returnCard.statusTab.labelSystemUser")},getStateHistoryEntries:function(){var t=this;return this.isLoading=!0,this.stateMachineHistoryRepository.search(this.stateMachineHistoryCriteria).then((function(e){var a,n,r,i,s,o;(t.total=null!==(a=null==e?void 0:e.total)&&void 0!==a?a:1,t.stateHistories=e.map((function(t){return{user:t.user,createdAt:t.createdAt,status:t.toStateMachineState}})),1===t.page)&&t.stateHistories.unshift({createdAt:null===(n=t.orderReturn)||void 0===n?void 0:n.createdAt,status:null!==(r=null===(i=e[0])||void 0===i?void 0:i.fromStateMachineState)&&void 0!==r?r:null===(s=t.orderReturn)||void 0===s?void 0:s.state,user:null===(o=t.orderReturn)||void 0===o?void 0:o.createdBy})})).finally((function(){t.isLoading=!1}))},getVariantState:function(t){return this.stateStyleDataProviderService.getStyle("order_return.state",t.technicalName).variant},onPageChange:function(t){var e=t.page,a=t.limit;this.page=e,this.limit=a,this.getStateHistoryEntries()}}})}}]);