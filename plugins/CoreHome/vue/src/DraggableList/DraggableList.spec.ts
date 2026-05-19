/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  h,
  nextTick,
} from 'vue';
import { mount } from '@vue/test-utils';
import DraggableList from './DraggableList.vue';

describe('CoreHome/DraggableList', () => {
  interface TestItem {
    label: string;
  }

  function createDataTransfer() {
    return {
      dropEffect: 'move',
      effectAllowed: 'move',
      setData: jest.fn(),
    };
  }

  function createWrapper(props = {}) {
    return mount(DraggableList, {
      props: {
        items: [
          { id: 'first', label: 'First' },
          { id: 'second', label: 'Second' },
          { id: 'third', label: 'Third' },
        ],
        itemKey: 'id',
        ...props,
      },
      slots: {
        default: ({ item }: { item: TestItem }) => [
          h('span', { class: 'dragHandle' }, '::'),
          h('span', item.label),
        ],
      },
    });
  }

  it('should reorder the rendered items while hovering and emit the final ids on drop', async () => {
    const wrapper = createWrapper();
    const items = wrapper.findAll('.draggableListItem');
    const dataTransfer = createDataTransfer();
    Object.defineProperty(items[2].element, 'getBoundingClientRect', {
      value: () => ({
        top: 0,
        bottom: 100,
        height: 100,
        left: 0,
        right: 100,
        width: 100,
      }),
    });

    await items[0].trigger('dragstart', { dataTransfer });
    await items[2].trigger('dragover', { clientY: 1, dataTransfer });

    expect(wrapper.findAll('.draggableListItem').map((item) => item.attributes('data-item-id'))).toEqual([
      'second',
      'first',
      'third',
    ]);

    await items[2].trigger('drop', { dataTransfer });

    expect(wrapper.emitted('reorder')).toEqual([
      [['second', 'first', 'third']],
    ]);
  });

  it('should reorder as soon as the dragged item reaches the next item', async () => {
    const wrapper = createWrapper();
    const items = wrapper.findAll('.draggableListItem');
    const dataTransfer = createDataTransfer();
    Object.defineProperty(items[1].element, 'getBoundingClientRect', {
      value: () => ({
        top: 0,
        bottom: 100,
        height: 100,
        left: 0,
        right: 100,
        width: 100,
      }),
    });

    await items[0].trigger('dragstart', { dataTransfer });
    await items[1].trigger('dragover', { clientY: 11, dataTransfer });

    expect(wrapper.findAll('.draggableListItem').map((item) => item.attributes('data-item-id'))).toEqual([
      'second',
      'first',
      'third',
    ]);
  });

  it('should keep a visible placeholder while dragging', async () => {
    const wrapper = createWrapper();
    const item = wrapper.find('.draggableListItem');
    const dataTransfer = createDataTransfer();

    await item.trigger('dragstart', { dataTransfer });
    await new Promise((resolve) => { window.setTimeout(resolve, 0); });
    await nextTick();

    expect(item.classes()).toContain('isDragged');
  });

  it('should update rendered slot data when items are replaced with the same keys and order', async () => {
    const wrapper = createWrapper();

    await wrapper.setProps({
      items: [
        { id: 'first', label: 'First updated' },
        { id: 'second', label: 'Second updated' },
        { id: 'third', label: 'Third updated' },
      ],
    });

    expect(wrapper.findAll('.draggableListItem').map((item) => item.text())).toEqual([
      '::First updated',
      '::Second updated',
      '::Third updated',
    ]);
  });

  it('should only start dragging from the configured handle', async () => {
    const wrapper = createWrapper({
      handle: '.dragHandle',
    });
    const item = wrapper.find('.draggableListItem');
    const handle = wrapper.find('.dragHandle');
    const dataTransfer = createDataTransfer();

    await item.trigger('dragstart', {
      dataTransfer,
    });

    expect(dataTransfer.setData).not.toHaveBeenCalled();

    await handle.trigger('dragstart', {
      dataTransfer,
    });

    expect(dataTransfer.setData).toHaveBeenCalledWith('text/plain', 'first');
  });
});
