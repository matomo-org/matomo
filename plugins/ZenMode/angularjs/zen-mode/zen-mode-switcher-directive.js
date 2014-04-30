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
angular.module('piwikApp.directive').directive('piwikZenModeSwitcher', function($rootElement, $cookies) {

    var zenMode = !!parseInt($cookies.zenMode, 10);

    var initDone = false;

    function updateZenMode()
    {
        if (!initDone) {
            initDone = true;
            init();
        }

        $cookies.zenMode = zenMode ? '1' : '0';

        if (zenMode) {
            $rootElement.addClass('zenMode');
        } else {
            $rootElement.removeClass('zenMode');
        }

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

    function init () {
        var menuNode = $('.Menu--dashboard');
        menuNode.on('piwikSwitchPage', onItemSelect);
        menuNode.on('mouseenter', 'li:has(ul)', overMainLI);
        menuNode.on('mouseleave', 'li:has(ul)', outMainLI);
    }

    if (zenMode) {
        updateZenMode();
    }

    return {
        restrict: 'A',
        compile: function (element, attrs) {

            element.on('click', function() {
                zenMode = !zenMode;
                updateZenMode();
            });

            return function () {
            };
        }
    };
});