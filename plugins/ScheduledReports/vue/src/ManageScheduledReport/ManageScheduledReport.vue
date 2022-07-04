<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="emailReports" ref="root">
    <div ref="reportSentSuccess" />
    <div ref="reportUpdatedSuccess" />
    <div>
      <div id="ajaxError" style="display:none"></div>

      <div id="ajaxLoadingDiv" style="display:none;">
        <div class="loadingPiwik">
          <img
            src="plugins/Morpheus/images/loading-blue.gif"
            :alt="translate('General_LoadingData')"
          />
          {{ translate('General_LoadingData') }}
        </div>
        <div class="loadingSegment">
          {{ translate('SegmentEditor_LoadingSegmentedDataMayTakeSomeTime') }}
        </div>
      </div>
      <ListReports
        v-show="showReportsList"
        :content-title="contentTitle"
        :user-login="userLogin"
        :login-module="loginModule"
        :reports="reports"
        :site-name="decodedSiteName"
        :segment-editor-activated="segmentEditorActivated"
        :saved-segments-by-id="savedSegmentsById"
        :periods="periods"
        :report-types="reportTypes"
        :download-output-type="downloadOutputType"
        :language="language"
        :report-formats-by-report-type="reportFormatsByReportType"
        @create="createReport()"
        @edit="editReport($event)"
        @delete="deleteReport($event)"
        @sendnow="sendReportNow($event)"
      />
      <AddReport
        v-if="showReportForm"
        :report="report"
        :periods="periods"
        :param-periods="paramPeriods"
        :report-type-options="reportTypeOptions"
        :report-formats-by-report-type-options="reportFormatsByReportTypeOptions"
        :display-formats="displayFormats"
        :reports-by-category-by-report-type="reportsByCategoryByReportType"
        :allow-multiple-reports-by-report-type="allowMultipleReportsByReportType"
        :count-websites="countWebsites"
        :site-name="decodedSiteName"
        :selected-reports="selectedReports"
        :report-types="reportTypes"
        :segment-editor-activated="segmentEditorActivated"
        :saved-segments-by-id="savedSegmentsById"
        @toggle-selected-report="toggleSelectedReport($event.reportType, $event.uniqueId)"
        @change="onChangeProperty($event.prop, $event.value)"
        @submit="submitReport()"
      >
        <template v-slot:report-parameters>
          <slot name="report-parameters"></slot>
        </template>
      </AddReport>

      <a id="bottom" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, nextTick } from 'vue';
import {
  translate,
  Matomo,
  NotificationsStore,
  ContentTable,
  AjaxHelper,
} from 'CoreHome';
import { Form } from 'CorePluginsAdmin';
import AddReport from '../AddReport/AddReport.vue';
import ListReports from '../ListReports/ListReports.vue';
import { Report } from '../types';
import { adjustHourToTimezone } from '../utilities';

interface ManageScheduledReportState {
  showReportsList: boolean;
  report: Report;
  selectedReports: Record<string, Record<string, boolean>>;
}

function scrollToTop() {
  Matomo.helper.lazyScrollTo('.emailReports', 200);
}

function updateParameters(reportType: string, report: Report) {
  if (window.updateReportParametersFunctions?.[reportType]) {
    window.updateReportParametersFunctions[reportType](report);
  }
}

function resetParameters(reportType: string, report: Report) {
  if (window.resetReportParametersFunctions?.[reportType]) {
    window.resetReportParametersFunctions[reportType](report);
  }
}

window.resetReportParametersFunctions = window.resetReportParametersFunctions || {};
window.updateReportParametersFunctions = window.updateReportParametersFunctions || {};
window.getReportParametersFunctions = window.getReportParametersFunctions || {};

const { $ } = window;

const timeZoneDifferenceInHours = Matomo.timezoneOffset / 3600;

export default defineComponent({
  props: {
    contentTitle: {
      type: String,
      required: true,
    },
    userLogin: {
      type: String,
      required: true,
    },
    loginModule: {
      type: String,
      required: true,
    },
    reports: {
      type: Array,
      required: true,
    },
    siteName: {
      type: String,
      required: true,
    },
    segmentEditorActivated: Boolean,
    savedSegmentsById: Object,
    periods: {
      type: Object,
      required: true,
    },
    downloadOutputType: {
      type: Number,
      required: true,
    },
    language: {
      type: String,
      required: true,
    },
    reportFormatsByReportType: {
      type: Object,
      required: true,
    },
    paramPeriods: {
      type: Object,
      required: true,
    },
    reportTypeOptions: {
      type: Object,
      required: true,
    },
    reportFormatsByReportTypeOptions: {
      type: Object,
      required: true,
    },
    displayFormats: {
      type: Object,
      required: true,
    },
    reportsByCategoryByReportType: {
      type: Object,
      required: true,
    },
    allowMultipleReportsByReportType: {
      type: Object,
      required: true,
    },
    countWebsites: {
      type: Number,
      required: true,
    },
    reportTypes: {
      type: Object,
      required: true,
    },
  },
  components: {
    AddReport,
    ListReports,
  },
  directives: {
    ContentTable,
    Form,
  },
  mounted() {
    $(this.$refs.root as HTMLElement).on('click', 'a.entityCancelLink', () => {
      this.showListOfReports();
    });

    Matomo.postEvent('ScheduledReports.ManageScheduledReport.mounted', {
      element: this.$refs.root,
    });
  },
  unmounted() {
    Matomo.postEvent('ScheduledReports.ManageScheduledReport.unmounted', {
      element: this.$refs.root,
    });
  },
  data(): ManageScheduledReportState {
    return {
      showReportsList: true,
      report: {} as unknown as Report,
      selectedReports: {},
    };
  },
  methods: {
    sendReportNow(idReport: string|number) {
      scrollToTop();

      AjaxHelper.post(
        {
          method: 'ScheduledReports.sendReport',
        },
        {
          idReport,
          force: true,
        },
      ).then(() => {
        this.fadeInOutSuccessMessage(
          this.$refs.reportSentSuccess as HTMLElement,
          translate('ScheduledReports_ReportSent'),
          false,
        );
      });
    },
    formSetEditReport(idReport: number) {
      const { ReportPlugin } = window;

      let report: Report = {
        idreport: idReport,
        type: ReportPlugin.defaultReportType,
        format: ReportPlugin.defaultReportFormat,
        description: '',
        period: ReportPlugin.defaultPeriod,
        hour: ReportPlugin.defaultHour,
        reports: [],
        idsegment: '',
        evolutionPeriodFor: 'prev',
        evolutionPeriodN: ReportPlugin.defaultEvolutionPeriodN,
        periodParam: ReportPlugin.defaultPeriod,
      } as unknown as Report;

      if (idReport > 0) {
        report = ReportPlugin.reportList[idReport];
        updateParameters(report.type, report);
      } else {
        resetParameters(report.type, report);
      }

      report.hour = adjustHourToTimezone(report.hour as string, timeZoneDifferenceInHours);

      this.selectedReports = {};
      Object.values(report.reports).forEach((reportId) => {
        this.selectedReports[report.type] = this.selectedReports[report.type] || {};
        this.selectedReports[report.type][reportId] = true;
      });

      report[`format${report.type}`] = report.format;

      if (!report.idsegment) {
        report.idsegment = '';
      }

      this.report = report;
      this.report.description = Matomo.helper.htmlDecode(report.description);
    },
    fadeInOutSuccessMessage(selector: HTMLElement, message: string, reload = true) {
      NotificationsStore.show({
        message,
        placeat: selector,
        context: 'success',
        noclear: true,
        type: 'toast',
        style: {
          display: 'inline-block',
          marginTop: '10px',
        },
        id: 'scheduledReportSuccess',
      });

      if (reload) {
        Matomo.helper.refreshAfter(2);
      }
    },
    changedReportType() {
      resetParameters(this.report.type, this.report);
    },
    deleteReport(idReport: string|number) {
      Matomo.helper.modalConfirm('#confirm', {
        yes: () => {
          AjaxHelper.post(
            {
              method: 'ScheduledReports.deleteReport',
            },
            {
              idReport,
            },
            {
              redirectOnSuccess: true,
            },
          );
        },
      });
    },
    showListOfReports(shouldScrollToTop?: boolean) {
      this.showReportsList = true;

      Matomo.helper.hideAjaxError();

      if (typeof shouldScrollToTop === 'undefined' || shouldScrollToTop) {
        scrollToTop();
      }
    },
    createReport() {
      this.showReportsList = false;

      // in nextTick so global report function records get manipulated before individual
      // entries are used
      nextTick(() => {
        this.formSetEditReport(0);
      });
    },
    editReport(reportId: number) {
      this.showReportsList = false;

      // in nextTick so global report function records get manipulated before individual
      // entries are used
      nextTick(() => {
        this.formSetEditReport(reportId);
      });
    },
    submitReport() {
      const apiParameters: QueryParameters = {
        idReport: this.report.idreport,
        description: this.report.description,
        idSegment: this.report.idsegment,
        reportType: this.report.type,
        reportFormat: this.report[`format${this.report.type}`] as string,
        periodParam: this.report.periodParam,
        evolutionPeriodFor: this.report.evolutionPeriodFor,
      };

      if (apiParameters.evolutionPeriodFor !== 'each') {
        apiParameters.evolutionPeriodN = this.report.evolutionPeriodN;
      }

      const { period } = this.report;
      const hour = adjustHourToTimezone(this.report.hour as string, -timeZoneDifferenceInHours);

      const selectedReports = this.selectedReports[apiParameters.reportType as string] || {};
      const reports = Object.keys(selectedReports).filter(
        (name) => this.selectedReports[apiParameters.reportType as string][name],
      );

      if (reports.length > 0) {
        apiParameters.reports = reports;
      }

      const reportParams = window.getReportParametersFunctions[this.report.type](this.report);
      apiParameters.parameters = reportParams as unknown as QueryParameters;

      const isCreate = this.report.idreport > 0;
      AjaxHelper.post(
        {
          method: isCreate ? 'ScheduledReports.updateReport' : 'ScheduledReports.addReport',
          period,
          hour,
        },
        apiParameters,
      ).then(() => {
        this.fadeInOutSuccessMessage(
          this.$refs.reportUpdatedSuccess as HTMLElement,
          translate('ScheduledReports_ReportUpdated'),
        );
      });
      return false;
    },
    onChangeProperty(propName: string, value: unknown) {
      this.report[propName] = value;

      if (propName === 'type') {
        this.changedReportType();
      }
    },
    toggleSelectedReport(reportType: string, uniqueId: string) {
      this.selectedReports[reportType] = this.selectedReports[reportType] || {};
      this.selectedReports[reportType][uniqueId] = !this.selectedReports[reportType][uniqueId];
    },
  },
  computed: {
    showReportForm() {
      return !this.showReportsList;
    },
    decodedSiteName() {
      return Matomo.helper.htmlDecode(this.siteName);
    },
  },
});
</script>
