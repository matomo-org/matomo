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

async function mountHeader(props: Record<string, unknown> = {}): Promise<VueWrapper> {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wrapper = mount(TransitionsSourceHeader as any, {
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
      expect(badge.attributes('title')).toBe('Transitions_PreprocessedBadgeTooltip:2025-03-10 04:05:06');
    });

    it('should show raw data without a tooltip', async () => {
      const wrapper = await mountHeader({ source: RAW });
      const badge = wrapper.find('.transitionsSourceHeader__badge');

      expect(badge.text()).toBe('Transitions_RawDataBadge');
      expect(badge.attributes('title')).toBeUndefined();
    });
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

    it('should ask again for the status of another page', async () => {
      const wrapper = await mountHeader();
      await wrapper.setProps({ actionName: 'https://example.org/other' });
      await flushPromises();

      expect(statusRequests()).toHaveLength(2);
      expect(statusRequests()[1][0]).toMatchObject({ pageUrl: 'https://example.org/other' });
    });
  });
});
