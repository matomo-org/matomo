<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div v-content-intro>
      <h2>{{ translate('PrivacyManager_AskingForConsent') }}</h2>
      <p>{{ translate('PrivacyManager_ConsentExplanation') }}</p>
    </div>

    <ContentBlock
      :content-title="translate('PrivacyManager_ConsentManager')"
      class="privacyAskingForConsent"
      v-if="consentManagerName"
    >
      <p v-html="$sanitize(consentManagerDetectedText)"></p>
      <p v-if="consentManagerIsConnected"
         v-html="$sanitize(translate('PrivacyManager_ConsentManagerConnected', consentManagerName))"
      ></p>
    </ContentBlock>

    <ContentBlock
      :content-title="translate('PrivacyManager_WhenDoINeedConsent')"
      class="privacyAskingForConsent"
    >
      <p>
        <span v-html="$sanitize(whenConsentIsNeeded1)"></span>
        <br /><br />
        <span v-html="$sanitize(whenConsentIsNeeded2)"></span>
        <br /><br />
        <span v-html="$sanitize(whenConsentIsNeeded3)"></span>
      </p>
    </ContentBlock>

    <ContentBlock
      :content-title="translate('PrivacyManager_HowDoIAskForConsent')"
      class="privacyAskingForConsent"
    >
      <p>{{ translate('PrivacyManager_HowDoIAskForConsentIntro') }}</p>
      <ul v-html="$sanitize(consentManagersList)"></ul>
      <p></p>
      <p v-html="$sanitize(howDoIAskForConsentOthers)"></p>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  ContentIntro,
  translate,
  externalLink,
  externalRawLink,
} from 'CoreHome';

export default defineComponent({
  props: {
    consentManagerName: {
      type: String,
      required: true,
    },
    consentManagerUrl: {
      type: String,
      required: true,
    },
    consentManagerIsConnected: {
      type: Boolean,
      required: true,
    },
    consentManagers: {
      type: Object,
      required: true,
    },
  },
  components: {
    ContentBlock,
  },
  directives: {
    ContentIntro,
  },
  computed: {
    whenConsentIsNeeded1() {
      return translate(
        'PrivacyManager_WhenConsentIsNeededPart1',
        externalLink('https://matomo.org/faq/new-to-piwik/what-is-gdpr/'),
        '</a>',
      );
    },
    whenConsentIsNeeded2() {
      const blogLink = 'https://matomo.org/blog/2018/04/lawful-basis-for-processing-personal-data-under-gdpr-with-matomo/';
      return translate(
        'PrivacyManager_WhenConsentIsNeededPart2',
        externalLink(blogLink),
        '</a>',
      );
    },
    whenConsentIsNeeded3() {
      return translate(
        'PrivacyManager_WhenConsentIsNeededPart3',
        externalLink('https://matomo.org/faq/how-to/faq_35661/'),
        '</a>',
      );
    },
    howDoIAskForConsentOthers() {
      return translate(
        'PrivacyManager_HowDoIAskForConsentOutro',
        externalLink('https://developer.matomo.org/guides/tracking-consent'),
        '</a>',
      );
    },
    consentManagersList() {
      let list = '';
      Object.entries(this.consentManagers).forEach(([name, url]) => {
        const u = externalRawLink(url);
        list += '<li>'
          + `  <a href="${u}"`
          + '     target="_blank" rel="noreferrer noopener">'
          + `    ${name} ${translate('PrivacyManager_ConsentManager')}`
          + '  </a>'
          + '</li>';
      });
      return list;
    },
    consentManagerDetectedText() {
      return translate(
        'PrivacyManager_ConsentManagerDetected',
        this.consentManagerName,
        `<a href="${this.consentManagerUrl}" target="_blank" rel="noreferrer noopener">`,
        '</a>',
      );
    },
  },
});
</script>
