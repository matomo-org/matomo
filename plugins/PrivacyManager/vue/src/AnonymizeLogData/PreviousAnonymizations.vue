<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <h3>{{ translate('PrivacyManager_PreviousRawDataAnonymizations') }}</h3>
    <table v-content-table>
      <thead>
      <tr>
        <th>{{ translate('PrivacyManager_Requester') }}</th>
        <th>{{ translate('PrivacyManager_AffectedIDSites') }}</th>
        <th>{{ translate('PrivacyManager_AffectedDate') }}</th>
        <th>{{ translate('PrivacyManager_Anonymize') }}</th>
        <th>{{ translate('PrivacyManager_VisitColumns') }}</th>
        <th>{{ translate('PrivacyManager_LinkVisitActionColumns') }}</th>
        <th>{{ translate('CorePluginsAdmin_Status') }}</th>
      </tr></thead>
      <tbody>
      <tr v-for="(entry, index) in anonymizations" :key="index">
        <td>{{ entry.requester }}</td>
        <td>{{ entry.sites.join(', ') }}</td>
        <td>{{ entry.date_start }} - {{ entry.date_end }}</td>
        <td>
          <span v-if="entry.anonymize_ip">{{ translate('PrivacyManager_IPAddress') }}<br /></span>
          <span v-if="entry.anonymize_location">{{ translate('Overlay_Location') }}<br /></span>
          <span v-if="entry.anonymize_userid">{{ translate('General_UserId') }}</span>
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
              :title="`${ translate('PrivacyManager_ScheduledDate', entry.scheduled_date || '') }`"
            ></span>
            {{ translate('PrivacyManager_Scheduled') }}
          </span>

          <span v-else-if="entry.job_start_date && !entry.job_finish_date">
            <span
              class="icon-info"
              style="cursor: help;"
              :title="`${ translate('PrivacyManager_ScheduledDate', entry.scheduled_date || '') }.
${ translate('PrivacyManager_JobStartDate', entry.job_start_date) }.
${ translate('PrivacyManager_CurrentOutput', entry.output) }`"
            ></span>
            {{ translate('PrivacyManager_InProgress') }}
          </span>
          <span v-else>
            <span
              class="icon-info"
              style="cursor: help;"
              :title="`${ translate('PrivacyManager_ScheduledDate', entry.scheduled_date || '') }.
${ translate('PrivacyManager_JobStartDate', entry.job_start_date) }.
${ translate('PrivacyManager_JobFinishDate', entry.job_finish_date) }.
${ translate('PrivacyManager_Output', entry.output) }`"
            ></span>
            {{ translate('General_Done') }}
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
