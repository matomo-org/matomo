<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <img
    class="sparklineImg"
    :class="{ 'sparklineImg--loading': !hasLoaded }"
    loading="lazy"
    alt=""
    :src="sparklineUrl"
    :width="width"
    :height="height"
    :style="sizeStyle"
    @load="hasLoaded = true"
    @error="hasLoaded = true"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Matomo from '../Matomo/Matomo';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import RangePeriod from '../Periods/Range';
import { format } from '../Periods';

export default defineComponent({
  name: 'Sparkline',
  props: {
    seriesIndices: Array,
    params: [Object, String],
    width: Number,
    height: Number,
  },
  // So a parent can show its own placeholder while an image is loading. Only changes are emitted,
  // not the initial `true` — a parent that cares starts out in its loading state anyway.
  emits: ['loadingChange'],
  data() {
    return {
      isWidget: false,
      themeMode: Matomo.getThemeMode(),
      // False while an image is on its way, so a parent can show a placeholder instead.
      hasLoaded: false,
    };
  },
  mounted() {
    this.isWidget = !!this.$el.closest('[widgetId]');
    window.addEventListener('themeModeChange', this.onThemeModeChange);
  },
  beforeUnmount() {
    window.removeEventListener('themeModeChange', this.onThemeModeChange);
  },
  watch: {
    // A new url means a new request, so go back to loading until it arrives. The browser keeps
    // showing the current image until then, so nothing goes blank.
    sparklineUrl() {
      this.hasLoaded = false;
    },
    hasLoaded(value: boolean) {
      this.$emit('loadingChange', !value);
    },
  },
  computed: {
    // Draw the image at the size the props ask for. The width/height attributes alone can't do
    // this, because any CSS rule beats them — including the 100x25 default in Sparkline.less.
    sizeStyle() {
      const { width, height } = this;

      return typeof width === 'number' && typeof height === 'number'
        ? { width: `${width}px`, height: `${height}px` }
        : undefined;
    },
    sparklineUrl() {
      const { seriesIndices, params, themeMode } = this;

      const sparklineColors = Matomo.getSparklineColors();

      if (seriesIndices) {
        sparklineColors.lineColor = sparklineColors.lineColor.filter(
          (c, index) => seriesIndices.indexOf(index) !== -1,
        );
      }

      const colors = JSON.stringify(sparklineColors);

      // Ask for twice the displayed size, so the image stays sharp on hi-DPI screens.
      const sizeParams = {
        ...(typeof this.width === 'number' ? { width: this.width * 2 } : {}),
        ...(typeof this.height === 'number' ? { height: this.height * 2 } : {}),
      };

      const defaultParams = {
        forceView: '1',
        viewDataTable: 'sparkline',
        widget: this.isWidget ? '1' : '0',
        showtitle: '1',
        colors,
        random: Date.now(),
        date: this.defaultDate,
        ...sizeParams,
        // mixinDefaultGetParams() will use the raw, encoded value from the URL (legacy behavior),
        // which means MatomoUrl.stringify() will end up double encoding it if we don't set it
        // ourselves here.
        segment: MatomoUrl.parsed.value.segment as string,
      };

      const givenParams = typeof params === 'object'
        ? params as QueryParameters
        : MatomoUrl.parse((params as string).substring((params as string).indexOf('?') + 1));

      const helper = new AjaxHelper();
      const urlParams = helper.mixinDefaultGetParams({ ...defaultParams, ...givenParams });

      // Append the token_auth to the URL if it was set (eg. embed dashboard)
      const token_auth = MatomoUrl.parsed.value.token_auth as string;
      if (token_auth && token_auth.length && Matomo.shouldPropagateTokenAuth) {
        urlParams.token_auth = token_auth;
      }

      urlParams.themeMode = themeMode;
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
  methods: {
    onThemeModeChange() {
      this.themeMode = Matomo.getThemeMode();
    },
  },
});
</script>
