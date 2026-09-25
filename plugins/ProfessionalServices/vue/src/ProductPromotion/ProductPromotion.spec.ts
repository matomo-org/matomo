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
    <div class="productPromotion__figure">
      <img class="productPromotion__image" src="promo.png" alt="">
    </div>
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

  it('keeps the banner and disables the button once the trial is requested', async () => {
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

    // The confirmation the user sees is the button itself, where they clicked.
    expect(document.body.contains(element)).toBe(true);

    const cta = element.querySelector<HTMLButtonElement>('[data-role=requestTrial]')!;
    expect(cta.disabled).toBe(true);
    expect(cta.classList.contains('productPromotion__ctaButton--requested')).toBe(true);
    expect(cta.textContent).toEqual('Marketplace_TrialRequested');
  });

  it('cannot be asked for twice once it has been requested', async () => {
    const { element } = mountBanner();
    modalConfirm.mockImplementation((node: HTMLElement, callbacks: Record<string, () => void>) => {
      callbacks.yes();
    });

    click(element, 'requestTrial');
    await Promise.resolve();
    await Promise.resolve();

    post.mockClear();
    modalConfirm.mockClear();

    // A disabled button fires no click, so nothing reaches the API a second time.
    click(element, 'requestTrial');

    expect(modalConfirm).not.toHaveBeenCalled();
    expect(post).not.toHaveBeenCalled();
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

  it('keeps the banner and reports the failure when dismissing fails', async () => {
    const { element } = mountBanner();
    post.mockRejectedValue(new Error('network down'));

    click(element, 'dismiss');
    await Promise.resolve();
    await Promise.resolve();
    await Promise.resolve();

    // It was not dismissed, so it must still be there to try again.
    expect(document.body.contains(element)).toBe(true);
    expect(showNotification).toHaveBeenCalledWith(
      expect.objectContaining({ context: 'error' }),
    );
  });

  it('drops the artwork from the layout when the image cannot be loaded', () => {
    const { element } = mountBanner();

    expect(element.classList.contains('productPromotion--noFigure')).toBe(false);

    element.querySelector('.productPromotion__image')!.dispatchEvent(new Event('error'));

    expect(element.classList.contains('productPromotion--noFigure')).toBe(true);

    // Everything the reader needs is still there.
    expect(element.querySelector('[data-role=dismiss]')).not.toEqual(null);
    expect(element.querySelector('[data-role=requestTrial]')).not.toEqual(null);
  });

  it('drops the artwork when the image had already failed before mounting', () => {
    const element = document.createElement('section');
    element.innerHTML = `
      <div class="productPromotion__figure">
        <img class="productPromotion__image" src="gone.png" alt="">
      </div>
      <a data-role="dismiss" href="#"></a>
    `;
    document.body.appendChild(element);

    // No error event is coming for an image that finished failing already.
    const image = element.querySelector('.productPromotion__image')!;
    Object.defineProperty(image, 'complete', { value: true });
    Object.defineProperty(image, 'naturalWidth', { value: 0 });

    ProductPromotion.mounted(element, {
      value: { pluginName: 'AbTesting', triggerName: 't', productName: 'A/B Testing' },
    } as DirectiveBinding<DirectiveValue>);

    expect(element.classList.contains('productPromotion--noFigure')).toBe(true);
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
