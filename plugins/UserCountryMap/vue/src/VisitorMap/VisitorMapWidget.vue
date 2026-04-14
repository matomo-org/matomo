<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <section class="card">
    <div
      class="UserCountryMap card-content"
      style="position:relative; overflow:hidden;"
    >
      <div class="UserCountryMap_container">
        <div
          class="UserCountryMap_map"
          style="overflow:hidden;"
        />
        <div class="UserCountryMap-overlay UserCountryMap-title">
          <div class="content">
            <div
              class="map-stats"
              style="color:#565656;"
            />
          </div>
        </div>
        <div class="UserCountryMap-overlay UserCountryMap-legend">
          <div class="content" />
        </div>
        <div class="UserCountryMap-tooltip UserCountryMap-info">
          <div class="content unlocated-stats" />
        </div>
        <div
          class="UserCountryMap-info-btn"
          data-tooltip-target=".UserCountryMap-tooltip"
        />
      </div>
      <div class="mapWidgetStatus">
        <template v-if="noData">
          <h2 v-if="!isWidget">
            {{ translate('UserCountryMap_VisitorMap') }}
          </h2>
          <div class="pk-emptyDataTable">
            {{ translate('CoreHome_ThereIsNoDataForThisReport') }}
          </div>
        </template>
        <template v-else-if="loading">
          <span class="loadingPiwik">
            <ActivityIndicator :loading="true" />
            {{ translate('General_LoadingData') }}...
          </span>
        </template>
      </div>
      <div
        v-if="!noData && !loading"
        class="dataTableFeatures"
      >
        <div class="dataTableFooterIcons">
          <div
            class="dataTableFooterWrap"
            var="graphVerticalBar"
          >
            <img
              class="UserCountryMap-activeItem dataTableFooterActiveItem"
              src="plugins/Morpheus/images/data_table_footer_active_item.png"
              style="left: 25px;"
            >

            <div class="tableIconsGroup">
              <span class="tableAllColumnsSwitch">
                <a
                  class="UserCountryMap-btn-zoom tableIcon"
                  format="table"
                >
                  <img
                    src="plugins/Morpheus/images/zoom-out.png"
                    title="Zoom to world"
                  >
                </a>
              </span>
            </div>
            <div class="tableIconsGroup UserCountryMap-view-mode-buttons">
              <span class="tableAllColumnsSwitch">
                <a
                  class="UserCountryMap-btn-region tableIcon activeIcon"
                  :data-region="translate('UserCountryMap_Regions')"
                  :data-country="translate('UserCountryMap_Countries')"
                >
                  <img
                    src="plugins/UserCountryMap/images/regions.png"
                    title="Show visitors per region/country"
                  >
                  <span style="margin:0;">{{
                    translate('UserCountryMap_Countries')
                  }}</span>&nbsp;
                </a>
                <a
                  class="UserCountryMap-btn-city tableIcon inactiveIcon"
                  style="display: none;"
                >
                  <img
                    src="plugins/UserCountryMap/images/cities.png"
                    title="Show visitors per city"
                  >
                  <span style="margin:0;">{{
                    translate('UserCountryMap_Cities')
                  }}</span>&nbsp;
                </a>
              </span>
            </div>
          </div>

          <select
            class="userCountryMapSelectMetrics browser-default"
            :style="metricSelectStyle"
          >
            <option
              v-for="metric in metrics"
              :key="metric[0]"
              :value="metric[0]"
              :selected="metric[0] === defaultMetric"
            >
              {{ metric[1] }}
            </option>
          </select>
          <select
            class="userCountryMapSelectCountry browser-default"
            style="height: auto;"
          >
            <option value="world">
              {{ translate('UserCountryMap_WorldWide') }}
            </option>
            <option disabled="disabled">
              ––––––
            </option>
            <option
              v-for="(name, code) in continents"
              :key="code"
              :value="code"
            >
              {{ name }}
            </option>
            <option disabled="disabled">
              ––––––
            </option>
          </select>
        </div>
      </div>
    </div>
  </section>
</template>

<script lang="ts">
import { defineComponent, nextTick } from 'vue';
import {
  AjaxHelper,
  ActivityIndicator,
  translate,
} from 'CoreHome';

declare global {
  interface Window {
    visitorMap?: { resize: () => void; destroy: () => void };
  }
}

interface VisitorMapConfig {
  visitsSummary: Record<string, unknown>;
  metrics: [string, string][];
  defaultMetric: string;
  svgBasePath: string;
  mapCssPath: string;
  reqParams: Record<string, unknown>;
  _: Record<string, string>;
  countryNames: Record<string, string>;
  continents: Record<string, string>;
}

/* eslint-disable @typescript-eslint/no-explicit-any */
declare const UserCountryMap: {
  VisitorMap: new (config: any, widget?: any) => {
    resize: () => void;
    destroy: () => void;
  };
};
/* eslint-enable @typescript-eslint/no-explicit-any */

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
  data() {
    return {
      loading: true,
      noData: false,
      metrics: [] as [string, string][],
      defaultMetric: 'nb_visits',
      continents: {} as Record<string, string>,
      metricSelectStyle: {
        float: 'right',
        marginRight: '25px',
        marginBottom: '10px',
        maxWidth: '10em',
        fontSize: '10px',
        height: 'auto',
      },
    };
  },
  mounted() {
    this.loadConfig();
  },
  beforeUnmount() {
    if (window.visitorMap) {
      window.visitorMap.destroy();
      window.visitorMap = undefined;
    }
  },
  methods: {
    translate,

    async loadConfig() {
      this.loading = true;
      this.noData = false;

      try {
        const config = await AjaxHelper.fetch<VisitorMapConfig>({
          method: 'UserCountryMap.getVisitorMapConfig',
        });

        if (!config.visitsSummary || !config.visitsSummary.nb_visits) {
          this.noData = true;
          this.loading = false;
          return;
        }

        this.metrics = config.metrics;
        this.defaultMetric = config.defaultMetric;
        this.continents = config.continents;
        this.loading = false;

        await nextTick();

        window.visitorMap = new UserCountryMap.VisitorMap(config);
      } catch {
        this.noData = true;
        this.loading = false;
      }
    },
  },
});
</script>
