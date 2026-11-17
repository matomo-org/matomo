/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Matomo, NumberFormatter, translate } from 'CoreHome';

const SCHEME_AND_WWW = /^(https?:\/\/)?(www\.)?/i;

/**
 * A complete HTML entity. A raw `&` in a query, eg `&section=`, starts none, though a browser
 * would read `&sect` as one.
 */
const HTML_ENTITY = /&(?:#\d+|#x[0-9a-f]+|[a-z][a-z0-9]*);/gi;

/**
 * URLs come as the tracker stores them, with query values HTML-encoded; shown as text. Only
 * complete entities are decoded, once.
 */
export function displayUrl(url: string): string {
  return url.replace(HTML_ENTITY, (entity) => Matomo.helper.htmlDecode(entity));
}

/** Control and format characters, eg bidi overrides and zero-width spaces, hide or reorder text. */
const HIDDEN_CHARACTER = /[\p{Cc}\p{Cf}]/u;

/** Text with its control and format characters percent-encoded, eg for a tooltip. */
export function encodeHiddenCharacters(text: string): string {
  return Array.from(text)
    .map((char) => (HIDDEN_CHARACTER.test(char) ? encodeURIComponent(char) : char))
    .join('');
}

/** The number of bytes of a UTF-8 sequence starting with the given byte, 0 if none does. */
function utf8SequenceLength(byte: number): number {
  if (byte >= 0xC2 && byte <= 0xDF) {
    return 2;
  }

  if (byte >= 0xE0 && byte <= 0xEF) {
    return 3;
  }

  if (byte >= 0xF0 && byte <= 0xF4) {
    return 4;
  }

  return 0;
}

/** Decodes the valid UTF-8 sequences of a run of `%XX`; any other byte stays encoded. */
function decodePercentRun(run: string): string {
  const bytes = run.match(/%[0-9A-Fa-f]{2}/g) || [];
  let result = '';
  let index = 0;

  while (index < bytes.length) {
    const lead = parseInt(bytes[index].substring(1), 16);
    const length = lead < 0x80 ? 1 : utf8SequenceLength(lead);
    const sequence = bytes.slice(index, index + Math.max(1, length)).join('');
    let decoded = '';

    if (length && index + length <= bytes.length) {
      try {
        decoded = decodeURIComponent(sequence);
      } catch (e) {
        // not UTF-8
      }
    }

    if (decoded && !HIDDEN_CHARACTER.test(decoded)) {
      result += decoded;
      index += length;
    } else {
      result += bytes[index];
      index += 1;
    }
  }

  return result;
}

/**
 * The URL for display, with percent-encoded UTF-8 decoded, eg `%E6%A9%9F` as `機`. Unlike the Pages
 * report, `+` stays as it is and invalid bytes stay encoded rather than dropped. Control and format
 * characters, eg a bidi override, are shown encoded so they can't reorder or hide text. For display
 * only: the stored URL, which a suggestion or an add must match, stays encoded.
 */
export function readableUrl(url: string): string {
  return encodeHiddenCharacters(displayUrl(url)).replace(/(%[0-9A-Fa-f]{2})+/g, decodePercentRun);
}

/** The page's name in a question or a message: readable, without scheme and `www.`. */
function pageName(url: string): string {
  return readableUrl(url).replace(/^https?:\/\/(www\.)?/i, '');
}

/** The question of the confirmation before a page is removed. */
export function stopConfirmLabel(url: string): string {
  return translate('Transitions_StopPreprocessingConfirm', pageName(url));
}

/** What happened after a page is removed, and what that means for its data; plain text. */
export function removedMessage(url: string): string {
  return translate('Transitions_PreprocessedPageRemoved', pageName(url));
}

/**
 * The same as HTML, eg for a notification, which renders its message as HTML. The decoded name may
 * contain markup and entities, so it is escaped.
 */
export function removedMessageHtml(url: string): string {
  return translate('Transitions_PreprocessedPageRemoved', Matomo.helper.htmlEntities(pageName(url)));
}

/** What happens after a page is added, given the months its past data is rebuilt for. */
export function addedMessage(rearchiveMonths: number): string {
  if (rearchiveMonths <= 0) {
    return translate('Transitions_PreprocessedPageAddedNewDataOnly');
  }

  return rearchiveMonths === 1
    ? translate('Transitions_PreprocessedPageAddedRebuildOneMonth')
    : translate('Transitions_PreprocessedPageAddedRebuild', NumberFormatter.formatNumber(rearchiveMonths));
}

/** A `YYYY-MM-DD HH:MM:SS` time in UTC, as the API returns it, to the minute. */
export function formatUtcDateTime(dateTime: string): string {
  return translate('Transitions_DateTimeUtc', dateTime.substring(0, 16));
}

/** No timezone is further behind UTC (UTC−12). */
const MAX_BEHIND_UTC_MS = 12 * 60 * 60 * 1000;

/**
 * The first day of the month before the current one, as `YYYY-MM-DD`, taken 12 hours behind UTC
 * so the month has ended in every website's timezone. The `lastMonth` keyword is not used: on eg
 * March 31 it lands in the current month.
 */
export function previousMonthStart(now: Date): string {
  const behindmost = new Date(now.getTime() - MAX_BEHIND_UTC_MS);
  const month = behindmost.getUTCMonth();
  const year = behindmost.getUTCFullYear() - (month === 0 ? 1 : 0);
  const previousMonth = month === 0 ? 12 : month;

  return `${year}-${String(previousMonth).padStart(2, '0')}-01`;
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
