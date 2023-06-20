<template>
  <div id="javascript-text">
    <div>
      <pre v-copy-to-clipboard="{}" class="codeblock" v-text="trackingCode" ref="trackingCode"/>
    </div>
  </div>
  <JsTrackingCodeAdvancedOptions
    :default-site="defaultSite"
    :max-custom-variables="maxCustomVariables"
    :server-side-do-not-track-enabled="serverSideDoNotTrackEnabled"
    :showBottomHR="true"
    @updateTrackingCode="updateTrackingCode"
    ref="jsTrackingCodeAdvanceOption"/>
</template>
<script lang="ts">
import { defineComponent } from 'vue';
import {
  SiteRef,
  CopyToClipboard,
} from 'CoreHome';

import JsTrackingCodeAdvancedOptions from './JsTrackingCodeAdvancedOptions.vue';

interface CustomVar {
  name: string;
  value: string;
}

interface JsTrackingCodeGeneratorState {
  showAdvanced: boolean;
  site: SiteRef;
  trackingCode: string;
  trackAllSubdomains: boolean;
  isLoading: boolean;
  siteUrls: Record<string, string[]>;
  siteExcludedQueryParams: Record<string, string[]>,
  siteExcludedReferrers: Record<string, string[]>,
  crossDomain: boolean;
  groupByDomain: boolean;
  trackAllAliases: boolean;
  trackNoScript: boolean;
  trackCustomVars: boolean;
  customVars: CustomVar[];
  canAddMoreCustomVariables: boolean;
  doNotTrack: boolean;
  disableCookies: boolean;
  useCustomCampaignParams: boolean;
  customCampaignName: string;
  customCampaignKeyword: string;
  trackingCodeAbortController: AbortController|null;
  isHighlighting: boolean;
  consentManagerName: string;
  consentManagerUrl: string;
  consentManagerIsConnected: boolean;
}

export default defineComponent({
  props: {
    defaultSite: {
      type: Object,
      required: true,
    },
    maxCustomVariables: Number,
    serverSideDoNotTrackEnabled: Boolean,
    jsTag: String,
  },
  components: {
    JsTrackingCodeAdvancedOptions,
  },
  directives: {
    CopyToClipboard,
  },
  data(): JsTrackingCodeGeneratorState {
    return {
      showAdvanced: false,
      site: this.defaultSite as SiteRef,
      trackingCode: '',
      trackAllSubdomains: false,
      isLoading: false,
      siteUrls: {},
      siteExcludedQueryParams: {},
      siteExcludedReferrers: {},
      crossDomain: false,
      groupByDomain: false,
      trackAllAliases: false,
      trackNoScript: false,
      trackCustomVars: false,
      customVars: [],
      canAddMoreCustomVariables: !!this.maxCustomVariables && this.maxCustomVariables > 0,
      doNotTrack: false,
      disableCookies: false,
      useCustomCampaignParams: false,
      customCampaignName: '',
      customCampaignKeyword: '',
      trackingCodeAbortController: null,
      isHighlighting: false,
      consentManagerName: '',
      consentManagerUrl: '',
      consentManagerIsConnected: false,
    };
  },
  created() {
    if (this.jsTag) {
      this.trackingCode = this.jsTag;
    }
  },
  methods: {
    updateTrackingCode(code:string) {
      this.trackingCode = code;

      const jsCodeTextarea = $(this.$refs.trackingCode as HTMLElement);
      if (jsCodeTextarea && !this.isHighlighting) {
        this.isHighlighting = true;
        jsCodeTextarea.effect('highlight', {
          complete: () => {
            this.isHighlighting = false;
          },
        }, 1500);
      }
    },
  },
});
</script>
