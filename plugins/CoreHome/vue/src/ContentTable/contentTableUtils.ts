/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

type MediaQueryChangeListener = () => void;

interface ContentTableRegistration {
  listener: MediaQueryChangeListener;
  mediaQuery: MediaQueryList;
}

const MOBILE_BREAKPOINT = '(max-width: 767px)';
const registrations = new WeakMap<HTMLElement, ContentTableRegistration>();

function ensureOverflowWrapper(el: HTMLElement): void {
  const parent = el.parentElement;

  if (!parent || parent.classList.contains('contentTableWrapper')) {
    return;
  }

  const wrapper = document.createElement('div');
  wrapper.className = 'contentTableWrapper';

  parent.insertBefore(wrapper, el);
  wrapper.appendChild(el);
}

function removeOverflowWrapper(el: HTMLElement): void {
  const parent = el.parentElement;

  if (!parent || !parent.classList.contains('contentTableWrapper')) {
    return;
  }

  const wrapperParent = parent.parentElement;
  if (!wrapperParent) {
    return;
  }

  wrapperParent.insertBefore(el, parent);
  parent.remove();
}

function shouldWrapTable(mediaQuery?: MediaQueryList): boolean {
  return (mediaQuery || window.matchMedia(MOBILE_BREAKPOINT)).matches;
}

function addMediaQueryListener(
  mediaQuery: MediaQueryList,
  listener: MediaQueryChangeListener,
): void {
  mediaQuery.addEventListener('change', listener);
}

function removeMediaQueryListener(
  mediaQuery: MediaQueryList,
  listener: MediaQueryChangeListener,
): void {
  mediaQuery.removeEventListener('change', listener);
}

function applyResponsiveContentTable(el: HTMLElement, mediaQuery?: MediaQueryList): void {
  el.classList.add('card', 'card-table', 'entityTable');

  if (shouldWrapTable(mediaQuery)) {
    ensureOverflowWrapper(el);
  } else {
    removeOverflowWrapper(el);
  }
}

export function unregisterResponsiveContentTable(el: HTMLElement): void {
  const registration = registrations.get(el);

  if (registration) {
    removeMediaQueryListener(registration.mediaQuery, registration.listener);
    registrations.delete(el);
  }

  removeOverflowWrapper(el);
}

export function registerResponsiveContentTable(el: HTMLElement): void {
  const existingRegistration = registrations.get(el);
  if (existingRegistration) {
    applyResponsiveContentTable(el, existingRegistration.mediaQuery);
    return;
  }

  const mediaQuery = window.matchMedia(MOBILE_BREAKPOINT);
  const listener = () => {
    if (!el.isConnected) {
      unregisterResponsiveContentTable(el);
      return;
    }

    applyResponsiveContentTable(el, mediaQuery);
  };

  addMediaQueryListener(mediaQuery, listener);
  registrations.set(el, { mediaQuery, listener });
  applyResponsiveContentTable(el, mediaQuery);
}
