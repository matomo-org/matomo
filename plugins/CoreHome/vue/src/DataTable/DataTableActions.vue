<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="showFooter && showFooterIcons">
    <!-- Report actions live in the report header, inside the single menu its 3-dots trigger
         opens. Three lists rather than one: `ul.tableConfiguration` and `.dataTableFooterIcons`
         are the hooks every dataTable.js handler binds to, and adjacent lists carry no margin,
         so this still reads as one continuous menu. -->
    <template v-if="isInHeader">
      <ul
        v-if="showConfigItems"
        :id="`dropdownConfigure${randomIdForDropdown}`"
        class="mtm-dropdownPanel__menu tableConfiguration"
      >
        <li v-if="showFlattenTable" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableFlatten"
            role="menuitemcheckbox"
            tabindex="0"
            :aria-checked="configState.flat"
            @keydown.enter.prevent="activateItem"
            @keydown.space.prevent="activateItem"
          >
            <span class="mtm-dropdownPanel__menuLabel">{{ flattenItemText }}</span>
            <span
              v-if="configState.flat"
              class="mtm-dropdownPanel__rightIcon"
              aria-hidden="true"
            ><span class="icon-ok" /></span>
          </div>
        </li>
        <li v-if="showDimensionsConfigItem" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableShowDimensions"
            role="menuitemcheckbox"
            tabindex="0"
            :aria-checked="configState.dimensions"
            @keydown.enter.prevent="activateItem"
            @keydown.space.prevent="activateItem"
          >
            <span class="mtm-dropdownPanel__menuLabel">{{ showDimensionsText }}</span>
            <span
              v-if="configState.dimensions"
              class="mtm-dropdownPanel__rightIcon"
              aria-hidden="true"
            ><span class="icon-ok" /></span>
          </div>
        </li>
        <li v-if="showFlatConfigItem" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableIncludeAggregateRows"
            role="menuitemcheckbox"
            tabindex="0"
            :aria-checked="configState.aggregateRows"
            @keydown.enter.prevent="activateItem"
            @keydown.space.prevent="activateItem"
          >
            <span class="mtm-dropdownPanel__menuLabel">{{ includeAggregateRowsText }}</span>
            <span
              v-if="configState.aggregateRows"
              class="mtm-dropdownPanel__rightIcon"
              aria-hidden="true"
            ><span class="icon-ok" /></span>
          </div>
        </li>
        <li v-if="showTotalsConfigItem" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableShowTotalsRow"
            role="menuitemcheckbox"
            tabindex="0"
            :aria-checked="configState.totalsRow"
            @keydown.enter.prevent="activateItem"
            @keydown.space.prevent="activateItem"
          >
            <span class="mtm-dropdownPanel__menuLabel">{{ keepTotalsRowText }}</span>
            <span
              v-if="configState.totalsRow"
              class="mtm-dropdownPanel__rightIcon"
              aria-hidden="true"
            ><span class="icon-ok" /></span>
          </div>
        </li>
        <li v-if="showPercentageValuesConfigItem" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableShowPercentageValues"
            role="menuitemcheckbox"
            tabindex="0"
            :aria-checked="configState.percentages"
            @keydown.enter.prevent="activateItem"
            @keydown.space.prevent="activateItem"
          >
            <span class="mtm-dropdownPanel__menuLabel">{{ percentageValuesText }}</span>
            <span
              v-if="configState.percentages"
              class="mtm-dropdownPanel__rightIcon"
              aria-hidden="true"
            ><span class="icon-ok" /></span>
          </div>
        </li>
        <li v-if="showExcludeLowPopulation" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableExcludeLowPopulation"
            role="menuitemcheckbox"
            tabindex="0"
            :aria-checked="configState.lowPopulation"
            @keydown.enter.prevent="activateItem"
            @keydown.space.prevent="activateItem"
          >
            <span class="mtm-dropdownPanel__menuLabel">{{ excludeLowPopText }}</span>
            <span
              v-if="configState.lowPopulation"
              class="mtm-dropdownPanel__rightIcon"
              aria-hidden="true"
            ><span class="icon-ok" /></span>
          </div>
        </li>
        <li v-if="showPivotBySubtable" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTablePivotBySubtable"
            role="menuitemcheckbox"
            tabindex="0"
            :aria-checked="configState.pivoted"
            @keydown.enter.prevent="activateItem"
            @keydown.space.prevent="activateItem"
          >
            <span class="mtm-dropdownPanel__menuLabel">{{ pivotByText }}</span>
            <span
              v-if="configState.pivoted"
              class="mtm-dropdownPanel__rightIcon"
              aria-hidden="true"
            ><span class="icon-ok" /></span>
          </div>
        </li>
      </ul>

      <ul class="mtm-dropdownPanel__menu">
        <!-- Keeps `dataTablePeriods` on the list and `tableIcon` on each entry: that pair is what
             dataTable.js binds the period change to. -->
        <li v-if="showPeriods && !isPromoted('periods')" class="mtm-dropdownPanel__menuItem">
          <a
            class="mtm-dropdownPanel__menuLink dataTableAction activatePeriodsSelection"
            href=""
            aria-haspopup="menu"
            :aria-expanded="periodsOpen ? 'true' : 'false'"
            @click.prevent.stop="periodsOpen = !periodsOpen"
          >
            <span class="mtm-dropdownPanel__menuLabel">
              {{ translate('CoreHome_ShowPeriod') }}
            </span>
            <span class="mtm-dropdownPanel__rightIcon"><span class="icon-chevron-right" /></span>
          </a>
          <div
            class="mtm-dropdownPanel__submenu"
            :class="{ 'mtm-dropdownPanel__submenu--open': periodsOpen }"
          >
            <div class="mtm-dropdownPanel">
              <PeriodsMenu
                :selectable-periods="selectablePeriods"
                :active-period="`${clientSideParameters.period || ''}`"
                :labels="translations"
                @pick="closePeriods"
              />
            </div>
          </div>
        </li>

        <!-- Keeps `annotationView`: dataTable.js binds the toggle on it and
             handleEvolutionAnnotations() reads it to decide whether the graph shows markers. -->
        <li v-if="showAnnotations" class="mtm-dropdownPanel__menuItem">
          <a
            class="mtm-dropdownPanel__menuLink dataTableAction annotationView"
            href=""
            :title="annotationsTitle"
            @click.prevent
          >
            <span class="mtm-dropdownPanel__menuLabel">
              {{ annotationsLabel }}
            </span>
          </a>
        </li>

        <li v-if="showExportAsImageIcon" class="mtm-dropdownPanel__menuItem">
          <a
            class="mtm-dropdownPanel__menuLink dataTableAction tableIcon"
            href=""
            :id="`dataTableExportAsImageIcon-${placement}`"
            @click.prevent="showExportImage($event)"
          >
            <span class="mtm-dropdownPanel__menuLabel">
              {{ translate('CoreHome_ExportImage') }}
            </span>
          </a>
        </li>
        <li v-if="showExport" class="mtm-dropdownPanel__menuItem">
          <a
            class="mtm-dropdownPanel__menuLink dataTableAction activateExportSelection"
            v-report-export="{
              reportTitle,
              requestParams,
              apiMethod: apiMethodToRequestDataTable,
              reportFormats,
              maxFilterLimit,
              canExportFlat: exportSupportsFlat,
            }"
            href=""
            @click.prevent
          >
            <span class="mtm-dropdownPanel__menuLabel">{{ translate('CoreHome_ExportData') }}</span>
          </a>
        </li>
        <li
          v-for="action in dataTableActions"
          :key="action.id"
          class="mtm-dropdownPanel__menuItem"
        >
          <a
            :class="`mtm-dropdownPanel__menuLink dataTableAction ${action.id}`"
            href=""
            @click.prevent
          >
            <span class="mtm-dropdownPanel__menuLabel">{{ action.title }}</span>
          </a>
        </li>
      </ul>

      <ul
        :id="`dropdownVisualizations${randomIdForDropdown}`"
        class="mtm-dropdownPanel__menu dataTableFooterIcons"
      >
        <Passthrough v-for="(footerIconGroup, index) in visibleFooterIconGroups" :key="index">
          <li
            v-if="showsSeparatorBefore(index)"
            class="mtm-dropdownPanel__separator"
            role="separator"
          />
          <li
            v-for="footerIcon in footerIconGroup.buttons"
            :key="footerIcon.id"
            class="mtm-dropdownPanel__menuItem"
          >
            <a
              :class="`mtm-dropdownPanel__menuLink ${footerIconGroup.class} tableIcon
                ${activeFooterIconIds.indexOf(footerIcon.id) !== -1 ? 'activeIcon' : ''}`"
              :data-footer-icon-id="footerIcon.id"
              role="menuitemradio"
              :aria-checked="activeFooterIconIds.indexOf(footerIcon.id) !== -1"
            >
              <span
                v-if="/^icon-/.test(footerIcon.icon || '')"
                class="mtm-dropdownPanel__menuIcon"
                :class="footerIcon.icon"
              />
              <img
                v-else
                class="mtm-dropdownPanel__menuIcon"
                width="16"
                height="16"
                :src="footerIcon.icon"
                alt=""
              />
              <span class="mtm-dropdownPanel__menuLabel">{{ footerIcon.title }}</span>
              <span
                v-if="activeFooterIconIds.indexOf(footerIcon.id) !== -1"
                class="mtm-dropdownPanel__rightIcon"
                aria-hidden="true"
              ><span class="icon-ok" /></span>
            </a>
          </li>
        </Passthrough>
      </ul>
    </template>

  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import Passthrough from '../Passthrough/Passthrough.vue';
import PeriodsMenu from './PeriodsMenu.vue';
import type { PromotableActionId } from './reportActions';
import ReportExport from '../ReportExport/ReportExport';
import { translate } from '../translate';
import { isBooleanLikeSet, resolveExportSupportsFlat } from './DataTableActions.utils';
import findReportRoot from './reportScope';

export interface FooterIcon {
  id: string;
  icon?: string;
  title?: string;
}

export interface FooterIconGroup {
  buttons: FooterIcon[];
  class?: string;
}

export interface DataTableAction {
  id: string;
  icon?: string;
  title?: string;
}

const { $ } = window;

export default defineComponent({
  props: {
    showPeriods: Boolean,
    showFooter: Boolean,
    showFooterIcons: Boolean,
    // Not used in this component: dataTable.js reads it to decide whether the ReportHeader shows
    // a search input for this report.
    showSearch: Boolean,
    showFlattenTable: Boolean,
    reportSupportsFlatten: Boolean,
    reportSupportsPercentageValues: Boolean,
    exportSupportsFlatten: Boolean,
    footerIcons: {
      type: Array as PropType<FooterIconGroup[]>,
      required: true,
    },
    viewDataTable: {
      type: String,
      required: true,
    },
    reportTitle: String,
    requestParams: {
      type: Object,
      required: true,
    },
    apiMethodToRequestDataTable: {
      type: String,
      required: true,
    },
    maxFilterLimit: {
      type: Number,
      required: true,
    },
    showExport: Boolean,
    showExportAsImageIcon: Boolean,
    showAnnotations: Boolean,
    annotationsShowing: Boolean,
    reportId: {
      type: String,
      required: true,
    },
    dataTableActions: {
      type: Array as PropType<DataTableAction[]>,
      required: true,
    },
    clientSideParameters: {
      type: Object,
      required: true,
    },
    hasMultipleDimensions: Boolean,
    isDataTableEmpty: Boolean,
    showTotalsRow: Boolean,
    showExcludeLowPopulation: Boolean,
    showPivotBySubtable: Boolean,
    selectablePeriods: Array as PropType<string[]>,
    translations: {
      type: Object,
      required: true,
    },
    // both templates that mount this send null when the report has no pivot dimension
    pivotDimensionName: String as PropType<string|null>,
    placement: {
      type: String,
      default: 'footer',
    },
    // Rendered by the header outside this menu, so not offered twice.
    promotedActions: {
      type: Array as PropType<PromotableActionId[]>,
      default: () => [],
    },
  },
  components: {
    Passthrough,
    PeriodsMenu,
  },
  directives: {
    ReportExport,
  },
  methods: {
    showsSeparatorBefore(index: number): boolean {
      if (index === 0) {
        return this.hasActionsAbove;
      }

      // Insights heads the visualisation list rather than standing on its own, so the rule falls
      // before it and not after. Absent, the graphs keep the one they would have had.
      return this.visibleFooterIconGroups[index - 1].class !== 'tableInsightViews';
    },
    // Not links the browser would follow, so a key press has to click them for the delegated
    // handler in dataTable.js to hear.
    activateItem(event: KeyboardEvent) {
      (event.currentTarget as HTMLElement | null)?.click();
    },
    isPromoted(action: PromotableActionId): boolean {
      return this.promotedActions.indexOf(action) !== -1;
    },
    // Picking a period is the end of the interaction, so the submenu folds with it. Not stopping
    // the event: dataTable.js listens for it further up, and the panel closes on it too.
    closePeriods() {
      this.periodsOpen = false;
    },
    showExportImage(event: Event) {
      findReportRoot(event.target as HTMLElement)
        .find('div.jqplot-target')
        .trigger('piwikExportAsImage');
    },
  },
  data() {
    return {
      // The submenu is opened from inside this panel, so its state belongs to this component.
      // Outlives a menu close on purpose, so the submenu is already open next time - kinder to
      // anyone who finds the pointer hard to place precisely. Picking a period does clear it.
      periodsOpen: false,
    };
  },
  computed: {
    // Only the header placement renders anything now; the footer one is kept as the carrier
    // dataTable.js reads this report's config off.
    isInHeader(): boolean {
      return this.placement === 'header';
    },
    annotationsLabel(): string {
      return this.annotationsShowing
        ? translate('Annotations_HideAnnotations')
        : translate('Annotations_ShowAnnotations');
    },
    annotationsTitle(): string {
      return this.annotationsShowing
        ? translate('Annotations_IconDescHideNotes')
        : translate('Annotations_IconDesc');
    },
    randomIdForDropdown(): number {
      return Math.floor(Math.random() * 999999);
    },
    allFooterIcons(): FooterIcon[] {
      return (this.footerIcons as FooterIconGroup[]).reduce((icons, footerIcon) => {
        icons.push(...footerIcon.buttons);
        return icons;
      }, [] as FooterIcon[]);
    },
    activeFooterIcons(): FooterIcon[] {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;

      const result = [this.viewDataTable];
      if (params.abandonedCarts === 0 || params.abandonedCarts === '0') {
        result.push('ecommerceOrder');
      } else if (params.abandonedCarts === 1 || params.abandonedCarts === '1') {
        result.push('ecommerceAbandonedCart');
      }

      return result
        .map((id) => this.allFooterIcons.find((button) => button.id === id))
        .filter((icon) => !!icon) as FooterIcon[];
    },
    // A group whose buttons all lack an icon renders nothing, and a separator for it would end the
    // menu on a rule with nothing under it.
    visibleFooterIconGroups(): FooterIconGroup[] {
      return (this.footerIcons as FooterIconGroup[])
        .map((group) => ({ ...group, buttons: group.buttons.filter((i) => !!i.icon) }))
        .filter((group) => group.buttons.length > 0);
    },
    // Whether anything renders above the visualisation lists, so their leading separator has
    // something to separate from.
    hasActionsAbove(): boolean {
      return this.showConfigItems
        || (this.showPeriods && !this.isPromoted('periods'))
        || this.showAnnotations
        || this.showExportAsImageIcon
        || this.showExport
        || this.dataTableActions.length > 0;
    },
    activeFooterIconIds(): string[] {
      return this.activeFooterIcons.map((icon) => icon.id);
    },
    reportFormats(): Record<string, string> {
      const formats: Record<string, string> = {
        TSV: 'TSV (Excel)',
        HTML: 'HTML',
        JSON: 'JSON',
        XML: 'XML',
        CSV: 'CSV',
        RSS: 'RSS',
      };
      return formats;
    },
    exportSupportsFlat() {
      return resolveExportSupportsFlat(
        !!this.exportSupportsFlatten,
        this.clientSideParameters.flat as number|string|boolean,
      );
    },
    showDimensionsConfigItem() {
      return this.showFlattenTable
        && `${this.clientSideParameters.flat}` === '1'
        && this.hasMultipleDimensions;
    },
    showFlatConfigItem() {
      return this.showFlattenTable && `${this.clientSideParameters.flat}` === '1';
    },
    showTotalsConfigItem() {
      return !this.isDataTableEmpty && this.showTotalsRow;
    },
    showPercentageValuesConfigItem() {
      return !this.isDataTableEmpty && this.reportSupportsPercentageValues;
    },
    // What each first-group entry is currently doing. The labels no longer say it, a tick does.
    configState(): Record<string, boolean> {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return {
        flat: isBooleanLikeSet(params.flat),
        dimensions: isBooleanLikeSet(params.show_dimensions),
        aggregateRows: isBooleanLikeSet(params.include_aggregate_rows),
        totalsRow: isBooleanLikeSet(params.keep_totals_row),
        percentages: isBooleanLikeSet(params.show_percentage_values),
        lowPopulation: isBooleanLikeSet(params.enable_filter_excludelowpop),
        pivoted: isBooleanLikeSet(params.pivotBy),
      };
    },
    flattenItemText() {
      return translate('CoreHome_MakeItFlat');
    },
    keepTotalsRowText() {
      return translate('CoreHome_ShowTotalsRow');
    },
    percentageValuesText() {
      return translate('CoreHome_ShowPercentageValues');
    },
    includeAggregateRowsText() {
      return translate('CoreHome_ShowAggregateRows');
    },
    showDimensionsText() {
      return translate('CoreHome_ShowDimensionsSeparately');
    },
    pivotByText() {
      return translate('CoreHome_PivotBy', this.pivotDimensionName || '');
    },
    excludeLowPopText() {
      return translate('CoreHome_ExcludeLowPopulation');
    },
    // Every config entry acts on a table, so a graph offers none - except where one is already
    // applied, which has to stay reachable to be undone. This is the gate the configure icon
    // carried before the actions moved into the header's single menu.
    showConfigItems(): boolean {
      return this.isTableView || this.isAnyConfigureIconHighlighted;
    },
    isTableView(): boolean {
      return this.viewDataTable === 'table'
        || this.viewDataTable === 'tableAllColumns'
        || this.viewDataTable === 'tableGoals';
    },
    isAnyConfigureIconHighlighted(): boolean {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return isBooleanLikeSet(params.flat)
        || isBooleanLikeSet(params.keep_totals_row)
        || isBooleanLikeSet(params.include_aggregate_rows)
        || isBooleanLikeSet(params.show_dimensions)
        || isBooleanLikeSet(params.pivotBy)
        || isBooleanLikeSet(params.enable_filter_excludelowpop)
        || isBooleanLikeSet(params.show_percentage_values);
    },
  },
});
</script>
