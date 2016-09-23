/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * If the given text or resolved expression matches any text within the element, the matching text will be wrapped
 * with a class.
 *
 * Example:
 * <div piwik-autocomplete-matched="'text'">My text</div> ==> <div>My <span class="autocompleteMatched">text</span></div>
 *
 * <div piwik-autocomplete-matched="searchTerm">{{ name }}</div>
 * <input type="text" ng-model="searchTerm">
 */
(function () {
    angular.module('piwikApp.directive').directive('piwikAttributes', piwikAttributes);

    piwikAttributes.$inject = ['$sanitize'];

    function piwikAttributes(piwik, $sanitize) {

        return {
            link: function (scope, element, attrs) {
                if (!attrs.piwikAttributes || !angular.isString(attrs.piwikAttributes)) {
                    return;
                }

                function applyAttributes(attributes)
                {
                    if (angular.isObject(attributes)) {
                        angular.forEach(attributes, function (value, key) {
                            if (key === 'disabled') {
                                element.prop(key, value);
                            } else {
                                element.attr(key, value);
                            }
                        });
                    }
                }

                applyAttributes(JSON.parse(attrs.piwikAttributes));

                attrs.$observe('piwikAttributes', function (newVal) {
                    applyAttributes(JSON.parse(newVal));
                });

            }
        };
    }
})();
