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

    <div class="recommendGoals-controls">
      <div class="recommendGoals-aiToggle">
        <button
          type="button"
          class="recommendGoals-switch"
          :class="{ 'recommendGoals-switchEnabled': useAi }"
          :aria-pressed="useAi ? 'true' : 'false'"
          @click="useAi = !useAi"
        >
          <span></span>
        </button>
        <div>
          <div class="recommendGoals-aiToggleTitle">
            {{ translate('Goals_RecommendUseAi') }}
          </div>
          <p class="recommendGoals-aiToggleHelp">
            {{ translate('Goals_RecommendAiToggleHelp') }}
          </p>
        </div>
      </div>
      <button
        type="button"
        class="btn recommendGoals-run"
        @click="recommend()"
        :disabled="isLoading || isCreating"
      >
        <span class="icon-search"></span>
        {{ translate('Goals_RecommendGoals') }}
      </button>
    </div>

    <ActivityIndicator :loading="isLoading" />

    <Alert severity="warning" v-if="aiError">{{ aiError }}</Alert>

    <div v-if="hasRun && !isLoading">
      <div v-if="visibleRecommendations.length">
        <table v-content-table>
          <thead>
            <tr>
              <th>{{ translate('Goals_GoalName') }}</th>
              <th>{{ translate('Goals_GoalIsTriggeredWhen') }}</th>
              <th>{{ translate('Goals_RecommendWhy') }}</th>
              <th v-if="userCanEditGoals" class="recommendGoals-actionCell"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(rec, index) in visibleRecommendations" :key="index">
              <td>{{ rec.name }}</td>
              <td>
                <span>{{ triggerDescription(rec) }}</span>
                <code class="recommendGoals-pattern">{{ rec.pattern }}</code>
              </td>
              <td>
                <div>{{ rec.reason }}</div>
                <div class="recommendGoals-note" v-if="rec.implementationNote">
                  {{ rec.implementationNote }}
                </div>
              </td>
              <td v-if="userCanEditGoals" class="recommendGoals-actionCell">
                <button
                  type="button"
                  class="btn btn-flat"
                  @click="createOne(rec)"
                  :disabled="isCreating"
                >
                  {{ translate('Goals_RecommendCreate') }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="recommendGoals-actions" v-if="userCanEditGoals">
          <button
            type="button"
            class="btn"
            @click="createAll()"
            :disabled="isCreating"
          >
            {{ translate('Goals_RecommendCreateAll') }}
          </button>
          <button
            type="button"
            class="btn btn-flat"
            @click="dismiss()"
            :disabled="isCreating"
          >
            {{ translate('Goals_RecommendDismiss') }}
          </button>
        </div>
      </div>
      <p v-else class="recommendGoals-empty">{{ translate('Goals_RecommendNoneFound') }}</p>

      <div class="recommendGoals-manual" v-if="manualGoals.length">
        <h3 class="recommendGoals-manualTitle">{{ translate('Goals_RecommendManualTitle') }}</h3>
        <p class="recommendGoals-intro">{{ translate('Goals_RecommendManualIntro') }}</p>
        <table v-content-table>
          <thead>
            <tr>
              <th>{{ translate('Goals_GoalName') }}</th>
              <th>{{ translate('Goals_RecommendManualHowTo') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(rec, index) in manualGoals" :key="'manual-' + index">
              <td>{{ rec.name }}</td>
              <td>{{ rec.howTo }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- TODO: remove debug output before release. -->
      <details class="recommendGoals-debug" v-if="debugOutput">
        <summary>{{ translate('Goals_RecommendDebugDetails') }}</summary>
        <pre>{{ debugOutput }}</pre>
      </details>
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
  ContentTable,
} from 'CoreHome';

interface RecommendedGoal {
  name: string;
  matchAttribute: string;
  pattern: string;
  patternType: string;
  caseSensitive?: boolean;
  allowMultipleConversionsPerVisit?: boolean;
  revenue?: number;
  useEventValueAsRevenue?: boolean;
  description?: string;
  reason: string;
  source: string;
  implementationNote?: string;
}

interface RecommendedManualGoal {
  name: string;
  howTo: string;
  category: string;
}

interface RecommendGoalsState {
  useAi: boolean;
  isLoading: boolean;
  isCreating: boolean;
  hasRun: boolean;
  aiError: string|null;
  recommendations: RecommendedGoal[];
  manualGoals: RecommendedManualGoal[];
  debug: Record<string, unknown>|null;
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
  },
  directives: {
    ContentTable,
  },
  data(): RecommendGoalsState {
    return {
      useAi: true,
      isLoading: false,
      isCreating: false,
      hasRun: false,
      aiError: null,
      recommendations: [],
      manualGoals: [],
      debug: null,
    };
  },
  methods: {
    recommend() {
      this.isLoading = true;
      this.aiError = null;

      AjaxHelper.fetch({
        method: 'Goals.getRecommendedGoals',
        idSite: this.idSite,
        useAi: this.useAi ? 1 : 0,
      }).then((response) => {
        this.recommendations = (response && response.goals) || [];
        this.manualGoals = (response && response.manualGoals) || [];
        this.aiError = (response && response.aiError) || null;
        this.debug = (response && response.debug) || null;
        console.debug('[Goals recommendations] raw debug payload', this.debug);
        this.hasRun = true;
      }).catch(() => {
        this.recommendations = [];
        this.manualGoals = [];
        this.aiError = translate('Goals_RecommendError');
        this.debug = null;
        this.hasRun = true;
      }).finally(() => {
        this.isLoading = false;
      });
    },
    addGoalRequest(rec: RecommendedGoal) {
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
      });
    },
    createOne(rec: RecommendedGoal) {
      this.isCreating = true;
      this.addGoalRequest(rec).then(() => {
        window.location.reload();
      }).catch(() => {
        this.isCreating = false;
      });
    },
    createAll() {
      this.isCreating = true;
      this.visibleRecommendations.reduce(
        (promise, rec) => promise.then(() => this.addGoalRequest(rec)),
        Promise.resolve() as Promise<unknown>,
      ).then(() => {
        window.location.reload();
      }).catch(() => {
        this.isCreating = false;
      });
    },
    dismiss() {
      this.isCreating = true;
      AjaxHelper.fetch({
        method: 'Goals.dismissRecommendedGoals',
        idSite: this.idSite,
      }).then(() => {
        this.hasRun = false;
        this.recommendations = [];
        this.manualGoals = [];
        this.aiError = null;
        this.debug = null;
      }).finally(() => {
        this.isCreating = false;
      });
    },
    triggerDescription(rec: RecommendedGoal): string {
      const matchAttribute = rec.matchAttribute || 'url';
      const patternType = rec.patternType || 'contains';
      const matchLabel = this.matchAttributeLabel(matchAttribute);

      if (patternType === 'greater_than') {
        return `${matchLabel}: ${translate('General_OperationGreaterThan')} `;
      }

      if (patternType === 'exact') {
        return `${matchLabel}: ${translate('Goals_IsExactly', '')}`;
      }

      if (patternType === 'regex') {
        return `${matchLabel}: ${translate('Goals_MatchesExpression', '')}`;
      }

      return `${matchLabel}: ${translate('Goals_Contains', '')}`;
    },
    matchAttributeLabel(matchAttribute: string): string {
      const labels: Record<string, string> = {
        url: translate('Goals_VisitUrl'),
        title: translate('Goals_VisitPageTitle'),
        file: translate('Goals_Download'),
        external_website: translate('Goals_ClickOutlink'),
        event_action: `${translate('Goals_SendEvent')} (${translate('Events_EventAction')})`,
        event_category: `${translate('Goals_SendEvent')} (${translate('Events_EventCategory')})`,
        event_name: `${translate('Goals_SendEvent')} (${translate('Events_EventName')})`,
        visit_duration: translate('Goals_VisitDurationMatchAttr'),
        visit_total_actions: translate('Goals_CategoryTextGeneral_Actions'),
        visit_total_pageviews: translate('Goals_VisitUrl'),
      };

      return labels[matchAttribute] || matchAttribute;
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
    visibleRecommendations(): RecommendedGoal[] {
      const existingGoalKeys = Object.values(this.goals || {})
        .filter((goal: Record<string, unknown>) => goal.pattern)
        .map((goal: Record<string, unknown>) => this.goalKey(
          `${goal.match_attribute || 'url'}`,
          `${goal.pattern || ''}`,
        ));

      return this.recommendations.filter(
        (rec) => existingGoalKeys.indexOf(this.goalKey(rec.matchAttribute || 'url', rec.pattern)) === -1,
      );
    },
    goalCount(): number {
      return Object.values(this.goals || {}).length;
    },
    shouldShowRecommendations(): boolean {
      return this.userCanEditGoals && (this.goalCount < 3 || this.hasRun);
    },
    debugOutput(): string {
      return this.debug ? JSON.stringify(this.debug, null, 2) : '';
    },
  },
});
</script>
