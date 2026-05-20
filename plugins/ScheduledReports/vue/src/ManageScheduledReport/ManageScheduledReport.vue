<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="emailReports" ref="root">
      <div ref="reportSentSuccess" />
      <div ref="reportUpdatedSuccess" />
      <div>
      <div id="ajaxError" style="display:none"></div>

      <div id="ajaxLoadingDiv" ref="ajaxLoadingDiv" style="display:none;">
        <div class="loadingPiwik">
          <MatomoLoader />
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
        :sending-reports="sendingReports"
        @create="createReport()"
        @edit="editReport($event)"
        @delete="deleteReport($event)"
        @sendnow="sendReportNow($event)"
      />
      <AddReport
        v-if="showReportForm"
        :report="report"
        :validation-errors="validationErrors"
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
        :selected-reports-order="selectedReportsOrder"
        :report-types="reportTypes"
        :segment-editor-activated="segmentEditorActivated"
        :saved-segments-by-id="savedSegmentsById"
        @toggle-selected-report="toggleSelectedReport($event.reportType, $event.uniqueId)"
        @reorder-selected-reports="onReorderSelectedReports($event.reportType, $event.order)"
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
  AjaxHelper,
  ContentTable,
  format,
  getToday,
  Matomo,
  MatomoUrl,
  MatomoLoader,
  NotificationsStore,
  NotificationType,
  translate,
} from 'CoreHome';
import { Form } from 'CorePluginsAdmin';
import AddReport from '../AddReport/AddReport.vue';
import ListReports from '../ListReports/ListReports.vue';
import type { Report } from '../types';
import { adjustHourToTimezone } from '../utilities';
import {
  consumeStoredValue,
  getStoredValue,
  removeStoredValue,
  setStoredValue,
} from './storage';

interface ManageScheduledReportState {
  showReportsList: boolean;
  report: Report;
  selectedReports: Record<string, Record<string, boolean>>;
  selectedReportsOrder: Record<string, string[]>;
  sendingReports: Array<string|number>;
  isDashboardExportInfoVisible: boolean;
  validationErrors: {
    name: boolean;
    reports: boolean;
  };
}

type WidgetReportMap = {
  dashboardName: string;
  email: Record<string, boolean>;
  idSegment?: string|number|null;
  unmappedWidgets?: string[];
};

type NotificationContext = NotificationType['context'];
type NotificationKind = NotificationType['type'];

function scrollToTop() {
  Matomo.helper.lazyScrollTo('.emailReports', 200, true);
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
const PENDING_NOTIFICATION_KEY = 'scheduledReports.pendingNotification';
const DASHBOARD_EXPORT_STORAGE_KEY = 'scheduledReports.dashboardExportId';
const VALIDATION_NOTIFICATION_ID = 'scheduledReportValidationError';

const timeZoneDifferenceInHours = Matomo.timezoneOffset / 3600;

export default defineComponent({
  name: 'ManageScheduledReport',
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
    MatomoLoader,
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
    this.handleDashboardExportFromSession();
    Matomo.postEvent('ScheduledReports.ManageScheduledReport.mounted', {
      element: this.$refs.root,
    });
    const pendingMessage = getStoredValue(PENDING_NOTIFICATION_KEY);
    if (pendingMessage && this.$refs.reportUpdatedSuccess) {
      removeStoredValue(PENDING_NOTIFICATION_KEY);
      scrollToTop();
      this.fadeInOutSuccessMessage(
        this.$refs.reportUpdatedSuccess as HTMLElement,
        pendingMessage,
        false,
      );
    }
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
      selectedReportsOrder: {},
      sendingReports: [],
      isDashboardExportInfoVisible: false,
      validationErrors: {
        name: false,
        reports: false,
      },
    };
  },
  methods: {
    sendReportNow(idReport: string|number) {
      if (this.sendingReports.includes(idReport)) {
        return;
      }
      scrollToTop();
      this.sendingReports.push(idReport);
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
      }).finally(() => {
        this.sendingReports = this.sendingReports.filter(
          (report: string | number) => report !== idReport,
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
        reportDescription: '',
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
      this.selectedReportsOrder = {};
      Object.values(report.reports).forEach((reportId) => {
        this.selectedReports[report.type] = this.selectedReports[report.type] || {};
        this.selectedReports[report.type][reportId] = true;
      });
      this.selectedReportsOrder[report.type] = Object.values(report.reports).map(
        (reportId) => reportId as string,
      );

      report[`format${report.type}`] = report.format;

      if (!report.idsegment) {
        report.idsegment = '';
      }

      this.report = report;
      this.report.description = Matomo.helper.htmlDecode(report.description);
      this.report.reportDescription = Matomo.helper.htmlDecode(
        report.parameters && Object.prototype.hasOwnProperty.call(
          report.parameters,
          'reportDescription',
        )
          ? `${report.parameters.reportDescription || ''}`
          : '',
      );
    },
    showNotificationMessage(
      selector: HTMLElement,
      message: string,
      context: NotificationContext = 'success',
      type: NotificationKind = 'toast',
    ) {
      NotificationsStore.show({
        message,
        placeat: selector,
        context,
        noclear: true,
        type,
        style: {
          display: 'inline-block',
          marginTop: '10px',
          width: '100%',
        },
        id: 'scheduledReportSuccess',
      });
    },
    fadeInOutSuccessMessage(selector: HTMLElement, message: string, reload = true) {
      this.showNotificationMessage(selector, message);
      if (reload) {
        Matomo.helper.refreshAfter(2);
      }
    },
    queueSaveNotificationAndRefresh(isUpdate: boolean) {
      setStoredValue(
        PENDING_NOTIFICATION_KEY,
        isUpdate
          ? translate('ScheduledReports_ReportUpdated')
          : translate('ScheduledReports_ReportAdded'),
      );
      Matomo.helper.refreshAfter(0);
    },
    showDashboardExportInfo(
      selector: HTMLElement, message: string,
      dashboardName: string, reload = true,
    ) {
      let dashboardInfoMessage = `${translate('ScheduledReports_ExportDashboardTitle')}
        <br/><br/>${translate('ScheduledReports_ExportDashboardPrepare', dashboardName)}
        <br/><br/>${translate('ScheduledReports_ExportDashboardWidgetsConvertedAutomatically')}
        <br/><br/>${translate('ScheduledReports_ExportDashboardEmailEnabledByDefault', translate('ScheduledReports_ReportSchedule'), translate('General_Never'))}
        <br/><br/>${translate('ScheduledReports_ExportDashboardDownload')}`;
      if (message !== '') {
        dashboardInfoMessage += `<br/><br/>${message}`;
      }
      this.isDashboardExportInfoVisible = true;
      this.showNotificationMessage(selector, dashboardInfoMessage, 'info', 'persistent');
      if (reload) {
        setStoredValue(PENDING_NOTIFICATION_KEY, message);
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
      this.resetValidationErrors();

      if (this.isDashboardExportInfoVisible) {
        NotificationsStore.remove('scheduledReportSuccess');
        this.isDashboardExportInfoVisible = false;
      }

      Matomo.helper.hideAjaxError();

      if (typeof shouldScrollToTop === 'undefined' || shouldScrollToTop) {
        scrollToTop();
      }
    },
    createReport(afterInit?: () => void) {
      this.showReportsList = false;

      // in nextTick so global report function records get manipulated before individual
      // entries are used
      nextTick(() => {
        this.formSetEditReport(0);
        this.resetValidationErrors();
        if (afterInit) {
          afterInit();
        }
      });
    },
    editReport(reportId: number) {
      this.showReportsList = false;

      // in nextTick so global report function records get manipulated before individual
      // entries are used
      nextTick(() => {
        this.formSetEditReport(reportId);
        this.resetValidationErrors();
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

      const reportType = apiParameters.reportType as string;
      const selectedReports = this.selectedReports[reportType] || {};
      let reports = (this.selectedReportsOrder[reportType] || []).filter(
        (name) => selectedReports[name],
      );

      if (!reports.length) {
        reports = Object.keys(selectedReports).filter(
          (name) => selectedReports[name],
        );
      }

      if (reports.length > 0) {
        apiParameters.reports = reports;
      }

      const validationErrors = this.getValidationErrors(reports);
      if (validationErrors.name || validationErrors.reports) {
        this.validationErrors = validationErrors;
        scrollToTop();
        this.showValidationErrors(validationErrors);
        return false;
      }

      const reportParams = window.getReportParametersFunctions[this.report.type](this.report);
      reportParams.reportDescription = this.report.reportDescription || '';
      apiParameters.parameters = reportParams as unknown as QueryParameters;

      const isUpdate = this.report.idreport > 0;
      AjaxHelper.post(
        {
          method: isUpdate ? 'ScheduledReports.updateReport' : 'ScheduledReports.addReport',
          period,
          hour,
        },
        apiParameters,
      ).then(() => {
        this.queueSaveNotificationAndRefresh(isUpdate);
      });
      return false;
    },
    onChangeProperty(propName: string, value: unknown) {
      this.report[propName] = value;

      if (propName === 'type') {
        this.changedReportType();
      }

      this.refreshValidationErrors();
    },
    toggleSelectedReport(reportType: string, uniqueId: string) {
      this.selectedReports[reportType] = this.selectedReports[reportType] || {};
      const newValue = !this.selectedReports[reportType][uniqueId];
      this.selectedReports[reportType][uniqueId] = newValue;

      this.selectedReportsOrder[reportType] = this.selectedReportsOrder[reportType] || [];

      if (newValue) {
        if (this.selectedReportsOrder[reportType].indexOf(uniqueId) === -1) {
          this.selectedReportsOrder[reportType].push(uniqueId);
        }
      } else {
        this.selectedReportsOrder[reportType] = this.selectedReportsOrder[reportType].filter(
          (reportId) => reportId !== uniqueId,
        );
      }

      this.refreshValidationErrors();
    },
    onReorderSelectedReports(reportType: string, order: string[]) {
      this.selectedReportsOrder[reportType] = order.filter(
        (uniqueId) => this.selectedReports[reportType]?.[uniqueId],
      );
    },
    getSelectedReportIds(reportType: string): string[] {
      const selectedReports = this.selectedReports[reportType] || {};
      let reports = (this.selectedReportsOrder[reportType] || []).filter(
        (name) => selectedReports[name],
      );

      if (!reports.length) {
        reports = Object.keys(selectedReports).filter(
          (name) => selectedReports[name],
        );
      }

      return reports;
    },
    getValidationErrors(selectedReportIds?: string[]) {
      const reportType = this.report.type as string;
      const selectedReports = selectedReportIds || this.getSelectedReportIds(reportType);
      return {
        name: !String(this.report.description || '').trim(),
        reports: selectedReports.length === 0,
      };
    },
    showValidationErrors(validationErrors: { name: boolean; reports: boolean }) {
      const messages = [];
      if (validationErrors.name) {
        messages.push(translate(
          'ScheduledReports_ReportMissingName',
          '<a href="#report_description">',
          '</a>',
        ));
      }
      if (validationErrors.reports) {
        messages.push(translate(
          'ScheduledReports_ReportMissingReports',
          '<a href="#scheduled-reports-selection-heading">',
          '</a>',
        ));
      }

      const message = messages.length > 1
        ? `<ul><li>${messages.join('</li><li>')}</li></ul>`
        : messages[0];

      NotificationsStore.remove(VALIDATION_NOTIFICATION_ID);
      NotificationsStore.show({
        message,
        placeat: this.$refs.reportUpdatedSuccess as HTMLElement,
        context: 'error',
        noclear: true,
        type: 'persistent',
        style: {
          display: 'inline-block',
          marginTop: '10px',
          width: '100%',
        },
        id: VALIDATION_NOTIFICATION_ID,
      });
    },
    refreshValidationErrors() {
      if (!this.validationErrors.name && !this.validationErrors.reports) {
        return;
      }

      const validationErrors = this.getValidationErrors();
      this.validationErrors = validationErrors;

      if (validationErrors.name || validationErrors.reports) {
        this.showValidationErrors(validationErrors);
      } else {
        NotificationsStore.remove(VALIDATION_NOTIFICATION_ID);
      }
    },
    resetValidationErrors() {
      this.validationErrors = {
        name: false,
        reports: false,
      };
      NotificationsStore.remove(VALIDATION_NOTIFICATION_ID);
    },
    async handleDashboardExportFromSession() {
      // Dashboard export bootstrap is session-backed on purpose; URL idDashboard is ignored.
      const storedDashboardId = this.consumeDashboardExportIdFromSession();
      if (storedDashboardId === null) {
        return;
      }

      const dashboardId = this.parsePositiveDashboardIdParam(storedDashboardId);
      if (dashboardId === '') {
        scrollToTop();
        this.showNotificationMessage(
          this.$refs.reportUpdatedSuccess as HTMLElement,
          translate('ScheduledReports_ExportDashboardInvalidDashboard'),
          'error',
          'persistent',
        );
        return;
      }

      this.getWidgetReportMapping(dashboardId)
        .then((mapping) => {
          if (!this.isValidDashboardExportMapping(mapping)) {
            scrollToTop();
            this.showNotificationMessage(
              this.$refs.reportUpdatedSuccess as HTMLElement,
              translate('ScheduledReports_ExportDashboardInvalidDashboard'),
              'error',
              'persistent',
            );
            return;
          }
          this.createReport(() => {
            this.applyDashboardExportMapping(mapping);
          });
        })
        .catch(() => {
          scrollToTop();
          this.showNotificationMessage(
            this.$refs.reportUpdatedSuccess as HTMLElement,
            translate('General_ErrorTryAgain'),
            'error',
          );
        });
    },
    consumeDashboardExportIdFromSession(): string|null {
      return consumeStoredValue(DASHBOARD_EXPORT_STORAGE_KEY);
    },
    async getWidgetReportMapping(dashboardId: string): Promise<WidgetReportMap> {
      return AjaxHelper.fetch(
        {
          method: 'ScheduledReports.getWidgetReportMap',
          dashId: dashboardId,
          idSite: Matomo.idSite,
          segment: this.getExportSegmentFromUrl(),
        },
      ).then((e) => e as WidgetReportMap);
    },
    getExportSegmentFromUrl(): string {
      const { segment } = MatomoUrl.parsed.value;
      return typeof segment === 'string' ? segment : '';
    },
    parsePositiveDashboardIdParam(value: unknown): string {
      if (typeof value !== 'string') {
        return '';
      }

      return /^[1-9]\d*$/.test(value.trim()) ? value.trim() : '';
    },
    isValidDashboardExportMapping(mapping: WidgetReportMap): boolean {
      if (!mapping?.dashboardName) {
        return false;
      }

      return Object.keys(mapping.email || {}).length > 0;
    },
    applyDashboardExportMapping(mapping: WidgetReportMap) {
      if (!this.isValidDashboardExportMapping(mapping)) {
        return;
      }
      const dashName = Matomo.helper.htmlDecode(mapping.dashboardName);
      const escapedDashName = Matomo.helper.escape(dashName);
      this.selectedReports = { email: { ...mapping.email } };
      this.selectedReportsOrder = { email: Object.keys(mapping.email || {}) };
      if (mapping.idSegment) {
        this.report.idsegment = mapping.idSegment;
      }

      const dateTodayString = format(getToday());
      this.report.description = translate(
        'ScheduledReports_ExportDashboardReportDescription',
        dashName,
        dateTodayString,
      );

      let unmappedWidgetsForDisplay = '';
      if (mapping.unmappedWidgets && mapping.unmappedWidgets.length) {
        const escapedWidgets = mapping.unmappedWidgets.map(
          (widgetName) => Matomo.helper.escape(widgetName),
        );
        unmappedWidgetsForDisplay = translate('ScheduledReports_WidgetsNotMappedToReports',
          escapedWidgets.join(', '));
      }
      this.showDashboardExportInfo(
        this.$refs.reportUpdatedSuccess as HTMLElement,
        unmappedWidgetsForDisplay,
        escapedDashName,
        false,
      );
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
