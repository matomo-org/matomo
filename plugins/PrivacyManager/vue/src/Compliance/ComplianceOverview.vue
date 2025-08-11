<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="title">
    <p>{{ description }}</p>
    <ComplianceTable
      v-if="!state.loading"
      :results="state.compliance_indicators"
    />
    <ActivityIndicator :loading="state.loading"/>
    <Field
      v-if="!state.loading"
      uicontrol="checkbox"
      name="enableFeature"
      :title="translate('PrivacyManager_ComplianceEnforceCheckboxIntro')"
      :introduction="translate('PrivacyManager_ComplianceEnforceCheckboxTitle')"
      :inline-help="translate('PrivacyManager_ComplianceEnforceCheckboxHelp')"
      v-model="isComplianceModeEnabled"
    />
    <SaveButton
      @confirm="this.showPasswordConfirmation = true"
      value="Save"
    />
    <PasswordConfirmation
      :model-value="this.showPasswordConfirmation"
      :passwordFieldId="'password' + complianceType"
      @confirmed="saveSettings"
    />
  </ContentBlock>
</template>

<script lang="ts">

import {
  defineComponent, watch, ref, toRaw,
} from 'vue';
import { ActivityIndicator, ContentBlock } from '../../../../CoreHome/vue/src';
import ComplianceTable from './ComplianceTable.vue';
import { createComplianceStore } from './Compliance.store';
import { Field, PasswordConfirmation, SaveButton } from '../../../../CorePluginsAdmin/vue/src';

export default defineComponent({
  props: {
    idSite: {
      type: String,
      required: true,
    },
    complianceType: {
      type: String,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      required: true,
    },
  },
  components: {
    PasswordConfirmation,
    SaveButton,
    Field,
    ActivityIndicator,
    ComplianceTable,
    ContentBlock,
  },
  methods: {
    saveSettings() {
      this.saveComplianceStatus(this.isComplianceModeEnabled);
      this.showPasswordConfirmation = false;
    },
  },
  setup(props) {
    const store = createComplianceStore(props.complianceType);
    store.setIdSite(props.idSite);
    watch(
      () => props.idSite,
      (newSite) => {
        if (newSite) {
          store.setIdSite(newSite);
        }
      },
      { immediate: true },
    );

    return {
      state: store.state,
      saveComplianceStatus: store.saveComplianceStatus,
      isComplianceModeEnabled: store.state.compliance_mode_enabled,
      showPasswordConfirmation: ref(false),
    };
  },
});
</script>
