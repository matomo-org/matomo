<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ul
    class="debugViewDetailRows"
    :class="{ 'debugViewDetailRows--nested': depth > 0 }"
  >
    <li
      v-for="entry in visibleEntries"
      :key="entry.key"
      class="debugViewDetailRow"
    >
      <div
        v-if="entry.children"
        class="debugViewDetailNested"
      >
        <span class="debugViewDetailKey">{{ entry.key }}</span>
        <DetailRows
          :entries="entry.children"
          :depth="depth + 1"
        />
      </div>
      <button
        v-else
        type="button"
        class="debugViewDetailValueRow"
        :class="{ 'debugViewDetailValueRow--expanded': isExpanded(entry.key) }"
        :aria-expanded="isExpanded(entry.key) ? 'true' : 'false'"
        :title="entry.text"
        @click="toggle(entry.key)"
      >
        <span class="debugViewDetailKey">{{ entry.key }}</span>
        <span class="debugViewDetailValue">{{ entry.text }}</span>
      </button>
    </li>
  </ul>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';

const MAX_DEPTH = 4;

// the capture replaces sensitive values (e.g. token_auth) with this sentinel
// before storage; it is shown translated in the UI
const REDACTED_SENTINEL = '__redacted__';

export interface DetailEntry {
  key: string;
  text: string;
  children: Record<string, unknown>|null;
}

function isSkippedKey(key: string): boolean {
  const lowerKey = key.toLowerCase();
  return lowerKey.endsWith('icon') || lowerKey.endsWith('iconsvg');
}

// booleans are kept: `false` is data ("all available details"), only truly
// empty values are dropped
function isSkippedValue(value: unknown): boolean {
  return value === null || value === undefined || value === '';
}

function toText(value: unknown): string {
  if (value === REDACTED_SENTINEL) {
    return translate('DebugView_Redacted');
  }

  if (value !== null && typeof value === 'object') {
    try {
      return JSON.stringify(value);
    } catch (e) {
      return String(value);
    }
  }

  return String(value);
}

export default defineComponent({
  name: 'DetailRows',
  props: {
    entries: {
      type: Object as PropType<Record<string, unknown>>,
      required: true,
    },
    depth: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
      expandedKeys: {} as Record<string, boolean>,
    };
  },
  watch: {
    entries() {
      this.expandedKeys = {};
    },
  },
  computed: {
    visibleEntries(): DetailEntry[] {
      const result: DetailEntry[] = [];

      Object.keys(this.entries).forEach((key) => {
        const value = this.entries[key];

        if (isSkippedValue(value) || isSkippedKey(key)) {
          return;
        }

        if (value && typeof value === 'object' && this.depth < MAX_DEPTH) {
          const children = value as Record<string, unknown>;
          if (Object.keys(children).length) {
            result.push({ key, text: '', children });
          }
          return;
        }

        result.push({ key, text: toText(value), children: null });
      });

      return result;
    },
  },
  methods: {
    toggle(key: string): void {
      this.expandedKeys[key] = !this.expandedKeys[key];
    },
    isExpanded(key: string): boolean {
      return !!this.expandedKeys[key];
    },
  },
});
</script>
