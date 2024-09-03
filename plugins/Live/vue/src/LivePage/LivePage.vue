<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <component
      :is="!isWidgetized ? 'ContentBlock' : 'Passthrough'"
      :content-title="!isWidgetized ? translate('Live_VisitorsInRealTime') : undefined"
    >
      <div v-live-widget-refresh="{liveRefreshAfterMs: liveRefreshAfterMs}">
        <VueEntryContainer :html="initialTotalVisitors"/>
        <VueEntryContainer :html="visitors"/>
      </div>

      <div class="visitsLiveFooter">
        <a
          :title="translate('Live_OnClickPause', translate('Live_VisitorsInRealTime'))"
          @click.prevent="onClickPause()"
        >
          <img id="pauseImage" border="0" src="plugins/Live/images/pause.png" />
        </a>
        <a
          :title="translate('Live_OnClickStart', translate('Live_VisitorsInRealTime'))"
          @click="onClickPlay();"
        >
          <img
            id="playImage"
            style="display: none;"
            border="0"
            src="plugins/Live/images/play.png"
          />
        </a>
        <span v-if="!disableLink">
        &nbsp;
        <a
          class="rightLink"
          :href="visitorLogUrl"
        >
          {{ translate('Live_LinkVisitorLog') }}
        </a>
        </span>
      </div>
    </component>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  Passthrough,
  MatomoUrl,
  VueEntryContainer,
} from 'CoreHome';
import LiveWidgetRefresh from '../LiveWidget/LiveWidgetRefresh';
import TotalVisitors from '../TotalVisitors/TotalVisitors.vue';

declare global {
  interface Window {
    onClickPause(): void;
    onClickPlay(): void;
  }
}

export default defineComponent({
  props: {
    disableLink: Boolean,
    visitors: String,
    initialTotalVisitors: String,
    liveRefreshAfterMs: Number,
    isWidgetized: Boolean,
  },
  components: {
    TotalVisitors,
    VueEntryContainer,
    ContentBlock,
    Passthrough,
  },
  directives: {
    LiveWidgetRefresh,
  },
  computed: {
    visitorLogUrl() {
      return `#?${MatomoUrl.stringify({
        ...MatomoUrl.hashParsed.value,
        category: 'General_Visitors',
        subcategory: 'Live_VisitorLog',
      })}`;
    },
  },
  methods: {
    onClickPause() {
      window.onClickPause();
    },
    onClickPlay() {
      window.onClickPlay();
    },
  },
});
</script>
