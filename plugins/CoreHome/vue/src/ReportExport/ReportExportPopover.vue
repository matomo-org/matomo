<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="report-export-popover row" id="reportExport">

    <div class="col l6">
      <div name="format">
        <Field
          :uicontrol="'radio'"
          :name="'format'"
          :title="translate('CoreHome_ExportFormat')"
          v-model="reportFormat"
          :full-width="true"
          :options="availableReportFormats[reportType]"
        />
      </div>

      <div>
        <Field
          :uicontrol="'checkbox'"
          :name="'option_flat'"
          :title="translate('CoreHome_FlattenReport')"
          v-model="optionFlat"
          v-show="hasSubtables"
        >
        </Field>
      </div>
      <div>
        <Field
          :uicontrol="'checkbox'"
          :name="'option_expanded'"
          :title="translate('CoreHome_ExpandSubtables')"
          v-model="optionExpanded"
          v-show="hasSubtables && !optionFlat"
        >
        </Field>
      </div>
      <div>
        <Field
          :uicontrol="'checkbox'"
          :name="'option_format_metrics'"
          :title="translate('CoreHome_FormatMetrics')"
          v-model="optionFormatMetrics"
        >
        </Field>
      </div>
    </div>

    <div class="col l6">
      <div>
        <Field
          :uicontrol="'radio'"
          :name="'filter_type'"
          :title="translate('CoreHome_ReportType')"
          v-model="reportType"
          :full-width="true"
          :options="availableReportTypes"
        >
        </Field>
      </div>

      <div class="filter_limit">
        <div v-show="!maxFilterLimit || maxFilterLimit <= 0">
          <Field
            :uicontrol="'radio'"
            :name="'filter_limit_all'"
            :title="translate('CoreHome_RowLimit')"
            v-model="reportLimitAll"

            :full-width="true"
            :options="limitAllOptions"
          >
          </Field>
        </div>

        <div v-if="reportLimitAll === 'no' && maxFilterLimit <= 0">
          <Field
            :uicontrol="'number'"
            name="filter_limit"
            :min="1"
            v-model="reportLimit"
            :full-width="true"
          >
          </Field>
        </div>

        <div v-if="reportLimitAll === 'no' && maxFilterLimit > 0">
          <Field
            :uicontrol="'number'"
            :name="'filter_limit'"
            :min="1"
            :max="maxFilterLimit"
            v-model="reportLimit"
            :value="reportLimit"
            :full-width="true"
            :title="filterLimitTooltip"
          >
          </Field>
        </div>
      </div>
    </div>

    <div class="col l12" v-show="showUrl">
      <textarea
        v-select-on-focus="{}"
        readonly
        class="exportFullUrl"
        :value="exportLinkWithoutToken"
      >
      </textarea>
      <div class="tooltip" v-html="$sanitize(translate(
        'CoreHome_ExportTooltipWithLink',
        '<a target=_blank href=\'?module=UsersManager&action=userSecurity\'>',
        '</a>',
        'ENTER_YOUR_TOKEN_AUTH_HERE',
      ))"></div>
    </div>

    <div class="col l12">
      <a
        class="btn"
        :href="exportLink"
        target="_new"
        :title="translate('CoreHome_ExportTooltip')"
      >{{ translate('General_Export') }}</a>
      <a href="javascript:" @click="showUrl=!showUrl" class="toggle-export-url">
        <span v-show="!showUrl">{{ translate('CoreHome_ShowExportUrl') }}</span>
        <span v-show="showUrl">{{ translate('CoreHome_HideExportUrl') }}</span>
      </a>
    </div>

  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import useExternalPluginComponent from '../useExternalPluginComponent';
import SelectOnFocus from '../SelectOnFocus/SelectOnFocus';
import Matomo from '../Matomo/Matomo';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import translate from '../translate';

const Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');

export default defineComponent({
  components: {
    Field,
  },
  directives: {
    SelectOnFocus,
  },
  props: {
    hasSubtables: Boolean,
    availableReportTypes: Object,
    availableReportFormats: Object,
    maxFilterLimit: Number,
    limitAllOptions: Object,
    dataTable: Object,
    requestParams: [Object, String],
    apiMethod: String,
    initialReportType: {
      type: String,
      default: 'default',
    },
    initialReportLimit: {
      type: [String, Number],
      default: 100,
    },
    initialReportLimitAll: {
      type: String,
      default: 'yes',
    },
    initialOptionFlat: {
      type: Boolean,
      default: false,
    },
    initialOptionExpanded: {
      type: Boolean,
      default: true,
    },
    initialOptionFormatMetrics: {
      type: Boolean,
      default: false,
    },
    initialReportFormat: {
      type: String,
      default: 'XML',
    },
  },
  data() {
    return {
      showUrl: false,
      reportFormat: this.initialReportFormat,
      optionFlat: this.initialOptionFlat,
      optionExpanded: this.initialOptionExpanded,
      optionFormatMetrics: this.initialOptionFormatMetrics,
      reportType: this.initialReportType,
      reportLimitAll: this.initialReportLimitAll,
      reportLimit: this.initialReportLimit,
    };
  },
  watch: {
    reportType(newVal) {
      if (!this.availableReportFormats[newVal][this.reportFormat]) {
        this.reportFormat = 'XML';
      }
    },
    reportLimit(newVal, oldVal) {
      if (this.maxFilterLimit > 0) {
        if (parseInt(newVal, 10) > parseInt(this.maxFilterLimit, 10)) {
          this.reportLimit = oldVal;
        }
      }
    },
  },
  computed: {
    filterLimitTooltip() {
      const rowLimit = translate('CoreHome_RowLimit');
      const computedMetricMax = translate('General_ComputedMetricMax', this.maxFilterLimit);
      return `${rowLimit} (${computedMetricMax})`;
    },
    exportLink() {
      return this.getExportLink(true);
    },
    exportLinkWithoutToken() {
      return this.getExportLink(false);
    },
  },
  methods: {
    getExportLink(withToken = true) {
      const {
        dataTable,
        reportFormat,
        apiMethod,
        reportType,
      } = this;

      if (!reportFormat) {
        return undefined;
      }

      let {
        requestParams,
      } = this;

      const limit = this.reportLimitAll === 'yes' ? -1 : this.reportLimit;

      if (requestParams && typeof requestParams === 'string') {
        requestParams = JSON.parse(requestParams);
      } else {
        requestParams = {};
      }

      const {
        segment,
        label,
        idGoal,
        idDimension,
        idSite,
      } = dataTable.param;

      let { date, period } = dataTable.param;

      if (reportFormat === 'RSS') {
        date = 'last10';
      }
      if (typeof dataTable.param.dateUsedInGraph !== 'undefined') {
        date = dataTable.param.dateUsedInGraph;
      }

      const formatsUseDayNotRange = Matomo.config.datatable_export_range_as_day.toLowerCase();

      if (formatsUseDayNotRange.indexOf(reportFormat.toLowerCase()) !== -1
        && dataTable.param.period === 'range'
      ) {
        period = 'day';
      }

      // Below evolution graph, show daily exports
      if (dataTable.param.period === 'range'
        && dataTable.param.viewDataTable === 'graphEvolution'
      ) {
        period = 'day';
      }

      const exportUrlParams: Record<string, unknown> = {
        module: 'API',
        format: reportFormat,
        idSite,
        period,
        date,
      };

      if (reportType === 'processed') {
        exportUrlParams.method = 'API.getProcessedReport';
        [exportUrlParams.apiModule, exportUrlParams.apiAction] = apiMethod.split('.');
      } else {
        exportUrlParams.method = apiMethod;
      }

      if (dataTable.param.compareDates
        && dataTable.param.compareDates.length
      ) {
        exportUrlParams.compareDates = dataTable.param.compareDates;
        exportUrlParams.compare = '1';
      }

      if (dataTable.param.comparePeriods
        && dataTable.param.comparePeriods.length
      ) {
        exportUrlParams.comparePeriods = dataTable.param.comparePeriods;
        exportUrlParams.compare = '1';
      }

      if (dataTable.param.compareSegments
        && dataTable.param.compareSegments.length
      ) {
        exportUrlParams.compareSegments = dataTable.param.compareSegments;
        exportUrlParams.compare = '1';
      }

      if (typeof dataTable.param.filter_pattern !== 'undefined') {
        exportUrlParams.filter_pattern = dataTable.param.filter_pattern;
      }

      if (typeof dataTable.param.filter_pattern_recursive !== 'undefined') {
        exportUrlParams.filter_pattern_recursive = dataTable.param.filter_pattern_recursive;
      }

      if (window.$.isPlainObject(requestParams)) {
        Object.entries(requestParams).forEach(([index, param]) => {
          let value = param;
          if (value === true) {
            value = 1;
          } else if (value === false) {
            value = 0;
          }
          exportUrlParams[index] = value;
        });
      }

      if (this.optionFlat) {
        exportUrlParams.flat = 1;
        if (typeof dataTable.param.include_aggregate_rows !== 'undefined'
          && dataTable.param.include_aggregate_rows === '1'
        ) {
          exportUrlParams.include_aggregate_rows = 1;
        }
      }

      if (!this.optionFlat && this.optionExpanded) {
        exportUrlParams.expanded = 1;
      }

      exportUrlParams.format_metrics = this.optionFormatMetrics ? 1 : 0;

      if (dataTable.param.pivotBy) {
        exportUrlParams.pivotBy = dataTable.param.pivotBy;
        exportUrlParams.pivotByColumnLimit = 20;

        if (dataTable.props.pivot_by_column) {
          exportUrlParams.pivotByColumn = dataTable.props.pivot_by_column;
        }
      }

      if (reportFormat === 'CSV' || reportFormat === 'TSV' || reportFormat === 'RSS') {
        exportUrlParams.translateColumnNames = 1;
        exportUrlParams.language = Matomo.language;
      }

      if (typeof segment !== 'undefined') {
        exportUrlParams.segment = decodeURIComponent(segment);
      }

      // Export Goals specific reports
      if (typeof idGoal !== 'undefined'
        && idGoal !== '-1'
      ) {
        exportUrlParams.idGoal = idGoal;
      }

      // Export Dimension specific reports
      if (typeof idDimension !== 'undefined'
        && idDimension !== '-1'
      ) {
        exportUrlParams.idDimension = idDimension;
      }

      if (label) {
        const labelParts = label.split(',');

        if (labelParts.length > 1) {
          exportUrlParams.label = labelParts;
        } else {
          [exportUrlParams.label] = labelParts;
        }
      }

      exportUrlParams.token_auth = 'ENTER_YOUR_TOKEN_AUTH_HERE';

      if (withToken === true) {
        exportUrlParams.token_auth = Matomo.token_auth;
        exportUrlParams.force_api_session = 1;
      }

      exportUrlParams.filter_limit = limit;

      const prefix = window.location.href.split('?')[0];
      return `${prefix}?${MatomoUrl.stringify(exportUrlParams)}`;
    },
  },
});
</script>
