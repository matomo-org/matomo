/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import isRetryableRequestError from './requestError';

describe('CoreHome/isRetryableRequestError', () => {
  function xhr(status: number, statusText = 'error') {
    return { status, statusText };
  }

  it('retries a request that never completed', () => {
    expect(isRetryableRequestError(xhr(0, 'abort'))).toBe(true);
    expect(isRetryableRequestError(xhr(0, 'error'))).toBe(true);
    expect(isRetryableRequestError(xhr(0, 'timeout'))).toBe(true);
    expect(isRetryableRequestError(xhr(200, 'timeout'))).toBe(true);
  });

  it('retries a server side error', () => {
    [500, 502, 503, 504].forEach((status) => {
      expect(isRetryableRequestError(xhr(status))).toBe(true);
    });
  });

  it('retries a rate limited request', () => {
    expect(isRetryableRequestError(xhr(429))).toBe(true);
  });

  it('gives up on a request the server rejected', () => {
    [400, 401, 403, 404].forEach((status) => {
      expect(isRetryableRequestError(xhr(status))).toBe(false);
    });
  });

  it('gives up when the response could not be parsed', () => {
    // jQuery keeps the native status text, so an unparsable response arrives as a plain 200
    expect(isRetryableRequestError(xhr(200, 'OK'))).toBe(false);
  });

  it('gives up on anything it cannot recognise', () => {
    expect(isRetryableRequestError(new Error('Something went wrong'))).toBe(false);
    expect(isRetryableRequestError({ status: 'nope' })).toBe(false);
    expect(isRetryableRequestError({})).toBe(false);
    expect(isRetryableRequestError(undefined)).toBe(false);
    expect(isRetryableRequestError(null)).toBe(false);
    expect(isRetryableRequestError('500')).toBe(false);
  });
});
