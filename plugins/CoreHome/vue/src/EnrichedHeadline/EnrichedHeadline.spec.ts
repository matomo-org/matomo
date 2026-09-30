/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import EnrichedHeadline from './EnrichedHeadline.vue';

vi.mock('../translate', () => ({
  translate: (key: string) => key,
  translateOrDefault: (key: string) => key,
}));

function createWrapper(props: Record<string, unknown> = {}) {
  return mount(EnrichedHeadline, {
    props,
    slots: {
      default: '<span>Pages</span>',
    },
    global: {
      config: {
        globalProperties: {
          translate: (key: string) => key,
          $sanitize: (value: string) => value,
        } as never,
      },
    },
  });
}

describe('EnrichedHeadline', () => {
  afterEach(() => {
    document.body.innerHTML = '';
  });

  it('should take the inline help from its prop', async () => {
    const wrapper = createWrapper({ inlineHelp: '<p>What this report shows</p>' });

    expect(wrapper.find('.enrichedHeadline__helpIcon .icon-info').exists()).toBe(true);

    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');

    expect(wrapper.find('.mtm-helpPanel').html()).toContain('What this report shows');
  });

  it('should not read the documentation out of an adjacent DataTable', () => {
    // the owner passes the documentation in; the old DOM scrape is gone
    document.body.innerHTML = '<div class="reportDocumentation" data-content="scraped"></div>';

    const wrapper = createWrapper();

    expect(wrapper.find('.enrichedHeadline__helpIcon').exists()).toBe(false);
    expect(wrapper.find('.mtm-helpPanel').exists()).toBe(false);
  });

  it('should follow the inlineHelp prop when the owner swaps in another report', async () => {
    const wrapper = createWrapper({ inlineHelp: '<p>first</p>' });
    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');

    await wrapper.setProps({ inlineHelp: '<p>second</p>' });

    expect(wrapper.find('.mtm-helpPanel').html()).toContain('second');
    expect(wrapper.find('.mtm-helpPanel').html()).not.toContain('first');
  });

  it('should follow the featureName prop, so feedback is filed under the current report', async () => {
    const wrapper = createWrapper({ featureName: 'Pages' });

    await wrapper.setProps({ featureName: 'Entry Pages' });

    expect(wrapper.vm.actualFeatureName).toBe('Entry Pages');
  });

  it('should close an open help popup when the new report has no documentation', async () => {
    const wrapper = createWrapper({ inlineHelp: '<p>first</p>' });

    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');
    expect(wrapper.vm.showInlineHelp).toBe(true);

    await wrapper.setProps({ inlineHelp: '' });

    expect(wrapper.vm.showInlineHelp).toBe(false);
    expect(wrapper.find('.enrichedHeadline__helpIcon').exists()).toBe(false);
  });

  it('should show the archived-on date in the help panel', async () => {
    const wrapper = createWrapper({
      inlineHelp: '<p>What this report shows</p>',
      reportGenerated: 'Report generated on Jul 30, 2026 (UTC)',
    });
    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');

    expect(wrapper.find('.mtm-helpPanel .mtm-helpPanel__date').text())
      .toBe('Report generated on Jul 30, 2026 (UTC)');
  });

  it('should render no date element when there is no archived-on date', async () => {
    // the empty string is what a report without an archived-on date passes; anything else,
    // including null, would render an empty date element
    const wrapper = createWrapper({
      inlineHelp: '<p>What this report shows</p>',
      reportGenerated: '',
    });
    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');

    expect(wrapper.find('.mtm-helpPanel .mtm-helpPanel__date').exists()).toBe(false);
  });

  it('should fall back to the rendered title for the feature name', () => {
    const wrapper = createWrapper();

    expect(wrapper.vm.actualFeatureName).toBe('Pages');
  });

  it('should keep the report name readable from the bare `title` class', () => {
    // the selector and the read the ReportSorter plugin makes:
    // $(widget).find('.enrichedHeadline .title').first().text()
    const wrapper = createWrapper();

    expect(wrapper.find('.enrichedHeadline .title').text()).toBe('Pages');
  });

  it('should keep the help panel inside the headline when no container is given', async () => {
    const wrapper = createWrapper({ inlineHelp: '<p>What this report shows</p>' });

    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');

    expect(wrapper.find('.enrichedHeadline__help > .mtm-helpPanel').exists()).toBe(true);
  });

  it('should move the help panel into the container the host provides', async () => {
    const container = document.createElement('div');
    document.body.appendChild(container);

    const wrapper = createWrapper({
      inlineHelp: '<p>What this report shows</p>',
      helpContainer: container,
    });

    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');

    expect(wrapper.find('.enrichedHeadline__help > .mtm-helpPanel').exists()).toBe(false);
    expect(container.querySelector('.mtm-helpPanel')?.innerHTML).toContain('What this report shows');
  });

  it('should leave its nest element empty until the panel is opened', async () => {
    // both nest elements collapse on `:empty`, so a closed panel must leave no child behind
    const wrapper = createWrapper({ inlineHelp: '<p>What this report shows</p>' });
    const nest = () => wrapper.find('.enrichedHeadline__help').element;

    expect(nest().children.length).toBe(0);

    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');

    expect(nest().children.length).toBe(1);
  });

  it('should still open and close a help panel it has handed to a container', async () => {
    const container = document.createElement('div');
    document.body.appendChild(container);

    const wrapper = createWrapper({
      inlineHelp: '<p>What this report shows</p>',
      helpContainer: container,
    });

    expect(container.querySelector('.mtm-helpPanel')).toBe(null);

    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');

    expect(container.querySelector('.mtm-helpPanel')).not.toBe(null);

    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');

    expect(container.querySelector('.mtm-helpPanel')).toBe(null);
    expect(container.children.length).toBe(0);
  });

  it('should hand the panel over and take it back as the host offers a container', async () => {
    const container = document.createElement('div');
    document.body.appendChild(container);

    const wrapper = createWrapper({ inlineHelp: '<p>What this report shows</p>' });
    await wrapper.find('.enrichedHeadline__helpIcon').trigger('click');

    expect(wrapper.find('.enrichedHeadline__help > .mtm-helpPanel').exists()).toBe(true);

    await wrapper.setProps({ helpContainer: container });

    expect(wrapper.find('.enrichedHeadline__help > .mtm-helpPanel').exists()).toBe(false);
    expect(container.querySelector('.mtm-helpPanel')).not.toBe(null);

    // and back, for a related report whose row goes away with its documentation
    await wrapper.setProps({ helpContainer: null });

    expect(container.querySelector('.mtm-helpPanel')).toBe(null);
    expect(wrapper.find('.enrichedHeadline__help > .mtm-helpPanel').exists()).toBe(true);
  });
});
