<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('AIProviders_AIProcessing')"
    class="ai-processing"
  >
    <div class="contentHelp">{{ translate('AIProviders_AIProcessingHelp') }}</div>
    <p>{{ translate('AIProviders_AIProcessingIntro') }}</p>

    <ActivityIndicator
      v-if="isLoading"
      :loading="isLoading"
    />

    <template v-else>
      <div
        v-for="category in categories"
        :key="category.id"
        class="ai-processing-category"
      >
        <div class="ai-processing-category-body">
          <h3 class="ai-processing-category-name">{{ translate(labels[category.id].name) }}</h3>
          <p>{{ translate(labels[category.id].description) }}</p>
          <div
            v-if="category.usedBy.length"
            class="ai-processing-used-by"
          >
            <span class="ai-processing-used-by-label">{{ translate('AIProviders_UsedBy') }}</span>
            <ul class="ai-processing-features">
              <li
                v-for="feature in category.usedBy"
                :key="feature.name"
                class="ai-processing-feature"
              >
                {{ feature.name }}
                <a
                  v-if="feature.disclosureUrl"
                  :href="feature.disclosureUrl"
                  rel="noreferrer noopener"
                  target="_blank"
                ><span class="icon-outlink" /> {{ translate('AIProviders_DataProcessingDetails') }}</a>
              </li>
            </ul>
          </div>
        </div>
        <div class="switch">
          <label>
            <input
              v-model="enabled[category.id]"
              :aria-label="translate(labels[category.id].name)"
              :disabled="isSaving"
              type="checkbox"
            />
            <span class="lever"></span>
          </label>
        </div>
      </div>

      <div class="ai-processing-footer">
        <SaveButton
          :value="translate('General_Save')"
          :disabled="!hasUnsavedChanges"
          :saving="isSaving"
          @confirm="save()"
        />
        <span v-if="hasUnsavedChanges">{{ translate('AIProviders_UnsavedChanges') }}</span>
      </div>
    </template>
  </ContentBlock>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import {
  ActivityIndicator,
  AjaxHelper,
  ContentBlock,
  NotificationsStore,
  translate,
} from 'CoreHome';
import { SaveButton } from 'CorePluginsAdmin';
import type { AIProcessingCategory, AIProcessingSetting } from './types';

const labels: Record<AIProcessingCategory, { name: string, description: string }> = {
  nonAnalytics: {
    name: 'AIProviders_NonAnalyticsData',
    description: 'AIProviders_NonAnalyticsDataDescription',
  },
  aggregatedAnalytics: {
    name: 'AIProviders_AggregatedAnalyticsData',
    description: 'AIProviders_AggregatedAnalyticsDataDescription',
  },
};

const categories = ref<AIProcessingSetting[]>([]);
const enabled = ref<Record<string, boolean>>({});
const isLoading = ref(false);
const isSaving = ref(false);

const hasUnsavedChanges = computed(() => categories.value
  .some((category) => enabled.value[category.id] !== category.enabled));

function apply(settings: AIProcessingSetting[]) {
  categories.value = settings;
  enabled.value = Object.fromEntries(settings.map((category) => [category.id, category.enabled]));
}

async function save() {
  isSaving.value = true;

  try {
    apply(await AjaxHelper.post<AIProcessingSetting[]>(
      { method: 'AIProviders.setAIProcessingSettings' },
      {
        enabledCategories: categories.value
          .filter((category) => enabled.value[category.id])
          .map((category) => category.id),
      },
      { withTokenInUrl: true },
    ));

    const id = NotificationsStore.show({
      message: translate('AIProviders_AIProcessingSaveSuccess'),
      type: 'transient',
      id: 'aiProcessingSettings',
      context: 'success',
    });
    NotificationsStore.scrollToNotification(id);
  } finally {
    isSaving.value = false;
  }
}

onMounted(async () => {
  isLoading.value = true;

  try {
    apply(await AjaxHelper.fetch<AIProcessingSetting[]>({ method: 'AIProviders.getAIProcessingSettings' }));
  } finally {
    isLoading.value = false;
  }
});
</script>

<style lang="less">
.ai-processing {
  .ai-processing-category {
    display: flex;
    align-items: flex-start;
    gap: 32px;
    padding: 20px 0;
    border-bottom: 1px solid var(--theme-color-background-tinyContrast);
  }

  .ai-processing-category-body {
    flex: 1 1 auto;

    p {
      margin: 6px 0 0;
      color: var(--theme-color-text-light);
    }
  }

  .ai-processing-category-name {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--theme-color-text-contrast);
  }

  .ai-processing-used-by {
    margin-top: 14px;
  }

  .ai-processing-used-by-label {
    display: block;
    margin-bottom: 6px;
    color: var(--theme-color-text-lighter);
    font-size: 12px;
    font-weight: 600;
  }

  .ai-processing-features {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin: 0;
    padding: 0;
    list-style: none;
  }

  .ai-processing-feature {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 12px;
    margin: 0;
    font-size: 13px;
    line-height: 20px;
  }

  .ai-processing-footer {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 20px;
    font-size: 13px;
    color: var(--theme-color-text-lighter);
  }
}
</style>
