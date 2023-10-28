/*! For license information please see 1.js.LICENSE.txt */
(this["webpackJsonpPluginproperty-extractor"]=this["webpackJsonpPluginproperty-extractor"]||[]).push([[1],{"+Jry":function(t,e,n){"use strict";n.r(e);n("mq7B");function r(t){return function(t){if(Array.isArray(t))return a(t)}(t)||function(t){if("undefined"!=typeof Symbol&&null!=t[Symbol.iterator]||null!=t["@@iterator"])return Array.from(t)}(t)||i(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function o(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var n=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=n){var r,o,i,a,s=[],l=!0,c=!1;try{if(i=(n=n.call(t)).next,0===e){if(Object(n)!==n)return;l=!1}else for(;!(l=(r=i.call(n)).done)&&(s.push(r.value),s.length!==e);l=!0);}catch(t){c=!0,o=t}finally{try{if(!l&&null!=n.return&&(a=n.return(),Object(a)!==a))return}finally{if(c)throw o}}return s}}(t,e)||i(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function i(t,e){if(t){if("string"==typeof t)return a(t,e);var n=Object.prototype.toString.call(t).slice(8,-1);return"Object"===n&&t.constructor&&(n=t.constructor.name),"Map"===n||"Set"===n?Array.from(t):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?a(t,e):void 0}}function a(t,e){(null==e||e>t.length)&&(e=t.length);for(var n=0,r=new Array(e);n<e;n++)r[n]=t[n];return r}function s(){s=function(){return t};var t={},e=Object.prototype,n=e.hasOwnProperty,r=Object.defineProperty||function(t,e,n){t[e]=n.value},o="function"==typeof Symbol?Symbol:{},i=o.iterator||"@@iterator",a=o.asyncIterator||"@@asyncIterator",c=o.toStringTag||"@@toStringTag";function p(t,e,n){return Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}),t[e]}try{p({},"")}catch(t){p=function(t,e,n){return t[e]=n}}function u(t,e,n,o){var i=e&&e.prototype instanceof h?e:h,a=Object.create(i.prototype),s=new C(o||[]);return r(a,"_invoke",{value:P(t,n,s)}),a}function d(t,e,n){try{return{type:"normal",arg:t.call(e,n)}}catch(t){return{type:"throw",arg:t}}}t.wrap=u;var f={};function h(){}function m(){}function y(){}var v={};p(v,i,(function(){return this}));var g=Object.getPrototypeOf,w=g&&g(g(E([])));w&&w!==e&&n.call(w,i)&&(v=w);var b=y.prototype=h.prototype=Object.create(v);function x(t){["next","throw","return"].forEach((function(e){p(t,e,(function(t){return this._invoke(e,t)}))}))}function _(t,e){function o(r,i,a,s){var c=d(t[r],t,i);if("throw"!==c.type){var p=c.arg,u=p.value;return u&&"object"==l(u)&&n.call(u,"__await")?e.resolve(u.__await).then((function(t){o("next",t,a,s)}),(function(t){o("throw",t,a,s)})):e.resolve(u).then((function(t){p.value=t,a(p)}),(function(t){return o("throw",t,a,s)}))}s(c.arg)}var i;r(this,"_invoke",{value:function(t,n){function r(){return new e((function(e,r){o(t,n,e,r)}))}return i=i?i.then(r,r):r()}})}function P(t,e,n){var r="suspendedStart";return function(o,i){if("executing"===r)throw new Error("Generator is already running");if("completed"===r){if("throw"===o)throw i;return j()}for(n.method=o,n.arg=i;;){var a=n.delegate;if(a){var s=O(a,n);if(s){if(s===f)continue;return s}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if("suspendedStart"===r)throw r="completed",n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);r="executing";var l=d(t,e,n);if("normal"===l.type){if(r=n.done?"completed":"suspendedYield",l.arg===f)continue;return{value:l.arg,done:n.done}}"throw"===l.type&&(r="completed",n.method="throw",n.arg=l.arg)}}}function O(t,e){var n=e.method,r=t.iterator[n];if(void 0===r)return e.delegate=null,"throw"===n&&t.iterator.return&&(e.method="return",e.arg=void 0,O(t,e),"throw"===e.method)||"return"!==n&&(e.method="throw",e.arg=new TypeError("The iterator does not provide a '"+n+"' method")),f;var o=d(r,t.iterator,e.arg);if("throw"===o.type)return e.method="throw",e.arg=o.arg,e.delegate=null,f;var i=o.arg;return i?i.done?(e[t.resultName]=i.value,e.next=t.nextLoc,"return"!==e.method&&(e.method="next",e.arg=void 0),e.delegate=null,f):i:(e.method="throw",e.arg=new TypeError("iterator result is not an object"),e.delegate=null,f)}function L(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function S(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function C(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(L,this),this.reset(!0)}function E(t){if(t){var e=t[i];if(e)return e.call(t);if("function"==typeof t.next)return t;if(!isNaN(t.length)){var r=-1,o=function e(){for(;++r<t.length;)if(n.call(t,r))return e.value=t[r],e.done=!1,e;return e.value=void 0,e.done=!0,e};return o.next=o}}return{next:j}}function j(){return{value:void 0,done:!0}}return m.prototype=y,r(b,"constructor",{value:y,configurable:!0}),r(y,"constructor",{value:m,configurable:!0}),m.displayName=p(y,c,"GeneratorFunction"),t.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===m||"GeneratorFunction"===(e.displayName||e.name))},t.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,y):(t.__proto__=y,p(t,c,"GeneratorFunction")),t.prototype=Object.create(b),t},t.awrap=function(t){return{__await:t}},x(_.prototype),p(_.prototype,a,(function(){return this})),t.AsyncIterator=_,t.async=function(e,n,r,o,i){void 0===i&&(i=Promise);var a=new _(u(e,n,r,o),i);return t.isGeneratorFunction(n)?a:a.next().then((function(t){return t.done?t.value:a.next()}))},x(b),p(b,c,"Generator"),p(b,i,(function(){return this})),p(b,"toString",(function(){return"[object Generator]"})),t.keys=function(t){var e=Object(t),n=[];for(var r in e)n.push(r);return n.reverse(),function t(){for(;n.length;){var r=n.pop();if(r in e)return t.value=r,t.done=!1,t}return t.done=!0,t}},t.values=E,C.prototype={constructor:C,reset:function(t){if(this.prev=0,this.next=0,this.sent=this._sent=void 0,this.done=!1,this.delegate=null,this.method="next",this.arg=void 0,this.tryEntries.forEach(S),!t)for(var e in this)"t"===e.charAt(0)&&n.call(this,e)&&!isNaN(+e.slice(1))&&(this[e]=void 0)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(t){if(this.done)throw t;var e=this;function r(n,r){return a.type="throw",a.arg=t,e.next=n,r&&(e.method="next",e.arg=void 0),!!r}for(var o=this.tryEntries.length-1;o>=0;--o){var i=this.tryEntries[o],a=i.completion;if("root"===i.tryLoc)return r("end");if(i.tryLoc<=this.prev){var s=n.call(i,"catchLoc"),l=n.call(i,"finallyLoc");if(s&&l){if(this.prev<i.catchLoc)return r(i.catchLoc,!0);if(this.prev<i.finallyLoc)return r(i.finallyLoc)}else if(s){if(this.prev<i.catchLoc)return r(i.catchLoc,!0)}else{if(!l)throw new Error("try statement without catch or finally");if(this.prev<i.finallyLoc)return r(i.finallyLoc)}}}},abrupt:function(t,e){for(var r=this.tryEntries.length-1;r>=0;--r){var o=this.tryEntries[r];if(o.tryLoc<=this.prev&&n.call(o,"finallyLoc")&&this.prev<o.finallyLoc){var i=o;break}}i&&("break"===t||"continue"===t)&&i.tryLoc<=e&&e<=i.finallyLoc&&(i=null);var a=i?i.completion:{};return a.type=t,a.arg=e,i?(this.method="next",this.next=i.finallyLoc,f):this.complete(a)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),f},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var n=this.tryEntries[e];if(n.finallyLoc===t)return this.complete(n.completion,n.afterLoc),S(n),f}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var n=this.tryEntries[e];if(n.tryLoc===t){var r=n.completion;if("throw"===r.type){var o=r.arg;S(n)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(t,e,n){return this.delegate={iterator:E(t),resultName:e,nextLoc:n},"next"===this.method&&(this.arg=void 0),f}},t}function l(t){return(l="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function c(t,e,n,r,o,i,a){try{var s=t[i](a),l=s.value}catch(t){return void n(t)}s.done?e(l):Promise.resolve(l).then(r,o)}function p(t){return function(){var e=this,n=arguments;return new Promise((function(r,o){var i=t.apply(e,n);function a(t){c(i,r,o,a,s,"next",t)}function s(t){c(i,r,o,a,s,"throw",t)}a(void 0)}))}}function u(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}function d(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?u(Object(n),!0).forEach((function(e){f(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):u(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}function f(t,e,n){return(e=function(t){var e=function(t,e){if("object"!==l(t)||null===t)return t;var n=t[Symbol.toPrimitive];if(void 0!==n){var r=n.call(t,e||"default");if("object"!==l(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(t)}(t,"string");return"symbol"===l(e)?e:String(e)}(e))in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}var h=Shopware.Mixin,m=Shopware.Data,y=m.Criteria,v=m.EntityCollection,g=Shopware.Component.getComponentHelper().mapState;e.default={template:'{% block sw_property_assistant_modal %}\n    <sw-modal class="sw-property-assistant-modal" @modal-close="onCancel">\n        <template #modal-header>\n            <div class="sw-property-assistant-modal__header-container">\n                <div class="sw-modal__titles">\n                    <h4\n                        id="modalTitleEl"\n                        class="sw-modal__title"\n                    >\n                        {{ $tc(\'property-extractor.assistant-modal.title\') }}\n                    </h4>\n                </div>\n\n                <sw-ai-copilot-badge />\n            </div>\n\n            <button\n                class="sw-modal__close"\n                @click="onCancel"\n            >\n                <sw-icon name="regular-times-s" />\n            </button>\n        </template>\n\n        <template #body>\n            <div class="sw-property-assistant-modal__body">\n                <div class="sw-property-assistant-modal__body-selection">\n                    <p class="sw-property-assistant-modal__description">\n                        {{ $tc(\'property-extractor.assistant-modal.description\') }}\n                    </p>\n\n                    <div class="sw-property-assistant-modal__textarea">\n                        <sw-help-text\n                            v-if="generateButtonHelpText !== \'\'"\n                            class="sw-property-assistant-modal__textarea-help"\n                            :text="generateButtonHelpText"\n                        />\n                        <sw-textarea-field\n                            v-model="description"\n                            class="sw-property-assistant-modal__textarea-field"\n                            :label="$tc(\'property-extractor.assistant-modal.textarea-label\')"\n                            :placeholder="$tc(\'property-extractor.assistant-modal.textarea-placeholder\')"\n                            @input="onChange"\n                            @change="onChange"\n                        />\n                        <span class="sw-property-assistant-modal__textarea-chars">\n                            {{ description.length }}/{{ descriptionLimit }}\n                        </span>\n                    </div>\n\n                    <span v-tooltip="generateButtonTooltip">\n                        <sw-button\n                            class="sw-property-assistant-modal__body-selection-generate-button"\n                            size="small"\n                            variant="primary"\n                            :is-loading="isLoading"\n                            :disabled="generateButtonDisabled"\n                            @click="onGenerate"\n                        >\n                            <sw-icon v-if="properties.length > 0" name="regular-shuffle" small />\n                            {{ generateButtonLabel }}\n                        </sw-button>\n                    </span>\n\n                    <sw-button\n                        v-if="showResetDescriptionButton"\n                        class="sw-property-assistant-modal__body-selection-reset-button"\n                        size="small"\n                        @click="setProductDescription"\n                    >\n                        {{ $tc(\'property-extractor.assistant-modal.reset-description-button-label\') }}\n                    </sw-button>\n\n                    <p v-if="showPropertyTable && !generateButtonDisabled && !isLoading" class="sw-property-assistant-modal__body-selection-notice">\n                        {{ $tc(\'property-extractor.assistant-modal.button-description\') }}\n                    </p>\n                </div>\n\n                <sw-alert\n                    class="sw-property-assistant-modal__alert"\n                    v-if="infoSnippet"\n                    :title="$tc(`${infoSnippet}.title`)"\n                    variant="info"\n                >\n                    {{ $tc(`${infoSnippet}.message`) }}\n                </sw-alert>\n\n                <div v-if="showPropertyTable" class="sw-property-assistant-modal__table">\n                    <sw-card-section divider="bottom" secondary>\n                        <sw-simple-search-field\n                            ref="searchField"\n                            v-model="searchTerm"\n                            size="small"\n                            variant="form"\n                            :placeholder="$tc(\'property-extractor.assistant-modal.search-placeholder\')"\n                            :disabled="isLoading"\n                        />\n                    </sw-card-section>\n\n                    <sw-empty-state\n                        v-if="searchTerm && filteredProperties.length === 0"\n                        :absolute="false"\n                        :title="$tc(\'sw-property-search.noPropertiesFound\')"\n                        :subline="$tc(\'sw-property-search.noPropertiesFoundDescription\')"\n                    >\n                        <template #icon>\n                            <img\n                                :src="\'/administration/static/img/empty-states/products-empty-state.svg\' | asset"\n                                :alt="$tc(\'sw-empty-state.messageNoResultTitle\')"\n                            >\n                        </template>\n                    </sw-empty-state>\n\n                    <sw-data-grid\n                        v-else\n                        ref="dataGrid"\n                        identifier="sw-property-assistant-modal-properties-data-grid"\n                        :columns="getAssistantColumns()"\n                        :data-source="paginatedProperties"\n                        :is-loading="isLoading"\n                        :skeleton-item-amount="3"\n                        :show-selection="false"\n                        show-actions\n                        show-header\n                        allow-inline-edit\n                        plain-appearance\n                    >\n                        <template #actions="{ item, itemIndex }">\n                            <sw-context-menu-item\n                                v-if="!item.isNew()"\n                                :router-link="{ name: \'sw.property.detail\', params: { id: item.id } }"\n                            >\n                                {{ $tc(\'property-extractor.assistant-modal.table.edit-action\') }}\n                            </sw-context-menu-item>\n\n                            <sw-context-menu-item\n                                class="sw-property-assistant-modal-properties-data-grid__delete"\n                                variant="danger"\n                                @click="onDelete(itemIndex)">\n                                {{ $tc(\'property-extractor.assistant-modal.table.delete-action\') }}\n                            </sw-context-menu-item>\n                        </template>\n\n                        <template #column-name="{ item, column, isInlineEdit }">\n                            <sw-data-grid-inline-edit\n                                class="sw-property-assistant-modal-properties-data-grid__edit-group-name"\n                                v-if="isInlineEdit && item.isNew()"\n                                :column="column"\n                                :compact="true"\n                                :value="item.translated?.name || item.name"\n                                @input="item.name = $event"\n                            />\n\n                            <div v-else class="sw-property-assistant-modal__table-column">\n                                <span class="sw-property-assistant-modal__table-column__name">{{ item.translated?.name || item.name }}</span>\n                                <sw-label v-if="item.isNew()" class="sw-property-assistant-modal__table-new-indicator" variant="primary" size="small" appearance="pill">\n                                    {{ $tc(\'property-extractor.assistant-modal.table.new-indicator\') }}\n                                </sw-label>\n                            </div>\n\n                        </template>\n\n                        <template #column-options="{ item, itemIndex }">\n                            <template v-for="(item, index) in item.options">\n                                <sw-label\n                                    :key="index"\n                                    class="sw-property-assistant-modal__table-label"\n                                    @dismiss="onDismiss(itemIndex, index)"\n                                    dismissable\n                                >\n                                    {{ item.translated?.name || item.name }}\n                                    <sw-label v-if="item.isNew()" class="sw-property-assistant-modal__table-new-indicator" variant="primary" size="small" appearance="pill">\n                                        {{ $tc(\'property-extractor.assistant-modal.table.new-indicator\') }}\n                                    </sw-label>\n                                </sw-label>\n                            </template>\n                        </template>\n\n                        <template #pagination>\n                            <sw-pagination\n                                :page="page"\n                                :limit="limit"\n                                :total="filteredProperties.length"\n                                :steps="[5, 10, 25, 50]"\n                                @page-change="onPageChange"\n                            />\n                        </template>\n                    </sw-data-grid>\n                </div>\n            </div>\n        </template>\n\n        <template #modal-footer>\n            <sw-button\n                class="sw-property-assistant-modal__cancel-button"\n                size="small"\n                @click="onCancel"\n            >\n                {{ $tc(\'global.default.cancel\') }}\n            </sw-button>\n\n            <sw-button\n                class="sw-property-assistant-modal__save-button"\n                :is-loading="isSaving"\n                v-if="showPropertyTable && properties.length > 0"\n                variant="primary" size="small"\n                @click="onSave"\n            >\n                {{ $tc(\'global.default.save\') }}\n            </sw-button>\n        </template>\n    </sw-modal>\n{% endblock %}\n',inject:["propertyExtractorService","repositoryFactory"],mixins:[h.getByName("notification"),h.getByName("listing")],data:function(){return{isLoading:!1,isSaving:!1,hasChanged:!1,showPropertyTable:!1,descriptionLimit:4e3,description:"",searchTerm:null,properties:[],page:1,limit:10,disableRouteParams:!0,guessed:[],newOptions:[],disableGenerate:!1,infoSnippet:null}},computed:d(d({},g("swProductDetail",["apiContext","product"])),{},{filteredProperties:function(){var t=this;return this.searchTerm?this.properties.filter((function(e){var n,r=((null===(n=e.translated)||void 0===n?void 0:n.name)||e.name).toLowerCase().includes(t.searchTerm.toLowerCase()),o=e.options.some((function(e){var n;return((null===(n=e.translated)||void 0===n?void 0:n.name)||e.name).toLowerCase().includes(t.searchTerm.toLowerCase())}));return r||o})):this.properties},generateButtonDisabled:function(){return this.description.length<200||this.description.length>this.descriptionLimit||this.disableGenerate},generateButtonHelpText:function(){return this.description.length<200?this.$tc("property-extractor.assistant-modal.help-text.to-short"):this.description.length>this.descriptionLimit?this.$tc("property-extractor.assistant-modal.help-text.to-long"):""},generateButtonLabel:function(){return this.properties.length<1?this.$tc("property-extractor.assistant-modal.generate-button.default"):this.$tc("property-extractor.assistant-modal.generate-button.new")},generateButtonTooltip:function(){return{message:this.$tc("property-extractor.assistant-modal.generate-button.tooltip"),disabled:!this.disableGenerate}},showResetDescriptionButton:function(){return this.hasChanged&&this.description!==this.sanitizedProductDescription},paginatedProperties:function(){return this.filteredProperties.slice((this.page-1)*this.limit,this.page*this.limit)},propertyGroupRepository:function(){return this.repositoryFactory.create("property_group")},propertyGroupOptionRepository:function(){return this.repositoryFactory.create("property_group_option")},sanitizedProductDescription:function(){var t,e=document.createElement("div");return e.innerHTML=(null===(t=this.product.translated)||void 0===t?void 0:t.description)||this.product.description,e.textContent}}),mounted:function(){this.setProductDescription()},methods:{getList:function(){},setProductDescription:function(){this.description=this.sanitizedProductDescription,this.disableGenerate=!1,this.infoSnippet=null},onGenerate:function(){var t=this;return p(s().mark((function e(){return s().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return t.showPropertyTable=!0,t.isLoading=!0,e.prev=2,e.next=5,t.propertyExtractorService.generate(t.description,{"sw-language-id":t.apiContext.languageId});case 5:if(t.guessed=e.sent,"object"!==l(t.guessed)||!Object.keys(t.guessed).length){e.next=12;break}return e.next=9,t.searchGroups(t.guessed);case 9:t.properties=e.sent,e.next=16;break;case 12:return t.properties=[],t.showPropertyTable=!1,t.infoSnippet="property-extractor.assistant-modal.no-result",e.abrupt("return");case 16:t.properties.length||(t.showPropertyTable=!1,t.infoSnippet="property-extractor.assistant-modal.no-suggestions"),t.disableGenerate=!0,e.next=24;break;case 20:e.prev=20,e.t0=e.catch(2),t.showPropertyTable=!1,t.createNotificationError({message:t.$tc("property-extractor.assistant-modal.table.error-message")});case 24:return e.prev=24,t.isLoading=!1,e.finish(24);case 27:case"end":return e.stop()}}),e,null,[[2,20,24,27]])})))()},onSave:function(){var t=this;return p(s().mark((function e(){return s().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return t.isSaving=!0,e.prev=1,e.next=4,t.propertyGroupRepository.saveAll(t.properties,t.apiContext).then(p(s().mark((function e(){return s().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,t.searchPropertyOptions();case 2:t.newOptions=e.sent;case 3:case"end":return e.stop()}}),e)}))));case 4:e.next=9;break;case 6:e.prev=6,e.t0=e.catch(1),t.createNotificationError({message:t.$tc("property-extractor.assistant-modal.table.error-message")});case 9:return e.prev=9,t.$emit("modal-save",t.newOptions),t.isSaving=!1,e.finish(9);case 13:case"end":return e.stop()}}),e,null,[[1,6,9,13]])})))()},onCancel:function(){this.$emit("close-property-assistant-modal")},onDismiss:function(t,e){var n=this.properties.at(t),r=n.options.at(e),o=n.getOrigin().options.findIndex((function(t){return t.id===r.id}));-1!==o&&n.getOrigin().options.splice(o,1),n.options.splice(e,1),0===n.options.length&&this.properties.splice(t,1),0===this.properties.length&&(this.showPropertyTable=!1)},onDelete:function(t){this.properties.splice(t,1),0===this.properties.length&&(this.showPropertyTable=!1)},onChange:function(){this.hasChanged=!0,this.disableGenerate=!1,this.infoSnippet=null},getAssistantColumns:function(){return[{property:"name",label:"property-extractor.assistant-modal.table.name-label",allowResize:!1,width:"120px",inlineEdit:"string"},{property:"options",label:"property-extractor.assistant-modal.table.options-label",allowResize:!1,inlineEdit:!0}]},searchGroups:function(t){var e=this;return p(s().mark((function n(){var r,i;return s().wrap((function(n){for(;;)switch(n.prev=n.next){case 0:return n.next=2,e.searchPropertyGroups(t);case 2:return r=n.sent,i=new v(e.propertyGroupRepository.route,e.propertyGroupRepository.entityName,e.apiContext),Object.entries(t).forEach((function(t){var n=o(t,2),a=n[0],s=n[1];s=Object.values(s);var l=r.findIndex((function(t){var e;return((null===(e=t.translated)||void 0===e?void 0:e.name)||t.name).toLowerCase()===a.toLowerCase()}));if(-1===l){var c=e.propertyGroupRepository.create(e.apiContext);return c.name=a,s.forEach((function(t){var n=e.propertyGroupOptionRepository.create(e.apiContext);n.name=t,c.options.add(n)})),void i.add(c)}var p=r.at(l),u=new v(e.propertyGroupOptionRepository.route,e.propertyGroupOptionRepository.entityName,e.apiContext);s.forEach((function(t){var n=p.options.findIndex((function(e){var n;return((null===(n=e.translated)||void 0===n?void 0:n.name)||e.name).toLowerCase()===t.toLowerCase()}));if(-1===n){var r=e.propertyGroupOptionRepository.create(e.apiContext);return r.name=t,void u.add(r)}var o=p.options.at(n);e.product.properties.map((function(t){return t.id})).includes(o.id)||u.add(o)})),0!==u.length&&(p.options=u,i.add(p))})),n.abrupt("return",i);case 6:case"end":return n.stop()}}),n)})))()},searchPropertyGroups:function(t){var e=this;return p(s().mark((function n(){var o,i;return s().wrap((function(n){for(;;)switch(n.prev=n.next){case 0:return(i=new y).addAssociation("options"),i.addFilter(y.equalsAny("name",Object.keys(t))),i.getAssociation("options").addFilter(y.equalsAny("name",(o=[]).concat.apply(o,r(Object.values(t))))),n.next=6,e.propertyGroupRepository.search(i,e.apiContext);case 6:return n.abrupt("return",n.sent);case 7:case"end":return n.stop()}}),n)})))()},searchPropertyOptions:function(){var t=this;return p(s().mark((function e(){var n,o;return s().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(n=new y,o=t.properties.reduce((function(t,e){return[].concat(r(t),r(e.options.map((function(t){return t.id})).filter((function(t){return t}))))}),[]),n.setIds(o),0!==o.length){e.next=5;break}return e.abrupt("return");case 5:return e.next=7,t.propertyGroupOptionRepository.search(n,t.apiContext);case 7:return e.abrupt("return",e.sent);case 8:case"end":return e.stop()}}),e)})))()},onPageChange:function(t){var e=t.page,n=t.limit;this.page=e,this.limit=n}}}},Dr9l:function(t,e,n){},P8hj:function(t,e,n){"use strict";function r(t,e){for(var n=[],r={},o=0;o<e.length;o++){var i=e[o],a=i[0],s={id:t+":"+o,css:i[1],media:i[2],sourceMap:i[3]};r[a]?r[a].parts.push(s):n.push(r[a]={id:a,parts:[s]})}return n}n.r(e),n.d(e,"default",(function(){return h}));var o="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!o)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var i={},a=o&&(document.head||document.getElementsByTagName("head")[0]),s=null,l=0,c=!1,p=function(){},u=null,d="data-vue-ssr-id",f="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function h(t,e,n,o){c=n,u=o||{};var a=r(t,e);return m(a),function(e){for(var n=[],o=0;o<a.length;o++){var s=a[o];(l=i[s.id]).refs--,n.push(l)}e?m(a=r(t,e)):a=[];for(o=0;o<n.length;o++){var l;if(0===(l=n[o]).refs){for(var c=0;c<l.parts.length;c++)l.parts[c]();delete i[l.id]}}}}function m(t){for(var e=0;e<t.length;e++){var n=t[e],r=i[n.id];if(r){r.refs++;for(var o=0;o<r.parts.length;o++)r.parts[o](n.parts[o]);for(;o<n.parts.length;o++)r.parts.push(v(n.parts[o]));r.parts.length>n.parts.length&&(r.parts.length=n.parts.length)}else{var a=[];for(o=0;o<n.parts.length;o++)a.push(v(n.parts[o]));i[n.id]={id:n.id,refs:1,parts:a}}}}function y(){var t=document.createElement("style");return t.type="text/css",a.appendChild(t),t}function v(t){var e,n,r=document.querySelector("style["+d+'~="'+t.id+'"]');if(r){if(c)return p;r.parentNode.removeChild(r)}if(f){var o=l++;r=s||(s=y()),e=b.bind(null,r,o,!1),n=b.bind(null,r,o,!0)}else r=y(),e=x.bind(null,r),n=function(){r.parentNode.removeChild(r)};return e(t),function(r){if(r){if(r.css===t.css&&r.media===t.media&&r.sourceMap===t.sourceMap)return;e(t=r)}else n()}}var g,w=(g=[],function(t,e){return g[t]=e,g.filter(Boolean).join("\n")});function b(t,e,n,r){var o=n?"":r.css;if(t.styleSheet)t.styleSheet.cssText=w(e,o);else{var i=document.createTextNode(o),a=t.childNodes;a[e]&&t.removeChild(a[e]),a.length?t.insertBefore(i,a[e]):t.appendChild(i)}}function x(t,e){var n=e.css,r=e.media,o=e.sourceMap;if(r&&t.setAttribute("media",r),u.ssrId&&t.setAttribute(d,e.id),o&&(n+="\n/*# sourceURL="+o.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(o))))+" */"),t.styleSheet)t.styleSheet.cssText=n;else{for(;t.firstChild;)t.removeChild(t.firstChild);t.appendChild(document.createTextNode(n))}}},mq7B:function(t,e,n){var r=n("Dr9l");r.__esModule&&(r=r.default),"string"==typeof r&&(r=[[t.i,r,""]]),r.locals&&(t.exports=r.locals);(0,n("P8hj").default)("439b7688",r,!0,{})}}]);
//# sourceMappingURL=1.js.map