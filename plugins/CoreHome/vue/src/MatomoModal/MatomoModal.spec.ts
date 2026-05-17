/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import { defineComponent, nextTick, h } from 'vue';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const MatomoModal = require('./MatomoModal.vue').default;

const activeWrappers: Array<{ unmount: () => void }> = [];

interface HostProps {
  modelValue?: boolean;
  classes?: unknown;
  contentClass?: unknown;
  ariaLabel?: string;
  withFooter?: boolean;
}

function mountHost(initial: HostProps = {}) {
  const Host = defineComponent({
    components: { MatomoModal },
    data() {
      return {
        modelValue: initial.modelValue ?? false,
        classes: initial.classes ?? '',
        contentClass: initial.contentClass ?? '',
        ariaLabel: initial.ariaLabel,
        withFooter: initial.withFooter ?? false,
        openedWith: null as HTMLElement | null,
        closedCount: 0,
      };
    },
    methods: {
      onOpened(root: HTMLElement) { this.openedWith = root; },
      onClosed() { this.closedCount += 1; },
    },
    render() {
      const slots: Record<string, () => unknown> = {
        default: () => h('p', { class: 'body-text' }, 'modal body'),
      };
      if (this.withFooter) {
        slots.footer = () => h('button', { class: 'footer-btn' }, 'OK');
      }
      return h(
        MatomoModal,
        {
          modelValue: this.modelValue,
          'onUpdate:modelValue': (val: boolean) => { this.modelValue = val; },
          classes: this.classes,
          contentClass: this.contentClass,
          ariaLabel: this.ariaLabel,
          onOpened: this.onOpened,
          onClosed: this.onClosed,
        },
        slots,
      );
    },
  });

  const wrapper = mount(Host, { attachTo: document.body });
  activeWrappers.push(wrapper);
  return wrapper;
}

function getModal(): HTMLElement | null {
  return document.body.querySelector<HTMLElement>('.modal');
}

function getOverlay(): HTMLElement | null {
  return document.body.querySelector<HTMLElement>('.modal-overlay');
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

  it('renders the slot inside .modal-content but stays hidden when closed', () => {
    mountHost({ modelValue: false });

    const modal = getModal()!;
    expect(modal).not.toBeNull();
    expect(modal.querySelector('.modal-content .body-text')?.textContent).toBe('modal body');
    expect(modal.classList.contains('open')).toBe(false);
    expect(getOverlay()).toBeNull();
    expect(document.body.style.overflow).toBe('');
  });

  it('applies aria attributes and custom classes', () => {
    mountHost({
      ariaLabel: 'My Modal',
      classes: ['custom-modal', { secondary: true }],
      contentClass: 'custom-content',
    });

    const modal = getModal()!;
    expect(modal.getAttribute('role')).toBe('dialog');
    expect(modal.getAttribute('aria-modal')).toBe('true');
    expect(modal.getAttribute('aria-label')).toBe('My Modal');
    expect(modal.classList.contains('custom-modal')).toBe(true);
    expect(modal.classList.contains('secondary')).toBe(true);
    expect(modal.querySelector('.modal-content')?.classList.contains('custom-content')).toBe(true);
  });

  it('renders an optional footer slot inside .modal-footer', () => {
    mountHost({ withFooter: true });

    expect(getModal()!.querySelector('.modal-footer .footer-btn')?.textContent).toBe('OK');
  });

  it('opens, locks body scroll, and emits opened with the modal root', async () => {
    const trigger = document.createElement('button');
    document.body.appendChild(trigger);
    trigger.focus();

    const wrapper = mountHost();
    wrapper.vm.modelValue = true;
    await settle();

    const modal = getModal()!;
    expect(modal.classList.contains('open')).toBe(true);
    expect(getOverlay()!.classList.contains('open')).toBe(true);
    expect(document.body.style.overflow).toBe('hidden');
    expect(wrapper.vm.openedWith).toBe(modal);
    expect(document.activeElement).toBe(modal);
  });

  it('closes when Escape is pressed and emits closed', async () => {
    const wrapper = mountHost({ modelValue: true });
    await settle();
    expect(getModal()!.classList.contains('open')).toBe(true);

    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    await settle();

    expect(wrapper.vm.modelValue).toBe(false);
    expect(getModal()!.classList.contains('open')).toBe(false);
    expect(getOverlay()).toBeNull();
    expect(wrapper.vm.closedCount).toBe(1);
    expect(document.body.style.overflow).toBe('');
  });

  it('closes when the overlay is clicked', async () => {
    const wrapper = mountHost({ modelValue: true });
    await settle();

    getOverlay()!.dispatchEvent(new MouseEvent('click', { bubbles: true }));
    await settle();

    expect(wrapper.vm.modelValue).toBe(false);
    expect(getModal()!.classList.contains('open')).toBe(false);
  });

  it('restores the previous body overflow when closed', async () => {
    document.body.style.overflow = 'auto';
    const trigger = document.createElement('button');
    document.body.appendChild(trigger);
    trigger.focus();
    const wrapper = mountHost();
    wrapper.vm.modelValue = true;
    await settle();
    expect(document.body.style.overflow).toBe('hidden');

    wrapper.vm.modelValue = false;
    await settle();
    expect(document.body.style.overflow).toBe('auto');
    expect(document.activeElement).toBe(trigger);
  });

  it('detaches the document keydown listener when unmounted while open', async () => {
    const wrapper = mountHost({ modelValue: true });
    await settle();

    wrapper.unmount();
    activeWrappers.pop();

    // Dispatching Escape after unmount must not throw and must not flip
    // any state — there is no longer a host listening.
    expect(() => {
      document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    }).not.toThrow();
    expect(document.body.style.overflow).toBe('');
  });
});
