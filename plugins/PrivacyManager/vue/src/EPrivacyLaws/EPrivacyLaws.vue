<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="eprivacyLaws">
    <ContentBlock :content-title="translate('PrivacyManager_EPrivacyLaws')">
      <p>
        {{ translate('PrivacyManager_EPrivacyIntro') }}
        <br /><br />
        {{ translate('PrivacyManager_EPrivacyDirectiveArticle53Intro') }}
      </p>
      <ul>
        <li>{{ translate('PrivacyManager_EPrivacyConsentRequired') }}</li>
        <li>
          {{ translate('PrivacyManager_EPrivacyExceptionsExist') }}
          <ul>
            <li>
              {{ translate('PrivacyManager_EPrivacyStrictlyNecessaryExamplesTitle') }}
              <ul>
                <li>{{ translate('PrivacyManager_EPrivacyExampleConsentStatus') }}</li>
                <li>{{ translate('PrivacyManager_EPrivacyExampleAuthenticationSecurity') }}</li>
                <li>{{ translate('PrivacyManager_EPrivacyExampleCartBilling') }}</li>
                <li>{{ translate('PrivacyManager_EPrivacyExamplePersonalisation') }}</li>
                <li>{{ translate('PrivacyManager_EPrivacyExampleLoadBalancing') }}</li>
              </ul>
            </li>
            <li>{{ translate('PrivacyManager_EPrivacyTransmissionException') }}</li>
          </ul>
        </li>
      </ul>
      <p>{{ translate('PrivacyManager_EPrivacyAnonymousTrackingConsent') }}</p>
    </ContentBlock>

    <ContentBlock :content-title="translate('PrivacyManager_EPrivacyNationalImplementationsTitle')">
      <ul>
        <li>{{ translate('PrivacyManager_EPrivacyNationalImplementationAnalyticsExempt') }}</li>
        <li>{{ translate('PrivacyManager_EPrivacyNationalImplementationPriorConsent') }}</li>
      </ul>
      <p>{{ translate('PrivacyManager_EPrivacyCheckLocalRules') }}</p>
    </ContentBlock>

    <ContentBlock :content-title="translate('PrivacyManager_EPrivacyFurtherInformationTitle')">
      <ul>
        <li>
          <a
            href="https://matomo.org/faq/general/eprivacy-directive-national-implementations-and-website-analytics/"
            target="_blank"
            rel="noreferrer noopener"
          >
            {{ translate('PrivacyManager_EPrivacyFurtherInfoDirectiveAndAnalytics') }}
          </a>
        </li>
        <li>
          <a
            href="https://matomo.org/faq/how-to/how-do-i-configure-matomo-without-tracking-consent-for-french-visitors-cnil-exemption/"
            target="_blank"
            rel="noreferrer noopener"
          >
            {{ translate('PrivacyManager_EPrivacyFurtherInfoFrenchVisitors') }}
          </a>
        </li>
        <li>
          <a
            href="https://matomo.org/faq/new-to-piwik/configure-matomo-analytics-for-tdddg-ttdsg-compliance/"
            target="_blank"
            rel="noreferrer noopener"
          >
            {{ translate('PrivacyManager_EPrivacyFurtherInfoTdddgCompliance') }}
          </a>
        </li>
        <li>
          <a
            href="https://matomo.org/faq/new-to-piwik/how-do-i-use-matomo-analytics-without-consent-or-cookie-banner/"
            target="_blank"
            rel="noreferrer noopener"
          >
            {{ translate('PrivacyManager_EPrivacyFurtherInfoWithoutConsent') }}
          </a>
        </li>
      </ul>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
} from 'CoreHome';

export default defineComponent({
  components: {
    ContentBlock,
  },
});
</script>
