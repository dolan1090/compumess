/*! For license information please see 3.js.LICENSE.txt */
(this["webpackJsonpPluginadvanced-search"]=this["webpackJsonpPluginadvanced-search"]||[]).push([[3],{"+FbV":function(e,t,n){},"00Xo":function(e,t,n){var r=n("+FbV");r.__esModule&&(r=r.default),"string"==typeof r&&(r=[[e.i,r,""]]),r.locals&&(e.exports=r.locals);(0,n("P8hj").default)("56ba80e6",r,!0,{})},P8hj:function(e,t,n){"use strict";function r(e,t){for(var n=[],r={},a=0;a<t.length;a++){var i=t[a],o=i[0],s={id:e+":"+a,css:i[1],media:i[2],sourceMap:i[3]};r[o]?r[o].parts.push(s):n.push(r[o]={id:o,parts:[s]})}return n}n.r(t),n.d(t,"default",(function(){return d}));var a="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!a)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var i={},o=a&&(document.head||document.getElementsByTagName("head")[0]),s=null,c=0,l=!1,u=function(){},h=null,p="data-vue-ssr-id",f="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function d(e,t,n,a){l=n,h=a||{};var o=r(e,t);return v(o),function(t){for(var n=[],a=0;a<o.length;a++){var s=o[a];(c=i[s.id]).refs--,n.push(c)}t?v(o=r(e,t)):o=[];for(a=0;a<n.length;a++){var c;if(0===(c=n[a]).refs){for(var l=0;l<c.parts.length;l++)c.parts[l]();delete i[c.id]}}}}function v(e){for(var t=0;t<e.length;t++){var n=e[t],r=i[n.id];if(r){r.refs++;for(var a=0;a<r.parts.length;a++)r.parts[a](n.parts[a]);for(;a<n.parts.length;a++)r.parts.push(g(n.parts[a]));r.parts.length>n.parts.length&&(r.parts.length=n.parts.length)}else{var o=[];for(a=0;a<n.parts.length;a++)o.push(g(n.parts[a]));i[n.id]={id:n.id,refs:1,parts:o}}}}function m(){var e=document.createElement("style");return e.type="text/css",o.appendChild(e),e}function g(e){var t,n,r=document.querySelector("style["+p+'~="'+e.id+'"]');if(r){if(l)return u;r.parentNode.removeChild(r)}if(f){var a=c++;r=s||(s=m()),t=S.bind(null,r,a,!1),n=S.bind(null,r,a,!0)}else r=m(),t=b.bind(null,r),n=function(){r.parentNode.removeChild(r)};return t(e),function(r){if(r){if(r.css===e.css&&r.media===e.media&&r.sourceMap===e.sourceMap)return;t(e=r)}else n()}}var y,w=(y=[],function(e,t){return y[e]=t,y.filter(Boolean).join("\n")});function S(e,t,n,r){var a=n?"":r.css;if(e.styleSheet)e.styleSheet.cssText=w(t,a);else{var i=document.createTextNode(a),o=e.childNodes;o[t]&&e.removeChild(o[t]),o.length?e.insertBefore(i,o[t]):e.appendChild(i)}}function b(e,t){var n=t.css,r=t.media,a=t.sourceMap;if(r&&e.setAttribute("media",r),h.ssrId&&e.setAttribute(p,t.id),a&&(n+="\n/*# sourceURL="+a.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(a))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}},SCQV:function(e,t,n){"use strict";n.r(t);n("00Xo");function r(e){return(r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function a(){a=function(){return e};var e={},t=Object.prototype,n=t.hasOwnProperty,i=Object.defineProperty||function(e,t,n){e[t]=n.value},o="function"==typeof Symbol?Symbol:{},s=o.iterator||"@@iterator",c=o.asyncIterator||"@@asyncIterator",l=o.toStringTag||"@@toStringTag";function u(e,t,n){return Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}),e[t]}try{u({},"")}catch(e){u=function(e,t,n){return e[t]=n}}function h(e,t,n,r){var a=t&&t.prototype instanceof d?t:d,o=Object.create(a.prototype),s=new P(r||[]);return i(o,"_invoke",{value:x(e,n,s)}),o}function p(e,t,n){try{return{type:"normal",arg:e.call(t,n)}}catch(e){return{type:"throw",arg:e}}}e.wrap=h;var f={};function d(){}function v(){}function m(){}var g={};u(g,s,(function(){return this}));var y=Object.getPrototypeOf,w=y&&y(y(k([])));w&&w!==t&&n.call(w,s)&&(g=w);var S=m.prototype=d.prototype=Object.create(g);function b(e){["next","throw","return"].forEach((function(t){u(e,t,(function(e){return this._invoke(t,e)}))}))}function _(e,t){function a(i,o,s,c){var l=p(e[i],e,o);if("throw"!==l.type){var u=l.arg,h=u.value;return h&&"object"==r(h)&&n.call(h,"__await")?t.resolve(h.__await).then((function(e){a("next",e,s,c)}),(function(e){a("throw",e,s,c)})):t.resolve(h).then((function(e){u.value=e,s(u)}),(function(e){return a("throw",e,s,c)}))}c(l.arg)}var o;i(this,"_invoke",{value:function(e,n){function r(){return new t((function(t,r){a(e,n,t,r)}))}return o=o?o.then(r,r):r()}})}function x(e,t,n){var r="suspendedStart";return function(a,i){if("executing"===r)throw new Error("Generator is already running");if("completed"===r){if("throw"===a)throw i;return T()}for(n.method=a,n.arg=i;;){var o=n.delegate;if(o){var s=E(o,n);if(s){if(s===f)continue;return s}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if("suspendedStart"===r)throw r="completed",n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);r="executing";var c=p(e,t,n);if("normal"===c.type){if(r=n.done?"completed":"suspendedYield",c.arg===f)continue;return{value:c.arg,done:n.done}}"throw"===c.type&&(r="completed",n.method="throw",n.arg=c.arg)}}}function E(e,t){var n=t.method,r=e.iterator[n];if(void 0===r)return t.delegate=null,"throw"===n&&e.iterator.return&&(t.method="return",t.arg=void 0,E(e,t),"throw"===t.method)||"return"!==n&&(t.method="throw",t.arg=new TypeError("The iterator does not provide a '"+n+"' method")),f;var a=p(r,e.iterator,t.arg);if("throw"===a.type)return t.method="throw",t.arg=a.arg,t.delegate=null,f;var i=a.arg;return i?i.done?(t[e.resultName]=i.value,t.next=e.nextLoc,"return"!==t.method&&(t.method="next",t.arg=void 0),t.delegate=null,f):i:(t.method="throw",t.arg=new TypeError("iterator result is not an object"),t.delegate=null,f)}function L(e){var t={tryLoc:e[0]};1 in e&&(t.catchLoc=e[1]),2 in e&&(t.finallyLoc=e[2],t.afterLoc=e[3]),this.tryEntries.push(t)}function C(e){var t=e.completion||{};t.type="normal",delete t.arg,e.completion=t}function P(e){this.tryEntries=[{tryLoc:"root"}],e.forEach(L,this),this.reset(!0)}function k(e){if(e){var t=e[s];if(t)return t.call(e);if("function"==typeof e.next)return e;if(!isNaN(e.length)){var r=-1,a=function t(){for(;++r<e.length;)if(n.call(e,r))return t.value=e[r],t.done=!1,t;return t.value=void 0,t.done=!0,t};return a.next=a}}return{next:T}}function T(){return{value:void 0,done:!0}}return v.prototype=m,i(S,"constructor",{value:m,configurable:!0}),i(m,"constructor",{value:v,configurable:!0}),v.displayName=u(m,l,"GeneratorFunction"),e.isGeneratorFunction=function(e){var t="function"==typeof e&&e.constructor;return!!t&&(t===v||"GeneratorFunction"===(t.displayName||t.name))},e.mark=function(e){return Object.setPrototypeOf?Object.setPrototypeOf(e,m):(e.__proto__=m,u(e,l,"GeneratorFunction")),e.prototype=Object.create(S),e},e.awrap=function(e){return{__await:e}},b(_.prototype),u(_.prototype,c,(function(){return this})),e.AsyncIterator=_,e.async=function(t,n,r,a,i){void 0===i&&(i=Promise);var o=new _(h(t,n,r,a),i);return e.isGeneratorFunction(n)?o:o.next().then((function(e){return e.done?e.value:o.next()}))},b(S),u(S,l,"Generator"),u(S,s,(function(){return this})),u(S,"toString",(function(){return"[object Generator]"})),e.keys=function(e){var t=Object(e),n=[];for(var r in t)n.push(r);return n.reverse(),function e(){for(;n.length;){var r=n.pop();if(r in t)return e.value=r,e.done=!1,e}return e.done=!0,e}},e.values=k,P.prototype={constructor:P,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=void 0,this.done=!1,this.delegate=null,this.method="next",this.arg=void 0,this.tryEntries.forEach(C),!e)for(var t in this)"t"===t.charAt(0)&&n.call(this,t)&&!isNaN(+t.slice(1))&&(this[t]=void 0)},stop:function(){this.done=!0;var e=this.tryEntries[0].completion;if("throw"===e.type)throw e.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var t=this;function r(n,r){return o.type="throw",o.arg=e,t.next=n,r&&(t.method="next",t.arg=void 0),!!r}for(var a=this.tryEntries.length-1;a>=0;--a){var i=this.tryEntries[a],o=i.completion;if("root"===i.tryLoc)return r("end");if(i.tryLoc<=this.prev){var s=n.call(i,"catchLoc"),c=n.call(i,"finallyLoc");if(s&&c){if(this.prev<i.catchLoc)return r(i.catchLoc,!0);if(this.prev<i.finallyLoc)return r(i.finallyLoc)}else if(s){if(this.prev<i.catchLoc)return r(i.catchLoc,!0)}else{if(!c)throw new Error("try statement without catch or finally");if(this.prev<i.finallyLoc)return r(i.finallyLoc)}}}},abrupt:function(e,t){for(var r=this.tryEntries.length-1;r>=0;--r){var a=this.tryEntries[r];if(a.tryLoc<=this.prev&&n.call(a,"finallyLoc")&&this.prev<a.finallyLoc){var i=a;break}}i&&("break"===e||"continue"===e)&&i.tryLoc<=t&&t<=i.finallyLoc&&(i=null);var o=i?i.completion:{};return o.type=e,o.arg=t,i?(this.method="next",this.next=i.finallyLoc,f):this.complete(o)},complete:function(e,t){if("throw"===e.type)throw e.arg;return"break"===e.type||"continue"===e.type?this.next=e.arg:"return"===e.type?(this.rval=this.arg=e.arg,this.method="return",this.next="end"):"normal"===e.type&&t&&(this.next=t),f},finish:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var n=this.tryEntries[t];if(n.finallyLoc===e)return this.complete(n.completion,n.afterLoc),C(n),f}},catch:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var n=this.tryEntries[t];if(n.tryLoc===e){var r=n.completion;if("throw"===r.type){var a=r.arg;C(n)}return a}}throw new Error("illegal catch attempt")},delegateYield:function(e,t,n){return this.delegate={iterator:k(e),resultName:t,nextLoc:n},"next"===this.method&&(this.arg=void 0),f}},e}function i(e,t,n,r,a,i,o){try{var s=e[i](o),c=s.value}catch(e){return void n(e)}s.done?t(c):Promise.resolve(c).then(r,a)}function o(e){return function(){var t=this,n=arguments;return new Promise((function(r,a){var o=e.apply(t,n);function s(e){i(o,r,a,s,c,"next",e)}function c(e){i(o,r,a,s,c,"throw",e)}s(void 0)}))}}t.default={template:'{% block sw_settings_search_view_live_search_sales_channel %}\n{% endblock %}\n\n{% block sw_settings_search_view_live_search_input %}\n<template v-if="esEnabled">\n    <sw-container columns="1fr minmax(100px, 200px)" gap="0 10px">\n        <sw-simple-search-field\n            v-model="previewSearchTerm"\n            class="sw-settings-search-preview-search__search_box"\n            variant="form"\n            :delay="1000"\n            @search-term-change="onChangePreviewSearchTerm"\n        >\n            <template #sw-simple-search-field-icon>\n                <sw-icon\n                    class="sw-settings-search-live-search__search-icon"\n                    small\n                    name="regular-search-s"\n                    @click="onChangePreviewSearchTerm"\n                />\n            </template>\n        </sw-simple-search-field>\n\n        <sw-single-select\n            class="sw-settings-search__live-search-select-entity"\n            :value="entity"\n            :options="entities"\n            @change="onChangeEntity"\n        />\n    </sw-container>\n</template>\n\n<template v-else>\n    {% parent %}\n</template>\n{% endblock %}\n\n{% block sw_settings_search_view_live_search_results_no_result %}\n<template v-if="showEmptyState">\n    <div class="sw-settings-search-live-search__no-result">\n        {{ $tc(\'sw-settings-search.liveSearchTab.textNoResult\') }}\n    </div>\n</template>\n\n<template v-else>\n    {% parent %}\n</template>\n{% endblock %}\n\n{% block sw_settings_search_view_live_search_results_search_grid %}\n<template v-if="esEnabled">\n    <sw-data-grid\n        v-if="previewResults.length > 0"\n        class="sw-settings-search-live-search__grid-result"\n        :plain-appearance="true"\n        :show-selection="false"\n        :show-actions="false"\n        :data-source="previewResults"\n        :is-loading="searchInProgress"\n        :columns="searchColumns"\n    >\n        <template #column-name="{ item }">\n            <sw-product-variant-info\n                :variations="item.variation"\n                :show-tooltip="false"\n            >\n                <sw-settings-search-live-search-keyword\n                    :text="(item.name || item.translated.name)"\n                    :search-term="previewSearchTerm"\n                />\n            </sw-product-variant-info>\n        </template>\n\n        <template #column-score="{ item }">\n            <span class="sw-settings-search-live-search__grid-result__score">\n                {{ Math.round(parseFloat(item.extensions.search._score)) }}\n            </span>\n        </template>\n\n        <template #pagination>\n            <sw-pagination\n                v-bind="{ page, limit, total }"\n                @page-change="onChangePage"\n            />\n        </template>\n    </sw-data-grid>\n</template>\n\n<template v-else>\n    {% parent %}\n</template>\n{% endblock %}\n\n',inject:["previewSearchService"],data:function(){return{previewResults:[],previewSearchTerm:"",page:1,limit:25,total:0,showEmptyState:!1}},computed:{isSearchEnable:function(){return Boolean(this.asSalesChannelId)},esEnabled:function(){return Shopware.State.getters["swAdvancedSearchState/esEnabled"]},entity:function(){return Shopware.State.getters["swAdvancedSearchState/entity"]},entities:function(){return Shopware.State.getters["swAdvancedSearchState/entities"]},asSalesChannelId:function(){return Shopware.State.getters["swAdvancedSearchState/salesChannelId"]}},methods:{searchOnStorefront:function(){this.salesChannelId=this.asSalesChannelId,this.$super("searchOnStorefront")},onPreviewSearch:function(){var e=arguments,t=this;return o(a().mark((function n(){var r,i,o;return a().wrap((function(n){for(;;)switch(n.prev=n.next){case 0:if(r=e.length>0&&void 0!==e[0]?e[0]:1,i=e.length>1&&void 0!==e[1]?e[1]:25,t.searchInProgress=!0,!(t.previewSearchTerm.length<=0)){n.next=7;break}return t.searchInProgress=!1,t.previewResults=[],n.abrupt("return");case 7:return n.prev=7,n.next=10,t.previewSearchService.search(t.previewSearchTerm,t.entity,t.asSalesChannelId,r,i);case 10:o=n.sent,t.total=o.meta.total,t.previewResults=o.data,t.searchInProgress=!1,t.$emit("live-search-results-change",{searchTerms:t.previewSearchTerm,searchResults:t.previewResults}),n.next=21;break;case 17:n.prev=17,n.t0=n.catch(7),t.searchInProgress=!1,t.createNotificationError({message:n.t0.message});case 21:return n.prev=21,t.previewResults.length<=0&&(t.showEmptyState=!0),n.finish(21);case 24:case"end":return n.stop()}}),n,null,[[7,17,21,24]])})))()},onChangePreviewSearchTerm:function(){this.page=1,this.limit=25,this.total=0,this.showEmptyState=!1,this.onPreviewSearch(this.page,this.limit)},onChangeEntity:function(e){Shopware.State.commit("swAdvancedSearchState/setCurrentSearchType",e),this.previewResults=[],this.previewSearchTerm="",this.page=1,this.limit=25,this.total=0,this.showEmptyState=!1},onChangePage:function(e){var t=e.page,n=e.limit;this.page=t,this.limit=n,this.onPreviewSearch(this.page,this.limit)}}}}}]);
//# sourceMappingURL=3.js.map