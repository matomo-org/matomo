<template>
  <ol class="list-style-decimal">
    <li>{{ translate('CoreAdminHome_JsTrackingCodeAdvancedOptionsStep') }}
      <JsTrackingCodeAdvancedOptions
          :site="site"
          :max-custom-variables="maxCustomVariables"
          :server-side-do-not-track-enabled="serverSideDoNotTrackEnabled"
          @updateTrackingCode="updateTrackingCode"/>
    </li>
    <li>
      <span>{{ getCopyCodeStep }}</span>
      <div id="javascript-text">
        <div>
          <pre v-copy-to-clipboard="{}" class="codeblock" v-text="trackingCode" ref="trackingCode"/>
        </div>
      </div>
    </li>
    <li><JsTrackerInstallCheck :site="site"/></li>
  </ol>
</template>
<script lang="ts">
import { defineComponent } from 'vue';
import {
  SiteRef,
  CopyToClipboard,
  translate,
} from 'CoreHome';
import { JsTrackerInstallCheck } from 'JsTrackerInstallCheck';
import JsTrackingCodeAdvancedOptions from './JsTrackingCodeAdvancedOptions.vue';

interface JsTrackingCodeGeneratorSitesWithoutDataState {
  site: SiteRef;
  trackingCode: string;
  isHighlighting: boolean;
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
    JsTrackerInstallCheck,
    JsTrackingCodeAdvancedOptions,
  },
  directives: {
    CopyToClipboard,
  },
  data(): JsTrackingCodeGeneratorSitesWithoutDataState {
    return {
      site: this.defaultSite as SiteRef,
      trackingCode: '',
      isHighlighting: false,
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
  computed: {
    getCopyCodeStep() {
      return translate('CoreAdminHome_JSTracking_CodeNoteBeforeClosingHead', '</head>');
    },
  },
});
</script>
