/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { mount } from '@vue/test-utils';
import StarToggle from './StarToggle.vue';
import STAR_TOGGLE_SHAPE from './starShape';

describe('CoreHome/StarToggle', () => {
  it('renders an unstarred button with the title as tooltip and accessible name', () => {
    const wrapper = mount(StarToggle, { props: { title: 'Add to Starred' } });

    const button = wrapper.find('button');
    expect(button.exists()).toBe(true);
    expect(button.attributes('type')).toBe('button');
    expect(button.attributes('title')).toBe('Add to Starred');
    expect(button.attributes('aria-label')).toBe('Add to Starred');
    expect(button.attributes('aria-pressed')).toBe('false');
    expect(button.classes()).toContain('starToggle');
    expect(button.classes()).not.toContain('starToggle--starred');
  });

  it('marks a starred button as pressed', () => {
    const wrapper = mount(StarToggle, { props: { starred: true, title: 'Remove from Starred' } });

    const button = wrapper.find('button');
    expect(button.attributes('aria-pressed')).toBe('true');
    expect(button.classes()).toContain('starToggle--starred');
  });

  it('emits the requested state when clicked and leaves the state to the parent', async () => {
    const wrapper = mount(StarToggle, { props: { starred: false } });

    await wrapper.find('button').trigger('click');

    expect(wrapper.emitted('toggle')).toEqual([[true]]);
    expect(wrapper.find('button').attributes('aria-pressed')).toBe('false');

    await wrapper.setProps({ starred: true });
    await wrapper.find('button').trigger('click');

    expect(wrapper.emitted('toggle')).toEqual([[true], [false]]);
  });

  it('animates when the starred state changes and stops when the animation ends', async () => {
    const wrapper = mount(StarToggle, { props: { starred: false } });

    await wrapper.setProps({ starred: true });
    expect(wrapper.find('button').classes()).toContain('starToggle--animateStar');

    await wrapper.find('button').trigger('animationend');
    expect(wrapper.find('button').classes()).not.toContain('starToggle--animateStar');

    await wrapper.setProps({ starred: false });
    expect(wrapper.find('button').classes()).toContain('starToggle--animateUnstar');
  });

  it('is no button and cannot be toggled when read-only', async () => {
    const wrapper = mount(StarToggle, {
      props: { starred: true, readonly: true, title: 'Starred goal' },
    });

    expect(wrapper.find('button').exists()).toBe(false);

    const indicator = wrapper.find('span.starToggle');
    expect(indicator.classes()).toContain('starToggle--readonly');
    expect(indicator.classes()).toContain('starToggle--starred');
    expect(indicator.attributes('role')).toBe('img');
    expect(indicator.attributes('title')).toBe('Starred goal');
    expect(indicator.attributes('aria-label')).toBe('Starred goal');
    expect(indicator.attributes('tabindex')).toBeUndefined();

    await indicator.trigger('click');
    expect(wrapper.emitted('toggle')).toBeUndefined();
  });

  it('hides a read-only star without a title from assistive technology', () => {
    const wrapper = mount(StarToggle, { props: { readonly: true } });

    const indicator = wrapper.find('span.starToggle');
    expect(indicator.attributes('title')).toBeUndefined();
    expect(indicator.attributes('role')).toBeUndefined();
    expect(indicator.attributes('aria-hidden')).toBe('true');
  });

  it('renders the same star as the Segments page', () => {
    const wrapper = mount(StarToggle);
    expect(wrapper.find('path').attributes('d')).toBe(STAR_TOGGLE_SHAPE);

    // the Segments page is rendered on the server and has its own copy of the star
    const segmentsTemplate = readFileSync(
      resolve(process.cwd(), 'plugins/SegmentEditor/templates/manageSegments.twig'),
      'utf8',
    );
    expect(segmentsTemplate).toContain(`d="${STAR_TOGGLE_SHAPE}"`);
  });
});
