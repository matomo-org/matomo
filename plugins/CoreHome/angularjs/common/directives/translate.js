/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Directive for easy & safe complex internationalization. This directive allows
 * users to embed the sprintf arguments used in internationalization inside an HTML
 * element. Since the HTML will eventually be sanitized by AngularJS, HTML can be used
 * within the sprintf args. Using the filter, this is not possible w/o manually sanitizing
 * and creating trusted HTML, which is not as safe.
 *
 * Usage:
 * <span piwik-translate="Plugin_TranslationToken">
 *     first arg::<strong>second arg</strong>::{{ unsafeDataThatWillBeSanitized }}
 * </span>
 */
angular.module('piwikApp.directive').directive('piwikTranslate', function() {
    return {
        restrict: 'A',
        scope: {
            piwikTranslate: '@'
        },
        compile: function(element, attrs) {
          var parts = element.html().split('::'),
                translated = _pk_translate(attrs.piwikTranslate, parts);
            element.html(translated);
        }
    };
});
