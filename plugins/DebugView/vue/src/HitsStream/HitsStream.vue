<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="debugViewStream" ref="root">
    <transition-group
      tag="ol"
      name="debugViewHitAnim"
      class="debugViewStreamList"
      :aria-label="translate('DebugView_SecondsStream')"
    >
      <li
        v-for="item in items"
        :key="item.hit.idRawRequest"
        class="debugViewStreamItem"
      >
        <div
          v-if="item.gapLabel"
          class="debugViewHitGap"
          aria-hidden="true"
        >
          <span class="debugViewHitGapLabel">{{ item.gapLabel }}</span>
        </div>
        <HitRow
          :hit="item.hit"
          :is-selected="item.hit.idRawRequest === selectedHitId"
          @open="$emit('openHit', item.hit)"
        />
      </li>
    </transition-group>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import HitRow from './HitRow.vue';
import { Hit, StreamItem } from '../types';

export default defineComponent({
  props: {
    hits: {
      type: Array as PropType<Hit[]>,
      required: true,
    },
    selectedHitId: {
      type: String as PropType<string|null>,
      default: null,
    },
  },
  components: {
    HitRow,
  },
  emits: ['openHit'],
  computed: {
    // hits are ordered newest first; each item carries the elapsed-time gap to
    // the next newer hit shown directly above it
    items(): StreamItem[] {
      return this.hits.map((hit, index) => {
        let gapLabel: string|null = null;

        if (index > 0) {
          const gap = this.hits[index - 1].timestamp - hit.timestamp;
          if (gap > 0 && gap < 60) {
            gapLabel = translate('DebugView_SecondsAgoShort', `${gap}`);
          } else if (gap >= 60) {
            gapLabel = translate('DebugView_MinutesAgoShort', `${Math.floor(gap / 60)}`);
          }
        }

        return { hit, gapLabel };
      });
    },
  },
  methods: {
    findRowElement(hitId: string): HTMLElement|null {
      const root = this.$refs.root as HTMLElement|undefined;
      if (!root) {
        return null;
      }

      const rows = root.querySelectorAll<HTMLElement>('.debugViewHitRow');
      for (let i = 0; i < rows.length; i += 1) {
        if (rows[i].getAttribute('data-hit-id') === hitId) {
          return rows[i];
        }
      }

      return null;
    },
    focusHit(hitId: string): void {
      const row = this.findRowElement(hitId);
      if (row) {
        row.focus();
      }
    },
    scrollToMinute(minuteStart: number): void {
      // hits are sorted newest first, so the first match is the topmost row of
      // the clicked minute's block
      const hit = this.hits.find(
        (candidate) => candidate.timestamp >= minuteStart && candidate.timestamp < minuteStart + 60,
      );
      if (!hit) {
        return;
      }

      const row = this.findRowElement(hit.idRawRequest);
      if (row && typeof row.scrollIntoView === 'function') {
        const reducedMotion = window.matchMedia
          && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        row.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
      }
    },
  },
});
</script>
