<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <!-- Renders its entries, not the list holding them: the consumer places them in the menu it
       already has, or in a panel of their own when both are offered. -->
  <Passthrough>
    <li v-if="showExportAsImageIcon" class="mtm-dropdownPanel__menuItem">
      <a
        class="mtm-dropdownPanel__menuLink dataTableAction tableIcon"
        href=""
        :id="`dataTableExportAsImageIcon-${placement}`"
        @click.prevent="showExportImage($event)"
      >
        <span class="mtm-dropdownPanel__menuLabel">{{ translate('CoreHome_ExportImage') }}</span>
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
  </Passthrough>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import Passthrough from '../Passthrough/Passthrough.vue';
import ReportExport from '../ReportExport/ReportExport';
import { translate } from '../translate';
import findReportRoot from './reportScope';

export default defineComponent({
  props: {
    showExport: Boolean,
    showExportAsImageIcon: Boolean,
    exportSupportsFlat: Boolean,
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
    reportFormats: {
      type: Object as PropType<Record<string, string>>,
      default: () => ({}),
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
  methods: {
    translate,
    showExportImage(event: Event) {
      findReportRoot(event.target as HTMLElement)
        .find('div.jqplot-target')
        .trigger('piwikExportAsImage');
    },
  },
});
</script>
