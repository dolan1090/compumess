/*! For license information please see 11.js.LICENSE.txt */
(this["webpackJsonpPluginreturn-management"]=this["webpackJsonpPluginreturn-management"]||[]).push([[11],{"71yi":function(e,t,n){"use strict";n.r(t);function r(e){return(r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function o(){o=function(){return e};var e={},t=Object.prototype,n=t.hasOwnProperty,i=Object.defineProperty||function(e,t,n){e[t]=n.value},c="function"==typeof Symbol?Symbol:{},u=c.iterator||"@@iterator",a=c.asyncIterator||"@@asyncIterator",l=c.toStringTag||"@@toStringTag";function s(e,t,n){return Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}),e[t]}try{s({},"")}catch(e){s=function(e,t,n){return e[t]=n}}function d(e,t,n,r){var o=t&&t.prototype instanceof h?t:h,c=Object.create(o.prototype),u=new E(r||[]);return i(c,"_invoke",{value:C(e,n,u)}),c}function m(e,t,n){try{return{type:"normal",arg:e.call(t,n)}}catch(e){return{type:"throw",arg:e}}}e.wrap=d;var f={};function h(){}function v(){}function p(){}var g={};s(g,u,(function(){return this}));var y=Object.getPrototypeOf,b=y&&y(y(S([])));b&&b!==t&&n.call(b,u)&&(g=b);var w=p.prototype=h.prototype=Object.create(g);function _(e){["next","throw","return"].forEach((function(t){s(e,t,(function(e){return this._invoke(t,e)}))}))}function N(e,t){function o(i,c,u,a){var l=m(e[i],e,c);if("throw"!==l.type){var s=l.arg,d=s.value;return d&&"object"==r(d)&&n.call(d,"__await")?t.resolve(d.__await).then((function(e){o("next",e,u,a)}),(function(e){o("throw",e,u,a)})):t.resolve(d).then((function(e){s.value=e,u(s)}),(function(e){return o("throw",e,u,a)}))}a(l.arg)}var c;i(this,"_invoke",{value:function(e,n){function r(){return new t((function(t,r){o(e,n,t,r)}))}return c=c?c.then(r,r):r()}})}function C(e,t,n){var r="suspendedStart";return function(o,i){if("executing"===r)throw new Error("Generator is already running");if("completed"===r){if("throw"===o)throw i;return O()}for(n.method=o,n.arg=i;;){var c=n.delegate;if(c){var u=x(c,n);if(u){if(u===f)continue;return u}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if("suspendedStart"===r)throw r="completed",n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);r="executing";var a=m(e,t,n);if("normal"===a.type){if(r=n.done?"completed":"suspendedYield",a.arg===f)continue;return{value:a.arg,done:n.done}}"throw"===a.type&&(r="completed",n.method="throw",n.arg=a.arg)}}}function x(e,t){var n=t.method,r=e.iterator[n];if(void 0===r)return t.delegate=null,"throw"===n&&e.iterator.return&&(t.method="return",t.arg=void 0,x(e,t),"throw"===t.method)||"return"!==n&&(t.method="throw",t.arg=new TypeError("The iterator does not provide a '"+n+"' method")),f;var o=m(r,e.iterator,t.arg);if("throw"===o.type)return t.method="throw",t.arg=o.arg,t.delegate=null,f;var i=o.arg;return i?i.done?(t[e.resultName]=i.value,t.next=e.nextLoc,"return"!==t.method&&(t.method="next",t.arg=void 0),t.delegate=null,f):i:(t.method="throw",t.arg=new TypeError("iterator result is not an object"),t.delegate=null,f)}function L(e){var t={tryLoc:e[0]};1 in e&&(t.catchLoc=e[1]),2 in e&&(t.finallyLoc=e[2],t.afterLoc=e[3]),this.tryEntries.push(t)}function k(e){var t=e.completion||{};t.type="normal",delete t.arg,e.completion=t}function E(e){this.tryEntries=[{tryLoc:"root"}],e.forEach(L,this),this.reset(!0)}function S(e){if(e){var t=e[u];if(t)return t.call(e);if("function"==typeof e.next)return e;if(!isNaN(e.length)){var r=-1,o=function t(){for(;++r<e.length;)if(n.call(e,r))return t.value=e[r],t.done=!1,t;return t.value=void 0,t.done=!0,t};return o.next=o}}return{next:O}}function O(){return{value:void 0,done:!0}}return v.prototype=p,i(w,"constructor",{value:p,configurable:!0}),i(p,"constructor",{value:v,configurable:!0}),v.displayName=s(p,l,"GeneratorFunction"),e.isGeneratorFunction=function(e){var t="function"==typeof e&&e.constructor;return!!t&&(t===v||"GeneratorFunction"===(t.displayName||t.name))},e.mark=function(e){return Object.setPrototypeOf?Object.setPrototypeOf(e,p):(e.__proto__=p,s(e,l,"GeneratorFunction")),e.prototype=Object.create(w),e},e.awrap=function(e){return{__await:e}},_(N.prototype),s(N.prototype,a,(function(){return this})),e.AsyncIterator=N,e.async=function(t,n,r,o,i){void 0===i&&(i=Promise);var c=new N(d(t,n,r,o),i);return e.isGeneratorFunction(n)?c:c.next().then((function(e){return e.done?e.value:c.next()}))},_(w),s(w,l,"Generator"),s(w,u,(function(){return this})),s(w,"toString",(function(){return"[object Generator]"})),e.keys=function(e){var t=Object(e),n=[];for(var r in t)n.push(r);return n.reverse(),function e(){for(;n.length;){var r=n.pop();if(r in t)return e.value=r,e.done=!1,e}return e.done=!0,e}},e.values=S,E.prototype={constructor:E,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=void 0,this.done=!1,this.delegate=null,this.method="next",this.arg=void 0,this.tryEntries.forEach(k),!e)for(var t in this)"t"===t.charAt(0)&&n.call(this,t)&&!isNaN(+t.slice(1))&&(this[t]=void 0)},stop:function(){this.done=!0;var e=this.tryEntries[0].completion;if("throw"===e.type)throw e.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var t=this;function r(n,r){return c.type="throw",c.arg=e,t.next=n,r&&(t.method="next",t.arg=void 0),!!r}for(var o=this.tryEntries.length-1;o>=0;--o){var i=this.tryEntries[o],c=i.completion;if("root"===i.tryLoc)return r("end");if(i.tryLoc<=this.prev){var u=n.call(i,"catchLoc"),a=n.call(i,"finallyLoc");if(u&&a){if(this.prev<i.catchLoc)return r(i.catchLoc,!0);if(this.prev<i.finallyLoc)return r(i.finallyLoc)}else if(u){if(this.prev<i.catchLoc)return r(i.catchLoc,!0)}else{if(!a)throw new Error("try statement without catch or finally");if(this.prev<i.finallyLoc)return r(i.finallyLoc)}}}},abrupt:function(e,t){for(var r=this.tryEntries.length-1;r>=0;--r){var o=this.tryEntries[r];if(o.tryLoc<=this.prev&&n.call(o,"finallyLoc")&&this.prev<o.finallyLoc){var i=o;break}}i&&("break"===e||"continue"===e)&&i.tryLoc<=t&&t<=i.finallyLoc&&(i=null);var c=i?i.completion:{};return c.type=e,c.arg=t,i?(this.method="next",this.next=i.finallyLoc,f):this.complete(c)},complete:function(e,t){if("throw"===e.type)throw e.arg;return"break"===e.type||"continue"===e.type?this.next=e.arg:"return"===e.type?(this.rval=this.arg=e.arg,this.method="return",this.next="end"):"normal"===e.type&&t&&(this.next=t),f},finish:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var n=this.tryEntries[t];if(n.finallyLoc===e)return this.complete(n.completion,n.afterLoc),k(n),f}},catch:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var n=this.tryEntries[t];if(n.tryLoc===e){var r=n.completion;if("throw"===r.type){var o=r.arg;k(n)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(e,t,n){return this.delegate={iterator:S(e),resultName:t,nextLoc:n},"next"===this.method&&(this.arg=void 0),f}},e}function i(e,t,n,r,o,i,c){try{var u=e[i](c),a=u.value}catch(e){return void n(e)}u.done?t(a):Promise.resolve(a).then(r,o)}function c(e){return function(){var t=this,n=arguments;return new Promise((function(r,o){var c=e.apply(t,n);function u(e){i(c,r,o,u,a,"next",e)}function a(e){i(c,r,o,u,a,"throw",e)}u(void 0)}))}}var u=Shopware.Component;t.default=u.wrapComponentConfig({template:'\n{% block sw_order_document_settings_modal_form_first_row %}\n<sw-container\n    columns="1fr 1fr 1fr"\n    gap="0 14px"\n>\n    \n    {% block sw_order_document_settings_modal_form_correction_document_number %}\n    <sw-text-field\n        v-model="documentConfig.documentNumber"\n        class="sw-order-document-settings-partial-cancellation-modal__document-number"\n        :label="$tc(\'sw-order.documentModal.labelDocumentStornoNumber\')"\n    />\n    {% endblock %}\n\n    \n    {% block sw_order_document_settings_modal_form_document_select_invoice %}\n    <sw-select-field\n        v-model="documentConfig.custom.invoiceNumber"\n        class="sw-order-document-settings-partial-cancellation-modal__invoice-select"\n        :label="$tc(\'sw-order.documentModal.labelInvoiceNumber\')"\n        :placeholder="$tc(\'sw-order.documentModal.selectInvoiceNumber\')"\n        @change="onSelectInvoice"\n    >\n        <option\n            v-for="invoice in invoices"\n            :key="invoice.config.custom.invoiceNumber"\n            :value="invoice.config.custom.invoiceNumber"\n        >\n            {{ invoice.config.custom.invoiceNumber }}\n        </option>\n    </sw-select-field>\n    {% endblock %}\n\n    \n    {% block sw_order_document_settings_modal_form_correction_document_include_cancelled %}\n        <sw-switch-field\n            v-model="documentConfig.custom.includeCancelled"\n            :label="$tc(\'swag-return-management.documentConfig.includeCancelled\')"\n        />\n    {% endblock %}\n\n    \n    {% block sw_order_document_settings_modal_form_correction_document_date %}\n    <sw-datepicker\n        v-model="documentConfig.documentDate"\n        date-type="date"\n        required\n        :label="$tc(\'sw-order.documentModal.labelDocumentDate\')"\n    />\n    {% endblock %}\n</sw-container>\n{% endblock %}\n',props:{order:{type:Object,required:!0},currentDocumentType:{type:Object,required:!0}},data:function(){return{documentConfig:{custom:{stornoNumber:"",invoiceNumber:"",includeCancelled:!1},documentNumber:"",documentComment:"",documentDate:""}}},computed:{documentPreconditionsFulfilled:function(){return!!this.documentConfig.custom.invoiceNumber},invoices:function(){return this.order.documents.filter((function(e){return"invoice"===e.documentType.technicalName}))}},created:function(){this.createdComponent()},methods:{createdComponent:function(){var e=this;return c(o().mark((function t(){var n;return o().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,e.numberRangeService.reserve("document_".concat(e.currentDocumentType.technicalName),e.order.salesChannelId,!0);case 2:n=t.sent,e.documentConfig.documentNumber=n.number,e.documentNumberPreview=e.documentConfig.documentNumber,e.documentConfig.documentDate=(new Date).toISOString();case 6:case"end":return t.stop()}}),t)})))()},onCreateDocument:function(){var e=arguments,t=this;return c(o().mark((function n(){var r,i,c,u,a,l;return o().wrap((function(n){for(;;)switch(n.prev=n.next){case 0:if(r=e.length>0&&void 0!==e[0]&&e[0],t.$emit("loading-document"),i=t.invoices.filter((function(e){return e.config.custom.invoiceNumber===t.documentConfig.custom.invoiceNumber}))[0],t.documentNumberPreview!==t.documentConfig.documentNumber){n.next=19;break}return n.prev=4,n.next=7,t.numberRangeService.reserve("document_".concat(t.currentDocumentType.technicalName),t.order.salesChannelId,!1);case 7:c=n.sent,t.documentConfig.custom.stornoNumber=c.number,c.number!==t.documentConfig.documentNumber&&t.createNotificationInfo({message:t.$tc("sw-order.documentCard.info.DOCUMENT__NUMBER_WAS_CHANGED")}),t.documentConfig.documentNumber=c.number,t.callDocumentCreate(r,i.id),n.next=17;break;case 14:n.prev=14,n.t0=n.catch(4),t.createNotificationError({message:null===n.t0||void 0===n.t0||null===(u=n.t0.response)||void 0===u||null===(a=u.data)||void 0===a||null===(l=a.errors[0])||void 0===l?void 0:l.detail});case 17:n.next=21;break;case 19:t.documentConfig.custom.stornoNumber=t.documentConfig.documentNumber,t.callDocumentCreate(r,i.id);case 21:case"end":return n.stop()}}),n,null,[[4,14]])})))()},onPreview:function(){this.$emit("loading-preview"),this.documentConfig.custom.stornoNumber=this.documentConfig.documentNumber,this.$super("onPreview")},onSelectInvoice:function(e){this.documentConfig.custom.invoiceNumber=e}}})}}]);
//# sourceMappingURL=11.js.map