<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="categoryTabs">
    <div class="categoryTabs__select">
      <select
        class="browser-default"
        :value="modelValue"
        :aria-label="translate('Marketplace_Categories')"
        @change="select(($event.target as HTMLSelectElement).value)"
      >
        <option v-for="tab in tabs" :key="tab.id" :value="tab.id">{{ tabLabel(tab) }}</option>
      </select>
    </div>

    <div class="categoryTabs__bar" ref="bar">
      <div class="categoryTabs__list">
        <button
          v-for="(tab, index) in tabs"
          :key="tab.id"
          type="button"
          class="categoryTabs__tab"
          :class="{
            'categoryTabs__tab--active': tab.id === modelValue,
            'categoryTabs__tab--overflow': index >= alwaysVisibleCount,
          }"
          :aria-current="tab.id === modelValue ? 'page' : undefined"
          @click="select(tab.id)"
        >{{ tabLabel(tab) }}</button>
      </div>

      <div class="categoryTabs__more" v-if="hasOverflowTabs">
        <button
          type="button"
          ref="moreButton"
          class="categoryTabs__tab categoryTabs__moreButton"
          :class="{ 'categoryTabs__tab--active': activeIsInOverflow }"
          :aria-expanded="expanded"
          aria-haspopup="menu"
          @click="expanded = !expanded"
        >
          <span>{{ activeIsInOverflow ? activeLabel : translate('Marketplace_Categories') }}</span>
          <span class="icon-chevron-down" aria-hidden="true" />
        </button>

        <div class="categoryTabs__menu" role="menu" v-if="expanded">
          <button
            v-for="tab in overflowTabs"
            :key="tab.id"
            type="button"
            role="menuitem"
            class="categoryTabs__menuItem"
            :class="{ 'categoryTabs__menuItem--active': tab.id === modelValue }"
            @click="select(tab.id)"
          >{{ tabLabel(tab) }}</button>
        </div>
      </div>
    </div>

    <span class="categoryTabs__announcement" aria-live="polite">{{ activeLabel }}</span>
  </div>
</template>

<script lang="ts">
import { defineComponent, markRaw, PropType } from 'vue';
import { translate, translateOrDefault, ucfirst } from 'CoreHome';
import {
  PluginTab,
  TAB_ALL,
  TAB_BUNDLES,
  TAB_PREMIUM,
  TAB_THEMES,
} from '../PluginGrid/pluginGrouping';

/**
 * At or below this width the bar shows only the first few tabs and moves the rest into a menu.
 *
 * It is written as the same max-width test `CategoryTabs.less` uses, not as its min-width
 * inverse: at exactly 1400px a min-width test reports "wide" while the stylesheet has already
 * hidden the overflow tabs, and the tabs past the fifth become unreachable.
 */
const NARROW_BREAKPOINT = '(max-width: 1400px)';

/** How many tabs stay on the bar when there is not room for all of them. */
const ALWAYS_VISIBLE = 5;

export interface CategoryTabsState {
  expanded: boolean;
  isWide: boolean;
  // markRaw'd: Vue must not proxy a MediaQueryList, its methods throw when called on a proxy
  narrowQuery: MediaQueryList|null;
  onNarrowChange: (() => void)|null;
  onDocumentClick: ((event: MouseEvent) => void)|null;
  onKeydown: ((event: KeyboardEvent) => void)|null;
}

export default defineComponent({
  props: {
    tabs: {
      type: Array as PropType<PluginTab[]>,
      required: true,
    },
    modelValue: {
      type: String,
      required: true,
    },
  },
  emits: ['update:modelValue'],
  data(): CategoryTabsState {
    return {
      expanded: false,
      isWide: true,
      narrowQuery: null,
      onNarrowChange: null,
      onDocumentClick: null,
      onKeydown: null,
    };
  },
  mounted() {
    const narrowQuery = markRaw(window.matchMedia(NARROW_BREAKPOINT));
    this.narrowQuery = narrowQuery;
    this.onNarrowChange = () => {
      this.isWide = !narrowQuery.matches;
      if (this.isWide) {
        this.expanded = false;
      }
    };
    narrowQuery.addEventListener('change', this.onNarrowChange);
    this.onNarrowChange();

    this.onDocumentClick = (event: MouseEvent) => {
      const bar = this.$refs.bar as HTMLElement|undefined;
      if (this.expanded && bar && !bar.contains(event.target as Node)) {
        this.expanded = false;
      }
    };
    this.onKeydown = (event: KeyboardEvent) => {
      if (event.key === 'Escape' && this.expanded) {
        this.expanded = false;
        // a closed menu must not leave focus on nothing, or the next Tab starts from the top
        (this.$refs.moreButton as HTMLElement|undefined)?.focus();
      }
    };
    document.addEventListener('mousedown', this.onDocumentClick);
    document.addEventListener('keydown', this.onKeydown);
  },
  unmounted() {
    if (this.narrowQuery && this.onNarrowChange) {
      this.narrowQuery.removeEventListener('change', this.onNarrowChange);
    }
    if (this.onDocumentClick) {
      document.removeEventListener('mousedown', this.onDocumentClick);
    }
    if (this.onKeydown) {
      document.removeEventListener('keydown', this.onKeydown);
    }
  },
  computed: {
    alwaysVisibleCount(): number {
      return this.isWide ? this.tabs.length : ALWAYS_VISIBLE;
    },
    overflowTabs(): PluginTab[] {
      return this.tabs.slice(this.alwaysVisibleCount);
    },
    hasOverflowTabs(): boolean {
      return this.overflowTabs.length > 0;
    },
    activeIsInOverflow(): boolean {
      return this.overflowTabs.some((tab) => tab.id === this.modelValue);
    },
    activeLabel(): string {
      const active = this.tabs.find((tab) => tab.id === this.modelValue);
      return active ? this.tabLabel(active) : '';
    },
  },
  methods: {
    translate,
    select(tabId: string) {
      this.expanded = false;
      this.$emit('update:modelValue', tabId);
    },
    tabLabel(tab: PluginTab): string {
      if (!tab.isCategory) {
        const fixed: Record<string, string> = {
          [TAB_ALL]: 'Marketplace_AllPlugins',
          [TAB_PREMIUM]: 'Marketplace_PaidPlugins',
          [TAB_BUNDLES]: 'Marketplace_Bundles',
          [TAB_THEMES]: 'CorePluginsAdmin_Themes',
        };
        return translate(fixed[tab.id] ?? tab.id);
      }

      // the Marketplace's category vocabulary is data, not a fixed list, so a value we have no key
      // for yet renders as something readable. translateOrDefault, not translate: an unknown key
      // makes translate() return "The string ... was not loaded in javascript".
      const key = `Marketplace_Category${ucfirst(tab.id)}`;
      const label = translateOrDefault(key);
      return label === key ? ucfirst(tab.id) : label;
    },
  },
});
</script>
