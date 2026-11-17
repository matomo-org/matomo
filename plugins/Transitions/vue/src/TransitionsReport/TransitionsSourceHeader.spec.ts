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
    MatomoDialog: {
      name: 'MatomoDialog',
      props: ['modelValue'],
      emits: ['yes', 'closeEnd', 'update:modelValue'],
      template: '<div class="matomoDialog"><slot /></div>',
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

/** Matomo's confirmation dialog: whether it is open, and its content. */
function dialogOf(wrapper: VueWrapper) {
  return wrapper.findComponent({ name: 'MatomoDialog' });
}

/** Answers Matomo's confirmation dialog, then lets it close. */
async function answerDialog(wrapper: VueWrapper, yes: boolean) {
  const dialog = dialogOf(wrapper);
  expect(dialog.props('modelValue')).toBe(true);

  if (yes) {
    dialog.vm.$emit('yes');
  }

  dialog.vm.$emit('update:modelValue', false);
  dialog.vm.$emit('closeEnd');
  await flushPromises();
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
      await answerDialog(wrapper, true);

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
        .toBe('Transitions_PreprocessedPagesUsage:12|20');
      // what pre-processing does, as a tooltip and for screen readers
      expect(wrapper.find('.transitionsSourceHeader__toggleWrap').attributes('title'))
        .toBe('Transitions_PreprocessThisPageHelp');
      const helpId = wrapper.find('.transitionsSourceHeader__toggle').attributes('aria-describedby');
      expect(wrapper.find(`#${helpId}`).text()).toBe('Transitions_PreprocessThisPageHelp');
      expect(wrapper.find('.transitionsSourceHeader__limit').exists()).toBe(false);
    });

    it('should show a short limit label instead of the toggle, explained in its tooltip', async () => {
      state.fetch.mockResolvedValue(status({ used: 20 }));
      const wrapper = await mountHeader();

      expect(wrapper.find('.transitionsSourceHeader__toggle').exists()).toBe(false);
      expect(wrapper.find('.transitionsSourceHeader__usage').exists()).toBe(false);
      expect(wrapper.find('a').exists()).toBe(false);

      const label = wrapper.find('.transitionsSourceHeader__limit');
      expect(label.text()).toBe('Transitions_PreprocessedPagesLimitReachedShort');
      expect(label.element.tagName).toBe('SPAN');
      // the explanation, as a tooltip and for screen readers; reachable by keyboard like the badge
      expect(label.attributes('title')).toBe('Transitions_PreprocessedPagesLimitReachedHelp:20');
      expect(label.attributes('tabindex')).toBe('0');
      expect(wrapper.find(`#${label.attributes('aria-describedby')}`).text())
        .toBe('Transitions_PreprocessedPagesLimitReachedHelp:20');
    });

    it.each([
      [{ used: 0, limit: 0 }, 'Transitions_PreprocessedPagesAddingDisabled'],
      [{ used: 3, limit: 0 }, 'Transitions_PreprocessedPagesAddingDisabled'],
      [{ used: 25, limit: 20 }, 'Transitions_PreprocessedPagesOverLimit:25|20|6'],
    ])('should explain why adding is disabled (%o)', async (counts, help) => {
      state.fetch.mockResolvedValue(status(counts));
      const wrapper = await mountHeader();

      expect(wrapper.find('.transitionsSourceHeader__toggle').exists()).toBe(false);
      const label = wrapper.find('.transitionsSourceHeader__limit');
      expect(label.text()).toBe('Transitions_PreprocessedPagesLimitReachedShort');
      expect(label.attributes('title')).toBe(help);
      expect(wrapper.find(`#${label.attributes('aria-describedby')}`).text()).toBe(help);
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
      state.post.mockRejectedValue(new Error(
        "This website's limit of pre-processed pages (20) is reached. Remove a page to add another one.",
      ));
      const wrapper = await mountHeader();

      state.fetch.mockResolvedValue(status({ used: 20 }));
      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();

      expect(statusRequests()).toHaveLength(2);
      // the short limit label replaces the toggle; the error of the click says why
      expect(wrapper.find('.transitionsSourceHeader__error').text())
        .toBe("This website's limit of pre-processed pages (20) is reached. Remove a page to add another one.");
      expect(wrapper.find('.transitionsSourceHeader__toggle').exists()).toBe(false);
      expect(wrapper.find('.transitionsSourceHeader__limit').text())
        .toBe('Transitions_PreprocessedPagesLimitReachedShort');
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
      await answerDialog(wrapper, true);

      expect(statusRequests()).toHaveLength(2);
      expect(wrapper.find('.transitionsSourceHeader__error').text())
        .toBe('This page is not selected for pre-processed Transitions.');
      expect(wrapper.find('.transitionsSourceHeader__toggle').text()).toContain('Transitions_PreprocessThisPage');
      expect(wrapper.find('.transitionsSourceHeader__usage').text())
        .toBe('Transitions_PreprocessedPagesUsage:11|20');
    });

    it('should show the URL of the confirmation decoded, as the page selector sends it encoded', async () => {
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      const wrapper = await mountHeader({ actionName: 'https://www.example.org/a?b=1&amp;c=&lt;d&gt;' });

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');

      expect(wrapper.find('.transitionsSourceHeader__dialogTitle').text())
        .toBe('Transitions_StopPreprocessingConfirm:example.org/a?b=1&c=<d>');
    });

    it('should move the focus back to the toggle once the dialog has closed', async () => {
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      state.post.mockResolvedValue({});
      const wrapper = await mountHeader({}, document.body);

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await answerDialog(wrapper, false);
      expect(document.activeElement?.className).toContain('transitionsSourceHeader__toggle');

      // stopped while the dialog is still closing: the toggle is focused once it has closed
      (document.activeElement as HTMLElement).blur();
      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      dialogOf(wrapper).vm.$emit('yes');
      await flushPromises();
      expect(document.activeElement).toBe(document.body);

      dialogOf(wrapper).vm.$emit('update:modelValue', false);
      dialogOf(wrapper).vm.$emit('closeEnd');
      await flushPromises();
      expect(document.activeElement?.textContent).toContain('Transitions_PreprocessThisPage');

      wrapper.unmount();
    });

    it.each([
      ['adding', () => state.post.mockResolvedValue({ idpage: 7 }), 'Transitions_StopPreprocessing'],
      ['a failed add', () => state.post.mockRejectedValue(new Error('failed')), 'Transitions_PreprocessThisPage'],
    ])('should keep the focus on the toggle after %s', async (_, answerPost, toggleText) => {
      answerPost();
      const wrapper = await mountHeader({}, document.body);

      (wrapper.find('.transitionsSourceHeader__toggle').element as HTMLElement).focus();
      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await flushPromises();

      expect(document.activeElement?.className).toContain('transitionsSourceHeader__toggle');
      expect(document.activeElement?.textContent).toContain(toggleText);

      wrapper.unmount();
    });

    it('should ask for confirmation before it stops pre-processing', async () => {
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      state.post.mockResolvedValue({});
      const wrapper = await mountHeader({ source: STORED });

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      expect(state.post).not.toHaveBeenCalled();

      // Matomo's confirmation dialog, with its Yes and No
      expect(dialogOf(wrapper).props('modelValue')).toBe(true);
      const dialog = dialogOf(wrapper).find('.ui-confirm').element;
      expect(wrapper.find('.transitionsSourceHeader__dialogTitle').text())
        .toBe('Transitions_StopPreprocessingConfirm:example.org/page');
      expect(dialog.textContent).toContain('Transitions_StopPreprocessingHelp');
      expect(Array.from(dialog.querySelectorAll('input'))
        .map((input) => [input.getAttribute('role'), input.value]))
        .toEqual([['yes', 'General_Yes'], ['no', 'General_No']]);

      await answerDialog(wrapper, false);
      expect(state.post).not.toHaveBeenCalled();

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await answerDialog(wrapper, true);

      expect(state.post).toHaveBeenCalledWith(
        { method: 'Transitions.removePreprocessedPage', idSite: 1 },
        { idPage: 3 },
        { createErrorNotification: false },
      );
      expect(wrapper.find('.transitionsSourceHeader__toggle').text()).toContain('Transitions_PreprocessThisPage');
      // what happens to the data already prepared
      expect(wrapper.find('.transitionsSourceHeader__message').text())
        .toBe('Transitions_PreprocessedPageRemoved:example.org/page');
      expect(wrapper.find('.transitionsSourceHeader__usage').text())
        .toBe('Transitions_PreprocessedPagesUsage:11|20');
      // data already stored is still what this report shows
      expect(wrapper.find('.transitionsSourceHeader__badge').text()).toBe('Transitions_PreprocessedBadge');
    });

    it('should show a page name with markup as text in the message after stopping', async () => {
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      state.post.mockResolvedValue({});
      const wrapper = await mountHeader({
        actionName: 'http://example.org/%3Cimg%20src%3Dx%3E?a=1&amp;section=2',
      });

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');
      await answerDialog(wrapper, true);

      expect(wrapper.find('.transitionsSourceHeader__message').text())
        .toBe('Transitions_PreprocessedPageRemoved:example.org/<img src=x>?a=1&section=2');
      expect(wrapper.find('img').exists()).toBe(false);
    });

    it('should render a URL with markup as text in the confirmation', async () => {
      const url = 'https://example.org/<img src=x onerror=alert(1)>';
      state.fetch.mockResolvedValue(status({ selected: true, idPage: 3 }));
      const wrapper = await mountHeader({ actionName: url });

      await wrapper.find('.transitionsSourceHeader__toggle').trigger('click');

      const title = wrapper.find('.transitionsSourceHeader__dialogTitle');
      expect(title.text()).toContain('example.org/<img src=x onerror=alert(1)>');
      expect(wrapper.find('img').exists()).toBe(false);
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
      await answerDialog(wrapper, true);

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
      await answerDialog(wrapper, false);
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
      await answerDialog(wrapper, true);

      state.fetch.mockResolvedValue(status({ selected: true, idPage: 9, used: 12 }));
      await wrapper.setProps({ actionName: 'https://example.org/other' });
      await flushPromises();

      finishStop({});
      await flushPromises();

      const toggle = wrapper.find('.transitionsSourceHeader__toggle');
      expect(toggle.text()).toContain('Transitions_StopPreprocessing');
      expect(toggle.attributes('disabled')).toBeUndefined();
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
