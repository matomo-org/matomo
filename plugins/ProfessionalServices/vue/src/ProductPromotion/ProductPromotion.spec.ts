/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

const post = vi.fn();
const modalConfirm = vi.fn();
const showNotification = vi.fn(() => 'notification-id');
const scrollToNotification = vi.fn();

vi.mock('CoreHome', () => ({
  AjaxHelper: { post: (...args: unknown[]) => post(...args) },
  Matomo: {
    helper: {
      modalConfirm: (...args: unknown[]) => modalConfirm(...args),
      showAjaxLoading: vi.fn(),
      hideAjaxLoading: vi.fn(),
    },
  },
  NotificationsStore: {
    show: (...args: unknown[]) => showNotification(...args),
    scrollToNotification: (...args: unknown[]) => scrollToNotification(...args),
  },
  translate: (key: string) => key,
}));

// imported after the mock so the directive picks it up
// eslint-disable-next-line import/first
import ProductPromotion from './ProductPromotion';

interface DirectiveValue {
  pluginName: string;
  triggerName: string;
  productName: string;
  confirmElement?: HTMLElement | null;
}

function mountBanner(): { element: HTMLElement, binding: DirectiveBinding<DirectiveValue> } {
  const element = document.createElement('section');
  element.innerHTML = `
    <a data-role="dismiss" href="#"></a>
    <button data-role="requestTrial"></button>
    <div class="ui-confirm" data-role="requestTrialConfirm"></div>
  `;
  document.body.appendChild(element);

  const binding = {
    value: {
      pluginName: 'AbTesting',
      triggerName: 'conversion_rate_ABtesting',
      productName: 'A/B Testing',
    },
  } as DirectiveBinding<DirectiveValue>;

  ProductPromotion.mounted(element, binding);

  return { element, binding };
}

function click(element: HTMLElement, role: string): void {
  element.querySelector<HTMLElement>(`[data-role=${role}]`)!
    .dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
}

describe('ProductPromotion directive', () => {
  beforeEach(() => {
    document.body.innerHTML = '';
    vi.clearAllMocks();
    post.mockResolvedValue(undefined);
    modalConfirm.mockImplementation(() => {});
  });

  it('dismisses the promotion and removes the banner', async () => {
    const { element } = mountBanner();

    click(element, 'dismiss');
    await Promise.resolve();
    await Promise.resolve();

    expect(post).toHaveBeenCalledWith(
      { method: 'ProfessionalServices.dismissDashboardPromotion' },
      { pluginName: 'AbTesting', triggerName: 'conversion_rate_ABtesting' },
    );
    expect(document.body.contains(element)).toBe(false);
  });

  it('holds on to the confirmation node so a second click still opens the modal', () => {
    const { element } = mountBanner();

    // modalConfirm moves the node out of the banner and never returns it, which is what
    // used to make the link inert after the user declined once.
    modalConfirm.mockImplementation((node: HTMLElement) => {
      document.body.appendChild(node);
    });

    click(element, 'requestTrial');
    expect(modalConfirm).toHaveBeenCalledTimes(1);

    expect(element.querySelector('[data-role=requestTrialConfirm]')).toBeNull();

    click(element, 'requestTrial');
    expect(modalConfirm).toHaveBeenCalledTimes(2);
    expect(modalConfirm.mock.calls[1][0]).toBe(modalConfirm.mock.calls[0][0]);
  });

  it('requests the trial and removes the banner once it is confirmed', async () => {
    const { element } = mountBanner();
    modalConfirm.mockImplementation((node: HTMLElement, callbacks: Record<string, () => void>) => {
      callbacks.yes();
    });

    click(element, 'requestTrial');
    await Promise.resolve();
    await Promise.resolve();

    expect(post).toHaveBeenCalledWith(
      { module: 'API', method: 'Marketplace.requestTrial' },
      { pluginName: 'AbTesting' },
    );
    expect(showNotification).toHaveBeenCalled();
    expect(document.body.contains(element)).toBe(false);
  });

  it('keeps the banner and reports the failure when the trial request fails', async () => {
    const { element } = mountBanner();
    post.mockRejectedValue(new Error('network down'));
    modalConfirm.mockImplementation((node: HTMLElement, callbacks: Record<string, () => void>) => {
      callbacks.yes();
    });

    click(element, 'requestTrial');
    await Promise.resolve();
    await Promise.resolve();
    await Promise.resolve();

    // No trial is pending, so the offer has to remain reachable.
    expect(document.body.contains(element)).toBe(true);
    expect(showNotification).toHaveBeenCalledWith(
      expect.objectContaining({ context: 'error' }),
    );
  });

  it('does nothing at all when the directive has no plugin to promote', () => {
    const element = document.createElement('section');
    element.innerHTML = '<a data-role="dismiss" href="#"></a>';
    document.body.appendChild(element);

    ProductPromotion.mounted(element, { value: {} } as DirectiveBinding<DirectiveValue>);

    click(element, 'dismiss');

    expect(post).not.toHaveBeenCalled();
    expect(document.body.contains(element)).toBe(true);
  });
});
