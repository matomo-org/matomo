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
          :value="siteName"
        >
        </Field>
      </div>
      <div>
        <Field
          uicontrol="textarea"
          name="report_description"
          :title="translate('General_Description')"
          v-model="report.description"
          :inline-help="translate('ScheduledReports_DescriptionOnFirstPage')"
        >
        </Field>
      </div>
      <div v-if="segmentEditorActivated">
        <Field
          uicontrol="select"
          name="report_segment"
          :title="translate('SegmentEditor_ChooseASegment')"
          v-model="report.idsegment"
          :options="savedSegmentsById"
        >
          <template v-slot:inline-help>
            <div
              id="reportSegmentInlineHelp"
              class="inline-help-node"
              v-if="segmentEditorActivated"
              v-html="reportSegmentInlineHelp"
            />
          </template>
        </Field>
      </div>
      <div>
        <Field
          uicontrol="select"
          name="report_schedule"
          :model-value="report.period"
          @update:model-value="report.period = $event;
                report.periodParam = report.period === 'never' ? null : report.period"
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
          v-model="report.periodParam"
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
          @update:model-value="report.hour = $event; updateReportHourUtc()"
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
          @update:model-value="report.type = $event; changedReportType()"
          :title="translate('ScheduledReports_ReportType')"
          :options="reportTypeOptions"
        >
        </Field>
      </div>
      <div v-for="(reportType, reportFormats) in reportFormatsByReportTypeOptions">
        <Field
          uicontrol="select"
          name="report_format"
          :title="translate('ScheduledReports_ReportFormat')"
          :class="reportType"
          v-show="report.type === reportType"
          v-model="report[`format${reportType}`]"
          :options="reportFormats"
        >
        </Field>
      </div>
      <slot name="report-parameters"></slot>
      <div
        v-show="report.type === 'email'
              && report.formatemail !== 'csv'
              && report.formatemail !== 'tsv'"
      >
        <div class="email">
          <Field
            uicontrol="select"
            name="display_format"
            v-model="report.displayFormat"
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
            v-show="report.displayFormat in [2, '2', 3, '3']"
            v-model="report.evolutionGraph"
          >
          </Field>
        </div>
        <div
          class="row evolution-graph-period"
          v-show="report.displayFormat in [1, '1', 2, '2', 3, '3']"
        >
          <div class="col s12">
            <label for="report_evolution_period_for_each">
              <input
                id="report_evolution_period_for_each"
                name="report_evolution_period_for"
                type="radio"
                checked
                v-model="actualReport.evolutionPeriodFor"
              />
              <span v-html="$sanitize(evolutionGraphsShowForEachInPeriod)">
                  </span>
            </label>
          </div>
          <div class="col s12">
            <label for="report_evolution_period_for_prev">
              <input
                id="report_evolution_period_for_prev"
                name="report_evolution_period_for"
                type="radio"
                v-model="actualReport.evolutionPeriodFor"
              />
              <span>{{ translate(
                'ScheduledReports_EvolutionGraphsShowForPreviousN',
                frequencyPeriodPlural,
              ) }}:
                    <input
                      type="number"
                      name="report_evolution_period_n"
                      v-model="report.evolutionPeriodN"
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
        v-for="(reportType, reportColumns) in reportsByCategoryByReportTypeInColumns"
      >
        <div class="col s12 m6" v-for="reportsByCategory in reportColumns">
          <div v-for="(category, reports) in reportsByCategory">
            <h3 class="reportCategory">{{ category }}</h3>
            <ul class="listReports">
              <li v-for="report in reports">
                <label>
                  <input
                    :name="`${reportType}Reports`"
                    :type="allowMultipleReportsByReportType[reportType] ? 'checkbox' : 'radio'"
                    :id="`${reportType}${report.uniqueId}`"
                    :checked="selectedReports[reportType]?.[report.uniqueId]"
                    @change="$emit('toggleSelectedReport', { reportType, uniqueId: report.uniqueId })"
                  />
                  <span>{{ report.name }}</span>
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
      <input
        type="hidden"
        id="report_idreport"
        v-model="editingReportId"
      />
      <SaveButton
        :value="saveButtonTitle"
        @confirm="$emit('submit')"
      />
      <div
        class="entityCancel"
        v-html="entityCancelText"
      >
      </div>
    </form>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock, Matomo, translate } from 'CoreHome';
import { Field, Form, SaveButton } from 'CorePluginsAdmin';
import { Report } from '../types';
import { adjustHourToTimezone } from '../utilities';

interface Option {
  key: string;
  value: string;
}

const { ReportPlugin } = window;

export default defineComponent({
  props: {
    report: Object,
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
  },
  emits: ['submit'],
  components: {
    ContentBlock,
    Field,
    SaveButton,
  },
  directives: {
    Form,
  },
  computed: {
    reportsByCategoryByReportTypeInColumns() {
      const reportsByCategoryByReportType = this.reportsByCategoryByReportType as
        Record<string, Record<string, Report[]>>;

      const inColumns = Object.values(reportsByCategoryByReportType).map((reportsByCategory) => {
        const newColumnAfter = Math.floor((reportsByCategory.length + 1) / 2);
        const column1 = reportsByCategory.slice(0, newColumnAfter);
        const column2 = reportsByCategory.slice(newColumnAfter);
        return [column1, column2];
      });

      return Object.fromEntries(
        Object.keys(reportsByCategoryByReportType),
        inColumns,
      );
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
        if (this.timeZoneDifferenceInHours * 2 % 2 != 0) {
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
      const isCreate = this.report.idreport > 0;
      return isCreate ? ReportPlugin.updateReportString : ReportPlugin.createReportString;
    },
  },
});
</script>
