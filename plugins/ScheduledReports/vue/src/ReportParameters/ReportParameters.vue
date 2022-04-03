<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="report">
    <Field
      uicontrol="checkbox"
      name="report_email_me"
      :introduction="translate('ScheduledReports_SendReportTo')"
      v-show="report.type === 'email'"
      v-model="report.emailMe"
      :title="`${translate('ScheduledReports_SentToMe')} (${currentUserEmail})`"
    />
  </div>

  <div v-if="report">
    <Field
      uicontrol="textarea"
      var-type="array"
      v-show="report.type === 'email'"
      v-model="report.additionalEmails"
      :title="translate('ScheduledReports_AlsoSendReportToTheseEmails')">
    </Field>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import { Field } from 'CorePluginsAdmin';
import { Report } from '../types';

export default defineComponent({
  props: {
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
  components: {
    Field,
  },
  setup(props) {
    const report = ref<Report|null>(null);

    const {
      resetReportParametersFunctions,
      updateReportParametersFunctions,
      getReportParametersFunctions,
    } = window;

    if (!resetReportParametersFunctions[props.reportType]) {
      resetReportParametersFunctions[props.reportType] = (theReport) => {
        report.value = theReport;

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

        report.value = theReport;

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

    return {
      report,
    };
  },
});
</script>
