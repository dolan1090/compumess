(this["webpackJsonpPluginwebhook-flow-action"]=this["webpackJsonpPluginwebhook-flow-action"]||[]).push([[4],{butA:function(e,t,n){"use strict";n.r(t);function r(e){return(r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function o(e){return function(e){if(Array.isArray(e))return a(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||function(e,t){if(!e)return;if("string"==typeof e)return a(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return a(e,t)}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function a(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}function i(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function l(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?i(Object(n),!0).forEach((function(t){c(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):i(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function c(e,t,n){return(t=function(e){var t=function(e,t){if("object"!==r(e)||null===e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var o=n.call(e,t||"default");if("object"!==r(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"===r(t)?t:String(t)}(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}t.default={template:'<sw-data-grid\n    class="sw-flow-call-webhook-parameter-grid"\n    :data-source="records"\n    :columns="parameterColumns"\n    :show-selection="false"\n    :plain-appearance="true"\n>\n    <template #column-name="{ item, itemIndex }">\n        <sw-text-field\n            v-model="item.name"\n            size="small"\n            :placeholder="$tc(\'sw-flow-call-webhook.modal.placeholderName\')"\n            @change="onChangeItem(item, itemIndex)"\n        />\n    </template>\n\n    <template #column-data="{ item, itemIndex }">\n        <sw-single-select\n            v-if="!item.isCustomData"\n            v-model="item.data"\n            size="small"\n            label-property="value"\n            :options="dataSelection"\n            :placeholder="$tc(\'sw-flow-call-webhook.modal.placeholderData\')"\n            @change="onChangeItem(item, itemIndex)"\n        />\n\n        <sw-text-field\n            v-if="item.isCustomData"\n            v-model="item.data"\n            size="small"\n            :placeholder="$tc(\'sw-flow-call-webhook.modal.placeholderCustomData\')"\n            @change="onChangeItem(item, itemIndex)"\n        />\n    </template>\n\n    <template #actions="{ item, itemIndex }">\n        <sw-context-menu-item\n            class="sw-flow-call-webhook-parameter-grid__choose-parameter"\n            @click="changeToCustomText(item, itemIndex)"\n        >\n                {{ item.isCustomData\n                ? $tc(\'sw-flow-call-webhook.modal.contextButton.choosePredefinedParam\')\n                : $tc(\'sw-flow-call-webhook.modal.contextButton.useOwnParam\') }}\n        </sw-context-menu-item>\n\n        <sw-context-menu-item\n            class="sw-flow-call-webhook-parameter-grid__delete-param"\n            variant="danger"\n            :disabled="disableDelete(itemIndex)"\n            @click="deleteItem(itemIndex)"\n        >\n            {{ $tc(\'sw-flow-call-webhook.modal.contextButton.deleteParameter\') }}\n        </sw-context-menu-item>\n    </template>\n</sw-data-grid>\n',model:{prop:"parameters",event:"change"},props:{parameters:{type:Array,default:{},required:!0},dataSelection:{type:Array,default:[],required:!0}},data:function(){return{records:this.parameters}},computed:{parameterColumns:function(){return[{label:this.$tc("sw-flow-call-webhook.modal.columnName"),property:"name",dataIndex:"name",primary:!0,width:"250px"},{label:this.$tc("sw-flow-call-webhook.modal.columnData"),property:"data",dataIndex:"data",primary:!0,width:"250px"}]}},watch:{parameters:{handler:function(e){e&&e.length&&(this.records=e)}}},methods:{changeToCustomText:function(e,t){this.$set(this.records,t,l(l({},e),{},{isCustomData:!e.isCustomData})),this.$emit("change",this.records)},onChangeItem:function(e,t){e.name&&e.data&&t===this.records.length-1&&(this.records=[].concat(o(this.records),[{name:"",data:""}]),this.$emit("change",this.records))},deleteItem:function(e){this.$delete(this.records,e),this.$emit("change",this.records)},disableDelete:function(e){return e===this.records.length-1}}}}}]);