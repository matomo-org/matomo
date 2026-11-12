<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="transitionsSourceHeader">
    <div class="transitionsSourceHeader__bar">
      <span
        v-if="source.loaded"
        class="transitionsSourceHeader__badge"
        :class="{
          'transitionsSourceHeader__badge--preprocessed': source.preprocessed,
          'transitionsSourceHeader__badge--raw': !source.preprocessed,
        }"
        :title="badgeTooltip || undefined"
      >{{ translate(source.preprocessed
        ? 'Transitions_PreprocessedBadge' : 'Transitions_RawDataBadge') }}</span>

      <div
        v-if="status"
        class="transitionsSourceHeader__actions"
      >
        <button
          v-if="status.selected"
          type="button"
          class="btn-flat transitionsSourceHeader__toggle"
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
              class="btn-flat transitionsSourceHeader__toggle"
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
}

let instanceCount = 0;

/** API errors arrive HTML-escaped; they are shown as text. */
function errorText(error: unknown): string {
  const message = error instanceof Error ? error.message : '';
  return message ? Matomo.helper.htmlDecode(message) : translate('General_ErrorRequest', '', '');
}

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
    };
  },
  setup() {
    instanceCount += 1;
    return { limitHelpId: `transitionsSourceHeaderLimit${instanceCount}` };
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
    loadStatus() {
      this.status = null;
      this.isConfirming = false;
      this.message = '';
      this.error = '';

      if (!this.canManage) {
        return;
      }

      const { actionName, idSite } = this;
      AjaxHelper.fetch<PreprocessedPageStatus>(
        {
          method: 'Transitions.getPreprocessedPageStatus',
          idSite,
          pageUrl: actionName,
        },
        { createErrorNotification: false },
      ).then((status) => {
        if (actionName === this.actionName && idSite === this.idSite) {
          this.status = status;
        }
      }).catch(() => {
        // without the status there is nothing to toggle; the report itself is unaffected
      });
    },
    add() {
      if (!this.status) {
        return;
      }

      this.isSaving = true;
      this.message = '';
      this.error = '';

      AjaxHelper.post<{ idpage: number }>(
        { method: 'Transitions.addPreprocessedPage', idSite: this.idSite },
        { pageUrl: this.actionName },
        { createErrorNotification: false },
      ).then((entry) => {
        if (!this.status) {
          return;
        }

        this.status = {
          ...this.status,
          selected: true,
          idPage: entry.idpage,
          used: this.status.used + 1,
        };
        this.message = this.status.rearchiveMonths > 0
          ? translate('Transitions_PreprocessedPageAddedRebuild', `${this.status.rearchiveMonths}`)
          : translate('Transitions_PreprocessedPageAddedNewDataOnly');
      }).catch((error) => {
        this.error = errorText(error);
      }).finally(() => {
        this.isSaving = false;
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

      this.isSaving = true;
      this.message = '';
      this.error = '';

      AjaxHelper.post(
        { method: 'Transitions.removePreprocessedPage', idSite: this.idSite },
        { idPage: this.status.idPage },
        { createErrorNotification: false },
      ).then(() => {
        if (!this.status) {
          return;
        }

        this.status = {
          ...this.status,
          selected: false,
          idPage: null,
          used: Math.max(0, this.status.used - 1),
        };
        this.isConfirming = false;
      }).catch((error) => {
        this.error = errorText(error);
      }).finally(() => {
        this.isSaving = false;
      });
    },
  },
});
</script>
