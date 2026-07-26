<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="pane"
    class="debugViewDetailsPane"
    role="dialog"
    :aria-label="paneLabel"
    @keydown.esc.prevent="$emit('close')"
    @keydown.tab="onTabKey"
  >
    <div class="debugViewDetailsHeader">
      <span
        class="debugViewHitIconCircle"
        :class="typeInfo.cssClass"
        aria-hidden="true"
      >
        <img
          v-if="typeInfo.iconSvg"
          :src="typeInfo.iconSvg"
          alt=""
        />
        <span
          v-else
          :class="typeInfo.icon"
        />
      </span>
      <h3
        class="debugViewDetailsTitle"
        :title="hit.title"
      >{{ hit.title }}</h3>
      <span
        v-if="hit.isBot"
        class="debugViewBotBadge"
        :title="hit.botName || undefined"
      >{{ translate('DebugView_BotBadge') }}</span>
      <button
        ref="closeButton"
        type="button"
        class="debugViewDetailsClose"
        :aria-label="translate('DebugView_CloseDetails')"
        @click="$emit('close')"
      >
        <span class="icon-close" aria-hidden="true" />
      </button>
    </div>
    <div
      class="debugViewDetailsTabs"
      role="tablist"
    >
      <button
        type="button"
        role="tab"
        id="debugViewTabParams"
        :aria-selected="activeTab === 'params' ? 'true' : 'false'"
        aria-controls="debugViewTabPanel"
        :tabindex="activeTab === 'params' ? 0 : -1"
        class="debugViewDetailsTab"
        :class="{ 'debugViewDetailsTab--active': activeTab === 'params' }"
        @click="activeTab = 'params'"
        @keydown.left.prevent="toggleTab()"
        @keydown.right.prevent="toggleTab()"
      >{{ translate('DebugView_ParametersTab') }}</button>
      <button
        type="button"
        role="tab"
        id="debugViewTabProcessed"
        :aria-selected="activeTab === 'processed' ? 'true' : 'false'"
        aria-controls="debugViewTabPanel"
        :tabindex="activeTab === 'processed' ? 0 : -1"
        class="debugViewDetailsTab"
        :class="{ 'debugViewDetailsTab--active': activeTab === 'processed' }"
        @click="activeTab = 'processed'"
        @keydown.left.prevent="toggleTab()"
        @keydown.right.prevent="toggleTab()"
      >{{ translate('DebugView_ProcessedTab') }}</button>
    </div>
    <div
      class="debugViewDetailsBody"
      role="tabpanel"
      id="debugViewTabPanel"
      :aria-labelledby="activeTab === 'params' ? 'debugViewTabParams' : 'debugViewTabProcessed'"
    >
      <template v-if="activeTab === 'params'">
        <template v-if="hasTrackingParams">
          <h4 class="debugViewDetailsSection">{{ translate('DebugView_TrackingParameters') }}</h4>
          <DetailRows
            :entries="trackingParamsEntries"
            :depth="0"
          />
        </template>
        <template v-if="hasDefaultParams">
          <h4 class="debugViewDetailsSection">{{ translate('DebugView_DefaultParameters') }}</h4>
          <DetailRows
            :entries="defaultParamsEntries"
            :depth="0"
          />
        </template>
        <template v-if="hasOtherParams">
          <h4 class="debugViewDetailsSection">{{ translate('DebugView_OtherParameters') }}</h4>
          <DetailRows
            :entries="otherParamsEntries"
            :depth="0"
          />
        </template>
      </template>
      <template v-if="activeTab === 'processed'">
        <template v-if="visitLoadState === 'bot'">
          <h4 class="debugViewDetailsSection">{{ translate('DebugView_ProcessedDetails') }}</h4>
          <p class="debugViewVisitUnavailable">
            {{ translate('DebugView_ProcessedNotAvailableBot') }}
          </p>
        </template>
        <div
          v-else-if="visitLoadState === 'loading'"
          class="debugViewLazyLoading"
        ><MatomoLoader /></div>
        <template v-else-if="visitLoadState === 'loaded'">
          <template v-if="hit.type === 'sessionRecording'">
            <h4 class="debugViewDetailsSection">{{ translate('DebugView_ProcessedDetails') }}</h4>
            <p class="debugViewVisitUnavailable">
              {{ translate('DebugView_ProcessedCannotBeShown') }}
            </p>
          </template>
          <template v-else-if="matchedActions.length">
            <h4 class="debugViewDetailsSection">{{ translate('DebugView_ProcessedDetails') }}</h4>
            <p
              v-if="hit.type === 'media' || hit.type === 'form'"
              class="debugViewProcessedHint"
            >{{ translate('DebugView_ProcessedAggregatedHint') }}</p>
            <DetailRows
              v-for="(action, index) in matchedActions"
              :key="index"
              :entries="action"
              :depth="0"
            />
          </template>
          <h4 class="debugViewDetailsSection">
            {{ translate('DebugView_ProcessedVisitDetails') }}
          </h4>
          <DetailRows
            :entries="visitEntries"
            :depth="0"
          />
        </template>
        <p
          v-else
          class="debugViewVisitUnavailable"
        >{{ translate('DebugView_VisitNotAvailable') }}</p>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate, AjaxHelper, MatomoLoader } from 'CoreHome';
import DetailRows from './DetailRows.vue';
import { Hit } from '../types';
import getHitTypeInfo, { HitTypeInfo } from '../hitTypes';

type VisitDetails = Record<string, unknown>;

export default defineComponent({
  props: {
    hit: {
      type: Object as PropType<Hit>,
      required: true,
    },
    idSite: {
      type: Number,
      required: true,
    },
  },
  components: {
    DetailRows,
    MatomoLoader,
  },
  emits: ['close'],
  data() {
    return {
      activeTab: 'params',
      visitDetails: null as VisitDetails|null,
      visitLoadState: 'loading' as 'loading'|'loaded'|'unavailable'|'bot',
    };
  },
  computed: {
    typeInfo(): HitTypeInfo {
      return getHitTypeInfo(this.hit.type, this.hit.trackingParams);
    },
    paneLabel(): string {
      return this.hit.title || translate('DebugView_HitDetails');
    },
    trackingParamsEntries(): Record<string, unknown> {
      return this.hit.trackingParams || {};
    },
    hasTrackingParams(): boolean {
      return !!this.hit.trackingParams
        && Object.keys(this.hit.trackingParams).length > 0;
    },
    defaultParamsEntries(): Record<string, unknown> {
      return this.hit.trackingParamsDefaults || {};
    },
    hasDefaultParams(): boolean {
      return !!this.hit.trackingParamsDefaults
        && Object.keys(this.hit.trackingParamsDefaults).length > 0;
    },
    otherParamsEntries(): Record<string, unknown> {
      return this.hit.trackingParamsOther || {};
    },
    hasOtherParams(): boolean {
      return !!this.hit.trackingParamsOther
        && Object.keys(this.hit.trackingParamsOther).length > 0;
    },
    // the processed Live actions belonging to this raw request. Core actions
    // are matched via the log_link_visit_action id (a pageview and the goal it
    // triggered share it); media, form and crash actions have no such row, so
    // they are matched via their own identifiers. Heatmap & Session Recording
    // requests are deliberately not matched (nothing sensible to show).
    matchedActions(): Record<string, unknown>[] {
      if (!this.visitDetails || this.hit.type === 'sessionRecording') {
        return [];
      }

      const rawActions = this.visitDetails.actionDetails;
      if (!Array.isArray(rawActions)) {
        return [];
      }

      const actions = rawActions as Record<string, unknown>[];
      const params = this.hit.trackingParams || {};

      switch (this.hit.type) {
        case 'media': {
          // the media resource (the request's ma_re) is exposed as the media
          // action's url; the view id does not survive Live's pipeline
          const resource = params.ma_re;
          if (!resource) {
            return [];
          }
          return actions.filter(
            (action) => action.type === 'media' && String(action.url) === String(resource),
          );
        }
        case 'crash': {
          // no shared id exists; the error message (prefix, as stored values
          // are truncated) identifies the crash
          const cra = typeof params.cra === 'string' ? params.cra : '';
          if (!cra) {
            return [];
          }
          const prefix = cra.replace(/\.\.\.$/, '');
          return actions.filter(
            (action) => action.type === 'crash'
              && typeof action.message === 'string'
              && (action.message === cra || action.message.indexOf(prefix) === 0),
          );
        }
        case 'form': {
          // form entries carry the pageview id and the numeric form id
          const pvId = params.pv_id;
          const faId = params.fa_id;
          if (pvId) {
            return actions.filter(
              (action) => action.type === 'form' && String(action.idpageview) === String(pvId),
            );
          }
          if (faId) {
            return actions.filter(
              (action) => action.type === 'form' && String(action.formId) === String(faId),
            );
          }
          return [];
        }
        default: {
          if (!this.hit.idLinkVa) {
            return [];
          }
          const idLinkVa = Number(this.hit.idLinkVa);
          return actions.filter(
            (action) => Number(action.pageId) === idLinkVa
              || Number(action.goalPageId) === idLinkVa,
          );
        }
      }
    },
    visitEntries(): Record<string, unknown> {
      if (!this.visitDetails) {
        return {};
      }

      const entries = { ...this.visitDetails };
      delete entries.actionDetails;
      return entries;
    },
  },
  watch: {
    'hit.idRawRequest': function onHitChange() {
      this.activeTab = 'params';
      this.loadVisit();
      this.$nextTick(() => this.focusCloseButton());
    },
  },
  mounted() {
    this.focusCloseButton();
    this.loadVisit();
  },
  methods: {
    // lazily loads the visit this hit belongs to, directly from the browser via
    // the visitId segment; the raw parameters render without waiting for it
    loadVisit(): void {
      this.visitDetails = null;

      // bot requests record no visit at all — nothing to load, the Processed
      // tab explains why instead
      if (this.hit.isBot) {
        this.visitLoadState = 'bot';
        return;
      }

      if (!this.hit.idVisit) {
        this.visitLoadState = 'unavailable';
        return;
      }

      this.visitLoadState = 'loading';
      const requestedHitId = this.hit.idRawRequest;

      AjaxHelper.fetch<VisitDetails[]>(
        {
          method: 'Live.getLastVisitsDetails',
          idSite: this.idSite,
          segment: `visitId==${this.hit.idVisit}`,
          filter_limit: 1,
          // pin the date window explicitly: AjaxHelper would otherwise inject
          // the page URL's current period/date (e.g. a stale date=yesterday),
          // scoping the lookup so the just-tracked visit is never found.
          // last2 (yesterday + today) always covers the <= 60 min stream
          // window, including around the site-timezone midnight
          period: 'range',
          date: 'last2',
        },
        { createErrorNotification: false },
      ).then((visits) => {
        if (requestedHitId !== this.hit.idRawRequest) {
          return; // a different hit was opened meanwhile
        }

        const visit = Array.isArray(visits) && visits.length ? visits[0] : null;
        if (visit) {
          this.visitDetails = visit;
          this.visitLoadState = 'loaded';
        } else {
          this.visitLoadState = 'unavailable';
        }
      }).catch(() => {
        if (requestedHitId === this.hit.idRawRequest) {
          this.visitLoadState = 'unavailable';
        }
      });
    },
    focusCloseButton(): void {
      const closeButton = this.$refs.closeButton as HTMLButtonElement|undefined;
      if (closeButton) {
        closeButton.focus();
      }
    },
    toggleTab(): void {
      this.activeTab = this.activeTab === 'params' ? 'processed' : 'params';
      this.$nextTick(() => {
        const pane = this.$refs.pane as HTMLElement|undefined;
        const active = pane && pane.querySelector<HTMLButtonElement>('[role="tab"][aria-selected="true"]');
        if (active) {
          active.focus();
        }
      });
    },
    // below the responsive breakpoint the pane overlays the stream, so keep
    // focus inside it while it is open (the content behind it is obscured)
    onTabKey(event: KeyboardEvent): void {
      if (!window.matchMedia || !window.matchMedia('(max-width: 960px)').matches) {
        return;
      }

      const pane = this.$refs.pane as HTMLElement|undefined;
      if (!pane) {
        return;
      }

      const focusable = Array.from(pane.querySelectorAll<HTMLElement>(
        'button:not([tabindex="-1"]), [href], input, select, textarea, [tabindex="0"]',
      )).filter((el) => el.offsetParent !== null);
      if (!focusable.length) {
        return;
      }

      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    },
  },
});
</script>
