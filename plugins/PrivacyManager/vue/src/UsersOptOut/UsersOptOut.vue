<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <ContentBlock
      :content-title="translate('PrivacyManager_TrackingOptOut')"
    >
      <OptOutCustomizer
        :matomo-url="matomoUrl"
        :language="language"
        :language-options="languageOptions"
      />
    </ContentBlock>

    <ContentBlock
      v-if="isSuperUser"
      id="DNT"
      :content-title="translate('PrivacyManager_DoNotTrack_SupportDNTPreference')"
    >
      <Alert severity="warning">
        {{ translate('PrivacyManager_DoNotTrack_Deprecated') }}
      </Alert>
      <p>
        <span v-if="dntSupport">
          <strong>{{ translate('PrivacyManager_DoNotTrack_Enabled') }}</strong>
          <br/>
          {{ translate('PrivacyManager_DoNotTrack_EnabledMoreInfo') }}
        </span>
        <span v-else>
          {{ translate('PrivacyManager_DoNotTrack_Disabled') }}
          {{ translate('PrivacyManager_DoNotTrack_DisabledMoreInfo') }}
        </span>
      </p>

      <DoNotTrackPreference
        :dnt-support="dntSupport"
        :do-not-track-options="doNotTrackOptions"
      />
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Alert, ContentBlock } from 'CoreHome';
import DoNotTrackPreference from '../DoNotTrackPreference/DoNotTrackPreference.vue';
import OptOutCustomizer from '../OptOutCustomizer/OptOutCustomizer.vue';

export default defineComponent({
  props: {
    language: {
      type: String,
      required: true,
    },
    matomoUrl: String,
    isSuperUser: Boolean,
    dntSupport: Boolean,
    doNotTrackOptions: {
      type: Array,
      required: true,
    },
    languageOptions: {
      type: Object,
      required: true,
    },
  },
  components: {
    Alert,
    ContentBlock,
    DoNotTrackPreference,
    OptOutCustomizer,
  },
});
</script>
