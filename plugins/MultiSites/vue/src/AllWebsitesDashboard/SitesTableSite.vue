<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <tr :class="{
    sitesTableGroup: !!site.isGroup,
    sitesTableGroupSite: !site.isGroup && !!site.group,
    sitesTableSite: !site.isGroup && !site.group,
  }">
    <td class="label">
      <template v-if="!site.isGroup">
        <a
            rel="noreferrer noopener"
            target="_blank"
            :href="site.main_url"
            :title="translate('General_GoTo', site.main_url)"
        >
          <span class="icon icon-outlink" /></a>
        <a
            title="View reports"
            class="value"
            :href="dashboardUrl"
        >
          {{ siteLabel }}
        </a>
      </template>
      <template v-else>
        <span class="value">{{ siteLabel }}</span>
      </template>
    </td>

    <td><span class="value">{{ formatNumber(site.nb_visits) }}</span></td>
    <td><span class="value">{{ formatNumber(site.nb_pageviews) }}</span></td>
    <td><span class="value">{{ formatNumber(site.hits) }}</span></td>
    <td v-if="displayRevenue">
      <span class="value">{{ formatCurrency(site.revenue, site.currencySymbol || '') }}</span>
    </td>

    <td :colspan="displaySparkline ? 1 : 2">
      <template v-if="!site.isGroup && sparklineMetric in site">
        <img :src="evolutionIconSrc" alt="" />
        <span :class="evolutionTrendClass">
          {{ calculateAndFormatEvolution(
                site[sparklineMetric], site[`previous_${sparklineMetric}`] * site.ratio, true
             ) }}
        </span>
      </template>
    </td>

    <td v-if="displaySparkline"
        class="sitesTableSparkline"
    >
      <a
          rel="noreferrer noopener"
          target="_blank"
          :href="dashboardUrl"
          :title="translate('General_GoTo', translate('Dashboard_DashboardOf', siteLabel))"
          v-if="!site.isGroup"
      >
        <img
            alt=""
            width="100"
            height="25"
            :src="evolutionSparklineSrc"
        />
      </a>
    </td>
  </tr>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  Matomo,
  MatomoUrl,
  Range,
  format,
} from 'CoreHome';

import { DashboardSiteData, DashboardMetrics, EvolutionTrend } from '../types';

export default defineComponent({
  props: {
    displayRevenue: {
      type: Boolean,
      required: true,
    },
    evolutionMetric: {
      type: String,
      required: true,
    },
    modelValue: {
      type: Object,
      required: true,
    },
    sparklineMetric: String,
    displaySparkline: Boolean,
  },
  computed: {
    dashboardUrl() {
      const dashboardParams = MatomoUrl.stringify({
        module: 'CoreHome',
        action: 'index',
        date: Matomo.currentDateString,
        period: Matomo.period,
        idSite: this.site.idsite,
      });

      return `?${dashboardParams}${this.tokenParam}`;
    },
    evolutionIconSrc() {
      if (this.evolutionTrend === 1) {
        return 'plugins/MultiSites/images/arrow_up.png';
      }

      if (this.evolutionTrend === -1) {
        return 'plugins/MultiSites/images/arrow_down.png';
      }

      return 'plugins/MultiSites/images/stop.png';
    },
    evolutionSparklineSrc() {
      let sparklineDate = Matomo.currentDateString as string;

      if (Matomo.period as string !== 'range') {
        const { startDate, endDate } = Range.getLastNRange(
          Matomo.period as string,
          '30',
          Matomo.currentDateString as string,
        );

        sparklineDate = `${format(startDate)},${format(endDate)}`;
      }

      const sparklineParams = MatomoUrl.stringify({
        module: 'MultiSites',
        action: 'getEvolutionGraph',
        date: sparklineDate,
        period: Matomo.period as string,
        idSite: this.site.idsite,
        columns: this.sparklineMetric,
        evolutionBy: this.sparklineMetric,
        colors: JSON.stringify(Matomo.getSparklineColors()),
        viewDataTable: 'sparkline',
      });

      return `?${sparklineParams}${this.tokenParam}`;
    },
    evolutionTrend(): EvolutionTrend {
      const property = `${this.evolutionMetric}_trend` as keyof DashboardMetrics;

      return this.site[property] as EvolutionTrend;
    },
    evolutionTrendClass(): string {
      if (this.evolutionTrend === 1) {
        return 'evolutionTrendPositive';
      }

      if (this.evolutionTrend === -1) {
        return 'evolutionTrendNegative';
      }

      return '';
    },
    site(): DashboardSiteData {
      return this.modelValue as DashboardSiteData;
    },
    siteLabel() {
      return Matomo.helper.htmlDecode(this.site.label);
    },
    tokenParam() {
      const token_auth = MatomoUrl.urlParsed.value.token_auth as string;

      return token_auth ? `&token_auth=${token_auth}` : '';
    },
  },
});
</script>
