/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

import CTAStatus from './CTAStatus.vue';

function mountStatus(props: Record<string, unknown> = {}) {
  return mount(CTAStatus, {
    props: {
      tone: 'success',
      label: 'Installed',
      inModal: false,
      ...props,
    },
    slots: { default: '<a href="#">Deactivate</a>' },
  });
}

describe('Marketplace/CTAStatus', () => {
  describe('on a card', () => {
    it('is a row of state and action, with no alert and no brackets', () => {
      const wrapper = mountStatus({ hasAction: true });

      expect(wrapper.find('.ctaStatus').exists()).toBe(true);
      expect(wrapper.find('.alert').exists()).toBe(false);
      expect(wrapper.find('.ctaStatus__label').text()).toBe('Installed');
      expect(wrapper.find('.ctaStatus__action').text()).toBe('Deactivate');
      expect(wrapper.text()).not.toContain('(');
    });

    it('carries the tone as a modifier and as the icon, the row having no background', () => {
      const wrapper = mountStatus({ tone: 'danger', label: 'License missing' });

      expect(wrapper.find('.ctaStatus--danger').exists()).toBe(true);
      expect(wrapper.find('.ctaStatus__icon').classes()).toContain('icon-error');
    });

    it('leaves out the action element entirely when there is no action', () => {
      expect(mountStatus({ hasAction: false }).find('.ctaStatus__action').exists()).toBe(false);
    });
  });

  describe('in the details modal', () => {
    it('stays the alert it has always been', () => {
      const wrapper = mountStatus({ inModal: true, hasAction: true });

      expect(wrapper.find('.alert.alert-success').exists()).toBe(true);
      expect(wrapper.find('.alert').classes()).toContain('alert-no-background');
      expect(wrapper.find('.ctaStatus').exists()).toBe(false);
      expect(wrapper.text().replace(/\s+/g, ' ')).toBe('Installed (Deactivate)');
    });

    it('renders no brackets when the caller has no action to put in them', () => {
      const wrapper = mountStatus({ inModal: true, hasAction: false });
      expect(wrapper.text()).toBe('Installed');
    });

    it('maps every tone onto the alert class that already styles it', () => {
      const alertClasses = (tone: string) => mountStatus({ inModal: true, tone })
        .find('.alert')
        .classes();

      expect(alertClasses('success')).toContain('alert-success');
      expect(alertClasses('warning')).toContain('alert-warning');
      expect(alertClasses('danger')).toContain('alert-danger');
    });
  });
});
