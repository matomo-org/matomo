<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <table class="dataTable" cellspacing="0">
      <thead>
      <tr>
        <th id="label" class="sortable label first" style="cursor: auto;">
          <div class="thDIV">{{ translate('General_Date') }}</div>
        </th>
        <th class="sortable" style="cursor: auto;">
          <div class="thDIV">{{ translate('General_ColumnNbVisits') }}</div>
        </th>
        <th class="sortable" style="cursor: auto;">
          <div class="thDIV">{{ translate('General_Actions') }}</div>
        </th>
      </tr>
      </thead>
      <tbody>
      <tr class="">
        <td class="label column">{{ translate('Live_LastHours', 24) }}</td>
        <td class="column" :title="countErrorToday">
          {{ visitorsCountToday || 0 }}
        </td>
        <td class="column" :title="countErrorToday">
          {{ pisToday || 0 }}
        </td>
      </tr>
      <tr class="">
        <td class="label column">{{ translate('Live_LastMinutes', 30) }}</td>
        <td class="column" :title="countErrorHalfHour">{{ visitorsCountHalfHour || 0 }}</td>
        <td class="column" :title="countErrorHalfHour">{{ pisHalfhour || 0 }}</td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    countErrorToday: Number,
    visitorsCountToday: Number,
    pisToday: Number,
    countErrorHalfHour: Number,
    visitorsCountHalfHour: Number,
    pisHalfhour: Number,
  },
});
</script>
