export default class RuleBuilderPreviewService extends Shopware.Classes.ApiService {
    constructor(httpClient, loginService) {
        super(httpClient, loginService, null, 'application/json');
        this.name = 'ruleBuilderPreviewService';
    }

    /**
     * @param orderId: String
     * @param conditions: Array
     * @param dateTime: String
     * @param additionalHeaders: Object
     * @returns {*} - ApiService.handleResponse(response)
     */
    preview(orderId, conditions, dateTime = null, additionalHeaders = {}) {
        if (Shopware.License.get('RULE_BUILDER-5423050')) {
            return this.httpClient.get(
                'api/_info/me',
                {
                    headers: {
                        ... this.getBasicHeaders(),
                        'sw-license-toggle': 'RULE_BUILDER-5423050',
                    },
                },
            );
        }

        return this.httpClient.post(
            `_admin/rule-builder-preview/${orderId}`,
            {
                conditions,
                dateTime,
            },
            {
                headers: this.getBasicHeaders(additionalHeaders),
            },
        ).then(response => Shopware.Classes.ApiService.handleResponse(response));
    }
}
