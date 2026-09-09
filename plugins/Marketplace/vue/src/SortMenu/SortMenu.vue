<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="sortMenu" ref="root">
    <button
      type="button"
      ref="trigger"
      class="sortMenu__trigger"
      :aria-expanded="expanded"
      aria-haspopup="menu"
      @click="expanded = !expanded"
    >
      <span>{{ translate('Marketplace_SortBy') }}: {{ activeLabel }}</span>
      <span class="icon-chevron-down" aria-hidden="true" />
    </button>

    <div class="sortMenu__menu" role="menu" v-if="expanded">
      <button
        v-for="option in options"
        :key="option.id"
        type="button"
        role="menuitemradio"
        :aria-checked="option.id === modelValue"
        class="sortMenu__item"
        @click="select(option.id)"
      >
        <span>{{ translate(option.labelKey) }}</span>
        <span class="icon-ok sortMenu__check" v-if="option.id === modelValue" aria-hidden="true" />
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from 'CoreHome';
import {
  SORT_ALPHA,
  SORT_DEVELOPER,
  SORT_LAST_UPDATED,
  SORT_NEWEST,
  SORT_POPULAR,
} from '../PluginGrid/pluginGrouping';

const OPTIONS = [
  { id: SORT_LAST_UPDATED, labelKey: 'Marketplace_SortByLastUpdated' },
  { id: SORT_POPULAR, labelKey: 'Marketplace_SortByPopular' },
  { id: SORT_NEWEST, labelKey: 'Marketplace_SortByNewest' },
  { id: SORT_ALPHA, labelKey: 'Marketplace_SortByAlpha' },
  // resolved on the client against the plugin owner; the Marketplace has no such sort method
  { id: SORT_DEVELOPER, labelKey: 'Marketplace_SortByDeveloper' },
];

export interface SortMenuState {
  expanded: boolean;
  onDocumentClick: ((event: MouseEvent) => void)|null;
  onKeydown: ((event: KeyboardEvent) => void)|null;
}

export default defineComponent({
  props: {
    modelValue: {
      type: String,
      required: true,
    },
  },
  emits: ['update:modelValue'],
  data(): SortMenuState {
    return {
      expanded: false,
      onDocumentClick: null,
      onKeydown: null,
    };
  },
  mounted() {
    this.onDocumentClick = (event: MouseEvent) => {
      const root = this.$refs.root as HTMLElement|undefined;
      if (this.expanded && root && !root.contains(event.target as Node)) {
        this.expanded = false;
      }
    };
    this.onKeydown = (event: KeyboardEvent) => {
      if (event.key === 'Escape' && this.expanded) {
        this.expanded = false;
        const trigger = this.$refs.trigger as HTMLElement|undefined;
        if (trigger) {
          trigger.focus();
        }
      }
    };
    document.addEventListener('mousedown', this.onDocumentClick);
    document.addEventListener('keydown', this.onKeydown);
  },
  unmounted() {
    if (this.onDocumentClick) {
      document.removeEventListener('mousedown', this.onDocumentClick);
    }
    if (this.onKeydown) {
      document.removeEventListener('keydown', this.onKeydown);
    }
  },
  computed: {
    options() {
      return OPTIONS;
    },
    activeLabel(): string {
      const active = OPTIONS.find((option) => option.id === this.modelValue) ?? OPTIONS[0];
      return translate(active.labelKey);
    },
  },
  methods: {
    translate,
    select(sort: string) {
      this.expanded = false;
      this.$emit('update:modelValue', sort);
    },
  },
});
</script>
