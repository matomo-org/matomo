<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div v-if="isInitialLoading" class="live-widget-loading">
      <MatomoLoader />
    </div>
    <div ref="root"></div>

    <div class="visitsLiveFooter">
      <a
        :title="translate('Live_OnClickPause', translate('Live_VisitorsInRealTime'))"
        @click.prevent="pause()"
      >
        <img
          id="pauseImage"
          border="0"
          src="plugins/Live/images/pause.png"
          role="presentation"
          v-show="isStarted"
        />
      </a>
      <a
        :title="translate('Live_OnClickStart', translate('Live_VisitorsInRealTime'))"
        @click="play()"
      >
        <img
          id="playImage"
          border="0"
          src="plugins/Live/images/play.png"
          role="presentation"
          v-show="!isStarted"
        />
      </a>
      <span v-if="!disableLink">
        &nbsp;
        <a
          class="rightLink"
          :href="visitorLogUrl"
        >
          {{ translate('Live_LinkVisitorLog') }}
        </a>
      </span>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  Matomo,
  MatomoLoader,
  MatomoUrl,
} from 'CoreHome';

const { $ } = window;

const DEFAULT_INTERVAL_MS = 3000;
const MAX_INTERVAL_MS = 300000;
const MAX_ROWS = 10;

export default defineComponent({
  props: {
    liveRefreshAfterMs: Number,
    disableLink: Boolean,
  },
  components: {
    MatomoLoader,
  },
  data() {
    return {
      isStarted: true,
      isStoppedByBlur: false,
      currentInterval: 0,
      updateInterval: null as number | null,
      isInitialLoading: true,
      visibilityListenerId: null as number | null,
    };
  },
  computed: {
    visitorLogUrl() {
      return `#?${MatomoUrl.stringify({
        ...MatomoUrl.hashParsed.value,
        category: 'General_Visitors',
        subcategory: 'Live_VisitorLog',
      })}`;
    },
  },
  mounted() {
    this.currentInterval = this.getBaseInterval();

    const root = this.$refs.root as HTMLElement | undefined;
    if (root && !root.closest('.widget')) {
      Matomo.postEvent('hidePeriodSelector');
    }

    this.fetchInitialContent();
    this.setupVisibilityHandling();
  },
  beforeUnmount() {
    this.clearUpdate();
    this.teardownListInteractions();
    this.teardownVisibilityHandling();
  },
  methods: {
    getBaseInterval(): number {
      const interval = Number(this.liveRefreshAfterMs);
      return Number.isFinite(interval) && interval > 0 ? interval : DEFAULT_INTERVAL_MS;
    },
    pause() {
      this.isStarted = false;
      this.clearUpdate();
    },
    play() {
      this.isStarted = true;
      this.currentInterval = 0;
      this.update();
    },
    clearUpdate() {
      if (this.updateInterval) {
        window.clearTimeout(this.updateInterval);
        this.updateInterval = null;
      }
    },
    scheduleUpdate(delayMs: number) {
      this.clearUpdate();
      if (!this.isStarted) {
        return;
      }
      this.updateInterval = window.setTimeout(() => {
        this.update();
      }, delayMs);
    },
    update() {
      if (this.isInitialLoading) {
        return;
      }
      if (!this.isStarted) {
        return;
      }

      const root = this.$refs.root as HTMLElement | undefined;
      if (!root || !root.isConnected) {
        return;
      }

      const segment = MatomoUrl.parsed.value.segment as string;
      AjaxHelper.fetch(
        {
          module: 'Live',
          action: 'getLastVisitsStart',
          segment,
        },
        {
          format: 'html',
        },
      ).then((response) => {
        const ensured = this.ensureVisitsList(response);
        const updated = ensured ? true : this.parseResponse(response);
        const baseInterval = this.getBaseInterval();

        if (!updated) {
          this.currentInterval += baseInterval;
        } else {
          this.currentInterval = baseInterval;
          this.refreshTotalVisitors(segment);
        }

        if (this.currentInterval > MAX_INTERVAL_MS) {
          this.currentInterval = MAX_INTERVAL_MS;
        }

        if (this.isStarted && root.isConnected) {
          this.scheduleUpdate(this.currentInterval);
        }
      }).catch(() => {
        if (this.isStarted && root.isConnected) {
          this.scheduleUpdate(this.getBaseInterval());
        }
      });
    },
    ensureVisitsList(response: string): boolean {
      const root = this.$refs.root as HTMLElement | undefined;
      if (!root) {
        return false;
      }

      if (root.querySelector('#visitsLive')) {
        return false;
      }

      const parser = new DOMParser();
      const doc = parser.parseFromString(response, 'text/html');
      const visitsList = doc.querySelector('#visitsLive');
      if (!visitsList) {
        return false;
      }

      root.appendChild(visitsList);
      Matomo.helper.compileVueEntryComponents(root);
      this.setupListInteractions();

      return true;
    },
    refreshTotalVisitors(segment: string) {
      const root = this.$refs.root as HTMLElement | undefined;
      if (!root) {
        return;
      }

      AjaxHelper.fetch(
        {
          module: 'Live',
          action: 'ajaxTotalVisitors',
          segment,
        },
        {
          format: 'html',
        },
      ).then((response) => {
        const container = root.querySelector('#visitsTotal');
        const wrapper = document.createElement('div');
        wrapper.innerHTML = response;
        const newContent = wrapper.querySelector('#visitsTotal');
        if (!newContent) {
          return;
        }

        if (!container) {
          const list = root.querySelector('#visitsLive');
          if (list) {
            list.before(newContent);
          } else {
            root.prepend(newContent);
          }
          Matomo.helper.compileVueEntryComponents(root);
          return;
        }

        Matomo.helper.destroyVueComponent(container as HTMLElement);
        container.replaceWith(newContent);
        Matomo.helper.compileVueEntryComponents(root);
      });
    },
    fetchInitialContent() {
      const segment = MatomoUrl.parsed.value.segment as string;
      const visitsPromise = AjaxHelper.fetch(
        {
          module: 'Live',
          action: 'getLastVisitsStart',
          segment,
        },
        {
          format: 'html',
        },
      );
      const totalPromise = AjaxHelper.fetch(
        {
          module: 'Live',
          action: 'ajaxTotalVisitors',
          segment,
        },
        {
          format: 'html',
        },
      );

      Promise.all([visitsPromise, totalPromise])
        .then(([visitsHtml, totalHtml]) => {
          const root = this.$refs.root as HTMLElement | undefined;
          if (!root) {
            return;
          }

          root.innerHTML = `${totalHtml || ''}${visitsHtml || ''}`;
          Matomo.helper.compileVueEntryComponents(root);
          this.setupListInteractions();
        })
        .catch(() => {
          // ignore initial errors, refresh loop will retry
        })
        .finally(() => {
          this.isInitialLoading = false;
          this.scheduleUpdate(this.currentInterval);
        });
    },
    parseResponse(response: string): boolean {
      const root = this.$refs.root as HTMLElement | undefined;
      if (!root) {
        return false;
      }

      const list = root.querySelector('#visitsLive');
      if (!list) {
        return false;
      }

      const parser = new DOMParser();
      const doc = parser.parseFromString(response, 'text/html');
      const items = Array.from(doc.querySelectorAll('li.visit')) as HTMLLIElement[];
      if (!items.length) {
        return false;
      }

      this.teardownListInteractions();

      let updated = false;
      for (let i = items.length - 1; i >= 0; i -= 1) {
        const item = items[i];
        const visitId = item.getAttribute('id');
        if (visitId) {
          const existing = list.querySelector(`#${visitId}`) as HTMLElement | null;
          if (existing) {
            if (existing.getAttribute('data-hash') !== item.getAttribute('data-hash')) {
              updated = true;
            }
            existing.remove();
            list.insertBefore(item, list.firstChild);
          } else {
            updated = true;
            item.style.display = 'none';
            list.insertBefore(item, list.firstChild);
            this.fadeIn(item);
          }
        }
      }

      const visits = list.querySelectorAll('li.visit');
      for (let i = visits.length - 1; i >= MAX_ROWS; i -= 1) {
        visits[i].remove();
      }

      this.setupListInteractions();

      return updated;
    },
    fadeIn(item: HTMLElement) {
      item.classList.add('live-widget-fade-in');
      item.style.display = '';
      item.addEventListener('animationend', () => {
        item.classList.remove('live-widget-fade-in');
      }, { once: true });
    },
    getVisitsList() {
      if (!$) {
        return null;
      }

      const root = this.$refs.root as HTMLElement | undefined;
      if (!root) {
        return null;
      }

      const list = root.querySelector('#visitsLive');
      if (!list) {
        return null;
      }

      return $(list);
    },
    setupListInteractions() {
      const $list = this.getVisitsList();
      if (!$list) {
        return;
      }

      this.teardownListInteractions();

      $list.on(
        'click.liveWidgetProfile',
        '.visits-live-launch-visitor-profile',
        function onClickLaunchProfile(this: HTMLElement, e: Event) {
          e.preventDefault();
          window.broadcast.propagateNewPopoverParameter(
            'visitorProfile',
            $(this).attr('data-visitor-id'),
          );
          return false;
        },
      );

      const visits = $list.find('li.visit');
      visits.tooltip({
        items: '.visitorLogIconWithDetails',
        track: true,
        show: { delay: 100, duration: 0 },
        hide: false,
        content() {
          return $('<ul>').html($('ul', $(this)).html());
        },
        tooltipClass: 'small',
      });

      $list.tooltip({
        track: true,
        content() {
          const title = $(this).attr('title') || '';
          return window.vueSanitize(title.replace(/\n/g, '<br />'));
        },
        show: { delay: 100, duration: 0 },
        hide: false,
      });
    },
    setupVisibilityHandling() {
      const visibility = window.Visibility;
      if (!visibility || !visibility.isSupported || !visibility.isSupported()) {
        return;
      }

      this.teardownVisibilityHandling();
      this.visibilityListenerId = visibility.change(() => {
        if (visibility.hidden()) {
          this.onTabBlur();
        } else {
          this.onTabFocus();
        }
      });
    },
    teardownVisibilityHandling() {
      const visibility = window.Visibility;
      if (!visibility || typeof this.visibilityListenerId !== 'number') {
        return;
      }

      visibility.unbind(this.visibilityListenerId);
      this.visibilityListenerId = null;
    },
    teardownListInteractions() {
      const $list = this.getVisitsList();
      if (!$list) {
        return;
      }

      $list.off('click.liveWidgetProfile', '.visits-live-launch-visitor-profile');

      try {
        $('li.visit', $list).tooltip('destroy');
      } catch (e) {
        // ignore
      }

      try {
        $list.tooltip('destroy');
      } catch (e) {
        // ignore
      }
    },
    onTabBlur() {
      if (this.isStarted) {
        this.isStoppedByBlur = true;
        this.pause();
      }
    },
    onTabFocus() {
      if (this.isStoppedByBlur && !this.isStarted) {
        this.isStoppedByBlur = false;
        this.play();
      }
    },
  },
});
</script>
