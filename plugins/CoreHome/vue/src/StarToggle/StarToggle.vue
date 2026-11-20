<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <button
    v-if="!readonly"
    type="button"
    class="starToggle"
    :class="stateClasses"
    :title="title"
    :aria-label="title"
    :aria-pressed="starred ? 'true' : 'false'"
    @click.stop.prevent="$emit('toggle', !starred)"
    @animationend="animation = ''"
  >
    <svg
      class="starToggle__icon"
      xmlns="http://www.w3.org/2000/svg"
      width="16"
      height="16"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <path class="starToggle__shape" stroke-width="3" :d="starShape" />
    </svg>
  </button>
  <span
    v-else
    class="starToggle starToggle--readonly"
    :class="stateClasses"
    :title="title || undefined"
    :role="title ? 'img' : undefined"
    :aria-label="title || undefined"
    :aria-hidden="title ? undefined : 'true'"
  >
    <svg
      class="starToggle__icon"
      xmlns="http://www.w3.org/2000/svg"
      width="16"
      height="16"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <path class="starToggle__shape" stroke-width="3" :d="starShape" />
    </svg>
  </span>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import STAR_TOGGLE_SHAPE from './starShape';
import closeTooltips from '../Tooltips/closeTooltips';

/**
 * The star used to mark an entity as starred, for example a segment or a goal.
 *
 * By default it is a button that emits `toggle` with the requested state when clicked. The parent
 * owns the state and saves it. With `readonly` it only shows the state and cannot be focused or
 * clicked.
 *
 * Usage:
 * <StarToggle :starred="goal.starred" :title="translate('...')" @toggle="save(goal, $event)" />
 * <StarToggle :starred="true" :readonly="true" :title="translate('...')" />
 */
export default defineComponent({
  name: 'StarToggle',
  props: {
    starred: {
      type: Boolean,
      default: false,
    },
    readonly: {
      type: Boolean,
      default: false,
    },
    // shown as tooltip and used as the accessible name
    title: {
      type: String,
      default: '',
    },
    // smaller star for places where it is shown next to a name
    small: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['toggle'],
  data() {
    return {
      animation: '',
    };
  },
  watch: {
    starred(isStarred: boolean) {
      if (!this.readonly) {
        this.animation = isStarred ? 'starToggle--animateStar' : 'starToggle--animateUnstar';
      }
    },
    title() {
      // an open tooltip would keep showing the previous title, eg "Add to..." on a star just added
      if (this.$el instanceof HTMLElement) {
        closeTooltips(this.$el);
      }
    },
  },
  computed: {
    starShape(): string {
      return STAR_TOGGLE_SHAPE;
    },
    stateClasses(): Record<string, boolean> {
      const classes: Record<string, boolean> = {
        'starToggle--starred': this.starred,
        'starToggle--small': this.small,
      };
      if (this.animation) {
        classes[this.animation] = true;
      }
      return classes;
    },
  },
});
</script>
