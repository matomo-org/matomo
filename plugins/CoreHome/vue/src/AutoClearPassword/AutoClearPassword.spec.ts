/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import AutoClearPassword from './AutoClearPassword';

interface DirectiveLike {
  mounted: (el: HTMLElement, binding: { value?: { delay?: number } }) => void;
  unmounted: (el: HTMLElement) => void;
}

const directive = AutoClearPassword as unknown as DirectiveLike;

const mounted: HTMLElement[] = [];

function mountInput(value = 'secret'): HTMLInputElement {
  const el = document.createElement('input');
  el.type = 'password';
  el.value = value;
  document.body.appendChild(el);
  directive.mounted(el, { value: { delay: 1 } });
  mounted.push(el);
  return el;
}

function mountWrapped(value = 'secret'): HTMLInputElement {
  // The directive may also be bound to an element wrapping the password input.
  const wrapper = document.createElement('div');
  const el = document.createElement('input');
  el.type = 'password';
  el.value = value;
  wrapper.appendChild(el);
  document.body.appendChild(wrapper);
  directive.mounted(wrapper, { value: { delay: 1 } });
  mounted.push(wrapper);
  return el;
}

function firePageHide(persisted: boolean): void {
  window.dispatchEvent(new PageTransitionEvent('pagehide', { persisted }));
}

describe('CoreHome/AutoClearPassword', () => {
  afterEach(() => {
    // Manual mount never fires `unmounted`; tear down so intervals and the
    // pagehide listener don't leak between tests (idempotent).
    mounted.splice(0).forEach((el) => directive.unmounted(el));
    document.body.innerHTML = '';
    vi.useRealTimers();
    vi.restoreAllMocks();
  });

  it('marks the input as enabled on mount', () => {
    const el = mountInput();

    expect(el.dataset.autoClearEnabled).toBe('true');
  });

  it('clears the field and removes all state on a real navigation (pagehide)', () => {
    const el = mountInput();

    firePageHide(false);

    expect(el.value).toBe('');
    expect(el.dataset.autoClearEnabled).toBeUndefined();
    expect((el as { onUmounted?: unknown }).onUmounted).toBeUndefined();
  });

  it('keeps the watcher armed but drops the value when entering the bfcache', () => {
    const el = mountInput();

    firePageHide(true);

    // Value is dropped, but the directive stays armed so the restored page is
    // still protected.
    expect(el.value).toBe('');
    expect(el.dataset.autoClearEnabled).toBe('true');

    const removeSpy = vi.spyOn(window, 'removeEventListener');
    firePageHide(false);
    expect(removeSpy).toHaveBeenCalledWith('pagehide', expect.any(Function));
    expect(el.dataset.autoClearEnabled).toBeUndefined();
  });

  it('clears the field after inactivity and tells plain listeners once', () => {
    vi.useFakeTimers();
    const el = mountInput('');
    const inputSpy = vi.fn();
    el.addEventListener('input', inputSpy);

    // Simulate the user typing a password, which arms the inactivity timer.
    el.value = 'secret';
    el.dispatchEvent(new Event('input'));

    // Let the inactivity timer elapse (delay is 1s in the test harness); the
    // extra time absorbs the 300ms value poll re-arming the timer once.
    vi.advanceTimersByTime(5000);

    expect(el.value).toBe('');
    // Once for the typing above, once for the clear, and no more: the clear
    // reaches our own listener and would otherwise re-arm the timer forever.
    expect(inputSpy).toHaveBeenCalledTimes(2);
    expect(vi.getTimerCount()).toBe(1); // the 300ms value poll, nothing else
  });

  it('arms a password input nested inside the bound element', () => {
    vi.useFakeTimers();
    const el = mountWrapped('');

    el.value = 'secret';
    el.dispatchEvent(new Event('input'));
    vi.advanceTimersByTime(5000);

    expect(el.value).toBe('');
  });

  it('cleans up on unmount without clearing the field value', () => {
    const el = mountInput('keepme');

    directive.unmounted(el);

    expect(el.value).toBe('keepme');
    expect(el.dataset.autoClearEnabled).toBeUndefined();
    expect((el as { onUmounted?: unknown }).onUmounted).toBeUndefined();
  });

  it('does not clear the field after teardown (timers/listeners removed)', () => {
    vi.useFakeTimers();
    const el = mountInput();

    directive.unmounted(el);
    vi.advanceTimersByTime(5000);

    expect(el.value).toBe('secret');
  });

  it('does not arm the same input twice', () => {
    const el = mountInput();
    const addSpy = vi.spyOn(window, 'addEventListener');

    directive.mounted(el, { value: { delay: 1 } });

    expect(addSpy).not.toHaveBeenCalledWith('pagehide', expect.any(Function));
  });
});
