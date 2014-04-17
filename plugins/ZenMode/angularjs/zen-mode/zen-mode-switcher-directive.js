/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-dialog="showDialog">...</div>
 * Will show dialog once showDialog evaluates to true.
 *
 * Will execute the "executeMyFunction" function in the current scope once the yes button is pressed.
 */
angular.module('piwikApp.directive').directive('piwikZenModeSwitcher', function($rootElement) {

    var zenMode = false;

    function updateZenMode()
    {
        $rootElement.toggleClass('zenMode');
        $rootElement.trigger('zen-mode', zenMode);
    }

    function overMainLI () {
        if (!zenMode) {
            return;
        }
        var $this = $(this);
        var position = $this.position();
        var width = $this.width();

        $this.find('ul').css({left: position.left + 'px', display: 'block', minWidth: width+'px'});
    };

    function outMainLI () {
        if (!zenMode) {
            return;
        }
        var $this = $(this);
        $this.find('ul').css({left: '', display: '', minWidth: ''});
    };

    function onItemSelect()
    {
        $('.Menu--dashboard').find('ul ul').css('display', '')
    }

    var initDone = false;
    function init () {
        if (!initDone) {
            initDone = true;
            var menuNode = $('.Menu--dashboard');
            menuNode.on('piwikSwitchPage', onItemSelect);
            menuNode.find('li:has(ul)').hover(overMainLI, outMainLI);
        }
    }

    return {
        restrict: 'A',
        compile: function (element, attrs) {

            element.on('click', function() {
                zenMode = !zenMode;
                init();
                updateZenMode();
            });

            return function () {
            };

        }
    };
});