import {Sequence} from '../../../../type/types';
import type {Entity} from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

/**
 * @package business-ops
 */
export default {
    computed: {
        sequenceData(): Array<Sequence> {
            const sequenceData = this.$super('sequenceData');

            sequenceData.map(item => {
                if (!this.getLicense('FLOW_BUILDER-6654893') &&
                    item.actionName === this.webhookConstant) {
                    item.disabled = true;
                }

                return item;
            });

            return sequenceData;
        },

        webhookConstant() {
            return this.flowBuilderService.getActionName('CALL_WEBHOOK')
        },

        actionOptions() {
            if (!this.getLicense('FLOW_BUILDER-6654893')) {
                return this.$super('actionOptions').filter(action => action?.value !== this.webhookConstant);
            }

            return this.$super('actionOptions');
        },
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        onEditAction(sequence: Entity<'flow_sequence'>, target: string, key: string): void {
            if (!this.getLicense('FLOW_BUILDER-6654893') && sequence.actionName === this.webhookConstant) {
                return;
            }

            this.$super('onEditAction', sequence, target, key);
        },
    },
}
