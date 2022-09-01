<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="report && report.type === 'mobile'">
    <SelectPhoneNumbers
      :phone-numbers="phoneNumbers"
      :with-introduction="true"
      :model-value="report.phoneNumbers"
      @update:model-value="$emit('change', 'phoneNumbers', $event)"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Report } from 'ScheduledReports';
import SelectPhoneNumbers from '../SelectPhoneNumbers/SelectPhoneNumbers.vue';

const REPORT_TYPE = 'mobile';

export default defineComponent({
  props: {
    report: {
      type: Object,
      required: true,
    },
    phoneNumbers: {
      type: [Array, Object],
      required: true,
    },
  },
  components: {
    SelectPhoneNumbers,
  },
  emits: ['change'],
  created() {
    const {
      resetReportParametersFunctions,
      updateReportParametersFunctions,
      getReportParametersFunctions,
    } = window;

    if (!resetReportParametersFunctions[REPORT_TYPE]) {
      resetReportParametersFunctions[REPORT_TYPE] = (report: Report) => {
        report.phoneNumbers = [];
        report.formatmobile = 'sms';
      };
    }

    if (!updateReportParametersFunctions[REPORT_TYPE]) {
      updateReportParametersFunctions[REPORT_TYPE] = (report: Report) => {
        if (!report?.parameters) {
          return;
        }

        if (report.parameters && report.parameters.phoneNumbers) {
          report.phoneNumbers = report.parameters.phoneNumbers;
        }
        report.formatmobile = 'sms';
      };
    }

    if (!getReportParametersFunctions[REPORT_TYPE]) {
      getReportParametersFunctions[REPORT_TYPE] = (report: Report) => {
        // returning [''] when no phone numbers are selected avoids the "please provide a value
        // for 'parameters'" error message
        const phoneNumbers: string[]|undefined = report.phoneNumbers as string[]|undefined;
        return {
          phoneNumbers: phoneNumbers || [''],
        };
      };
    }
  },
});
</script>
