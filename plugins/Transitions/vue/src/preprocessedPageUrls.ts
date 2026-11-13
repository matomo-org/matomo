/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Matomo, translate } from 'CoreHome';

const SCHEME_AND_WWW = /^(https?:\/\/)?(www\.)?/i;

/** URLs come as the tracker stores them, with query values HTML-encoded; shown as text. */
export function displayUrl(url: string): string {
  return Matomo.helper.htmlDecode(url);
}

/** The question of the inline confirmation before a page is removed. */
export function stopConfirmLabel(url: string): string {
  return translate(
    'Transitions_StopPreprocessingConfirm',
    displayUrl(url).replace(/^https?:\/\/(www\.)?/i, ''),
  );
}

/** A `YYYY-MM-DD HH:MM:SS` time in UTC, as the API returns it, to the minute. */
export function formatUtcDateTime(dateTime: string): string {
  return translate('Transitions_DateTimeUtc', dateTime.substring(0, 16));
}

function percentDecode(text: string): string {
  try {
    return decodeURIComponent(text);
  } catch (e) {
    return text;
  }
}

/**
 * What typed text and a decoded URL are compared by: without protocol and `www.`,
 * percent-decoded and case-insensitive.
 */
export function matchKey(text: string): string {
  return percentDecode(text.trim().replace(SCHEME_AND_WWW, '')).toLowerCase();
}

/**
 * Two decoded URLs with the same key are the same selected page: protocol and `www.` don't count,
 * and the host is case-insensitive. The path is not.
 */
export function pageKey(url: string): string {
  const withoutScheme = url.trim().replace(SCHEME_AND_WWW, '');
  const hostEnd = withoutScheme.search(/[/?#]/);
  const host = hostEnd === -1 ? withoutScheme : withoutScheme.substring(0, hostEnd);

  return host.toLowerCase() + withoutScheme.substring(host.length);
}
