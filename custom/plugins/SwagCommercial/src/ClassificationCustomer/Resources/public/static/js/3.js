/*! For license information please see 3.js.LICENSE.txt */
(this["webpackJsonpPluginclassification-customer"]=this["webpackJsonpPluginclassification-customer"]||[]).push([[3],{"Bl/s":function(t,e,r){var n=r("PHMn");n.__esModule&&(n=n.default),"string"==typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);(0,r("P8hj").default)("149f3450",n,!0,{})},P8hj:function(t,e,r){"use strict";function n(t,e){for(var r=[],n={},o=0;o<e.length;o++){var a=e[o],i=a[0],s={id:t+":"+o,css:a[1],media:a[2],sourceMap:a[3]};n[i]?n[i].parts.push(s):r.push(n[i]={id:i,parts:[s]})}return r}r.r(e),r.d(e,"default",(function(){return d}));var o="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!o)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var a={},i=o&&(document.head||document.getElementsByTagName("head")[0]),s=null,c=0,u=!1,l=function(){},f=null,p="data-vue-ssr-id",h="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function d(t,e,r,o){u=r,f=o||{};var i=n(t,e);return v(i),function(e){for(var r=[],o=0;o<i.length;o++){var s=i[o];(c=a[s.id]).refs--,r.push(c)}e?v(i=n(t,e)):i=[];for(o=0;o<r.length;o++){var c;if(0===(c=r[o]).refs){for(var u=0;u<c.parts.length;u++)c.parts[u]();delete a[c.id]}}}}function v(t){for(var e=0;e<t.length;e++){var r=t[e],n=a[r.id];if(n){n.refs++;for(var o=0;o<n.parts.length;o++)n.parts[o](r.parts[o]);for(;o<r.parts.length;o++)n.parts.push(g(r.parts[o]));n.parts.length>r.parts.length&&(n.parts.length=r.parts.length)}else{var i=[];for(o=0;o<r.parts.length;o++)i.push(g(r.parts[o]));a[r.id]={id:r.id,refs:1,parts:i}}}}function m(){var t=document.createElement("style");return t.type="text/css",i.appendChild(t),t}function g(t){var e,r,n=document.querySelector("style["+p+'~="'+t.id+'"]');if(n){if(u)return l;n.parentNode.removeChild(n)}if(h){var o=c++;n=s||(s=m()),e=b.bind(null,n,o,!1),r=b.bind(null,n,o,!0)}else n=m(),e=x.bind(null,n),r=function(){n.parentNode.removeChild(n)};return e(t),function(n){if(n){if(n.css===t.css&&n.media===t.media&&n.sourceMap===t.sourceMap)return;e(t=n)}else r()}}var y,w=(y=[],function(t,e){return y[t]=e,y.filter(Boolean).join("\n")});function b(t,e,r,n){var o=r?"":n.css;if(t.styleSheet)t.styleSheet.cssText=w(e,o);else{var a=document.createTextNode(o),i=t.childNodes;i[e]&&t.removeChild(i[e]),i.length?t.insertBefore(a,i[e]):t.appendChild(a)}}function x(t,e){var r=e.css,n=e.media,o=e.sourceMap;if(n&&t.setAttribute("media",n),f.ssrId&&t.setAttribute(p,e.id),o&&(r+="\n/*# sourceURL="+o.sources[0]+" */",r+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(o))))+" */"),t.styleSheet)t.styleSheet.cssText=r;else{for(;t.firstChild;)t.removeChild(t.firstChild);t.appendChild(document.createTextNode(r))}}},PHMn:function(t,e,r){},RUNl:function(t,e,r){"use strict";r.r(e);r("Bl/s");function n(t){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function o(t,e,r){return(e=function(t){var e=function(t,e){if("object"!==n(t)||null===t)return t;var r=t[Symbol.toPrimitive];if(void 0!==r){var o=r.call(t,e||"default");if("object"!==n(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(t)}(t,"string");return"symbol"===n(e)?e:String(e)}(e))in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}function a(t){return function(t){if(Array.isArray(t))return i(t)}(t)||function(t){if("undefined"!=typeof Symbol&&null!=t[Symbol.iterator]||null!=t["@@iterator"])return Array.from(t)}(t)||function(t,e){if(!t)return;if("string"==typeof t)return i(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);"Object"===r&&t.constructor&&(r=t.constructor.name);if("Map"===r||"Set"===r)return Array.from(t);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return i(t,e)}(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function i(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=new Array(e);r<e;r++)n[r]=t[r];return n}function s(){s=function(){return t};var t={},e=Object.prototype,r=e.hasOwnProperty,o=Object.defineProperty||function(t,e,r){t[e]=r.value},a="function"==typeof Symbol?Symbol:{},i=a.iterator||"@@iterator",c=a.asyncIterator||"@@asyncIterator",u=a.toStringTag||"@@toStringTag";function l(t,e,r){return Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}),t[e]}try{l({},"")}catch(t){l=function(t,e,r){return t[e]=r}}function f(t,e,r,n){var a=e&&e.prototype instanceof d?e:d,i=Object.create(a.prototype),s=new k(n||[]);return o(i,"_invoke",{value:C(t,r,s)}),i}function p(t,e,r){try{return{type:"normal",arg:t.call(e,r)}}catch(t){return{type:"throw",arg:t}}}t.wrap=f;var h={};function d(){}function v(){}function m(){}var g={};l(g,i,(function(){return this}));var y=Object.getPrototypeOf,w=y&&y(y(j([])));w&&w!==e&&r.call(w,i)&&(g=w);var b=m.prototype=d.prototype=Object.create(g);function x(t){["next","throw","return"].forEach((function(e){l(t,e,(function(t){return this._invoke(e,t)}))}))}function S(t,e){function a(o,i,s,c){var u=p(t[o],t,i);if("throw"!==u.type){var l=u.arg,f=l.value;return f&&"object"==n(f)&&r.call(f,"__await")?e.resolve(f.__await).then((function(t){a("next",t,s,c)}),(function(t){a("throw",t,s,c)})):e.resolve(f).then((function(t){l.value=t,s(l)}),(function(t){return a("throw",t,s,c)}))}c(u.arg)}var i;o(this,"_invoke",{value:function(t,r){function n(){return new e((function(e,n){a(t,r,e,n)}))}return i=i?i.then(n,n):n()}})}function C(t,e,r){var n="suspendedStart";return function(o,a){if("executing"===n)throw new Error("Generator is already running");if("completed"===n){if("throw"===o)throw a;return O()}for(r.method=o,r.arg=a;;){var i=r.delegate;if(i){var s=L(i,r);if(s){if(s===h)continue;return s}}if("next"===r.method)r.sent=r._sent=r.arg;else if("throw"===r.method){if("suspendedStart"===n)throw n="completed",r.arg;r.dispatchException(r.arg)}else"return"===r.method&&r.abrupt("return",r.arg);n="executing";var c=p(t,e,r);if("normal"===c.type){if(n=r.done?"completed":"suspendedYield",c.arg===h)continue;return{value:c.arg,done:r.done}}"throw"===c.type&&(n="completed",r.method="throw",r.arg=c.arg)}}}function L(t,e){var r=e.method,n=t.iterator[r];if(void 0===n)return e.delegate=null,"throw"===r&&t.iterator.return&&(e.method="return",e.arg=void 0,L(t,e),"throw"===e.method)||"return"!==r&&(e.method="throw",e.arg=new TypeError("The iterator does not provide a '"+r+"' method")),h;var o=p(n,t.iterator,e.arg);if("throw"===o.type)return e.method="throw",e.arg=o.arg,e.delegate=null,h;var a=o.arg;return a?a.done?(e[t.resultName]=a.value,e.next=t.nextLoc,"return"!==e.method&&(e.method="next",e.arg=void 0),e.delegate=null,h):a:(e.method="throw",e.arg=new TypeError("iterator result is not an object"),e.delegate=null,h)}function E(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function T(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function k(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(E,this),this.reset(!0)}function j(t){if(t){var e=t[i];if(e)return e.call(t);if("function"==typeof t.next)return t;if(!isNaN(t.length)){var n=-1,o=function e(){for(;++n<t.length;)if(r.call(t,n))return e.value=t[n],e.done=!1,e;return e.value=void 0,e.done=!0,e};return o.next=o}}return{next:O}}function O(){return{value:void 0,done:!0}}return v.prototype=m,o(b,"constructor",{value:m,configurable:!0}),o(m,"constructor",{value:v,configurable:!0}),v.displayName=l(m,u,"GeneratorFunction"),t.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===v||"GeneratorFunction"===(e.displayName||e.name))},t.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,m):(t.__proto__=m,l(t,u,"GeneratorFunction")),t.prototype=Object.create(b),t},t.awrap=function(t){return{__await:t}},x(S.prototype),l(S.prototype,c,(function(){return this})),t.AsyncIterator=S,t.async=function(e,r,n,o,a){void 0===a&&(a=Promise);var i=new S(f(e,r,n,o),a);return t.isGeneratorFunction(r)?i:i.next().then((function(t){return t.done?t.value:i.next()}))},x(b),l(b,u,"Generator"),l(b,i,(function(){return this})),l(b,"toString",(function(){return"[object Generator]"})),t.keys=function(t){var e=Object(t),r=[];for(var n in e)r.push(n);return r.reverse(),function t(){for(;r.length;){var n=r.pop();if(n in e)return t.value=n,t.done=!1,t}return t.done=!0,t}},t.values=j,k.prototype={constructor:k,reset:function(t){if(this.prev=0,this.next=0,this.sent=this._sent=void 0,this.done=!1,this.delegate=null,this.method="next",this.arg=void 0,this.tryEntries.forEach(T),!t)for(var e in this)"t"===e.charAt(0)&&r.call(this,e)&&!isNaN(+e.slice(1))&&(this[e]=void 0)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(t){if(this.done)throw t;var e=this;function n(r,n){return i.type="throw",i.arg=t,e.next=r,n&&(e.method="next",e.arg=void 0),!!n}for(var o=this.tryEntries.length-1;o>=0;--o){var a=this.tryEntries[o],i=a.completion;if("root"===a.tryLoc)return n("end");if(a.tryLoc<=this.prev){var s=r.call(a,"catchLoc"),c=r.call(a,"finallyLoc");if(s&&c){if(this.prev<a.catchLoc)return n(a.catchLoc,!0);if(this.prev<a.finallyLoc)return n(a.finallyLoc)}else if(s){if(this.prev<a.catchLoc)return n(a.catchLoc,!0)}else{if(!c)throw new Error("try statement without catch or finally");if(this.prev<a.finallyLoc)return n(a.finallyLoc)}}}},abrupt:function(t,e){for(var n=this.tryEntries.length-1;n>=0;--n){var o=this.tryEntries[n];if(o.tryLoc<=this.prev&&r.call(o,"finallyLoc")&&this.prev<o.finallyLoc){var a=o;break}}a&&("break"===t||"continue"===t)&&a.tryLoc<=e&&e<=a.finallyLoc&&(a=null);var i=a?a.completion:{};return i.type=t,i.arg=e,a?(this.method="next",this.next=a.finallyLoc,h):this.complete(i)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),h},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.finallyLoc===t)return this.complete(r.completion,r.afterLoc),T(r),h}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.tryLoc===t){var n=r.completion;if("throw"===n.type){var o=n.arg;T(r)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(t,e,r){return this.delegate={iterator:j(t),resultName:e,nextLoc:r},"next"===this.method&&(this.arg=void 0),h}},t}function c(t,e,r,n,o,a,i){try{var s=t[a](i),c=s.value}catch(t){return void r(t)}s.done?e(c):Promise.resolve(c).then(n,o)}function u(t){return function(){var e=this,r=arguments;return new Promise((function(n,o){var a=t.apply(e,r);function i(t){c(a,n,o,i,s,"next",t)}function s(t){c(a,n,o,i,s,"throw",t)}i(void 0)}))}}var l=Shopware,f=l.Component,p=l.Mixin,h=Shopware.Data.EntityCollection;e.default=f.wrapComponentConfig({template:'<sw-page\n    class="swag-customer-classification-index"\n>\n    <template #smart-bar-header>\n        <div class="swag-customer-classification-index__header">\n            <h2>{{ $tc(\'swag-customer-classification.textTitle\', totalCustomer, { totalCustomer: totalCustomer }) }}</h2>\n            <sw-ai-copilot-badge />\n        </div>\n    </template>\n\n    <template #content>\n        <sw-card-view\n            v-if="totalCustomer > 0"\n        >\n            <sw-alert variant="info">\n                {{ $tc(\'swag-customer-classification.alertPageIntroduction\') }}\n            </sw-alert>\n\n            <swag-customer-classification-basic\n                :tag-data="tagData"\n                @tag-select="updateSelectTags"\n                @tag-update="updateTagList"\n                @start-classify="onStartClick"\n            />\n        </sw-card-view>\n\n        <sw-empty-state\n            v-if="totalCustomer <= 0"\n            :title="$tc(\'swag-customer-classification.emptyState.messageEmptyTitle\')"\n            :subline="$tc(\'swag-customer-classification.emptyState.messageEmptySubline\')"\n            icon="regular-users"\n            color="#F88962"\n        />\n\n        <router-view\n            :item-total="customerIds.length"\n            :is-loading="isLoading"\n            :process-status="processStatus"\n            @modal-close="onCloseModal"\n            @start-classify="startClassification"\n        />\n    </template>\n</sw-page>\n',inject:["acl","systemConfigApiService","repositoryFactory","customerClassifyApiService"],mixins:[p.getByName("notification")],data:function(){return{isLoading:!1,tagData:[],selectedTags:[],formatResponse:'{"[group.id]": ["customer.customer_number(s)"],...}',processStatus:"",hasOldTags:!1,chunkSize:50}},computed:{tagRepository:function(){return this.repositoryFactory.create("tag")},customerIds:function(){return Shopware.State.get("shopwareApps").selectedIds},totalCustomer:function(){var t;return null!==(t=this.customerIds.length)&&void 0!==t?t:0}},created:function(){this.createdComponent()},methods:{createdComponent:function(){var t=this;return u(s().mark((function e(){var r,n;return s().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,t.getSystemConfigLabels();case 2:if(e.t1=r=e.sent,e.t0=null!==e.t1,!e.t0){e.next=6;break}e.t0=void 0!==r;case 6:if(!e.t0){e.next=10;break}e.t2=r,e.next=11;break;case 10:e.t2=[];case 11:t.tagData=e.t2,t.hasOldTags=(null===(n=t.tagData)||void 0===n?void 0:n.length)>0;case 13:case"end":return e.stop()}}),e)})))()},onStartClick:function(){this.$router.push({name:"swag.customer.classification.index.save"})},startClassification:function(){var t=this;return u(s().mark((function e(){var r,n,o;return s().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(t.isLoading=!0,e.prev=1,!t.hasOldTags){e.next=5;break}return e.next=5,t.removeOldTags();case 5:return e.next=7,t.saveTagsToDatabase();case 7:return r=e.sent,e.next=10,t.saveTagsToSystemConfig(r);case 10:n=0;case 11:if(!(n<t.totalCustomer)){e.next=18;break}return o=t.customerIds.slice(n,n+t.chunkSize),e.next=15,t.customerClassifyApiService.classify(t.selectedTags,o,t.formatResponse);case 15:n+=t.chunkSize,e.next=11;break;case 18:t.processStatus="success",e.next=24;break;case 21:e.prev=21,e.t0=e.catch(1),t.processStatus="error";case 24:return e.prev=24,t.isLoading=!1,e.finish(24);case 27:case"end":return e.stop()}}),e,null,[[1,21,24,27]])})))()},saveTagsToDatabase:function(){var t=this;return u(s().mark((function e(){var r,n,o;return s().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return r=[],n=a(t.tagData),t.tagData.forEach((function(e,o){var a=t.tagRepository.create();a.id=e.id,a.name=e.name,r.push(a),n[o].id=a.id})),o=new h(t.tagRepository.source,t.tagRepository.entityName,Shopware.Context.api,null,r),e.abrupt("return",t.tagRepository.saveAll(o).then((function(){return Promise.resolve(n)})));case 5:case"end":return e.stop()}}),e)})))()},removeOldTags:function(){var t=this;return u(s().mark((function e(){var r,n;return s().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,t.getSystemConfigLabels();case 2:return r=e.sent,n=r.map((function(t){return t.id})),e.abrupt("return",t.tagRepository.syncDeleted(n));case 5:case"end":return e.stop()}}),e)})))()},saveTagsToSystemConfig:function(t){return this.systemConfigApiService.saveValues(o({},"core.customer.classification.labels",t))},updateSelectTags:function(t){this.selectedTags=t},updateTagList:function(t){this.tagData=t},getSystemConfigLabels:function(){var t=this;return u(s().mark((function e(){var r,n,o,a,i,c;return s().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.prev=0,e.next=3,t.systemConfigApiService.getValues("core.customer");case 3:return r=e.sent,n=r["core.customer.classification.labels"],e.abrupt("return",Promise.resolve(n));case 8:e.prev=8,e.t0=e.catch(0),c=null===e.t0||void 0===e.t0||null===(o=e.t0.response)||void 0===o||null===(a=o.data)||void 0===a||null===(i=a.errors[0])||void 0===i?void 0:i.detail,t.createNotificationError({title:t.$tc("global.default.error"),message:c});case 12:case"end":return e.stop()}}),e,null,[[0,8]])})))()},onCloseModal:function(){this.$router.push({name:"swag.customer.classification.index"})}}})}}]);
//# sourceMappingURL=3.js.map