<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    v-if="shouldShowRecommendations"
    :content-title="translate('Goals_RecommendedGoals')"
    :feature="translate('Goals_RecommendedGoals')"
    class="recommendGoals"
  >
    <p class="recommendGoals-intro">{{ translate('Goals_RecommendedGoalsIntro') }}</p>

    <ActivityIndicator :loading="isLoadingSaved" />

    <div v-if="hasRun && !isLoading">
      <div v-if="recommendations.length">
        <div class="recommendGoals-list">
          <RecommendGoalCard
            v-for="rec in recommendations"
            :key="recKey(rec)"
            :rec="rec"
            :accepted="isAccepted(rec)"
            :creating="creatingId === recKey(rec)"
            :busy="isBusy"
            :primary="pendingRecommendations.length === 1"
            @create="createOne(rec)"
            @dismiss="dismissOne(rec)"
          />
        </div>

        <div class="recommendGoals-actions">
          <button
            type="button"
            class="btn-flat"
            @click="dismiss()"
            :disabled="isBusy"
          >
            {{ translate('Goals_RecommendDismiss') }}
          </button>
          <button
            v-if="pendingRecommendations.length > 1"
            type="button"
            class="btn"
            @click="createAll()"
            :disabled="isBusy"
          >
            {{ isCreatingAll
              ? translate('Goals_RecommendCreating')
              : translate('Goals_RecommendCreateAll') }}
          </button>
        </div>
      </div>
      <p v-else class="recommendGoals-empty">{{ translate('Goals_RecommendNoneFound') }}</p>

      <details class="recommendGoals-manual" v-if="manualGoals.length">
        <summary>
          <span class="icon-chevron-right"></span>
          {{ translate('Goals_RecommendManualTitle') }} ({{ manualGoals.length }})
        </summary>
        <p class="recommendGoals-intro">{{ translate('Goals_RecommendManualIntro') }}</p>
        <ul class="recommendGoals-manualList">
          <li v-for="(rec, index) in manualGoals" :key="'manual-' + index">
            <div class="recommendGoals-manualBody">
              <span class="recommendGoals-manualName">{{ rec.name }}</span>
              <span class="recommendGoals-manualHowTo">{{ rec.howTo }}</span>
            </div>
            <button
              type="button"
              class="btn-flat"
              @click="$emit('prefill', rec)"
              :disabled="isBusy"
            >
              {{ translate('Goals_RecommendManualStartInForm') }}
            </button>
          </li>
        </ul>
      </details>
    </div>

    <Alert severity="warning" v-if="aiError && !isLoading">{{ aiError }}</Alert>
    <Alert severity="warning" v-if="fallbackModeMessage">
      {{ fallbackModeMessage }}
    </Alert>
    <Alert severity="danger" v-if="createError && !isLoading">{{ createError }}</Alert>

    <div class="recommendGoals-scanProgress" v-if="isLoading">
      <Progressbar :progress="scanProgress" :label="scanProgressLabel" />
      <p class="recommendGoals-scanHint">{{ translate('Goals_RecommendProgressHint') }}</p>
    </div>

    <div class="recommendGoals-footer" v-else>
      <p class="recommendGoals-meta" aria-live="polite" v-if="lastScannedAgo">
        {{ translate('Goals_RecommendLastScanned', lastScannedAgo) }}
        <span v-if="useAi && remainingAiScans !== null">
          ({{ translate('Goals_RecommendScansRemainingToday', `${remainingAiScans}`) }})
        </span>
      </p>
      <div class="recommendGoals-toolbar">
        <button
          type="button"
          class="btn recommendGoals-run"
          :class="{ 'btn-outline': pendingRecommendations.length > 0 }"
          @click="recommend()"
          :disabled="isBusy"
        >
          <span class="icon-search"></span>
          {{ scanButtonLabel }}
        </button>
        <div class="switch recommendGoals-aiSwitch" v-if="isAiAvailable">
          <label>
            <input
              type="checkbox"
              v-model="useAi"
              :disabled="isBusy"
            />
            <span class="lever"></span>
            {{ translate('Goals_RecommendUseAi') }}
          </label>
        </div>
        <span
          class="recommendGoals-chip recommendGoals-chip--aiUnavailable"
          v-else-if="aiUnavailableLabel"
          :title="aiUnavailableHelp"
        >
          {{ aiUnavailableLabel }}
        </span>
        <button
          v-if="isAiAvailable"
          type="button"
          class="recommendGoals-privacyLink"
          :aria-expanded="showPrivacyNote ? 'true' : 'false'"
          aria-controls="recommendGoalsPrivacyNote"
          @click="showPrivacyNote = !showPrivacyNote"
        >{{ translate('Goals_RecommendWhatDataIsShared') }}</button>
      </div>
      <p
        class="recommendGoals-privacyNote"
        id="recommendGoalsPrivacyNote"
        v-show="isAiAvailable && showPrivacyNote"
      >
        {{ privacyNote }}
      </p>
    </div>
  </ContentBlock>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import {
  Matomo,
  AjaxHelper,
  translate,
  ContentBlock,
  ActivityIndicator,
  Alert,
  Progressbar,
} from 'CoreHome';
import RecommendGoalCard from './RecommendGoalCard.vue';
import useScanProgress from './useScanProgress';
import type { RecommendedGoal, RecommendedManualGoal, RecommendationsResponse } from './types';

const props = withDefaults(defineProps<{
  goals?: Record<string, Record<string, unknown>>;
  userCanEditGoals?: boolean;
}>(), {
  goals: () => ({}),
});

/* eslint-disable func-call-spacing, no-spaced-func */
const emit = defineEmits<{
  (e: 'created', idGoals: number[]): void;
  (e: 'prefill', goal: RecommendedManualGoal): void;
}>();
/* eslint-enable func-call-spacing, no-spaced-func */

const useAi = ref(false);
const lastRunUsedAi = ref(false);
const isLoading = ref(false);
const isLoadingSaved = ref(false);
const creatingId = ref<string|null>(null);
const isCreatingAll = ref(false);
const isDismissing = ref(false);
const dismissingId = ref<string|null>(null);
const hasRun = ref(false);
const showPrivacyNote = ref(false);
const aiError = ref<string|null>(null);
const createError = ref<string|null>(null);
const recommendationMode = ref<string|null>(null);
const recommendations = ref<RecommendedGoal[]>([]);
const manualGoals = ref<RecommendedManualGoal[]>([]);
const generatedAt = ref<number|null>(null);
const remainingAiScans = ref<number|null>(null);
const providerName = ref<string>(translate('Goals_RecommendAiProviderFallback'));
const aiAvailability = ref<string>('available');
const serverPrivacyNote = ref<string>('');
const createdRecommendationKeys = ref<string[]>([]);

const {
  progress: scanProgress,
  isInRankingPhase,
  start: startScanProgress,
  stop: stopScanProgress,
} = useScanProgress();

const idSite = computed((): number|string => Matomo.idSite);

const shouldShowRecommendations = computed(() => props.userCanEditGoals);

const isAiAvailable = computed(() => aiAvailability.value === 'available');

// 'disabled' stays silent: nobody on the instance can enable AI, so there is
// nothing to act on. The other two states differ in what needs doing.
const aiUnavailableLabel = computed(() => {
  if (aiAvailability.value === 'notActivated') {
    return translate('Goals_RecommendAiNotActivated');
  }

  return aiAvailability.value === 'notConfigured'
    ? translate('Goals_RecommendAiNotConfigured')
    : '';
});

const aiUnavailableHelp = computed(() => (aiAvailability.value === 'notActivated'
  ? translate('Goals_RecommendAiNotActivatedHelp')
  : translate('Goals_RecommendAiNotConfiguredHelp')));

const isBusy = computed(() => isLoading.value
  || isCreatingAll.value
  || isDismissing.value
  || creatingId.value !== null
  || dismissingId.value !== null);

function recKey(rec: RecommendedGoal): string {
  return rec.id || rec.name;
}

function goalKey(matchAttribute: string, pattern: string): string {
  let normalizedPattern = `${pattern || ''}`.trim().toLowerCase().replace(/\/+$/, '');
  if (matchAttribute === 'url') {
    normalizedPattern = normalizedPattern.replace(/^https?:\/\/[^/]+/i, '');
  }

  return `${matchAttribute}:${normalizedPattern.replace(/^\/|\/$/g, '')}`;
}

const existingGoalKeys = computed(() => Object.values(props.goals || {})
  .filter((goal) => goal.pattern)
  .map((goal) => goalKey(
    `${goal.match_attribute || 'url'}`,
    `${goal.pattern || ''}`,
  )));

function isAccepted(rec: RecommendedGoal): boolean {
  if (createdRecommendationKeys.value.indexOf(recKey(rec)) !== -1) {
    return true;
  }

  return existingGoalKeys.value
    .indexOf(goalKey(rec.matchAttribute || 'url', rec.pattern)) !== -1;
}

const pendingRecommendations = computed(
  () => recommendations.value.filter((rec) => !isAccepted(rec)),
);

const scanButtonLabel = computed(() => (hasRun.value
  ? translate('Goals_RecommendRescan')
  : translate('Goals_RecommendGoals')));

const lastScannedAgo = computed(() => {
  if (!generatedAt.value) {
    return '';
  }

  const date = new Date(generatedAt.value * 1000);
  if (typeof Intl === 'undefined' || !Intl.RelativeTimeFormat) {
    return date.toLocaleString();
  }

  const formatter = new Intl.RelativeTimeFormat(Matomo.language, { numeric: 'auto' });
  const elapsedSeconds = Math.max(0, Math.round((Date.now() - date.getTime()) / 1000));
  if (elapsedSeconds < 60) {
    return formatter.format(-elapsedSeconds, 'second');
  }

  if (elapsedSeconds < 3600) {
    return formatter.format(-Math.round(elapsedSeconds / 60), 'minute');
  }

  if (elapsedSeconds < 86400) {
    return formatter.format(-Math.round(elapsedSeconds / 3600), 'hour');
  }

  return formatter.format(-Math.round(elapsedSeconds / 86400), 'day');
});

const scanProgressLabel = computed(() => (lastRunUsedAi.value && isInRankingPhase.value
  ? translate('Goals_RecommendProgressAiRanking')
  : translate('Goals_RecommendProgressCrawling')));

const fallbackModeMessage = computed(() => {
  if (!hasRun.value || isLoading.value || !lastRunUsedAi.value) {
    return '';
  }

  if (recommendationMode.value === 'deterministic') {
    return translate('Goals_RecommendationFallbackUsed');
  }

  return '';
});

// server-built so plugins can replace it for their environment
const privacyNote = computed(
  () => serverPrivacyNote.value
    || translate('Goals_RecommendAiToggleHelp', providerName.value),
);

function loadSavedRecommendations() {
  isLoadingSaved.value = true;

  AjaxHelper.fetch<RecommendationsResponse>({
    method: 'Goals.getSavedRecommendedGoals',
    idSite: idSite.value,
  }, { createErrorNotification: false }).then((response) => {
    if (response && response.aiAvailability) {
      aiAvailability.value = response.aiAvailability;
    }
    if (response && response.privacyNote) {
      serverPrivacyNote.value = response.privacyNote;
    }

    if (!response || !response.generatedAt) {
      return;
    }

    remainingAiScans.value = typeof response.remainingAiScans === 'number'
      ? response.remainingAiScans
      : null;
    if (response.providerName) {
      providerName.value = response.providerName;
    }
    recommendations.value = response.goals || [];
    manualGoals.value = response.manualGoals || [];
    recommendationMode.value = response.mode || null;
    generatedAt.value = response.generatedAt;
    useAi.value = !!response.useAi;
    lastRunUsedAi.value = !!response.useAi;
    hasRun.value = true;
  }).catch(() => {
    // saved recommendations are optional; the user can still run a fresh scan
  }).finally(() => {
    isLoadingSaved.value = false;
  });
}

function recommend() {
  isLoading.value = true;
  aiError.value = null;
  createError.value = null;
  recommendationMode.value = null;
  const requestedAi = useAi.value && isAiAvailable.value;
  lastRunUsedAi.value = requestedAi;
  startScanProgress();

  AjaxHelper.fetch<RecommendationsResponse>({
    method: 'Goals.runGoalRecommendationScan',
    idSite: idSite.value,
    useAi: requestedAi ? 1 : 0,
  }, { createErrorNotification: false }).then((response) => {
    recommendations.value = (response && response.goals) || [];
    manualGoals.value = (response && response.manualGoals) || [];
    aiError.value = (response && response.aiError) || null;
    recommendationMode.value = (response && response.mode) || null;
    generatedAt.value = (response && response.generatedAt) || null;
    remainingAiScans.value = response && typeof response.remainingAiScans === 'number'
      ? response.remainingAiScans
      : null;
    if (response && response.providerName) {
      providerName.value = response.providerName;
    }
    if (response && response.aiAvailability) {
      aiAvailability.value = response.aiAvailability;
    }
    if (response && response.privacyNote) {
      serverPrivacyNote.value = response.privacyNote;
    }
    hasRun.value = true;
  }).catch((error: unknown) => {
    recommendations.value = [];
    manualGoals.value = [];
    aiError.value = error instanceof Error && error.message === 'Rate Limit was exceed'
      ? translate('Goals_RecommendScanAlreadyRunning')
      : translate('Goals_RecommendError');
    recommendationMode.value = null;
    hasRun.value = true;
  }).finally(() => {
    stopScanProgress();
    isLoading.value = false;
  });
}

function addGoalRequest(rec: RecommendedGoal): Promise<{ value?: number }> {
  return AjaxHelper.fetch({
    method: 'Goals.addGoal',
    idSite: idSite.value,
    name: rec.name,
    matchAttribute: rec.matchAttribute || 'url',
    pattern: rec.pattern,
    patternType: rec.patternType || 'contains',
    caseSensitive: rec.caseSensitive ? 1 : 0,
    allowMultipleConversionsPerVisit: rec.allowMultipleConversionsPerVisit ? 1 : 0,
    revenue: rec.revenue || 0,
    description: rec.description || rec.reason || '',
    useEventValueAsRevenue: rec.useEventValueAsRevenue ? 1 : 0,
    createdFromRecommendedGoal: 1,
  }, { createErrorNotification: false });
}

function createOne(rec: RecommendedGoal) {
  creatingId.value = recKey(rec);
  createError.value = null;
  addGoalRequest(rec).then((response) => {
    if (response && response.value) {
      createdRecommendationKeys.value.push(recKey(rec));
    }
    emit('created', response && response.value ? [response.value] : []);
  }).catch(() => {
    createError.value = translate('Goals_RecommendCreateError');
  }).finally(() => {
    creatingId.value = null;
  });
}

function createAll() {
  isCreatingAll.value = true;
  createError.value = null;
  const createdIds: number[] = [];

  pendingRecommendations.value.reduce(
    (promise, rec) => promise.then(() => addGoalRequest(rec)).then((response) => {
      if (response && response.value) {
        createdRecommendationKeys.value.push(recKey(rec));
        createdIds.push(response.value);
      }
    }),
    Promise.resolve(),
  ).catch(() => {
    createError.value = translate('Goals_RecommendCreateError');
  }).finally(() => {
    isCreatingAll.value = false;
    if (createdIds.length) {
      emit('created', createdIds);
    }
  });
}

function dismiss() {
  isDismissing.value = true;
  AjaxHelper.fetch({
    method: 'Goals.dismissRecommendedGoals',
    idSite: idSite.value,
  }).then(() => {
    hasRun.value = false;
    recommendations.value = [];
    manualGoals.value = [];
    aiError.value = null;
    createError.value = null;
    recommendationMode.value = null;
    lastRunUsedAi.value = false;
    generatedAt.value = null;
  }).finally(() => {
    isDismissing.value = false;
  });
}

function dismissOne(rec: RecommendedGoal) {
  dismissingId.value = recKey(rec);
  isDismissing.value = true;
  createError.value = null;
  AjaxHelper.fetch<{ success?: boolean }>({
    method: 'Goals.dismissRecommendedGoal',
    idSite: idSite.value,
    recommendationId: rec.id || '',
  }, { createErrorNotification: false }).then((response) => {
    if (!response || !response.success) {
      createError.value = translate('Goals_RecommendDismissError');
      return;
    }

    recommendations.value = recommendations.value.filter((other) => other !== rec);
    if (!recommendations.value.length && !manualGoals.value.length) {
      hasRun.value = false;
      recommendationMode.value = null;
      generatedAt.value = null;
    }
  }).catch(() => {
    createError.value = translate('Goals_RecommendDismissError');
  }).finally(() => {
    dismissingId.value = null;
    isDismissing.value = false;
  });
}

if (shouldShowRecommendations.value) {
  loadSavedRecommendations();
}
</script>
