<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <h3>Previous raw data anonymizations</h3>
    <table v-content-table>
      <thead>
      <tr>
        <th>Requester</th>
        <th>Affected ID Sites</th>
        <th>Affected date</th>
        <th>Anonymize</th>
        <th>Visit Columns</th>
        <th>Link Visit Action Columns</th>
        <th>Status</th>
      </tr></thead>
      <tbody>
      <tr v-for="(entry, index) in anonymizations" :key="index">
        <td>{{ entry.requester }}</td>
        <td>{{ entry.sites.join(', ') }}</td>
        <td>{{ entry.date_start }} - {{ entry.date_end }}</td>
        <td>
          <span v-if="entry.anonymize_ip">IP address<br /></span>
          <span v-if="entry.anonymize_location">Location<br /></span>
          <span v-if="entry.anonymize_userid">User ID</span>
          <span
            v-if="!entry.anonymize_ip && !entry.anonymize_location && !entry.anonymize_userid"
          >-</span>
        </td>
        <td>{{ entry.unset_visit_columns.join(', ') }}</td>
        <td>{{ entry.unset_link_visit_action_columns.join(', ') }}</td>
        <td>
          <span v-if="!entry.job_start_date">
            <span
              class="icon-info"
              style="cursor: help;"
              :title="`Scheduled date: ${entry.scheduled_date || ''}.`"
            ></span>
            Scheduled
          </span>

          <span v-else-if="entry.job_start_date && !entry.job_finish_date">
            <span
              class="icon-info"
              style="cursor: help;"
              :title="`Scheduled date: ${entry.scheduled_date || ''}. Job Start Date:` +
                ` ${entry.job_start_date}. Current Output: ${entry.output}`"
            ></span>
            In progress
          </span>
          <span v-else>
            <span
              class="icon-info"
              style="cursor: help;"
              :title="`Scheduled date: ${entry.scheduled_date || ''}. Job Start Date:` +
                ` ${entry.job_start_date}. Job Finish Date: ${entry.job_finish_date}. ` +
                `Output: ${entry.output}`"
            ></span>
            Done
          </span>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentTable } from 'CoreHome';

export default defineComponent({
  props: {
    anonymizations: {
      type: Array,
      required: true,
    },
  },
  directives: {
    ContentTable,
  },
});
</script>
