!function(e){function t(t){for(var r,n,i=t[0],c=t[1],s=0,a=[];s<i.length;s++)n=i[s],Object.prototype.hasOwnProperty.call(o,n)&&o[n]&&a.push(o[n][0]),o[n]=0;for(r in c)Object.prototype.hasOwnProperty.call(c,r)&&(e[r]=c[r]);for(u&&u(t);a.length;)a.shift()()}var r={},n={"classification-customer":0},o={"classification-customer":0};function i(t){if(r[t])return r[t].exports;var n=r[t]={i:t,l:!1,exports:{}};return e[t].call(n.exports,n,n.exports,i),n.l=!0,n.exports}i.e=function(e){var t=[];n[e]?t.push(n[e]):0!==n[e]&&{0:1,1:1,2:1,3:1,4:1}[e]&&t.push(n[e]=new Promise((function(t,r){for(var o="static/css/"+({}[e]||e)+".css",c=i.p+o,s=document.getElementsByTagName("link"),a=0;a<s.length;a++){var u=(f=s[a]).getAttribute("data-href")||f.getAttribute("href");if("stylesheet"===f.rel&&(u===o||u===c))return t()}var l=document.getElementsByTagName("style");for(a=0;a<l.length;a++){var f;if((u=(f=l[a]).getAttribute("data-href"))===o||u===c)return t()}var p=document.createElement("link");p.rel="stylesheet",p.type="text/css";p.onerror=p.onload=function(o){if(p.onerror=p.onload=null,"load"===o.type)t();else{var i=o&&("load"===o.type?"missing":o.type),s=o&&o.target&&o.target.href||c,a=new Error("Loading CSS chunk "+e+" failed.\n("+s+")");a.code="CSS_CHUNK_LOAD_FAILED",a.type=i,a.request=s,delete n[e],p.parentNode.removeChild(p),r(a)}},p.href=c,document.head.appendChild(p)})).then((function(){n[e]=0})));var r=o[e];if(0!==r)if(r)t.push(r[2]);else{var c=new Promise((function(t,n){r=o[e]=[t,n]}));t.push(r[2]=c);var s,a=document.createElement("script");a.charset="utf-8",a.timeout=120,i.nc&&a.setAttribute("nonce",i.nc),a.src=function(e){return i.p+"static/js/"+({}[e]||e)+".js"}(e);var u=new Error;s=function(t){a.onerror=a.onload=null,clearTimeout(l);var r=o[e];if(0!==r){if(r){var n=t&&("load"===t.type?"missing":t.type),i=t&&t.target&&t.target.src;u.message="Loading chunk "+e+" failed.\n("+n+": "+i+")",u.name="ChunkLoadError",u.type=n,u.request=i,r[1](u)}o[e]=void 0}};var l=setTimeout((function(){s({type:"timeout",target:a})}),12e4);a.onerror=a.onload=s,document.head.appendChild(a)}return Promise.all(t)},i.m=e,i.c=r,i.d=function(e,t,r){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)i.d(r,n,function(t){return e[t]}.bind(null,n));return r},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p=(window.__sw__.assetPath + '/bundles/classificationcustomer/'),i.oe=function(e){throw console.error(e),e};var c=this["webpackJsonpPluginclassification-customer"]=this["webpackJsonpPluginclassification-customer"]||[],s=c.push.bind(c);c.push=t,c=c.slice();for(var a=0;a<c.length;a++)t(c[a]);var u=s;i(i.s="Aaa7")}({Aaa7:function(e,t,r){"use strict";r.r(t);var n=r("l6kG");function o(e){return(o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function i(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function c(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?i(Object(r),!0).forEach((function(t){s(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):i(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}function s(e,t,r){return(t=l(t))in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function a(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function u(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,l(n.key),n)}}function l(e){var t=function(e,t){if("object"!==o(e)||null===e)return e;var r=e[Symbol.toPrimitive];if(void 0!==r){var n=r.call(e,t||"default");if("object"!==o(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===o(t)?t:String(t)}function f(e,t){return(f=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,t){return e.__proto__=t,e})(e,t)}function p(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var r,n=d(e);if(t){var o=d(this).constructor;r=Reflect.construct(n,arguments,o)}else r=n.apply(this,arguments);return m(this,r)}}function m(e,t){if(t&&("object"===o(t)||"function"==typeof t))return t;if(void 0!==t)throw new TypeError("Derived constructors may only return object or undefined");return function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e)}function d(e){return(d=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var h=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&f(e,t)}(s,e);var t,r,o,i=p(s);function s(e,t){var r,n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"classify";return a(this,s),(r=i.call(this,e,t,n)).name="customerClassifyApiService",r}return t=s,(r=[{key:"generateTags",value:function(e,t){var r=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{};if(Shopware.License.get(n.b))return this.httpClient.get("_info/me",{headers:c(c({},this.getBasicHeaders()),{},{"sw-license-toggle":n.b})});var o=this.getBasicHeaders(r),i={additionInformation:e,numberOfTags:t,customerFields:["customer_number","order_count","last_order_date","order_total_amount","last login","first login"],formatResponse:'{"classifications": [{ "name": [tagged_name], "description": [description without formula], "ruleBuilder": [formula]}'};return this.httpClient.post("/_action/classification-customer/generate-tags",i,{headers:o})}},{key:"classify",value:function(e,t,r){var o=arguments.length>4&&void 0!==arguments[4]?arguments[4]:{};if(Shopware.License.get(n.c))return this.httpClient.get("_info/me",{headers:c(c({},this.getBasicHeaders()),{},{"sw-license-toggle":n.c})});var i=this.getBasicHeaders(o),s={groups:e,customerIds:t,formatResponse:r};return this.httpClient.post("/_action/classification-customer/classify",s,{headers:i})}}])&&u(t.prototype,r),o&&u(t,o),Object.defineProperty(t,"prototype",{writable:!1}),s}(Shopware.Classes.ApiService);Shopware.Service().register("customerClassifyApiService",(function(){var e=Shopware.Application.getContainer("init");return new h(e.httpClient,Shopware.Service("loginService"))})),Shopware.Component.register("swag-customer-classification-index",(function(){return r.e(3).then(r.bind(null,"RUNl"))})),Shopware.Component.register("swag-customer-classification-basic",(function(){return r.e(4).then(r.bind(null,"wuxm"))})),Shopware.Component.register("swag-customer-classification-save-modal",(function(){return r.e(2).then(r.bind(null,"F441"))})),Shopware.Component.register("swag-customer-classification-confirm-modal",(function(){return r.e(1).then(r.bind(null,"wRJp"))})),Shopware.Component.register("swag-customer-classification-process-modal",(function(){return r.e(7).then(r.bind(null,"idjC"))})),Shopware.Component.register("swag-customer-classification-success-modal",(function(){return r.e(8).then(r.bind(null,"qcTC"))})),Shopware.Component.register("swag-customer-classification-error-modal",(function(){return r.e(6).then(r.bind(null,"XzBK"))})),Shopware.Component.register("swag-customer-classification-edit-tag-modal",(function(){return r.e(5).then(r.bind(null,"10z9"))})),Shopware.Component.override("sw-customer-list",(function(){return r.e(0).then(r.bind(null,"0RnA"))})),Shopware.License.get(n.a)&&Shopware.Module.register("swag-customer-classification",{type:"plugin",name:"customer-classification",title:"swag-customer-classification.mainMenuItemIndex",color:"#F88962",icon:"regular-users",routes:{index:{component:"swag-customer-classification-index",path:"index",meta:{privilege:"customer.viewer",parentPath:"sw.customer.index"},children:{save:{component:"swag-customer-classification-save-modal",path:"save",redirect:{name:"swag.customer.classification.index.save.confirm"},children:{confirm:{component:"swag-customer-classification-confirm-modal",path:"confirm"},process:{component:"swag-customer-classification-process-modal",path:"process"},success:{component:"swag-customer-classification-success-modal",path:"success"},error:{component:"swag-customer-classification-error-modal",path:"error"}}}}}}})},l6kG:function(e,t,r){"use strict";r.d(t,"a",(function(){return n})),r.d(t,"b",(function(){return o})),r.d(t,"c",(function(){return i}));var n="CUSTOMER_CLASSIFICATION-8634272",o="CUSTOMER_CLASSIFICATION-9819811",i="CUSTOMER_CLASSIFICATION-9136958"}});