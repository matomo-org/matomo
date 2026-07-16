/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const EditButton = require('./EditButton.vue').default;

function createSegment(overrides: Record<string, unknown> = {}) {
  return {
    key: 'segment-1',
    type: 'segment',
    idsegment: '1',
    label: 'My segment',
    tooltip: 'My segment',
    showEditButton: true,
    editTitle: 'Edit segment',
    editState: '',
    ...overrides,
  };
}

describe('SegmentEditor/EditButton.vue', () => {
  it('does not render when showEditButton is false', () => {
    const wrapper = mount(EditButton, {
      props: { segment: createSegment({ showEditButton: false }) },
    });

    expect(wrapper.find('button').exists()).toBe(false);
  });

  it('renders the button with the segment edit metadata', () => {
    const wrapper = mount(EditButton, {
      props: { segment: createSegment() },
    });

    const button = wrapper.find('button');
    expect(button.classes()).toContain('editSegment');
    expect(button.attributes('title')).toBe('Edit segment');
    expect(button.attributes('data-state')).toBe('');
  });

  it('emits openEditButton with the segment id when clicked', async () => {
    const wrapper = mount(EditButton, {
      props: { segment: createSegment({ idsegment: '42' }) },
    });

    await wrapper.find('button').trigger('click');

    expect(wrapper.emitted('openEditButton')).toHaveLength(1);
    expect(wrapper.emitted('openEditButton')?.[0]).toEqual(['42']);
  });

  it('does not emit when the edit state is disabled', async () => {
    const wrapper = mount(EditButton, {
      props: { segment: createSegment({ editState: 'disabled' }) },
    });

    await wrapper.find('button').trigger('click');

    expect(wrapper.emitted('openEditButton')).toBeUndefined();
  });

  it('does not emit when the segment has no id', async () => {
    const wrapper = mount(EditButton, {
      props: { segment: createSegment({ idsegment: '' }) },
    });

    await wrapper.find('button').trigger('click');

    expect(wrapper.emitted('openEditButton')).toBeUndefined();
  });
});
