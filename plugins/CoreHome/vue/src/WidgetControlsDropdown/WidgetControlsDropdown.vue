<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<!--
  A group of widget-control actions (minimise / maximise / refresh / close), rendered as a
  dropdown-panel menu. It owns no trigger or panel of its own: the dropdown shell (3-dots
  trigger + panel + open/close) is provided by the host (ReportHeader), and this group is
  slotted inside that panel. Each action only emits an intent; the host decides what to do.
-->
<template>
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
        @click="$emit(control.id)"
      >
        <span class="mtm-dropdownPanel__menuIcon" :class="control.icon"></span>
        <span class="mtm-dropdownPanel__menuLabel">{{ control.label }}</span>
      </button>
    </li>
  </ul>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
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
});
</script>
