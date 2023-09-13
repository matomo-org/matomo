<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
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

    <template v-if="!loading">
      <div class="row" id="start-tracking-detection" v-if="recommendedMethod">
        <!--div id="share-button">
            <a href="">
                <span class="icon-upload"></span>
                Share
            </a>
        </div-->
        <img :src="recommendedMethod.icon" :alt="`${recommendedMethod.name} logo`" />
        <h2>Install Matomo with {{ recommendedMethod.name }} (recommended for you)</h2>
        <p>
          We have detected {{ recommendedMethod.name }} on your site, so you can set up Matomo
          within a few minutes with our official {{ recommendedMethod.name }} integration.
        </p>
        <a href="" class="btn">Install with {{ recommendedMethod.name }}</a>
      </div>

      <div class="row" id="start-tracking-method-list">
        <span class="icon-search"></span>
        <h2>{{ translate('SitesManager_SiteWithoutDataOtherInstallMethods') }}</h2>
        <p>{{ translate('SitesManager_SiteWithoutDataOtherInstallMethodsIntro') }}</p>
        <ul>
          <li class="list-entry" v-for="method in trackingMethods" :key="method.id">
            <a :href="`#${method.id.toLowerCase()}`" @click="showMethod(method)">
              <img :src="method.icon" class="list-entry-icon" v-if="method.icon" />
              <span class="list-entry-text">{{ method.name }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div :id="method.id.toLowerCase()" v-for="method in trackingMethods" :key="method.id"
           class="start-tracking-method">
        <VueEntryContainer :html="method.content"/>
      </div>
    </template>

    <div id="start-tracking-skip">
      <h2>{{ translate('SitesManager_SiteWithoutDataNotYetReady') }}</h2>
      <div>{{ translate('SitesManager_SiteWithoutDataTemporarilyHidePage') }}</div>
      <a :href="ignoreSitesWithoutDataLink" class="ignoreSitesWithoutData">
        {{ translate('SitesManager_SiteWithoutDataHidePageForHour') }}
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
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
  type: string,
  content: string,
  icon: string,
  priority: number,
  wasDetected: boolean
}
interface SiteWithoutDataState {
  loading: boolean,
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
      recommendedMethod: null,
      trackingMethods: [],
    };
  },
  created() {
    const params: QueryParameters = {
      module: 'SitesManager',
      action: 'siteWithoutDataTabs',
    };

    AjaxHelper.fetch(params).then((response) => {
      this.trackingMethods = response.tabs;
      this.recommendedMethod = response.recommendedMethod;
      this.loading = false;
    });
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
      return translate('SitesManager_SiteWithoutDataChooseTrackingMethod');
    },
  },
});
</script>
