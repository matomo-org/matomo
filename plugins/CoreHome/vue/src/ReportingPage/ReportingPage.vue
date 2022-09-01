<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="reporting-page">
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
import AjaxHelper from '../AjaxHelper/AjaxHelper';

function showOnlyRawDataNotification() {
  const params = 'category=General_Visitors&subcategory=Live_VisitorLog';
  const url = window.broadcast.buildReportingUrl(params);
  NotificationsStore.show({
    id: 'onlyRawData',
    animate: false,
    context: 'info',
    message: translate('CoreHome_PeriodHasOnlyRawData', `<a href="${url}">`, '</a>'),
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
  },
  data(): ReportingPageState {
    return {
      loading: false,
      hasRawData: false,
      hasNoVisits: false,
      dateLastChecked: null,
      hasNoPage: false,
    };
  },
  created() {
    ReportingPageStoreInstance.resetPage();

    this.loading = true; // we only set loading on initial load
    this.renderInitialPage();

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

      this.renderPage(newValue.category as string, newValue.subcategory as string);
    });

    Matomo.on('loadPage', (category: string, subcategory: string) => {
      this.renderPage(category, subcategory);
    });
  },
  computed: {
    widgets() {
      return ReportingPageStoreInstance.widgets.value;
    },
  },
  methods: {
    renderPage(category: string, subcategory: string) {
      if (!category || !subcategory) {
        ReportingPageStoreInstance.resetPage();
        this.loading = false;
        return;
      }

      const parsedUrl = MatomoUrl.parsed.value;
      const currentPeriod = parsedUrl.period as string;
      const currentDate = parsedUrl.date as string;

      try {
        Periods.parse(currentPeriod, currentDate);
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

      Matomo.postEvent('piwikPageChange', {});

      NotificationsStore.clearTransientNotifications();

      if (Periods.parse(currentPeriod, currentDate).containsToday()) {
        this.showOnlyRawDataMessageIfRequired();
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
      this.renderPage(parsed.category as string, parsed.subcategory as string);
    },
    showOnlyRawDataMessageIfRequired() {
      if (this.hasRawData && this.hasNoVisits) {
        showOnlyRawDataNotification();
      }

      const parsedUrl = MatomoUrl.parsed.value;

      const { segment } = parsedUrl;
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

      const subcategory = parsedUrl.subcategory as string;
      const category = parsedUrl.category as string;
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

      AjaxHelper.fetch({ method: 'VisitsSummary.getVisits' }).then((json) => {
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
          method: 'Live.getLastVisitsDetails',
          filter_limit: 1,
          doNotFetchActions: 1,
        }).then((lastVisits) => {
          if (!lastVisits || lastVisits.length === 0) {
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
