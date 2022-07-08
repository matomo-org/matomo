<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="showFooter && showFooterIcons">
    <a
      v-dropdown-button
      class="dropdown-button dropdownConfigureIcon dataTableAction"
      href=""
      :data-target="`dropdownConfigure${randomIdForDropdown}`"
    >
      <span class="icon-configure"></span>
    </a>

    <a v-if="hasFooterIconsToShow"
      class="dropdown-button dataTableAction activateVisualizationSelection"
      href=""
      :data-target="`dropdownVisualizations${randomIdForDropdown}`"
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
            ></span>
            <img
              v-else
              width="16"
              height="16"
              :title="footerIcon.title"
              :src="footerIcon.icon"
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
    ><span class="icon-export"></span></a>

    <a
      v-if="showExportAsImageIcon"
      class="dataTableAction tableIcon"
      href
      id="dataTableFooterExportAsImageIcon"
      @click.prevent="showExportImage($event)"
      :title="translate('General_ExportAsImage')"
    >
      <span class="icon-image"></span>
    </a>

    <a
      v-if="showAnnotations"
      class="dataTableAction annotationView"
      href
      :title="translate('Annotations_Annotations')"
      @click.prevent
    ><span class="icon-annotation"></span></a>

    <a
      v-if="showSearch"
      class="dropdown-button dataTableAction searchAction"
      href
      :title="translate('General_Search')"
    >
      <span class="icon-search"></span>
      <span class="icon-close" :title="translate('CoreHome_CloseSearch')"></span>
      <input
        :id="`widgetSearch_${reportId}`"
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
    >
      <span v-if="/^icon-/.test(action.icon || '')" :class="action.icon"></span>
      <img v-else width="16" height="16" :title="action.title" :src="action.icon"/>
    </a>

    <ul
      :id="`dropdownConfigure${randomIdForDropdown}`"
      class="dropdown-content tableConfiguration"
    >
      <li v-if="showFlattenTable">
        <div class="configItem dataTableFlatten"></div>
      </li>
      <li
        v-if="showFlattenTable && `${clientSideParameters.flat}` === '1' && hasMultipleDimensions"
      >
        <div class="configItem dataTableShowDimensions"></div>
      </li>
      <li v-if="showFlattenTable && `${clientSideParameters.flat}` === '1'">
        <div class="configItem dataTableIncludeAggregateRows"></div>
      </li>
      <li v-if="!isDataTableEmpty && showTotalsRow">
        <div class="configItem dataTableShowTotalsRow"></div>
      </li>
      <li v-if="showExcludeLowPopulation">
        <div class="configItem dataTableExcludeLowPopulation"></div>
      </li>
      <li v-if="showPivotBySubtable">
        <div class="configItem dataTablePivotBySubtable"></div>
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

interface FooterIcon {
  id: string;
  icon?: string;
}

interface FooterIconGroup {
  buttons: FooterIcon[];
}

const { $ } = window;

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
    abandonedCarts: Number,
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
      const result = [this.viewDataTable];

      if (this.abandonedCarts === 0) {
        result.push('ecommerceOrder');
      } else if (this.abandonedCarts === 1) {
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
  },
});
</script>
