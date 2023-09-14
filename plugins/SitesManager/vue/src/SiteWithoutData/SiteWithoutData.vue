<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <a id="start-tracking-back"
       v-if="showMethodDetails"
       @click.prevent="showOverview();"
    >
      <span class="icon-chevron-left"></span>
      {{ translate('Mobile_NavigationBack') }}
    </a>

    <h1 id="start-tracking-data-header">
      {{ headline }}
    </h1>

    <div id="start-tracking-cta">
      <a rel="noreferrer noopener" target="_blank" :href="inviteUserLink">
        <span class="icon-user-add"></span>
        {{ translate('UsersManager_InviteTeamMember') }}
      </a>
      <VueEntryContainer :html="additionalCtaContent" v-if="additionalCtaContent"/>
    </div>

    <ActivityIndicator
      :loading-message="`${translate('SitesManager_DetectingYourSite')}&hellip;`"
      :loading="loading"
    />

    <template v-if="!loading && !showMethodDetails">
      <div class="row" id="start-tracking-detection" v-if="recommendedMethod">
        <!--div id="share-button">
            <a href="">
                <span class="icon-upload"></span>
                {{ translate('General_Share') }}
            </a>
        </div-->
        <img :src="recommendedMethod.icon" :alt="`${recommendedMethod.name} logo`" />
        <h2>Install Matomo with {{ recommendedMethod.name }} (recommended for you)</h2>
        <p>
          We have detected {{ recommendedMethod.name }} on your site, so you can set up Matomo
          within a few minutes with our official {{ recommendedMethod.name }} integration.
        </p>
        <a :href="`#${recommendedMethod.id.toLowerCase()}`"
           class="btn" id="showMethod"
           @click.prevent="showMethod(recommendedMethod.id)">
          Install with {{ recommendedMethod.name }}
        </a>
      </div>

      <div class="row" id="start-tracking-method-list">
        <span class="icon-search"></span>
        <h2>{{ translate('SitesManager_SiteWithoutDataOtherInstallMethods') }}</h2>
        <p>{{ translate('SitesManager_SiteWithoutDataOtherInstallMethodsIntro') }}</p>
        <ul>
          <li class="list-entry" v-for="method in trackingMethods" :key="method.id">
            <a :href="`#${method.id.toLowerCase()}`" @click.prevent="showMethod(method.id)">
              <img :src="method.icon" class="list-entry-icon" v-if="method.icon" />
              <span class="list-entry-text">{{ method.name }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div id="start-tracking-skip">
        <h2>{{ translate('SitesManager_SiteWithoutDataNotYetReady') }}</h2>
        <div>{{ translate('SitesManager_SiteWithoutDataTemporarilyHidePage') }}</div>
        <a :href="ignoreSitesWithoutDataLink" class="ignoreSitesWithoutData">
          {{ translate('SitesManager_SiteWithoutDataHidePageForHour') }}
        </a>
      </div>
    </template>

    <div id="start-tracking-details" v-if="showMethodDetails">
      <!--div id="share-button">
          <a href="">
              <span class="icon-upload"></span>
              {{ translate('General_Share') }}
          </a>
      </div-->
      <img :src="showMethodDetails.icon" :alt="`${showMethodDetails.name} logo`" />
      <h2>{{ translate('SitesManager_StepByStepGuide') }}</h2>
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
  MatomoUrl,
  VueEntryContainer,
} from 'CoreHome';

interface TrackingMethod {
  id: string,
  name: string,
  type: number,
  content: string,
  icon: string,
  priority: number,
  wasDetected: boolean
}
interface SiteWithoutDataState {
  loading: boolean,
  showMethodDetails: TrackingMethod|null;
  recommendedMethod: TrackingMethod|null,
  trackingMethods: Array<TrackingMethod>,
}

export default defineComponent({
  props: {
    inviteUserLink: {
      type: String,
      required: true,
    },
    additionalCtaContent: String,
    isSingleSite: Boolean,
  },
  components: {
    ActivityIndicator,
    VueEntryContainer,
  },
  data(): SiteWithoutDataState {
    return {
      loading: true,
      showMethodDetails: null,
      recommendedMethod: null,
      trackingMethods: [],
    };
  },
  created() {
    const params: QueryParameters = {
      module: 'SitesManager',
      action: 'getTrackingMethodsForSite',
    };

    AjaxHelper.fetch(params).then((response) => {
      this.trackingMethods = response.trackingMethods;
      this.recommendedMethod = response.recommendedMethod;
      this.loading = false;

      // set up watch once all data was fetched, to ensure tracking methods are available
      watch(() => MatomoUrl.hashParsed.value.activeTab as string, (activeTab) => {
        this.showMethodDetails = this.findTrackingMethod(activeTab);
      });

      if (MatomoUrl.hashParsed.value.activeTab) {
        this.showMethodDetails = this.findTrackingMethod(
          MatomoUrl.hashParsed.value.activeTab as string,
        );
      }
    });
  },
  methods: {
    findTrackingMethod(methodId: string) {
      if (this.recommendedMethod && this.recommendedMethod.id === methodId) {
        return this.recommendedMethod;
      }

      let trackingMethod = null;

      Object.entries(this.trackingMethods).forEach(([, method]) => {
        if (method.id === methodId) {
          trackingMethod = method;
        }
      });

      return trackingMethod;
    },
    showMethod(methodId: string) {
      MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, activeTab: methodId });
    },
    showOverview() {
      MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, activeTab: null });
    },
  },
  computed: {
    ignoreSitesWithoutDataLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'SitesManager',
        action: 'ignoreNoDataMessage',
      })}`;
    },
    headline() {
      if (this.showMethodDetails && this.showMethodDetails.name) {
        if (this.showMethodDetails.type === 99) {
          return this.showMethodDetails.name;
        }
        return `Install with ${this.showMethodDetails.name}`;
      }
      return translate('SitesManager_SiteWithoutDataChooseTrackingMethod');
    },
  },
});
</script>
