/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import EvolutionTrendIcon from './EvolutionTrendIcon.vue';

const UP_PATH = 'M3.77344 11L8.27344 5L12.7734 11H3.77344Z';
const DOWN_PATH = 'M3.77344 6L8.27344 12L12.7734 6H3.77344Z';

describe('CoreVisualizations/EvolutionTrendIcon.vue', () => {
  it('renders the up triangle for direction "up"', () => {
    const wrapper = mount(EvolutionTrendIcon, { props: { direction: 'up' } });

    expect(wrapper.find('path').attributes('d')).toBe(UP_PATH);
    expect(wrapper.find('rect').exists()).toBe(false);
  });

  it('renders the down triangle for direction "down"', () => {
    const wrapper = mount(EvolutionTrendIcon, { props: { direction: 'down' } });

    expect(wrapper.find('path').attributes('d')).toBe(DOWN_PATH);
    expect(wrapper.find('rect').exists()).toBe(false);
  });

  it('renders the dash (rect) and no path for direction "neutral"', () => {
    const wrapper = mount(EvolutionTrendIcon, { props: { direction: 'neutral' } });

    expect(wrapper.find('rect').exists()).toBe(true);
    expect(wrapper.find('path').exists()).toBe(false);
  });

  it('colours the shape with currentColor so it inherits the badge colour', () => {
    const wrapper = mount(EvolutionTrendIcon, { props: { direction: 'up' } });

    expect(wrapper.find('path').attributes('fill')).toBe('currentColor');
  });
});
