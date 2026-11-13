/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { flushPromises, mount, VueWrapper } from '@vue/test-utils';

// Hoisted so the mock factories, which vitest lifts above this file's imports, can reach them.
const state = vi.hoisted(() => ({
  fetch: vi.fn(),
  post: vi.fn(),
}));

vi.mock('CoreHome', async () => {
  const { translateStub } = await import('../TransitionsReport/testCoreHomeMock');
  return {
    ActivityIndicator: { template: '<div class="activityIndicator" />', props: ['loading'] },
    AjaxHelper: {
      fetch: (...args: unknown[]) => state.fetch(...args),
      post: (...args: unknown[]) => state.post(...args),
    },
    ContentBlock: { template: '<div class="contentBlock"><slot /></div>', props: ['contentTitle'] },
    ContentTable: {},
    Matomo: {
      idSite: 1,
      helper: {
        htmlDecode: (value: string) => {
          const textArea = document.createElement('textarea');
          textArea.innerHTML = value;
          return textArea.value;
        },
      },
    },
    NumberFormatter: {
      formatNumber: (value: number) => String(value),
    },
    // runs at once, so a spec doesn't wait for the suggestions
    debounce: (fn: (...args: unknown[]) => void) => fn,
    translate: translateStub,
  };
});

vi.mock('CorePluginsAdmin', () => ({
  Field: {
    name: 'Field',
    template: '<input class="fieldInput" :value="modelValue" '
      + '@input="$emit(\'update:modelValue\', $event.target.value)" />',
    props: ['uicontrol', 'name', 'modelValue', 'title', 'fullWidth'],
    emits: ['update:modelValue'],
  },
}));

import { translateStub } from '../TransitionsReport/testCoreHomeMock';
import ManagePreprocessedPages, { PreprocessedPage } from './ManagePreprocessedPages.vue';

function page(idpage: number, overrides: Partial<PreprocessedPage> = {}): PreprocessedPage {
  return {
    idpage,
    url: `https://example.org/page-${idpage}`,
    created_date: '2025-03-10 09:14:33',
    created_by: 'admin',
    ...overrides,
  };
}

function pages(count: number): PreprocessedPage[] {
  return Array.from({ length: count }, (_, index) => page(count - index));
}

/** Answers each API method with the given result; the list is read on every call. */
function answer(list: () => PreprocessedPage[], suggestions: unknown[] = []) {
  state.fetch.mockImplementation((params: Record<string, unknown>) => {
    if (params.method === 'Transitions.getPreprocessedPages') {
      return Promise.resolve(structuredClone(list()));
    }

    return Promise.resolve(suggestions);
  });
}

async function mountPage(
  props: Record<string, unknown> = {},
  attachTo: HTMLElement|undefined = undefined,
): Promise<VueWrapper> {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wrapper = mount(ManagePreprocessedPages as any, {
    attachTo,
    props: { maxPages: 20, rearchiveMonths: 6, ...props },
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

function listRequests() {
  return state.fetch.mock.calls.filter(([params]) => params.method === 'Transitions.getPreprocessedPages');
}

async function typeUrl(wrapper: VueWrapper, url: string) {
  await wrapper.find('.fieldInput').setValue(url);
  await flushPromises();
}

describe('Transitions/ManagePreprocessedPages', () => {
  beforeEach(() => {
    state.fetch.mockReset();
    state.post.mockReset();
  });

  describe('list', () => {
    it('should list the pages with the usage of the limit', async () => {
      answer(() => [
        page(2, { url: 'https://example.org/b' }),
        page(1, { url: 'https://example.org/a', created_by: null, created_date: '2025-02-01 23:05:00' }),
      ]);
      const wrapper = await mountPage();

      expect(listRequests()[0][0]).toMatchObject({ method: 'Transitions.getPreprocessedPages', idSite: 1 });
      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:2|20');
      expect(wrapper.find('.managePreprocessedPages__meterFill').attributes('style')).toContain('width: 10%');

      const rows = wrapper.findAll('.managePreprocessedPages__row');
      expect(rows.map((row) => row.find('.managePreprocessedPages__url').text()))
        .toEqual(['https://example.org/b', 'https://example.org/a']);
      expect(rows[0].find('.managePreprocessedPages__date').text())
        .toBe('Transitions_DateTimeUtc:2025-03-10 09:14');
      expect(rows[0].find('.managePreprocessedPages__addedBy').text()).toBe('admin');

      const unknown = rows[1].find('.managePreprocessedPages__unknownUser');
      expect(unknown.text()).toBe('General_Unknown');
      expect(unknown.attributes('title')).toBe('Transitions_UnknownUserHelp');
      // reachable by keyboard and described to screen readers
      expect(unknown.attributes('tabindex')).toBe('0');
      expect(wrapper.find(`#${unknown.attributes('aria-describedby')}`).text())
        .toBe('Transitions_UnknownUserHelp');

      expect(wrapper.find('.managePreprocessedPages__limitMessage').exists()).toBe(false);
      expect(wrapper.find('.managePreprocessedPages__empty').exists()).toBe(false);
    });

    it('should name a super user creator that the viewer may not see, and a deleted one Unknown', async () => {
      answer(() => [
        page(3, { created_by: null, created_by_superuser: true }),
        page(2, { created_by: null, created_by_superuser: false }),
        page(1, { created_by: 'admin', created_by_superuser: false }),
      ]);
      const wrapper = await mountPage();

      const cells = wrapper.findAll('.managePreprocessedPages__addedBy');
      expect(cells[0].text()).toBe('Transitions_AddedBySuperUser');
      expect(cells[0].find('.managePreprocessedPages__unknownUser').exists()).toBe(false);
      expect(cells[1].find('.managePreprocessedPages__unknownUser').text()).toBe('General_Unknown');
      expect(cells[2].text()).toBe('admin');
    });

    it('should render URLs with markup as text', async () => {
      answer(() => [page(1, { url: 'https://example.org/"><img src=x onerror=alert(1)>' })]);
      const wrapper = await mountPage();

      const cell = wrapper.find('.managePreprocessedPages__url');
      expect(cell.text()).toBe('https://example.org/"><img src=x onerror=alert(1)>');
      expect(wrapper.find('img').exists()).toBe(false);

      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      expect(wrapper.find('.managePreprocessedPages__confirmText').text())
        .toContain('example.org/"><img src=x onerror=alert(1)>');
      expect(wrapper.find('img').exists()).toBe(false);
    });

    it('should explain the empty list', async () => {
      answer(() => []);
      const wrapper = await mountPage();

      expect(wrapper.find('.managePreprocessedPages__empty').text()).toBe('Transitions_PreprocessedPagesEmpty');
      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:0|20');

      await typeUrl(wrapper, 'https://example.org/');
      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeUndefined();
    });

    it('should show a list error inline', async () => {
      state.fetch.mockRejectedValue(new Error('You can&#039;t access this resource'));
      const wrapper = await mountPage();

      expect(wrapper.find('.managePreprocessedPages__error').text()).toBe('You can\'t access this resource');

      // nothing is known about the list, so it isn't shown as empty and nothing can be added
      expect(wrapper.find('.managePreprocessedPages__usage').exists()).toBe(false);
      expect(wrapper.find('.managePreprocessedPages__table').isVisible()).toBe(false);
      await typeUrl(wrapper, 'https://example.org/new');
      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeDefined();
    });

    it('should show stored URLs decoded, as text', async () => {
      answer(() => [page(1, { url: 'https://example.org/a?b=&lt;x&gt;&amp;c=1' })]);
      const wrapper = await mountPage();

      expect(wrapper.find('.managePreprocessedPages__url').text()).toBe('https://example.org/a?b=<x>&c=1');
      expect(wrapper.find('x').exists()).toBe(false);

      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      expect(wrapper.find('.managePreprocessedPages__confirmText').text())
        .toContain('Transitions_StopPreprocessingConfirm:example.org/a?b=<x>&c=1');
    });

    it('should keep the newest list when reloads finish out of order', async () => {
      const pending: ((list: PreprocessedPage[]) => void)[] = [];
      state.fetch.mockImplementation((params: Record<string, unknown>) => {
        if (params.method !== 'Transitions.getPreprocessedPages') {
          return Promise.resolve([]);
        }

        if (!state.post.mock.calls.length) {
          return Promise.resolve([page(1)]);
        }

        return new Promise((resolve) => {
          pending.push(resolve);
        });
      });
      state.post.mockResolvedValue({});
      const wrapper = await mountPage();

      // an add, then a remove, each followed by a reload
      await typeUrl(wrapper, 'https://example.org/page-2');
      await wrapper.find('form').trigger('submit');
      await flushPromises();
      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      await wrapper.find('.managePreprocessedPages__stop').trigger('click');
      await flushPromises();
      expect(pending).toHaveLength(2);

      // the newer reload lands first
      pending[1]([page(2)]);
      await flushPromises();
      pending[0]([page(2), page(1)]);
      await flushPromises();

      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(1);
    });
  });

  describe('limit', () => {
    it('should say that adding is turned off with a limit of 0', async () => {
      answer(() => pages(2));
      const wrapper = await mountPage({ maxPages: 0 });
      await typeUrl(wrapper, 'https://example.org/new');

      expect(wrapper.find('.managePreprocessedPages__limitMessage').text())
        .toBe('Transitions_PreprocessedPagesAddingDisabled');
      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeDefined();
    });

    it('should block adding when the limit is reached', async () => {
      answer(() => pages(20));
      const wrapper = await mountPage();
      await typeUrl(wrapper, 'https://example.org/new');

      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:20|20');
      expect(wrapper.find('.managePreprocessedPages__meterFill').attributes('style')).toContain('width: 100%');
      expect(wrapper.find('.managePreprocessedPages__limitMessage').text())
        .toBe('Transitions_PreprocessedPagesLimitReached:20');
      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeDefined();

      await wrapper.find('form').trigger('submit');
      expect(state.post).not.toHaveBeenCalled();
    });

    it('should explain a limit lowered below the number of pages', async () => {
      answer(() => pages(25));
      const wrapper = await mountPage();
      await typeUrl(wrapper, 'https://example.org/new');

      expect(wrapper.find('.managePreprocessedPages__limitMessage').text())
        .toBe('Transitions_PreprocessedPagesOverLimit:25|20|6');
      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeDefined();

      // a progress bar can't go past its maximum
      const meter = wrapper.find('.managePreprocessedPages__meter');
      expect(meter.attributes('aria-valuenow')).toBe('20');
      expect(meter.attributes('aria-valuemax')).toBe('20');
    });
  });

  describe('adding', () => {
    it('should add the typed URL and reload the list', async () => {
      let list = [page(1)];
      answer(() => list);
      state.post.mockImplementation(() => {
        list = [page(2, { url: 'https://example.org/new' }), page(1)];
        return Promise.resolve(list[0]);
      });
      const wrapper = await mountPage();

      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeDefined();
      await typeUrl(wrapper, '  https://example.org/new ');
      await wrapper.find('form').trigger('submit');
      await flushPromises();

      expect(state.post).toHaveBeenCalledWith(
        { method: 'Transitions.addPreprocessedPage', idSite: 1 },
        { pageUrl: 'https://example.org/new' },
        { createErrorNotification: false },
      );
      expect(listRequests()).toHaveLength(2);
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(2);
      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:2|20');
      expect((wrapper.find('.fieldInput').element as HTMLInputElement).value).toBe('');
    });

    it('should explain a duplicate inline and read the list again', async () => {
      answer(() => [page(1)]);
      state.post.mockRejectedValue(new Error('Transitions_PreprocessedPageAlreadySelected'));
      const wrapper = await mountPage();

      await typeUrl(wrapper, 'https://www.example.org/page-1');
      await wrapper.find('form').trigger('submit');
      await flushPromises();

      expect(wrapper.find('.managePreprocessedPages__addError .managePreprocessedPages__error').text())
        .toBe('Transitions_PreprocessedPageAlreadySelected');
      expect(wrapper.find('.managePreprocessedPages__duplicateHelp').text())
        .toBe('Transitions_PreprocessedPageDuplicateHelp');
      expect(listRequests()).toHaveLength(2);
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(1);
    });

    it('should show the pages another admin added meanwhile when adding fails', async () => {
      let list = pages(19);
      answer(() => list);
      state.post.mockImplementation(() => {
        list = pages(20);
        return Promise.reject(new Error('You can select at most 20 pages for pre-processed Transitions.'));
      });
      const wrapper = await mountPage();

      await typeUrl(wrapper, 'https://example.org/new');
      await wrapper.find('form').trigger('submit');
      await flushPromises();

      expect(wrapper.find('.managePreprocessedPages__addError').text())
        .toBe('You can select at most 20 pages for pre-processed Transitions.');
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(20);
      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:20|20');
      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeDefined();
      expect((wrapper.find('.fieldInput').element as HTMLInputElement).value).toBe('https://example.org/new');
    });

    it('should show other API errors without the duplicate note', async () => {
      answer(() => []);
      state.post.mockRejectedValue(new Error('Please enter a valid page URL starting with http:// or https://.'));
      const wrapper = await mountPage();

      await typeUrl(wrapper, 'example.org/a');
      await wrapper.find('form').trigger('submit');
      await flushPromises();

      expect(wrapper.find('.managePreprocessedPages__addError').text())
        .toBe('Please enter a valid page URL starting with http:// or https://.');
      expect(wrapper.find('.managePreprocessedPages__duplicateHelp').exists()).toBe(false);
    });

    const POOL = [
      { label: '/a', nb_hits: 4812, url: 'https://example.org/a' },
      { label: '/a?q', nb_hits: 13, url: 'https://example.org/a?q=&lt;b&gt;' },
      { label: '/a/b', nb_hits: '12', url: 'http://www.example.org/A/b' },
      { label: '/c', nb_hits: 11, url: 'https://example.org/c' },
    ];

    function poolRequests() {
      return state.fetch.mock.calls.filter(([params]) => params.method === 'Actions.getPageUrls');
    }

    it('should read the most viewed pages of last month once, when the field is first used', async () => {
      answer(() => [], POOL);
      const wrapper = await mountPage();
      expect(poolRequests()).toHaveLength(0);

      await wrapper.find('.managePreprocessedPages__field').trigger('focusin');
      await flushPromises();
      await typeUrl(wrapper, 'example.org/a');
      await typeUrl(wrapper, 'example.org/c');
      await wrapper.find('.managePreprocessedPages__field').trigger('focusin');

      expect(poolRequests()).toHaveLength(1);
      expect(poolRequests()[0][0]).toMatchObject({
        idSite: 1,
        period: 'month',
        date: 'lastMonth',
        flat: 1,
        filter_sort_column: 'nb_hits',
        filter_sort_order: 'desc',
        filter_limit: 500,
      });
      expect(poolRequests()[0][0]).not.toHaveProperty('filter_pattern');
    });

    it.each([
      ['the list is loaded', () => new Promise<PreprocessedPage[]>(() => { /* never answered */ }), {}],
      ['a page can be added', () => Promise.resolve(pages(20)), {}],
      ['adding is turned on', () => Promise.resolve([]), { maxPages: 0 }],
    ])('should not read the suggestions before %s', async (_, list, props) => {
      state.fetch.mockImplementation((params: Record<string, unknown>) => (
        params.method === 'Transitions.getPreprocessedPages' ? list() : Promise.resolve(POOL)
      ));
      const wrapper = await mountPage(props);

      await wrapper.find('.managePreprocessedPages__field').trigger('focusin');
      await typeUrl(wrapper, 'example.org/a');

      expect(poolRequests()).toHaveLength(0);
    });

    it('should suggest the matching pages in pageviews order, whatever their scheme and case', async () => {
      answer(() => [], POOL);
      const wrapper = await mountPage();

      await typeUrl(wrapper, 'https://www.example.org/a');

      expect(wrapper.find('.managePreprocessedPages__suggestionsHeader').text())
        .toContain('Transitions_PreprocessedPagesSuggestions');
      const suggestions = wrapper.findAll('.managePreprocessedPages__suggestion');
      expect(suggestions.map((suggestion) => [
        suggestion.find('.managePreprocessedPages__suggestionUrl').text(),
        suggestion.find('.managePreprocessedPages__suggestionPageviews').text(),
      ])).toEqual([
        ['https://example.org/a', '4812'],
        ['https://example.org/a?q=<b>', '13'],
        ['http://www.example.org/A/b', '12'],
      ]);
      expect(suggestions[1].find('.managePreprocessedPages__suggestionUrl').attributes('title'))
        .toBe('https://example.org/a?q=<b>');

      await suggestions[1].trigger('click');
      await flushPromises();

      expect((wrapper.find('.fieldInput').element as HTMLInputElement).value)
        .toBe('https://example.org/a?q=<b>');
      expect(wrapper.find('.managePreprocessedPages__suggestions').exists()).toBe(false);
    });

    it('should leave out selected pages, whatever their scheme and host case', async () => {
      let list = [page(1, { url: 'http://EXAMPLE.org/a' })];
      answer(() => list, POOL);
      state.post.mockImplementation(() => {
        list = [page(2, { url: 'https://example.org/a?q=&lt;b&gt;' }), ...list];
        return Promise.resolve(list[0]);
      });
      const wrapper = await mountPage();

      await typeUrl(wrapper, 'example.org/a');
      expect(wrapper.findAll('.managePreprocessedPages__suggestionUrl').map((url) => url.text()))
        .toEqual(['https://example.org/a?q=<b>', 'http://www.example.org/A/b']);

      // an added page is left out as well
      await typeUrl(wrapper, 'https://example.org/a?q=<b>');
      await wrapper.find('form').trigger('submit');
      await flushPromises();
      await typeUrl(wrapper, 'example.org/a');

      expect(wrapper.findAll('.managePreprocessedPages__suggestionUrl').map((url) => url.text()))
        .toEqual(['http://www.example.org/A/b']);
    });

    it.each(['café', 'caf%C3%A9', 'CAFÉ'])('should match "%s" against percent-encoded URLs', async (typed) => {
      answer(() => [], [
        { label: '/caf%C3%A9', nb_hits: 3, url: 'https://example.org/caf%C3%A9' },
        { label: '/cafe', nb_hits: 2, url: 'https://example.org/cafe' },
      ]);
      const wrapper = await mountPage();

      await typeUrl(wrapper, typed);

      expect(wrapper.findAll('.managePreprocessedPages__suggestionUrl').map((url) => url.text()))
        .toEqual(['https://example.org/caf%C3%A9']);
    });

    it('should keep a malformed percent sign as typed', async () => {
      answer(() => [], [{ label: '/100%', nb_hits: 3, url: 'https://example.org/100%' }]);
      const wrapper = await mountPage();

      await typeUrl(wrapper, '/100%');

      expect(wrapper.findAll('.managePreprocessedPages__suggestionUrl').map((url) => url.text()))
        .toEqual(['https://example.org/100%']);
    });

    it('should close the suggestions on Escape and when the focus leaves the field', async () => {
      answer(() => [], POOL);
      const wrapper = await mountPage();
      const field = wrapper.find('.managePreprocessedPages__field');

      await typeUrl(wrapper, 'example.org/a');
      await field.trigger('keydown', { key: 'Escape' });
      expect(wrapper.find('.managePreprocessedPages__suggestions').exists()).toBe(false);

      await typeUrl(wrapper, 'example.org/a/');
      expect(wrapper.find('.managePreprocessedPages__suggestions').exists()).toBe(true);

      // to a suggestion, the list stays
      const suggestion = wrapper.find('.managePreprocessedPages__suggestion').element;
      await field.trigger('focusout', { relatedTarget: suggestion });
      expect(wrapper.find('.managePreprocessedPages__suggestions').exists()).toBe(true);

      await field.trigger('focusout', { relatedTarget: wrapper.find('.managePreprocessedPages__add').element });
      expect(wrapper.find('.managePreprocessedPages__suggestions').exists()).toBe(false);
    });

    it('should keep the focus in the field when a suggestion is picked', async () => {
      answer(() => [], POOL);
      const wrapper = await mountPage({}, document.body);

      await typeUrl(wrapper, 'example.org/c');
      const suggestion = wrapper.find('.managePreprocessedPages__suggestion');
      (suggestion.element as HTMLElement).focus();
      await suggestion.trigger('click');
      await flushPromises();

      expect(document.activeElement).toBe(wrapper.find('.fieldInput').element);
      wrapper.unmount();
    });

    it('should match typed special characters against the decoded URL', async () => {
      answer(() => [], POOL);
      const wrapper = await mountPage();

      await typeUrl(wrapper, 'a?q=<b');

      expect(wrapper.findAll('.managePreprocessedPages__suggestionUrl').map((url) => url.text()))
        .toEqual(['https://example.org/a?q=<b>']);
    });

    it('should show at most 10 suggestions', async () => {
      answer(() => [], Array.from({ length: 30 }, (_, index) => ({
        label: `/p${index}`, nb_hits: 100 - index, url: `https://example.org/p${index}`,
      })));
      const wrapper = await mountPage();

      await typeUrl(wrapper, 'example.org/p');

      expect(wrapper.findAll('.managePreprocessedPages__suggestion')).toHaveLength(10);
    });

    it.each(['   ', 'ab', 'https://www.ab'])('should not suggest anything for "%s"', async (text) => {
      answer(() => [], POOL);
      const wrapper = await mountPage();

      await typeUrl(wrapper, text);

      expect(wrapper.find('.managePreprocessedPages__suggestions').exists()).toBe(false);
    });

    it('should still accept typed URLs when the suggestions fail to load', async () => {
      state.fetch.mockImplementation((params: Record<string, unknown>) => (
        params.method === 'Actions.getPageUrls'
          ? Promise.reject(new Error('failed'))
          : Promise.resolve([])
      ));
      const wrapper = await mountPage();

      await typeUrl(wrapper, 'https://example.org/new');

      expect(wrapper.find('.managePreprocessedPages__suggestions').exists()).toBe(false);
      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeUndefined();
    });

    it.each([
      [6, 'Transitions_PreprocessedPagesRebuildHint:6'],
      [1, 'Transitions_PreprocessedPagesRebuildHintOneMonth'],
      [0, 'Transitions_PreprocessedPagesNewDataOnlyHint'],
    ])('should say how far back an added page is prepared (%s months)', async (months, hint) => {
      answer(() => []);
      const wrapper = await mountPage({ rearchiveMonths: months });

      expect(wrapper.find('.managePreprocessedPages__hint').text()).toBe(hint);
    });
  });

  describe('removing', () => {
    it('should remove a page only after the inline confirmation', async () => {
      let list = [page(2), page(1)];
      answer(() => list);
      state.post.mockImplementation(() => {
        list = [page(2)];
        return Promise.resolve({});
      });
      const wrapper = await mountPage();

      await wrapper.findAll('.managePreprocessedPages__remove')[1].trigger('click');
      const confirming = wrapper.find('.managePreprocessedPages__row--confirming');
      expect(confirming.text()).toContain('Transitions_StopPreprocessingConfirm:example.org/page-1');
      expect(confirming.text()).toContain('Transitions_StopPreprocessingHelp');

      await wrapper.find('.managePreprocessedPages__cancel').trigger('click');
      expect(wrapper.find('.managePreprocessedPages__row--confirming').exists()).toBe(false);
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(2);
      expect(state.post).not.toHaveBeenCalled();

      await wrapper.findAll('.managePreprocessedPages__remove')[1].trigger('click');
      await wrapper.find('.managePreprocessedPages__stop').trigger('click');
      await flushPromises();

      expect(state.post).toHaveBeenCalledWith(
        { method: 'Transitions.removePreprocessedPage', idSite: 1 },
        { idPage: 1 },
        { createErrorNotification: false },
      );
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(1);
      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:1|20');
    });

    it('should move the focus back to the remove button, or to the one now in its place', async () => {
      let list = [page(3), page(2), page(1)];
      answer(() => list);
      state.post.mockImplementation(() => {
        list = [page(3), page(1)];
        return Promise.resolve({});
      });
      const wrapper = await mountPage({}, document.body);
      const removeButtons = () => wrapper.findAll('.managePreprocessedPages__remove');

      await removeButtons()[1].trigger('click');
      await flushPromises();
      expect(document.activeElement).toBe(wrapper.find('.managePreprocessedPages__cancel').element);

      await wrapper.find('.managePreprocessedPages__cancel').trigger('click');
      await flushPromises();
      expect(document.activeElement).toBe(removeButtons()[1].element);

      await removeButtons()[1].trigger('click');
      await wrapper.find('.managePreprocessedPages__stop').trigger('click');
      await flushPromises();
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(2);
      expect(document.activeElement).toBe(removeButtons()[1].element);

      wrapper.unmount();
    });

    it('should move the focus to the URL field once the last page is removed', async () => {
      let list = [page(1)];
      answer(() => list);
      state.post.mockImplementation(() => {
        list = [];
        return Promise.resolve({});
      });
      const wrapper = await mountPage({}, document.body);

      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      await wrapper.find('.managePreprocessedPages__stop').trigger('click');
      await flushPromises();

      expect(document.activeElement).toBe(wrapper.find('.fieldInput').element);
      wrapper.unmount();
    });

    it('should show the list without a page another admin removed meanwhile', async () => {
      let list = [page(2), page(1)];
      answer(() => list);
      state.post.mockImplementation(() => {
        list = [page(2)];
        return Promise.reject(new Error('This page is not selected for pre-processed Transitions.'));
      });
      const wrapper = await mountPage();

      await wrapper.findAll('.managePreprocessedPages__remove')[1].trigger('click');
      await wrapper.find('.managePreprocessedPages__stop').trigger('click');
      await flushPromises();

      expect(wrapper.find('.managePreprocessedPages__error').text())
        .toBe('This page is not selected for pre-processed Transitions.');
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(1);
      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:1|20');
    });

    it('should keep the row and show the error when removing fails', async () => {
      answer(() => [page(1)]);
      state.post.mockRejectedValue(new Error('This page is not selected for pre-processed Transitions.'));
      const wrapper = await mountPage();

      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      await wrapper.find('.managePreprocessedPages__stop').trigger('click');
      await flushPromises();

      expect(wrapper.find('.managePreprocessedPages__error').text())
        .toBe('This page is not selected for pre-processed Transitions.');
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(1);
    });
  });
});
