<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<script setup lang="ts">
import { computed } from 'vue';
import { AutoClearPassword as vAutoClearPassword, translate } from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import type { Provider, ProviderConfiguration } from '../types';

const props = defineProps<{
  provider: Provider;
  configuration: ProviderConfiguration | undefined;
  availableModels: string[];
  selected: boolean;
  usableAsDefault: boolean;
  canEdit: boolean;
  isTesting: boolean;
  isDisconnecting: boolean;
}>();

/* eslint-disable func-call-spacing, no-spaced-func */
const emit = defineEmits<{
  (e: 'select'): void;
  (e: 'update:apiKey', value: string): void;
  (e: 'update:endpointUrl', value: string): void;
  (e: 'update:useFipsEndpoint', value: boolean): void;
  (e: 'update:model', value: string): void;
  (e: 'test'): void;
  (e: 'disconnect'): void;
}>();
/* eslint-enable func-call-spacing, no-spaced-func */

const hasPendingKey = computed(() => (props.configuration?.apiKey ?? '') !== '');
const hasEndpointUrl = computed(() => (props.configuration?.endpointUrl ?? '') !== '');
const hasKey = computed(() => hasPendingKey.value || props.provider.configuration.hasApiKey);

// Providers with a default endpoint (e.g. AWS Bedrock) only need the key;
// fully custom servers need the URL and commonly run without authentication.
const canTest = computed(() => {
  if (!props.provider.supportsCustomEndpoint || props.provider.defaultEndpointUrl) {
    return hasKey.value;
  }
  return hasEndpointUrl.value;
});

const modelOptions = computed(() => {
  const options: Record<string, string> = {};
  props.availableModels.forEach((model) => { options[model] = model; });
  return options;
});

function selectProvider() {
  if (props.usableAsDefault) {
    emit('select');
  }
}
</script>

<template>
  <div
    :aria-checked="selected"
    :aria-disabled="!usableAsDefault"
    :class="{ 'is-selected': selected, 'is-not-usable': !usableAsDefault }"
    class="ai-providers-card"
    role="radio"
    :tabindex="usableAsDefault ? 0 : -1"
    @click="selectProvider()"
    @keydown.enter.prevent="selectProvider()"
    @keydown.space.prevent="selectProvider()"
  >
    <div class="ai-providers-card-inner">
      <div class="ai-providers-card-heading">
        <div class="ai-providers-card-header">
          <span class="ai-providers-card-name">{{ provider.name }}</span>
          <span
            v-if="selected"
            class="ai-providers-card-default"
          >
            {{ translate('AIProviders_DefaultBadge') }}
          </span>
        </div>

        <p class="ai-providers-card-description">
          {{ translate(provider.description) }}
        </p>
      </div>

      <template v-if="canEdit">
        <Field
          v-if="provider.supportsCustomEndpoint"
          class="ai-providers-endpoint-field"
          :model-value="configuration?.endpointUrl"
          :name="`endpointUrl-${provider.id}`"
          :title="translate(provider.endpointFieldTitle)"
          :placeholder="translate(provider.endpointFieldPlaceholder)"
          autocomplete="off"
          full-width
          uicontrol="text"
          @update:model-value="emit('update:endpointUrl', `${$event}`)"
        />

        <Field
          v-if="provider.supportsFipsEndpoint"
          class="ai-providers-fips-field"
          :model-value="configuration?.useFipsEndpoint"
          :name="`useFipsEndpoint-${provider.id}`"
          :title="translate('AIProviders_BedrockUseFipsEndpoint')"
          full-width
          uicontrol="checkbox"
          @update:model-value="emit('update:useFipsEndpoint', !!$event)"
        />

        <Field
          v-auto-clear-password
          :model-value="configuration?.apiKey"
          :name="`apiKey-${provider.id}`"
          :placeholder="provider.configuration.hasApiKey
            ? translate('AIProviders_ApiKeyAlreadyConfiguredPlaceholder')
            : translate('AIProviders_ApiKeyPlaceholder')"
          :title="translate('AIProviders_ApiKey')"
          autocomplete="new-password"
          full-width
          uicontrol="password"
          @update:model-value="emit('update:apiKey', `${$event}`)"
        />

        <div
          v-if="provider.supportsCustomEndpoint"
          class="ai-providers-card-model"
        >
          <Field
            v-if="availableModels.length"
            :model-value="configuration?.model"
            :name="`model-${provider.id}`"
            :title="translate('AIProviders_Model')"
            :options="modelOptions"
            full-width
            uicontrol="select"
            @update:model-value="emit('update:model', `${$event}`)"
          />
          <p
            v-else
            class="ai-providers-card-model-help"
          >
            {{ translate('AIProviders_ClickTestConnectionToShowAvailableModels') }}
          </p>
          <button
            v-if="availableModels.length"
            class="btn-flat ai-providers-refresh-models"
            type="button"
            :disabled="isTesting || !canTest"
            :title="translate('AIProviders_RefreshModels')"
            @click.prevent.stop="emit('test')"
          >
            <span
              aria-hidden="true"
              class="icon icon-reload"
            ></span>
            {{ translate('AIProviders_RefreshModels') }}
          </button>
        </div>

        <div
          :class="{ 'is-connected': provider.configuration.isUsable }"
          class="ai-providers-card-status"
        >
          <span
            aria-hidden="true"
            class="icon ai-providers-status-icon"
            :class="provider.configuration.isUsable ? 'icon-ok' : 'icon-minus'"
          ></span>
          {{
            provider.configuration.isUsable
              ? translate('AIProviders_StatusConnected')
              : translate('AIProviders_StatusNotConnected')
          }}
        </div>

        <div class="ai-providers-card-actions">
          <button
            class="btn btn-outline btn-small"
            type="button"
            :disabled="isTesting || !canTest"
            @click.prevent.stop="emit('test')"
          >
            {{
              isTesting
                ? translate('AIProviders_TestingConnection')
                : translate('AIProviders_TestConnection')
            }}
          </button>
          <button
            class="btn-flat"
            type="button"
            :disabled="isDisconnecting || !provider.configuration.hasApiKey"
            @click.prevent.stop="emit('disconnect')"
          >
            {{
              isDisconnecting
                ? translate('AIProviders_Disconnecting')
                : translate('AIProviders_Disconnect')
            }}
          </button>
        </div>
      </template>
    </div>
  </div>
</template>

<style lang="less">
.ai-providers-card {
  display: flex;
  flex-direction: column;
  background: var(--theme-color-background-contrast);
  border: 1px solid var(--ai-providers-border);
  border-radius: 6px;
  cursor: pointer;
  transition: border-color 120ms ease;

  &:hover:not(.is-not-usable):not(.is-selected) {
    border-color: var(--ai-providers-border-strong);
  }

  &.is-selected {
    border-color: var(--ai-providers-accent);
    box-shadow: 0 0 0 1px var(--ai-providers-accent) inset;
    background: var(--theme-color-background-tinyContrast);
  }

  &.is-not-usable {
    cursor: default;
  }

  &:focus,
  &:focus-visible,
  &:active {
    outline: none;
  }

  // Strip every outer margin Materialize puts on the form field so the card's
  // own layout (the `gap` on .ai-providers-card-inner) is the single source of
  // vertical spacing. Without this the row/form-group/input margins stack
  // unpredictably and collide with the status line.
  .matomo-form-field {
    border: 0;
    margin: 0;

    > .col {
      padding-left: 0 !important;
      padding-right: 0 !important;
    }
  }

  .input-field {
    margin: 0;
  }

  .input-field > label,
  .input-field.col > label {
    left: 0 !important;
  }

  .matomo-field-select > label,
  .matomo-field-select > .select-wrapper + label {
    left: 0 !important;
  }

  .input-field > input {
    padding-left: 0;
    margin-left: 0;
    margin-bottom: 0;
    width: 100%;
    box-sizing: border-box;
  }

  .matomo-form-field.ai-providers-endpoint-field,
  .matomo-form-field.ai-providers-fips-field {
    margin-bottom: 16px;
  }

  // Text fields need the extra headroom above them for their floating label;
  // the checkbox has none, so collapse the preceding field's spacing to keep
  // the card's 16px rhythm.
  .matomo-form-field.ai-providers-fips-field {
    margin-top: -16px;

    .checkbox label {
      display: inline-flex;
      align-items: center;
    }

    .checkbox [type="checkbox"] + span {
      height: auto;
      line-height: 1.5;
    }
  }
}

.ai-providers-card-inner {
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 16px;
  padding: 16px;
}

.ai-providers-card-default {
  flex: none;
  padding: 3px 8px;
  background-color: var(--ai-providers-accent);
  color: var(--theme-color-brand-contrast);
  border-radius: 3px;
  font-size: 10px;
  font-weight: 700;
  line-height: 1.5;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.ai-providers-card-header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 8px 12px;
  // Reserve the badge's height so promoting a card to default doesn't push
  // the rest of the card content down.
  min-height: 24px;
  margin-bottom: 8px;
}

.ai-providers-card-name {
  flex: 1 1 auto;
  min-width: 0;
  color: var(--ai-providers-heading);
  font-weight: 600;
  font-size: 15px;
}

.ai-providers-card-description {
  color: var(--ai-providers-text-muted);
  font-size: 13px;
  line-height: 1.5;
  margin: 0;
}

.ai-providers-refresh-models {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
  padding: 0;
  font-size: 12px;

  &[disabled] {
    pointer-events: none;
    cursor: not-allowed;
    color: var(--theme-color-text-on-disabled);
  }

  .icon-reload {
    font-size: 12px;
  }
}

.ai-providers-card-model-help {
  color: var(--ai-providers-text-muted);
  font-size: 12px;
  line-height: 1.5;
  margin: 0;
}

.ai-providers-card-status {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
  color: var(--ai-providers-text-muted);
  font-size: 13px;
  line-height: 1.5;

  &.is-connected {
    color: var(--ai-providers-accent);

    .ai-providers-status-icon {
      color: var(--ai-providers-accent);
    }
  }
}

.ai-providers-status-icon {
  font-size: 14px;
  line-height: 1;
  color: var(--ai-providers-border-strong);
  flex: none;
}

.ai-providers-card-actions {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-content: flex-start;
  gap: 12px;
  margin-top: auto;
  padding-top: 16px;

  .btn,
  .btn-flat {
    white-space: nowrap;
  }

  .btn-flat[disabled] {
    pointer-events: none;
    cursor: not-allowed;
    color: var(--theme-color-text-on-disabled);
  }
}
</style>
