/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div matomo-what-is-new>
 */
(function () {
    angular.module('piwikApp').directive('matomoWhatIsNew', matomoWhatIsNew);

   matomoWhatIsNew.$inject = ['piwik'];

    function matomoWhatIsNew(piwik) {

        return {
            restrict: 'A',
            compile: function (element, attrs) {
                element.on('click', function (e) {
                    e.stopPropagation();
                    e.preventDefault();

                    var div = '<div ng-show="loading" class="loadingPiwik">\n' +
                        '    <img src="plugins/Morpheus/images/loading-blue.gif" alt=""/> <span>'+_pk_translate('General_LoadingData')+'</span>\n' +
                        '</div>';
                    piwik.helper.modalConfirm(div);

                    var ajaxRequest = new ajaxHelper();
                    ajaxRequest.addParams(piwik.helper.getArrayFromQueryString('module=CoreAdminHome&action=whatIsNew'), 'get');
                    ajaxRequest.setCallback(function (html) {
                        $(".modal.open .modal-content").html(html);
                    });
                    ajaxRequest.setFormat('html');
                    ajaxRequest.send();
                });

            }
        };
    }
})();