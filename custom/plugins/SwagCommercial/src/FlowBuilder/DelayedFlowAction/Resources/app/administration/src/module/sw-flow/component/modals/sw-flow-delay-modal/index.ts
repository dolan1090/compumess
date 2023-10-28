// @ts-ignore
import type {PropType} from 'vue';
import template from './sw-flow-delay-modal.html';
import './sw-flow-delay-modal.scss';
import {DELAY_OPTIONS, CUSTOM_TIME} from '../../../../../constant/sw-flow-delay.constant';
import {Sequence, DelayType, DelayOption, FieldError, DelayConfig} from '../../../../../type/types';

const { Component } = Shopware;
const { ShopwareError } = Shopware.Classes;

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    props: {
        sequence: {
            type: Object as PropType<Sequence>,
            required: true,
        },

        type: {
            type: String as PropType<DelayType>,
            required: true,
        },

        isUpdateDelay: {
            type: Boolean,
            required: false,
        },
    },

    data(): {
        time: String,
        timeError: null,
        typeError: null,
        customTimeError: null,
        CUSTOM_TIME
    } {
        return {
            time: '',
            timeError: null,
            typeError: null,
            customTimeError: null,
            CUSTOM_TIME,
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        actionDelayOptions(): DelayOption[] {
            return DELAY_OPTIONS.map(option => {
                return {
                    ...option,
                    label: this.$tc(option.label),
                }
            })
        }
    },

    watch: {
        time(value: string): void {
            if (value && this.timeError) {
                this.timeError = null;
            }

            if (value && this.customTimeError) {
                this.customTimeError = null;
            }
        },

        type(value: string): void {
            if (value && this.typeError) {
                this.typeError = null;
            }

            this.time = null;
            this.customTimeError = null;
            this.timeError = null;
        },
    },

    methods: {
        createdComponent(): void {
            this.time = this.type === CUSTOM_TIME ? '' : null;

            if (this.isUpdateDelay) {
                const { delay } = this.sequence.config;
                if (delay.length === 1) {
                    this.time = delay[0].value;
                } else {
                    this.time = `${delay[0].value}:${delay[1].value}:${delay[2].value}:${delay[3].value}`;
                }
            }
        },

        fieldError(time: string): FieldError | null {
            if (!time) {
                return new ShopwareError({
                    code: 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                });
            }

            return null;
        },

        validateCustomTime(time: string): FieldError | null {
            const times = this.convertCustomTime();
            const isInValidTimes = times.every(time => time.value === 0);

            if (!time || isInValidTimes) {
                return new ShopwareError({
                    code: 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                });
            }

            const formatTimeRegex = /^(\d+):(\d+):(\d+):(\d+)$/;
            if (!formatTimeRegex.exec(time)) {
                return new ShopwareError({
                    code: 'CUSTOM_TIME_INVALID',
                    detail: this.$tc('sw-flow-delay.modal.customTimeInvalid'),
                });
            }

            return null;
        },

        getTimeType(index: number): string {
            if (index < 0) return '';

            switch (index) {
                case 0: return 'month';
                case 1: return 'week';
                case 2: return 'day';
                case 3: return 'hour';

                default: return '';
            }
        },

        convertCustomTime(): DelayConfig {
            return this.time.split(':').map((item: string, index: number) => {
                return {
                    value: parseInt(item),
                    type: this.getTimeType(index, item)
                };
            })
        },

        onClose(): void {
            this.$emit('modal-close');
        },

        onSelectDelay(typeDelay: DelayOption): void {
            const type = typeDelay === null ? '' : typeDelay;
            this.$emit('type-change', type);
        },

        onSaveDelay(): void {
            if (this.type === CUSTOM_TIME) {
                this.customTimeError = this.validateCustomTime(this.time);
            } else {
                this.timeError = this.fieldError(this.time);
            }

            this.typeError = this.fieldError(this.type);
            if (this.timeError || this.typeError) {
                return null;
            }

            if (this.customTimeError || this.typeError) {
                return null;
            }

            let newSequence = {
                ...this.sequence,
                config: {
                    delay: [
                        {
                            type: this.type,
                            value: this.time
                        }
                    ]
                }
            };

            if (this.type === CUSTOM_TIME) {
                newSequence.config = {
                    delay: this.convertCustomTime(),
                };
            }

            this.$emit('modal-save', newSequence);
        },

        getTimeLabel(type: string): string {
            switch (type) {

                case 'hour': {
                    return this.$tc('sw-flow-delay.modal.labelHour');
                }

                case 'day': {
                    return this.$tc('sw-flow-delay.modal.labelDay');
                }

                case 'week':{
                    return this.$tc('sw-flow-delay.modal.labelWeek');
                }

                case 'month': {
                    return this.$tc('sw-flow-delay.modal.labelMonth');
                }

                default: return '';
            }
        },

        getTimePlaceholder(type: string): string {
            return this.$tc('sw-flow-delay.modal.placeholderTime', 0,  {
                type: this.getTimeLabel(type)
            });
        },

    },
});
