<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    class="managePreprocessedPages"
    :content-title="translate('Transitions_PreprocessedTransitions')"
  >
    <p class="managePreprocessedPages__intro">
      {{ translate('Transitions_PreprocessedPagesIntro') }}
    </p>

    <div class="managePreprocessedPages__usage">
      <span class="managePreprocessedPages__usageLabel">{{ usageLabel }}</span>
      <div
        class="managePreprocessedPages__meter"
        role="progressbar"
        aria-valuemin="0"
        :aria-valuemax="maxPages"
        :aria-valuenow="pages.length"
        :aria-label="usageLabel"
      >
        <span
          class="managePreprocessedPages__meterFill"
          :style="{ width: `${usagePercent}%` }"
        />
      </div>
    </div>

    <p
      v-if="limitMessage"
      class="managePreprocessedPages__limitMessage"
    >{{ limitMessage }}</p>

    <p
      v-if="listError"
      class="managePreprocessedPages__error"
      role="alert"
    >{{ listError }}</p>

    <ActivityIndicator :loading="isLoading" />

    <table
      v-show="!isLoading"
      class="managePreprocessedPages__table"
      v-content-table
    >
      <thead>
        <tr>
          <th>{{ translate('Actions_ColumnPageURL') }}</th>
          <th>{{ translate('Transitions_ColumnDateAdded') }}</th>
          <th>{{ translate('Transitions_ColumnAddedBy') }}</th>
          <th class="managePreprocessedPages__actionColumn">
            <span class="u-visuallyHidden">{{ translate('General_Remove') }}</span>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="!pages.length">
          <td
            colspan="4"
            class="managePreprocessedPages__empty"
          >{{ translate('Transitions_PreprocessedPagesEmpty') }}</td>
        </tr>
        <tr
          v-for="page in pages"
          :key="page.idpage"
          class="managePreprocessedPages__row"
          :class="{ 'managePreprocessedPages__row--confirming': confirmingId === page.idpage }"
        >
          <td
            v-if="confirmingId === page.idpage"
            colspan="4"
          >
            <div class="managePreprocessedPages__confirm">
              <div class="managePreprocessedPages__confirmText">
                <strong>{{ confirmLabel(page.url) }}</strong>
                {{ translate('Transitions_StopPreprocessingHelp') }}
              </div>
              <div class="managePreprocessedPages__confirmActions">
                <button
                  type="button"
                  class="btn-flat managePreprocessedPages__cancel"
                  :disabled="removingId !== null"
                  @click="confirmingId = null"
                >{{ translate('General_Cancel') }}</button>
                <button
                  type="button"
                  class="btn managePreprocessedPages__stop"
                  :disabled="removingId !== null"
                  @click="remove(page)"
                >{{ translate('Transitions_StopPreprocessing') }}</button>
              </div>
            </div>
          </td>
          <template v-else>
            <td class="managePreprocessedPages__url">{{ page.url }}</td>
            <td class="managePreprocessedPages__date">{{ formatDate(page.created_date) }}</td>
            <td class="managePreprocessedPages__addedBy">
              <span
                v-if="page.created_by === null"
                class="managePreprocessedPages__unknownUser"
                :title="translate('Transitions_UnknownUserHelp')"
              >{{ translate('General_Unknown') }}</span>
              <template v-else>{{ page.created_by }}</template>
            </td>
            <td class="managePreprocessedPages__actionColumn">
              <button
                type="button"
                class="managePreprocessedPages__remove"
                :disabled="removingId !== null"
                @click="confirmRemove(page)"
              >{{ translate('General_Remove') }}</button>
            </td>
          </template>
        </tr>
      </tbody>
    </table>

    <form
      class="managePreprocessedPages__addForm"
      @submit.prevent="add"
    >
      <div class="managePreprocessedPages__field">
        <Field
          uicontrol="text"
          name="preprocessedPageUrl"
          :title="translate('Transitions_AddPageUrl')"
          :full-width="true"
          :model-value="pageUrl"
          @update:model-value="onPageUrlInput"
        />
        <div
          v-if="suggestions.length"
          class="managePreprocessedPages__suggestions"
        >
          <div class="managePreprocessedPages__suggestionsHeader">
            <span>{{ translate('Transitions_PreprocessedPagesSuggestions') }}</span>
            <span>{{ translate('General_ColumnPageviews') }}</span>
          </div>
          <ul class="managePreprocessedPages__suggestionList">
            <li
              v-for="suggestion in suggestions"
              :key="suggestion.url"
              class="managePreprocessedPages__suggestionItem"
            >
              <button
                type="button"
                class="managePreprocessedPages__suggestion"
                @click="pickSuggestion(suggestion.url)"
              >
                <span
                  class="managePreprocessedPages__suggestionUrl"
                  :title="suggestion.url"
                >{{ suggestion.url }}</span>
                <span class="managePreprocessedPages__suggestionPageviews">
                  {{ formatNumber(suggestion.nb_hits) }}
                </span>
              </button>
            </li>
          </ul>
        </div>
      </div>
      <button
        type="submit"
        class="btn managePreprocessedPages__add"
        :disabled="isAddDisabled"
      >{{ translate('Transitions_AddPage') }}</button>
    </form>

    <div
      v-if="addError"
      class="managePreprocessedPages__addError"
      role="alert"
    >
      <p class="managePreprocessedPages__error">{{ addError }}</p>
      <p
        v-if="isDuplicateError"
        class="managePreprocessedPages__duplicateHelp"
      >{{ translate('Transitions_PreprocessedPageDuplicateHelp') }}</p>
    </div>

    <p class="managePreprocessedPages__hint">{{ rebuildHint }}</p>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ActivityIndicator,
  AjaxHelper,
  ContentBlock,
  ContentTable,
  Matomo,
  NumberFormatter,
  debounce,
  translate,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import apiErrorText from '../apiErrorText';

/** An entry of Transitions.getPreprocessedPages. */
export interface PreprocessedPage {
  idpage: number;
  url: string;
  created_date: string;
  created_by: string|null;
}

/** A row of the flat page URLs report the suggestions come from. */
export interface PageUrlSuggestion {
  url: string;
  nb_hits: number|string;
}

export interface ManagePreprocessedPagesState {
  pages: PreprocessedPage[];
  isLoading: boolean;
  listError: string;
  pageUrl: string;
  pickedUrl: string;
  isAdding: boolean;
  addError: string;
  isDuplicateError: boolean;
  confirmingId: number|null;
  removingId: number|null;
  suggestions: PageUrlSuggestion[];
}

const MAX_SUGGESTIONS = 10;

/** Shorter input would match most of a large site's pages, so it isn't worth a report request. */
const MIN_SUGGESTION_CHARS = 3;

/** The report's URLs are matched whatever their protocol. */
function withoutScheme(text: string): string {
  return text.trim().replace(/^https?:\/\/(www\.)?/i, '');
}

/** The typed text is matched literally; the API wraps the pattern in slashes and escapes those. */
function toUrlPattern(text: string): string {
  return withoutScheme(text).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

export default defineComponent({
  props: {
    maxPages: {
      type: Number,
      required: true,
    },
    rearchiveMonths: {
      type: Number,
      required: true,
    },
  },
  components: {
    ActivityIndicator,
    ContentBlock,
    Field,
  },
  directives: {
    ContentTable,
  },
  data(): ManagePreprocessedPagesState {
    return {
      pages: [],
      isLoading: false,
      listError: '',
      pageUrl: '',
      pickedUrl: '',
      isAdding: false,
      addError: '',
      isDuplicateError: false,
      confirmingId: null,
      removingId: null,
      suggestions: [],
    };
  },
  setup() {
    return { suggestionsAbort: { controller: null as AbortController|null } };
  },
  created() {
    this.fetchSuggestions = debounce(this.fetchSuggestions.bind(this), 300);
    this.loadPages();
  },
  computed: {
    usageLabel(): string {
      return translate(
        'Transitions_PreprocessedPagesUsage',
        NumberFormatter.formatNumber(this.pages.length),
        NumberFormatter.formatNumber(this.maxPages),
      );
    },
    usagePercent(): number {
      if (this.maxPages <= 0) {
        return 100;
      }

      return Math.min(100, Math.round((this.pages.length / this.maxPages) * 100));
    },
    isLimitReached(): boolean {
      return this.pages.length >= this.maxPages;
    },
    limitMessage(): string {
      const used = this.pages.length;

      if (used > this.maxPages) {
        return translate(
          'Transitions_PreprocessedPagesOverLimit',
          NumberFormatter.formatNumber(used),
          NumberFormatter.formatNumber(this.maxPages),
          NumberFormatter.formatNumber(used - this.maxPages + 1),
        );
      }

      if (used === this.maxPages) {
        return translate(
          'Transitions_PreprocessedPagesLimitReached',
          NumberFormatter.formatNumber(this.maxPages),
        );
      }

      return '';
    },
    isAddDisabled(): boolean {
      return this.isLoading || this.isAdding || this.isLimitReached || !this.pageUrl.trim();
    },
    rebuildHint(): string {
      return this.rearchiveMonths > 0
        ? translate('Transitions_PreprocessedPagesRebuildHint', `${this.rearchiveMonths}`)
        : translate('Transitions_PreprocessedPagesNewDataOnlyHint');
    },
  },
  methods: {
    /** A reload after a change keeps the list on screen until the new one is in. */
    loadPages(isInitialLoad = true) {
      this.isLoading = isInitialLoad;
      this.listError = '';

      return AjaxHelper.fetch<PreprocessedPage[]>(
        {
          method: 'Transitions.getPreprocessedPages',
          idSite: Matomo.idSite,
          filter_limit: '-1',
        },
        { createErrorNotification: false },
      ).then((pages) => {
        this.pages = pages;
      }).catch((error) => {
        this.listError = apiErrorText(error);
      }).finally(() => {
        this.isLoading = false;
      });
    },
    onPageUrlInput(value: string) {
      this.pageUrl = value ?? '';
      this.addError = '';
      this.isDuplicateError = false;

      if (this.pageUrl === this.pickedUrl) {
        return;
      }

      this.pickedUrl = '';
      this.fetchSuggestions(this.pageUrl);
    },
    fetchSuggestions(text: string) {
      this.suggestionsAbort.controller?.abort();
      this.suggestionsAbort.controller = null;

      if (withoutScheme(text).length < MIN_SUGGESTION_CHARS) {
        this.suggestions = [];
        return;
      }

      const abortController = new AbortController();
      this.suggestionsAbort.controller = abortController;

      AjaxHelper.fetch<PageUrlSuggestion[]>(
        {
          method: 'Actions.getPageUrls',
          idSite: Matomo.idSite,
          period: 'range',
          date: 'last30',
          segment: '',
          flat: 1,
          filter_column: 'url',
          filter_pattern: toUrlPattern(text),
          filter_sort_column: 'nb_hits',
          filter_sort_order: 'desc',
          filter_limit: MAX_SUGGESTIONS,
          showColumns: 'label,nb_hits,url',
        },
        { createErrorNotification: false, abortController },
      ).then((rows) => {
        if (this.suggestionsAbort.controller !== abortController) {
          return;
        }

        this.suggestions = (rows || []).filter((row) => !!row.url);
      }).catch(() => {
        // suggestions are optional; any URL can still be typed
        if (this.suggestionsAbort.controller === abortController) {
          this.suggestions = [];
        }
      });
    },
    pickSuggestion(url: string) {
      this.pickedUrl = url;
      this.pageUrl = url;
      this.suggestions = [];
      this.suggestionsAbort.controller?.abort();
      this.suggestionsAbort.controller = null;
    },
    add() {
      if (this.isAddDisabled) {
        return;
      }

      this.isAdding = true;
      this.addError = '';
      this.isDuplicateError = false;

      AjaxHelper.post(
        { method: 'Transitions.addPreprocessedPage', idSite: Matomo.idSite },
        { pageUrl: this.pageUrl.trim() },
        { createErrorNotification: false },
      ).then(() => {
        this.pageUrl = '';
        this.pickedUrl = '';
        this.suggestions = [];
        return this.loadPages(false);
      }).catch((error) => {
        this.addError = apiErrorText(error);
        this.isDuplicateError = this.addError
          === translate('Transitions_PreprocessedPageAlreadySelected');
      }).finally(() => {
        this.isAdding = false;
      });
    },
    /** The remove button goes away with its row's content, so the focus moves to the choice. */
    confirmRemove(page: PreprocessedPage) {
      this.confirmingId = page.idpage;
      this.$nextTick(() => {
        const cancel = (this.$el as HTMLElement).querySelector<HTMLElement>(
          '.managePreprocessedPages__cancel',
        );
        cancel?.focus();
      });
    },
    remove(page: PreprocessedPage) {
      this.removingId = page.idpage;
      this.listError = '';

      AjaxHelper.post(
        { method: 'Transitions.removePreprocessedPage', idSite: Matomo.idSite },
        { idPage: page.idpage },
        { createErrorNotification: false },
      ).then(() => {
        this.confirmingId = null;
        return this.loadPages(false);
      }).catch((error) => {
        this.listError = apiErrorText(error);
      }).finally(() => {
        this.removingId = null;
      });
    },
    confirmLabel(url: string): string {
      return translate(
        'Transitions_StopPreprocessingConfirm',
        url.replace(/^https?:\/\/(www\.)?/i, ''),
      );
    },
    formatDate(date: string): string {
      return `${date.substring(0, 16)} UTC`;
    },
    formatNumber(value: number|string): string {
      return NumberFormatter.formatNumber(Number(value));
    },
  },
});
</script>
