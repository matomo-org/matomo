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
import {
  defineComponent,
  ref,
  watch,
  onMounted,
} from 'vue';
import {
  EnrichedHeadline,
  Matomo,
  MatomoUrl,
  SiteSelector,
  SiteRef,
} from 'CoreHome';
import { fetchCompliancePolicies, CompliancePolicy } from './Compliance.store';
import ComplianceOverview from './ComplianceOverview.vue';

export default defineComponent({
  components: {
    EnrichedHeadline,
    ComplianceOverview,
    SiteSelector,
  },
  setup() {
    const site = ref<SiteRef>({
      id: Matomo.idSite ?? MatomoUrl.urlParsed.value.idSite,
      name: Matomo.helper.htmlDecode(Matomo.siteName),
    });
    const siteId = ref(String(Matomo.idSite ?? MatomoUrl.urlParsed.value.idSite));

    watch(site, (newSite) => {
      siteId.value = newSite?.id != null ? String(newSite.id) : '';
    });

    const complianceTypes = ref<CompliancePolicy[]>([]);
    onMounted(async () => {
      complianceTypes.value = await fetchCompliancePolicies();
    });

    return {
      site,
      siteId,
      complianceTypes,
    };
  },
});
</script>
