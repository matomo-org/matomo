<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="hybridTrackingSaveNotifications"
    v-if="state.saveError || state.saveSuccess"
  >
    <div v-if="state.saveError" class="notification system notification-error">
      {{ state.saveError }}
    </div>
    <div v-if="state.saveSuccess" class="notification system notification-success">
      {{ translate('PrivacyManager_ComplianceSettingsSaved') }}
    </div>
  </div>
  <ContentBlock
    class="hybridTracking"
    :content-title="translate('PrivacyManager_ComplianceHybridTrackingTitle')"
  >
    <p>{{ translate('PrivacyManager_ComplianceHybridTrackingDescription') }}</p>
    <p>{{ translate('PrivacyManager_ComplianceHybridTrackingRequirementsIntro') }}</p>
    <ul class="hybridTrackingRequirements">
      <li>{{ translate('PrivacyManager_ComplianceHybridTrackingRequirementPolicy') }}</li>
      <li v-html="$sanitize(requirementConsent)" />
    </ul>
    <ActivityIndicator :loading="state.loading"/>
    <template v-if="!state.loading">
      <div v-if="state.fetchError" class="notification system notification-error">
        {{ translate('General_ErrorTryAgain') }}
        {{ translate('General_ExceptionContactSupportGeneric', ['','']) }}
      </div>
      <template v-else>
        <div class="hybridTrackingToggle">
          <div class="hybridTrackingToggleText">
            <h3>
              <label for="hybridTrackingEnabled">
                {{ translate('PrivacyManager_ComplianceHybridTrackingToggleTitle') }}
              </label>
            </h3>
            <p>{{ toggleHelp }}</p>
            <p
              class="hybridTrackingEnabledForAllWebsites"
              v-if="isInheritedFromAllWebsites"
            >
              {{ translate('PrivacyManager_ComplianceHybridTrackingEnabledForAllWebsites') }}
            </p>
          </div>
          <div class="switch">
            <label>
              <input
                type="checkbox"
                id="hybridTrackingEnabled"
                :checked="isToggledOn"
                :disabled="!canToggle"
                @change="toggle()"
              />
              <span class="lever"></span>
            </label>
          </div>
        </div>
        <template v-if="canToggle">
          <SaveButton
            class="hybridTrackingSave"
            :disabled="!hasUnsavedChanges"
            :saving="state.saving"
            @confirm="showPasswordConfirmation = true"
          />
          <PasswordConfirmation
            :model-value="showPasswordConfirmation"
            passwordFieldId="passwordHybridTracking"
            @confirmed="saveSettings"
            @aborted="showPasswordConfirmation = false"
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
  ref,
  watch,
} from 'vue';
import {
  ActivityIndicator,
  ContentBlock,
  externalLink,
  translate,
} from 'CoreHome';
import { PasswordConfirmation, SaveButton } from 'CorePluginsAdmin';
import { createHybridTrackingStore } from './HybridTracking.store';

// TODO: replace with the address of the hybrid tracking setup guide once documentation provides it
const SETUP_GUIDE_URL = 'https://matomo.org/faq/how-to/hybrid-tracking/';

export default defineComponent({
  props: {
    idSite: {
      type: String,
      required: true,
    },
  },
  components: {
    ActivityIndicator,
    ContentBlock,
    PasswordConfirmation,
    SaveButton,
  },
  methods: {
    saveSettings(password: string) {
      this.save(password);
      this.showPasswordConfirmation = false;
    },
  },
  setup(props) {
    const store = createHybridTrackingStore();

    watch(
      () => props.idSite,
      (newSite) => {
        if (newSite) {
          store.setIdSite(newSite);
        }
      },
      { immediate: true },
    );

    const isAllWebsites = computed(() => props.idSite === 'all');

    // a website measures hybrid tracking when it is on for every website, and cannot turn it
    // off for itself then
    const isInheritedFromAllWebsites = computed(
      () => !isAllWebsites.value && store.state.enabledForAllWebsites,
    );
    const isToggledOn = computed(
      () => isInheritedFromAllWebsites.value || store.state.localEnabled,
    );
    const canToggle = computed(
      () => store.state.isWritable && !isInheritedFromAllWebsites.value,
    );

    const toggleHelp = computed(() => translate(isAllWebsites.value
      ? 'PrivacyManager_ComplianceHybridTrackingToggleHelpAllWebsites'
      : 'PrivacyManager_ComplianceHybridTrackingToggleHelpSingleWebsite'));

    const requirementConsent = translate(
      'PrivacyManager_ComplianceHybridTrackingRequirementConsent',
      externalLink(SETUP_GUIDE_URL),
      '</a>',
    );

    return {
      state: store.state,
      hasUnsavedChanges: store.hasUnsavedChanges,
      toggle: store.toggle,
      save: store.save,
      isInheritedFromAllWebsites,
      isToggledOn,
      canToggle,
      toggleHelp,
      requirementConsent,
      showPasswordConfirmation: ref(false),
    };
  },
});
</script>
