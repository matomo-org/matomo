<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('Marketplace_ManageRequests')"
    class="manageRequests"
  >
    <div v-if="requests.length === 0" class="manageRequests__empty">
      <h3 class="manageRequests__emptyTitle">{{ translate('Marketplace_NoPluginRequestsYet') }}</h3>
      <p>{{ translate('Marketplace_NoPluginRequestsYetHelp') }}</p>
      <a class="btn" :href="marketplaceUrl">{{ translate('Marketplace_BrowseMarketplace') }}</a>
    </div>

    <template v-else>
      <p class="manageRequests__intro">{{ translate('Marketplace_ManageRequestsIntro') }}</p>

      <table v-content-table class="manageRequests__table">
        <thead>
        <tr>
          <th>{{ translate('General_Plugin') }}</th>
          <th>{{ translate('Marketplace_RequestedOn') }}</th>
          <th>{{ translate('Marketplace_YourNote') }}</th>
          <th>{{ translate('CorePluginsAdmin_Status') }}</th>
          <th>{{ translate('Marketplace_SuperUserNote') }}</th>
        </tr>
        </thead>
        <tbody>
        <tr
          v-for="request in requests"
          :key="request.pluginName"
          class="manageRequests__row"
          :data-plugin="request.pluginName"
        >
          <td class="manageRequests__plugin">{{ request.displayName }}</td>
          <td class="manageRequests__requestedOn">{{ request.requestedAtPretty }}</td>
          <td>
            <span
              class="manageRequests__note"
              :title="request.message || undefined"
            >{{ request.message || '-' }}</span>
          </td>
          <td>
            <span
              class="manageRequests__status"
              :class="`manageRequests__status--${request.status}`"
            >{{ statusLabel(request.status) }}</span>
          </td>
          <td>
            <span
              class="manageRequests__note manageRequests__decisionNote"
              :title="request.decisionNote || undefined"
            >{{ request.decisionNote || '-' }}</span>
          </td>
        </tr>
        </tbody>
      </table>
    </template>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  ContentBlock,
  ContentTable,
  MatomoUrl,
  translate,
} from 'CoreHome';

export interface PluginRequestOfUser {
  pluginName: string;
  displayName: string;
  requestType: 'trial' | 'install';
  requestedAt: string;
  requestedAtPretty: string;
  message: string | null;
  status: 'pending' | 'approved' | 'declined';
  decisionNote: string | null; // only for a declined request
}

const STATUS_LABELS: Record<string, string> = {
  pending: 'Marketplace_RequestStatusPending',
  approved: 'Marketplace_RequestStatusApproved',
  declined: 'Marketplace_RequestStatusDeclined',
};

export default defineComponent({
  props: {
    /** The current user's requests, newest first, from Marketplace.getMyPluginRequests. */
    requests: {
      type: Array as PropType<PluginRequestOfUser[]>,
      required: true,
    },
  },
  components: {
    ContentBlock,
  },
  directives: {
    ContentTable,
  },
  computed: {
    marketplaceUrl(): string {
      const { idSite, period, date } = MatomoUrl.urlParsed.value;

      return `?${MatomoUrl.stringify({
        module: 'Marketplace',
        action: 'overview',
        idSite,
        period,
        date,
      })}`;
    },
  },
  methods: {
    statusLabel(status: string): string {
      return STATUS_LABELS[status] ? translate(STATUS_LABELS[status]) : status;
    },
  },
});
</script>
