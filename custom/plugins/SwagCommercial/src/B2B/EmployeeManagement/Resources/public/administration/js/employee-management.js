!function(e){function t(t){for(var r,n,i=t[0],a=t[1],c=0,u=[];c<i.length;c++)n=i[c],Object.prototype.hasOwnProperty.call(o,n)&&o[n]&&u.push(o[n][0]),o[n]=0;for(r in a)Object.prototype.hasOwnProperty.call(a,r)&&(e[r]=a[r]);for(l&&l(t);u.length;)u.shift()()}var r={},n={"employee-management":0},o={"employee-management":0};function i(t){if(r[t])return r[t].exports;var n=r[t]={i:t,l:!1,exports:{}};return e[t].call(n.exports,n,n.exports,i),n.l=!0,n.exports}i.e=function(e){var t=[];n[e]?t.push(n[e]):0!==n[e]&&{0:1,1:1,2:1,3:1,4:1,5:1}[e]&&t.push(n[e]=new Promise((function(t,r){for(var o="static/css/"+({}[e]||e)+".css",a=i.p+o,c=document.getElementsByTagName("link"),u=0;u<c.length;u++){var l=(p=c[u]).getAttribute("data-href")||p.getAttribute("href");if("stylesheet"===p.rel&&(l===o||l===a))return t()}var s=document.getElementsByTagName("style");for(u=0;u<s.length;u++){var p;if((l=(p=s[u]).getAttribute("data-href"))===o||l===a)return t()}var f=document.createElement("link");f.rel="stylesheet",f.type="text/css";f.onerror=f.onload=function(o){if(f.onerror=f.onload=null,"load"===o.type)t();else{var i=o&&("load"===o.type?"missing":o.type),c=o&&o.target&&o.target.href||a,u=new Error("Loading CSS chunk "+e+" failed.\n("+c+")");u.code="CSS_CHUNK_LOAD_FAILED",u.type=i,u.request=c,delete n[e],f.parentNode.removeChild(f),r(u)}},f.href=a,document.head.appendChild(f)})).then((function(){n[e]=0})));var r=o[e];if(0!==r)if(r)t.push(r[2]);else{var a=new Promise((function(t,n){r=o[e]=[t,n]}));t.push(r[2]=a);var c,u=document.createElement("script");u.charset="utf-8",u.timeout=120,i.nc&&u.setAttribute("nonce",i.nc),u.src=function(e){return i.p+"static/js/"+({}[e]||e)+".js"}(e);var l=new Error;c=function(t){u.onerror=u.onload=null,clearTimeout(s);var r=o[e];if(0!==r){if(r){var n=t&&("load"===t.type?"missing":t.type),i=t&&t.target&&t.target.src;l.message="Loading chunk "+e+" failed.\n("+n+": "+i+")",l.name="ChunkLoadError",l.type=n,l.request=i,r[1](l)}o[e]=void 0}};var s=setTimeout((function(){c({type:"timeout",target:u})}),12e4);u.onerror=u.onload=c,document.head.appendChild(u)}return Promise.all(t)},i.m=e,i.c=r,i.d=function(e,t,r){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)i.d(r,n,function(t){return e[t]}.bind(null,n));return r},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p=(window.__sw__.assetPath + '/bundles/employeemanagement/'),i.oe=function(e){throw console.error(e),e};var a=this["webpackJsonpPluginemployee-management"]=this["webpackJsonpPluginemployee-management"]||[],c=a.push.bind(a);a.push=t,a=a.slice();for(var u=0;u<a.length;u++)t(a[u]);var l=c;i(i.s="6HCo")}({"/rlj":function(e,t,r){var n=Shopware,o=n.Component;n.License.get("EMPLOYEE_MANAGEMENT-3702619")&&(o.override("sw-order-general-info",(function(){return r.e(9).then(r.bind(null,"Knrm"))})),o.override("sw-order-detail",(function(){return r.e(10).then(r.bind(null,"vty8"))})))},"6HCo":function(e,t,r){"use strict";r.r(t);r("IRGx"),r("D4fb");var n={name:"sw.customer.detail.company",path:"/sw/customer/detail/:id/company",component:"sw-customer-detail-company",meta:{parentPath:"sw.customer.index",privilege:"b2b_employee_management.viewer"}};function o(e,t){"sw.customer.detail"!==t.name||t.children.some((function(e){return e.name===n.name}))||(t.children.push(n),e(t))}var i,a=[{name:"sw.customer.company.employee.create",path:"/sw/customer/detail/:id/company/employee",component:"sw-customer-employee-create",meta:{parentPath:"sw.customer.detail.company",privilege:"b2b_employee_management.creator"}},{name:"sw.customer.company.employee.detail",path:"/sw/customer/detail/:id/company/employee/:employeeId",component:"sw-customer-employee-detail",meta:{parentPath:"sw.customer.detail.company",privilege:"b2b_employee_management.editor"}}],c=[{name:"sw.customer.company.role.create",path:"/sw/customer/detail/:id/company/role",component:"sw-customer-role-create",meta:{parentPath:"sw.customer.detail.company",privilege:"b2b_employee_management.creator"}},{name:"sw.customer.company.role.detail",path:"/sw/customer/detail/:id/company/role/:roleId",component:"sw-customer-role-detail",meta:{parentPath:"sw.customer.detail.company",privilege:"b2b_employee_management.editor"}}];function u(e){return(u="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function l(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,b(n.key),n)}}function s(e,t){return(s=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,t){return e.__proto__=t,e})(e,t)}function p(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var r,n=d(e);if(t){var o=d(this).constructor;r=Reflect.construct(n,arguments,o)}else r=n.apply(this,arguments);return f(this,r)}}function f(e,t){if(t&&("object"===u(t)||"function"==typeof t))return t;if(void 0!==t)throw new TypeError("Derived constructors may only return object or undefined");return m(e)}function m(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function d(e){return(d=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function y(e,t,r){return(t=b(t))in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function b(e){var t=function(e,t){if("object"!==u(e)||null===e)return e;var r=e[Symbol.toPrimitive];if(void 0!==r){var n=r.call(e,t||"default");if("object"!==u(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===u(t)?t:String(t)}Shopware.Module.register("sw-customer-company",{routeMiddleware:o}),(i=Shopware.Module.getModuleByEntityName("customer"))&&[].concat(a,c).forEach((function(e){i.routes.set(e.name,e)}));var h=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&s(e,t)}(i,e);var t,r,n,o=p(i);function i(e,t){var r;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,i),y(m(r=o.call(this,e,t)),"name","employeeApiService"),y(m(r),"EntityName","b2b_employee"),r}return t=i,(r=[{key:"createEmployee",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=this.getBasicHeaders(t);return this.httpClient.post("/_action/create-employee",e,{headers:r})}},{key:"updateEmployee",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=this.getBasicHeaders(t);return this.httpClient.patch("/_action/update-employee",e,{headers:r})}},{key:"invite",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=this.getBasicHeaders(t),n={id:e};return this.httpClient.post("/_action/invite-employee",n,{headers:r})}}])&&l(t.prototype,r),n&&l(t,n),Object.defineProperty(t,"prototype",{writable:!1}),i}(Shopware.Classes.ApiService);function v(e){return(v="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function g(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,j(n.key),n)}}function w(e,t){return(w=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,t){return e.__proto__=t,e})(e,t)}function _(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var r,n=P(e);if(t){var o=P(this).constructor;r=Reflect.construct(n,arguments,o)}else r=n.apply(this,arguments);return S(this,r)}}function S(e,t){if(t&&("object"===v(t)||"function"==typeof t))return t;if(void 0!==t)throw new TypeError("Derived constructors may only return object or undefined");return O(e)}function O(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function P(e){return(P=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function j(e){var t=function(e,t){if("object"!==v(e)||null===e)return e;var r=e[Symbol.toPrimitive];if(void 0!==r){var n=r.call(e,t||"default");if("object"!==v(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===v(t)?t:String(t)}var E=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&w(e,t)}(i,e);var t,r,n,o=_(i);function i(e,t){var r,n,a,c;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,i),r=o.call(this,e,t),n=O(r),c="rolePermissionApiService",(a=j(a="name"))in n?Object.defineProperty(n,a,{value:c,enumerable:!0,configurable:!0,writable:!0}):n[a]=c,r}return t=i,(r=[{key:"getAllPermissions",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},t=this.getBasicHeaders(e);return this.httpClient.get("/_action/permission",{headers:t})}}])&&g(t.prototype,r),n&&g(t,n),Object.defineProperty(t,"prototype",{writable:!1}),i}(Shopware.Classes.ApiService),C=Shopware.Application.getContainer("init");Shopware.Service().register("employeeApiService",(function(){return new h(C.httpClient,Shopware.Service("loginService"))})),Shopware.Service().register("rolePermissionApiService",(function(){return new E(C.httpClient,Shopware.Service("loginService"))}));r("/rlj"),r("Vpbe")},D4fb:function(e,t,r){var n=Shopware,o=n.Component;n.License.get("EMPLOYEE_MANAGEMENT-3702619")&&(o.register("sw-permission-tree",(function(){return r.e(2).then(r.bind(null,"aHXb"))})),o.register("sw-customer-role-card",(function(){return r.e(1).then(r.bind(null,"CT3j"))})),o.register("sw-customer-role-create",(function(){return r.e(4).then(r.bind(null,"9QND"))})),o.register("sw-customer-detail-company",(function(){return r.e(8).then(r.bind(null,"vYjj"))})),o.register("sw-customer-employee-card",(function(){return r.e(0).then(r.bind(null,"ZOop"))})),o.register("sw-customer-employee-create",(function(){return r.e(7).then(r.bind(null,"4tqO"))})),o.extend("sw-customer-role-detail","sw-customer-role-create",(function(){return r.e(5).then(r.bind(null,"gr0n"))})),o.extend("sw-customer-employee-detail","sw-customer-employee-create",(function(){return r.e(3).then(r.bind(null,"UfUE"))})),o.override("sw-customer-detail",(function(){return r.e(6).then(r.bind(null,"MVbg"))})))},IRGx:function(e,t){Shopware.Service("privileges").addPrivilegeMappingEntry({category:"permissions",parent:"b2b",key:"b2b_employee_management",roles:{viewer:{privileges:["b2b_employee:read","b2b_components_role:read","b2b_business_partner:read","customer_specific_features:read"],dependencies:["customer.viewer"]},editor:{privileges:["b2b_employee:update","b2b_components_role:update","b2b_business_partner:create","b2b_business_partner:update","b2b_business_partner:delete","customer_specific_features:create","customer_specific_features:update"],dependencies:["b2b_employee_management.viewer","customer.editor"]},creator:{privileges:["b2b_employee:create","b2b_components_role:create","b2b_business_partner:create","customer_specific_features:create"],dependencies:["b2b_employee_management.editor"]},deleter:{privileges:["b2b_employee:delete","b2b_components_role:delete"],dependencies:["b2b_employee_management.editor"]}}})},Vpbe:function(e,t,r){var n=Shopware,o=n.Component;n.License.get("EMPLOYEE_MANAGEMENT-3702619")&&o.override("sw-settings-customer-group-detail",(function(){return r.e(11).then(r.bind(null,"3b/U"))}))}});