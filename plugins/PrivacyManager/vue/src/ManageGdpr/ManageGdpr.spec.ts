/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount, flushPromises } from '@vue/test-utils';

const mocks = vi.hoisted(() => ({
  fetch: vi.fn(),
  post: vi.fn(),
  showNotification: vi.fn(),
  sendContentAsDownload: vi.fn(),
  modalConfirm: vi.fn(),
}));

vi.mock('CoreHome', () => ({
  translate: (key: string) => key,
  Matomo: {
    visitorProfileEnabled: true,
    hasSuperUserAccess: true,
    helper: {
      sendContentAsDownload: mocks.sendContentAsDownload,
      modalConfirm: mocks.modalConfirm,
    },
  },
  AjaxHelper: { fetch: mocks.fetch, post: mocks.post },
  ContentBlock: { template: '<div><slot></slot></div>' },
  SiteSelector: { template: '<div></div>' },
  ContentTable: {},
  NotificationsStore: { show: mocks.showNotification, scrollToNotification: vi.fn() },
  MatomoUrl: { stringify: () => '', urlParsed: { value: {} } },
}));

vi.mock('SegmentEditor', () => ({
  SegmentGenerator: { template: '<div></div>' },
}));

vi.mock('CorePluginsAdmin', () => ({
  SaveButton: {
    props: ['disabled', 'value'],
    emits: ['confirm'],
    template: '<input type="button" :disabled="disabled" @click="$emit(\'confirm\')"/>',
  },
  Field: { template: '<div></div>' },
}));

import ManageGdpr from './ManageGdpr.vue';

const MAX_VISITS = 1000;

function makeVisits(count: number) {
  return Array.from({ length: count }, (_, i) => ({
    idSite: 1,
    idVisit: `${i + 1}`,
    visitorId: `v${i + 1}`,
    lastActionDateTime: '2026-11-26 10:00:00',
  }));
}

function postedVisitCounts() {
  return mocks.post.mock.calls.map(([, body]) => body.visits.length);
}

function searchCount() {
  return mocks.fetch.mock.calls.filter(([params]) => params.method === 'PrivacyManager.findDataSubjects').length;
}

// the export of one batch: a row per visit, and the actions those visits share
function exportOf(visits: { idvisit: string|number }[]) {
  return {
    log_visit: visits.map((v) => ({ idvisit: v.idvisit })),
    log_action_log_visit_visit_entry_idaction_url: [{ idaction: 2 }, { idaction: 1 }],
  };
}

async function mountWithSearchResult(visitCount: number) {
  mocks.fetch.mockImplementation((params: { method: string }) => {
    switch (params.method) {
      case 'SitesManager.getSitesIdWithAdminAccess':
        return Promise.resolve([1]);
      case 'PrivacyManager.findDataSubjects':
        return Promise.resolve(makeVisits(visitCount));
      default:
        return Promise.resolve({
          available: true,
          consentedVisitsOnly: false,
          consentedVisitsOnlyIdSites: [],
        });
    }
  });

  const wrapper = mount(ManageGdpr, {
    props: { maxVisits: MAX_VISITS },
    global: {
      config: {
        globalProperties: {
          translate: (key: string) => key,
          $sanitize: (html: string) => html,
        } as never,
      },
    },
  });
  await flushPromises();
  await wrapper.find('.findDataSubjects').trigger('click');
  await flushPromises();
  return wrapper;
}

describe('PrivacyManager/ManageGdpr.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mocks.modalConfirm.mockImplementation(
      (element: HTMLElement, { yes }: { yes: () => void }) => yes(),
    );
  });

  it('exports the selected visits in batches and downloads them as one file', async () => {
    let finishLastBatch = () => {};
    mocks.post.mockImplementation((params: unknown, body: { visits: { idvisit: string }[] }) => {
      if (body.visits.length === 100) {
        return new Promise((resolve) => {
          finishLastBatch = () => resolve(exportOf(body.visits));
        });
      }
      return Promise.resolve(exportOf(body.visits));
    });
    const wrapper = await mountWithSearchResult(MAX_VISITS + 1);

    await wrapper.find('.exportDataSubjects').trigger('click');
    await flushPromises();

    expect(postedVisitCounts()).toEqual([450, 450, 100]);
    expect(wrapper.find('.exportDataSubjects').attributes('disabled')).toBeDefined();
    expect(mocks.sendContentAsDownload).not.toHaveBeenCalled();

    finishLastBatch();
    await flushPromises();

    expect(wrapper.find('.exportDataSubjects').attributes('disabled')).toBeUndefined();
    expect(mocks.sendContentAsDownload).toHaveBeenCalledTimes(1);
    const exported = JSON.parse(mocks.sendContentAsDownload.mock.calls[0][1]);
    expect(exported.log_visit).toHaveLength(MAX_VISITS);
    expect(exported.log_action_log_visit_visit_entry_idaction_url)
      .toEqual([{ idaction: 1 }, { idaction: 2 }]);
  });

  it('downloads nothing and enables the export again when a batch fails', async () => {
    mocks.post
      .mockImplementationOnce(
        (params: unknown, body: { visits: { idvisit: string }[] }) => Promise.resolve(
          exportOf(body.visits),
        ),
      )
      .mockImplementationOnce(() => Promise.reject(new Error('batch failed')));
    const wrapper = await mountWithSearchResult(MAX_VISITS);

    await wrapper.find('.exportDataSubjects').trigger('click');
    await flushPromises();

    expect(postedVisitCounts()).toEqual([450, 450]);
    expect(mocks.sendContentAsDownload).not.toHaveBeenCalled();
    expect(wrapper.find('.exportDataSubjects').attributes('disabled')).toBeUndefined();
  });

  it('disables deleting while an export runs', async () => {
    let finishExport = () => {};
    mocks.post.mockImplementation((params: unknown, body: { visits: { idvisit: string }[] }) => (
      new Promise((resolve) => {
        finishExport = () => resolve(exportOf(body.visits));
      })
    ));
    const wrapper = await mountWithSearchResult(1);

    await wrapper.find('.exportDataSubjects').trigger('click');
    await flushPromises();

    expect(wrapper.find('.deleteDataSubjects').attributes('disabled')).toBeDefined();

    finishExport();
    await flushPromises();

    expect(wrapper.find('.deleteDataSubjects').attributes('disabled')).toBeUndefined();
  });

  it('disables exporting while a delete runs', async () => {
    let finishDelete = () => {};
    mocks.post.mockImplementation(() => new Promise((resolve) => {
      finishDelete = () => resolve({});
    }));
    const wrapper = await mountWithSearchResult(1);

    await wrapper.find('.deleteDataSubjects').trigger('click');
    await flushPromises();

    expect(wrapper.find('.exportDataSubjects').attributes('disabled')).toBeDefined();

    finishDelete();
    await flushPromises();

    expect(wrapper.find('.exportDataSubjects').attributes('disabled')).toBeUndefined();
  });

  it('deletes the selected visits in batches and searches again', async () => {
    mocks.post.mockResolvedValue({});
    const wrapper = await mountWithSearchResult(MAX_VISITS);

    await wrapper.find('.deleteDataSubjects').trigger('click');
    await flushPromises();

    expect(postedVisitCounts()).toEqual([450, 450, 100]);
    expect(mocks.post.mock.calls.every(([params]) => params.method === 'PrivacyManager.deleteDataSubjects')).toBe(true);
    expect(mocks.showNotification).toHaveBeenCalledWith(expect.objectContaining({ message: 'PrivacyManager_VisitsSuccessfullyDeleted' }));
    expect(searchCount()).toBe(2);
    expect(wrapper.find('.deleteDataSubjects').attributes('disabled')).toBeUndefined();
  });

  it('stops at a failed delete batch and lists the visits that are left', async () => {
    mocks.post
      .mockResolvedValueOnce({})
      .mockRejectedValueOnce(new Error('batch failed'));
    const wrapper = await mountWithSearchResult(MAX_VISITS);

    await wrapper.find('.deleteDataSubjects').trigger('click');
    await flushPromises();

    expect(postedVisitCounts()).toEqual([450, 450]);
    expect(mocks.showNotification).not.toHaveBeenCalled();
    expect(searchCount()).toBe(2);
    expect(wrapper.findAll('tr[title]')).toHaveLength(MAX_VISITS);
    expect(wrapper.find('.deleteDataSubjects').attributes('disabled')).toBeUndefined();
  });
});
