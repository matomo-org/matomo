/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { flushPromises, mount, VueWrapper } from '@vue/test-utils';

// Hoisted so the mock factory, which vitest lifts above this file's imports, can reach them.
const state = vi.hoisted(() => ({
  fetch: vi.fn(),
  post: vi.fn(),
  matomo: {
    idSite: 1 as string|number,
    hasSuperUserAccess: false,
    userCurrentRole: 'admin',
    transitionsHasPreprocessedPages: true,
  },
}));

vi.mock('CoreHome', async () => {
  const { translateStub } = await import('./testCoreHomeMock');
  return {
    AjaxHelper: {
      fetch: (...args: unknown[]) => state.fetch(...args),
      post: (...args: unknown[]) => state.post(...args),
    },
    Matomo: new Proxy({}, {
      get(target, name: string) {
        if (name === 'helper') {
          return {
            htmlDecode: (value: string) => {
              const textArea = document.createElement('textarea');
              textArea.innerHTML = value;
              return textArea.value;
            },
          };
        }

        return state.matomo[name as keyof typeof state.matomo];
      },
      set(target, name: string, value) {
        (state.matomo as Record<string, unknown>)[name] = value;
        return true;
      },
    }),
    MatomoUrl: {
      parsed: { value: { period: 'year', date: '2025-01-01' } },
      stringify: (params: Record<string, unknown>) => Object.entries(params)
        .filter(([, value]) => value !== undefined && value !== '')
        .map(([key, value]) => `${key}=${encodeURIComponent(String(value))}`)
        .join('&'),
    },
    NumberFormatter: {
      formatNumber: (value: number) => String(value),
    },
    translate: translateStub,
  };
});

import { translateStub } from './testCoreHomeMock';
import TransitionsSourceHeader, { PreprocessedPageStatus } from './TransitionsSourceHeader.vue';
import { resolveDataSource } from './transitionsReportData';

const PAGE_URL = 'https://example.org/page';

const RAW = { loaded: true, preprocessed: false, preprocessedAt: '' };
const STORED = { loaded: true, preprocessed: true, preprocessedAt: '2025-03-10 04:05:06' };

function status(overrides: Partial<PreprocessedPageStatus> = {}): PreprocessedPageStatus {
  return {
    selected: false,
    idPage: null,
    used: 12,
    limit: 20,
    rearchiveMonths: 6,
    ...overrides,
  };
}

async function mountHeader(
  props: Record<string, unknown> = {},
  attachTo: HTMLElement|undefined = undefined,
): Promise<VueWrapper> {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wrapper = mount(TransitionsSourceHeader as any, {
    attachTo,
    props: {
      source: RAW,
      actionType: 'url',
      actionName: PAGE_URL,
      idSite: 1,
      ...props,
    },
    global: {
      config: {
        globalProperties: {
          translate: translateStub,
        },
      },
    },
  });
  await flushPromises();
  return wrapper;
}

function statusRequests() {
  return state.fetch.mock.calls.filter(
    ([params]) => params.method === 'Transitions.getPreprocessedPageStatus',
  );
}

describe('Transitions/TransitionsSourceHeader', () => {
  beforeEach(() => {
    state.fetch.mockReset();
    state.post.mockReset();
    state.matomo.idSite = 1;
    state.matomo.hasSuperUserAccess = false;
    state.matomo.userCurrentRole = 'admin';
    state.matomo.transitionsHasPreprocessedPages = true;
    state.fetch.mockResolvedValue(status());
  });

  describe('data source of the parts', () => {
    it('should be unknown until a part is served', () => {
      expect(resolveDataSource({})).toEqual({ loaded: false, preprocessed: false, preprocessedAt: '' });
    });

    it('should be pre-processed only while every part served so far was', () => {
      const stored = { preprocessed: true, preprocessedAt: '2025-03-10 10:00:00' };

      expect(resolveDataSource({ internalReferrers: stored }).preprocessed).toBe(true);
      expect(resolveDataSource({
        internalReferrers: stored,
        followingActions: { preprocessed: false, preprocessedAt: '' },
      })).toEqual({ loaded: true, preprocessed: false, preprocessedAt: '' });
    });

    it('should date the stored parts by the oldest of them', () => {
      expect(resolveDataSource({
        internalReferrers: { preprocessed: true, preprocessedAt: '2025-03-10 10:00:00' },
        followingActions: { preprocessed: true, preprocessedAt: '2025-03-09 23:59:59' },
        externalReferrers: { preprocessed: true, preprocessedAt: '2025-03-11 00:00:00' },
      }).preprocessedAt).toBe('2025-03-09 23:59:59');
    });
  });

  describe('badge', () => {
    it('should not show a badge before a part is served', async () => {
      const wrapper = await mountHeader({ source: { loaded: false, preprocessed: false, preprocessedAt: '' } });
      expect(wrapper.find('.transitionsSourceHeader__badge').exists()).toBe(false);
    });

    it('should show the pre-processed badge with the oldest update time', async () => {
      const wrapper = await mountHeader({ source: STORED });
      const badge = wrapper.find('.transitionsSourceHeader__badge');

      expect(badge.text()).toBe('Transitions_PreprocessedBadge');
      expect(badge.classes()).toContain('transitionsSourceHeader__badge--preprocessed');
      expect(badge.attributes('title')).toBe('Transitions_PreprocessedBadgeTooltip:Transitions_DateTimeUtc:2025-03-10 04:05');

      // reachable by keyboard and described to screen readers
      expect(badge.attributes('tabindex')).toBe('0');
      const description = wrapper.find(`#${badge.attributes('aria-describedby')}`);
      expect(description.text()).toBe('Transitions_PreprocessedBadgeTooltip:Transitions_DateTimeUtc:2025-03-10 04:05');
    });

    it('should show raw data without a tooltip', async () => {
      const wrapper = await mountHeader({ source: RAW });
      const badge = wrapper.find('.transitionsSourceHeader__badge');

      expect(badge.text()).toBe('Transitions_RawDataBadge');
      expect(badge.attributes('title')).toBeUndefined();
      expect(badge.attributes('tabindex')).toBeUndefined();
    });
  });

  describe('websites without pre-processed pages', () => {
    beforeEach(() => {
      state.matomo.transitionsHasPreprocessedPages = false;
    });

    it('should show viewers no badge row for raw data', async () => {
      state.matomo.userCurrentRole = 'view';
      const wrapper = await mountHeader({ source: RAW });

      expect(wrapper.find('.transitionsSourceHeader').exists()).toBe(false);
    });

    it('should still point out stored data, eg of a page removed since', async () => {
      state.matomo.userCurrentRole = 'view';
      const wrapper = await mountHeader({ source: STORED });

      expect(wrapper.find('.transitionsSourceHeader__badge').text()).toBe('Transitions_PreprocessedBadge');
    });

    it('should point out raw data once the first page was added, also after it is removed again', async () => {
      state.fetch.mockResolvedValue(status({ used: 0 }));
      state.post.mockResolvedValue({ idpage: 7 });
      const wrapper = await mountHeader({ source: RAW });
      expect(wrapper.find('.transitionsSourceHeader__badge').exists()).toBe(false);

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();

      expect(wrapper.find('.transitionsSourceHeader__badge').text()).toBe('Transitions_RawDataBadge');
      expect(state.matomo.transitionsHasPreprocessedPages).toBe(true);

      // the data stored for a removed page is still served, also beyond the period limit
      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await wrapper.find('.transitionsSourceHeader__stop').trigger('click');
      await flushPromises();

      expect(wrapper.find('.transitionsSourceHeader__badge').text()).toBe('Transitions_RawDataBadge');
      expect(state.matomo.transitionsHasPreprocessedPages).toBe(true);
    });

    it('should not take back the flag of the page globals when no page is selected', async () => {
      state.matomo.transitionsHasPreprocessedPages = true;
      state.fetch.mockResolvedValue(status({ used: 0 }));
      const wrapper = await mountHeader({ source: RAW });

      expect(wrapper.find('.transitionsSourceHeader__badge').text()).toBe('Transitions_RawDataBadge');
      expect(state.matomo.transitionsHasPreprocessedPages).toBe(true);
    });

    it('should give admins the toggle without a raw data badge', async () => {
      state.fetch.mockResolvedValue(status({ used: 0 }));
      const wrapper = await mountHeader({ source: RAW });

      expect(wrapper.find('.transitionsSourceHeader__badge').exists()).toBe(false);
      expect(wrapper.find('.transitionsSourceHeader__toggle').text())
        .toContain('Transitions_PreprocessThisPage');
    });
  });

  it('should not point out raw data of another website than the one in the URL', async () => {
    state.matomo.userCurrentRole = 'view';
    const wrapper = await mountHeader({ source: RAW, idSite: 2 });

    expect(wrapper.find('.transitionsSourceHeader').exists()).toBe(false);
  });

  describe('access', () => {
    it.each(['view', 'write'])('should give a %s user the badge only, without asking for the status', async (role) => {
      state.matomo.userCurrentRole = role;
      const wrapper = await mountHeader({ source: STORED });

      expect(wrapper.find('.transitionsSourceHeader__badge').exists()).toBe(true);
      expect(wrapper.find('.transitionsSourceHeader__toggle').exists()).toBe(false);
      expect(state.fetch).not.toHaveBeenCalled();
    });

    it('should not offer the toggle for page titles', async () => {
      const wrapper = await mountHeader({ actionType: 'title', actionName: 'A title' });

      expect(wrapper.find('.transitionsSourceHeader__toggle').exists()).toBe(false);
      expect(state.fetch).not.toHaveBeenCalled();
    });

    it('should not offer the toggle to a site admin for another website', async () => {
      const wrapper = await mountHeader({ idSite: 2 });

      expect(wrapper.find('.transitionsSourceHeader__toggle').exists()).toBe(false);
      expect(state.fetch).not.toHaveBeenCalled();
    });

    it('should offer the toggle to a super user for any website', async () => {
      state.matomo.userCurrentRole = 'view';
      state.matomo.hasSuperUserAccess = true;
      const wrapper = await mountHeader({ idSite: 2 });

      expect(wrapper.find('.transitionsSourceHeader__toggle').exists()).toBe(true);
      expect(statusRequests()[0][0]).toMatchObject({ idSite: 2, pageUrl: PAGE_URL });
    });

    it('should leave out the toggle when the status request fails', async () => {
      state.fetch.mockRejectedValue(new Error('no access'));
      const wrapper = await mountHeader();

      expect(wrapper.find('.transitionsSourceHeader__toggle').exists()).toBe(false);
      expect(wrapper.find('.transitionsSourceHeader__error').exists()).toBe(false);
    });
  });

  describe('admin toggle', () => {
    it('should offer to pre-process a page that is not selected, with the usage', async () => {
      const wrapper = await mountHeader();

      expect(statusRequests()).toHaveLength(1);
      expect(statusRequests()[0][0]).toMatchObject({ idSite: 1, pageUrl: PAGE_URL });
      expect(wrapper.find('.transitionsSourceHeader__toggle').text()).toContain('Transitions_PreprocessThisPage');
      expect(wrapper.find('.transitionsSourceHeader__toggle').attributes('disabled')).toBeUndefined();
      expect(wrapper.find('.transitionsSourceHeader__usage').text())
        .toBe('Transitions_PreprocessedPagesUsageShort:12|20');
      expect(wrapper.find('.transitionsSourceHeader__manageLink').exists()).toBe(false);
    });

    it('should disable adding at the limit and link to the admin page', async () => {
      state.fetch.mockResolvedValue(status({ used: 20 }));
      const wrapper = await mountHeader();

      const toggle = wrapper.find('.transitionsSourceHeader__toggle');
      expect(toggle.attributes('disabled')).toBeDefined();
      expect(wrapper.find('.transitionsSourceHeader__toggleWrap').attributes('title'))
        .toBe('Transitions_PreprocessedPagesLimitReachedHelp:20');
      expect(wrapper.find('.transitionsSourceHeader__usage').exists()).toBe(false);

      const link = wrapper.find('.transitionsSourceHeader__manageLink');
      expect(link.text()).toBe('Transitions_ManagePreprocessedPages');
      expect(link.attributes('href')).toBe('?module=Transitions&action=manage&idSite=1&period=year&date=2025-01-01');
    });

    it.each([
      [{ used: 0, limit: 0 }, 'Transitions_PreprocessedPagesAddingDisabled'],
      [{ used: 3, limit: 0 }, 'Transitions_PreprocessedPagesAddingDisabled'],
      [{ used: 25, limit: 20 }, 'Transitions_PreprocessedPagesOverLimit:25|20|6'],
    ])('should explain why adding is disabled (%o)', async (counts, help) => {
      state.fetch.mockResolvedValue(status(counts));
      const wrapper = await mountHeader();

      expect(wrapper.find('.transitionsSourceHeader__toggle').attributes('disabled')).toBeDefined();
      expect(wrapper.find('.transitionsSourceHeader__toggleWrap').attributes('title')).toBe(help);
      const describedBy = wrapper.find('.transitionsSourceHeader__toggle').attributes('aria-describedby');
      expect(wrapper.find(`#${describedBy}`).text()).toBe(help);
    });

    it('should add the page and say how far back it is prepared', async () => {
      state.post.mockResolvedValue({ idpage: 7 });
      const wrapper = await mountHeader();

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();

      expect(state.post).toHaveBeenCalledWith(
        { method: 'Transitions.addPreprocessedPage', idSite: 1 },
        { pageUrl: PAGE_URL },
        { createErrorNotification: false },
      );
      expect(wrapper.find('.transitionsSourceHeader__toggle').text()).toContain('Transitions_StopPreprocessing');
      expect(wrapper.find('.transitionsSourceHeader__message').text())
        .toBe('Transitions_PreprocessedPageAddedRebuild:6');
      // the report on screen was still served from raw data
      expect(wrapper.find('.transitionsSourceHeader__badge').text()).toBe('Transitions_RawDataBadge');
    });

    it('should say when only the past month is prepared', async () => {
      state.fetch.mockResolvedValue(status({ rearchiveMonths: 1 }));
      state.post.mockResolvedValue({ idpage: 7 });
      const wrapper = await mountHeader();

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();

      expect(wrapper.find('.transitionsSourceHeader__message').text())
        .toBe('Transitions_PreprocessedPageAddedRebuildOneMonth');
    });

    it('should say only new data is prepared when the rebuild window is 0', async () => {
      state.fetch.mockResolvedValue(status({ rearchiveMonths: 0 }));
      state.post.mockResolvedValue({ idpage: 7 });
      const wrapper = await mountHeader();

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();

      expect(wrapper.find('.transitionsSourceHeader__message').text())
        .toBe('Transitions_PreprocessedPageAddedNewDataOnly');
    });

    it('should show an API error as text', async () => {
      state.post.mockRejectedValue(new Error('This page is &lt;b&gt;already&lt;/b&gt; selected.'));
      const wrapper = await mountHeader();

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();

      const error = wrapper.find('.transitionsSourceHeader__error');
      expect(error.text()).toBe('This page is <b>already</b> selected.');
      expect(error.find('b').exists()).toBe(false);
      expect(wrapper.find('.transitionsSourceHeader__toggle').text()).toContain('Transitions_PreprocessThisPage');
    });

    it('should read the status again when adding fails, eg as another admin reached the limit', async () => {
      state.post.mockRejectedValue(new Error('You can select at most 20 pages for pre-processed Transitions.'));
      const wrapper = await mountHeader();

      state.fetch.mockResolvedValue(status({ used: 20 }));
      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();

      expect(statusRequests()).toHaveLength(2);
      expect(wrapper.find('.transitionsSourceHeader__error').text())
        .toBe('You can select at most 20 pages for pre-processed Transitions.');
      expect(wrapper.find('.transitionsSourceHeader__toggle').attributes('disabled')).toBeDefined();
      expect(wrapper.find('.transitionsSourceHeader__manageLink').exists()).toBe(true);
    });

    it('should read the status again when adding fails, eg as another admin added the page', async () => {
      state.post.mockRejectedValue(new Error('This page is already selected.'));
      const wrapper = await mountHeader();

      state.fetch.mockResolvedValue(status({ selected: true, idPage: 4, used: 13 }));
      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();

      expect(wrapper.find('.transitionsSourceHeader__error').text()).toBe('This page is already selected.');
      const toggle = wrapper.find('.transitionsSourceHeader__toggle');
      expect(toggle.text()).toContain('Transitions_StopPreprocessing');
      expect(toggle.attributes('disabled')).toBeUndefined();
    });

    it('should read the status again when stopping fails, eg as another admin removed the page', async () => {
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      state.post.mockRejectedValue(new Error('This page is not selected for pre-processed Transitions.'));
      const wrapper = await mountHeader();

      state.fetch.mockResolvedValue(status({ used: 11 }));
      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await wrapper.find('.transitionsSourceHeader__stop').trigger('click');
      await flushPromises();

      expect(statusRequests()).toHaveLength(2);
      expect(wrapper.find('.transitionsSourceHeader__error').text())
        .toBe('This page is not selected for pre-processed Transitions.');
      expect(wrapper.find('.transitionsSourceHeader__confirm').exists()).toBe(false);
      expect(wrapper.find('.transitionsSourceHeader__toggle').text()).toContain('Transitions_PreprocessThisPage');
      expect(wrapper.find('.transitionsSourceHeader__usage').text())
        .toBe('Transitions_PreprocessedPagesUsageShort:11|20');
    });

    it('should show the URL of the confirmation decoded, as the page selector sends it encoded', async () => {
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      const wrapper = await mountHeader({ actionName: 'https://www.example.org/a?b=1&amp;c=&lt;d&gt;' });

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');

      expect(wrapper.find('.transitionsSourceHeader__confirm strong').text())
        .toBe('Transitions_StopPreprocessingConfirm:example.org/a?b=1&c=<d>');
    });

    it('should move the focus back to the toggle when the confirmation closes', async () => {
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      state.post.mockResolvedValue({});
      const wrapper = await mountHeader({}, document.body);

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();
      expect(document.activeElement?.className).toContain('transitionsSourceHeader__cancel');

      await wrapper.find('.transitionsSourceHeader__cancel').trigger('click');
      await flushPromises();
      expect(document.activeElement?.className).toContain('transitionsSourceHeader__toggle');

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await wrapper.find('.transitionsSourceHeader__stop').trigger('click');
      await flushPromises();
      expect(document.activeElement?.textContent).toContain('Transitions_PreprocessThisPage');

      wrapper.unmount();
    });

    it('should ask for confirmation before it stops pre-processing', async () => {
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      state.post.mockResolvedValue({});
      const wrapper = await mountHeader({ source: STORED });

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      expect(state.post).not.toHaveBeenCalled();

      const confirm = wrapper.find('.transitionsSourceHeader__confirm');
      expect(confirm.text()).toContain('Transitions_StopPreprocessingConfirm:example.org/page');
      expect(confirm.text()).toContain('Transitions_StopPreprocessingHelp');

      await wrapper.find('.transitionsSourceHeader__cancel').trigger('click');
      expect(wrapper.find('.transitionsSourceHeader__confirm').exists()).toBe(false);
      expect(state.post).not.toHaveBeenCalled();

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await wrapper.find('.transitionsSourceHeader__stop').trigger('click');
      await flushPromises();

      expect(state.post).toHaveBeenCalledWith(
        { method: 'Transitions.removePreprocessedPage', idSite: 1 },
        { idPage: 3 },
        { createErrorNotification: false },
      );
      expect(wrapper.find('.transitionsSourceHeader__confirm').exists()).toBe(false);
      expect(wrapper.find('.transitionsSourceHeader__toggle').text()).toContain('Transitions_PreprocessThisPage');
      expect(wrapper.find('.transitionsSourceHeader__usage').text())
        .toBe('Transitions_PreprocessedPagesUsageShort:11|20');
      // data already stored is still what this report shows
      expect(wrapper.find('.transitionsSourceHeader__badge').text()).toBe('Transitions_PreprocessedBadge');
    });

    it('should render a URL with markup as text in the confirmation', async () => {
      const url = 'https://example.org/<img src=x onerror=alert(1)>';
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      const wrapper = await mountHeader({ actionName: url });

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');

      const confirm = wrapper.find('.transitionsSourceHeader__confirm');
      expect(confirm.text()).toContain('example.org/<img src=x onerror=alert(1)>');
      expect(confirm.find('img').exists()).toBe(false);
    });

    it('should send a page URL without a scheme with http://, which matches https too', async () => {
      state.fetch.mockResolvedValue(status({ selected: false }));
      state.post.mockResolvedValue({ idpage: 7 });
      const wrapper = await mountHeader({ actionName: 'example.org/deep/page' });

      expect(statusRequests()[0][0]).toMatchObject({ pageUrl: 'http://example.org/deep/page' });

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();

      expect(state.post.mock.calls[0][1]).toEqual({ pageUrl: 'http://example.org/deep/page' });
    });

    it('should drop an add that finishes after another page was opened', async () => {
      let finishAdd: (entry: { idpage: number }) => void = () => undefined;
      state.post.mockReturnValue(new Promise((resolve) => {
        finishAdd = resolve;
      }));
      const wrapper = await mountHeader();

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');

      state.fetch.mockResolvedValue(status({ selected: true, idPage: 9, used: 13 }));
      await wrapper.setProps({ actionName: 'https://example.org/other' });
      await flushPromises();

      finishAdd({ idpage: 7 });
      await flushPromises();

      expect(wrapper.find('.transitionsSourceHeader__message').exists()).toBe(false);
      expect(wrapper.find('.transitionsSourceHeader__toggle').attributes('disabled')).toBeUndefined();

      // stopping now removes the other page's entry, not the one just added
      state.post.mockReset();
      state.post.mockResolvedValue({});
      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await wrapper.find('.transitionsSourceHeader__stop').trigger('click');
      await flushPromises();

      expect(state.post.mock.calls[0][1]).toEqual({ idPage: 9 });
    });

    it('should drop an add that finishes after leaving the page and coming back', async () => {
      let finishAdd: (entry: { idpage: number }) => void = () => undefined;
      state.post.mockReturnValue(new Promise((resolve) => {
        finishAdd = resolve;
      }));
      const wrapper = await mountHeader();

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await wrapper.setProps({ actionName: 'https://example.org/other' });
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 7, used: 13 }));
      await wrapper.setProps({ actionName: PAGE_URL });
      await flushPromises();

      finishAdd({ idpage: 7 });
      await flushPromises();

      // the status read after coming back already counts the page
      expect(wrapper.find('.transitionsSourceHeader__message').exists()).toBe(false);
      expect(wrapper.find('.transitionsSourceHeader__toggle').text()).toContain('Transitions_StopPreprocessing');
      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      expect(wrapper.find('.transitionsSourceHeader__confirm').exists()).toBe(true);
      await wrapper.find('.transitionsSourceHeader__cancel').trigger('click');
      expect(wrapper.find('.transitionsSourceHeader__usage').exists()).toBe(false);
    });

    it('should drop a failed add once another page was opened', async () => {
      let failAdd: (error: Error) => void = () => undefined;
      state.post.mockReturnValue(new Promise((resolve, reject) => {
        failAdd = reject;
      }));
      const wrapper = await mountHeader();

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await wrapper.setProps({ actionName: 'https://example.org/other' });
      await flushPromises();

      failAdd(new Error('This page is already selected.'));
      await flushPromises();

      expect(wrapper.find('.transitionsSourceHeader__error').exists()).toBe(false);
    });

    it('should drop a stop that finishes after another page was opened', async () => {
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      let finishStop: (value: unknown) => void = () => undefined;
      state.post.mockReturnValue(new Promise((resolve) => {
        finishStop = resolve;
      }));
      const wrapper = await mountHeader();

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await wrapper.find('.transitionsSourceHeader__stop').trigger('click');

      state.fetch.mockResolvedValue(status({ selected: true, idPage: 9, used: 12 }));
      await wrapper.setProps({ actionName: 'https://example.org/other' });
      await flushPromises();

      finishStop({});
      await flushPromises();

      const toggle = wrapper.find('.transitionsSourceHeader__toggle');
      expect(toggle.text()).toContain('Transitions_StopPreprocessing');
      expect(toggle.attributes('disabled')).toBeUndefined();
      expect(wrapper.find('.transitionsSourceHeader__confirm').exists()).toBe(false);
    });

    it('should ask again for the status of another page', async () => {
      const wrapper = await mountHeader();
      await wrapper.setProps({ actionName: 'https://example.org/other' });
      await flushPromises();

      expect(statusRequests()).toHaveLength(2);
      expect(statusRequests()[1][0]).toMatchObject({ pageUrl: 'https://example.org/other' });
    });
  });
});
