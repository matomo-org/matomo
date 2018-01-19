$(function () {

    angular.element(document).injector().invoke(handleZenMode);

    function handleZenMode ($rootElement, $cookies) {
        var zenMode = !!parseInt($cookies.get('zenMode'), 10);
        var iconSwitcher = $('.top_controls .icon-arrowup');

        iconSwitcher.click(function(event) {
           Mousetrap.trigger('z')
        });

        function updateZenMode() {
            if (zenMode) {
                $('body').addClass('zenMode');
                iconSwitcher.addClass('icon-arrowdown').removeClass('icon-arrowup');
                iconSwitcher.prop('title', _pk_translate('CoreHome_ExitZenMode'));
            } else {
                $('body').removeClass('zenMode');
                iconSwitcher.removeClass('icon-arrowdown').addClass('icon-arrowup');
                iconSwitcher.prop('title', _pk_translate('CoreHome_EnterZenMode'));
            }
        }

        piwikHelper.registerShortcut('z', _pk_translate('CoreHome_ShortcutZenMode'), function (event) {
            if (event.altKey) {
                return;
            }
            zenMode = !zenMode;
            $cookies.put('zenMode', zenMode ? '1' : '0');
            updateZenMode();
        });

        updateZenMode();
    }
});
