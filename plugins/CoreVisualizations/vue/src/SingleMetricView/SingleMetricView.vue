<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="singleMetricView"
    :class="{'loading': isLoading}"
    ref="root"
  >
    <div class="metric-sparkline">
      <Sparkline :params="sparklineParams">
      </Sparkline>
    </div>
    <div class="metric-value">
      <span :title="metricDocumentation">
        <strong>{{ metricValue }}</strong> {{ (metricTranslation || '').toLowerCase() }}
      </span>
      <span
        class="metricEvolution"
        v-if="pastValue !== null"
        :title="translate(
          'General_EvolutionSummaryGeneric', metricValue, currentPeriod, pastValue,
          pastPeriod, metricChangePercent)"
      >
        <span
          :class="{
            'positive-evolution': metricValueUnformatted > pastValueUnformatted,
            'negative-evolution': metricValueUnformatted < pastValueUnformatted,
          }"
        >
          {{ metricChangePercent }}
        </span>
      </span>
    </div>
  </div>
</template>

<script lang="ts">
import {
  computed,
  createVNode,
  defineComponent,
  onBeforeUnmount,
  onMounted,
  ref,
  watch,
} from 'vue';
import {
  Matomo,
  AjaxHelper,
  Sparkline,
  Range,
  Periods,
  format,
  createVueApp, translate,
} from 'CoreHome';
import SeriesPicker from '../SeriesPicker/SeriesPicker.vue';

interface SelectableColumnInfo {
  column: string;
  translation: string;
}

type MetricValues = Record<string, number|string>;

interface Goal {
  idgoal: string|number;
  name: string;
}

function getPastPeriodStr(): string {
  const { startDate } = Range.getLastNRange(Matomo.period!, 2, Matomo.currentDateString!);
  const dateRange = Periods.get(Matomo.period!).parse(startDate).getDateRange();
  return `${format(dateRange[0])},${format(dateRange[1])}`;
}

const { $ } = window;

export default defineComponent({
  props: {
    metric: {
      type: String,
      required: true,
    },
    idGoal: [String, Number],
    metricTranslations: {
      type: Object,
      required: true,
    },
    metricDocumentations: Object,
    goals: {
      type: Object,
      required: true,
    },
    goalMetrics: Array,
  },
  components: {
    Sparkline,
  },
  setup(props) {
    const root = ref<HTMLElement|null>(null);

    const isLoading = ref<boolean>(false);
    const responses = ref<null|MetricValues[]>(null);
    const actualMetric = ref<string>(props.metric);
    const actualIdGoal = ref<string|number|undefined>(props.idGoal);

    const selectedColumns = computed(() => [
      actualIdGoal.value ? `goal${actualIdGoal.value}_${actualMetric.value}` : actualMetric.value,
    ]);

    const metricValueUnformatted = computed(() => {
      if (!responses.value?.[1]) {
        return null;
      }

      return responses.value[1][actualMetric.value];
    });

    const pastValueUnformatted = computed(() => {
      if (!responses.value?.[2]) {
        return null;
      }

      return responses.value[2][actualMetric.value] || 0;
    });

    const metricChangePercent = computed(() => {
      if (!metricValueUnformatted.value) {
        return null;
      }

      const currentValue: number = typeof metricValueUnformatted.value === 'string'
        ? parseInt(metricValueUnformatted.value, 10)
        : metricValueUnformatted.value as number;

      const pastValue: number = typeof pastValueUnformatted.value === 'string'
        ? parseInt(pastValueUnformatted.value, 10)
        : pastValueUnformatted.value as number;

      const evolution = Matomo.helper.calculateEvolution(currentValue, pastValue);

      return `${(evolution * 100).toFixed(2)} %`;
    });

    const pastValue = computed(() => {
      if (!responses.value?.[3]) {
        return null;
      }

      const pastDataFormatted = responses.value[3];
      return pastDataFormatted[actualMetric.value] || 0;
    });

    const metricValue = computed(() => {
      if (!responses.value?.[0]) {
        return null;
      }

      const currentData = responses.value[0];
      return currentData[actualMetric.value] || 0;
    });

    const metricTranslation = computed(() => {
      if (!props.metricTranslations?.[actualMetric.value]) {
        return '';
      }

      return props.metricTranslations[actualMetric.value];
    });

    const metricDocumentation = computed(() => {
      if (!props.metricDocumentations?.[actualMetric.value]) {
        return '';
      }

      return props.metricDocumentations[actualMetric.value];
    });

    const currentPeriod = computed(() => {
      if (Matomo.startDateString === Matomo.endDateString) {
        return Matomo.endDateString;
      }
      return `${Matomo.startDateString}, ${Matomo.endDateString}`;
    });

    function isIdGoalSet() {
      return actualIdGoal.value || actualIdGoal.value === 0;
    }

    const sparklineParams = computed<QueryParameters>(() => {
      const params: QueryParameters = {
        module: 'API',
        action: 'get',
        columns: actualMetric.value,
      };

      if (isIdGoalSet()) {
        params.idGoal = actualIdGoal.value;
        params.module = 'Goals';
      }

      return params;
    });

    const pastPeriod = computed(() => {
      if (Matomo.period === 'range') {
        return undefined;
      }

      return getPastPeriodStr();
    });

    const selectableColumns = computed(() => {
      const result: SelectableColumnInfo[] = [];

      Object.keys(props.metricTranslations).forEach((column) => {
        result.push({
          column,
          translation: props.metricTranslations[column],
        });
      });

      Object.values((props.goals || {}) as Record<string, Goal>).forEach((goal) => {
        (props.goalMetrics as string[]).forEach((column) => {
          result.push({
            column: `goal${goal.idgoal}_${column}`,
            translation: `${goal.name} - ${props.metricTranslations[column]}`,
          });
        });
      });

      return result;
    });

    function setWidgetTitle() {
      let title = metricTranslation.value;

      if (isIdGoalSet()) {
        const goalName = props.goals[actualIdGoal.value!]?.name || translate('General_Unknown');
        title = `${goalName} - ${title}`;
      }

      $(root.value as HTMLElement)
        .closest('div.widget')
        .find('.widgetTop > .widgetName > span')
        .text(title);
    }

    function getLastPeriodDate(): string {
      const range = Range.getLastNRange(Matomo.period!, 2, Matomo.currentDateString!);
      return format(range.startDate);
    }

    function fetchData() {
      isLoading.value = true;

      const promises = [];
      let apiModule = 'API';
      let apiAction = 'get';
      const extraParams: QueryParameters = {};

      if (isIdGoalSet()) {
        // the conversion rate added by the AddColumnsProcessedMetrics filter conflicts w/
        // the goals one, so don't run it
        extraParams.idGoal = actualIdGoal.value;

        extraParams.filter_add_columns_when_show_all_columns = 0;
        apiModule = 'Goals';
        apiAction = 'get';
      }

      const method = `${apiModule}.${apiAction}`;

      // first request for formatted data
      promises.push(AjaxHelper.fetch({
        method,
        format_metrics: 'all',
        ...extraParams,
      }));

      if (Matomo.period !== 'range') {
        // second request for unformatted data so we can calculate evolution
        promises.push(AjaxHelper.fetch({
          method,
          format_metrics: '0',
          ...extraParams,
        }));

        // third request for past data (unformatted)
        promises.push(AjaxHelper.fetch({
          method,
          date: getLastPeriodDate(),
          format_metrics: '0',
          ...extraParams,
        }));

        // fourth request for past data (formatted for tooltip display)
        promises.push(AjaxHelper.fetch({
          method,
          date: getLastPeriodDate(),
          format_metrics: 'all',
          ...extraParams,
        }));
      }

      return Promise.all(promises).then((r) => {
        responses.value = r;
        isLoading.value = false;
      });
    }

    function onMetricChanged(newMetric: string) {
      actualMetric.value = newMetric;

      fetchData().then(setWidgetTitle); // notify widget of parameter change so it is replaced

      $(root.value as HTMLElement).closest('[widgetId]').trigger('setParameters', {
        column: actualMetric.value,
        idGoal: actualIdGoal.value,
      });
    }

    function setMetric(newColumn: string) {
      let idGoal: number|undefined = undefined;
      let actualColumn: string = newColumn;

      const m = newColumn.match(/^goal([0-9]+)_(.*)/);
      if (m) {
        idGoal = +m[1];
        [, , actualColumn] = m;
      }

      if (actualMetric.value !== actualColumn || idGoal !== actualIdGoal.value) {
        actualMetric.value = actualColumn;
        actualIdGoal.value = idGoal;
        onMetricChanged(actualColumn);
      }
    }

    function createSeriesPicker() {
      const element = $(root.value as HTMLElement);
      const $widgetName = element.closest('div.widget').find('.widgetTop > .widgetName');

      const $seriesPickerElem = $('<div class="single-metric-view-picker"><div></div></div>');

      const app = createVueApp({
        render: () => createVNode(SeriesPicker, {
          multiselect: false,
          selectableColumns: selectableColumns.value,
          selectableRows: [],
          selectedColumns: selectedColumns.value,
          selectedRows: [],
          onSelect: ({ columns }: { columns: string[] }) => {
            setMetric(columns[0]);
          },
        }),
      });

      $widgetName.append($seriesPickerElem);
      app.mount($seriesPickerElem.children()[0]);
      return app;
    }

    let seriesPickerApp: ReturnType<typeof createVueApp>;

    onMounted(() => {
      seriesPickerApp = createSeriesPicker();
    });

    onBeforeUnmount(() => {
      $(root.value as HTMLElement)
        .closest('.widgetContent')
        .off('widget:destroy')
        .off('widget:reload');
      $(root.value as HTMLElement)
        .closest('div.widget')
        .find('.single-metric-view-picker')
        .remove();
      seriesPickerApp.unmount();
    });

    watch(() => props.metric, () => {
      onMetricChanged(props.metric);
    });
    onMetricChanged(props.metric);

    return {
      root,
      metricValue,
      isLoading,
      selectedColumns,
      responses,
      metricValueUnformatted,
      pastValueUnformatted,
      metricChangePercent,
      pastValue,
      metricTranslation,
      metricDocumentation,
      sparklineParams,
      pastPeriod,
      selectableColumns,
      currentPeriod,
    };
  },
});
</script>
