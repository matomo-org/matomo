<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('CoreAdminHome_ArchivingSettings')"
    anchor="archivingSettings"
    class="matomo-archiving-settings"
  >
    <div>
      <div class="form-group row">
        <h3 class="col s12">{{ translate('General_AllowPiwikArchivingToTriggerBrowser') }}</h3>
        <div class="col s12 m6">
          <p>
            <label>
              <input
                type="radio"
                id="enableBrowserTriggerArchiving1"
                name="enableBrowserTriggerArchiving"
                value="1"
                v-model="enableBrowserTriggerArchivingValue"
              />
              <span>{{ translate('General_Yes') }}</span>
              <span class="form-description" style="margin-left: 4px;">
                {{ translate('General_Default') }}
              </span>
            </label>
          </p>

          <p>
            <label for="enableBrowserTriggerArchiving2">
              <input
                type="radio"
                id="enableBrowserTriggerArchiving2"
                name="enableBrowserTriggerArchiving"
                value="0"
                v-model="enableBrowserTriggerArchivingValue"
              />
              <span>{{ translate('General_No') }}</span>
              <span
                class="form-description"
                v-html="$sanitize(archivingTriggerDesc)"
                style="margin-left: 4px;"
              >
              </span>
            </label>
          </p>
        </div><div class="col s12 m6">
        <div class="form-help" v-html="$sanitize(archivingInlineHelp)">
        </div>
      </div>
      </div>

      <div class="form-group row">
        <h3 class="col s12">
          {{ translate('General_ReportsContainingTodayWillBeProcessedAtMostEvery') }}
        </h3>
        <div class="input-field col s12 m6">
          <input
            type="text"
            v-model="todayArchiveTimeToLiveValue"
            id='todayArchiveTimeToLive'
            :disabled="!isGeneralSettingsAdminEnabled"
          />
          <span class="form-description">
            {{ translate('General_RearchiveTimeIntervalOnlyForTodayReports') }}
          </span>
        </div>
        <div class="col s12 m6">
          <div class="form-help" v-if="isGeneralSettingsAdminEnabled">
            <strong v-if="showWarningCron">
              {{ translate('General_NewReportsWillBeProcessedByCron') }}<br/>
              {{ translate('General_ReportsWillBeProcessedAtMostEveryHour') }}
              {{ translate('General_IfArchivingIsFastYouCanSetupCronRunMoreOften') }}<br/>
            </strong>
            {{ translate('General_SmallTrafficYouCanLeaveDefault', todayArchiveTimeToLiveDefault) }}
            <br/>
            {{ translate('General_MediumToHighTrafficItIsRecommendedTo', 1800, 3600) }}
          </div>
        </div>
      </div>

      <div>
        <SaveButton
          :saving="isLoading"
          @confirm="save()"
        />
      </div>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  translate,
  NotificationsStore,
  AjaxHelper,
} from 'CoreHome';
import { SaveButton } from 'CorePluginsAdmin';

export default defineComponent({
  props: {
    enableBrowserTriggerArchiving: Boolean,
    showSegmentArchiveTriggerInfo: Boolean,
    isGeneralSettingsAdminEnabled: Boolean,
    showWarningCron: Boolean,
    todayArchiveTimeToLive: Number,
    todayArchiveTimeToLiveDefault: Number,
  },
  components: {
    ContentBlock,
    SaveButton,
  },
  data() {
    return {
      isLoading: false,
      enableBrowserTriggerArchivingValue: this.enableBrowserTriggerArchiving ? 1 : 0,
      todayArchiveTimeToLiveValue: this.todayArchiveTimeToLive,
    };
  },
  watch: {
    enableBrowserTriggerArchiving(newValue) {
      this.enableBrowserTriggerArchivingValue = newValue ? 1 : 0;
    },
    todayArchiveTimeToLive(newValue) {
      this.todayArchiveTimeToLiveValue = newValue;
    },
  },
  computed: {
    archivingTriggerDesc() {
      let result = '';

      result += translate(
        'General_ArchivingTriggerDescription',
        '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/docs/setup-auto-archiving/">',
        '</a>',
      );

      if (this.showSegmentArchiveTriggerInfo) {
        result += translate('General_ArchivingTriggerSegment');
      }

      return result;
    },
    archivingInlineHelp() {
      let result = translate('General_ArchivingInlineHelp');
      result += '<br/>';
      result += translate(
        'General_SeeTheOfficialDocumentationForMoreInformation',
        '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/docs/setup-auto-archiving/">',
        '</a>',
      );
      return result;
    },
  },
  methods: {
    save() {
      this.isLoading = true;

      AjaxHelper.post({ module: 'API', method: 'CoreAdminHome.setArchiveSettings' }, {
        enableBrowserTriggerArchiving: this.enableBrowserTriggerArchivingValue,
        todayArchiveTimeToLive: this.todayArchiveTimeToLiveValue,
      }).then(() => {
        this.isLoading = false;

        const notificationId = NotificationsStore.show({
          message: translate('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success',
        });
        NotificationsStore.scrollToNotification(notificationId);
      }).finally(() => {
        this.isLoading = false;
      });
    },
  },
});

</script>
