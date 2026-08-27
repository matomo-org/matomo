<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="reportHeader"
    :class="{
      'reportHeader--flush': isFullPage && !plainTitle,
      'reportHeader--plainTitle': plainTitle,
    }"
  >
    <!-- First line: the title, the widget controls and the report's own menu. -->
    <div v-if="!isEmpty" class="reportHeader__header">
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

      <!-- Widget controls: hidden until the widget is hovered/focused. Each action emits an
           intent that onControl() bridges to the jQuery widget. -->
      <div class="reportHeader__controls">
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

        <!-- The report's 3-dots menu. The panel is one menu built from the report's own actions;
           when the report has none the whole toolbar stays `:empty` and claims none of the
           header's gap. -->
        <div class="reportHeader__toolbar">
          <div
            v-if="showActions"
            ref="actions"
            class="reportHeader__actions"
            v-expand-on-click="{ expander: 'actionsTrigger' }"
          >
            <button
              ref="actionsTrigger"
              type="button"
              class="reportHeader__actionsTrigger"
              :title="translate('CoreHome_ReportActions')"
              :aria-label="translate('CoreHome_ReportActions')"
            >
              <span class="icon-more-verti" aria-hidden="true" />
            </button>

            <!-- ExpandOnClick only closes on a click *outside* the element, so picking an action
               would otherwise leave the menu hanging open over the reloading report. The
               dropdown it replaces closed on item click, so match that. -->
            <!-- The click handler only dismisses the menu after an entry was chosen. Every entry is
               a focusable control of its own and Escape closes the menu through ExpandOnClick, so
               no keyboard path depends on it. -->
            <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events -->
            <div class="reportHeader__actionsMenu" @click="closeActions">
              <div class="mtm-dropdownPanel mtm-dropdownPanel--wide mtm-dropdownPanel--withSubmenu">
                <DataTableActions
                  placement="header"
                  :show-footer="showFooter"
                  :show-footer-icons="showFooterIcons"
                  :footer-icons="footerIcons"
                  :report-title="titleText"
                  :request-params="requestParams"
                  :api-method-to-request-data-table="apiMethodToRequestDataTable"
                  :max-filter-limit="maxFilterLimit"
                  :show-annotations="showAnnotations"
                  :annotations-showing="annotationsShowing"
                  :show-periods="showPeriods"
                  :show-export="showExport"
                  :show-export-as-image-icon="showExportAsImageIcon"
                  :report-id="reportId"
                  :data-table-actions="dataTableActions"
                  :show-flatten-table="showFlattenTable"
                  :report-supports-flatten="reportSupportsFlatten"
                  :report-supports-percentage-values="reportSupportsPercentageValues"
                  :export-supports-flatten="exportSupportsFlatten"
                  :client-side-parameters="clientSideParameters"
                  :has-multiple-dimensions="hasMultipleDimensions"
                  :is-data-table-empty="isDataTableEmpty"
                  :show-totals-row="showTotalsRow"
                  :show-exclude-low-population="showExcludeLowPopulation"
                  :show-pivot-by-subtable="showPivotBySubtable"
                  :translations="actionTranslations"
                  :view-data-table="viewDataTable"
                  :pivot-dimension-name="pivotDimensionName"
                  :selectable-periods="selectablePeriods"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Second line, mounted independently of the first so a titleless report still gets its
         search. Only the search lives here today; a later story adding a sibling must widen the
         condition below. -->
    <div v-if="canSearch" class="reportHeader__subheader">
      <div class="reportHeader__search">
        <SearchInput
          :model-value="query"
          :placeholder="searchPlaceholder"
          :show-clear="true"
          @update:model-value="onQueryInput"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import DataTableActions, {
  DataTableAction,
  FooterIconGroup,
} from '../DataTable/DataTableActions.vue';
import EnrichedHeadline from '../EnrichedHeadline/EnrichedHeadline.vue';
import ExpandOnClick from '../ExpandOnClick/ExpandOnClick';
import SearchInput from '../SearchInput/SearchInput.vue';
import WidgetControls from '../WidgetControls/WidgetControls.vue';
import { translate } from '../translate';

// A search is a full DataTable reload, so debounce to avoid one on every keystroke.
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
    // A titleless widgetized embed still mounts the subheader for its search, so only the
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
    // Shows the search input; dataTable.js drives it from the report's `show_search` config.
    showSearch: Boolean,
    // Current server-side pattern, pushed back after each reload to keep the field in sync.
    searchQuery: {
      type: String,
      default: '',
    },
    searchPlaceholder: {
      type: String,
      default: '',
    },
    // Report action props, forwarded to DataTableActions. They are declared here rather than on
    // a nested vue-entry because compileVueEntryComponents mounts parents first, which would
    // leave a nested entry detached before its own turn came.
    showFooter: Boolean,
    showFooterIcons: Boolean,
    footerIcons: {
      type: Array as PropType<FooterIconGroup[]>,
      default: () => [],
    },
    requestParams: {
      type: Object,
      default: () => ({}),
    },
    apiMethodToRequestDataTable: {
      type: String,
      default: '',
    },
    maxFilterLimit: {
      type: Number,
      default: 0,
    },
    showAnnotations: Boolean,
    annotationsShowing: Boolean,
    showPeriods: Boolean,
    showExport: Boolean,
    showExportAsImageIcon: Boolean,
    reportId: {
      type: String,
      default: '',
    },
    dataTableActions: {
      type: Array as PropType<DataTableAction[]>,
      default: () => [],
    },
    showFlattenTable: Boolean,
    reportSupportsFlatten: Boolean,
    reportSupportsPercentageValues: Boolean,
    exportSupportsFlatten: Boolean,
    clientSideParameters: {
      type: Object,
      default: () => ({}),
    },
    hasMultipleDimensions: Boolean,
    isDataTableEmpty: Boolean,
    showTotalsRow: Boolean,
    showExcludeLowPopulation: Boolean,
    showPivotBySubtable: Boolean,
    // Not `translations`: DataTableActions uses that name for its own period/label map, and the
    // header would otherwise shadow the global translate() helper in this file.
    actionTranslations: {
      type: Object,
      default: () => ({}),
    },
    viewDataTable: {
      type: String,
      default: '',
    },
    pivotDimensionName: {
      type: String as PropType<string|null>,
      default: null,
    },
    selectablePeriods: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
  },
  components: {
    DataTableActions,
    EnrichedHeadline,
    SearchInput,
    WidgetControls,
  },
  directives: {
    ExpandOnClick,
  },
  emits: ['minimise', 'maximise', 'refresh', 'close', 'titleClick', 'search'],
  data() {
    return {
      // Local mirror of the field, seeded from `searchQuery`.
      query: this.searchQuery,
      searchDebounceTimer: null as ReturnType<typeof setTimeout> | null,
    };
  },
  watch: {
    searchQuery(value: string) {
      // Server-driven update; reflect it into the field without dispatching another search.
      // Skip while a search is pending: a reload syncing the previous pattern back would
      // otherwise revert the user's in-flight typing.
      if (this.searchDebounceTimer) {
        return;
      }
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
    // The actions menu is offered wherever the report renders its footer icons. A subtable has
    // none: ViewDataTable forces show_footer_icons off for one, and it reuses its parent's
    // header anyway.
    showActions(): boolean {
      return this.showFooter && this.showFooterIcons;
    },
    // Whether the header line has nothing to render. Only that line is dropped: the subheader
    // below it is mounted independently, so a titleless widgetized report still gets its search.
    isEmpty(): boolean {
      return !this.showTitle && !this.hasControls && !this.showActions;
    },
    isFullPage(): boolean {
      return this.context === 'fullPage';
    },
    // Search is offered on real reports, but not where there is no table to search: the widget
    // preview is a static thumbnail, and a minimised widget has its content hidden.
    canSearch(): boolean {
      return this.showSearch && this.context !== 'preview' && this.context !== 'collapsed';
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
    closeActions() {
      (this.$refs.actions as HTMLElement | undefined)?.classList.remove('expanded');
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
        this.dispatchSearch(value);
        return;
      }
      this.searchDebounceTimer = setTimeout(() => this.dispatchSearch(value), SEARCH_DEBOUNCE_MS);
    },
    dispatchSearch(keyword: string) {
      this.searchDebounceTimer = null;
      // Re-emit for Vue-native consumers...
      this.$emit('search', { keyword });

      // ...and dispatch a bubbling native event so the jQuery DataTable can apply the filter.
      this.$el.dispatchEvent(new CustomEvent('reportheader:search', {
        bubbles: true,
        detail: { keyword },
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
