<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="debugViewPage"
    ref="root"
    @keydown.esc="onPageEscape"
  >
    <ContentBlock :content-title="translate('DebugView_DebugView')">
      <p class="debugViewIntro">{{ translate('DebugView_PageDescription') }}</p>
      <div class="debugViewTopBar">
        <div class="debugViewLiveStatus">
          <span
            class="debugViewLiveDot"
            :class="{ 'debugViewLiveDot--paused': paused }"
            aria-hidden="true"
          />
          <span class="debugViewLiveText">{{ liveStatusText }}</span>
          <button
            type="button"
            class="debugViewPauseButton"
            :aria-label="paused ? translate('DebugView_Resume') : translate('DebugView_Pause')"
            @click="togglePaused()"
          >
            <span
              :class="paused ? 'icon-play' : 'icon-pause'"
              aria-hidden="true"
            />
          </button>
        </div>
      </div>
      <Alert
        v-if="pollingError !== null"
        severity="warning"
      >
        <strong>{{ translate('DebugView_PollingErrorTitle') }}</strong><br />
        {{ translate('DebugView_PollingErrorMessage') }}
        <span
          v-if="pollingError"
          class="debugViewErrorDetails"
        >{{ pollingError }}</span>
      </Alert>
      <ActivityIndicator :loading="isInitialLoading" />
      <div
        v-if="!isInitialLoading"
        class="debugViewLayout"
      >
        <MinutesRail
          :buckets="minuteBuckets"
          :selected-minute="selectedMinute"
          :pending-count="buffer.length"
          :paused="paused"
          @select-minute="onSelectMinute"
        />
        <div class="debugViewStreamColumn">
          <div
            v-if="!hits.length"
            class="debugViewEmptyState"
          >
            <span
              class="icon-search debugViewEmptyIcon"
              aria-hidden="true"
            />
            <p class="debugViewEmptyHeadline">
              <span
                class="debugViewLiveDot"
                :class="{ 'debugViewLiveDot--paused': paused }"
                aria-hidden="true"
              />
              {{ translate('DebugView_WaitingForRequests') }}
            </p>
            <p class="debugViewEmptyHint">{{ translate('DebugView_WaitingForRequestsHint') }}</p>
          </div>
          <HitsStream
            v-else
            ref="stream"
            :hits="hits"
            :selected-hit-id="selectedHit ? selectedHit.idRawRequest : null"
            @open-hit="onOpenHit"
          />
        </div>
        <HitDetailsPane
          v-if="selectedHit"
          :hit="selectedHit"
          :id-site="idSite"
          @close="onCloseDetails"
        />
      </div>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  ContentBlock,
  ActivityIndicator,
  Alert,
} from 'CoreHome';
import MinutesRail from '../MinutesRail/MinutesRail.vue';
import HitsStream from '../HitsStream/HitsStream.vue';
import HitDetailsPane from '../HitDetailsPane/HitDetailsPane.vue';
import { AutoRefreshController } from '../AutoRefreshController/AutoRefreshController';
import { ApiResponse, Hit, MinuteBucket } from '../types';

interface HitsStreamComponent {
  scrollToMinute: (minuteStart: number) => void;
  focusHit: (hitId: string) => void;
}

// responses/errors are tagged with the stream generation they were requested
// for, so an in-flight poll can never pollute the stream after a reset
interface TaggedResponse {
  generation: number;
  response: ApiResponse;
}

interface TaggedError {
  generation: number;
  error: unknown;
}

const MAX_RENDERED_HITS = 500;
const RAIL_TICK_MS = 15000;

// hit ids are BIGINT UNSIGNED decimal strings from the API and may exceed
// Number's safe integer range — compare them without ever converting: longer
// decimal means bigger, equal length falls back to a lexical comparison
function compareDecimalIds(a: string, b: string): number {
  const left = /^\d+$/.test(a) ? a.replace(/^0+(?=\d)/, '') : '0';
  const right = /^\d+$/.test(b) ? b.replace(/^0+(?=\d)/, '') : '0';
  if (left.length !== right.length) {
    return left.length - right.length;
  }
  if (left === right) {
    return 0;
  }
  return left < right ? -1 : 1;
}

export default defineComponent({
  props: {
    idSite: {
      type: Number,
      required: true,
    },
    refreshInterval: {
      type: Number,
      default: 5,
    },
    lastMinutes: {
      type: Number,
      default: 30,
    },
  },
  components: {
    ActivityIndicator,
    Alert,
    ContentBlock,
    HitDetailsPane,
    HitsStream,
    MinutesRail,
  },
  data() {
    return {
      hits: [] as Hit[],
      buffer: [] as Hit[],
      paused: false,
      selectedHit: null as Hit|null,
      selectedMinute: null as number|null,
      isInitialLoading: true,
      pollingError: null as string|null,
      timezone: '',
      serverTimeOffset: 0,
      nowTick: Math.floor(Date.now() / 1000),
      // incremental polling cursor; a decimal string, never a Number (see
      // compareDecimalIds)
      lastId: '0',
      seenHits: new Map<string, number>(),
      // minute-rail counts, tracked separately from `hits` so the rail stays
      // accurate even when the rendered stream is capped at MAX_RENDERED_HITS
      minuteCounts: new Map<number, number>(),
      streamGeneration: 0,
      refreshController: null as AutoRefreshController<TaggedResponse>|null,
      railTimer: null as number|null,
      isUnmounted: false,
    };
  },
  computed: {
    liveStatusText(): string {
      return this.paused
        ? translate('DebugView_StreamPaused')
        : translate('DebugView_StreamLive');
    },
    lastMinutesCount(): number {
      const minutes = Number(this.lastMinutes);
      return Number.isFinite(minutes) && minutes > 0 ? Math.floor(minutes) : 30;
    },
    // "now" in server time: browser now corrected by the clock offset tracked
    // from every API response
    serverNow(): number {
      return this.nowTick + this.serverTimeOffset;
    },
    minuteBuckets(): MinuteBucket[] {
      const formatter = this.createMinuteFormatter();
      const counts = this.minuteCounts;

      const nowMinuteStart = Math.floor(this.serverNow / 60) * 60;
      const buckets: MinuteBucket[] = [];
      for (let i = 0; i < this.lastMinutesCount; i += 1) {
        const minuteStart = nowMinuteStart - (i * 60);
        const count = counts.get(minuteStart) || 0;
        buckets.push({
          minuteStart,
          count,
          label: formatter.format(new Date(minuteStart * 1000)),
          showLabel: i % 5 === 0 || count > 0,
        });
      }

      return buckets;
    },
  },
  mounted() {
    this.initRefreshController();
    if (this.refreshController) {
      this.refreshController.start();
    }

    // slide the minute buckets forward even when no new hits arrive
    this.railTimer = window.setInterval(() => {
      this.nowTick = Math.floor(Date.now() / 1000);
      this.pruneOldHits();
    }, RAIL_TICK_MS);
  },
  beforeUnmount() {
    this.isUnmounted = true;

    if (this.railTimer) {
      window.clearInterval(this.railTimer);
      this.railTimer = null;
    }

    if (this.refreshController) {
      this.refreshController.destroy();
      this.refreshController = null;
    }
  },
  methods: {
    createMinuteFormatter(): Intl.DateTimeFormat {
      const options: Intl.DateTimeFormatOptions = { hour: 'numeric', minute: '2-digit' };
      try {
        return new Intl.DateTimeFormat(
          undefined,
          { ...options, timeZone: this.timezone || undefined },
        );
      } catch (e) {
        return new Intl.DateTimeFormat(undefined, options);
      }
    },
    initRefreshController() {
      this.refreshController = new AutoRefreshController<TaggedResponse>({
        getBaseInterval: () => {
          const seconds = Number(this.refreshInterval);
          return Number.isFinite(seconds) && seconds > 0 ? seconds * 1000 : 5000;
        },
        shouldRun: () => {
          if (this.isUnmounted) {
            return false;
          }

          const root = this.$refs.root as HTMLElement|undefined;
          return Boolean(root && root.isConnected);
        },
        request: () => {
          const generation = this.streamGeneration;
          return this.fetchHits().then(
            (response) => ({ generation, response }),
            (error) => {
              const tagged = new Error('DebugView poll failed') as Error & TaggedError;
              tagged.generation = generation;
              tagged.error = error;
              throw tagged;
            },
          );
        },
        handleResponse: (tagged) => this.processResponse(tagged),
        onError: (error) => this.handlePollingError(error),
      });
    },
    fetchHits(): Promise<ApiResponse> {
      return AjaxHelper.fetch<ApiResponse>(
        {
          method: 'DebugView.getRecentHits',
          idSite: this.idSite,
          lastMinutes: this.lastMinutesCount,
          // ids are monotonic, so a strict id cursor can never lose late or
          // same-second hits — no overlap window needed
          minId: this.lastId,
        },
        { createErrorNotification: false },
      );
    },
    processResponse(tagged: TaggedResponse): { updated: boolean } {
      if (tagged.generation !== this.streamGeneration) {
        // stale response from before a reset — discard entirely
        return { updated: false };
      }

      const { response } = tagged;
      this.isInitialLoading = false;
      this.pollingError = null;
      this.nowTick = Math.floor(Date.now() / 1000);

      if (response && Number.isFinite(response.serverTime)) {
        this.serverTimeOffset = response.serverTime - this.nowTick;
      }

      if (response && response.timezone) {
        this.timezone = response.timezone;
      }

      const incoming = ((response && response.hits) || []).filter(
        (hit) => !!hit && !!hit.idRawRequest && !this.seenHits.has(hit.idRawRequest),
      );

      incoming.forEach((hit) => {
        this.seenHits.set(hit.idRawRequest, hit.timestamp);
        if (compareDecimalIds(hit.idRawRequest, this.lastId) > 0) {
          this.lastId = hit.idRawRequest;
        }
      });

      if (incoming.length) {
        if (this.paused) {
          this.buffer = this.buffer.concat(incoming);
        } else {
          this.hits = this.sortNewestFirst(this.hits.concat(incoming));
          this.addToRailCounts(incoming);
        }
      }

      this.pruneOldHits();

      return { updated: incoming.length > 0 };
    },
    handlePollingError(taggedError: unknown) {
      let inner: unknown = taggedError;
      if (taggedError && typeof taggedError === 'object' && 'generation' in taggedError) {
        const tagged = taggedError as TaggedError;
        if (tagged.generation !== this.streamGeneration) {
          // failure of a request from before a reset — ignore
          return;
        }
        inner = tagged.error;
      }

      this.isInitialLoading = false;

      let message = '';
      if (inner && typeof inner === 'object' && 'message' in inner) {
        const errorMessage = (inner as Error).message;
        if (typeof errorMessage === 'string') {
          message = errorMessage;
        }
      }

      this.pollingError = message;
    },
    addToRailCounts(hits: Hit[]) {
      hits.forEach((hit) => {
        const minuteStart = Math.floor(hit.timestamp / 60) * 60;
        this.minuteCounts.set(minuteStart, (this.minuteCounts.get(minuteStart) || 0) + 1);
      });
    },
    sortNewestFirst(hits: Hit[]): Hit[] {
      return hits.slice().sort((lhs, rhs) => {
        if (rhs.timestamp !== lhs.timestamp) {
          return rhs.timestamp - lhs.timestamp;
        }
        return compareDecimalIds(rhs.idRawRequest, lhs.idRawRequest);
      });
    },
    pruneOldHits() {
      const windowStart = this.serverNow - (this.lastMinutesCount * 60);

      this.hits = this.hits
        .filter((hit) => hit.timestamp >= windowStart)
        .slice(0, MAX_RENDERED_HITS);

      if (this.buffer.length) {
        this.buffer = this.buffer.filter((hit) => hit.timestamp >= windowStart);
      }

      this.seenHits.forEach((timestamp, id) => {
        if (timestamp < windowStart) {
          this.seenHits.delete(id);
        }
      });

      this.minuteCounts.forEach((count, minuteStart) => {
        if (minuteStart + 60 <= windowStart) {
          this.minuteCounts.delete(minuteStart);
        }
      });
    },
    togglePaused() {
      if (this.paused) {
        this.resumeStream();
      } else {
        this.paused = true;
      }
    },
    resumeStream() {
      if (this.buffer.length) {
        this.hits = this.sortNewestFirst(this.hits.concat(this.buffer));
        this.addToRailCounts(this.buffer);
        this.buffer = [];
        this.pruneOldHits();
      }
      this.paused = false;
    },
    onPageEscape() {
      if (this.selectedHit) {
        this.onCloseDetails();
      }
    },
    onSelectMinute(minuteStart: number) {
      this.selectedMinute = minuteStart;

      const stream = this.$refs.stream as unknown as HitsStreamComponent|undefined;
      if (stream) {
        stream.scrollToMinute(minuteStart);
      }
    },
    onOpenHit(hit: Hit) {
      this.selectedHit = hit;
    },
    onCloseDetails() {
      const closedHit = this.selectedHit;
      this.selectedHit = null;

      if (!closedHit) {
        return;
      }

      this.$nextTick(() => {
        const stream = this.$refs.stream as unknown as HitsStreamComponent|undefined;
        if (stream) {
          stream.focusHit(closedHit.idRawRequest);
        }
      });
    },
  },
});
</script>
