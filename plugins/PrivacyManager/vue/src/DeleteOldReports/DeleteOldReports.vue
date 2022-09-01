<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    id="formDeleteSettings"
    v-form
  >
    <div id="deleteReportsSettingEnabled">
      <div>
        <Field
          uicontrol="checkbox"
          name="deleteReportsEnable"
          :model-value="enabled"
          @update:model-value="enabled = $event; reloadDbStats()"
          :title="translate('PrivacyManager_UseDeleteReports')"
          :inline-help="translate('PrivacyManager_DeleteAggregateReportsDetailedInfo')"
        >
        </Field>
      </div>
      <div
        class="alert alert-warning"
        style="width: 50%;"
        v-show="enabled"
      >
        <span>
          {{ translate('PrivacyManager_DeleteReportsInfo2', deleteOldLogsText) }}<br /><br />
          {{ translate('PrivacyManager_DeleteReportsInfo3', deleteOldLogsText) }}
        </span>
      </div>
    </div>
    <div
      id="deleteReportsSettings"
      v-show="enabled"
    >
      <div>
        <Field
          uicontrol="text"
          name="deleteReportsOlderThan"
          :model-value="deleteOlderThan"
          @update:model-value="deleteOlderThan = $event; reloadDbStats()"
          :title="deleteReportsOlderThanTitle"
          :inline-help="translate('PrivacyManager_LeastMonthsInput', '1')"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="checkbox"
          name="deleteReportsKeepBasic"
          :model-value="keepBasic"
          @update:model-value="keepBasic = $event; reloadDbStats()"
          :title="deleteReportsKeepBasicTitle"
          :inline-help="translate('PrivacyManager_KeepBasicMetricsReportsDetailedInfo')"
        >
        </Field>
      </div>
      <h3>
        {{ translate('PrivacyManager_KeepDataFor') }}
      </h3>
      <div>
        <div>
          <Field
            uicontrol="checkbox"
            name="deleteReportsKeepDay"
            :model-value="keepDataForDay"
            @update:model-value="keepDataForDay = $event; reloadDbStats()"
            :title="translate('General_DailyReports')"
          >
          </Field>
        </div>
        <div>
          <Field
            uicontrol="checkbox"
            name="deleteReportsKeepWeek"
            :model-value="keepDataForWeek"
            @update:model-value="keepDataForWeek = $event; reloadDbStats()"
            :title="translate('General_WeeklyReports')"
          >
          </Field>
        </div>
        <div>
          <Field
            uicontrol="checkbox"
            name="deleteReportsKeepMonth"
            :model-value="keepDataForMonth"
            @update:model-value="keepDataForMonth = $event; reloadDbStats()"
            :title="`${translate('General_MonthlyReports')} (${translate('General_Recommended')})`"
          >
          </Field>
        </div>
        <div>
          <Field
            uicontrol="checkbox"
            name="deleteReportsKeepYear"
            :model-value="keepDataForYear"
            @update:model-value="keepDataForYear = $event; reloadDbStats()"
            :title="`${translate('General_YearlyReports')} (${translate('General_Recommended')})`"
          >
          </Field>
        </div>
        <div>
          <Field
            uicontrol="checkbox"
            name="deleteReportsKeepRange"
            :model-value="keepDataForRange"
            @update:model-value="keepDataForRange = $event; reloadDbStats()"
            :title="translate('General_RangeReports')"
          >
          </Field>
        </div>
        <div>
          <Field
            uicontrol="checkbox"
            name="deleteReportsKeepSegments"
            :model-value="keepDataForSegments"
            @update:model-value="keepDataForSegments = $event; reloadDbStats()"
            :title="translate('PrivacyManager_KeepReportSegments')"
          >
          </Field>
        </div>
      </div>
    </div>
    <SaveButton
      @confirm="this.showPasswordConfirmModal = true"
      :saving="isLoading"
    />
    <PasswordConfirmation
      v-model="showPasswordConfirmModal"
      @confirmed="saveSettings"
    >
      <h2 v-if="enabled && !enableDeleteLogs">
        {{ translate('PrivacyManager_DeleteReportsConfirm') }}
      </h2>
      <h2 v-if="enabled && enableDeleteLogs">
        {{ translate('PrivacyManager_DeleteBothConfirm') }}
      </h2>
      <div v-if="enabled">{{ translate('UsersManager_ConfirmWithPassword') }}</div>
      <h2 v-if="!enabled">{{ translate('UsersManager_ConfirmWithPassword') }}</h2>
    </PasswordConfirmation>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from 'CoreHome';
import {
  PasswordConfirmation,
  Form,
  Field,
  SaveButton,
} from 'CorePluginsAdmin';
import ReportDeletionSettingsStore, {
  ReportDeletionSettings,
} from '../ReportDeletionSettings/ReportDeletionSettings.store';

interface DeleteOldReportsState {
  isLoading: boolean;
  enabled: boolean;
  deleteOlderThan: string;
  keepBasic: boolean;
  keepDataForDay: boolean;
  keepDataForWeek: boolean;
  keepDataForMonth: boolean;
  keepDataForYear: boolean;
  keepDataForRange: boolean;
  keepDataForSegments: boolean;
  showPasswordConfirmModal: boolean;
}

function getInt(value: unknown) {
  return value ? '1' : '0';
}

export default defineComponent({
  props: {
    isDataPurgeSettingsEnabled: Boolean,
    deleteData: {
      type: Object,
      required: true,
    },
    scheduleDeletionOptions: {
      type: Object,
      required: true,
    },
  },
  components: {
    Field,
    SaveButton,
    PasswordConfirmation,
  },
  directives: {
    Form,
  },
  data(): DeleteOldReportsState {
    return {
      isLoading: false,
      enabled: parseInt(this.deleteData.config.delete_reports_enable, 10) === 1,
      deleteOlderThan: this.deleteData.config.delete_reports_older_than,
      keepBasic: parseInt(this.deleteData.config.delete_reports_keep_basic_metrics, 10) === 1,
      keepDataForDay: parseInt(this.deleteData.config.delete_reports_keep_day_reports, 10) === 1,
      keepDataForWeek: parseInt(this.deleteData.config.delete_reports_keep_week_reports, 10) === 1,
      keepDataForMonth: parseInt(
        this.deleteData.config.delete_reports_keep_month_reports,
        10,
      ) === 1,
      keepDataForYear: parseInt(this.deleteData.config.delete_reports_keep_year_reports, 10) === 1,
      keepDataForRange: parseInt(
        this.deleteData.config.delete_reports_keep_range_reports,
        10,
      ) === 1,
      keepDataForSegments: parseInt(
        this.deleteData.config.delete_reports_keep_segment_reports,
        10,
      ) === 1,
      showPasswordConfirmModal: false,
    };
  },
  created() {
    setTimeout(() => {
      ReportDeletionSettingsStore.initSettings(this.settings);
    });
  },
  methods: {
    saveSettings(password: string) {
      const method = 'PrivacyManager.setDeleteReportsSettings';

      this.isLoading = true;
      ReportDeletionSettingsStore
        .savePurgeDataSettings(method, this.settings, password)
        .finally(() => {
          this.isLoading = false;
        });
    },
    reloadDbStats() {
      ReportDeletionSettingsStore.updateSettings(this.settings);
    },
  },
  computed: {
    settings(): ReportDeletionSettings {
      return {
        enableDeleteReports: this.enabled,
        deleteReportsOlderThan: this.deleteOlderThan,
        keepBasic: getInt(this.keepBasic),
        keepDay: getInt(this.keepDataForDay),
        keepWeek: getInt(this.keepDataForWeek),
        keepMonth: getInt(this.keepDataForMonth),
        keepYear: getInt(this.keepDataForYear),
        keepRange: getInt(this.keepDataForRange),
        keepSegments: getInt(this.keepDataForSegments),
      };
    },
    deleteOldLogsText(): string {
      return translate('PrivacyManager_UseDeleteLog');
    },
    deleteReportsOlderThanTitle(): string {
      const first = translate('PrivacyManager_DeleteReportsOlderThan');
      return `${first} (${translate('Intl_PeriodMonths')})`;
    },
    deleteReportsKeepBasicTitle(): string {
      const first = translate('PrivacyManager_KeepBasicMetrics');
      return `${first} (${translate('General_Recommended')})`;
    },
    enableDeleteLogs(): boolean {
      return !!ReportDeletionSettingsStore.enableDeleteLogs.value;
    },
  },
});
</script>
