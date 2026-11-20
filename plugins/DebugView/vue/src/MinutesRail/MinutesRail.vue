<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="debugViewMinutesRail">
    <div
      class="debugViewPendingBadge"
      :class="{ 'debugViewPendingBadge--paused': paused }"
      aria-live="polite"
    >
      {{ translate('DebugView_NewHitsSincePaused', `${pendingCount}`) }}
    </div>
    <ol
      class="debugViewMinutesList"
      :aria-label="translate('DebugView_MinutesTimeline')"
    >
      <li
        v-for="(bucket, index) in buckets"
        :key="bucket.minuteStart"
        class="debugViewMinuteItem"
        :class="{ 'debugViewMinuteItem--has-hits': bucket.count > 0 }"
      >
        <button
          type="button"
          class="debugViewMinuteDot"
          :class="{
            'debugViewMinuteDot--has-hits': bucket.count > 0,
            'debugViewMinuteDot--current': index === 0,
            'debugViewMinuteDot--selected': bucket.minuteStart === selectedMinute,
          }"
          :disabled="bucket.count === 0"
          :aria-label="getDotLabel(bucket, index)"
          @click="$emit('selectMinute', bucket.minuteStart)"
        >
          <span
            v-if="bucket.count > 0"
            class="debugViewMinuteCount"
          >{{ bucket.count }}</span>
        </button>
        <span
          v-if="bucket.showLabel"
          class="debugViewMinuteLabel"
          aria-hidden="true"
        >{{ bucket.label }}</span>
      </li>
    </ol>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import { MinuteBucket } from '../types';

export default defineComponent({
  props: {
    buckets: {
      type: Array as PropType<MinuteBucket[]>,
      required: true,
    },
    selectedMinute: {
      type: Number as PropType<number|null>,
      default: null,
    },
    pendingCount: {
      type: Number,
      default: 0,
    },
    paused: Boolean,
  },
  emits: ['selectMinute'],
  methods: {
    getDotLabel(bucket: MinuteBucket, index: number): string {
      const parts: string[] = [];

      if (bucket.count > 1) {
        parts.push(translate('DebugView_HitsInMinute', `${bucket.count}`, bucket.label));
      } else if (bucket.count === 1) {
        parts.push(translate('DebugView_OneHitInMinute', bucket.label));
      } else {
        parts.push(translate('DebugView_NoHitsInMinute', bucket.label));
      }

      if (index === 0) {
        parts.push(translate('DebugView_CurrentMinute'));
      }

      if (bucket.minuteStart === this.selectedMinute) {
        parts.push(translate('DebugView_SelectedMinute'));
      }

      if (bucket.count > 0) {
        parts.push(translate('DebugView_JumpToMinute', bucket.label));
      }

      return parts.join('. ');
    },
  },
});
</script>
