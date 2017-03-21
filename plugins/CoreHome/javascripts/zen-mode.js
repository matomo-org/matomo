$(function () {


    angular.element(document).injector().invoke(handleZenMode);

    function handleZenMode ($rootElement, $cookies) {
        var zenMode = !!parseInt($cookies.get('zenMode'), 10);

        function updateZenMode() {
            if (zenMode) {
                $('body').addClass('zenMode');
            } else {
                $('body').removeClass('zenMode');
            }
        }

        Mousetrap.bind('z', function (event) {
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