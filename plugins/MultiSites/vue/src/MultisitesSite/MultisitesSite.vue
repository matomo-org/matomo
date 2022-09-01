<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <tr
    :class="{'groupedWebsite': website.group, 'website': !website.group, 'group': website.isGroup}"
    ref="root"
  >
    <td
      class="multisites-label label"
      v-if="!website.isGroup"
    >
      <a
        title="View reports"
        class="value truncated-text-line"
        :href="dashboardUrl(website)"
      >
        {{ websiteLabel }}
      </a>
      <span v-if="website.main_url">
        <a
          rel="noreferrer noopener"
          target="_blank"
          :href="website.main_url"
          :title="translate('General_GoTo', website.main_url)"
        >
          <span class="icon icon-outlink" /></a>
      </span>
    </td>
    <td
      class="multisites-label label"
      v-if="website.isGroup"
    >
      <span class="value">{{ websiteLabel }}</span>
    </td>
    <td class="multisites-column">
      <span class="value">{{ website.nb_visits }}</span>
    </td>
    <td class="multisites-column">
      <span class="value">{{ website.nb_pageviews }}</span>
    </td>
    <td
      class="multisites-column"
      v-if="displayRevenueColumn"
    >
      <span class="value">{{ website.revenue }}</span>
    </td>
    <td
      class="multisites-evolution"
      v-if="period !== 'range'"
      :title="website.tooltip"
    >
      <div
        class="visits value"
        v-if="!website.isGroup"
      >
        <span v-show="website[`${evolutionMetric}_trend`] === 1">
          <img
            class="multisites_icon"
            src="plugins/MultiSites/images/arrow_up.png"
            alt
          /> <span style="color: green;">{{ website[evolutionMetric] }}</span>
        </span>
        <span v-show="website[`${evolutionMetric}_trend`] === 0">
          <img
            class="multisites_icon"
            src="plugins/MultiSites/images/stop.png"
            alt
          /> <span>{{ website[evolutionMetric] }}</span>
        </span>
        <span v-show="website[`${evolutionMetric}_trend`] === -1">
          <img
            class="multisites_icon"
            src="plugins/MultiSites/images/arrow_down.png"
            alt
          /> <span style="color: red;">{{ website[evolutionMetric] }}</span>
        </span>
      </div>
    </td>
    <td
      style="width:180px;"
      v-if="showSparklines"
    >
      <div
        class="sparkline"
        style="width: 100px; margin: auto;"
        v-if="!website.isGroup"
      >
        <a
          rel="noreferrer noopener"
          target="_blank"
          :href="dashboardUrl(website)"
          :title="translate('General_GoTo', translate('Dashboard_DashboardOf', websiteLabel))"
        >
          <img
            alt
            width="100"
            height="25"
            :src="sparklineImage(website)"
          />
        </a>
      </div>
    </td>
  </tr>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Matomo, MatomoUrl, Site } from 'CoreHome';

export default defineComponent({
  props: {
    website: {
      type: Object,
      required: true,
    },
    evolutionMetric: {
      type: String,
      required: true,
    },
    showSparklines: Boolean,
    dateSparkline: String,
    displayRevenueColumn: Boolean,
    metric: String,
  },
  mounted() {
    Matomo.postEvent('MultiSites.MultiSitesSite.mounted', { element: this.$refs.root });
  },
  unmounted() {
    Matomo.postEvent('MultiSites.MultiSitesSite.unmounted', { element: this.$refs.root });
  },
  methods: {
    dashboardUrl(website: Site) {
      return `index.php?module=CoreHome&action=index&date=${this.date}&period=${this.period}`
        + `&idSite=${website.idsite}${this.tokenParam}`;
    },
    sparklineImage(website: Site) {
      let { metric } = this;

      switch (this.evolutionMetric) {
        case 'visits_evolution':
          metric = 'nb_visits';
          break;
        case 'pageviews_evolution':
          metric = 'nb_pageviews';
          break;
        case 'revenue_evolution':
          metric = 'revenue';
          break;
        default:
          break;
      }

      return `index.php?module=MultiSites&action=getEvolutionGraph&period=${this.period}&date=`
        + `${this.dateSparkline}&evolutionBy=${metric}&columns=${metric}&idSite=${website.idsite}`
        + `&idsite=${website.idsite}&viewDataTable=sparkline${this.tokenParam}&colors=`
        + `${encodeURIComponent(JSON.stringify(Matomo.getSparklineColors()))}`;
    },
  },
  computed: {
    tokenParam() {
      const token_auth = MatomoUrl.urlParsed.value.token_auth as string;
      return token_auth ? `&token_auth=${token_auth}` : '';
    },
    period() {
      return Matomo.period;
    },
    date() {
      return MatomoUrl.urlParsed.value.date as string;
    },
    websiteLabel() {
      return Matomo.helper.htmlDecode(this.website.label);
    },
  },
});
</script>
