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
    <ul
      class="selectedReportsList"
      ref="list"
    >
      <li
        v-for="report in reports"
        :key="report.uniqueId"
        :data-unique-id="report.uniqueId"
      >
        <span class="icon-menu-hamburger drag-icon"></span>
        <span class="selectedReportName">{{ decode(report.name) }}</span>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  nextTick,
  PropType,
} from 'vue';
import {
  Matomo,
  translate,
} from 'CoreHome';

const { $ } = window;

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
  computed: {
    shouldDisplay(): boolean {
      return !!this.enabled && this.reports.length > 0;
    },
  },
  watch: {
    reports() {
      this.scheduleRefresh();
    },
    enabled() {
      this.scheduleRefresh();
    },
  },
  mounted() {
    this.scheduleRefresh();
  },
  beforeUnmount() {
    this.destroySortable();
  },
  methods: {
    translate,
    decode(name: string) {
      return Matomo.helper.htmlDecode(name);
    },
    getListElement() {
      return this.$refs.list as HTMLElement|undefined;
    },
    scheduleRefresh() {
      nextTick(() => {
        this.refreshSortable();
      });
    },
    refreshSortable() {
      if (!this.shouldDisplay) {
        this.destroySortable();
        return;
      }

      const listElement = this.getListElement();
      if (!listElement) {
        return;
      }

      const $list = $(listElement);
      if ($list.data('ui-sortable')) {
        $list.sortable('refresh');
        return;
      }

      $list.sortable({
        axis: 'y',
        helper: 'clone',
        placeholder: 'selectedReportPlaceholder',
        stop: () => {
          this.emitOrder();
        },
      });
    },
    destroySortable() {
      const listElement = this.getListElement();
      if (!listElement) {
        return;
      }

      const $list = $(listElement);
      if ($list.data('ui-sortable')) {
        $list.sortable('destroy');
      }
    },
    emitOrder() {
      const listElement = this.getListElement();
      if (!listElement) {
        return;
      }

      const order = $(listElement).find('li').map(function mapSelected() {
        return String($(this).data('uniqueId'));
      }).get();

      this.$emit('reorder', order);
    },
  },
});
</script>
