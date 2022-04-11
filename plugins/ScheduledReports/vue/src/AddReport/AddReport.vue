<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    class="entityAddContainer"
    :content-title="translate('ScheduledReports_CreateAndScheduleReport')"
  >
    <div class="clear" />
    <form
      id="addEditReport"
      @submit="$emit('submit')"
      v-form
    >
      <div>
        <Field
          uicontrol="text"
          name="website"
          :title="translate('General_Website')"
          :disabled="true"
          :model-value="siteName"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="textarea"
          name="report_description"
          :title="translate('General_Description')"
          :model-value="report.description"
          @update:model-value="$emit('change', { prop: 'description', value: $event })"
          :inline-help="translate('ScheduledReports_DescriptionOnFirstPage')"
        >
        </Field>
      </div>
      <div v-if="segmentEditorActivated">
        <Field
          uicontrol="select"
          name="report_segment"
          :title="translate('SegmentEditor_ChooseASegment')"
          :model-value="report.idsegment"
          @update:model-value="$emit('change', { prop: 'idsegment', value: $event })"
          :options="savedSegmentsById"
        >
          <template v-slot:inline-help>
            <div
              id="reportSegmentInlineHelp"
              class="inline-help-node"
              v-if="segmentEditorActivated"
              v-html="$sanitize(reportSegmentInlineHelp)"
            />
          </template>
        </Field>
      </div>
      <div>
        <Field
          uicontrol="select"
          name="report_schedule"
          :model-value="report.period"
          @update:model-value="$emit('change', { prop: 'period', value: $event });
            $emit('change', {
              prop: 'periodParam',
              value: report.period === 'never' ? null : report.period,
            })"
          :title="translate('ScheduledReports_EmailSchedule')"
          :options="periods"
        >
          <template v-slot:inline-help>
            <div
              id="emailScheduleInlineHelp"
              class="inline-help-node"
            >
              {{ translate('ScheduledReports_WeeklyScheduleHelp') }}
              <br />
              {{ translate('ScheduledReports_MonthlyScheduleHelp') }}
            </div>
          </template>
        </Field>
      </div>
      <div>
        <Field
          uicontrol="select"
          name="report_period"
          :model-value="report.periodParam"
          @update:model-value="$emit('change', { prop: 'periodParam', value: $event })"
          :options="paramPeriods"
          :title="translate('ScheduledReports_ReportPeriod')"
        >
          <template v-slot:inline-help>
            <div
              id="emailReportPeriodInlineHelp"
              class="inline-help-node"
            >
              {{ translate('ScheduledReports_ReportPeriodHelp') }}
              <br /><br />
              {{ translate('ScheduledReports_ReportPeriodHelp2') }}
            </div>
          </template>
        </Field>
      </div>
      <div>
        <Field
          uicontrol="select"
          name="report_hour"
          :model-value="report.hour"
          @update:model-value="$emit('change', { prop: 'hour', value: $event })"
          :title="translate('ScheduledReports_ReportHour', 'X')"
          :options="reportHours"
        >
          <template v-slot:inline-help>
            <div
              id="reportHourHelpText"
              class="inline-help-node"
              v-if="timezoneOffset !== 0 && timezoneOffset !== '0'"
            >
              <span v-text="reportHourUtc" />
            </div>
          </template>
        </Field>
      </div>
      <div>
        <Field
          uicontrol="select"
          name="report_type"
          :disabled="reportTypes.length === 1"
          :model-value="report.type"
          @update:model-value="$emit('change', { prop: 'type', value: $event })"
          :title="translate('ScheduledReports_ReportType')"
          :options="reportTypeOptions"
        >
        </Field>
      </div>
      <div
        v-for="(reportFormats, reportType) in reportFormatsByReportTypeOptions"
        :key="reportType"
      >
        <Field
          uicontrol="select"
          name="report_format"
          :title="translate('ScheduledReports_ReportFormat')"
          :class="reportType"
          v-show="report.type === reportType"
          :model-value="report[`format${reportType}`]"
          @update:model-value="$emit('change', { prop: `format${reportType}`, value: $event })"
          :options="reportFormats"
        >
        </Field>
      </div>
      <div ref="reportParameters">
        <slot name="report-parameters"></slot>
      </div>
      <div
        v-show="report.type === 'email'
              && report.formatemail !== 'csv'
              && report.formatemail !== 'tsv'"
      >
        <div class="email">
          <Field
            uicontrol="select"
            name="display_format"
            :model-value="report.displayFormat"
            @update:model-value="$emit('change', { prop: 'displayFormat', value: $event })"
            :options="displayFormats"
            :introduction="translate('ScheduledReports_AggregateReportsFormat')"
          >
          </Field>
        </div>
        <div class="report_evolution_graph">
          <Field
            uicontrol="checkbox"
            name="report_evolution_graph"
            :title="translate('ScheduledReports_EvolutionGraph', 5)"
            v-show="[2, '2', 3, '3'].indexOf(report.displayFormat) !== -1"
            :model-value="report.evolutionGraph"
            @update:model-value="$emit('change', { prop: 'evolutionGraph', value: $event })"
          >
          </Field>
        </div>
        <div
          class="row evolution-graph-period"
          v-show="[1, '1', 2, '2', 3, '3'].indexOf(report.displayFormat) !== -1"
        >
          <div class="col s12">
            <label for="report_evolution_period_for_each">
              <input
                id="report_evolution_period_for_each"
                name="report_evolution_period_for"
                type="radio"
                value="each"
                :checked="report.evolutionPeriodFor === 'each'"
                @change="$emit(
                  'change',
                  { prop: 'evolutionPeriodFor', value: $event.target.value },
                )"
              />
              <span v-html="$sanitize(evolutionGraphsShowForEachInPeriod)"></span>
            </label>
          </div>
          <div class="col s12">
            <label for="report_evolution_period_for_prev">
              <input
                id="report_evolution_period_for_prev"
                name="report_evolution_period_for"
                type="radio"
                value="prev"
                :checked="report.evolutionPeriodFor === 'prev'"
                @change="$emit(
                  'change',
                  { prop: 'evolutionPeriodFor', value: $event.target.value },
                )"
              />
              <span>{{ translate(
                'ScheduledReports_EvolutionGraphsShowForPreviousN',
                frequencyPeriodPlural,
              ) }}:
                <input
                  type="number"
                  name="report_evolution_period_n"
                  :value="report.evolutionPeriodN"
                  @keydown="onEvolutionPeriodN($event)"
                  @change="onEvolutionPeriodN($event)"
                />
              </span>
            </label>
          </div>
        </div>
      </div>
      <div class="row">
        <h3 class="col s12">{{ translate('ScheduledReports_ReportsIncluded') }}</h3>
      </div>
      <div
        name="reportsList"
        :class="`row ${reportType}`"
        v-show="report.type === reportType"
        v-for="(reportColumns, reportType) in reportsByCategoryByReportTypeInColumns"
        :key="reportType"
      >
        <div class="col s12 m6" v-for="(reportsByCategory, index) in reportColumns" :key="index">
          <div v-for="(reports, category) in reportsByCategory" :key="category">
            <h3 class="reportCategory">{{ category }}</h3>
            <ul class="listReports">
              <li v-for="report in reports" :key="report.uniqueId">
                <label>
                  <input
                    :name="`${reportType}Reports`"
                    :type="allowMultipleReportsByReportType[reportType] ? 'checkbox' : 'radio'"
                    :id="`${reportType}${report.uniqueId}`"
                    :checked="selectedReports[reportType]?.[report.uniqueId]"
                    @change="$emit('toggleSelectedReport', {
                      reportType,
                      uniqueId: report.uniqueId,
                    })"
                  />
                  <span>{{ decode(report.name) }}</span>
                  <div class="entityInlineHelp" v-if="report.uniqueId === 'MultiSites_getAll'">
                    {{ translate('ScheduledReports_ReportIncludeNWebsites', countWebsites) }}
                  </div>
                </label>
              </li>
            </ul>
            <br />
          </div>
        </div>
      </div>
      <SaveButton
        :value="saveButtonTitle"
        @confirm="$emit('submit')"
      />
      <div
        class="entityCancel"
        v-html="$sanitize(entityCancelText)"
      >
      </div>
    </form>
  </ContentBlock>
</template>

<script lang="ts">
import {
  defineComponent,
  onMounted,
  reactive,
  ref,
  watch,
} from 'vue';
import {
  ContentBlock,
  Matomo,
  translate,
  debounce,
} from 'CoreHome';
import { Field, Form, SaveButton } from 'CorePluginsAdmin';
import { adjustHourToTimezone } from '../utilities';

interface Option {
  key: string;
  value: string;
}

export default defineComponent({
  props: {
    report: {
      type: Object,
      required: true,
    },
    selectedReports: Object,
    paramPeriods: {
      type: Object,
      required: true,
    },
    reportTypeOptions: {
      type: Object,
      required: true,
    },
    reportFormatsByReportTypeOptions: {
      type: Object,
      required: true,
    },
    displayFormats: {
      type: Object,
      required: true,
    },
    reportsByCategoryByReportType: {
      type: Object,
      required: true,
    },
    allowMultipleReportsByReportType: {
      type: Object,
      required: true,
    },
    countWebsites: {
      type: Number,
      required: true,
    },
    siteName: {
      type: String,
      required: true,
    },
    reportTypes: {
      type: Object,
      required: true,
    },
    segmentEditorActivated: Boolean,
    savedSegmentsById: Object,
    periods: {
      type: Object,
      required: true,
    },
  },
  emits: ['submit', 'change', 'toggleSelectedReport'],
  components: {
    ContentBlock,
    Field,
    SaveButton,
  },
  directives: {
    Form,
  },
  created() {
    this.onEvolutionPeriodN = debounce(this.onEvolutionPeriodN, 50);
  },
  methods: {
    onEvolutionPeriodN(event: Event) {
      this.$emit('change', {
        prop: 'evolutionPeriodN',
        value: (event.target as HTMLInputElement).value,
      });
    },
    decode(s: string) {
      // report names can be encoded (mainly goals)
      return Matomo.helper.htmlDecode(s);
    },
  },
  setup(props, ctx) {
    const reportParameters = ref<HTMLElement|null>(null);

    const angularControllerProxy = reactive({
      report: {
        ...props.report,
      },
    });

    watch(
      () => angularControllerProxy.report,
      (newValue) => {
        Object.keys(newValue).forEach((key) => {
          if (newValue[key] !== props.report[key]) {
            ctx.emit('change', { prop: key, value: newValue[key] });
          }
        });
      },
      { deep: true },
    );

    watch(
      () => props.report,
      (newValue) => {
        Object.assign(angularControllerProxy.report, newValue);
        Matomo.helper.getAngularDependency('$timeout')();
      },
      { deep: true },
    );

    onMounted(() => {
      const reportParametersElement = reportParameters.value as HTMLElement;
      Matomo.helper.compileAngularComponents(reportParametersElement, {
        params: {
          manageScheduledReport: angularControllerProxy,
        },
      });

      Matomo.helper.compileVueEntryComponents(reportParametersElement, {
        report: angularControllerProxy.report,
        onChange(prop: string, value: unknown) {
          ctx.emit('change', { prop, value });
        },
      });
    });

    return {
      reportParameters,
    };
  },
  beforeUnmount() {
    const reportParameters = this.$refs.reportParameters as HTMLElement;
    Matomo.helper.destroyVueComponent(reportParameters);
  },
  computed: {
    reportsByCategoryByReportTypeInColumns() {
      const reportsByCategoryByReportType = this.reportsByCategoryByReportType as
        Record<string, Record<string, unknown[]>>;

      const inColumns = Object.entries(reportsByCategoryByReportType).map(
        ([key, reportsByCategory]) => {
          const newColumnAfter = Math.floor((Object.keys(reportsByCategory).length + 1) / 2);

          const column1: Record<string, unknown[]> = {};
          const column2: Record<string, unknown[]> = {};

          let currentColumn = column1;
          Object.entries(reportsByCategory).forEach(([category, reports]) => {
            currentColumn[category] = reports;

            if (Object.keys(currentColumn).length >= newColumnAfter) {
              currentColumn = column2;
            }
          });

          return [key, [column1, column2]];
        },
      );

      return Object.fromEntries(inColumns);
    },
    entityCancelText() {
      return translate(
        'General_OrCancel',
        '<a class="entityCancelLink">',
        '</a>',
      );
    },
    frequencyPeriodSingle() {
      if (!this.report || !this.report.period) {
        return '';
      }

      const { ReportPlugin } = window;

      let translation = ReportPlugin.periodTranslations[this.report.period];
      if (!translation) {
        translation = ReportPlugin.periodTranslations.day;
      }
      return translation.single;
    },
    frequencyPeriodPlural() {
      if (!this.report || !this.report.period) {
        return '';
      }

      const { ReportPlugin } = window;

      let translation = ReportPlugin.periodTranslations[this.report.period];
      if (!translation) {
        translation = ReportPlugin.periodTranslations.day;
      }
      return translation.plural;
    },
    evolutionGraphsShowForEachInPeriod() {
      return translate(
        'ScheduledReports_EvolutionGraphsShowForEachInPeriod',
        '<strong>',
        '</strong>',
        this.frequencyPeriodSingle,
      );
    },
    reportSegmentInlineHelp() {
      return translate(
        'ScheduledReports_Segment_Help',
        '<a href="./" rel="noreferrer noopener" target="_blank">',
        '</a>',
        translate('SegmentEditor_DefaultAllVisits'),
        translate('SegmentEditor_AddNewSegment'),
      );
    },
    timezoneOffset() {
      return Matomo.timezoneOffset;
    },
    timeZoneDifferenceInHours() {
      return Matomo.timezoneOffset / 3600;
    },
    reportHours() {
      const hours: Option[] = [];
      for (let i = 0; i < 24; i += 1) {
        if ((this.timeZoneDifferenceInHours * 2) % 2 !== 0) {
          hours.push({
            key: `${i}.5`,
            value: `${i}:30`,
          });
        } else {
          hours.push({
            key: `${i}`,
            value: `${i}`,
          });
        }
      }
      return hours;
    },
    reportHourUtc() {
      const reportHour = adjustHourToTimezone(
        this.report.hour as string,
        -this.timeZoneDifferenceInHours,
      );
      return translate('ScheduledReports_ReportHourWithUTC', [reportHour]);
    },
    saveButtonTitle() {
      const { ReportPlugin } = window;

      const isCreate = this.report.idreport > 0;
      return isCreate ? ReportPlugin.updateReportString : ReportPlugin.createReportString;
    },
  },
});
</script>
