/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const CompareButton = require('./CompareButton.vue').default;

function createSegment(overrides: Record<string, unknown> = {}) {
  return {
    key: 'segment-1',
    type: 'segment',
    idsegment: '1',
    definition: 'countryCode==nz',
    label: 'My segment',
    tooltip: 'My segment',
    showCompareButton: true,
    compareButtonClass: 'segmentAction compareSegment',
    compareTitle: 'Compare segment',
    compareState: '',
    ...overrides,
  };
}

describe('SegmentEditor/CompareButton.vue', () => {
  it('does not render when showCompareButton is false', () => {
    const wrapper = mount(CompareButton, {
      props: { segment: createSegment({ showCompareButton: false }) },
    });

    expect(wrapper.find('button').exists()).toBe(false);
  });

  it('renders the button with the segment compare metadata and the icon', () => {
    const wrapper = mount(CompareButton, {
      props: { segment: createSegment() },
    });

    const button = wrapper.find('button');
    expect(button.classes()).toContain('compareSegment');
    expect(button.attributes('title')).toBe('Compare segment');
    expect(button.attributes('data-state')).toBe('');
    expect(button.find('svg').exists()).toBe(true);
  });

  it('applies the isAnonymous class only when the prop is true', async () => {
    const wrapper = mount(CompareButton, {
      props: { segment: createSegment(), isAnonymous: false },
    });

    expect(wrapper.find('button').classes()).not.toContain('isAnonymous');

    await wrapper.setProps({ isAnonymous: true });

    expect(wrapper.find('button').classes()).toContain('isAnonymous');
  });

  it('passes the compare state through to the compare icon fill', () => {
    const wrapper = mount(CompareButton, {
      props: { segment: createSegment({ compareState: 'active' }) },
    });

    expect(wrapper.findAll('path').every((path) => path.attributes('fill') === 'currentColor')).toBe(true);
  });

  it('emits toggleCompareButton with the segment definition when clicked', async () => {
    const wrapper = mount(CompareButton, {
      props: { segment: createSegment({ definition: 'deviceType==mobile' }) },
    });

    await wrapper.find('button').trigger('click');

    expect(wrapper.emitted('toggleCompareButton')).toHaveLength(1);
    expect(wrapper.emitted('toggleCompareButton')?.[0]).toEqual(['deviceType==mobile']);
  });

  it('does not emit when the compare state is disabled', async () => {
    const wrapper = mount(CompareButton, {
      props: { segment: createSegment({ compareState: 'disabled' }) },
    });

    await wrapper.find('button').trigger('click');

    expect(wrapper.emitted('toggleCompareButton')).toBeUndefined();
  });

  it('does not emit when the segment has no definition', async () => {
    const wrapper = mount(CompareButton, {
      props: { segment: createSegment({ definition: undefined }) },
    });

    await wrapper.find('button').trigger('click');

    expect(wrapper.emitted('toggleCompareButton')).toBeUndefined();
  });
});
