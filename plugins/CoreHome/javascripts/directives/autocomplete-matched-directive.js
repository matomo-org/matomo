/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp.directive').directive('piwikAutocompleteMatched', function() {
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

            var content   = element.text();
            var startTerm = content.toLowerCase().indexOf(searchTerm);
            if (-1 !== startTerm) {
                var word = content.substr(startTerm, searchTerm.length);
                content = content.replace(word, '<span class="autocompleteMatched">' + word + '</span>');
                element.html(content);
            };
        }
    };
});