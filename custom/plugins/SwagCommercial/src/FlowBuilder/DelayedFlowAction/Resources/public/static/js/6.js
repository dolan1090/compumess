(this["webpackJsonpPlugindelayed-flow-action"]=this["webpackJsonpPlugindelayed-flow-action"]||[]).push([[6],{"9Qzu":function(e,t,n){var i=n("hu23");i.__esModule&&(i=i.default),"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);(0,n("P8hj").default)("5e1e7f4f",i,!0,{})},FWA2:function(e,t,n){"use strict";n.r(t);n("9Qzu");function i(e){return(i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function l(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(e);t&&(i=i.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,i)}return n}function a(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?l(Object(n),!0).forEach((function(t){o(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):l(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function o(e,t,n){return(t=function(e){var t=function(e,t){if("object"!==i(e)||null===e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var l=n.call(e,t||"default");if("object"!==i(l))return l;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===i(t)?t:String(t)}(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}var s=Shopware,r=s.Component,c=s.Mixin,d=s.State,u=s.Service,f=Shopware.Utils.array.uniqBy,m=Shopware.Data.Criteria,w=r.getComponentHelper(),y=w.mapGetters,p=w.mapState,h=Shopware.Utils.format.date;t.default=r.wrapComponentConfig({template:'<div class="sw-flow-delay-tab">\n    <sw-card class="sw-flow-delay-tab__card" position-identifier="sw-flow-delay-tab-card" :is-loading="isLoading">\n        <template #grid>\n            <div class="sw-flow-delay-tab__container">\n                <div v-if="isShowWarningAlert">\n                    <sw-alert\n                        variant="warning"\n                        class="sw-flow-delay-tab__warning-unknow-trigger"\n                    >\n                        <p>{{ $tc(\'sw-flow.flowNotification.messageUnknownTriggerWarning\') }}</p>\n                        <p>{{ $tc(\'sw-flow.flowNotification.textIntroduce\') }}</p>\n                        <ul>\n                            <li>{{ $tc(\'sw-flow.flowNotification.textGuide1\') }}</li>\n                            <li>{{ $tc(\'sw-flow.flowNotification.textGuide2\') }}</li>\n                            <li>{{ $tc(\'sw-flow.flowNotification.textGuide3\') }}</li>\n                        </ul>\n                    </sw-alert>\n\n                    <sw-alert\n                        variant="warning"\n                        class="sw-flow-delay-tab__warning-box"\n                    >\n                        {{ $tc(\'sw-flow-delay.delay.warningText\') }}\n                    </sw-alert>\n                </div>\n\n                <sw-card-section secondary>\n                    <sw-container columns="1fr 90px" gap="15px">\n                        <sw-simple-search-field\n                            v-model="searchTerms"\n                            class="sw-flow-delay-tab__search-field"\n                            size="medium"\n                            variant="form"\n                            :placeholder="$tc(\'sw-flow-delay.delay.list.placeholderSearch\')"\n                            :delay="500"\n                            @search-term-change="onSearchTermChange"\n                        />\n\n                        <sw-context-button\n                            menu-horizontal-align="left"\n                            :menu-width="250"\n                            :auto-close="false"\n                            :auto-close-outside-click="true"\n                            :z-index="1000"\n                        >\n                            <template slot="button">\n                                <sw-button\n                                    class="sw-flow-delay-tab__filter-menu-trigger"\n                                    size="small"\n                                >\n                                    <sw-icon\n                                        name="regular-filter-s"\n                                        size="16"\n                                    />\n                                    {{ $tc(\'sw-flow-delay.delay.list.filter\') }}\n                                </sw-button>\n                                <i\n                                    v-if="delayedActionsFilter.length > 0"\n                                    class="filter-badge"\n                                >\n                                    {{ delayedActionsFilter.length }}\n                                </i>\n                            </template>\n\n                            <h3>{{ $tc(\'sw-flow-delay.delay.list.filter\') }}</h3>\n\n                            <sw-context-menu-divider />\n\n                            <sw-multi-select\n                                v-model="delayedActionsFilter"\n                                class="sw-flow-delay-tab__filter-action-list-select"\n                                :label="$tc(\'sw-flow-delay.delay.list.filterLabel\')"\n                                :label-property="filterItems.label"\n                                :value-property="filterItems.value"\n                                :options="filterItems"\n                                @change="getList"\n                            />\n\n                            <div class="sw-flow-delay-tab__filter-footer">\n                                <a\n                                    href="#"\n                                    @click.prevent="resetFilters"\n                                >\n                                    {{ $tc(\'sw-flow-delay.delay.list.resetFilter\') }}\n                                </a>\n                            </div>\n                        </sw-context-button>\n                    </sw-container>\n                </sw-card-section>\n\n                <sw-card-section divider="top">\n                    <sw-entity-listing\n                        v-if="!isLoading && delayedActions.length > 0"\n                        ref="delayedActionsGrid"\n                        class="sw-flow-delay-tab-actions-list"\n                        :show-settings="true"\n                        :show-actions="true"\n                        :show-selection="true"\n                        :full-page="false"\n                        :is-loading="isLoading"\n                        :repository="delayedActionsRepository"\n                        :items="delayedActions"\n                        :columns="delayedActionColumns"\n                        :sort-by="sortBy"\n                        :sort-direction="sortDirection"\n                        @column-sort="onSortColumn"\n                        @items-delete-finish="getList"\n                    >\n                        <template #bulk-additional="{ selection }">\n                            \n                            <a\n                                class="sw-flow-delay-tab__execute-all-link"\n                                @click="showExecuteAllDelayed = true"\n                            >\n                                {{ $tc(\'sw-flow-delay.delay.list.buttonExecute\') }}\n                            </a>\n                        </template>\n\n                        <template #bulk-modals-additional="{ selection, ids }">\n                            <sw-modal\n                                v-if="showExecuteAllDelayed"\n                                variant="small"\n                                :title="$tc(\'global.default.warning\')"\n                                @modal-close="onCloseModal"\n                            >\n                                <sw-alert variant="warning">\n                                    {{ $tc(\'sw-flow-delay.delay.list.executeAllWarningMessage\') }}\n                                </sw-alert>\n\n                                <template #modal-footer>\n                                    <sw-button size="small" @click="onCloseModal">\n                                        {{ $tc(\'global.default.cancel\') }}\n                                    </sw-button>\n\n                                    <sw-button\n                                        class="sw-flow-delay-tab__execute-all-button"\n                                        size="small"\n                                        variant="primary"\n                                        @click="onExecuteAll(ids)">\n                                        {{ $tc(\'sw-flow-delay.delay.list.buttonExecute\') }}\n                                    </sw-button>\n                                </template>\n                            </sw-modal>\n                        </template>\n\n                        <template #column-order.orderNumber="{ item }">\n                            <router-link v-if="item.orderId" :to="{ name: \'sw.order.detail\', params: { id: item.orderId } }">\n                                {{ item.order.orderNumber }}\n                            </router-link>\n                            <span v-else>\n                                {{ $tc(\'sw-flow-delay.delay.list.notAvailable\') }}\n                            </span>\n                        </template>\n\n                        <template #column-customer.firstName="{ item }">\n                            <router-link\n                                v-if="item.customerId"\n                                :to="{\n                                    name: \'sw.customer.detail\',\n                                    params: { id: item.customerId },\n                                }"\n                            >\n                                {{ item.customer.lastName || item.order.orderCustomer.lastName }},\n                                {{ item.customer.firstName || item.order.orderCustomer.firstName }}\n                            </router-link>\n                            <span v-else>\n                                {{ $tc(\'sw-flow-delay.delay.list.notAvailable\') }}\n                            </span>\n                        </template>\n\n                        <template #column-name="{ item }">\n                            <sw-flow-sequence-label\n                                :sequence="getSequence(item)"\n                                :app-flow-actions="appActions"\n                                @click="detailActionsModal(item)"\n                            />\n                        </template>\n\n                        <template #column-executionTime="{ item }">\n                            {{ remainingTime(item.executionTime) }}\n                        </template>\n\n                        <template #column-scheduledFor="{ item }">\n                            {{ getScheduledFor(item.executionTime) }}\n                        </template>\n\n                        <template #pagination>\n                            <sw-pagination\n                                :page="page"\n                                :limit="limit"\n                                :total="total"\n                                :total-visible="25"\n                                :auto-hide="false"\n                                @page-change="onPageChange"\n                            />\n                        </template>\n\n                        <template #actions="{ item }">\n                            <sw-context-menu-item class="sw-flow-delay-tab-actions-list__execute-action" @click="onAction(item.id, \'EXECUTE\')">\n                                {{ $tc(\'sw-flow-delay.delay.list.actionExecuteDelayedAction\') }}\n                            </sw-context-menu-item>\n\n                            <sw-context-menu-item class="sw-flow-delay-tab-actions-list__cancel-action" variant="danger" @click="onAction(item.id, \'DELETE\')">\n                                {{ $tc(\'sw-flow-delay.delay.list.actionCancelDelayedAction\') }}\n                            </sw-context-menu-item>\n                        </template>\n\n                        <template #action-modals="{ item }">\n                            <sw-modal\n                                v-if="showModal === item.id"\n                                variant="small"\n                                :title="$tc(\'global.default.warning\')"\n                                @modal-close="onCloseModal"\n                            >\n                                <p v-if="actionType ===\'DELETE\'"\n                                   class="sw-flow-list__confirm-delete-text">\n                                    {{ $tc(\'sw-flow-delay.delay.list.cancelWarningMessage\') }}\n                                </p>\n\n                                <p v-else\n                                   class="sw-flow-list__confirm-delete-text">\n                                    <sw-alert variant="warning">\n                                        {{ $tc(\'sw-flow-delay.delay.list.confirmExecuteActionText\') }}\n                                    </sw-alert>\n                                </p>\n\n                                <template #modal-footer>\n                                    <sw-button size="small" class="sw-flow-delay-tab__button_cancel" @click="onCloseModal">\n                                        {{ $tc(\'global.default.cancel\') }}\n                                    </sw-button>\n\n                                    <sw-button\n                                        v-if="actionType ===\'DELETE\'"\n                                        size="small"\n                                        variant="danger"\n                                        class="sw-flow-delay-tab__button_delete"\n                                        @click="onConfirmAction(item.id)">\n                                        {{ $tc(\'global.default.delete\') }}\n                                    </sw-button>\n                                    <sw-button\n                                        v-else\n                                        size="small"\n                                        variant="primary"\n                                        class="sw-flow-delay-tab__button_execute"\n                                        @click="onConfirmAction(item.id)">\n                                        {{ $tc(\'sw-flow-delay.delay.list.buttonExecute\') }}\n                                    </sw-button>\n                                </template>\n                            </sw-modal>\n\n                            <sw-flow-action-detail-modal\n                                v-if="showDetailActionsModal === item.id"\n                                :sequence="item"\n                                :app-flow-actions="appActions"\n                                @modal-close="showDetailActionsModal = null"\n                            />\n                        </template>\n                    </sw-entity-listing>\n\n                </sw-card-section>\n            </div>\n\n            <sw-container v-if="!isLoading && !delayedActions.length" rows="auto 400px">\n                <sw-empty-state class="sw-flow-delay-tab-actions__empty-state" :show-description="false" :title="$tc(\'sw-flow-delay.delay.list.emptyStateTitle\')">\n                    <template #icon>\n                        <img :alt="$tc(\'sw-flow-delay.delay.list.emptyStateTitle\')" :src="\'/administration/static/img/empty-states/settings-empty-state.svg\' | asset">\n                    </template>\n                </sw-empty-state>\n            </sw-container>\n        </template>\n    </sw-card>\n</div>\n',inject:["repositoryFactory","flowBuilderService"],mixins:[c.getByName("listing"),c.getByName("placeholder"),c.getByName("notification"),c.getByName("sw-inline-snippet")],data:function(){return{total:0,isLoading:!1,delayedActions:[],sortBy:"executionTime",sortDirection:"ASC",showModal:!1,actionType:"DELETE",searchTerms:null,showExecuteAllDelayed:!1,filterItems:[],delayedActionsFilter:[],showDetailActionsModal:null}},computed:a(a({flow:function(){return d.get("swFlowState").flow},isUnknownTrigger:function(){var e=this;return!!this.$route.params.id&&!this.triggerEvents.some((function(t){return t.name===e.flow.eventName}))},hasDelayedActions:function(){return this.sequences.some((function(e){return"action.delay"===e.actionName}))},isShowWarningAlert:function(){return this.isUnknownTrigger&&!this.flow.active&&this.hasDelayedActions},delayedActionsRepository:function(){return this.repositoryFactory.create("swag_delay_action")},delayedActionColumns:function(){return this.getDelayedActionColumns()},delayedActionFilterCriteria:function(){var e=new m;return e.addAssociation("sequence.children.rule"),e.addFilter(m.equals("flowId",this.flow.id)),e.addFilter(m.not("and",[m.equals("sequence.children.id",null)])),e},delayedActionCriteria:function(){var e=new m(this.page,this.limit);return e.addAssociation("order").addAssociation("customer").addAssociation("sequence.children.rule"),this.searchTerms&&e.addFilter(m.multi("or",[m.contains("order.orderNumber",this.searchTerms),m.contains("customer.firstName",this.searchTerms),m.contains("customer.lastName",this.searchTerms)])),this.delayedActionsFilter.length>0&&e.addFilter(m.multi("or",[m.equalsAny("sequence.children.actionName",this.delayedActionsFilter),m.equalsAny("sequence.children.rule.name",this.delayedActionsFilter)])),e.addSorting(m.sort(this.sortBy,this.sortDirection)),e.addFilter(m.equals("flowId",this.flow.id)),e.addFilter(m.not("and",[m.equals("sequence.children.id",null)])),e.getAssociation("sequence.children").addSorting(m.sort("position","ASC")),e},delayConstant:function(){return this.flowBuilderService.getActionName("DELAY")}},p("swFlowState",["triggerEvents"])),y("swFlowState",["appActions","sequences"])),methods:{getLicense:function(e){return Shopware.License.get(e)},getList:function(){var e=this;if(!this.getLicense("FLOW_BUILDER-1475275"))return this.isLoading=!0,this.delayedActionsRepository.search(this.delayedActionCriteria).then((function(t){e.delayedActions=t,e.total=t.total})).catch((function(){e.createNotificationError({message:e.$tc("sw-flow-delay.delay.list.fetchErrorMessage")})})).finally((function(){e.isLoading=!1,e.createActionFilter()}));Shopware.Application.getContainer("init").httpClient.get("api/_info/config",{headers:{Accept:"application/vnd.api+json",Authorization:"Bearer ".concat(Shopware.Service("loginService").getToken()),"Content-Type":"application/json","sw-license-toggle":"FLOW_BUILDER-1475275"}})},getDelayedActionColumns:function(){return[{property:"order.orderNumber",dataIndex:"order.orderNumber",label:"sw-flow-delay.delay.list.columnOrderNumber",allowResize:!1},{property:"customer.firstName",dataIndex:"customer.firstName",label:"sw-flow-delay.delay.list.columnCustomer",allowResize:!1},{property:"name",dataIndex:"name",label:"sw-flow-delay.delay.list.columnName"},{property:"executionTime",dataIndex:"executionTime",label:"sw-flow-delay.delay.list.columnRemainingTime",allowResize:!1},{property:"scheduledFor",dataIndex:"executionTime",label:"sw-flow-delay.delay.list.columnScheduleFor",allowResize:!1}]},onSearchTermChange:function(){this.getList()},dateFormat:function(e){return new Date(e)},getScheduledFor:function(e){var t=new Date(e).getTime();return h(this.dateFormat(t))},remainingTime:function(e){var t=(new Date).getTime(),n=new Date(e).getTime()-t;if(!(n>=0))return this.$tc("sw-flow-delay.delay.list.expiredAction");var i=Math.floor(n/864e5),l=Math.floor(n%864e5/36e5),a=Math.floor(n%36e5/6e4);return i>=100?"".concat(i," ")+this.$tc("sw-flow-delay.delay.list.day",i):i>=1&&i<100?"".concat(("0"+i).slice(-2)," ")+this.$tc("sw-flow-delay.delay.list.day",i)+" ".concat(("0"+l).slice(-2),":").concat(("0"+a).slice(-2)," ")+this.$tc("sw-flow-delay.delay.list.hour",l):i<1&&l>=1?"".concat(("0"+l).slice(-2),":").concat(("0"+a).slice(-2)," ")+this.$tc("sw-flow-delay.delay.list.hour",l):l<1?"".concat(("0"+a).slice(-2)," ")+this.$tc("sw-flow-delay.delay.list.minute",a):void 0},onAction:function(e,t){this.showModal=e,this.actionType=t},onCloseModal:function(){this.showDetailActionsModal=!1,this.showModal=!1,this.showExecuteAllDelayed=!1},onConfirmAction:function(e){var t=this;return this.showModal=!1,"DELETE"===this.actionType?this.delayedActionsRepository.delete(e).then((function(){t.$refs.delayedActionsGrid.resetSelection(),t.getList()})):this.delayedExecute([e])},modalConfirmExecuteAll:function(){this.showExecuteAllDelayed=!0},onExecuteAll:function(e){return this.showExecuteAllDelayed=!1,this.delayedExecute(e)},delayedExecute:function(e){var t=this;return u("swFlowDelayService").delayedExecute(e).then((function(){t.$refs.delayedActionsGrid.resetSelection(),t.getList()})).catch((function(){t.createNotificationError({message:t.$tc("sw-flow-delay.delay.list.executeActionErrorMessage")})})).finally((function(){t.isLoading=!1}))},createActionFilter:function(){var e=this;return this.delayedActionsRepository.search(this.delayedActionFilterCriteria).then((function(t){e.filterItems=e.getFilterItems(t)})).catch((function(){e.createNotificationError({message:e.$tc("sw-flow-delay.delay.list.fetchErrorMessage")})}))},getFilterItems:function(e){var t=this;if(!e.length)return[];var n=[];return e.forEach((function(e){var i=t.getSequence(e);n.push(t.convertFilter(i))})),f(n,"value")},resetFilters:function(){this.delayedActionsFilter=[],this.getList()},detailActionsModal:function(e){this.showDetailActionsModal=e.id},getSequence:function(e){var t=e.sequence.children;return t.length?t[0]:null},convertFilter:function(e){var t,n,i;if(null!==(t=e.rule)&&void 0!==t&&t.name)return{label:null===(n=e.rule)||void 0===n?void 0:n.name,value:null===(i=e.rule)||void 0===i?void 0:i.name};var l,a=Object.values(this.appActions).find((function(t){return t.name===e.actionName}));return a?{label:(null===(l=a.translated)||void 0===l?void 0:l.label)||a.label,value:a.name}:e.actionName===this.delayConstant?{label:this.$tc("sw-flow-delay.detail.sequence.delayActionTitle"),value:e.actionName}:{label:"".concat(this.$tc(this.flowBuilderService.getActionTitle(e.actionName).label)),value:e.actionName}}}})},P8hj:function(e,t,n){"use strict";function i(e,t){for(var n=[],i={},l=0;l<t.length;l++){var a=t[l],o=a[0],s={id:e+":"+l,css:a[1],media:a[2],sourceMap:a[3]};i[o]?i[o].parts.push(s):n.push(i[o]={id:o,parts:[s]})}return n}n.r(t),n.d(t,"default",(function(){return w}));var l="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!l)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var a={},o=l&&(document.head||document.getElementsByTagName("head")[0]),s=null,r=0,c=!1,d=function(){},u=null,f="data-vue-ssr-id",m="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function w(e,t,n,l){c=n,u=l||{};var o=i(e,t);return y(o),function(t){for(var n=[],l=0;l<o.length;l++){var s=o[l];(r=a[s.id]).refs--,n.push(r)}t?y(o=i(e,t)):o=[];for(l=0;l<n.length;l++){var r;if(0===(r=n[l]).refs){for(var c=0;c<r.parts.length;c++)r.parts[c]();delete a[r.id]}}}}function y(e){for(var t=0;t<e.length;t++){var n=e[t],i=a[n.id];if(i){i.refs++;for(var l=0;l<i.parts.length;l++)i.parts[l](n.parts[l]);for(;l<n.parts.length;l++)i.parts.push(h(n.parts[l]));i.parts.length>n.parts.length&&(i.parts.length=n.parts.length)}else{var o=[];for(l=0;l<n.parts.length;l++)o.push(h(n.parts[l]));a[n.id]={id:n.id,refs:1,parts:o}}}}function p(){var e=document.createElement("style");return e.type="text/css",o.appendChild(e),e}function h(e){var t,n,i=document.querySelector("style["+f+'~="'+e.id+'"]');if(i){if(c)return d;i.parentNode.removeChild(i)}if(m){var l=r++;i=s||(s=p()),t=b.bind(null,i,l,!1),n=b.bind(null,i,l,!0)}else i=p(),t=A.bind(null,i),n=function(){i.parentNode.removeChild(i)};return t(e),function(i){if(i){if(i.css===e.css&&i.media===e.media&&i.sourceMap===e.sourceMap)return;t(e=i)}else n()}}var g,v=(g=[],function(e,t){return g[e]=t,g.filter(Boolean).join("\n")});function b(e,t,n,i){var l=n?"":i.css;if(e.styleSheet)e.styleSheet.cssText=v(t,l);else{var a=document.createTextNode(l),o=e.childNodes;o[t]&&e.removeChild(o[t]),o.length?e.insertBefore(a,o[t]):e.appendChild(a)}}function A(e,t){var n=t.css,i=t.media,l=t.sourceMap;if(i&&e.setAttribute("media",i),u.ssrId&&e.setAttribute(f,t.id),l&&(n+="\n/*# sourceURL="+l.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(l))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}},hu23:function(e,t,n){}}]);