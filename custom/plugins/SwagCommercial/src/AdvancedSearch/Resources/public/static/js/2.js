(this["webpackJsonpPluginadvanced-search"]=this["webpackJsonpPluginadvanced-search"]||[]).push([[2],{"8zGP":function(e,t,n){var i=n("iJu0");i.__esModule&&(i=i.default),"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);(0,n("P8hj").default)("2409313a",i,!0,{})},P8hj:function(e,t,n){"use strict";function i(e,t){for(var n=[],i={},r=0;r<t.length;r++){var o=t[r],a=o[0],s={id:e+":"+r,css:o[1],media:o[2],sourceMap:o[3]};i[a]?i[a].parts.push(s):n.push(i[a]={id:a,parts:[s]})}return n}n.r(t),n.d(t,"default",(function(){return h}));var r="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!r)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var o={},a=r&&(document.head||document.getElementsByTagName("head")[0]),s=null,l=0,u=!1,c=function(){},d=null,p="data-vue-ssr-id",f="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function h(e,t,n,r){u=n,d=r||{};var a=i(e,t);return m(a),function(t){for(var n=[],r=0;r<a.length;r++){var s=a[r];(l=o[s.id]).refs--,n.push(l)}t?m(a=i(e,t)):a=[];for(r=0;r<n.length;r++){var l;if(0===(l=n[r]).refs){for(var u=0;u<l.parts.length;u++)l.parts[u]();delete o[l.id]}}}}function m(e){for(var t=0;t<e.length;t++){var n=e[t],i=o[n.id];if(i){i.refs++;for(var r=0;r<i.parts.length;r++)i.parts[r](n.parts[r]);for(;r<n.parts.length;r++)i.parts.push(g(n.parts[r]));i.parts.length>n.parts.length&&(i.parts.length=n.parts.length)}else{var a=[];for(r=0;r<n.parts.length;r++)a.push(g(n.parts[r]));o[n.id]={id:n.id,refs:1,parts:a}}}}function v(){var e=document.createElement("style");return e.type="text/css",a.appendChild(e),e}function g(e){var t,n,i=document.querySelector("style["+p+'~="'+e.id+'"]');if(i){if(u)return c;i.parentNode.removeChild(i)}if(f){var r=l++;i=s||(s=v()),t=C.bind(null,i,r,!1),n=C.bind(null,i,r,!0)}else i=v(),t=w.bind(null,i),n=function(){i.parentNode.removeChild(i)};return t(e),function(i){if(i){if(i.css===e.css&&i.media===e.media&&i.sourceMap===e.sourceMap)return;t(e=i)}else n()}}var y,b=(y=[],function(e,t){return y[e]=t,y.filter(Boolean).join("\n")});function C(e,t,n,i){var r=n?"":i.css;if(e.styleSheet)e.styleSheet.cssText=b(t,r);else{var o=document.createTextNode(r),a=e.childNodes;a[t]&&e.removeChild(a[t]),a.length?e.insertBefore(o,a[t]):e.appendChild(o)}}function w(e,t){var n=t.css,i=t.media,r=t.sourceMap;if(i&&e.setAttribute("media",i),d.ssrId&&e.setAttribute(p,t.id),r&&(n+="\n/*# sourceURL="+r.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(r))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}},"b7d/":function(e,t,n){"use strict";n.r(t);var i=n("eYt7"),r=n("v4K6");n("8zGP");function o(e){return(o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function a(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(e);t&&(i=i.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,i)}return n}function s(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?a(Object(n),!0).forEach((function(t){l(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):a(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function l(e,t,n){return(t=function(e){var t=function(e,t){if("object"!==o(e)||null===e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var i=n.call(e,t||"default");if("object"!==o(i))return i;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===o(t)?t:String(t)}(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}t.default={template:'<div\n    class="swag-advanced-search-entity-stream-value"\n    :class="componentClasses"\n>\n    <template v-if="!fieldDefinition">\n        <sw-container class="swag-advanced-search-entity-stream-value__placeholder" />\n    </template>\n\n    <template v-else-if="fieldType === \'boolean\'">\n        <sw-single-select\n            size="medium"\n            :options="booleanOptions"\n            :value="condition.value"\n            @change="setBooleanValue"\n        />\n    </template>\n\n    <template v-else>\n        <sw-arrow-field>\n            <sw-single-select\n                v-model="filterType"\n                size="medium"\n                :options="operators"\n                :placeholder="$tc(\'swag-advanced-search.boostingTab.filter.placeholderOperatorSelect\')"\n            />\n        </sw-arrow-field>\n\n        <template v-if="fieldType === \'uuid\'">\n            <sw-entity-single-select\n                v-if="actualCondition.type === \'equals\'"\n                v-model="actualCondition.value"\n                size="medium"\n                :entity="definition.entity"\n                :context="context"\n            />\n\n            <sw-entity-multi-id-select\n                v-else-if="actualCondition.type === \'equalsAny\'"\n                v-model="multiValue"\n                size="medium"\n                :repository="repository"\n                :context="context"\n            />\n\n            <sw-container\n                v-else\n                class="swag-advanced-search-entity-stream-value__placeholder"\n            />\n        </template>\n\n        <template v-else-if="getConditionType(condition) === \'range\'">\n            <template v-if="filterType === \'range\'">\n                <sw-arrow-field>\n                    <component\n                        :is="inputComponent"\n                        v-model="gte"\n                        size="medium"\n                    />\n                </sw-arrow-field>\n\n                <component\n                    :is="inputComponent"\n                    v-model="lte"\n                    size="medium"\n                />\n            </template>\n\n            <template v-else>\n                <component\n                    :is="inputComponent"\n                    v-model="currentParameter"\n                    size="medium"\n                />\n            </template>\n        </template>\n\n        <template v-else-if="actualCondition.type === \'equalsAny\'">\n            <sw-tagged-field size="medium" v-model="multiValue" />\n        </template>\n\n        <template v-else>\n            <component\n                :is="inputComponent"\n                v-model="stringValue"\n                size="medium"\n            />\n        </template>\n    </template>\n</div>\n',inject:["repositoryFactory","conditionDataProviderService"],props:{condition:{type:Object,required:!0},fieldName:{type:String,required:!1,default:null},definition:{type:Object,required:!0}},data:function(){return{value:null,childComponents:null}},computed:{repository:function(){return Object(i.a)(this.repositoryFactory.create(this.definition.entity),r.d)},componentClasses:function(){return[this.growthClass]},growthClass:function(){return null===this.childComponents?"sw-product-stream-value--grow-0":"sw-product-stream-value--grow-".concat(this.childComponents.length)},actualCondition:function(){return"not"===this.condition.type?this.condition.queries[0]:this.condition},filterType:{get:function(){var e=this.getConditionType(this.condition);return"range"===e?this.getRangeType(this.actualCondition):e},set:function(e){this.conditionDataProviderService.isRangeType(e)?this.onChangeType("range",this.getParameters(e)):this.onChangeType(e,null)}},fieldDefinition:function(){return this.definition.getField(this.fieldName)},operators:function(){var e=this;return null===this.fieldType?[]:this.conditionDataProviderService.getOperatorSet(this.fieldType).map((function(t){return{label:e.$tc(t.label),value:t.identifier}}))},fieldType:function(){return this.fieldDefinition?this.definition.isJsonField(this.fieldDefinition)?"object":this.fieldDefinition.type:null},booleanOptions:function(){return[{label:this.$tc("global.default.yes"),value:"1"},{label:this.$tc("global.default.no"),value:"0"}]},multiValue:{get:function(){return null===this.actualCondition.value||""===this.actualCondition.value?[]:this.actualCondition.value.split("|")},set:function(e){this.actualCondition.value=e.join("|")}},inputComponent:function(){switch(this.fieldType){case"uuid":return"sw-entity-multi-id-select";case"float":case"int":return"sw-number-field";case"date":return"sw-datepicker";case"string":case"object":default:return"sw-text-field"}},currentParameter:{get:function(){return this.actualCondition.parameters?this.actualCondition.parameters[this.getParameterName(this.filterType)]:null},set:function(e){var t=this.getParameterName(this.filterType);this.actualCondition.parameters=l({},t,e)}},gte:{get:function(){return this.actualCondition.parameters?this.actualCondition.parameters.gte:null},set:function(e){this.actualCondition.parameters.gte=e}},lte:{get:function(){return this.actualCondition.parameters?this.actualCondition.parameters.lte:null},set:function(e){this.actualCondition.parameters.lte=e}},stringValue:{get:function(){return["int","float"].includes(this.fieldType)?Number.parseFloat(this.actualCondition.value):this.actualCondition.value},set:function(e){this.actualCondition.value=e.toString()}},context:function(){return s(s({},Shopware.Context.api),{},{inheritance:!0})}},mounted:function(){this.mountedComponent()},methods:{mountedComponent:function(){this.childComponents=this.$children},onChangeType:function(e,t){this.$emit("type-change",{type:e,parameters:t})},getConditionType:function(e){if("not"===this.condition.type){var t=e.queries[0].type;return this.conditionDataProviderService.negateOperator(t).identifier}return this.condition.type},getRangeType:function(e){if(null===e.parameters)return null;var t=e.parameters.hasOwnProperty("lte"),n=e.parameters.hasOwnProperty("gte");return n&&t?this.conditionDataProviderService.getOperator("range").identifier:n?this.conditionDataProviderService.getOperator("greaterThanEquals").identifier:t?this.conditionDataProviderService.getOperator("lessThanEquals").identifier:this.condition.parameters.hasOwnProperty("lt")?this.conditionDataProviderService.getOperator("lessThan").identifier:this.condition.parameters.hasOwnProperty("gt")?this.conditionDataProviderService.getOperator("greaterThan").identifier:null},getParameters:function(e){if("range"===e)return{lte:null,gte:null};var t=this.getParameterName(e);return t?l({},t,null):null},getParameterName:function(e){switch(e){case"greaterThanEquals":return"gte";case"lessThanEquals":return"lte";case"lessThan":return"lt";case"greaterThan":return"gt";default:return null}},setBooleanValue:function(e){this.condition.value=e,this.condition.type="equals"}}}},eYt7:function(e,t,n){"use strict";function i(e,t){if(!Shopware.License.get(t))return e;var n=e.buildHeaders;return e.buildHeaders=function(){var i=arguments.length>0&&void 0!==arguments[0]?arguments[0]:Shopware.Context.api,r=n.call(e,i);return Object.assign(r,{"sw-license-toggle":t}),r},e}n.d(t,"a",(function(){return i}))},iJu0:function(e,t,n){}}]);