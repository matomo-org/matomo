/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports.
vi.mock('CoreHome', async () => ({
  ...(await import('../testCoreHomeMock')).coreHomeMock(),
  ContentBlock: { name: 'ContentBlock', template: '<div><slot/></div>' },
  ContentTable: {},
}));

/* eslint-disable import/first */
import ManageRequests, { PluginRequestOfUser } from './ManageRequests.vue';
import { translateStub } from '../testCoreHomeMock';

function makeRequest(overrides: Partial<PluginRequestOfUser> = {}): PluginRequestOfUser {
  return {
    pluginName: 'MediaAnalytics',
    displayName: 'Media Analytics',
    requestType: 'trial',
    requestedAt: '2026-11-05 10:00:00',
    requestedAtPretty: 'Thursday, November 5, 2026',
    message: null,
    status: 'pending',
    decisionNote: null,
    ...overrides,
  };
}

function mountPage(requests: PluginRequestOfUser[]) {
  return mount(ManageRequests, {
    props: { requests },
    global: { mocks: { translate: translateStub } },
  });
}

const cells = (wrapper: ReturnType<typeof mountPage>, row = 0) => wrapper
  .findAll('tbody tr')[row].findAll('td').map((cell) => cell.text());

describe('Marketplace/ManageRequests', () => {
  it('explains the page and names the columns', () => {
    const wrapper = mountPage([makeRequest()]);

    expect(wrapper.find('.manageRequests__intro').text()).toBe('Marketplace_ManageRequestsIntro');
    expect(wrapper.findAll('th').map((th) => th.text())).toEqual([
      'General_Plugin',
      'Marketplace_RequestedOn',
      'Marketplace_YourNote',
      'CorePluginsAdmin_Status',
      'Marketplace_SuperUserNote',
    ]);
  });

  it('lists a request with its date, note and status', () => {
    const wrapper = mountPage([makeRequest({ message: 'For our video pages' })]);

    expect(cells(wrapper)).toEqual([
      'Media Analytics',
      'Thursday, November 5, 2026',
      'For our video pages',
      'Marketplace_RequestStatusPending',
      '-',
    ]);
  });

  it('shows the super user\'s note of a declined request', () => {
    const wrapper = mountPage([
      makeRequest({ status: 'declined', decisionNote: 'Maybe next year' }),
    ]);

    expect(cells(wrapper)[3]).toBe('Marketplace_RequestStatusDeclined');
    expect(cells(wrapper)[4]).toBe('Maybe next year');
  });

  it('shows a dash for a request without a note', () => {
    expect(cells(mountPage([makeRequest({ status: 'approved' })]))[2]).toBe('-');
  });

  it('keeps the full text of a note for its tooltip, as long notes are cut after two lines', () => {
    const note = 'A long note '.repeat(30).trim();
    const wrapper = mountPage([makeRequest({ message: note })]);

    expect(wrapper.find('.manageRequests__note').attributes('title')).toBe(note);
  });

  it('invites a user without requests to browse the Marketplace', () => {
    const wrapper = mountPage([]);

    expect(wrapper.find('table').exists()).toBe(false);
    expect(wrapper.find('.manageRequests__emptyTitle').text())
      .toBe('Marketplace_NoPluginRequestsYet');
    expect(wrapper.find('.manageRequests__empty p').text())
      .toBe('Marketplace_NoPluginRequestsYetHelp');
    expect(wrapper.find('.manageRequests__empty a').text()).toBe('Marketplace_BrowseMarketplace');
    expect(wrapper.find('.manageRequests__empty a').attributes('href'))
      .toBe('?module=Marketplace&action=overview');
  });
});
