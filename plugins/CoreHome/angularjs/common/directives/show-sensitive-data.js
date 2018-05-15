/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Handles visibility of sensitive data. By default data will be shown replaced with stars (*)
 * On click on the element the full data will be shown
 *
 * Configuration attributes:
 * data-show-characters          number of characters to show in clear text (defaults to 6)
 * data-click-element-selector   selector for element that will show the full data on click (defaults to element)
 *
 * Example:
 * <div piwik-show-sensitive-date="some text"></div>
 */
(function () {
    angular.module('piwikApp.directive').directive('piwikShowSensitiveData', piwikShowSensitiveData);

    function piwikShowSensitiveData(){
        return {
            restrict: 'A',
            link: function(scope, element, attr) {

                var sensitiveData = attr.piwikShowSensitiveData || attr.text();
                var showCharacters = attr.showCharacters || 6;
                var clickElement = attr.clickElementSelector || element;

                var protectedData = '';
                if (showCharacters > 0) {
                    protectedData += sensitiveData.substr(0, showCharacters);
                }
                protectedData += sensitiveData.substr(showCharacters).replace(/./g, '*');
                element.html(protectedData);

                function onClickHandler(event) {
                    element.html(sensitiveData);
                    $(clickElement).css({
                        cursor: ''
                    });
                    $(clickElement).tooltip("destroy");
                }

                $(clickElement).tooltip({
                    content: _pk_translate('CoreHome_ClickToSeeFullInformation'),
                    items: '*',
                    track: true
                });

                $(clickElement).one('click', onClickHandler);
                $(clickElement).css({
                    cursor: 'pointer'
                })
            }
        };
    }
})();
