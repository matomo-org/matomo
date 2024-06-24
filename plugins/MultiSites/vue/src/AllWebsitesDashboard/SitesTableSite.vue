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

    <td><span class="value">{{ site.nb_visits }}</span></td>
    <td><span class="value">{{ site.nb_pageviews }}</span></td>
    <td><span class="value">{{ site.nb_hits }}</span></td>
    <td v-if="displayRevenue"><span class="value">{{ site.revenue }}</span></td>

    <td :colspan="displaySparkline ? 1 : 2">
      <template v-if="!site.isGroup && !!site[evolutionMetric]">
        <img :src="evolutionIconSrc" alt="" />
        <span :style="{color: evolutionColor}">{{ site[evolutionMetric] }}</span>
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
import { Matomo, MatomoUrl } from 'CoreHome';

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
    sparklineDate: String,
    sparklineMetric: String,
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
    displaySparkline() {
      return !this.site.isGroup && this.sparklineDate && this.sparklineMetric;
    },
    evolutionColor() {
      if (this.evolutionTrend === 1) {
        return 'green';
      }

      if (this.evolutionTrend === -1) {
        return 'red';
      }

      return 'inherit';
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
      const sparklineParams = MatomoUrl.stringify({
        module: 'MultiSites',
        action: 'getEvolutionGraph',
        date: this.sparklineDate,
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
