<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <h2>
    <EnrichedHeadline>{{ translate('PrivacyManager_Compliance') }}</EnrichedHeadline>
  </h2>

  <div class="complianceScope">
    <Field
      uicontrol="radio"
      name="complianceScope"
      :title="translate('PrivacyManager_ComplianceApplySettingsTo')"
      :options="scopeOptions"
      v-model="scope"
    />

    <div
      class="complianceScopeSite"
      v-if="isSingleWebsiteScope"
    >
      <SiteSelector
        id="complianceSite"
        :switch-site-on-select="false"
        :show-selected-site="true"
        :show-all-sites-item="false"
        :placeholder="translate('PrivacyManager_SelectWebsite')"
        v-model="site"
      />
    </div>

    <p class="complianceScopeOverrideNote">
      {{ translate('PrivacyManager_ComplianceScopeOverrideNote') }}
    </p>
    <p
      class="complianceScopeNotice"
      v-if="scopeNotice"
    >
      {{ scopeNotice }}
    </p>
  </div>

  <template v-if="siteId">
    <template v-if="granularComplianceEnabled">
      <GranularComplianceOverview
        v-for="type in complianceTypes"
        :key="type.id"
        :id-site="siteId"
        :compliance-type="type.id"
        :title="type.title"
      />
    </template>
    <template v-else>
      <ComplianceOverview
        v-for="type in complianceTypes"
        :key="type.id"
        :id-site="siteId"
        :compliance-type="type.id"
        :title="type.title"
        :description="type.description"
      />
    </template>
  </template>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  ref,
  onMounted,
} from 'vue';
import {
  EnrichedHeadline,
  Matomo,
  MatomoUrl,
  SiteSelector,
  SiteRef,
  translate,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import { fetchCompliancePolicies, CompliancePolicy } from './Compliance.store';
import ComplianceOverview from './ComplianceOverview.vue';
import GranularComplianceOverview from './GranularComplianceOverview.vue';

const SCOPE_ALL_WEBSITES = 'all';
const SCOPE_SINGLE_WEBSITE = 'site';

export default defineComponent({
  props: {
    granularComplianceEnabled: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    EnrichedHeadline,
    ComplianceOverview,
    Field,
    GranularComplianceOverview,
    SiteSelector,
  },
  setup() {
    // every website is configured unless `complianceScope` explicitly asks for the
    // single website in `idSite`. A dedicated parameter is needed because the admin menu
    // always rewrites a menu entry's `idSite` to a numeric site id, so `idSite` on its own
    // cannot tell a deep link to one website apart from ordinary menu navigation.
    // parsed query values are typed as unknown, so normalise to the string SiteRef expects
    const requestedIdSite = `${MatomoUrl.urlParsed.value.idSite ?? ''}`;
    const isSingleWebsiteRequested = MatomoUrl.urlParsed.value.complianceScope
        === SCOPE_SINGLE_WEBSITE
      && !!requestedIdSite
      && requestedIdSite !== SCOPE_ALL_WEBSITES;

    const scope = ref(
      isSingleWebsiteRequested ? SCOPE_SINGLE_WEBSITE : SCOPE_ALL_WEBSITES,
    );
    const site = ref<SiteRef|null>(isSingleWebsiteRequested ? {
      id: requestedIdSite,
      name: Matomo.siteName ? Matomo.helper.htmlDecode(Matomo.siteName) : '',
    } : null);

    const scopeOptions = [
      {
        key: SCOPE_ALL_WEBSITES,
        value: translate('General_MultiSitesSummary'),
      },
      {
        key: SCOPE_SINGLE_WEBSITE,
        value: translate('PrivacyManager_ComplianceScopeSingleWebsite'),
      },
    ];

    const isSingleWebsiteScope = computed(() => scope.value === SCOPE_SINGLE_WEBSITE);

    // empty while a single website is being configured but none has been picked yet,
    // which keeps the compliance overviews hidden until the scope is unambiguous
    const siteId = computed(() => {
      if (!isSingleWebsiteScope.value) {
        return SCOPE_ALL_WEBSITES;
      }

      return site.value?.id != null ? `${site.value.id}` : '';
    });

    const scopeNotice = computed(() => {
      if (!isSingleWebsiteScope.value) {
        return translate('PrivacyManager_ComplianceScopeAllWebsitesNotice');
      }

      if (!site.value?.name) {
        return '';
      }

      return translate(
        'PrivacyManager_ComplianceScopeSingleWebsiteNotice',
        site.value.name,
      );
    });

    const complianceTypes = ref<CompliancePolicy[]>([]);
    onMounted(async () => {
      complianceTypes.value = await fetchCompliancePolicies();
    });

    return {
      scope,
      scopeOptions,
      isSingleWebsiteScope,
      scopeNotice,
      site,
      siteId,
      complianceTypes,
    };
  },
});
</script>
