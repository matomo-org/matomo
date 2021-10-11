/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Similar to angulars $location but works around some limitation. Use it if you need to access
 * search params
 */
const PiwikUrl = {
  getSearchParam(paramName: string): string {
    const hash = window.location.href.split('#');

    const regex = new RegExp(`${paramName}(\\[]|=)`);
    if (hash && hash[1] && regex.test(decodeURIComponent(hash[1]))) {
      const valueFromHash = window.broadcast.getValueFromHash(paramName, window.location.href);

      // for date, period and idsite fall back to parameter from url, if non in hash was provided
      if (valueFromHash
        || (paramName !== 'date' && paramName !== 'period' && paramName !== 'idSite')
      ) {
        return valueFromHash;
      }
    }

    return window.broadcast.getValueFromUrl(paramName, window.location.search);
  },
};

export default PiwikUrl;
