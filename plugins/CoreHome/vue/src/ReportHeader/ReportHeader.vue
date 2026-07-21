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
        :role="titleClickable ? 'button' : undefined"
        :tabindex="titleClickable ? 0 : undefined"
        :title="titleClickable ? titleClickHint : undefined"
        @click="onTitleClick"
        @keydown.enter.prevent="onTitleClick"
        @keydown.space.prevent="onTitleClick"
      >
        <span>{{ title }}</span>
      </h3>
      <!-- Visually-hidden label so assistive tech announces the region as a widget,
           restoring the old `.widgetNameOffScreen` text. -->
      <span class="u-visuallyHidden">{{ translate('General_Widget') }}</span>
      <!-- future: report-feedback actions live here alongside the title -->
    </div>

    <!-- Widget controls: an inline action row, hidden until the widget is hovered/focused.
         Each action emits an intent that onControl() bridges to the jQuery widget. -->
    <div class="reportHeader__widgetControls">
      <WidgetControls
        v-if="hasControls"
        :can-minimise="controls.minimise"
        :can-maximise="controls.maximise"
        :can-refresh="controls.refresh"
        :can-close="controls.close"
        @minimise="onControl('minimise')"
        @maximise="onControl('maximise')"
        @refresh="onControl('refresh')"
        @close="onControl('close')"
      />
    </div>

    <!-- Reserved anchor for report actions (visualisation switcher, export, ...).
         Populated by a later story; intentionally empty here. -->
    <div class="reportHeader__actions" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import WidgetControls from '../WidgetControls/WidgetControls.vue';
import { translate } from '../translate';

export interface ControlVisibility {
  minimise: boolean;
  maximise: boolean;
  refresh: boolean;
  close: boolean;
}

// Which widget controls each context exposes. Kept here so every surface that renders
// the header stays consistent with the redesign spec. `dashboard` is the normal widget state
// (all controls only make sense on a dashboard); `maximised`/`collapsed` are its state
// variants; `widgetized`/`preview` render no controls. Consumers outside a widget (e.g.
// full-page reports) pass a no-control context.
const CONTROLS_BY_CONTEXT: Record<string, ControlVisibility> = {
  dashboard: {
    minimise: true, maximise: true, refresh: true, close: true,
  },
  maximised: {
    minimise: true, maximise: false, refresh: true, close: false,
  },
  collapsed: {
    minimise: false, maximise: true, refresh: false, close: true,
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
    title: {
      type: String,
      default: '',
    },
    titleClickable: Boolean,
    titleClickHint: {
      type: String,
      default: '',
    },
  },
  components: {
    WidgetControls,
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
    onControl(intent: string) {
      // Re-emit for Vue-native consumers...
      this.$emit(intent as 'minimise'|'maximise'|'refresh'|'close');

      // ...and dispatch a bubbling native event so non-Vue owners (the jQuery dashboard
      // widget) can bridge control intents back to their existing handlers.
      this.$el.dispatchEvent(new CustomEvent(`widgetcontrol:${intent}`, { bubbles: true }));
    },
  },
});
</script>
