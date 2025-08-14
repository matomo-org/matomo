<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <h2>
    <EnrichedHeadline>{{ translate('PrivacyManager_Compliance') }}</EnrichedHeadline>
  </h2>

  <label for="complianceSite">{{ translate('PrivacyManager_ComplianceSelectSite') }}</label>
  <SiteSelector
    id="complianceSite"
    :switch-site-on-select="false"
    :show-selected-site="true"
    v-model="site"
  />

  <ComplianceOverview
    v-for="type in complianceTypes"
    :key="type.id"
    :id-site="siteId"
    :compliance-type="type.id"
    :title="type.title"
    :description="type.description"
  />
</template>

<script lang="ts">
import { defineComponent, ref, watch } from 'vue';
import {
  EnrichedHeadline,
  Matomo,
  SiteSelector,
  SiteRef,
} from 'CoreHome';
import ComplianceOverview from './ComplianceOverview.vue';

export default defineComponent({
  components: {
    EnrichedHeadline,
    ComplianceOverview,
    SiteSelector,
  },
  setup() {
    const site = ref<SiteRef>({
      id: Matomo.idSite,
      name: Matomo.helper.htmlDecode(Matomo.siteName),
    });
    const siteId = ref(String(Matomo.idSite));

    watch(site, (newSite) => {
      siteId.value = newSite?.id != null ? String(newSite.id) : '';
    });

    const complianceTypes = [
      {
        id: 'cnil',
        title: 'CNIL website analytics consent exemption conditions',
        description: 'This table provides an indication of whether certain settings align with CNIL guidance. It does not guarantee full legal compliance. To qualify for the consent exemption under CNIL rules, all required configurations must be implemented. If any setting is shown as “Non-Compliant,” the exemption conditions are not met, and consent must be obtained from users. If any setting is shown as “Unknown” Matomo cannot determine whether this requirement has been implemented. In such cases, these measures must be manually verified.',
      },
      {
        id: 'hipaa',
        title: 'HIPAA website analytics consent exemption conditions',
        description: 'This section outlines whether your analytics setup aligns with healthcare data protection requirements under HIPAA.',
      },
      {
        id: 'ccpa',
        title: 'CCPA website analytics consent exemption conditions',
        description: 'This overview checks how well your tracking policies meet California Consumer Privacy Act standards.',
      },
    ];

    return {
      site,
      siteId,
      complianceTypes,
    };
  },
});
</script>
