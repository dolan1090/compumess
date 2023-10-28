(this["webpackJsonpPluginclassification-customer"]=this["webpackJsonpPluginclassification-customer"]||[]).push([[0],{"0RnA":function(e,t,n){"use strict";n.r(t);n("J+An");var r=n("l6kG");function a(e){return function(e){if(Array.isArray(e))return i(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||function(e,t){if(!e)return;if("string"==typeof e)return i(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return i(e,t)}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function i(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}var s=Shopware.Component;t.default=s.wrapComponentConfig({template:'{% block sw_customer_list_grid_columns %}\n    {% parent %}\n    <template #column-tags="{ item }">\n        <sw-entity-tag-select\n            :entityCollection="item.tags"\n            label=""\n            :value-limit="2"\n            class="sw-customer-card__tag-select"\n            disabled\n            size="medium"\n        />\n    </template>\n\n    <template #bulk-additional>\n        <a\n            v-if="hasToggleKey"\n            class="link link-primary sw-customer-list-grid__button-classify"\n            @click="onClickClassify"\n        >\n            {{ $tc(\'swag-customer-classification.buttonClassify\') }}\n        </a>\n    </template>\n{% endblock %}\n\n{% block sw_customer_list_bulk_edit_modal %}\n    <template\n        #bulk-edit-modal="{ selection }"\n    >\n        <sw-bulk-edit-modal\n            v-if="showBulkEditModal"\n            ref="bulkEditModal"\n            class="sw-customer-bulk-edit-modal"\n            :selection="selection"\n            :bulk-grid-edit-columns="customerColumns"\n            @edit-items="onBulkEditItems"\n            @modal-close="onBulkEditModalClose"\n        >\n            <template #column-tags="{ item }">\n                <sw-entity-tag-select\n                    :entityCollection="item.tags"\n                    label=""\n                    :value-limit="2"\n                    class="sw-customer-card__tag-select"\n                    disabled\n                    size="medium"\n                />\n            </template>\n\n            <template #column-firstName="{ item }">\n                <router-link\n                    :to="{ name: \'sw.customer.detail\', params: { id: item.id } }"\n                    target="_blank"\n                    rel="noreferrer noopener"\n                >\n                    {{ item.lastName }}, {{ item.firstName }}\n                </router-link>\n            </template>\n\n            <template #column-group="{ item }">\n                <sw-label\n                    v-if="item.requestedGroup"\n                    class="sw-customer-list__requested-group-label"\n                    variant="warning"\n                    appearance="pill"\n                >\n                    {{ $tc(\'sw-customer.list.columnGroupRequested\') }} {{ item.requestedGroup.translated.name }}\n                </sw-label>\n                <sw-label\n                    v-else\n                    size="default"\n                    appearance="pill"\n                >\n                    {{ item.group.translated.name }}\n                </sw-label>\n            </template>\n        </sw-bulk-edit-modal>\n    </template>\n{% endblock %}\n',computed:{defaultCriteria:function(){var e=this.$super("defaultCriteria");return e.addAssociation("tags"),e},customerColumns:function(){var e=a(this.getCustomerColumns());return this.hasToggleKey&&e.splice(1,0,{property:"tags",label:"sw-customer.baseForm.labelTags",sortable:!1,allowResize:!0,multiLine:!0}),e},hasToggleKey:function(){return Shopware.License.get(r.a)}},methods:{onClickClassify:function(){Shopware.License.get(r.b)?Shopware.Application.getContainer("init").httpClient.get("_info/config",{headers:{Accept:"application/vnd.api+json",Authorization:"Bearer ".concat(Shopware.Service("loginService").getToken()),"Content-Type":"application/json","sw-license-toggle":r.b}}):this.$router.push({name:"swag.customer.classification.index"})}}})},"J+An":function(e,t,n){var r=n("a6Hm");r.__esModule&&(r=r.default),"string"==typeof r&&(r=[[e.i,r,""]]),r.locals&&(e.exports=r.locals);(0,n("P8hj").default)("f6ff14fc",r,!0,{})},P8hj:function(e,t,n){"use strict";function r(e,t){for(var n=[],r={},a=0;a<t.length;a++){var i=t[a],s=i[0],o={id:e+":"+a,css:i[1],media:i[2],sourceMap:i[3]};r[s]?r[s].parts.push(o):n.push(r[s]={id:s,parts:[o]})}return n}n.r(t),n.d(t,"default",(function(){return f}));var a="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!a)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var i={},s=a&&(document.head||document.getElementsByTagName("head")[0]),o=null,l=0,u=!1,c=function(){},d=null,m="data-vue-ssr-id",p="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function f(e,t,n,a){u=n,d=a||{};var s=r(e,t);return g(s),function(t){for(var n=[],a=0;a<s.length;a++){var o=s[a];(l=i[o.id]).refs--,n.push(l)}t?g(s=r(e,t)):s=[];for(a=0;a<n.length;a++){var l;if(0===(l=n[a]).refs){for(var u=0;u<l.parts.length;u++)l.parts[u]();delete i[l.id]}}}}function g(e){for(var t=0;t<e.length;t++){var n=e[t],r=i[n.id];if(r){r.refs++;for(var a=0;a<r.parts.length;a++)r.parts[a](n.parts[a]);for(;a<n.parts.length;a++)r.parts.push(b(n.parts[a]));r.parts.length>n.parts.length&&(r.parts.length=n.parts.length)}else{var s=[];for(a=0;a<n.parts.length;a++)s.push(b(n.parts[a]));i[n.id]={id:n.id,refs:1,parts:s}}}}function h(){var e=document.createElement("style");return e.type="text/css",s.appendChild(e),e}function b(e){var t,n,r=document.querySelector("style["+m+'~="'+e.id+'"]');if(r){if(u)return c;r.parentNode.removeChild(r)}if(p){var a=l++;r=o||(o=h()),t=w.bind(null,r,a,!1),n=w.bind(null,r,a,!0)}else r=h(),t=C.bind(null,r),n=function(){r.parentNode.removeChild(r)};return t(e),function(r){if(r){if(r.css===e.css&&r.media===e.media&&r.sourceMap===e.sourceMap)return;t(e=r)}else n()}}var v,y=(v=[],function(e,t){return v[e]=t,v.filter(Boolean).join("\n")});function w(e,t,n,r){var a=n?"":r.css;if(e.styleSheet)e.styleSheet.cssText=y(t,a);else{var i=document.createTextNode(a),s=e.childNodes;s[t]&&e.removeChild(s[t]),s.length?e.insertBefore(i,s[t]):e.appendChild(i)}}function C(e,t){var n=t.css,r=t.media,a=t.sourceMap;if(r&&e.setAttribute("media",r),d.ssrId&&e.setAttribute(m,t.id),a&&(n+="\n/*# sourceURL="+a.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(a))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}},a6Hm:function(e,t,n){}}]);