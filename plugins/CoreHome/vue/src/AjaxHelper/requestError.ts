/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import jqXHR = JQuery.jqXHR;

/**
 * Whether a failed request is worth sending again: only one that never completed, or that the
 * server could not answer. Anything else, including anything unrecognised, stops the caller,
 * so it can never keep repeating a request the server already rejected.
 *
 * @param error rejection value of a failed request, usually a jqXHR
 */
export default function isRetryableRequestError(error: unknown): boolean {
  if (!error || typeof error !== 'object') {
    return false;
  }

  const xhr = error as jqXHR;

  // aborted, dropped, or no response at all
  if (xhr.statusText === 'abort' || xhr.statusText === 'timeout' || xhr.status === 0) {
    return true;
  }

  if (typeof xhr.status !== 'number') {
    return false;
  }

  // rate limiting only passes because callers back off before trying again
  return xhr.status === 429 || xhr.status >= 500;
}
