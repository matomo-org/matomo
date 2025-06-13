<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <a
    :class="{
      'matomo-copy-action': true,
      'table-action': true,
      'icon-content-copy': true,
      'is-disabled': !isActionEnabled,
    }"
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
} from 'vue';
import { translate, translateOrDefault } from '../translate';
import Tooltips from '../Tooltips/Tooltips';
import MatomoCopyLogic from './MatomoCopyLogic';

export default defineComponent({
  props: {
    /**
     * Useful data to pass to the modal, such as the ID for which entity this action triggers a copy
     */
    modelData: {
      type: Object,
      required: true,
    },
    /**
     * This allows modelData to be emitted to the parent so that it can be used by the modal
     */
    copyFormData: {
      type: Object,
      required: true,
      default: () => ({}),
    },
    /**
     * Indicates the modal should be shown. Emitting an update notifies the parent to show the modal
     */
    showCopyModal: {
      type: Boolean,
      required: true,
      default: false,
    },
    /**
     * Indicates whether the action should be shown.
     */
    isActionVisible: {
      type: Boolean,
      required: true,
      default: false,
    },
    /**
     * Allows disabling the action (if you want it visible, but not active).
     */
    isActionEnabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    /**
     * Allows setting custom tooltip text. The default is 'Copy {copyEntityTypeTranslation}'.
     */
    tooltipTextOverride: {
      type: String,
      required: false,
      default: '',
    },
    /**
     * Custom tooltip text used when the action is disabled, great for explaining why it's disabled.
     */
    tooltipTextOverrideDisabled: {
      type: String,
      required: false,
      default: '',
    },
    /**
     * Translation of what is being copied (e.g. goal, funnel, segment, ...). This can be a string
     * or translation key. If nothing is provided 'report' is used.
     */
    copyEntityTypeTranslation: {
      type: String,
      required: false,
      default: '',
    },
  },
  emits: ['update:showCopyModal', 'update:copyFormData'],
  directives: {
    Tooltips,
  },
  mixins: [
    MatomoCopyLogic,
  ],
  methods: {
    handleClick() {
      // Combines the model data and copy form data just in case model data is missing fields
      const modifiedData = {
        ...this.modelData,
        ...this.copyFormData,
      };
      this.$emit('update:copyFormData', modifiedData);
      this.$emit('update:showCopyModal', true);
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

      return translate('CoreHome_CopyX', this.getEntityTypeTranslation);
    },
  },
});
</script>
