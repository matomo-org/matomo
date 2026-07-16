/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

jest.mock('CoreHome', () => ({}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const CategoryList = require('./CategoryList.vue').default;

describe('Dashboard/AddWidgetModal/CategoryList', () => {
  const longCategory = 'A very long category name that should wrap onto another line in the modal';

  it('renders one <li> per category and applies the chosen class', () => {
    const wrapper = mount(CategoryList as any, {
      props: {
        categories: [
          'Visitors',
          longCategory,
          'Live',
        ],
        chosenCategory: longCategory,
      },
    });

    const items = wrapper.findAll('li');
    const buttons = wrapper.findAll('li button');
    expect(items).toHaveLength(3);
    expect(buttons).toHaveLength(3);
    expect(items[0].classes()).not.toContain('widgetpreview-choosen');
    expect(items[1].classes()).toContain('widgetpreview-choosen');
    expect(items[2].classes()).not.toContain('widgetpreview-choosen');
    expect(buttons.map((i) => i.text())).toEqual(['Visitors', longCategory, 'Live']);
  });

  it('renders the full category text for long labels', () => {
    const wrapper = mount(CategoryList as any, {
      props: {
        categories: [longCategory],
        chosenCategory: longCategory,
      },
    });

    expect(wrapper.find('li button').text()).toBe(longCategory);
  });

  it('emits update:chosenCategory on mouseover', async () => {
    const wrapper = mount(CategoryList as any, {
      props: {
        categories: [
          'Visitors',
          'Goals',
        ],
        chosenCategory: null,
      },
    });

    await wrapper.findAll('li button')[1].trigger('mouseover');

    expect(wrapper.emitted()['update:chosenCategory']).toEqual([['Goals']]);
  });

  describe('keyboard', () => {
    it('emits update:chosenCategory and confirm on Enter', async () => {
      const wrapper = mount(CategoryList as any, {
        props: { categories: ['Visitors', 'Goals'], chosenCategory: null },
      });

      await wrapper.findAll('li button')[1].trigger('keydown', { key: 'Enter' });

      expect(wrapper.emitted()['update:chosenCategory']).toEqual([['Goals']]);
      expect(wrapper.emitted().confirm).toEqual([[]]);
    });

    it('emits update:chosenCategory and confirm on Space', async () => {
      const wrapper = mount(CategoryList as any, {
        props: { categories: ['Visitors', 'Goals'], chosenCategory: null },
      });

      await wrapper.findAll('li button')[0].trigger('keydown', { key: ' ' });

      expect(wrapper.emitted()['update:chosenCategory']).toEqual([['Visitors']]);
      expect(wrapper.emitted().confirm).toEqual([[]]);
    });

    it('does not emit confirm on mouse / focus paths', async () => {
      const wrapper = mount(CategoryList as any, {
        props: { categories: ['Visitors'], chosenCategory: null },
      });

      await wrapper.findAll('li button')[0].trigger('mouseover');
      await wrapper.findAll('li button')[0].trigger('click');
      await wrapper.findAll('li button')[0].trigger('focus');

      expect(wrapper.emitted().confirm).toBeUndefined();
    });
  });
});
