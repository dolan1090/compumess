/*! For license information please see 7.js.LICENSE.txt */
(this["webpackJsonpPluginemployee-management"]=this["webpackJsonpPluginemployee-management"]||[]).push([[7],{"4tqO":function(e,t,r){"use strict";r.r(t);function n(e){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function o(){o=function(){return e};var e={},t=Object.prototype,r=t.hasOwnProperty,i=Object.defineProperty||function(e,t,r){e[t]=r.value},a="function"==typeof Symbol?Symbol:{},c=a.iterator||"@@iterator",s=a.asyncIterator||"@@asyncIterator",l=a.toStringTag||"@@toStringTag";function u(e,t,r){return Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}),e[t]}try{u({},"")}catch(e){u=function(e,t,r){return e[t]=r}}function p(e,t,r,n){var o=t&&t.prototype instanceof d?t:d,a=Object.create(o.prototype),c=new I(n||[]);return i(a,"_invoke",{value:x(e,r,c)}),a}function m(e,t,r){try{return{type:"normal",arg:e.call(t,r)}}catch(e){return{type:"throw",arg:e}}}e.wrap=p;var f={};function d(){}function h(){}function y(){}var v={};u(v,c,(function(){return this}));var w=Object.getPrototypeOf,g=w&&w(w(N([])));g&&g!==t&&r.call(g,c)&&(v=g);var b=y.prototype=d.prototype=Object.create(v);function _(e){["next","throw","return"].forEach((function(t){u(e,t,(function(e){return this._invoke(t,e)}))}))}function E(e,t){function o(i,a,c,s){var l=m(e[i],e,a);if("throw"!==l.type){var u=l.arg,p=u.value;return p&&"object"==n(p)&&r.call(p,"__await")?t.resolve(p.__await).then((function(e){o("next",e,c,s)}),(function(e){o("throw",e,c,s)})):t.resolve(p).then((function(e){u.value=e,c(u)}),(function(e){return o("throw",e,c,s)}))}s(l.arg)}var a;i(this,"_invoke",{value:function(e,r){function n(){return new t((function(t,n){o(e,r,t,n)}))}return a=a?a.then(n,n):n()}})}function x(e,t,r){var n="suspendedStart";return function(o,i){if("executing"===n)throw new Error("Generator is already running");if("completed"===n){if("throw"===o)throw i;return $()}for(r.method=o,r.arg=i;;){var a=r.delegate;if(a){var c=L(a,r);if(c){if(c===f)continue;return c}}if("next"===r.method)r.sent=r._sent=r.arg;else if("throw"===r.method){if("suspendedStart"===n)throw n="completed",r.arg;r.dispatchException(r.arg)}else"return"===r.method&&r.abrupt("return",r.arg);n="executing";var s=m(e,t,r);if("normal"===s.type){if(n=r.done?"completed":"suspendedYield",s.arg===f)continue;return{value:s.arg,done:r.done}}"throw"===s.type&&(n="completed",r.method="throw",r.arg=s.arg)}}}function L(e,t){var r=t.method,n=e.iterator[r];if(void 0===n)return t.delegate=null,"throw"===r&&e.iterator.return&&(t.method="return",t.arg=void 0,L(e,t),"throw"===t.method)||"return"!==r&&(t.method="throw",t.arg=new TypeError("The iterator does not provide a '"+r+"' method")),f;var o=m(n,e.iterator,t.arg);if("throw"===o.type)return t.method="throw",t.arg=o.arg,t.delegate=null,f;var i=o.arg;return i?i.done?(t[e.resultName]=i.value,t.next=e.nextLoc,"return"!==t.method&&(t.method="next",t.arg=void 0),t.delegate=null,f):i:(t.method="throw",t.arg=new TypeError("iterator result is not an object"),t.delegate=null,f)}function k(e){var t={tryLoc:e[0]};1 in e&&(t.catchLoc=e[1]),2 in e&&(t.finallyLoc=e[2],t.afterLoc=e[3]),this.tryEntries.push(t)}function S(e){var t=e.completion||{};t.type="normal",delete t.arg,e.completion=t}function I(e){this.tryEntries=[{tryLoc:"root"}],e.forEach(k,this),this.reset(!0)}function N(e){if(e){var t=e[c];if(t)return t.call(e);if("function"==typeof e.next)return e;if(!isNaN(e.length)){var n=-1,o=function t(){for(;++n<e.length;)if(r.call(e,n))return t.value=e[n],t.done=!1,t;return t.value=void 0,t.done=!0,t};return o.next=o}}return{next:$}}function $(){return{value:void 0,done:!0}}return h.prototype=y,i(b,"constructor",{value:y,configurable:!0}),i(y,"constructor",{value:h,configurable:!0}),h.displayName=u(y,l,"GeneratorFunction"),e.isGeneratorFunction=function(e){var t="function"==typeof e&&e.constructor;return!!t&&(t===h||"GeneratorFunction"===(t.displayName||t.name))},e.mark=function(e){return Object.setPrototypeOf?Object.setPrototypeOf(e,y):(e.__proto__=y,u(e,l,"GeneratorFunction")),e.prototype=Object.create(b),e},e.awrap=function(e){return{__await:e}},_(E.prototype),u(E.prototype,s,(function(){return this})),e.AsyncIterator=E,e.async=function(t,r,n,o,i){void 0===i&&(i=Promise);var a=new E(p(t,r,n,o),i);return e.isGeneratorFunction(r)?a:a.next().then((function(e){return e.done?e.value:a.next()}))},_(b),u(b,l,"Generator"),u(b,c,(function(){return this})),u(b,"toString",(function(){return"[object Generator]"})),e.keys=function(e){var t=Object(e),r=[];for(var n in t)r.push(n);return r.reverse(),function e(){for(;r.length;){var n=r.pop();if(n in t)return e.value=n,e.done=!1,e}return e.done=!0,e}},e.values=N,I.prototype={constructor:I,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=void 0,this.done=!1,this.delegate=null,this.method="next",this.arg=void 0,this.tryEntries.forEach(S),!e)for(var t in this)"t"===t.charAt(0)&&r.call(this,t)&&!isNaN(+t.slice(1))&&(this[t]=void 0)},stop:function(){this.done=!0;var e=this.tryEntries[0].completion;if("throw"===e.type)throw e.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var t=this;function n(r,n){return a.type="throw",a.arg=e,t.next=r,n&&(t.method="next",t.arg=void 0),!!n}for(var o=this.tryEntries.length-1;o>=0;--o){var i=this.tryEntries[o],a=i.completion;if("root"===i.tryLoc)return n("end");if(i.tryLoc<=this.prev){var c=r.call(i,"catchLoc"),s=r.call(i,"finallyLoc");if(c&&s){if(this.prev<i.catchLoc)return n(i.catchLoc,!0);if(this.prev<i.finallyLoc)return n(i.finallyLoc)}else if(c){if(this.prev<i.catchLoc)return n(i.catchLoc,!0)}else{if(!s)throw new Error("try statement without catch or finally");if(this.prev<i.finallyLoc)return n(i.finallyLoc)}}}},abrupt:function(e,t){for(var n=this.tryEntries.length-1;n>=0;--n){var o=this.tryEntries[n];if(o.tryLoc<=this.prev&&r.call(o,"finallyLoc")&&this.prev<o.finallyLoc){var i=o;break}}i&&("break"===e||"continue"===e)&&i.tryLoc<=t&&t<=i.finallyLoc&&(i=null);var a=i?i.completion:{};return a.type=e,a.arg=t,i?(this.method="next",this.next=i.finallyLoc,f):this.complete(a)},complete:function(e,t){if("throw"===e.type)throw e.arg;return"break"===e.type||"continue"===e.type?this.next=e.arg:"return"===e.type?(this.rval=this.arg=e.arg,this.method="return",this.next="end"):"normal"===e.type&&t&&(this.next=t),f},finish:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var r=this.tryEntries[t];if(r.finallyLoc===e)return this.complete(r.completion,r.afterLoc),S(r),f}},catch:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var r=this.tryEntries[t];if(r.tryLoc===e){var n=r.completion;if("throw"===n.type){var o=n.arg;S(r)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(e,t,r){return this.delegate={iterator:N(e),resultName:t,nextLoc:r},"next"===this.method&&(this.arg=void 0),f}},e}function i(e,t,r,n,o,i,a){try{var c=e[i](a),s=c.value}catch(e){return void r(e)}c.done?t(s):Promise.resolve(s).then(n,o)}function a(e){return function(){var t=this,r=arguments;return new Promise((function(n,o){var a=e.apply(t,r);function c(e){i(a,n,o,c,s,"next",e)}function s(e){i(a,n,o,c,s,"throw",e)}c(void 0)}))}}var c=Shopware,s=c.Component,l=c.Mixin,u=Shopware.Data.Criteria;t.default=s.wrapComponentConfig({template:'{% block sw_customer_employee_create %}\n<sw-page class="sw-customer-employee-create">\n    {% block sw_customer_employee_create_header %}\n    <template #smart-bar-header>\n        <h2>{{ $tc(\'sw-customer.employee.titleCreate\') }}</h2>\n    </template>\n    {% endblock %}\n\n    {% block sw_customer_employee_create_actions %}\n    <template #smart-bar-actions>\n        <sw-button @click="onCancel">\n            {{ $tc(\'global.default.cancel\') }}\n        </sw-button>\n\n        <sw-button-process\n            class="sw-customer-employee-create__save-button"\n            variant="primary"\n            :is-loading="isLoading"\n            :process-success="isSaveSuccessful"\n            @click.prevent="onSave"\n        >\n            {{ $tc(\'sw-customer.employee.buttonCreate\') }}\n        </sw-button-process>\n    </template>\n    {% endblock %}\n\n    {% block sw_customer_employee_create_content %}\n    <template #content>\n        <sw-card-view>\n            {% block sw_customer_employee_create_content_card %}\n            <sw-card\n                v-if="entity"\n                class="sw-customer-employee-create__basic-information-card"\n                positionIdentifier="sw-customer-employee-create"\n                :title="$tc(\'sw-customer.employee.titleEmployeeData\')"\n                :subtitle="$tc(\'sw-customer.employee-management.warningEdit\')"\n                :isLoading="isLoading"\n            >\n                <sw-container\n                    columns="auto"\n                    gap="0 1rem"\n                >\n                    {% block sw_customer_employee_create_content_card_first_name %}\n                    <sw-field\n                        v-model="entity.firstName"\n                        class="sw-customer-employee-create__basic-information-card-first-name-field"\n                        type="text"\n                        required\n                        validation="required"\n                        :error="errorFirstName"\n                        :label="$tc(\'sw-customer.employee.labelFirstName\')"\n                        :placeholder="$tc(\'sw-customer.employee.placeholderFirstName\')"\n                    ></sw-field>\n                    {% endblock %}\n\n                    {% block sw_customer_employee_create_content_card_last_name %}\n                    <sw-field\n                        v-model="entity.lastName"\n                        class="sw-customer-employee-create__basic-information-card-last-name-field"\n                        type="text"\n                        required\n                        validation="required"\n                        :error="errorLastName"\n                        :label="$tc(\'sw-customer.employee.labelLastName\')"\n                        :placeholder="$tc(\'sw-customer.employee.placeholderLastName\')"\n                    ></sw-field>\n                    {% endblock %}\n\n                    {% block sw_customer_employee_create_content_card_email %}\n                    <sw-field\n                        v-model="entity.email"\n                        class="sw-customer-employee-create__basic-information-card-email-field"\n                        type="email"\n                        required\n                        validation="required"\n                        :error="errorEmail"\n                        :label="$tc(\'sw-customer.employee.labelEmail\')"\n                        :placeholder="$tc(\'sw-customer.employee.placeholderEmail\')"\n                    ></sw-field>\n                    {% endblock %}\n\n                    {% block sw_customer_employee_create_content_card_role %}\n                    <sw-entity-single-select\n                        v-model="entity.roleId"\n                        class="sw-customer-employee-create__basic-information-card-role-field"\n                        entity="b2b_components_role"\n                        :criteria="roleCriteria"\n                        label-property="name"\n                        :label="$tc(\'sw-customer.employee.labelRole\')"\n                        :placeholder="$tc(\'sw-customer.employee.labelRole\')"\n                        :resetOption="$tc(\'sw-customer.employee.placeholderRole\')"\n                    >\n                        <template #selection-label-property="{ item }">\n                            <div>\n                                {{ item.name }}<span v-if="defaultRoleId && item.id === defaultRoleId"> {{ $tc(\'sw-customer.employee.labelRoleDefault\') }}</span>\n                            </div>\n                        </template>\n\n                        <template #result-label-property="{ item }">\n                            <div>\n                                {{ item.name }}<span v-if="defaultRoleId && item.id === defaultRoleId"> {{ $tc(\'sw-customer.employee.labelRoleDefault\') }}</span>\n                            </div>\n                        </template>\n                    </sw-entity-single-select>\n                    {% endblock %}\n                </sw-container>\n            </sw-card>\n            {% endblock %}\n        </sw-card-view>\n    </template>\n    {% endblock %}\n</sw-page>\n{% endblock %}\n',inject:["acl","repositoryFactory","employeeApiService"],mixins:[l.getByName("notification")],shortcuts:{"SYSTEMKEY+S":"onSave",ESCAPE:"onCancel"},data:function(){return{entity:null,isLoading:!0,isSaveSuccessful:!1,defaultRoleId:null}},computed:{customerId:function(){return this.$route.params.id},customerRepository:function(){return this.repositoryFactory.create("customer")},employeeRepository:function(){return this.repositoryFactory.create("b2b_employee")},businessPartnerRepository:function(){return this.repositoryFactory.create("b2b_business_partner")},roleCriteria:function(){return(new u).addFilter(u.equals("businessPartnerCustomerId",this.customerId))},businessPartnerCriteria:function(){return(new u).addFilter(u.equals("customerId",this.customerId))},errorFirstName:function(){return this.$store.getters["error/getApiError"](this.entity,"firstName")},errorLastName:function(){return this.$store.getters["error/getApiError"](this.entity,"lastName")},errorEmail:function(){return this.$store.getters["error/getApiError"](this.entity,"email")}},created:function(){this.createdComponent()},methods:{createdComponent:function(){var e=this;return a(o().mark((function t(){return o().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,e.initDefaultRole();case 2:e.initEntity();case 3:case"end":return t.stop()}}),t)})))()},onSave:function(){var e=this;return a(o().mark((function t(){return o().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,e.save();case 2:case"end":return t.stop()}}),t)})))()},save:function(){var e=this;return a(o().mark((function t(){var r,n,i,a,c,s,l;return o().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return e.isLoading=!0,e.isSaveSuccessful=!1,t.prev=2,t.next=5,e.employeeApiService.createEmployee(e.entity);case 5:return r=t.sent,e.isSaveSuccessful=!0,e.entity.id=r.data[0],t.next=10,e.sendInvitation();case 10:e.navigateToDetailPage(),t.next=19;break;case 13:t.prev=13,t.t0=t.catch(2),s=null===(n=t.t0.response)||void 0===n||null===(i=n.data.errors[0])||void 0===i?void 0:i.detail,l=null!=s?s:e.$tc("global.notification.notificationSaveErrorMessageRequiredFieldsInvalid"),"B2B__EMPLOYEE_MAIL_NOT_UNIQUE"===(null===(a=t.t0.response)||void 0===a||null===(c=a.data.errors[0])||void 0===c?void 0:c.code)&&(l=e.$tc("sw-customer.employee.notification.existingEmployeeEmail")),e.createNotificationError({message:l});case 19:return t.prev=19,e.isLoading=!1,t.finish(19);case 22:case"end":return t.stop()}}),t,null,[[2,13,19,22]])})))()},sendInvitation:function(){var e=this;return a(o().mark((function t(){return o().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return e.isLoading=!0,t.prev=1,t.next=4,e.employeeApiService.invite(e.entity.id);case 4:e.createNotificationSuccess({message:e.$tc("sw-customer.employee.notification.inviteSuccessMessage")}),t.next=10;break;case 7:t.prev=7,t.t0=t.catch(1),e.createNotificationError({message:e.$tc("sw-customer.employee.notification.inviteFailedMessage")});case 10:return t.prev=10,e.isLoading=!1,t.finish(10);case 13:case"end":return t.stop()}}),t,null,[[1,7,10,13]])})))()},onCancel:function(){this.navigateToCompany()},initEntity:function(){var e=this.employeeRepository.create();e.businessPartnerCustomerId=this.customerId,e.roleId=this.defaultRoleId,this.entity=e,this.isLoading=!1},navigateToDetailPage:function(){this.$router.push({name:"sw.customer.company.employee.detail",params:{employeeId:this.entity.id}})},navigateToCompany:function(){this.$router.push({name:"sw.customer.detail.company",query:{edit:!1}})},initDefaultRole:function(){var e=this;return a(o().mark((function t(){var r;return o().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,e.businessPartnerRepository.search(e.businessPartnerCriteria);case 2:if((r=t.sent.first())&&r.defaultRoleId){t.next=5;break}return t.abrupt("return");case 5:e.defaultRoleId=r.defaultRoleId;case 6:case"end":return t.stop()}}),t)})))()}}})}}]);
//# sourceMappingURL=7.js.map