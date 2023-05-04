<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
      <p v-html="$sanitize(startTrackingDataTitle)"></p>
      <p>{{ translate('SitesManager_SiteWithoutDataStartTrackingDataDescriptionLine1') }}</p>
      <p>&nbsp;</p>

      <p>{{ translate('SitesManager_SiteWithoutDataStartTrackingDataDescriptionLine2') }}</p>
      <p>&nbsp;</p>

      <WidgetLoader
        :widget-params="{module: 'SitesManager', action: 'siteWithoutDataTabs'}"
        :loading-message="`${translate('SitesManager_DetectingYourSite')}...`"
      />

      <div class="no-data-footer row">
        <hr/>
        <div class="col s2 m-top-1">
          <a
            class="btn"
            id="emailTrackingCodeBtn"
            :href="emailInstructionsLink"
          >{{ translate('SitesManager_EmailInstructionsButtonText') }}</a>
        </div>

        <VueEntryContainer :html="afterIntroEventContent"/>

        <div class="col s2 m-top-1">
          <a
            class="btn"
            id="demoSiteBtn"
            :href="emailInstructionsLink"
          >{{ translate('SitesManager_DemoSiteButtonText') }}</a>
        </div>

        <div class="col s2 m-top-1">
          <a :href="ignoreSitesWithoutDataLink"
             class="btn ignoreSitesWithoutData"
          >
            {{ translate('SitesManager_SiteWithoutDataIgnorePage') }}
          </a>
        </div>
      </div>

    <VueEntryContainer :html="afterTrackingHelpEventContent"/>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  MatomoUrl,
  WidgetLoader,
  VueEntryContainer,
} from 'CoreHome';

export default defineComponent({
  props: {
    emailBody: {
      type: String,
      required: true,
    },
    afterIntroEventContent: String,
    afterTrackingHelpEventContent: String,
  },
  components: {
    WidgetLoader,
    VueEntryContainer,
  },
  computed: {
    siteWithoutDataDesc() {
      return translate(
        'SitesManager_SiteWithoutDataDescription',
        `<a href="${this.emailInstructionsLink}">`,
        '</a>',
      );
    },
    startTrackingDataTitle() {
      return translate(
        'SitesManager_SiteWithoutDataStartTrackingDataHeader',
        '<h1>',
        '</h1>',
      );
    },
    emailInstructionsLink() {
      return `mailto:?${MatomoUrl.stringify({
        subject: translate('SitesManager_EmailInstructionsSubject'),
        body: this.emailBody,
      })}`;
    },
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
