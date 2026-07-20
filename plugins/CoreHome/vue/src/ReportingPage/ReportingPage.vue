<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="reporting-page">
    <SiteWithoutData
      v-if="showEmptySiteScreen"
      :embedded-in-reporting="true"
      @dismissed="onNoDataDismissed"
    />
    <template v-else>
    <ActivityIndicator
      :loading="loading"
    />
    <div v-show="hasNoPage">{{ translate('CoreHome_NoSuchPage') }}</div>
    <div
      class="row"
      v-for="widget in widgets"
      :key="widget.uniqueId"
    >
      <Widget
        class="col s12 fullWidgetColumn"
        v-if="!widget.group"
        :widget="widget"
      />
      <div
        v-if="widget.group"
        class="col s12 l6 leftWidgetColumn"
      >
        <Widget
          v-for="widgetInGroup in widget.left"
          :widget="widgetInGroup"
          :key="widgetInGroup.uniqueId"
        />
      </div>
      <div
        v-if="widget.group"
        class="col s12 l6 rightWidgetColumn"
      >
        <Widget
          v-for="widgetInGroup in widget.right"
          :widget="widgetInGroup"
          :key="widgetInGroup.uniqueId"
        />
      </div>
    </div>
    </template>
  </div>
</template>

<script lang="ts">
import { defineComponent, watch } from 'vue';
import ActivityIndicator from '../ActivityIndicator/ActivityIndicator.vue';
import Widget from '../Widget/Widget.vue';
import ReportingPageStoreInstance from './ReportingPage.store';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { Periods } from '../Periods';
import { NotificationsStore } from '../Notification';
import { translate } from '../translate';
import Matomo from '../Matomo/Matomo';
import ReportingPagesStoreInstance from '../ReportingPages/ReportingPages.store';
import { DEFAULT_GROUP } from '../ReportingMenu/ReportingMenu.store';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import useExternalPluginComponent from '../useExternalPluginComponent';

const SiteWithoutData = useExternalPluginComponent('SitesManager', 'SiteWithoutData');

// Reuse the standalone page's id so its styling (SitesManager.less) and detection
// (broadcast.isNoDataPage, UI tests) also apply to the in-SPA gate.
const SITE_WITHOUT_DATA_BODY_ID = 'site-without-data';

function showOnlyRawDataNotification() {
  const params = 'category=General_Visitors&subcategory=Live_VisitorLog';
  const url = window.broadcast.buildReportingUrl(params);
  let message = translate('CoreHome_PeriodHasOnlyRawData', `<a href="${url}">`, '</a>');

  if (!Matomo.visitorLogEnabled) {
    message = translate('CoreHome_PeriodHasOnlyRawDataNoVisitsLog');
  }

  NotificationsStore.show({
    id: 'onlyRawData',
    animate: false,
    context: 'info',
    message,
    type: 'transient',
  });
}

function hideOnlyRawDataNoticifation() {
  NotificationsStore.remove('onlyRawData');
}

interface ReportingPageState {
  loading: boolean;
  hasRawData: boolean;
  hasNoVisits: boolean;
  dateLastChecked: Date|null;
  hasNoPage: boolean;
  siteHasNoData: boolean;
  noDataDismissed: boolean;
}

interface LoadPageArgs {
  category: string;
  subcategory: string;
  promise?: Promise<void>;
}

export default defineComponent({
  components: {
    ActivityIndicator,
    Widget,
    SiteWithoutData,
  },
  props: {
    // groups the empty-site gate is skipped for (e.g. AI Insights), resolved server-side
    groupsWithoutTrackingRequirement: { type: Array, default: () => [] },
  },
  data(): ReportingPageState {
    return {
      loading: false,
      hasRawData: false,
      hasNoVisits: false,
      dateLastChecked: null,
      hasNoPage: false,
      siteHasNoData: false,
      noDataDismissed: false,
    };
  },
  created() {
    ReportingPageStoreInstance.resetPage();

    this.loading = true; // we only set loading on initial load
    this.renderInitialPage();

    // Fetched in parallel (not awaited) so the common has-data case isn't delayed by a round-trip.
    // A no-data site therefore starts rendering the report first; the gate replaces it once this
    // resolves. The discarded fetch is cheap (a no-data site has nothing to archive).
    this.fetchSiteEmptyState();

    watch(() => this.showEmptySiteScreen, (active) => {
      this.updateSiteWithoutDataBodyId(active);
    });

    watch(() => MatomoUrl.parsed.value, (newValue, oldValue) => {
      if (newValue.category === oldValue.category
        && newValue.subcategory === oldValue.subcategory
        && newValue.period === oldValue.period
        && newValue.date === oldValue.date
        && newValue.segment === oldValue.segment
        && JSON.stringify(newValue.compareDates) === JSON.stringify(oldValue.compareDates)
        && JSON.stringify(newValue.comparePeriods) === JSON.stringify(oldValue.comparePeriods)
        && JSON.stringify(newValue.compareSegments) === JSON.stringify(oldValue.compareSegments)
        && JSON.stringify(newValue.columns || '') === JSON.stringify(oldValue.columns || '')
      ) {
        // this page is already loaded
        return;
      }

      if (newValue.date !== oldValue.date || newValue.period !== oldValue.period) {
        hideOnlyRawDataNoticifation();
        this.dateLastChecked = null;
        this.hasRawData = false;
        this.hasNoVisits = false;
      }

      this.renderPage(
        newValue.category as string,
        newValue.subcategory as string,
        newValue.period as string,
        newValue.date as string,
        newValue.segment as string,
      );
    });

    Matomo.on('loadPage', (category: string, subcategory: string) => {
      const parsedUrl = MatomoUrl.parsed.value;
      this.renderPage(
        category,
        subcategory,
        parsedUrl.period as string,
        parsedUrl.date as string,
        parsedUrl.segment as string,
      );
    });
  },
  unmounted() {
    this.updateSiteWithoutDataBodyId(false);
  },
  computed: {
    widgets() {
      return ReportingPageStoreInstance.widgets.value;
    },
    showEmptySiteScreen() {
      if (!this.siteHasNoData || this.noDataDismissed) {
        return false;
      }

      const activeGroup = (MatomoUrl.parsed.value.group as string) || DEFAULT_GROUP;
      return !this.groupsWithoutTrackingRequirement.includes(activeGroup);
    },
  },
  methods: {
    fetchSiteEmptyState() {
      AjaxHelper.fetch(
        { module: 'SitesManager', action: 'getSiteEmptyState', idSite: Matomo.idSite },
        { createErrorNotification: false },
      ).then((response) => {
        this.siteHasNoData = response === true;
      }).catch(() => {
        // ignore errors - don't block the dashboard on the empty-site check
        this.siteHasNoData = false;
      });
    },
    onNoDataDismissed() {
      // stay on the current page and load it now that the screen is gone
      this.noDataDismissed = true;
      this.renderInitialPage();
    },
    updateSiteWithoutDataBodyId(active: boolean) {
      if (active) {
        document.body.id = SITE_WITHOUT_DATA_BODY_ID;
      } else if (document.body.id === SITE_WITHOUT_DATA_BODY_ID) {
        document.body.id = '';
      }
    },
    renderPage(
      category: string, subcategory: string, period: string, date: string, segment: string,
    ) {
      // No report to render while the gate is shown; rendering would emit matomoPageChange and
      // abort the requests the just-mounted SiteWithoutData component fired. Still clear transient
      // notifications from the page we navigated away from (e.g. an archiving notice).
      if (this.showEmptySiteScreen) {
        NotificationsStore.clearTransientNotifications();
        this.loading = false;
        return;
      }

      if (!category || !subcategory) {
        ReportingPageStoreInstance.resetPage();
        this.loading = false;
        return;
      }

      try {
        Periods.parse(period, date);
      } catch (e) {
        NotificationsStore.show({
          id: 'invalidDate',
          animate: false,
          context: 'error',
          message: translate('CoreHome_DateInvalid'),
          type: 'transient',
        });

        ReportingPageStoreInstance.resetPage();
        this.loading = false;
        return;
      }

      NotificationsStore.remove('invalidDate');

      Matomo.postEvent('matomoPageChange', {});

      NotificationsStore.clearTransientNotifications();

      if (Periods.parse(period, date).containsToday()) {
        this.showOnlyRawDataMessageIfRequired(category, subcategory, period, date, segment);
      }

      const params: LoadPageArgs = { category, subcategory };
      Matomo.postEvent('ReportingPage.loadPage', params);
      if (params.promise) {
        this.loading = true;
        Promise.resolve(params.promise).finally(() => {
          this.loading = false;
        });
        return;
      }

      ReportingPageStoreInstance.fetchPage(category, subcategory).then(() => {
        const hasNoPage = !ReportingPageStoreInstance.page.value;
        if (hasNoPage) {
          const page = ReportingPagesStoreInstance.findPageInCategory(category);
          if (page && page.subcategory) {
            MatomoUrl.updateHash({
              ...MatomoUrl.hashParsed.value,
              subcategory: page.subcategory.id,
            });
            return;
          }
        }

        this.hasNoPage = hasNoPage;
        this.loading = false;
      });
    },
    renderInitialPage() {
      const parsed = MatomoUrl.parsed.value;
      this.renderPage(
        parsed.category as string,
        parsed.subcategory as string,
        parsed.period as string,
        parsed.date as string,
        parsed.segment as string,
      );
    },
    showOnlyRawDataMessageIfRequired(
      category: string, subcategory: string, period: string, date: string, segment: string,
    ) {
      if (this.hasRawData && this.hasNoVisits) {
        showOnlyRawDataNotification();
      }

      if (segment) {
        hideOnlyRawDataNoticifation();
        return;
      }

      const subcategoryExceptions = [
        'Live_VisitorLog',
        'General_RealTime',
        'UserCountryMap_RealTimeMap',
        'MediaAnalytics_TypeAudienceLog',
        'MediaAnalytics_TypeRealTime',
        'FormAnalytics_TypeRealTime',
        'Goals_AddNewGoal',
      ];

      const categoryExceptions = [
        'HeatmapSessionRecording_Heatmaps',
        'HeatmapSessionRecording_SessionRecordings',
        'Marketplace_Marketplace',
      ];

      if (subcategoryExceptions.indexOf(subcategory) !== -1
        || categoryExceptions.indexOf(category) !== -1
        || subcategory.toLowerCase().indexOf('manage') !== -1
      ) {
        hideOnlyRawDataNoticifation();
        return;
      }

      const minuteInMilliseconds = 60000;
      if (this.dateLastChecked
        && ((new Date()).valueOf() - this.dateLastChecked.valueOf()) < minuteInMilliseconds
      ) {
        return;
      }

      AjaxHelper.fetch({
        method: 'VisitsSummary.getVisits',
        date,
        period,
        segment,
      }).then((json) => {
        this.dateLastChecked = new Date();

        if (json.value > 0) {
          this.hasNoVisits = false;
          hideOnlyRawDataNoticifation();
          return undefined;
        }

        this.hasNoVisits = true;

        if (this.hasRawData) {
          showOnlyRawDataNotification();
          return undefined;
        }

        return AjaxHelper.fetch({
          method: 'Live.getMostRecentVisitsDateTime',
          date,
          period,
        }).then((lastVisits) => {
          if (!lastVisits || lastVisits.value === '') {
            this.hasRawData = false;
            hideOnlyRawDataNoticifation();
            return;
          }

          this.hasRawData = true;
          showOnlyRawDataNotification();
        });
      });
    },
  },
});
</script>
