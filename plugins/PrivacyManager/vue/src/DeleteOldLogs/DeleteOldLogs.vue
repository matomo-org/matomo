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
    <div id="deleteLogSettingEnabled">
      <div>
        <Field
          uicontrol="checkbox"
          name="deleteEnable"
          :model-value="enabled"
          @update:model-value="enabled = $event; reloadDbStats()"
          :title="translate('PrivacyManager_UseDeleteLog')"
          :inline-help="translate('PrivacyManager_DeleteRawDataInfo')"
        >
        </Field>
      </div>
      <div
        class="alert alert-warning deleteOldLogsWarning"
        style="width: 50%;"
        v-show="enabled"
      >
        <a
          href="https://matomo.org/faq/general/faq_125"
          rel="noreferrer noopener"
          target="_blank"
        >
          {{ translate('General_ClickHere') }}
        </a>
      </div>
    </div>
    <div
      id="deleteLogSettings"
      v-show="enabled"
    >
      <div>
        <Field
          uicontrol="text"
          name="deleteOlderThan"
          :model-value="deleteOlderThan"
          @update:model-value="deleteOlderThan = $event; reloadDbStats()"
          :title="deleteOlderThanTitle"
          :inline-help="translate('PrivacyManager_LeastDaysInput', '1')"
        >
        </Field>
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
      <h2 v-if="enabled && !enableDeleteReports">
        {{ translate('PrivacyManager_DeleteLogsConfirm') }}
      </h2>
      <h2 v-if="enabled && enableDeleteReports">
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

interface DeleteOldLogsState {
  isLoading: boolean;
  enabled: boolean;
  deleteOlderThan: string;
  showPasswordConfirmModal: boolean;
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
    PasswordConfirmation,
    Field,
    SaveButton,
  },
  directives: {
    Form,
  },
  data(): DeleteOldLogsState {
    return {
      isLoading: false,
      enabled: parseInt(this.deleteData.config.delete_logs_enable, 10) === 1,
      deleteOlderThan: this.deleteData.config.delete_logs_older_than,
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
      const method = 'PrivacyManager.setDeleteLogsSettings';
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
        enableDeleteLogs: !!this.enabled,
        deleteLogsOlderThan: this.deleteOlderThan,
      };
    },
    deleteOlderThanTitle(): string {
      return `${translate('PrivacyManager_DeleteLogsOlderThan')} (${translate('Intl_PeriodDays')})`;
    },
    enableDeleteReports(): boolean {
      return !!ReportDeletionSettingsStore.enableDeleteReports.value;
    },
  },
});
</script>
