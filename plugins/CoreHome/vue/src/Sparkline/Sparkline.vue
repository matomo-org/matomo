<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <img :src="sparklineUrl" />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Matomo from '../Matomo/Matomo';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import RangePeriod from '../Periods/Range';
import { format } from '../Periods';

export default defineComponent({
  props: {
    seriesIndices: Array,
    params: Object,
  },
  data() {
    return {
      isWidget: false,
    };
  },
  mounted() {
    this.isWidget = !!this.$el.closest('[widgetId]');
  },
  computed: {
    sparklineUrl() {
      const { seriesIndices, params } = this;

      const sparklineColors = Matomo.getSparklineColors();

      if (seriesIndices) {
        sparklineColors.lineColor = sparklineColors.lineColor.filter(
          (c, index) => seriesIndices.indexOf(index) !== -1,
        );
      }

      const colors = JSON.stringify(sparklineColors);

      const defaultParams = {
        forceView: '1',
        viewDataTable: 'sparkline',
        widget: this.isWidget ? '1' : '0',
        showtitle: '1',
        colors,
        random: Date.now(),
        date: this.defaultDate,
        // mixinDefaultGetParams() will use the raw, encoded value from the URL (legacy behavior),
        // which means MatomoUrl.stringify() will end up double encoding it if we don't set it
        // ourselves here.
        segment: MatomoUrl.parsed.value.segment as string,
      };

      const helper = new AjaxHelper();
      const urlParams = helper.mixinDefaultGetParams({ ...defaultParams, ...params });

      // Append the token_auth to the URL if it was set (eg. embed dashboard)
      const token_auth = MatomoUrl.parsed.value.token_auth as string;
      if (token_auth && token_auth.length && Matomo.shouldPropagateTokenAuth) {
        urlParams.token_auth = token_auth;
      }

      return `?${MatomoUrl.stringify(urlParams)}`;
    },
    defaultDate() {
      if (Matomo.period === 'range') {
        return `${Matomo.startDateString},${Matomo.endDateString}`;
      }

      const dateRange = RangePeriod.getLastNRange(
        Matomo.period!,
        30,
        Matomo.currentDateString!,
      ).getDateRange();

      const piwikMinDate = new Date(Matomo.minDateYear, Matomo.minDateMonth - 1, Matomo.minDateDay);
      if (dateRange[0] < piwikMinDate) {
        dateRange[0] = piwikMinDate;
      }

      const startDateStr = format(dateRange[0]);
      const endDateStr = format(dateRange[1]);

      return `${startDateStr},${endDateStr}`;
    },
  },
});
</script>
