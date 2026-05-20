<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="widgetpreview-preview">
    <div
      v-if="widget"
      class="widget"
    >
      <div
        class="widgetTop"
        :title="translate('Dashboard_AddPreviewedWidget')"
        role="button"
        tabindex="0"
        @click="$emit('select', widget.uniqueId)"
        @keydown.enter.prevent="$emit('select', widget.uniqueId)"
        @keydown.space.prevent="$emit('select', widget.uniqueId)"
      >
        <h3 class="widgetName">{{ translate('Dashboard_WidgetPreview') }}</h3>
      </div>
      <div class="widgetContent">
        <Widget
          :key="widget.uniqueId"
          :widget="widget"
          :widgetized="true"
          :suppress-notifications="true"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  translate,
  Widget,
  WidgetType,
} from 'CoreHome';

export default defineComponent({
  name: 'WidgetPreview',
  components: {
    Widget,
  },
  props: {
    widget: {
      type: Object as PropType<WidgetType | null>,
      default: null,
    },
  },
  emits: ['select'],
  methods: {
    translate,
  },
});
</script>
