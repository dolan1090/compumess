!function(e){function t(t){for(var r,n,i=t[0],u=t[1],c=0,a=[];c<i.length;c++)n=i[c],Object.prototype.hasOwnProperty.call(o,n)&&o[n]&&a.push(o[n][0]),o[n]=0;for(r in u)Object.prototype.hasOwnProperty.call(u,r)&&(e[r]=u[r]);for(l&&l(t);a.length;)a.shift()()}var r={},n={"quick-order":0},o={"quick-order":0};function i(t){if(r[t])return r[t].exports;var n=r[t]={i:t,l:!1,exports:{}};return e[t].call(n.exports,n,n.exports,i),n.l=!0,n.exports}i.e=function(e){var t=[];n[e]?t.push(n[e]):0!==n[e]&&{0:1}[e]&&t.push(n[e]=new Promise((function(t,r){for(var o="static/css/"+({}[e]||e)+".css",u=i.p+o,c=document.getElementsByTagName("link"),a=0;a<c.length;a++){var l=(s=c[a]).getAttribute("data-href")||s.getAttribute("href");if("stylesheet"===s.rel&&(l===o||l===u))return t()}var f=document.getElementsByTagName("style");for(a=0;a<f.length;a++){var s;if((l=(s=f[a]).getAttribute("data-href"))===o||l===u)return t()}var p=document.createElement("link");p.rel="stylesheet",p.type="text/css";p.onerror=p.onload=function(o){if(p.onerror=p.onload=null,"load"===o.type)t();else{var i=o&&("load"===o.type?"missing":o.type),c=o&&o.target&&o.target.href||u,a=new Error("Loading CSS chunk "+e+" failed.\n("+c+")");a.code="CSS_CHUNK_LOAD_FAILED",a.type=i,a.request=c,delete n[e],p.parentNode.removeChild(p),r(a)}},p.href=u,document.head.appendChild(p)})).then((function(){n[e]=0})));var r=o[e];if(0!==r)if(r)t.push(r[2]);else{var u=new Promise((function(t,n){r=o[e]=[t,n]}));t.push(r[2]=u);var c,a=document.createElement("script");a.charset="utf-8",a.timeout=120,i.nc&&a.setAttribute("nonce",i.nc),a.src=function(e){return i.p+"static/js/"+({}[e]||e)+".js"}(e);var l=new Error;c=function(t){a.onerror=a.onload=null,clearTimeout(f);var r=o[e];if(0!==r){if(r){var n=t&&("load"===t.type?"missing":t.type),i=t&&t.target&&t.target.src;l.message="Loading chunk "+e+" failed.\n("+n+": "+i+")",l.name="ChunkLoadError",l.type=n,l.request=i,r[1](l)}o[e]=void 0}};var f=setTimeout((function(){c({type:"timeout",target:a})}),12e4);a.onerror=a.onload=c,document.head.appendChild(a)}return Promise.all(t)},i.m=e,i.c=r,i.d=function(e,t,r){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)i.d(r,n,function(t){return e[t]}.bind(null,n));return r},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p=(window.__sw__.assetPath + '/bundles/quickorder/'),i.oe=function(e){throw console.error(e),e};var u=this["webpackJsonpPluginquick-order"]=this["webpackJsonpPluginquick-order"]||[],c=u.push.bind(u);u.push=t,u=u.slice();for(var a=0;a<u.length;a++)t(u[a]);var l=c;i(i.s="J9v0")}({J9v0:function(e,t,r){"use strict";function n(e){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function o(e,t){for(var r=0;r<t.length;r++){var o=t[r];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,(i=o.key,u=void 0,u=function(e,t){if("object"!==n(e)||null===e)return e;var r=e[Symbol.toPrimitive];if(void 0!==r){var o=r.call(e,t||"default");if("object"!==n(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(i,"string"),"symbol"===n(u)?u:String(u)),o)}var i,u}function i(e,t){return(i=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,t){return e.__proto__=t,e})(e,t)}function u(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var r,n=a(e);if(t){var o=a(this).constructor;r=Reflect.construct(n,arguments,o)}else r=n.apply(this,arguments);return c(this,r)}}function c(e,t){if(t&&("object"===n(t)||"function"==typeof t))return t;if(void 0!==t)throw new TypeError("Derived constructors may only return object or undefined");return function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e)}function a(e){return(a=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}r.r(t);var l=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&i(e,t)}(a,e);var t,r,n,c=u(a);function a(e,t){var r;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,a),(r=c.call(this,e,t)).name="specificFeaturesApiService",r}return t=a,(r=[{key:"getSpecificFeatures",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=this.getBasicHeaders(t);return this.httpClient.get("/_admin/licensing/features/B2B",{additionalParams:e,headers:r})}}])&&o(t.prototype,r),n&&o(t,n),Object.defineProperty(t,"prototype",{writable:!1}),a}(Shopware.Classes.ApiService);Shopware.Service().register("specificFeaturesApiService",(function(){var e=Shopware.Application.getContainer("init");return new l(e.httpClient,Shopware.Service("loginService"))})),Shopware.Component.register("swag-b2b-features-customer-specific-features",(function(){return r.e(0).then(r.bind(null,"br5J"))})),Shopware.Component.override("sw-customer-detail",(function(){return r.e(2).then(r.bind(null,"aLi6"))})),Shopware.Component.override("sw-customer-detail-base",(function(){return r.e(3).then(r.bind(null,"8k+Y"))})),Shopware.Component.override("sw-bulk-edit-customer",(function(){return r.e(1).then(r.bind(null,"CDsj"))}))}});