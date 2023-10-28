(this["webpackJsonpPluginreturn-management"]=this["webpackJsonpPluginreturn-management"]||[]).push([[0],{"+8Jl":function(e,t,n){"use strict";n.r(t);n("cH0/");var r=n("wR8H"),i=n("hE3U");function s(e){return function(e){if(Array.isArray(e))return o(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||function(e,t){if(!e)return;if("string"==typeof e)return o(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return o(e,t)}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function o(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}var a=Shopware,l=a.Component,u=a.Mixin;t.default=l.wrapComponentConfig({template:'{% block sw_order_line_items_grid %}\n<sw-container\n    type="row"\n    class="sw-order-line-items-grid"\n>\n    {% parent %}\n\n    <swag-return-management-create-return-modal\n        v-if="showReturnModal"\n        :line-items="returnItems"\n        :order="order"\n        @return-create="onReturnCreate"\n        @return-added-items="onAddedItemsToReturn"\n        @modal-close="onCloseReturnModal"\n    />\n\n    <swag-return-management-set-item-status-modal\n        v-if="showItemStatusModal"\n        :line-items="selectedChangeStatusItems"\n        entity-name="orderLineItem"\n        @set-status-success="onSetStatusSuccess"\n        @modal-close="onCloseItemStatusModal"\n    />\n\n    <swag-return-management-delete-line-item-modal\n        v-if="showDeleteLineItemModal"\n        :return-id="returnId"\n        :line-items="deleteLineItems"\n        :context="context"\n        @item-delete="onDeleteItemHasReturn"\n        @modal-close="onCloseDeleteLineItemModal"\n    />\n</sw-container>\n{% endblock %}\n\n{% block sw_order_line_items_grid_bulk_actions_extension %}\n    \n    <a\n        v-if="hasToggleKey"\n        v-tooltip="{\n            message: $tc(\'sw-privileges.tooltip.warning\'),\n            disabled: acl.can(\'order_return.editor\'),\n            showOnDisabledElements: true\n        }"\n        class="link link-primary sw-order-line-items-grid__return-items"\n        :class="returnItemButtonClass"\n        @click="onReturnSelectedItems(null)"\n    >\n        {{ $tc(\'swag-return-management.detail.buttonReturnItems\') }}\n        <sw-help-text\n            v-if="acl.can(\'order_return.editor\')"\n            :text="$tc(\'swag-return-management.detail.messageErrorCreateReturnModal\')"\n        />\n    </a>\n\n    \n    <a\n        v-if="hasToggleKey"\n        class="link link-primary sw-order-line-items-grid__set-status"\n        @click="onSetItemStatus(null)"\n    >\n        {{ $tc(\'swag-return-management.detail.buttonSetStatus\') }}\n    </a>\n{% endblock %}\n\n{% block sw_order_line_items_grid_grid_actions_extension %}\n    <sw-context-menu-item\n        v-if="showStatusItemAction(item) && hasToggleKey"\n        v-tooltip.left="{\n            message: $tc(\'sw-privileges.tooltip.warning\'),\n            disabled: acl.can(\'order.editor\'),\n            showOnDisabledElements: true\n        }"\n        :disabled="!acl.can(\'order.editor\')"\n        @click="onSetItemStatus(item)"\n    >\n        {{ $tc(\'swag-return-management.detail.contextMenuSetStatus\') }}\n    </sw-context-menu-item>\n\n    <sw-context-menu-item\n        v-if="showStatusItemAction(item) && hasToggleKey"\n        v-tooltip.left="tooltipReturnContextMenu(item)"\n        :disabled="!isReturnableItem(item)"\n        @click="onReturnSelectedItems(item)"\n    >\n        {{ $tc(\'swag-return-management.returnItemGrid.contextMenuReturn\') }}\n    </sw-context-menu-item>\n{% endblock %}\n\n{% block sw_order_line_items_grid_grid_columns %}\n    {% parent %}\n\n    <template #column-extensions.state.name="{ item }">\n        {{ getItemStatus(item) }}\n\n        <sw-icon\n            v-if="showSetStatusManuallyWarning(item)"\n            v-tooltip="{ message: $tc(\'swag-return-management.detail.tooltipChangeItemStatusManually\') }"\n            class="sw-order-line-items-grid__status-warning"\n            name="solid-exclamation-circle"\n            size="12px"\n        />\n    </template>\n{% endblock %}\n',mixins:[u.getByName("notification")],data:function(){return{returnItems:[],showReturnModal:!1,showItemStatusModal:!1,showDeleteLineItemModal:!1,selectedChangeStatusItems:null,deleteLineItems:[]}},computed:{hasOrderReturn:function(){var e,t,n;return(null===(e=this.order)||void 0===e||null===(t=e.extensions)||void 0===t||null===(n=t.returns)||void 0===n?void 0:n.length)>0},getLineItemColumns:function(){var e=this.$super("getLineItemColumns");return[].concat(s(e.slice(0,2)),[{property:"extensions.state.name",label:"swag-return-management.returnItemGrid.columnStatus",allowResize:!1}],s(e.slice(2)))},returnId:function(){var e,t,n;return(null===(e=this.order)||void 0===e||null===(t=e.extensions)||void 0===t||null===(n=t.returns[0])||void 0===n?void 0:n.id)||""},returnItemButtonClass:function(){return{"is--disabled":!this.acl.can("order_return.editor")}},hasToggleKey:function(){return Shopware.License.get(i.a)}},methods:{onInlineEditSave:function(e){var t,n,r,i;return e.quantity<(null===(t=e.extensions)||void 0===t||null===(n=t.returns[0])||void 0===n?void 0:n.quantity)?(this.orderLineItemRepository.discard(e),void this.createNotificationError({message:this.$tc("swag-return-management.notification.messageErrorLineItemQuantity",0,{returnQuantity:null===(r=e.extensions)||void 0===r||null===(i=r.returns[0])||void 0===i?void 0:i.quantity})})):this.$super("onInlineEditSave",e)},isReturnableItem:function(e){if(!this.acl.can("order_return.editor"))return!1;var t=[r.b.CANCELLED,r.b.RETURNED_PARTIALLY,r.b.RETURNED];return!this.isItemAddedToReturn(e)&&e.type!==this.lineItemTypes.CREDIT&&e.type!==this.lineItemTypes.PROMOTION&&!this.itemHasReturnStatus(t,e)&&!this.isDigitalProduct(e)},isDigitalProduct:function(e){var t;return null===(t=e.states)||void 0===t?void 0:t.includes("is-download")},onReturnSelectedItems:function(e){var t=this;if(this.acl.can("order_return.editor"))if(Shopware.License.get(i.c)){Shopware.Application.getContainer("init").httpClient.get("_info/config",{headers:{Accept:"application/vnd.api+json",Authorization:"Bearer ".concat(Shopware.Service("loginService").getToken()),"Content-Type":"application/json","sw-license-toggle":i.c}})}else{var n=e?[e]:Object.values(this.selectedItems);this.returnItems=Object.values(n).filter((function(e){return e.type!==t.lineItemTypes.CREDIT&&e.type!==t.lineItemTypes.PROMOTION&&!t.isDigitalProduct(e)})),this.showReturnModal=!0}},onCloseReturnModal:function(){this.showReturnModal=!1},onReturnCreate:function(){this.showReturnModal=!1,this.returnItems=[],this.$refs.dataGrid.resetSelection(),this.$emit("save-edits")},onAddedItemsToReturn:function(e){this.showReturnModal=!1,this.returnItems=[],this.$refs.dataGrid.resetSelection(),this.$emit("save-and-reload",e)},getItemStatus:function(e){var t,n,r,i;return null!==(t=null==e||null===(n=e.extensions)||void 0===n||null===(r=n.state)||void 0===r||null===(i=r.translated)||void 0===i?void 0:i.name)&&void 0!==t?t:""},showStatusItemAction:function(e){return e.type!==this.lineItemTypes.CREDIT&&e.type!==this.lineItemTypes.PROMOTION},onSetItemStatus:function(e){var t=this;if(Shopware.License.get(i.c)){Shopware.Application.getContainer("init").httpClient.get("_info/config",{headers:{Accept:"application/vnd.api+json",Authorization:"Bearer ".concat(Shopware.Service("loginService").getToken()),"Content-Type":"application/json","sw-license-toggle":i.c}})}else{var n=e?[e]:Object.values(this.selectedItems);this.selectedChangeStatusItems=n.filter((function(e){return t.showStatusItemAction(e)})),this.selectedChangeStatusItems.length?this.showItemStatusModal=!0:this.createNotificationError({message:this.$tc("swag-return-management.detail.messageErrorSetStatusModal")})}},onCloseItemStatusModal:function(){this.showItemStatusModal=!1,this.selectedChangeStatusItem=null},onSetStatusSuccess:function(){this.showItemStatusModal=!1,this.selectedChangeStatusItem=null,this.$refs.dataGrid.resetSelection(),this.$emit("save-and-reload")},onDeleteSelectedItems:function(){if(Object.values(this.selectedItems).some((function(e){var t,n;return(null==e||null===(t=e.extensions)||void 0===t||null===(n=t.returns)||void 0===n?void 0:n.length)>0})))return this.showDeleteLineItemModal=!0,void(this.deleteLineItems=Object.values(this.selectedItems));this.$super("onDeleteSelectedItems")},onDeleteItem:function(e,t){var n,r;if((null==e||null===(n=e.extensions)||void 0===n||null===(r=n.returns)||void 0===r?void 0:r.length)>0)return this.showDeleteLineItemModal=!0,void(this.deleteLineItems=[e]);this.$super("onDeleteItem",e,t)},onCloseDeleteLineItemModal:function(){this.showDeleteLineItemModal=!1,this.deleteLineItems=[]},onDeleteItemHasReturn:function(){this.$emit("item-delete"),this.$refs.dataGrid.resetSelection(),this.showDeleteLineItemModal=!1,this.deleteLineItems=[]},tooltipReturnContextMenu:function(e){return{message:this.acl.can("order_return.editor")?this.$tc("swag-return-management.detail.messageErrorCreateReturnModal"):this.$tc("sw-privileges.tooltip.warning"),showOnDisabledElements:!0,disabled:this.isReturnableItem(e)}},isItemAddedToReturn:function(e){var t,n;return(null==e||null===(t=e.extensions)||void 0===t||null===(n=t.returns)||void 0===n?void 0:n.length)>0},itemHasReturnStatus:function(e,t){var n,r;return e.includes(null==t||null===(n=t.extensions)||void 0===n||null===(r=n.state)||void 0===r?void 0:r.technicalName)},showSetStatusManuallyWarning:function(e){var t=[r.b.RETURN_REQUESTED,r.b.RETURNED_PARTIALLY,r.b.RETURNED];return!this.isItemAddedToReturn(e)&&this.itemHasReturnStatus(t,e)&&this.hasOrderReturn}}})},"0dwe":function(e,t,n){},P8hj:function(e,t,n){"use strict";function r(e,t){for(var n=[],r={},i=0;i<t.length;i++){var s=t[i],o=s[0],a={id:e+":"+i,css:s[1],media:s[2],sourceMap:s[3]};r[o]?r[o].parts.push(a):n.push(r[o]={id:o,parts:[a]})}return n}n.r(t),n.d(t,"default",(function(){return p}));var i="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!i)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var s={},o=i&&(document.head||document.getElementsByTagName("head")[0]),a=null,l=0,u=!1,d=function(){},c=null,m="data-vue-ssr-id",h="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function p(e,t,n,i){u=n,c=i||{};var o=r(e,t);return f(o),function(t){for(var n=[],i=0;i<o.length;i++){var a=o[i];(l=s[a.id]).refs--,n.push(l)}t?f(o=r(e,t)):o=[];for(i=0;i<n.length;i++){var l;if(0===(l=n[i]).refs){for(var u=0;u<l.parts.length;u++)l.parts[u]();delete s[l.id]}}}}function f(e){for(var t=0;t<e.length;t++){var n=e[t],r=s[n.id];if(r){r.refs++;for(var i=0;i<r.parts.length;i++)r.parts[i](n.parts[i]);for(;i<n.parts.length;i++)r.parts.push(v(n.parts[i]));r.parts.length>n.parts.length&&(r.parts.length=n.parts.length)}else{var o=[];for(i=0;i<n.parts.length;i++)o.push(v(n.parts[i]));s[n.id]={id:n.id,refs:1,parts:o}}}}function g(){var e=document.createElement("style");return e.type="text/css",o.appendChild(e),e}function v(e){var t,n,r=document.querySelector("style["+m+'~="'+e.id+'"]');if(r){if(u)return d;r.parentNode.removeChild(r)}if(h){var i=l++;r=a||(a=g()),t=S.bind(null,r,i,!1),n=S.bind(null,r,i,!0)}else r=g(),t=R.bind(null,r),n=function(){r.parentNode.removeChild(r)};return t(e),function(r){if(r){if(r.css===e.css&&r.media===e.media&&r.sourceMap===e.sourceMap)return;t(e=r)}else n()}}var I,w=(I=[],function(e,t){return I[e]=t,I.filter(Boolean).join("\n")});function S(e,t,n,r){var i=n?"":r.css;if(e.styleSheet)e.styleSheet.cssText=w(t,i);else{var s=document.createTextNode(i),o=e.childNodes;o[t]&&e.removeChild(o[t]),o.length?e.insertBefore(s,o[t]):e.appendChild(s)}}function R(e,t){var n=t.css,r=t.media,i=t.sourceMap;if(r&&e.setAttribute("media",r),c.ssrId&&e.setAttribute(m,t.id),i&&(n+="\n/*# sourceURL="+i.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(i))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}},"cH0/":function(e,t,n){var r=n("0dwe");r.__esModule&&(r=r.default),"string"==typeof r&&(r=[[e.i,r,""]]),r.locals&&(e.exports=r.locals);(0,n("P8hj").default)("ccdb9434",r,!0,{})},wR8H:function(e,t,n){"use strict";n.d(t,"b",(function(){return r})),n.d(t,"a",(function(){return i}));var r=function(e){return e.OPEN="open",e.SHIPPED="shipped",e.SHIPPED_PARTIALLY="shipped_partially",e.RETURN_REQUESTED="return_requested",e.RETURNED="returned",e.RETURNED_PARTIALLY="returned_partially",e.CANCELLED="cancelled",e}(r||{}),i=function(e){return e.STORNO="storno",e.CREDIT_NOTE="credit_note",e.PARTIAL_CANCELLATION="partial_cancellation",e.INVOICE="invoice",e.DELIVERY_NOTE="delivery_note",e}(i||{})}}]);