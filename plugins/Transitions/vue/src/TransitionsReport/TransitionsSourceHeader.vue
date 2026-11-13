<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    v-if="hasContent"
    class="transitionsSourceHeader"
  >
    <div class="transitionsSourceHeader__bar">
      <span
        v-if="showBadge"
        class="transitionsSourceHeader__badge"
        :class="{
          'transitionsSourceHeader__badge--preprocessed': source.preprocessed,
          'transitionsSourceHeader__badge--raw': !source.preprocessed,
        }"
        :title="badgeTooltip || undefined"
        :tabindex="badgeTooltip ? 0 : undefined"
        :aria-describedby="badgeTooltip ? badgeHelpId : undefined"
      >{{ translate(source.preprocessed
        ? 'Transitions_PreprocessedBadge' : 'Transitions_RawDataBadge') }}</span>
      <span
        v-if="showBadge && badgeTooltip"
        :id="badgeHelpId"
        class="u-visuallyHidden"
      >{{ badgeTooltip }}</span>

      <div
        v-if="status"
        class="transitionsSourceHeader__actions"
      >
        <button
          v-if="status.selected"
          type="button"
          class="transitionsSourceHeader__toggle"
          :disabled="isSaving || isConfirming"
          @click="confirmStop"
        >
          <span aria-hidden="true">&#9733;</span>
          {{ translate('Transitions_StopPreprocessing') }}
        </button>

        <template v-else>
          <span
            class="transitionsSourceHeader__toggleWrap"
            :title="isLimitReached ? limitReachedHelp : undefined"
          >
            <button
              type="button"
              class="transitionsSourceHeader__toggle"
              :disabled="isSaving || isLimitReached"
              :aria-describedby="isLimitReached ? limitHelpId : undefined"
              @click="add"
            >
              <span aria-hidden="true">&#9734;</span>
              {{ translate('Transitions_PreprocessThisPage') }}
            </button>
          </span>
          <span
            v-if="isLimitReached"
            :id="limitHelpId"
            class="u-visuallyHidden"
          >{{ limitReachedHelp }}</span>
          <a
            v-if="isLimitReached"
            class="transitionsSourceHeader__manageLink"
            :href="manageUrl"
          >{{ translate('Transitions_ManagePreprocessedPages') }}</a>
          <span
            v-else
            class="transitionsSourceHeader__usage"
          >{{ usageLabel }}</span>
        </template>
      </div>
    </div>

    <div
      v-if="isConfirming && status?.selected"
      class="transitionsSourceHeader__confirm"
      role="group"
      :aria-label="confirmLabel"
    >
      <div class="transitionsSourceHeader__confirmText">
        <strong>{{ confirmLabel }}</strong>
        {{ translate('Transitions_StopPreprocessingHelp') }}
      </div>
      <div class="transitionsSourceHeader__confirmActions">
        <button
          type="button"
          class="btn-flat transitionsSourceHeader__cancel"
          :disabled="isSaving"
          @click="isConfirming = false"
        >{{ translate('General_Cancel') }}</button>
        <button
          type="button"
          class="btn transitionsSourceHeader__stop"
          :disabled="isSaving"
          @click="stop"
        >{{ translate('Transitions_StopPreprocessing') }}</button>
      </div>
    </div>

    <p
      v-if="message"
      class="transitionsSourceHeader__message"
      role="status"
    >{{ message }}</p>
    <p
      v-if="error"
      class="transitionsSourceHeader__error"
      role="alert"
    >{{ error }}</p>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  AjaxHelper,
  Matomo,
  MatomoUrl,
  NumberFormatter,
  translate,
} from 'CoreHome';
import { TransitionsDataSource } from './types';
import apiErrorText from '../apiErrorText';

/** Transitions.getPreprocessedPageStatus */
export interface PreprocessedPageStatus {
  selected: boolean;
  idPage: number|null;
  used: number;
  limit: number;
  rearchiveMonths: number;
}

export interface TransitionsSourceHeaderState {
  status: PreprocessedPageStatus|null;
  isSaving: boolean;
  isConfirming: boolean;
  message: string;
  error: string;
  siteHasPages: boolean;
}

let instanceCount = 0;

/**
 * The report's source badge, shown to everyone, and for admins of the website the control that
 * adds the page URL to the pre-processed pages or removes it. Page titles are never pre-processed,
 * so they get the badge only.
 */
export default defineComponent({
  props: {
    source: {
      type: Object as PropType<TransitionsDataSource>,
      required: true,
    },
    actionType: {
      type: String,
      required: true,
    },
    actionName: {
      type: String,
      required: true,
    },
    idSite: {
      type: [String, Number],
      default: '',
    },
  },
  data(): TransitionsSourceHeaderState {
    return {
      status: null,
      isSaving: false,
      isConfirming: false,
      message: '',
      error: '',
      siteHasPages: !!Matomo.transitionsHasPreprocessedPages,
    };
  },
  setup() {
    instanceCount += 1;
    return {
      limitHelpId: `transitionsSourceHeaderLimit${instanceCount}`,
      badgeHelpId: `transitionsSourceHeaderBadge${instanceCount}`,
      // each status load starts a generation; a response of an older one is dropped
      statusLoads: { latest: 0 },
    };
  },
  created() {
    this.loadStatus();
  },
  watch: {
    actionName() {
      this.loadStatus();
    },
    actionType() {
      this.loadStatus();
    },
    idSite() {
      this.loadStatus();
    },
  },
  computed: {
    /**
     * The role in the page's globals is the one for the website in the URL. A report of another
     * website, eg one opened from Overlay, gets the control only for super users.
     */
    canManage(): boolean {
      if (this.actionType !== 'url' || !this.idSite) {
        return false;
      }

      if (Matomo.hasSuperUserAccess) {
        return true;
      }

      return Matomo.userCurrentRole === 'admin' && `${this.idSite}` === `${Matomo.idSite}`;
    },
    /**
     * Raw data is what every report shows without the feature, so it is only pointed out on
     * websites that have pre-processed pages. Stored data is always pointed out.
     */
    showBadge(): boolean {
      if (!this.source.loaded) {
        return false;
      }

      return this.source.preprocessed || (this.siteHasPages && this.isSiteInUrl);
    },
    /** The page's globals describe the website in the URL only. */
    isSiteInUrl(): boolean {
      return `${this.idSite}` === `${Matomo.idSite}`;
    },
    hasContent(): boolean {
      return this.showBadge || !!this.status || !!this.message || !!this.error;
    },
    badgeTooltip(): string {
      if (!this.source.preprocessed || !this.source.preprocessedAt) {
        return '';
      }

      return translate('Transitions_PreprocessedBadgeTooltip', this.source.preprocessedAt);
    },
    isLimitReached(): boolean {
      return !!this.status && !this.status.selected && this.status.used >= this.status.limit;
    },
    limitReachedHelp(): string {
      return translate(
        'Transitions_PreprocessedPagesLimitReachedHelp',
        NumberFormatter.formatNumber(this.status?.limit ?? 0),
      );
    },
    usageLabel(): string {
      return translate(
        'Transitions_PreprocessedPagesUsageShort',
        NumberFormatter.formatNumber(this.status?.used ?? 0),
        NumberFormatter.formatNumber(this.status?.limit ?? 0),
      );
    },
    /**
     * A page opened from a report row may come without its scheme; the API wants one, and matches
     * http and https alike.
     */
    pageUrl(): string {
      return /^https?:\/\//i.test(this.actionName) ? this.actionName : `http://${this.actionName}`;
    },
    confirmLabel(): string {
      return translate(
        'Transitions_StopPreprocessingConfirm',
        this.actionName.replace(/^https?:\/\/(www\.)?/i, ''),
      );
    },
    manageUrl(): string {
      return `?${MatomoUrl.stringify({
        module: 'Transitions',
        action: 'manage',
        idSite: this.idSite,
        period: MatomoUrl.parsed.value.period as string,
        date: MatomoUrl.parsed.value.date as string,
      })}`;
    },
  },
  methods: {
    /**
     * Whether a response still belongs to the page on screen. The embedded report reuses this
     * component, also for a page it showed before.
     */
    isCurrent(load: number): boolean {
      return load === this.statusLoads.latest;
    },
    /** Keeps the raw data badge, and the row action's period check, in step with the list. */
    updateSiteHasPages(used: number) {
      if (!this.isSiteInUrl) {
        return;
      }

      this.siteHasPages = used > 0;
      Matomo.transitionsHasPreprocessedPages = used > 0;
    },
    loadStatus() {
      this.statusLoads.latest += 1;
      const load = this.statusLoads.latest;
      this.status = null;
      this.isSaving = false;
      this.isConfirming = false;
      this.message = '';
      this.error = '';

      if (!this.canManage) {
        return;
      }

      const { idSite, pageUrl } = this;
      AjaxHelper.fetch<PreprocessedPageStatus>(
        {
          method: 'Transitions.getPreprocessedPageStatus',
          idSite,
          pageUrl,
        },
        { createErrorNotification: false },
      ).then((status) => {
        if (this.isCurrent(load)) {
          this.status = status;
          this.updateSiteHasPages(status.used);
        }
      }).catch(() => {
        // without the status there is nothing to toggle; the report itself is unaffected
      });
    },
    add() {
      if (!this.status) {
        return;
      }

      const { idSite, pageUrl } = this;
      const load = this.statusLoads.latest;
      this.isSaving = true;
      this.message = '';
      this.error = '';

      AjaxHelper.post<{ idpage: number }>(
        { method: 'Transitions.addPreprocessedPage', idSite },
        { pageUrl },
        { createErrorNotification: false },
      ).then((entry) => {
        if (!this.isCurrent(load) || !this.status) {
          return;
        }

        this.status = {
          ...this.status,
          selected: true,
          idPage: entry.idpage,
          used: this.status.used + 1,
        };
        this.updateSiteHasPages(this.status.used);
        this.message = this.status.rearchiveMonths > 0
          ? translate('Transitions_PreprocessedPageAddedRebuild', `${this.status.rearchiveMonths}`)
          : translate('Transitions_PreprocessedPageAddedNewDataOnly');
      }).catch((error) => {
        if (this.isCurrent(load)) {
          this.error = apiErrorText(error);
        }
      }).finally(() => {
        if (this.isCurrent(load)) {
          this.isSaving = false;
        }
      });
    },
    /** The toggle is disabled while the choice is open, so the focus moves to the choice. */
    confirmStop() {
      this.isConfirming = true;
      this.$nextTick(() => {
        const cancel = (this.$el as HTMLElement).querySelector<HTMLElement>(
          '.transitionsSourceHeader__cancel',
        );
        cancel?.focus();
      });
    },
    stop() {
      if (!this.status?.idPage) {
        return;
      }

      const { idSite } = this;
      const load = this.statusLoads.latest;
      this.isSaving = true;
      this.message = '';
      this.error = '';

      AjaxHelper.post(
        { method: 'Transitions.removePreprocessedPage', idSite },
        { idPage: this.status.idPage },
        { createErrorNotification: false },
      ).then(() => {
        if (!this.isCurrent(load) || !this.status) {
          return;
        }

        this.status = {
          ...this.status,
          selected: false,
          idPage: null,
          used: Math.max(0, this.status.used - 1),
        };
        this.updateSiteHasPages(this.status.used);
        this.isConfirming = false;
      }).catch((error) => {
        if (this.isCurrent(load)) {
          this.error = apiErrorText(error);
        }
      }).finally(() => {
        if (this.isCurrent(load)) {
          this.isSaving = false;
        }
      });
    },
  },
});
</script>
