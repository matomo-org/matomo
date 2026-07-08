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
      <!-- Visually-hidden label so assistive tech announces the region as a widget,
           restoring the old `.widgetNameOffScreen` text. -->
      <span class="u-visuallyHidden">{{ translate('General_Widget') }}</span>
      <!-- future: report-feedback actions live here alongside the title -->
    </div>

    <div class="reportHeader__widgetControls">
      <!-- The dropdown shell (3-dots trigger + panel) is owned here so it can be reused
           across widgets and full-page reports. Action groups are slotted into the panel;
           the widget controls are one such group (WidgetControlsDropdown). -->
      <div
        v-if="hasControls"
        ref="dropdown"
        class="reportHeader__dropdown"
        v-expand-on-click="{ expander: 'dropdownTrigger' }"
      >
        <button
          ref="dropdownTrigger"
          type="button"
          class="reportHeader__dropdownTrigger"
          :title="translate('CoreHome_WidgetControls')"
          :aria-label="translate('CoreHome_WidgetControls')"
        >
          <!-- 3-dots icon; fill/stroke use currentColor so the .less controls idle/hover colour -->
          <svg
            class="reportHeader__dropdownIcon"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
            focusable="false"
          >
            <path
              d="M9.99935 10.834C10.4596 10.834 10.8327 10.4609 10.8327 10.0007C10.8327 9.54041
                10.4596 9.16732 9.99935 9.16732C9.53911 9.16732 9.16602 9.54041 9.16602
                10.0007C9.16602 10.4609 9.53911 10.834 9.99935 10.834Z"
              fill="currentColor"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M9.99935 5.00065C10.4596 5.00065 10.8327 4.62755 10.8327 4.16732C10.8327
                3.70708 10.4596 3.33398 9.99935 3.33398C9.53911 3.33398 9.16602 3.70708 9.16602
                4.16732C9.16602 4.62755 9.53911 5.00065 9.99935 5.00065Z"
              fill="currentColor"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M9.99935 16.6673C10.4596 16.6673 10.8327 16.2942 10.8327 15.834C10.8327
                15.3737 10.4596 15.0007 9.99935 15.0007C9.53911 15.0007 9.16602 15.3737 9.16602
                15.834C9.16602 16.2942 9.53911 16.6673 9.99935 16.6673Z"
              fill="currentColor"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </button>

        <div class="reportHeader__dropdownMenu">
          <div class="mtm-dropdownPanel">
            <WidgetControlsDropdown
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
        </div>
      </div>
    </div>

    <!-- Reserved anchor for report actions (visualisation switcher, export, ...).
         Populated by a later story; intentionally empty here. -->
    <div class="reportHeader__actions"></div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import ExpandOnClick from '../ExpandOnClick/ExpandOnClick';
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
  directives: {
    ExpandOnClick,
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

      // Close the panel after a selection (ExpandOnClick only auto-closes on outside click).
      const dropdown = this.$refs.dropdown as HTMLElement | undefined;
      if (dropdown) {
        dropdown.classList.remove('expanded');
      }
    },
  },
});
</script>
