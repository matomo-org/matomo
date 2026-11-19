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

    <div
      v-if="hasLoaded && limitMessage"
      class="managePreprocessedPages__limitMessage"
      tabindex="-1"
    >
      <Alert :severity="pages.length > maxPages ? 'warning' : 'info'">{{ limitMessage }}</Alert>
    </div>

    <div
      v-if="listError"
      class="managePreprocessedPages__listError"
    >
      <Alert severity="danger">
        <div
          class="managePreprocessedPages__error"
          role="alert"
        >{{ listError }}</div>
      </Alert>
    </div>

    <ActivityIndicator :loading="isLoading || removingId !== null" />

    <table
      v-show="hasLoaded"
      class="managePreprocessedPages__table"
      v-content-table
    >
      <thead class="managePreprocessedPages__head">
        <tr>
          <th>{{ translate('Actions_ColumnPageURL') }}</th>
          <th>{{ translate('Transitions_ColumnDateAdded') }}</th>
          <th>{{ translate('Transitions_ColumnAddedBy') }}</th>
          <th class="managePreprocessedPages__actionColumn">
            <span class="u-visuallyHidden">{{ translate('General_Remove') }}</span>
          </th>
        </tr>
      </thead>
      <tbody class="managePreprocessedPages__body">
        <tr
          v-if="!pages.length"
          class="managePreprocessedPages__emptyRow"
        >
          <td
            colspan="4"
            class="managePreprocessedPages__empty"
          >{{ translate('Transitions_PreprocessedPagesEmpty') }}</td>
        </tr>
        <tr
          v-for="page in pages"
          :key="page.idpage"
          class="managePreprocessedPages__row"
        >
          <td
            :id="`${idPrefix}Url${page.idpage}`"
            class="managePreprocessedPages__url"
            :title="urlTitle(page.url)"
          >{{ readableUrl(page.url) }}</td>
          <td class="managePreprocessedPages__date">
            <span class="managePreprocessedPages__cellLabel">
              {{ translate('Transitions_ColumnDateAdded') }}
            </span>
            {{ formatDate(page.created_date) }}
          </td>
          <td class="managePreprocessedPages__addedBy">
            <span class="managePreprocessedPages__cellLabel">
              {{ translate('Transitions_ColumnAddedBy') }}
            </span>
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
          <td class="entityTable_ActionCell managePreprocessedPages__actionColumn">
            <button
              :id="`${idPrefix}Remove${page.idpage}`"
              type="button"
              class="table-action icon-delete managePreprocessedPages__remove"
              :title="translate('General_Remove')"
              :aria-labelledby="`${idPrefix}Remove${page.idpage} ${idPrefix}Url${page.idpage}`"
              :disabled="removingId !== null"
              :data-state="removingId !== null ? 'disabled' : undefined"
              @click="confirmRemove(page)"
            >
              <span class="u-visuallyHidden">{{ translate('General_Remove') }}</span>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <span
      :id="unknownUserHelpId"
      class="u-visuallyHidden"
    >{{ translate('Transitions_UnknownUserHelp') }}</span>

    <template v-if="canAdd">
      <form
        class="managePreprocessedPages__addForm"
        @submit.prevent="add"
      >
        <div
          ref="field"
          class="managePreprocessedPages__field"
          @focusin="loadSuggestions"
          @focusout="onFieldFocusOut"
          @keydown.esc="areSuggestionsOpen = false"
        >
          <Field
            uicontrol="text"
            name="preprocessedPageUrl"
            autocomplete="off"
            :title="translate('Transitions_AddPageUrl')"
            :full-width="true"
            :model-value="pageUrl"
            :ui-control-attributes="inputAttributes"
            @update:model-value="onPageUrlInput"
          />
          <div
            v-if="suggestions.length"
            class="managePreprocessedPages__suggestions"
            role="group"
            :aria-label="suggestionsTitle"
            :style="suggestionsPosition"
          >
            <div class="managePreprocessedPages__suggestionsHeader">
              <span>{{ suggestionsTitle }}</span>
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
                    :title="encodeHiddenCharacters(suggestion.url)"
                  >{{ readableUrl(suggestion.url) }}</span>
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
        <button
          type="button"
          class="btn btn-outline managePreprocessedPages__addTop"
          :disabled="isAdding || isAddingTopPages"
          @click="prepareAddTopPages"
        >{{ translate('Transitions_AddTopPages') }}</button>
        <div class="managePreprocessedPages__addProgress">
          <ActivityIndicator :loading="isAdding || isAddingTopPages" />
        </div>
      </form>

      <div
        v-if="addError"
        class="managePreprocessedPages__addError"
      >
        <Alert severity="danger">
          <div
            :id="`${idPrefix}AddError`"
            role="alert"
          >
            <div class="managePreprocessedPages__error">{{ addError }}</div>
            <div
              v-if="isDuplicateError"
              class="managePreprocessedPages__duplicateHelp"
            >{{ translate('Transitions_PreprocessedPageDuplicateHelp') }}</div>
          </div>
        </Alert>
      </div>

      <div
        :id="`${idPrefix}Hint`"
        class="managePreprocessedPages__hint"
      >{{ rebuildHint }}</div>
    </template>

    <MatomoDialog
      v-model="isRemoveDialogOpen"
      @yes="removeConfirmed"
      @close-end="onRemoveDialogClosed"
    >
      <div class="ui-confirm">
        <h2 class="managePreprocessedPages__dialogTitle">
          {{ pageToRemove ? confirmLabel(pageToRemove.url) : '' }}
        </h2>
        <p>{{ translate('Transitions_StopPreprocessingHelp') }}</p>
        <!-- the dialog turns the inputs into its buttons by these roles -->
        <!-- eslint-disable vuejs-accessibility/aria-role -->
        <input
          role="yes"
          type="button"
          :value="translate('General_Yes')"
        />
        <input
          role="no"
          type="button"
          :value="translate('General_No')"
        />
        <!-- eslint-enable vuejs-accessibility/aria-role -->
      </div>
    </MatomoDialog>

    <MatomoDialog
      v-model="isTopPagesDialogOpen"
      @yes="addTopPages"
      @close-end="onTopPagesDialogClosed"
    >
      <div class="ui-confirm managePreprocessedPages__topPagesDialog">
        <h2 class="managePreprocessedPages__dialogTitle">{{ topPagesConfirmLabel }}</h2>
        <p>{{ rebuildHint }}</p>
        <!-- eslint-disable vuejs-accessibility/aria-role -->
        <input
          role="yes"
          type="button"
          :value="translate('General_Yes')"
        />
        <input
          role="no"
          type="button"
          :value="translate('General_No')"
        />
        <!-- eslint-enable vuejs-accessibility/aria-role -->
      </div>
    </MatomoDialog>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ActivityIndicator,
  AjaxHelper,
  Alert,
  ContentBlock,
  ContentTable,
  Matomo,
  MatomoDialog,
  NotificationsStore,
  NumberFormatter,
  translate,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import apiErrorText from '../apiErrorText';
import {
  addedMessage,
  displayUrl,
  encodeHiddenCharacters,
  readableUrl,
  removedMessageHtml,
  formatUtcDateTime,
  matchKey,
  pageKey,
  previousMonthStart,
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
  isUrlError: boolean;
  pageToRemove: PreprocessedPage|null;
  /** The row of the page to remove, which takes the focus back when the dialog has closed. */
  removeIndex: number;
  isRemoveDialogOpen: boolean;
  removingId: number|null;
  suggestionsPosition: Record<string, string>;
  suggestionPool: SuggestionEntry[]|null;
  /**
   * The period the suggestions are from: the last 30 days, or as fallback the month starting on
   * this `YYYY-MM-DD` date.
   */
  suggestionPeriod: string;
  isSuggestionPoolRequested: boolean;
  /** Why the suggestions couldn't be read, eg a network error; empty when they could. */
  suggestionPoolError: string;
  /** While the most viewed pages are read before the confirmation, and while they are added. */
  isAddingTopPages: boolean;
  isTopPagesDialogOpen: boolean;
  /** The free slots the confirmation names, and the most viewed pages fill. */
  topPagesLimit: number;
}

const MAX_SUGGESTIONS = 10;

/** The most viewed pages are read once per page view and matched as the admin types. */
const SUGGESTION_POOL_SIZE = 500;

/** Shorter input matches too many pages to be a useful suggestion. */
const MIN_SUGGESTION_CHARS = 3;

/** The add errors about the typed URL, not about the limit, a concurrent change or the network. */
const URL_ERROR_KEYS = [
  'Transitions_PreprocessedPageAlreadySelected',
  'Transitions_PreprocessedPageInvalidUrl',
  'Transitions_PreprocessedPageUrlTooLong',
];

/** Whether a message is the given translation, with any value in its placeholders. */
function isTranslation(message: string, key: string): boolean {
  const pattern = translate(key)
    .split(/%(?:\d+\$)?s/)
    .map((part) => part.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'))
    .join('.+');

  return new RegExp(`^${pattern}$`).test(message);
}

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
    Alert,
    ContentBlock,
    Field,
    MatomoDialog,
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
      isUrlError: false,
      pageToRemove: null,
      removeIndex: -1,
      isRemoveDialogOpen: false,
      removingId: null,
      suggestionsPosition: {},
      suggestionPool: null,
      suggestionPeriod: 'last30',
      isSuggestionPoolRequested: false,
      suggestionPoolError: '',
      isAddingTopPages: false,
      isTopPagesDialogOpen: false,
      topPagesLimit: 0,
    };
  },
  setup() {
    instanceCount += 1;
    return {
      unknownUserHelpId: `managePreprocessedPagesUnknownUser${instanceCount}`,
      idPrefix: `managePreprocessedPages${instanceCount}`,
      // the newest list request wins, so a slower earlier reload can't bring back a stale list
      listLoads: { latest: 0 },
      // the suggestions' load, which the most viewed pages button waits for too
      suggestionPoolLoad: { promise: Promise.resolve() },
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
    /** The fallback names its month: on the 1st it can be the one before last in some timezones. */
    suggestionsTitle(): string {
      return translate(
        'Transitions_PreprocessedPagesSuggestions',
        this.suggestionPeriod === 'last30'
          ? translate('CoreHome_PresetDateLast30Days')
          : this.suggestionMonth,
      );
    },
    /** The name of the month the suggestions fell back to; empty for the last 30 days. */
    suggestionMonth(): string {
      if (this.suggestionPeriod === 'last30') {
        return '';
      }

      return translate(`Intl_Month_Long_StandAlone_${Number(this.suggestionPeriod.substring(5, 7))}`);
    },
    topPagesConfirmLabel(): string {
      const limit = NumberFormatter.formatNumber(this.topPagesLimit);

      return this.suggestionPeriod === 'last30'
        ? translate('Transitions_AddTopPagesConfirmLast30Days', limit)
        : translate('Transitions_AddTopPagesConfirmMonth', limit, this.suggestionMonth);
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
    /** The add form is shown only when a page can be added; else the limit message says why. */
    canAdd(): boolean {
      return this.hasLoaded && !this.isLimitReached;
    },
    isAddDisabled(): boolean {
      return !this.canAdd || this.isAdding || this.isAddingTopPages || !this.pageUrl.trim();
    },
    /** The hint, and the last add's error, describe the field; a URL error marks it invalid. */
    inputAttributes(): Record<string, string> {
      const describedBy = [`${this.idPrefix}Hint`];

      if (this.addError) {
        describedBy.unshift(`${this.idPrefix}AddError`);
      }

      return {
        'aria-describedby': describedBy.join(' '),
        'aria-invalid': this.isUrlError ? 'true' : 'false',
      };
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
      this.isUrlError = false;
      this.areSuggestionsOpen = true;

      this.positionSuggestions();
      this.loadSuggestions();
    },
    /** The suggestions open right under the input, as wide as it, whatever the field's spacing. */
    positionSuggestions() {
      const field = this.$refs.field as HTMLElement|undefined;
      const input = field?.querySelector('input');

      if (!field || !input) {
        return;
      }

      const fieldBox = field.getBoundingClientRect();
      const inputBox = input.getBoundingClientRect();
      this.suggestionsPosition = {
        top: `${inputBox.bottom - fieldBox.top}px`,
        left: `${inputBox.left - fieldBox.left}px`,
        width: `${inputBox.width}px`,
      };
    },
    /** The list closes once the focus leaves the field and its suggestions. */
    onFieldFocusOut(event: FocusEvent) {
      const field = event.currentTarget as HTMLElement;
      const next = event.relatedTarget as Node|null;

      if (!next || !field.contains(next)) {
        this.areSuggestionsOpen = false;
      }
    },
    /** The most viewed pages of a period, flat and sorted by pageviews; rows with a URL only. */
    fetchSuggestionRows(period: string, date: string): Promise<PageUrlSuggestion[]> {
      return AjaxHelper.fetch<PageUrlSuggestion[]>(
        {
          method: 'Actions.getPageUrls',
          idSite: Matomo.idSite,
          period,
          date,
          segment: '',
          flat: 1,
          filter_sort_column: 'nb_hits',
          filter_sort_order: 'desc',
          filter_limit: SUGGESTION_POOL_SIZE,
          showColumns: 'label,nb_hits,url',
        },
        { createErrorNotification: false },
      ).then((rows) => (rows || []).filter((row) => !!row.url));
    },
    /**
     * The most viewed pages of the last 30 days, or of the previous calendar month when that range
     * has no rows or fails, eg where a range can't be archived on request. The pool is sorted by
     * pageviews, so the first matches are the most viewed ones. It is read in the background once
     * the field is first used, not before a page could be added; any URL can be typed meanwhile.
     */
    loadSuggestions(): Promise<void> {
      if (this.isSuggestionPoolRequested || !this.hasLoaded || this.isLimitReached) {
        return this.suggestionPoolLoad.promise;
      }

      this.isSuggestionPoolRequested = true;
      this.suggestionPeriod = 'last30';
      this.suggestionPoolError = '';
      this.suggestionPoolLoad.promise = this.fetchSuggestionRows('range', 'last30')
        .catch(() => [] as PageUrlSuggestion[])
        .then((rows) => {
          if (rows.length) {
            return rows;
          }

          this.suggestionPeriod = previousMonthStart(new Date());
          return this.fetchSuggestionRows('month', this.suggestionPeriod);
        })
        .then((rows) => {
          this.suggestionPool = rows.map((row) => {
            const url = displayUrl(row.url);
            return {
              url,
              nb_hits: row.nb_hits,
              matchKey: matchKey(url),
              pageKey: pageKey(url),
            };
          });
        })
        .catch((error) => {
          // suggestions are optional; any URL can still be typed
          this.suggestionPool = [];
          this.suggestionPoolError = apiErrorText(error);
        });

      return this.suggestionPoolLoad.promise;
    },
    /** A suggestion picked by keyboard goes away with the list; the focus returns to the field. */
    pickSuggestion(url: string) {
      this.pageUrl = url;
      this.areSuggestionsOpen = false;
      this.focusField();
    },
    focusField() {
      this.$nextTick(() => {
        this.focusFieldOrLimitMessage();
      });
    },
    /** The URL field, or the limit message that took the form's place. */
    focusFieldOrLimitMessage() {
      (this.$el as HTMLElement).querySelector<HTMLElement>(
        '.managePreprocessedPages__field input, .managePreprocessedPages__limitMessage',
      )?.focus();
    },
    /**
     * A focus lost while adding, as the button was disabled or the form went away with the limit
     * reached, moves to the field or the limit message.
     */
    keepFocusAfterAdd() {
      this.$nextTick(() => {
        const focused = document.activeElement;

        if (!focused || focused === document.body) {
          this.focusFieldOrLimitMessage();
        }
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
      this.isUrlError = false;

      AjaxHelper.post(
        { method: 'Transitions.addPreprocessedPage', idSite: Matomo.idSite },
        { pageUrl: this.pageUrl.trim() },
        { createErrorNotification: false },
      ).then(() => {
        this.pageUrl = '';
        this.showSuccess(addedMessage(this.rearchiveMonths));
        return this.loadPages(false);
      }).catch((error) => {
        this.addError = apiErrorText(error);
        this.isDuplicateError = this.addError
          === translate('Transitions_PreprocessedPageAlreadySelected');
        this.isUrlError = URL_ERROR_KEYS.some((key) => isTranslation(this.addError, key));
        return this.loadPages(false);
      }).finally(() => {
        this.isAdding = false;
        this.keepFocusAfterAdd();
      });
    },
    /**
     * The most viewed pages are those of the suggestions, read now unless already in. The
     * confirmation names the period they come from; with no pages in either period it explains
     * why nothing is added. A failed read is tried again on the next click.
     */
    prepareAddTopPages() {
      if (!this.canAdd || this.isAdding || this.isAddingTopPages) {
        return;
      }

      if (this.suggestionPoolError) {
        this.isSuggestionPoolRequested = false;
      }

      this.isAddingTopPages = true;
      this.addError = '';
      this.isDuplicateError = false;
      this.isUrlError = false;

      this.loadSuggestions().then(() => {
        this.isAddingTopPages = false;

        if (this.suggestionPoolError) {
          this.addError = this.suggestionPoolError;
        } else if (!this.suggestionPool?.length) {
          this.showInfo(translate('Transitions_TopPagesNoData', this.suggestionMonth));
        } else if (this.canAdd) {
          this.topPagesLimit = this.maxPages - this.pages.length;
          this.isTopPagesDialogOpen = true;
          return;
        }

        this.focusTopPagesButton();
      });
    },
    /** Fills the free slots named in the confirmation; the list is read again also on failure. */
    addTopPages() {
      const isLast30 = this.suggestionPeriod === 'last30';
      this.isAddingTopPages = true;

      AjaxHelper.post<PreprocessedPage[]>(
        { method: 'Transitions.addTopPreprocessedPages', idSite: Matomo.idSite },
        {
          limit: this.topPagesLimit,
          period: isLast30 ? 'range' : 'month',
          date: this.suggestionPeriod,
        },
        { createErrorNotification: false },
      ).then((added) => {
        const count = (added || []).length;

        if (!count) {
          this.showInfo(translate('Transitions_TopPagesAlreadySelected'));
        } else if (count === 1) {
          this.showSuccess(translate('Transitions_TopPagesAddedOne'));
        } else {
          this.showSuccess(translate('Transitions_TopPagesAdded', NumberFormatter.formatNumber(count)));
        }

        return this.loadPages(false);
      }).catch((error) => {
        this.addError = apiErrorText(error);
        return this.loadPages(false);
      }).finally(() => {
        this.isAddingTopPages = false;

        if (!this.isTopPagesDialogOpen) {
          this.focusTopPagesButton();
        }
      });
    },
    onTopPagesDialogClosed() {
      if (!this.isAddingTopPages) {
        this.focusTopPagesButton();
      }
    },
    /** The button, or the limit message that took the form's place once the slots are filled. */
    focusTopPagesButton() {
      this.$nextTick(() => {
        (this.$el as HTMLElement).querySelector<HTMLElement>(
          '.managePreprocessedPages__addTop, .managePreprocessedPages__limitMessage',
        )?.focus();
      });
    },
    /**
     * Matomo's confirmation dialog. The focus returns to the row's remove button, or the one now in
     * its place, once the dialog has closed and the page is removed.
     */
    confirmRemove(page: PreprocessedPage) {
      this.pageToRemove = page;
      this.removeIndex = this.rowIndex(page);
      this.isRemoveDialogOpen = true;
    },
    removeConfirmed() {
      if (this.pageToRemove) {
        this.remove(this.pageToRemove);
      }
    },
    onRemoveDialogClosed() {
      if (this.removingId === null) {
        this.focusRemoveButton(this.removeIndex);
      }
    },
    /** The message is rendered as HTML, so any text from a URL in it must be escaped. */
    showSuccess(message: string) {
      NotificationsStore.show({
        id: 'TransitionsPreprocessedPages',
        message,
        context: 'success',
        type: 'toast',
      });
    },
    showInfo(message: string) {
      NotificationsStore.show({
        id: 'TransitionsPreprocessedPages',
        message,
        context: 'info',
        type: 'toast',
      });
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

        this.focusFieldOrLimitMessage();
      });
    },
    /** The page's row, found by its ID, as a reload replaces the page objects. */
    rowIndex(page: PreprocessedPage): number {
      return this.pages.findIndex((entry) => entry.idpage === page.idpage);
    },
    /** The list is read again also after a failure, which may be due to another admin's change. */
    remove(page: PreprocessedPage) {
      const index = this.rowIndex(page);
      this.removingId = page.idpage;
      this.listError = '';

      AjaxHelper.post(
        { method: 'Transitions.removePreprocessedPage', idSite: Matomo.idSite },
        { idPage: page.idpage },
        { createErrorNotification: false },
      ).then(
        () => {
          this.showSuccess(removedMessageHtml(page.url));
          return '';
        },
        (error) => apiErrorText(error),
      ).then((error) => this.loadPages(false, error)).then(() => {
        this.removingId = null;

        if (!this.isRemoveDialogOpen) {
          this.focusRemoveButton(index);
        }
      });
    },
    confirmLabel(url: string): string {
      return stopConfirmLabel(url);
    },
    formatDate(date: string): string {
      return formatUtcDateTime(date);
    },
    readableUrl(url: string): string {
      return readableUrl(url);
    },
    /** The stored URL, where the list shows it decoded; hidden characters stay encoded. */
    urlTitle(url: string): string|undefined {
      const stored = encodeHiddenCharacters(displayUrl(url));
      return readableUrl(url) !== stored ? stored : undefined;
    },
    encodeHiddenCharacters(text: string): string {
      return encodeHiddenCharacters(text);
    },
    formatNumber(value: number|string): string {
      return NumberFormatter.formatNumber(Number(value));
    },
  },
});
</script>
