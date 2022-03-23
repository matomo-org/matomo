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
  ./plugins/PrivacyManager/templates/privacySettings.twig
  ./plugins/PrivacyManager/angularjs/delete-old-reports/delete-old-reports.controller.js
- create PR
</todo>

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
      @confirm="save()"
      :saving="isLoading"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate, Matomo } from 'CoreHome';
import { Form, Field, SaveButton } from 'CorePluginsAdmin';
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
}

function getInt(value: unknown) {
  return value ? '1' : '0';
}

const { $ } = window;

export default defineComponent({
  props: {
    isDataPurgeSettingsEnabled: Boolean,
    deleteData: {
      type: Object,
      required: true,
    },
    deleteOldLogs: {
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
  },
  directives: {
    Form,
  },
  data(): DeleteOldReportsState {
    return {
      isLoading: false,
      enabled: this.deleteData.config.delete_reports_enable === '1',
      deleteOlderThan: this.deleteData.config.delete_reports_older_than,
      keepBasic: this.deleteData.config.delete_reports_keep_basic_metrics === '1',
      keepDataForDay: this.deleteData.config.delete_reports_keep_day_reports === '1',
      keepDataForWeek: this.deleteData.config.delete_reports_keep_week_reports === '1',
      keepDataForMonth: this.deleteData.config.delete_reports_keep_month_reports === '1',
      keepDataForYear: this.deleteData.config.delete_reports_keep_year_reports === '1',
      keepDataForRange: this.deleteData.config.delete_reports_keep_range_reports === '1',
      keepDataForSegments: this.deleteData.config.delete_reports_keep_segment_reports === '1',
    };
  },
  created() {
    setTimeout(() => {
      ReportDeletionSettingsStore.initSettings(this.settings);
    });
  },
  methods: {
    saveSettings() {
      const method = 'PrivacyManager.setDeleteReportsSettings';

      this.isLoading = true;
      ReportDeletionSettingsStore.savePurgeDataSettings(method, this.settings).finally(() => {
        this.isLoading = false;
      });
    },
    reloadDbStats() {
      ReportDeletionSettingsStore.updateSettings(this.settings);
    },
    save() {
      if (this.enabled) {
        let confirmId = 'deleteReportsConfirm';
        if (ReportDeletionSettingsStore.enableDeleteLogs.value) {
          confirmId = 'deleteBothConfirm';
        }

        $('#confirmDeleteSettings').find('>h2').hide();
        $(`#${confirmId}`).show();

        Matomo.helper.modalConfirm('#confirmDeleteSettings', {
          yes: () => {
            this.saveSettings();
          },
        });
      } else {
        this.saveSettings();
      }
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
    deleteReportsKeepBasic(): string {
      const first = translate('PrivacyManager_KeepBasicMetrics');
      return `${first} (${translate('General_Recommended')})`;
    },
  },
});
</script>
