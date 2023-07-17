<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
      <h1 id="start-tracking-data-header">
        {{translate('SitesManager_SiteWithoutDataStartTrackingDataHeader')}}
      </h1>
      <p v-html="$sanitize(siteWithoutDataDescLine1)"></p>
      <p v-html="$sanitize(siteWithoutDataDescLine2)"></p>
      <p>&nbsp;</p>

      <WidgetLoader
        :widget-params="{module: 'SitesManager', action: 'siteWithoutDataTabs',
        activeTab: activeTab}"
        :loading-message="`${translate('SitesManager_DetectingYourSite')}...`"
      />

      <div class="no-data-footer row">
        <hr v-if="afterIntroEventContent"/>

        <VueEntryContainer :html="afterIntroEventContent"/>
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
    siteWithoutDataStartTrackingTranslationKey: {
      type: String,
      required: true,
    },
    inviteUserLink: {
      type: String,
      required: true,
    },
    afterIntroEventContent: String,
    afterTrackingHelpEventContent: String,
    activeTab: String,
  },
  components: {
    WidgetLoader,
    VueEntryContainer,
  },
  computed: {
    siteWithoutDataDescLine1() {
      return translate(
        this.siteWithoutDataStartTrackingTranslationKey,
        `<a rel="noreferrer noopener" target="_blank" class="emailTrackingCode" href="${this.emailInstructionsLink}">`,
        '</a>',
        `<a rel="noreferrer noopener" target="_blank" href="${this.inviteUserLink}">`,
        '</a>',
      );
    },
    siteWithoutDataDescLine2() {
      return translate(
        'SitesManager_SiteWithoutDataStartTrackingDataDescriptionLine2',
        `<a href="${this.ignoreSitesWithoutDataLink}" class="ignoreSitesWithoutData">`,
        '</a>',
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
