/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import ReportHeader from './ReportHeader.vue';

vi.mock('../translate', () => ({
  translate: (key: string) => {
    const messages: Record<string, string> = {
      Dashboard_Minimise: 'Minimise',
      Dashboard_Maximise: 'Maximise',
      General_Refresh: 'Refresh',
      General_Close: 'Close',
      General_Widget: 'Widget',
    };

    return messages[key] || key;
  },
}));

// EnrichedHeadline reaches for Matomo globals and $sanitize; this ticket only cares that it is
// rendered with the right props.
const EnrichedHeadlineStub = {
  name: 'EnrichedHeadline',
  props: ['featureName', 'inlineHelp', 'reportGenerated', 'editUrl', 'helpUrl'],
  template: '<div class="enrichedHeadline"><slot/></div>',
};

describe('ReportHeader', () => {
  function mountComponent(customProps = {}) {
    return mount(ReportHeader, {
      props: {
        context: 'dashboard',
        reportTitle: 'Visits Over Time',
        ...customProps,
      },
      global: {
        stubs: {
          EnrichedHeadline: EnrichedHeadlineStub,
        },
      },
    });
  }

  it('should render the widget title', () => {
    const wrapper = mountComponent();

    expect(wrapper.find('.reportHeader__title').text()).toBe('Visits Over Time');
  });

  it('should render the reserved (empty) toolbar anchor on the header line', () => {
    const wrapper = mountComponent();

    expect(wrapper.find('.reportHeader__header .reportHeader__toolbar').exists()).toBe(true);
  });

  it('should render the title by default', () => {
    expect(mountComponent().find('.reportHeader__title').exists()).toBe(true);
  });

  it('should render no line at all when there is nothing to show', () => {
    const wrapper = mountComponent({ context: 'widgetized', showTitle: false });

    expect(wrapper.find('.reportHeader__header').exists()).toBe(false);
    expect(wrapper.find('.reportHeader__subheader').exists()).toBe(false);
    // Leaves the host `:empty`, which the stylesheet collapses.
    expect(wrapper.find('.reportHeader').element.children.length).toBe(0);
  });

  it('should keep the subheader when only the search is left', () => {
    const wrapper = mountComponent({ context: 'widgetized', showTitle: false, showSearch: true });

    expect(wrapper.find('.reportHeader__header').exists()).toBe(false);
    expect(wrapper.find('.reportHeader__subheader .reportHeader__search').exists()).toBe(true);
  });

  it('should render the full header when a widgetized report keeps its title', () => {
    const wrapper = mountComponent({ context: 'widgetized' });

    expect(wrapper.find('.reportHeader').exists()).toBe(true);
    expect(wrapper.find('.reportHeader__title').exists()).toBe(true);
  });

  it('should show all four controls in the dashboard context', () => {
    const wrapper = mountComponent({ context: 'dashboard' });

    expect(wrapper.findAll('.widgetControls__action').length).toBe(4);
  });

  it('should show only minimise and refresh in the maximised context', () => {
    const wrapper = mountComponent({ context: 'maximised' });

    expect(wrapper.find('.widgetControls__action--minimise').exists()).toBe(true);
    expect(wrapper.find('.widgetControls__action--refresh').exists()).toBe(true);
    expect(wrapper.find('.widgetControls__action--maximise').exists()).toBe(false);
    expect(wrapper.find('.widgetControls__action--close').exists()).toBe(false);
  });

  it('should show only maximise and close in the collapsed context', () => {
    const wrapper = mountComponent({ context: 'collapsed' });

    expect(wrapper.find('.widgetControls__action--maximise').exists()).toBe(true);
    expect(wrapper.find('.widgetControls__action--close').exists()).toBe(true);
    expect(wrapper.find('.widgetControls__action--minimise').exists()).toBe(false);
    expect(wrapper.find('.widgetControls__action--refresh').exists()).toBe(false);
  });

  it('should render no controls in the preview and widgetized contexts', () => {
    expect(mountComponent({ context: 'preview' }).find('.widgetControls').exists()).toBe(false);
    expect(mountComponent({ context: 'widgetized' }).find('.widgetControls').exists()).toBe(false);
  });

  it('should re-emit control intents from the row', async () => {
    const wrapper = mountComponent({ context: 'dashboard' });

    await wrapper.find('.widgetControls__action--refresh').trigger('click');

    expect(wrapper.emitted('refresh')).toBeTruthy();
  });

  it('should dispatch a bubbling widgetcontrol:* CustomEvent for the jQuery bridge', async () => {
    const wrapper = mountComponent({ context: 'dashboard' });
    const received: string[] = [];
    wrapper.element.addEventListener('widgetcontrol:maximise', () => received.push('maximise'));

    await wrapper.find('.widgetControls__action--maximise').trigger('click');

    expect(received).toEqual(['maximise']);
  });

  it('should mark the title clickable and emit titleClick when clickable', async () => {
    const wrapper = mountComponent({ context: 'preview', titleClickable: true });

    const title = wrapper.find('.reportHeader__title');
    expect(title.classes()).toContain('reportHeader__title--clickable');
    expect(title.attributes('role')).toBe('button');

    await title.trigger('click');
    expect(wrapper.emitted('titleClick')).toBeTruthy();
  });

  it('should not make the title clickable by default', () => {
    const wrapper = mountComponent();

    const title = wrapper.find('.reportHeader__title');
    expect(title.classes()).not.toContain('reportHeader__title--clickable');
    expect(title.attributes('role')).toBeUndefined();
  });

  it('should render no controls in the fullPage context', () => {
    const wrapper = mountComponent({ context: 'fullPage' });

    expect(wrapper.find('.widgetControls').exists()).toBe(false);
    expect(wrapper.findAll('.widgetControls__action').length).toBe(0);
  });

  it('should not announce a full-page report as a widget', () => {
    expect(mountComponent().find('.u-visuallyHidden').exists()).toBe(true);
    expect(mountComponent({ context: 'fullPage' }).find('.u-visuallyHidden').exists()).toBe(false);
  });

  it('should render the title as an h3 by default and as an h2 when asked', () => {
    expect(mountComponent().find('.reportHeader__title').element.tagName).toBe('H3');
    expect(
      mountComponent({ headingLevel: 'h2' }).find('.reportHeader__title').element.tagName,
    ).toBe('H2');
  });

  it('should fall back to an h3 for an unexpected heading level', () => {
    const wrapper = mountComponent({ headingLevel: 'script' });

    expect(wrapper.find('.reportHeader__title').element.tagName).toBe('H3');
  });

  it('should keep the plain .widgetName > span markup when not enriched', () => {
    const wrapper = mountComponent();

    expect(wrapper.find('.enrichedHeadline').exists()).toBe(false);
    expect(wrapper.find('.reportHeader__title.widgetName > span').text()).toBe('Visits Over Time');
  });

  it('should render the title through EnrichedHeadline when enriched', () => {
    const wrapper = mountComponent({
      context: 'fullPage',
      headingLevel: 'h2',
      enriched: true,
      featureName: 'Pages',
      inlineHelp: 'What this report shows',
      reportGenerated: 'generated 5 min ago',
      editUrl: 'index.php?module=Foo',
      helpUrl: 'https://matomo.org/guide',
    });

    const headline = wrapper.findComponent(EnrichedHeadlineStub);
    expect(headline.exists()).toBe(true);
    expect(headline.props()).toEqual({
      featureName: 'Pages',
      // the documentation is plain text; EnrichedHeadline's help panel is styled for a paragraph
      inlineHelp: '<p>What this report shows</p>',
      reportGenerated: 'generated 5 min ago',
      editUrl: 'index.php?module=Foo',
      helpUrl: 'https://matomo.org/guide',
    });
    expect(wrapper.find('.reportHeader__title.widgetName .enrichedHeadline span').text())
      .toBe('Visits Over Time');
  });

  it('should enrich a report with no help too, so it keeps its rating icons', () => {
    // every full-page report used to mount EnrichedHeadline, help text or not
    const wrapper = mountComponent({ context: 'fullPage', enriched: true });

    expect(wrapper.find('.enrichedHeadline').exists()).toBe(true);
    expect(wrapper.findComponent(EnrichedHeadlineStub).props('inlineHelp')).toBe('');
  });

  it('should leave the rated feature name to EnrichedHeadline by default', () => {
    // the report passes no feature name, so EnrichedHeadline names it after the rendered title,
    // which is the widget name WidgetLoader injected
    const wrapper = mountComponent({ context: 'fullPage', enriched: true });

    expect(wrapper.findComponent(EnrichedHeadlineStub).props('featureName')).toBe('');
  });

  it('should still render a heading passed through the deprecated title prop', () => {
    // a plugin written against the 5.x component only knows `title`
    const wrapper = mount(ReportHeader, {
      props: { context: 'dashboard', title: 'Visits Over Time' },
    });

    expect(wrapper.find('.reportHeader__title').text()).toBe('Visits Over Time');
  });

  it('should prefer reportTitle when both are given', () => {
    const wrapper = mountComponent({ title: 'Old name' });

    expect(wrapper.find('.reportHeader__title').text()).toBe('Visits Over Time');
  });

  it('should not cancel the activation of a link inside the title', async () => {
    // EnrichedHeadline renders links in the heading (editable title, external help, read more);
    // the heading's own key handlers must not preventDefault on those
    const wrapper = mountComponent({
      context: 'fullPage',
      enriched: true,
      editUrl: 'index.php?module=Foo',
    });

    const link = document.createElement('a');
    link.href = '#somewhere';
    wrapper.find('.reportHeader__title').element.appendChild(link);

    const event = new KeyboardEvent('keydown', { key: 'Enter', bubbles: true, cancelable: true });
    link.dispatchEvent(event);

    expect(event.defaultPrevented).toBe(false);
    expect(wrapper.emitted('titleClick')).toBeUndefined();
  });

  it('should keep the plain heading metrics for a report shown without a card', () => {
    // the two are exclusive: a report matches either the card title or the plain heading it had
    const plain = mountComponent({ context: 'fullPage', plainTitle: true });

    expect(plain.classes()).toContain('reportHeader--plainTitle');
    expect(plain.classes()).not.toContain('reportHeader--flush');

    const inCard = mountComponent({ context: 'fullPage' });

    expect(inCard.classes()).toContain('reportHeader--flush');
    expect(inCard.classes()).not.toContain('reportHeader--plainTitle');
  });

  it('should add the flush modifier only for a full-page report', () => {
    expect(mountComponent().classes()).not.toContain('reportHeader--flush');
    expect(mountComponent({ context: 'fullPage' }).classes()).toContain('reportHeader--flush');
  });

  describe('report search', () => {
    it('should not render the search input by default', () => {
      expect(mountComponent().find('.reportHeader__search').exists()).toBe(false);
    });

    it('should render the search input when showSearch is set', () => {
      const wrapper = mountComponent({ showSearch: true });

      expect(wrapper.find('.reportHeader__search .mtm-searchInput__input').exists()).toBe(true);
    });

    it('should not render the search input on a minimised widget', () => {
      // the dashboard hides .widgetContent in this state, so there is no table left to search
      const wrapper = mountComponent({ context: 'collapsed', showSearch: true });

      expect(wrapper.find('.reportHeader__search').exists()).toBe(false);
    });

    it('should not render the search input in the widget preview', () => {
      const wrapper = mountComponent({ context: 'preview', showSearch: true });

      expect(wrapper.find('.reportHeader__search').exists()).toBe(false);
    });

    it('should render the search input on a titleless widgetized report', () => {
      const wrapper = mountComponent({ context: 'widgetized', showTitle: false, showSearch: true });

      expect(wrapper.find('.reportHeader__search .mtm-searchInput__input').exists()).toBe(true);
    });

    it('should seed the search field from searchQuery', () => {
      const wrapper = mountComponent({ showSearch: true, searchQuery: 'pages' });

      const input = wrapper.find('.mtm-searchInput__input').element as HTMLInputElement;
      expect(input.value).toBe('pages');
    });

    it('should debounce typing into a single search dispatch', async () => {
      vi.useFakeTimers();
      try {
        const wrapper = mountComponent({ showSearch: true });
        const input = wrapper.find('.mtm-searchInput__input');

        await input.setValue('a');
        await input.setValue('ab');
        // nothing dispatched within the debounce window
        expect(wrapper.emitted('search')).toBeUndefined();

        vi.advanceTimersByTime(300);
        expect(wrapper.emitted('search')).toEqual([[{ keyword: 'ab' }]]);
      } finally {
        vi.useRealTimers();
      }
    });

    it('should dispatch a bubbling reportheader:search CustomEvent for the jQuery bridge', async () => {
      vi.useFakeTimers();
      try {
        const wrapper = mountComponent({ showSearch: true });
        const received: string[] = [];
        wrapper.element.addEventListener('reportheader:search', (e) => {
          received.push((e as CustomEvent).detail.keyword);
        });

        await wrapper.find('.mtm-searchInput__input').setValue('term');
        vi.advanceTimersByTime(300);

        expect(received).toEqual(['term']);
      } finally {
        vi.useRealTimers();
      }
    });

    it('should apply a clear immediately, without waiting for the debounce', async () => {
      const wrapper = mountComponent({ showSearch: true, searchQuery: 'term' });

      await wrapper.find('.mtm-searchInput__clear').trigger('click');

      expect(wrapper.emitted('search')).toEqual([[{ keyword: '' }]]);
    });

    it('should sync the field from searchQuery without dispatching a search', async () => {
      const wrapper = mountComponent({ showSearch: true });

      await wrapper.setProps({ searchQuery: 'pushed' });

      const input = wrapper.find('.mtm-searchInput__input').element as HTMLInputElement;
      expect(input.value).toBe('pushed');
      expect(wrapper.emitted('search')).toBeUndefined();
    });

    it('should release a pending search when the header is torn down', async () => {
      vi.useFakeTimers();
      try {
        const wrapper = mountComponent({ showSearch: true });
        const input = wrapper.find('.mtm-searchInput__input');

        (input.element as HTMLInputElement).value = 'gone';
        await input.trigger('input');
        expect(vi.getTimerCount()).toBe(1);

        wrapper.unmount();

        // a reload replaces the table under a header that stays put, so a debounce left running
        // here would outlive the component it belongs to
        expect(vi.getTimerCount()).toBe(0);
      } finally {
        vi.useRealTimers();
      }
    });

    it('should not let a server-pushed query revert what is being typed', async () => {
      vi.useFakeTimers();
      try {
        const wrapper = mountComponent({ showSearch: true });
        const input = wrapper.find('.mtm-searchInput__input');

        (input.element as HTMLInputElement).value = 'typing';
        await input.trigger('input');

        // a reload settling mid-debounce syncs the pattern it was started with
        await wrapper.setProps({ searchQuery: 'stale' });

        expect((input.element as HTMLInputElement).value).toBe('typing');

        vi.runAllTimers();
        await wrapper.vm.$nextTick();
        expect(wrapper.emitted('search')).toEqual([[{ keyword: 'typing' }]]);
      } finally {
        vi.useRealTimers();
      }
    });

  });
});
