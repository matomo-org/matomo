<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    v-if="!isEmpty"
    class="reportHeader"
    :class="{
      'reportHeader--flush': isFullPage && !plainTitle,
      'reportHeader--plainTitle': plainTitle,
    }"
  >
    <div class="reportHeader__main">
      <!-- `.widgetName` and the nested <span> are an external hook dataTable.js,
           SingleMetricView and UserCountryMap look for. `.self` stops the key handlers
           cancelling links EnrichedHeadline renders inside the heading. -->
      <component
        v-if="showTitle"
        :is="titleTag"
        class="reportHeader__title widgetName"
        :class="{ 'reportHeader__title--clickable': titleClickable }"
        :role="titleClickable ? 'button' : undefined"
        :tabindex="titleClickable ? 0 : undefined"
        :title="titleClickable ? titleClickHint : undefined"
        @click="onTitleClick"
        @keydown.enter.self.prevent="onTitleClick"
        @keydown.space.self.prevent="onTitleClick"
      >
        <EnrichedHeadline
          v-if="enriched"
          :feature-name="featureName"
          :inline-help="wrappedInlineHelp"
          :report-generated="reportGenerated"
          :edit-url="editUrl"
          :help-url="helpUrl"
        ><span>{{ titleText }}</span></EnrichedHeadline>
        <span v-else>{{ titleText }}</span>
      </component>
      <span v-if="!isFullPage" class="u-visuallyHidden">{{ translate('General_Widget') }}</span>
      <!-- Report search. Owns the debounce and bridges the keyword to the jQuery DataTable
           through a bubbling `reportheader:search` event (mirrors the `widgetcontrol:*`
           bridge). The current server-side pattern is pushed back via the `searchQuery` prop. -->
      <div
        v-if="showSearch"
        class="reportHeader__search"
      >
        <SearchInput
          :model-value="query"
          :placeholder="searchPlaceholder"
          :show-clear="true"
          @update:model-value="onQueryInput"
        />
      </div>
    </div>

    <!-- Widget controls: an inline action row, hidden until the widget is hovered/focused.
         Each action emits an intent that onControl() bridges to the jQuery widget. -->
    <div class="reportHeader__widgetControls">
      <WidgetControls
        v-if="hasControls"
        :can-minimise="controls.minimise"
        :can-maximise="controls.maximise"
        :can-refresh="controls.refresh"
        :can-close="controls.close"
        @minimise="onControl('minimise')"
        @maximise="onControl('maximise')"
        @refresh="onControl('refresh')"
        @close="onControl('close')"
      />
    </div>

    <!-- Reserved anchor for report actions (visualisation switcher, export, ...).
         Populated by a later story; intentionally empty here. -->
    <div class="reportHeader__actions" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import EnrichedHeadline from '../EnrichedHeadline/EnrichedHeadline.vue';
import SearchInput from '../SearchInput/SearchInput.vue';
import WidgetControls from '../WidgetControls/WidgetControls.vue';
import { translate } from '../translate';

// How long typing pauses before a search is dispatched. A search is a full DataTable AJAX
// reload, so debouncing avoids a reload on every keystroke.
const SEARCH_DEBOUNCE_MS = 300;

export interface ControlVisibility {
  minimise: boolean;
  maximise: boolean;
  refresh: boolean;
  close: boolean;
}

// Which widget controls each context exposes. Kept here so every surface that renders
// the header stays consistent with the redesign spec. `dashboard` is the normal widget state
// (all controls only make sense on a dashboard); `maximised`/`collapsed` are its state
// variants; `widgetized`/`preview`/`fullPage` render no controls.
const CONTROLS_BY_CONTEXT: Record<string, ControlVisibility> = {
  dashboard: {
    minimise: true, maximise: true, refresh: true, close: true,
  },
  maximised: {
    minimise: true, maximise: false, refresh: true, close: false,
  },
  collapsed: {
    minimise: false, maximise: true, refresh: false, close: true,
  },
  widgetized: {
    minimise: false, maximise: false, refresh: false, close: false,
  },
  preview: {
    minimise: false, maximise: false, refresh: false, close: false,
  },
  fullPage: {
    minimise: false, maximise: false, refresh: false, close: false,
  },
};

export default defineComponent({
  props: {
    context: {
      type: String,
      default: 'dashboard',
    },
    // Not `title`: that attribute on the twig host is picked up by the widget's v-tooltips
    // directive and shown as a tooltip repeating the heading. Core passes `report-title`.
    reportTitle: {
      type: String,
      default: '',
    },
    // @deprecated 6.0.0 - use `reportTitle`. 5.x only had `title`. Vue templates only: as a
    // twig host attribute it would trigger the tooltip above.
    title: {
      type: String,
      default: '',
    },
    titleClickable: Boolean,
    titleClickHint: {
      type: String,
      default: '',
    },
    headingLevel: {
      type: String,
      default: 'h3',
    },
    enriched: Boolean,
    // Keeps the plain heading metrics of a report that is not shown in a card.
    plainTitle: Boolean,
    // A titleless widgetized embed still mounts the header as an actions anchor, so only the
    // heading is suppressed, not the whole component.
    showTitle: {
      type: Boolean,
      default: true,
    },
    // Left empty on purpose: EnrichedHeadline then names the rated feature after the rendered
    // title, i.e. the widget name. Only dataTable.js sets it, to follow a related report.
    featureName: {
      type: String,
      default: '',
    },
    inlineHelp: {
      type: String,
      default: '',
    },
    reportGenerated: {
      type: String,
      default: '',
    },
    editUrl: {
      type: String,
      default: '',
    },
    helpUrl: {
      type: String,
      default: '',
    },
    // Renders the report search input under the title. dataTable.js drives this from the
    // report's `show_search` config.
    showSearch: Boolean,
    // The current server-side search pattern, pushed back after each reload so the field stays
    // in sync (prefill on load, related-report switch, programmatic clear).
    searchQuery: {
      type: String,
      default: '',
    },
    searchPlaceholder: {
      type: String,
      default: '',
    },
  },
  components: {
    EnrichedHeadline,
    SearchInput,
    WidgetControls,
  },
  emits: ['minimise', 'maximise', 'refresh', 'close', 'titleClick', 'search'],
  data() {
    return {
      // Local mirror of the search field. Seeded from `searchQuery`; user input schedules a
      // debounced search, a `searchQuery` change syncs it back without re-triggering one.
      query: this.searchQuery,
      searchDebounceTimer: null as ReturnType<typeof setTimeout> | null,
    };
  },
  watch: {
    searchQuery(value: string) {
      // Server-driven update; reflect it into the field without dispatching another search.
      if (value !== this.query) {
        this.query = value;
      }
    },
  },
  computed: {
    controls(): ControlVisibility {
      return CONTROLS_BY_CONTEXT[this.context] || CONTROLS_BY_CONTEXT.widgetized;
    },
    hasControls(): boolean {
      const c = this.controls;
      return c.minimise || c.maximise || c.refresh || c.close;
    },
    // No title and no controls to render. Actions are always empty today; a later story that adds
    // them must widen this so the full header comes back.
    isEmpty(): boolean {
      return !this.showTitle && !this.hasControls;
    },
    isFullPage(): boolean {
      return this.context === 'fullPage';
    },
    titleText(): string {
      return this.reportTitle || this.title;
    },
    // Whitelisted so the prop can never inject an arbitrary tag.
    titleTag(): string {
      return this.headingLevel === 'h2' ? 'h2' : 'h3';
    },
    // The help panel is styled for a paragraph, which the old scrape also added.
    wrappedInlineHelp(): string {
      return this.inlineHelp ? `<p>${this.inlineHelp}</p>` : '';
    },
  },
  methods: {
    translate,
    onTitleClick() {
      if (this.titleClickable) {
        this.$emit('titleClick');
      }
    },
    onControl(intent: string) {
      // Re-emit for Vue-native consumers...
      this.$emit(intent as 'minimise'|'maximise'|'refresh'|'close');

      // ...and dispatch a bubbling native event so non-Vue owners (the jQuery dashboard
      // widget) can bridge control intents back to their existing handlers.
      this.$el.dispatchEvent(new CustomEvent(`widgetcontrol:${intent}`, { bubbles: true }));
    },
    onQueryInput(value: string) {
      this.query = value;
      if (this.searchDebounceTimer) {
        clearTimeout(this.searchDebounceTimer);
        this.searchDebounceTimer = null;
      }
      if (!value) {
        // Clearing applies immediately so all results come back without a pause.
        this.dispatchSearch();
        return;
      }
      this.searchDebounceTimer = setTimeout(() => this.dispatchSearch(), SEARCH_DEBOUNCE_MS);
    },
    dispatchSearch() {
      this.searchDebounceTimer = null;
      // Re-emit for Vue-native consumers...
      this.$emit('search', { keyword: this.query });

      // ...and dispatch a bubbling native event so the jQuery DataTable can apply the filter.
      this.$el.dispatchEvent(new CustomEvent('reportheader:search', {
        bubbles: true,
        detail: { keyword: this.query },
      }));
    },
  },
  beforeUnmount() {
    if (this.searchDebounceTimer) {
      clearTimeout(this.searchDebounceTimer);
    }
  },
});
</script>
