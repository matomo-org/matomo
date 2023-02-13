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
      :content-title="translate('PrivacyManager_WhenDoINeedConsent')"
      class="privacyAskingForConsent"
    >
      <p>
        <span v-html="$sanitize(whenConsentIsNeeded1)"></span>
        <br /><br />
        <span v-html="$sanitize(whenConsentIsNeeded2)"></span>
      </p>
    </ContentBlock>

    <ContentBlock
      :content-title="translate('PrivacyManager_HowDoIAskForConsent')"
      class="privacyAskingForConsent"
    >
      <p v-html="$sanitize(howDoIAskForConsentIntroduction)"></p>
    </ContentBlock>

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
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  ContentIntro,
  translate,
  MatomoUrl,
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
  },
  components: {
    ContentBlock,
  },
  directives: {
    ContentIntro,
  },
  computed: {
    whenConsentIsNeeded1() {
      const blogLink = 'https://matomo.org/blog/2018/04/lawful-basis-for-processing-personal-data-under-gdpr-with-matomo/';
      return translate(
        'PrivacyManager_WhenConsentIsNeeded1',
        '<a href="https://matomo.org/docs/gdpr/" target="_blank" rel="noreferrer noopener">',
        '</a>',
        `<a href="${blogLink}" target="_blank" rel="noreferrer noopener">`,
        '</a>',
      );
    },
    whenConsentIsNeeded2() {
      const link = `?${MatomoUrl.stringify({
        module: 'PrivacyManager',
        action: 'privacySettings',
      })}`;

      return translate(
        'PrivacyManager_WhenConsentIsNeeded2',
        `<a href="${link}">`,
        '</a>.',
      );
    },
    howDoIAskForConsentIntroduction() {
      const link = 'https://developer.matomo.org/guides/tracking-consent';
      return translate(
        'PrivacyManager_HowDoIAskForConsentIntroduction',
        `<a href="${link}" target="_blank" rel="noreferrer noopener">`,
        '</a>',
      );
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
