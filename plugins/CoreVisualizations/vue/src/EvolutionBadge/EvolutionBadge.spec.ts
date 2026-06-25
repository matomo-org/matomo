/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import EvolutionBadge from './EvolutionBadge.vue';

function mountBadge(props: Record<string, unknown>) {
  return mount(EvolutionBadge, { props });
}

describe('CoreVisualizations/EvolutionBadge.vue', () => {
  it('renders an increase as a positive (green) badge pointing up', () => {
    const wrapper = mountBadge({ percent: 4 });

    expect(wrapper.vm.direction).toBe('up');
    expect(wrapper.classes()).toContain('evolutionBadge');
    expect(wrapper.classes()).toContain('evolutionBadge--positive');
    expect(wrapper.find('.evolutionBadge__icon path').exists()).toBe(true);
    expect(wrapper.find('.evolutionBadge__value').text()).toBe('+4%');
  });

  it('renders a decrease as a negative (red) badge pointing down', () => {
    const wrapper = mountBadge({ percent: -4 });

    expect(wrapper.vm.direction).toBe('down');
    expect(wrapper.classes()).toContain('evolutionBadge--negative');
    expect(wrapper.find('.evolutionBadge__icon path').exists()).toBe(true);
    expect(wrapper.find('.evolutionBadge__value').text()).toBe('-4%');
  });

  it('renders no change as a neutral (grey) badge with a dash', () => {
    const wrapper = mountBadge({ percent: 0 });

    expect(wrapper.vm.direction).toBe('neutral');
    expect(wrapper.classes()).toContain('evolutionBadge--neutral');
    // the neutral state uses a dash (rect), not an arrow (path)
    expect(wrapper.find('.evolutionBadge__icon rect').exists()).toBe(true);
    expect(wrapper.find('.evolutionBadge__icon path').exists()).toBe(false);
    expect(wrapper.find('.evolutionBadge__value').text()).toBe('0%');
  });

  it('inverts the colour when isLowerValueBetter, keeping the arrow direction', () => {
    const increased = mountBadge({ percent: 4, isLowerValueBetter: true });
    // value went up, but for a lower-is-better metric that is a bad change
    expect(increased.vm.direction).toBe('up');
    expect(increased.classes()).toContain('evolutionBadge--negative');

    const decreased = mountBadge({ percent: -4, isLowerValueBetter: true });
    // value went down, which is a good change for a lower-is-better metric
    expect(decreased.vm.direction).toBe('down');
    expect(decreased.classes()).toContain('evolutionBadge--positive');
  });

  it('uses the trend prop as the authoritative direction over the percent sign', () => {
    // a negative trend wins even though the percent string carries no sign
    const wrapper = mountBadge({ percent: '4%', trend: -10 });

    expect(wrapper.vm.direction).toBe('down');
    expect(wrapper.classes()).toContain('evolutionBadge--negative');
    // direction is down, so no leading "+" is added to the formatted value
    expect(wrapper.find('.evolutionBadge__value').text()).toBe('4%');
  });

  it('accepts a pre-formatted percent string and prefixes a "+" for increases', () => {
    const wrapper = mountBadge({ percent: '4%' });

    expect(wrapper.vm.direction).toBe('up');
    expect(wrapper.find('.evolutionBadge__value').text()).toBe('+4%');
  });

  it('keeps an existing sign on a pre-formatted negative percent string', () => {
    const wrapper = mountBadge({ percent: '-4%' });

    expect(wrapper.vm.direction).toBe('down');
    expect(wrapper.find('.evolutionBadge__value').text()).toBe('-4%');
  });

  it('reads a localised minus (U+2212) as a decrease when no trend is given', () => {
    // fi/sv/sl/et and others format negatives with U+2212 "−", not an ASCII hyphen
    const wrapper = mountBadge({ percent: '\u22124%' });

    expect(wrapper.vm.direction).toBe('down');
    expect(wrapper.classes()).toContain('evolutionBadge--negative');
    // the localised sign is kept as-is, with no spurious "+" prepended
    expect(wrapper.find('.evolutionBadge__value').text()).toBe('\u22124%');
  });

  it('exposes the tooltip as the title attribute and omits it when empty', () => {
    const withTooltip = mountBadge({ percent: 4, tooltip: '10 visits vs 8 visits' });
    expect(withTooltip.attributes('title')).toBe('10 visits vs 8 visits');

    const withoutTooltip = mountBadge({ percent: 4 });
    expect(withoutTooltip.attributes('title')).toBeUndefined();
  });

  it('renders a long value on a single line without truncating it', () => {
    const wrapper = mountBadge({ percent: '+1,234,567%', trend: 5 });

    expect(wrapper.vm.direction).toBe('up');
    expect(wrapper.find('.evolutionBadge__value').text()).toBe('+1,234,567%');
  });
});
