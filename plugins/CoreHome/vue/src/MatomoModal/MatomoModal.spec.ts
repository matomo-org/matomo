/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import MatomoModal from './MatomoModal.vue';

const activeWrappers: Array<{ unmount: () => void }> = [];

function mountModal(extraProps: Record<string, unknown> = {}, slots: Record<string, string> = {}) {
  const wrapper = mount(MatomoModal, {
    attachTo: document.body,
    props: {
      modelValue: false,
      ...extraProps,
    },
    slots: {
      default: '<p class="body-text">modal body</p>',
      ...slots,
    },
  });

  activeWrappers.push(wrapper);
  return wrapper;
}

function getModal(): HTMLElement {
  return document.body.querySelector('.modal') as HTMLElement;
}

function getOverlay(): HTMLElement | null {
  return document.body.querySelector('.modal-overlay');
}

async function settle() {
  await nextTick();
  await nextTick();
}

describe('CoreHome/MatomoModal', () => {
  beforeEach(() => {
    document.body.style.overflow = '';
  });

  afterEach(() => {
    while (activeWrappers.length) {
      activeWrappers.pop()!.unmount();
    }
    document.body.innerHTML = '';
  });

  it('renders its content and optional footer with modal classes and attributes', () => {
    mountModal({
      ariaLabel: 'My Modal',
      classes: ['custom-modal', { secondary: true }],
      contentClass: 'custom-content',
    }, {
      footer: '<button class="footer-btn">OK</button>',
    });

    const modal = getModal();

    expect(modal.querySelector('.modal-content .body-text')?.textContent).toBe('modal body');
    expect(modal.querySelector('.modal-footer .footer-btn')?.textContent).toBe('OK');
    expect(modal.classList.contains('open')).toBe(false);
    expect(modal.getAttribute('role')).toBe('dialog');
    expect(modal.getAttribute('aria-modal')).toBe('true');
    expect(modal.getAttribute('aria-label')).toBe('My Modal');
    expect(modal.classList.contains('custom-modal')).toBe(true);
    expect(modal.classList.contains('secondary')).toBe(true);
    expect(modal.querySelector('.modal-content')?.classList.contains('custom-content')).toBe(true);
  });

  it('locks scroll, focuses the modal, and emits lifecycle events when opened and closed', async () => {
    document.body.style.overflow = 'auto';
    const trigger = document.createElement('button');
    document.body.appendChild(trigger);
    trigger.focus();

    const wrapper = mountModal();

    await wrapper.setProps({ modelValue: true });
    await settle();

    const modal = getModal();

    expect(modal.classList.contains('open')).toBe(true);
    expect(getOverlay()?.classList.contains('open')).toBe(true);
    expect(document.body.style.overflow).toBe('hidden');
    expect(document.activeElement).toBe(modal);
    expect(wrapper.emitted('opened')?.[0]?.[0]).toBe(modal);

    await wrapper.setProps({ modelValue: false });
    await settle();

    expect(getOverlay()).toBeNull();
    expect(document.body.style.overflow).toBe('auto');
    expect(document.activeElement).toBe(trigger);
    expect(wrapper.emitted('closed')).toHaveLength(1);
  });

  it('requests close when Escape is pressed or the overlay is clicked', async () => {
    const wrapper = mountModal({ modelValue: true });
    await settle();

    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    getOverlay()?.dispatchEvent(new MouseEvent('click', { bubbles: true }));

    expect(wrapper.emitted('update:modelValue')).toEqual([[false], [false]]);
  });

  it('cleans up global side effects when unmounted while open', async () => {
    const wrapper = mountModal({ modelValue: true });
    await settle();

    wrapper.unmount();
    activeWrappers.pop();

    expect(document.body.style.overflow).toBe('');
    expect(() => {
      document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    }).not.toThrow();
  });
});
