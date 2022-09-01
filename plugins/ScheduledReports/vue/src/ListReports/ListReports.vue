<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    id="entityEditContainer"
    class="entityTableContainer"
    help-url="https://matomo.org/docs/email-reports/"
    :feature="'true'"
    :content-title="contentTitle"
  >
    <table v-content-table>
      <thead>
      <tr>
        <th class="first">{{ translate('General_Description') }}</th>
        <th>{{ translate('ScheduledReports_EmailSchedule') }}</th>
        <th>{{ translate('ScheduledReports_ReportFormat') }}</th>
        <th>{{ translate('ScheduledReports_SendReportTo') }}</th>
        <th>{{ translate('General_Download') }}</th>
        <th>{{ translate('General_Edit') }}</th>
        <th>{{ translate('General_Delete') }}</th>
      </tr>
      </thead>
      <tbody>
        <tr v-if="userLogin === 'anonymous'">
          <td colspan="7">
            <br />
            {{ translate('ScheduledReports_MustBeLoggedIn') }}
            <br />&rsaquo; <a :href="`index.php?module=${loginModule}`">
              {{ translate('Login_LogIn') }}
            </a>
            <br /><br />
          </td>
        </tr>
        <tr v-else-if="!reports?.length">
          <td colspan="7">
            <br />
            {{ translate('ScheduledReports_ThereIsNoReportToManage', siteName) }}.
            <br /><br />
          </td>
        </tr>
        <tr v-for="report in decodedReports" :key="report.idreport">
          <td class="first">
            {{ report.description }}
            <div
              class="entityInlineHelp"
              style="font-size: 9pt;"
              v-if="segmentEditorActivated && report.idsegment"
            >
              <span v-if="savedSegmentsById[report.idsegment]">
                {{ savedSegmentsById[report.idsegment] }}
              </span>
              <span v-else>
                {{ translate('ScheduledReports_SegmentDeleted') }}
              </span>
            </div>
          </td>
          <td>{{ periods[report.period] }}
            <!-- Last sent on {{ report.ts_last_sent }} -->
          </td>
          <td>
            <span v-if="report.format">{{ report.format.toUpperCase() }}</span>
          </td>
          <td>
            <span v-if="report.recipients.length === 0">
              {{ translate('ScheduledReports_NoRecipients') }}
            </span>
            <span v-for="(recipient, index) in report.recipients" :key="index">
              {{ recipient }}
              <br />
            </span>

            <a
              v-if="report.recipients.length !== 0"
              href="#"
              name="linkSendNow"
              class="link_but withIcon"
              style="margin-top:3px;"
              @click.prevent="$emit('sendnow', report.idreport)"
            >
              <img
                border="0"
                :src="reportTypes[report.type]"
              />
              {{ translate('ScheduledReports_SendReportNow') }}
            </a>
          </td>
          <td>
            <form
              method="POST"
              target="_blank"
              :id="`downloadReportForm_${report.idreport}`"
              :action="linkTo({
                module: 'API',
                segment: null,
                method: 'ScheduledReports.generateReport',
                idReport: report.idreport,
                outputType: downloadOutputType,
                language: language,
                format: ['html', 'csv', 'tsv'].indexOf(report.format) !== -1
                  ? report.format : 'original',
              })"
            >
              <input
                type="hidden"
                name="token_auth"
                :value="token_auth"
              />
              <input
                type="hidden"
                name="force_api_session"
                value="1"
              />
            </form>
            <a
              href=""
              rel="noreferrer noopener"
              name="linkDownloadReport"
              class="link_but withIcon"
              @click.prevent="displayReport(report.idreport)"
              :id="report.idreport"
            >
              <img
                border="0"
                :width="16"
                :height="16"
                :src="reportFormatsByReportType[report.type][report.format]"
              /> {{ translate('General_Download') }}
            </a>
          </td>
          <td style="text-align: center;padding-top:2px;">
            <button
              class="table-action"
              @click="$emit('edit', report.idreport)"
              :title="translate('General_Edit')"
            >
              <span class="icon-edit" />
            </button>
          </td>
          <td style="text-align: center;padding-top:2px;">
            <button
              class="table-action"
              @click="$emit('delete', report.idreport)"
              :title="translate('General_Delete')"
            >
              <span class="icon-delete" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="tableActionBar">
      <button
        id="add-report"
        @click="$emit('create')"
        v-if="userLogin !== 'anonymous'"
      >
        <span class="icon-add" />
        {{ translate('ScheduledReports_CreateAndScheduleReport') }}
      </button>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  ContentTable,
  MatomoUrl,
  Matomo,
} from 'CoreHome';
import { Report } from '../types';

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
    reportTypes: {
      type: Object,
      required: true,
    },
  },
  components: {
    ContentBlock,
  },
  directives: {
    ContentTable,
  },
  emits: ['create', 'edit', 'delete', 'sendnow'],
  methods: {
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...params,
      })}`;
    },
    displayReport(reportId: number|string) {
      $(`#downloadReportForm_${reportId}`).submit();
    },
  },
  computed: {
    token_auth() {
      return Matomo.token_auth;
    },
    decodedReports() {
      return (this.reports as Report[]).map(
        (r) => ({ ...r, description: Matomo.helper.htmlDecode(r.description) }),
      );
    },
  },
});
</script>
