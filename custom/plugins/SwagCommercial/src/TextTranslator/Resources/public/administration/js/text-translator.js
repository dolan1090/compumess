!function(e){var n={};function t(s){if(n[s])return n[s].exports;var i=n[s]={i:s,l:!1,exports:{}};return e[s].call(i.exports,i,i.exports,t),i.l=!0,i.exports}t.m=e,t.c=n,t.d=function(e,n,s){t.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:s})},t.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},t.t=function(e,n){if(1&n&&(e=t(e)),8&n)return e;if(4&n&&"object"==typeof e&&e&&e.__esModule)return e;var s=Object.create(null);if(t.r(s),Object.defineProperty(s,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var i in e)t.d(s,i,function(n){return e[n]}.bind(null,i));return s},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},t.p=(window.__sw__.assetPath + '/bundles/texttranslator/'),t(t.s="OJWB")}({CNYL:function(e){e.exports=JSON.parse('{"sw-settings-translator":{"general":{"title":"Bewertungen","notificationGeneric":"Ein Fehler ist aufgetreten."},"detail":{"title":"Bewertungen","buttonSave":"Speichern","buttonCancel":"Abbrechen"}}}')},OJWB:function(e,n,t){"use strict";t.r(n);var s=Shopware.Mixin;Shopware.License.get("REVIEW_TRANSLATOR-1649854")&&Shopware.Component.register("sw-settings-translator-index",{template:'<sw-page class="sw-settings-reviews-index">\n    \n    {% block sw_settings_reviews_search_bar %}\n        <template #search-bar>\n            <sw-search-bar />\n        </template>\n    {% endblock %}\n\n    \n    {% block sw_settings_reviews_header %}\n        <template #smart-bar-header>\n            <h2>{{ $tc(\'sw-settings.index.title\') }} <sw-icon\n                    name="regular-chevron-right-xs"\n                    small\n                /> {{ $tc(\'sw-settings-translator.general.title\') }} </h2>\n        </template>\n    {% endblock %}\n\n    \n    {% block sw_settings_reviews_smart_bar_actions %}\n        <template #smart-bar-actions>\n            \n            {% block sw_settings_reviews_actions_save %}\n                <sw-button-process\n                    class="sw-settings-reviews__save-action"\n                    :is-loading="isLoading"\n                    :process-success="isSaveSuccessful"\n                    :disabled="isLoading"\n                    variant="primary"\n                    @process-finish="saveFinish"\n                    @click="onSave"\n                >\n                    {{ $tc(\'sw-settings-translator.detail.buttonSave\') }}\n                </sw-button-process>\n            {% endblock %}\n        </template>\n    {% endblock %}\n\n    \n    {% block sw_settings_reviews_content %}\n        <template #content>\n            <sw-card-view>\n                <sw-skeleton v-if="isLoading" />\n\n                <sw-system-config\n                    v-show="!isLoading"\n                    ref="systemConfig"\n                    sales-channel-switchable\n                    domain="TextTranslator.reviewTranslator"\n                    @loading-changed="onLoadingChanged"\n                />\n\n            </sw-card-view>\n        </template>\n    {% endblock %}\n</sw-page>\n',data:function(){return{isLoading:!1,isSaveSuccessful:!1}},metaInfo:function(){return{title:this.$createTitle()}},mixins:[s.getByName("notification")],methods:{saveFinish:function(){this.isSaveSuccessful=!1},onSave:function(){var e=this;this.isSaveSuccessful=!1,this.isLoading=!0,this.$refs.systemConfig.saveAll().then((function(){e.isLoading=!1,e.isSaveSuccessful=!0})).catch((function(n){e.isLoading=!1,e.createNotificationError({message:n})}))},onLoadingChanged:function(e){this.isLoading=e}}});var i=t("CNYL"),a=t("pIAG");Shopware.License.get("REVIEW_TRANSLATOR-1649854")&&(Shopware.Locale.extend("de-DE",i),Shopware.Locale.extend("en-GB",a)),Shopware.License.get("REVIEW_TRANSLATOR-1649854")&&Shopware.Module.register("sw-settings-translator",{type:"plugin",name:"sw-settings-translator",title:"sw-settings-translator.general.title",icon:"regular-cog",color:"#9AA8B5",routes:{index:{component:"sw-settings-translator-index",path:"index",meta:{parentPath:"sw.settings.index"}}},settingsItem:{group:"shop",to:"sw.settings.translator.index",icon:"regular-star",name:"swag-example.general.mainMenuItemGeneral"}})},pIAG:function(e){e.exports=JSON.parse('{"sw-settings-translator":{"general":{"title":"Reviews","notificationGeneric":"An error occurred."},"detail":{"title":"Reviews","buttonSave":"Save","buttonCancel":"Cancel"}}}')}});