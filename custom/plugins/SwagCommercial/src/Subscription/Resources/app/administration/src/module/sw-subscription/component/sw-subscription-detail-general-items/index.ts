import template from './sw-subscription-detail-general-items.html.twig';
import './sw-subscription-detail-general-items.scss';
import type { ComponentHelper, DataGridColumn, SubscriptionConvertedOrder } from '../../../../type/types';
import type { CalculatedTax, LineItem } from 'src/module/sw-order/order.types';
import type { SubscriptionState } from '../../../../state/subscription.store';

enum LineItemType {
    PRODUCT = 'product',
    CREDIT = 'credit',
    CUSTOM = 'custom',
    PROMOTION = 'promotion',
}

const { mapState } = Shopware.Component.getComponentHelper() as ComponentHelper;
const { format, array, object } = Shopware.Utils;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: [
        'repositoryFactory',
    ],

    computed: {
        ...mapState<SubscriptionState>('swSubscription', [
            'subscription',
            'isLoading',
        ]),

        taxStatus(): string {
            return this.order.price.taxStatus;
        },

        unitPriceLabel(): string {
            let transTax = 'columnPriceGross';

            if (this.taxStatus === 'net') {
                transTax = 'columnPriceNet';
            }

            if (this.taxStatus === 'tax-free') {
                transTax = 'columnPriceTaxFree';
            }

            return this.$tc(`commercial.subscriptions.subscriptions.detail.general.itemGrid.${transTax}`);
        },

        order(): SubscriptionConvertedOrder {
            return this.subscription.convertedOrder;
        },

        deliveryDiscounts() {
            return array.slice(this.order.deliveries, 1) || [];
        },

        sortedCalculatedTaxes(): CalculatedTax[] {
            return object.cloneDeep(this.order.price.calculatedTaxes)
                .sort((prev: CalculatedTax, current: CalculatedTax) => prev.taxRate - current.taxRate)
                .filter((price: CalculatedTax) => price.tax !== 0);
        },

        displayRounded(): boolean {
            return this.subscription.totalRounding.interval !== 0.01
                || this.subscription.totalRounding.decimals !== this.subscription.itemRounding.decimals;
        },

        lineItemColumns(): DataGridColumn[] {
            const columns: DataGridColumn[] = [{
                property: 'quantity',
                label: 'commercial.subscriptions.subscriptions.detail.general.itemGrid.columnQuantity',
                align: 'right',
                width: '90px',
            }, {
                property: 'label',
                label: 'commercial.subscriptions.subscriptions.detail.general.itemGrid.columnProductName',
                primary: true,
                multiLine: true,
            }, {
                property: 'payload.productNumber',
                label: 'commercial.subscriptions.subscriptions.detail.general.itemGrid.columnProductNumber',
                visible: false,
            }, {
                property: 'price.unitPrice',
                label: this.unitPriceLabel,
                align: 'right',
                width: '120px',
            }];

            if (this.taxStatus !== 'tax-free') {
                columns.push({
                    property: 'price.taxRules[0]',
                    label: 'commercial.subscriptions.subscriptions.detail.general.itemGrid.columnTax',
                    align: 'right',
                    width: '90px',
                });
            }

            return [...columns, {
                property: 'price.totalPrice',
                label: this.taxStatus === 'gross'
                    ? 'commercial.subscriptions.subscriptions.detail.general.itemGrid.columnTotalPriceGross'
                    : 'commercial.subscriptions.subscriptions.detail.general.itemGrid.columnTotalPriceNet',
                align: 'right',
                width: '120px',
            }];
        },
    },

    methods: {
        isProductItem(item: LineItem): boolean {
            return LineItemType.PRODUCT === item.type;
        },

        isPromotionItem(item: LineItem) {
            return item.type === LineItemType.PROMOTION;
        },

        isCreditItem(item: LineItem): boolean {
            return item.type === LineItemType.CREDIT;
        },

        hasChildren(item: LineItem): boolean {
            return !!item.children && item.children.length > 0;
        },

        hasMultipleTaxes(item: LineItem): boolean {
            return !!item.price?.calculatedTaxes && item.price.calculatedTaxes.length > 1;
        },

        hasMultipleTaxRules(item: LineItem): boolean {
            return !!item.price?.taxRules && item.price.taxRules.length > 1;
        },

        showTaxValue(item: LineItem): string {
            return (this.isCreditItem(item) || this.isPromotionItem(item)) && this.hasMultipleTaxRules(item)
                ? this.$tc('commercial.subscriptions.subscriptions.detail.general.itemGrid.textCreditTax')
                : `${item.price?.taxRules[0].taxRate ?? '-'} %`;
        },

        getTooltipTaxDetail(item: LineItem) {
            const sortTaxes = [...item.price?.calculatedTaxes ?? []]
                .sort((prev, current) => prev.taxRate - current.taxRate);

            const decorateTaxes = sortTaxes
                .map((taxItem) => this.$tc('commercial.subscriptions.subscriptions.detail.general.itemGrid.taxDetail', 0, {
                    taxRate: taxItem.taxRate,
                    // @ts-expect-error - currently format.currency's third parameter is not correctly typed as optional
                    tax: format.currency(
                        taxItem.tax,
                        this.subscription.currency.shortName,
                    ),
                }));

            return {
                showDelay: 300,
                message: `${this.$tc('commercial.subscriptions.subscriptions.detail.general.itemGrid.tax')}<br>${decorateTaxes.join('<br>')}`,
            };
        },
    },
});
