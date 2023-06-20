/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 *  Listing Formatter for formatting listing out of a set of values
 *
 * @type {object}
 */
var ListingFormatter = (function () {

  /**
   * Formats the given numeric value with the given pattern
   *
   * @param {string} type
   * @param {Array} items
   * @returns {string}
   */
  function format(type, items) {
    switch (items.length) {
      case 0:
        return '';
      case 1:
        return items[0];
      case 2:
        var pattern = _pk_translate('Intl_ListPattern' + type + '2');
        return pattern.replace('{0}', items[0]).replace('{1}', items[1]);
      default:
        var patternStart = _pk_translate('Intl_ListPattern' + type + 'Start');
        var patternMiddle = _pk_translate('Intl_ListPattern' + type + 'Middle');
        var patternEnd = _pk_translate('Intl_ListPattern' + type + 'End');

        var result = patternStart;

        while (items.length > 2) {
          var pattern = items.length > 3 ? patternMiddle : patternEnd;
          result = result.replace('{0}', items.shift()).replace('{1}', pattern);
        }

        return result.replace('{0}', items[0]).replace('{1}', items[1]);
    }
  }

  /**
   * Public available methods
   */
  return {

    formatAnd: function (values) {
      return format('And', values);
    },

    formatOr: function (values) {
      return format('Or', values);
    },
  }
})();
