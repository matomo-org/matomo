<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div v-content-intro>
      <h2>{{ translate('PrivacyManager_AskingForConsent') }}</h2>
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
      :content-title="translate('PrivacyManager_ConsentRequirements')"
      class="privacyAskingForConsent"
    >
      <p>{{ translate('PrivacyManager_ConsentRequirementsIntro') }}</p>
      <ol>
        <li>{{ translate('PrivacyManager_ConsentRequirementsReasonPersonalData') }}</li>
        <li>{{ translate('PrivacyManager_ConsentRequirementsReasonStorage') }}</li>
      </ol>
    </ContentBlock>

    <ContentBlock
      :content-title="translate('PrivacyManager_WhenDoINeedConsent')"
      class="privacyAskingForConsent"
    >
      <p>{{ translate('PrivacyManager_DetermineConsentNeedIntro') }}</p>
      <ul>
        <li>{{ translate('PrivacyManager_DetermineConsentNeedAction1') }}</li>
        <li>{{ translate('PrivacyManager_DetermineConsentNeedAction2') }}</li>
      </ul>
      <br />
      <p>{{ translate('PrivacyManager_ConsentNotRequiredIntro') }}</p>
      <ul>
        <li>{{ translate('PrivacyManager_ConsentNotRequiredCondition1') }}</li>
        <li>{{ translate('PrivacyManager_ConsentNotRequiredCondition2') }}</li>
        <li>{{ translate('PrivacyManager_ConsentNotRequiredCondition3') }}</li>
        <li>{{ translate('PrivacyManager_ConsentNotRequiredCondition4') }}</li>
      </ul>
    </ContentBlock>

    <ContentBlock
      :content-title="translate('PrivacyManager_HandlingPreviouslyCollectedData')"
      class="privacyAskingForConsent"
    >
      <p>{{ translate('PrivacyManager_HandlingPreviouslyCollectedDataIntro') }}</p>
      <p>{{ translate('PrivacyManager_HandlingPreviouslyCollectedDataDetails') }}</p>
    </ContentBlock>

    <ContentBlock
      :content-title="translate('PrivacyManager_HowToObtainValidConsent')"
      class="privacyAskingForConsent"
    >
      <ol>
        <li>{{ translate('PrivacyManager_ValidConsentRequirement1') }}</li>
        <li>{{ translate('PrivacyManager_ValidConsentRequirement2') }}</li>
        <li>{{ translate('PrivacyManager_ValidConsentRequirement3') }}</li>
        <li>{{ translate('PrivacyManager_ValidConsentRequirement4') }}</li>
        <li>{{ translate('PrivacyManager_ValidConsentRequirement5') }}</li>
        <li>{{ translate('PrivacyManager_ValidConsentRequirement6') }}</li>
        <li>{{ translate('PrivacyManager_ValidConsentRequirement7') }}</li>
        <li>{{ translate('PrivacyManager_ValidConsentRequirement8') }}</li>
        <li>{{ translate('PrivacyManager_ValidConsentRequirement9') }}</li>
        <li>{{ translate('PrivacyManager_ValidConsentRequirement10') }}</li>
      </ol>
    </ContentBlock>

    <ContentBlock
      :content-title="translate('PrivacyManager_ConsentManagementPlatforms')"
      class="privacyAskingForConsent"
    >
      <p>{{ translate('PrivacyManager_ConsentManagementPlatformsIntro') }}</p>
      <ul v-html="$sanitize(consentManagersList)"></ul>
      <p v-html="$sanitize(consentManagementPlatformsOutro)"></p>
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
    consentManagementPlatformsOutro() {
      return translate(
        'PrivacyManager_ConsentManagementPlatformsOutro',
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
