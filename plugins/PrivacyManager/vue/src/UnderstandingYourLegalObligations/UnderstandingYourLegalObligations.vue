<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="understandingYourLegalObligations">
    <ContentBlock
      :content-title="translate('PrivacyManager_UnderstandingYourLegalObligations')"
    >
      <p>
        {{ translate('PrivacyManager_UnderstandingYourLegalObligationsIntro1') }}
        <br /><br />
        {{ translate('PrivacyManager_UnderstandingYourLegalObligationsIntro2') }}
        <br /><br />
        {{ translate('PrivacyManager_UnderstandingYourLegalObligationsIntro3') }}
        <br /><br />
        {{ translate('PrivacyManager_UnderstandingYourLegalObligationsIntro4') }}
      </p>
      <p>{{ translate('PrivacyManager_UnderstandingYourLegalObligationsIntro5') }}</p>

      <ul class="browser-default">
        <li
          v-html="$sanitize(
            translate('PrivacyManager_UnderstandingYourLegalObligationsBulletStrictConsent')
          )"
        ></li>
        <li v-html="$sanitize(exemptionsBullet)"></li>
        <li
          v-html="$sanitize(
            translate('PrivacyManager_UnderstandingYourLegalObligationsBulletPersonalDataOnly')
          )"
        ></li>
        <li
          v-html="$sanitize(
            translate('PrivacyManager_UnderstandingYourLegalObligationsBulletTransparencyOptOut')
          )"
        ></li>
        <li
          v-html="$sanitize(
            translate(
              'PrivacyManager_UnderstandingYourLegalObligationsBulletNoComplianceRequirements'
            )
          )"
        ></li>
      </ul>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  externalLink,
  translate,
} from 'CoreHome';

export default defineComponent({
  components: {
    ContentBlock,
  },
  computed: {
    exemptionsBullet() {
      return translate(
        'PrivacyManager_UnderstandingYourLegalObligationsBulletExemptions',
        externalLink(
          'https://matomo.org/faq/how-to/how-do-i-configure-matomo-without-tracking-consent-for-french-visitors-cnil-exemption/',
        ),
        '</a>',
      );
    },
  },
  methods: {
    translate,
  },
});
</script>
