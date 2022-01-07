/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


(function () {
    angular.module('piwikApp.directive').directive('piwikShowSensitiveData', piwikShowSensitiveData);

    function piwikShowSensitiveData(){
        return {
            restrict: 'A',
            link: function(scope, element, attr) {

                var sensitiveData = attr.piwikShowSensitiveData || (attr.text ? attr.text() : '');
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
                });
            }
        };
    }
})();
