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
const MatomoUrl = {
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

  onLocationChange(callback: (newLocation: URLSearchParams) => void): void {
    window.addEventListener('hashchange', () => {
      const newLocation = new URLSearchParams(window.location.hash.replace(/^[#?/]+/, ''));
      callback(newLocation);
    });
  },

  parseHashQuery(): QueryParameters {
    return this.parseQueryString(window.location.hash.replace(/^[#?/]+/, ''));
  },

  parseQueryString(query: string): QueryParameters {
    const params = new URLSearchParams(query);
    const result: QueryParameters = {};

    // TODO: doesn't handle object query params
    Array.from(params.keys()).forEach((name) => {
      if (/[[\]]/.test(name)
        || name.indexOf('%5B%5D') !== -1
      ) {
        result[name] = params.getAll(name);
      } else {
        result[name] = params.get(name);
      }
    });

    return result;
  },

  stringify(search: QueryParameters): string {
    // TODO: using $ since URLSearchParams does not handle array params the way Matomo uses them
    return $.param(search);
  },
};

export default MatomoUrl;
