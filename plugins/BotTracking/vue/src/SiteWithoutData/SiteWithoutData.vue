<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <a v-if="showMethodDetails"
       class="tracking-method-back"
       @click.prevent="showOverview();"
    >
      <span class="icon-chevron-left"></span>
      {{ translate('Mobile_NavigationBack') }}
    </a>

    <h1>{{ headline }}</h1>
    <p v-if="!loading && !showMethodDetails">
      {{ translate('BotTracking_SiteWithoutDataChooseTrackingMethodPreamble1') }}
      <br>
      {{ translate('BotTracking_SiteWithoutDataChooseTrackingMethodPreamble2') }}
    </p>

    <ActivityIndicator
      :loading-message="translate('BotTracking_DetectingYourSite')"
      :loading="loading"
    />

    <template v-if="!loading && !showMethodDetails">
      <div class="row" v-if="errorMessage">
        <span class="icon-warning"></span>
        <h2>{{ errorMessage }}</h2>
        <a class="btn" href="#" @click.prevent="fetchTrackingMethods()">
          {{ translate('General_Refresh') }}
        </a>
      </div>

      <div class="row tracking-method-detection" v-if="recommendedMethod">
        <img :src="recommendedMethod.icon" :alt="`${recommendedMethod.name} logo`" />
        <h2>
          {{ translate(
            'BotTracking_SiteWithoutDataInstallWithXRecommendation',
            recommendedMethod.name,
          ) }}
        </h2>
        <p>
          {{ translate(
            'BotTracking_SiteWithoutDataRecommendationText',
            recommendedMethod.name,
          ) }}
        </p>
        <a :href="`#${recommendedMethod.id.toLowerCase()}`"
           class="btn"
           @click.prevent="showMethod(recommendedMethod.id)">
          {{ translate(
            'BotTracking_SiteWithoutDataInstallWithX',
            recommendedMethod.name,
          ) }}
        </a>
      </div>

      <div class="row tracking-method-list">
        <span class="icon-search"></span>
        <h2>{{ translate('BotTracking_SiteWithoutDataOtherInstallMethods') }}</h2>
        <p>{{ translate('BotTracking_SiteWithoutDataOtherInstallMethodsIntro') }}</p>
        <ul>
          <li class="list-entry" v-for="method in trackingMethods" :key="method.id">
            <a
              v-if="method.content"
              :href="`#${method.id.toLowerCase()}`"
              @click.prevent="showMethod(method.id)"
            >
              <img :src="method.icon" class="list-entry-icon" v-if="method.icon" />
              <span v-else class="list-entry-icon" aria-hidden="true"></span>
              <span class="list-entry-text">{{ method.name }}</span>
            </a>
            <a
              v-else-if="method.link"
              :href="method.link"
              target="_blank"
              rel="noreferrer noopener"
            >
              <img :src="method.icon" class="list-entry-icon" v-if="method.icon" />
              <span v-else class="list-entry-icon" aria-hidden="true"></span>
              <span class="list-entry-text">{{ method.name }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="tracking-method-skip">
        <h2>{{ translate('BotTracking_SiteWithoutDataNotYetReady') }}</h2>
        <a :href="backToMatomoLink">
          {{ translate('BotTracking_SiteWithoutDataBackToMatomo') }}
        </a>
      </div>
    </template>

    <div v-if="showMethodDetails"
         class="tracking-method-details"
         :data-method="showMethodDetails.id">
      <img :src="showMethodDetails.icon" :alt="`${showMethodDetails.name} logo`" />
      <VueEntryContainer :html="showMethodDetails.content" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, watch } from 'vue';
import {
  translate,
  ActivityIndicator,
  AjaxHelper,
  Matomo,
  MatomoUrl,
  VueEntryContainer,
} from 'CoreHome';

interface TrackingMethod {
  id: string,
  name: string,
  content: string,
  link?: string,
  icon: string,
  priority: number,
  isOthers: boolean,
  wasDetected: boolean
}
interface SiteWithoutDataState {
  loading: boolean,
  errorMessage: string|null,
  showMethodDetails: TrackingMethod|null;
  recommendedMethod: TrackingMethod|null,
  trackingMethods: Array<TrackingMethod>,
}

export default defineComponent({
  components: {
    ActivityIndicator,
    VueEntryContainer,
  },
  props: {
    backToMatomoLink: String,
  },
  data(): SiteWithoutDataState {
    return {
      loading: true,
      errorMessage: null,
      showMethodDetails: null,
      recommendedMethod: null,
      trackingMethods: [],
    };
  },
  created() {
    watch(() => MatomoUrl.hashParsed.value.activeTab as string, (activeTab) => {
      this.showMethodDetails = this.findTrackingMethod(activeTab);
    });

    this.fetchTrackingMethods();
  },
  methods: {
    fetchTrackingMethods() {
      const params: QueryParameters = {
        module: 'BotTracking',
        action: 'getTrackingMethodsForSite',
        idSite: Matomo.idSite,
      };

      this.loading = true;
      this.errorMessage = null;

      AjaxHelper.fetch(params).then((response) => {
        const trackingMethods = Array.isArray(response?.trackingMethods)
          ? [...response.trackingMethods]
          : [];
        const recommendedIndex = trackingMethods.findIndex((method) => method.wasDetected);

        if (recommendedIndex !== -1) {
          this.recommendedMethod = trackingMethods[recommendedIndex];
          trackingMethods.splice(recommendedIndex, 1);
        } else {
          this.recommendedMethod = null;
        }

        this.trackingMethods = trackingMethods;
      }).catch(() => {
        this.errorMessage = translate('General_ErrorRequest', '', '');
        this.recommendedMethod = null;
        this.trackingMethods = [];
      }).finally(() => {
        this.loading = false;
        this.showMethodDetails = this.findTrackingMethod(
          MatomoUrl.hashParsed.value.activeTab as string,
        );
      });
    },
    findTrackingMethod(methodId: string|null) {
      if (
        this.recommendedMethod
        && methodId
        && this.recommendedMethod.id.toLowerCase() === methodId.toLowerCase()
      ) {
        return this.recommendedMethod;
      }

      let trackingMethod = null;

      Object.entries(this.trackingMethods).forEach(([, method]) => {
        if (methodId && method.id.toLowerCase() === methodId.toLowerCase() && method.content) {
          trackingMethod = method;
        }
      });

      return trackingMethod;
    },
    showMethod(methodId: string) {
      MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, activeTab: methodId.toLowerCase() });
    },
    showOverview() {
      MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, activeTab: null });
    },
  },
  computed: {
    headline(): string {
      if (this.showMethodDetails && this.showMethodDetails.name) {
        if (this.showMethodDetails.isOthers) {
          return this.showMethodDetails.name;
        }

        return translate('BotTracking_SiteWithoutDataInstallWithX', this.showMethodDetails.name);
      }
      return translate('BotTracking_SiteWithoutDataChooseTrackingMethod');
    },
  },
});
</script>
