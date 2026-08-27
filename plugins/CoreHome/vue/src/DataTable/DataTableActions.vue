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
          >
            <span
              class="mtm-dropdownPanel__menuLabel mtm-dropdownPanel__menuLabel--stacked"
              v-html="$sanitize(flattenItemText)"
            />
          </div>
        </li>
        <li v-if="showDimensionsConfigItem" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableShowDimensions"
          >
            <span
              class="mtm-dropdownPanel__menuLabel mtm-dropdownPanel__menuLabel--stacked"
              v-html="$sanitize(showDimensionsText)"
            />
          </div>
        </li>
        <li v-if="showFlatConfigItem" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableIncludeAggregateRows"
          >
            <span
              class="mtm-dropdownPanel__menuLabel mtm-dropdownPanel__menuLabel--stacked"
              v-html="$sanitize(includeAggregateRowsText)"
            />
          </div>
        </li>
        <li v-if="showTotalsConfigItem" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableShowTotalsRow"
          >
            <span
              class="mtm-dropdownPanel__menuLabel mtm-dropdownPanel__menuLabel--stacked"
              v-html="$sanitize(keepTotalsRowText)"
            />
          </div>
        </li>
        <li v-if="showPercentageValuesConfigItem" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableShowPercentageValues"
            :aria-label="percentageValuesLabel"
          >
            <span
              class="mtm-dropdownPanel__menuLabel mtm-dropdownPanel__menuLabel--stacked"
              v-html="$sanitize(percentageValuesText)"
            />
          </div>
        </li>
        <li v-if="showExcludeLowPopulation" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTableExcludeLowPopulation"
          >
            <span
              class="mtm-dropdownPanel__menuLabel mtm-dropdownPanel__menuLabel--stacked"
              v-html="$sanitize(excludeLowPopText)"
            />
          </div>
        </li>
        <li v-if="showPivotBySubtable" class="mtm-dropdownPanel__menuItem">
          <div
            class="mtm-dropdownPanel__menuLink configItem dataTablePivotBySubtable"
          >
            <span
              class="mtm-dropdownPanel__menuLabel mtm-dropdownPanel__menuLabel--stacked"
              v-html="$sanitize(pivotByText)"
            />
          </div>
        </li>
      </ul>

      <ul class="mtm-dropdownPanel__menu">
        <!-- Keeps `dataTablePeriods` on the list and `tableIcon` on each entry: that pair is what
             dataTable.js binds the period change to. -->
        <li v-if="showPeriods" class="mtm-dropdownPanel__menuItem">
          <a
            class="mtm-dropdownPanel__menuLink dataTableAction activatePeriodsSelection"
            href=""
            aria-haspopup="menu"
            :aria-expanded="periodsOpen ? 'true' : 'false'"
            @click.prevent.stop="periodsOpen = !periodsOpen"
          >
            <span class="mtm-dropdownPanel__menuIcon"><span class="icon-calendar" /></span>
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
              <ul class="mtm-dropdownPanel__menu dataTablePeriods" role="menu">
                <li
                  v-for="selectablePeriod in selectablePeriods"
                  :key="selectablePeriod"
                  class="mtm-dropdownPanel__menuItem"
                  role="none"
                >
                  <a
                    :data-period="selectablePeriod"
                    role="menuitem"
                    tabindex="0"
                    :aria-current="clientSideParameters.period === selectablePeriod"
                    :class="`mtm-dropdownPanel__menuLink tableIcon ${clientSideParameters.period
                      === selectablePeriod ? 'activeIcon' : ''}`"
                    @click="closePeriods"
                    @keydown.enter.prevent="activateItem"
                    @keydown.space.prevent="activateItem"
                  >
                    <span class="mtm-dropdownPanel__menuLabel">
                      {{ translations[selectablePeriod] || selectablePeriod }}
                    </span>
                  </a>
                </li>
              </ul>
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
            <span class="mtm-dropdownPanel__menuIcon"><span class="icon-annotation" /></span>
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
            <span class="mtm-dropdownPanel__menuIcon"><span class="icon-image" /></span>
            <span class="mtm-dropdownPanel__menuLabel">
              {{ translate('General_ExportAsImage') }}
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
            <span class="mtm-dropdownPanel__menuIcon"><span class="icon-export" /></span>
            <span class="mtm-dropdownPanel__menuLabel">{{ translate('General_Export') }}</span>
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
            <span
              v-if="/^icon-/.test(action.icon || '')"
              class="mtm-dropdownPanel__menuIcon"
              :class="action.icon"
            />
            <img
              v-else
              class="mtm-dropdownPanel__menuIcon"
              width="16"
              height="16"
              :src="action.icon"
              alt=""
            />
            <span class="mtm-dropdownPanel__menuLabel">{{ action.title }}</span>
          </a>
        </li>
      </ul>

      <ul
        :id="`dropdownVisualizations${randomIdForDropdown}`"
        class="mtm-dropdownPanel__menu dataTableFooterIcons"
      >
        <Passthrough v-for="(footerIconGroup, index) in footerIcons" :key="index">
          <li
            v-if="index > 0"
            class="mtm-dropdownPanel__separator"
            role="separator"
          />
          <li
            v-for="footerIcon in footerIconGroup.buttons.filter((i) => !!i.icon)"
            :key="footerIcon.id"
            class="mtm-dropdownPanel__menuItem"
          >
            <a
              :class="`mtm-dropdownPanel__menuLink ${footerIconGroup.class} tableIcon
                ${activeFooterIconIds.indexOf(footerIcon.id) !== -1 ? 'activeIcon' : ''}`"
              :data-footer-icon-id="footerIcon.id"
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

function getSingleStateIconText(text: string, addDefault?: boolean, replacement?: string) {
  if (/(%(.\$)?s+)/g.test(translate(text))) {
    const values = ['<span class="mtm-dropdownPanel__menuAction action">'];
    if (replacement) {
      values.push(replacement);
    }
    let result = translate(text, ...values);
    if (addDefault) {
      result += ` (${translate('CoreHome_Default')})`;
    }
    result += '</span>';
    return result;
  }

  return translate(text);
}

function getToggledIconText(toggled: boolean, textToggled: string, textUntoggled: string) {
  if (toggled) {
    return getSingleStateIconText(textToggled, true);
  }

  return getSingleStateIconText(textUntoggled);
}

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
  },
  components: {
    Passthrough,
  },
  directives: {
    ReportExport,
  },
  methods: {
    // Picking a period is the end of the interaction, so the submenu folds with it. Not stopping
    // the event: dataTable.js listens for it further up, and the panel closes on it too.
    // The item is not a link, so a key press has to click it for the delegated handler to hear.
    activateItem(event: KeyboardEvent) {
      (event.currentTarget as HTMLElement | null)?.click();
    },
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
    flattenItemText() {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return getToggledIconText(
        isBooleanLikeSet(params.flat),
        'CoreHome_UnFlattenDataTable',
        'CoreHome_FlattenDataTable',
      );
    },
    keepTotalsRowText() {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return getToggledIconText(
        isBooleanLikeSet(params.keep_totals_row),
        'CoreHome_RemoveTotalsRowDataTable',
        'CoreHome_AddTotalsRowDataTable',
      );
    },
    percentageValuesText() {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return getToggledIconText(
        isBooleanLikeSet(params.show_percentage_values),
        'CoreHome_ShowAbsoluteValuesDataTable',
        'CoreHome_ShowPercentageValuesDataTable',
      );
    },
    percentageValuesLabel() {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return isBooleanLikeSet(params.show_percentage_values)
        ? translate('CoreHome_ShowAbsoluteValues')
        : translate('CoreHome_ShowPercentageValues');
    },
    includeAggregateRowsText() {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return getToggledIconText(
        isBooleanLikeSet(params.include_aggregate_rows),
        'CoreHome_DataTableExcludeAggregateRows',
        'CoreHome_DataTableIncludeAggregateRows',
      );
    },
    showDimensionsText() {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return getToggledIconText(
        isBooleanLikeSet(params.show_dimensions),
        'CoreHome_DataTableCombineDimensions',
        'CoreHome_DataTableShowDimensions',
      );
    },
    pivotByText() {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      if (isBooleanLikeSet(params.pivotBy)) {
        return getSingleStateIconText('CoreHome_UndoPivotBySubtable', true);
      }

      return getSingleStateIconText('CoreHome_PivotBySubtable', false, this.pivotDimensionName
        || undefined);
    },
    excludeLowPopText() {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return getToggledIconText(
        isBooleanLikeSet(params.enable_filter_excludelowpop),
        'CoreHome_IncludeRowsWithLowPopulation',
        'CoreHome_ExcludeRowsWithLowPopulation',
      );
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
