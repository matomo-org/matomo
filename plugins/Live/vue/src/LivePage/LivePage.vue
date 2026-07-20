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
      <LiveWidget
        :live-refresh-after-ms="liveRefreshAfterMs"
        :disable-link="disableLink"
      />
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
