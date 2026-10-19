<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="inModal" class="ctaStatus ctaStatus--stacked">
    <div class="alert alert-no-background" :class="alertClass">
      {{ label }}
      <span v-if="hasAction && !actionAsButton" class="ctaStatus__alertAction">(<slot />)</span>
    </div>
    <div v-if="hasAction && actionAsButton" class="ctaStatus__actionButton"><slot /></div>
  </div>

  <div v-else class="ctaStatus" :class="toneClass">
    <span class="ctaStatus__label">
      <span class="ctaStatus__icon" :class="iconClass" aria-hidden="true" />
      {{ label }}
    </span>
    <div class="ctaStatus__action" v-if="hasAction"><slot /></div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

/**
 * A plugin's non-actionable state - installed, cannot install, license missing - with whatever
 * action remains beside it.
 *
 * Two renderings: the modal keeps the alert banner it has always had, with the action in brackets
 * or, with `actionAsButton`, as a button under it; a card draws one row, state then action, since
 * the alert's padding and border will not fit. Success takes the brand alert rather than the green
 * one, as the page around it is drawn in the brand colour.
 */
const ALERT_CLASSES: Record<string, string> = {
  success: 'alert-brand',
  warning: 'alert-warning',
  danger: 'alert-danger',
};

const ICON_CLASSES: Record<string, string> = {
  success: 'icon-ok',
  warning: 'icon-warning',
  danger: 'icon-error',
};

export default defineComponent({
  props: {
    /** One of success, warning or danger. */
    tone: {
      type: String,
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
    inModal: {
      type: Boolean,
      required: true,
    },
    /** The slot is a function either way, so the caller says, or the brackets render empty. */
    hasAction: {
      type: Boolean,
      default: false,
    },
    /**
     * In the modal, draw the action as a button under the alert rather than as a link in brackets
     * inside it. For an action the card draws as a button too, so the two look the same.
     */
    actionAsButton: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    alertClass(): string {
      return ALERT_CLASSES[this.tone] ?? ALERT_CLASSES.warning;
    },
    iconClass(): string {
      return ICON_CLASSES[this.tone] ?? ICON_CLASSES.warning;
    },
    toneClass(): string {
      return `ctaStatus--${this.tone}`;
    },
  },
});
</script>
