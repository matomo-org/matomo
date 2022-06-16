/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
$(function () {
  var iconRefresh = $('.top_controls .icon-reload');

  function refresh() {
    var Matomo = window.CoreHome.Matomo;
    var hashParsed = window.CoreHome.MatomoUrl.hashParsed.value;

    Matomo.postEvent('loadPage', hashParsed.category, hashParsed.subcategory);
  }

  function isCoreHomeModuleActive() {
    var search = window.CoreHome.MatomoUrl.parse(window.location.search.slice(1));
    return search.module === 'CoreHome';
  }

  if (isCoreHomeModuleActive()) {
    iconRefresh.removeClass('hidden');

    iconRefresh.on('click', function (e) {
      e.preventDefault();
      refresh();
    });

    piwikHelper.registerShortcut('r', _pk_translate('CoreHome_ShortcutRefresh'), function (event) {
      if (event.altKey) {
        return;
      }
      if (event.preventDefault) {
        event.preventDefault();
      } else {
        event.returnValue = false; // IE
      }

      refresh();
    });
  }
});
