<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="report">
    <div>
      <Field
        uicontrol="checkbox"
        name="report_email_me"
        :introduction="translate('ScheduledReports_SendReportTo')"
        v-show="report.type === 'email'"
        :model-value="report.emailMe"
        @update:model-value="$emit('change', 'emailMe', $event)"
        :title="`${translate('ScheduledReports_SentToMe')} (${currentUserEmail})`"
      />
    </div>

    <div>
      <Field
        uicontrol="textarea"
        var-type="array"
        v-show="report.type === 'email'"
        :model-value="report.additionalEmails"
        @update:model-value="$emit('change', 'additionalEmails', $event)"
        :title="translate('ScheduledReports_AlsoSendReportToTheseEmails')">
      </Field>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Field } from 'CorePluginsAdmin';

export default defineComponent({
  props: {
    report: {
      type: Object,
      required: true,
    },
    reportType: {
      type: String,
      required: true,
    },
    defaultDisplayFormat: {
      type: Number,
      required: true,
    },
    defaultEmailMe: {
      type: Boolean,
      required: true,
    },
    defaultEvolutionGraph: {
      type: Boolean,
      required: true,
    },
    currentUserEmail: {
      type: String,
      required: true,
    },
  },
  emits: ['change'],
  components: {
    Field,
  },
  setup(props) {
    const {
      resetReportParametersFunctions,
      updateReportParametersFunctions,
      getReportParametersFunctions,
    } = window;

    if (!resetReportParametersFunctions[props.reportType]) {
      resetReportParametersFunctions[props.reportType] = (theReport) => {
        theReport.displayFormat = props.defaultDisplayFormat;
        theReport.emailMe = props.defaultEmailMe;
        theReport.evolutionGraph = props.defaultEvolutionGraph;
        theReport.additionalEmails = [];
      };
    }

    if (!updateReportParametersFunctions[props.reportType]) {
      updateReportParametersFunctions[props.reportType] = (theReport) => {
        if (!theReport?.parameters) {
          return;
        }

        ['displayFormat', 'emailMe', 'evolutionGraph', 'additionalEmails'].forEach((field) => {
          if (field in theReport.parameters) {
            theReport[field] = theReport.parameters[field];
          }
        });
      };
    }

    if (!getReportParametersFunctions[props.reportType]) {
      getReportParametersFunctions[props.reportType] = (theReport) => ({
        displayFormat: theReport.displayFormat,
        emailMe: theReport.emailMe,
        evolutionGraph: theReport.evolutionGraph,
        additionalEmails: (theReport.additionalEmails || []),
      });
    }
  },
});
</script>
