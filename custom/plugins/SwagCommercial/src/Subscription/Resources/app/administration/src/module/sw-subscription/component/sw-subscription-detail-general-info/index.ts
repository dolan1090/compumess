import type Repository from 'src/core/data/repository.data';
import template from './sw-subscription-detail-general-info.html.twig';
import './sw-subscription-detail-general-info.scss';
import type { TEntityCollection, TCriteria, TEntity, ComponentHelper } from '../../../../type/types';
import type { SubscriptionState } from '../../../../state/subscription.store';

const { mapState } = Shopware.Component.getComponentHelper() as ComponentHelper;
const { Criteria } = Shopware.Data;
const { format } = Shopware.Utils;

type Transition = {
    actionName: string;
    fromStateName: string;
    name: string;
    technicalName: string;
    toStateName: string;
    url: string;
}

type TransitionOption = {
    stateName: string,
    id: string | number,
    name: string,
    disabled: boolean,
}

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: [
        'repositoryFactory',
        'stateMachineService',
        'stateStyleDataProviderService',
        'subscriptionApiService',
        'acl',
    ],


    mixins: [
        Shopware.Mixin.getByName('notification'),
    ],

    data(): {
        tagCollection: TEntityCollection<'tag'> | null;
        longDateFormatOptions: Intl.DateTimeFormatOptions;
        shortDateFormatOptions: Intl.DateTimeFormatOptions;
        allTransitions: TEntityCollection<'state_machine_state'> | null;
        possibleTransitions: Transition[];
        newStateMachineState: TEntity<'state_machine_state'> | null;
        } {
        return {
            tagCollection: null,
            longDateFormatOptions: {
                hour: '2-digit',
                minute: '2-digit',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            },
            shortDateFormatOptions: {
                hour: undefined,
                minute: undefined,
                day: '2-digit',
                month: '2-digit',
                year: '2-digit',
            },
            allTransitions: null,
            possibleTransitions: [],
            newStateMachineState: null,
        };
    },

    created(): void {
        this.createdComponent();
    },

    computed: {
        ...mapState<SubscriptionState>('swSubscription', [
            'subscription',
        ]),

        tagRepository(): Repository<'tag'> {
            return this.repositoryFactory.create(
                this.subscription.tags.entity,
                this.subscription.tags.source,
            );
        },

        stateMachineStateRepository(): Repository<'state_machine_state'> {
            return this.repositoryFactory.create('state_machine_state');
        },

        summary(): string {
            const { subscriptionCustomer } = this.subscription;
            return `${this.subscription.subscriptionNumber} - ${subscriptionCustomer.firstName} ${subscriptionCustomer.lastName} (${subscriptionCustomer.email})`;
        },

        totalPrice(): string {
            return this.subscription.convertedOrder.price.totalPrice;
        },

        currencyName(): string {
            return this.subscription.currency.translated.shortName;
        },

        totalRounding(): string {
            return this.subscription.totalRounding.decimals;
        },

        paymentMethodName(): string {
            return this.subscription.paymentMethod.translated.distinguishableName;
        },

        shippingMethodName(): string {
            return this.subscription.shippingMethod.translated.name;
        },

        subscriptionPlanName(): string {
            return this.subscription.subscriptionPlanName;
        },

        subscriptionIntervalName(): string {
            return this.subscription.subscriptionIntervalName;
        },

        createdAt(): string {
            return format.date(this.subscription.createdAt, this.longDateFormatOptions);
        },

        updatedAt(): string {
            return format.date(
                this.subscription.updatedAt ?? this.subscription.createdAt,
                this.shortDateFormatOptions,
            );
        },

        stateUpdatedAt(): string {
            const stateMachineState = this.subscription.stateMachineState;

            return format.date(
                stateMachineState.updatedAt ?? stateMachineState.createdAt,
                this.longDateFormatOptions,
            );
        },

        summarySubCreated(): string {
            return this.$tc('commercial.subscriptions.subscriptions.detail.general.summarySubCreated', 0, {
                date: this.createdAt,
                paymentMethod: this.paymentMethodName,
                shippingMethod: this.shippingMethodName,
            });
        },

        summarySubUpdated(): string {
            return this.$tc('commercial.subscriptions.subscriptions.detail.general.summarySubUpdated', 0, {
                date: this.updatedAt,
            });
        },

        stateDescription(): string {
            return this.$tc('commercial.subscriptions.subscriptions.detail.general.stateDescription', 0, {
                date: this.stateUpdatedAt,
            });
        },

        stateBackgroundStyle(): string {
            return this.stateStyleDataProviderService.getStyle(
                'subscription.state',
                this.newStateMachineState?.technicalName ?? this.subscription.stateMachineState.technicalName,
            ).selectBackgroundStyle;
        },

        statePlaceholder(): string {
            return this.$tc('commercial.subscriptions.subscriptions.detail.general.stateName', 0, {
                name: this.newStateMachineState?.translated?.name ?? this.subscription.stateMachineState.translated.name,
            });
        },

        stateMachineStateCriteria(): TCriteria {
            const criteria = new Criteria(1, null);
            criteria.addSorting(Criteria.sort('name', 'ASC'));
            criteria.addAssociation('stateMachine');
            criteria.addFilter(
                Criteria.equals(
                    'state_machine_state.stateMachine.technicalName',
                    'subscription.state',
                ),
            );

            return criteria;
        },

        transitionOptions(): Array<TransitionOption> {
            if (this.allTransitions === null || this.possibleTransitions.length === 0) {
                return [];
            }

            return this.allTransitions.map((state: TEntity<'state_machine_state'>, idx: number) => {
                const transitionToState = this.possibleTransitions.filter(
                    (transition) => transition.toStateName === state.technicalName,
                )[0];

                return {
                    stateName: state.technicalName,
                    id: !transitionToState ? idx : transitionToState.actionName,
                    name: state.translated?.name ?? state.name,
                    disabled: !transitionToState,
                };
            });
        },
    },

    methods: {
        createdComponent(): void {
            void this.loadAllTransitions();
            void this.loadPossibleTransitions();
        },

        onStateSelected(stateType: string | null, actionName: string | null): void {
            if (!stateType || !actionName) {
                this.createStateChangeErrorNotification(this.$tc('commercial.subscriptions.subscriptions.detail.general.stateErrorNoAction'));
                return;
            }

            this.newStateMachineState = this.subscriptionApiService
                .subscriptionStateTransition(this.subscription.id, actionName)
                .then((response: TEntity<'state_machine_state'>) => this.newStateMachineState = response)
                .catch((e: Error) => this.createStateChangeErrorNotification(e.message))
                .finally(() => this.loadPossibleTransitions());
        },

        async onTagAdd(item: TEntity<'tag'>): Promise<void> {
            await this.tagRepository.assign(item.id);
            this.subscription.tags.add(item);
        },

        async onTagRemove(item: TEntity<'tag'>): Promise<void> {
            await this.tagRepository.delete(item.id);
            this.subscription.tags.remove(item.id);
        },

        async loadAllTransitions(): Promise<void> {
            this.allTransitions = await this.stateMachineStateRepository.search(this.stateMachineStateCriteria);
        },

        async loadPossibleTransitions(): Promise<void> {
            const response = await this.stateMachineService.getState('subscription', this.subscription.id);
            this.possibleTransitions = response.data.transitions;
        },

        createStateChangeErrorNotification(message: string) {
            this.createNotificationError({
                message: this.$tc('commercial.subscriptions.subscriptions.detail.general.stateErrorStateChange', 0, { message }),
            });
        },
    },
});
