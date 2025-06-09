<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('Marketplace_OverviewPluginSubscriptions')"
    class="subscriptionOverview"
  >
    <div v-if="hasLicenseKey">
      <p>
        {{ translate('Marketplace_PluginSubscriptionsList') }}
        <a target="_blank" rel="noreferrer noopener" :href="loginUrl" v-if="loginUrl">
          {{ translate('Marketplace_OverviewPluginSubscriptionsAllDetails') }}
        </a>
        <br/>
        {{ translate('Marketplace_OverviewPluginSubscriptionsMissingInfo') }}
        <br />

        {{ translate('Marketplace_NoValidSubscriptionNoUpdates') }}
        <span v-html="$sanitize(translate(
          'Marketplace_CurrentNumPiwikUsers',
          `<strong>${numUsers}</strong>`,
        ))"></span>
      </p>

      <br />

      <table v-content-table>
        <thead>
        <tr>
          <th>{{ translate('General_Name') }}</th>
          <th>{{ translate('Marketplace_SubscriptionType') }}</th>
          <th>{{ translate('CorePluginsAdmin_Status') }}</th>
          <th>{{ translate('Marketplace_SubscriptionStartDate') }}</th>
          <th>{{ translate('Marketplace_SubscriptionEndDate') }}</th>
          <th>{{ translate('Marketplace_SubscriptionNextPaymentDate') }}</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(subscription, index) in (subscriptions || [])" :key="index">
          <td class="subscriptionName">
            <a
              v-if="subscription.plugin.htmlUrl"
              :href="subscription.plugin.htmlUrl"
              rel="noreferrer noopener"
              target="_blank"
            >
              {{ subscription.plugin.displayName }}
            </a>
            <span v-else>{{ subscription.plugin.displayName }}</span>
          </td>
          <td class="subscriptionType">{{ subscription.productType }}</td>
          <td
            class="subscriptionStatus"
            :title="getSubscriptionStatusTitle(subscription)"
          >
            <span class="icon-error" v-if="!subscription.isValid"></span>
            <span class="icon-warning" v-else-if="subscription.isExpiredSoon"></span>
            <span class="icon-error"
                  v-else-if="subscription.status !== '' && subscription.status !== 'Active'">
            </span>
            <span class="icon-ok" v-else></span>

            {{ subscription.status }}

            <span
              v-if="subscription.isExceeded"
              class="errorMessage"
              :title="translate('Marketplace_LicenseExceededPossibleCause')"
            >
              <span class="icon-error"></span> {{ translate('Marketplace_Exceeded') }}
            </span>
          </td>
          <td>{{ subscription.start }}</td>
          <td>
            {{ subscription.isValid && subscription.nextPayment
              ? translate('Marketplace_LicenseRenewsNextPaymentDate')
              : subscription.end }}
          </td>
          <td>{{ subscription.nextPayment }}</td>
        </tr>
        <tr v-if="!subscriptions.length">
          <td colspan="6">{{ translate('Marketplace_NoSubscriptionsFound') }}</td>
        </tr>
        </tbody>
      </table>

      <div class="tableActionBar">
        <a :href="marketplaceOverviewLink" class="">
          <span class="icon-table"></span>
          {{ translate('Marketplace_BrowseMarketplace') }}
        </a>
      </div>
    </div>
    <div v-else>
      <p v-html="$sanitize(missingLicenseText)"></p>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  ContentTable,
  MatomoUrl,
  translate,
} from 'CoreHome';

interface Subscription {
  isValid: boolean;
  isExpiredSoon: boolean;
}

export default defineComponent({
  props: {
    loginUrl: {
      type: String,
      required: true,
    },
    numUsers: {
      type: Number,
      required: true,
    },
    hasLicenseKey: Boolean,
    subscriptions: {
      type: Array,
      required: true,
    },
  },
  components: {
    ContentBlock,
  },
  directives: {
    ContentTable,
  },
  methods: {
    getSubscriptionStatusTitle(sub: Subscription) {
      if (!sub.isValid) {
        return translate('Marketplace_SubscriptionInvalid');
      }

      if (sub.isExpiredSoon) {
        return translate('Marketplace_SubscriptionExpiresSoon');
      }

      return undefined;
    },
  },
  computed: {
    marketplaceOverviewLink() {
      return `?${MatomoUrl.stringify({
        module: 'Marketplace',
        action: 'overview',
      })}`;
    },
    licenseKeyLink() {
      return `?${MatomoUrl.stringify({
        module: 'Marketplace',
        action: 'manageLicenseKey',
      })}`;
    },
    missingLicenseText() {
      return translate(
        'Marketplace_OverviewPluginSubscriptionsMissingLicenseMessage',
        `<a href="${this.licenseKeyLink}">`,
        '</a>',
        `<a href="${this.marketplaceOverviewLink}">`,
        '</a>',
      );
    },
  },
});
</script>
