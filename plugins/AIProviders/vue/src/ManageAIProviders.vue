<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="ai-providers-page">
    <header class="ai-providers-page-header">
      <h2 class="ai-providers-page-title">
        <EnrichedHeadline>
          {{ translate('AIProviders_MenuTitle') }}
        </EnrichedHeadline>
      </h2>
      <p class="ai-providers-page-subtitle">
        {{ translate('AIProviders_ConfigurationIntro') }}
      </p>
    </header>

    <ActivityIndicator
      v-if="isLoading"
      :loading="isLoading"
    />

    <template v-else-if="settings">
      <ContentBlock class="ai-providers-content">
      <span
        class="ai-providers-unsaved-changes"
        :class="{ 'is-visible': hasUnsavedChanges }"
      >
        {{ translate('AIProviders_UnsavedChanges') }}
      </span>
      <div v-form class="ai-providers">
        <Alert
          v-if="!canEditProviderConfiguration"
          severity="info"
        >
          {{ translate('AIProviders_ManagedConfigurationHelp') }}
        </Alert>

        <h3 class="ai-providers-defaults-title">
          {{ translate('AIProviders_DefaultsTitle') }}
        </h3>

        <section class="ai-providers-section">
          <h4 class="ai-providers-subsection-title">
            {{ translate('AIProviders_DefaultProvider') }}
          </h4>
          <p class="ai-providers-section-help">
            {{ translate('AIProviders_DefaultProviderHelp') }}
          </p>

          <div
            :aria-label="translate('AIProviders_DefaultProvider')"
            class="ai-providers-cards"
            role="radiogroup"
          >
            <ProviderCard
              v-for="provider in providers"
              :key="provider.id"
              :available-models="availableModels[provider.id] || []"
              :can-edit="canEditProviderConfiguration"
              :configuration="providerConfigurations[provider.id]"
              :is-disconnecting="!!disconnectingProviders[provider.id]"
              :is-testing="!!testingProviders[provider.id]"
              :provider="provider"
              :selected="defaultProviderId === provider.id"
              :usable-as-default="provider.configuration.isUsable"
              @disconnect="disconnectProvider(provider.id)"
              @select="provider.configuration.isUsable ? defaultProviderId = provider.id : null"
              @test="testConnection(provider.id)"
              @update:api-key="updateApiKey(provider.id, $event)"
              @update:endpoint-url="updateEndpointUrl(provider.id, $event)"
              @update:model="updateModel(provider.id, $event)"
              @update:use-fips-endpoint="updateUseFipsEndpoint(provider.id, $event)"
            />
          </div>

          <Alert
            v-if="!hasUsableProvider"
            class="ai-providers-default-warning"
            severity="warning"
          >
            {{ translate('AIProviders_NoDefaultProviderWarning') }}
          </Alert>
        </section>

        <section
          v-if="canEditCapabilityLevel"
          class="ai-providers-section"
        >
          <h4 class="ai-providers-subsection-title">
            {{ translate('AIProviders_DefaultCapabilityLevel') }}
          </h4>
          <p class="ai-providers-section-help">
            {{ translate('AIProviders_DefaultCapabilityLevelHelp') }}
          </p>

          <div
            :aria-label="translate('AIProviders_DefaultCapabilityLevel')"
            class="ai-providers-capability-cards"
            role="radiogroup"
          >
            <label
              v-for="capability in capabilityLevelOptions"
              :key="capability.id"
              :class="{ 'is-selected': defaultCapabilityLevel === capability.id }"
              class="ai-providers-capability-card"
            >
              <div class="ai-providers-capability-header">
                <input
                  v-model="defaultCapabilityLevel"
                  :value="capability.id"
                  name="defaultCapabilityLevel"
                  type="radio"
                />
                <span class="ai-providers-capability-label">{{ capability.label }}</span>
              </div>
              <div
                v-if="capability.description"
                class="ai-providers-capability-description"
              >
                {{ capability.description }}
              </div>
            </label>
          </div>
        </section>
      </div>
      </ContentBlock>
    </template>

    <div
      v-if="settings"
      class="ai-providers-footer"
    >
      <button
        :disabled="isSaving || !hasUnsavedChanges"
        class="btn btn-outline"
        type="button"
        @click="cancelChanges()"
      >
        {{ translate('General_Cancel') }}
      </button>
      <SaveButton
        :disabled="!hasUnsavedChanges"
        :saving="isSaving"
        @confirm="saveSettings()"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import {
  ActivityIndicator,
  AjaxHelper,
  Alert,
  ContentBlock,
  EnrichedHeadline,
  NotificationsStore,
  translate,
} from 'CoreHome';
import { Form as vForm, SaveButton } from 'CorePluginsAdmin';
import ProviderCard from './components/ProviderCard.vue';
import type {
  CapabilityLevelOption,
  ProviderConfiguration,
  Settings,
  TestConnectionResponse,
} from './types';

const settings = ref<Settings | null>(null);
const isLoading = ref(false);
const isSaving = ref(false);
const defaultProviderId = ref('');
const defaultCapabilityLevel = ref('');
const providerConfigurations = ref<Record<string, ProviderConfiguration>>({});
// Models discovered per provider from the last "test connection"/refresh, used
// to populate the model picker.
const availableModels = ref<Record<string, string[]>>({});
const testingProviders = ref<Record<string, boolean>>({});
const disconnectingProviders = ref<Record<string, boolean>>({});

const providers = computed(() => settings.value?.providers || []);
const hasUsableProvider = computed(() => providers.value.some(
  (provider) => provider.configuration.isUsable,
));
const canEditCapabilityLevel = computed(() => !!settings.value?.canEditCapabilityLevel);
const canEditProviderConfiguration = computed(() => !!settings.value?.canEditProviderConfiguration);

// Snapshot of the saved state, used to detect unsaved changes.
const savedSnapshot = ref('');

const capabilityLevelOptions = computed<CapabilityLevelOption[]>(() => {
  const capabilityLevels = settings.value?.capabilityLevels || {};

  return Object.entries(capabilityLevels).map(([id, keys]) => ({
    id,
    label: translate(keys.label),
    description: keys.description ? translate(keys.description) : '',
  }));
});

// Serializes everything the user can edit, so it can be compared to the saved snapshot.
function serializeEditableState() {
  return JSON.stringify({
    defaultProviderId: defaultProviderId.value,
    defaultCapabilityLevel: defaultCapabilityLevel.value,
    providerConfigurations: providerConfigurations.value,
  });
}

const hasUnsavedChanges = computed(() => (
  !!settings.value && serializeEditableState() !== savedSnapshot.value
));

/**
 * Applies the given settings to the component state.
 * @param nextSettings
 */
function applySettings(nextSettings: Settings) {
  settings.value = nextSettings;
  defaultProviderId.value = nextSettings.defaultProviderId;
  defaultCapabilityLevel.value = nextSettings.defaultCapabilityLevel;

  const nextProviderConfigurations: Record<string, ProviderConfiguration> = {};
  nextSettings.providers.forEach((provider) => {
    const savedModel = provider.configuration.model || '';
    nextProviderConfigurations[provider.id] = {
      apiKey: '',
      endpointUrl: provider.configuration.endpointUrl || '',
      model: savedModel,
      useFipsEndpoint: provider.configuration.useFipsEndpoint || false,
    };
  });
  providerConfigurations.value = nextProviderConfigurations;
  availableModels.value = {};

  // Capture the freshly applied state as the baseline for unsaved-change detection.
  savedSnapshot.value = serializeEditableState();
}

function markProviderUsable(providerId: string) {
  if (!settings.value) {
    return;
  }

  const provider = settings.value.providers.find((p) => p.id === providerId);
  if (provider) {
    provider.configuration = {
      ...provider.configuration,
      hasApiKey: true,
      isUsable: true,
    };
  }

  if (!defaultProviderId.value) {
    defaultProviderId.value = providerId;
  }
}

function getCleanErrorMessage(error: unknown) {
  let message = '';

  if (error && typeof error === 'object' && 'message' in error) {
    message = `${(error as { message: string }).message}`;
  } else {
    message = `${error}`;
  }

  return message
    .replace(/\s*#\d+\s+[\s\S]*$/, '')
    .replace(/\s+/g, ' ')
    .trim();
}

// Notification IDs may only contain word characters (alphanumerics + underscore),
// see core/Notification/Manager.php::checkId(). Provider IDs can contain other
// characters (e.g. hyphens), so sanitize before using them in a notification ID.
function notificationId(id: string) {
  return id.replace(/[^\w]/g, '_');
}

function showErrorNotification(error: unknown, id: string) {
  const cleaned = getCleanErrorMessage(error);
  const isUseful = cleaned && cleaned !== 'Something went wrong';
  const message = isUseful
    ? translate('AIProviders_RequestFailed', cleaned)
    : translate('AIProviders_UnexpectedError');

  return NotificationsStore.show({
    message,
    type: 'transient',
    id,
    context: 'error',
  });
}

function updateModel(providerId: string, model: string) {
  providerConfigurations.value[providerId] = {
    ...providerConfigurations.value[providerId],
    model,
  };
}

async function fetchAvailableModels(providerId: string, showNotifications: boolean) {
  testingProviders.value[providerId] = true;

  try {
    const response = await AjaxHelper.post<TestConnectionResponse>(
      {
        method: 'AIProviders.testConnection',
      },
      {
        providerId,
        providerConfiguration: JSON.stringify(providerConfigurations.value[providerId] || {}),
      },
      {
        withTokenInUrl: true,
        createErrorNotification: false,
      },
    );

    markProviderUsable(providerId);

    if (response.models && response.models.length) {
      availableModels.value[providerId] = response.models;

      // Default to the first discovered model when none is selected yet.
      if (!providerConfigurations.value[providerId]?.model) {
        updateModel(providerId, response.models[0]);
      }
    }

    if (showNotifications) {
      NotificationsStore.show({
        message: translate(
          'AIProviders_TestConnectionSuccess',
          response.providerName,
        ),
        type: 'transient',
        id: notificationId(`aiProvidersTest-${providerId}`),
        context: 'success',
      });
    }
  } catch (error) {
    if (showNotifications) {
      showErrorNotification(error, notificationId(`aiProvidersTestError-${providerId}`));
    }
  } finally {
    testingProviders.value[providerId] = false;
  }
}

async function loadSettings() {
  isLoading.value = true;

  try {
    const response = await AjaxHelper.fetch<Settings>({
      method: 'AIProviders.getSettings',
    }, {
      createErrorNotification: false,
    });
    applySettings(response);
    response.providers
      .filter((provider) => provider.supportsCustomEndpoint && provider.configuration.isUsable)
      .forEach((provider) => {
        fetchAvailableModels(provider.id, false);
      });
  } catch (error) {
    showErrorNotification(error, 'aiProvidersLoadError');
  } finally {
    isLoading.value = false;
  }
}

function updateApiKey(providerId: string, apiKey: string) {
  providerConfigurations.value[providerId] = {
    ...providerConfigurations.value[providerId],
    apiKey,
  };
}

function updateEndpointUrl(providerId: string, endpointUrl: string) {
  providerConfigurations.value[providerId] = {
    ...providerConfigurations.value[providerId],
    endpointUrl,
  };
  availableModels.value[providerId] = [];
}

function updateUseFipsEndpoint(providerId: string, useFipsEndpoint: boolean) {
  providerConfigurations.value[providerId] = {
    ...providerConfigurations.value[providerId],
    useFipsEndpoint,
  };
  availableModels.value[providerId] = [];
}

async function disconnectProvider(providerId: string) {
  disconnectingProviders.value[providerId] = true;

  try {
    const response = await AjaxHelper.post<Settings>(
      {
        method: 'AIProviders.disconnectProvider',
      },
      {
        providerId,
      },
      {
        withTokenInUrl: true,
        createErrorNotification: false,
      },
    );
    applySettings(response);

    NotificationsStore.show({
      message: translate('AIProviders_DisconnectSuccess'),
      type: 'transient',
      id: notificationId(`aiProvidersDisconnect-${providerId}`),
      context: 'success',
    });
  } catch (error) {
    showErrorNotification(error, notificationId(`aiProvidersDisconnectError-${providerId}`));
  } finally {
    disconnectingProviders.value[providerId] = false;
  }
}

async function testConnection(providerId: string) {
  await fetchAvailableModels(providerId, true);
}

function cancelChanges() {
  if (settings.value) {
    applySettings(settings.value);
  }
}

/**
 * Saves the current settings to the server.
 */
async function saveSettings() {
  isSaving.value = true;

  try {
    const response = await AjaxHelper.post<Settings>(
      {
        method: 'AIProviders.saveSettings',
      },
      {
        defaultProviderId: defaultProviderId.value,
        defaultCapabilityLevel: defaultCapabilityLevel.value,
        providerConfigurations: JSON.stringify(providerConfigurations.value),
      },
      {
        withTokenInUrl: true,
        createErrorNotification: false,
      },
    );
    applySettings(response);

    const notificationInstanceId = NotificationsStore.show({
      message: translate('AIProviders_SettingsSaveSuccess'),
      type: 'transient',
      id: 'aiProvidersSettings',
      context: 'success',
    });
    NotificationsStore.scrollToNotification(notificationInstanceId);
  } catch (error) {
    const notificationInstanceId = showErrorNotification(error, 'aiProvidersSettingsError');
    NotificationsStore.scrollToNotification(notificationInstanceId);
  } finally {
    isSaving.value = false;
  }
}

onMounted(loadSettings);
</script>

<style lang="less">
.ai-providers-page {
  --ai-providers-border: var(--theme-color-border-light);
  --ai-providers-border-strong: var(--theme-color-border);
  --ai-providers-accent: var(--theme-color-brand);
  --ai-providers-text-muted: var(--theme-color-text-light);
  --ai-providers-heading: var(--theme-color-headline-alternative);

  h2, h3, h4 {
    color: var(--ai-providers-heading);
    margin: 0;
    padding: 0;
  }
}

.ai-providers-page-header {
  margin-bottom: 24px;
}

.ai-providers-page-title {
  font-size: 22px;
  line-height: 1.3;
}

.ai-providers-page-subtitle {
  color: var(--ai-providers-text-muted);
  font-size: 14px;
  line-height: 1.5;
  margin: 4px 0 0;
}

.ai-providers-defaults-title {
  font-size: 18px;
  line-height: 1.4;
  margin-bottom: 24px!important;
}

.ai-providers-subsection-title {
  font-size: 15px;
  font-weight: 600;
  line-height: 1.4;
}

.ai-providers-section + .ai-providers-section {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid var(--ai-providers-border);
}

.ai-providers-section-help {
  color: var(--ai-providers-text-muted);
  margin: 4px 0 16px;
}

.ai-providers-cards,
.ai-providers-capability-cards {
  display: grid;
  gap: 16px;
}

// Query container for the card grid: the content width depends on the admin
// sidebar, so columns must follow the section width, not the viewport.
.ai-providers-section {
  container-type: inline-size;
}

// Fallback for browsers without container-query support: cards fill and wrap.
.ai-providers-cards {
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  margin-top: 16px;
}

.ai-providers-capability-cards {
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}

// Discrete column counts keep the rows balanced (5 cards: 2+2+1, 3+2).
@container (min-width: 620px) {
  .ai-providers-cards {
    grid-template-columns: repeat(2, 1fr);
  }
}

@container (min-width: 960px) {
  .ai-providers-cards {
    grid-template-columns: repeat(3, 1fr);
  }
}

.ai-providers-default-warning {
  margin-top: 16px;
}

.ai-providers-capability-card {
  display: flex;
  flex-direction: column;
  padding: 16px;
  background: var(--theme-color-background-contrast);
  border: 1px solid var(--ai-providers-border);
  border-radius: 6px;
  cursor: pointer;
  transition: border-color 120ms ease, box-shadow 120ms ease, background-color 120ms ease;

  &:hover {
    border-color: var(--ai-providers-border-strong);
  }

  &.is-selected {
    border-color: var(--ai-providers-accent);
    box-shadow: 0 0 0 1px var(--ai-providers-accent) inset;
    background: var(--theme-color-background-tinyContrast);
  }
}

.ai-providers-capability-header {
  margin-bottom: 8px;
}

.ai-providers-capability-label {
  color: var(--ai-providers-heading);
  font-weight: 600;
  font-size: 15px;
}

.ai-providers-capability-description {
  color: var(--ai-providers-text-muted);
  font-size: 13px;
  line-height: 1.5;
}

.ai-providers-content {
  position: relative;
}

.ai-providers-unsaved-changes {
  position: absolute;
  top: 16px;
  right: 16px;
  padding: 5px 14px;
  // Matomo's @color-orange-brand (#f57c00) as warning accent on a light tint.
  background: fade(#f57c00, 20%);
  border-radius: 3px;
  color: #f57c00;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.01em;
  line-height: 1.5;
  visibility: hidden;
  opacity: 0;
  transform: translateY(-4px);
  transition: opacity 150ms ease, transform 150ms ease;

  &.is-visible {
    visibility: visible;
    opacity: 1;
    transform: translateY(0);
  }

  // A solid orange block glows on dark backgrounds; tone it down to a
  // translucent tint with orange text instead.
  [data-theme-mode="dark"] & {
    background: fade(#f57c00, 18%);
    color: #f5a557;
  }
}

.ai-providers-footer {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  margin-top: 24px;

  // The core .btn[disabled] rule swaps background and text color but leaves
  // the btn-outline brand-green border in place. Keep the border transparent
  // (rather than removing it) so the button doesn't shrink by 1px.
  .btn-outline[disabled] {
    border-color: transparent;
  }
}
</style>
