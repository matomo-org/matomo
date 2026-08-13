<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="granularComplianceSaveNotifications"
    ref="saveNotifications"
    v-if="state.saveError || state.saveSuccess"
  >
    <div v-if="state.saveError" class="notification system notification-error">
      {{ state.saveError }}
    </div>
    <div v-if="state.saveSuccess" class="notification system notification-success">
      {{ translate('PrivacyManager_ComplianceSettingsSaved') }}
    </div>
  </div>
  <ContentBlock :content-title="title">
    <div class="granularComplianceHeaderActions" v-if="canSave">
      <SaveButton
        :class="'granularCompliance-' + complianceType + '-save-top'"
        :disabled="!hasUnsavedChanges"
        :saving="state.saving"
        @confirm="showPasswordConfirmation = true"
      />
    </div>
    <p v-html="$sanitize(description)" />
    <p class="granularComplianceStatusExplanation">
      {{ translate('PrivacyManager_ComplianceStatusExplanation') }}
    </p>
    <ActivityIndicator :loading="state.loading"/>
    <template v-if="!state.loading">
      <div v-if="state.fetchError" class="notification system notification-error">
          {{ translate('General_ErrorTryAgain') }}
          {{ translate('General_ExceptionContactSupportGeneric', ['','']) }}
      </div>
      <template v-else>
        <div class="granularComplianceEnforceAll" v-if="canSave">
          <div class="granularComplianceEnforceAllText">
            <h3>{{ translate('PrivacyManager_ComplianceEnforceAllTitle') }}</h3>
            <p>{{ translate('PrivacyManager_ComplianceEnforceAllDescription') }}</p>
          </div>
          <button
            type="button"
            class="btn granularComplianceEnforceAllButton"
            :disabled="state.saving || state.loading"
            @click="enforceAll()"
          >
            {{ translate('PrivacyManager_ComplianceEnforceAllSettings') }}
          </button>
        </div>
        <GranularComplianceTable
          :settings="toggleableSettings"
          :local-enforced="state.localEnforced"
          :dirty-setting-ids="dirtySettingIds"
          :disabled="state.configControlled || state.saving"
          @toggle="toggleSetting($event)"
        />
        <h3 class="granularComplianceExternalTitle">
          {{ translate('PrivacyManager_ComplianceManagedOutsideTitle') }}
        </h3>
        <GranularComplianceTable
          :settings="externalSettings"
          :show-toggles="false"
        />
        <template v-if="canSave">
          <SaveButton
            :class="'granularCompliance-' + complianceType + '-save'"
            :disabled="!hasUnsavedChanges"
            :saving="state.saving"
            @confirm="showPasswordConfirmation = true"
          />
          <PasswordConfirmation
            :model-value="showPasswordConfirmation"
            :passwordFieldId="'passwordGranular' + complianceType"
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
  computed,
  defineComponent,
  nextTick,
  ref,
  watch,
} from 'vue';
import { ActivityIndicator, ContentBlock } from 'CoreHome';
import { PasswordConfirmation, SaveButton } from 'CorePluginsAdmin';
import { createGranularComplianceStore } from './GranularCompliance.store';
import GranularComplianceTable from './GranularComplianceTable.vue';

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
    ActivityIndicator,
    ContentBlock,
    GranularComplianceTable,
    PasswordConfirmation,
    SaveButton,
  },
  methods: {
    saveSettings(password: string) {
      this.save(password);
      this.showPasswordConfirmation = false;
    },
    resetSave() {
      this.showPasswordConfirmation = false;
    },
  },
  setup(props) {
    const store = createGranularComplianceStore(props.complianceType);

    watch(
      () => props.idSite,
      (newSite) => {
        if (newSite) {
          store.setIdSite(newSite);
        }
      },
      { immediate: true },
    );

    const toggleableSettings = computed(
      () => store.state.settings.filter((s) => s.section === 'settings'),
    );
    const externalSettings = computed(
      () => store.state.settings.filter((s) => s.section === 'external'),
    );
    const hasUnsavedChanges = computed(() => store.dirtySettingIds.value.length > 0);
    const canSave = computed(() => !store.state.configControlled);

    // the save buttons can sit far below the notification area, so bring the
    // outcome of a save into view once it is rendered
    const saveNotifications = ref<HTMLElement | null>(null);
    watch(
      () => [store.state.saveError, store.state.saveSuccess] as const,
      ([saveError, saveSuccess]) => {
        if (saveError || saveSuccess) {
          nextTick(() => {
            saveNotifications.value?.scrollIntoView({ behavior: 'smooth', block: 'center' });
          });
        }
      },
    );

    return {
      saveNotifications,
      state: store.state,
      dirtySettingIds: store.dirtySettingIds,
      toggleSetting: store.toggleSetting,
      enforceAll: store.enforceAll,
      save: store.save,
      toggleableSettings,
      externalSettings,
      hasUnsavedChanges,
      canSave,
      showPasswordConfirmation: ref(false),
    };
  },
});
</script>
