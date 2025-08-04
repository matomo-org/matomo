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
          Setting Name
        </th>
        <th class="label">
          Status
        </th>
        <th class="label">
          Notes
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(item, index) in results" :key="index">
        <td>{{ item.name }}</td>
        <td :class="{
          'status': true,
          'compliant': item.value === 'compliant',
          'non-compliant': item.value === 'non_compliant',
          'unknown': item.value === 'unknown',
        }">
          <span :class="{
              'icon-ok': item.value === 'compliant',
              'icon-close': item.value === 'non_compliant',
              'icon-circle': item.value === 'unknown'
           }"></span>
          {{ item.value }}
        </td>
        <td>{{ item.notes }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ComplianceIndicator } from './compliance.store';

export default defineComponent({
  props: {
    results: {
      type: Array as PropType<ComplianceIndicator[]>,
      required: true,
    },
  },
});
</script>
