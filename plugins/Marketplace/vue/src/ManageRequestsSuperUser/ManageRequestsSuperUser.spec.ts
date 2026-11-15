/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { flushPromises, mount, VueWrapper } from '@vue/test-utils';

const {
  modalConfirm, ajaxPost, ajaxFetch, showNotification, closeModal,
} = vi.hoisted(() => ({
  modalConfirm: vi.fn(),
  ajaxPost: vi.fn(),
  ajaxFetch: vi.fn(),
  showNotification: vi.fn(),
  closeModal: vi.fn(),
}));

vi.stubGlobal('$', () => ({ closest: () => ({ modal: closeModal }) }));

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports.
vi.mock('CoreHome', async () => ({
  ...(await import('../testCoreHomeMock')).coreHomeMock(),
  Matomo: { helper: { modalConfirm } },
  AjaxHelper: { post: ajaxPost, fetch: ajaxFetch },
  NotificationsStore: { show: showNotification, scrollToNotification: vi.fn() },
  NumberFormatter: {
    formatNumber: (value: number, max: number, min: number) => Number(value).toLocaleString(
      'en-US',
      { maximumFractionDigits: max, minimumFractionDigits: min },
    ),
  },
}));

vi.mock('CorePluginsAdmin', () => ({ Field: { template: '<div/>' } }));

/* eslint-disable import/first */
import ManageRequestsSuperUser, {
  PendingPluginRequest,
  PluginRequestDecision,
  PluginRequestHistory,
} from './ManageRequestsSuperUser.vue';
import { translateStub } from '../testCoreHomeMock';
import { IPluginShopVariation } from '../types';

function makePending(overrides: Partial<PendingPluginRequest> = {}): PendingPluginRequest {
  return {
    pluginName: 'Funnels',
    displayName: 'Funnels',
    isFree: false,
    priceFrom: {
      price: '239',
      currency: 'USD',
      period: 'year',
    } as IPluginShopVariation,
    requestType: 'trial',
    numRequests: 2,
    lastRequestedAt: '2026-11-08 10:00:00',
    lastRequestedAtPretty: 'Sunday, November 8, 2026',
    requesters: [
      {
        login: 'jane',
        email: 'jane@example.test',
        requestedAt: '2026-11-08 10:00:00',
        requestedAtPretty: 'Sunday, November 8, 2026',
        message: 'For the checkout',
      },
      {
        login: 'joe',
        email: 'joe@example.test',
        requestedAt: '2026-11-01 10:00:00',
        requestedAtPretty: 'Sunday, November 1, 2026',
        message: null,
      },
    ],
    ...overrides,
  };
}

function makeDecision(overrides: Partial<PluginRequestDecision> = {}): PluginRequestDecision {
  return {
    pluginName: 'CustomReports',
    displayName: 'Custom Reports',
    numRequests: 2,
    decision: 'declined',
    decidedBy: 'admin',
    note: 'We use another tool',
    decidedAt: '2026-10-26 10:00:00',
    decidedAtPretty: 'Monday, October 26, 2026',
    ...overrides,
  };
}

function makeHistory(total: number, offset = 0, pageSize = 5): PluginRequestHistory {
  const count = Math.max(0, Math.min(pageSize, total - offset));

  return {
    total,
    decisions: Array.from({ length: count }, (_, index) => makeDecision({
      pluginName: `Plugin${offset + index + 1}`,
      displayName: `Plugin ${offset + index + 1}`,
    })),
  };
}

const StartFreeTrialStub = {
  name: 'StartFreeTrial',
  props: ['modelValue', 'apiMethod', 'isValidConsumer', 'currentUserEmail'],
  emits: ['update:modelValue', 'trialStarted'],
  template: '<div class="startFreeTrialStub"/>',
};

function mountPage(
  pendingRequests: PendingPluginRequest[] = [makePending()],
  history: PluginRequestHistory = makeHistory(1),
) {
  return mount(ManageRequestsSuperUser, {
    props: {
      pendingRequests,
      history,
      historyPageSize: 5,
      isValidConsumer: true,
      currentUserEmail: 'admin@example.test',
    },
    global: {
      mocks: { translate: translateStub },
      stubs: { StartFreeTrial: StartFreeTrialStub },
    },
  });
}

type Page = VueWrapper<InstanceType<typeof ManageRequestsSuperUser>>;

const tab = (wrapper: Page, id: string) => wrapper.find(`[data-tab="${id}"]`);
const row = (wrapper: Page, plugin: string) => wrapper
  .find(`.manageRequestsSuperUser__row[data-plugin="${plugin}"]`);
const cells = (wrapper: Page, plugin: string) => row(wrapper, plugin)
  .findAll('td').map((cell) => cell.text());
const headers = (wrapper: Page) => wrapper.findAll('.manageRequestsSuperUser__table > thead th')
  .map((th) => th.text());

async function showHistory(wrapper: Page) {
  await tab(wrapper, 'history').trigger('click');
}

/** Opens the decline dialog of the plugin, and returns its Decline request handler. */
async function openDeclineDialog(wrapper: Page, plugin: string) {
  await row(wrapper, plugin).find('.manageRequestsSuperUser__decline').trigger('click');
  const handles = modalConfirm.mock.calls[modalConfirm.mock.calls.length - 1][1];

  return handles.validation as () => void;
}

describe('Marketplace/ManageRequestsSuperUser', () => {
  beforeEach(() => {
    modalConfirm.mockClear();
    closeModal.mockClear();
    showNotification.mockClear();
    ajaxPost.mockReset();
    ajaxFetch.mockReset();
    ajaxFetch.mockResolvedValue(makeHistory(1));
  });

  describe('tabs', () => {
    it('counts the plugins with pending requests on the Pending tab', () => {
      const wrapper = mountPage([makePending(), makePending({ pluginName: 'Cohorts' })]);

      expect(tab(wrapper, 'pending').text()).toBe('Marketplace_PendingRequestsTab:2');
      expect(tab(wrapper, 'history').text()).toBe('CorePluginsAdmin_History');
    });

    it('opens on the Pending tab and switches to History', async () => {
      const wrapper = mountPage();

      expect(tab(wrapper, 'pending').attributes('aria-selected')).toBe('true');
      expect(wrapper.find('.manageRequestsSuperUser__history').exists()).toBe(false);

      await showHistory(wrapper);

      expect(tab(wrapper, 'history').attributes('aria-selected')).toBe('true');
      expect(wrapper.find('.manageRequestsSuperUser__pending').exists()).toBe(false);
      expect(wrapper.find('.manageRequestsSuperUser__history').exists()).toBe(true);
    });
  });

  describe('Pending', () => {
    it('names the columns', () => {
      expect(headers(mountPage())).toEqual([
        'General_Plugin',
        'Marketplace_Requests',
        'Marketplace_LastRequested',
        'General_Price',
        'General_Action',
      ]);
    });

    it('lists a plugin with its number of requests, last request and price', () => {
      const wrapper = mountPage();

      expect(cells(wrapper, 'Funnels').slice(0, 4)).toEqual([
        'Funnels',
        '2',
        'Sunday, November 8, 2026',
        '239 Marketplace_PerYearWithCurrency:USD',
      ]);
    });

    it('prices a monthly variation per month', () => {
      const wrapper = mountPage([makePending({
        priceFrom: { price: '19.5', currency: 'EUR', period: 'month' } as IPluginShopVariation,
      })]);

      expect(cells(wrapper, 'Funnels')[3]).toBe('19.50 Marketplace_PerMonthWithCurrency:EUR');
    });

    it('offers a decline and a trial for a premium plugin', () => {
      const actions = row(mountPage(), 'Funnels').findAll('.manageRequestsSuperUser__actionList button');

      expect(actions.map((button) => button.text())).toEqual([
        'Marketplace_Decline',
        'Marketplace_StartTrial30Days',
      ]);
    });

    it('offers only a decline for a free plugin', () => {
      const wrapper = mountPage([makePending({ isFree: true, priceFrom: null })]);

      expect(cells(wrapper, 'Funnels')[3]).toBe('Marketplace_Free');
      expect(row(wrapper, 'Funnels').find('.manageRequestsSuperUser__startTrial').exists()).toBe(false);
      expect(row(wrapper, 'Funnels').find('.manageRequestsSuperUser__decline').exists()).toBe(true);
    });

    it('offers only a decline, without a price, when the Marketplace cannot be reached', () => {
      const wrapper = mountPage([makePending({ isFree: null, priceFrom: null })]);

      expect(cells(wrapper, 'Funnels')[3]).toBe('-');
      expect(row(wrapper, 'Funnels').find('.manageRequestsSuperUser__startTrial').exists()).toBe(false);
    });

    it('shows the requesters of a plugin when its row is expanded', async () => {
      const wrapper = mountPage();
      const toggle = row(wrapper, 'Funnels').find('.manageRequestsSuperUser__toggle');

      expect(wrapper.find('.manageRequestsSuperUser__requesterPanel').exists()).toBe(false);
      expect(toggle.attributes('aria-expanded')).toBe('false');

      await toggle.trigger('click');

      expect(toggle.attributes('aria-expanded')).toBe('true');
      expect(wrapper.findAll('.manageRequestsSuperUser__requesterTable th').map((th) => th.text()))
        .toEqual(['Marketplace_RequestedBy', 'Marketplace_RequestedOn', 'General_Note']);
      const requesters = wrapper.findAll('.manageRequestsSuperUser__requesterRow')
        .map((requester) => requester.findAll('td').map((cell) => cell.text()));
      expect(requesters).toEqual([
        ['janejane@example.test', 'Sunday, November 8, 2026', 'For the checkout'],
        ['joejoe@example.test', 'Sunday, November 1, 2026', '-'],
      ]);
      expect(wrapper.find('.manageRequestsSuperUser__email').text()).toBe('jane@example.test');

      await toggle.trigger('click');

      expect(wrapper.find('.manageRequestsSuperUser__requesterPanel').exists()).toBe(false);
    });

    it('shows a deleted requester as unknown', async () => {
      const wrapper = mountPage([makePending({
        numRequests: 1,
        requesters: [{
          login: null,
          email: null,
          requestedAt: '2026-11-02 10:00:00',
          requestedAtPretty: 'Monday, November 2, 2026',
          message: null,
        }],
      })]);

      await row(wrapper, 'Funnels').find('.manageRequestsSuperUser__toggle').trigger('click');

      expect(wrapper.find('.manageRequestsSuperUser__requesterRow td').text()).toBe('General_Unknown');
    });

    it('says there are no pending requests and points to History', () => {
      const wrapper = mountPage([]);

      expect(tab(wrapper, 'pending').text()).toBe('Marketplace_PendingRequestsTab:0');
      expect(wrapper.find('.manageRequestsSuperUser__pending table').exists()).toBe(false);
      expect(wrapper.find('.manageRequestsSuperUser__empty').text())
        .toBe('Marketplace_PendingRequestsEmpty');
    });
  });

  describe('Decline', () => {
    it('asks for confirmation with an optional message to the requesters', async () => {
      const wrapper = mountPage();

      await openDeclineDialog(wrapper, 'Funnels');

      const dialog = wrapper.find('.ui-confirm');
      expect(modalConfirm).toHaveBeenCalledTimes(1);
      expect(modalConfirm.mock.calls[0][2]).toMatchObject({
        focusSelector: '#declineRequestNote',
        classes: 'requestPluginModal',
      });
      expect(dialog.find('h2').text()).toBe('Marketplace_DeclineRequestTitle');
      expect(dialog.find('p').text()).toBe('Marketplace_DeclineRequestTextMany:2');
      expect(dialog.find('label').text()).toBe('Marketplace_MessageToRequesters');
      expect(dialog.find('textarea').attributes('placeholder'))
        .toBe('Marketplace_MessageToRequestersPlaceholder');
      expect(dialog.find('textarea').attributes('maxlength')).toBe('1000');
      expect(dialog.findAll('input[type="button"]').map((button) => button.attributes('value')))
        .toEqual(['General_Cancel', 'Marketplace_DeclineRequest']);
    });

    it('speaks of one user when one user requested the plugin', async () => {
      const wrapper = mountPage([makePending({ numRequests: 1 })]);

      await openDeclineDialog(wrapper, 'Funnels');

      expect(wrapper.find('.ui-confirm p').text()).toBe('Marketplace_DeclineRequestTextOne');
    });

    it('starts each dialog with an empty message', async () => {
      const wrapper = mountPage();

      await openDeclineDialog(wrapper, 'Funnels');
      await wrapper.find('#declineRequestNote').setValue('Not this year');
      await openDeclineDialog(wrapper, 'Funnels');

      expect((wrapper.find('#declineRequestNote').element as HTMLTextAreaElement).value).toBe('');
    });

    it('declines with the message, removes the row and reports how many users were told', async () => {
      ajaxPost.mockResolvedValue({ value: 2 });
      const wrapper = mountPage([makePending(), makePending({ pluginName: 'Cohorts' })]);

      const decline = await openDeclineDialog(wrapper, 'Funnels');
      await wrapper.find('#declineRequestNote').setValue('Not this year');
      decline();
      await flushPromises();

      expect(closeModal).toHaveBeenCalledWith('close');
      expect(ajaxPost).toHaveBeenCalledWith(
        { module: 'API', method: 'Marketplace.declinePluginRequest' },
        { pluginName: 'Funnels', note: 'Not this year' },
      );
      expect(row(wrapper, 'Funnels').exists()).toBe(false);
      expect(tab(wrapper, 'pending').text()).toBe('Marketplace_PendingRequestsTab:1');
      expect(showNotification).toHaveBeenCalledWith(expect.objectContaining({
        message: 'Marketplace_RequestDeclinedMany:2',
        context: 'success',
      }));
    });

    it('reports one user when one request was declined', async () => {
      ajaxPost.mockResolvedValue({ value: 1 });
      const wrapper = mountPage([makePending({ numRequests: 1 })]);

      (await openDeclineDialog(wrapper, 'Funnels'))();
      await flushPromises();

      expect(showNotification).toHaveBeenCalledWith(expect.objectContaining({
        message: 'Marketplace_RequestDeclinedOne',
      }));
    });

    it('shows the decision on the first History page', async () => {
      ajaxPost.mockResolvedValue({ value: 2 });
      ajaxFetch.mockResolvedValue({
        total: 1,
        decisions: [makeDecision({ pluginName: 'Funnels', displayName: 'Funnels' })],
      });
      const wrapper = mountPage(undefined, makeHistory(0));

      (await openDeclineDialog(wrapper, 'Funnels'))();
      await flushPromises();
      await showHistory(wrapper);

      expect(ajaxFetch).toHaveBeenCalledWith({
        module: 'Marketplace',
        action: 'getPluginRequestHistoryPage',
        offset: 0,
      });
      expect(wrapper.find('.manageRequestsSuperUser__decision').attributes('data-plugin'))
        .toBe('Funnels');
    });

    it('keeps the row when the decline fails', async () => {
      ajaxPost.mockRejectedValue(new Error('Another Super User has already responded'));
      const wrapper = mountPage();

      (await openDeclineDialog(wrapper, 'Funnels'))();
      await flushPromises();

      expect(row(wrapper, 'Funnels').exists()).toBe(true);
      expect(showNotification).not.toHaveBeenCalled();
    });
  });

  describe('Start 30-day trial', () => {
    it('starts the trial through the approval of the requests', async () => {
      const wrapper = mountPage();

      await row(wrapper, 'Funnels').find('.manageRequestsSuperUser__startTrial').trigger('click');

      const startFreeTrial = wrapper.findComponent(StartFreeTrialStub);
      expect(startFreeTrial.props()).toMatchObject({
        modelValue: { name: 'Funnels', displayName: 'Funnels' },
        apiMethod: 'Marketplace.approvePluginTrial',
        isValidConsumer: true,
        currentUserEmail: 'admin@example.test',
      });
    });

    it('removes the row once the trial started', async () => {
      const wrapper = mountPage([makePending(), makePending({ pluginName: 'Cohorts' })]);

      await row(wrapper, 'Funnels').find('.manageRequestsSuperUser__startTrial').trigger('click');
      const startFreeTrial = wrapper.findComponent(StartFreeTrialStub);
      startFreeTrial.vm.$emit('update:modelValue', null);
      startFreeTrial.vm.$emit('trialStarted');
      await flushPromises();

      expect(row(wrapper, 'Funnels').exists()).toBe(false);
      expect(row(wrapper, 'Cohorts').exists()).toBe(true);
      expect(ajaxFetch).toHaveBeenCalledWith(expect.objectContaining({ offset: 0 }));
    });

    it('keeps the row when the trial does not start', async () => {
      const wrapper = mountPage();

      await row(wrapper, 'Funnels').find('.manageRequestsSuperUser__startTrial').trigger('click');
      wrapper.findComponent(StartFreeTrialStub).vm.$emit('update:modelValue', null);
      await flushPromises();

      expect(row(wrapper, 'Funnels').exists()).toBe(true);
    });
  });

  describe('History', () => {
    it('names the columns', async () => {
      const wrapper = mountPage();
      await showHistory(wrapper);

      expect(headers(wrapper)).toEqual([
        'General_Plugin',
        'Marketplace_Requests',
        'Marketplace_Decision',
        'Marketplace_DecidedBy',
        'General_Note',
        'General_Date',
      ]);
    });

    it('lists a decision with who made it and the note', async () => {
      const wrapper = mountPage(undefined, { total: 1, decisions: [makeDecision()] });
      await showHistory(wrapper);

      expect(wrapper.find('.manageRequestsSuperUser__decision').findAll('td').map((cell) => cell.text()))
        .toEqual([
          'Custom Reports',
          '2',
          'Marketplace_RequestStatusDeclined',
          'admin',
          'We use another tool',
          'Monday, October 26, 2026',
        ]);
    });

    it('shows a dash for an automatic decision without a note', async () => {
      const wrapper = mountPage(undefined, {
        total: 1,
        decisions: [makeDecision({ decision: 'approved', decidedBy: null, note: null })],
      });
      await showHistory(wrapper);

      const decisionCells = wrapper.find('.manageRequestsSuperUser__decision').findAll('td')
        .map((cell) => cell.text());
      expect(decisionCells.slice(2, 5)).toEqual(['Marketplace_RequestStatusApproved', '-', '-']);
    });

    it('says when no decision was made yet', async () => {
      const wrapper = mountPage(undefined, makeHistory(0));
      await showHistory(wrapper);

      expect(wrapper.find('.manageRequestsSuperUser__history table').exists()).toBe(false);
      expect(wrapper.find('.manageRequestsSuperUser__empty').text())
        .toBe('Marketplace_NoPluginRequestDecisions');
    });

    it('shows five decisions on one page without a link to another', async () => {
      const wrapper = mountPage(undefined, makeHistory(5));
      await showHistory(wrapper);

      expect(wrapper.findAll('.manageRequestsSuperUser__decision')).toHaveLength(5);
      expect(wrapper.find('.manageRequestsSuperUser__paginationInfo').text())
        .toBe('General_Pagination:1,5,5');
      expect(wrapper.find('.manageRequestsSuperUser__next').exists()).toBe(false);
      expect(wrapper.find('.manageRequestsSuperUser__previous').exists()).toBe(false);
    });

    it('pages through the decisions five at a time', async () => {
      ajaxFetch.mockResolvedValue(makeHistory(6, 5));
      const wrapper = mountPage(undefined, makeHistory(6));
      await showHistory(wrapper);

      expect(wrapper.find('.manageRequestsSuperUser__paginationInfo').text())
        .toBe('General_Pagination:1,5,6');
      expect(wrapper.find('.manageRequestsSuperUser__next').text()).toBe('General_Next ›');

      await wrapper.find('.manageRequestsSuperUser__next').trigger('click');
      await flushPromises();

      expect(ajaxFetch).toHaveBeenCalledWith({
        module: 'Marketplace',
        action: 'getPluginRequestHistoryPage',
        offset: 5,
      });
      expect(wrapper.findAll('.manageRequestsSuperUser__decision')).toHaveLength(1);
      expect(wrapper.find('.manageRequestsSuperUser__paginationInfo').text())
        .toBe('General_Pagination:6,6,6');
      expect(wrapper.find('.manageRequestsSuperUser__next').exists()).toBe(false);
      expect(wrapper.find('.manageRequestsSuperUser__previous').text()).toBe('‹ General_Previous');
    });
  });
});
