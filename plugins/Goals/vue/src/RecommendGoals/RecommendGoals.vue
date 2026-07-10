<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    v-if="shouldShowRecommendations"
    :content-title="translate('Goals_RecommendedGoals')"
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
      </p>
      <div class="recommendGoals-toolbar">
        <button
          type="button"
          class="btn recommendGoals-run"
          @click="recommend()"
          :disabled="isBusy"
        >
          <span class="icon-search"></span>
          {{ scanButtonLabel }}
        </button>
        <div class="switch recommendGoals-aiSwitch">
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
        <button
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
        v-show="showPrivacyNote"
      >
        {{ translate('Goals_RecommendAiToggleHelp') }}
      </p>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
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
import type { RecommendedGoal, RecommendedManualGoal, RecommendationsResponse } from './types';

// fake, time-based progress: the backend reports no incremental scan status. The
// crawl phase eases to 60%, the (AI) ranking phase approaches but never reaches 100%.
const SCAN_CRAWL_PHASE_MS = 15000;
const SCAN_EXPECTED_TOTAL_MS = 30000;
const SCAN_CRAWL_PHASE_PROGRESS = 60;
const SCAN_RANKING_PHASE_PROGRESS = 93;
const SCAN_PROGRESS_TICK_MS = 250;

interface RecommendGoalsState {
  useAi: boolean;
  lastRunUsedAi: boolean;
  isLoading: boolean;
  isLoadingSaved: boolean;
  creatingId: string|null;
  isCreatingAll: boolean;
  isDismissing: boolean;
  dismissingId: string|null;
  hasRun: boolean;
  showPrivacyNote: boolean;
  aiError: string|null;
  createError: string|null;
  recommendationMode: string|null;
  recommendations: RecommendedGoal[];
  manualGoals: RecommendedManualGoal[];
  generatedAt: number|null;
  createdRecommendationKeys: string[];
  scanProgress: number;
  scanStartedAt: number|null;
  scanProgressTimer: number|null;
}

export default defineComponent({
  props: {
    goals: {
      type: Object,
      default: () => ({}),
    },
    userCanEditGoals: Boolean,
  },
  components: {
    ContentBlock,
    ActivityIndicator,
    Alert,
    Progressbar,
    RecommendGoalCard,
  },
  emits: ['created', 'prefill'],
  data(): RecommendGoalsState {
    return {
      useAi: true,
      lastRunUsedAi: false,
      isLoading: false,
      isLoadingSaved: false,
      creatingId: null,
      isCreatingAll: false,
      isDismissing: false,
      dismissingId: null,
      hasRun: false,
      showPrivacyNote: false,
      aiError: null,
      createError: null,
      recommendationMode: null,
      recommendations: [],
      manualGoals: [],
      generatedAt: null,
      createdRecommendationKeys: [],
      scanProgress: 0,
      scanStartedAt: null,
      scanProgressTimer: null,
    };
  },
  created() {
    if (this.shouldShowRecommendations) {
      this.loadSavedRecommendations();
    }
  },
  beforeUnmount() {
    this.stopScanProgress();
  },
  methods: {
    recKey(rec: RecommendedGoal): string {
      return rec.id || rec.name;
    },
    loadSavedRecommendations() {
      this.isLoadingSaved = true;

      AjaxHelper.fetch<RecommendationsResponse>({
        method: 'Goals.getSavedRecommendedGoals',
        idSite: this.idSite,
      }, { createErrorNotification: false }).then((response) => {
        if (!response || !response.generatedAt) {
          return;
        }

        this.recommendations = response.goals || [];
        this.manualGoals = response.manualGoals || [];
        this.recommendationMode = response.mode || null;
        this.generatedAt = response.generatedAt;
        this.useAi = !!response.useAi;
        this.lastRunUsedAi = !!response.useAi;
        this.hasRun = true;
      }).catch(() => {
        // saved recommendations are optional; the user can still run a fresh scan
      }).finally(() => {
        this.isLoadingSaved = false;
      });
    },
    recommend() {
      this.isLoading = true;
      this.aiError = null;
      this.createError = null;
      this.recommendationMode = null;
      const requestedAi = this.useAi;
      this.lastRunUsedAi = requestedAi;
      this.startScanProgress();

      AjaxHelper.fetch<RecommendationsResponse>({
        method: 'Goals.getRecommendedGoals',
        idSite: this.idSite,
        useAi: requestedAi ? 1 : 0,
      }, { createErrorNotification: false }).then((response) => {
        this.recommendations = (response && response.goals) || [];
        this.manualGoals = (response && response.manualGoals) || [];
        this.aiError = (response && response.aiError) || null;
        this.recommendationMode = (response && response.mode) || null;
        this.generatedAt = (response && response.generatedAt) || null;
        this.hasRun = true;
      }).catch(() => {
        this.recommendations = [];
        this.manualGoals = [];
        this.aiError = translate('Goals_RecommendError');
        this.recommendationMode = null;
        this.hasRun = true;
      }).finally(() => {
        this.stopScanProgress();
        this.isLoading = false;
      });
    },
    startScanProgress() {
      this.scanStartedAt = Date.now();
      this.scanProgress = 0;
      this.scanProgressTimer = window.setInterval(() => {
        this.scanProgress = this.computeScanProgress();
      }, SCAN_PROGRESS_TICK_MS);
    },
    stopScanProgress() {
      if (this.scanProgressTimer !== null) {
        window.clearInterval(this.scanProgressTimer);
        this.scanProgressTimer = null;
      }
      this.scanStartedAt = null;
      this.scanProgress = 0;
    },
    computeScanProgress(): number {
      if (this.scanStartedAt === null) {
        return 0;
      }

      const elapsed = Date.now() - this.scanStartedAt;
      if (elapsed <= SCAN_CRAWL_PHASE_MS) {
        return (elapsed / SCAN_CRAWL_PHASE_MS) * SCAN_CRAWL_PHASE_PROGRESS;
      }

      const rankingElapsed = elapsed - SCAN_CRAWL_PHASE_MS;
      const rankingDuration = SCAN_EXPECTED_TOTAL_MS - SCAN_CRAWL_PHASE_MS;
      if (rankingElapsed <= rankingDuration) {
        return SCAN_CRAWL_PHASE_PROGRESS
          + (rankingElapsed / rankingDuration)
            * (SCAN_RANKING_PHASE_PROGRESS - SCAN_CRAWL_PHASE_PROGRESS);
      }

      // past the expected duration: creep slowly towards (but never reach) 99%
      const overtimeSeconds = (rankingElapsed - rankingDuration) / 1000;
      return Math.min(99, SCAN_RANKING_PHASE_PROGRESS + (overtimeSeconds * 0.1));
    },
    addGoalRequest(rec: RecommendedGoal): Promise<{ value?: number }> {
      return AjaxHelper.fetch({
        method: 'Goals.addGoal',
        idSite: this.idSite,
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
    },
    createOne(rec: RecommendedGoal) {
      this.creatingId = this.recKey(rec);
      this.createError = null;
      this.addGoalRequest(rec).then((response) => {
        if (response && response.value) {
          this.createdRecommendationKeys.push(this.recKey(rec));
        }
        this.$emit('created', response && response.value ? [response.value] : []);
      }).catch(() => {
        this.createError = translate('Goals_RecommendCreateError');
      }).finally(() => {
        this.creatingId = null;
      });
    },
    createAll() {
      this.isCreatingAll = true;
      this.createError = null;
      const createdIds: number[] = [];

      this.pendingRecommendations.reduce(
        (promise, rec) => promise.then(() => this.addGoalRequest(rec)).then((response) => {
          if (response && response.value) {
            this.createdRecommendationKeys.push(this.recKey(rec));
            createdIds.push(response.value);
          }
        }),
        Promise.resolve(),
      ).catch(() => {
        this.createError = translate('Goals_RecommendCreateError');
      }).finally(() => {
        this.isCreatingAll = false;
        if (createdIds.length) {
          this.$emit('created', createdIds);
        }
      });
    },
    dismiss() {
      this.isDismissing = true;
      AjaxHelper.fetch({
        method: 'Goals.dismissRecommendedGoals',
        idSite: this.idSite,
      }).then(() => {
        this.hasRun = false;
        this.recommendations = [];
        this.manualGoals = [];
        this.aiError = null;
        this.createError = null;
        this.recommendationMode = null;
        this.lastRunUsedAi = false;
        this.generatedAt = null;
      }).finally(() => {
        this.isDismissing = false;
      });
    },
    dismissOne(rec: RecommendedGoal) {
      this.dismissingId = this.recKey(rec);
      this.isDismissing = true;
      this.createError = null;
      AjaxHelper.fetch<{ success?: boolean }>({
        method: 'Goals.dismissRecommendedGoal',
        idSite: this.idSite,
        recommendationId: rec.id || '',
      }, { createErrorNotification: false }).then((response) => {
        if (!response || !response.success) {
          this.createError = translate('Goals_RecommendDismissError');
          return;
        }

        this.recommendations = this.recommendations.filter((other) => other !== rec);
        if (!this.recommendations.length && !this.manualGoals.length) {
          this.hasRun = false;
          this.recommendationMode = null;
          this.generatedAt = null;
        }
      }).catch(() => {
        this.createError = translate('Goals_RecommendDismissError');
      }).finally(() => {
        this.dismissingId = null;
        this.isDismissing = false;
      });
    },
    isAccepted(rec: RecommendedGoal): boolean {
      if (this.createdRecommendationKeys.indexOf(this.recKey(rec)) !== -1) {
        return true;
      }

      return this.existingGoalKeys
        .indexOf(this.goalKey(rec.matchAttribute || 'url', rec.pattern)) !== -1;
    },
    goalKey(matchAttribute: string, pattern: string): string {
      let normalizedPattern = `${pattern || ''}`.trim().toLowerCase().replace(/\/+$/, '');
      if (matchAttribute === 'url') {
        normalizedPattern = normalizedPattern.replace(/^https?:\/\/[^/]+/i, '');
      }

      return `${matchAttribute}:${normalizedPattern.replace(/^\/|\/$/g, '')}`;
    },
  },
  computed: {
    idSite(): number|string {
      return Matomo.idSite;
    },
    isBusy(): boolean {
      return this.isLoading
        || this.isCreatingAll
        || this.isDismissing
        || this.creatingId !== null
        || this.dismissingId !== null;
    },
    existingGoalKeys(): string[] {
      return Object.values(this.goals || {})
        .filter((goal: Record<string, unknown>) => goal.pattern)
        .map((goal: Record<string, unknown>) => this.goalKey(
          `${goal.match_attribute || 'url'}`,
          `${goal.pattern || ''}`,
        ));
    },
    pendingRecommendations(): RecommendedGoal[] {
      return this.recommendations.filter((rec) => !this.isAccepted(rec));
    },
    shouldShowRecommendations(): boolean {
      return this.userCanEditGoals;
    },
    scanButtonLabel(): string {
      return this.hasRun
        ? translate('Goals_RecommendRescan')
        : translate('Goals_RecommendGoals');
    },
    lastScannedAgo(): string {
      if (!this.generatedAt) {
        return '';
      }

      const date = new Date(this.generatedAt * 1000);
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
    },
    scanProgressLabel(): string {
      const isRankingPhase = this.lastRunUsedAi && this.scanProgress >= SCAN_CRAWL_PHASE_PROGRESS;

      return isRankingPhase
        ? translate('Goals_RecommendProgressAiRanking')
        : translate('Goals_RecommendProgressCrawling');
    },
    fallbackModeMessage(): string {
      if (!this.hasRun || this.isLoading || !this.lastRunUsedAi) {
        return '';
      }

      if (this.recommendationMode === 'deterministic') {
        return translate('Goals_RecommendationFallbackUsed');
      }

      return '';
    },
  },
});
</script>
