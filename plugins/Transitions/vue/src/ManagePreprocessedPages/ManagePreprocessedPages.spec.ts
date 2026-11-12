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

async function mountPage(props: Record<string, unknown> = {}): Promise<VueWrapper> {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wrapper = mount(ManagePreprocessedPages as any, {
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
      expect(rows[0].find('.managePreprocessedPages__date').text()).toBe('2025-03-10 09:14 UTC');
      expect(rows[0].find('.managePreprocessedPages__addedBy').text()).toBe('admin');

      const unknown = rows[1].find('.managePreprocessedPages__unknownUser');
      expect(unknown.text()).toBe('General_Unknown');
      expect(unknown.attributes('title')).toBe('Transitions_UnknownUserHelp');

      expect(wrapper.find('.managePreprocessedPages__limitMessage').exists()).toBe(false);
      expect(wrapper.find('.managePreprocessedPages__empty').exists()).toBe(false);
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
    });
  });

  describe('limit', () => {
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

    it('should explain a duplicate inline and keep the list', async () => {
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
      expect(listRequests()).toHaveLength(1);
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(1);
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

    it('should suggest the most viewed matching pages of the last 30 days', async () => {
      answer(() => [], [
        { label: '/a', nb_hits: 4812, url: 'https://example.org/a' },
        { label: '/a/b', nb_hits: '12', url: 'https://example.org/a/b' },
      ]);
      const wrapper = await mountPage();

      await typeUrl(wrapper, 'https://www.example.org/a?(x)');

      const request = state.fetch.mock.calls.find(([params]) => params.method === 'Actions.getPageUrls')![0];
      expect(request).toMatchObject({
        idSite: 1,
        period: 'range',
        date: 'last30',
        flat: 1,
        filter_column: 'url',
        filter_pattern: 'example\\.org/a\\?\\(x\\)',
        filter_sort_column: 'nb_hits',
        filter_sort_order: 'desc',
      });

      expect(wrapper.find('.managePreprocessedPages__suggestionsHeader').text())
        .toContain('Transitions_PreprocessedPagesSuggestions');
      const suggestions = wrapper.findAll('.managePreprocessedPages__suggestion');
      expect(suggestions.map((suggestion) => [
        suggestion.find('.managePreprocessedPages__suggestionUrl').text(),
        suggestion.find('.managePreprocessedPages__suggestionPageviews').text(),
      ])).toEqual([['https://example.org/a', '4812'], ['https://example.org/a/b', '12']]);

      await suggestions[1].trigger('click');
      await flushPromises();

      expect((wrapper.find('.fieldInput').element as HTMLInputElement).value).toBe('https://example.org/a/b');
      expect(wrapper.find('.managePreprocessedPages__suggestions').exists()).toBe(false);
    });

    it('should not ask for suggestions for an empty field', async () => {
      answer(() => []);
      const wrapper = await mountPage();

      await typeUrl(wrapper, '   ');

      expect(state.fetch.mock.calls.some(([params]) => params.method === 'Actions.getPageUrls')).toBe(false);
    });

    it.each([
      [6, 'Transitions_PreprocessedPagesRebuildHint:6'],
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
