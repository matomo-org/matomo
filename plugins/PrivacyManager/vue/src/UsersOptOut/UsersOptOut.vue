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
      <component
        v-for="(preface, index) in prefaceComponentsResolved"
        :key="index"
        :is="preface"
      ></component>

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
import { defineComponent, markRaw } from 'vue';
import {
  Alert,
  ContentBlock,
  useExternalPluginComponent,
  Matomo,
} from 'CoreHome';
import DoNotTrackPreference from '../DoNotTrackPreference/DoNotTrackPreference.vue';
import OptOutCustomizer from '../OptOutCustomizer/OptOutCustomizer.vue';

interface UsersOptOutState {
  prefaceComponents: { plugin: string, component: string }[];
}

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
  data(): UsersOptOutState {
    return {
      prefaceComponents: [],
    };
  },
  computed: {
    prefaceComponentsResolved() {
      return markRaw(this.prefaceComponents.map(
        (c) => markRaw(useExternalPluginComponent(c.plugin, c.component)),
      ));
    },
  },
  created() {
    const components: { plugin: string, component: string }[] = [];
    Matomo.postEvent('PrivacyManager.UsersOptOut.preface', components);
    this.prefaceComponents = components;
  },
});
</script>
