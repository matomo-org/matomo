<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <h2>
      Compliance
  </h2>

  <p>
    Select a site below to get an indication if the given site is compliant according to the
    indicated privacy law
  </p>

  <SiteSelector
    id="complianceDashboard"
    :switch-site-on-select="false"
    :show-selected-site="true"
    v-model="site"
  />

  <div>
    <ComplianceOverview
      v-for="type in complianceTypes"
      :key="type.id"
      :id-site="siteId"
      :compliance-type="type.id"
      :title="type.title"
      :description="type.description"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, watch } from 'vue';
import { SiteSelector, SiteRef } from '../../../../CoreHome/vue/src';
import ComplianceOverview from './ComplianceOverview.vue';
import Matomo from '../../../../CoreHome/vue/src/Matomo/Matomo';

export default defineComponent({
  components: {
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
        title: 'CNIL Compliance',
        description: 'This table provides an indication of whether certain settings align with CNIL guidance. It does not guarantee full legal compliance.',
      },
      {
        id: 'hipaa',
        title: 'HIPAA Compliance',
        description: 'This section outlines whether your analytics setup aligns with healthcare data protection requirements under HIPAA.',
      },
      {
        id: 'ccpa',
        title: 'CCPA Compliance',
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
