(this["webpackJsonpPluginemployee-management"]=this["webpackJsonpPluginemployee-management"]||[]).push([[8],{vYjj:function(e,n,o){"use strict";o.r(n);n.default=Shopware.Component.wrapComponentConfig({template:'{% block sw_customer_detail_company %}\n<div class="sw-customer-detail-company">\n    {% block sw_customer_detail_company_employees %}\n    <sw-customer-employee-card\n        v-if="acl.can(\'b2b_employee_management.viewer\')"\n        :customer="customer"\n    ></sw-customer-employee-card>\n    {% endblock %}\n\n    {% block sw_customer_detail_company_roles %}\n        <sw-customer-role-card\n            v-if="acl.can(\'b2b_employee_management.viewer\')"\n            :customer="customer"\n        ></sw-customer-role-card>\n    {% endblock %}\n</div>\n{% endblock %}\n',inject:["acl"],props:{customer:{type:Object,required:!0}}})}}]);