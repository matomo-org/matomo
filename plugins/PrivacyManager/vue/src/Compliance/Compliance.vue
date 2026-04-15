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
  SitesStore,
  SiteSelector,
  SiteRef,
  translate,
} from 'CoreHome';
import { fetchCompliancePolicies, CompliancePolicy } from './Compliance.store';
import ComplianceOverview from './ComplianceOverview.vue';

function getInitialSite(urlIdSite: string|number|undefined, multiSitesSummary: string): SiteRef {
  if (urlIdSite != null && urlIdSite !== '') {
    if (String(urlIdSite) === 'all') {
      return { id: 'all', name: multiSitesSummary };
    }

    if (String(Matomo.idSite) === String(urlIdSite) && Matomo.siteName) {
      return {
        id: urlIdSite,
        name: Matomo.helper.htmlDecode(Matomo.siteName),
      };
    }

    return { id: urlIdSite, name: String(urlIdSite) };
  }

  return { id: 'all', name: multiSitesSummary };
}

function getSiteFromUrlId(
  urlIdSite: string|number,
  sites: ReadonlyArray<{ idsite: string|number; name: string }>,
  multiSitesSummary: string,
): SiteRef {
  if (String(urlIdSite) === 'all') {
    return { id: 'all', name: multiSitesSummary };
  }

  const selectedSite = sites.find(
    (availableSite) => String(availableSite.idsite) === String(urlIdSite),
  );
  if (selectedSite) {
    return { id: selectedSite.idsite, name: selectedSite.name };
  }

  return getInitialSite(urlIdSite, multiSitesSummary);
}

export default defineComponent({
  components: {
    EnrichedHeadline,
    ComplianceOverview,
    SiteSelector,
  },
  setup() {
    const multiSitesSummary = translate('General_MultiSitesSummary');
    const urlIdSite = MatomoUrl.urlParsed.value.idSite as string|number|undefined;
    const site = ref<SiteRef>(getInitialSite(urlIdSite, multiSitesSummary));
    const siteId = ref(urlIdSite != null && urlIdSite !== '' ? String(urlIdSite) : '');

    watch(site, (newSite) => {
      siteId.value = newSite?.id != null ? String(newSite.id) : '';
    });

    const complianceTypes = ref<CompliancePolicy[]>([]);
    onMounted(async () => {
      const [policies, sites] = await Promise.all([
        fetchCompliancePolicies(),
        SitesStore.loadInitialSites(),
      ]);

      complianceTypes.value = policies;

      if (urlIdSite != null && urlIdSite !== '') {
        site.value = getSiteFromUrlId(urlIdSite, sites || [], multiSitesSummary);
        return;
      }

      if (sites?.length === 1) {
        site.value = { id: sites[0].idsite, name: sites[0].name };
        return;
      }

      if (sites?.length) {
        site.value = { id: 'all', name: multiSitesSummary };
      }
    });

    return {
      site,
      siteId,
      complianceTypes,
    };
  },
});
</script>
