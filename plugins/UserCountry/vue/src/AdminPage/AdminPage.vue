<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-content-intro>
    <h2>
      <EnrichedHeadline
        :help-url="externalRawLink('https://matomo.org/docs/geo-locate/')"
        id="location-providers"
      >
        {{ translate('UserCountry_Geolocation') }}
      </EnrichedHeadline>
    </h2>
    <p>{{ translate('UserCountry_GeolocationPageDesc') }}</p>
  </div>
  <ContentBlock :content-title="translate('UserCountry_LocationProvider')">
    <LocationProviderSelection
      :current-provider-id="currentProviderId"
      :is-there-working-provider="isThereWorkingProvider"
      :set-up-guides="setUpGuides"
      :this-ip="thisIp"
      :location-providers="locationProviders"
      :default-provider-id="defaultProviderId"
      :disabled-provider-id="disabledProviderId"
    />
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  ContentIntro,
  EnrichedHeadline,
} from 'CoreHome';
import LocationProviderSelection from '../LocationProviderSelection/LocationProviderSelection.vue';

export default defineComponent({
  props: {
    currentProviderId: {
      type: String,
      required: true,
    },
    isThereWorkingProvider: Boolean,
    setUpGuides: String,
    thisIp: {
      type: String,
      required: true,
    },
    locationProviders: {
      type: Object,
      required: true,
    },
    defaultProviderId: {
      type: String,
      required: true,
    },
    disabledProviderId: {
      type: String,
      required: true,
    },
  },
  components: {
    ContentBlock,
    LocationProviderSelection,
    EnrichedHeadline,
  },
  directives: {
    ContentIntro,
    ContentBlock,
  },
});
</script>
