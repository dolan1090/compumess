/*! For license information please see 1.js.LICENSE.txt */
(this["webpackJsonpPluginflow-sharing"]=this["webpackJsonpPluginflow-sharing"]||[]).push([[1],{"+ZPZ":function(e,t,n){var r=n("iH3F");r.__esModule&&(r=r.default),"string"==typeof r&&(r=[[e.i,r,""]]),r.locals&&(e.exports=r.locals);(0,n("P8hj").default)("4ee793a7",r,!0,{})},MUxG:function(e,t,n){"use strict";n.r(t);n("+ZPZ");function r(e){return(r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function i(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function o(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?i(Object(n),!0).forEach((function(t){a(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):i(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function a(e,t,n){return(t=function(e){var t=function(e,t){if("object"!==r(e)||null===e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var i=n.call(e,t||"default");if("object"!==r(i))return i;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===r(t)?t:String(t)}(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function l(e){return function(e){if(Array.isArray(e))return s(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||function(e,t){if(!e)return;if("string"==typeof e)return s(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return s(e,t)}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function s(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}function u(){u=function(){return e};var e={},t=Object.prototype,n=t.hasOwnProperty,i=Object.defineProperty||function(e,t,n){e[t]=n.value},o="function"==typeof Symbol?Symbol:{},a=o.iterator||"@@iterator",l=o.asyncIterator||"@@asyncIterator",s=o.toStringTag||"@@toStringTag";function c(e,t,n){return Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}),e[t]}try{c({},"")}catch(e){c=function(e,t,n){return e[t]=n}}function d(e,t,n,r){var o=t&&t.prototype instanceof h?t:h,a=Object.create(o.prototype),l=new C(r||[]);return i(a,"_invoke",{value:j(e,n,l)}),a}function f(e,t,n){try{return{type:"normal",arg:e.call(t,n)}}catch(e){return{type:"throw",arg:e}}}e.wrap=d;var p={};function h(){}function m(){}function v(){}var w={};c(w,a,(function(){return this}));var y=Object.getPrototypeOf,g=y&&y(y(L([])));g&&g!==t&&n.call(g,a)&&(w=g);var b=v.prototype=h.prototype=Object.create(w);function S(e){["next","throw","return"].forEach((function(t){c(e,t,(function(e){return this._invoke(t,e)}))}))}function x(e,t){function o(i,a,l,s){var u=f(e[i],e,a);if("throw"!==u.type){var c=u.arg,d=c.value;return d&&"object"==r(d)&&n.call(d,"__await")?t.resolve(d.__await).then((function(e){o("next",e,l,s)}),(function(e){o("throw",e,l,s)})):t.resolve(d).then((function(e){c.value=e,l(c)}),(function(e){return o("throw",e,l,s)}))}s(u.arg)}var a;i(this,"_invoke",{value:function(e,n){function r(){return new t((function(t,r){o(e,n,t,r)}))}return a=a?a.then(r,r):r()}})}function j(e,t,n){var r="suspendedStart";return function(i,o){if("executing"===r)throw new Error("Generator is already running");if("completed"===r){if("throw"===i)throw o;return k()}for(n.method=i,n.arg=o;;){var a=n.delegate;if(a){var l=R(a,n);if(l){if(l===p)continue;return l}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if("suspendedStart"===r)throw r="completed",n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);r="executing";var s=f(e,t,n);if("normal"===s.type){if(r=n.done?"completed":"suspendedYield",s.arg===p)continue;return{value:s.arg,done:n.done}}"throw"===s.type&&(r="completed",n.method="throw",n.arg=s.arg)}}}function R(e,t){var n=t.method,r=e.iterator[n];if(void 0===r)return t.delegate=null,"throw"===n&&e.iterator.return&&(t.method="return",t.arg=void 0,R(e,t),"throw"===t.method)||"return"!==n&&(t.method="throw",t.arg=new TypeError("The iterator does not provide a '"+n+"' method")),p;var i=f(r,e.iterator,t.arg);if("throw"===i.type)return t.method="throw",t.arg=i.arg,t.delegate=null,p;var o=i.arg;return o?o.done?(t[e.resultName]=o.value,t.next=e.nextLoc,"return"!==t.method&&(t.method="next",t.arg=void 0),t.delegate=null,p):o:(t.method="throw",t.arg=new TypeError("iterator result is not an object"),t.delegate=null,p)}function _(e){var t={tryLoc:e[0]};1 in e&&(t.catchLoc=e[1]),2 in e&&(t.finallyLoc=e[2],t.afterLoc=e[3]),this.tryEntries.push(t)}function O(e){var t=e.completion||{};t.type="normal",delete t.arg,e.completion=t}function C(e){this.tryEntries=[{tryLoc:"root"}],e.forEach(_,this),this.reset(!0)}function L(e){if(e){var t=e[a];if(t)return t.call(e);if("function"==typeof e.next)return e;if(!isNaN(e.length)){var r=-1,i=function t(){for(;++r<e.length;)if(n.call(e,r))return t.value=e[r],t.done=!1,t;return t.value=void 0,t.done=!0,t};return i.next=i}}return{next:k}}function k(){return{value:void 0,done:!0}}return m.prototype=v,i(b,"constructor",{value:v,configurable:!0}),i(v,"constructor",{value:m,configurable:!0}),m.displayName=c(v,s,"GeneratorFunction"),e.isGeneratorFunction=function(e){var t="function"==typeof e&&e.constructor;return!!t&&(t===m||"GeneratorFunction"===(t.displayName||t.name))},e.mark=function(e){return Object.setPrototypeOf?Object.setPrototypeOf(e,v):(e.__proto__=v,c(e,s,"GeneratorFunction")),e.prototype=Object.create(b),e},e.awrap=function(e){return{__await:e}},S(x.prototype),c(x.prototype,l,(function(){return this})),e.AsyncIterator=x,e.async=function(t,n,r,i,o){void 0===o&&(o=Promise);var a=new x(d(t,n,r,i),o);return e.isGeneratorFunction(n)?a:a.next().then((function(e){return e.done?e.value:a.next()}))},S(b),c(b,s,"Generator"),c(b,a,(function(){return this})),c(b,"toString",(function(){return"[object Generator]"})),e.keys=function(e){var t=Object(e),n=[];for(var r in t)n.push(r);return n.reverse(),function e(){for(;n.length;){var r=n.pop();if(r in t)return e.value=r,e.done=!1,e}return e.done=!0,e}},e.values=L,C.prototype={constructor:C,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=void 0,this.done=!1,this.delegate=null,this.method="next",this.arg=void 0,this.tryEntries.forEach(O),!e)for(var t in this)"t"===t.charAt(0)&&n.call(this,t)&&!isNaN(+t.slice(1))&&(this[t]=void 0)},stop:function(){this.done=!0;var e=this.tryEntries[0].completion;if("throw"===e.type)throw e.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var t=this;function r(n,r){return a.type="throw",a.arg=e,t.next=n,r&&(t.method="next",t.arg=void 0),!!r}for(var i=this.tryEntries.length-1;i>=0;--i){var o=this.tryEntries[i],a=o.completion;if("root"===o.tryLoc)return r("end");if(o.tryLoc<=this.prev){var l=n.call(o,"catchLoc"),s=n.call(o,"finallyLoc");if(l&&s){if(this.prev<o.catchLoc)return r(o.catchLoc,!0);if(this.prev<o.finallyLoc)return r(o.finallyLoc)}else if(l){if(this.prev<o.catchLoc)return r(o.catchLoc,!0)}else{if(!s)throw new Error("try statement without catch or finally");if(this.prev<o.finallyLoc)return r(o.finallyLoc)}}}},abrupt:function(e,t){for(var r=this.tryEntries.length-1;r>=0;--r){var i=this.tryEntries[r];if(i.tryLoc<=this.prev&&n.call(i,"finallyLoc")&&this.prev<i.finallyLoc){var o=i;break}}o&&("break"===e||"continue"===e)&&o.tryLoc<=t&&t<=o.finallyLoc&&(o=null);var a=o?o.completion:{};return a.type=e,a.arg=t,o?(this.method="next",this.next=o.finallyLoc,p):this.complete(a)},complete:function(e,t){if("throw"===e.type)throw e.arg;return"break"===e.type||"continue"===e.type?this.next=e.arg:"return"===e.type?(this.rval=this.arg=e.arg,this.method="return",this.next="end"):"normal"===e.type&&t&&(this.next=t),p},finish:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var n=this.tryEntries[t];if(n.finallyLoc===e)return this.complete(n.completion,n.afterLoc),O(n),p}},catch:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var n=this.tryEntries[t];if(n.tryLoc===e){var r=n.completion;if("throw"===r.type){var i=r.arg;O(n)}return i}}throw new Error("illegal catch attempt")},delegateYield:function(e,t,n){return this.delegate={iterator:L(e),resultName:t,nextLoc:n},"next"===this.method&&(this.arg=void 0),p}},e}function c(e,t,n,r,i,o,a){try{var l=e[o](a),s=l.value}catch(e){return void n(e)}l.done?t(s):Promise.resolve(s).then(r,i)}function d(e){return function(){var t=this,n=arguments;return new Promise((function(r,i){var o=e.apply(t,n);function a(e){c(o,r,i,a,l,"next",e)}function l(e){c(o,r,i,a,l,"throw",e)}a(void 0)}))}}var f=Shopware.Data,p=f.Criteria,h=f.EntityCollection,m=Shopware,v=m.State,w=m.Context,y=m.Service,g=Shopware.Utils.fileReader;t.default={template:'<sw-modal\n    class="sw-flow-upload-modal"\n    :closable="false"\n    :title="$tc(\'sw-flow-sharing.uploadModal.title\')"\n    @modal-close="onCancel"\n>\n    <div v-if="!ruleConflict">\n        <sw-file-input\n            v-model="jsonFile"\n            class="sw-flow-upload-modal__file-upload"\n            :allowed-mime-types="[\'application/json\']"\n            :key="isLoading"\n            :label="$tc(\'sw-flow-sharing.uploadModal.uploadFileLabel\')"\n            @change="onFileChange"\n        >\n            <temlpate #caption-label>\n                {{ $tc(\'sw-flow-sharing.uploadModal.labelUpload\') }}\n            </temlpate>\n        </sw-file-input>\n\n        <div\n            v-if="(rules.length || mailTemplates.length) && !showWarning"\n            class="sw-flow-upload-modal__content"\n        >\n            <p\n                class="sw-flow-upload-modal__description"\n                v-html="$tc(\'sw-flow-sharing.uploadModal.description\')"\n            ></p>\n            <div class="sw-flow-upload-modal__included">\n                <div class="sw-flow-upload-modal__data-included">\n                    <ul>\n                        <li\n                            v-for="(rule, index) in rules"\n                            :key="index"\n                        >\n                            <sw-checkbox-field\n                                class="sw-flow-upload-modal__rule-item"\n                                :value="!!rule.id"\n                                :label="$tc(\'sw-flow-sharing.uploadModal.ruleLabel\',0, { ruleName: rule.name })"\n                                @change="(checked) => handleSelectRule(rule, checked)"\n                            />\n                        </li>\n                    </ul>\n\n                    <ul>\n                        <li\n                            v-for="(mail, index) in mailTemplates"\n                            :key="index"\n                        >\n                            <sw-checkbox-field\n                                class="sw-flow-upload-modal__mail-template-item"\n                                :value="!!mail.id"\n                                :label="$tc(\'sw-flow-sharing.uploadModal.mailTemplateLabel\',0, { mail: mail.mailTemplateTypeName })"\n                                @change="(checked) => handleSelectMail(mail, checked)"\n                            />\n                        </li>\n                    </ul>\n                </div>\n            </div>\n        </div>\n\n        <sw-alert\n            v-if="showWarning"\n            class="sw-flow-upload-modal__warning"\n            variant="warning"\n        >\n            <p>\n                {{ $tc(\'sw-flow-sharing.uploadModal.warningAlert.description\') }}\n            </p>\n\n            <div v-if="requiredSWVersion">\n                <p>{{ $tc(\'sw-flow-sharing.uploadModal.warningAlert.shopwareVersionLabel\') }}</p>\n                <ul>\n                    <li>\n                        {{ requiredSWVersion }}\n                    </li>\n                </ul>\n            </div>\n\n            <div v-if="requiredPlugins || requiredApps">\n                <p>{{ $tc(\'sw-flow-sharing.uploadModal.warningAlert.extensionsLabel\') }}</p>\n                <ul v-if="requiredPlugins.length">\n                    <li\n                        v-for="(item, index) in requiredPlugins"\n                        :key="index"\n                    >\n                        {{ item }}\n                    </li>\n                </ul>\n\n                <ul v-if="requiredApps.length">\n                    <li\n                        v-for="(item, index) in requiredApps"\n                        :key="index"\n                    >\n                        {{ item }}\n                    </li>\n                </ul>\n            </div>\n        </sw-alert>\n    </div>\n    <div v-else>\n        <sw-alert\n            class="sw-flow-upload-modal__warning"\n            variant="warning"\n        >\n            <p>\n                <strong>{{ $tc(\'sw-flow-sharing.uploadModal.warningAlert.ruleConflictLabel\') }}</strong> <br>\n                {{ $tc(\'sw-flow-sharing.uploadModal.warningAlert.ruleConflictDescription\', \'flowFile\', { flowFile: jsonFile.name }) }}\n            </p>\n        </sw-alert>\n\n        <div\n            v-if="affectedRules.length"\n            class="sw-flow-upload-modal__affected-rules"\n        >\n            <p>\n                <strong>{{$tc(\'sw-flow-sharing.uploadModal.affectedRules\')}}:</strong>\n            </p>\n            <ul>\n                <li\n                    v-for="(rule, index) in affectedRules"\n                    :key="index"\n                >\n                    {{ rule.name }}\n                </li>\n            </ul>\n        </div>\n        <div class="sw-flow-upload-modal__resolve-rule-conflict">\n            <sw-radio-field\n                v-model="keepLocalRules"\n                block\n                class="sw-flow-upload-modal__resolve-rule-conflict-option"\n                identification=""\n                :options="resolveRulesConflictOptions"\n                :disabled="!acl.can(\'sales_channel.editor\')"\n            />\n        </div>\n    </div>\n    <template #modal-footer>\n        <sw-button\n            class="sw-flow-upload-modal__cancel-button"\n            size="small"\n            @click="onCancel"\n        >\n            {{ $tc(\'global.default.cancel\') }}\n        </sw-button>\n\n        <sw-button\n            class="sw-flow-upload-modal__upload-button"\n            variant="primary"\n            size="small"\n            :disabled="!acl.can(\'flow.creator\') || disableUpload"\n            @click="onUpload"\n        >\n            {{ $tc(\'sw-flow-sharing.uploadButton\') }}\n        </sw-button>\n    </template>\n</sw-modal>\n',inject:["acl","repositoryFactory","flowSharingService","ruleConditionsConfigApiService"],props:{isLoading:{type:Boolean,required:!1,default:!1}},data:function(){return{disableUpload:!1,report:{},jsonFile:null,flowData:null,rules:[],mailTemplates:[],notSelectedRuleIds:[],notSelectedMailIds:[],requiredSWVersion:null,requiredPlugins:[],requiredApps:[],mailTemplateTypes:null,ruleConflict:!1,affectedRules:[],keepLocalRules:!0,hasError:!1}},computed:{ruleRepository:function(){return this.repositoryFactory.create("rule")},ruleConditionRepository:function(){return this.repositoryFactory.create("rule_condition")},mailTemplateRepository:function(){return this.repositoryFactory.create("mail_template")},mailTemplateTypeRepository:function(){return this.repositoryFactory.create("mail_template_type")},showWarning:function(){return this.requiredSWVersion||this.requiredPlugins.length||this.requiredApps.length},resolveRulesConflictOptions:function(){return[{value:!0,name:this.$tc("sw-flow-sharing.uploadModal.keepLocalRulesLabel"),description:this.$tc("sw-flow-sharing.uploadModal.keepLocalRulesDescription")},{value:!1,name:this.$tc("sw-flow-sharing.uploadModal.overrideLocalRulesLabel"),description:this.$tc("sw-flow-sharing.uploadModal.overrideLocalRulesDescription","flowFile",{flowFile:this.jsonFile.name})}]}},watch:{jsonFile:function(e){this.resetData(),this.disableUpload=!e},flowData:function(e){e.requirements&&this.validateRequirements(e.requirements)},report:function(e){e.shopwareVersion&&(this.requiredSWVersion=e.shopwareVersion),e.pluginInstalled&&(this.requiredPlugins=e.pluginInstalled),e.appInstalled&&(this.requiredApps=e.appInstalled)}},created:function(){this.createdComponent()},methods:{createdComponent:function(){this.ruleConditionsConfigApiService.load(),this.resetData(),this.jsonFile||(this.disableUpload=!0)},resetData:function(){this.rules=[],this.mailTemplates=[],this.notSelectedRuleIds=[],this.notSelectedMailIds=[],this.report={},this.requiredSWVersion=null,this.requiredPlugins=[],this.requiredApps=[],this.ruleConflict=!1,this.affectedRules=[],this.keepLocalRules=!0,v.dispatch("swFlowSharingState/resetFlowSharingState")},onCancel:function(){this.$emit("modal-close")},getMailTemplateCollection:function(){return this.mailTemplateTypeRepository.search(new p(1,25))},saveEmailTemplates:function(){var e=this;return d(u().mark((function t(){var n;return u().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return n=[],e.mailTemplates.map((function(t){e.notSelectedMailIds.includes(t.id)||(t=e.createMailTemplate(t))&&n.push(t)})),t.next=4,e.mailTemplateRepository.saveAll(n);case 4:case"end":return t.stop()}}),t)})))()},saveRules:function(){var e=this;return d(u().mark((function t(){var n;return u().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return n=[],e.rules.map((function(t){e.notSelectedRuleIds.includes(t.id)||n.push(e.createRule(t))})),t.next=4,e.ruleRepository.saveAll(n);case 4:case"end":return t.stop()}}),t)})))()},onUpload:function(){var e=this;return d(u().mark((function t(){var n,r,i;return u().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:if(!(e.affectedRules.length>0)||e.ruleConflict){t.next=3;break}return e.ruleConflict=!0,t.abrupt("return");case 3:if(!(e.hasError||e.flowData.length<=0)){t.next=6;break}return e.$emit("modal-upload-finished",!1),t.abrupt("return");case 6:return t.next=8,e.getMailTemplateCollection();case 8:return e.mailTemplateTypes=t.sent,n=Object.assign({},e.flowData.dataIncluded),e.ruleConflict&&!e.keepLocalRules&&(r=e.rules).push.apply(r,l(Object.values(e.affectedRules))),t.next=13,Promise.all([e.saveEmailTemplates(),e.saveRules()]);case 13:i=Object.assign({},e.flowData.referenceIncluded),v.commit("swFlowSharingState/setFlow",e.flowData.flow),v.commit("swFlowSharingState/setDataIncluded",n),v.commit("swFlowSharingState/setReferenceIncluded",i),e.$emit("modal-upload-finished",!0);case 18:case"end":return t.stop()}}),t)})))()},onFileChange:function(){var e=this;return this.jsonFile?g.readAsText(this.jsonFile).then((function(t){e.flowData=JSON.parse(t),e.flowData&&e.generateData(e.flowData)})):null},isMatchCondition:function(e,t){var n=!("orContainer"!==e.type&&"andContainer"!==e.type||"[]"!==JSON.stringify(e.value)&&"{}"!==JSON.stringify(e.value));return e.id==t.id&&e.type==t.type&&(n||JSON.stringify(e.value)===JSON.stringify(t.value))},hasRuleConditionsConflict:function(e,t){var n=this;if(e.length!==t.length)return!0;var r=e.filter((function(e){return!t.some((function(t){return n.isMatchCondition(e,t)}))})),i=t.filter((function(t){return!e.some((function(e){return n.isMatchCondition(t,e)}))}));return[].concat(l(r),l(i)).length>0},generateRuleData:function(e){var t=this;return d(u().mark((function n(){var r,i,a,s;return u().wrap((function(n){for(;;)switch(n.prev=n.next){case 0:return r=e.map((function(e){return e.id})),(i=new p(1,null)).addFilter(p.equalsAny("id",r)),i.addAssociation("conditions"),n.next=6,t.ruleRepository.search(i);case 6:a=n.sent,s=Object.values(a).map((function(e){return e.id})),e.map((function(e){if(s.includes(e.id)){for(var n=null,r=0;r<a.length;r++){(a[r].id=e.id)&&(n=a[r]);break}var i=l(e.conditions),u=l(n.conditions);t.hasRuleConditionsConflict(i,u)&&t.affectedRules.push(o(o({},e),{},{_isNew:!1}))}else t.rules.push(e)}));case 9:case"end":return n.stop()}}),n)})))()},generateMailTemplateData:function(e){var t=this;return d(u().mark((function n(){var r,i,o,a;return u().wrap((function(n){for(;;)switch(n.prev=n.next){case 0:return r=[],i=[],e.forEach((function(e){var t=e.find((function(e){return e.locale===v.get("session").currentLocale}));t&&(i.push(t.id),r.push(t))})),(o=new p(1,null)).addFilter(p.equalsAny("id",i)),n.next=7,t.mailTemplateRepository.searchIds(o);case 7:a=n.sent,r.forEach((function(e){a.data.includes(e.id)||t.mailTemplates.push(e)}));case 9:case"end":return n.stop()}}),n)})))()},generateData:function(e){var t=this;return d(u().mark((function n(){var r;return u().wrap((function(n){for(;;)switch(n.prev=n.next){case 0:if(r=e.dataIncluded){n.next=3;break}return n.abrupt("return");case 3:r.rule&&t.generateRuleData(Object.values(r.rule)),r.mail_template&&t.generateMailTemplateData(Object.values(r.mail_template));case 5:case"end":return n.stop()}}),n)})))()},validateRequirements:function(e){var t=this;this.flowSharingService.checkRequirements({requirements:e}).then((function(e){t.report=e,t.disableUpload=!!Object.keys(e).length})).catch((function(){t.hasError=!0}))},handleSelectRule:function(e,t){this.notSelectedRuleIds=t?this.notSelectedRuleIds.filter((function(t){return t!==e.id})):[].concat(l(this.notSelectedRuleIds),[e.id])},handleSelectMail:function(e,t){this.notSelectedMailIds=t?this.notSelectedMailIds.filter((function(t){return t!==e.id})):[].concat(l(this.notSelectedMailIds),[e.id])},createRule:function(e){var t=this,n=this.ruleRepository.create();Object.keys(e).forEach((function(t){n[t]=e[t]}));var r=new h(this.ruleConditionRepository.route,this.ruleConditionRepository.entityName,w.api);return y("flowBuilderService").rearrangeArrayObjects(e.conditions).forEach((function(e){var n=t.ruleConditionRepository.create();Object.keys(e).forEach((function(t){n[t]=e[t]})),r.add(n)})),n.conditions=r,n},createMailTemplate:function(e){new p(1,25).addFilter(p.equals("technicalName",e.technicalName));var t=this.mailTemplateRepository.create(),n=this.mailTemplateTypes.find((function(t){return t.technicalName===e.technicalName}));return n?(t.id=e.id,t.mailTemplateTypeId=null==n?void 0:n.id,t.senderName=e.senderName,t.subject=e.subject,t.description=e.description,t.contentHtml=e.contentHtml,t.contentPlain=e.contentPlain,t.customFields=e.customFields,t):null}}}},P8hj:function(e,t,n){"use strict";function r(e,t){for(var n=[],r={},i=0;i<t.length;i++){var o=t[i],a=o[0],l={id:e+":"+i,css:o[1],media:o[2],sourceMap:o[3]};r[a]?r[a].parts.push(l):n.push(r[a]={id:a,parts:[l]})}return n}n.r(t),n.d(t,"default",(function(){return h}));var i="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!i)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var o={},a=i&&(document.head||document.getElementsByTagName("head")[0]),l=null,s=0,u=!1,c=function(){},d=null,f="data-vue-ssr-id",p="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function h(e,t,n,i){u=n,d=i||{};var a=r(e,t);return m(a),function(t){for(var n=[],i=0;i<a.length;i++){var l=a[i];(s=o[l.id]).refs--,n.push(s)}t?m(a=r(e,t)):a=[];for(i=0;i<n.length;i++){var s;if(0===(s=n[i]).refs){for(var u=0;u<s.parts.length;u++)s.parts[u]();delete o[s.id]}}}}function m(e){for(var t=0;t<e.length;t++){var n=e[t],r=o[n.id];if(r){r.refs++;for(var i=0;i<r.parts.length;i++)r.parts[i](n.parts[i]);for(;i<n.parts.length;i++)r.parts.push(w(n.parts[i]));r.parts.length>n.parts.length&&(r.parts.length=n.parts.length)}else{var a=[];for(i=0;i<n.parts.length;i++)a.push(w(n.parts[i]));o[n.id]={id:n.id,refs:1,parts:a}}}}function v(){var e=document.createElement("style");return e.type="text/css",a.appendChild(e),e}function w(e){var t,n,r=document.querySelector("style["+f+'~="'+e.id+'"]');if(r){if(u)return c;r.parentNode.removeChild(r)}if(p){var i=s++;r=l||(l=v()),t=b.bind(null,r,i,!1),n=b.bind(null,r,i,!0)}else r=v(),t=S.bind(null,r),n=function(){r.parentNode.removeChild(r)};return t(e),function(r){if(r){if(r.css===e.css&&r.media===e.media&&r.sourceMap===e.sourceMap)return;t(e=r)}else n()}}var y,g=(y=[],function(e,t){return y[e]=t,y.filter(Boolean).join("\n")});function b(e,t,n,r){var i=n?"":r.css;if(e.styleSheet)e.styleSheet.cssText=g(t,i);else{var o=document.createTextNode(i),a=e.childNodes;a[t]&&e.removeChild(a[t]),a.length?e.insertBefore(o,a[t]):e.appendChild(o)}}function S(e,t){var n=t.css,r=t.media,i=t.sourceMap;if(r&&e.setAttribute("media",r),d.ssrId&&e.setAttribute(f,t.id),i&&(n+="\n/*# sourceURL="+i.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(i))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}},iH3F:function(e,t,n){}}]);
//# sourceMappingURL=1.js.map