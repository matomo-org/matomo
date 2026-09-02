/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import ReportHeader from './ReportHeader.vue';
import ExportMenu from '../DataTable/ExportMenu.vue';

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

  it('should render the toolbar anchor on the header line', () => {
    const wrapper = mountComponent();

    expect(wrapper.find('.reportHeader__header .reportHeader__toolbar').exists()).toBe(true);
  });

  describe('report actions menu', () => {
    // The menu only exists where the report renders footer icons; a subtable has none. It also
    // needs something to hold, or there is nothing for a trigger to open.
    const withActions = { showFooter: true, showFooterIcons: true, showExport: true };

    it('should offer the actions menu only when the report has footer icons', () => {
      expect(mountComponent().find('.reportHeader__actionsTrigger').exists()).toBe(false);
      expect(mountComponent({ showFooter: true }).find('.reportHeader__actionsTrigger').exists())
        .toBe(false);
      expect(mountComponent(withActions).find('.reportHeader__actionsTrigger').exists()).toBe(true);
    });

    it('should keep the header line for a titleless report that still has actions', () => {
      const wrapper = mountComponent({ ...withActions, showTitle: false, context: 'widgetized' });

      expect(wrapper.find('.reportHeader__header').exists()).toBe(true);
      expect(wrapper.find('.reportHeader__title').exists()).toBe(false);
    });

    // ExpandOnClick closes only on a click outside the element, so without an explicit close the
    // menu stays open over the report the chosen action just reloaded.
    it('should close the menu when an action inside it is chosen', async () => {
      const wrapper = mountComponent(withActions);
      const actions = wrapper.find('.reportHeader__actions');

      actions.element.classList.add('reportHeader__actions--expanded');
      expect(actions.classes()).toContain('reportHeader__actions--expanded');

      await wrapper.find('.reportHeader__actionsMenu').trigger('click');

      expect(wrapper.find('.reportHeader__actions').classes())
        .not.toContain('reportHeader__actions--expanded');
    });

    // Closing this way bypasses ExpandOnClick, whose own close() then returns early for want of
    // the class - so a trigger left saying "expanded" here never gets told otherwise again.
    it('should stop saying the menu is open once an action closes it', async () => {
      const wrapper = mountComponent(withActions);
      const trigger = wrapper.find('.reportHeader__actionsTrigger');
      expect(trigger.attributes('aria-haspopup')).toBe('menu');
      expect(trigger.attributes('aria-expanded')).toBe('false');

      // ExpandOnClick binds the expander in a timeout, so the click has to come after it
      await new Promise((resolve) => { setTimeout(resolve, 0); });
      await trigger.trigger('click');
      expect(trigger.attributes('aria-expanded')).toBe('true');

      await wrapper.find('.reportHeader__actionsMenu').trigger('click');
      expect(trigger.attributes('aria-expanded')).toBe('false');
    });

    // The promoted panels announced role="menu" and answered no arrow key until the composable
    // served them.
    it('should walk a promoted panel with the arrow keys', async () => {
      const wrapper = mountComponent({
        showFooter: true,
        showFooterIcons: true,
        showPeriods: true,
        selectablePeriods: ['day', 'week', 'month'],
        context: 'widgetized',
      });
      document.body.appendChild(wrapper.element);
      (wrapper.vm as unknown as { promotedCount: number }).promotedCount = 1;
      await wrapper.vm.$nextTick();

      const panel = wrapper.find('[data-report-action="periods"] .mtm-selector__dropdown');
      const items = panel.findAll('[role^="menuitem"]').map((item) => item.element);
      expect(items.length).toBe(3);

      await panel.trigger('keydown', { key: 'ArrowDown' });
      expect(document.activeElement).toBe(items[0]);

      await panel.trigger('keydown', { key: 'End' });
      expect(document.activeElement).toBe(items[2]);

      wrapper.unmount();
    });

    // Picking an entry folds the panel without the directive hearing it, so the focus has to be
    // handed back by hand - and only to the keyboard that asked for it.
    it('should give the focus back when the keyboard picks from a promoted panel', async () => {
      const wrapper = mountComponent({
        showFooter: true,
        showFooterIcons: true,
        showPeriods: true,
        selectablePeriods: ['day', 'week'],
        context: 'widgetized',
      });
      document.body.appendChild(wrapper.element);
      (wrapper.vm as unknown as { promotedCount: number }).promotedCount = 1;
      await wrapper.vm.$nextTick();

      const control = wrapper.find('[data-report-action="periods"]');
      const trigger = control.find('.mtm-selector__trigger').element as HTMLElement;
      const entry = control.find('[role^="menuitem"]').element as HTMLElement;

      entry.focus();
      expect(document.activeElement).toBe(entry);

      // A keyboard-activated click reports no pointer, which is `detail: 0` - the default here,
      // and not something trigger() can set on a read-only UIEvent.
      entry.dispatchEvent(new MouseEvent('click', { bubbles: true }));
      await wrapper.vm.$nextTick();

      expect(document.activeElement).toBe(trigger);

      wrapper.unmount();
    });

    // The composable serves the keys now, so the panel is where they are pinned.
    it('should walk the menu with the arrow keys', async () => {
      const wrapper = mountComponent({ ...withActions, showAnnotations: true });
      document.body.appendChild(wrapper.element);

      const menu = wrapper.find('.reportHeader__actionsMenu');
      const items = wrapper.findAll('[role^="menuitem"]').map((item) => item.element);
      expect(items.length).toBeGreaterThan(1);

      await menu.trigger('keydown', { key: 'ArrowDown' });
      expect(document.activeElement).toBe(items[0]);

      await menu.trigger('keydown', { key: 'ArrowDown' });
      expect(document.activeElement).toBe(items[1]);

      await menu.trigger('keydown', { key: 'End' });
      expect(document.activeElement).toBe(items[items.length - 1]);

      // and round, so the list has no dead end
      await menu.trigger('keydown', { key: 'ArrowDown' });
      expect(document.activeElement).toBe(items[0]);

      wrapper.unmount();
    });

    // Promoting empties the menu on a report whose only entries were promotable, and a trigger
    // opening onto nothing is worse than no trigger.
    it('should offer no trigger once the menu has nothing left to hold', async () => {
      const wrapper = mountComponent({
        ...withActions,
        showAnnotations: true,
        context: 'widgetized',
      });
      expect(wrapper.find('.reportHeader__actionsTrigger').exists()).toBe(true);

      (wrapper.vm as unknown as { promotedCount: number }).promotedCount = 2;
      await wrapper.vm.$nextTick();

      expect(wrapper.findAll('[data-report-action]').length).toBe(2);
      expect(wrapper.find('.reportHeader__actionsTrigger').exists()).toBe(false);
    });
  });

  // jsdom lays nothing out, so the fit measurement always demotes; these set the count the
  // measurement would have reached and check what gets drawn at it.
  describe('promoted report actions', () => {
    const offered = {
      showFooter: true,
      showFooterIcons: true,
      showExport: true,
      showExportAsImageIcon: true,
      showAnnotations: true,
      exportSupportsFlatten: true,
      clientSideParameters: { flat: '1' },
    };

    async function mountPromoted(count: number, customProps = {}) {
      const wrapper = mountComponent({ ...offered, ...customProps });
      (wrapper.vm as unknown as { promotedCount: number }).promotedCount = count;
      await wrapper.vm.$nextTick();
      return wrapper;
    }

    it('should draw the export control as a panel, since it holds two entries', async () => {
      const wrapper = await mountPromoted(1);

      const control = wrapper.find('[data-report-action="export"]');
      expect(control.exists()).toBe(true);
      expect(control.find('.mtm-selector__trigger').attributes('aria-haspopup')).toBe('menu');
      expect(control.find('[role="menu"]').exists()).toBe(true);
      expect(control.find('a.activateExportSelection').exists()).toBe(true);
      expect(control.find('a.dataTableAction.tableIcon').exists()).toBe(true);

      // the entries rendering is not enough: what the export directive is handed decides whether
      // the popover offers a flat export at all
      expect(wrapper.findComponent(ExportMenu).props()).toMatchObject({
        exportSupportsFlatten: true,
        clientSideParameters: { flat: '1' },
      });
    });

    it('should draw the annotations control as one icon, since it is one toggle', async () => {
      const wrapper = await mountPromoted(2);

      const control = wrapper.find('[data-report-action="annotations"]');
      expect(control.classes()).toContain('mtm-selector--iconOnly');
      expect(control.find('.mtm-selector__label').exists()).toBe(false);

      // no words, so the state and the name are carried by the button itself
      const button = control.find('button');
      expect(button.classes()).toContain('annotationView');
      expect(button.attributes('aria-pressed')).toBe('false');
      expect(button.attributes('aria-label')).toBe('Annotations_ShowAnnotations');
    });

    // jsdom has no matchMedia and lays nothing out, so the fit measurement is handed both.
    let narrow = false;

    beforeEach(() => {
      narrow = false;
      Object.defineProperty(window, 'matchMedia', {
        writable: true,
        configurable: true,
        value: () => ({ matches: narrow }) as MediaQueryList,
      });
    });
    // jsdom lays nothing out, so the fit measurement is handed the widths it would have read.
    function giveRoom(wrapper: ReturnType<typeof mountComponent>, width: number) {
      const size = (selector: string, prop: string, value: number) => {
        Object.defineProperty(wrapper.find(selector).element, prop, {
          value, configurable: true,
        });
      };
      size('.reportHeader__header', 'clientWidth', width);
      size('.reportHeader__controls', 'offsetWidth', 100);
    }

    // The observer skips a width it has already measured, so a demotion that kept the width it was
    // promoted at could never be undone by returning to it - restoring a window, undoing a zoom.
    it('should remember the width it demoted at, not the one it promoted at', async () => {
      const wrapper = mountComponent({ ...offered, context: 'widgetized' });
      const vm = wrapper.vm as unknown as {
        updatePromoted: () => Promise<void>; promotedCount: number; lastMeasuredWidth: number;
      };

      giveRoom(wrapper, 1200);
      await vm.updatePromoted();
      expect(vm.promotedCount).toBeGreaterThan(0);
      expect(vm.lastMeasuredWidth).toBe(1200);

      narrow = true;
      giveRoom(wrapper, 700);
      await vm.updatePromoted();

      expect(vm.promotedCount).toBe(0);
      expect(vm.lastMeasuredWidth).toBe(700);
    });

    // Maximising a widget changes what shares the line without changing its width.
    it('should give the controls back when the line gains widget controls', async () => {
      const wrapper = mountComponent({ ...offered, context: 'widgetized' });
      const vm = wrapper.vm as unknown as {
        updatePromoted: () => Promise<void>; promotedCount: number;
      };

      giveRoom(wrapper, 1200);
      await vm.updatePromoted();
      expect(vm.promotedCount).toBeGreaterThan(0);

      await wrapper.setProps({ context: 'dashboard' });
      await wrapper.vm.$nextTick();

      expect(vm.promotedCount).toBe(0);
    });

    // Only the template's order enforces this, so re-reversing it must fail something.
    it('should draw the highest rank nearest the trigger', async () => {
      const wrapper = await mountPromoted(3, { showPeriods: true, selectablePeriods: ['day'] });

      expect(wrapper.findAll('[data-report-action]')
        .map((control) => control.attributes('data-report-action')))
        .toEqual(['annotations', 'export', 'periods']);
    });

    // The fit loop gives controls back one at a time, and an unmounted one never hears onClosed.
    it('should stop announcing a control the fit loop took back while it was open', async () => {
      const wrapper = mountComponent({
        ...offered,
        context: 'widgetized',
        showPeriods: true,
        selectablePeriods: ['day'],
      });
      const vm = wrapper.vm as unknown as {
        updatePromoted: () => Promise<void>;
        promotedCount: number;
        promotedExport: { expanded: boolean };
      };

      giveRoom(wrapper, 1200);
      await vm.updatePromoted();
      expect(vm.promotedCount).toBeGreaterThan(1);

      vm.promotedExport.expanded = true;
      giveRoom(wrapper, 200);
      await vm.updatePromoted();

      expect(vm.promotedCount).toBe(0);
      expect(vm.promotedExport.expanded).toBe(false);
    });

    // The trigger goes away with the menu it opens, and never hears onClosed either.
    it('should stop announcing the menu when the trigger goes away with it', async () => {
      const wrapper = mountComponent({
        showFooter: true,
        showFooterIcons: true,
        showExport: true,
        showAnnotations: true,
        context: 'widgetized',
      });
      const vm = wrapper.vm as unknown as {
        updatePromoted: () => Promise<void>; actionsSelector: { expanded: boolean };
      };

      vm.actionsSelector.expanded = true;
      giveRoom(wrapper, 1200);
      await vm.updatePromoted();
      await wrapper.vm.$nextTick();

      expect(wrapper.find('.reportHeader__actionsTrigger').exists()).toBe(false);
      expect(vm.actionsSelector.expanded).toBe(false);
    });

    it('should promote in priority order, so the least deserving is given back first', async () => {
      const one = await mountPromoted(1);
      expect(one.find('[data-report-action="export"]').exists()).toBe(true);
      expect(one.find('[data-report-action="annotations"]').exists()).toBe(false);

      const none = await mountPromoted(0);
      expect(none.find('[data-report-action="export"]').exists()).toBe(false);
    });
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
