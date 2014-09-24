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
    angular.module('piwikApp.directive').directive('piwikAutocompleteMatched', piwikAutocompleteMatched);

    function piwikAutocompleteMatched() {
        return function(scope, element, attrs) {
            var searchTerm;

            scope.$watch(attrs.piwikAutocompleteMatched, function(value) {
                searchTerm = value;
                updateText();
            });

            function updateText () {
                if (!searchTerm || !element) {
                    return;
                }

                var content   = element.html();
                var startTerm = content.toLowerCase().indexOf(searchTerm.toLowerCase());

                if (-1 !== startTerm) {
                    var word = content.substr(startTerm, searchTerm.length);
                    content = content.replace(word, '<span class="autocompleteMatched">' + word + '</span>');
                    element.html(content);
                }
            }
        };
    }
})();