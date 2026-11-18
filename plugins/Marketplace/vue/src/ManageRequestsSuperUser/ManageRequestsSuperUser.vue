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

      <div v-else class="manageRequestsSuperUser__tableScroll">
        <table class="manageRequestsSuperUser__table">
          <thead>
          <tr class="manageRequestsSuperUser__headRow">
            <th class="manageRequestsSuperUser__headCell manageRequestsSuperUser__headCell--indented">
              {{ translate('General_Plugin') }}
            </th>
            <th class="manageRequestsSuperUser__headCell">{{ translate('Marketplace_Requests') }}</th>
            <th class="manageRequestsSuperUser__headCell">{{ translate('Marketplace_LastRequested') }}</th>
            <th class="manageRequestsSuperUser__headCell">{{ translate('General_Price') }}</th>
            <th class="manageRequestsSuperUser__headCell manageRequestsSuperUser__headCell--alignEnd">
              {{ translate('General_Action') }}
            </th>
          </tr>
          </thead>
          <tbody>
          <template v-for="plugin in pending" :key="plugin.pluginName">
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
                  <button
                    v-if="plugin.isFree === false"
                    type="button"
                    class="btn btn-outline manageRequestsSuperUser__startTrial"
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
  Matomo,
  NotificationsStore,
  translate,
} from 'CoreHome';
import { PluginName } from 'CorePluginsAdmin';
import { IPluginShopVariation } from '../types';
import {
  PERIOD_MONTHLY,
  formatAmount,
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

interface ManageRequestsSuperUserState {
  activeTab: Tab;
  pending: PendingPluginRequest[];
  expandedPlugins: string[];
  closingPlugins: string[];
  historyPage: PluginRequestHistory;
  historyOffset: number;
  isLoadingHistory: boolean;
  decliningPlugin: PendingPluginRequest | null;
  busyPlugins: string[];
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
  },
  components: {
    StartFreeTrial,
  },
  directives: {
    PluginName,
  },
  data(): ManageRequestsSuperUserState {
    return {
      activeTab: 'pending',
      pending: [...this.pendingRequests],
      expandedPlugins: [],
      closingPlugins: [],
      historyPage: this.history,
      historyOffset: 0,
      isLoadingHistory: false,
      decliningPlugin: null,
      busyPlugins: [],
      declineNote: '',
      trialPlugin: null,
      trialPluginName: null,
    };
  },
  computed: {
    tabs(): { id: Tab, label: string }[] {
      return [
        { id: 'pending', label: translate('Marketplace_PendingRequestsTab', `${this.pending.length}`) },
        { id: 'history', label: translate('CorePluginsAdmin_History') },
      ];
    },
    declineText(): string {
      const numRequests = this.decliningPlugin?.numRequests || 0;

      return numRequests === 1
        ? translate('Marketplace_DeclineRequestTextOne')
        : translate('Marketplace_DeclineRequestTextMany', `${numRequests}`);
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
    priceLabel(plugin: PendingPluginRequest): string {
      if (plugin.isFree) {
        return translate('Marketplace_Free');
      }

      const price = variationPrice(plugin.priceFrom || undefined);
      const period = toShopPeriod(plugin.priceFrom?.period || '');
      if (price === null || !period || !plugin.priceFrom?.currency) {
        return '-';
      }

      const amountPeriod = translate(
        period === PERIOD_MONTHLY ? 'Marketplace_PerMonthWithCurrency' : 'Marketplace_PerYearWithCurrency',
        plugin.priceFrom.currency,
      );

      return translate('Marketplace_PriceFrom', `${formatAmount(price)} ${amountPeriod}`);
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
