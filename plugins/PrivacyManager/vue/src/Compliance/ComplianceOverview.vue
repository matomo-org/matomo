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
      :results="state.complianceRequirements"
    />
    <ActivityIndicator :loading="state.loading"/>
    <Field
      v-if="!state.loading"
      uicontrol="checkbox"
      :name="idSite + '_' + complianceType +  '_enableFeature'"
      :title="translate('PrivacyManager_ComplianceEnforceCheckboxIntro')"
      :introduction="translate('PrivacyManager_ComplianceEnforceCheckboxTitle')"
      :inline-help="translate('PrivacyManager_ComplianceEnforceCheckboxHelp')"
      v-model="shouldEnforceComplianceMode"
    />
    <SaveButton
      v-if="!state.loading"
      @confirm="this.showPasswordConfirmation = true"
      :value="translate('General_Save')"
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
  defineComponent, watch, ref,
} from 'vue';
import { ActivityIndicator, ContentBlock } from 'CoreHome';
import { Field, PasswordConfirmation, SaveButton } from 'CorePluginsAdmin';
import { createComplianceStore } from './Compliance.store';
import ComplianceTable from './ComplianceTable.vue';

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
      this.saveComplianceStatus(this.shouldEnforceComplianceMode);
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
      shouldEnforceComplianceMode: store.state.complianceModeEnforced,
      showPasswordConfirmation: ref(false),
    };
  },
});
</script>
