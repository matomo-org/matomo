<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <ContentBlock :content-title="`${translate('SitesManager_SiteWithoutDataTitle')} 🚀`">
      <p>&nbsp;</p>
      <p v-html="$sanitize(siteWithoutDataDesc)"></p>
      <p>{{ translate('SitesManager_SiteWithoutDataMessageDisappears') }}</p>

      <h3>{{ translate('SitesManager_SiteWithoutDataChoosePreferredWay') }}</h3>

      <WidgetLoader
        :widget-params="{module: 'SitesManager', action: 'siteWithoutDataTabs'}"
        :loading-message="`${translate('SitesManager_DetectingYourSite')}...`"
      />

      <hr/>

      <a
        class="btn"
        id="emailTrackingCodeBtn"
        :href="emailInstructionsLink"
      >{{ translate('SitesManager_EmailInstructionsButton') }}</a>

      <VueEntryContainer :html="afterIntroEventContent"/>

      <br />
      <a :href="ignoreSitesWithoutDataLink"
         class="btn ignoreSitesWithoutData"
      >
        {{ translate('SitesManager_SiteWithoutDataIgnoreMessage') }}
      </a>
    </ContentBlock>

    <VueEntryContainer :html="afterTrackingHelpEventContent"/>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
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
    ContentBlock,
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
