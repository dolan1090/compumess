!function(e){function n(n){for(var t,r,i=n[0],l=n[1],a=0,s=[];a<i.length;a++)r=i[a],Object.prototype.hasOwnProperty.call(o,r)&&o[r]&&s.push(o[r][0]),o[r]=0;for(t in l)Object.prototype.hasOwnProperty.call(l,t)&&(e[t]=l[t]);for(u&&u(n);s.length;)s.shift()()}var t={},r={"flow-sharing":0},o={"flow-sharing":0};function i(n){if(t[n])return t[n].exports;var r=t[n]={i:n,l:!1,exports:{}};return e[n].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.e=function(e){var n=[];r[e]?n.push(r[e]):0!==r[e]&&{0:1,1:1,2:1,3:1}[e]&&n.push(r[e]=new Promise((function(n,t){for(var o="static/css/"+({}[e]||e)+".css",l=i.p+o,a=document.getElementsByTagName("link"),s=0;s<a.length;s++){var u=(d=a[s]).getAttribute("data-href")||d.getAttribute("href");if("stylesheet"===d.rel&&(u===o||u===l))return n()}var c=document.getElementsByTagName("style");for(s=0;s<c.length;s++){var d;if((u=(d=c[s]).getAttribute("data-href"))===o||u===l)return n()}var f=document.createElement("link");f.rel="stylesheet",f.type="text/css";f.onerror=f.onload=function(o){if(f.onerror=f.onload=null,"load"===o.type)n();else{var i=o&&("load"===o.type?"missing":o.type),a=o&&o.target&&o.target.href||l,s=new Error("Loading CSS chunk "+e+" failed.\n("+a+")");s.code="CSS_CHUNK_LOAD_FAILED",s.type=i,s.request=a,delete r[e],f.parentNode.removeChild(f),t(s)}},f.href=l,document.head.appendChild(f)})).then((function(){r[e]=0})));var t=o[e];if(0!==t)if(t)n.push(t[2]);else{var l=new Promise((function(n,r){t=o[e]=[n,r]}));n.push(t[2]=l);var a,s=document.createElement("script");s.charset="utf-8",s.timeout=120,i.nc&&s.setAttribute("nonce",i.nc),s.src=function(e){return i.p+"static/js/"+({}[e]||e)+".js"}(e);var u=new Error;a=function(n){s.onerror=s.onload=null,clearTimeout(c);var t=o[e];if(0!==t){if(t){var r=n&&("load"===n.type?"missing":n.type),i=n&&n.target&&n.target.src;u.message="Loading chunk "+e+" failed.\n("+r+": "+i+")",u.name="ChunkLoadError",u.type=r,u.request=i,t[1](u)}o[e]=void 0}};var c=setTimeout((function(){a({type:"timeout",target:s})}),12e4);s.onerror=s.onload=a,document.head.appendChild(s)}return Promise.all(n)},i.m=e,i.c=t,i.d=function(e,n,t){i.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:t})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,n){if(1&n&&(e=i(e)),8&n)return e;if(4&n&&"object"==typeof e&&e&&e.__esModule)return e;var t=Object.create(null);if(i.r(t),Object.defineProperty(t,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var r in e)i.d(t,r,function(n){return e[n]}.bind(null,r));return t},i.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(n,"a",n),n},i.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},i.p=(window.__sw__.assetPath + '/bundles/flowsharing/'),i.oe=function(e){throw console.error(e),e};var l=this["webpackJsonpPluginflow-sharing"]=this["webpackJsonpPluginflow-sharing"]||[],a=l.push.bind(l);l.push=n,l=l.slice();for(var s=0;s<l.length;s++)n(l[s]);var u=a;i(i.s="vtFI")}({"7KzZ":function(e){e.exports=JSON.parse('{"sw-flow-sharing":{"uploadButton":"Flow hochladen","downloadButton":"Herunterladen","downloadModal":{"title":"Flow herunterladen","description":"Diese Flow-Datei enthält alle Sequenzen sowie die Konfigurationen, die in Bedingungen und Aktionen verwendet werden. Beim Hochladen in ein Fremdsystem werden fehlende Daten angelegt.<br><br>Verweise auf Kategorien, Produkte und Eigenschaften können in der Flow-Datei weggelassen werden. Beim Hochladen in ein Fremdsystem müssen diese fehlende Referenzen neu zugeordnet werden.","dataIncluded":"In dieser Datei enthaltene Daten","references":"In dieser Datei enthaltene Referenzen","isNotIncludedReferences":"Keine Referenzen enthalten","downloadButton":"Flow herunterladen","ruleLabel":"Regel: {ruleName}","mailTemplateLabel":"E-Mail-Vorlagen: {mail}"},"uploadModal":{"title":"Flow hochladen","description":"Dieser Flow enthält neue Regeln und E-Mail-Templates. Wähle die Regeln und E-Mail-Vorlagen aus, die in Deinem System erstellt werden sollen:","uploadLabel":"Wähle eine json-Datei aus, um ein Flow-Template hochzuladen.","uploadFileLabel":"Datei hochladen","rules":"{rules} Regel | {rules} Regeln","tags":"{tags} Tag | {tags} Tags","emails":"{emails} E-Mail-Template | {emails} E-Mail-Templates","dataWillBeCreated":"wird erstellt, wenn Du diesen Flow hochlädst. | werden erstellt, wenn Du diesen Flow hochlädst.","references":"{references} Datenverweis | {references} Datenverweise","referencesNeedToAssign":"muss neu zugewiesen werden, wenn Du diesen Flow hochlädst. | müssen neu zugewiesen werden, wenn Du diesen Flow hochlädst.","ruleLabel":"Regel: {ruleName}","mailTemplateLabel":"E-Mail-Vorlagen: {mail}","warningAlert":{"description":"Dein System ist aktuell nicht kompatibel mit dem hochgeladenen Flow.","shopwareVersionLabel":"Erforderliche Shopware-Version:","extensionsLabel":"Erforderliche Verlängerung:","ruleConflictLabel":"Regelkonflikte","ruleConflictDescription":"Die Flow-Datei “{flowFile}” enthält Regeln, die bereits in Deinem System vorhanden sind. Diese Regeln verwenden Bedingungen, die sich von den Bedingungen in Deinen lokalen Regeln unterscheiden. Welche Regeln möchtest Du verwenden?"},"affectedRules":"Betroffene Regeln","keepLocalRulesLabel":"Lokale Regeln beibehalten","keepLocalRulesDescription":"Deine lokalen Regeln werden beibehalten, aber der hochgeladene Flow wird möglicherweise nicht wie vorgesehen funktionieren. Bitte überprüfe die betroffenen Regeln innerhalb des Flows.","overrideLocalRulesLabel":"Lokale Regeln überschreiben","overrideLocalRulesDescription":"Deine lokalen Regeln werden mit Regeln aus der Flow-Datei “{flowFile}” überschrieben. Bitte überprüfe die Zuweisungen aller betroffenen Regeln und ihre Bedingungen auf mögliche Änderungen."},"importError":{"invalidActionHeading":"Ungültige Aktion","invalidActionText":"Diese Aktion verweist auf Daten, die in Deinem System nicht gefunden werden konnten.","invalidRuleHeading":"Ungültige Regel","invalidRuleText":"Diese Regel verweist auf Daten, die in Deinem System nicht gefunden wurden.","missingRuleText":"Diese Regel konnte in Deinem System nicht gefunden werden.","textAssignCustomerGroup":"Bitte Kundengruppe neu zuweisen:","textCustomerGroup":"Kundengruppe:","textMissingObject":"Die folgenden Daten konnten in Deinem System nicht gefunden werden:","description":"Ungespeicherte Änderungen vorhanden. Willst Du die Seite trotzdem verlassen?","confirmButton":"Seite verlassen"},"notification":{"messageDownloadSuccess":"Flow heruntergeladen.","messageDownloadError":"Flow konnte nicht heruntergeladen werden."}}}')},"7hmI":function(e){e.exports=JSON.parse('{"sw-flow-sharing":{"uploadButton":"Upload flow","downloadButton":"Download","downloadModal":{"title":"Download flow","description":"This flow file will include all sequences and the configurations used in its conditions and actions. When uploaded to a foreign system, missing data will be created.<br><br>References to categories, products and properties can be excluded from the flow file. When uploaded to a foreign system, these missing references will have to be reassigned.","dataIncluded":"Data included in this file","references":"References included in this file","isNotIncludedReferences":"No references included","downloadButton":"Download flow","ruleLabel":"Rule: {ruleName}","mailTemplateLabel":"Email template: {mail}"},"uploadModal":{"title":"Upload flow","description":"This flow contains new rules and email templates. Select those rules and email templates that will be created in your system:","uploadLabel":"Please select a json file to upload a flow template","uploadFileLabel":"Upload file","rules":"{rules} rule | {rules} rules","tags":"{tags} tag | {tags} tags","emails":"{emails} email template | {emails} email templates","dataWillBeCreated":"will be created when uploading this flow.","references":"{references} reference| {references} references","referencesNeedToAssign":"needs to be reassigned when uploading this flow. | need to be reassigned when uploading this flow.","ruleLabel":"Rule: {ruleName}","mailTemplateLabel":"Email template: {mail}","warningAlert":{"description":"Your system is currently not compatible with the uploaded flow.","shopwareVersionLabel":"Required Shopware version:","extensionsLabel":"Required extension:","ruleConflictLabel":"Conflicting rules","ruleConflictDescription":"The flow file “{flowFile}” contains rules that already exist in your system. These rules use conditions that are different from conditions used in your local rules. Which rules do you want to use?"},"affectedRules":"Affected rules","keepLocalRulesLabel":"Keep local rules","keepLocalRulesDescription":"Your local rules will remain as they are, but the uploaded flow will potentially not work as intended. Please double-check the affected rules within the flow.","overrideLocalRulesLabel":"Overwrite local rules","overrideLocalRulesDescription":"Your local rules will be overwritten with rules from the flow file “{flowFile}”. Make sure to double-check the assignments of all affected rules and their conditions for potential changes."},"importError":{"invalidActionHeading":"Invalid Action","invalidActionText":"This action references data that could not be found in your system.","invalidRuleHeading":"Invalid Rule","invalidRuleText":"This rule references data that was not found in your system.","missingRuleText":"This rule could not be found in your system.","textAssignCustomerGroup":"Please reassign the customer group:","textCustomerGroup":"Customer group:","textMissingObject":"The following data could not be found in your system:","description":"There are unsaved changes. Are you sure you want to leave this page without saving?","confirmButton":"Leave page"},"notification":{"messageDownloadSuccess":"Flow has been downloaded.","messageDownloadError":"The flow could not be downloaded."}}}')},flwm:function(e,n,t){"use strict";t.d(n,"a",(function(){return r})),t.d(n,"b",(function(){return o})),t.d(n,"c",(function(){return i}));var r=Object.freeze({ADD_TAG:"action.add.tag",ADD_ORDER_TAG:"action.add.order.tag",ADD_CUSTOMER_TAG:"action.add.customer.tag",REMOVE_TAG:"action.remove.tag",REMOVE_ORDER_TAG:"action.remove.order.tag",REMOVE_CUSTOMER_TAG:"action.remove.customer.tag",SET_ORDER_STATE:"action.set.order.state",GENERATE_DOCUMENT:"action.generate.document",MAIL_SEND:"action.mail.send",STOP_FLOW:"action.stop.flow",SET_ORDER_CUSTOM_FIELD:"action.set.order.custom.field",SET_CUSTOMER_CUSTOM_FIELD:"action.set.customer.custom.field",SET_CUSTOMER_GROUP_CUSTOM_FIELD:"action.set.customer.group.custom.field",CHANGE_CUSTOMER_GROUP:"action.change.customer.group",CHANGE_CUSTOMER_STATUS:"action.change.customer.status",ADD_CUSTOMER_AFFILIATE_AND_CAMPAIGN_CODE:"action.add.customer.affiliate.and.campaign.code",ADD_ORDER_AFFILIATE_AND_CAMPAIGN_CODE:"action.add.order.affiliate.and.campaign.code",APP_FLOW_ACTION:"action.app.flow"}),o=Object.freeze({"action.add.order.tag":"tag","action.add.customer.tag":"tag","action.remove.order.tag":"tag","action.remove.customer.tag":"tag","action.change.customer.group":"customer_group","action.set.customer.custom.field":"custom_field","action.set.order.custom.field":"custom_field","action.set.customer.group.custom.field":"custom_field","action.mail.send":"mail_template"}),i=Object.freeze({"action.add.order.tag":"tag","action.add.customer.tag":"customerTag","action.remove.order.tag":"orderTag","action.remove.customer.tag":"customerTag","action.change.customer.group":"customerGroup","action.change.customer.status":"customerStatus","action.set.customer.custom.field":"customerCustomField","action.set.order.custom.field":"orderCustomField","action.set.customer.group.custom.field":"customerGroupCustomField","action.mail.send":"emailTemplate"})},vtFI:function(e,n,t){"use strict";t.r(n);t("flwm");var r=t("7KzZ"),o=t("7hmI");Shopware.Locale.extend("de-DE",r),Shopware.Locale.extend("en-GB",o);function i(e){return(i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function l(e,n){var t=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);n&&(r=r.filter((function(n){return Object.getOwnPropertyDescriptor(e,n).enumerable}))),t.push.apply(t,r)}return t}function a(e){for(var n=1;n<arguments.length;n++){var t=null!=arguments[n]?arguments[n]:{};n%2?l(Object(t),!0).forEach((function(n){s(e,n,t[n])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(t)):l(Object(t)).forEach((function(n){Object.defineProperty(e,n,Object.getOwnPropertyDescriptor(t,n))}))}return e}function s(e,n,t){return(n=d(n))in e?Object.defineProperty(e,n,{value:t,enumerable:!0,configurable:!0,writable:!0}):e[n]=t,e}function u(e,n){if(!(e instanceof n))throw new TypeError("Cannot call a class as a function")}function c(e,n){for(var t=0;t<n.length;t++){var r=n[t];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,d(r.key),r)}}function d(e){var n=function(e,n){if("object"!==i(e)||null===e)return e;var t=e[Symbol.toPrimitive];if(void 0!==t){var r=t.call(e,n||"default");if("object"!==i(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===n?String:Number)(e)}(e,"string");return"symbol"===i(n)?n:String(n)}function f(e,n){return(f=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,n){return e.__proto__=n,e})(e,n)}function m(e){var n=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var t,r=h(e);if(n){var o=h(this).constructor;t=Reflect.construct(r,arguments,o)}else t=r.apply(this,arguments);return p(this,t)}}function p(e,n){if(n&&("object"===i(n)||"function"==typeof n))return n;if(void 0!==n)throw new TypeError("Derived constructors may only return object or undefined");return function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e)}function h(e){return(h=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var g=Shopware.Classes.ApiService,w=function(e){!function(e,n){if("function"!=typeof n&&null!==n)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(n&&n.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),n&&f(e,n)}(i,e);var n,t,r,o=m(i);function i(e,n){var t,r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"flow-sharing";return u(this,i),(t=o.call(this,e,n,r)).name="flowSharingService",t}return n=i,(t=[{key:"downloadFlow",value:function(e){return Shopware.License.get("FLOW_BUILDER-2000923")?this.httpClient.get("api/_info/me",{headers:a(a({},this.getBasicHeaders()),{},{"sw-license-toggle":"FLOW_BUILDER-2000923"})}):this.httpClient.get("/_admin/".concat(this.getApiBasePath(),"/download/").concat(e),{headers:this.getBasicHeaders()}).then((function(e){return g.handleResponse(e)}))}},{key:"checkRequirements",value:function(e){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},t=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},r=n,o=this.getBasicHeaders(t);return this.httpClient.post("/_admin/".concat(this.getApiBasePath(),"/check-requirements"),e,{params:r,headers:o}).then((function(e){return g.handleResponse(e)}))}}])&&c(n.prototype,t),r&&c(n,r),Object.defineProperty(n,"prototype",{writable:!1}),i}(g);Shopware.Component.override("sw-flow-index",(function(){return t.e(11).then(t.bind(null,"9EwY"))})),Shopware.Component.override("sw-flow-detail",(function(){return t.e(10).then(t.bind(null,"ei7U"))})),Shopware.Component.override("sw-flow-list",(function(){return t.e(12).then(t.bind(null,"RXmm"))})),Shopware.Component.override("sw-flow-sequence-action",(function(){return t.e(8).then(t.bind(null,"CMzL"))})),Shopware.Component.override("sw-flow-sequence-condition",(function(){return t.e(9).then(t.bind(null,"alMt"))})),Shopware.Component.override("sw-flow-change-customer-group-modal",(function(){return t.e(4).then(t.bind(null,"QfLs"))})),Shopware.Component.override("sw-flow-mail-send-modal",(function(){return t.e(5).then(t.bind(null,"MLfy"))})),Shopware.Component.override("sw-flow-tag-modal",(function(){return t.e(7).then(t.bind(null,"B6Kp"))})),Shopware.Component.override("sw-flow-set-entity-custom-field-modal",(function(){return t.e(6).then(t.bind(null,"r0RG"))})),Shopware.Component.register("sw-flow-sequence-error",(function(){return t.e(2).then(t.bind(null,"gFUF"))})),Shopware.Component.register("sw-flow-sequence-modal-error",(function(){return t.e(3).then(t.bind(null,"oKA/"))})),Shopware.Component.register("sw-flow-download-modal",(function(){return t.e(0).then(t.bind(null,"Lqh6"))})),Shopware.Component.register("sw-flow-upload-modal",(function(){return t.e(1).then(t.bind(null,"MUxG"))}));var b=Shopware,v=b.Application,y=b.State,D=b.Service;y.registerModule("swFlowSharingState",{namespaced:!0,state:{flow:{},dataIncluded:{},referenceIncluded:{}},mutations:{setFlow:function(e,n){e.flow=n},setDataIncluded:function(e,n){e.dataIncluded=n},setReferenceIncluded:function(e,n){e.referenceIncluded=n},removeCurrentFlow:function(e){e.flow={eventName:"",sequences:[]}},removeReferenceIncluded:function(e){e.referenceIncluded={}},removeDataIncluded:function(e){e.dataIncluded={}}},actions:{resetFlowSharingState:function(e){var n=e.commit;n("removeCurrentFlow"),n("removeReferenceIncluded"),n("removeDataIncluded")}}}),D().register("flowSharingService",(function(){var e=v.getContainer("init");return new w(e.httpClient,D("loginService"))}))}});