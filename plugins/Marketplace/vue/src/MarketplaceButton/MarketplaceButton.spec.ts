/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

import MarketplaceButton from './MarketplaceButton.vue';

function mountButton(props: Record<string, unknown> = {}) {
  return mount(MarketplaceButton, {
    props: { href: '?module=CorePluginsAdmin', ...props },
    slots: { default: 'Deactivate' },
  });
}

describe('Marketplace/MarketplaceButton', () => {
  it('is a link to the given URL, labelled by its slot', () => {
    const wrapper = mountButton();

    expect(wrapper.element.tagName).toBe('A');
    expect(wrapper.attributes('href')).toBe('?module=CorePluginsAdmin');
    expect(wrapper.text()).toBe('Deactivate');
  });

  it('uses its own block, not the Materialize button', () => {
    expect(mountButton().classes()).toEqual(['marketplaceButton']);
  });

  it('adds a modifier for each of outline and full width', () => {
    const classes = mountButton({ outline: true, fullWidth: true }).classes();

    expect(classes).toContain('marketplaceButton--outline');
    expect(classes).toContain('marketplaceButton--fullWidth');
  });
});
