<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="categoryTabs">
    <div class="categoryTabs__select">
      <select
        class="browser-default categoryTabs__selectInput"
        :value="modelValue"
        :aria-label="translate('Marketplace_Categories')"
        @change="selectFromEvent($event)"
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
          class="categoryTabs__tab categoryTabs__tab--more"
          :class="{ 'categoryTabs__tab--active': activeIsInOverflow }"
          :aria-expanded="expanded"
          @click="expanded = !expanded"
        >
          <span>{{ activeIsInOverflow ? activeLabel : translate('Marketplace_Categories') }}</span>
          <span class="icon-chevron-down" aria-hidden="true" />
        </button>

        <!--
          A disclosure, not an ARIA menu: menu semantics promise arrow/Home/End navigation and
          focus moved into the menu, and these are ordinary buttons the tab key already reaches.
        -->
        <div class="categoryTabs__menu" v-if="expanded">
          <button
            v-for="tab in overflowTabs"
            :key="tab.id"
            type="button"
            class="categoryTabs__menuItem"
            :class="{ 'categoryTabs__menuItem--active': tab.id === modelValue }"
            :aria-current="tab.id === modelValue ? 'page' : undefined"
            @click="select(tab.id)"
          >{{ tabLabel(tab) }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, markRaw, PropType } from 'vue';
import { translate } from 'CoreHome';
import { PluginTab } from '../PluginGrid/pluginGrouping';
import { tabLabel } from '../PluginGrid/categoryLabels';

/**
 * At or below this width the bar keeps the first few tabs and moves the rest into a menu. Written
 * as the same max-width test `CategoryTabs.less` uses: the min-width inverse disagrees with the
 * stylesheet at exactly 1400px, leaving the tabs past the fifth unreachable.
 */
const NARROW_BREAKPOINT = '(max-width: 1400px)';

/** How many tabs stay on the bar when there is not room for all of them. */
const ALWAYS_VISIBLE = 5;

export interface CategoryTabsState {
  expanded: boolean;
  isWide: boolean;
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
        const moreButton = this.$refs.moreButton as HTMLElement|undefined;
        if (moreButton) {
          moreButton.focus();
        }
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
    /** The cast lives here rather than in the template, which is compiled without TypeScript. */
    selectFromEvent(event: Event) {
      this.select((event.target as HTMLSelectElement).value);
    },
    select(tabId: string) {
      this.expanded = false;
      this.$emit('update:modelValue', tabId);
    },
    tabLabel,

    /**
     * Moves focus onto the selected tab, for a caller that changed the selection from elsewhere on
     * the page - a section's "See all" - whose own control the re-render removes.
     *
     * An overflow tab is display:none until there is room for it, and focus() does nothing on one,
     * so the More button stands in; it already renders as active in that case.
     */
    focusActiveTab() {
      const bar = this.$refs.bar as HTMLElement|undefined;
      const active = bar?.querySelector<HTMLElement>('.categoryTabs__tab--active');
      const moreButton = this.$refs.moreButton as HTMLElement|undefined;

      if (active && active.offsetParent !== null) {
        active.focus();
        return;
      }

      const fallback = moreButton ?? active;
      if (fallback) {
        fallback.focus();
      }
    },
  },
});
</script>
