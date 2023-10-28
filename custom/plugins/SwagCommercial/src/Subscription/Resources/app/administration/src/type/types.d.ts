import type { LineItem } from 'src/module/sw-order/order.types';
import type { mapState } from 'vuex';

/**
 * @package checkout#
 *
 * @public
 */
export type { Entity as TEntity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
export type { default as TEntityCollection } from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';
export type { default as TCriteria } from '@shopware-ag/admin-extension-sdk/es/data/Criteria';

// Shopware.Component.getComponentHelper() is not type safe
export type ComponentHelper = {
    [helperName: string]: unknown,
    mapState: mapState,
    mapPropertyErrors: (entityName: string, properties: any[]) => any,
}

export type SortDirection = 'ASC' | 'DESC' | undefined;

export interface DataGridColumn {
    property: string,
    dataIndex?: string,
    label?: string,
    visible?: boolean,
    multiLine?: boolean,
    routerLink?: string,
    allowResize?: boolean,
    inlineEdit?: string,
    primary?: boolean,
    align?: 'left' | 'right' | 'center',
    width?: string,
}

export interface DurationObject {
    M: number,
    W: number,
    D: number,
}

export interface DateInterval {
    frequency: number,
    unit: string,
}

export interface CronInterval {
    daysOfMonth: string[],
    monthsOfYear: string[],
    daysOfWeek: string[],
}

export interface GeneratedIntervalPreview {
    timestamps: number[],
}

export type SubscriptionConvertedOrder = {
    price: {
        extensions: Array<any>
        netPrice: number,
        totalPrice: number,
        positionPrice: number,
        rawTotal: number,
        taxStatus: 'gross' | 'net' | 'tax-free',
        calculatedTaxes: Array<{
            tax: number,
            taxRate: number,
            price: number,
            extensions: Array<any>
        }>,
        taxRules: Array<{
            taxRate: number,
            percentage: number,
            extensions: Array<any>
        }>,
    },
    shippingCosts: {
        extensions: Array<any>
        unitPrice: number,
        quantity: number,
        totalPrice: number,
    },
    stateId: string,
    currencyId: string,
    currencyFactor: number,
    salesChannelId: string,
    orderDateTime: string,
    itemRounding: CashRoundingConfig,
    totalRounding: CashRoundingConfig,
    billingAddressId: string,
    languageId: string,
    ruleIds: Array<string>,
    addresses: Array<{
        id: string,
        city: string,
        street: string,
        zipcode: string,
        lastName: string,
        countryId: string,
        firstName: string,
        salutationId: string
    }>
    lineItems: Array<LineItem>
}
