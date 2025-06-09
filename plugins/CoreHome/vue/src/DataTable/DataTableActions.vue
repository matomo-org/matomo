<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="showFooter && showFooterIcons">
    <a
      v-dropdown-button
      class="dropdown-button dropdownConfigureIcon dataTableAction"
      :class="{highlighted: isAnyConfigureIconHighlighted}"
      href
      @click.prevent
      :data-target="`dropdownConfigure${randomIdForDropdown}`"
      style="margin-right:3.5px"
      v-if="hasConfigItems && (isAnyConfigureIconHighlighted || isTableView)"
    >
      <span class="icon-configure"></span>
    </a>

    <a v-if="hasFooterIconsToShow"
      class="dropdown-button dataTableAction activateVisualizationSelection"
      v-dropdown-button
      href
      :data-target="`dropdownVisualizations${randomIdForDropdown}`"
      style="margin-right:3.5px"
      @click.prevent
    >
      <span
        v-if="/^icon-/.test(activeFooterIcon || '')"
        :title="translate('CoreHome_ChangeVisualization')"
        :class="activeFooterIcon"
      ></span>
      <img
        v-else
        :title="translate('CoreHome_ChangeVisualization')"
        width="16"
        height="16"
        :src="activeFooterIcon"
      />
    </a>

    <ul
      v-if="showFooterIcons"
      :id="`dropdownVisualizations${randomIdForDropdown}`"
      class="dropdown-content dataTableFooterIcons"
    >
      <Passthrough v-for="(footerIconGroup, index) in footerIcons" :key="index">
        <li
          v-for="footerIcon in footerIconGroup.buttons.filter((i) => !!i.icon)"
          :key="footerIcon.id"
        >
          <a
            :class="`${footerIconGroup.class} tableIcon
              ${activeFooterIconIds.indexOf(footerIcon.id) !== -1 ? 'activeIcon' : ''}`"
            :data-footer-icon-id="footerIcon.id"
          >
            <span
              v-if="/^icon-/.test(footerIcon.icon || '')"
              :title="footerIcon.title"
              :class="footerIcon.icon"
              style="margin-right:5.5px"
            ></span>
            <img
              v-else
              width="16"
              height="16"
              :title="footerIcon.title"
              :src="footerIcon.icon"
              style="margin-right:5.5px"
            />
            <span v-if="footerIcon.title">{{ footerIcon.title }}</span>
          </a>
        </li>
        <li class="divider"></li>
      </Passthrough>
      <li class="divider"></li>
    </ul>

    <a
      v-if="showExport"
      class="dataTableAction activateExportSelection"
      v-report-export="{
        reportTitle,
        requestParams,
        apiMethod: apiMethodToRequestDataTable,
        reportFormats,
        maxFilterLimit,
      }"
      :title="translate('General_ExportThisReport')"
      href=""
      style="margin-right:3.5px"
      @click.prevent
    ><span class="icon-export"></span></a>

    <a
      v-if="showExportAsImageIcon"
      class="dataTableAction tableIcon"
      href
      id="dataTableFooterExportAsImageIcon"
      @click.prevent="showExportImage($event)"
      :title="translate('General_ExportAsImage')"
      style="margin-right:3.5px"
    >
      <span class="icon-image"></span>
    </a>

    <a
      v-if="showAnnotations"
      class="dataTableAction annotationView"
      href
      :title="translate('Annotations_Annotations')"
      @click.prevent
      style="margin-right:3.5px"
    ><span class="icon-annotation"></span></a>

    <a
      v-if="showSearch"
      class="dropdown-button dataTableAction searchAction"
      href
      :title="translate('General_Search')"
      style="margin-right:3.5px"
      draggable="false"
      @click.prevent
    >
      <span class="icon-search" draggable="false"></span>
      <span class="icon-close" draggable="false" :title="translate('CoreHome_CloseSearch')"></span>
      <input
        :id="`widgetSearch_${reportId}_${placement}`"
        :title="translate('CoreHome_DataTableHowToSearch')"
        type="text"
        class="dataTableSearchInput"
      />
    </a>

    <a
      v-for="action in dataTableActions"
      :key="action.id"
      :class="`dataTableAction ${action.id}`"
      href
      @click.prevent
      :title="action.title"
      style="margin-right:3.5px"
    >
      <span v-if="/^icon-/.test(action.icon || '')" :class="action.icon"></span>
      <img v-else width="16" height="16" :title="action.title" :src="action.icon"/>
    </a>

    <ul
      :id="`dropdownConfigure${randomIdForDropdown}`"
      class="dropdown-content tableConfiguration"
    >
      <li v-if="showFlattenTable">
        <div
          class="configItem dataTableFlatten"
          v-html="$sanitize(flattenItemText)"
        ></div>
      </li>
      <li
        v-if="showDimensionsConfigItem"
      >
        <div
          class="configItem dataTableShowDimensions"
          v-html="$sanitize(showDimensionsText)"
        ></div>
      </li>
      <li v-if="showFlatConfigItem">
        <div
          class="configItem dataTableIncludeAggregateRows"
          v-html="$sanitize(includeAggregateRowsText)"
        ></div>
      </li>
      <li v-if="showTotalsConfigItem">
        <div
          class="configItem dataTableShowTotalsRow"
          v-html="$sanitize(keepTotalsRowText)"
        ></div>
      </li>
      <li v-if="showExcludeLowPopulation">
        <div
          class="configItem dataTableExcludeLowPopulation"
          v-html="$sanitize(excludeLowPopText)"
        ></div>
      </li>
      <li v-if="showPivotBySubtable">
        <div
          class="configItem dataTablePivotBySubtable"
          v-html="$sanitize(pivotByText)"
        ></div>
      </li>
    </ul>

    <a
      v-if="showPeriods"
      v-dropdown-button
      class="dropdown-button dataTableAction activatePeriodsSelection"
      href=""
      @click.prevent
      :title="translate('CoreHome_ChangePeriod')"
      :data-target="`dropdownPeriods${randomIdForDropdown}`"
    >
      <div>
        <span class="icon-calendar"></span>
        <span class="periodName">
          {{ translations[clientSideParameters.period] || clientSideParameters.period }}
        </span>
      </div>
    </a>
    <ul
      v-if="showPeriods"
      :id="`dropdownPeriods${randomIdForDropdown}`"
      class="dropdown-content dataTablePeriods"
    >
      <li v-for="selectablePeriod in selectablePeriods" :key="selectablePeriod">
        <a
          :data-period="selectablePeriod"
          :class="`tableIcon ${clientSideParameters.period === selectablePeriod
            ? 'activeIcon' : ''}`"
        >
          <span>{{ translations[selectablePeriod] || selectablePeriod }}</span>
        </a>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Passthrough from '../Passthrough/Passthrough.vue';
import DropdownButton from '../DropdownButton/DropdownButton';
import ReportExport from '../ReportExport/ReportExport';
import { translate } from '../translate';

interface FooterIcon {
  id: string;
  icon?: string;
}

interface FooterIconGroup {
  buttons: FooterIcon[];
}

const { $ } = window;

function getSingleStateIconText(text: string, addDefault?: boolean, replacement?: string) {
  if (/(%(.\$)?s+)/g.test(translate(text))) {
    const values = ['<br /><span class="action">'];
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

function isBooleanLikeSet(value: number|string|boolean) {
  return !!value && value !== '0';
}

export default defineComponent({
  props: {
    showPeriods: Boolean,
    showFooter: Boolean,
    showFooterIcons: Boolean,
    showSearch: Boolean,
    showFlattenTable: Boolean,
    footerIcons: {
      type: Array,
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
    reportId: {
      type: String,
      required: true,
    },
    dataTableActions: {
      type: Array,
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
    selectablePeriods: Array,
    translations: {
      type: Object,
      required: true,
    },
    pivotDimensionName: String,
    placement: {
      type: String,
      default: 'footer',
    },
  },
  components: {
    Passthrough,
  },
  directives: {
    DropdownButton,
    ReportExport,
  },
  methods: {
    showExportImage(event: Event) {
      $(event.target as HTMLElement)
        .closest('.dataTable')
        .find('div.jqplot-target')
        .trigger('piwikExportAsImage');
    },
  },
  computed: {
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
    activeFooterIcon(): string|undefined {
      return this.activeFooterIcons[0]?.icon;
    },
    activeFooterIconIds(): string[] {
      return this.activeFooterIcons.map((icon) => icon.id);
    },
    numIcons(): number {
      return this.allFooterIcons.length;
    },
    hasFooterIconsToShow(): boolean {
      return !!this.activeFooterIcons.length && this.numIcons > 1;
    },
    reportFormats(): Record<string, string> {
      const formats: Record<string, string> = {
        CSV: 'CSV',
        TSV: 'TSV (Excel)',
        XML: 'XML',
        JSON: 'Json',
        HTML: 'HTML',
      };
      formats.RSS = 'RSS';
      return formats;
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
    hasConfigItems() {
      return this.showFlattenTable
        || this.showDimensionsConfigItem
        || this.showFlatConfigItem
        || this.showTotalsConfigItem
        || this.showExcludeLowPopulation
        || this.showPivotBySubtable;
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

      return getSingleStateIconText('CoreHome_PivotBySubtable', false, this.pivotDimensionName);
    },
    excludeLowPopText() {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return getToggledIconText(
        isBooleanLikeSet(params.enable_filter_excludelowpop),
        'CoreHome_IncludeRowsWithLowPopulation',
        'CoreHome_ExcludeRowsWithLowPopulation',
      );
    },
    isAnyConfigureIconHighlighted() {
      const params = this.clientSideParameters as Record<string, string|number|boolean>;
      return isBooleanLikeSet(params.flat)
        || isBooleanLikeSet(params.keep_totals_row)
        || isBooleanLikeSet(params.include_aggregate_rows)
        || isBooleanLikeSet(params.show_dimensions)
        || isBooleanLikeSet(params.pivotBy)
        || isBooleanLikeSet(params.enable_filter_excludelowpop);
    },
    isTableView() {
      return this.viewDataTable === 'table'
        || this.viewDataTable === 'tableAllColumns'
        || this.viewDataTable === 'tableGoals';
    },
  },
});
</script>
