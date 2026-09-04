<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    v-if="inModal"
    class="alert alert-no-background"
    :class="alertClass"
  >
    {{ label }}
    <span v-if="hasAction" style="white-space:nowrap">(<slot />)</span>
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
 * action still remains beside it.
 *
 * Two renderings, because the two places this appears want opposite things. In the details modal
 * it stays the alert it has always been, a banner with the action in brackets after the sentence.
 * On a card the prototype draws it as one row: the state on the left in its tone, the remaining
 * action as a button on the right, both inside the card's action area. The alert's 60px left
 * padding and 2px border cannot fit there.
 */
const ALERT_CLASSES: Record<string, string> = {
  success: 'alert-success',
  warning: 'alert-warning',
  danger: 'alert-danger',
};

// The card row has no background to carry the tone, so the icon does it. These are the icons the
// alert draws through ::before, kept the same so a state reads alike in both places.
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
    /**
     * Whether the caller passes an action at all. The slot is a function either way, so the
     * caller has to say, or the alert renders an empty pair of brackets.
     */
    hasAction: {
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
