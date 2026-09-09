<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    :aria-checked="selected"
    :aria-disabled="!usableAsDefault"
    :class="{
      'is-selected border-brand shadow-[inset_0_0_0_1px_var(--theme-color-brand)] bg-background-tiny-contrast': selected,
      'is-not-usable cursor-default': !usableAsDefault,
      'hover:border-border': usableAsDefault && !selected,
    }"
    class="ai-providers-card flex flex-col bg-background-contrast border border-border-light rounded-md cursor-pointer
      transition-[border-color] duration-[120ms] ease-in-out focus:outline-none focus-visible:outline-none active:outline-none
      [&_.matomo-form-field]:m-0 [&_.matomo-form-field]:border-0 [&_.matomo-form-field>.col]:px-0
      [&_.input-field]:m-0 [&_.input-field>label]:left-0 [&_.matomo-field-select>label]:left-0
      [&_.matomo-field-select>.select-wrapper+label]:left-0
      [&_.input-field>input]:pl-0 [&_.input-field>input]:ml-0 [&_.input-field>input]:mb-0
      [&_.input-field>input]:w-full [&_.input-field>input]:box-border"
    role="radio"
    :tabindex="usableAsDefault ? 0 : -1"
    @click="selectProvider()"
    @keydown.enter.prevent="selectProvider()"
    @keydown.space.prevent="selectProvider()"
  >
    <div class="ai-providers-card-inner flex flex-1 flex-col gap-4 p-4">
      <div class="ai-providers-card-heading">
        <div class="ai-providers-card-header mb-2 flex min-h-6 flex-wrap items-center justify-between gap-x-3 gap-y-2">
          <span class="ai-providers-card-name min-w-0 flex-auto text-headline text-[15px] font-semibold">{{ provider.name }}</span>
          <span
            v-if="selected"
            class="ai-providers-card-default flex-none rounded-[3px] bg-brand px-2 py-[3px]
              text-[10px] font-bold uppercase leading-normal tracking-[0.04em] text-brand-contrast"
          >
            {{ translate('AIProviders_DefaultBadge') }}
          </span>
        </div>

        <p class="ai-providers-card-description m-0 text-[13px] leading-normal text-text-light">
          {{ translate(provider.description) }}
        </p>
      </div>

      <template v-if="canEdit">
        <Field
          v-if="provider.supportsCustomEndpoint"
          class="ai-providers-endpoint-field mb-4!"
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
          class="ai-providers-fips-field mb-4! -mt-4! [&_.checkbox_label]:inline-flex [&_.checkbox_label]:items-center
            [&_.checkbox_input+span]:h-auto [&_.checkbox_input+span]:leading-normal"
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
            class="ai-providers-card-model-help m-0 text-xs leading-normal text-text-light"
          >
            {{ translate('AIProviders_ClickTestConnectionToShowAvailableModels') }}
          </p>
          <button
            v-if="availableModels.length"
            class="btn-flat ai-providers-refresh-models mt-1 inline-flex items-center gap-1.5 p-0 text-xs
              disabled:pointer-events-none disabled:cursor-not-allowed disabled:text-text-on-disabled [&_.icon-reload]:text-xs"
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
          :class="provider.configuration.isUsable ? 'is-connected text-brand' : 'text-text-light'"
          class="ai-providers-card-status m-0 flex items-center gap-2 text-[13px] leading-normal"
        >
          <span
            aria-hidden="true"
            class="icon ai-providers-status-icon flex-none text-sm leading-none"
            :class="provider.configuration.isUsable ? 'icon-ok text-brand' : 'icon-minus text-border'"
          ></span>
          {{
            provider.configuration.isUsable
              ? translate('AIProviders_StatusConnected')
              : translate('AIProviders_StatusNotConnected')
          }}
        </div>

        <div class="ai-providers-card-actions mt-auto flex flex-col items-stretch justify-start gap-3 pt-4">
          <button
            class="btn btn-outline btn-small whitespace-nowrap"
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
            class="btn-flat whitespace-nowrap disabled:pointer-events-none disabled:cursor-not-allowed disabled:text-text-on-disabled"
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

