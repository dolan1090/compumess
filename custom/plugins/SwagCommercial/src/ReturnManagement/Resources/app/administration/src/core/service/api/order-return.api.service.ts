import type { LoginService } from '@administration/core/service/login.service';
import type { AxiosInstance, AxiosResponse } from 'axios';
import { TRAP_KEY_1 } from '../../../config';

interface ReturnItemPayload {
    orderLineItemId: string,
    quantity: number,
    internalComment: string,
}

interface CreateReturnPayload {
    lineItems: Array<ReturnItemPayload>,
}

interface AddedReturnLineItemsPayload {
    orderLineItems: Array<ReturnItemPayload>,
}

interface ChangeStatusPayload {
    ids: string[]
}

const ApiService = Shopware.Classes.ApiService;

/**
 * Gateway for the API end point "order/return"
 * @class
 * @extends ApiService
 * @package checkout
 */
export default class OrderReturnApiService extends ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService, apiEndpoint = 'return') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'orderReturnApiService';
    }

    create(orderId: string, payload: CreateReturnPayload, versionId: string, additionalParams = {}, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = Object.assign(ApiService.getVersionHeader(versionId), this.getBasicHeaders(additionalHeaders));
        if (Shopware.License.get(TRAP_KEY_1)) {
            return this.httpClient.get(
                '_info/me',
                {
                    headers: {
                        ...this.getBasicHeaders(),
                        'sw-license-toggle': TRAP_KEY_1,
                    },
                },
            );
        }

        return this.httpClient.post(`_proxy/order/${orderId}/return`, payload, {
            additionalParams,
            headers,
        });
    }

    addItems(orderId: string, orderReturnId: string, payload: AddedReturnLineItemsPayload, versionId: string, additionalParams = {}, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = Object.assign(ApiService.getVersionHeader(versionId), this.getBasicHeaders(additionalHeaders));
        if (Shopware.License.get(TRAP_KEY_1)) {
            return this.httpClient.get(
                '_info/me',
                {
                    headers: {
                        ...this.getBasicHeaders(),
                        'sw-license-toggle': TRAP_KEY_1,
                    },
                },
            );
        }

        return this.httpClient.post(`_action/order/${orderId}/order-return/${orderReturnId}/add-items`, payload, {
            additionalParams,
            headers,
        });
    }

    changeStateOrderReturn(orderReturnId: string, transition: string, versionId: string, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = Object.assign(ApiService.getVersionHeader(versionId), this.getBasicHeaders(additionalHeaders));
        if (Shopware.License.get(TRAP_KEY_1)) {
            return this.httpClient.get(
                '_info/me',
                {
                    headers: {
                        ...this.getBasicHeaders(),
                        'sw-license-toggle': TRAP_KEY_1,
                    },
                },
            );
        }

        return this.httpClient.post(`_action/state-machine/order_return/${orderReturnId}/state/${transition}`, {}, { headers });
    }

    changeStateOrderLineItem(transition: string, payload: ChangeStatusPayload, versionId: string, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = Object.assign(ApiService.getVersionHeader(versionId), this.getBasicHeaders(additionalHeaders));
        if (Shopware.License.get(TRAP_KEY_1)) {
            return this.httpClient.get(
                '_info/me',
                {
                    headers: {
                        ...this.getBasicHeaders(),
                        'sw-license-toggle': TRAP_KEY_1,
                    },
                },
            );
        }

        return this.httpClient.post(`_action/order-line-item/state/${transition}`,
            payload,
            {
                headers,
            },
        );
    }

    changeStateOrderReturnLineItem(transition: string, payload: ChangeStatusPayload, versionId:string, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = Object.assign(ApiService.getVersionHeader(versionId), this.getBasicHeaders(additionalHeaders));
        if (Shopware.License.get(TRAP_KEY_1)) {
            return this.httpClient.get(
                '_info/me',
                {
                    headers: {
                        ...this.getBasicHeaders(),
                        'sw-license-toggle': TRAP_KEY_1,
                    },
                },
            );
        }

        return this.httpClient.post(`_action/order-return-line-item/state/${transition}`,
            payload,
            {
                headers,
            });
    }

    recalculateRefundAmount(returnId: string, versionId: string, additionalParams = {}, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = Object.assign(ApiService.getVersionHeader(versionId), this.getBasicHeaders(additionalHeaders));
        if (Shopware.License.get(TRAP_KEY_1)) {
            return this.httpClient.get(
                '_info/me',
                {
                    headers: {
                        ...this.getBasicHeaders(),
                        'sw-license-toggle': TRAP_KEY_1,
                    },
                },
            );
        }

        return this.httpClient.post(`_action/order/return/${returnId}/calculate`,
            {},
            {
                additionalParams,
                headers,
            });
    }
}
