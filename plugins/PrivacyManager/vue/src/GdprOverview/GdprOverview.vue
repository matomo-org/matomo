<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="gdprOverview">
    <div v-content-intro>
      <h2>{{ translate('PrivacyManager_GdprOverview') }}</h2>
      <p>{{ translate('PrivacyManager_GdprOverviewIntro1') }}</p>
      <ul>
        <li>{{ translate('PrivacyManager_GdprOverviewKeyPoint1') }}</li>
        <li>{{ translate('PrivacyManager_GdprOverviewIntro3') }}</li>
        <li>{{ translate('PrivacyManager_GdprOverviewIntro4') }}</li>
      </ul>
      <p>{{ translate('PrivacyManager_GdprOverviewMatomoPersonalData') }}</p>
      <p>{{ translate('PrivacyManager_GdprOverviewApplicabilityIntro') }}</p>
      <ul>
        <li>{{ translate('PrivacyManager_GdprOverviewApplicabilityCondition1') }}</li>
        <li>
          {{ translate('PrivacyManager_GdprOverviewApplicabilityCondition2') }}
          <ul>
            <li>{{ translate('PrivacyManager_GdprOverviewApplicabilityCondition2Detail1') }}</li>
            <li>{{ translate('PrivacyManager_GdprOverviewApplicabilityCondition2Detail2') }}</li>
          </ul>
        </li>
      </ul>
      <p>{{ translate('PrivacyManager_GdprOverviewIntro2') }}</p>
    </div>

    <VueEntryContainer :html="afterGDPROverviewIntroContent"/>

    <ContentBlock :content-title="translate('PrivacyManager_DataProcessingAgreement')">
      <p><span v-html="$sanitize(dataProcessingAgreementIntro1)"></span></p>
      <p>{{ translate('PrivacyManager_DataProcessingAgreementIntro2') }}</p>
    </ContentBlock>

    <ContentBlock :content-title="translate('PrivacyManager_GdprChecklists')">
      <p>
        {{ translate('PrivacyManager_GdprChecklistDesc1') }}
        <br /><br />
        <span v-html="$sanitize(gdprChecklistDesc2)"></span>
      </p>
    </ContentBlock>

    <ContentBlock :content-title="translate('PrivacyManager_IndividualsRights')">
      <p>{{ translate('PrivacyManager_IndividualsRightsIntro') }}</p>
      <ol>
        <li>{{ translate('PrivacyManager_IndividualsRightsInform') }}</li>
        <li v-html="$sanitize(rightsLinkText('IndividualsRightsAccess'))"></li>
        <li v-html="$sanitize(rightsLinkText('IndividualsRightsErasure'))"></li>
        <li v-html="$sanitize(rightsLinkText('IndividualsRightsRectification'))"></li>
        <li v-html="$sanitize(rightsLinkText('IndividualsRightsPortability'))"></li>
        <li v-html="$sanitize(rightsLinkText('IndividualsRightsObject', 'usersOptOut'))"></li>
        <li>{{ translate('PrivacyManager_IndividualsRightsChildren') }}</li>
      </ol>
    </ContentBlock>

    <ContentBlock :content-title="translate('PrivacyManager_AwarenessDocumentation')">
      <p>{{ translate('PrivacyManager_AwarenessDocumentationIntro') }}</p>
      <ol>
        <li>{{ translate('PrivacyManager_AwarenessDocumentationDesc1') }}</li>
        <li>{{ translate('PrivacyManager_AwarenessDocumentationDesc2') }}</li>
        <li v-html="$sanitize(awarenessDocumentationDesc3)"></li>
        <li v-html="$sanitize(awarenessDocumentationDesc4)"></li>
      </ol>
    </ContentBlock>

    <ContentBlock :content-title="translate('PrivacyManager_SecurityProcedures')">
      <p>{{ translate('PrivacyManager_SecurityProceduresIntro') }}</p>
      <ol>
        <li v-html="$sanitize(securityProceduresDesc1)"></li>
        <li v-html="$sanitize(securityProceduresDesc2)"></li>
        <li v-html="$sanitize(securityProceduresDesc3)"></li>
        <li v-html="$sanitize(securityProceduresDesc4)"></li>
      </ol>
    </ContentBlock>

    <ContentBlock :content-title="translate('PrivacyManager_DataRetention')">
      <p>{{ translate('PrivacyManager_DataRetentionInMatomo') }}</p>
      <ul>
        <li
          v-if="deleteLogsEnable"
          v-html="$sanitize(translate(
            'PrivacyManager_RawDataRemovedAfter',
            `<strong>${rawDataRetention}</strong>`,
          ))"
        ></li>
        <li
          v-else
          v-html="$sanitize(translate('PrivacyManager_RawDataNeverRemoved'))"
        ></li>
        <li
          v-if="deleteReportsEnable"
          v-html="$sanitize(translate(
            'PrivacyManager_ReportsRemovedAfter',
            `<strong>${reportRetention}</strong>`,
          ))"
        ></li>
        <li
          v-else
          v-html="$sanitize(translate('PrivacyManager_ReportsNeverRemoved'))"
        ></li>
      </ul>
      <p>
        <br />
        {{ translate('PrivacyManager_DataRetentionOverall') }}
      </p>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  VueEntryContainer,
  ContentIntro,
  externalLink,
  MatomoUrl,
  translate,
} from 'CoreHome';

function externalLinkTranslate(tokenSuffix: string, url: string) {
  return translate(
    `PrivacyManager_${tokenSuffix}`,
    externalLink(url),
    '</a>',
  );
}

export default defineComponent({
  props: {
    afterGDPROverviewIntroContent: String,
    deleteLogsEnable: Boolean,
    deleteReportsEnable: Boolean,
    rawDataRetention: null,
    reportRetention: null,
  },
  components: {
    ContentBlock,
    VueEntryContainer,
  },
  directives: {
    ContentIntro,
  },
  computed: {
    dataProcessingAgreementIntro1() {
      return translate(
        'PrivacyManager_DataProcessingAgreementIntro1Linked',
        externalLink('https://matomo.org/matomo-cloud-dpa/'),
        '</a>',
      );
    },
    gdprChecklistDesc2() {
      return externalLinkTranslate(
        'GdprChecklistDesc2',
        'https://matomo.org/guide/manage-matomo/privacy/',
      );
    },
    awarenessDocumentationDesc3() {
      return externalLinkTranslate(
        'AwarenessDocumentationDesc3',
        'https://matomo.org/faq/general/faq_18254/',
      );
    },
    awarenessDocumentationDesc4() {
      return externalLinkTranslate(
        'AwarenessDocumentationDesc4',
        'https://matomo.org/blog/2018/04/gdpr-how-to-fill-in-the-information-asset-register-when-using-matomo/',
      );
    },
    securityProceduresDesc1() {
      return externalLinkTranslate(
        'SecurityProceduresDesc1',
        'https://matomo.org/docs/security/',
      );
    },
    securityProceduresDesc2() {
      return externalLinkTranslate(
        'SecurityProceduresDesc2',
        'https://ico.org.uk/for-organisations/uk-gdpr-guidance-and-resources/international-transfers/a-guide-to-international-transfers/',
      );
    },
    securityProceduresDesc3() {
      return externalLinkTranslate(
        'SecurityProceduresDesc3',
        'https://ico.org.uk/for-organisations/report-a-breach/personal-data-breach/personal-data-breaches-a-guide/',
      );
    },
    securityProceduresDesc4() {
      return externalLinkTranslate(
        'SecurityProceduresDesc4',
        'https://www.cnil.fr/en/guidelines-dpia',
      );
    },
  },
  methods: {
    rightsLinkText(tokenSuffix: string, action = 'gdprTools') {
      const link = `?${MatomoUrl.stringify({
        module: 'PrivacyManager',
        action,
      })}`;

      return translate(
        `PrivacyManager_${tokenSuffix}`,
        `<a target="_blank" rel="noreferrer noopener" href="${link}">`,
        '</a>',
      );
    },
  },
});
</script>
