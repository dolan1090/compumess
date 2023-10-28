(this["webpackJsonpPluginclassification-customer"]=this["webpackJsonpPluginclassification-customer"]||[]).push([[2],{F441:function(t,e,n){"use strict";n.r(e);n("p5Fv");var o=Shopware.Component;e.default=o.wrapComponentConfig({template:'<sw-modal\n    class="swag-customer-classification-save-modal"\n    :title="title"\n    variant="small"\n    @modal-close="onModalClose"\n>\n\n    <div class="swag-customer-classification-save-modal-body">\n        <router-view\n            :item-total="itemTotal"\n            @start-classify="startClassify"\n            @buttons-update="updateButtons"\n            @redirect="redirect"\n            @title-set="setTitle"\n        />\n    </div>\n\n    <template #modal-footer>\n        <div class="footer-left">\n            <sw-button\n                v-for="button in buttons.left"\n                :key="button.key"\n                size="small"\n                :variant="button.variant"\n                :disabled="button.disabled"\n                @click="onButtonClick(button.action)"\n            >\n                {{ button.label }}\n            </sw-button>\n        </div>\n\n        <div class="footer-right">\n            <sw-button\n                v-for="button in buttons.right"\n                :key="button.key"\n                size="small"\n                :variant="button.variant"\n                :disabled="button.disabled"\n                @click="onButtonClick(button.action)"\n            >\n                {{ button.label }}\n            </sw-button>\n        </div>\n    </template>\n</sw-modal>\n',props:{isLoading:{required:!0,type:Boolean},processStatus:{required:!0,type:String},itemTotal:{required:!0,type:Number}},data:function(){return{title:null,buttonConfig:[]}},computed:{currentStep:function(){return this.isLoading&&!this.processStatus?"process":this.isLoading||"success"!==this.processStatus?this.isLoading||"error"!==this.processStatus?"confirm":"error":"success"},buttons:function(){return{right:this.buttonConfig.filter((function(t){return"right"===t.position})),left:this.buttonConfig.filter((function(t){return"left"===t.position}))}}},watch:{currentStep:function(t){"success"===t&&this.redirect("success"),"error"===t&&this.redirect("error")}},created:function(){this.createdComponent()},beforeDestroy:function(){this.beforeDestroyComponent()},methods:{createdComponent:function(){this.addEventListeners()},beforeDestroyComponent:function(){this.removeEventListeners()},addEventListeners:function(){var t=this;window.addEventListener("beforeunload",(function(e){return t.beforeUnloadListener(e)}))},removeEventListeners:function(){var t=this;window.removeEventListener("beforeunload",(function(e){return t.beforeUnloadListener(e)}))},beforeUnloadListener:function(t){return this.isLoading?(t.preventDefault(),t.returnValue=this.$tc("swag-customer-classification.notificationModal.messageBeforeTabLeave"),this.$tc("swag-customer-classification.notificationModal.messageBeforeTabLeave")):""},onModalClose:function(){this.$emit("modal-close")},startClassify:function(){this.$emit("start-classify")},redirect:function(t){t?this.$router.push({path:t}):this.$emit("modal-close")},setTitle:function(t){this.title=t},updateButtons:function(t){this.buttonConfig=t},onButtonClick:function(t){"string"!=typeof t?"function"==typeof t&&t.call():this.redirect(t)}}})},FJiR:function(t,e,n){},P8hj:function(t,e,n){"use strict";function o(t,e){for(var n=[],o={},i=0;i<e.length;i++){var s=e[i],r=s[0],a={id:t+":"+i,css:s[1],media:s[2],sourceMap:s[3]};o[r]?o[r].parts.push(a):n.push(o[r]={id:r,parts:[a]})}return n}n.r(e),n.d(e,"default",(function(){return h}));var i="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!i)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var s={},r=i&&(document.head||document.getElementsByTagName("head")[0]),a=null,u=0,c=!1,l=function(){},d=null,f="data-vue-ssr-id",p="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function h(t,e,n,i){c=n,d=i||{};var r=o(t,e);return v(r),function(e){for(var n=[],i=0;i<r.length;i++){var a=r[i];(u=s[a.id]).refs--,n.push(u)}e?v(r=o(t,e)):r=[];for(i=0;i<n.length;i++){var u;if(0===(u=n[i]).refs){for(var c=0;c<u.parts.length;c++)u.parts[c]();delete s[u.id]}}}}function v(t){for(var e=0;e<t.length;e++){var n=t[e],o=s[n.id];if(o){o.refs++;for(var i=0;i<o.parts.length;i++)o.parts[i](n.parts[i]);for(;i<n.parts.length;i++)o.parts.push(b(n.parts[i]));o.parts.length>n.parts.length&&(o.parts.length=n.parts.length)}else{var r=[];for(i=0;i<n.parts.length;i++)r.push(b(n.parts[i]));s[n.id]={id:n.id,refs:1,parts:r}}}}function m(){var t=document.createElement("style");return t.type="text/css",r.appendChild(t),t}function b(t){var e,n,o=document.querySelector("style["+f+'~="'+t.id+'"]');if(o){if(c)return l;o.parentNode.removeChild(o)}if(p){var i=u++;o=a||(a=m()),e=C.bind(null,o,i,!1),n=C.bind(null,o,i,!0)}else o=m(),e=w.bind(null,o),n=function(){o.parentNode.removeChild(o)};return e(t),function(o){if(o){if(o.css===t.css&&o.media===t.media&&o.sourceMap===t.sourceMap)return;e(t=o)}else n()}}var g,y=(g=[],function(t,e){return g[t]=e,g.filter(Boolean).join("\n")});function C(t,e,n,o){var i=n?"":o.css;if(t.styleSheet)t.styleSheet.cssText=y(e,i);else{var s=document.createTextNode(i),r=t.childNodes;r[e]&&t.removeChild(r[e]),r.length?t.insertBefore(s,r[e]):t.appendChild(s)}}function w(t,e){var n=e.css,o=e.media,i=e.sourceMap;if(o&&t.setAttribute("media",o),d.ssrId&&t.setAttribute(f,e.id),i&&(n+="\n/*# sourceURL="+i.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(i))))+" */"),t.styleSheet)t.styleSheet.cssText=n;else{for(;t.firstChild;)t.removeChild(t.firstChild);t.appendChild(document.createTextNode(n))}}},p5Fv:function(t,e,n){var o=n("FJiR");o.__esModule&&(o=o.default),"string"==typeof o&&(o=[[t.i,o,""]]),o.locals&&(t.exports=o.locals);(0,n("P8hj").default)("59d81db3",o,!0,{})}}]);