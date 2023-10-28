/*! For license information please see 1.js.LICENSE.txt */
(this["webpackJsonpPluginexport-assistant"]=this["webpackJsonpPluginexport-assistant"]||[]).push([[1],{P8hj:function(t,e,r){"use strict";function n(t,e){for(var r=[],n={},o=0;o<e.length;o++){var i=e[o],a=i[0],s={id:t+":"+o,css:i[1],media:i[2],sourceMap:i[3]};n[a]?n[a].parts.push(s):r.push(n[a]={id:a,parts:[s]})}return r}r.r(e),r.d(e,"default",(function(){return m}));var o="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!o)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var i={},a=o&&(document.head||document.getElementsByTagName("head")[0]),s=null,l=0,c=!1,u=function(){},p=null,d="data-vue-ssr-id",f="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function m(t,e,r,o){c=r,p=o||{};var a=n(t,e);return h(a),function(e){for(var r=[],o=0;o<a.length;o++){var s=a[o];(l=i[s.id]).refs--,r.push(l)}e?h(a=n(t,e)):a=[];for(o=0;o<r.length;o++){var l;if(0===(l=r[o]).refs){for(var c=0;c<l.parts.length;c++)l.parts[c]();delete i[l.id]}}}}function h(t){for(var e=0;e<t.length;e++){var r=t[e],n=i[r.id];if(n){n.refs++;for(var o=0;o<n.parts.length;o++)n.parts[o](r.parts[o]);for(;o<r.parts.length;o++)n.parts.push(v(r.parts[o]));n.parts.length>r.parts.length&&(n.parts.length=r.parts.length)}else{var a=[];for(o=0;o<r.parts.length;o++)a.push(v(r.parts[o]));i[r.id]={id:r.id,refs:1,parts:a}}}}function y(){var t=document.createElement("style");return t.type="text/css",a.appendChild(t),t}function v(t){var e,r,n=document.querySelector("style["+d+'~="'+t.id+'"]');if(n){if(c)return u;n.parentNode.removeChild(n)}if(f){var o=l++;n=s||(s=y()),e=b.bind(null,n,o,!1),r=b.bind(null,n,o,!0)}else n=y(),e=x.bind(null,n),r=function(){n.parentNode.removeChild(n)};return e(t),function(n){if(n){if(n.css===t.css&&n.media===t.media&&n.sourceMap===t.sourceMap)return;e(t=n)}else r()}}var g,w=(g=[],function(t,e){return g[t]=e,g.filter(Boolean).join("\n")});function b(t,e,r,n){var o=r?"":n.css;if(t.styleSheet)t.styleSheet.cssText=w(e,o);else{var i=document.createTextNode(o),a=t.childNodes;a[e]&&t.removeChild(a[e]),a.length?t.insertBefore(i,a[e]):t.appendChild(i)}}function x(t,e){var r=e.css,n=e.media,o=e.sourceMap;if(n&&t.setAttribute("media",n),p.ssrId&&t.setAttribute(d,e.id),o&&(r+="\n/*# sourceURL="+o.sources[0]+" */",r+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(o))))+" */"),t.styleSheet)t.styleSheet.cssText=r;else{for(;t.firstChild;)t.removeChild(t.firstChild);t.appendChild(document.createTextNode(r))}}},a2XN:function(t,e,r){},dix8:function(t,e,r){var n=r("a2XN");n.__esModule&&(n=n.default),"string"==typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);(0,r("P8hj").default)("24a4ccf0",n,!0,{})},m1BP:function(t,e,r){"use strict";r.r(e);var n={customer:[{property:"email",label:"sw-customer.list.columnEmail",routerLink:"sw.customer.detail",primary:!0,width:"200px"},{property:"firstName",dataIndex:"firstName",label:"First name",routerLink:"sw.customer.detail",width:"150px"},{property:"lastName",dataIndex:"lastName",label:"Last name",routerLink:"sw.customer.detail",width:"150px"},{property:"customerNumber",dataIndex:"customerNumber",label:"sw-customer.list.columnCustomerNumber",align:"right"},{property:"affiliateCode",label:"sw-customer.list.columnAffiliateCode"},{property:"campaignCode",label:"sw-customer.list.columnCampaignCode"}],product:[{property:"name",label:"sw-product.list.columnName",routerLink:"sw.product.detail",inlineEdit:"string",allowResize:!0,primary:!0},{property:"productNumber",naturalSorting:!0,label:"sw-product.list.columnProductNumber",align:"right",allowResize:!0},{property:"stock",label:"sw-product.list.columnInStock",inlineEdit:"number",allowResize:!0,align:"right"},{property:"availableStock",label:"sw-product.list.columnAvailableStock",allowResize:!0,align:"right"},{property:"createdAt",label:"sw-product.list.columnCreatedAt",allowResize:!0,visible:!1},{property:"updatedAt",label:"sw-product.list.columnUpdatedAt",allowResize:!0,visible:!1}],order:[{property:"orderNumber",label:"sw-order.list.columnOrderNumber",routerLink:"sw.order.detail",allowResize:!0,primary:!0},{property:"billingAddressId",dataIndex:"billingAddress.street",label:"sw-order.list.columnBillingAddress",allowResize:!0},{property:"amountTotal",label:"sw-order.list.columnAmount",align:"right",allowResize:!0},{property:"orderDateTime",label:"sw-order.list.orderDate",allowResize:!0},{property:"affiliateCode",inlineEdit:"string",label:"sw-order.list.columnAffiliateCode",allowResize:!0,visible:!1},{property:"campaignCode",inlineEdit:"string",label:"sw-order.list.columnCampaignCode",allowResize:!0,visible:!1}],product_price:[{property:"productId",dataIndex:"productId",label:"product_id",width:"250px",primary:!0},{property:"ruleId",dataIndex:"ruleId",label:"rule_id"},{property:"priceNet",dataIndex:"priceNet",label:"Net price"},{property:"priceGross",dataIndex:"priceGross",label:"Gross price"},{property:"quantityStart",dataIndex:"quantityStart",label:"Quantity Start"},{property:"quantityEnd",dataIndex:"quantityEnd",label:"Quantity End"}],category:[{property:"name",dataIndex:"name",label:"Name",width:"250px",primary:!0},{property:"description",dataIndex:"description",label:"Description"},{property:"type",dataIndex:"type",label:"Type"}],media:[{property:"fileName",dataIndex:"fileName",label:"File name",width:"250px",primary:!0},{property:"fileSize",dataIndex:"fileSize",label:"Size (in Byte)"},{property:"fileExtension",dataIndex:"fileExtension",label:"File Extension"},{property:"uploadedAt",dataIndex:"uploadedAt",label:"Uploaded at"}],newsletter_recipient:[{property:"email",dataIndex:"email",label:"Email",routerLink:"sw.newsletter.recipient.detail",width:"250px",primary:!0},{property:"title",dataIndex:"title",label:"Title",align:"right"},{property:"firstName",dataIndex:"firstName",label:"First name",align:"right"},{property:"lastName",dataIndex:"lastName",label:"Last name",align:"right"}],promotion_individual_code:[{property:"code",dataIndex:"code",label:"Code",width:"250px",primary:!0}],promotion_discount:[{property:"promotionId",dataIndex:"promotionId",label:"promotion_id",width:"250px",primary:!0},{property:"scope",dataIndex:"scope",label:"Scope"},{property:"type",dataIndex:"type",label:"Type"},{property:"value",dataIndex:"value",label:"Value"}],property_group_option:[{property:"name",dataIndex:"name",label:"Option name",width:"250px",primary:!0}],product_cross_selling:[{property:"name",dataIndex:"name",width:"250px",primary:!0}]};r("dix8");function o(t){return(o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function i(){i=function(){return t};var t={},e=Object.prototype,r=e.hasOwnProperty,n=Object.defineProperty||function(t,e,r){t[e]=r.value},a="function"==typeof Symbol?Symbol:{},s=a.iterator||"@@iterator",l=a.asyncIterator||"@@asyncIterator",c=a.toStringTag||"@@toStringTag";function u(t,e,r){return Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}),t[e]}try{u({},"")}catch(t){u=function(t,e,r){return t[e]=r}}function p(t,e,r,o){var i=e&&e.prototype instanceof m?e:m,a=Object.create(i.prototype),s=new _(o||[]);return n(a,"_invoke",{value:L(t,r,s)}),a}function d(t,e,r){try{return{type:"normal",arg:t.call(e,r)}}catch(t){return{type:"throw",arg:t}}}t.wrap=p;var f={};function m(){}function h(){}function y(){}var v={};u(v,s,(function(){return this}));var g=Object.getPrototypeOf,w=g&&g(g(C([])));w&&w!==e&&r.call(w,s)&&(v=w);var b=y.prototype=m.prototype=Object.create(v);function x(t){["next","throw","return"].forEach((function(e){u(t,e,(function(t){return this._invoke(e,t)}))}))}function E(t,e){function i(n,a,s,l){var c=d(t[n],t,a);if("throw"!==c.type){var u=c.arg,p=u.value;return p&&"object"==o(p)&&r.call(p,"__await")?e.resolve(p.__await).then((function(t){i("next",t,s,l)}),(function(t){i("throw",t,s,l)})):e.resolve(p).then((function(t){u.value=t,s(u)}),(function(t){return i("throw",t,s,l)}))}l(c.arg)}var a;n(this,"_invoke",{value:function(t,r){function n(){return new e((function(e,n){i(t,r,e,n)}))}return a=a?a.then(n,n):n()}})}function L(t,e,r){var n="suspendedStart";return function(o,i){if("executing"===n)throw new Error("Generator is already running");if("completed"===n){if("throw"===o)throw i;return A()}for(r.method=o,r.arg=i;;){var a=r.delegate;if(a){var s=I(a,r);if(s){if(s===f)continue;return s}}if("next"===r.method)r.sent=r._sent=r.arg;else if("throw"===r.method){if("suspendedStart"===n)throw n="completed",r.arg;r.dispatchException(r.arg)}else"return"===r.method&&r.abrupt("return",r.arg);n="executing";var l=d(t,e,r);if("normal"===l.type){if(n=r.done?"completed":"suspendedYield",l.arg===f)continue;return{value:l.arg,done:r.done}}"throw"===l.type&&(n="completed",r.method="throw",r.arg=l.arg)}}}function I(t,e){var r=e.method,n=t.iterator[r];if(void 0===n)return e.delegate=null,"throw"===r&&t.iterator.return&&(e.method="return",e.arg=void 0,I(t,e),"throw"===e.method)||"return"!==r&&(e.method="throw",e.arg=new TypeError("The iterator does not provide a '"+r+"' method")),f;var o=d(n,t.iterator,e.arg);if("throw"===o.type)return e.method="throw",e.arg=o.arg,e.delegate=null,f;var i=o.arg;return i?i.done?(e[t.resultName]=i.value,e.next=t.nextLoc,"return"!==e.method&&(e.method="next",e.arg=void 0),e.delegate=null,f):i:(e.method="throw",e.arg=new TypeError("iterator result is not an object"),e.delegate=null,f)}function N(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function S(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function _(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(N,this),this.reset(!0)}function C(t){if(t){var e=t[s];if(e)return e.call(t);if("function"==typeof t.next)return t;if(!isNaN(t.length)){var n=-1,o=function e(){for(;++n<t.length;)if(r.call(t,n))return e.value=t[n],e.done=!1,e;return e.value=void 0,e.done=!0,e};return o.next=o}}return{next:A}}function A(){return{value:void 0,done:!0}}return h.prototype=y,n(b,"constructor",{value:y,configurable:!0}),n(y,"constructor",{value:h,configurable:!0}),h.displayName=u(y,c,"GeneratorFunction"),t.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===h||"GeneratorFunction"===(e.displayName||e.name))},t.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,y):(t.__proto__=y,u(t,c,"GeneratorFunction")),t.prototype=Object.create(b),t},t.awrap=function(t){return{__await:t}},x(E.prototype),u(E.prototype,l,(function(){return this})),t.AsyncIterator=E,t.async=function(e,r,n,o,i){void 0===i&&(i=Promise);var a=new E(p(e,r,n,o),i);return t.isGeneratorFunction(r)?a:a.next().then((function(t){return t.done?t.value:a.next()}))},x(b),u(b,c,"Generator"),u(b,s,(function(){return this})),u(b,"toString",(function(){return"[object Generator]"})),t.keys=function(t){var e=Object(t),r=[];for(var n in e)r.push(n);return r.reverse(),function t(){for(;r.length;){var n=r.pop();if(n in e)return t.value=n,t.done=!1,t}return t.done=!0,t}},t.values=C,_.prototype={constructor:_,reset:function(t){if(this.prev=0,this.next=0,this.sent=this._sent=void 0,this.done=!1,this.delegate=null,this.method="next",this.arg=void 0,this.tryEntries.forEach(S),!t)for(var e in this)"t"===e.charAt(0)&&r.call(this,e)&&!isNaN(+e.slice(1))&&(this[e]=void 0)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(t){if(this.done)throw t;var e=this;function n(r,n){return a.type="throw",a.arg=t,e.next=r,n&&(e.method="next",e.arg=void 0),!!n}for(var o=this.tryEntries.length-1;o>=0;--o){var i=this.tryEntries[o],a=i.completion;if("root"===i.tryLoc)return n("end");if(i.tryLoc<=this.prev){var s=r.call(i,"catchLoc"),l=r.call(i,"finallyLoc");if(s&&l){if(this.prev<i.catchLoc)return n(i.catchLoc,!0);if(this.prev<i.finallyLoc)return n(i.finallyLoc)}else if(s){if(this.prev<i.catchLoc)return n(i.catchLoc,!0)}else{if(!l)throw new Error("try statement without catch or finally");if(this.prev<i.finallyLoc)return n(i.finallyLoc)}}}},abrupt:function(t,e){for(var n=this.tryEntries.length-1;n>=0;--n){var o=this.tryEntries[n];if(o.tryLoc<=this.prev&&r.call(o,"finallyLoc")&&this.prev<o.finallyLoc){var i=o;break}}i&&("break"===t||"continue"===t)&&i.tryLoc<=e&&e<=i.finallyLoc&&(i=null);var a=i?i.completion:{};return a.type=t,a.arg=e,i?(this.method="next",this.next=i.finallyLoc,f):this.complete(a)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),f},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.finallyLoc===t)return this.complete(r.completion,r.afterLoc),S(r),f}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.tryLoc===t){var n=r.completion;if("throw"===n.type){var o=n.arg;S(r)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(t,e,r){return this.delegate={iterator:C(t),resultName:e,nextLoc:r},"next"===this.method&&(this.arg=void 0),f}},t}function a(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var r=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=r){var n,o,i,a,s=[],l=!0,c=!1;try{if(i=(r=r.call(t)).next,0===e){if(Object(r)!==r)return;l=!1}else for(;!(l=(n=i.call(r)).done)&&(s.push(n.value),s.length!==e);l=!0);}catch(t){c=!0,o=t}finally{try{if(!l&&null!=r.return&&(a=r.return(),Object(a)!==a))return}finally{if(c)throw o}}return s}}(t,e)||function(t,e){if(!t)return;if("string"==typeof t)return s(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);"Object"===r&&t.constructor&&(r=t.constructor.name);if("Map"===r||"Set"===r)return Array.from(t);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return s(t,e)}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function s(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=new Array(e);r<e;r++)n[r]=t[r];return n}function l(t,e,r,n,o,i,a){try{var s=t[i](a),l=s.value}catch(t){return void r(t)}s.done?e(l):Promise.resolve(l).then(n,o)}function c(t){return function(){var e=this,r=arguments;return new Promise((function(n,o){var i=t.apply(e,r);function a(t){l(i,n,o,a,s,"next",t)}function s(t){l(i,n,o,a,s,"throw",t)}a(void 0)}))}}var u=Shopware.Data.Criteria;e.default={template:'<sw-modal\n    class="swag-export-assistant-modal"\n    variant="full"\n    :is-loading="isLoading"\n    @modal-close="turnOffModalPreview"\n>\n    <template #modal-title>\n        {{ modalTitle }}\n        <sw-ai-copilot-badge />\n    </template>\n\n    <div class="swag-export-assistant-modal__content">\n        <sw-data-grid\n            v-if="!isLoading && (entities && entities.total > 0)"\n            class="swag-export-assistant-modal__table"\n            :data-source="entities"\n            :columns="columns"\n            :show-actions="false"\n            :show-selection="false"\n        >\n            <template #column-priceNet="{ item }">\n                {{ item.price[0].net }}\n            </template>\n\n            <template #column-priceGross="{ item }">\n                {{ item.price[0].gross }}\n            </template>\n\n        </sw-data-grid>\n\n        <sw-empty-state\n            v-if="!isLoading && (!entities || entities.total === 0)"\n            :title="$tc(\'swag-export-assistant.modal.messageEmptyTitle\')"\n            :subline="emptySubline"\n            :absolute="false"\n        />\n    </div>\n\n    <template #modal-footer>\n        <sw-button\n            size="small"\n            @click="turnOffModalPreview"\n        >\n            {{ $tc(\'global.default.cancel\') }}\n        </sw-button>\n\n        <sw-button\n            v-if="entities && entities.total > 0"\n            variant="primary"\n            size="small"\n            @click="onStartExport"\n        >\n            {{ $tc(\'swag-export-assistant.modal.buttonStartExport\') }}\n        </sw-button>\n    </template>\n\n    <template #modal-loader>\n        <div\n            v-if="isLoading"\n            class="swag-export-assistant-modal__loader"\n        >\n            <sw-loader />\n            <span class="swag-export-assistant-modal__loader-description">\n                {{ $tc(\'swag-export-assistant.modal.messageLoading\') }}\n            </span>\n        </div>\n    </template>\n</sw-modal>\n',inject:["repositoryFactory","importExport","criteriaGeneratorService"],mixins:[Shopware.Mixin.getByName("notification")],props:{searchTerm:{type:String,required:!0}},data:function(){return{isLoading:!1,entities:null,columns:[],entity:null,profileId:null,criteria:null}},computed:{profileRepository:function(){return this.repositoryFactory.create("import_export_profile")},profileCriteria:function(){var t=new u;return t.setLimit(1),t.addFilter(u.equals("systemDefault",!0)),t.addFilter(u.equals("sourceEntity",this.entity)),t},modalTitle:function(){var t,e=this.isLoading?"":this.$tc("swag-export-assistant.modal.foundItem",this.entities&&this.entities.length,{total:null===(t=this.entities)||void 0===t?void 0:t.total});return this.$tc("swag-export-assistant.default.preview")+e},emptySubline:function(){return'"'.concat(this.searchTerm,'"')}},created:function(){this.createdComponent()},methods:{createdComponent:function(){this.getExportData()},getExportData:function(){var t=this;return c(i().mark((function e(){var r,o,s,l,c,p,d,f;return i().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return t.isLoading=!0,e.prev=1,t.criteria=new u,e.next=5,t.criteriaGeneratorService.generate({prompt:t.searchTerm,criteria:t.criteria});case 5:return r=e.sent,o=r.entity,(s=Shopware.Utils.object.cloneDeep(t.criteria)).setLimit(25),l=t.repositoryFactory.create(o),t.entity=o,e.next=13,Promise.all([l.search(s),t.profileRepository.search(t.profileCriteria)]).catch((function(e){var r,n,o,i,a,s,l,c,u,p,d;if(400===(null==e||null===(r=e.response)||void 0===r?void 0:r.status))throw new Error(t.$tc("global.swag-search-assistant.messageMissingEntityOrCriteria"));if("ERR_BAD_REQUEST"===e.code&&"FRAMEWORK__UNMAPPED_FIELD"===(null===(n=e.response)||void 0===n||null===(o=n.data)||void 0===o||null===(i=o.errors[0])||void 0===i?void 0:i.code))throw new Error("Assistant: ".concat(null===(u=e.response)||void 0===u||null===(p=u.data)||void 0===p||null===(d=p.errors[0])||void 0===d?void 0:d.detail));t.turnOffModalPreview(),t.createNotificationError({message:null!==(a="Assistant: ".concat(null===(s=e.response)||void 0===s||null===(l=s.data)||void 0===l||null===(c=l.errors[0])||void 0===c?void 0:c.detail))&&void 0!==a?a:e.message})}));case 13:c=e.sent,p=a(c,2),d=p[0],f=p[1],t.entities=d,t.columns=n[o],t.profileId=f.first()?f.first().id:null,e.next=26;break;case 22:e.prev=22,e.t0=e.catch(1),t.turnOffModalPreview(),t.createNotificationError({message:e.t0.message});case 26:return e.prev=26,t.isLoading=!1,e.finish(26);case 29:case"end":return e.stop()}}),e,null,[[1,22,26,29]])})))()},turnOffModalPreview:function(){this.$emit("turn-off-modal-preview")},onStartExport:function(){var t=this;return c(i().mark((function e(){return i().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,t.importExport.export(t.profileId,t.handleProgress,{parameters:{criteria:t.criteria.parse()}}).catch((function(e){t.isLoading=!1,e.response&&e.response.data&&e.response.data.errors?e.response.data.errors.forEach((function(e){t.createNotificationError({message:"".concat(e.code,": ").concat(e.detail)})})):t.createNotificationError({message:e.message})}));case 2:return t.turnOffModalPreview(),e.next=5,t.$nextTick();case 5:t.createNotificationSuccess({message:t.$tc("swag-export-assistant.base.messageExportDone")});case 6:case"end":return e.stop()}}),e)})))()},handleProgress:function(t){this.createNotificationInfo({message:this.$tc("sw-import-export.exporter.messageExportStarted")}),this.isLoading=!1,this.$emit("export-started",t)}}}}}]);
//# sourceMappingURL=1.js.map