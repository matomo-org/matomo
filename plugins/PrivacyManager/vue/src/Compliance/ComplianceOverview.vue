<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="title">
  <p v-html="$sanitize(description)" />
    <ActivityIndicator :loading="state.loading"/>
    <template v-if="!state.loading">
      <div v-if="state.fetchComplianceError" class="notification system notification-error">
          {{ translate('General_ErrorTryAgain') }}
          {{ translate('General_ExceptionContactSupportGeneric', ['','']) }}
      </div>
      <template v-else>
        <ComplianceTable
          :results="state.complianceRequirements"
        />
        <template v-if="!state.complianceConfigControlled">
          <Field
            uicontrol="checkbox"
            :name="'site-' + idSite + '-' + complianceType +  '-enableFeature'"
            :title="translate('PrivacyManager_ComplianceEnforceCheckboxIntro')"
            :introduction="translate('PrivacyManager_ComplianceEnforceCheckboxTitle')"
            :inline-help="translate('PrivacyManager_ComplianceEnforceCheckboxHelp')"
            v-model="shouldEnforceComplianceMode"
          />
          <div v-if="state.saveComplianceError" class="notification system notification-error">
              {{ translate('General_ErrorTryAgain') }}
              {{ translate('General_ExceptionContactSupportGeneric', ['','']) }}
          </div>
          <SaveButton
            :class="'site-' + idSite + '-' + complianceType +  '-save'"
            @confirm="this.showPasswordConfirmation = true"
            :value="translate('General_Save')"
          />
          <PasswordConfirmation
            :model-value="this.showPasswordConfirmation"
            :passwordFieldId="'password' + complianceType"
            @confirmed="saveSettings"
            @aborted="resetSave"
          />
        </template>
      </template>
    </template>
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
    saveSettings(password: string) {
      this.saveComplianceStatus(this.shouldEnforceComplianceMode, password);
      this.showPasswordConfirmation = false;
    },
    resetSave() {
      this.showPasswordConfirmation = false;
    },
  },
  setup(props) {
    const store = createComplianceStore(props.complianceType);
    store.setIdSite(props.idSite);

    // mirror store.complianceModeEnforced into a local writable ref
    const shouldEnforceComplianceMode = ref(false);
    // keep local ref in sync with store (on first load and any later fetches)
    watch(
      () => store.state.complianceModeEnforced,
      (val) => { shouldEnforceComplianceMode.value = val; },
      { immediate: true },
    );

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
      shouldEnforceComplianceMode,
      showPasswordConfirmation: ref(false),
    };
  },
});
</script>
