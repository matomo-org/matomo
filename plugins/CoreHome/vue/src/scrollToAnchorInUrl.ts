/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { nextTick, watch } from 'vue';
import MatomoUrl from './MatomoUrl/MatomoUrl';

const { $ } = window;

function scrollToAnchorNode($node: HTMLElement|JQuery|string) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  ($ as any).scrollTo($node, 20);
}

function preventDefaultIfEventExists(event: Event|null) {
  if (event) {
    event.preventDefault();
  }
}

function scrollToAnchorIfPossible(hash: string, event: Event|null) {
  if (!hash) {
    return;
  }

  if (hash.indexOf('&') !== -1) {
    return;
  }

  let $node: JQuery|null = null;
  try {
    $node = $(`#${hash}`);
  } catch (err) {
    // on jquery syntax error, ignore so nothing is logged to the console
    return;
  }

  if ($node?.length) {
    scrollToAnchorNode($node);
    preventDefaultIfEventExists(event);
    return;
  }

  $node = $(`a[name=${hash}]`);

  if ($node?.length) {
    scrollToAnchorNode($node);
    preventDefaultIfEventExists(event);
  }
}

function isLinkWithinSamePage(location: URL, newUrl: string) {
  if (location && location.origin && newUrl.indexOf(location.origin) === -1) {
    // link to different domain
    return false;
  }

  if (location && location.pathname && newUrl.indexOf(location.pathname) === -1) {
    // link to different path
    return false;
  }

  if (location && location.search && newUrl.indexOf(location.search) === -1) {
    // link with different search
    return false;
  }

  return true;
}

function handleScrollToAnchorIfPresentOnPageLoad() {
  if (window.location.hash.slice(0, 2) === '#/') {
    const hash = window.location.hash.slice(2);
    scrollToAnchorIfPossible(hash, null);
  }
}

function handleScrollToAnchorAfterPageLoad() {
  watch(() => MatomoUrl.url.value, (newUrl, oldUrl) => {
    if (!newUrl) {
      return;
    }

    const hashPos = newUrl.href.indexOf('#/');
    if (hashPos === -1) {
      return;
    }

    if (oldUrl && !isLinkWithinSamePage(oldUrl, newUrl.href)) {
      return;
    }

    const hash = newUrl.href.slice(hashPos + 2);

    scrollToAnchorIfPossible(hash, null);
  });
}

handleScrollToAnchorAfterPageLoad();
$(handleScrollToAnchorIfPresentOnPageLoad);

export default function scrollToAnchorInUrl(): void {
  // may be called when page is only fully loaded after some additional requests
  // timeout needed to ensure Vue rendered fully
  nextTick(handleScrollToAnchorIfPresentOnPageLoad);
}
