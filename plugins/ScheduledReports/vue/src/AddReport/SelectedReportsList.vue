<template>
  <div
    class="selectedReportsWrapper"
    v-if="shouldDisplay"
  >
    <div class="selectedReportsHeading">
      <h3>{{ translate('ScheduledReports_SelectedReports') }}</h3>
    </div>
    <p class="selectedReportsHelp">
      {{ translate('ScheduledReports_SelectedReportsHelp') }}
    </p>
    <DraggableList
      class="selectedReportsList"
      :items="reports"
      item-key="uniqueId"
      @reorder="onReorder"
    >
      <template #default="{ item: report }">
        <span class="icon-menu-hamburger drag-icon"></span>
        <span class="selectedReportName">{{ decode(report.name) }}</span>
      </template>
    </DraggableList>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  PropType,
} from 'vue';
import {
  DraggableList,
  Matomo,
  translate,
} from 'CoreHome';

interface SelectedReport {
  uniqueId: string;
  name: string;
}

export default defineComponent({
  name: 'SelectedReportsList',
  props: {
    reports: {
      type: Array as PropType<SelectedReport[]>,
      required: true,
    },
    enabled: {
      type: Boolean,
      default: true,
    },
  },
  emits: ['reorder'],
  components: {
    DraggableList,
  },
  computed: {
    shouldDisplay(): boolean {
      return !!this.enabled && this.reports.length > 0;
    },
  },
  methods: {
    translate,
    decode(name: string) {
      return Matomo.helper.htmlDecode(name);
    },
    onReorder(order: string[]) {
      this.$emit('reorder', order);
    },
  },
});
</script>
