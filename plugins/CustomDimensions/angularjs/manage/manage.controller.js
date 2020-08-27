/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ManageCustomDimensionsController', ManageCustomDimensionsController);

    ManageCustomDimensionsController.$inject = ['$scope', '$rootScope', '$location', 'piwik'];

    function ManageCustomDimensionsController($scope, $rootScope, $location, piwik) {

        this.editMode = false;

        var self = this;

        function getValidDimensionScope(scope)
        {
            if (-1 !== ['action', 'visit'].indexOf(scope)) {
                return scope;
            }

            return '';
        }

        function initState() {
            // as we're not using angular router we have to handle it manually here
            var $search = $location.search();
            if ('idDimension' in $search) {
                
                var scope = getValidDimensionScope($search['scope']);

                if ($search.idDimension === 0 || $search.idDimension === '0') {
                    var parameters = {isAllowed: true, scope: scope};
                    $rootScope.$emit('CustomDimensions.initAddDimension', parameters);
                    if (parameters && !parameters.isAllowed) {
                        self.editMode = false;
                        self.dimensionId = null;
                        self.dimensionScope = '';

                        return;
                    }
                }

                self.editMode = true;
                self.dimensionId = parseInt($search['idDimension'], 10);
                self.dimensionScope = scope;
            } else {
                self.editMode = false;
                self.dimensionId = null;
                self.dimensionScope = '';
            }
            
            piwik.helper.lazyScrollToContent();
        }

        initState();

        var onChangeSuccess = $rootScope.$on('$locationChangeSuccess', initState);

        $scope.$on('$destroy', function() {
            if (onChangeSuccess) {
                onChangeSuccess();
            }
        });
    }
})();
