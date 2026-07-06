<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="widgetControls"
    v-expand-on-click="{ expander: 'trigger' }"
  >
    <button
      ref="trigger"
      type="button"
      class="widgetControls__trigger"
      :title="translate('CoreHome_WidgetControls')"
      :aria-label="translate('CoreHome_WidgetControls')"
    >
      <!-- 3-dots icon; fill/stroke use currentColor so the .less controls idle/hover colour -->
      <svg
        class="widgetControls__icon"
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

    <!-- nest element: owns positioning + open/close; hosts the shared dropdown panel -->
    <div class="widgetControls__menu">
      <div class="mtm-dropdownPanel">
        <ul class="mtm-dropdownPanel__menu">
          <li
            v-for="control in visibleControls"
            :key="control.id"
            class="mtm-dropdownPanel__menuItem"
          >
            <button
              type="button"
              class="mtm-dropdownPanel__menuLink"
              :class="`widgetControl-${control.id}`"
              @click="onControl(control.id)"
            >
              <span class="mtm-dropdownPanel__menuIcon" :class="control.icon"></span>
              <span class="mtm-dropdownPanel__menuLabel">{{ control.label }}</span>
            </button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import ExpandOnClick from '../ExpandOnClick/ExpandOnClick';
import { translate } from '../translate';

interface WidgetControl {
  id: string;
  icon: string;
  label: string;
  visible: boolean;
}

export default defineComponent({
  props: {
    canMinimise: Boolean,
    canMaximise: Boolean,
    canRefresh: Boolean,
    canClose: Boolean,
  },
  directives: {
    ExpandOnClick,
  },
  emits: ['minimise', 'maximise', 'refresh', 'close'],
  computed: {
    visibleControls(): WidgetControl[] {
      const controls: WidgetControl[] = [
        {
          id: 'minimise',
          icon: 'icon-minimise',
          label: translate('Dashboard_Minimise'),
          visible: this.canMinimise,
        },
        {
          id: 'maximise',
          icon: 'icon-fullscreen',
          label: translate('Dashboard_Maximise'),
          visible: this.canMaximise,
        },
        {
          id: 'refresh',
          icon: 'icon-reload',
          label: translate('General_Refresh'),
          visible: this.canRefresh,
        },
        {
          id: 'close',
          icon: 'icon-close',
          label: translate('General_Close'),
          visible: this.canClose,
        },
      ];

      return controls.filter((control) => control.visible);
    },
  },
  methods: {
    translate,
    onControl(intent: string) {
      // Emit a Vue event for Vue-native consumers...
      this.$emit(intent as 'minimise'|'maximise'|'refresh'|'close');

      // ...and dispatch a bubbling native event so non-Vue owners (the jQuery dashboard
      // widget) can bridge control intents back to their existing handlers.
      this.$el.dispatchEvent(new CustomEvent(`widgetcontrol:${intent}`, { bubbles: true }));

      // close the menu after a selection (ExpandOnClick only auto-closes on outside click)
      (this.$el as HTMLElement).classList.remove('expanded');
    },
  },
});
</script>
