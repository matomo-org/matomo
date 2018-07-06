/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <select piwik-material-select>
 */
(function () {
    angular.module('piwikApp').directive('piwikMaterialSelect', piwikMaterialSelect);

    piwikMaterialSelect.$inject = [];

    function piwikMaterialSelect() {
        return {
            restrict: 'A',
            link: function (scope, $element) {
                if (!$element.is('select')) {
                    throw new Error('piwik-material-select can only be used on <select> elements');
                }

                $element.material_select();

                // use default option as input placeholder for proper styling
                var $defaultOption = $element.children('option[placeholder]').eq(0);
                if ($defaultOption.length) {
                    var $materialInput = $element.closest('.select-wrapper').find('input');

                    var defaultValue = $defaultOption.text();
                    $materialInput.val('').attr('placeholder', defaultValue);

                    $element.change(function () {
                        if ($materialInput.val() === defaultValue) {
                            $materialInput.val('');
                        }
                    });
                }

                // when the dropdown is shown, set a high z-index on this select so it will be over other selects
                $element.closest('.select-wrapper').find('input').click(function () {
                    // TODO
                });
            },
        };
    }
})();