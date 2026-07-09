<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="recommendGoals-card"
    :class="{ 'recommendGoals-card--added': accepted }"
  >
    <div class="recommendGoals-cardMain">
      <span class="recommendGoals-cardIcon">
        <span :class="goalIcon"></span>
      </span>
      <div class="recommendGoals-cardBody">
        <div class="recommendGoals-cardTitle">
          <span class="recommendGoals-cardName">{{ rec.name }}</span>
          <span class="recommendGoals-chip" v-if="rec.category">{{ rec.category }}</span>
          <span v-if="needsSetup"
            class="recommendGoals-chip recommendGoals-chip--setup"
            :title="translate('Goals_RecommendNeedsSetupHelp')">
            {{ translate('Goals_RecommendNeedsSetup') }}
          </span>
        </div>
        <p class="recommendGoals-cardReason">{{ rec.reason }}</p>
        <p class="recommendGoals-cardTrigger">
          {{ triggerDescription }}
          <code class="recommendGoals-pattern">{{ displayPattern }}</code>
        </p>
      </div>
      <div class="recommendGoals-cardActions">
        <span v-if="accepted" class="recommendGoals-accepted">
          <span class="icon-ok"></span>
          {{ translate('General_Added') }}
        </span>
        <template v-else>
          <button
            type="button"
            class="btn"
            @click="$emit('create')"
            :disabled="busy"
          >
            {{ creating
              ? translate('Goals_RecommendCreating')
              : translate('Goals_RecommendCreate') }}
          </button>
          <button
            type="button"
            class="recommendGoals-dismissBtn"
            :title="translate('Goals_RecommendDismissSuggestion')"
            :aria-label="translate('Goals_RecommendDismissSuggestion')"
            @click="$emit('dismiss')"
            :disabled="busy"
          >
            <span class="icon-close"></span>
          </button>
        </template>
      </div>
    </div>
    <details class="recommendGoals-evidence" v-if="hasEvidence">
      <summary>
        <span class="icon-chevron-right"></span>
        {{ translate('Goals_RecommendWhySuggested') }}
      </summary>
      <div class="recommendGoals-evidenceBody">
        <ul v-if="rec.evidence && rec.evidence.length">
          <li v-for="(item, index) in rec.evidence" :key="index">{{ item }}</li>
        </ul>
        <p class="recommendGoals-evidenceNote" v-if="needsSetup && rec.implementationNote">
          <span class="recommendGoals-evidenceLabel">
            {{ translate('Goals_RecommendManualHowTo') }}
          </span>
          {{ rec.implementationNote }}
        </p>
      </div>
    </details>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import { translate } from 'CoreHome';
import type { RecommendedGoal } from './types';

export default defineComponent({
  props: {
    rec: {
      type: Object as PropType<RecommendedGoal>,
      required: true,
    },
    accepted: Boolean,
    creating: Boolean,
    busy: Boolean,
  },
  emits: ['create', 'dismiss'],
  computed: {
    needsSetup(): boolean {
      return (this.rec.matchAttribute || '').indexOf('event_') === 0;
    },
    hasEvidence(): boolean {
      return !!((this.rec.evidence && this.rec.evidence.length)
        || (this.needsSetup && this.rec.implementationNote));
    },
    goalIcon(): string {
      const matchAttribute = this.rec.matchAttribute || 'url';
      if (matchAttribute === 'file') {
        return 'icon-download';
      }

      if (matchAttribute === 'external_website') {
        return 'icon-outlink';
      }

      if (matchAttribute.indexOf('event_') === 0) {
        return 'icon-form';
      }

      if (matchAttribute.indexOf('visit_') === 0) {
        return 'icon-clock';
      }

      return 'icon-goal';
    },
    displayPattern(): string {
      if ((this.rec.matchAttribute || '') === 'visit_duration') {
        return translate('Intl_NMinutes', this.rec.pattern);
      }

      return this.rec.pattern;
    },
    triggerDescription(): string {
      const matchAttribute = this.rec.matchAttribute || 'url';
      const patternType = this.rec.patternType || 'contains';
      const matchLabel = this.matchAttributeLabel(matchAttribute);

      if (patternType === 'greater_than') {
        return `${matchLabel}: ${translate('General_OperationGreaterThan')}`;
      }

      if (patternType === 'exact') {
        return `${matchLabel}: ${translate('Goals_IsExactly', '').trim()}`;
      }

      if (patternType === 'regex') {
        return `${matchLabel}: ${translate('Goals_MatchesExpression', '').trim()}`;
      }

      return `${matchLabel}: ${translate('Goals_Contains', '').trim()}`;
    },
  },
  methods: {
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
  },
});
</script>
