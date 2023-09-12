<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <h1 id="start-tracking-data-header">
      {{ translate('SitesManager_SiteWithoutDataChooseTrackingMethod') }}
    </h1>

    <div id="start-tracking-cta">
      <div>
        <a rel="noreferrer noopener" target="_blank" :href="inviteUserLink">
          <span class="icon-user-add"></span>
          {{ translate('UsersManager_InviteTeamMember') }}
        </a>
      </div>
      <VueEntryContainer :html="additionalCtaContent" v-if="additionalCtaContent"/>
    </div>

    <SiteSelector v-if="!isSingleSite"
      :show-selected-site="true"
      :show-all-sites-item="false"
    />

    <WidgetLoader
      :widget-params="{module: 'SitesManager', action: 'siteWithoutDataTabs'}"
      :loading-message="`${translate('SitesManager_DetectingYourSite')}&hellip;`"
    />

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
  MatomoUrl,
  SiteSelector,
  WidgetLoader,
  VueEntryContainer,
} from 'CoreHome';

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
    SiteSelector,
    WidgetLoader,
    VueEntryContainer,
  },
  computed: {
    ignoreSitesWithoutDataLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'SitesManager',
        action: 'ignoreNoDataMessage',
      })}`;
    },
  },
});
</script>
