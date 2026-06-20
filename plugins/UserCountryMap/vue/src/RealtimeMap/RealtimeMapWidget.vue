<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="card">
    <div
      ref="mapRoot"
      class="RealTimeMap card-content"
      style="position:relative; overflow:hidden;"
      :data-config="configJson"
      :data-standalone="isStandalone ? 1 : 0"
      tabindex="0"
    >
      <div class="RealTimeMap_container">
        <div
          class="RealTimeMap_map"
          style="overflow:hidden;"
        />
        <div class="realTimeMap_overlay">
          <span
            v-if="showFooterMessage"
            class="showing_visits_of"
            style="display:none;"
          >
            {{ translate('UserCountryMap_ShowingVisits') }}
            <span
              class="realTimeMap_timeSpan"
              style="font-weight:bold;"
            />
          </span>
          <span
            class="no_data"
            style="display:none;"
          >
            {{ translate(
              'CoreHome_ThereIsNoDataForThisReport'
            ) }}
          </span>
          <span class="loading_data">
            {{ translate('General_LoadingData') }}...
          </span>
          <img
            src="plugins/UserCountryMap/images/realtimemap-loading.gif"
            style="vertical-align:baseline;position:relative;left:-2px;"
          >
        </div>
        <div
          v-if="showDateTime"
          class="realTimeMap_overlay realTimeMap_datetime"
        />
      </div>
      <div class="RealTimeMap_meta">
        <span
          v-if="!loadFailed"
          class="loadingPiwik"
        >
          <ActivityIndicator :loading="true" />
          {{ translate('General_LoadingData') }}...
        </span>
        <span v-else class="pk-emptyDataTable">
          {{ translate(
            'CoreHome_ThereIsNoDataForThisReport'
          ) }}
        </span>
      </div>
      <div
        v-if="hasSuperUserAccess"
        id="realTimeMapNoVisitsInfo"
        class="alert alert-info"
        style="display:none;margin-top:20px;margin-bottom:0;"
      >
        <p>{{ translate('UserCountryMap_NoVisitsInfo') }}</p>
        <p>
          {{ translate('UserCountryMap_NoVisitsInfo2') }}
        </p>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, nextTick } from 'vue';
import {
  AjaxHelper,
  ActivityIndicator,
  Matomo,
  MatomoUrl,
  translate,
} from 'CoreHome';

// Real-time map options that the legacy server action read from the request.
// They are forwarded from the current URL so the config endpoint honours them
// (the page/widget URL may override the defaults, e.g. realtimeWindow).
const REALTIME_OPTION_PARAMS = [
  'realtimeWindow',
  'filter_limit',
  'changeVisitAlpha',
  'removeOldVisits',
  'showFooterMessage',
  'showDateTime',
  'doNotRefreshVisits',
  'enableAnimation',
  'forceNowValue',
];

/* eslint-disable @typescript-eslint/no-explicit-any */
// Legacy real-time map JS namespace (defined in realtime-map.js). It lives on
// its own global (window.UserCountryMapLegacy), not on window.UserCountryMap,
// which is owned by this plugin's Vue UMD bundle.
declare const UserCountryMapLegacy: {
  RealtimeMap: {
    initElements: () => void;
    new (element: any): {
      _destroy?: () => void;
    };
  };
};

declare const $: any;
/* eslint-enable @typescript-eslint/no-explicit-any */

interface RealtimeMapConfig {
  metrics: unknown[];
  svgBasePath: string;
  liveRefreshAfterMs: number;
  _: Record<string, string>;
  reqParams: Record<string, unknown>;
  siteHasGoals: boolean;
  maxVisits: number;
  changeVisitAlpha: number;
  removeOldVisits: number;
  showFooterMessage: number;
  showDateTime: number;
  doNotRefreshVisits: number;
  enableAnimation: number;
  forceNowValue: number;
}

interface RealtimeMapWidgetData {
  configJson: string;
  showFooterMessage: boolean;
  showDateTime: boolean;
  loadFailed: boolean;
  resizeObserver?: ResizeObserver;
}

export default defineComponent({
  components: {
    ActivityIndicator,
  },
  props: {
    uniqueId: String,
    widgetName: String,
    widgetized: Boolean,
    isWidget: Boolean,
    isWide: Boolean,
  },
  data(): RealtimeMapWidgetData {
    return {
      configJson: '',
      showFooterMessage: true,
      showDateTime: true,
      loadFailed: false,
    };
  },
  computed: {
    isStandalone(): boolean {
      return !this.widgetized && !this.isWidget;
    },
    hasSuperUserAccess(): boolean {
      return !!Matomo.hasSuperUserAccess;
    },
  },
  mounted() {
    this.loadConfig();
  },
  beforeUnmount() {
    this.stopResizeObserver();
    // UIControl instances register themselves; find and
    // destroy the one attached to our element
    const el = this.$refs.mapRoot as HTMLElement | undefined;
    if (el && typeof $ === 'function') {
      const ctrl = $(el).data('uiControlObject');
      // eslint-disable-next-line no-underscore-dangle
      if (ctrl && typeof ctrl._destroy === 'function') {
        ctrl._destroy(); // eslint-disable-line no-underscore-dangle
      }
    }
  },
  methods: {
    translate,

    async loadConfig() {
      try {
        const params: Record<string, string> = {
          module: 'UserCountryMap',
          action: 'getRealtimeMapConfig',
        };
        // Source idSite from the URL (hash is authoritative) so the config is
        // built for the viewed site, rather than relying on a possibly stale
        // Matomo.idSite at mount time.
        const idSite = MatomoUrl.getSearchParam('idSite');
        if (idSite) {
          params.idSite = idSite;
        }
        REALTIME_OPTION_PARAMS.forEach((name) => {
          const value = MatomoUrl.getSearchParam(name);
          if (value !== undefined && value !== '') {
            params[name] = value;
          }
        });

        const config = await AjaxHelper.fetch<RealtimeMapConfig>(params);

        this.showFooterMessage = !!config.showFooterMessage;
        this.showDateTime = !!config.showDateTime;
        this.configJson = JSON.stringify(config);

        await nextTick();

        UserCountryMapLegacy.RealtimeMap.initElements();

        this.startResizeObserver();
      } catch {
        this.loadFailed = true;
      }
    },

    startResizeObserver() {
      const container = (this.$refs.mapRoot as HTMLElement)
        ?.querySelector('.RealTimeMap_container');
      if (!container || !window.ResizeObserver) {
        return;
      }

      let lastW = container.clientWidth;
      let lastH = container.clientHeight;

      this.resizeObserver = new ResizeObserver(() => {
        const w = container.clientWidth;
        const h = container.clientHeight;
        if (w !== lastW || h !== lastH) {
          lastW = w;
          lastH = h;
          if (typeof $ === 'function') {
            const el = this.$refs.mapRoot;
            const ctrl = $(el).data('uiControlObject');
            if (ctrl && typeof ctrl.resize === 'function') {
              ctrl.resize();
            }
          }
        }
      });
      this.resizeObserver.observe(container);
    },

    stopResizeObserver() {
      if (this.resizeObserver) {
        this.resizeObserver.disconnect();
        this.resizeObserver = undefined;
      }
    },
  },
});
</script>
