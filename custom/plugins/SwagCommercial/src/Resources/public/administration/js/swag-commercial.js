!function(e){function t(t){for(var n,r,i=t[0],a=t[1],c=0,u=[];c<i.length;c++)r=i[c],Object.prototype.hasOwnProperty.call(o,r)&&o[r]&&u.push(o[r][0]),o[r]=0;for(n in a)Object.prototype.hasOwnProperty.call(a,n)&&(e[n]=a[n]);for(l&&l(t);u.length;)u.shift()()}var n={},r={"swag-commercial":0},o={"swag-commercial":0};function i(t){if(n[t])return n[t].exports;var r=n[t]={i:t,l:!1,exports:{}};return e[t].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.e=function(e){var t=[];r[e]?t.push(r[e]):0!==r[e]&&{0:1}[e]&&t.push(r[e]=new Promise((function(t,n){for(var o="static/css/"+({}[e]||e)+".css",a=i.p+o,c=document.getElementsByTagName("link"),u=0;u<c.length;u++){var l=(p=c[u]).getAttribute("data-href")||p.getAttribute("href");if("stylesheet"===p.rel&&(l===o||l===a))return t()}var s=document.getElementsByTagName("style");for(u=0;u<s.length;u++){var p;if((l=(p=s[u]).getAttribute("data-href"))===o||l===a)return t()}var f=document.createElement("link");f.rel="stylesheet",f.type="text/css";f.onerror=f.onload=function(o){if(f.onerror=f.onload=null,"load"===o.type)t();else{var i=o&&("load"===o.type?"missing":o.type),c=o&&o.target&&o.target.href||a,u=new Error("Loading CSS chunk "+e+" failed.\n("+c+")");u.code="CSS_CHUNK_LOAD_FAILED",u.type=i,u.request=c,delete r[e],f.parentNode.removeChild(f),n(u)}},f.href=a,document.head.appendChild(f)})).then((function(){r[e]=0})));var n=o[e];if(0!==n)if(n)t.push(n[2]);else{var a=new Promise((function(t,r){n=o[e]=[t,r]}));t.push(n[2]=a);var c,u=document.createElement("script");u.charset="utf-8",u.timeout=120,i.nc&&u.setAttribute("nonce",i.nc),u.src=function(e){return i.p+"static/js/"+({}[e]||e)+".js"}(e);var l=new Error;c=function(t){u.onerror=u.onload=null,clearTimeout(s);var n=o[e];if(0!==n){if(n){var r=t&&("load"===t.type?"missing":t.type),i=t&&t.target&&t.target.src;l.message="Loading chunk "+e+" failed.\n("+r+": "+i+")",l.name="ChunkLoadError",l.type=r,l.request=i,n[1](l)}o[e]=void 0}};var s=setTimeout((function(){c({type:"timeout",target:u})}),12e4);u.onerror=u.onload=c,document.head.appendChild(u)}return Promise.all(t)},i.m=e,i.c=n,i.d=function(e,t,n){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)i.d(n,r,function(t){return e[t]}.bind(null,r));return n},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p=(window.__sw__.assetPath + '/bundles/swagcommercial/'),i.oe=function(e){throw console.error(e),e};var a=this["webpackJsonpPluginswag-commercial"]=this["webpackJsonpPluginswag-commercial"]||[],c=a.push.bind(a);a.push=t,a=a.slice();for(var u=0;u<a.length;u++)t(a[u]);var l=c;i(i.s="3euH")}({"3euH":function(e,t,n){Shopware.Component.register("sw-text-field-ai",(function(){return n.e(0).then(n.bind(null,"nykC"))}));var r=function(){Shopware.Application.getContainer("init").httpClient.get("_admin/known-ips",{headers:{Accept:"application/json",Authorization:"Bearer ".concat(Shopware.Service("loginService").getToken),"Content-Type":"application/json","sw-license-toggle":"FLOW_BUILDER-2909938"}}).catch((function(){}))};void 0===Shopware.License&&Object.defineProperty(Shopware,"License",{get:function(){return Object.defineProperty({},"get",{get:function(){return function(e){return Shopware.State.get("context").app.config.licenseToggles[e]}},set:function(){r()}})},set:function(){r()}})}});