<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<!--
  An inline row of widget-control actions (minimise / maximise / refresh / close), rendered as
  icon buttons. Each action only emits an intent; the host (ReportHeader) decides what to do
  and bridges it to the jQuery widget. Visibility per control is driven by the host.
-->
<template>
  <div class="widgetControls">
    <button
      v-for="control in visibleControls"
      :key="control.id"
      type="button"
      class="widgetControls__action"
      :class="`widgetControls__action--${control.id}`"
      :title="control.label"
      :aria-label="control.label"
      @click="$emit(control.id)"
    >
      <span class="widgetControls__icon" :class="control.icon" />
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from '../translate';

export interface WidgetControl {
  id: 'minimise' | 'maximise' | 'refresh' | 'close';
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
          id: 'refresh',
          icon: 'icon-reload',
          label: translate('General_Refresh'),
          visible: this.canRefresh,
        },
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
