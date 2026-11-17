/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  DOMWrapper,
  flushPromises,
  mount,
  VueWrapper,
} from '@vue/test-utils';

// Hoisted so the mock factories, which vitest lifts above this file's imports, can reach them.
const state = vi.hoisted(() => ({
  fetch: vi.fn(),
  post: vi.fn(),
  notify: vi.fn(),
}));

vi.mock('CoreHome', async () => {
  const { translateStub } = await import('../TransitionsReport/testCoreHomeMock');
  return {
    ActivityIndicator: {
      name: 'ActivityIndicator',
      template: '<div class="activityIndicator" />',
      props: ['loading'],
    },
    AjaxHelper: {
      fetch: (...args: unknown[]) => state.fetch(...args),
      post: (...args: unknown[]) => state.post(...args),
    },
    ContentBlock: { template: '<div class="contentBlock"><slot /></div>', props: ['contentTitle'] },
    Alert: {
      template: '<div class="alert" :class="\'alert-\' + severity"><slot /></div>',
      props: ['severity'],
    },
    ContentTable: {},
    Matomo: {
      idSite: 1,
      helper: {
        // as piwikHelper.htmlEntities
        htmlEntities: (value: string) => value
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/{{/g, '{&#8291;{'),
        htmlDecode: (value: string) => {
          const textArea = document.createElement('textarea');
          textArea.innerHTML = value;
          return textArea.value;
        },
      },
    },
    MatomoDialog: {
      name: 'MatomoDialog',
      props: ['modelValue'],
      emits: ['yes', 'closeEnd', 'update:modelValue'],
      template: '<div class="matomoDialog"><slot /></div>',
    },
    NotificationsStore: {
      show: (...args: unknown[]) => state.notify(...args),
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
    props: ['uicontrol', 'name', 'modelValue', 'title', 'fullWidth', 'uiControlAttributes', 'autocomplete'],
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

/** A cell's value, without the label shown on a phone. */
function cellValue(cell: DOMWrapper<Element>): string {
  const label = cell.find('.managePreprocessedPages__cellLabel');
  return cell.text().substring(label.exists() ? label.text().length : 0).trim();
}

function dialogTitle(wrapper: VueWrapper): string {
  return wrapper.find('.managePreprocessedPages__dialogTitle').text();
}

async function typeUrl(wrapper: VueWrapper, url: string) {
  await wrapper.find('.fieldInput').setValue(url);
  await flushPromises();
}

describe('Transitions/ManagePreprocessedPages', () => {
  beforeEach(() => {
    state.fetch.mockReset();
    state.post.mockReset();
    state.notify.mockReset();
  });

  afterEach(() => {
    vi.useRealTimers();
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
      expect(cellValue(rows[0].find('.managePreprocessedPages__date')))
        .toBe('Transitions_DateTimeUtc:2025-03-10 09:14');
      expect(cellValue(rows[0].find('.managePreprocessedPages__addedBy'))).toBe('admin');
      // the column names, for a phone, where the header is hidden
      expect(rows[0].findAll('.managePreprocessedPages__cellLabel').map((label) => label.text()))
        .toEqual(['Transitions_ColumnDateAdded', 'Transitions_ColumnAddedBy']);

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
      expect(cellValue(cells[0])).toBe('Transitions_AddedBySuperUser');
      expect(cells[0].find('.managePreprocessedPages__unknownUser').exists()).toBe(false);
      expect(cells[1].find('.managePreprocessedPages__unknownUser').text()).toBe('General_Unknown');
      expect(cellValue(cells[2])).toBe('admin');
    });

    it('should render URLs with markup as text', async () => {
      answer(() => [page(1, { url: 'https://example.org/"><img src=x onerror=alert(1)>' })]);
      const wrapper = await mountPage();

      const cell = wrapper.find('.managePreprocessedPages__url');
      expect(cell.text()).toBe('https://example.org/"><img src=x onerror=alert(1)>');
      expect(wrapper.find('img').exists()).toBe(false);

      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      expect(dialogTitle(wrapper))
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
      expect(wrapper.find('.managePreprocessedPages__addForm').exists()).toBe(false);
    });

    it('should show stored URLs decoded, as text', async () => {
      answer(() => [page(1, { url: 'https://example.org/a?b=&lt;x&gt;&amp;c=1' })]);
      const wrapper = await mountPage();

      expect(wrapper.find('.managePreprocessedPages__url').text()).toBe('https://example.org/a?b=<x>&c=1');
      expect(wrapper.find('x').exists()).toBe(false);

      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      expect(dialogTitle(wrapper))
        .toContain('Transitions_StopPreprocessingConfirm:example.org/a?b=<x>&c=1');
    });

    it('should show percent-encoded UTF-8 decoded, as the Pages report does, with the stored URL as title', async () => {
      answer(() => [
        page(1, { url: 'https://example.org/%E6%A9%9F%E8%83%BD?q=a%20b&x=%E2' }),
        page(2, { url: 'https://example.org/plain' }),
      ]);
      const wrapper = await mountPage();

      const cells = wrapper.findAll('.managePreprocessedPages__url');
      // a sequence that isn't UTF-8 stays as it is
      expect(cells[0].text()).toBe('https://example.org/機能?q=a b&x=%E2');
      expect(cells[0].attributes('title')).toBe('https://example.org/%E6%A9%9F%E8%83%BD?q=a%20b&x=%E2');
      expect(cells[1].attributes('title')).toBeUndefined();

      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      expect(dialogTitle(wrapper))
        .toContain('Transitions_StopPreprocessingConfirm:example.org/機能?q=a b&x=%E2');
    });

    it('should keep control characters encoded in the title as in the text', async () => {
      answer(() => [page(1, { url: 'https://example.org/a\u202Eb%E6%A9%9F' })]);
      const wrapper = await mountPage();

      const cell = wrapper.find('.managePreprocessedPages__url');
      expect(cell.text()).toBe('https://example.org/a%E2%80%AEb機');
      expect(cell.attributes('title')).toBe('https://example.org/a%E2%80%AEb%E6%A9%9F');
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
      await answerDialog(wrapper, true);
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

      expect(wrapper.find('.managePreprocessedPages__limitMessage').text())
        .toBe('Transitions_PreprocessedPagesAddingDisabled');
      expect(wrapper.find('.managePreprocessedPages__addForm').exists()).toBe(false);
      expect(wrapper.find('.managePreprocessedPages__hint').exists()).toBe(false);
    });

    it('should hide the add form when the limit is reached', async () => {
      answer(() => pages(20));
      const wrapper = await mountPage();

      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:20|20');
      expect(wrapper.find('.managePreprocessedPages__meterFill').attributes('style')).toContain('width: 100%');
      expect(wrapper.find('.managePreprocessedPages__limitMessage').text())
        .toBe('Transitions_PreprocessedPagesLimitReached:20');
      expect(wrapper.find('.managePreprocessedPages__limitMessage .alert').classes()).toContain('alert-info');
      expect(wrapper.find('.managePreprocessedPages__addForm').exists()).toBe(false);
    });

    it('should explain a limit lowered below the number of pages', async () => {
      answer(() => pages(25));
      const wrapper = await mountPage();

      expect(wrapper.find('.managePreprocessedPages__limitMessage').text())
        .toBe('Transitions_PreprocessedPagesOverLimit:25|20|6');
      expect(wrapper.find('.managePreprocessedPages__limitMessage .alert').classes()).toContain('alert-warning');
      expect(wrapper.find('.managePreprocessedPages__addForm').exists()).toBe(false);

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
      // when the data appears, as a success notification like other admin pages
      expect(state.notify).toHaveBeenCalledWith(expect.objectContaining({
        message: 'Transitions_PreprocessedPageAddedRebuild:6',
        context: 'success',
        type: 'toast',
      }));
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
      expect(wrapper.find('.managePreprocessedPages__addError .alert').classes()).toContain('alert-danger');
      expect(listRequests()).toHaveLength(2);

      // the field is marked invalid and described by the error, then the hint
      const field = wrapper.findComponent({ name: 'Field' });
      const errorId = wrapper.find('.managePreprocessedPages__addError [role="alert"]').attributes('id');
      const hintId = wrapper.find('.managePreprocessedPages__hint').attributes('id');
      expect(field.props('autocomplete')).toBe('off');
      expect(field.props('uiControlAttributes')).toEqual({
        'aria-describedby': `${errorId} ${hintId}`,
        'aria-invalid': 'true',
      });

      await typeUrl(wrapper, 'https://example.org/other');
      expect(field.props('uiControlAttributes')['aria-describedby']).toBe(hintId);
      expect(field.props('uiControlAttributes')['aria-invalid']).toBe('false');
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(1);
    });

    it('should show the pages another admin added meanwhile when adding fails', async () => {
      let list = pages(19);
      answer(() => list);
      state.post.mockImplementation(() => {
        list = pages(20);
        return Promise.reject(new Error(
          "This website's limit of pre-processed pages (20) is reached. Remove a page to add another one.",
        ));
      });
      const wrapper = await mountPage({}, document.body);

      await typeUrl(wrapper, 'https://example.org/new');
      (wrapper.find('.managePreprocessedPages__add').element as HTMLElement).focus();
      await wrapper.find('form').trigger('submit');
      await flushPromises();

      // the form goes away with the limit reached, and the limit message says why
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(20);
      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:20|20');
      expect(wrapper.find('.managePreprocessedPages__addForm').exists()).toBe(false);
      expect(wrapper.find('.managePreprocessedPages__addError').exists()).toBe(false);
      expect(wrapper.find('.managePreprocessedPages__limitMessage').text())
        .toBe('Transitions_PreprocessedPagesLimitReached:20');
      // the focus was in the form; it moves to the message that took its place
      expect(document.activeElement).toBe(wrapper.find('.managePreprocessedPages__limitMessage').element);

      wrapper.unmount();
    });

    it('should show a loading indicator next to the button while adding', async () => {
      let finishAdd: (value: unknown) => void = () => undefined;
      answer(() => []);
      state.post.mockReturnValue(new Promise((resolve) => {
        finishAdd = resolve;
      }));
      const wrapper = await mountPage();
      const indicator = () => wrapper.findAllComponents({ name: 'ActivityIndicator' })
        .find((component) => component.element.closest('.managePreprocessedPages__addForm'));

      await typeUrl(wrapper, 'https://example.org/new');
      expect(indicator()!.props('loading')).toBe(false);

      await wrapper.find('form').trigger('submit');
      expect(indicator()!.props('loading')).toBe(true);
      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeDefined();

      finishAdd({ idpage: 1 });
      await flushPromises();
      expect(indicator()!.props('loading')).toBe(false);
    });

    it.each([
      ['an invalid URL', 'Transitions_PreprocessedPageInvalidUrl', 'true'],
      ['a concurrent change', 'Transitions_PreprocessedPagesBusy', 'false'],
      ['a network failure', 'General_ErrorRequest', 'false'],
    ])('should show %s without the duplicate note, marking the field invalid only for the URL', async (
      _,
      message,
      isInvalid,
    ) => {
      answer(() => []);
      state.post.mockRejectedValue(new Error(message));
      const wrapper = await mountPage();

      await typeUrl(wrapper, 'example.org/a');
      await wrapper.find('form').trigger('submit');
      await flushPromises();

      expect(wrapper.find('.managePreprocessedPages__addError').text()).toBe(message);
      expect(wrapper.find('.managePreprocessedPages__duplicateHelp').exists()).toBe(false);
      expect(wrapper.findComponent({ name: 'Field' }).props('uiControlAttributes')['aria-invalid'])
        .toBe(isInvalid);
      expect(state.notify).not.toHaveBeenCalled();
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

    /** The suggestions per period: rows, or an error to fail with. */
    function answerPool(last30: unknown[]|Error, previousMonth: unknown[]|Error = []) {
      state.fetch.mockImplementation((params: Record<string, unknown>) => {
        if (params.method === 'Transitions.getPreprocessedPages') {
          return Promise.resolve([]);
        }

        const rows = params.period === 'range' ? last30 : previousMonth;
        return rows instanceof Error ? Promise.reject(rows) : Promise.resolve(rows);
      });
    }

    async function suggestionsFor(wrapper: VueWrapper, typed: string) {
      await wrapper.find('.managePreprocessedPages__field').trigger('focusin');
      await flushPromises();
      await typeUrl(wrapper, typed);
      return wrapper.findAll('.managePreprocessedPages__suggestionUrl').map((url) => url.text());
    }

    it('should keep to the last 30 days when they have pages', async () => {
      answerPool(POOL, [{ label: '/old', nb_hits: 1, url: 'https://example.org/a-old' }]);
      const wrapper = await mountPage();

      expect(await suggestionsFor(wrapper, 'example.org/a')).toEqual([
        'https://example.org/a',
        'https://example.org/a?q=<b>',
        'http://www.example.org/A/b',
      ]);
      expect(poolRequests().map(([params]) => params.period)).toEqual(['range']);
      expect(wrapper.find('.managePreprocessedPages__suggestionsHeader').text())
        .toContain('Transitions_PreprocessedPagesSuggestions:CoreHome_PresetDateLast30Days');
    });

    it.each([
      ['have no pages, eg as the range can\'t be archived on request', []],
      ['have no pages with a URL', [{ label: 'Others', nb_hits: 3 }]],
      ['fail', new Error('No data')],
    ])('should fall back to the previous month when the last 30 days %s', async (_, last30) => {
      vi.useFakeTimers({ toFake: ['Date'] });
      vi.setSystemTime(new Date('2026-06-15T10:00:00Z'));
      answerPool(last30 as unknown[]|Error, POOL);
      const wrapper = await mountPage();

      expect(await suggestionsFor(wrapper, 'example.org/c')).toEqual(['https://example.org/c']);
      expect(poolRequests().map(([params]) => [params.period, params.date])).toEqual([
        ['range', 'last30'],
        ['month', '2026-05-01'],
      ]);
      expect(poolRequests()[1][0]).toMatchObject({
        flat: 1,
        segment: '',
        filter_sort_column: 'nb_hits',
        filter_sort_order: 'desc',
        filter_limit: 500,
      });
      expect(wrapper.find('.managePreprocessedPages__suggestionsHeader').text())
        .toContain('Transitions_PreprocessedPagesSuggestions:Intl_Month_Long_StandAlone_5');
    });

    it.each([
      ['are empty', [], []],
      ['fail', new Error('No data'), new Error('No data')],
    ])('should show no suggestions and no error when both periods %s', async (_, last30, previousMonth) => {
      answerPool(last30 as unknown[]|Error, previousMonth as unknown[]|Error);
      const wrapper = await mountPage();

      expect(await suggestionsFor(wrapper, 'example.org/a')).toEqual([]);
      expect(wrapper.find('.managePreprocessedPages__suggestions').exists()).toBe(false);
      expect(wrapper.find('.managePreprocessedPages__error').exists()).toBe(false);

      // at most these two requests, however often the field is used
      await typeUrl(wrapper, 'example.org/b');
      await wrapper.find('.managePreprocessedPages__field').trigger('focusin');
      await flushPromises();
      expect(poolRequests()).toHaveLength(2);
      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeUndefined();
    });

    it.each([
      ['on the last day of a month longer than the previous one', '2026-03-31T18:00:00Z', '2026-02-01'],
      ['across the year', '2027-01-15T10:00:00Z', '2026-12-01'],
      ['while the month just started in UTC has not yet for every website', '2026-04-01T06:00:00Z', '2026-02-01'],
      ['once the month has started for every website', '2026-04-01T12:00:00Z', '2026-03-01'],
    ])('should fall back to the previous calendar month %s', async (_, now, date) => {
      vi.useFakeTimers({ toFake: ['Date'] });
      vi.setSystemTime(new Date(now));
      answerPool([], POOL);
      const wrapper = await mountPage();

      await wrapper.find('.managePreprocessedPages__field').trigger('focusin');
      await flushPromises();

      expect(poolRequests()[1][0]).toMatchObject({ period: 'month', date });
      // the header names the month shown, also when it is the one before last
      await typeUrl(wrapper, 'example.org/c');
      expect(wrapper.find('.managePreprocessedPages__suggestionsHeader').text())
        .toContain(`Transitions_PreprocessedPagesSuggestions:Intl_Month_Long_StandAlone_${Number(date.substring(5, 7))}`);
    });

    it('should read the most viewed pages of the last 30 days once, when the field is first used', async () => {
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
        period: 'range',
        date: 'last30',
        flat: 1,
        filter_sort_column: 'nb_hits',
        filter_sort_order: 'desc',
        filter_limit: 500,
      });
      expect(poolRequests()[0][0]).not.toHaveProperty('filter_pattern');
    });

    it('should let a URL be typed and added while the suggestions are still loading', async () => {
      state.fetch.mockImplementation((params: Record<string, unknown>) => (
        params.method === 'Transitions.getPreprocessedPages'
          ? Promise.resolve([])
          : new Promise(() => { /* never answered */ })
      ));
      state.post.mockResolvedValue({ idpage: 1 });
      const wrapper = await mountPage();

      await wrapper.find('.managePreprocessedPages__field').trigger('focusin');
      await typeUrl(wrapper, 'https://example.org/new');
      expect(wrapper.find('.managePreprocessedPages__add').attributes('disabled')).toBeUndefined();
      await wrapper.find('form').trigger('submit');
      await flushPromises();

      expect(state.post).toHaveBeenCalledTimes(1);
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

      expect(wrapper.find('.managePreprocessedPages__field').exists()).toBe(false);
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

      // shown decoded, as in the list, with the stored URL as title; that is what a pick adds
      const urls = wrapper.findAll('.managePreprocessedPages__suggestionUrl');
      expect(urls.map((url) => url.text())).toEqual(['https://example.org/café']);
      expect(urls[0].attributes('title')).toBe('https://example.org/caf%C3%A9');
      await wrapper.find('.managePreprocessedPages__suggestion').trigger('click');
      expect((wrapper.find('.fieldInput').element as HTMLInputElement).value)
        .toBe('https://example.org/caf%C3%A9');
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
    it('should remove a page only after Matomo\'s confirmation dialog', async () => {
      let list = [page(2), page(1)];
      answer(() => list);
      state.post.mockImplementation(() => {
        list = [page(2)];
        return Promise.resolve({});
      });
      const wrapper = await mountPage();

      await wrapper.findAll('.managePreprocessedPages__remove')[1].trigger('click');
      expect(dialogOf(wrapper).props('modelValue')).toBe(true);
      const dialog = dialogOf(wrapper).find('.ui-confirm').element;
      expect(dialogTitle(wrapper)).toBe('Transitions_StopPreprocessingConfirm:example.org/page-1');
      expect(dialog.textContent).toContain('Transitions_StopPreprocessingHelp');
      expect(Array.from(dialog.querySelectorAll('input'))
        .map((input) => [input.getAttribute('role'), input.value]))
        .toEqual([['yes', 'General_Yes'], ['no', 'General_No']]);

      await answerDialog(wrapper, false);
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(2);
      expect(state.post).not.toHaveBeenCalled();

      await wrapper.findAll('.managePreprocessedPages__remove')[1].trigger('click');
      await answerDialog(wrapper, true);

      expect(state.post).toHaveBeenCalledWith(
        { method: 'Transitions.removePreprocessedPage', idSite: 1 },
        { idPage: 1 },
        { createErrorNotification: false },
      );
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(1);
      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:1|20');
      expect(state.notify).toHaveBeenCalledWith(expect.objectContaining({
        message: 'Transitions_PreprocessedPageRemoved:example.org/page-1',
        context: 'success',
        type: 'toast',
      }));
    });

    it('should name each remove button by the page', async () => {
      answer(() => [page(2), page(1)]);
      const wrapper = await mountPage();

      const button = wrapper.findAll('.managePreprocessedPages__remove')[1];
      const names = button.attributes('aria-labelledby')!.split(' ')
        .map((id) => wrapper.find(`[id="${id}"]`).text());
      expect(names).toEqual(['General_Remove', 'https://example.org/page-1']);

      expect(button.attributes('title')).toBe('General_Remove');
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
      (document.activeElement as HTMLElement).blur();
      await answerDialog(wrapper, false);
      expect(document.activeElement).toBe(removeButtons()[1].element);

      // removed while the dialog is still closing: the row is focused once it has closed
      await removeButtons()[1].trigger('click');
      (document.activeElement as HTMLElement).blur();
      dialogOf(wrapper).vm.$emit('yes');
      await flushPromises();
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(2);
      expect(document.activeElement).toBe(document.body);

      dialogOf(wrapper).vm.$emit('update:modelValue', false);
      dialogOf(wrapper).vm.$emit('closeEnd');
      await flushPromises();
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
      await answerDialog(wrapper, true);

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
      await answerDialog(wrapper, true);

      expect(wrapper.find('.managePreprocessedPages__error').text())
        .toBe('This page is not selected for pre-processed Transitions.');
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(1);
      expect(wrapper.find('.managePreprocessedPages__usageLabel').text())
        .toBe('Transitions_PreprocessedPagesUsage:1|20');
    });

    it('should show the remove icons disabled, the CoreHome way, while a page is removed', async () => {
      let finishRemove: (value: unknown) => void = () => undefined;
      answer(() => [page(2), page(1)]);
      state.post.mockReturnValue(new Promise((resolve) => {
        finishRemove = resolve;
      }));
      const wrapper = await mountPage();
      const states = () => wrapper.findAll('.managePreprocessedPages__remove')
        .map((button) => [button.attributes('data-state'), button.attributes('disabled') !== undefined]);

      expect(states()).toEqual([[undefined, false], [undefined, false]]);

      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      await answerDialog(wrapper, true);
      expect(states()).toEqual([['disabled', true], ['disabled', true]]);

      finishRemove({});
      await flushPromises();
      expect(states()).toEqual([[undefined, false], [undefined, false]]);
    });

    it('should move the focus to the limit message once the last page is removed and none can be added', async () => {
      let list = [page(1)];
      answer(() => list);
      state.post.mockImplementation(() => {
        list = [];
        return Promise.resolve({});
      });
      const wrapper = await mountPage({ maxPages: 0 }, document.body);

      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      await answerDialog(wrapper, true);

      expect(document.activeElement).toBe(wrapper.find('.managePreprocessedPages__limitMessage').element);
      wrapper.unmount();
    });

    it('should escape a page name with markup in the removal notification, which renders HTML', async () => {
      const url = 'http://example.org/%3Cimg%20src%3Dx%3E?a=1&amp;section=2';
      let list = [page(1, { url })];
      answer(() => list);
      state.post.mockImplementation(() => {
        list = [];
        return Promise.resolve({});
      });
      const wrapper = await mountPage();

      // shown as text, decoded, in the list and the dialog
      expect(wrapper.find('.managePreprocessedPages__url').text())
        .toBe('http://example.org/<img src=x>?a=1&section=2');
      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      expect(dialogTitle(wrapper))
        .toBe('Transitions_StopPreprocessingConfirm:example.org/<img src=x>?a=1&section=2');
      expect(wrapper.find('img').exists()).toBe(false);

      await answerDialog(wrapper, true);
      const { message } = state.notify.mock.calls[0][0];
      expect(message)
        .toBe('Transitions_PreprocessedPageRemoved:example.org/&lt;img src=x&gt;?a=1&amp;section=2');
      const rendered = document.createElement('div');
      rendered.innerHTML = message;
      expect(rendered.querySelector('img')).toBeNull();
      expect(rendered.textContent)
        .toBe('Transitions_PreprocessedPageRemoved:example.org/<img src=x>?a=1&section=2');
    });

    it('should keep the row and show the error when removing fails', async () => {
      answer(() => [page(1)]);
      state.post.mockRejectedValue(new Error('This page is not selected for pre-processed Transitions.'));
      const wrapper = await mountPage();

      await wrapper.find('.managePreprocessedPages__remove').trigger('click');
      await answerDialog(wrapper, true);

      expect(wrapper.find('.managePreprocessedPages__error').text())
        .toBe('This page is not selected for pre-processed Transitions.');
      expect(wrapper.findAll('.managePreprocessedPages__row')).toHaveLength(1);
    });
  });
});
