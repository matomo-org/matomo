<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <!-- Renders its entries, not the list holding them: the consumer places them in the menu it
       already has, or in a panel of their own when both are offered. -->
  <Passthrough>
    <li v-if="showExportAsImageIcon" class="mtm-dropdownPanel__menuItem" role="none">
      <a
        class="mtm-dropdownPanel__menuLink dataTableAction tableIcon"
        href=""
        role="menuitem"
        tabindex="0"
        :id="`dataTableExportAsImageIcon-${placement}`"
        @click.prevent="showExportImage($event)"
        @keydown.space.prevent="activateItem"
      >
        <span class="mtm-dropdownPanel__menuLabel">{{ translate('CoreHome_ExportImage') }}</span>
      </a>
    </li>
    <li v-if="showExport" class="mtm-dropdownPanel__menuItem" role="none">
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
        role="menuitem"
        tabindex="0"
        @click.prevent
        @keydown.space.prevent="activateItem"
      >
        <span class="mtm-dropdownPanel__menuLabel">{{ translate('CoreHome_ExportData') }}</span>
      </a>
    </li>
  </Passthrough>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import Passthrough from '../Passthrough/Passthrough.vue';
import ReportExport from '../ReportExport/ReportExport';
import { translate } from '../translate';
import findReportRoot from './reportScope';
import { resolveExportSupportsFlat } from './DataTableActions.utils';
import activateMenuItem from './activateMenuItem';

export default defineComponent({
  props: {
    showExport: Boolean,
    showExportAsImageIcon: Boolean,
    exportSupportsFlatten: Boolean,
    clientSideParameters: {
      type: Object as PropType<Record<string, unknown>>,
      default: () => ({}),
    },
    reportTitle: {
      type: String,
      default: '',
    },
    requestParams: {
      type: Object as PropType<Record<string, unknown>>,
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
  computed: {
    reportFormats(): Record<string, string> {
      return {
        TSV: 'TSV (Excel)',
        HTML: 'HTML',
        JSON: 'JSON',
        XML: 'XML',
        CSV: 'CSV',
        RSS: 'RSS',
      };
    },
    exportSupportsFlat(): boolean {
      return resolveExportSupportsFlat(
        this.exportSupportsFlatten,
        this.clientSideParameters.flat as number|string|boolean,
      );
    },
  },
  methods: {
    translate,
    activateItem: activateMenuItem,
    showExportImage(event: Event) {
      findReportRoot(event.target as HTMLElement)
        .find('div.jqplot-target')
        .trigger('piwikExportAsImage');
    },
  },
});
</script>
