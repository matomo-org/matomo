<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div v-content-intro>
      <h2>
        <EnrichedHeadline :help-url="externalRawLink('https://matomo.org/docs/privacy/')">
          {{ translate('PrivacyManager_AnonymizeData') }}
        </EnrichedHeadline>
      </h2>

      <p>
        <span v-html="$sanitize(teaserHeader)" style="margin-right:3.5px"></span>
        <span v-html="$sanitize(seeAlsoOurOfficialGuide)"></span>
      </p>
    </div>

    <ContentBlock
       id="anonymizeIPAnchor"
       :content-title="translate('PrivacyManager_UseAnonymizeTrackingData')"
    >
      <AnonymizeIp
        :anonymize-ip-enabled="anonymizeIpEnabled"
        :anonymize-user-id="anonymizeUserId"
        :mask-length="maskLength"
        :use-anonymized-ip-for-visit-enrichment="useAnonymizedIpForVisitEnrichment"
        :anonymize-order-id="anonymizeOrderId"
        :force-cookieless-tracking="forceCookielessTracking"
        :anonymize-referrer="anonymizeReferrer"
        :mask-length-options="maskLengthOptions"
        :use-anonymized-ip-for-visit-enrichment-options="useAnonymizedIpForVisitEnrichmentOptions"
        :tracker-file-name="trackerFileName"
        :tracker-writable="trackerWritable"
        :referrer-anonymization-options="referrerAnonymizationOptions"
        :randomize-config-id="randomizeConfigId"
        :config-randomisation-feature-flag="configRandomisationFeatureFlag"
      />
    </ContentBlock>

    <div v-if="isDataPurgeSettingsEnabled">
      <ContentBlock
        id="deleteLogsAnchor"
        :content-title="translate('PrivacyManager_DeleteOldRawData')"
      >
        <p>{{ translate('PrivacyManager_DeleteDataDescription') }}</p>

        <DeleteOldLogs
          :is-data-purge-settings-enabled="isDataPurgeSettingsEnabled"
          :delete-data="deleteData"
          :schedule-deletion-options="scheduleDeletionOptions"
        />
      </ContentBlock>

      <ContentBlock
        id="deleteReportsAnchor"
        :content-title="translate('PrivacyManager_DeleteOldAggregatedReports')"
      >
        <DeleteOldReports
          :is-data-purge-settings-enabled="isDataPurgeSettingsEnabled"
          :delete-data="deleteData"
          :schedule-deletion-options="scheduleDeletionOptions"
        ></DeleteOldReports>

      </ContentBlock>

      <ScheduleReportDeletion
        :is-data-purge-settings-enabled="isDataPurgeSettingsEnabled"
        :delete-data="deleteData"
        :schedule-deletion-options="scheduleDeletionOptions"
      ></ScheduleReportDeletion>
    </div>

    <a name="anonymizeHistoricalData" id="anonymizeHistoricalData"></a>

    <ContentBlock
      :content-title="translate('PrivacyManager_AnonymizePreviousData')"
      class="logDataAnonymizer"
    >
      <p>
        {{ translate('PrivacyManager_AnonymizePreviousDataDescription') }}
      </p>

      <AnonymizeLogData v-if="isSuperUser"></AnonymizeLogData>
      <p v-else>{{ translate('PrivacyManager_AnonymizePreviousDataOnlySuperUser') }}</p>

      <br />
      <PreviousAnonymizations
        :anonymizations="anonymizations"
      />
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  ContentIntro,
  EnrichedHeadline,
  translate,
  externalLink,
} from 'CoreHome';
import AnonymizeIp from '../AnonymizeIp/AnonymizeIp.vue';
import DeleteOldLogs from '../DeleteOldLogs/DeleteOldLogs.vue';
import DeleteOldReports from '../DeleteOldReports/DeleteOldReports.vue';
import ScheduleReportDeletion from '../ScheduleReportDeletion/ScheduleReportDeletion.vue';
import AnonymizeLogData from '../AnonymizeLogData/AnonymizeLogData.vue';
import PreviousAnonymizations from '../AnonymizeLogData/PreviousAnonymizations.vue';

export default defineComponent({
  props: {
    anonymizeIpEnabled: Boolean,
    anonymizeUserId: Boolean,
    maskLength: {
      type: Number,
      required: true,
    },
    useAnonymizedIpForVisitEnrichment: [Boolean, String, Number],
    anonymizeOrderId: Boolean,
    forceCookielessTracking: Boolean,
    anonymizeReferrer: String,
    maskLengthOptions: {
      type: Array,
      required: true,
    },
    useAnonymizedIpForVisitEnrichmentOptions: {
      type: Array,
      required: true,
    },
    trackerFileName: {
      type: String,
      required: true,
    },
    trackerWritable: {
      type: Boolean,
      required: true,
    },
    referrerAnonymizationOptions: {
      type: Object,
      required: true,
    },
    isDataPurgeSettingsEnabled: Boolean,
    deleteData: {
      type: Object,
      required: true,
    },
    scheduleDeletionOptions: {
      type: Object,
      required: true,
    },
    anonymizations: {
      type: Array,
      required: true,
    },
    isSuperUser: Boolean,
    randomizeConfigId: Boolean,
    configRandomisationFeatureFlag: Boolean,
  },
  components: {
    AnonymizeIp,
    EnrichedHeadline,
    ContentBlock,
    DeleteOldLogs,
    DeleteOldReports,
    ScheduleReportDeletion,
    AnonymizeLogData,
    PreviousAnonymizations,
  },
  directives: {
    ContentIntro,
  },
  computed: {
    teaserHeader() {
      return translate(
        'PrivacyManager_TeaserHeader',
        '<a href="#anonymizeIPAnchor">',
        '</a>',
        '<a href="#deleteLogsAnchor">',
        '</a>',
        '<a href="#anonymizeHistoricalData">',
        '</a>',
      );
    },
    seeAlsoOurOfficialGuide() {
      return translate(
        'PrivacyManager_SeeAlsoOurOfficialGuidePrivacy',
        externalLink('https://matomo.org/privacy/'),
        '</a>',
      );
    },
  },
});
</script>
