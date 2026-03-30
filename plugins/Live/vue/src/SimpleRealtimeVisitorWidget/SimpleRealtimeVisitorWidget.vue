<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="simple-realtime-visitor-widget">
    <div class="simple-realtime-visitor-counter" :title="visitorsTooltip">
      <div>{{ visitorsCountText }}</div>
    </div>

    <br />
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <div class="simple-realtime-elaboration" v-html="$sanitize(messageHtml)"></div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  formatNumber,
  translate,
} from 'CoreHome';

type VisibilityApi = {
  isSupported: () => boolean;
  hidden: () => boolean;
};

const DEFAULT_LAST_MINUTES = 3;
const DEFAULT_REFRESH_AFTER_SECS = 3;
const QUERY_MAX_EXECUTION_TIME_EXCEEDED_TRANSLATION_KEY = 'Live_QueryMaxExecutionTimeExceeded';

export default defineComponent({
  props: {
    lastMinutes: Number,
    refreshAfterXSecs: Number,
  },
  data() {
    return {
      visitorsCount: null as number | null,
      visitsCount: null as number | null,
      actionsCount: null as number | null,
      error: '',
      refreshTimer: null as number | null,
      stopRefreshing: false,
    };
  },
  computed: {
    refreshIntervalMs(): number {
      const seconds = Number(this.refreshAfterXSecs);
      const normalized = Number.isFinite(seconds) && seconds > 0
        ? seconds
        : DEFAULT_REFRESH_AFTER_SECS;
      return normalized * 1000;
    },
    normalizedLastMinutes(): number {
      const minutes = Number(this.lastMinutes);
      return Number.isFinite(minutes) && minutes > 0 ? minutes : DEFAULT_LAST_MINUTES;
    },
    visitorsCountText(): string {
      return this.formatCount(this.visitorsCount);
    },
    visitsCountText(): string {
      return this.formatCount(this.visitsCount);
    },
    actionsCountText(): string {
      return this.formatCount(this.actionsCount);
    },
    visitorsTooltip(): string {
      if (this.visitorsCount === 1) {
        return translate('Live_NbVisitor');
      }

      return translate('Live_NbVisitors', this.visitorsCountText);
    },
    visitsText(): string {
      if (this.visitsCount === 1) {
        return translate('General_OneVisit');
      }

      return translate('General_NVisits', this.visitsCountText);
    },
    actionsText(): string {
      if (this.actionsCount === 1) {
        return translate('General_OneAction');
      }

      return translate('VisitsSummary_NbActionsDescription', this.actionsCountText);
    },
    minutesText(): string {
      if (this.normalizedLastMinutes === 1) {
        return translate('Intl_OneMinute');
      }

      return translate('Intl_NMinutes', this.normalizedLastMinutes);
    },
    messageHtml(): string {
      const visitsMessage = `<span class="simple-realtime-metric" data-metric="visits">${this.visitsText}</span>`;
      const actionsMessage = `<span class="simple-realtime-metric" data-metric="actions">${this.actionsText}</span>`;
      const minutesMessage = `<span class="simple-realtime-metric" data-metric="minutes">${this.minutesText}</span>`;

      return translate(
        'Live_SimpleRealTimeWidget_Message',
        visitsMessage,
        actionsMessage,
        minutesMessage,
      );
    },
  },
  mounted() {
    this.update();
  },
  beforeUnmount() {
    this.clearScheduledUpdate();
  },
  methods: {
    clearScheduledUpdate() {
      if (this.refreshTimer) {
        window.clearTimeout(this.refreshTimer);
        this.refreshTimer = null;
      }
    },
    scheduleUpdate() {
      this.clearScheduledUpdate();
      this.refreshTimer = window.setTimeout(() => {
        this.update();
      }, this.refreshIntervalMs);
    },
    parseCount(value: unknown): number | null {
      const parsed = Number(value);
      if (!Number.isFinite(parsed) || parsed < 0) {
        return null;
      }

      return parsed;
    },
    formatCount(value: number | null): string {
      if (value === null) {
        return '-';
      }

      return formatNumber(value, 0, 0);
    },
    resetCounters() {
      this.visitorsCount = null;
      this.visitsCount = null;
      this.actionsCount = null;
    },
    isTabHidden(): boolean {
      const visibility = (window as Window & { Visibility?: VisibilityApi }).Visibility;
      return Boolean(
        visibility
        && visibility.isSupported
        && visibility.isSupported()
        && visibility.hidden(),
      );
    },
    getErrorMessage(error: unknown): string {
      if (typeof error === 'string') {
        return error;
      }

      if (error && typeof error === 'object' && 'message' in error) {
        const { message } = error as { message?: unknown };
        if (typeof message === 'string') {
          return message;
        }
      }

      return '';
    },
    isMaxExecutionTimeError(error: unknown): boolean {
      const message = this.getErrorMessage(error);
      const translatedMarker = translate(QUERY_MAX_EXECUTION_TIME_EXCEEDED_TRANSLATION_KEY);

      return message.startsWith(translatedMarker)
        || message.includes(QUERY_MAX_EXECUTION_TIME_EXCEEDED_TRANSLATION_KEY);
    },
    update() {
      const element = this.$el as HTMLElement | undefined;
      if (!element || !element.isConnected) {
        return;
      }

      if (this.isTabHidden()) {
        this.scheduleUpdate();
        return;
      }

      AjaxHelper.fetch(
        {
          module: 'API',
          method: 'Live.getCounters',
          showColumns: 'visits,visitors,actions',
          lastMinutes: this.normalizedLastMinutes,
        },
        {
          format: 'json',
        },
      ).then((response) => {
        const counters = Array.isArray(response) && response.length ? response[0] : {};
        this.visitorsCount = this.parseCount(counters.visitors);
        this.visitsCount = this.parseCount(counters.visits);
        this.actionsCount = this.parseCount(counters.actions);
        this.error = '';
        this.stopRefreshing = false;
      }).catch((error) => {
        this.error = this.getErrorMessage(error);
        this.stopRefreshing = this.isMaxExecutionTimeError(error);
        if (this.stopRefreshing) {
          this.resetCounters();
        }
      }).finally(() => {
        if (element.isConnected && !this.stopRefreshing) {
          this.scheduleUpdate();
        }
      });
    },
  },
});
</script>
