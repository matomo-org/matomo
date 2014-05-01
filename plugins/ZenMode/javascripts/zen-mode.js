/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    if (!isDashboard()) {
        return;
    }

    var addedElement = $('#topRightBar').append(
          '<span class="topBarElem activateZenMode" piwik-zen-mode-switcher>'
        + '<img src="plugins/CoreHome/images/navigation_expand.png">'
        + ' </span>'
    );

    piwikHelper.compileAngularComponents(addedElement);

    addedElement = $('.Menu--dashboard').prepend(
          '<span piwik-zen-mode-switcher class="deactivateZenMode">'
        + '<img src="plugins/CoreHome/images/navigation_collapse.png" >'
        + '</span>');

    piwikHelper.compileAngularComponents(addedElement);

    angular.element(document).injector().invoke(handleZenMode);

    function handleZenMode ($rootElement, $cookies) {

        var zenMode = !!parseInt($cookies.zenMode, 10);

        $rootElement.on('zen-mode-toggle', toggleZenMode);

        function toggleZenMode()
        {
            zenMode = !zenMode;

            updateZenMode();
        }

        function updateZenMode()
        {
            $cookies.zenMode = zenMode ? '1' : '0';

            if (zenMode) {
                $rootElement.addClass('zenMode');
                initMenu();
            } else {
                $rootElement.removeClass('zenMode');
                uninitMenu();
            }

            resetSubmenu();
        }

        if (zenMode) {
            updateZenMode();
        }

        Mousetrap.bind('alt+f', function() {
            if (isDashboardWidgetsArea()) {
                return;
            }

            $('.dataTableFooterIcons').toggle();
        });

        Mousetrap.bind('alt+z', function() {
            toggleZenMode();
        });

        Mousetrap.bind('alt+s', function(event) {
            if (event.preventDefault) {
                event.preventDefault();
            } else {
                event.returnValue = false; // IE
            }

            $('.quick-access input').focus();
        });
    }

    function isDashboardWidgetsArea()
    {
        return !!$('#dashboardWidgetsArea').length;
    }

    function isDashboard()
    {
        return !!$('.Menu--dashboard').length;
    }

    function initMenu () {
        var menuNode = $('.Menu--dashboard');
        menuNode.on('piwikSwitchPage', resetSubmenu);
        menuNode.on('mouseenter', 'li:has(ul)', overMainLI);
        menuNode.on('mouseleave', 'li:has(ul)', outMainLI);

        $('#Searchmenu').on('keydown focus', '.quick-access input', showQuickAccessMenu);
        $('#Searchmenu').on('blur', '.quick-access input', hideQuickAccessMenu);
    }

    function uninitMenu () {
        var menuNode = $('.Menu--dashboard');
        menuNode.off('piwikSwitchPage', resetSubmenu);
        menuNode.off('mouseenter', 'li:has(ul)', overMainLI);
        menuNode.off('mouseleave', 'li:has(ul)', outMainLI);

        $('#Searchmenu').off('keydown focus', '.quick-access input', showQuickAccessMenu);
        $('#Searchmenu').off('blur', '.quick-access input', hideQuickAccessMenu);
    }

    function overMainLI () {
        var $this = $(this);
        var position = $this.position();
        var width = $this.width();

        $this.find('ul').css({left: position.left + 'px', display: 'block', minWidth: width+'px'});
    }

    function outMainLI () {
        $(this).find('ul').css({left: '', display: '', minWidth: ''});
    }

    function resetSubmenu()
    {
        $('.Menu--dashboard').find('li:has(ul)').mouseleave();
    }

    function showQuickAccessMenu() {
        resetSubmenu();
        $('#Searchmenu').mouseenter();
    }

    function hideQuickAccessMenu() {
        $('#Searchmenu').mouseleave();
    }
});

