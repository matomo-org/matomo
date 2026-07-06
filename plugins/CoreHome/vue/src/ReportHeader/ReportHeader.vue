<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="reportHeader">
    <div class="reportHeader__main">
      <!-- The title text lives in a nested <span> to preserve the legacy `.widgetName > span`
           contract other plugins hook into (dataTable related reports, UserCountryMap map
           title, SingleMetricView metric title + series picker). The `widgetName` class is
           kept only as that external hook — styling goes through `.reportHeader__title`. -->
      <h3
        class="reportHeader__title widgetName"
        :class="{ 'reportHeader__title--clickable': titleClickable }"
        :role="titleClickable ? 'button' : null"
        :tabindex="titleClickable ? 0 : null"
        :title="titleClickable ? titleClickHint : null"
        @click="onTitleClick"
        @keydown.enter.prevent="onTitleClick"
        @keydown.space.prevent="onTitleClick"
      >
        <span>{{ title }}</span>
      </h3>
      <!-- future: report-feedback actions live here alongside the title -->
    </div>

    <div class="reportHeader__widgetControls">
      <WidgetControlsDropdown
        v-if="hasControls"
        :can-minimise="controls.minimise"
        :can-maximise="controls.maximise"
        :can-refresh="controls.refresh"
        :can-close="controls.close"
        @minimise="$emit('minimise')"
        @maximise="$emit('maximise')"
        @refresh="$emit('refresh')"
        @close="$emit('close')"
      />
    </div>

    <!-- Reserved anchor for report actions (visualisation switcher, export, ...).
         Populated by a later story; intentionally empty here. -->
    <div class="reportHeader__actions"></div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import WidgetControlsDropdown from '../WidgetControlsDropdown/WidgetControlsDropdown.vue';
import { translate } from '../translate';

interface ControlVisibility {
  minimise: boolean;
  maximise: boolean;
  refresh: boolean;
  close: boolean;
}

// Which widget controls each context exposes. Kept here so every surface that renders
// the header stays consistent with the redesign spec.
const CONTROLS_BY_CONTEXT: Record<string, ControlVisibility> = {
  dashboard: {
    minimise: true, maximise: true, refresh: true, close: true,
  },
  maximised: {
    minimise: true, maximise: false, refresh: true, close: false,
  },
  widgetized: {
    minimise: false, maximise: false, refresh: false, close: false,
  },
  preview: {
    minimise: false, maximise: false, refresh: false, close: false,
  },
};

export default defineComponent({
  props: {
    context: {
      type: String,
      default: 'dashboard',
    },
    title: String,
    titleClickable: Boolean,
    titleClickHint: String,
  },
  components: {
    WidgetControlsDropdown,
  },
  emits: ['minimise', 'maximise', 'refresh', 'close', 'titleClick'],
  computed: {
    controls(): ControlVisibility {
      return CONTROLS_BY_CONTEXT[this.context] || CONTROLS_BY_CONTEXT.widgetized;
    },
    hasControls(): boolean {
      const c = this.controls;
      return c.minimise || c.maximise || c.refresh || c.close;
    },
  },
  methods: {
    translate,
    onTitleClick() {
      if (this.titleClickable) {
        this.$emit('titleClick');
      }
    },
  },
});
</script>
