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

    <div
      v-if="hasLoaded"
      class="managePreprocessedPages__usage"
    >
      <span class="managePreprocessedPages__usageLabel">{{ usageLabel }}</span>
      <div
        class="managePreprocessedPages__meter"
        role="progressbar"
        aria-valuemin="0"
        :aria-valuemax="maxPages"
        :aria-valuenow="meterValue"
        :aria-label="usageLabel"
      >
        <span
          class="managePreprocessedPages__meterFill"
          :style="{ width: `${usagePercent}%` }"
        />
      </div>
    </div>

    <p
      v-if="hasLoaded && limitMessage"
      class="managePreprocessedPages__limitMessage"
    >{{ limitMessage }}</p>

    <p
      v-if="listError"
      class="managePreprocessedPages__error"
      role="alert"
    >{{ listError }}</p>

    <ActivityIndicator :loading="isLoading" />

    <table
      v-show="hasLoaded"
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
                  @click="cancelRemove(page)"
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
            <td class="managePreprocessedPages__url">{{ displayUrl(page.url) }}</td>
            <td class="managePreprocessedPages__date">{{ formatDate(page.created_date) }}</td>
            <td class="managePreprocessedPages__addedBy">
              <template v-if="page.created_by === null && page.created_by_superuser">
                {{ translate('Transitions_AddedBySuperUser') }}
              </template>
              <span
                v-else-if="page.created_by === null"
                class="managePreprocessedPages__unknownUser"
                tabindex="0"
                :title="translate('Transitions_UnknownUserHelp')"
                :aria-describedby="unknownUserHelpId"
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
    <span
      :id="unknownUserHelpId"
      class="u-visuallyHidden"
    >{{ translate('Transitions_UnknownUserHelp') }}</span>

    <form
      class="managePreprocessedPages__addForm"
      @submit.prevent="add"
    >
      <div
        class="managePreprocessedPages__field"
        @focusin="loadSuggestions"
        @focusout="onFieldFocusOut"
        @keydown.esc="areSuggestionsOpen = false"
      >
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
                @mousedown.prevent
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
  translate,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import apiErrorText from '../apiErrorText';
import {
  displayUrl,
  formatUtcDateTime,
  matchKey,
  pageKey,
  stopConfirmLabel,
} from '../preprocessedPageUrls';

/** An entry of Transitions.getPreprocessedPages. */
export interface PreprocessedPage {
  idpage: number;
  url: string;
  created_date: string;
  /** Null for a deleted user, and for a super user when the viewer isn't one. */
  created_by: string|null;
  created_by_superuser?: boolean;
}

/** A row of the flat page URLs report the suggestions come from. */
export interface PageUrlSuggestion {
  url: string;
  nb_hits: number|string;
}

/** A suggestion with its URL decoded, and the keys it is matched by worked out once. */
export interface SuggestionEntry {
  url: string;
  nb_hits: number|string;
  matchKey: string;
  pageKey: string;
}

export interface ManagePreprocessedPagesState {
  pages: PreprocessedPage[];
  hasLoaded: boolean;
  isLoading: boolean;
  listError: string;
  pageUrl: string;
  areSuggestionsOpen: boolean;
  isAdding: boolean;
  addError: string;
  isDuplicateError: boolean;
  confirmingId: number|null;
  removingId: number|null;
  suggestionPool: SuggestionEntry[]|null;
  isSuggestionPoolRequested: boolean;
}

const MAX_SUGGESTIONS = 10;

/** The most viewed pages are read once per page view and matched as the admin types. */
const SUGGESTION_POOL_SIZE = 500;

/** Shorter input matches too many pages to be a useful suggestion. */
const MIN_SUGGESTION_CHARS = 3;

let instanceCount = 0;

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
      hasLoaded: false,
      isLoading: false,
      listError: '',
      pageUrl: '',
      areSuggestionsOpen: false,
      isAdding: false,
      addError: '',
      isDuplicateError: false,
      confirmingId: null,
      removingId: null,
      suggestionPool: null,
      isSuggestionPoolRequested: false,
    };
  },
  setup() {
    instanceCount += 1;
    return {
      unknownUserHelpId: `managePreprocessedPagesUnknownUser${instanceCount}`,
      // the newest list request wins, so a slower earlier reload can't bring back a stale list
      listLoads: { latest: 0 },
    };
  },
  created() {
    this.loadPages();
  },
  computed: {
    /** Selected pages are left out: picking one could only be refused as a duplicate. */
    suggestions(): SuggestionEntry[] {
      const typed = matchKey(this.pageUrl);
      if (!this.suggestionPool || !this.areSuggestionsOpen || typed.length < MIN_SUGGESTION_CHARS) {
        return [];
      }

      const selected = new Set(this.pages.map((page) => pageKey(displayUrl(page.url))));

      return this.suggestionPool
        .filter((row) => !selected.has(row.pageKey) && row.matchKey.includes(typed))
        .slice(0, MAX_SUGGESTIONS);
    },
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
    /** A progress bar can't go past its maximum; the label tells how far over the limit it is. */
    meterValue(): number {
      return Math.min(this.pages.length, Math.max(0, this.maxPages));
    },
    isLimitReached(): boolean {
      return this.pages.length >= this.maxPages;
    },
    limitMessage(): string {
      const used = this.pages.length;

      if (this.maxPages <= 0) {
        return translate('Transitions_PreprocessedPagesAddingDisabled');
      }

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
      return !this.hasLoaded || this.isAdding || this.isLimitReached || !this.pageUrl.trim();
    },
    rebuildHint(): string {
      if (this.rearchiveMonths <= 0) {
        return translate('Transitions_PreprocessedPagesNewDataOnlyHint');
      }

      return this.rearchiveMonths === 1
        ? translate('Transitions_PreprocessedPagesRebuildHintOneMonth')
        : translate(
          'Transitions_PreprocessedPagesRebuildHint',
          NumberFormatter.formatNumber(this.rearchiveMonths),
        );
    },
  },
  methods: {
    /**
     * A reload after a change keeps the list on screen until the new one is in. A reload after a
     * failed change keeps that change's error, unless the reload fails too.
     */
    loadPages(isInitialLoad = true, error = '') {
      this.listLoads.latest += 1;
      const load = this.listLoads.latest;
      this.isLoading = isInitialLoad;
      this.listError = error;

      return AjaxHelper.fetch<PreprocessedPage[]>(
        {
          method: 'Transitions.getPreprocessedPages',
          idSite: Matomo.idSite,
          filter_limit: '-1',
        },
        { createErrorNotification: false },
      ).then((pages) => {
        if (load === this.listLoads.latest) {
          this.pages = pages;
          this.hasLoaded = true;
        }
      }).catch((loadError) => {
        if (load === this.listLoads.latest) {
          this.listError = apiErrorText(loadError);
        }
      }).finally(() => {
        if (load === this.listLoads.latest) {
          this.isLoading = false;
        }
      });
    },
    onPageUrlInput(value: string) {
      this.pageUrl = value ?? '';
      this.addError = '';
      this.isDuplicateError = false;
      this.areSuggestionsOpen = true;

      this.loadSuggestions();
    },
    /** The list closes once the focus leaves the field and its suggestions. */
    onFieldFocusOut(event: FocusEvent) {
      const field = event.currentTarget as HTMLElement;
      const next = event.relatedTarget as Node|null;

      if (!next || !field.contains(next)) {
        this.areSuggestionsOpen = false;
      }
    },
    /**
     * The most viewed pages of last month: a finished month, which archiving has already processed
     * and keeps. The pool is sorted by pageviews, so the first matches are the most viewed ones.
     * Nothing is read until a page could be added.
     */
    loadSuggestions() {
      if (this.isSuggestionPoolRequested || !this.hasLoaded || this.isLimitReached) {
        return;
      }

      this.isSuggestionPoolRequested = true;
      AjaxHelper.fetch<PageUrlSuggestion[]>(
        {
          method: 'Actions.getPageUrls',
          idSite: Matomo.idSite,
          period: 'month',
          date: 'lastMonth',
          segment: '',
          flat: 1,
          filter_sort_column: 'nb_hits',
          filter_sort_order: 'desc',
          filter_limit: SUGGESTION_POOL_SIZE,
          showColumns: 'label,nb_hits,url',
        },
        { createErrorNotification: false },
      ).then((rows) => {
        this.suggestionPool = (rows || []).filter((row) => !!row.url).map((row) => {
          const url = displayUrl(row.url);
          return {
            url,
            nb_hits: row.nb_hits,
            matchKey: matchKey(url),
            pageKey: pageKey(url),
          };
        });
      }).catch(() => {
        // suggestions are optional; any URL can still be typed
        this.suggestionPool = [];
      });
    },
    /** A suggestion picked by keyboard goes away with the list; the focus returns to the field. */
    pickSuggestion(url: string) {
      this.pageUrl = url;
      this.areSuggestionsOpen = false;
      this.focusField();
    },
    focusField() {
      this.$nextTick(() => {
        (this.$el as HTMLElement).querySelector<HTMLElement>(
          '.managePreprocessedPages__field input',
        )?.focus();
      });
    },
    /** A failed add may be due to another admin's change, so the list is read again. */
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
        return this.loadPages(false);
      }).catch((error) => {
        this.addError = apiErrorText(error);
        this.isDuplicateError = this.addError
          === translate('Transitions_PreprocessedPageAlreadySelected');
        return this.loadPages(false);
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
    cancelRemove(page: PreprocessedPage) {
      this.confirmingId = null;
      this.focusRemoveButton(this.pages.indexOf(page));
    },
    /** The button at the given row, or the last one; the URL field when the list is empty. */
    focusRemoveButton(index: number) {
      this.$nextTick(() => {
        const buttons = (this.$el as HTMLElement).querySelectorAll<HTMLElement>(
          '.managePreprocessedPages__remove',
        );
        const button = buttons[Math.min(index, buttons.length - 1)];

        if (button) {
          button.focus();
          return;
        }

        this.focusField();
      });
    },
    /**
     * The list is read again also after a failure, which may be due to another admin's change. The
     * choice goes away either way, so the focus moves to the remove button now in the row's place.
     */
    remove(page: PreprocessedPage) {
      const index = this.pages.indexOf(page);
      this.removingId = page.idpage;
      this.listError = '';

      AjaxHelper.post(
        { method: 'Transitions.removePreprocessedPage', idSite: Matomo.idSite },
        { idPage: page.idpage },
        { createErrorNotification: false },
      ).then(
        () => '',
        (error) => apiErrorText(error),
      ).then((error) => {
        this.confirmingId = null;
        return this.loadPages(false, error);
      }).then(() => {
        this.removingId = null;
        this.focusRemoveButton(index);
      });
    },
    confirmLabel(url: string): string {
      return stopConfirmLabel(url);
    },
    formatDate(date: string): string {
      return formatUtcDateTime(date);
    },
    displayUrl(url: string): string {
      return displayUrl(url);
    },
    formatNumber(value: number|string): string {
      return NumberFormatter.formatNumber(Number(value));
    },
  },
});
</script>
