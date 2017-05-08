/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-plugin-upload>
 */
(function () {

    angular.module('piwikApp').directive('piwikPluginUpload', piwikPluginUpload);

    piwikPluginUpload.$inject = ['piwik'];

    function piwikPluginUpload(piwik){

        return {
            restrict: 'A',
            compile: function (element, attrs) {

                return function (scope, element, attrs) {

                    $('.uploadPlugin').click(function (event) {
                        event.preventDefault();

                        piwikHelper.modalConfirm('#installPluginByUpload', {});
                    });

                    $('#uploadPluginForm').submit(function (event) {

                        var $zipFile = $('[name=pluginZip]');
                        var fileName = $zipFile.val();

                        if (!fileName || '.zip' != fileName.slice(-4)) {
                            event.preventDefault();
                            alert(_pk_translate('CorePluginsAdmin_NoZipFileSelected'));
                        }
                    });
                };
            }
        };
    }
})();