/*! For license information please see review-summary.js.LICENSE.txt */
!function(e){var t={};function r(n){if(t[n])return t[n].exports;var i=t[n]={i:n,l:!1,exports:{}};return e[n].call(i.exports,i,i.exports,r),i.l=!0,i.exports}r.m=e,r.c=t,r.d=function(e,t,n){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)r.d(n,i,function(t){return e[t]}.bind(null,i));return n},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p=(window.__sw__.assetPath + '/bundles/reviewsummary/'),r(r.s="6cBp")}({"6cBp":function(e,t,r){"use strict";r.r(t);r("kDjw");function n(e){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function i(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function o(e,t,r){return(t=function(e){var t=function(e,t){if("object"!==n(e)||null===e)return e;var r=e[Symbol.toPrimitive];if(void 0!==r){var i=r.call(e,t||"default");if("object"!==n(i))return i;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===n(t)?t:String(t)}(t))in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function a(){a=function(){return e};var e={},t=Object.prototype,r=t.hasOwnProperty,i=Object.defineProperty||function(e,t,r){e[t]=r.value},o="function"==typeof Symbol?Symbol:{},s=o.iterator||"@@iterator",u=o.asyncIterator||"@@asyncIterator",c=o.toStringTag||"@@toStringTag";function l(e,t,r){return Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}),e[t]}try{l({},"")}catch(e){l=function(e,t,r){return e[t]=r}}function p(e,t,r,n){var o=t&&t.prototype instanceof m?t:m,a=Object.create(o.prototype),s=new E(n||[]);return i(a,"_invoke",{value:O(e,r,s)}),a}function d(e,t,r){try{return{type:"normal",arg:e.call(t,r)}}catch(e){return{type:"throw",arg:e}}}e.wrap=p;var f={};function m(){}function h(){}function v(){}var y={};l(y,s,(function(){return this}));var w=Object.getPrototypeOf,g=w&&w(w(P([])));g&&g!==t&&r.call(g,s)&&(y=g);var b=v.prototype=m.prototype=Object.create(y);function S(e){["next","throw","return"].forEach((function(t){l(e,t,(function(e){return this._invoke(t,e)}))}))}function x(e,t){function o(i,a,s,u){var c=d(e[i],e,a);if("throw"!==c.type){var l=c.arg,p=l.value;return p&&"object"==n(p)&&r.call(p,"__await")?t.resolve(p.__await).then((function(e){o("next",e,s,u)}),(function(e){o("throw",e,s,u)})):t.resolve(p).then((function(e){l.value=e,s(l)}),(function(e){return o("throw",e,s,u)}))}u(c.arg)}var a;i(this,"_invoke",{value:function(e,r){function n(){return new t((function(t,n){o(e,r,t,n)}))}return a=a?a.then(n,n):n()}})}function O(e,t,r){var n="suspendedStart";return function(i,o){if("executing"===n)throw new Error("Generator is already running");if("completed"===n){if("throw"===i)throw o;return L()}for(r.method=i,r.arg=o;;){var a=r.delegate;if(a){var s=_(a,r);if(s){if(s===f)continue;return s}}if("next"===r.method)r.sent=r._sent=r.arg;else if("throw"===r.method){if("suspendedStart"===n)throw n="completed",r.arg;r.dispatchException(r.arg)}else"return"===r.method&&r.abrupt("return",r.arg);n="executing";var u=d(e,t,r);if("normal"===u.type){if(n=r.done?"completed":"suspendedYield",u.arg===f)continue;return{value:u.arg,done:r.done}}"throw"===u.type&&(n="completed",r.method="throw",r.arg=u.arg)}}}function _(e,t){var r=t.method,n=e.iterator[r];if(void 0===n)return t.delegate=null,"throw"===r&&e.iterator.return&&(t.method="return",t.arg=void 0,_(e,t),"throw"===t.method)||"return"!==r&&(t.method="throw",t.arg=new TypeError("The iterator does not provide a '"+r+"' method")),f;var i=d(n,e.iterator,t.arg);if("throw"===i.type)return t.method="throw",t.arg=i.arg,t.delegate=null,f;var o=i.arg;return o?o.done?(t[e.resultName]=o.value,t.next=e.nextLoc,"return"!==t.method&&(t.method="next",t.arg=void 0),t.delegate=null,f):o:(t.method="throw",t.arg=new TypeError("iterator result is not an object"),t.delegate=null,f)}function j(e){var t={tryLoc:e[0]};1 in e&&(t.catchLoc=e[1]),2 in e&&(t.finallyLoc=e[2],t.afterLoc=e[3]),this.tryEntries.push(t)}function C(e){var t=e.completion||{};t.type="normal",delete t.arg,e.completion=t}function E(e){this.tryEntries=[{tryLoc:"root"}],e.forEach(j,this),this.reset(!0)}function P(e){if(e){var t=e[s];if(t)return t.call(e);if("function"==typeof e.next)return e;if(!isNaN(e.length)){var n=-1,i=function t(){for(;++n<e.length;)if(r.call(e,n))return t.value=e[n],t.done=!1,t;return t.value=void 0,t.done=!0,t};return i.next=i}}return{next:L}}function L(){return{value:void 0,done:!0}}return h.prototype=v,i(b,"constructor",{value:v,configurable:!0}),i(v,"constructor",{value:h,configurable:!0}),h.displayName=l(v,c,"GeneratorFunction"),e.isGeneratorFunction=function(e){var t="function"==typeof e&&e.constructor;return!!t&&(t===h||"GeneratorFunction"===(t.displayName||t.name))},e.mark=function(e){return Object.setPrototypeOf?Object.setPrototypeOf(e,v):(e.__proto__=v,l(e,c,"GeneratorFunction")),e.prototype=Object.create(b),e},e.awrap=function(e){return{__await:e}},S(x.prototype),l(x.prototype,u,(function(){return this})),e.AsyncIterator=x,e.async=function(t,r,n,i,o){void 0===o&&(o=Promise);var a=new x(p(t,r,n,i),o);return e.isGeneratorFunction(r)?a:a.next().then((function(e){return e.done?e.value:a.next()}))},S(b),l(b,c,"Generator"),l(b,s,(function(){return this})),l(b,"toString",(function(){return"[object Generator]"})),e.keys=function(e){var t=Object(e),r=[];for(var n in t)r.push(n);return r.reverse(),function e(){for(;r.length;){var n=r.pop();if(n in t)return e.value=n,e.done=!1,e}return e.done=!0,e}},e.values=P,E.prototype={constructor:E,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=void 0,this.done=!1,this.delegate=null,this.method="next",this.arg=void 0,this.tryEntries.forEach(C),!e)for(var t in this)"t"===t.charAt(0)&&r.call(this,t)&&!isNaN(+t.slice(1))&&(this[t]=void 0)},stop:function(){this.done=!0;var e=this.tryEntries[0].completion;if("throw"===e.type)throw e.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var t=this;function n(r,n){return a.type="throw",a.arg=e,t.next=r,n&&(t.method="next",t.arg=void 0),!!n}for(var i=this.tryEntries.length-1;i>=0;--i){var o=this.tryEntries[i],a=o.completion;if("root"===o.tryLoc)return n("end");if(o.tryLoc<=this.prev){var s=r.call(o,"catchLoc"),u=r.call(o,"finallyLoc");if(s&&u){if(this.prev<o.catchLoc)return n(o.catchLoc,!0);if(this.prev<o.finallyLoc)return n(o.finallyLoc)}else if(s){if(this.prev<o.catchLoc)return n(o.catchLoc,!0)}else{if(!u)throw new Error("try statement without catch or finally");if(this.prev<o.finallyLoc)return n(o.finallyLoc)}}}},abrupt:function(e,t){for(var n=this.tryEntries.length-1;n>=0;--n){var i=this.tryEntries[n];if(i.tryLoc<=this.prev&&r.call(i,"finallyLoc")&&this.prev<i.finallyLoc){var o=i;break}}o&&("break"===e||"continue"===e)&&o.tryLoc<=t&&t<=o.finallyLoc&&(o=null);var a=o?o.completion:{};return a.type=e,a.arg=t,o?(this.method="next",this.next=o.finallyLoc,f):this.complete(a)},complete:function(e,t){if("throw"===e.type)throw e.arg;return"break"===e.type||"continue"===e.type?this.next=e.arg:"return"===e.type?(this.rval=this.arg=e.arg,this.method="return",this.next="end"):"normal"===e.type&&t&&(this.next=t),f},finish:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var r=this.tryEntries[t];if(r.finallyLoc===e)return this.complete(r.completion,r.afterLoc),C(r),f}},catch:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var r=this.tryEntries[t];if(r.tryLoc===e){var n=r.completion;if("throw"===n.type){var i=n.arg;C(r)}return i}}throw new Error("illegal catch attempt")},delegateYield:function(e,t,r){return this.delegate={iterator:P(e),resultName:t,nextLoc:r},"next"===this.method&&(this.arg=void 0),f}},e}function s(e,t,r,n,i,o,a){try{var s=e[o](a),u=s.value}catch(e){return void r(e)}s.done?t(u):Promise.resolve(u).then(n,i)}function u(e){return function(){var t=this,r=arguments;return new Promise((function(n,i){var o=e.apply(t,r);function a(e){s(o,n,i,a,u,"next",e)}function u(e){s(o,n,i,a,u,"throw",e)}a(void 0)}))}}var c=Shopware.Component.getComponentHelper().mapState,l=Shopware,p=l.Context,d=l.Data.Criteria;Shopware.Component.override("sw-product-detail-reviews",{template:'{% block sw_product_detail_reviews %}\n{% parent %}\n<div\n    v-if="getLicense(\'REVIEW_SUMMARY-8147095\') && !isLoading && total && visibleReviewsTotal > 0"\n    class="sw-product-detail-reviews-summary"\n>\n    <sw-confirm-modal\n        v-if="deleteConfirmModalShown"\n        type="delete"\n        :text="$tc(\'sw-product-detail-review-summary.deleteConfirm\')"\n        @confirm="onDeleteSummary"\n        @cancel="deleteConfirmModalShown = false"\n        @close="deleteConfirmModalShown = false"\n    />\n\n    <sw-modal\n        class="sw-product-detail-reviews-summary__generate"\n        v-if="showGenerateModal"\n        :title="$tc(\'sw-product-detail-review-summary.titleGenerate\')"\n        @modal-close="onCloseGenerateModal"\n    >\n        <template #modal-title>\n            <h4\n                id="modalTitleEl"\n                class="sw-modal__title"\n            >\n                {{ $tc(\'sw-product-detail-review-summary.titleGenerate\') }}\n                <sw-ai-copilot-badge />\n            </h4>\n        </template>\n\n        <sw-skeleton v-if="isGenerating" />\n\n        <template v-else>\n            <sw-alert\n                v-if="generateStatus === \'success\'"\n                variant="success"\n                :title="$tc(\'sw-product-detail-review-summary.alerts.successTitle\')"\n            >\n                {{ $tc(\'sw-product-detail-review-summary.alerts.successText\') }}\n            </sw-alert>\n\n            <sw-alert\n                v-if="generateStatus === \'error\'"\n                variant="error"\n                :title="$tc(\'sw-product-detail-review-summary.alerts.errorTitle\')"\n            >\n                {{ $tc(\'sw-product-detail-review-summary.alerts.errorText\') }}\n            </sw-alert>\n\n            <sw-single-select\n                :label="$tc(\'sw-product-detail-review-summary.fieldLabels.phrasing\')"\n                class="sw-product-detail-reviews-summary__phrasing"\n                :options="phrasingOptions"\n                v-model="phrasing"\n                :disabled="isGenerating"\n            />\n\n            <sw-field\n                class="sw-product-detail-reviews-summary__textarea"\n                :label="$tc(\'sw-product-detail-review-summary.fieldLabels.textarea\')"\n                :placeholder="placeholder(product.extensions.reviewSummaries[0], \'summary\', \'\')"\n                type="textarea"\n                v-model="tempSummary"\n                :disabled="isGenerating"\n            />\n            <sw-ai-copilot-warning />\n        </template>\n\n        <sw-button\n            class="sw-product-detail-reviews-summary__re-generate"\n            variant="ghost"\n            size="small"\n            :isLoading="isGenerating"\n            @click="onGenerateReviewSummary"\n            v-tooltip.top="{ message: $tc(\'sw-product-detail-review-summary.actions.renewOverrideWarning\') }"\n        >\n            <sw-icon\n                name="regular-undo"\n                small\n            />\n            {{ $tc(\'sw-product-detail-review-summary.actions.renew\') }}\n        </sw-button>\n\n        <template #modal-footer>\n            <sw-button\n                class="sw-product-detail-reviews-summary__cancel-action"\n                size="small"\n                @click="onCloseGenerateModal"\n            >\n                {{ $tc(\'global.default.cancel\') }}\n            </sw-button>\n\n            <sw-button\n                class="sw-product-detail-reviews-summary__apply-action"\n                variant="primary"\n                :disabled="isGenerating"\n                size="small"\n                @click="onApply"\n            >\n                {{ $tc(\'global.default.apply\') }}\n            </sw-button>\n        </template>\n    </sw-modal>\n\n    <sw-card\n        v-if="product.extensions.reviewSummaries.first() && product.extensions.reviewSummaries.first().summary"\n        class="sw-product-detail-reviews-summary__content"\n        :title="$tc(\'sw-product-detail-review-summary.title\')"\n        position-identifier="sw-product-detail-review-summary"\n    >\n        <template #title>\n            <div class="sw-card__title">\n                {{ $tc(\'sw-product-detail-review-summary.title\') }}\n                <sw-ai-copilot-badge />\n            </div>\n        </template>\n\n        <template #header-right>\n            <sw-switch-field\n                v-model="product.extensions.reviewSummaries.first().visible"\n                :label="$tc(\'sw-product-detail-review-summary.fieldLabels.visible\')"\n            />\n\n            <sw-context-button>\n                <sw-context-menu-item\n                    class="sw-product-detail-reviews-summary__edit-action"\n                    @click="onEdit"\n                >\n                    {{ $tc(\'global.default.edit\') }}\n                </sw-context-menu-item>\n                <sw-context-menu-item\n                    class="sw-product-detail-reviews-summary__delete-action"\n                    @click="confirmDelete(product.extensions.reviewSummaries.first().id)"\n                    variant="danger"\n                >\n                    {{ $tc(\'global.default.delete\') }}\n                </sw-context-menu-item>\n            </sw-context-button>\n        </template>\n\n        <div class="product-detail-reviews-summary__text">\n            {{ product.extensions.reviewSummaries.first().summary }}\n        </div>\n    </sw-card>\n\n    <div\n        v-else\n        class="sw-product-detail-reviews-summary__empty-state">\n        <sw-alert\n            class="sw-product-detail-reviews-summary__empty-state-alert"\n            variant="info"\n            icon="solid-sparkles"\n            title="AI Copilot"\n        >\n            <p class="sw-product-detail-reviews-summary__empty-state-text">\n                {{ $tc(\'sw-product-detail-review-summary.emptyState.text\') }}\n            </p>\n\n            <sw-button\n                class="sw-product-detail-reviews-summary__generate-action"\n                @click.prevent="initGenerate"\n                size="small"\n            >\n                <sw-icon\n                    name="solid-sparkles"\n                    size="14px"\n                />\n                {{ $tc(\'sw-product-detail-review-summary.emptyState.createAction\') }}\n            </sw-button>\n        </sw-alert>\n        <sw-ai-copilot-warning />\n    </div>\n</div>\n{% endblock %}\n',inject:["repositoryFactory","reviewSummaryService"],mixins:[Shopware.Mixin.getByName("placeholder")],data:function(){return{phrasing:"positive",isGenerating:!1,showGenerateModal:!1,deleteConfirmModalShown:!1,generateStatus:null,salesChannelId:null,salesChannels:[],deleteId:"",tempSummary:null,visibleReviewsTotal:0}},created:function(){var e=this;return u(a().mark((function t(){return a().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,e.salesChannelRepository.search(new Shopware.Data.Criteria,e.apiContext);case 2:return e.salesChannels=t.sent,e.salesChannelId=e.salesChannels[0].id,t.next=6,e.getAllVisibleReviews();case 6:case"end":return t.stop()}}),t)})))()},methods:{getAllVisibleReviews:function(){var e=this;return u(a().mark((function t(){var r,n;return a().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return(r=new d).addFilter(d.equals("productId",e.product.id)),r.addFilter(d.equals("status",!0)),t.next=5,e.reviewRepository.searchIds(r,p.api);case 5:n=t.sent,e.visibleReviewsTotal=n.total;case 7:case"end":return t.stop()}}),t)})))()},onShowGenerateModal:function(){this.generateStatus=null,this.showGenerateModal=!0},onEdit:function(){this.tempSummary=this.product.extensions.reviewSummaries.first().summary,this.onShowGenerateModal()},onCloseGenerateModal:function(){this.showGenerateModal=!1},onGenerateReviewSummary:function(){var e=this;return u(a().mark((function t(){var r,n;return a().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return e.isGenerating=!0,r={mood:e.phrasing,languageIds:[e.apiContext.languageId],productId:e.product.id,salesChannelId:e.salesChannelId,fake:!1},t.prev=2,t.next=5,e.reviewSummaryService.generate(r);case 5:n=t.sent,e.tempSummary=n.data[e.apiContext.languageId],e.generateStatus="success",t.next=13;break;case 10:t.prev=10,t.t0=t.catch(2),e.generateStatus="error";case 13:return t.prev=13,e.isGenerating=!1,t.finish(13);case 16:case"end":return t.stop()}}),t,null,[[2,10,13,16]])})))()},onApply:function(){var e=this;this.product.extensions.reviewSummaries.length>0?this.updateExistingSummary():this.createNewSummaryEntity(),this.$nextTick((function(){e.onCloseGenerateModal()}))},updateExistingSummary:function(){this.product.extensions.reviewSummaries.first().summary=this.tempSummary},createNewSummaryEntity:function(){var e=this.summaryRepository.create(this.apiContext);e.summary=this.tempSummary,e.languageId=this.apiContext.languageId,e.salesChannelId=this.salesChannelId,e.visible=!1,this.product.extensions.reviewSummaries.add(e)},onDeleteSummary:function(){var e=this;this.product.extensions.reviewSummaries.first().translations.length>1?this.product.extensions.reviewSummaries.first().summary=null:(this.product.extensions.reviewSummaries.remove(this.deleteId),this.deleteId=""),this.$nextTick((function(){e.deleteConfirmModalShown=!1}))},confirmDelete:function(e){this.deleteId=e,this.deleteConfirmModalShown=!0},initGenerate:function(){this.onShowGenerateModal(),this.onGenerateReviewSummary()},getLicense:function(e){return Shopware.License.get(e)}},computed:function(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?i(Object(r),!0).forEach((function(t){o(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):i(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}({salesChannelRepository:function(){return this.repositoryFactory.create("sales_channel")},summaryRepository:function(){return this.repositoryFactory.create("product_review_summary")},phrasingOptions:function(){return[{label:this.$tc("sw-product-detail-review-summary.phrasingOptions.positive"),value:"positive"},{label:this.$tc("sw-product-detail-review-summary.phrasingOptions.neutral"),value:"neutral"}]}},c("swProductDetail",["apiContext"]))});r("8z/B");function f(e){return(f="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function m(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function h(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?m(Object(r),!0).forEach((function(t){v(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):m(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}function v(e,t,r){return(t=w(t))in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function y(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,w(n.key),n)}}function w(e){var t=function(e,t){if("object"!==f(e)||null===e)return e;var r=e[Symbol.toPrimitive];if(void 0!==r){var n=r.call(e,t||"default");if("object"!==f(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===f(t)?t:String(t)}function g(e,t){return(g=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,t){return e.__proto__=t,e})(e,t)}function b(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var r,n=x(e);if(t){var i=x(this).constructor;r=Reflect.construct(n,arguments,i)}else r=n.apply(this,arguments);return S(this,r)}}function S(e,t){if(t&&("object"===f(t)||"function"==typeof t))return t;if(void 0!==t)throw new TypeError("Derived constructors may only return object or undefined");return function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e)}function x(e){return(x=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var O=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&g(e,t)}(o,Shopware.Classes.ApiService);var t,r,n,i=b(o);function o(e,t){var r;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,o),(r=i.call(this,e,t,null,"application/json")).name="reviewSummaryService",r}return t=o,(r=[{key:"generate",value:function(e){if(Shopware.License.get("REVIEW_SUMMARY-8147095"))return this.httpClient.post("/_action/generate-review-summary",e,{headers:h({},this.getBasicHeaders())})}},{key:"generateBulk",value:function(e){if(Shopware.License.get("REVIEW_SUMMARY-8147095"))return this.httpClient.post("/_action/generate-review-summary-bulk",e,{headers:h({},this.getBasicHeaders())})}}])&&y(t.prototype,r),n&&y(t,n),Object.defineProperty(t,"prototype",{writable:!1}),o}();Shopware.Service().register("reviewSummaryService",(function(e){var t=Shopware.Application.getContainer("init");return new O(t.httpClient,Shopware.Service("loginService"))}))},"8z/B":function(e,t){Shopware.Component.override("sw-product-detail",{computed:{productCriteria:function(){var e=this.$super("productCriteria");return e.addAssociation("reviewSummaries.translations"),e}}})},P8hj:function(e,t,r){"use strict";function n(e,t){for(var r=[],n={},i=0;i<t.length;i++){var o=t[i],a=o[0],s={id:e+":"+i,css:o[1],media:o[2],sourceMap:o[3]};n[a]?n[a].parts.push(s):r.push(n[a]={id:a,parts:[s]})}return r}r.r(t),r.d(t,"default",(function(){return m}));var i="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!i)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var o={},a=i&&(document.head||document.getElementsByTagName("head")[0]),s=null,u=0,c=!1,l=function(){},p=null,d="data-vue-ssr-id",f="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function m(e,t,r,i){c=r,p=i||{};var a=n(e,t);return h(a),function(t){for(var r=[],i=0;i<a.length;i++){var s=a[i];(u=o[s.id]).refs--,r.push(u)}t?h(a=n(e,t)):a=[];for(i=0;i<r.length;i++){var u;if(0===(u=r[i]).refs){for(var c=0;c<u.parts.length;c++)u.parts[c]();delete o[u.id]}}}}function h(e){for(var t=0;t<e.length;t++){var r=e[t],n=o[r.id];if(n){n.refs++;for(var i=0;i<n.parts.length;i++)n.parts[i](r.parts[i]);for(;i<r.parts.length;i++)n.parts.push(y(r.parts[i]));n.parts.length>r.parts.length&&(n.parts.length=r.parts.length)}else{var a=[];for(i=0;i<r.parts.length;i++)a.push(y(r.parts[i]));o[r.id]={id:r.id,refs:1,parts:a}}}}function v(){var e=document.createElement("style");return e.type="text/css",a.appendChild(e),e}function y(e){var t,r,n=document.querySelector("style["+d+'~="'+e.id+'"]');if(n){if(c)return l;n.parentNode.removeChild(n)}if(f){var i=u++;n=s||(s=v()),t=b.bind(null,n,i,!1),r=b.bind(null,n,i,!0)}else n=v(),t=S.bind(null,n),r=function(){n.parentNode.removeChild(n)};return t(e),function(n){if(n){if(n.css===e.css&&n.media===e.media&&n.sourceMap===e.sourceMap)return;t(e=n)}else r()}}var w,g=(w=[],function(e,t){return w[e]=t,w.filter(Boolean).join("\n")});function b(e,t,r,n){var i=r?"":n.css;if(e.styleSheet)e.styleSheet.cssText=g(t,i);else{var o=document.createTextNode(i),a=e.childNodes;a[t]&&e.removeChild(a[t]),a.length?e.insertBefore(o,a[t]):e.appendChild(o)}}function S(e,t){var r=t.css,n=t.media,i=t.sourceMap;if(n&&e.setAttribute("media",n),p.ssrId&&e.setAttribute(d,t.id),i&&(r+="\n/*# sourceURL="+i.sources[0]+" */",r+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(i))))+" */"),e.styleSheet)e.styleSheet.cssText=r;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(r))}}},kDjw:function(e,t,r){var n=r("ru7k");n.__esModule&&(n=n.default),"string"==typeof n&&(n=[[e.i,n,""]]),n.locals&&(e.exports=n.locals);(0,r("P8hj").default)("21290470",n,!0,{})},ru7k:function(e,t,r){}});
//# sourceMappingURL=review-summary.js.map