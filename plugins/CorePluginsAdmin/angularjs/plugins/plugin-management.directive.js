/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-plugin-management>
 */
(function () {

    angular.module('piwikApp').directive('piwikPluginManagement', piwikPluginManagement);

    piwikPluginManagement.$inject = ['piwik'];

    function piwikPluginManagement(piwik){

        return {
            restrict: 'A',
            compile: function (element, attrs) {

                return function (scope, element, attrs) {

                    var uninstallConfirmMessage = '';

                    element.find('.uninstall').click(function (event) {
                        event.preventDefault();

                        var link = $(this).attr('href');
                        var pluginName = $(this).attr('data-plugin-name');

                        if (!link || !pluginName) {
                            return;
                        }

                        if (!uninstallConfirmMessage) {
                            uninstallConfirmMessage = $('#uninstallPluginConfirm').text();
                        }

                        var messageToDisplay = uninstallConfirmMessage.replace('%s', pluginName);

                        $('#uninstallPluginConfirm').text(messageToDisplay);

                        piwikHelper.modalConfirm('#confirmUninstallPlugin', {
                            yes: function () {
                                window.location = link;
                            }
                        });
                    });

                    element.find('.plugin-donation-link').click(function (event) {
                        event.preventDefault();

                        var overlayId = $(this).data('overlay-id');

                        piwikHelper.modalConfirm('#'+overlayId, {});
                    });

                };
            }
        };
    }
})();