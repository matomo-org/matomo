<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

// TODO
<todo>
- conversion check (mistakes get fixed in quickmigrate)
- property types
- state types
- look over template
- look over component code
- get to build
- REMOVE DUPLICATE CODE IN TEMPLATE
- test in UI
- check uses:
  ./plugins/ScheduledReports/templates/index.twig
  ./plugins/ScheduledReports/angularjs/manage-scheduled-report/manage-scheduled-report.controller.js
  ./plugins/ScheduledReports/angularjs/manage-scheduled-report/manage-scheduled-report.directive.js
- create PR
</todo>

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
        :title="title"
        :user-login="userLogin"
        :login-module="loginModule"
        :reports="reports"
        :site-name="siteName"
        :segment-editor-activated="segmentEditorActivated"
        :saved-segments-by-id="savedSegmentsById"
        :periods="periods"
        :recipient="recipient"
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
        :param-periods="paramPeriods"
        :report-type-options="reportTypeOptions"
        :report-formats-by-report-type-options="reportFormatsByReportTypeOptions"
        :report-formats="reportFormats"
        :display-formats="displayFormats"
        :reports-by-category-by-report-type="reportsByCategoryByReportType"
        :allow-multiple-reports-by-report-type="allowMultipleReportsByReportType"
        :reports-by-category="reportsByCategory"
        :count-websites="countWebsites"
        :site-name="siteName"
        :selected-reports="selectedReports"
        @toggle-selected-report="selectedReports[$event.reportType][$event.uniqueId]"
        @submit="submitReport()"
      />

      <a id="bottom" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  Matomo,
  NotificationsStore,
  ContentBlock,
  ContentTable,
  AjaxHelper,
} from 'CoreHome';
import { Form, Field, SaveButton } from 'CorePluginsAdmin';
import AddReport from '../AddReport/AddReport.vue';
import ListReports from '../ListReports/ListReports.vue';
import { Report } from '../types';
import { adjustHourToTimezone } from '../utilities';

interface ManageScheduledReportState {
  showReportsList: boolean;
  actualReport: Report;
  selectedReports: Record<string, Record<string, boolean>>;
}

function scrollToTop() {
  Matomo.helper.lazyScrollTo(".emailReports", 200);
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

const { $, ReportPlugin } = window;

const timeZoneDifferenceInHours = Matomo.timezoneOffset / 3600;

export default defineComponent({
  props: {
    title: {
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
      type: String,
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
  },
  components: {
    AddReport,
    ListReports,
    ContentBlock,
    Field,
    SaveButton,
  },
  directives: {
    ContentTable,
    Form,
  },
  mounted() {
    $(this.$refs.root as HTMLElement).on('click', 'a.entityCancelLink', () => {
      this.showListOfReports();
    });
  },
  data(): ManageScheduledReportState {
    return {
      showReportsList: true,
      actualReport: {} as unknown as Report, // TODO: set evolution period for = 'prev' initially
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
        );
      });
    },
    formSetEditReport(idReport: number) {
      const report: Report = {
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
        this.report = ReportPlugin.reportList[idReport];
        updateParameters(report.type, this.report);
      } else {
        resetParameters(report.type, this.report);
      }

      report.hour = adjustHourToTimezone(report.hour as string, timeZoneDifferenceInHours);

      this.selectedReports = {};
      Object.keys(report.reports).forEach((key) => {
        this.selectedReports[report.type] = this.selectedReports[report.type] || {};
        this.selectedReports[report.type][key] = true;
      })

      report[`format${report.type}`] = report.format;

      if (!report.idsegment) {
        report.idsegment = '';
      }

      this.report = report;
      this.report.description = Matomo.helper.htmlDecode(report.description);
    },
    fadeInOutSuccessMessage(selector: HTMLElement, message: string) {
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

      Matomo.helper.refreshAfter(2);
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
              redirectOnSuccess: {},
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
      this.formSetEditReport(0);
    },
    editReport(reportId: number) {
      this.showReportsList = false;
      this.formSetEditReport(reportId);
    },
    submitReport() {
      const apiParameters: QueryParameters = {
        idReport: this.report.idreport,
        description: this.report.description,
        idSegment: this.report.idsegment,
        reportType: this.report.type,
        reportFormat: this.report[`format${this.report.type}`],
        periodParam: this.report.periodParam,
        evolutionPeriodFor: this.report.evolutionPeriodFor,
      };

      if (apiParameters.evolutionPeriodFor !== 'each') {
        apiParameters.evolutionPeriodN = this.report.evolutionPeriodN;
      }

      const period = this.report.period;
      const hour = adjustHourToTimezone(this.report.hour, -timeZoneDifferenceInHours);

      const reports = Object.keys(this.selectedReports[apiParameters.reportType]).filter(
        (name) => this.selectedReports[apiParameters.reportType][name],
      );

      if (reports.length > 0) {
        apiParameters.reports = this.reports;
      }

      apiParameters.parameters = window.getReportParametersFunctions[this.report.type](this.report);

      const isCreate = this.report.idReport > 0;
      AjaxHelper.post(
        {
          method: isCreate ? 'ScheduledReports.updateReport' : 'ScheduledReports.addReport',
          period,
          hour,
        },
        apiParameters,
        {
          redirectOnSuccess: true,
        },
      ).then(() => {
        this.fadeInOutSuccessMessage(
          this.$refs.reportUpdatedSuccess as HTMLElement,
          translate('ScheduledReports_ReportUpdated'),
        );
      });
      return false;
    },
  },
  computed: {
    showReportForm() {
      return !this.showListOfReports;
    },
  },
});
</script>
