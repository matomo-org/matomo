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
    <div v-if="!isEmpty" ref="headerRow" class="reportHeader__header">
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
      <div ref="headerControls" class="reportHeader__controls">
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
          <!-- Promoted report actions, never beside the widget controls: separate scopes.
               Least deserving first, so the highest rank sits nearest the trigger and a new one
               appears to its left rather than pushing the others along. -->
          <!-- One toggle, so it needs no panel and no label: the icon and its title carry it. -->
          <div
            v-if="isPromoted('annotations')"
            class="mtm-selector mtm-selector--iconOnly"
            data-report-action="annotations"
          >
            <button
              type="button"
              class="mtm-selector__trigger annotationView"
              :title="annotationsTitle"
              :aria-label="annotationsTitle"
              :aria-pressed="annotationsShowing ? 'true' : 'false'"
            >
              <span class="mtm-selector__icon" aria-hidden="true">
                <span class="icon-annotation" />
              </span>
            </button>
          </div>

          <div
            v-if="isPromoted('export')"
            ref="exportSelector"
            class="mtm-selector"
            data-report-action="export"
            v-expand-on-click="{
              expander: 'exportTrigger',
              onExpand: () => { exportExpanded = true; },
              onClosed: () => { exportExpanded = false; },
            }"
          >
            <button
              ref="exportTrigger"
              type="button"
              class="mtm-selector__trigger"
              aria-haspopup="menu"
              :aria-expanded="exportExpanded ? 'true' : 'false'"
            >
              <span class="mtm-selector__icon" aria-hidden="true">
                <span class="icon-export" />
              </span>
              <span class="mtm-selector__label">{{ translate('General_Export') }}</span>
              <span class="mtm-selector__rightIcon" aria-hidden="true">
                <span class="icon-chevron-down" />
              </span>
            </button>
            <!-- Choosing an export is the end of the interaction, so the panel folds with it. -->
            <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events -->
            <div class="mtm-selector__dropdown" @click="closePromotedExport">
              <div class="mtm-dropdownPanel">
                <ul
                  class="mtm-dropdownPanel__menu"
                  role="menu"
                  :aria-label="translate('General_Export')"
                >
                  <ExportMenu
                    :show-export="actions.showExport"
                    :show-export-as-image-icon="actions.showExportAsImageIcon"
                    :export-supports-flatten="actions.exportSupportsFlatten"
                    :client-side-parameters="actions.clientSideParameters"
                    :report-title="titleText"
                    :request-params="actions.requestParams"
                    :api-method-to-request-data-table="actions.apiMethodToRequestDataTable"
                    :max-filter-limit="actions.maxFilterLimit"
                    placement="header"
                  />
                </ul>
              </div>
            </div>
          </div>

          <div
            v-if="isPromoted('periods')"
            ref="periodsSelector"
            class="mtm-selector"
            data-report-action="periods"
            v-expand-on-click="{
              expander: 'periodsTrigger',
              onExpand: () => { periodsExpanded = true; },
              onClosed: () => { periodsExpanded = false; },
            }"
          >
            <button
              ref="periodsTrigger"
              type="button"
              class="mtm-selector__trigger"
              aria-haspopup="menu"
              :aria-expanded="periodsExpanded ? 'true' : 'false'"
            >
              <span class="mtm-selector__icon" aria-hidden="true">
                <span class="icon-calendar" />
              </span>
              <span class="mtm-selector__label">{{ translate('CoreHome_ShowPeriod') }}</span>
              <span class="mtm-selector__rightIcon" aria-hidden="true">
                <span class="icon-chevron-down" />
              </span>
            </button>
            <div class="mtm-selector__dropdown">
              <div class="mtm-dropdownPanel">
                <PeriodsMenu
                  :selectable-periods="actions.selectablePeriods"
                  :active-period="`${(actions.clientSideParameters || {}).period || ''}`"
                  :labels="actions.actionTranslations"
                  @pick="closePromotedPeriods"
                />
              </div>
            </div>
          </div>

          <div
            v-if="showActions"
            ref="actions"
            class="reportHeader__actions"
            v-expand-on-click="{
              expander: 'actionsTrigger',
              onExpand: (event) => { actionsExpanded = true; focusFirstAction(event); },
              onClosed: (event) => { actionsExpanded = false; restoreActionsFocus(event); },
            }"
          >
            <button
              ref="actionsTrigger"
              type="button"
              class="reportHeader__actionsTrigger"
              :title="translate('CoreHome_ReportActions')"
              :aria-label="translate('CoreHome_ReportActions')"
              aria-haspopup="menu"
              :aria-expanded="actionsExpanded ? 'true' : 'false'"
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
                  :promoted-actions="promotedActions"
                  :report-title="titleText"
                  :report-id="reportId"
                  :show-footer="actions.showFooter"
                  :show-footer-icons="actions.showFooterIcons"
                  :footer-icons="actions.footerIcons"
                  :request-params="actions.requestParams"
                  :api-method-to-request-data-table="actions.apiMethodToRequestDataTable"
                  :max-filter-limit="actions.maxFilterLimit"
                  :show-annotations="actions.showAnnotations"
                  :annotations-showing="annotationsShowing"
                  :show-periods="actions.showPeriods"
                  :show-export="actions.showExport"
                  :show-export-as-image-icon="actions.showExportAsImageIcon"
                  :data-table-actions="actions.dataTableActions"
                  :show-flatten-table="actions.showFlattenTable"
                  :report-supports-flatten="actions.reportSupportsFlatten"
                  :report-supports-percentage-values="actions.reportSupportsPercentageValues"
                  :export-supports-flatten="actions.exportSupportsFlatten"
                  :client-side-parameters="actions.clientSideParameters"
                  :has-multiple-dimensions="actions.hasMultipleDimensions"
                  :is-data-table-empty="actions.isDataTableEmpty"
                  :show-totals-row="actions.showTotalsRow"
                  :show-exclude-low-population="actions.showExcludeLowPopulation"
                  :show-pivot-by-subtable="actions.showPivotBySubtable"
                  :translations="actions.actionTranslations"
                  :view-data-table="actions.viewDataTable"
                  :pivot-dimension-name="actions.pivotDimensionName"
                  :selectable-periods="actions.selectablePeriods"
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
import ReportActionsStore from '../DataTable/ReportActions.store';
import ExportMenu from '../DataTable/ExportMenu.vue';
import PeriodsMenu from '../DataTable/PeriodsMenu.vue';
import {
  NO_PROMOTION_BREAKPOINT,
  PROMOTED_RENDERERS,
  promotableActions,
} from '../DataTable/reportActions';
import type { PromotableActionId } from '../DataTable/reportActions';
import type { ReportActionsConfig } from '../DataTable/ReportActions.store';
import { menuHoldsAnything } from '../DataTable/menuContents';
import reportIdentity from '../DataTable/reportIdentity';
import EnrichedHeadline from '../EnrichedHeadline/EnrichedHeadline.vue';
import ExpandOnClick from '../ExpandOnClick/ExpandOnClick';
import SearchInput from '../SearchInput/SearchInput.vue';
import WidgetControls from '../WidgetControls/WidgetControls.vue';
import { translate } from '../translate';

// A search is a full DataTable reload, so debounce to avoid one on every keystroke.
const SEARCH_DEBOUNCE_MS = 300;

// The title wraps instead of overflowing, so there is no overflow to detect: promote only while
// it keeps about a dozen characters.
const MIN_TITLE_WIDTH = 140;

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
    // Written by syncReportHeaderActions() with the key it published under, so this header cannot
    // read one nobody wrote.
    reportKey: {
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
    ExportMenu,
    PeriodsMenu,
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
      // Stands in until the first publish; see the reportKey prop.
      mountedReportKey: '',
      query: this.searchQuery,
      searchDebounceTimer: null as ReturnType<typeof setTimeout> | null,
      promotedCount: 0,
      periodsExpanded: false,
      exportExpanded: false,
      actionsExpanded: false,
      resizeObserver: null as ResizeObserver | null,
      lastMeasuredWidth: -1,
    };
  },
  mounted() {
    // Only until the table publishes and tells us the key it wrote - see syncReportHeaderActions().
    this.mountedReportKey = reportIdentity(this.$el as HTMLElement, this.reportId);
    this.watchForRoom();
    this.updatePromoted();
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
    promotable() {
      this.updatePromoted();
    },
  },
  computed: {
    promotable(): PromotableActionId[] {
      return promotableActions(this.actions)
        .filter((id) => PROMOTED_RENDERERS.indexOf(id) !== -1);
    },
    annotationsTitle(): string {
      return this.annotationsShowing
        ? translate('Annotations_HideAnnotations')
        : translate('Annotations_ShowAnnotations');
    },
    promotedActions(): PromotableActionId[] {
      return this.promotable.slice(0, this.promotedCount);
    },
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
    // What the menu renders from. Twig gives this header a starting point; on a reload the table is
    // replaced and this header is not, so twig's values then describe the load before it and
    // whatever the report published for itself wins.
    activeReportKey(): string {
      return this.reportKey || this.mountedReportKey;
    },
    actions(): ReportActionsConfig {
      return {
        showFooter: this.showFooter,
        showFooterIcons: this.showFooterIcons,
        footerIcons: this.footerIcons,
        viewDataTable: this.viewDataTable,
        clientSideParameters: this.clientSideParameters,
        isDataTableEmpty: this.isDataTableEmpty,
        showFlattenTable: this.showFlattenTable,
        reportSupportsFlatten: this.reportSupportsFlatten,
        reportSupportsPercentageValues: this.reportSupportsPercentageValues,
        exportSupportsFlatten: this.exportSupportsFlatten,
        hasMultipleDimensions: this.hasMultipleDimensions,
        showTotalsRow: this.showTotalsRow,
        showExcludeLowPopulation: this.showExcludeLowPopulation,
        showPivotBySubtable: this.showPivotBySubtable,
        dataTableActions: this.dataTableActions,
        showExport: this.showExport,
        showExportAsImageIcon: this.showExportAsImageIcon,
        showAnnotations: this.showAnnotations,
        showPeriods: this.showPeriods,
        selectablePeriods: this.selectablePeriods,
        requestParams: this.requestParams,
        maxFilterLimit: this.maxFilterLimit,
        apiMethodToRequestDataTable: this.apiMethodToRequestDataTable,
        pivotDimensionName: this.pivotDimensionName,
        actionTranslations: this.actionTranslations,
        ...ReportActionsStore.get(this.activeReportKey),
      } as ReportActionsConfig;
    },
    // A trigger opening onto nothing is worse than no trigger: once its ranks are promoted out, a
    // report may have nothing left to put in the menu.
    showActions(): boolean {
      return this.actions.showFooter && this.actions.showFooterIcons
        && menuHoldsAnything(this.actions, this.promotedActions);
    },
    // Whether the header line has nothing to render. Only that line is dropped: the subheader
    // below it is mounted independently, so a titleless widgetized report still gets its search.
    isEmpty(): boolean {
      return !this.showTitle && !this.hasControls && !this.showActions
        && this.promotedActions.length === 0;
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
    // Picking a period is the end of the interaction. ExpandOnClick keeps its state in the class,
    // so dropping it is how a panel closes itself - the same move MetricsPicker makes.
    closePromotedPeriods() {
      (this.$refs.periodsSelector as HTMLElement | undefined)?.classList.remove('expanded');
      this.periodsExpanded = false;
    },
    closePromotedExport() {
      (this.$refs.exportSelector as HTMLElement | undefined)?.classList.remove('expanded');
      this.exportExpanded = false;
    },
    isPromoted(action: PromotableActionId): boolean {
      return this.promotedActions.indexOf(action) !== -1;
    },
    // Watches the line's width only: promoting changes what is inside it, never how wide it is.
    watchForRoom() {
      const row = this.$refs.headerRow as HTMLElement | undefined;
      if (!row || typeof ResizeObserver === 'undefined') {
        return;
      }

      this.resizeObserver = new ResizeObserver(() => {
        if (row.clientWidth === this.lastMeasuredWidth) {
          return;
        }
        this.updatePromoted();
      });
      this.resizeObserver.observe(row);
    },
    // A control removed with its panel open never hears onClosed, and would come back announcing
    // itself expanded.
    demoteAll() {
      this.promotedCount = 0;
      this.periodsExpanded = false;
      this.exportExpanded = false;
    },
    async updatePromoted() {
      const row = this.$refs.headerRow as HTMLElement | undefined;
      // A header that is not laid out reports a phone-sized window, and demoting on that sticks:
      // the width that would undo it is the one already recorded, so nothing asks again.
      if (!row || row.clientWidth <= 0) {
        return;
      }

      const promotable = this.promotable.length;
      // Widget controls leave too little of the line to share, so nothing comes out beside them
      // until the fit is tuned more finely.
      if (!promotable || this.hasControls
        || window.matchMedia(NO_PROMOTION_BREAKPOINT).matches) {
        this.demoteAll();
        return;
      }

      // Try them all, then give back the least deserving until the title has room again.
      this.lastMeasuredWidth = row.clientWidth;
      this.promotedCount = promotable;
      await this.$nextTick();

      const controls = this.$refs.headerControls as HTMLElement | undefined;
      if (!controls) {
        return;
      }

      // A report with no title has no room to take.
      while (this.showTitle && this.promotedCount > 0
        && row.clientWidth - controls.offsetWidth < MIN_TITLE_WIDTH) {
        this.promotedCount -= 1;
        // eslint-disable-next-line no-await-in-loop
        await this.$nextTick();
      }
    },
    translate,
    onTitleClick() {
      if (this.titleClickable) {
        this.$emit('titleClick');
      }
    },
    closeActions(event: MouseEvent) {
      (this.$refs.actions as HTMLElement | undefined)?.classList.remove('expanded');
      this.actionsExpanded = false;
      this.restoreActionsFocus(event);
    },
    // Closing from the keyboard leaves the focus inside a panel about to disappear, and a menu
    // hands it back to the trigger. A pointer left the focus where the user put it, and taking it
    // would draw a focus ring they never asked for.
    restoreActionsFocus(event: MouseEvent|KeyboardEvent) {
      const actions = this.$refs.actions as HTMLElement | undefined;
      const byKeyboard = event.type === 'keyup' || (event as MouseEvent).detail === 0;

      if (byKeyboard && actions?.contains(document.activeElement)) {
        (this.$refs.actionsTrigger as HTMLElement | undefined)?.focus();
      }
    },
    // A button opened with the keyboard reports no pointer, and only then does a menu take the
    // focus off the trigger.
    focusFirstAction(event: MouseEvent|KeyboardEvent) {
      if ((event as MouseEvent).detail !== 0) {
        return;
      }

      this.$nextTick(() => {
        const actions = this.$refs.actions as HTMLElement | undefined;
        actions?.querySelector<HTMLElement>('[role^="menuitem"]')?.focus();
      });
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
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    }
  },
});
</script>
