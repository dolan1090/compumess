import template from './sw-flow-delay-action.html.twig';
import './sw-flow-delay-action.scss';
import {DELAY_OPTIONS, CUSTOM_TIME, SEQUENCE_TYPES} from '../../../../constant/sw-flow-delay.constant';
import {DelayType, DelayAction, DelayConfig, Sequence, WarningConfig} from '../../../../type/types';

const { Component, State } = Shopware;
const utils = Shopware.Utils;
const { mapGetters, mapState } = Component.getComponentHelper();

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    inject: ['flowBuilderService'],

    template,

    data():{
        showDelayModal: boolean,
        isUpdateDelay: boolean,
        showWarningDeleteDelay: boolean,
        delayType: DelayType,
    } {
        return {
            showDelayModal: false,
            isUpdateDelay: false,
            showWarningDeleteDelay: false,
            delayType: 'hour',
        }
    },

    watch: {
        sequence: {
            handler(value: Sequence): void {
                if (value.actionName !== this.delayConstant || value.ruleId) {
                    return;
                }

                const selectorSequence = this.sequences.find(item => (item.parentId === value.id));

                if (!selectorSequence?.id && value.config?.delay && !value.trueBlock) {
                    this.createSequence({
                        actionName: null,
                        trueCase: true,
                    })
                }
            },
            immediate: true,
        },

        showWarningModal(value: WarningConfig): void {
            if (value.name === SEQUENCE_TYPES.DELAY_ACTION && value.actionType === 'EDIT' && value.id === this.sequence.id) {
                this.showDelayModal = true;
                State.commit('swFlowDelay/setShowWarningModal', { type: '', name: '', enabled: false, id: '' });
            }

            if (value.name === SEQUENCE_TYPES.DELAY_ACTION && value.actionType === 'DELETE' && value.id === this.sequence.id) {
                this.onConfirmDeleteDelay();
            }
        },
    },

    computed: {
        ...mapGetters('swFlowState', ['sequences']),
        ...mapState('swFlowDelay', ['showWarningModal']),

        delayConstant(): string {
            return this.flowBuilderService.getActionName('DELAY');
        },

        actionDelayOptions(): Array<DelayAction> {
            return DELAY_OPTIONS.map(option => {
                return {
                    ...option,
                    label: this.$tc(option.label),
                }
            })
        },

        showDelayElement(): boolean {
            return this.sequence.actionName === this.delayConstant;
        },

        showCustomDescription(): boolean {
            return this.sequence.config.delay?.length > 1;
        },

        customTimeDescription(): string {
            const { delay } = this.sequence.config;

            const month = this.convertTimeString(delay[0].type, delay[0].value);
            const week = this.convertTimeString(delay[1].type, delay[1].value);
            const day = this.convertTimeString(delay[2].type, delay[2].value);
            const hour = this.convertTimeString(delay[3].type, delay[3].value);

            return [month, week, day, hour].filter(item => item).join();
        },

        timeDescription(): string {
            const { actionName } = this.sequence;
            if (actionName !== this.delayConstant) {
                return null;
            }

            const { type, value } = this.delayConfig;
            return this.convertTimeString(type, value);
        },

        delayConfig(): DelayConfig {
            const { config } = this.sequence;

            if (!config.delay || !Object.values(config.delay).length) {
                return {
                    type: null,
                    value: null,
                };
            }

            if (config.delay.length === 1) {
                return {
                    type: config.delay[0].type,
                    value: config.delay[0].value,
                }
            }

            return {
                type: CUSTOM_TIME,
                value: null,
            };
        }
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        convertTimeString(type: string, value: string): string {
            if (!value) {
                return '';
            }

            const unit = this.getTimeLabel(type, value);
            return ` ${value} ${unit}`;
        },

        getTimeLabel(type: string, number: number): string {
            switch (type) {

                case 'hour': {
                    return this.$tc('sw-flow-delay.modal.labelHour', number);
                }

                case 'day': {
                    return this.$tc('sw-flow-delay.modal.labelDay', number);
                }

                case 'week':{
                    return this.$tc('sw-flow-delay.modal.labelWeek', number);
                }

                case 'month': {
                    return this.$tc('sw-flow-delay.modal.labelMonth', number);
                }

                default: return '';
            }
        },

        onEditDelay(): void {
            if (localStorage.getItem('delay_action') === 'true') {
                this.delayType = this.delayConfig.type || DELAY_OPTIONS[0].value;
                this.isUpdateDelay = true;
                this.showDelayModal = true;
                return;
            }

            State.commit('swFlowDelay/setShowWarningModal', { type: SEQUENCE_TYPES.DELAY_ACTION, actionType: 'EDIT', name: '', enabled: true, id: this.sequence.id });
            this.delayType = this.delayConfig.type || DELAY_OPTIONS[0].value;
            this.isUpdateDelay = true;
        },

        onDeleteDelay(): void {
            if (localStorage.getItem('delay_deleted') === 'true') {
                this.onConfirmDeleteDelay();
                return;
            }

            State.commit('swFlowDelay/setShowWarningModal', { type: SEQUENCE_TYPES.DELAY_ACTION, actionType: 'DELETE', name: '', enabled: true, id: this.sequence.id });
        },

        onConfirmDeleteDelay(): void {
            const children = this.sequences.filter(item => (item.parentId === this.sequence.id))
            children.forEach(item => {
                State.commit('swFlowState/updateSequence', {
                    id: item.id,
                    parentId: this.sequence.parentId,
                    trueCase: this.sequence.trueCase
                });
            })

            State.commit('swFlowState/removeSequences', [this.sequence.id]);
        },

        onSelectDelay(delayType: DelayType): void {
            if (!delayType) {
                return;
            }

            this.showDelayModal = true;
            this.delayType = delayType;
        },

        onChangeType(delayType: DelayType): void {
            this.delayType = delayType;
        },

        onCloseDelayModal(): void {
            this.showDelayModal = false;
        },

        onSaveDelay(data: Sequence): void {
            State.commit('swFlowState/updateSequence', data);
            this.showDelayModal = false;

            if (!this.isUpdateDelay) {
                this.createSequence({
                    actionName: null,
                    trueCase: true,
                });
            }
        },

        arrowClasses(thenCase: boolean) {
            return {
                'has--then-selector': thenCase,
            };
        },

        createSequence(params): void {
            let sequence = this.sequenceRepository.create();
            const newSequence = {
                ...sequence,
                parentId: this.sequence.id,
                displayGroup: this.sequence.displayGroup,
                actionName: params.actionName,
                ruleId: null,
                config: {},
                position: 1,
                trueCase: params.trueCase,
                id: utils.createId(),
            };

            sequence = Object.assign(sequence, newSequence);
            State.commit('swFlowState/addSequence', sequence);
        },
    }
});
