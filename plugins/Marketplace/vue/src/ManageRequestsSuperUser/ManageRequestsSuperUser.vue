<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="manageRequestsSuperUser">
    <h2 class="manageRequestsSuperUser__title">{{ translate('Marketplace_ManageRequests') }}</h2>
    <p class="manageRequestsSuperUser__intro">{{ translate('Marketplace_ManageRequestsSuperUserIntro') }}</p>

    <div class="manageRequestsSuperUser__tabList" role="tablist">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        role="tab"
        class="manageRequestsSuperUser__tabItem"
        :class="{ 'manageRequestsSuperUser__tabItem--active': tab.id === activeTab }"
        :aria-selected="tab.id === activeTab ? 'true' : 'false'"
        :data-tab="tab.id"
        @click="activeTab = tab.id"
      >{{ tab.label }}</button>
    </div>

    <div
      v-if="activeTab === 'pending'"
      class="manageRequestsSuperUser__panel manageRequestsSuperUser__pending"
      role="tabpanel"
    >
      <p v-if="pending.length === 0" class="manageRequestsSuperUser__empty">
        {{ translate('Marketplace_PendingRequestsEmpty') }}
      </p>

      <template v-else>
        <Alert v-if="showUserLimitBanner" severity="info" class="manageRequestsSuperUser__userLimit">
          <div
            class="manageRequestsSuperUser__userLimitText"
            v-html="$sanitize(translate(
              'Marketplace_ShopUserLimitExceeded',
              `<strong>${numUsers}</strong>`,
              `${shopUserLimit}`,
            ))"
          />
          <a
            class="manageRequestsSuperUser__userLimitContactSales"
            :href="contactSalesUrl"
            target="_blank"
            rel="noreferrer noopener"
          >{{ translate('Marketplace_ContactSales') }}</a>
        </Alert>

        <div
          v-if="showInstallAllFree && canInstallFreePlugins && !showUserLimitBanner"
          class="manageRequestsSuperUser__toolbar"
        >
          <a
            class="btn btn-outline manageRequestsSuperUser__installAllFree"
            :class="{ disabled: isInstalling || busyPlugins.length > 0 }"
            :aria-disabled="isInstalling || busyPlugins.length > 0 ? 'true' : undefined"
            :href="installLink()"
            @click="onInstallClick($event, busyPlugins.length > 0)"
          >{{ translate('Marketplace_InstallAllFree') }}</a>
        </div>

        <div class="manageRequestsSuperUser__tableScroll">
          <table class="manageRequestsSuperUser__table">
            <thead>
            <tr class="manageRequestsSuperUser__headRow">
              <th
                v-for="column in pendingColumns"
                :key="column.id"
                class="manageRequestsSuperUser__headCell"
                :class="{ 'manageRequestsSuperUser__headCell--indented': column.id === 'displayName' }"
                :aria-sort="ariaSort(column.id)"
              >
                <button
                  type="button"
                  class="manageRequestsSuperUser__sort"
                  :data-sort="column.id"
                  @click="sortPendingBy(column.id)"
                >
                  {{ column.label }}
                  <span
                    class="manageRequestsSuperUser__sortIcon"
                    :class="sortIconClass(column.id)"
                    aria-hidden="true"
                  />
                </button>
              </th>
              <th class="manageRequestsSuperUser__headCell manageRequestsSuperUser__headCell--alignEnd">
                {{ translate('General_Action') }}
              </th>
            </tr>
            </thead>
            <tbody>
            <template v-for="plugin in sortedPending" :key="plugin.pluginName">
              <tr class="manageRequestsSuperUser__row" :data-plugin="plugin.pluginName">
                <td class="manageRequestsSuperUser__cell manageRequestsSuperUser__cell--flushStart">
                  <div class="manageRequestsSuperUser__pluginName">
                    <button
                      type="button"
                      class="manageRequestsSuperUser__toggle"
                      :aria-expanded="isExpanded(plugin) ? 'true' : 'false'"
                      :aria-label="translate('Marketplace_ShowPluginRequesters', plugin.displayName)"
                      @click="toggleRequesters(plugin)"
                    >
                      <span
                        class="manageRequestsSuperUser__toggleIcon"
                        :class="isExpanded(plugin) ? 'icon-chevron-down' : 'icon-chevron-right'"
                        aria-hidden="true"
                      />
                    </button>
                    <a
                      class="manageRequestsSuperUser__pluginLink"
                      v-plugin-name="{ pluginName: plugin.pluginName }"
                    >{{ plugin.displayName }}</a>
                  </div>
                </td>
                <td class="manageRequestsSuperUser__cell manageRequestsSuperUser__numRequests">
                  {{ plugin.numRequests }}
                </td>
                <td class="manageRequestsSuperUser__cell manageRequestsSuperUser__date">
                  {{ plugin.lastRequestedAtPretty }}
                </td>
                <td class="manageRequestsSuperUser__cell manageRequestsSuperUser__price">
                  {{ priceLabel(plugin) }}
                </td>
                <td class="manageRequestsSuperUser__cell">
                  <div class="manageRequestsSuperUser__actionList">
                    <button
                      type="button"
                      class="manageRequestsSuperUser__decline"
                      :disabled="isBusy(plugin)"
                      @click="openDeclineDialog(plugin)"
                    >{{ translate('Marketplace_Decline') }}</button>
                    <a
                      v-if="plugin.isFree === true && plugin.canBeInstalled"
                      class="btn btn-outline manageRequestsSuperUser__approveAndInstall"
                      :class="{ disabled: isInstalling || isBusy(plugin) }"
                      :aria-disabled="isInstalling || isBusy(plugin) ? 'true' : undefined"
                      :href="installLink(plugin.pluginName)"
                      @click="onInstallClick($event, isBusy(plugin))"
                    >{{ translate('Marketplace_ApproveAndInstall') }}</a>
                    <a
                      v-else-if="plugin.isFree === false && isOverShopUserLimit"
                      class="btn btn-outline manageRequestsSuperUser__contactSales"
                      :href="contactSalesUrl"
                      target="_blank"
                      rel="noreferrer noopener"
                    >{{ translate('Marketplace_ContactSales') }}</a>
                    <button
                      v-else-if="plugin.isFree === false"
                      type="button"
                      class="btn manageRequestsSuperUser__startTrial"
                      :disabled="isBusy(plugin)"
                      @click="startTrial(plugin)"
                    >{{ translate('Marketplace_StartTrial30Days') }}</button>
                  </div>
                </td>
              </tr>
              <tr
                v-if="isExpanded(plugin) || isClosing(plugin)"
                class="manageRequestsSuperUser__requesterPanel"
                :data-plugin="plugin.pluginName"
              >
                <td colspan="5" class="manageRequestsSuperUser__requesterPanelCell">
                  <Transition
                    appear
                    enter-from-class="manageRequestsSuperUser__requesterReveal--collapsed"
                    enter-active-class="manageRequestsSuperUser__requesterReveal--sliding"
                    leave-active-class="manageRequestsSuperUser__requesterReveal--sliding"
                    leave-to-class="manageRequestsSuperUser__requesterReveal--collapsed"
                    @after-leave="onRequestersClosed(plugin)"
                  >
                    <div v-if="isExpanded(plugin)" class="manageRequestsSuperUser__requesterReveal">
                      <div class="manageRequestsSuperUser__requesterRevealContent">
                        <table class="manageRequestsSuperUser__requesterTable">
                          <thead>
                          <tr class="manageRequestsSuperUser__requesterHeadRow">
                            <th class="manageRequestsSuperUser__requesterHeadCell">
                              {{ translate('Marketplace_RequestedBy') }}
                            </th>
                            <th class="manageRequestsSuperUser__requesterHeadCell">
                              {{ translate('Marketplace_RequestedOn') }}
                            </th>
                            <th class="manageRequestsSuperUser__requesterHeadCell">
                              {{ translate('General_Note') }}
                            </th>
                          </tr>
                          </thead>
                          <tbody>
                          <tr
                            v-for="(requester, index) in plugin.requesters"
                            :key="index"
                            class="manageRequestsSuperUser__requesterRow"
                          >
                            <td class="manageRequestsSuperUser__requesterCell">
                              <span>{{ requester.login || translate('General_Unknown') }}</span>
                              <span
                                v-if="requester.email"
                                class="manageRequestsSuperUser__email"
                              >{{ requester.email }}</span>
                            </td>
                            <td class="manageRequestsSuperUser__requesterCell manageRequestsSuperUser__date">
                              {{ requester.requestedAtPretty }}
                            </td>
                            <td class="manageRequestsSuperUser__requesterCell">
                              <span
                                class="manageRequestsSuperUser__note"
                                :title="requester.message || undefined"
                              >{{ requester.message || '-' }}</span>
                            </td>
                          </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </Transition>
                </td>
              </tr>
            </template>
            </tbody>
          </table>
        </div>
      </template>
    </div>

    <div v-else class="manageRequestsSuperUser__panel manageRequestsSuperUser__history" role="tabpanel">
      <p v-if="historyPage.total === 0" class="manageRequestsSuperUser__empty">
        {{ translate('Marketplace_NoPluginRequestDecisions') }}
      </p>

      <template v-else>
        <div class="manageRequestsSuperUser__tableScroll">
          <table class="manageRequestsSuperUser__table">
            <thead>
            <tr class="manageRequestsSuperUser__headRow">
              <th class="manageRequestsSuperUser__headCell">{{ translate('General_Plugin') }}</th>
              <th class="manageRequestsSuperUser__headCell">{{ translate('Marketplace_Requests') }}</th>
              <th class="manageRequestsSuperUser__headCell">{{ translate('Marketplace_Decision') }}</th>
              <th class="manageRequestsSuperUser__headCell">{{ translate('Marketplace_DecidedBy') }}</th>
              <th class="manageRequestsSuperUser__headCell">{{ translate('General_Note') }}</th>
              <th class="manageRequestsSuperUser__headCell">{{ translate('General_Date') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr
              v-for="(decision, index) in historyPage.decisions"
              :key="`${decision.pluginName}-${decision.decidedAt}-${index}`"
              class="manageRequestsSuperUser__decision"
              :data-plugin="decision.pluginName"
            >
              <td class="manageRequestsSuperUser__cell">{{ decision.displayName }}</td>
              <td class="manageRequestsSuperUser__cell">{{ decision.numRequests }}</td>
              <td class="manageRequestsSuperUser__cell">
                <span
                  class="manageRequestsSuperUser__status"
                  :class="`manageRequestsSuperUser__status--${decision.decision}`"
                >{{ decisionLabel(decision.decision) }}</span>
              </td>
              <td class="manageRequestsSuperUser__cell">{{ decision.decidedBy || '-' }}</td>
              <td class="manageRequestsSuperUser__cell">
                <span
                  class="manageRequestsSuperUser__note"
                  :title="decision.note || undefined"
                >{{ decision.note || '-' }}</span>
              </td>
              <td class="manageRequestsSuperUser__cell manageRequestsSuperUser__date">
                {{ decision.decidedAtPretty }}
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <div class="manageRequestsSuperUser__pagination">
          <span class="manageRequestsSuperUser__paginationInfo">{{ paginationLabel }}</span>
          <button
            v-if="historyOffset > 0"
            type="button"
            class="btn-flat manageRequestsSuperUser__previous"
            :disabled="isLoadingHistory"
            @click="loadHistory(historyOffset - historyPageSize)"
          >‹ {{ translate('General_Previous') }}</button>
          <button
            v-if="historyOffset + historyPageSize < historyPage.total"
            type="button"
            class="btn-flat manageRequestsSuperUser__next"
            :disabled="isLoadingHistory"
            @click="loadHistory(historyOffset + historyPageSize)"
          >{{ translate('General_Next') }} ›</button>
        </div>
      </template>
    </div>

    <div class="ui-confirm manageRequestsSuperUser__declineDialog" ref="declineDialog">
      <button
        type="button"
        class="btn-close modal-close"
        :aria-label="translate('General_Close')"
      ><span class="icon-close"></span></button>
      <h2 class="manageRequestsSuperUser__declineTitle">{{ translate('Marketplace_DeclineRequestTitle') }}</h2>
      <p class="manageRequestsSuperUser__declineIntro">{{ declineText }}</p>
      <div class="manageRequestsSuperUser__declineMessage">
        <label class="manageRequestsSuperUser__declineLabel" for="declineRequestNote">
          {{ translate('Marketplace_MessageToRequesters') }}
        </label>
        <textarea
          id="declineRequestNote"
          class="materialize-textarea manageRequestsSuperUser__declineInput"
          maxlength="1000"
          :placeholder="translate('Marketplace_MessageToRequestersPlaceholder')"
          v-model="declineNote"
        />
      </div>
      <input role="no" type="button" :value="translate('General_Cancel')"/>
      <input role="validation" type="button" :value="translate('Marketplace_DeclineRequest')"/>
    </div>

    <StartFreeTrial
      v-model="trialPlugin"
      api-method="Marketplace.approvePluginTrial"
      :is-valid-consumer="isValidConsumer"
      :current-user-email="currentUserEmail || undefined"
      @trial-started="onTrialStarted"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  AjaxHelper,
  Alert,
  Matomo,
  MatomoUrl,
  NotificationsStore,
  externalRawLink,
  translate,
} from 'CoreHome';
import { PluginName } from 'CorePluginsAdmin';
import { IPluginShopVariation } from '../types';
import {
  PERIOD_MONTHLY,
  ShopPeriod,
  formatAmount,
  monthlyAmount,
  toShopPeriod,
  variationPrice,
} from '../PluginDetails/shopPricing';
import StartFreeTrial from '../StartFreeTrial/StartFreeTrial.vue';

export interface PluginRequester {
  login: string | null; // null for a deleted user
  email: string | null;
  requestedAt: string;
  requestedAtPretty: string;
  message: string | null;
}

export interface PendingPluginRequest {
  pluginName: string;
  displayName: string;
  isFree: boolean | null; // null when the Marketplace cannot be reached
  canBeInstalled: boolean;
  priceFrom: IPluginShopVariation | null;
  requestType: 'trial' | 'install';
  numRequests: number;
  lastRequestedAt: string;
  lastRequestedAtPretty: string;
  requesters: PluginRequester[];
}

export interface PluginRequestDecision {
  pluginName: string;
  displayName: string;
  numRequests: number;
  decision: 'approved' | 'declined';
  decidedBy: string | null; // null for an automatic decision, or a deleted user
  note: string | null;
  decidedAt: string;
  decidedAtPretty: string;
}

export interface PluginRequestHistory {
  total: number;
  decisions: PluginRequestDecision[];
}

type Tab = 'pending' | 'history';

type PendingSortColumn = 'displayName' | 'numRequests' | 'lastRequestedAt' | 'price';

type SortOrder = 'asc' | 'desc';

const FIRST_SORT_ORDER: Record<PendingSortColumn, SortOrder> = {
  displayName: 'asc',
  numRequests: 'desc',
  lastRequestedAt: 'desc',
  price: 'asc',
};

interface ShownPrice {
  variation: IPluginShopVariation;
  price: number;
  period: ShopPeriod;
  currency: string;
}

/**
 * Null when the price is unknown, which the table shows as '-' and the price sort puts last
 */
function shownPrice(plugin: PendingPluginRequest): ShownPrice | null {
  const variation = plugin.priceFrom;
  const price = variationPrice(variation || undefined);
  const period = toShopPeriod(variation?.period || '');
  if (!variation || price === null || !period || !variation.currency) {
    return null;
  }

  return {
    variation,
    price,
    period,
    currency: variation.currency,
  };
}

/**
 * Per month, so that monthly and annual prices compare; null when the price is unknown
 */
function monthlyPrice(plugin: PendingPluginRequest): number | null {
  if (plugin.isFree) {
    return 0;
  }

  const shown = shownPrice(plugin);
  return shown ? monthlyAmount(shown.variation, shown.period) : null;
}

function comparePending(
  a: PendingPluginRequest,
  b: PendingPluginRequest,
  column: Exclude<PendingSortColumn, 'price'>,
): number {
  if (column === 'displayName') {
    return a.displayName.localeCompare(b.displayName, undefined, { sensitivity: 'base' });
  }

  if (column === 'numRequests') {
    return a.numRequests - b.numRequests;
  }

  // UTC as `Y-m-d H:i:s`, so the text order is the time order
  return a.lastRequestedAt.localeCompare(b.lastRequestedAt);
}

interface ManageRequestsSuperUserState {
  activeTab: Tab;
  pending: PendingPluginRequest[];
  sortColumn: PendingSortColumn;
  sortOrder: SortOrder;
  expandedPlugins: string[];
  closingPlugins: string[];
  historyPage: PluginRequestHistory;
  historyOffset: number;
  isLoadingHistory: boolean;
  decliningPlugin: PendingPluginRequest | null;
  busyPlugins: string[];
  isInstalling: boolean;
  declineNote: string;
  trialPlugin: { name: string, displayName: string } | null;
  trialPluginName: string | null;
}

const DECISION_LABELS: Record<string, string> = {
  approved: 'Marketplace_RequestStatusApproved',
  declined: 'Marketplace_RequestStatusDeclined',
};

export default defineComponent({
  props: {
    /** From Marketplace.getPendingPluginRequests, with the dates formatted. */
    pendingRequests: {
      type: Array as PropType<PendingPluginRequest[]>,
      required: true,
    },
    /** The first page of Marketplace.getPluginRequestHistory, with the dates formatted. */
    history: {
      type: Object as PropType<PluginRequestHistory>,
      required: true,
    },
    historyPageSize: {
      type: Number,
      required: true,
    },
    isValidConsumer: Boolean,
    currentUserEmail: [String, Boolean] as PropType<string | false>,
    installRequestedNonce: {
      type: String,
      required: true,
    },
    numUsers: {
      type: Number,
      required: true,
    },
    /** The most users a plan bought in the shop covers. */
    shopUserLimit: {
      type: Number,
      required: true,
    },
    /** Off until premium plugins can be added to the cart in bulk too, as both bulk actions ship together. */
    showInstallAllFree: Boolean,
  },
  components: {
    Alert,
    StartFreeTrial,
  },
  directives: {
    PluginName,
  },
  data(): ManageRequestsSuperUserState {
    return {
      activeTab: 'pending',
      pending: [...this.pendingRequests],
      // the order Marketplace.getPendingPluginRequests returns
      sortColumn: 'lastRequestedAt',
      sortOrder: 'desc',
      expandedPlugins: [],
      closingPlugins: [],
      historyPage: this.history,
      historyOffset: 0,
      isLoadingHistory: false,
      decliningPlugin: null,
      busyPlugins: [],
      isInstalling: false,
      declineNote: '',
      trialPlugin: null,
      trialPluginName: null,
    };
  },
  mounted() {
    window.addEventListener('pageshow', this.onPageShow);
  },
  beforeUnmount() {
    window.removeEventListener('pageshow', this.onPageShow);
  },
  computed: {
    tabs(): { id: Tab, label: string }[] {
      return [
        { id: 'pending', label: translate('Marketplace_PendingRequestsTab', `${this.pending.length}`) },
        { id: 'history', label: translate('CorePluginsAdmin_History') },
      ];
    },
    pendingColumns(): { id: PendingSortColumn, label: string }[] {
      return [
        { id: 'displayName', label: translate('General_Plugin') },
        { id: 'numRequests', label: translate('Marketplace_Requests') },
        { id: 'lastRequestedAt', label: translate('Marketplace_LastRequested') },
        { id: 'price', label: translate('General_Price') },
      ];
    },
    sortedPending(): PendingPluginRequest[] {
      const column = this.sortColumn;
      const direction = this.sortOrder === 'asc' ? 1 : -1;

      return [...this.pending].sort((a, b) => {
        if (column === 'price') {
          const priceA = monthlyPrice(a);
          const priceB = monthlyPrice(b);

          // an unknown price is last whichever way the column is sorted
          if (priceA === null || priceB === null) {
            if (priceA !== priceB) {
              return priceA === null ? 1 : -1;
            }
          } else if (priceA !== priceB) {
            return (priceA - priceB) * direction;
          }
        } else {
          const result = comparePending(a, b, column);
          if (result !== 0) {
            return result * direction;
          }
        }

        return comparePending(b, a, 'lastRequestedAt') || comparePending(a, b, 'displayName');
      });
    },
    declineText(): string {
      const numRequests = this.decliningPlugin?.numRequests || 0;

      return numRequests === 1
        ? translate('Marketplace_DeclineRequestTextOne')
        : translate('Marketplace_DeclineRequestTextMany', `${numRequests}`);
    },
    isOverShopUserLimit(): boolean {
      return this.numUsers > this.shopUserLimit;
    },
    hasPendingPaidPlugin(): boolean {
      return this.pending.some((plugin) => plugin.isFree === false);
    },
    showUserLimitBanner(): boolean {
      return this.isOverShopUserLimit && this.hasPendingPaidPlugin;
    },
    canInstallFreePlugins(): boolean {
      return this.pending.some((plugin) => plugin.isFree === true && plugin.canBeInstalled);
    },
    contactSalesUrl(): string {
      return externalRawLink('https://matomo.org/contact-sales/');
    },
    paginationLabel(): string {
      return translate(
        'General_Pagination',
        `${this.historyOffset + 1}`,
        `${this.historyOffset + this.historyPage.decisions.length}`,
        `${this.historyPage.total}`,
      );
    },
  },
  methods: {
    isExpanded(plugin: PendingPluginRequest): boolean {
      return this.expandedPlugins.includes(plugin.pluginName);
    },
    isBusy(plugin: PendingPluginRequest): boolean {
      return this.busyPlugins.includes(plugin.pluginName);
    },
    isClosing(plugin: PendingPluginRequest): boolean {
      return this.closingPlugins.includes(plugin.pluginName);
    },
    toggleRequesters(plugin: PendingPluginRequest) {
      if (this.isExpanded(plugin)) {
        this.expandedPlugins = this.expandedPlugins.filter((name) => name !== plugin.pluginName);
        this.closingPlugins = [...this.closingPlugins, plugin.pluginName];
        return;
      }

      this.closingPlugins = this.closingPlugins.filter((name) => name !== plugin.pluginName);
      this.expandedPlugins = [...this.expandedPlugins, plugin.pluginName];
    },
    onRequestersClosed(plugin: PendingPluginRequest) {
      this.closingPlugins = this.closingPlugins.filter((name) => name !== plugin.pluginName);
    },
    sortPendingBy(column: PendingSortColumn) {
      if (this.sortColumn === column) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortOrder = FIRST_SORT_ORDER[column];
      }
    },
    sortIconClass(column: PendingSortColumn): string {
      if (this.sortColumn !== column) {
        return 'icon-chevron-down manageRequestsSuperUser__sortIcon--inactive';
      }

      return this.sortOrder === 'asc' ? 'icon-chevron-up' : 'icon-chevron-down';
    },
    ariaSort(column: PendingSortColumn): 'ascending' | 'descending' | 'none' {
      if (this.sortColumn !== column) {
        return 'none';
      }

      return this.sortOrder === 'asc' ? 'ascending' : 'descending';
    },
    priceLabel(plugin: PendingPluginRequest): string {
      if (plugin.isFree) {
        return translate('Marketplace_Free');
      }

      const shown = shownPrice(plugin);
      if (!shown) {
        return '-';
      }

      const amountPeriod = translate(
        shown.period === PERIOD_MONTHLY ? 'Marketplace_PerMonthWithCurrency' : 'Marketplace_PerYearWithCurrency',
        shown.currency,
      );

      return translate('Marketplace_PriceFrom', `${formatAmount(shown.price)} ${amountPeriod}`);
    },
    /**
     * The link's nonce is single-use, so a second click would fail the install the first one started
     */
    onInstallClick(event: MouseEvent, isBusy: boolean) {
      if (isBusy || this.isInstalling) {
        event.preventDefault();
        return;
      }

      this.isInstalling = true;
    },
    onPageShow(event: PageTransitionEvent) {
      // a page restored from the back/forward cache still has the flag set by the click that left it
      if (event.persisted) {
        this.isInstalling = false;
      }
    },
    /**
     * Without a plugin name, the action installs every pending free plugin
     */
    installLink(pluginName?: string): string {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        idSite: MatomoUrl.parsed.value.idSite,
        module: 'Marketplace',
        action: 'installRequestedPlugins',
        nonce: this.installRequestedNonce,
        // null drops what the page's own URL may carry
        pluginName: pluginName || null,
        token_auth: null,
      })}`;
    },
    decisionLabel(decision: string): string {
      return DECISION_LABELS[decision] ? translate(DECISION_LABELS[decision]) : decision;
    },
    removePending(pluginName: string) {
      this.pending = this.pending.filter((plugin) => plugin.pluginName !== pluginName);
      this.expandedPlugins = this.expandedPlugins.filter((name) => name !== pluginName);
      this.closingPlugins = this.closingPlugins.filter((name) => name !== pluginName);
    },
    loadHistory(offset: number): Promise<void> {
      this.isLoadingHistory = true;

      return AjaxHelper.fetch<PluginRequestHistory>({
        module: 'Marketplace',
        action: 'getPluginRequestHistoryPage',
        offset,
      }).then((history) => {
        this.historyPage = history;
        this.historyOffset = offset;
      }).finally(() => {
        this.isLoadingHistory = false;
      });
    },
    openDeclineDialog(plugin: PendingPluginRequest) {
      this.decliningPlugin = plugin;
      this.declineNote = '';

      const dialog = this.$refs.declineDialog as HTMLElement;

      Matomo.helper.modalConfirm(
        dialog,
        {
          validation: () => {
            const note = this.declineNote;
            $(dialog).closest('.modal').modal('close');
            this.decline(plugin, note);
          },
        },
        {
          onCloseEnd: () => {
            this.decliningPlugin = null;
          },
          focusSelector: '#declineRequestNote',
          classes: 'manageRequestsSuperUser__declineModal',
        },
      );
    },
    decline(plugin: PendingPluginRequest, note: string) {
      this.busyPlugins = [...this.busyPlugins, plugin.pluginName];

      AjaxHelper.post<{ value: number }>(
        {
          module: 'API',
          method: 'Marketplace.declinePluginRequest',
        },
        {
          pluginName: plugin.pluginName,
          note,
        },
      ).then((response) => {
        const numDeclined = response.value;

        this.removePending(plugin.pluginName);

        const notificationInstanceId = NotificationsStore.show({
          message: numDeclined === 1
            ? translate('Marketplace_RequestDeclinedOne')
            : translate('Marketplace_RequestDeclinedMany', `${numDeclined}`),
          context: 'success',
          id: 'pluginRequestDeclined',
          placeat: '#notificationContainer',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);

        return this.loadHistory(0);
      }).catch(() => {
        // AjaxHelper already shows the error, and the row stays for another try
      }).finally(() => {
        this.busyPlugins = this.busyPlugins.filter((name) => name !== plugin.pluginName);
      });
    },
    startTrial(plugin: PendingPluginRequest) {
      this.trialPluginName = plugin.pluginName;
      this.trialPlugin = { name: plugin.pluginName, displayName: plugin.displayName };
    },
    onTrialStarted() {
      if (this.trialPluginName) {
        this.removePending(this.trialPluginName);
      }

      this.trialPluginName = null;
      this.loadHistory(0);
    },
  },
});
</script>
