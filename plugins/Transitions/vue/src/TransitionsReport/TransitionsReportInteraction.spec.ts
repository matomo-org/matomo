/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { VueWrapper } from '@vue/test-utils';

const postEvent = vi.fn();

// Pulled in dynamically: a vi.mock() factory is hoisted above every import in the file, so it
// cannot reach a top-level one.
vi.mock('CoreHome', async () => {
  const { coreHomeMock } = await import('./testCoreHomeMock');
  return coreHomeMock((...args: unknown[]) => postEvent(...args));
});

import { flushRibbons, mountTransitionsReport } from './testTransitionsReportHarness';

import { installFakeTransitionsBackend, FakeTransitionsBackend } from './testFakeTransitionsModel';
import { stubElementRects, useSynchronousFrames } from './testMeasuredLayout';

const REPORT = {
  pageviews: 100,
  loops: 0,
  exits: 10,
  directEntries: 5,
  groups: {
    previousPages: {
      total: 40,
      details: [{ url: 'http://example.org/a', referrals: 40 }],
    },
    searchEngines: {
      total: 25,
      details: [{ label: 'Google', referrals: 20 }, { label: 'Bing', referrals: 5 }],
    },
    followingPages: {
      total: 20,
      details: [{ url: 'http://example.org/c', referrals: 20 }],
    },
    outlinks: {
      total: 8,
      details: [{ url: 'http://other.example/x', referrals: 8 }],
    },
  },
};

describe('Transitions/TransitionsReport interaction', () => {
  let backend: FakeTransitionsBackend;

  beforeEach(() => {
    postEvent.mockClear();
    stubElementRects();
    backend = installFakeTransitionsBackend(structuredClone(REPORT));
    useSynchronousFrames();
  });

  afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
  });

  async function mountLoaded(props = {}): Promise<VueWrapper> {
    const wrapper = mountTransitionsReport(props);
    backend.respond();
    await flushRibbons();
    return wrapper;
  }

  /** Finds a section by its (untranslated) heading text. */
  function sectionByTitle(wrapper: VueWrapper, title: string) {
    return wrapper.findAll('.transitionsSection').find(
      (section) => section.find('.transitionsSection__title').text() === title,
    );
  }

  /** Finds a summary row by its (untranslated) label. */
  function summaryRow(wrapper: VueWrapper, label: string) {
    return wrapper.findAll('.transitionsRow--summary').find(
      (row) => row.find('.transitionsRow__label').text() === label,
    )!;
  }

  /** Finds a center card metric by its (untranslated) inline label. */
  function cardMetric(wrapper: VueWrapper, label: string) {
    return wrapper.findAll('.transitionsCenterCard__metric').find(
      (metric) => metric.text().includes(label),
    )!;
  }

  /** The labels of the detail rows of the open section on a side. */
  function openRowLabels(wrapper: VueWrapper, title: string): string[] {
    const section = sectionByTitle(wrapper, title);
    return section
      ? section.findAll('.transitionsRow__label').map((node) => node.text())
      : [];
  }

  it('should open a group when its summary row is clicked', async () => {
    const wrapper = await mountLoaded();

    expect(sectionByTitle(wrapper, 'Transitions_FromSearchEngines')).toBeUndefined();

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('click');
    await flushRibbons();

    expect(openRowLabels(wrapper, 'Transitions_FromSearchEngines')).toEqual(['Google', 'Bing']);
  });

  it('should move the previously open group into the catch-all block', async () => {
    const wrapper = await mountLoaded();

    expect(openRowLabels(wrapper, 'Transitions_FromPreviousPages')).toEqual(['/a']);

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('click');
    await flushRibbons();

    expect(sectionByTitle(wrapper, 'Transitions_FromPreviousPages')).toBeUndefined();
    expect(summaryRow(wrapper, 'Transitions_FromPreviousPages').exists()).toBe(true);
  });

  it('should leave the other side untouched when a group is opened', async () => {
    const wrapper = await mountLoaded();

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('click');
    await flushRibbons();

    expect(openRowLabels(wrapper, 'Transitions_ToFollowingPages')).toEqual(['/c']);
  });

  it('should not offer to open a group that has no detail rows', async () => {
    const wrapper = await mountLoaded();

    const exits = summaryRow(wrapper, 'General_ColumnExits');
    expect(exits.classes()).not.toContain('transitionsRow--actionable');

    await exits.trigger('click');
    await flushRibbons();

    expect(openRowLabels(wrapper, 'Transitions_ToFollowingPages')).toEqual(['/c']);
  });

  it('should open a group when its center card metric is clicked', async () => {
    const wrapper = await mountLoaded();

    const outlinks = cardMetric(wrapper, 'Transitions_NumOutlinks');

    await outlinks.trigger('click');
    await flushRibbons();

    expect(openRowLabels(wrapper, 'General_Outlinks')).toEqual(['other.example/x']);
  });

  it('should render a download row as a link, with the domain stripped from its label', async () => {
    // Downloads keep only their path in the label but still open in a new tab, so the shortening
    // and the linking are driven separately. Added per test to keep the shared totals untouched.
    backend.report.groups!.downloads = {
      total: 6,
      details: [{ url: 'http://example.org/files/report.pdf', referrals: 6 }],
    };

    const wrapper = await mountLoaded();

    await summaryRow(wrapper, 'General_Downloads').trigger('click');
    await flushRibbons();

    const row = sectionByTitle(wrapper, 'General_Downloads')!.find('.transitionsRow');
    expect(row.element.tagName).toBe('A');
    expect(row.attributes('href')).toBe('http://example.org/files/report.pdf');
    expect(row.attributes('target')).toBe('_blank');
    expect(row.attributes('rel')).toBe('noreferrer noopener');
    expect(row.find('.transitionsRow__label').text()).toBe('/files/report.pdf');
  });

  it('should not turn a dangerous outlink URL into a link', async () => {
    // Row labels are tracked URLs, so an attacker who can send tracking requests chooses them.
    backend.report.groups!.outlinks = {
      total: 3,
      details: [{ url: 'javascript:alert(document.domain)', referrals: 3 }],
    };

    const wrapper = await mountLoaded();

    await summaryRow(wrapper, 'General_Outlinks').trigger('click');
    await flushRibbons();

    const row = sectionByTitle(wrapper, 'General_Outlinks')!.find('.transitionsRow');
    expect(row.element.tagName).toBe('DIV');
    expect(row.attributes('href')).toBeUndefined();
    expect(row.classes()).not.toContain('transitionsRow--actionable');
  });

  it('should not open a metric that has no detail rows', async () => {
    const wrapper = await mountLoaded();

    const exits = cardMetric(wrapper, 'Transitions_ExitsInline');

    expect(exits.classes()).not.toContain('transitionsCenterCard__metric--actionable');

    await exits.trigger('click');
    await flushRibbons();

    expect(openRowLabels(wrapper, 'Transitions_ToFollowingPages')).toEqual(['/c']);
  });

  it('should leave a metric at zero without a tooltip or a hover highlight', async () => {
    const wrapper = await mountLoaded();

    const socialNetworks = cardMetric(wrapper, 'Referrers_TypeSocialNetworks');

    // Nothing happened in that group, so there is no share to explain and nothing to emphasise.
    expect(socialNetworks.attributes('title')).toBe('');

    await socialNetworks.trigger('mouseenter');
    await flushRibbons();

    expect(socialNetworks.classes()).not.toContain('transitionsCenterCard__metric--highlighted');
  });

  it('should still highlight a metric that has a value but cannot be opened', async () => {
    const wrapper = await mountLoaded();

    // The highlight is gated on the value, not on expandability: direct entries have no rows to
    // open, yet the legacy renderer highlighted them whenever they were above zero.
    const directEntries = cardMetric(wrapper, 'Referrers_TypeDirectEntries');

    expect(directEntries.classes()).not.toContain('transitionsCenterCard__metric--actionable');

    await directEntries.trigger('mouseenter');
    await flushRibbons();

    expect(directEntries.classes()).toContain('transitionsCenterCard__metric--highlighted');
  });

  it('should highlight the ribbon of a summary row when it is hovered', async () => {
    const wrapper = await mountLoaded();

    expect(wrapper.findAll('.transitionsRibbons__band--highlighted')).toHaveLength(0);

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('mouseenter');
    await flushRibbons();

    expect(wrapper.findAll('.transitionsRibbons__band--highlighted')).toHaveLength(1);

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('mouseleave');
    await flushRibbons();

    expect(wrapper.findAll('.transitionsRibbons__band--highlighted')).toHaveLength(0);
  });

  it('should highlight every ribbon of the open group when its metric is hovered', async () => {
    const wrapper = await mountLoaded();

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('click');
    await flushRibbons();

    const searchEngines = cardMetric(wrapper, 'Referrers_TypeSearchEngines');

    await searchEngines.trigger('mouseenter');
    await flushRibbons();

    // Both detail rows of the now-open group light up.
    expect(wrapper.findAll('.transitionsRibbons__band--highlighted')).toHaveLength(2);
  });

  it('should post switchTransitionsUrl when an internal page row is clicked', async () => {
    const wrapper = await mountLoaded();

    await wrapper.find('.transitionsRow').trigger('click');

    expect(postEvent).toHaveBeenCalledWith(
      'Transitions.switchTransitionsUrl',
      { url: 'http://example.org/a' },
    );
  });

  it('should reopen the popover instead when mounted in one', async () => {
    const wrapper = await mountLoaded({ context: 'popover' });

    await wrapper.find('.transitionsRow').trigger('click');

    // The popover is mounted through a vue-entry, which cannot pass a handler in, so this event
    // is the whole mechanism: the row action listens for it and reopens the popover.
    expect(postEvent).toHaveBeenCalledWith(
      'Transitions.reloadPopover',
      { url: 'http://example.org/a' },
    );
    expect(postEvent).not.toHaveBeenCalledWith(
      'Transitions.switchTransitionsUrl',
      expect.anything(),
    );
  });

  it('should render an external row as a link and not navigate the report', async () => {
    const wrapper = await mountLoaded();

    await summaryRow(wrapper, 'General_Outlinks').trigger('click');
    await flushRibbons();

    const row = sectionByTitle(wrapper, 'General_Outlinks')!.find('.transitionsRow');
    expect(row.element.tagName).toBe('A');
    expect(row.attributes('href')).toBe('http://other.example/x');
    expect(row.attributes('rel')).toBe('noreferrer noopener');

    await row.trigger('click');
    expect(postEvent).not.toHaveBeenCalledWith(
      'Transitions.switchTransitionsUrl',
      expect.anything(),
    );
  });

  it('should reset the open groups when the action changes', async () => {
    const wrapper = await mountLoaded();

    await summaryRow(wrapper, 'Transitions_FromSearchEngines').trigger('click');
    await flushRibbons();

    await wrapper.setProps({ actionName: 'http://example.org/other' });
    backend.respond();
    await flushRibbons();

    expect(openRowLabels(wrapper, 'Transitions_FromPreviousPages')).toEqual(['/a']);
    expect(sectionByTitle(wrapper, 'Transitions_FromSearchEngines')).toBeUndefined();
  });
});
