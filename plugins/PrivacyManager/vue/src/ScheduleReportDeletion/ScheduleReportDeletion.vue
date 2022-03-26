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
    <ContentBlock
      id="scheduleSettingsHeadline"
      v-show="isEitherDeleteSectionEnabled"
      :content-title="translate('PrivacyManager_DeleteSchedulingSettings')"
    >
      <div id="deleteSchedulingSettings">
        <div>
          <Field
            uicontrol="select"
            name="deleteLowestInterval"
            :title="translate('PrivacyManager_DeleteDataInterval')"
            v-model="deleteLowestInterval"
            :options="scheduleDeletionOptions"
          >
            <template v-slot:inline-help>
              <div
                id="deleteSchedulingSettingsInlineHelp"
                class="inline-help-node"
              >
                <span v-if="deleteData.lastRun">
                  <strong>{{ translate('PrivacyManager_LastDelete') }}:</strong>
                  {{ deleteData.lastRunPretty }}
                  <br />
                  <br />
                </span>
                <strong>{{ translate('PrivacyManager_NextDelete') }}:</strong>
                {{ deleteData.nextRunPretty }}
                <br />
                <br />
                <a
                  id="purgeDataNowLink"
                  href="#"
                  v-show="showPurgeNowLink"
                  @click="executeDataPurgeNow()"
                >{{ translate('PrivacyManager_PurgeNow') }}</a>
                <ActivityIndicator
                  :loading-message="translate('PrivacyManager_PurgingData')"
                  :loading="loadingDataPurge"
                />
                <span
                  id="db-purged-message"
                  v-show="dataWasPurged"
                >{{ translate('PrivacyManager_DBPurged') }}</span>
              </div>
            </template>
          </Field>
        </div>
      </div>
      <div
        id="deleteDataEstimateSect"
        class="form-group row"
        v-if="deleteData.config.enable_database_size_estimate === '1'
          || deleteData.config.enable_database_size_estimate === 1"
      >
        <h3
          class="col s12"
          id="databaseSizeHeadline"
        >
          {{ translate('PrivacyManager_ReportsDataSavedEstimate') }}
        </h3>
        <div class="col s12 m6">
          <div
            id="deleteDataEstimate"
            v-show="showEstimate"
            v-html="$sanitize(estimation)"
          />&nbsp;<ActivityIndicator :loading="loadingEstimation" />
        </div>
        <div class="col s12 m6">
          <div
            v-if="deleteData.config.enable_auto_database_size_estimate !== '1'
                && deleteData.config.enable_auto_database_size_estimate !== 1"
            class="form-help"
          >
            <a
              id="getPurgeEstimateLink"
              href="#"
              @click.prevent="getPurgeEstimate()"
            >{{ translate('PrivacyManager_GetPurgeEstimate') }}</a>
          </div>
        </div>
      </div>
      <SaveButton
        @confirm="save()"
        :saving="isLoading"
      />
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  Matomo,
  AjaxHelper,
  ContentBlock,
  ActivityIndicator,
} from 'CoreHome';
import { Form, Field, SaveButton } from 'CorePluginsAdmin';
import ReportDeletionSettingsStore from '../ReportDeletionSettings/ReportDeletionSettings.store';

interface ScheduleReportDeletionState {
  isLoading: boolean;
  loadingDataPurge: boolean;
  dataWasPurged: boolean;
  showPurgeNowLink: boolean;
  deleteLowestInterval: string;
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
    ContentBlock,
    ActivityIndicator,
    Field,
    SaveButton,
  },
  directives: {
    Form,
  },
  data(): ScheduleReportDeletionState {
    return {
      isLoading: false,
      loadingDataPurge: false,
      dataWasPurged: false,
      showPurgeNowLink: true,
      deleteLowestInterval: this.deleteData.config.delete_logs_schedule_lowest_interval,
    };
  },
  methods: {
    save() {
      const method = 'PrivacyManager.setScheduleReportDeletionSettings';
      ReportDeletionSettingsStore.savePurgeDataSettings(method, {
        deleteLowestInterval: this.deleteLowestInterval,
      });
    },
    executeDataPurgeNow() {
      if (ReportDeletionSettingsStore.state.value.isModified) {
        // ask user if they really want to delete their old data
        Matomo.helper.modalConfirm('#saveSettingsBeforePurge', {
          yes: () => null,
        });

        return;
      }

      Matomo.helper.modalConfirm('#confirmPurgeNow', {
        yes: () => {
          this.loadingDataPurge = true;
          this.showPurgeNowLink = false; // execute a data purge

          AjaxHelper.fetch(
            {
              module: 'PrivacyManager',
              action: 'executeDataPurge',
              format: 'html',
            },
            { withTokenInUrl: true },
          ).then(() => {
            // force reload
            ReportDeletionSettingsStore.reloadDbStats();
            this.dataWasPurged = true;

            setTimeout(() => {
              this.dataWasPurged = false;
              this.showPurgeNowLink = true;
            }, 2000);
          }).finally(() => {
            this.loadingDataPurge = false;
          });
        },
      });
    },
    getPurgeEstimate() {
      return ReportDeletionSettingsStore.reloadDbStats(true);
    },
  },
  computed: {
    showEstimate() {
      return ReportDeletionSettingsStore.state.value.showEstimate;
    },
    isEitherDeleteSectionEnabled() {
      return ReportDeletionSettingsStore.isEitherDeleteSectionEnabled();
    },
    estimation() {
      return ReportDeletionSettingsStore.state.value.estimation;
    },
    loadingEstimation() {
      return ReportDeletionSettingsStore.state.value.loadingEstimation;
    },
  },
});
</script>
