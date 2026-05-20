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
  const longCategoryName = 'A very long category name that should wrap onto another line in the modal';
  const longCategory = { id: 'long-category', label: longCategoryName, widgets: [] };

  it('renders one <li> per category and applies the chosen class', () => {
    const wrapper = mount(CategoryList as any, {
      props: {
        categories: [
          { id: 'visitors', label: 'Visitors', widgets: [] },
          longCategory,
          { id: 'live', label: 'Live', widgets: [] },
        ],
        chosenCategory: 'long-category',
      },
    });

    const items = wrapper.findAll('li');
    expect(items).toHaveLength(3);
    expect(items[0].classes()).not.toContain('widgetpreview-choosen');
    expect(items[1].classes()).toContain('widgetpreview-choosen');
    expect(items[2].classes()).not.toContain('widgetpreview-choosen');
    expect(items.map((i) => i.text())).toEqual(['Visitors', longCategoryName, 'Live']);
  });

  it('renders the full category text for long labels', () => {
    const wrapper = mount(CategoryList as any, {
      props: {
        categories: [longCategory],
        chosenCategory: 'long-category',
      },
    });

    expect(wrapper.find('li').text()).toBe(longCategoryName);
  });

  it('emits update:chosenCategory on mouseover', async () => {
    const wrapper = mount(CategoryList as any, {
      props: {
        categories: [
          { id: 'visitors', label: 'Visitors', widgets: [] },
          { id: 'goals', label: 'Goals', widgets: [] },
        ],
        chosenCategory: null,
      },
    });

    await wrapper.findAll('li')[1].trigger('mouseover');

    expect(wrapper.emitted()['update:chosenCategory']).toEqual([['goals']]);
  });
});
