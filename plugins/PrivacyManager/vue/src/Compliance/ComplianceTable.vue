<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <table class="card-table dataTable compliance">
    <thead>
      <tr>
        <th class="label">
          {{ translate('PrivacyManager_ComplianceTableSettingName') }}
        </th>
        <th class="label">
          {{ translate('PrivacyManager_ComplianceTableSettingStatus') }}
        </th>
        <th class="label">
          {{ translate('PrivacyManager_ComplianceTableSettingNotes') }}
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(item, index) in results" :key="index">
        <td>{{ item.name }}</td>
        <td :class="['status', getStatusClass(item.value)]">
          <span :class="['icon', getIconClass(item.value)]"></span>
          {{ translate(getStatusText(item.value)) }}
        </td>
        <td>{{ item.notes }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ComplianceRequirement } from './Compliance.store';

const statusClassMap: Record<string, string> = {
  compliant: 'compliant',
  non_compliant: 'non-compliant',
  unknown: 'unknown',
};

const iconClassMap: Record<string, string> = {
  compliant: 'icon-ok',
  non_compliant: 'icon-close',
  unknown: 'icon-circle',
};

const statusTextMap: Record<string, string> = {
  compliant: 'PrivacyManager_ComplianceCompliant',
  non_compliant: 'PrivacyManager_ComplianceNonCompliant',
  unknown: 'PrivacyManager_ComplianceComplianceUnknown',
};

export default defineComponent({
  props: {
    results: {
      type: Array as PropType<ComplianceRequirement[]>,
      required: true,
    },
  },
  methods: {
    getStatusClass(value: string) {
      return statusClassMap[value] || statusClassMap.unknown;
    },
    getIconClass(value: string) {
      return iconClassMap[value] || iconClassMap.unknown;
    },
    getStatusText(value: string) {
      return statusTextMap[value] || statusTextMap.unknown;
    },
  },
});
</script>
