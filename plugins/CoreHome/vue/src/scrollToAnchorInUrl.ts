/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { nextTick, watch } from 'vue';
import MatomoUrl from './MatomoUrl/MatomoUrl';

const { $ } = window;

function scrollToAnchorNode($node: HTMLElement|JQuery|string) {
  $.scrollTo($node, 20);
}

function preventDefaultIfEventExists(event: Event) {
  if (event) {
    event.preventDefault();
  }
}

function scrollToAnchorIfPossible(hash: string, event: Event) {
  if (!hash) {
    return;
  }

  if (-1 !== hash.indexOf('&')) {
    return;
  }

  let $node: JQuery|null = null;
  try {
    $node = $('#' + hash);
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
  if (location && location.origin && -1 === newUrl.indexOf(location.origin)) {
    // link to different domain
    return false;
  }

  if (location && location.pathname && -1 === newUrl.indexOf(location.pathname)) {
    // link to different path
    return false;
  }

  if (location && location.search && -1 === newUrl.indexOf(location.search)) {
    // link with different search
    return false;
  }

  return true;
}

function handleScrollToAnchorIfPresentOnPageLoad() {
  if (window.location.hash.slice(0, 2) == '#/') {
    const hash = window.location.hash.slice(2);
    scrollToAnchorIfPossible(hash, null);
  }
}

function handleScrollToAnchorAfterPageLoad() {
  watch(() => MatomoUrl.url, (newUrl) => {
    if (!newUrl) {
      return;
    }

    const hashPos = newUrl.href.indexOf('#/');
    if (-1 === hashPos) {
      return;
    }

    if (!isLinkWithinSamePage(this.location, newUrl.href)) {
      return;
    }

    const hash = newUrl.href.slice(hashPos + 2);

    scrollToAnchorIfPossible(hash, event);
  });
}

handleScrollToAnchorAfterPageLoad();
$(handleScrollToAnchorIfPresentOnPageLoad);

export default function scrollToAnchorInUrl() {
  // may be called when page is only fully loaded after some additional requests
  // timeout needed to ensure Vue rendered fully
  nextTick(handleScrollToAnchorIfPresentOnPageLoad);
}
