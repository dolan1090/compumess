/*! For license information please see 2.js.LICENSE.txt */
(this["webpackJsonpPluginreturn-management"]=this["webpackJsonpPluginreturn-management"]||[]).push([[2],{"9Mn5":function(e,t,n){},"BO/6":function(e,t,n){"use strict";n.r(t);var r=function(e){return e.PRODUCT="product",e.CREDIT="credit",e.CUSTOM="custom",e.PROMOTION="promotion",e}(r||{}),i=function(e){return e.PERCENTAGE="percentage",e.ABSOLUTE="absolute",e.FIXED="fixed",e.FIXED_UNIT="fixed_unit",e}(i||{}),a=function(e){return e.CART="cart",e.DELIVERY="delivery",e.SET="set",e.SETGROUP="setgroup",e}(a||{}),o=(n("Qwqh"),n("wR8H"));function s(e){return(s="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function u(){u=function(){return e};var e={},t=Object.prototype,n=t.hasOwnProperty,r=Object.defineProperty||function(e,t,n){e[t]=n.value},i="function"==typeof Symbol?Symbol:{},a=i.iterator||"@@iterator",o=i.asyncIterator||"@@asyncIterator",l=i.toStringTag||"@@toStringTag";function c(e,t,n){return Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}),e[t]}try{c({},"")}catch(e){c=function(e,t,n){return e[t]=n}}function m(e,t,n,i){var a=t&&t.prototype instanceof f?t:f,o=Object.create(a.prototype),s=new P(i||[]);return r(o,"_invoke",{value:x(e,n,s)}),o}function d(e,t,n){try{return{type:"normal",arg:e.call(t,n)}}catch(e){return{type:"throw",arg:e}}}e.wrap=m;var p={};function f(){}function h(){}function g(){}var v={};c(v,a,(function(){return this}));var y=Object.getPrototypeOf,w=y&&y(y(_([])));w&&w!==t&&n.call(w,a)&&(v=w);var I=g.prototype=f.prototype=Object.create(v);function b(e){["next","throw","return"].forEach((function(t){c(e,t,(function(e){return this._invoke(t,e)}))}))}function S(e,t){function i(r,a,o,u){var l=d(e[r],e,a);if("throw"!==l.type){var c=l.arg,m=c.value;return m&&"object"==s(m)&&n.call(m,"__await")?t.resolve(m.__await).then((function(e){i("next",e,o,u)}),(function(e){i("throw",e,o,u)})):t.resolve(m).then((function(e){c.value=e,o(c)}),(function(e){return i("throw",e,o,u)}))}u(l.arg)}var a;r(this,"_invoke",{value:function(e,n){function r(){return new t((function(t,r){i(e,n,t,r)}))}return a=a?a.then(r,r):r()}})}function x(e,t,n){var r="suspendedStart";return function(i,a){if("executing"===r)throw new Error("Generator is already running");if("completed"===r){if("throw"===i)throw a;return O()}for(n.method=i,n.arg=a;;){var o=n.delegate;if(o){var s=E(o,n);if(s){if(s===p)continue;return s}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if("suspendedStart"===r)throw r="completed",n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);r="executing";var u=d(e,t,n);if("normal"===u.type){if(r=n.done?"completed":"suspendedYield",u.arg===p)continue;return{value:u.arg,done:n.done}}"throw"===u.type&&(r="completed",n.method="throw",n.arg=u.arg)}}}function E(e,t){var n=t.method,r=e.iterator[n];if(void 0===r)return t.delegate=null,"throw"===n&&e.iterator.return&&(t.method="return",t.arg=void 0,E(e,t),"throw"===t.method)||"return"!==n&&(t.method="throw",t.arg=new TypeError("The iterator does not provide a '"+n+"' method")),p;var i=d(r,e.iterator,t.arg);if("throw"===i.type)return t.method="throw",t.arg=i.arg,t.delegate=null,p;var a=i.arg;return a?a.done?(t[e.resultName]=a.value,t.next=e.nextLoc,"return"!==t.method&&(t.method="next",t.arg=void 0),t.delegate=null,p):a:(t.method="throw",t.arg=new TypeError("iterator result is not an object"),t.delegate=null,p)}function R(e){var t={tryLoc:e[0]};1 in e&&(t.catchLoc=e[1]),2 in e&&(t.finallyLoc=e[2],t.afterLoc=e[3]),this.tryEntries.push(t)}function L(e){var t=e.completion||{};t.type="normal",delete t.arg,e.completion=t}function P(e){this.tryEntries=[{tryLoc:"root"}],e.forEach(R,this),this.reset(!0)}function _(e){if(e){var t=e[a];if(t)return t.call(e);if("function"==typeof e.next)return e;if(!isNaN(e.length)){var r=-1,i=function t(){for(;++r<e.length;)if(n.call(e,r))return t.value=e[r],t.done=!1,t;return t.value=void 0,t.done=!0,t};return i.next=i}}return{next:O}}function O(){return{value:void 0,done:!0}}return h.prototype=g,r(I,"constructor",{value:g,configurable:!0}),r(g,"constructor",{value:h,configurable:!0}),h.displayName=c(g,l,"GeneratorFunction"),e.isGeneratorFunction=function(e){var t="function"==typeof e&&e.constructor;return!!t&&(t===h||"GeneratorFunction"===(t.displayName||t.name))},e.mark=function(e){return Object.setPrototypeOf?Object.setPrototypeOf(e,g):(e.__proto__=g,c(e,l,"GeneratorFunction")),e.prototype=Object.create(I),e},e.awrap=function(e){return{__await:e}},b(S.prototype),c(S.prototype,o,(function(){return this})),e.AsyncIterator=S,e.async=function(t,n,r,i,a){void 0===a&&(a=Promise);var o=new S(m(t,n,r,i),a);return e.isGeneratorFunction(n)?o:o.next().then((function(e){return e.done?e.value:o.next()}))},b(I),c(I,l,"Generator"),c(I,a,(function(){return this})),c(I,"toString",(function(){return"[object Generator]"})),e.keys=function(e){var t=Object(e),n=[];for(var r in t)n.push(r);return n.reverse(),function e(){for(;n.length;){var r=n.pop();if(r in t)return e.value=r,e.done=!1,e}return e.done=!0,e}},e.values=_,P.prototype={constructor:P,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=void 0,this.done=!1,this.delegate=null,this.method="next",this.arg=void 0,this.tryEntries.forEach(L),!e)for(var t in this)"t"===t.charAt(0)&&n.call(this,t)&&!isNaN(+t.slice(1))&&(this[t]=void 0)},stop:function(){this.done=!0;var e=this.tryEntries[0].completion;if("throw"===e.type)throw e.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var t=this;function r(n,r){return o.type="throw",o.arg=e,t.next=n,r&&(t.method="next",t.arg=void 0),!!r}for(var i=this.tryEntries.length-1;i>=0;--i){var a=this.tryEntries[i],o=a.completion;if("root"===a.tryLoc)return r("end");if(a.tryLoc<=this.prev){var s=n.call(a,"catchLoc"),u=n.call(a,"finallyLoc");if(s&&u){if(this.prev<a.catchLoc)return r(a.catchLoc,!0);if(this.prev<a.finallyLoc)return r(a.finallyLoc)}else if(s){if(this.prev<a.catchLoc)return r(a.catchLoc,!0)}else{if(!u)throw new Error("try statement without catch or finally");if(this.prev<a.finallyLoc)return r(a.finallyLoc)}}}},abrupt:function(e,t){for(var r=this.tryEntries.length-1;r>=0;--r){var i=this.tryEntries[r];if(i.tryLoc<=this.prev&&n.call(i,"finallyLoc")&&this.prev<i.finallyLoc){var a=i;break}}a&&("break"===e||"continue"===e)&&a.tryLoc<=t&&t<=a.finallyLoc&&(a=null);var o=a?a.completion:{};return o.type=e,o.arg=t,a?(this.method="next",this.next=a.finallyLoc,p):this.complete(o)},complete:function(e,t){if("throw"===e.type)throw e.arg;return"break"===e.type||"continue"===e.type?this.next=e.arg:"return"===e.type?(this.rval=this.arg=e.arg,this.method="return",this.next="end"):"normal"===e.type&&t&&(this.next=t),p},finish:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var n=this.tryEntries[t];if(n.finallyLoc===e)return this.complete(n.completion,n.afterLoc),L(n),p}},catch:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var n=this.tryEntries[t];if(n.tryLoc===e){var r=n.completion;if("throw"===r.type){var i=r.arg;L(n)}return i}}throw new Error("illegal catch attempt")},delegateYield:function(e,t,n){return this.delegate={iterator:_(e),resultName:t,nextLoc:n},"next"===this.method&&(this.arg=void 0),p}},e}function l(e,t,n,r,i,a,o){try{var s=e[a](o),u=s.value}catch(e){return void n(e)}s.done?t(u):Promise.resolve(u).then(r,i)}function c(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function m(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?c(Object(n),!0).forEach((function(t){d(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):c(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function d(e,t,n){return(t=function(e){var t=function(e,t){if("object"!==s(e)||null===e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var r=n.call(e,t||"default");if("object"!==s(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===s(t)?t:String(t)}(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}var p=Shopware,f=p.Component,h=p.Utils,g=p.Mixin,v=h.format,y=f.getComponentHelper().mapState;t.default=f.wrapComponentConfig({template:'<div\n    class="swag-return-management-return-line-items-grid"\n>\n    <sw-alert\n        v-if="showDiscountWarning"\n        variant="warning"\n        class="swag-return-management-return-line-items-grid__warning"\n    >\n       <strong>{{ $tc(\'swag-return-management.returnItemGrid.discountWarning.textOrderHasDiscount\') }}</strong>\n            <ul>\n                <li v-for="item in discountLineItems">\n                    {{ item.label + ": " + getDescription(item) }}\n                </li>\n            </ul>\n        <p>{{ $tc(\'swag-return-management.returnItemGrid.discountWarning.textCheckRefundAmount\') }}</p>\n    </sw-alert>\n\n    <sw-container\n        class="swag-return-management-return-line-items-grid__actions-container"\n        columns="1fr"\n    >\n        <sw-card-filter\n            ref="itemFilter"\n            :placeholder="$tc(\'swag-return-management.returnItemGrid.placeholderSearchBarItem\')"\n            @sw-card-filter-term-change="onSearchTermChange"\n        />\n    </sw-container>\n\n    <sw-empty-state\n        v-if="orderReturnLineItems.length === 0"\n        :title="$tc(\'swag-return-management.returnItemGrid.textNoItemFound\')"\n        :absolute="false"\n        :show-description="false"\n    >\n        <template #icon>\n            <img\n                :src="\'/administration/static/img/empty-states/products-empty-state.svg\' | asset"\n                :alt="$tc(\'swag-return-management.returnItemGrid.textNoItemFound\')"\n            >\n        </template>\n    </sw-empty-state>\n\n    <sw-data-grid\n        v-else\n        ref="dataGrid"\n        class="swag-return-management-return-line-items-grid__data-grid"\n        identifier="swag-return-management-line-item-grid"\n        :allow-inline-edit="acl.can(\'order_return.editor\')"\n        :data-source="orderReturnLineItems"\n        :columns="getLineItemColumns"\n        :is-loading="isLoading"\n        :show-selection="acl.can(\'order_return.editor\')"\n        show-settings\n        @inline-edit-cancel="onInlineEditCancel"\n        @inline-edit-save="onInlineEditSave"\n        @selection-change="onSelectionChange"\n    >\n        <template #column-lineItem.label="{ item }">\n            <div\n                v-if="isProductItem(item.lineItem)"\n                class="swag-return-management-return-line-items-grid__item-product"\n            >\n                <router-link\n                    v-if="item.lineItem.payload && item.lineItem.payload.options"\n                    class="swag-return-management-return-line-items-grid__item-payload-options"\n                    :to="{ name: \'sw.product.detail\', params: { id: item.lineItem.productId } }"\n                >\n                    <sw-product-variant-info :variations="item.lineItem.payload.options">\n                        <div class="swag-return-management-return-line-items-grid__item-label">\n                            {{ item.lineItem.label }}\n                        </div>\n                    </sw-product-variant-info>\n                </router-link>\n\n                <template\n                    v-else\n                    class="swag-return-management-return-line-items-grid__item-payload-options"\n                >\n                    <span class="swag-return-management-return-line-items-grid__item-label">\n                        {{ item.lineItem.label }}\n                    </span>\n                </template>\n            </div>\n\n            <span\n                v-else\n                class="swag-return-management-return-line-items-grid__item-label"\n            >\n                {{ item.lineItem.label }}\n            </span>\n        </template>\n\n        <template #column-state.name="{item}">\n            {{ (getItemStatus(item)) }}\n        </template>\n\n        <template #column-price.unitPrice="{ item }">\n            <span>{{ item.price.unitPrice | currency(order.currency.shortName, order.itemRounding.decimals) }}</span>\n        </template>\n\n        <template #column-refundAmount="{ item, isInlineEdit }">\n            <sw-number-field\n                v-if="isInlineEdit"\n                v-model="item.refundAmount"\n                :min="0"\n                size="small"\n            />\n            <span v-else>\n                {{ item.refundAmount | currency(order.currency.shortName, order.itemRounding.decimals) }}\n            </span>\n\n            <sw-help-text\n                v-if="showSuggestedPriceHelpText(item)"\n                class="swag-return-management-return-line-items-grid__item-price-suggestion"\n                :text="getSuggestedPrice(item)"\n            />\n        </template>\n\n        <template #column-quantity="{ item, isInlineEdit }">\n            <sw-number-field\n                v-if="isInlineEdit"\n                v-model="item.quantity"\n                :min="0"\n                :max="getItemMaxQuantity(item)"\n                @change="(value) => onChangeQuantity(value, item)"\n                size="small"\n            />\n            <span v-else>{{ item.quantity }} x</span>\n        </template>\n\n        <template #column-price.taxRules[0]="{ item }">\n            <span>\n                {{ showTaxValue(item) }}\n            </span>\n        </template>\n\n        <template #column-price.totalPrice="{ item }">\n            <span>{{ item.price.totalPrice | currency(order.currency.shortName, order.itemRounding.decimals) }}</span>\n        </template>\n\n        <template #actions="{ item }">\n            <sw-context-menu-item\n                class="swag-return-management-line-items-grid__show-product"\n                :disabled="!isProductItem(item.lineItem)"\n                :router-link="{ name: \'sw.product.detail\', params: { id: item.lineItem.productId } }"\n            >\n                {{ $tc(\'swag-return-management.returnItemGrid.contextMenuShowProduct\') }}\n            </sw-context-menu-item>\n\n            <sw-context-menu-item\n                class="swag-return-management-line-items-grid__open-item-detail"\n                @click="onOpenItemDetail(item)"\n            >\n                {{ $tc(\'swag-return-management.returnItemGrid.contextMenuOpenReturnDetails\') }}\n            </sw-context-menu-item>\n\n            <sw-context-menu-item\n                v-tooltip.left="{\n                    message: $tc(\'sw-privileges.tooltip.warning\'),\n                    disabled: acl.can(\'order_return.editor\'),\n                    showOnDisabledElements: true\n                }"\n                class="swag-return-management-line-items-grid__set-status"\n                :disabled="!acl.can(\'order_return.editor\')"\n                @click="onSetItemStatus(item)"\n            >\n                {{ $tc(\'swag-return-management.returnItemGrid.contextMenuSetStatus\') }}\n            </sw-context-menu-item>\n\n            <sw-context-menu-item\n                v-tooltip.left="{\n                    message: $tc(\'sw-privileges.tooltip.warning\'),\n                    disabled: acl.can(\'order_return.editor\'),\n                    showOnDisabledElements: true\n                }"\n                class="swag-return-management-line-items-grid__remove"\n                variant="danger"\n                :disabled="!acl.can(\'order_return.editor\')"\n                @click="onDeleteItem(item)"\n            >\n                {{ $tc(\'swag-return-management.returnItemGrid.contextMenuDelete\') }}\n            </sw-context-menu-item>\n\n        </template>\n\n        <template #bulk>\n            \n            <a\n                class="link link-danger"\n                @click="onDeleteItem(null)"\n            >\n                {{ $tc(\'global.default.delete\') }}\n            </a>\n\n            \n            <a\n                class="link link-primary"\n                @click="onSetItemStatus(null)"\n            >\n                {{ $tc(\'swag-return-management.returnItemGrid.buttonSetStatus\') }}\n            </a>\n        </template>\n\n    </sw-data-grid>\n\n    <swag-return-management-set-item-status-modal\n        v-if="showItemStatusModal"\n        :line-items="selectedActionItems"\n        :excluded-states="excludedStates"\n        entity-name="orderReturnLineItem"\n        @set-status-success="onSetStatusSuccess"\n        @modal-close="onCloseItemStatusModal"\n    />\n\n    <swag-return-management-delete-return-item-modal\n        v-if="showDeleteReturnItemModal"\n        :return-id="returnId"\n        :items="selectedActionItems"\n        :context="versionContext"\n        @item-delete="onDeleteItemSuccess"\n        @modal-close="onCloseDeleteReturnItemModal"\n    />\n\n    <swag-return-management-item-detail-modal\n        v-if="showItemDetailsModal"\n        :item="selectedItemOpenDetail"\n        @update-item-success="onUpdateItemSuccess"\n        @modal-close="onCloseItemDetailModal"\n    />\n</div>\n',mixins:[g.getByName("notification")],inject:["acl","repositoryFactory","orderReturnApiService"],props:{returnId:{type:String,required:!0},returnLineItems:{type:Array,required:!0},taxStatus:{type:String,required:!0}},data:function(){return{isLoading:!1,selectedItems:{},searchTerm:"",showItemStatusModal:!1,showDeleteReturnItemModal:!1,showItemDetailsModal:!1,selectedActionItems:null,selectedItemOpenDetail:null}},computed:m(m({},y("swOrderDetail",["order","versionContext"])),{},{orderReturnLineItemRepository:function(){return this.repositoryFactory.create("order_return_line_item")},orderReturnLineItems:function(){if(!this.searchTerm)return this.returnLineItems;var e=this.searchTerm.split(/[\W_]+/gi);return this.returnLineItems.filter((function(t){return!!t.lineItem.label&&e.every((function(e){return t.lineItem.label.toLowerCase().includes(e.toLowerCase())}))}))},unitPriceLabel:function(){return"net"===this.taxStatus?this.$tc("swag-return-management.returnItemGrid.columnPriceNet"):"tax-free"===this.taxStatus?this.$tc("swag-return-management.returnItemGrid.columnPriceTaxFree"):this.$tc("swag-return-management.returnItemGrid.columnPriceGross")},getLineItemColumns:function(){var e=[{property:"quantity",label:"swag-return-management.returnItemGrid.columnQuantity",allowResize:!1,align:"right",width:"90px",inlineEdit:!0},{property:"lineItem.label",label:"swag-return-management.returnItemGrid.columnProductName",allowResize:!1,primary:!0,multiLine:!0},{property:"state.name",label:"swag-return-management.returnItemGrid.columnStatus",allowResize:!1,multiLine:!0},{property:"price.unitPrice",label:this.unitPriceLabel,allowResize:!1,align:"right",width:"120px"}];return"tax-free"!==this.taxStatus&&e.push({property:"price.taxRules[0]",label:"swag-return-management.returnItemGrid.columnTax",allowResize:!1,align:"right",width:"90px"}),[].concat(e,[{property:"price.totalPrice",label:"swag-return-management.returnItemGrid.columnSubTotal",allowResize:!1,align:"right",width:"120px"},{property:"refundAmount",label:"swag-return-management.returnItemGrid.columnRefund",allowResize:!1,align:"right",width:"120px",inlineEdit:!0}])},showDiscountWarning:function(){return this.discountLineItems.length>0&&this.returnLineItems.length>0},discountLineItems:function(){var e,t;return null===(e=this.order)||void 0===e||null===(t=e.lineItems)||void 0===t?void 0:t.filter((function(e){return e.type===r.PROMOTION||e.type===r.CREDIT}))},excludedStates:function(){return[o.b.OPEN,o.b.SHIPPED,o.b.SHIPPED_PARTIALLY,o.b.CANCELLED]}}),created:function(){var e=this;this.discountLineItems.forEach((function(t){var n,i;t.type!==r.CREDIT&&(null==t||null===(n=t.payload)||void 0===n||null===(i=n.composition)||void 0===i||i.forEach((function(t){e.returnLineItems.forEach((function(n,r){t.id===n.lineItem.identifier&&(e.returnLineItems[r].discount||(e.returnLineItems[r].discount=[]),e.returnLineItems[r].discount.push(t))}))})))}))},methods:{onSelectionChange:function(e){this.selectedItems=e},onChangeQuantity:function(e,t){t.quantity=e,t.refundAmount=t.price.unitPrice*t.quantity},showTaxValue:function(e){return"".concat(e.price.taxRules[0].taxRate," %")},onDeleteItem:function(e){this.selectedActionItems=e?[e]:Object.values(this.selectedItems),this.showDeleteReturnItemModal=!0},onSearchTermChange:function(e){this.searchTerm=e.toLowerCase()},isProductItem:function(e){return e.type===r.PRODUCT},getItemStatus:function(e){var t,n,r;return null!==(t=null==e||null===(n=e.state)||void 0===n||null===(r=n.translated)||void 0===r?void 0:r.name)&&void 0!==t?t:""},getItemStatusTechnicalName:function(e){var t,n;return null!==(t=null==e||null===(n=e.state)||void 0===n?void 0:n.technicalName)&&void 0!==t?t:""},onSetItemStatus:function(e){this.selectedActionItems=e?[e]:Object.values(this.selectedItems),this.showItemStatusModal=!0},onOpenItemDetail:function(e){this.selectedItemOpenDetail=e,this.showItemDetailsModal=!0},onCloseItemDetailModal:function(){this.selectedItemOpenDetail=null,this.showItemDetailsModal=!1},onCloseItemStatusModal:function(){this.showItemStatusModal=!1,this.selectedChangeStatusItem=null},updateStatusOrderLineItem:function(e,t){return this.orderReturnApiService.changeStateOrderLineItem(e,{ids:[t.lineItem.id]},this.versionContext.versionId)},updateRefundAmount:function(e){var t,n=this;return(t=u().mark((function t(){var r;return u().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.prev=0,t.next=3,n.orderReturnLineItemRepository.save(e,n.versionContext);case 3:return t.next=5,n.orderReturnApiService.recalculateRefundAmount(n.returnId,n.versionContext.versionId);case 5:if(n.getItemStatusTechnicalName(e)===o.b.RETURNED){t.next=8;break}return n.$emit("reload-data"),t.abrupt("return");case 8:return r=e.quantity===n.getItemMaxQuantity(e)?o.b.RETURNED:o.b.RETURNED_PARTIALLY,t.next=11,n.updateStatusOrderLineItem(r,e);case 11:n.$emit("reload-data"),t.next=17;break;case 14:t.prev=14,t.t0=t.catch(0),n.createNotificationError({message:n.$tc("swag-return-management.notification.labelErrorUpdateRefund")+t.t0});case 17:case"end":return t.stop()}}),t,null,[[0,14]])})),function(){var e=this,n=arguments;return new Promise((function(r,i){var a=t.apply(e,n);function o(e){l(a,r,i,o,s,"next",e)}function s(e){l(a,r,i,o,s,"throw",e)}o(void 0)}))})()},onSetStatusSuccess:function(){this.onCloseItemStatusModal(),this.$refs.dataGrid.resetSelection(),this.$emit("reload-data")},onDeleteItemSuccess:function(){this.onCloseDeleteReturnItemModal(),this.$refs.dataGrid.resetSelection(),this.$emit("reload-data")},onCloseDeleteReturnItemModal:function(){this.showDeleteReturnItemModal=!1,this.selectedChangeStatusItem=null},onUpdateItemSuccess:function(){this.onCloseItemDetailModal(),this.$emit("reload-data")},onInlineEditCancel:function(e){this.orderReturnLineItemRepository.hasChanges(e)&&this.orderReturnLineItemRepository.discard(e)},onInlineEditSave:function(e){this.orderReturnLineItemRepository.hasChanges(e)&&this.updateRefundAmount(e)},getDescription:function(e){var t,n,o=e.price.totalPrice,s=null===(t=this.order)||void 0===t||null===(n=t.currency)||void 0===n?void 0:n.shortName;if(e.type===r.CREDIT)return this.$tc("swag-return-management.returnItemGrid.discountWarning.textCreditDescription",0,{value:v.currency(Math.abs(o),s,2)});var u=e.payload,l=u.value,c=u.discountScope,m=u.discountType,d=u.groupId,p="sw-order.createBase.textPromotionDescription.".concat(c);if(c===a.CART&&m===i.ABSOLUTE&&Math.abs(o)<l)return this.$tc("".concat(p,".absoluteUpto"),0,{value:v.currency(Number(l),s,2),totalPrice:v.currency(Math.abs(o),s,2)});var f=m===i.PERCENTAGE?l:v.currency(Number(l),s,2);return this.$tc("".concat(p,".").concat(m),0,{value:f,groupId:d})},getSuggestedPrice:function(e){var t,n,r,i,a,o=0;null==e||null===(t=e.discount)||void 0===t||t.forEach((function(t){var n=t.discount*e.quantity/t.quantity;o+=n}));var s=e.price.totalPrice-o;return this.$tc("swag-return-management.returnItemGrid.tooltipSuggestedPrice",0,{price:v.currency(s,null===(n=this.order)||void 0===n||null===(r=n.currency)||void 0===r?void 0:r.shortName,null===(i=this.order)||void 0===i||null===(a=i.totalRounding)||void 0===a?void 0:a.decimals)})},getItemMaxQuantity:function(e){var t,n;return null!==(t=null==e||null===(n=e.lineItem)||void 0===n?void 0:n.quantity)&&void 0!==t?t:null},showSuggestedPriceHelpText:function(e){var t;return(null==e||null===(t=e.discount)||void 0===t?void 0:t.length)>0}}})},P8hj:function(e,t,n){"use strict";function r(e,t){for(var n=[],r={},i=0;i<t.length;i++){var a=t[i],o=a[0],s={id:e+":"+i,css:a[1],media:a[2],sourceMap:a[3]};r[o]?r[o].parts.push(s):n.push(r[o]={id:o,parts:[s]})}return n}n.r(t),n.d(t,"default",(function(){return f}));var i="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!i)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var a={},o=i&&(document.head||document.getElementsByTagName("head")[0]),s=null,u=0,l=!1,c=function(){},m=null,d="data-vue-ssr-id",p="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function f(e,t,n,i){l=n,m=i||{};var o=r(e,t);return h(o),function(t){for(var n=[],i=0;i<o.length;i++){var s=o[i];(u=a[s.id]).refs--,n.push(u)}t?h(o=r(e,t)):o=[];for(i=0;i<n.length;i++){var u;if(0===(u=n[i]).refs){for(var l=0;l<u.parts.length;l++)u.parts[l]();delete a[u.id]}}}}function h(e){for(var t=0;t<e.length;t++){var n=e[t],r=a[n.id];if(r){r.refs++;for(var i=0;i<r.parts.length;i++)r.parts[i](n.parts[i]);for(;i<n.parts.length;i++)r.parts.push(v(n.parts[i]));r.parts.length>n.parts.length&&(r.parts.length=n.parts.length)}else{var o=[];for(i=0;i<n.parts.length;i++)o.push(v(n.parts[i]));a[n.id]={id:n.id,refs:1,parts:o}}}}function g(){var e=document.createElement("style");return e.type="text/css",o.appendChild(e),e}function v(e){var t,n,r=document.querySelector("style["+d+'~="'+e.id+'"]');if(r){if(l)return c;r.parentNode.removeChild(r)}if(p){var i=u++;r=s||(s=g()),t=I.bind(null,r,i,!1),n=I.bind(null,r,i,!0)}else r=g(),t=b.bind(null,r),n=function(){r.parentNode.removeChild(r)};return t(e),function(r){if(r){if(r.css===e.css&&r.media===e.media&&r.sourceMap===e.sourceMap)return;t(e=r)}else n()}}var y,w=(y=[],function(e,t){return y[e]=t,y.filter(Boolean).join("\n")});function I(e,t,n,r){var i=n?"":r.css;if(e.styleSheet)e.styleSheet.cssText=w(t,i);else{var a=document.createTextNode(i),o=e.childNodes;o[t]&&e.removeChild(o[t]),o.length?e.insertBefore(a,o[t]):e.appendChild(a)}}function b(e,t){var n=t.css,r=t.media,i=t.sourceMap;if(r&&e.setAttribute("media",r),m.ssrId&&e.setAttribute(d,t.id),i&&(n+="\n/*# sourceURL="+i.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(i))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}},Qwqh:function(e,t,n){var r=n("9Mn5");r.__esModule&&(r=r.default),"string"==typeof r&&(r=[[e.i,r,""]]),r.locals&&(e.exports=r.locals);(0,n("P8hj").default)("cd686578",r,!0,{})},wR8H:function(e,t,n){"use strict";n.d(t,"b",(function(){return r})),n.d(t,"a",(function(){return i}));var r=function(e){return e.OPEN="open",e.SHIPPED="shipped",e.SHIPPED_PARTIALLY="shipped_partially",e.RETURN_REQUESTED="return_requested",e.RETURNED="returned",e.RETURNED_PARTIALLY="returned_partially",e.CANCELLED="cancelled",e}(r||{}),i=function(e){return e.STORNO="storno",e.CREDIT_NOTE="credit_note",e.PARTIAL_CANCELLATION="partial_cancellation",e.INVOICE="invoice",e.DELIVERY_NOTE="delivery_note",e}(i||{})}}]);
//# sourceMappingURL=2.js.map