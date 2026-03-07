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
<<<<<<< HEAD
      <LiveWidget
        :live-refresh-after-ms="liveRefreshAfterMs"
        :disable-link="disableLink"
      />
=======
      <div v-live-widget-refresh="{liveRefreshAfterMs: liveRefreshAfterMs}">
        <VueEntryContainer :html="initialTotalVisitors"/>
        <VueEntryContainer :html="visitors"/>
      </div>

      <div class="visitsLiveFooter">
        <a
          :title="translate('Live_OnClickPause', translate('Live_VisitorsInRealTime'))"
          @click.prevent="onClickPause()"
        >
          <img id="pauseImage" border="0" src="plugins/Live/images/pause.svg" role="presentation" />
        </a>
        <a
          :title="translate('Live_OnClickStart', translate('Live_VisitorsInRealTime'))"
          @click="onClickPlay();"
        >
          <img
            id="playImage"
            style="display: none;"
            border="0"
            src="plugins/Live/images/play.svg"
            role="presentation"
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
>>>>>>> 72d03d26c (Initiate Dark mode)
    </component>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  Passthrough,
} from 'CoreHome';
import LiveWidget from '../LiveWidget/LiveWidget.vue';

export default defineComponent({
  props: {
    disableLink: Boolean,
    liveRefreshAfterMs: Number,
    isWidgetized: Boolean,
  },
  components: {
    LiveWidget,
    ContentBlock,
    Passthrough,
  },
});
</script>
