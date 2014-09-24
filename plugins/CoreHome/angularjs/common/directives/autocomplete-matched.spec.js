/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    describe('piwikAutocompleteMatchedDirective', function() {
        var $compile;
        var $rootScope;

        beforeEach(module('piwikApp.directive'));
        beforeEach(inject(function(_$compile_, _$rootScope_){
            $compile = _$compile_;
            $rootScope = _$rootScope_;
        }));

        function assertRenderedContentIs(query, expectedResult) {
            var template = '<div piwik-autocomplete-matched="\'' + query + '\'">My Content</div>';
            var element  = $compile(template)($rootScope);
            $rootScope.$digest();
            expect(element.html()).to.eql(expectedResult);
        }

        describe('#piwikAutocompleteMatched()', function() {

            it('should not change anything if query does not match the text', function() {
                assertRenderedContentIs('Whatever', 'My Content');
            });

            it('should wrap the matching part and find case insensitive', function() {
                assertRenderedContentIs('y cont', 'M<span class="autocompleteMatched">y Cont</span>ent');
            });

            it('should be able to wrap the whole content', function() {
                assertRenderedContentIs('my content', '<span class="autocompleteMatched">My Content</span>');
            });

            it('should find matching content case sensitive', function() {
                assertRenderedContentIs('My Co', '<span class="autocompleteMatched">My Co</span>ntent');
            });
        });
    });
})();