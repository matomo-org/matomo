<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <a
    :class="[
      {
        'entity-duplicator-action': true,
        'table-action': true,
        'icon-content-copy': true,
        'is-disabled': !isActionEnabled,
      },
      extraClasses,
    ]"
    v-tooltips
    :title="getActionTooltip"
    v-show="isActionVisible"
    :aria-disabled="!isActionEnabled"
    @click="!isActionEnabled || handleClick()"
  />
</template>

<script lang="ts">
import {
  defineComponent,
  PropType,
} from 'vue';
import { translate, translateOrDefault } from '../translate';
import Tooltips from '../Tooltips/Tooltips';
import { EntityDuplicatorStore } from './EntityDuplicatorStore';

export default defineComponent({
  props: {
    /**
     * Useful data to pass to the modal, such as the ID for which entity this action triggers a copy
     */
    actionFormData: {
      type: Object,
      required: true,
    },
    /**
     * The reactive class for controlling the settings of the modal from multiple components.
     */
    modalStore: {
      type: Object as PropType<EntityDuplicatorStore>,
      required: true,
    },
    /**
     * Indicates whether the action should be shown.
     */
    isActionVisible: {
      type: Boolean,
      required: true,
    },
    /**
     * Allows disabling the action (if you want it visible, but not active).
     */
    isActionEnabled: {
      type: Boolean,
      default: false,
    },
    /**
     * Allows setting custom tooltip text. The default is 'Copy {entityTypeTranslation}'.
     */
    tooltipTextOverride: {
      type: String,
      default: '',
    },
    /**
     * Custom tooltip text used when the action is disabled, great for explaining why it's disabled.
     */
    tooltipTextOverrideDisabled: {
      type: String,
      default: '',
    },
    /**
     * Optional property to provide any custom classes to the root of the action's anchor element
     */
    extraClasses: {
      type: [String, Array, Object],
      default: '',
    },
  },
  directives: {
    Tooltips,
  },
  methods: {
    handleClick() {
      this.modalStore.showModal(this.actionFormData);
    },
  },
  computed: {
    getActionTooltip(): string {
      if (this.isActionEnabled && this.tooltipTextOverride.length) {
        return translateOrDefault(this.tooltipTextOverride);
      }

      if (!this.isActionEnabled && this.tooltipTextOverrideDisabled.length) {
        return translateOrDefault(this.tooltipTextOverrideDisabled);
      }

      return translate('CoreHome_CopyX', this.modalStore.getEntityTypeTranslation);
    },
  },
});
</script>
